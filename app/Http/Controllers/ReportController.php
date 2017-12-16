<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Reportproof;
use App\Http\Requests;
use Validator;
use File;

class ReportController extends Controller
{
    public function homepage()
    {
      return Report::with([
        'member' => function ($query) {
          $query->addSelect('id','username','avatar');
        },
        'category' => function ($query) {
          $query->addSelect('id','name','icon');
        },
        'comments.member' => function ($query) {
          $query->addSelect('id','username','avatar');
        }])->orderBy('created_at', 'desc')->get();

    }

    public function show($id)
    {

      $proof = Reportproof::where('report_id', $id)->get();
      if ($proof) {
        $proof = $proof;
      }else {
        $proof = [];
      }

      $report = Report::with([
        'member' => function ($query) {
          $query->addSelect('id','username','avatar');
        },
        'category' => function ($query) {
          $query->addSelect('id','name','icon');
        },
        'comments.member' => function ($query) {
          $query->addSelect('id','username','avatar');
        }])->where('id', $id)->first();
        $report['proofs'] = $proof;

      if (!$report) {
        return response()->json([
          'status'  => 'failed',
          'message' => 'laporan tidak ada',
        ]);
      }
      return $report;
    }
    public function user_report(Request $request)
    {
      $report = Report::with([
        'member' => function ($query) {
          $query->addSelect('id','username','avatar');
        },
        'category' => function ($query) {
          $query->addSelect('id','name','icon');
        },
        'comments.member' => function ($query) {
          $query->addSelect('id','username','avatar');
        }])->where('member_id', $request->user()->id)->orderBy('created_at', 'desc')->get();

      if (!$report) {
        return response()->json([
          'status'  => 'failed',
          'message' => 'laporan tidak ada',
        ]);
      }
      return $report;
    }
    public function store(Request $request)
    {

      $validator = Validator::make($request->all(), [
        'description' => 'required',
        'street'      => 'required',
        'lat'         => 'required',
        'long'        => 'required',
        'category_id' => 'required',
        'image'       => 'required'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status'  => 'failed',
          'message' => $validator->messages(),
        ]);
      }

      //decode base64 to image
      $strName = str_replace(' ', '_', $request->user()->username);
      $image_parts    = explode(";base64,", $request->json('image'));
      $image_type_aux = explode("image/", $image_parts[0]);
      $image_type     = $image_type_aux[1];
      $image_base64   = base64_decode($image_parts[1]);
      $file_name      = $strName.'_'. uniqid() . '.'.$image_type;
      $file           = public_path('../../assets/images/reports/') . $file_name;
      file_put_contents($file, $image_base64);

      $member = $request->user()->reports()->create([
        'description' => $request->json('description'),
        'street'      => $request->json('street'),
        'lat'         => $request->json('lat'),
        'long'        => $request->json('long'),
        'category_id' => $request->json('category_id'),
        'image'       => $file_name
      ]);

      return response()->json([
        'status'  => 'success',
        'message' => 'Data user berhasil masuk',
        'data'    => $member
      ]);
    }

    public function update_text(Request $request, $id)
    {
      $validator = Validator::make($request->all(), [
        'description' => 'required',
        'street'      => 'required',
        'lat'         => 'required',
        'long'        => 'required',
        'category_id' => 'required'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status'  => 'failed',
          'message' => $validator->messages(),
        ]);
      }

      $report = Report::find($id);

      //testing ownership of report
      if ($request->user()->id != $report->member_id) {
        return response()->json([
          'status'  => 'failed',
          'message' => 'laporan bukan milik anda',
        ]);
      }
      $report->description  = $request->description;
      $report->street       = $request->street;
      $report->lat          = $request->lat;
      $report->long         = $request->long;
      $report->category_id  = $request->category_id;

      $report->save();

      return response()->json([
        'status'    => 'success',
        'message'   => 'data berhasil diedit',
        'data'      => $report,
      ]);

    }

    public function update_image(Request $request, $id){
      $validator = Validator::make($request->all(), [
        'image'  => 'required',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'status'  => 'failed',
          'message' => $validator->messages(),
        ]);
      }

      $report = Report::find($id);

      //testing ownership of report
      if ($request->user()->id != $report->member_id) {
        return response()->json([
          'status'  => 'failed',
          'message' => 'laporan bukan milik anda',
        ]);
      }

      //decode base64 to image
      $strName = str_replace(' ', '_', $request->user()->username);
      $image_parts    = explode(";base64,", $request->json('image'));
      $image_type_aux = explode("image/", $image_parts[0]);
      $image_type     = $image_type_aux[1];
      $image_base64   = base64_decode($image_parts[1]);
      $file_name      = $strName.'_'. uniqid() . '.'.$image_type;
      $file           = public_path('../../assets/images/reports/') . $file_name;
      File::Delete(public_path('../../assets/images/reports/') . $report->image);
      file_put_contents($file, $image_base64);
      //end decode

      //set data avatar
      $report->image=$file_name;

      $report->save();

      return response()->json([
        'status'    => 'success',
        'message'   => 'data berhasil diedit',
        'data'      => $report,
      ]);
  }

  public function destroy(Request $request, $id)
  {
    $report = Report::find($id);
    //testing ownership of report
    if ($request->user()->id != $report->member_id) {
      return response()->json([
        'status'  => 'failed',
        'message' => 'laporan bukan milik anda',
      ],403);
    }
    File::Delete(public_path('../../assets/images/reports/') . $report->image);
    $report->delete();
    return response()->json([
      'status'  => 'success',
      'message' => 'data berhasil dihapus',
    ],200);
  }
}
