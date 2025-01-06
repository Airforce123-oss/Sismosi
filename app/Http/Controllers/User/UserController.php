<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Models\Role;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        // Logika untuk index
    }

    public function userView($id)
    {
        // Ambil data pengguna berdasarkan ID dan role terkait
        $user = User::find($id);  // Gunakan find() untuk mendapatkan pengguna dengan ID
        $roles = DB::table('role_type_users')->get(); // Role yang ada

        // Periksa jika pengguna tidak ditemukan
        if (!$user) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan.');
        }

        return view('usermanagement.user_update', compact('user', 'roles'));
    }

    public function getUser(Request $request)
    {
        // Mengambil data pengguna
        return response()->json($request->user());
    }

    public function assignRole(Request $request, $userId)
    {
        // Validasi input
        $request->validate([
            'role_name' => 'required|string',
        ]);

        // Temukan role berdasarkan nama
        $role = Role::where('name', $request->role_name)->first();

        if (!$role) {
            return response()->json(['error' => 'Role tidak ditemukan.'], 404);
        }

        // Temukan pengguna berdasarkan ID
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'Pengguna tidak ditemukan.'], 404);
        }

        // Menetapkan role ke pengguna
        $user->assignRole($role->name); // Menetapkan role berdasarkan nama role yang ditemukan
        return response()->json(['message' => 'Role berhasil ditetapkan.']);
    }

    // Menambahkan metode assign role secara manual
    public function assignRoleManually()
    {
        $user = User::find(2);  // Temukan pengguna dengan ID 2
        $role = Role::where('name', 'teacher')->first();  // Temukan role 'teacher'
    
        if ($user && $role) {
            // Sinkronisasi role dengan guard yang benar
            $user->roles()->sync([$role->id]);  // Pastikan menggunakan ID yang benar
            return response()->json(['message' => 'Role berhasil ditetapkan.']);
        } else {
            return response()->json(['error' => 'Pengguna atau role tidak ditemukan.'], 404);
        }
    }

    public function updateRoles(Request $request, $userId)
{
    // Validasi input roles
    $request->validate([
        'roles' => 'required|array', 
        'roles.*' => 'exists:roles,id',
    ]);

    // Temukan pengguna berdasarkan ID
    $user = User::findOrFail($userId);

    // Sinkronkan roles yang dipilih dengan pengguna (menetapkan roles yang baru)
    $user->roles()->sync($request->roles); // Menyesuaikan dengan ID role yang dikirim

    return redirect()->route('user.roles.index')->with('success', 'Roles berhasil diperbarui!');
}

}
