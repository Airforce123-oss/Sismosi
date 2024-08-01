<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{

    protected $table = 'wali_kelas';
    protected $fillable = ['name', 'class_id'];
    
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

}
