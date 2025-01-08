<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceTeacherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'teacher_id' => $this->student_id,
            'status' => $this->status,
            'date' => $this->date,
            // tambahkan field lain yang perlu ditampilkan
        ];
    }
}
