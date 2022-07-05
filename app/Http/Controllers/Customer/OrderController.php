<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth; 
use App\Models\Order;
use App\Models\Product;
use Stripe;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {
                $customer = Auth::user();
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|between:2,100',
                    'quantity' => 'required|string',
                    'product_id' => 'required'
                ]);
                if($validator->fails()){ 
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => $validator->errors(),
                    ], 404);
                }

                $product = Product::where('id',$request->product_id)->first();
                if($product){
                    /* Save Order */
                    $order = new Order();
                    $order->name = $request->name;
                    $order->quantity = $request->quantity;
                    $order->status = 'processing';
                    $order->amount = $product->amount*$request->quantity;
                    $order->product_id = $request->product_id;
                    $order->customer_id = $customer->id;
                    $order->save();
                    if(intval($order->id)){
                        // Update product stock
                        $new_quantity = $product->quantity - $request->quantity;
                        if($new_quantity > 0){
                            $product->quantity = $new_quantity;
                            $product->status = 'instock';
                            $product->update();
                        }else{
                            $product->quantity = $new_quantity;
                            $product->status = 'outofstock';
                            $product->update();
                        }

                        /* Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                        Stripe\Charge::create ([
                                "amount" => 100,
                                "currency" => "usd",
                                "source" => $request->stripeToken,
                                "description" => "This payment is initiated by Yescart Customer"
                        ]); */

                        return response()->json([
                            'code' => 200,
                            'status' => true,
                            'message' => 'Order created successfully',
                            'data' => new OrderResource($order)
                        ], 200);
                    }
                }
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'message' => 'Some error has been ocurred.'
                ], 404);
            }
        }catch (JWTException $e) {
            return response()->json([
                'code' => 404,
                'status' =>  false,
                'message' => 'Something went wrong'
            ],404);
        }
    }

    public function list(Request $request)
    {
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {
                $user = Auth::user();
                if($request->status){
                    $orders = OrderResource::collection(Order::latest()
                            ->where('customer_id',$user->id)
                            ->where('status',$request->status)
                            ->get());
                }else{
                    $orders = OrderResource::collection(Order::latest()
                    ->where('customer_id',$user->id)
                    ->get());
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
                        ],404);
                }
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'message' => 'Some error has been ocurred.'
                    ],404);
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
