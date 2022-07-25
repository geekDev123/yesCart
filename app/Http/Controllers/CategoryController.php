<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Category;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    public function list()
    {
        $categories = CategoryResource::collection(Category::latest()->get());
        if($categories){
            return response()->json([
                'code' => 200,
                'status' => true,
                'data' => $categories
            ], 200);   
        }else{
            return response()->json([
                'code' => 404,
                'status' => false,
                'message' => 'Some error has been ocurred.'
                ],200);
        }
         
    }
}
