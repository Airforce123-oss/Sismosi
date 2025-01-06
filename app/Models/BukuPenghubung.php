<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $teacher_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Teacher $teacher
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung query()
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BukuPenghubung extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'teacher_id'];

    // Relasi dengan Teacher
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
