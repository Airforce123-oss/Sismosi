<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id_kelas
 * @property string $nama_kelas
 * @property string $kode_kelas
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ClassesForStudent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassesForStudent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassesForStudent query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassesForStudent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassesForStudent whereIdKelas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassesForStudent whereKodeKelas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassesForStudent whereNamaKelas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassesForStudent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClassesForStudent extends Model
{
    use HasFactory;

    // Specify the table associated with the model
    protected $table = 'classes_for_student';

    // Define which attributes can be mass assigned
    protected $fillable = ['nama_kelas', 'kode_kelas'];

    protected $primaryKey = 'id_kelas';

    // Timestamps are enabled by default, so no need to specify them explicitly
}
