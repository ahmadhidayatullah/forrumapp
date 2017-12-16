<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginWebController extends Controller
{
    public function signin()
    {
        return view('admin.login');
    }
}
