<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
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
            'nip' => $this->nip,
            'class' => new ClassResource($this->whenLoaded('class')),
            'master_mapel' => MapelResource::collection($this->whenLoaded('masterMapel')),
            'jabatan' => new JabatanResource($this->whenLoaded('jabatan')),
            'wali_kelas' => $this->whenLoaded('waliKelas', function () {
            $waliKelas = $this->waliKelas;

            if (!$waliKelas) return null;

            return [
                'id' => $waliKelas->id,
                'nama_guru' => $waliKelas->name, // ✅ Ambil nama guru dari tabel wali_kelas
            ];
        }),

            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
