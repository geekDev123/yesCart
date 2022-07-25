<?php

namespace App\Http\Controllers\Butcher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ProductResource;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductController extends Controller
{
    public function store(Request $request)
    {   
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised'],200);
            } else {
                $butcher = Auth::user();
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|between:2,100',
                    'amount' => 'required|string',
                    'image' => 'required|mimes:jpeg,png,jpg,gif',
                    'quantity' => 'required|string',
                    'category_id' => 'required',
                    'delivery_type' => 'required',
                    'delivery_day' => 'required'
                ]);
                if($validator->fails()){ 
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => $validator->errors(),
                    ], 200);
                }

                if($request->hasFile('image')) 
                {
                    $getImage = $request->image;
                    $imageName = time().'.'.$getImage->extension();
                    $imagePath = public_path(). '/images/products/'.$request->type;
                    $imageUrl = '/images/products/'.time().'.'.$getImage->extension();
                    $getImage->move($imagePath, $imageName);
                }else{
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => $validator->errors(),
                    ], 200);
                }
                
                $product = new Product();
                $product->name = $request->name;
                $product->amount = $request->amount;
                $product->description = $request->description;
                $product->image = $imageUrl;
                $product->quantity = $request->quantity;
                $product->category_id = $request->category_id;
                $product->status = 'instock';
                $product->butcher_id = $butcher->id;
                $product->delivery_type = $request->delivery_type;
                $product->delivery_day = $request->delivery_day;
                $product->save();

                if(intval($product->id)){
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'message' => 'Product added successfully',
                        'data' => new ProductResource($product)
                    ], 200);
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

 
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {
                $butcher = Auth::user();
                $validator = Validator::make($request->all(), [
                    'status' => 'required',
                    'quantity' => 'required',
                ]);
                if($validator->fails()){ 
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => $validator->errors(),
                    ], 404);
                }
            
                $product = Product::where('id',$request->id)->where('butcher_id',$butcher->id)->first();
                if($product){
                    $product->status = $request->status;
                    $product->quantity = $request->quantity;
                    $product->butcher_id = $butcher->id;
                    $product->save();
                    if(intval($product->id)){
                        return response()->json([
                            'code' => 200,
                            'status' => true,
                            'message' => 'Product updated successfully',
                            'data' => new ProductResource($product)
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


    public function show($id)
    {
       
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {        
                $product = Product::where('id',$id)->first();  
                if( $product != null){
                    $data = new ProductResource($product);
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'data' => $data
                    ], 200);   
                }else{
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => 'Product not found'
                        ],404);
                }
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'message' => 'Some error has been ocurred.'
                    ],404);
            }
        } catch (JWTException $e) {
            return response()->json([
                'code' => 404,
                'status' =>  false,
                'message' => 'Something went wrong'
            ],404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {
                $product = Product::find($request->id);
                $product->delete();
                if($product){
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'message' => 'Product deleted!',
                        'data' => new ProductResource($product)
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
