<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'master_mapel';
    protected $primaryKey = 'id';
public $incrementing = true;
protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'kode_mapel',
        'mapel',
        'hari',
        'jam_ke',
        'guru_id',
        'kelas_id',
    ];

    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'mapel_id', 'id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'id');
    }

    public function kelas()
    {
        return $this->belongsTo(Classes::class, 'kelas_id');
    }

    public function guru()
    {
        return $this->belongsTo(Teacher::class, 'guru_id', 'id');
    }
    

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_mapel', 'mapel_id', 'teacher_id')
                    ->withPivot('kode_mapel') // tambahkan kolom lain yang memang ada
                    ->withTimestamps();
    }

}

