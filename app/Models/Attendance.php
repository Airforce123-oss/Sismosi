<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'student_id', 'tanggal_kehadiran', 'status_kehadiran'
    ];

    protected $guarded = ['id'];

    /*
       public function siswa()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
    */

    // Di dalam model Attendance
    public function siswa()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    
}
