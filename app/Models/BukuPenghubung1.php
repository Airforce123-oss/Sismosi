<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Classes;

class BukuPenghubung1 extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'parentName',
        'studentName',
        'gender',
        'class_id', // ganti dari 'class' ke 'class_id'
        'issue',
        'action',
        'note',
    ];

    // Tambahkan relasi ke tabel classes
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
