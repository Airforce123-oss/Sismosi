<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * App\Models\Student
 *
 * @property int $id
 * @property string $name
 * @property int|null $gender_id
 * @property int|null $class_id
 * @property int|null $religion_id
 * @property int|null $no_induk_id
 * @property int|null $user_id
* @property int|null $parent_id
 *
 * @property \App\Models\NoInduk $noInduk
 * @property \App\Models\Classes $class
 * @property \App\Models\Gender $gender
 * @property \App\Models\Religion $religion
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Attendance[] $attendances
 * @property \App\Models\Mapel $mapel
 * @property \App\Models\User $user
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\KomentarSiswa[] $komentarSiswas
 * @property-read \App\Models\Classes $kelas
 */
class Student extends Model
{

    protected $table = 'students';

    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'gender_id',
        'class_id',
        'religion_id',
        'no_induk_id',
        'user_id',
        'parent_id'
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
        return $this->belongsToMany(Mapel::class, 'enrollments', 'student_id', 'mapel_id');
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
