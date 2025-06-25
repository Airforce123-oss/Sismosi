<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    /**
     * Tentukan apakah user berhak melakukan request ini.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Aturan validasi untuk data yang dikirim.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'no_induk_id' => ['nullable', 'integer', 'exists:no_induks,id'],
            'no_induk' => ['required_without:no_induk_id', 'nullable', 'string', 'max:50', 'unique:no_induks,no_induk'],
            'name' => ['required', 'string', 'max:255'],
            'class_id' => ['required', 'exists:classes,id'],
            'gender_id' => ['required', 'exists:genders,id'],
            'religion_id' => ['required', 'exists:religions,id'],
        ];
    }

    /**
     * Label alias untuk field agar pesan error lebih ramah.
     */
    public function attributes()
    {
        return [
            'no_induk_id' => 'ID Nomor Induk',
            'no_induk' => 'Nomor Induk',
            'name' => 'Nama Siswa',
            'class_id' => 'Kelas',
            'gender_id' => 'Jenis Kelamin',
            'religion_id' => 'Agama',
        ];
    }
}
