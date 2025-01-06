<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * 
 *
 * @property int $id
 * @property int $no_induk_id
 * @property int $class_id
 * @property int $gender_id
 * @property int $religion_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read \App\Models\Classes $class
 * @property-read \App\Models\Gender $gender
 * @property-read \App\Models\NoInduk $noInduk
 * @property-read \App\Models\Religion $religion
 * @method static \Illuminate\Database\Eloquent\Builder|Student newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Student newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Student query()
 * @method static \Illuminate\Database\Eloquent\Builder|Student whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Student whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Student whereGenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Student whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Student whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Student whereNoIndukId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Student whereReligionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Student whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

    public function index(Request $request)
{
    $perPage = $request->input('per_page', 10);
    $students = Student::with(['noInduk', 'gender', 'class', 'religion'])
        ->paginate($perPage);

    return response()->json($students);
}

}
