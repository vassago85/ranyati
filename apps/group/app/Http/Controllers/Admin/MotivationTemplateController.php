<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationType;
use App\Models\MotivationRequest;
use App\Models\MotivationTemplate;
use App\Models\MotivationTemplateVersion;
use App\Models\TemplateAsset;
use App\Support\ActivityLog;
use App\Support\Motivations\ConditionEvaluator;
use App\Support\Motivations\DocumentKinds;
use App\Support\Motivations\QuestionSchema;
use App\Support\Motivations\Templates\BlockKinds;
use App\Support\Motivations\Templates\PlaceholderCatalogue;
use App\Support\Motivations\Templates\TemplateVersionDiff;
use App\Support\Motivations\Templates\TemplateVersionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MotivationTemplateController extends Controller
{
    public function __construct(private readonly TemplateVersionManager $versions) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', MotivationTemplate::class);

        $filter = $request->query('status');
        $categoryFilter = $request->query('category');

        $query = MotivationTemplate::with(['publishedVersion', 'applicationTypes:id,name,slug'])
            ->withCount(['versions'])
            ->orderByRaw("CASE status WHEN 'published' THEN 1 WHEN 'draft' THEN 2 ELSE 3 END")
            ->orderBy('name');

        if ($filter && array_key_exists($filter, MotivationTemplate::STATUSES)) {
            $query->where('status', $filter);
        }
        if ($categoryFilter && array_key_exists($categoryFilter, ApplicationType::CATEGORIES)) {
            $query->where('category', $categoryFilter);
        }

        return view('admin.motivations.templates.index', [
            'templates' => $query->get(),
            'statuses' => MotivationTemplate::STATUSES,
            'activeStatus' => $filter,
            'activeCategory' => $categoryFilter,
            'counts' => MotivationTemplate::select('status', DB::raw('COUNT(*) as c'))
                ->groupBy('status')->pluck('c', 'status'),
            'categories' => ApplicationType::CATEGORIES,
        ]);
    }

    public function create()
    {
        Gate::authorize('create', MotivationTemplate::class);

        return view('admin.motivations.templates.create', [
            'categories' => ApplicationType::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', MotivationTemplate::class);

        $data = $request->validate($this->baseRules());

        $template = MotivationTemplate::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['slug'] ?? $data['name']),
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'status' => MotivationTemplate::STATUS_DRAFT,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $this->versions->ensureDraft($template, $request->user()->id);

        return redirect()->route('admin.motivation-templates.edit', $template)
            ->with('success', 'Template created. Build your layout and publish when ready.');
    }

    public function edit(MotivationTemplate $motivationTemplate)
    {
        Gate::authorize('view', $motivationTemplate);
        $motivationTemplate->load(['applicationTypes:id,name,slug,category', 'publishedVersion', 'versions.publisher']);

        $draft = $this->versions->ensureDraft($motivationTemplate);
        $assets = TemplateAsset::orderBy('category')->orderBy('name')->get();

        return view('admin.motivations.templates.edit', [
            'template' => $motivationTemplate,
            'draft' => $draft,
            'assets' => $assets,
            'palette' => BlockKinds::groups(),
            'kinds' => BlockKinds::all(),
            'placeholderCatalogue' => PlaceholderCatalogue::forTemplate($motivationTemplate),
            'conditionFields' => PlaceholderCatalogue::conditionFieldsFor($motivationTemplate),
            'operators' => ConditionEvaluator::OPERATORS,
            'unaryOps' => ConditionEvaluator::UNARY,
            'assetCategories' => TemplateAsset::CATEGORIES,
            'documentKinds' => DocumentKinds::all(),
            'statuses' => MotivationTemplate::STATUSES,
            'provinces' => QuestionSchema::PROVINCES,
            'previewRequests' => MotivationRequest::with('user.clientProfile', 'applicationType:id,name,slug')
                ->latest()->take(50)->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'label' => $r->reference().' — '.($r->user->clientProfile?->full_names.' '.$r->user->clientProfile?->surname ?: $r->user?->name).' · '.$r->applicationType?->name,
                ])->values()->all(),
        ]);
    }

    public function update(Request $request, MotivationTemplate $motivationTemplate)
    {
        Gate::authorize('update', $motivationTemplate);

        $data = $request->validate(array_merge($this->baseRules($motivationTemplate), [
            'blocks' => 'nullable|array',
            'settings' => 'nullable|array',
        ]));

        $draft = DB::transaction(function () use ($motivationTemplate, $data, $request) {
            $motivationTemplate->update([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['slug'] ?? $data['name'], $motivationTemplate->id),
                'category' => $data['category'],
                'description' => $data['description'] ?? null,
                'updated_by' => $request->user()->id,
            ]);

            return $this->versions->saveDraft(
                $motivationTemplate,
                $data['blocks'] ?? [],
                $data['settings'] ?? [],
                $request->user()->id,
            );
        });

        if ($request->wantsJson()) {
            return response()->json([
                'draft_id' => $draft->id,
                'version' => $draft->version,
                'blocks' => $draft->blocks,
                'settings' => $draft->settings,
                'saved_at' => now()->toIso8601String(),
            ]);
        }

        return redirect()->route('admin.motivation-templates.edit', $motivationTemplate)
            ->with('success', 'Changes saved.');
    }

    /**
     * JSON autosave of the draft only. Returns the normalised state so the
     * builder can re-render with any clean-up the server applied.
     */
    public function autosave(Request $request, MotivationTemplate $motivationTemplate)
    {
        Gate::authorize('update', $motivationTemplate);

        $data = $request->validate([
            'blocks' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        $draft = $this->versions->saveDraft(
            $motivationTemplate,
            $data['blocks'] ?? [],
            $data['settings'] ?? [],
            $request->user()->id,
        );

        return response()->json([
            'draft_id' => $draft->id,
            'version' => $draft->version,
            'blocks' => $draft->blocks,
            'settings' => $draft->settings,
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    public function publish(Request $request, MotivationTemplate $motivationTemplate)
    {
        Gate::authorize('publish', $motivationTemplate);

        // Accept the final JSON state before publishing — in case the user
        // publishes faster than the autosave timer fires.
        $data = $request->validate([
            'blocks' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);
        $this->versions->saveDraft(
            $motivationTemplate,
            $data['blocks'] ?? ($motivationTemplate->draftVersion()?->blocks ?? []),
            $data['settings'] ?? ($motivationTemplate->draftVersion()?->settings ?? []),
            $request->user()->id,
        );

        $version = $this->versions->publish($motivationTemplate, $request->user()->id);
        ActivityLog::record(ActivityLog::ACTION_TEMPLATE_PUBLISHED, $motivationTemplate, [
            'version' => $version->version,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'version' => $version->version,
                'published_at' => $version->published_at?->toIso8601String(),
            ]);
        }

        return redirect()->route('admin.motivation-templates.edit', $motivationTemplate)
            ->with('success', "Published version {$version->version}.");
    }

    public function restore(Request $request, MotivationTemplate $motivationTemplate, MotivationTemplateVersion $version)
    {
        Gate::authorize('update', $motivationTemplate);
        abort_unless($version->template_id === $motivationTemplate->id, 404);

        $draft = $this->versions->restore($motivationTemplate, $version, $request->user()->id);
        ActivityLog::record(ActivityLog::ACTION_TEMPLATE_RESTORED, $motivationTemplate, [
            'from_version' => $version->version,
            'draft_version' => $draft->version,
        ]);

        return redirect()->route('admin.motivation-templates.edit', $motivationTemplate)
            ->with('success', "Restored version {$version->version} as the new draft (v{$draft->version}).");
    }

    public function versions(MotivationTemplate $motivationTemplate)
    {
        Gate::authorize('view', $motivationTemplate);
        $versions = $motivationTemplate->versions()->with('publisher')->orderByDesc('version')->get();

        $withDiffs = [];
        $prev = null;
        foreach ($versions->reverse() as $version) {
            $withDiffs[$version->id] = TemplateVersionDiff::compare($prev, $version);
            $prev = $version;
        }

        return response()->json([
            'versions' => $versions->map(fn ($v) => [
                'id' => $v->id,
                'version' => $v->version,
                'is_draft' => $v->isDraft(),
                'is_published' => $v->isPublished(),
                'published_at' => $v->published_at?->toIso8601String(),
                'publisher' => $v->publisher?->name,
                'diff' => $withDiffs[$v->id] ?? ['added' => [], 'removed' => [], 'changed' => []],
            ])->values(),
        ]);
    }

    public function duplicate(Request $request, MotivationTemplate $motivationTemplate)
    {
        Gate::authorize('create', MotivationTemplate::class);

        $clone = DB::transaction(function () use ($motivationTemplate, $request) {
            $copy = MotivationTemplate::create([
                'name' => $motivationTemplate->name.' (copy)',
                'slug' => $this->uniqueSlug($motivationTemplate->slug.'-copy'),
                'category' => $motivationTemplate->category,
                'description' => $motivationTemplate->description,
                'status' => MotivationTemplate::STATUS_DRAFT,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $source = $motivationTemplate->publishedVersion ?: $motivationTemplate->latestVersion();
            if ($source) {
                MotivationTemplateVersion::create([
                    'template_id' => $copy->id,
                    'version' => 1,
                    'blocks' => array_map(function ($b) {
                        $b['id'] = (string) Str::uuid();

                        return $b;
                    }, (array) $source->blocks),
                    'settings' => (array) $source->settings,
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id,
                ]);
            }

            return $copy;
        });

        return redirect()->route('admin.motivation-templates.edit', $clone)
            ->with('success', 'Duplicated. Rename and polish before publishing.');
    }

    public function archive(Request $request, MotivationTemplate $motivationTemplate)
    {
        Gate::authorize('archive', $motivationTemplate);

        $motivationTemplate->update([
            'status' => MotivationTemplate::STATUS_ARCHIVED,
            'updated_by' => $request->user()->id,
        ]);
        ActivityLog::record(ActivityLog::ACTION_TEMPLATE_ARCHIVED, $motivationTemplate);

        return redirect()->route('admin.motivation-templates.index')
            ->with('success', 'Template archived.');
    }

    public function restoreTemplate(Request $request, MotivationTemplate $motivationTemplate)
    {
        Gate::authorize('archive', $motivationTemplate);

        $motivationTemplate->update([
            'status' => $motivationTemplate->published_version_id
                ? MotivationTemplate::STATUS_PUBLISHED
                : MotivationTemplate::STATUS_DRAFT,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.motivation-templates.edit', $motivationTemplate)
            ->with('success', 'Template un-archived.');
    }

    public function destroy(MotivationTemplate $motivationTemplate)
    {
        Gate::authorize('delete', $motivationTemplate);
        $motivationTemplate->delete();

        return redirect()->route('admin.motivation-templates.index')
            ->with('success', 'Template deleted.');
    }

    private function baseRules(?MotivationTemplate $template = null): array
    {
        return [
            'name' => 'required|string|max:200',
            'slug' => ['nullable', 'string', 'max:150', 'alpha_dash', Rule::unique('motivation_templates', 'slug')->ignore($template?->id)],
            'category' => ['required', Rule::in(array_keys(ApplicationType::CATEGORIES))],
            'description' => 'nullable|string|max:2000',
        ];
    }

    private function uniqueSlug(string $preferred, ?int $ignoreId = null): string
    {
        $base = Str::slug($preferred) ?: 'template';
        $slug = $base;
        $i = 2;
        while (MotivationTemplate::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
