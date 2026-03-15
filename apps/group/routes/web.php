<?php

use App\Mail\NewMotivationEnquiry;
use App\Models\MotivationEnquiry;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
]))->name('enquire');

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
    ]);

    $enquiry = MotivationEnquiry::create($validated);

    Mail::send(new NewMotivationEnquiry($enquiry));

    return redirect()->route('enquire')->with('success', 'Thank you! Your enquiry has been submitted. Our team will be in touch shortly.');
})->name('enquire.submit');

// ── Admin ──────────────────────────────────────────────────────

Route::get('/admin/login', fn () => view('admin.login'))->name('admin.login');

Route::post('/admin/login', function (Request $request) {
    $request->validate(['password' => 'required']);

    if ($request->password !== config('app.admin_password')) {
        return back()->withErrors(['password' => 'Incorrect password.']);
    }

    $request->session()->put('admin_authenticated', true);

    return redirect()->route('admin.dashboard');
})->name('admin.login.submit');

Route::post('/admin/logout', function (Request $request) {
    $request->session()->forget('admin_authenticated');

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
                 'mail_from_name', 'mail_from_address', 'notification_email'];
        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = Setting::get($key);
        }

        $mailStatus = ($settings['mailgun_domain'] && $settings['mailgun_secret'] && $settings['mail_mailer'] === 'mailgun')
            ? 'configured' : 'pending';

        return view('admin.settings', compact('settings', 'mailStatus'));
    })->name('settings');

    Route::post('/settings', function (Request $request) {
        $fields = ['mail_mailer', 'mailgun_domain', 'mailgun_endpoint',
                   'mail_from_name', 'mail_from_address', 'notification_email'];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                Setting::set($field, $request->input($field), 'mail');
            }
        }

        if ($request->filled('mailgun_secret')) {
            Setting::set('mailgun_secret', $request->input('mailgun_secret'), 'mail');
        }

        return back()->with('success', 'Mail settings saved successfully.');
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
});
