<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Section> $sections
 * @property-read int|null $sections_count
 * @method static \Database\Factories\ClassesFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Classes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Classes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Classes query()
 * @method static \Illuminate\Database\Eloquent\Builder|Classes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Classes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Classes whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Classes whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Classes extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function sections()
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    protected $table = 'classes';
}