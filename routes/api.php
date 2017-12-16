<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware'=>['api']],function(){

  Route::post('/auth/signup', 'AuthController@signup');
  Route::post('/auth/signin', 'AuthController@signin');
  Route::post('/social/login/facebook','SocialController@cek_login');

  Route::group(['middleware'=>['jwt.auth']],function(){
    //User
    Route::get('/profile','MemberController@show');
    Route::put('/profile/{id}','MemberController@updateText');
    Route::put('/profile/change_password/{id}','MemberController@change_password');
    Route::put('/profile/update_avatar/{id}','MemberController@update_avatar');
    //Notification
    Route::get('/notification','MemberController@get_notif');
    Route::get('/notification/count','MemberController@get_notif_count');
    //report
    Route::post('/report','ReportController@store');
    Route::get('/report','ReportController@homepage');//beranda
    Route::get('/report/{id}','ReportController@show');
    Route::get('/report/user/reports','ReportController@user_report');//report's user
    Route::put('/report/update_text/{id}','ReportController@update_text');
    Route::put('/report/update_image/{id}','ReportController@update_image');
    Route::delete('/report/{id}','ReportController@destroy');
    //comment
    Route::post('/comment/{id_report}','CommentController@store');
    Route::put('/comment/{id_comment}','CommentController@update');
    Route::delete('/comment/{id_comment}','CommentController@destroy');
    //category
    Route::get('/category','CategoryController@index');
  });

});
