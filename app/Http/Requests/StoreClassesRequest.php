<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ubah menjadi true jika pengguna diizinkan untuk membuat permintaan ini
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:60',
            'kode_kelas' => 'required|string|max:20|unique:classes,kode_kelas,' . $this->route('classId'),
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'nama_kelas.string' => 'Nama kelas harus berupa string.',
            'nama_kelas.max' => 'Nama kelas maksimal 60 karakter.',
            'kode_kelas.required' => 'Kode kelas wajib diisi.',
            'kode_kelas.string' => 'Kode kelas harus berupa string.',
            'kode_kelas.max' => 'Kode kelas maksimal 40 karakter.',
        ];
    }
}
