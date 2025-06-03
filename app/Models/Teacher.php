<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $table = 'teachers';

    protected $fillable = [
        'name',
        'nip',
        'class_id',
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function attendanceTeachers()
    {
        return $this->hasMany(AttendanceTeacher::class, 'teacher_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(AttendanceTeacher::class, 'teacher_id', 'id');
    }
    public function waliKelas()
    {
        return $this->hasOne(WaliKelas::class, 'user_id', 'user_id');
    }

public function masterMapel()
{
    return $this->belongsToMany(Mapel::class, 'teacher_mapel', 'teacher_id', 'mapel_id')
                ->withPivot('kode_mapel') 
                ->withTimestamps();      
}


}

