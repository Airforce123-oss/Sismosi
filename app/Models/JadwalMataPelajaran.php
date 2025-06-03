<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalMataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'jadwal_mata_pelajaran';

    // Jika kamu ingin mass assignment
    protected $fillable = [
        'hari',
        'jam_ke',
        'jam',
        'mapel_id',
        'kelas_id',
        'tahun',
        'guru_id',
    ];

    // Pastikan untuk menambahkan $timestamps jika tabel menggunakan timestamps
    public $timestamps = true;

    // Relasi ke model Mapel
    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    // Relasi ke model Kelas
    public function kelas()
    {
        return $this->belongsTo(Classes::class, 'kelas_id');
    }

    public function waliKelas()
    {
        // define sebagai relasi hasOneThrough jika wali kelas di tabel teachers
        return $this->hasOneThrough(
            Teacher::class,  
            Classes::class,  
            'id',            
            'id',         
        'kelas_id', 
            'wali_kelas_id'
        );
    }
    

        public function guru()
    {
        return $this->belongsTo(Teacher::class, 'guru_id');
    }


}
