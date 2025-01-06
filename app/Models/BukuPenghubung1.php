<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $date
 * @property string $parentName
 * @property string $studentName
 * @property string $gender
 * @property string $class
 * @property string $issue
 * @property string $action
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung1 newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung1 newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung1 query()
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung1 whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung1 whereClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung1 whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung1 whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung1 whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung1 whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung1 whereIssue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung1 whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung1 whereParentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung1 whereStudentName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BukuPenghubung1 whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BukuPenghubung1 extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'parentName', 'studentName', 'gender', 'class', 'issue', 'action', 'note',
    ];
}
