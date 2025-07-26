<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailStudentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'student_id' => optional($this->student?->noInduk)->no_induk,
            'class_id' => $this->class_id,
            'gender' => $this->gender,
            'parent_name' => $this->parent_name,
            'address' => $this->address,
            'student' => new StudentResource($this->whenLoaded('student')),
        ];
    }
}
