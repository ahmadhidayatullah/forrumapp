<?php

namespace App\Http\Controllers;


use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Member;
use Validator;
use JWTAuth;

class AuthController extends Controller{
  private $data;
  public function signup(Request $request){
    $validator = Validator::make($request->all(), [
      'username'  => 'required|unique:members',
      'email'     => 'required|unique:members',
      'password'  => 'required',
      'name'  => 'required',
      'avatar' => 'required'
    ]
    // [
    //   'email.unique' => 'e-mail sudah digunakan',
    //   'email.required' => 'e-mail harus diisi',
    //   'username.required|name.required' => 'data harus diisi sudah digunakan',
    // ]
  );

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
  $strName = str_replace(' ', '_', $request->json('username'));
  $image_parts    = explode(";base64,", $request->json('avatar'));
  $image_type_aux = explode("image/", $image_parts[0]);
  $image_type     = $image_type_aux[1];
  $image_base64   = base64_decode($image_parts[1]);
  $file_name      = $strName.'_'. uniqid() . '.'.$image_type;
  $file           = public_path('storage/avatars/') . $file_name;
  file_put_contents($file, $image_base64);

  $member = Member::create([
    'username'  => $request->json('username'),
    'email'  => $request->json('email'),
    'name'  => $request->json('name'),
    'password'  => bcrypt($request->json('password')),
    'avatar' => $file_name
  ]);

  return response()->json([
    'status'  => 'success',
    'message' => 'Data user berhasil masuk',
    'data'    => $member
  ]);
}

public function signin(Request $request){
  $validator = Validator::make($request->all(), [
    'username'  => 'required','password'  => 'required',
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

  // grab credentials from the request
  $credentials = $request->only('username', 'password');

  try {
    // attempt to verify the credentials and create a token for the user
    if (! $token = JWTAuth::attempt($credentials)) {
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
      'member_id' => $request->user()->id,
      'token'     => $token
    ]
  ]);

}
}
