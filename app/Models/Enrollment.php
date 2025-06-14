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
        'class_id',
        'teacher_id',
        'enrollment_date',
        'status',
        'no_kd',
        'cognitive_1',
        'cognitive_2',
        'cognitive_pas',
        'cognitive_average',
        'skill_1',
        'skill_2',
        'skill_pas',
        'skill_average',
        'final_mark',
    ];
    

   

       // Relasi ke model Student
       public function student()
       {
           return $this->belongsTo(Student::class, 'student_id', 'id');
       }
   
       // Relasi ke model Mapel (atau Course)
// Benar
        public function mapel()
        {
            return $this->belongsTo(Mapel::class, 'mapel_id', 'id');
        }

    
    // Relasi ke Teacher melalui Student (karena Teacher terkait dengan Student melalui class_id)
        public function teacher()
        {
            return $this->belongsTo(Teacher::class, 'teacher_id');
        }
    
    
   


}
