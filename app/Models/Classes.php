<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'wali_kelas_id',
    ];

    protected $table = 'classes';

    public function sections()
    {
        return $this->hasMany(Section::class, 'class_id');
    }


    public function waliKelas()
    {
        return $this->belongsTo(Teacher::class, 'wali_kelas_id'); // Menghubungkan dengan model Teacher
    }

    public function attendanceTeachers()
{
    return $this->hasMany(AttendanceTeacher::class, 'class_id');
}

}