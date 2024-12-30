<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        // Menggunakan 'mapel_id' sebagai foreign key yang mengarah ke 'id_mapel' di tabel 'master_mapel'
        return $this->belongsTo(Mapel::class, 'mapel_id', 'id_mapel');
    }
}
