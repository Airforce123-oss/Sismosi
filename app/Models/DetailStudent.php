<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $student_id
 * @property int $class_id
 * @property string $gender
 * @property string $parent_name
 * @property string $address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Student|null $student
 * @method static \Illuminate\Database\Eloquent\Builder|DetailStudent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DetailStudent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DetailStudent query()
 * @method static \Illuminate\Database\Eloquent\Builder|DetailStudent whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailStudent whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailStudent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailStudent whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailStudent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailStudent whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailStudent whereParentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailStudent whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DetailStudent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DetailStudent extends Model
{
    use HasFactory;

    // Tentukan nama tabel yang sesuai
    protected $table = 'detailstudents';

    // Tentukan kolom yang dapat diisi
    protected $fillable = [
        'name', 'student_id', 'class_id', 'gender', 'parent_name', 'address'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
