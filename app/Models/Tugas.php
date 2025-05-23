<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    // Nama tabel dalam database
    protected $table = 'tugas';

    // Primary key tabel
    protected $primaryKey = 'id';

    // Primary key auto increment
    public $incrementing = true;

    // Tipe data primary key
    protected $keyType = 'int';

    // Kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'mapel_id',    // Foreign key ke tabel master_mapel
        'student_id',
        'teacher_id',  // Foreign key ke tabel teachers
        'description', // Deskripsi tugas
        'class_id',
    ];

    // Relasi ke model Mapel
    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }


    // Relasi ke model Teacher
        public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

}
