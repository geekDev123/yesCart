<?php

namespace App\Http\Controllers\Butcher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Resources\OrderResource;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class OrderController extends Controller
{
    protected $user;
    
    public function list(Request $request)
    {   
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised'],200);
            } else {
                if($request->status){
                    $orders = OrderResource::collection(Order::latest()
                            ->where('status',$request->status)
                            ->get());
                }else{
                    $orders = OrderResource::collection(Order::latest()->get());
                }
                
                if( sizeof($orders) != 0){
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'data' => $orders
                    ], 200);   
                }else{
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => 'Orders not found'
                        ],200);
                }
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'message' => 'Some error has been ocurred.'
                    ],200);
            }
        } catch (JWTException $e) {
            return response()->json([
                'code' => 404,
                'status' =>  false,
                'message' => 'Something went wrong'
            ],404);
        }
    }
}
