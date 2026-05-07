<?php

use App\Mail\NewArmsEnquiry;
use App\Mail\NewMotivationEnquiry;
use App\Models\ArmsEnquiry;
use App\Models\ArmsListing;
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
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    $host = request()->getHost();

    if (str_starts_with($host, 'motivations.')) {
        return view('motivations');
    }

    if (str_starts_with($host, 'storage.')) {
        return view('storage-landing');
    }

    if (str_starts_with($host, 'arms.')) {
        return view('arms-landing', [
            'listings' => ArmsListing::prioritised()->get(),
        ]);
    }

    return view('welcome');
});

Route::get('/motivations', function () {
    if (str_starts_with(request()->getHost(), 'motivations.')) {
        return redirect('/', 301);
    }
    return view('motivations');
});

Route::get('/storage', function () {
    if (str_starts_with(request()->getHost(), 'storage.')) {
        return redirect('/', 301);
    }
    return view('storage-landing');
});

Route::get('/resources/{slug?}', function (?string $slug = null) {
    $host = request()->getHost();
    $isStorage = str_starts_with($host, 'storage.');

    $map = $isStorage ? [
        '' => 'resources.storage.index',
        'about' => 'resources.storage.about',
        'safe-custody' => 'resources.storage.safe-custody',
        'fca-requirements' => 'resources.storage.fca-requirements',
        'faq' => 'resources.storage.faq',
    ] : [
        '' => 'resources.motivations.index',
        'about' => 'resources.motivations.about',
        'firearm-licence-process' => 'resources.motivations.firearm-licence-process',
        'firearms-control-act' => 'resources.motivations.firearms-control-act',
        'services' => 'resources.motivations.services',
        'faq' => 'resources.motivations.faq',
        'documents-required' => 'resources.motivations.documents-required',
    ];

    $view = $map[$slug ?? ''] ?? null;

    abort_unless($view, 404);

    return view($view);
})->where('slug', '[a-z0-9-]+');

Route::get('/sitemap.xml', function () {
    $host = request()->getHost();
    $today = date('Y-m-d');

    if (str_starts_with($host, 'motivations.')) {
        $base = 'https://motivations.ranyati.co.za';
        $pages = [
            ''                                    => ['priority' => '1.0', 'changefreq' => 'monthly',  'lastmod' => $today],
            '/enquire'                            => ['priority' => '0.9', 'changefreq' => 'monthly',  'lastmod' => $today],
            '/faq'                                => ['priority' => '0.85', 'changefreq' => 'weekly',  'lastmod' => $today],
            '/firearm-motivation-letter'          => ['priority' => '0.9',  'changefreq' => 'monthly', 'lastmod' => $today],
            '/firearm-licence-motivation-self-defence' => ['priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/firearm-licence-motivation-sport-shooting' => ['priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/firearm-licence-motivation-hunting' => ['priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/firearm-licence-renewal-south-africa' => ['priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/firearm-licence-appeal-south-africa' => ['priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/resources'                          => ['priority' => '0.9', 'changefreq' => 'weekly',   'lastmod' => $today],
            '/resources/about'                    => ['priority' => '0.8', 'changefreq' => 'monthly',  'lastmod' => $today],
            '/resources/firearm-licence-process'  => ['priority' => '0.8', 'changefreq' => 'monthly',  'lastmod' => $today],
            '/resources/firearms-control-act'     => ['priority' => '0.8', 'changefreq' => 'monthly',  'lastmod' => $today],
            '/resources/services'                 => ['priority' => '0.8', 'changefreq' => 'monthly',  'lastmod' => $today],
            '/resources/faq'                      => ['priority' => '0.8', 'changefreq' => 'weekly',   'lastmod' => $today],
            '/resources/documents-required'       => ['priority' => '0.7', 'changefreq' => 'monthly',  'lastmod' => $today],
        ];
    } elseif (str_starts_with($host, 'storage.')) {
        $base = 'https://storage.ranyati.co.za';
        $pages = [
            ''                           => ['priority' => '1.0', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/firearm-storage-pretoria'  => ['priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/long-term-firearm-storage-south-africa' => ['priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/temporary-firearm-storage' => ['priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/secure-firearm-storage-faq' => ['priority' => '0.85', 'changefreq' => 'weekly', 'lastmod' => $today],
            '/resources'                 => ['priority' => '0.9', 'changefreq' => 'weekly',  'lastmod' => $today],
            '/resources/about'           => ['priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/resources/safe-custody'    => ['priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/resources/fca-requirements'=> ['priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/resources/faq'             => ['priority' => '0.8', 'changefreq' => 'weekly',  'lastmod' => $today],
        ];
    } elseif (str_starts_with($host, 'arms.')) {
        $base = 'https://arms.ranyati.co.za';
        $pages = [
            '' => ['priority' => '1.0', 'changefreq' => 'daily', 'lastmod' => $today],
        ];
    } else {
        $base = 'https://ranyati.co.za';
        $pages = [
            '' => ['priority' => '1.0', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/about' => ['priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/services' => ['priority' => '0.9', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/contact' => ['priority' => '0.85', 'changefreq' => 'monthly', 'lastmod' => $today],
            '/faq' => ['priority' => '0.85', 'changefreq' => 'weekly', 'lastmod' => $today],
            '/guides' => ['priority' => '0.85', 'changefreq' => 'weekly', 'lastmod' => $today],
        ];
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($pages as $path => $meta) {
        $xml .= '<url>';
        $xml .= '<loc>' . $base . $path . '</loc>';
        $xml .= '<lastmod>' . $meta['lastmod'] . '</lastmod>';
        $xml .= '<changefreq>' . $meta['changefreq'] . '</changefreq>';
        $xml .= '<priority>' . $meta['priority'] . '</priority>';
        $xml .= '</url>';
    }
    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
});

Route::get('/robots.txt', function () {
    $host = request()->getHost();

    if (str_starts_with($host, 'motivations.')) {
        $sitemap = 'https://motivations.ranyati.co.za/sitemap.xml';
    } elseif (str_starts_with($host, 'storage.')) {
        $sitemap = 'https://storage.ranyati.co.za/sitemap.xml';
    } elseif (str_starts_with($host, 'arms.')) {
        $sitemap = 'https://arms.ranyati.co.za/sitemap.xml';
    } else {
        $sitemap = 'https://ranyati.co.za/sitemap.xml';
    }

    $txt = "User-agent: *\nAllow: /\nDisallow: /admin\n\nSitemap: {$sitemap}\n";

    return response($txt, 200, ['Content-Type' => 'text/plain']);
});

// ── SEO support pages (host-aware; same path may resolve differently per hostname) ──

$apexOnly = function (): void {
    $h = request()->getHost();
    abort_if(str_starts_with($h, 'motivations.') || str_starts_with($h, 'storage.') || str_starts_with($h, 'arms.'), 404);
};

$motivationsOnly = function (): void {
    abort_unless(str_starts_with(request()->getHost(), 'motivations.'), 404);
};

$storageOnly = function (): void {
    abort_unless(str_starts_with(request()->getHost(), 'storage.'), 404);
};

$armsOnly = function (): void {
    abort_unless(str_starts_with(request()->getHost(), 'arms.'), 404);
};

// ── Arms public routes ──────────────────────────────────────────

Route::post('/arms/send-otp', function (Request $request) use ($armsOnly) {
    $armsOnly();

    $request->validate(['email' => 'required|email|max:255']);

    $email = strtolower(trim($request->email));
    $rateLimitKey = 'arms-otp-send:' . $email;

    if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
        return response()->json([
            'error' => 'Too many attempts. Please wait ' . RateLimiter::availableIn($rateLimitKey) . ' seconds.',
        ], 429);
    }

    RateLimiter::hit($rateLimitKey, 120);

    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    Cache::put("arms-otp:{$email}", $code, now()->addMinutes(10));

    try {
        Mail::send('emails.arms-otp-verification', ['code' => $code], function ($message) use ($email) {
            $message->to($email)->subject('Your Verification Code — Ranyati Arms');
        });
    } catch (\Throwable $e) {
        return response()->json(['error' => 'Failed to send verification email. Please try again.'], 500);
    }

    return response()->json(['message' => 'Verification code sent.']);
})->name('arms.send-otp');

Route::post('/listing/{listing}/enquire', function (Request $request, ArmsListing $listing) use ($armsOnly) {
    $armsOnly();

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:50',
        'message' => 'nullable|string|max:2000',
        'otp' => 'required|string|size:6',
    ]);

    $email = strtolower(trim($validated['email']));
    $cachedCode = Cache::get("arms-otp:{$email}");

    if (! $cachedCode || $cachedCode !== $validated['otp']) {
        return response()->json(['error' => 'Invalid or expired verification code.'], 422);
    }

    Cache::forget("arms-otp:{$email}");

    $enquiry = ArmsEnquiry::create([
        'arms_listing_id' => $listing->id,
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'] ?? null,
        'message' => $validated['message'] ?? null,
    ]);

    try {
        Mail::send(new NewArmsEnquiry($enquiry));
    } catch (\Throwable $e) {
    }

    return response()->json(['success' => true, 'message' => 'Thank you! Your enquiry has been sent.']);
})->name('arms.enquire');

Route::get('/about', function () use ($apexOnly) {
    $apexOnly();

    return view('seo.about');
});

Route::get('/services', function () use ($apexOnly) {
    $apexOnly();

    return view('seo.services');
});

Route::get('/contact', function () use ($apexOnly) {
    $apexOnly();

    return view('seo.contact');
});

Route::get('/guides', function () use ($apexOnly) {
    $apexOnly();

    return view('seo.guides');
});

Route::get('/faq', function () {
    $host = request()->getHost();
    if (str_starts_with($host, 'motivations.')) {
        return view('seo.motivations.faq');
    }
    abort_if(str_starts_with($host, 'storage.'), 404);

    return view('seo.faq');
});

Route::get('/firearm-motivation-letter', function () use ($motivationsOnly) {
    $motivationsOnly();

    return view('seo.motivations.firearm-motivation-letter');
});
Route::get('/firearm-licence-motivation-self-defence', function () use ($motivationsOnly) {
    $motivationsOnly();

    return view('seo.motivations.self-defence');
});
Route::get('/firearm-licence-motivation-sport-shooting', function () use ($motivationsOnly) {
    $motivationsOnly();

    return view('seo.motivations.sport-shooting');
});
Route::get('/firearm-licence-motivation-hunting', function () use ($motivationsOnly) {
    $motivationsOnly();

    return view('seo.motivations.hunting');
});
Route::get('/firearm-licence-renewal-south-africa', function () use ($motivationsOnly) {
    $motivationsOnly();

    return view('seo.motivations.renewal');
});
Route::get('/firearm-licence-appeal-south-africa', function () use ($motivationsOnly) {
    $motivationsOnly();

    return view('seo.motivations.appeal');
});

Route::get('/firearm-storage-pretoria', function () use ($storageOnly) {
    $storageOnly();

    return view('seo.storage.pretoria');
});
Route::get('/long-term-firearm-storage-south-africa', function () use ($storageOnly) {
    $storageOnly();

    return view('seo.storage.long-term');
});
Route::get('/temporary-firearm-storage', function () use ($storageOnly) {
    $storageOnly();

    return view('seo.storage.temporary');
});
Route::get('/secure-firearm-storage-faq', function () use ($storageOnly) {
    $storageOnly();

    return view('seo.storage.faq');
});

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
    $nrapaBypass = $request->input('source') === 'nrapa_endorsement'
        && $request->input('otp') === 'nrapa-verified';

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:50',
        'endorsement_type' => 'nullable|string|max:255',
        'purpose' => 'nullable|string|max:255',
        'membership_number' => 'nullable|string|max:100',
        'message' => 'nullable|string|max:2000',
        'source' => 'nullable|string|max:100',
        'otp' => $nrapaBypass ? 'required|string' : 'required|string|size:6',
        'cf-turnstile-response' => 'nullable|string',
    ]);

    if (! $nrapaBypass) {
        $email = strtolower(trim($validated['email']));
        $cachedCode = Cache::get("otp:{$email}");

        if (! $cachedCode || $cachedCode !== $validated['otp']) {
            return back()->withInput()->withErrors(['otp' => 'Invalid or expired verification code.']);
        }
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

    if (! $nrapaBypass) {
        Cache::forget("otp:{$email}");
    }

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
                 'arms_enquiry_email',
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
                   'arms_enquiry_email', 'turnstile_site_key'];

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

    // ── Arms Listings Management ────────────────────────────────

    Route::get('/arms', function () {
        return view('admin.arms.index', [
            'listings' => ArmsListing::latest('featured_at')->paginate(25),
        ]);
    })->name('arms');

    Route::get('/arms/create', function () {
        return view('admin.arms.form', ['listing' => null]);
    })->name('arms.create');

    Route::post('/arms', function (Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'calibre' => 'required|string|max:255',
            'accessories' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:5000',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:5120',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('arms', 'public');
            }
        }

        ArmsListing::create([
            ...\Illuminate\Support\Arr::except($validated, ['images']),
            'images' => $imagePaths,
            'status' => 'active',
            'featured_at' => now(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.arms')->with('success', 'Listing created.');
    })->name('arms.store');

    Route::get('/arms/{listing}/edit', function (ArmsListing $listing) {
        return view('admin.arms.form', compact('listing'));
    })->name('arms.edit');

    Route::put('/arms/{listing}', function (Request $request, ArmsListing $listing) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'calibre' => 'required|string|max:255',
            'accessories' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:5000',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:5120',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer',
        ]);

        $currentImages = $listing->images ?? [];

        $removeIndexes = $request->input('remove_images', []);
        if (! empty($removeIndexes)) {
            foreach ($removeIndexes as $idx) {
                if (isset($currentImages[$idx])) {
                    Storage::disk('public')->delete($currentImages[$idx]);
                    unset($currentImages[$idx]);
                }
            }
            $currentImages = array_values($currentImages);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if (count($currentImages) >= 4) break;
                $currentImages[] = $image->store('arms', 'public');
            }
        }

        $listing->update([
            ...\Illuminate\Support\Arr::except($validated, ['images', 'remove_images']),
            'images' => $currentImages,
        ]);

        return redirect()->route('admin.arms')->with('success', 'Listing updated.');
    })->name('arms.update');

    Route::delete('/arms/{listing}', function (ArmsListing $listing) {
        if ($listing->images) {
            foreach ($listing->images as $path) {
                Storage::disk('public')->delete($path);
            }
        }
        $listing->delete();

        return redirect()->route('admin.arms')->with('success', 'Listing deleted.');
    })->name('arms.delete');

    Route::post('/arms/{listing}/feature', function (ArmsListing $listing) {
        $listing->feature();

        return back()->with('success', 'Listing re-featured.');
    })->name('arms.feature');

    Route::post('/arms/{listing}/archive', function (ArmsListing $listing) {
        $listing->archive();

        return back()->with('success', 'Listing archived.');
    })->name('arms.archive');

    // ── Arms Enquiries ─────────────────────────────────────────

    Route::get('/arms/enquiries', function () {
        return view('admin.arms.enquiries', [
            'enquiries' => ArmsEnquiry::with('listing')->latest()->paginate(25),
            'unreadCount' => ArmsEnquiry::whereNull('read_at')->count(),
        ]);
    })->name('arms.enquiries');

    Route::post('/arms/enquiries/{enquiry}/read', function (ArmsEnquiry $enquiry) {
        $enquiry->update(['read_at' => now()]);

        return back()->with('success', 'Marked as read.');
    })->name('arms.enquiries.read');

    Route::post('/arms/enquiries/mark-all-read', function () {
        ArmsEnquiry::whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'All arms enquiries marked as read.');
    })->name('arms.enquiries.mark-all-read');

    // ── User Management (developer & owner only) ───────────────

    Route::get('/users', function () {
        abort_unless(auth()->user()->canManageUsers(), 403);

        return view('admin.users', [
            'users' => User::orderByRaw("CASE role WHEN 'developer' THEN 1 WHEN 'owner' THEN 2 WHEN 'admin' THEN 3 ELSE 4 END")->get(),
        ]);
    })->name('users');

    Route::post('/users', function (Request $request) {
        $actor = $request->user();
        abort_unless($actor->canManageUsers(), 403);

        $allowed = implode(',', $actor->assignableRoles());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => "required|in:{$allowed}",
        ]);

        User::create($request->only('name', 'email', 'password', 'role'));

        return back()->with('success', 'User created successfully.');
    })->name('users.store');

    Route::post('/users/{user}/update', function (Request $request, User $user) {
        $actor = $request->user();
        abort_unless($actor->canManageUsers(), 403);

        if (! $actor->canManage($user) && $actor->id !== $user->id) {
            return back()->with('error', 'You cannot edit a user with equal or higher rank.');
        }

        $allowed = implode(',', $actor->assignableRoles());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => "required|in:{$allowed}",
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
        $actor = auth()->user();
        abort_unless($actor->canManageUsers(), 403);

        if ($user->id === $actor->id) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        if (! $actor->canManage($user)) {
            return back()->with('error', 'You cannot delete a user with equal or higher rank.');
        }

        $user->delete();

        return back()->with('success', 'User deleted.');
    })->name('users.delete');
});
