<?php

use App\Http\Controllers\AgentDocsController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Mail\NewArmsEnquiry;
use App\Mail\NewMotivationEnquiry;
use App\Models\ArmsEnquiry;
use App\Models\ArmsListing;
use App\Models\Document;
use App\Models\MotivationEnquiry;
use App\Models\QuestionnaireResponse;
use App\Support\QuestionnaireRegistry;
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
use Illuminate\Support\Facades\Validator;

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

// ── SEO / agent endpoints (host-aware) ──────────────────────────
//
// Sitemap and robots are extracted into dedicated controllers so that
// content updates (new pages, new disallows, Content-Signal directives) live
// in PHP files instead of inline closures, and lastmod values are derived
// from the underlying Blade view's filemtime() rather than today's date.

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

// Machine-readable agent documents — Motivations host only (the controller
// 404s on apex/storage/arms hosts so this content never leaks cross-brand).
Route::get('/llms.txt',    [AgentDocsController::class, 'llms'])->name('agent.llms');
Route::get('/about.md',    [AgentDocsController::class, 'aboutMd'])->name('agent.about-md');
Route::get('/services.md', [AgentDocsController::class, 'servicesMd'])->name('agent.services-md');
Route::get('/faq.md',      [AgentDocsController::class, 'faqMd'])->name('agent.faq-md');

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

// Per-listing detail page. Each visible listing gets its own URL so it can
// be indexed individually, share with the listing's own photo, and surface
// in long-tail searches like "used Glock 19 Pretoria". Archived listings
// 404 so search engines drop them from the index. Sold listings deliberately
// stay live (HTTP 200, indexable) with a "Sold" state + SoldOut JSON-LD, so a
// firearm selling never turns its URL into a 404 as stock churns.
Route::get('/listings/{listing:slug}', function (ArmsListing $listing) use ($armsOnly) {
    $armsOnly();

    if ($listing->status === 'archived') {
        abort(404);
    }

    return view('arms-listing-detail', [
        'listing' => $listing,
        'related' => ArmsListing::prioritised()
            ->where('id', '!=', $listing->id)
            ->limit(6)
            ->get(),
    ]);
})->name('arms.listing.show');

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

// Public arms enquiry POST. Bound explicitly by `id` because the landing-page
// JS hardcodes `modalListing.id`; the ArmsListing model's default route key is
// `slug` (for SEO), so without this override Laravel would look up `where slug
// = 34` and return 404. Keep this an :id route forever.
Route::post('/listing/{listing:id}/enquire', function (Request $request, ArmsListing $listing) use ($armsOnly) {
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
Route::get('/prs-shooting-south-africa', function () use ($motivationsOnly) {
    $motivationsOnly();

    return view('seo.motivations.prs-shooting');
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

// ── Forms & Documents (Motivations host) ────────────────────────
// Public, downloadable application questionnaires and fee structures.

Route::get('/forms', function () use ($motivationsOnly) {
    $motivationsOnly();

    $documents = Document::published()->ordered()->get()
        ->groupBy(fn (Document $doc) => $doc->category ?: 'General');

    return view('forms', compact('documents'));
})->name('forms');

Route::get('/forms/{document}/download', function (Document $document) use ($motivationsOnly) {
    $motivationsOnly();

    abort_unless($document->is_published, 404);
    abort_unless(Storage::disk('public')->exists($document->path), 404);

    $document->increment('download_count');

    return Storage::disk('public')->download($document->path, $document->original_name);
})->name('forms.download');

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
        'saps_station' => 'nullable|required_if:endorsement_type,Renewal|string|max:255',
        'purpose' => 'nullable|string|max:255',
        'services' => 'nullable|array',
        'services.*' => ['string', 'max:80', \Illuminate\Validation\Rule::in(\App\Support\MotivationServices::validKeys())],
        'membership_number' => 'nullable|string|max:100',
        'message' => 'nullable|string|max:2000',
        'source' => 'nullable|string|max:100',
        'otp' => $nrapaBypass ? 'required|string' : 'required|string|size:6',
        'cf-turnstile-response' => 'nullable|string',
    ], [
        'saps_station.required_if' => 'Please enter your local SAPS station so we can advise the correct renewal process and pricing.',
    ]);

    // Defensive: drop any submitted keys not in the current registry so a
    // stale form on the client can never poison the stored enquiry.
    if (! empty($validated['services'])) {
        $valid = array_flip(\App\Support\MotivationServices::validKeys());
        $validated['services'] = array_values(array_filter(
            $validated['services'],
            fn ($k) => isset($valid[$k]),
        ));
    } else {
        $validated['services'] = [];
    }

    // Station is only relevant for renewals — clear it for every other type so
    // a leftover value from a previous form interaction can't stick around.
    if (($validated['endorsement_type'] ?? null) !== 'Renewal') {
        $validated['saps_station'] = null;
    } else {
        $validated['saps_station'] = trim((string) ($validated['saps_station'] ?? '')) ?: null;
    }

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

    // Send the customer their own branded acknowledgement. Wrapped because a
    // delivery hiccup to a single customer's address must never break the
    // form submission flow or block the staff notification above.
    try {
        Mail::send(new \App\Mail\MotivationEnquiryReceipt($enquiry));
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::warning('Motivation enquiry customer receipt failed', [
            'enquiry_id' => $enquiry->id,
            'error' => $e->getMessage(),
        ]);
    }

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

    Route::get('/applications', [\App\Http\Controllers\AdminFirearmApplicationsController::class, 'index'])
        ->name('applications');
    Route::get('/applications/log', [\App\Http\Controllers\AdminFirearmApplicationsController::class, 'log'])
        ->name('applications.log');
    Route::post('/applications/circuit/reset', [\App\Http\Controllers\AdminFirearmApplicationsController::class, 'resetCircuit'])
        ->name('applications.circuit.reset');
    Route::get('/applications/batches/{batchKey}', [\App\Http\Controllers\AdminFirearmApplicationsController::class, 'showBatch'])
        ->whereNumber('batchKey')
        ->name('applications.batch');
    Route::get('/applications/users/{user}', [\App\Http\Controllers\AdminFirearmApplicationsController::class, 'showUser'])
        ->name('applications.user');

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
        $errors = [];

        foreach (['notification_email' => 'Motivation Enquiry Recipient', 'arms_enquiry_email' => 'Arms Enquiry Recipient'] as $field => $label) {
            if (! $request->has($field)) {
                continue;
            }
            $raw = trim((string) $request->input($field));
            if ($raw === '') {
                continue;
            }
            $rawParts = preg_split('/[\s,;]+/', $raw) ?: [];
            $invalid = [];
            foreach ($rawParts as $part) {
                $part = trim($part);
                if ($part === '') continue;
                if (! filter_var($part, FILTER_VALIDATE_EMAIL)) {
                    $invalid[] = $part;
                }
            }
            if (! empty($invalid)) {
                $errors[$field] = $label.': invalid email '.implode(', ', $invalid).'.';
                continue;
            }
            $clean = implode(', ', Setting::parseEmailList($raw));
            $request->merge([$field => $clean]);
        }

        if (! empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

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
            'description_long' => 'nullable|string|max:20000',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:15360',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = \App\Support\ImageOptimizer::storeUpload($image, 'arms');
            }
        }

        $isReduced = ! empty($validated['original_price']) && $validated['original_price'] > $validated['price'];

        ArmsListing::create([
            ...\Illuminate\Support\Arr::except($validated, ['images']),
            'images' => $imagePaths,
            'status' => $isReduced ? 'reduced' : 'active',
            'featured_at' => now(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.arms')->with('success', 'Listing created.');
    })->name('arms.store');

    Route::get('/arms/{listing}/edit', function (ArmsListing $listing) {
        return view('admin.arms.form', compact('listing'));
    })->name('arms.edit');

    Route::get('/arms/{listing}/card', function (ArmsListing $listing) {
        $images = collect($listing->images ?? [])
            ->map(fn ($img) => asset('storage/'.$img))
            ->values();

        return view('admin.arms.card', compact('listing', 'images'));
    })->name('arms.card');

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
            'description_long' => 'nullable|string|max:20000',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:15360',
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

        $newCount = $request->hasFile('images') ? count($request->file('images')) : 0;
        if (count($currentImages) + $newCount > 10) {
            return back()
                ->withErrors(['images' => 'Total images cannot exceed 10. You have '.count($currentImages).' kept and tried to add '.$newCount.'.'])
                ->withInput();
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $currentImages[] = \App\Support\ImageOptimizer::storeUpload($image, 'arms');
            }
        }

        $updateData = [
            ...\Illuminate\Support\Arr::except($validated, ['images', 'remove_images']),
            'images' => $currentImages,
        ];

        // Keep status in sync with the price for live listings; never override
        // terminal sold/archived states from an edit.
        if (in_array($listing->status, ['active', 'reduced'], true)) {
            $isReduced = ! empty($validated['original_price']) && $validated['original_price'] > $validated['price'];
            $updateData['status'] = $isReduced ? 'reduced' : 'active';
        }

        $listing->update($updateData);

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

    Route::post('/arms/{listing}/sold', function (ArmsListing $listing) {
        $listing->markSold();

        return back()->with('success', 'Listing marked as sold. Its page stays live (200) with a Sold state.');
    })->name('arms.sold');

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

    // ── Documents (downloadable forms & questionnaires) ────────

    Route::get('/documents', function () {
        return view('admin.documents.index', [
            'documents' => Document::ordered()->paginate(50),
        ]);
    })->name('documents');

    Route::get('/documents/create', function () {
        return view('admin.documents.form', ['document' => null]);
    })->name('documents.create');

    Route::post('/documents', function (Request $request) {
        $validated = $request->validate([
            'category' => 'nullable|string|max:255',
            'files' => 'required|array|min:1|max:30',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx|max:25600',
        ]);

        $nextOrder = (int) (Document::max('sort_order') ?? 0);
        $count = 0;

        foreach ($request->file('files') as $file) {
            $original = $file->getClientOriginalName();

            Document::create([
                'title' => Document::titleFromFilename($original),
                'category' => $validated['category'] ?? null,
                'path' => $file->store('documents', 'public'),
                'original_name' => $original,
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'sort_order' => ++$nextOrder,
                'is_published' => true,
                'created_by' => auth()->id(),
            ]);
            $count++;
        }

        return redirect()->route('admin.documents')
            ->with('success', $count.' document'.($count === 1 ? '' : 's').' uploaded.');
    })->name('documents.store');

    Route::get('/documents/{document}/edit', function (Document $document) {
        return view('admin.documents.form', compact('document'));
    })->name('documents.edit');

    Route::put('/documents/{document}', function (Request $request, Document $document) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'category' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:25600',
        ]);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? null,
            'sort_order' => $validated['sort_order'] ?? $document->sort_order,
            'is_published' => $request->boolean('is_published'),
        ];

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($document->path);
            $file = $request->file('file');
            $data['path'] = $file->store('documents', 'public');
            $data['original_name'] = $file->getClientOriginalName();
            $data['mime'] = $file->getClientMimeType();
            $data['size'] = $file->getSize();
        }

        $document->update($data);

        return redirect()->route('admin.documents')->with('success', 'Document updated.');
    })->name('documents.update');

    Route::post('/documents/{document}/toggle', function (Document $document) {
        $document->update(['is_published' => ! $document->is_published]);

        return back()->with('success', $document->is_published ? 'Document published.' : 'Document hidden.');
    })->name('documents.toggle');

    Route::delete('/documents/{document}', function (Document $document) {
        Storage::disk('public')->delete($document->path);
        $document->delete();

        return redirect()->route('admin.documents')->with('success', 'Document deleted.');
    })->name('documents.delete');

    // ── Digital Questionnaires (scaffold — admin only, not public) ──

    Route::get('/questionnaires', function () {
        return view('admin.questionnaires.index', [
            'questionnaires' => QuestionnaireRegistry::all(),
            'responses' => QuestionnaireResponse::with('submitter')->latest()->paginate(25),
        ]);
    })->name('questionnaires');

    Route::get('/questionnaires/responses/{response}', function (QuestionnaireResponse $response) {
        $definition = QuestionnaireRegistry::find($response->questionnaire_key);

        return view('admin.questionnaires.show', compact('response', 'definition'));
    })->name('questionnaires.response');

    Route::delete('/questionnaires/responses/{response}', function (QuestionnaireResponse $response) {
        $response->delete();

        return redirect()->route('admin.questionnaires')->with('success', 'Response deleted.');
    })->name('questionnaires.response.delete');

    Route::get('/questionnaires/{key}/fill', function (string $key) {
        $definition = QuestionnaireRegistry::find($key);
        abort_unless($definition, 404);

        return view('admin.questionnaires.fill', compact('key', 'definition'));
    })->name('questionnaires.fill');

    Route::post('/questionnaires/{key}', function (Request $request, string $key) {
        $definition = QuestionnaireRegistry::find($key);
        abort_unless($definition, 404);

        $fields = QuestionnaireRegistry::fields($definition);

        $rules = [];
        $labels = [];
        foreach ($fields as $field) {
            $name = $field['name'];
            $labels[$name] = $field['label'];
            $required = $field['required'] ?? false;

            $rules[$name] = match ($field['type']) {
                'checkbox' => $required ? 'accepted' : 'nullable',
                'email' => ($required ? 'required' : 'nullable').'|email|max:255',
                'textarea' => ($required ? 'required' : 'nullable').'|string|max:5000',
                'select', 'radio' => ($required ? 'required' : 'nullable').'|string|in:'.implode(',', $field['options'] ?? []),
                default => ($required ? 'required' : 'nullable').'|string|max:255',
            };
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames(collect($labels)->map(fn ($l) => mb_strtolower($l))->all());
        $validated = $validator->validate();

        $answers = [];
        $applicantName = null;
        $applicantEmail = null;
        foreach ($fields as $field) {
            $name = $field['name'];
            $value = $field['type'] === 'checkbox'
                ? $request->boolean($name)
                : ($validated[$name] ?? null);
            $answers[$name] = $value;

            if (($field['primary'] ?? null) === 'name') {
                $applicantName = $value;
            }
            if (($field['primary'] ?? null) === 'email') {
                $applicantEmail = $value;
            }
        }

        $response = QuestionnaireResponse::create([
            'questionnaire_key' => $key,
            'questionnaire_title' => $definition['title'],
            'applicant_name' => $applicantName,
            'applicant_email' => $applicantEmail,
            'answers' => $answers,
            'status' => 'submitted',
            'submitted_by' => auth()->id(),
        ]);

        return redirect()->route('admin.questionnaires.response', $response)
            ->with('success', 'Questionnaire submitted and saved.');
    })->name('questionnaires.submit');

    // ── User Management (developer & owner only) ───────────────

    Route::get('/users', function () {
        abort_unless(auth()->user()->canManageUsers(), 403);

        return view('admin.users', [
            'admins' => User::whereIn('role', [User::ROLE_DEVELOPER, User::ROLE_OWNER, User::ROLE_ADMIN])
                ->orderByRaw("CASE role WHEN 'developer' THEN 1 WHEN 'owner' THEN 2 WHEN 'admin' THEN 3 ELSE 4 END")
                ->get(),
            'members' => User::where('role', User::ROLE_CLIENT)->orderByDesc('created_at')->get(),
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

    // ── Member (tracker) management ────────────────────────────
    // Members use the existing 'client' role (tracker-only). Admin-created
    // members are auto-verified so they can sign in immediately.

    Route::post('/users/members', function (Request $request) {
        abort_unless($request->user()->canManageUsers(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_CLIENT,
            'email_verified_at' => now(),
        ]);

        return back()->with('success', 'Member created. They can sign in to the tracker immediately.');
    })->name('users.members.store');

    Route::post('/users/members/{user}/update', function (Request $request, User $user) {
        abort_unless($request->user()->canManageUsers(), 403);
        abort_unless($user->isClient(), 404);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8',
        ]);

        $update = ['name' => $data['name'], 'email' => $data['email']];
        if ($request->filled('password')) {
            $update['password'] = $data['password'];
        }

        $user->update($update);

        return back()->with('success', 'Member updated.');
    })->name('users.members.update');

    Route::delete('/users/members/{user}', function (Request $request, User $user) {
        abort_unless($request->user()->canManageUsers(), 403);
        abort_unless($user->isClient(), 404);

        $user->delete();

        return back()->with('success', 'Member deleted.');
    })->name('users.members.delete');
});

require __DIR__.'/status-tracker.php';
