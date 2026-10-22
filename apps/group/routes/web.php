<?php

use App\Http\Controllers\Admin\ApplicationTypeController;
use App\Http\Controllers\Admin\MotivationLetterController;
use App\Http\Controllers\Admin\MotivationMemberController;
use App\Http\Controllers\Admin\MotivationRequestController;
use App\Http\Controllers\Admin\MotivationTemplateController;
use App\Http\Controllers\Admin\Storage\AgreementsController;
use App\Http\Controllers\Admin\Storage\DashboardController;
use App\Http\Controllers\Admin\Storage\EstatesController;
use App\Http\Controllers\Admin\Storage\ItemsController;
use App\Http\Controllers\Admin\Storage\LabelController;
use App\Http\Controllers\Admin\Storage\LogController;
use App\Http\Controllers\Admin\Storage\RegisterController;
use App\Http\Controllers\Admin\Storage\SearchController;
use App\Http\Controllers\Admin\Storage\SelfStorageController;
use App\Http\Controllers\Admin\Storage\SettingsController;
use App\Http\Controllers\Admin\TemplateAssetController;
use App\Http\Controllers\Admin\TemplatePreviewController;
use App\Http\Controllers\AdminFirearmApplicationsController;
use App\Http\Controllers\AgentDocsController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Mail\MotivationEnquiryReceipt;
use App\Mail\NewArmsEnquiry;
use App\Mail\NewMotivationEnquiry;
use App\Models\ArmsEnquiry;
use App\Models\ArmsListing;
use App\Models\ArmsSavedReply;
use App\Models\Document;
use App\Models\MotivationEnquiry;
use App\Models\QuestionnaireResponse;
use App\Models\Setting;
use App\Models\User;
use App\Support\AdminAreas;
use App\Support\ArmsListingNormaliser;
use App\Support\ArmsPhone;
use App\Support\ImageOptimizer;
use App\Support\MotivationServices;
use App\Support\QuestionnaireRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

Route::get('/', function (Request $request) {
    $host = $request->getHost();

    if (str_starts_with($host, 'motivations.')) {
        return view('motivations');
    }

    if (str_starts_with($host, 'storage.')) {
        return view('storage-landing');
    }

    if (str_starts_with($host, 'arms.')) {
        $type = in_array($request->query('type'), array_keys(ArmsListing::TYPES), true)
            ? $request->query('type')
            : null;
        $sort = in_array($request->query('sort'), ['newest', 'price_asc', 'price_desc'], true)
            ? $request->query('sort')
            : 'newest';
        $stock = ArmsListing::visible()->get();

        return view('arms-landing', [
            'listings' => ArmsListing::arrangeForPublic($stock, $type, $sort),
            'typeCounts' => [
                'all' => $stock->count(),
                'handgun' => $stock->where('type', 'handgun')->count(),
                'rifle' => $stock->where('type', 'rifle')->count(),
                'shotgun' => $stock->where('type', 'shotgun')->count(),
            ],
            'activeType' => $type,
            'activeSort' => $sort,
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
Route::get('/llms.txt', [AgentDocsController::class, 'llms'])->name('agent.llms');
Route::get('/about.md', [AgentDocsController::class, 'aboutMd'])->name('agent.about-md');
Route::get('/services.md', [AgentDocsController::class, 'servicesMd'])->name('agent.services-md');
Route::get('/faq.md', [AgentDocsController::class, 'faqMd'])->name('agent.faq-md');

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

// Per-listing detail page. Sold and archived listings stay on a 200 page
// with noindex, no enquiry form, and up to three similar firearms.
Route::get('/listings/{listing:slug}', function (ArmsListing $listing) use ($armsOnly) {
    $armsOnly();

    $unavailable = ! $listing->isLive();

    return view('arms-listing-detail', [
        'listing' => $listing,
        'related' => $unavailable
            ? $listing->similarActive(3)
            : ArmsListing::prioritised()->where('id', '!=', $listing->id)->limit(3)->get(),
        'unavailable' => $unavailable,
    ]);
})->name('arms.listing.show');

Route::post('/arms/send-otp', function (Request $request) use ($armsOnly) {
    $armsOnly();

    // Require both the email and a matching confirmation so a typo'd address
    // never gets a verification code mailed into the void. Both the per-listing
    // modal and the general-enquiry form on arms-landing.blade.php send
    // `email_confirmation` alongside `email`. We use the explicit
    // `required|same:email` pair (rather than Laravel's `confirmed` rule) so
    // the two failure modes can carry distinct user-facing messages.
    $request->validate([
        'email' => 'required|email:rfc|max:255',
        'email_confirmation' => 'required|same:email',
    ], [
        'email_confirmation.required' => 'Please confirm your email.',
        'email_confirmation.same' => 'Email addresses do not match.',
    ]);

    $email = strtolower(trim($request->email));
    $rateLimitKey = 'arms-otp-send:'.$email;

    if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
        return response()->json([
            'error' => 'Too many attempts. Please wait '.RateLimiter::availableIn($rateLimitKey).' seconds.',
        ], 429);
    }

    RateLimiter::hit($rateLimitKey, 120);

    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    Cache::put("arms-otp:{$email}", $code, now()->addMinutes(10));

    try {
        Mail::send('emails.arms-otp-verification', ['code' => $code], function ($message) use ($email) {
            $message->to($email)->subject('Your Verification Code — Ranyati Arms');
        });
    } catch (Throwable $e) {
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

    $rateLimitKey = 'arms-enquiry:'.$request->ip();
    if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
        return response()->json(['error' => 'Too many enquiries. Please wait a few minutes and try again.'], 429);
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:50',
        'message' => 'nullable|string|max:2000',
        'otp' => 'required|string|size:6',
        'website' => 'nullable|string|max:255',
    ]);

    if (filled($validated['website'] ?? null)) {
        return response()->json(['success' => true, 'message' => 'Thanks. We have your enquiry and will get back to you.']);
    }

    if (! $listing->isLive()) {
        return response()->json([
            'error' => 'This firearm is sold. Look at the similar stock on the page, or send a general enquiry.',
        ], 422);
    }

    $phone = ArmsPhone::normalise($validated['phone']);
    if ($phone === null) {
        return response()->json(['error' => 'Enter a South African cell number, for example 082 000 0000.'], 422);
    }

    $email = strtolower(trim($validated['email']));
    $cachedCode = Cache::get("arms-otp:{$email}");

    if (! $cachedCode || $cachedCode !== $validated['otp']) {
        return response()->json(['error' => 'Invalid or expired verification code.'], 422);
    }

    Cache::forget("arms-otp:{$email}");
    RateLimiter::hit($rateLimitKey, 600);

    $enquiry = ArmsEnquiry::create([
        'arms_listing_id' => $listing->id,
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phone' => $phone,
        'phone_e164' => $phone,
        'message' => $validated['message'] ?? null,
        'status' => 'new',
    ]);

    try {
        Mail::send(new NewArmsEnquiry($enquiry));
    } catch (Throwable $e) {
    }

    return response()->json(['success' => true, 'message' => 'Thanks. We have your enquiry and will get back to you.']);
})->middleware('throttle:30,1')->name('arms.enquire');

Route::post('/arms/general-enquiry', function (Request $request) use ($armsOnly) {
    $armsOnly();

    $rateLimitKey = 'arms-enquiry:'.$request->ip();
    if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
        return response()->json(['error' => 'Too many enquiries. Please wait a few minutes and try again.'], 429);
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:50',
        'message' => 'required|string|max:2000',
        'otp' => 'required|string|size:6',
        'type' => 'nullable|in:handgun,rifle,shotgun',
        'budget' => 'nullable|numeric|min:0',
        'website' => 'nullable|string|max:255',
    ]);

    if (filled($validated['website'] ?? null)) {
        return response()->json(['success' => true, 'message' => 'Thanks. We have your enquiry and will get back to you.']);
    }

    $phone = ArmsPhone::normalise($validated['phone']);
    if ($phone === null) {
        return response()->json(['error' => 'Enter a South African cell number, for example 082 000 0000.'], 422);
    }

    // Same OTP flow as the per-listing enquiry — the code is set by the
    // shared `/arms/send-otp` endpoint and read back from the application
    // cache. Having one unified cache key means a user who verified an
    // email for a per-listing enquiry does not have to verify it again
    // for the general-enquiry form in the same session.
    $email = strtolower(trim($validated['email']));
    $cachedCode = Cache::get("arms-otp:{$email}");

    if (! $cachedCode || $cachedCode !== $validated['otp']) {
        return response()->json(['error' => 'Invalid or expired verification code.'], 422);
    }

    Cache::forget("arms-otp:{$email}");
    RateLimiter::hit($rateLimitKey, 600);

    $lookingFor = trim($validated['message']);
    if (! empty($validated['type'] ?? null)) {
        $lookingFor .= "\nType: ".ArmsListing::TYPES[$validated['type']];
    }
    if (($validated['budget'] ?? null) !== null && $validated['budget'] !== '') {
        $lookingFor .= "\nBudget: R ".number_format((float) $validated['budget'], 0, '.', ' ');
    }

    ArmsEnquiry::create([
        'arms_listing_id' => null,
        'name' => $validated['name'],
        'email' => $validated['email'],
        'phone' => $phone,
        'phone_e164' => $phone,
        'message' => $lookingFor,
        'status' => 'new',
    ]);

    return response()->json(['success' => true, 'message' => 'Thanks. We have your enquiry and will get back to you.']);
})->name('arms.enquire.general');

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
    abort_if(str_starts_with($host, 'storage.') || str_starts_with($host, 'arms.'), 404);

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
    $rateLimitKey = 'otp-send:'.$email;

    if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
        return response()->json([
            'error' => 'Too many attempts. Please wait '.RateLimiter::availableIn($rateLimitKey).' seconds.',
        ], 429);
    }

    RateLimiter::hit($rateLimitKey, 120);

    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    Cache::put("otp:{$email}", $code, now()->addMinutes(10));

    try {
        Mail::send('emails.otp-verification', ['code' => $code], function ($message) use ($email) {
            $message->to($email)->subject('Your Verification Code — Ranyati Motivations');
        });
    } catch (Throwable $e) {
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
        'services.*' => ['string', 'max:80', Rule::in(MotivationServices::validKeys())],
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
        $valid = array_flip(MotivationServices::validKeys());
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
        Mail::send(new MotivationEnquiryReceipt($enquiry));
    } catch (Throwable $e) {
        Log::warning('Motivation enquiry customer receipt failed', [
            'enquiry_id' => $enquiry->id,
            'error' => $e->getMessage(),
        ]);
    }

    return redirect()->route('enquire')->with('success', 'Thank you! Your enquiry has been submitted. Our team will be in touch shortly.');
})->name('enquire.submit');

// ── Admin ──────────────────────────────────────────────────────

Route::get('/admin/login', function () {
    /** @var User|null $user */
    $user = Auth::user();

    if ($user) {
        return redirect()->to($user->adminLandingUrl());
    }

    return view('admin.login');
})->name('admin.login');

Route::post('/admin/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Password guessing on this public form. Same limit as the OTP sends:
    // 3 attempts for an email, then a 120 second wait.
    $email = strtolower(trim($request->email));
    $rateLimitKey = 'admin-login:'.$email;

    if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
        return back()->withInput($request->only('email'))->withErrors([
            'email' => 'Too many attempts. Please wait '.RateLimiter::availableIn($rateLimitKey).' seconds.',
        ]);
    }

    if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
        RateLimiter::hit($rateLimitKey, 120);

        return back()->withInput($request->only('email'))->withErrors(['email' => 'Invalid credentials.']);
    }

    if (! $request->user()->isAdmin()) {
        Auth::logout();
        RateLimiter::hit($rateLimitKey, 120);

        return back()->withInput($request->only('email'))->withErrors(['email' => 'You do not have admin access.']);
    }

    RateLimiter::clear($rateLimitKey);
    $request->session()->regenerate();

    return redirect()->to($request->user()->adminLandingUrl());
})->name('admin.login.submit');

Route::post('/admin/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('admin.login');
})->name('admin.logout');

Route::prefix('admin')->middleware('admin')->name('admin.')->group(function () {

    // Post-login area chooser. Deliberately never auto-redirects, even when
    // the user has a saved default — this is also the "switch area" screen
    // and must stay reachable as a way to change or clear that default.
    Route::get('/choose', function (Request $request) {
        return view('admin.choose', [
            'areas' => AdminAreas::available(),
            'current' => $request->user()->default_admin_area,
        ]);
    })->name('choose');

    Route::post('/choose', function (Request $request) {
        $validated = $request->validate([
            'area' => ['required', 'string', Rule::in(AdminAreas::validKeys())],
        ]);

        // Unticking "remember" clears any previously saved default, so this
        // screen doubles as the way to go back to being asked every login.
        $request->user()->update([
            'default_admin_area' => $request->boolean('remember_area') ? $validated['area'] : null,
        ]);

        return redirect()->to(AdminAreas::urlFor($validated['area']));
    })->name('choose.submit');

    // Phase 2: `/admin` is the motivations-area overview (mockup 01) —
    // 4 KPI tiles, phase pipeline card, "Needs your action" table. The
    // previous enquiries-only dashboard is now covered by the Enquiries
    // link in the sidebar and the "Unread enquiries" KPI tile.
    Route::get('/', App\Http\Controllers\Admin\DashboardController::class)->name('dashboard');

    // Global search used by the topbar search field (focus with `/`).
    Route::get('/search', App\Http\Controllers\Admin\SearchController::class)->name('search');

    Route::get('/enquiries', function (Request $request) {
        $allowedFilters = ['unread', 'read', 'month', 'needs_reply', ...MotivationEnquiry::statusKeys()];
        $filter = $request->query('filter');
        $filter = in_array($filter, $allowedFilters, true) ? $filter : null;

        $allowedSorts = ['created_at', 'replied_at', 'status', 'name'];
        $sort = in_array($request->query('sort'), $allowedSorts, true) ? $request->query('sort') : 'created_at';
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $q = trim((string) $request->query('q', ''));

        $query = MotivationEnquiry::query();

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $like = '%'.$q.'%';
                $sub->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('membership_number', 'like', $like);
            });
        }

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        } elseif ($filter === 'month') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($filter === 'needs_reply') {
            $query->whereNull('replied_at')
                ->where('status', '!=', MotivationEnquiry::STATUS_CLOSED);
        } elseif (in_array($filter, MotivationEnquiry::statusKeys(), true)) {
            $query->where('status', $filter);
        }

        // Sort by chosen column, then id desc as the tiebreaker so consistent
        // ordering across pages.
        $query->orderBy($sort, $direction)->orderByDesc('id');

        return view('admin.enquiries', [
            'enquiries' => $query->paginate(25)->withQueryString(),
            'unreadCount' => MotivationEnquiry::whereNull('read_at')->count(),
            'readCount' => MotivationEnquiry::whereNotNull('read_at')->count(),
            'totalCount' => MotivationEnquiry::count(),
            'needsReplyCount' => MotivationEnquiry::whereNull('replied_at')
                ->where('status', '!=', MotivationEnquiry::STATUS_CLOSED)
                ->count(),
            'activeFilter' => $filter,
            'sort' => $sort,
            'direction' => $direction,
            'q' => $q,
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

    Route::post('/enquiries/{enquiry}/status', function (Request $request, MotivationEnquiry $enquiry) {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(MotivationEnquiry::statusKeys())],
        ]);

        $enquiry->update(['status' => $validated['status']]);

        return back()->with('success', 'Status updated to '.MotivationEnquiry::statusLabels()[$validated['status']].'.');
    })->name('enquiries.status');

    Route::post('/enquiries/{enquiry}/mark-replied', function (MotivationEnquiry $enquiry) {
        $enquiry->update(['replied_at' => now()]);

        return back()->with('success', 'Reply logged. This enquiry is no longer marked as needing a reply.');
    })->name('enquiries.mark-replied');

    Route::post('/enquiries/{enquiry}/clear-replied', function (MotivationEnquiry $enquiry) {
        $enquiry->update(['replied_at' => null]);

        return back()->with('success', 'Reply marker cleared — the enquiry is back in the "needs reply" queue.');
    })->name('enquiries.clear-replied');

    Route::delete('/enquiries/{enquiry}', function (MotivationEnquiry $enquiry) {
        $enquiry->delete();

        return redirect()->route('admin.enquiries')->with('success', 'Enquiry deleted.');
    })->name('enquiries.delete');

    Route::get('/applications', [AdminFirearmApplicationsController::class, 'index'])
        ->name('applications');
    Route::get('/applications/log', [AdminFirearmApplicationsController::class, 'log'])
        ->name('applications.log');
    Route::post('/applications/circuit/reset', [AdminFirearmApplicationsController::class, 'resetCircuit'])
        ->name('applications.circuit.reset');
    Route::get('/applications/batches/{batchKey}', [AdminFirearmApplicationsController::class, 'showBatch'])
        ->whereNumber('batchKey')
        ->name('applications.batch');
    Route::get('/applications/users/{user}', [AdminFirearmApplicationsController::class, 'showUser'])
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
                if ($part === '') {
                    continue;
                }
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

            return back()->with('success', 'Test email sent to '.$request->test_email);
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to send test email: '.$e->getMessage());
        }
    })->name('settings.test');

    // ── Arms Listings Management ────────────────────────────────

    Route::get('/arms', function (Request $request) {
        $allowedFilters = ['all', 'active', 'featured', 'sold', 'archived', 'reduced'];
        $allowedSorts = ['created_at', 'price', 'status', 'title', 'enquiries_count'];
        $sort = in_array($request->query('sort'), $allowedSorts, true) ? $request->query('sort') : null;
        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';
        $q = trim((string) $request->query('q', ''));
        $requestedFilter = $request->query('filter');

        if (! is_string($requestedFilter) || ! in_array($requestedFilter, $allowedFilters, true)) {
            return redirect()->route('admin.arms', array_filter([
                'filter' => 'active',
                'q' => $q !== '' ? $q : null,
                'sort' => $sort,
                'direction' => $sort ? $direction : null,
                'page' => $request->query('page'),
            ]));
        }

        $filter = $requestedFilter;
        $query = ArmsListing::query()->withCount('enquiries');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $like = '%'.$q.'%';
                $sub->where('title', 'like', $like)
                    ->orWhere('make', 'like', $like)
                    ->orWhere('model', 'like', $like)
                    ->orWhere('calibre', 'like', $like)
                    ->orWhere('stock_number', 'like', $like);
            });
        }

        switch ($filter) {
            case 'active':
                $query->whereIn('status', ['active', 'reduced']);
                break;
            case 'featured':
                $query->where('is_featured', true)->whereIn('status', ['active', 'reduced']);
                break;
            case 'sold':
                $query->where('status', 'sold');
                break;
            case 'archived':
                $query->where('status', 'archived');
                break;
            case 'reduced':
                $query->priceReduced();
                break;
        }

        if ($sort) {
            $query->orderBy($sort, $direction)->orderByDesc('id');
        } else {
            $query->adminOrdered();
        }

        $featuredCount = ArmsListing::featuredSpotCount();
        $maxFeatured = ArmsListing::featuredLimit();

        return view('admin.arms.index', [
            'listings' => $query->paginate(25)->withQueryString(),
            'stats' => [
                'active' => ArmsListing::whereIn('status', ['active', 'reduced'])->count(),
                'featured' => $featuredCount,
                'reduced' => ArmsListing::priceReduced()->count(),
                'sold' => ArmsListing::where('status', 'sold')->count(),
                'archived' => ArmsListing::where('status', 'archived')->count(),
                'enquiries' => ArmsEnquiry::count(),
                'total' => ArmsListing::count(),
            ],
            'activeFilter' => $filter,
            'sort' => $sort,
            'direction' => $direction,
            'q' => $q,
            'featuredCount' => $featuredCount,
            'maxFeatured' => $maxFeatured,
            'staleAfterDays' => max(1, (int) config('arms.stale_after_days', 60)),
        ]);
    })->name('arms');

    Route::get('/arms/create', function () {
        return view('admin.arms.form', [
            'listing' => null,
            'calibres' => ArmsListingNormaliser::calibreOptions(),
            'featuredFull' => ArmsListing::featuredSpotCount() >= ArmsListing::featuredLimit(),
        ]);
    })->name('arms.create');

    Route::post('/arms', function (Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'make' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'calibre' => 'required|string|max:255',
            'type' => 'required|in:handgun,rifle,shotgun',
            'condition' => 'required|in:excellent,very_good,good,fair',
            'accessories' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:20000',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:15360',
        ]);

        $validated['model'] = ArmsListingNormaliser::model($validated['model'] ?? null);
        $validated['calibre'] = ArmsListingNormaliser::calibre($validated['calibre']) ?? '';
        $validated['description_long'] = $validated['description'] ?? null;
        if ($validated['calibre'] === '') {
            return back()->withErrors(['calibre' => 'Choose a calibre.'])->withInput();
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = ImageOptimizer::storeUpload($image, 'arms');
            }
        }

        ArmsListing::create([
            ...Arr::except($validated, ['images']),
            'images' => $imagePaths,
            'status' => 'active',
            'is_featured' => false,
            'featured_at' => now(),
            'created_by' => auth()->id(),
        ]);

        $redirect = redirect()
            ->route('admin.arms', ['filter' => 'active'])
            ->with('success', 'Listing created.');

        if ($warning = ArmsListingNormaliser::repeatWarning($validated['make'], $validated['model'])) {
            $redirect->with('warning', $warning);
        }

        return $redirect;
    })->name('arms.store');

    Route::get('/arms/{listing}/edit', function (ArmsListing $listing) {
        return view('admin.arms.form', [
            'listing' => $listing,
            'calibres' => ArmsListingNormaliser::calibreOptions(),
            'featuredFull' => ArmsListing::featuredSpotCount() >= ArmsListing::featuredLimit(),
        ]);
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
            'model' => 'nullable|string|max:255',
            'calibre' => 'required|string|max:255',
            'type' => 'required|in:handgun,rifle,shotgun',
            'condition' => 'required|in:excellent,very_good,good,fair',
            'accessories' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:20000',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:15360',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer',
            'image_order' => 'nullable|array',
            'image_order.*' => 'integer',
        ]);

        $validated['model'] = ArmsListingNormaliser::model($validated['model'] ?? null);
        $validated['calibre'] = ArmsListingNormaliser::calibre($validated['calibre']) ?? '';
        $validated['description_long'] = $validated['description'] ?? null;
        if ($validated['calibre'] === '') {
            return back()->withErrors(['calibre' => 'Choose a calibre.'])->withInput();
        }

        $existing = array_values($listing->images ?? []);
        $remove = array_map('intval', (array) $request->input('remove_images', []));
        $order = array_map('intval', (array) $request->input('image_order', []));

        foreach ($remove as $idx) {
            if (isset($existing[$idx]) && is_string($existing[$idx])) {
                Storage::disk('public')->delete($existing[$idx]);
            }
        }

        $currentImages = [];
        $used = [];
        $sequence = $order !== [] ? $order : array_keys($existing);
        foreach ($sequence as $idx) {
            if (in_array($idx, $remove, true) || ! isset($existing[$idx]) || isset($used[$idx])) {
                continue;
            }
            $currentImages[] = $existing[$idx];
            $used[$idx] = true;
        }
        foreach ($existing as $idx => $path) {
            if (isset($used[$idx]) || in_array($idx, $remove, true)) {
                continue;
            }
            $currentImages[] = $path;
        }

        $newCount = $request->hasFile('images') ? count($request->file('images')) : 0;
        if (count($currentImages) + $newCount > 10) {
            return back()
                ->withErrors(['images' => 'Total images cannot exceed 10. You have '.count($currentImages).' kept and tried to add '.$newCount.'.'])
                ->withInput();
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $currentImages[] = ImageOptimizer::storeUpload($image, 'arms');
            }
        }

        $updateData = [
            ...Arr::except($validated, ['images', 'remove_images', 'image_order']),
            'images' => $currentImages,
        ];

        // Live listings stay active — price drops are expressed via
        // original_price, and featuring/archiving/sold are all manual actions.
        if (in_array($listing->status, ['active', 'reduced'], true)) {
            $updateData['status'] = 'active';
        }

        $listing->update($updateData);

        $redirect = redirect()
            ->route('admin.arms', ['filter' => 'active'])
            ->with('success', 'Listing updated.');

        if ($warning = ArmsListingNormaliser::repeatWarning($validated['make'], $validated['model'])) {
            $redirect->with('warning', $warning);
        }

        return $redirect;
    })->name('arms.update');

    Route::delete('/arms/{listing}', function (ArmsListing $listing) {
        if ($listing->images) {
            foreach ($listing->images as $path) {
                Storage::disk('public')->delete($path);
            }
        }
        $listing->delete();

        return redirect()->route('admin.arms', ['filter' => 'active'])->with('success', 'Listing deleted.');
    })->name('arms.delete');

    Route::post('/arms/{listing}/feature', function (ArmsListing $listing) {
        if (! $listing->feature()) {
            return back()->with('error', 'Remove another listing from the top first.');
        }

        return back()->with('success', 'Listing pinned to the top of the stock list.');
    })->name('arms.feature');

    Route::post('/arms/{listing}/unfeature', function (ArmsListing $listing) {
        $listing->unfeature();

        return back()->with('success', 'Removed from the top of the stock list.');
    })->name('arms.unfeature');

    Route::post('/arms/{listing}/restore', function (ArmsListing $listing) {
        $listing->restore();

        return back()->with('success', 'Put back on sale.');
    })->name('arms.restore');

    Route::post('/arms/{listing}/archive', function (ArmsListing $listing) {
        $wasFeatured = (bool) $listing->is_featured;
        $listing->archive();

        return back()->with('success', $wasFeatured
            ? 'Archived. Removed from the top.'
            : 'Listing archived.');
    })->name('arms.archive');

    Route::post('/arms/{listing}/sold', function (ArmsListing $listing) {
        $wasFeatured = (bool) $listing->is_featured;
        $listing->markSold();

        return back()->with('success', $wasFeatured
            ? 'Marked as sold. Removed from the top.'
            : 'Marked as sold.');
    })->name('arms.sold');

    Route::post('/arms/{listing}/reduce', function (Request $request, ArmsListing $listing) {
        abort_unless($listing->isLive(), 403);

        $data = $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        if ((float) $data['price'] >= (float) $listing->price) {
            return back()->with('error', 'Enter a price lower than the current price.');
        }

        $listing->update([
            'original_price' => $listing->price,
            'price' => $data['price'],
        ]);

        return back()->with('success', 'Price reduced. The previous price stays visible with a line through it.');
    })->name('arms.reduce');

    Route::post('/arms/{listing}/remove-reduction', function (ArmsListing $listing) {
        $listing->update(['original_price' => null]);

        return back()->with('success', 'Price reduction removed.');
    })->name('arms.remove-reduction');

    Route::post('/arms/{listing}/duplicate', function (ArmsListing $listing) {
        // Photos: copy the underlying files so deleting either listing later
        // doesn't yank images from the other. `Storage::copy` is a no-op if
        // the source is already missing (skipped) so a partial gallery still
        // duplicates gracefully.
        $copiedImages = [];
        foreach ($listing->images ?? [] as $source) {
            if (! is_string($source) || $source === '') {
                continue;
            }

            try {
                if (! Storage::disk('public')->exists($source)) {
                    continue;
                }

                $ext = pathinfo($source, PATHINFO_EXTENSION) ?: 'jpg';
                $destination = 'arms/'.Str::random(40).'.'.$ext;

                Storage::disk('public')->copy($source, $destination);
                $copiedImages[] = $destination;
            } catch (Throwable $e) {
                Log::warning('Duplicate: failed to copy image', [
                    'listing_id' => $listing->id,
                    'source' => $source,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $copy = $listing->replicate(['slug', 'stock_number', 'is_featured', 'featured_at', 'archived_at']);
        $copy->title = trim($listing->title.' (copy)');
        $copy->stock_number = null;
        $copy->images = $copiedImages;
        $copy->status = 'active';
        $copy->is_featured = false;
        $copy->featured_at = now();
        $copy->archived_at = null;
        $copy->created_by = auth()->id() ?? $listing->created_by;
        $copy->save(); // saving() hook regenerates the slug.

        return redirect()
            ->route('admin.arms.edit', $copy)
            ->with('success', 'Duplicated — review the details and save when you\'re ready.');
    })->name('arms.duplicate');

    // ── Arms saved replies ─────────────────────────────────────

    Route::get('/arms/replies', function () {
        return view('admin.arms.replies', [
            'replies' => ArmsSavedReply::query()->orderBy('sort')->orderBy('id')->get(),
        ]);
    })->name('arms.replies');

    Route::post('/arms/replies', function (Request $request) {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'body' => 'required|string|max:2000',
        ]);

        ArmsSavedReply::create([
            ...$data,
            'sort' => (int) ArmsSavedReply::max('sort') + 1,
        ]);

        return back()->with('success', 'Saved reply added.');
    })->name('arms.replies.store');

    Route::put('/arms/replies/{reply}', function (Request $request, ArmsSavedReply $reply) {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'body' => 'required|string|max:2000',
        ]);

        $reply->update($data);

        return back()->with('success', 'Saved reply updated.');
    })->name('arms.replies.update');

    Route::delete('/arms/replies/{reply}', function (ArmsSavedReply $reply) {
        $reply->delete();

        return back()->with('success', 'Saved reply deleted.');
    })->name('arms.replies.delete');

    // ── Arms Enquiries ─────────────────────────────────────────

    Route::get('/arms/enquiries', function (Request $request) {
        $allowed = ['new', 'replied', 'closed', 'general', 'all'];
        $q = trim((string) $request->query('q', ''));
        $listingId = $request->query('listing');
        $listingId = is_numeric($listingId) ? (int) $listingId : null;

        if (! in_array($request->query('filter'), $allowed, true)) {
            return redirect()->route('admin.arms.enquiries', array_filter([
                'filter' => 'new',
                'q' => $q !== '' ? $q : null,
                'listing' => $listingId,
            ]));
        }

        $filter = $request->query('filter');
        $query = ArmsEnquiry::with('listing');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $like = '%'.$q.'%';
                $sub->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('message', 'like', $like);
            });
        }

        if ($filter === 'general') {
            $query->whereNull('arms_listing_id');
        } elseif ($listingId !== null) {
            $query->where('arms_listing_id', $listingId);
        }

        if (in_array($filter, ['new', 'replied', 'closed'], true)) {
            $query->where('status', $filter);
        }

        $counts = ArmsEnquiry::query();
        if ($listingId !== null && $filter !== 'general') {
            $counts->where('arms_listing_id', $listingId);
        }

        return view('admin.arms.enquiries', [
            'enquiries' => $query->latest()->paginate(25)->withQueryString(),
            'counts' => [
                'new' => (clone $counts)->where('status', 'new')->count(),
                'replied' => (clone $counts)->where('status', 'replied')->count(),
                'closed' => (clone $counts)->where('status', 'closed')->count(),
                'general' => ArmsEnquiry::whereNull('arms_listing_id')->count(),
                'all' => (clone $counts)->count(),
            ],
            'activeFilter' => $filter,
            'q' => $q,
            'listingId' => $listingId,
            'listingFilter' => $listingId ? ArmsListing::find($listingId) : null,
            'replies' => ArmsSavedReply::query()->orderBy('sort')->orderBy('id')->get(),
        ]);
    })->name('arms.enquiries');

    Route::post('/arms/enquiries/{enquiry}/replied', function (ArmsEnquiry $enquiry) {
        $enquiry->update(['status' => 'replied', 'read_at' => now()]);

        return back()->with('success', 'Marked as replied.');
    })->name('arms.enquiries.replied');

    Route::post('/arms/enquiries/{enquiry}/close', function (ArmsEnquiry $enquiry) {
        $enquiry->update(['status' => 'closed', 'read_at' => now()]);

        return back()->with('success', 'Enquiry closed.');
    })->name('arms.enquiries.close');

    Route::post('/arms/enquiries/{enquiry}/whatsapp', function (Request $request, ArmsEnquiry $enquiry) {
        $digits = ArmsPhone::whatsappDigits($enquiry->phone_e164);
        if ($digits === null) {
            return back()->with('error', 'That phone number is not a South African cell number.');
        }

        $enquiry->update(['status' => 'replied', 'read_at' => now()]);

        $text = '';
        if ($request->input('reply') === 'similar') {
            $text = 'Hi '.$enquiry->name.', the '.$enquiry->firearmLabel().' has been sold. Similar stock: '.$enquiry->similarStockText();
        } elseif ($request->filled('reply_id')) {
            $saved = ArmsSavedReply::find($request->integer('reply_id'));
            $text = $saved ? $saved->renderFor($enquiry) : '';
        }

        $url = 'https://wa.me/'.$digits.($text !== '' ? '?text='.rawurlencode($text) : '');

        return redirect()->away($url);
    })->name('arms.enquiries.whatsapp');

    Route::delete('/arms/enquiries/{enquiry}', function (ArmsEnquiry $enquiry) {
        $enquiry->delete();

        return back()->with('success', 'Enquiry deleted.');
    })->name('arms.enquiries.delete');

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

    // ── Motivation builder & requests ──────────────────────────
    Route::prefix('motivation-templates')->name('motivation-templates.')->group(function () {
        Route::get('/', [MotivationTemplateController::class, 'index'])->name('index');
        Route::get('/create', [MotivationTemplateController::class, 'create'])->name('create');
        Route::post('/', [MotivationTemplateController::class, 'store'])->name('store');
        Route::get('/assets', [TemplateAssetController::class, 'index'])->name('assets.index');
        Route::post('/assets', [TemplateAssetController::class, 'store'])->name('assets.store');
        Route::delete('/assets/{asset}', [TemplateAssetController::class, 'destroy'])->name('assets.destroy');

        Route::get('/{motivationTemplate}/edit', [MotivationTemplateController::class, 'edit'])->name('edit');
        Route::put('/{motivationTemplate}', [MotivationTemplateController::class, 'update'])->name('update');
        Route::post('/{motivationTemplate}/autosave', [MotivationTemplateController::class, 'autosave'])->name('autosave');
        Route::post('/{motivationTemplate}/publish', [MotivationTemplateController::class, 'publish'])->name('publish');
        Route::post('/{motivationTemplate}/duplicate', [MotivationTemplateController::class, 'duplicate'])->name('duplicate');
        Route::post('/{motivationTemplate}/archive', [MotivationTemplateController::class, 'archive'])->name('archive');
        Route::post('/{motivationTemplate}/unarchive', [MotivationTemplateController::class, 'restoreTemplate'])->name('unarchive');
        Route::delete('/{motivationTemplate}', [MotivationTemplateController::class, 'destroy'])->name('destroy');

        Route::get('/{motivationTemplate}/versions', [MotivationTemplateController::class, 'versions'])->name('versions');
        Route::post('/{motivationTemplate}/versions/{version}/restore', [MotivationTemplateController::class, 'restore'])->name('versions.restore');

        Route::post('/{motivationTemplate}/preview', [TemplatePreviewController::class, 'store'])->name('preview.store');
        Route::get('/{motivationTemplate}/preview/{token}', [TemplatePreviewController::class, 'show'])->name('preview.show');
        Route::post('/{motivationTemplate}/canvas-preview', [TemplatePreviewController::class, 'canvas'])->name('canvas-preview');
    });

    Route::prefix('application-types')->name('application-types.')->group(function () {
        Route::get('/', [ApplicationTypeController::class, 'index'])->name('index');
        Route::get('/create', [ApplicationTypeController::class, 'create'])->name('create');
        Route::post('/', [ApplicationTypeController::class, 'store'])->name('store');
        Route::get('/{applicationType}/edit', [ApplicationTypeController::class, 'edit'])->name('edit');
        Route::put('/{applicationType}', [ApplicationTypeController::class, 'update'])->name('update');
        Route::delete('/{applicationType}', [ApplicationTypeController::class, 'destroy'])->name('destroy');
        Route::post('/{applicationType}/clone', [ApplicationTypeController::class, 'clone'])->name('clone');

        Route::post('/assets', [ApplicationTypeController::class, 'uploadAsset'])->name('assets.upload');
        Route::delete('/assets/{asset}', [ApplicationTypeController::class, 'destroyAsset'])->name('assets.destroy');
    });

    Route::prefix('motivation-members')->name('motivation-members.')->group(function () {
        Route::get('/', [MotivationMemberController::class, 'index'])->name('index');
        Route::get('/create', [MotivationMemberController::class, 'create'])->name('create');
        Route::post('/', [MotivationMemberController::class, 'store'])->name('store');
        Route::get('/{motivationMember}', [MotivationMemberController::class, 'show'])->name('show');
        Route::get('/{motivationMember}/profile', [MotivationMemberController::class, 'editProfile'])->name('profile.edit');
        Route::post('/{motivationMember}/profile', [MotivationMemberController::class, 'updateProfile'])->name('profile.update');
        Route::post('/{motivationMember}/motivations', [MotivationMemberController::class, 'startMotivation'])->name('motivations.start');
    });

    Route::prefix('motivation-requests')->name('motivation-requests.')->group(function () {
        Route::get('/', [MotivationRequestController::class, 'index'])->name('index');
        // Phase 2: CSV export of the current filter. Registered BEFORE
        // the `{motivation}` show route so Laravel's model-binding on
        // `/{motivation}` doesn't swallow `/export`.
        Route::get('/export.csv', [MotivationRequestController::class, 'export'])->name('export');
        Route::get('/{motivation}', [MotivationRequestController::class, 'show'])->name('show');
        Route::get('/{motivation}/edit', [MotivationRequestController::class, 'editOnBehalf'])->name('edit');
        Route::post('/{motivation}/answers', [MotivationRequestController::class, 'updateAnswers'])->name('answers');
        Route::post('/{motivation}/documents', [MotivationRequestController::class, 'uploadDocumentOnBehalf'])->name('documents.upload');
        Route::delete('/{motivation}/documents/{document}', [MotivationRequestController::class, 'deleteDocument'])->name('documents.delete');
        Route::post('/{motivation}/submit-on-behalf', [MotivationRequestController::class, 'submitOnBehalf'])->name('submit-on-behalf');
        Route::get('/{motivation}/poa', [MotivationRequestController::class, 'downloadPoa'])->name('poa');
        Route::post('/{motivation}/verify', [MotivationRequestController::class, 'verify'])->name('verify');
        Route::post('/{motivation}/request-changes', [MotivationRequestController::class, 'requestChanges'])->name('request-changes');
        Route::post('/{motivation}/regenerate', [MotivationRequestController::class, 'regenerate'])->name('regenerate');
        Route::get('/{motivation}/book', [MotivationRequestController::class, 'downloadBook'])->name('book');
        Route::post('/{motivation}/documents/{document}/check', [MotivationRequestController::class, 'checkDocument'])->name('documents.check');
        Route::get('/{motivation}/documents/{document}', [MotivationRequestController::class, 'viewDocument'])->name('documents.view');
        Route::post('/{motivation}/endorsement', [MotivationRequestController::class, 'uploadEndorsement'])->name('endorsement');

        // Per-firearm letter actions (new template pipeline).
        Route::post('/{motivation}/letters/generate', [MotivationLetterController::class, 'generate'])->name('letters.generate');
        Route::get('/{motivation}/letters/status', [MotivationLetterController::class, 'status'])->name('letters.status');
        Route::get('/{motivation}/letters/{letter}', [MotivationLetterController::class, 'open'])->name('letters.open');
        Route::get('/{motivation}/letters/{letter}/download', [MotivationLetterController::class, 'download'])->name('letters.download');
        Route::post('/{motivation}/letters/{letter}/regenerate', [MotivationLetterController::class, 'regenerate'])->name('letters.regenerate');
        Route::post('/{motivation}/letters/{letter}/text', [MotivationLetterController::class, 'updateText'])->name('letters.text');
        Route::post('/{motivation}/letters/{letter}/finalise', [MotivationLetterController::class, 'finalise'])->name('letters.finalise');
        Route::delete('/{motivation}/letters/{letter}', [MotivationLetterController::class, 'destroy'])->name('letters.destroy');

        Route::post('/{motivation}/ready', [MotivationRequestController::class, 'markReady'])->name('ready');
        Route::post('/{motivation}/printed', [MotivationRequestController::class, 'markPrinted'])->name('printed');
        Route::post('/{motivation}/invoice', [MotivationRequestController::class, 'uploadInvoice'])->name('invoice');
        Route::post('/{motivation}/paid', [MotivationRequestController::class, 'markPaid'])->name('paid');
        Route::post('/{motivation}/ready-for-collection', [MotivationRequestController::class, 'markReadyForCollection'])->name('ready-for-collection');
        Route::post('/{motivation}/shipped', [MotivationRequestController::class, 'markShipped'])->name('shipped');
        Route::post('/{motivation}/completed', [MotivationRequestController::class, 'markCompleted'])->name('completed');
    });

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
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
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

    // ── Storage (safe custody) ─────────────────────────────────
    // Real controllers under App\Http\Controllers\Admin\Storage\* rather
    // than more closures — the storage module has enough surface area
    // (intake, custody events, R2 uploads, PDF labels, register print)
    // that keeping it as an inline closure block would drown web.php.
    Route::prefix('storage')->name('storage.')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'save'])->name('settings.save');
        Route::post('/settings/test-connection', [SettingsController::class, 'testConnection'])->name('settings.test');

        Route::get('/estates', [EstatesController::class, 'index'])->name('estates');
        Route::get('/estates/create', [EstatesController::class, 'create'])->name('estates.create');
        Route::post('/estates', [EstatesController::class, 'store'])->name('estates.store');

        Route::get('/self', [SelfStorageController::class, 'index'])->name('self');
        Route::get('/self/create', [SelfStorageController::class, 'create'])->name('self.create');
        Route::post('/self', [SelfStorageController::class, 'store'])->name('self.store');

        Route::get('/agreements/{agreement}', [AgreementsController::class, 'show'])->name('agreements.show');
        Route::get('/agreements/{agreement}/items/create', [AgreementsController::class, 'addItems'])->name('agreements.items.create');
        Route::post('/agreements/{agreement}/items', [AgreementsController::class, 'storeItems'])->name('agreements.items.store');

        Route::get('/items/{item}', [ItemsController::class, 'show'])->name('items.show');
        Route::post('/items/{item}/events', [ItemsController::class, 'storeEvent'])->name('items.events.store');
        Route::get('/items/{item}/collect', [ItemsController::class, 'collectForm'])->name('items.collect.form');
        Route::post('/items/{item}/collect', [ItemsController::class, 'collect'])->name('items.collect');
        Route::get('/items/{item}/files/{file}/download', [ItemsController::class, 'downloadFile'])->name('items.files.download');
        Route::get('/items/{item}/label', [LabelController::class, 'show'])->name('items.label');

        Route::get('/log', LogController::class)->name('log');
        Route::get('/register/{book}', [RegisterController::class, 'show'])->name('register.show');
        Route::get('/search', SearchController::class)->name('search');
    });
});

require __DIR__.'/status-tracker.php';
