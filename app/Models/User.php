<?php
namespace App\Models;

use Laratrust\Models\Role;
use Laratrust\Models\LaratrustUserTrait;
use Spatie\Permission\Traits\HasRoles as TraitsHasRoles; 
use Laratrust\Models\Permission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;// Gunakan LaratrustUserTrait saja

    use TraitsHasRoles;

    //use LaratrustUserTrait;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'join_date',
        'phone_number',
        'status',
        'role_name',
        'avatar',
        'position',
        'department',
        'password',
        'class_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id', 'id');
    }
    

    public function getRedirectRoute()
    {
        // Periksa apakah pengguna memiliki role admin dan mengembalikan dashboard admin
        if ($this->hasRole('admin')) {
            return route('dashboard'); // Pastikan nama route sesuai
    
        // Periksa apakah pengguna memiliki role teacher
        } elseif ($this->hasRole('teacher')) {
            // Ambil data kelas guru terkait
            $waliClass = $this->waliKelas;
            
    
            // Log percobaan login guru untuk keperluan debugging
            Log::info('Percobaan Login Guru:', [
                'user_id' => $this->id,
                'class_id_in_users_table' => $this->class_id,
                'teacher_relation' => $waliClass ? $waliClass->toArray() : 'Tidak ada guru terkait ditemukan',
            ]);
    
            // Pastikan teacherClass ada sebelum digunakan untuk class_id
            if ($waliClass) {
                return route('dashboardTeacher', ['kelas' => $waliClass->class_id]); // Pastikan route ini sesuai
            } else {
                // Fallback jika tidak ditemukan guru terkait
                return route('dashboardTeacher'); // Pastikan route ini didefinisikan di routes/web.php
            }
    
        // Periksa apakah pengguna memiliki role student
        } elseif ($this->hasRole('student')) {
            return route('dashboardStudent'); // Pastikan route dashboard siswa sesuai
        }
    
        // Kasus default jika tidak ada role yang ditemukan (seharusnya tidak terjadi jika role sudah benar)
        return route('home'); // Route default atau halaman utama, dapat disesuaikan sesuai kebutuhan
    }
      
    /**
     * Relasi ke Role
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

        /**
     * Relasi ke Permission
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user', 'user_id', 'permission_id');
    }

    /**
     * Cek apakah pengguna memiliki role tertentu
     */
    public function hasRole($role)
    {
        return $this->roles->contains('name', $role);
    }

    /**
     * Cek apakah pengguna memiliki permission tertentu
     */
    public function hasPermission($permission)
    {
        return $this->permissions->contains('name', $permission);
    }

    public function attendanceTeachers()
{
return $this->hasMany(AttendanceTeacher::class, 'teacher_id');
}

public function waliKelas()
{
    return $this->hasOne(WaliKelas::class, 'teacher_id', 'id');
}

public function student()
{
    return $this->hasOne(\App\Models\Student::class, 'user_id');
}

public function class()
{
    return $this->belongsTo(Classes::class, 'class_id');
}



}
