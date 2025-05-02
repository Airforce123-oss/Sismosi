<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Student;
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
    
            // Cek role_name pada user
            $roleName = $user->role_name; // Mengambil role_name dari user
    
            // Debugging: Log role user
            Log::info('Authenticated User:', [
                'user_id' => $user->id,
                'role_name' => $roleName,
            ]);
    
            // Jika user adalah student
            if ($roleName === 'student') {
                $studentId = $request->input('student_id');

                // Check if student_id is provided
                if ($studentId) {
                    // Fetch student data where user_id matches the authenticated user's ID
                    // and the student_id matches the provided student_id
                    $student = Student::where('user_id', auth()->id())
                    ->where('id', $studentId)
                    ->first();
            
                    // Check if student exists
                    if ($student) {
                        // Return the student data to the frontend
                        return Inertia::render('StudentDashboard', [
                            'student_id' => $student->id,
                            'student_name' => $student->name,
                        ]);
                    } else {
                        // Return an error page if student not found
                        return Inertia::render('ErrorPage', [
                            'message' => 'Student not found.',
                        ]);
                    }
                } else {
                    // Return an error page if student_id is not provided
                    return Inertia::render('ErrorPage', [
                        'message' => 'No student selected.',
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
