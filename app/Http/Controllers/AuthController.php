<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Metode untuk menangani login
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->only('email', 'password');
        
        // Cek kredensial dan autentikasi
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
    
            // Ambil student_id dari request
            $studentId = $request->student_id;
    
            // Debugging: Log student_id yang diterima
            Log::info('Login Request Data:', [
                'email' => $request->email,
                'student_id' => $studentId,
            ]);
    
            // Cek role_name pada user
            $roleName = $user->role_name; // Mengambil role_name dari user
    
            // Debugging: Log role user
            Log::info('Authenticated User:', [
                'user_id' => $user->id,
                'role_name' => $roleName,
            ]);
    
            // Jika user adalah student
            if ($roleName === 'student') {
                $student = \App\Models\Student::where('user_id', $user->id)->first();
            
                if ($student) {
                    // Pastikan student_id yang dikirim valid
                    if ($studentId != $student->id) {
                        return redirect()->route('login')->withErrors([
                            'student_id' => 'Student ID tidak valid untuk pengguna ini.',
                        ]);
                    }
            
                    // Simpan student_id ke session
                    session(['student_id' => $studentId]);
                    return redirect()->route('student.dashboard');
                } else {
                    // Jika user tidak terdaftar sebagai student
                    return redirect()->route('login')->withErrors([
                        'email' => 'Email tidak terdaftar sebagai siswa.',
                    ]);
                }
            }
            
    
            // Jika user adalah teacher
            if ($roleName === 'teacher') {
                // Pastikan teacher terdaftar di tabel teachers (jika perlu)
                $teacher = \App\Models\Teacher::where('user_id', $user->id)->first();
                if ($teacher) {
                    // Jika terdaftar sebagai teacher
                    return redirect()->route('teacher.dashboard');
                } else {
                    // Jika user tidak terdaftar sebagai teacher
                    return redirect()->route('login')->withErrors([
                        'email' => 'Email tidak terdaftar sebagai guru.',
                    ]);
                }
            }
    
            // Jika user adalah admin
            if ($roleName === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            
    
            // Jika role tidak dikenali
            return redirect()->route('login')->withErrors([
                'email' => 'Role tidak ditemukan untuk pengguna ini.',
            ]);
        }
    
        // Jika login gagal
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
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
