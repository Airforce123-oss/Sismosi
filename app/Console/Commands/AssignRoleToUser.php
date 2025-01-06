<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class AssignRoleToUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-role {userId} {roleName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tetapkan role ke pengguna berdasarkan ID dan nama role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ambil argumen dari command
        $userId = $this->argument('userId');
        $roleName = $this->argument('roleName');
    
        // Cari pengguna berdasarkan ID
        $user = User::find($userId);

        // Cari role berdasarkan nama
        $role = Role::where('name', $roleName)->first();
    
        if ($user && $role) {
            // Tetapkan role ke pengguna
            $user->roles()->sync([$role->id]);
            $this->info("Role '{$roleName}' berhasil ditetapkan untuk pengguna ID {$userId}.");
        } else {
            // Tampilkan error jika pengguna atau role tidak ditemukan
            if (!$user) {
                $this->error("Pengguna dengan ID {$userId} tidak ditemukan.");
            }
            if (!$role) {
                $this->error("Role '{$roleName}' tidak ditemukan.");
            }
        }
    }
}
