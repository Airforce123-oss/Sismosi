<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $no_induk
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Student> $students
 * @property-read int|null $students_count
 * @method static \Illuminate\Database\Eloquent\Builder|NoInduk newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoInduk newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NoInduk query()
 * @method static \Illuminate\Database\Eloquent\Builder|NoInduk whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoInduk whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoInduk whereNoInduk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NoInduk whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NoInduk extends Model
{
    use HasFactory;

    protected $fillable = ['no_induk'];

    public function students()
    {
        return $this->hasMany(Student::class, 'no_induk_id', 'id');
        //eturn $this->hasMany(Student::class);
    }

}
