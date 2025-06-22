<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //return true;
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
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'jabatan_id' => 'nullable|integer|exists:master_jabatan,id',
            'mapel_id' => 'nullable|integer|exists:master_mapel,id',
            // 'class_id' => 'nullable|exists:classes,id', // kalau tidak dipakai, biarkan tetap dihapus
        ];
    }

}
