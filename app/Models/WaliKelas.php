<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaliKelas extends Model
{
    use HasFactory;

    protected $table = 'wali_kelas'; // Sudah sesuai dengan yang ada di Teacher

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_mapel', 'wali_kelas_id', 'mapel_id')
        ->withPivot('id', 'kode_mapel', 'mapel', 'created_at', 'updated_at');
    }
}
