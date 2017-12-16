<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
  public function index()
  {
    $category = Category::all();
    return response()->json([
      'status'  => 'success',
      'message' => 'berhasil menampilkan data',
      'data'=>$category
    ]);

  }

}
