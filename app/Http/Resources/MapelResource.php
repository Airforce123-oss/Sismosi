<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MapelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_mapel' => $this->kode_mapel,
            'mapel' => $this->mapel,
            'hari' => $this->hari,   
            'jam_ke' => $this->jam_ke,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'teachers' => $this->teachers->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                ];
            }),
        ];
    }
}
