<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
 public function toArray($request)
{
    return [
        'id'       => $this->id,
        'student'  => [
            'id'   => $this->student->id ?? null,
            'name' => $this->student->name ?? null,
            'class' => [
                'id'   => $this->student->class->id ?? null,
                'name' => $this->student->class->name ?? null,
            ],
        ],
        'mapel'    => [
            'id'    => $this->mapel->id ?? null,
            'mapel' => $this->mapel->mapel ?? null,
        ],
        'teacher'  => [
            'id'   => $this->teacher->id ?? null,
            'name' => $this->teacher->name ?? null,
        ],
    ];
}

}
