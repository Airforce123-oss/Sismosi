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

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            //$token = $user->createToken('YourAppName')->plainTextToken; // Membuat token untuk user

            //return response()->json(['token' => $token]);
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

        return response()->json(['message' => 'Logged out successfully']);
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
