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
            'butcher_name' => getUserMetaInfoById($this->butcher_id),
            'quantity' => $this->quantity,
            'price' => $this->price,
            'product_image' => getProductById($this->product_id)->image
        ];
    }
}
