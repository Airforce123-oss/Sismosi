<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoInduk extends Model
{
    use HasFactory;

    protected $fillable = ['no_induk'];

    public function students()
    {
        return $this->hasMany(Student::class, 'no_induk_id', 'id');
        //eturn $this->hasMany(Student::class);
    }

}
