<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            if ($user->hasAnyRole(['admin', 'super-admin', 'tamu', 'BK', 'bk', 'bendahara'])) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->hasRole('siswa')) {
                return redirect()->route('siswa.dashboard');
            }
            if ($user->hasRole('pembimbing')) {
                return redirect()->route('pembimbing.dashboard');
            }

            return redirect()->intended(route('dashboard', absolute: false));
        }

        return Inertia::render('Auth/VerifyEmail', ['status' => session('status')]);
    }
}
