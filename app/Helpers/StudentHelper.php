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
        return $request->validate([
            'no_induk_id' => 'required|exists:no_induks,id',
            'gender_id' => 'required|exists:genders,id',
            'religion_id' => 'required|exists:religions,id',
            'name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',  
            //'parent_name' => 'required|string|max:255',
            //'address' => 'required|string|max:255',
        ]);
    }
}
