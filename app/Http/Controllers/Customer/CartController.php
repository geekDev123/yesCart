<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth; 
use App\Models\Cart;
use App\Models\Product;
use App\Http\Resources\CartResource;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {
                $customer = Auth::user();
                $validator = Validator::make($request->all(), [
                    'product_id' => 'required',
                    'butcher_id' => 'required|string',
                    'quantity' => 'required',
                    'price' => 'required'
                ]);
                if($validator->fails()){ 
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => $validator->errors()->first(),
                    ], 404);
                }
            
                $get_product = Product::where('id',$request->product_id)->first();
                $get_cart = Cart::where('product_id',$request->product_id)->where('customer_id',$customer->id)->first();
              
                if(empty($get_product->quantity)){
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => 'Product is out of stock.'
                    ], 404);
                }else{
                    if(!$get_cart){
                        /* Save Cart */
                        $cart = new Cart();
                        $cart->product_id = $request->product_id;
                        $cart->butcher_id = $request->butcher_id;
                        $cart->customer_id = $customer->id;
                        $cart->quantity = $request->quantity;
                        $cart->price = $request->price;
                        $cart->save();
                        if(intval($cart->id)){
                                return response()->json([
                                    'code' => 200,
                                    'status' => true,
                                    'message' => 'Item added to cart successfully',
                                    'data' => new CartResource($cart)
                                ], 200);
                            //} 
                        }
                    }else{
                    if($get_cart->butcher_id != $request->butcher_id){
                        return response()->json([
                            'code' => 404,
                            'status' => false,
                            'message' => 'Not allowed to add item more than one butcher.'
                        ], 404);
                    }
                        return response()->json([
                            'code' => 404,
                            'status' => false,
                            'message' => 'Product is already in cart.'
                        ], 404);
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
                $cart = CartResource::collection(Cart::latest()
                ->where('customer_id',$user->id)
                ->get());
                
                if( sizeof($cart) != 0){
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'data' => $cart
                    ], 200);   
                }else{
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => 'Cart is empty'
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

    public function update(Request $request)
    {
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {
                $customer = Auth::user();
                $validator = Validator::make($request->all(), [
                    'quantity' => 'required',
                    'price' => 'required',
                ]);
                if($validator->fails()){ 
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => $validator->errors(),
                    ], 404);
                }
               
                $cart = Cart::where('product_id',$request->product_id)->where('customer_id',$customer->id)->first();
                //$product = Product::where('id',$cart->product_id)->first();
                if($cart){
                    $cart->quantity = $request->quantity;
                    $cart->price = $request->price;
                    $cart->save();
                    if(intval($cart->id)){
                        /*     
                        $new_quantity = $product->quantity - $request->quantity;
                        if($new_quantity > 0){
                            $product->quantity = $new_quantity;
                            $product->status = 'instock';
                            $product->update();
                        }else{
                            $product->status = 'outofstock';
                            $product->update();
                        } */
                        return response()->json([
                            'code' => 200,
                            'status' => true,
                            'message' => 'Cart updated successfully',
                            'data' => new CartResource($cart)
                        ], 200);
                    }
                    
                }else{
                    return response()->json([
                        'code' => 404,
                        'status' =>  false,
                        'message' => 'Some error has been ocurred.'
                    ],404);
                }
                
            }
            
        }catch (JWTException $e) {
            return response()->json([
                'code' => 404,
                'status' =>  false,
                'message' => 'Something went wrong'
            ],404);
        }
    }

    public function delete(Request $request)
    {
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {
                $cart = Cart::find($request->id);
                $cart->delete();
                if($cart){
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'message' => 'Cart Item deleted!',
                        'data' => new CartResource($cart)
                    ], 200);
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
}
