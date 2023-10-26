<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LeaderBoardResource extends JsonResource
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

            "name" => $this->name ?? auth()->user()->name,
            "rank" => $this->rank ?? 0,
            "level" => $this->level ?? 0,
            "scores" => $this->score ?? 0,
            "country_code" => $this->country_code ?? null
        ];
    }
}
