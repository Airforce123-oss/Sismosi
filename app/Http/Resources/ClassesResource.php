<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassesResource extends JsonResource
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
            'name' => $this->name,
            'kode_kelas' => $this->kode_kelas,
            'wali_kelas_id' => $this->wali_kelas_id,
            'tahun_ajaran' => $this->tahun_ajaran,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),

        ];
    }
}




/*
  return [
           'id_kelas' => $this->id_kelas,
            'nama_kelas' => $this->nama_kelas,
            'kode_kelas' => $this->kode_kelas,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
*/