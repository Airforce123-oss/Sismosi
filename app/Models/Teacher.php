<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'class_id',
    ];

    // Jika Anda menggunakan relasi
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    protected $table = 'wali_kelas';
}
