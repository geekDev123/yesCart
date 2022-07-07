<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth; 
use App\Models\User;
use App\Models\Product;
use App\Models\Package;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ProductResource;

class IndexController extends Controller
{
    public function get_nearby_butchers(Request $request)
    {     
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {
                $user = Auth::User();
            
                $lat = $user['lat'];
                $long = $user['long'];
              
                if($request->limit && intval($request->limit) > 0){  

                    $data = User::select("*",DB::raw("6371 * acos(cos(radians($lat)) 
                                    * cos(radians(users.lat)) 
                                    * cos(radians(users.long) - radians($long)) 
                                    + sin(radians($lat)) 
                                    * sin(radians(users.lat))) AS distance"))
                                ->having('distance', '<', 1000)
                                ->where('type','butcher')
                                ->limit($request->limit)
                                ->get(); 
                     
                }else{

                    $data = User::select("*",DB::raw("6371 * acos(cos(radians($lat)) 
                                    * cos(radians(users.lat)) 
                                    * cos(radians(users.long) - radians($long)) 
                                    + sin(radians($lat)) 
                                    * sin(radians(users.lat))) AS distance"))
                                ->having('distance', '<', 1000)
                                ->where('type','butcher')
                                ->groupBy("users.id")
                                ->get(); 
                   
                } 

                


                if(!empty($data)){
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'data' => $data
                    ], 200);
                }else{
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => 'Butchers not found',
                    ], 404);
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
     * Search Products
     */
    public function search(Request $request)
    {     
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {

                $validator = Validator::make($request->all(), [
                    'keyword' => 'required',
                ]);
                if($validator->fails()){ 
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => $validator->errors(),
                    ], 404);
                }
                
                $get_users = User::select("*", DB::raw("'vendor' as type"))->where('name','like','%'.$request->keyword.'%')->get();
                $get_products = Product::select("*", DB::raw("'product' as type"))->where('name','like','%'.$request->keyword.'%')->get();
                $get_packages = Package::select("*", DB::raw("'package' as type"))->where('name','like','%'.$request->keyword.'%')->get();
              
                $users = collect($get_users);
                $products = collect($get_products);
                $merged = $users->merge($products);
                $packages = collect($get_packages);
                $data = $merged->merge($packages); 
               
                if($data){
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'data' => $data
                    ], 200);
                }else{
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => 'Results not found',
                    ], 404);
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

    public function get_butcher_by_id(Request $request)
    {
       
        try { 
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else { 
                $user = Auth::User();
            
                $lat = $user['lat'];
                $long = $user['long'];

                $butcher = User::select("*",DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
                    * cos(radians(users.lat)) 
                    * cos(radians(users.long) - radians(" . $long . ")) 
                    + sin(radians(" .$lat. ")) 
                    * sin(radians(users.lat))) AS distance"))->where('id',$request->id)
                    ->with('products')
                    ->first();
                $data = $butcher;
              
                if($data){
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'data' => $data
                    ], 200);
                }else{
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => 'Results not found',
                    ], 404);
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
}

