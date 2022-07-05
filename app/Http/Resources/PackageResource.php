<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
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
            'products' => $this->package_infos,
            'delivery_type' => $this->delivery_type,
            'delivery_day' => $this->delivery_day,
            'agent_id' => getUserMetaInfoById($this->agent_id),
            'agent_name' => $this->agent_id,
            'status' => $this->status,
            'amount' => $this->amount
        ];
    }
}
