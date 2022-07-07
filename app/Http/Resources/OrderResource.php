<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'quantity' => $this->quantity,
            'status' => $this->status,
            'amount' => $this->amount,
            'vendor' => getVendorInfoByProductId($this->product_id),
            'stripe_customer_id' => $this->stripe_customer_id,
            'subscription_id' => $this->subscription_id,
            'charges_amount' => $this->charges_amount,
            'plan_price' => $this->plan_price,
            'subscription' => $this->subscription,
            'created_at' => $this->created_at->format('Y-m-d H:i:s')
        ];
    }
}
