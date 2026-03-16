<?php

use App\Mail\NewMotivationEnquiry;
use App\Models\MotivationEnquiry;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $host = request()->getHost();

    if (str_starts_with($host, 'motivations.')) {
        return view('motivations');
    }

    if (str_starts_with($host, 'storage.')) {
        return view('storage-landing');
    }

    return view('welcome');
});

Route::get('/motivations', fn () => view('motivations'));
Route::get('/storage', fn () => view('storage-landing'));

Route::get('/enquire', fn (Request $request) => view('enquire', [
    'prefill' => $request->only(['name', 'email', 'type', 'purpose', 'membership']),
    'turnstileSiteKey' => Setting::get('turnstile_site_key', ''),
]))->name('enquire');

Route::post('/enquire/send-otp', function (Request $request) {
    $request->validate(['email' => 'required|email|max:255']);

    $email = strtolower(trim($request->email));
    $rateLimitKey = 'otp-send:' . $email;

    if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
        return response()->json([
            'error' => 'Too many attempts. Please wait ' . RateLimiter::availableIn($rateLimitKey) . ' seconds.',
        ], 429);
    }

    RateLimiter::hit($rateLimitKey, 120);

    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    Cache::put("otp:{$email}", $code, now()->addMinutes(10));

    try {
        Mail::send('emails.otp-verification', ['code' => $code], function ($message) use ($email) {
            $message->to($email)->subject('Your Verification Code — Ranyati Motivations');
        });
    } catch (\Throwable $e) {
        return response()->json(['error' => 'Failed to send verification email. Please try again.'], 500);
    }

    return response()->json(['message' => 'Verification code sent.']);
})->name('enquire.send-otp');

Route::post('/enquire', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:50',
        'endorsement_type' => 'nullable|string|max:255',
        'purpose' => 'nullable|string|max:255',
        'membership_number' => 'nullable|string|max:100',
        'message' => 'nullable|string|max:2000',
        'source' => 'nullable|string|max:100',
        'otp' => 'required|string|size:6',
        'cf-turnstile-response' => 'required|string',
    ]);

    $email = strtolower(trim($validated['email']));
    $cachedCode = Cache::get("otp:{$email}");

    if (! $cachedCode || $cachedCode !== $validated['otp']) {
        return back()->withInput()->withErrors(['otp' => 'Invalid or expired verification code.']);
    }

    $turnstileSecret = Setting::get('turnstile_secret_key', '');
    if ($turnstileSecret) {
        $turnstileResponse = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $turnstileSecret,
            'response' => $validated['cf-turnstile-response'],
            'remoteip' => $request->ip(),
        ]);

        if (! $turnstileResponse->json('success')) {
            return back()->withInput()->withErrors(['turnstile' => 'Bot verification failed. Please try again.']);
        }
    }

    Cache::forget("otp:{$email}");

    $enquiry = MotivationEnquiry::create(collect($validated)->except(['otp', 'cf-turnstile-response'])->toArray());

    Mail::send(new NewMotivationEnquiry($enquiry));

    return redirect()->route('enquire')->with('success', 'Thank you! Your enquiry has been submitted. Our team will be in touch shortly.');
})->name('enquire.submit');

// ── Admin ──────────────────────────────────────────────────────

Route::get('/admin/login', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return view('admin.login');
})->name('admin.login');

Route::post('/admin/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
        return back()->withInput($request->only('email'))->withErrors(['email' => 'Invalid credentials.']);
    }

    if (! $request->user()->isAdmin()) {
        Auth::logout();
        return back()->withErrors(['email' => 'You do not have admin access.']);
    }

    $request->session()->regenerate();

    return redirect()->route('admin.dashboard');
})->name('admin.login.submit');

Route::post('/admin/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('admin.login');
})->name('admin.logout');

Route::prefix('admin')->middleware('admin')->name('admin.')->group(function () {

    Route::get('/', function () {
        $total = MotivationEnquiry::count();
        $unread = MotivationEnquiry::whereNull('read_at')->count();

        return view('admin.dashboard', [
            'stats' => [
                'total' => $total,
                'unread' => $unread,
                'read' => $total - $unread,
                'this_month' => MotivationEnquiry::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)->count(),
            ],
            'recent' => MotivationEnquiry::latest()->take(10)->get(),
        ]);
    })->name('dashboard');

    Route::get('/enquiries', function () {
        return view('admin.enquiries', [
            'enquiries' => MotivationEnquiry::latest()->paginate(25),
            'unreadCount' => MotivationEnquiry::whereNull('read_at')->count(),
        ]);
    })->name('enquiries');

    Route::get('/enquiries/{enquiry}', function (MotivationEnquiry $enquiry) {
        if (! $enquiry->read_at) {
            $enquiry->update(['read_at' => now()]);
        }

        return view('admin.enquiry-show', compact('enquiry'));
    })->name('enquiries.show');

    Route::post('/enquiries/{enquiry}/toggle-read', function (MotivationEnquiry $enquiry) {
        $enquiry->update(['read_at' => $enquiry->read_at ? null : now()]);

        return back()->with('success', $enquiry->read_at ? 'Marked as read.' : 'Marked as unread.');
    })->name('enquiries.toggle-read');

    Route::post('/enquiries/mark-all-read', function () {
        MotivationEnquiry::whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'All enquiries marked as read.');
    })->name('enquiries.mark-all-read');

    Route::delete('/enquiries/{enquiry}', function (MotivationEnquiry $enquiry) {
        $enquiry->delete();

        return redirect()->route('admin.enquiries')->with('success', 'Enquiry deleted.');
    })->name('enquiries.delete');

    Route::get('/settings', function () {
        $keys = ['mail_mailer', 'mailgun_domain', 'mailgun_secret', 'mailgun_endpoint',
                 'mail_from_name', 'mail_from_address', 'notification_email',
                 'turnstile_site_key', 'turnstile_secret_key'];
        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = Setting::get($key);
        }

        $mailStatus = ($settings['mailgun_domain'] && $settings['mailgun_secret'] && $settings['mail_mailer'] === 'mailgun')
            ? 'configured' : 'pending';

        $turnstileStatus = ($settings['turnstile_site_key'] && $settings['turnstile_secret_key'])
            ? 'configured' : 'pending';

        return view('admin.settings', compact('settings', 'mailStatus', 'turnstileStatus'));
    })->name('settings');

    Route::post('/settings', function (Request $request) {
        $fields = ['mail_mailer', 'mailgun_domain', 'mailgun_endpoint',
                   'mail_from_name', 'mail_from_address', 'notification_email',
                   'turnstile_site_key'];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $group = str_starts_with($field, 'turnstile') ? 'security' : 'mail';
                Setting::set($field, $request->input($field), $group);
            }
        }

        if ($request->filled('mailgun_secret')) {
            Setting::set('mailgun_secret', $request->input('mailgun_secret'), 'mail');
        }

        if ($request->filled('turnstile_secret_key')) {
            Setting::set('turnstile_secret_key', $request->input('turnstile_secret_key'), 'security');
        }

        return back()->with('success', 'Settings saved successfully.');
    })->name('settings.save');

    Route::post('/settings/test', function (Request $request) {
        $request->validate(['test_email' => 'required|email']);

        try {
            Mail::raw('This is a test email from Ranyati Motivations admin panel. If you received this, your mail configuration is working correctly.', function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('Test Email — Ranyati Motivations');
            });

            return back()->with('success', 'Test email sent to ' . $request->test_email);
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    })->name('settings.test');

    // ── User Management ────────────────────────────────────────

    Route::get('/users', function () {
        return view('admin.users', [
            'users' => User::orderByRaw("CASE role WHEN 'developer' THEN 1 WHEN 'owner' THEN 2 WHEN 'admin' THEN 3 ELSE 4 END")->get(),
        ]);
    })->name('users');

    Route::post('/users', function (Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:developer,owner,admin',
        ]);

        User::create($request->only('name', 'email', 'password', 'role'));

        return back()->with('success', 'User created successfully.');
    })->name('users.store');

    Route::post('/users/{user}/update', function (Request $request, User $user) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:developer,owner,admin',
            'password' => 'nullable|string|min:8',
        ]);

        $data = $request->only('name', 'email', 'role');
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return back()->with('success', 'User updated.');
    })->name('users.update');

    Route::delete('/users/{user}', function (User $user) {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return back()->with('success', 'User deleted.');
    })->name('users.delete');
});
