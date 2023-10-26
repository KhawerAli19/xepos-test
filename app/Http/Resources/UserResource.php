<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'name' => $this->name,
            "phone_no" => $this->phone_no,
            "city" => $this->city,
            "state" => $this->state,
            "country" => $this->country,
            "country_code" => $this->country_code,
            "status" => $this->status,
            'image' => $this->getFirstMedia()?->original_url,
            'thumb' => $this->getFirstMedia()?->thumbnail_url,
            "selected_avatar" => $this->selected_avatar,
            "created_at" => $this->created_at?->format("Y-m-d h:m:s A"),
            "updated_at" => $this->updated_at?->format("Y-m-d h:m:s A"),
            "image_url" => $this->image_url,
            "coins" => $this->coins,
            'profile_photo_url' => $this->profile_photo_url,
            "coin" => $this->coin
        ];
    }
}
