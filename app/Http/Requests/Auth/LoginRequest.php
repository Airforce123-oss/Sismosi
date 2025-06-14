<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credential' => ['required', 'string'],
            'password' => ['required', 'string'],
            'role' => ['required', 'in:student,teacher,admin,parent'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if ($this->role === 'student') {
            $student = \App\Models\Student::with('user')->whereHas('noInduk', function ($query) {
                $query->where('no_induk', $this->credential);
            })->first();

            if (!$student || !$student->user) {
                throw ValidationException::withMessages([
                    'credential' => trans('auth.failed'),
                ]);
            }

            $user = $student->user;

            if (!Hash::check($this->password, $user->password)) {
                throw ValidationException::withMessages([
                    'credential' => '❌ Password salah.',
                ]);
            }

            Auth::login($user); // ✅ login sukses di sini
        } else {
            if (! Auth::attempt([
                'email' => $this->credential,
                'password' => $this->password,
            ], $this->boolean('remember'))) {
                throw ValidationException::withMessages([
                    'credential' => trans('auth.failed'),
                ]);
            }
        }

        // 🔥 HAPUS bagian ini karena sudah tidak perlu:
        // if (! Auth::attempt($credentials, $this->boolean('remember'))) { ... }
        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'credential' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('credential')).'|'.$this->ip());
    }
}
