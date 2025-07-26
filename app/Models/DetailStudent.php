<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailStudent extends Model
{
    use HasFactory;

    // Tentukan nama tabel yang sesuai
    protected $table = 'detailstudents';

    // Tentukan kolom yang dapat diisi
    protected $fillable = [
        'name', 'student_id', 'student_db_id' ,'class_id', 'gender', 'parent_name', 'address'
    ];

    protected $appends = ['student_id'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_db_id');
    }

    public function genderData()
    {
        return $this->belongsTo(Gender::class, 'gender');
    }
    public function getStudentIdAttribute()
    {
        return optional($this->student->noInduk)->no_induk;
    }

}
