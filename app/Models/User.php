<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable implements LaratrustUser
{

    use HasApiTokens, HasFactory, Notifiable, HasRolesAndPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'join_date',
        'phone_number',
        'status',
        'role_name',
        'email',
        'role_name',
        'avatar',
        'position',
        'department',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $getUser = self::orderBy('user_id', 'desc')->first();

            if ($getUser) {
                $latestID = intval(substr($getUser->user_id, 3));
                $nextID = $latestID + 1;
            } else {
                $nextID = 1;
            }
            $model->user_id = '000' . sprintf("%03s", $nextID);
            while (self::where('user_id', $model->user_id)->exists()) {
                $nextID++;
                $model->user_id = '000' . sprintf("%03s", $nextID);
            }
        });
    }

    public function kelas()
{
    return $this->belongsTo(Classes::class); // Jika ada model Kelas
}

    

        public function getRedirectRoute()
    {
        if ($this->hasRole('admin')){
            return 'dashboard';
        }elseif ($this->hasRole('teacher')) {
            // Ambil kelas yang dipegang oleh teacher
            $teacherClass = $this->kelas;  // Misalkan kelas disimpan di kolom 'kelas' pada tabel 'users'
    
            // Cek apakah kelas ada, jika ada redirect ke kelas tertentu
            if ($teacherClass) {
                return route('dashboardTeacher', ['kelas' => $teacherClass]); // Mengarahkan ke route dashboardTeacher dengan parameter kelas
            } else {
                return 'dashboardTeacher';  // Default jika kelas tidak ditemukan
            }
        } elseif($this->hasRole('student')){
            return 'dashboardStudent';
        }
    } 
}
