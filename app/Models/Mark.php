<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $student_id
 * @property int $mapel_id
 * @property int $mark
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Mapel $mapel
 * @property-read \App\Models\Student|null $student
 * @method static \Illuminate\Database\Eloquent\Builder|Mark newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mark newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mark query()
 * @method static \Illuminate\Database\Eloquent\Builder|Mark whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mark whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mark whereMapelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mark whereMark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mark whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mark whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Mark extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika berbeda dari bentuk jamak nama model
    protected $table = 'marks';

     // Pastikan timestamps diaktifkan (ini nilai default)
     public $timestamps = true;
    protected $fillable = [
        'student_id',
        'mapel_id',
        'mark',
    ];

    // Relasi dengan model Student
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Relasi dengan model Mapel (master_mapel)
    public function mapel()
    {
        // Menggunakan 'mapel_id' sebagai foreign key yang mengarah ke 'id' di tabel 'master_mapel'
        return $this->belongsTo(Mapel::class, 'mapel_id', 'id');
    }
}
