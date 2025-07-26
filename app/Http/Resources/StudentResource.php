<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ClassesResource;
use App\Http\Resources\GenderResource;
use App\Http\Resources\ReligionResource;
use App\Http\Resources\NoIndukResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'noInduk' => new NoIndukResource($this->whenLoaded('noInduk')),
            'name' => $this->name,
            'gender' => new GenderResource($this->whenLoaded('gender')),
            'religion' => new ReligionResource($this->whenLoaded('religion')),
            'class' => ClassesResource::make($this->whenLoaded('class')), // Using whenLoaded for optimization
            'created_at' => $this->created_at->toFormattedDateString(),
            'attendance' =>  AttendancesResource::make($this->whenLoaded('attendances'))
        ];
    }
}
