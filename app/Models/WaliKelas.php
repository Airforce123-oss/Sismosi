<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaliKelas extends Model
{
    use HasFactory;

    protected $table = 'wali_kelas'; // Nama tabel
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function kelas()
    {
        return $this->hasMany(Classes::class, 'wali_kelas_id');
    }

    public function students()
    {
        return $this->hasManyThrough(Student::class, Classes::class, 'wali_kelas_id', 'class_id');
    }

    public function classes()
{
    return $this->belongsTo(Classes::class, 'class_id');
}

    public function updateTeacherId()
    {
        // Ambil semua wali kelas yang belum memiliki teacher_id
        $waliKelas = $this->whereNull('teacher_id')->get();

        foreach ($waliKelas as $wk) {
            // Cari user berdasarkan nip
            $user = User::where('nip', $wk->nip)->first();

            if ($user) {
                $wk->teacher_id = $user->id;  // Perbarui teacher_id
                $wk->save();  // Simpan perubahan
            }
        }
    }
}

