<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'mapel_id',
        'student_id',
        'teacher_id',
        'class_id',
        'description',
    ];

    // Relasi ke master_mapel
    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function kelas()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'id');
    }

    // OPTIONAL: relasi wali kelas via class_id -> classes -> wali_kelas
    public function waliKelas()
    {
        return $this->hasOneThrough(
            Teacher::class,
            Classes::class,
            'id',             // FK di Classes
            'id',             // FK di Teacher
            'class_id',       // FK lokal di Tugas
            'wali_kelas_id'   // FK di Classes pointing ke Teacher
        );
    }
}
