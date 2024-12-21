<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
