<?php

namespace App\Http\Controllers\Butcher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
class IndexController extends Controller
{

    public function list(Request $request)
    {           
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised'],200);
            } else {
                $butcher = Auth::user();
                if($request->status){
                    $products = ProductResource::collection(Product::where('status',$request->status)->where('butcher_id',$butcher->id)->latest()->get());
                }else{
                    $products = ProductResource::collection(Product::where('butcher_id',$butcher->id)->latest()->get());
                }
                
                if( sizeof($products) != 0){
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'data' => $products
                    ], 200);   
                }else{
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => 'Product not found'
                        ],200);
                }
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'message' => 'Some error has been ocurred.'
                    ],200);
            }
        }catch (JWTException $e) {
            return response()->json([
                'code' => 404,
                'status' =>  false,
                'message' => 'Something went wrong'
            ],404);
        }
    }


}
