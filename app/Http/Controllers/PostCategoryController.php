<?php

namespace App\Http\Controllers;

use App\Models\PostCategory;
use Illuminate\Http\Request;

class PostCategoryController extends Controller
{
    public function index()
    {
        // Logic to retrieve and return post categories
        $data = PostCategory::query()->orderBy('id', 'desc')->paginate(6);
        return response()->json(['data' => $data, 'status' => true]);
    }
}
