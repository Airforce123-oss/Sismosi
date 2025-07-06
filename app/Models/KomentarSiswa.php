<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KomentarSiswa extends Model
{
    use HasFactory;
    protected $fillable = [
    'student_id',
    'komentar',
    'parent_id',
];
}
