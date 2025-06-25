<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class StudentHelper
{
    // Fungsi untuk menangani log data yang diterima
    public static function logReceivedData($data)
    {
        Log::info('Data yang diterima:', $data);
    }

    // Fungsi untuk melakukan validasi data
    public static function validateStudentData($request)
    {
        return $request->validated(); // ✅ ini aman, karena StoreStudentRequest sudah memvalidasi sebelumnya
    }
}
