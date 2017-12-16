<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Comment;
use App\Http\Requests;
use App\Models\Report;
use Validator;

class CommentController extends Controller
{
    public function store(Request $request, $id)
    {
      $validator = Validator::make($request->all(), [
        'body' => 'required'
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

      $report = Report::findOrFail($id);

      $comment = $request->user()->comments()->create([

        'body'      => $request->json('body'),
        'report_id' => $id
      ]);

      if ($report->member_id != $request->user()->id) {
        Notification::create([
          'member_id'   => $report->member_id,
          'report_id' => $id,
          'subject'   => $request->user()->username.' mengomentari laporan anda'
        ]);
      }

      return response()->json([
        'status'  => 'success',
        'message' => 'Data user berhasil masuk',
        'data'    => $comment
      ]);
    }

    public function update(Request $request, $id)
    {
      $validator = Validator::make($request->all(), [
        'body' => 'required'
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

      $comment = Comment::find($id);

      //testing ownership of report
      if ($request->user()->id != $comment->member_id) {
        return response()->json([
          'status'  => 'failed',
          'message' => 'komentar bukan milik anda',
        ]);
      }

      $comment->body  = $request->body;

      $comment->save();

      return response()->json([
        'status'    => 'success',
        'message'   => 'data berhasil diedit',
        'data'      => $comment,
      ]);
    }

    public function destroy(Request $request, $id)
    {
        $comment = Comment::find($id);
        //testing ownership of report
        if ($request->user()->id != $comment->member_id) {
          return response()->json([
            'status'  => 'failed',
            'message' => 'komentar bukan milik anda',
          ],403);
        }

        $comment->delete();
        return response()->json([
          'status'  => 'success',
          'message' => 'data berhasil dihapus',
        ],200);
    }
}
