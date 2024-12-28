<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'mapel_id',
        'enrollment_date',
        'status',
        'mark', // Pastikan 'mark' ada di sini
    ];

       // Relasi ke model Student
       public function student()
       {
           return $this->belongsTo(Student::class, 'student_id', 'id');
       }
   
       // Relasi ke model Mapel (atau Course)
       public function course()
       {
           return $this->belongsTo(Mapel::class, 'mapel_id', 'id_mapel');
       }
}
