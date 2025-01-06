<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $kode_mapel
 * @property string $mapel
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read Mapel|null $course
 * @property-read \App\Models\Section|null $section
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tugas> $tugas
 * @property-read int|null $tugas_count
 * @method static \Illuminate\Database\Eloquent\Builder|Mapel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mapel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mapel query()
 * @method static \Illuminate\Database\Eloquent\Builder|Mapel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mapel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mapel whereKodeMapel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mapel whereMapel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mapel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Mapel extends Model
{
    use HasFactory;

    protected $table = 'master_mapel'; // Tabel yang benar
    protected $primaryKey = 'id';
    public $incrementing = false; 
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'id',
        'kode_mapel', // Kolom kode mata pelajaran
        'mapel',      // Nama mata pelajaran
    ];
    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'mapel_id', 'id');
    }
    
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function course() {
        return $this->belongsTo(Mapel::class, 'mapel_id', 'id'); // Kolom yang benar adalah id
    }
    
}

