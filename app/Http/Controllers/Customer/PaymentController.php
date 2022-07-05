<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe;
use Illuminate\Support\Facades\Auth; 

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        try {
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {
               
                //$stripe = Stripe::make(env('STRIPE_SECRET'));
                $stripe = \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                
                $get_stripe_token = \Stripe\Token::create([
                    'card' => [
                    'number' => '4242424242424242',
                    'exp_month' => 5,
                    'exp_year' => 2023,
                    'cvc' => '213',
                    ],
                ]);


                if(isset($get_stripe_token) && $get_stripe_token['id']){

                    $stripe_token   = $get_stripe_token["id"];
                    $data           = $request->all();                    
                    $amount         = $data["amount"];                    
                    
                    // If user has select recurring mode
                    if($data['subscription'] == 'yes'){

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
                          
                            //create subscription 
                           /*  $subscription = $stripe->subscriptions->create($customer_id, [
                                'plan' => 'price_1L2UvhSIA5YfdA9XLGCcm48U',
                                'payment_behavior' => 'allow_incomplete',
                                'off_session' => true,
                            ]); */
                            $stripe = new \Stripe\StripeClient(
                                env('STRIPE_SECRET')
                            );
                            $subscription = $stripe->subscriptions->create([
                                'customer' => $customer['id'],
                                'items' => [
                                  [
                                      'price' => 'price_1L2UvhSIA5YfdA9XLGCcm48U'
                                  ],
                                ],
                                'metadata' => [
                                  'start_date' => time(),
                                ],
                                'payment_behavior' => 'allow_incomplete',
                                'off_session' => true,
                          
                              ]);
                         
                            
                            // charge payment
                            $payment = Stripe\Charge::create ([
                                "amount" => $amount,
                                "currency" => "usd",
                                'customer' => $customer_id,
                                "description" => "This is test payment",
                            ]);
                            

                            if( $payment){
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
                            return response()->json([
                                'code' => 404,
                                'status' => false,
                                'message' => 'Customer has not been created.'
                            ], 404);
                        }
                        
                    }else{
                        // charge payment
                        $payment = Stripe\Charge::create ([
                            "amount" => $amount,
                            "currency" => "usd",
                            "source" => $stripe_token,
                            "description" => "This is test payment",
                        ]);

                        if( $payment){
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
                    }
                    
                    

                }else{
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => 'Stripe token has been not generated.'
                    ], 404);
                }
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

    public function stripePost(Request $request)
    {
        try {
            if (!$user = auth()->user()) {
                return response()->json(['code' => 404, 'status' => false, 'message' => 'Unathorised']);
            } else {
                Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

                $stripe = new \Stripe\StripeClient(
                    env('STRIPE_SECRET')
                );
                
                $stripe_token = $stripe->tokens->create([
                    'card' => [
                    'number' => '4242424242424242',
                    'exp_month' => 5,
                    'exp_year' => 2023,
                    'cvc' => '314',
                    ],
                ]);
                $stripe_token = $stripe_token["id"];
               
                $payment = Stripe\Charge::create ([
                        "amount" => 100*100,
                        "currency" => "usd",
                        "source" => $stripe_token,
                        "description" => "This is test payment",
                ]);
               
                if($payment){
                    return response()->json([
                        'code' => 200,
                        'status' => true,
                        'message' => 'Payment successfully done.'
                    ], 200);
                }
                return response()->json([
                    'code' => 404,
                    'status' => false,
                    'message' => "Some error has been occured"
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

    public function plans()
    {
        $stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET')
          );
        $plans = $stripe->plans->all(['limit' => 3]);
        if($plans){
            return response()->json([
                'code' => 200,
                'status' => true,
                'message' => $plans
            ], 200);
        }
    }

}
