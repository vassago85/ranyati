<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class ClientAuthController extends Controller
{
    private const OTP_CACHE_PREFIX = 'account-otp:';

    private const OTP_TTL_MINUTES = 10;

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isClient()) {
            return redirect()->route('account.applications.index');
        }

        return view('account.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        if (! $request->user()->isClient()) {
            Auth::logout();

            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Please use the account area for client login.',
            ]);
        }

        $request->session()->regenerate();

        if (! $request->user()->hasVerifiedEmail()) {
            return redirect()->route('account.verify');
        }

        return redirect()->intended(route('account.applications.index'));
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isClient()) {
            return redirect()->route('account.applications.index');
        }

        return view('account.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_CLIENT,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $this->sendOtp($user->email);

        return redirect()->route('account.verify')
            ->with('success', 'Account created. Check your inbox for a 6-digit verification code.');
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('account.applications.index');
        }

        return view('account.verify');
    }

    public function verify(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('account.applications.index');
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = strtolower(trim($request->user()->email));
        $cachedCode = Cache::get(self::OTP_CACHE_PREFIX.$email);

        if (! $cachedCode || $cachedCode !== $data['code']) {
            return back()->withErrors([
                'code' => 'Invalid or expired verification code.',
            ]);
        }

        $request->user()->markEmailAsVerified();
        Cache::forget(self::OTP_CACHE_PREFIX.$email);

        return redirect()->route('account.applications.index')
            ->with('success', 'Email verified. You can now add applications and receive daily updates.');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('account.applications.index');
        }

        $email = strtolower(trim($request->user()->email));
        $rateLimitKey = 'account-otp-send:'.$email;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return back()->withErrors([
                'code' => 'Too many attempts. Please wait '.RateLimiter::availableIn($rateLimitKey).' seconds before requesting another code.',
            ]);
        }

        RateLimiter::hit($rateLimitKey, 120);

        if (! $this->sendOtp($email)) {
            return back()->withErrors([
                'code' => 'Could not send a new verification code. Please try again shortly.',
            ]);
        }

        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('account.login');
    }

    private function sendOtp(string $email): bool
    {
        $email = strtolower(trim($email));
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(self::OTP_CACHE_PREFIX.$email, $code, now()->addMinutes(self::OTP_TTL_MINUTES));

        try {
            Mail::send('emails.account-otp-verification', ['code' => $code], function ($message) use ($email) {
                $message->to($email)->subject('Verify your Ranyati Motivations account');
            });
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        return true;
    }
}
