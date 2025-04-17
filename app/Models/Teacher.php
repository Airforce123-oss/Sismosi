<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $table = 'wali_kelas';
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
    public function masterMapel()
    {
        return $this->belongsToMany(Mapel::class, 'teacher_mapel', 'wali_kelas_id', 'mapel_id')
                    ->withPivot('id', 'kode_mapel', 'mapel', 'created_at', 'updated_at');
    }
    
     // Relasi dengan WaliKelas (One to Many / Many to One)
     public function waliKelas()
     {
         return $this->hasOne(WaliKelas::class, 'teacher_id', 'id');
     }
     
     
    

}
