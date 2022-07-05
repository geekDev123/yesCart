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

                        $this->create_payment($user,$order->amount,$request);
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


    /**
     * Create Payment in Stripe
     */


    function create_payment($user, $amount,$data)
    {
       
        try{
              //$stripe = Stripe::make(env('STRIPE_SECRET'));
              $stripe = \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                
              $stripeClient = new \Stripe\StripeClient(
                env('STRIPE_SECRET')
              );
              $get_stripe_token = \Stripe\Token::create([
                  'card' => [
                  'number' => '4242424242424242',
                  'exp_month' => 5,
                  'exp_year' => 2023,
                  'cvc' => '453',
                  ],
              ]);
             
              if(isset($get_stripe_token) && $get_stripe_token['id']){

                $stripe_token   = $get_stripe_token["id"];
                $today = time();
                $user           = Auth::user();
                // Create customer on stripe
                $customer = \Stripe\Customer::create([
                    'name' => $user->name,
                    'address' => [
                        'line1' => '510 Townsend St',
                        'postal_code' => '98140',
                        'city' => 'San Francisco',
                        'state' => 'CA',
                        'country' => 'US',
                    ],
                    'email' => $user->email,
                    'source' => $stripe_token
                ]);
                
            
                if( isset($customer) && isset($customer['id'])){
                    $customer_id = $customer['id'];
                    
                    
                         
                  // If user has select recurring mode
                  if($data['subscription'] == 'yes'){
                        /* Create Product */
                        $product = $stripeClient->products->create([
                            'name' => 'Subscription for '.$data['name'],
                            ]);
                            
                            
                            /* Create plan */
                            $price = $stripeClient->prices->create([
                            'unit_amount' => $amount,
                            'currency' => 'usd',
                            'recurring' => ['interval' => 'month'],
                            'product' => $product['id'],
                            ]);
                    /* Create subscription */
                        $subscription = $stripeClient->subscriptions->create([
                            'customer' => $customer['id'],
                            'items' => [
                                [
                                    'price' => $price['id']
                                ],
                            ],
                            'metadata' => [
                                'start_date' => time(),
                            ],
                            'payment_behavior' => 'allow_incomplete',
                            'off_session' => true,
                    
                        ]);


                        if( $subscription){
                            return response()->json([
                                'code' => 200,
                                'status' => true,
                                'message' => 'Payment successfully done.'
                            ], 200);
                        }
                        return response()->json([
                            'code' => 404,
                            'status' => false,
                            'message' => 'Some error has been ocurred.'
                        ], 404);
                          
                      }else{
                        $get_stripe_token = \Stripe\Token::create([
                            'card' => [
                            'number' => '4242424242424242',
                            'exp_month' => 5,
                            'exp_year' => 2023,
                            'cvc' => '453',
                            ],
                        ]);
                        $charge = $stripeClient->charges->create([
                            'amount' => $amount,
                            'currency' => 'usd',
                            'source' => $get_stripe_token["id"],
                            'description' => 'YesCart test Payment',
                          ]);
                       
                          return response()->json([
                              'code' => 404,
                              'status' => false,
                              'message' => 'Customer has not been created.'
                          ], 404);
                      }
                      
                  }else{
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => 'Customer has not been created.'
                    ], 404);
                  }
                  
                  

              }else{
                  return response()->json([
                      'code' => 404,
                      'status' => false,
                      'message' => 'Stripe token has been not generated.'
                  ], 404);
              }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            return response()->json([
                'code' => 404,
                'status' => false,
                'message' => $message
            ], 404);
        }
    }
}
