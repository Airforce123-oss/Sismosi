<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'master_mapel'; // Tabel yang benar
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'id',
        'kode_mapel', // Kolom kode mata pelajaran
        'mapel',      // Nama mata pelajaran
    ];
    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'mapel_id', 'id');
    }
    
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function course() {
        return $this->belongsTo(Mapel::class, 'mapel_id', 'id'); // Kolom yang benar adalah id
    }
    public function waliKelas()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_mapel', 'mapel_id', 'wali_kelas_id')
                    ->withPivot('id', 'kode_mapel', 'mapel', 'created_at', 'updated_at');
    }
    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_mapel', 'mapel_id', 'wali_kelas_id');
    }

    
    
}

