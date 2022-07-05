<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth; 
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\DB;

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

                    $data = User::select("*",DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
                                * cos(radians(users.lat)) 
                                * cos(radians(users.long) - radians(" . $long . ")) 
                                + sin(radians(" .$lat. ")) 
                                * sin(radians(users.lat))) AS distance"))
                            ->having('distance', '<', 1000)
                            ->where('type','butcher')
                            ->limit($request->limit)
                            ->get(); 
                }else{

                    $data = User::select("*",DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
                                * cos(radians(users.lat)) 
                                * cos(radians(users.long) - radians(" . $long . ")) 
                                + sin(radians(" .$lat. ")) 
                                * sin(radians(users.lat))) AS distance"))
                            ->having('distance', '<', 1000)
                            ->where('type','butcher')
                            ->limit($request->limit)
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
}
