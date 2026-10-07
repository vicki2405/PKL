<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\SiswaUser;
use App\Models\PembimbingUser;
use App\Models\Siswa;
use App\Models\Pembimbing;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
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

        $login = $this->input('login');

        // Check if input is email
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$fieldType => $login, 'password' => $this->input('password')], $this->boolean('remember'))) {
            // Fallback: check PembimbingUser and SiswaUser, then auto-create/login system User
            $pUser = PembimbingUser::query()->where($fieldType, $login)->first();
                if ($pUser && Hash::check($this->input('password'), $pUser->password)) {
                    $existing = null;
                    if ($pUser->pembimbing && $pUser->pembimbing->user_id) {
                        $existing = User::query()->find($pUser->pembimbing->user_id);
                    }
                    if (! $existing) {
                        $existing = User::query()
                            ->where(function ($q) use ($pUser) {
                                $hasCondition = false;
                                if (! empty($pUser->email)) {
                                    $q->where('email', $pUser->email);
                                    $hasCondition = true;
                                }
                                if (! empty($pUser->username)) {
                                    if ($hasCondition) {
                                        $q->orWhere('username', $pUser->username);
                                    } else {
                                        $q->where('username', $pUser->username);
                                        $hasCondition = true;
                                    }
                                }
                                if (! $hasCondition) {
                                    $q->whereRaw('0 = 1');
                                }
                            })
                            ->first();
                    }

                    if (! $existing) {
                        $baseUsername = $pUser->username ?: Str::slug($pUser->name);
                        $sysUsername = $baseUsername;
                        $suffix = 1;
                        while (User::query()->where('username', $sysUsername)->exists()) {
                            $sysUsername = $baseUsername.'-'.$suffix++;
                        }
                        $sysEmail = $pUser->email;
                        if ($sysEmail && User::query()->where('email', $sysEmail)->exists()) {
                            $sysEmail = null;
                        }
                        $existing = User::query()->create([
                            'name' => $pUser->name,
                            'email' => $sysEmail,
                            'username' => $sysUsername,
                            'password' => $this->input('password'),
                        ]);
                        $existing->assignRole('pembimbing');
                        if ($pUser->pembimbing) {
                            $p = $pUser->pembimbing;
                            $p->update(['user_id' => $existing->id]);
                        } else {
                            $p = Pembimbing::query()->where('nama_guru', $pUser->name)->first();
                            if ($p) {
                                $p->update(['user_id' => $existing->id]);
                            }
                        }
                    } else {
                        $existing->update(['password' => $this->input('password')]);
                    }
                    Auth::login($existing, $this->boolean('remember'));
                } else {
                    $sUser = SiswaUser::query()->where($fieldType, $login)->first();
                    if ($sUser && Hash::check($this->input('password'), $sUser->password)) {
                        $existing = null;
                        if ($sUser->siswa && $sUser->siswa->user_id) {
                            $existing = User::query()->find($sUser->siswa->user_id);
                        }
                        if (! $existing) {
                            $existing = User::query()
                                ->where(function ($q) use ($sUser) {
                                    $hasCondition = false;
                                    if (! empty($sUser->email)) {
                                        $q->where('email', $sUser->email);
                                        $hasCondition = true;
                                    }
                                    if (! empty($sUser->username)) {
                                        if ($hasCondition) {
                                            $q->orWhere('username', $sUser->username);
                                        } else {
                                            $q->where('username', $sUser->username);
                                            $hasCondition = true;
                                        }
                                    }
                                    if (! $hasCondition) {
                                        $q->whereRaw('0 = 1');
                                    }
                                })
                                ->first();
                        }

                        if (! $existing) {
                            $baseUsername = $sUser->username ?: Str::slug($sUser->name);
                            $sysUsername = $baseUsername;
                            $suffix = 1;
                            while (User::query()->where('username', $sysUsername)->exists()) {
                                $sysUsername = $baseUsername.'-'.$suffix++;
                            }
                            $sysEmail = $sUser->email;
                            if ($sysEmail && User::query()->where('email', $sysEmail)->exists()) {
                                $sysEmail = null;
                            }
                            $existing = User::query()->create([
                                'name' => $sUser->name,
                                'email' => $sysEmail,
                                'username' => $sysUsername,
                                'password' => $this->input('password'),
                            ]);
                            $existing->assignRole('siswa');
                            if ($sUser->siswa) {
                                $s = $sUser->siswa;
                                $s->update(['user_id' => $existing->id, 'siswa_user_id' => $sUser->id]);
                            } else {
                                $s = Siswa::query()->where('nisn', $sUser->username)->first();
                                if ($s) {
                                    $s->update(['user_id' => $existing->id, 'siswa_user_id' => $sUser->id]);
                                }
                            }
                        } else {
                            $existing->update(['password' => $this->input('password')]);
                            if ($sUser->siswa && (! $sUser->siswa->user_id || $sUser->siswa->user_id !== $existing->id)) {
                                $sUser->siswa->update(['user_id' => $existing->id, 'siswa_user_id' => $sUser->id]);
                            }
                        }
                        Auth::login($existing, $this->boolean('remember'));
                    }
                }

            if (! Auth::check()) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'login' => trans('auth.failed'),
                ]);
            }
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
            'login' => trans('auth.throttle', [
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
        return Str::transliterate(Str::lower($this->input('login')).'|'.$this->ip());
    }
}
