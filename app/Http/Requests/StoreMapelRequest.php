<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMapelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kode_mapel' => ['required', 'string', 'max:40'],  // Ganti 'varchar' dengan 'string'
            'mapel' => ['required', 'string', 'max:60'],        // Ganti 'varchar' dengan 'string'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'kode_mapel' => 'Kode Mata Pelajaran',
            'mapel' => 'Nama Mata Pelajaran',
        ];
    }
}
