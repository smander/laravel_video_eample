<?php

namespace App\Http\Resources\Media;

use Illuminate\Http\Resources\Json\JsonResource;


class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'video_size'    =>  $this->size,
            'views'  =>  $this->views,
            'created_by'  =>  $this->model->username,
        ];
    }
}
