<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SplashResource extends JsonResource
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

            "name" => $this->name,
            "image" => $this->image_url,
            "email" => $this->email,
            "phone_no" => $this->phone_no,
            "rank" => $this->leaderBoard != null ?  $this->leaderBoard->rank : null,
            "level" => $this->leaderBoard != null ? $this->leaderBoard->level : null
        ];
    }
}
