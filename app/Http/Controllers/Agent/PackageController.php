<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Models\Package;
use App\Models\Product;
use App\Models\PackageInfo;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\PackageResource;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class PackageController extends Controller
{
    /**
     * Store Package
     */
    public function store(Request $request)
    {   
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {
                $agent = Auth::user();
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|between:2,100',
                    'product_ids' => 'required',
                    'products_quantity' => 'required',
                    'delivery_type' => 'required|string',
                    //'delivery_day' => 'required|string',
                    'agent_id' => 'required',
                    'amount' => 'required|string'
                ]);
                if($validator->fails()){ 
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => $validator->errors(),
                    ], 404);
                }
               
                $get_product_image = Product::find($request->product_ids[0]);
              
                $package = new Package();
                $package->name = $request->name;
                $package->delivery_type = $request->delivery_type;
                $package->delivery_day = $request->delivery_day;
                $package->agent_id = $agent->id;
                $package->status = '0';
                $package->amount = $request->amount;
                $package->image = $get_product_image->image;
                $package->save();
                
                if(intval($package->id)){

                    $product_ids = $request->product_ids;
                    $products_quantity = $request->products_quantity;
                    foreach($product_ids as $k => $id){
                        $packageInfo = new PackageInfo();
                        $packageInfo->package_id = $package->id;
                        $packageInfo->product_id = $id;
                        $packageInfo->products_quantity = $products_quantity[$k];
                        $packageInfo->save(); 
                         // Update product stock        
                        $product = Product::where('id',$id)->first();  
                        if($product){
                            $new_quantity = $product->quantity - $products_quantity[$k];
                           
                            if($new_quantity > 0){
                                $product->quantity = $new_quantity;
                                $product->status = 'instock';
                                $product->save();
                            }else{
                                $product->quantity = $new_quantity;
                                $product->status = 'outofstock';
                                $product->save();
                            }
                        }              
                    }
                    
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'message' => 'Package created successfully',
                        'data' => new PackageResource($package)
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
     * Get list of all packages
     */
    public function list(Request $request){
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {
                $agent = Auth::user();
                if($request->status){
                    $packages = PackageResource::collection(Package::latest()
                            ->where('agent_id',$agent->id)
                            ->where('status',$request->status)
                            ->get());
                }else{
                    $packages = PackageResource::collection(Package::latest()
                            ->where('agent_id',$agent->id)
                            ->get());
                }
                
                if( sizeof($packages) != 0){
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'data' => $packages
                    ], 200);   
                }else{
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => 'Packages not found'
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
     * Update Package By Id
     */

    public function update(Request $request)
    {
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {
                $agent = Auth::user();
                $validator = Validator::make($request->all(), [
                    'package_id' => 'required',
                    'product_id' => 'required',
                    'products_quantity' => 'required',
                ]);
                if($validator->fails()){ 
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => $validator->errors(),
                    ], 404);
                }
            
                $package = PackageInfo::where('package_id',$request->package_id)->where('product_id',$request->product_id)->first();
                
                if($package){
                    $package->products_quantity = $request->products_quantity;
                    $package->save();
                    if(intval($package->id)){
                        $product = Product::where('id',$request->product_id)->first();  
                        if($product){
                            $new_quantity = $product->quantity - $request->products_quantity;
                           
                            if($new_quantity > 0){
                                $product->quantity = $new_quantity;
                                $product->status = 'instock';
                                $product->save();
                            }else{
                                $product->quantity = $new_quantity;
                                $product->status = 'outofstock';
                                $product->save();
                            }
                        }              
                        $packages = PackageResource::collection(Package::where('id',$request->package_id)
                            ->get());
                        return response()->json([
                            'code' => 200,
                            'status' => true,
                            'message' => 'Package updated successfully',
                            'data' => $packages
                        ], 200);
                    }
                }
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'message' => 'Package not found'
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

    /**
     * Delete Package
     */

    public function delete(Request $request)
    {
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {
                $package = Package::find($request->id);
                $package->delete();
                if($package){
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'message' => 'Package deleted!',
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
