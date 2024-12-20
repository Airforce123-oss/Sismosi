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

    // Attendance.php
    public function student()
    {
    return $this->belongsTo(Student::class, 'student_id');
    }

    // Student.php
    public function attendances()
    {
    return $this->hasMany(Attendance::class, 'student_id');
    }

    public function index(Request $request)
{
    $perPage = $request->input('per_page', 5);
    $students = Student::with(['noInduk', 'gender', 'class', 'religion'])
        ->paginate($perPage);

    return response()->json($students);
}

}
