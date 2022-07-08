<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Stripe;
class AuthController extends Controller
{
    public $token = true; 

    /**
     * Register API
     */

    public function register(Request $request)
    {
        // echo "<pre>";print_r($request->all());exit;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|min:6',
            'type' => 'required|string',
            'phone_number'=>'required|min:11|numeric'
        ]);
        if($validator->fails()){ 
            return response()->json([
                'code' => 404,
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 404);
        }
        if($request->type == 'butcher'){

            $validator = Validator::make($request->all(), [
                'image' => 'required|mimes:jpeg,png,jpg,gif',
                'lat' => 'required|string',
                'long' => 'required|string',
                'address' => 'required|string',
                'description' => 'required|string',
            ]);
            if($validator->fails()){ 
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'message' => $validator->errors(),
                ], 404);
            }
            if($request->hasFile('image')){
                $getImage = $request->image;
                $imageName = time().'.'.$getImage->extension();
                $imagePath = public_path(). '/images/'.$request->type;
                $imageUrl = '/images/'.$request->type.'/'.time().'.'.$getImage->extension();
                $getImage->move($imagePath, $imageName);
            }


            $stripeClient = new \Stripe\StripeClient(
                env('STRIPE_SECRET')
              );
            $merchant_account = $stripeClient->accounts->create(
                [
                  'country' => 'US',
                  'type' => 'express',
                  'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                  ],
                  'business_type' => 'individual',
                  'business_profile' => ['url' => 'https://geekinformatics.com/yesCart'],
                ]
              );
        }else{
            $imageUrl = '';
        }
        
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->type = $request->type;
        $user->phone_number = $request->phone_number;
        $user->lat = $request->lat;
        $user->long = $request->long;
        $user->address = $request->address;
        $user->description = $request->description;
        $user->image = $imageUrl;
        $user->merchant_id = isset($merchant_account['id']) ? $merchant_account['id']:"";
        $user->save();
        
        if( intval($user->id) > 0 ){
            $token = auth()->login($user);            
            return response()->json([
                'code' => 200,
                'status' => true,
                'message' => 'User registered successfully',
                'access_token' => $token,
                'data' => new UserResource($user)
            ], 200);
        } 
        return response()->json([
            'code' => 404,
            'status' => false,
            'message' => 'Some error has been ocurred.'
        ], 404);
       
    }

    /**
     * Login API
     */

    public function login(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
         
        if ($validator->fails()) {
            return response()->json([
                'code' => 404,
                'status' => false,
                'message' => $validator->errors()->first()
            ], 404);
        }

        $jwt_token = null;
        config()->set('jwt.ttl', 60*24*1); 

        if (!$jwt_token = JWTAuth::attempt($validator->validated())) {
            return response()->json([
                'code' => 404,
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
       
        $user = Auth::User();
        
        if($user['type'] == "customer"){
            $customerInfo = User::where('id',$user->id)->first();
            $customerInfo->lat = $request->lat;
            $customerInfo->long = $request->long;
            $customerInfo->save();
        }
    
        return $this->createNewToken($jwt_token);
    }

    /**
     * Logout API
     */

    public function logout(Request $request) {

        JWTAuth::parseToken()->invalidate($request->token);
        
        auth()->logout();
        return response()->json([
            'code' => 200,
            'status' => true,
            'message' => 'User successfully signed out'
        ], 200);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get user profile API
     */
    public function userProfile(Request $request) {
        return response()->json([
                'code' => 200,
                'status' => true,
                'data' => new UserResource(auth()->user())
            ], 200);
    }

     /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        
        return response()->json([
            'code' => 200,
            'status' => true,
            'access_token' => $token,
            'data' => new UserResource(auth()->user())
        ], 200);
    }
}

