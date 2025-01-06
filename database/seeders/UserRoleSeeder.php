<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Membuat role jika belum ada
        Role::firstOrCreate([
            'name' => 'admin',
        ], [
            'display_name' => 'Admin',
            'guard_name' => 'web',
        ]);
        
        Role::firstOrCreate([
            'name' => 'teacher',
        ], [
            'display_name' => 'Teacher',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'student',
        ], [
            'display_name' => 'Student',
            'guard_name' => 'web',
        ]);

        // Menetapkan role untuk pengguna dengan ID 1 (admin)
        $user = User::find(1);
        if ($user) {
            // Gunakan syncWithoutDetaching untuk menghindari penambahan duplikasi role
            $user->roles()->syncWithoutDetaching([Role::where('name', 'admin')->first()->id]);
        }

        // Menetapkan role untuk pengguna dengan ID 2 (teacher)
        $teacherUser = User::find(2);
        if ($teacherUser) {
            // Gunakan syncWithoutDetaching untuk menghindari penambahan duplikasi role
            $teacherUser->roles()->syncWithoutDetaching([Role::where('name', 'teacher')->first()->id]);
        }

        // Menetapkan role untuk pengguna dengan ID 3 (student)
        $studentUser = User::find(3);
        if ($studentUser) {
            // Gunakan syncWithoutDetaching untuk menghindari penambahan duplikasi role
            $studentUser->roles()->syncWithoutDetaching([Role::where('name', 'student')->first()->id]);
        }
    }
}
