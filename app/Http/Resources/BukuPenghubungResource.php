<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BukuPenghubungResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'teacher_id' => $this->teacher_id,
            'teacher_name' => $this->teacher->name, // Misalnya, untuk nama guru
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
