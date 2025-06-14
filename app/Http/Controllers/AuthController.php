<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Metode untuk menangani login
   // Metode untuk menangani login

    public function username()
    {
        return 'no_induk_id';
    }
    public function login(Request $request)
    {
        \Log::info('Login request received', $request->all());

        $request->validate([
            'credential' => 'required|string',
            'password' => 'required|string',
            'role' => 'required|in:student,teacher,admin,parent',
        ]);

        $role = $request->role;
        $credential = $request->credential;
        $password = $request->password;

        $user = null;

        if ($role === 'student') {
            // Login siswa berdasarkan username (NIS disimpan di username)
            $user = User::where('username', $credential)->first();

            if (!$user) {
                return response()->json(['message' => 'Akun pengguna siswa tidak ditemukan'], 404);
            }

            // Pastikan user punya relasi ke model Student
            $student = Student::where('user_id', $user->id)->first();

            if (!$student) {
                return response()->json(['message' => 'Data siswa tidak ditemukan'], 404);
            }
        } else {
            // Role lain menggunakan email
            if (!filter_var($credential, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['message' => 'Email tidak valid untuk role ' . $role], 422);
            }

            $user = User::where('email', $credential)->first();

            if (!$user) {
                return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
            }
        }
        // Verifikasi password
        if (!Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Password salah'], 401);
        }

        // Verifikasi role
        if (!$user->hasRole($role)) {
            return response()->json(['message' => 'Akses ditolak untuk role ' . $role], 403);
        }

        // Login dan simpan ke session
        Auth::login($user);

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => $user->only(['id', 'name', 'email']),
            'role' => $role,
        ]);
    }

    public function studentLogin(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'credential' => 'required',
                'password' => 'required',
            ]);

            // Coba cari user berdasarkan username/NIS
            $user = User::where('username', $validated['credential'])->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User dengan NIS tersebut tidak ditemukan.',
                    'details' => [
                        'input' => $validated['credential']
                    ]
                ], 404);
            }

            if (!Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'message' => 'Password salah.',
                    'details' => [
                        'user_id' => $user->id,
                        'username' => $user->username
                    ]
                ], 401);
            }

            Auth::login($user);

            return response()->json([
                'message' => 'Login siswa berhasil.',
                'user' => $user,
            ]);

        } catch (ValidationException $e) {
            // Tangkap dan kembalikan kesalahan validasi
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // Tangkap error umum lainnya
            Log::error('Login error:', ['error' => $e->getMessage(), 'trace' => $e->getTrace()]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat login.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    // (Optional) Metode untuk menangani logout
    public function logout(Request $request)
    {
        // Revoke token yang digunakan oleh user
        $request->user()->tokens->each(function ($token) {
            $token->delete();
        });
    
        return response()->json(['message' => 'Logged out successfully'], 200); // Pastikan ada status 200
    }
    

    public function refreshToken(Request $request)
    {
        Log::info('Request untuk refresh token diterima');
        
        // Pastikan token yang lama valid
        $user = $request->user();
        
        if (!$user) {
            Log::error('Tidak ada user terkait dengan token ini');
            return response()->json(['message' => 'User tidak terautentikasi'], 401);
        }
    
        // Revoke token lama
        $user->tokens->each(function ($token) {
            $token->delete();
        });
        
        // Membuat token baru
        try {
            $newToken = $user->createToken('YourAppName')->plainTextToken;
        } catch (\Exception $e) {
            Log::error('Error saat membuat token baru: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mendapatkan token baru'], 500);
        }
    
        // Kirimkan token baru
        return response()->json([
            'message' => 'Token refreshed successfully!',
            'data' => $newToken,
        ]);
    }
}
