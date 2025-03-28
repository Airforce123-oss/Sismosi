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
        'class_id',
    ];

    // Jika Anda menggunakan relasi
    //public function class()
    //{
      //  return $this->belongsTo(Classes::class, 'class_id');
   // }

   public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function attendance()
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
        return $this->hasMany(WaliKelas::class, 'teacher_id');
    }
    

}
