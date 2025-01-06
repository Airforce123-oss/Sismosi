<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $mapel_id
 * @property int $teacher_id
 * @property string $description
 * @property int|null $student_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Mapel $mapel
 * @property-read \App\Models\Student|null $student
 * @property-read \App\Models\Teacher $teacher
 * @method static \Illuminate\Database\Eloquent\Builder|Tugas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tugas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tugas query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tugas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tugas whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tugas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tugas whereMapelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tugas whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tugas whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tugas whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Tugas extends Model
{
    use HasFactory;

    // Nama tabel dalam database
    protected $table = 'tugas';

    // Primary key tabel
    protected $primaryKey = 'id';

    // Primary key auto increment
    public $incrementing = true;

    // Tipe data primary key
    protected $keyType = 'int';

    // Kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'mapel_id',    // Foreign key ke tabel master_mapel
        'student_id',
        'teacher_id',  // Foreign key ke tabel teachers
        'description', // Deskripsi tugas
    ];

    // Relasi ke model Mapel
    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }


    // Relasi ke model Teacher
    public function teacher()
{
    return $this->belongsTo(Teacher::class, 'teacher_id');
}

}
