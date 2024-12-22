<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'master_mapel'; // Tabel yang benar
    protected $primaryKey = 'id_mapel'; // Primary key yang benar

    protected $fillable = [
        'kode_mapel', 'mapel', // Kolom yang dapat diisi
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}

