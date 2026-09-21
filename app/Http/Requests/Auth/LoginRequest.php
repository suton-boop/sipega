<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginInput = trim($this->input('email'));
        $password = $this->input('password');

        // Deteksi apakah input login adalah Email atau NIP
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $credentials = $isEmail 
            ? ['email' => $loginInput, 'password' => $password]
            : ['nip' => $loginInput, 'password' => $password];

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            // Jika login dengan NIP gagal, coba cari user berdasarkan NIP dan verifikasi password
            if (!$isEmail) {
                // Bersihkan karakter non-numerik dari NIP jika ada
                $cleanNip = preg_replace('/[^0-9]/', '', $loginInput);
                if ($cleanNip !== $loginInput && Auth::attempt(['nip' => $cleanNip, 'password' => $password], $this->boolean('remember'))) {
                    RateLimiter::clear($this->throttleKey());
                    return;
                }
                
                // Coba fallback dengan email siapa tahu user memasukkan email tanpa format standar
                if (Auth::attempt(['email' => $loginInput, 'password' => $password], $this->boolean('remember'))) {
                    RateLimiter::clear($this->throttleKey());
                    return;
                }
            }

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'NIP / Email atau kata sandi yang Anda masukkan tidak sesuai.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
