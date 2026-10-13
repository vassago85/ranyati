<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationType;
use App\Models\MotivationTemplate;
use App\Models\TemplateAsset;
use App\Support\ActivityLog;
use App\Support\Motivations\BookSchema;
use App\Support\Motivations\ConditionEvaluator;
use App\Support\Motivations\DocumentKinds;
use App\Support\Motivations\Placeholders;
use App\Support\Motivations\QuestionSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApplicationTypeController extends Controller
{
    public function index()
    {
        return view('admin.motivations.types.index', [
            'types' => ApplicationType::withCount('blocks', 'requests')->orderBy('sort')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.motivations.types.form', [
            'type' => new ApplicationType([
                'has_motivation' => true,
                'is_active' => true,
                'questions' => [],
                'document_requirements' => [],
            ]),
            'assets' => TemplateAsset::orderBy('name')->get(),
            'documentKinds' => DocumentKinds::all(),
            'placeholderCatalogue' => Placeholders::catalogue([]),
            'conditionFields' => Placeholders::conditionFields([]),
            'templateLibrary' => $this->templateLibrary(null),
            'attachedTemplates' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $type = ApplicationType::create($this->typeAttributes($data));
        $this->syncBlocks($type, $data['blocks_json'] ?? '');
        $this->syncTemplates($type, $data['templates_json'] ?? '', $request);

        return redirect()->route('admin.application-types.edit', $type)->with('success', 'Application type created.');
    }

    public function edit(ApplicationType $applicationType)
    {
        $applicationType->load(['blocks', 'templates.publishedVersion']);

        return view('admin.motivations.types.form', [
            'type' => $applicationType,
            'assets' => TemplateAsset::orderBy('name')->get(),
            'documentKinds' => DocumentKinds::all(),
            'placeholderCatalogue' => Placeholders::catalogue($applicationType->sections()),
            'conditionFields' => Placeholders::conditionFields($applicationType->sections()),
            'templateLibrary' => $this->templateLibrary($applicationType->category),
            'attachedTemplates' => $applicationType->templates->map(fn ($t) => [
                'template_id' => $t->id,
                'name' => $t->name,
                'status' => $t->status,
                'has_published' => (bool) $t->published_version_id,
                'is_default' => (bool) $t->pivot->is_default,
                'sort' => (int) $t->pivot->sort,
                'condition' => is_string($t->pivot->condition) ? json_decode($t->pivot->condition, true) : $t->pivot->condition,
            ])->values()->all(),
        ]);
    }

    public function update(Request $request, ApplicationType $applicationType)
    {
        $data = $this->validatePayload($request, $applicationType);
        $applicationType->update($this->typeAttributes($data));
        $this->syncBlocks($applicationType, $data['blocks_json'] ?? '');
        $this->syncTemplates($applicationType, $data['templates_json'] ?? '', $request);

        return redirect()->route('admin.application-types.edit', $applicationType)->with('success', 'Changes saved.');
    }

    public function destroy(ApplicationType $applicationType)
    {
        if ($applicationType->requests()->exists()) {
            return back()->with('error', 'This type has motivation requests attached and cannot be deleted. Mark it inactive instead.');
        }

        $applicationType->delete();

        return redirect()->route('admin.application-types.index')->with('success', 'Application type deleted.');
    }

    public function clone(ApplicationType $applicationType)
    {
        $clone = $applicationType->replicate(['slug']);
        $clone->name = $applicationType->name.' (copy)';
        $clone->slug = Str::slug($clone->name).'-'.Str::lower(Str::random(4));
        $clone->is_active = false;
        $clone->save();

        foreach ($applicationType->blocks as $block) {
            $copy = $block->replicate(['application_type_id']);
            $copy->application_type_id = $clone->id;
            $copy->save();
        }

        return redirect()->route('admin.application-types.edit', $clone)->with('success', 'Cloned. Rename and tweak as needed.');
    }

    public function uploadAsset(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'file' => 'required|file|mimes:jpg,jpeg,png|max:4096',
        ]);

        $path = $data['file']->store('motivation-assets', 'public');
        $asset = TemplateAsset::create([
            'name' => $data['name'],
            'path' => $path,
            'mime' => $data['file']->getMimeType(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['asset' => $asset]);
        }

        return back()->with('success', 'Asset uploaded.');
    }

    public function destroyAsset(TemplateAsset $asset)
    {
        Storage::disk('public')->delete($asset->path);
        $asset->delete();

        return back()->with('success', 'Asset removed.');
    }

    private function validatePayload(Request $request, ?ApplicationType $existing = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:200',
            'slug' => ['required', 'string', 'max:120', 'alpha_dash', Rule::unique('application_types', 'slug')->ignore($existing?->id)],
            'category' => ['required', Rule::in(array_keys(ApplicationType::CATEGORIES))],
            'description' => 'nullable|string|max:1000',
            'has_motivation' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort' => 'nullable|integer|min:0|max:9999',
            'questions_json' => 'nullable|string',
            'requirements_json' => 'nullable|string',
            'blocks_json' => 'nullable|string',
            'templates_json' => 'nullable|string',
        ]);
    }

    /**
     * Build the "library" dataset the Templates tab uses to let the admin
     * pick templates to attach. We prefer templates in the same category
     * but include everything else below them for flexibility.
     *
     * @return array<int, array<string, mixed>>
     */
    private function templateLibrary(?string $preferCategory): array
    {
        return MotivationTemplate::query()
            ->whereIn('status', [MotivationTemplate::STATUS_DRAFT, MotivationTemplate::STATUS_PUBLISHED])
            ->orderByRaw('CASE WHEN category = ? THEN 0 ELSE 1 END', [$preferCategory ?? ''])
            ->orderBy('name')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'category' => $t->category,
                'category_label' => $t->categoryLabel(),
                'status' => $t->status,
                'has_published' => (bool) $t->published_version_id,
            ])->values()->all();
    }

    private function syncTemplates(ApplicationType $type, string $json, Request $request): void
    {
        $rows = $this->decode($json);
        $sync = [];
        $sort = 0;
        $sawDefault = false;

        foreach ($rows as $row) {
            $templateId = (int) ($row['template_id'] ?? 0);
            if ($templateId <= 0 || ! MotivationTemplate::whereKey($templateId)->exists()) {
                continue;
            }
            $isDefault = (bool) ($row['is_default'] ?? false) && ! $sawDefault;
            if ($isDefault) {
                $sawDefault = true;
            }
            $condition = ConditionEvaluator::normalize($row['condition'] ?? null);
            $sync[$templateId] = [
                'is_default' => $isDefault,
                'sort' => $sort++,
                // Store as JSON string so the pivot read-back is predictable.
                'condition' => $condition ? json_encode($condition) : null,
            ];
        }

        $before = $type->templates()->pluck('motivation_templates.id')->all();
        $type->templates()->sync($sync);
        $after = array_keys($sync);

        if ($before !== $after || $type->wasRecentlyCreated) {
            ActivityLog::record(
                ActivityLog::ACTION_TEMPLATE_ASSIGNED,
                $type,
                [
                    'before' => $before,
                    'after' => $after,
                    'default' => $sawDefault ? array_key_first(array_filter($sync, fn ($r) => $r['is_default'])) : null,
                ],
                $request->user()?->id,
            );
        }
    }

    private function typeAttributes(array $data): array
    {
        $sections = QuestionSchema::normalizeSections($this->decode($data['questions_json'] ?? null));
        $requirements = BookSchema::normalizeRequirements($this->decode($data['requirements_json'] ?? null));

        return [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'has_motivation' => (bool) ($data['has_motivation'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'sort' => (int) ($data['sort'] ?? 0),
            'questions' => $sections,
            'document_requirements' => $requirements,
        ];
    }

    private function syncBlocks(ApplicationType $type, string $json): void
    {
        $blocks = BookSchema::normalizeBlocks($this->decode($json));

        DB::transaction(function () use ($type, $blocks) {
            $type->blocks()->delete();
            foreach ($blocks as $i => $def) {
                $type->blocks()->create([
                    'sort' => $i,
                    'kind' => $def['kind'],
                    'settings' => $def['settings'],
                    'condition' => $def['condition'],
                ]);
            }
        });
    }

    private function decode(mixed $value): array
    {
        if (! is_string($value) || $value === '') {
            return [];
        }
        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function sampleCondition(): array
    {
        return [
            'operators' => ConditionEvaluator::OPERATORS,
            'unary' => ConditionEvaluator::UNARY,
        ];
    }
}
