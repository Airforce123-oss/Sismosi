<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Student extends Model
{

    protected $table = 'students';

    protected $guarded = ['id'];
    protected $fillable = [
        'name', 'gender_id', 'class_id', 'religion_id', 'no_induk_id'
    ];
    

    public function noInduk()
    {
        return $this->belongsTo(NoInduk::class, 'no_induk_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function gender()
    {
        //return $this->belongsTo(Gender::class, 'gender_id');
        return $this->belongsTo(Gender::class, 'gender_id');
    }

    public function religion()
    {
        return $this->belongsTo(Religion::class, 'religion_id');
    }
    
    // Student.php
    public function attendances()
    {
    return $this->hasMany(Attendance::class, 'student_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

        public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $students = Student::with(['noInduk', 'gender', 'class', 'religion'])
            ->paginate($perPage);

        return response()->json($students);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

        public function komentarSiswas()
    {
        return $this->hasMany(\App\Models\KomentarSiswa::class, 'student_id');
    }

        public function getKelasAttribute()
    {
        return \App\Models\Classes::find($this->class_id);
    }


}
