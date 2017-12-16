<?php

namespace App\Http\Controllers;


use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Member;
use Validator;
use JWTAuth;

class SocialController extends Controller{
  private $data;

  public function cek_login(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'username'  => 'required',
      'email'     => 'required',
      'social_id'     => 'required',
      'name'  => 'required',
      'avatar' => 'required'
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

    $cekUser = Member::where('social_id', $request->social_id)->count();
    if ($cekUser > 0) {
      return $this->signin($request);
    }else {
      return $this->signup($request);
    }

  }

  public function signup($request){
    $validator = Validator::make($request->all(), [
      'username'  => 'required|unique:members',
      'email'     => 'required|unique:members',
      'social_id'     => 'required|unique:members',
      'name'  => 'required',
      'avatar' => 'required'
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
    //decode base64 to image
    $strName = str_replace(' ','_',$request->json('username'));
    $image_parts    = explode(";base64,", $request->json('avatar'));
    $image_type_aux = explode("image/", $image_parts[0]);
    $image_type     = $image_type_aux[1];
    $image_base64   = base64_decode($image_parts[1]);
    $file_name      = $strName.'_'. uniqid() . '.'.$image_type;
    $file           = public_path('../../assets/images/avatars/') . $file_name;
    file_put_contents($file, $image_base64);

    $member = Member::create([
      'username'  => $request->json('username'),
      'email'  => $request->json('email'),
      'name'  => $request->json('name'),
      'social_id'  => $request->json('social_id'),
      'password'=>'login facebook',
      'avatar' => $file_name
    ]);

    $token = JWTAuth::fromUser($member);
    return response()->json([
      'status'  => 'success',
      'message' => 'berhasil register dan login',
      'data'=> [
        'member_id' => $member->id,
        'token'     => $token
      ]
    ]);
  }

public function signin($request){
  $validator = Validator::make($request->all(), [
    'username'  => 'required','email'  => 'required','social_id'  => 'required'
  ]);

  if ($validator->fails()) {
    $messages = collect($validator->messages());
    foreach ($messages as $message => $value) {
      $data[$message] = $value[0];
    }
    return response()->json([
      'status'  => 'failed',
      'message' => 'Periksa masukkan anda',
      'data' => $data,
    ]);
  }

  $data_user = Member::where('social_id', $request->social_id)->first();

  try {
    // attempt to verify the credentials and create a token for the user
    if (! $token = JWTAuth::fromUser($data_user)) {
      return response()->json([
        'status'  => 'failed',
        'message' => 'periksa kembali user dan password'
      ], 401);
    }
  } catch (JWTException $e) {
    // something went wrong whilst attempting to encode the token
    return response()->json([
      'status'  => 'failed',
      'message' => 'could_not_create_token'
    ], 500);
  }

  // all good so return the token
  return response()->json([
    'status'  => 'success',
    'message' => 'berhasil login',
    'data'=> [
      'member_id' => $data_user->id,
      'token'     => $token
    ]
  ]);

}
}
