<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $student_id
 * @property int|null $mapel_id
 * @property string $enrollment_date
 * @property string $status
 * @property string|null $description
 * @property int|null $teacher_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $mark
 * @property-read \App\Models\Mapel|null $course
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\Teacher|null $teacher
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment whereEnrollmentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment whereMapelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment whereMark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Enrollment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Enrollment extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'mapel_id',
        'enrollment_date',
        'status',
        'mark',
        'description',
        'teacher_id'
    ];

   

       // Relasi ke model Student
       public function student()
       {
           return $this->belongsTo(Student::class, 'student_id', 'id');
       }
   
       // Relasi ke model Mapel (atau Course)
       public function course() {
        return $this->belongsTo(Mapel::class, 'mapel_id', 'id');
    }
    
   // Relasi ke Teacher melalui Student (karena Teacher terkait dengan Student melalui class_id)
   public function teacher()
   {
       return $this->belongsTo(Teacher::class, 'teacher_id');
   }


}
