<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceTeacher extends Model
{
    use HasFactory;


    protected $table = 'attendance_teachers';

    protected $fillable = [
        'teacher_id', 'tanggal_kehadiran', 'status_kehadiran'
    ];

    protected $guarded = ['id'];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
