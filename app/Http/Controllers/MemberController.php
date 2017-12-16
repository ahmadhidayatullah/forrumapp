<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Member;
use Validator;
use File;

class MemberController extends Controller
{
    public function get_notif(Request $request)
    {
      // $notifications = Notification::where('member_id', $request->user()->id)->select('id', 'subject','seen', 'created_at')->orderBy('id','desc')->get();
      $notifications = Notification::with(['report'=> function($query){
        $query->addSelect('id','image');
      }])->where('member_id', $request->user()->id)->select('id', 'subject','seen','report_id', 'created_at')->orderBy('id','desc')->get();

      if ($notifications->count() > 0) {
        Notification::where('member_id', $request->user()->id)->where('seen', 0)->update(['seen'=> 1]);
        return response()->json([
          'status'  => 'success',
          'message' => 'Data berhasil ditampilakan',
          'data' => $notifications
        ]);
      }else {
        return response()->json([
          'status'  => 'success',
          'message' => 'notifikasi kosong',
          'data' => null
        ]);
      }
    }
    public function get_notif_count(Request $request)
    {
      $count = $request->user()->notifications->where('seen', 0)->count();
      return response()->json([
        'status'  => 'success',
        'message' => 'Data berhasil dihitung',
        'data' => $count,
      ]);
    }
    public function show(Request $request)
    {
      $counts = Member::withCount([
        'reports',
        'reports AS waiting' => function ($query) {
          $query->where('status','menunggu');
        },
        'reports AS process' => function ($query) {
          $query->where('status','proses');
        },
        'reports AS done' => function ($query) {
          $query->where('status','selesai');
        },
        'reports AS rejected' => function ($query) {
          $query->where('status','ditolak');
        }
        ])->where('id', $request->user()->id)->get();

        return $counts;

    }

    public function updateText(Request $request, $id)
    {
      $validator = Validator::make($request->all(), [
        'email'     => [
          'required',
          Rule::unique('members')->ignore($id),
        ],
        'name'      => 'required',
      ]);

      if ($validator->fails()) {
        $messages = collect($validator->messages());
        foreach ($messages as $message => $value) {
          $data[$message] = $value[0];
        }
        return response()->json([
          'status'  => 'failed',
          'message' => 'Data gagal diupdate',
          'data' => $data,
        ]);
      }

    $member = Member::find($id);
    $member->email    = $request->email;
    $member->name     = $request->name;
    $member->gender   = $request->gender;
    $member->address  = $request->address;
    $member->birtday  = $request->birtday;

    $member->save();

    return response()->json([
      'status'    => 'success',
      'message'   => 'data berhasil diedit',
      'data'      => $member,
    ]);

    }
    public function change_password(Request $request, $id)
    {
      $validator = Validator::make($request->all(), [
        'new_password'  => 'required',
        'conf_password' => 'required',
      ]);

      if ($validator->fails()) {
        $messages = collect($validator->messages());
        foreach ($messages as $message => $value) {
          $data[$message] = $value[0];
        }
        return response()->json([
          'status'  => 'failed',
          'message' => 'Data gagal disimpan',
          'data' => $data,
        ]);
      }

    if ($request->json('new_password') != $request->json('conf_password')) {
      return response()->json([
        'status'  => 'failed',
        'message' => 'data tidak sama',
        'data'=>null
      ]);
    }

    $member = Member::find($id);
    $member->password=bcrypt($request->json('new_password'));

    $member->save();

    return response()->json([
      'status'    => 'success',
      'message'   => 'password berhasil diedit',
      'data'      => $member,
    ]);

    }

    public function update_avatar(Request $request, $id){
      $validator = Validator::make($request->all(), [
        'avatar'  => 'required',
      ]);

      if ($validator->fails()) {
        $messages = collect($validator->messages());
        foreach ($messages as $message => $value) {
          $data[$message] = $value[0];
        }
        return response()->json([
          'status'  => 'failed',
          'message' => 'Data gagal diupdate',
          'data' => $data,
        ]);
      }

    $member = Member::find($id);

    //decode base64 to image
    $strName = str_replace(' ', '_', $member->username);
    $image_parts    = explode(";base64,", $request->json('avatar'));
    $image_type_aux = explode("image/", $image_parts[0]);
    $image_type     = $image_type_aux[1];
    $image_base64   = base64_decode($image_parts[1]);
    $file_name      = $strName.'_'. uniqid() . '.'.$image_type;
    $file           = public_path('storage/avatars/') . $file_name;
    File::Delete(public_path('public/avatars/') . $member->avatar);
    file_put_contents($file, $image_base64);
    //end decode

    //set data avatar
    $member->avatar=$file_name;

    $member->save();

    return response()->json([
      'status'    => 'success',
      'message'   => 'data berhasil diedit',
      'data'      => $member,
    ]);
  }
}
