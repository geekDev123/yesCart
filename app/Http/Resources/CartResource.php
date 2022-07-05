<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'product_id' => $this->product_id,
            'product_name' => getProductById($this->product_id)->name,
            'butcher_id' => $this->butcher_id,
            'butcher_name' => getUserMetaInfoById($this->butcher_id,'name') ? getUserMetaInfoById($this->butcher_id,'name')->meta_value:"",
            'customer_id' => $this->customer_id,
            'customer_name' => getUserMetaInfoById($this->butcher_id,'name') ? getUserMetaInfoById($this->customer_id,'name')->meta_value : "",
            'quantity' => $this->quantity,
            'price' => $this->price,
            'product_image' => env('APP_URL').getProductById($this->product_id)->image
        ];
    }
}
