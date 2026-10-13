<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MotivationTemplate;
use App\Models\TemplateAsset;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Template asset upload / delete. Shared across every template; backs the
 * asset browser the builder opens from image / title-page / signature blocks.
 */
class TemplateAssetController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', MotivationTemplate::class);

        $assets = TemplateAsset::orderBy('category')->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'assets' => $assets->map(fn ($a) => $this->asJson($a))->values(),
            ]);
        }

        return view('admin.motivations.templates.assets', [
            'assets' => $assets,
            'categories' => TemplateAsset::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', MotivationTemplate::class);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'alt' => 'nullable|string|max:200',
            'category' => ['required', Rule::in(array_keys(TemplateAsset::CATEGORIES))],
            'file' => 'required|file|mimes:jpg,jpeg,png|max:4096',
        ]);

        /** @var UploadedFile $file */
        $file = $data['file'];
        $path = $file->store('motivation-assets', 'public');

        [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];

        $asset = TemplateAsset::create([
            'name' => $data['name'],
            'category' => $data['category'],
            'alt' => $data['alt'] ?? null,
            'path' => $path,
            'mime' => $file->getMimeType(),
            'width' => $width,
            'height' => $height,
            'size' => $file->getSize(),
            'created_by' => $request->user()->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['asset' => $this->asJson($asset)]);
        }

        return back()->with('success', 'Asset uploaded.');
    }

    public function destroy(TemplateAsset $asset)
    {
        Gate::authorize('create', MotivationTemplate::class);

        Storage::disk('public')->delete($asset->path);
        $asset->delete();

        return back()->with('success', 'Asset removed.');
    }

    private function asJson(TemplateAsset $asset): array
    {
        return [
            'id' => $asset->id,
            'name' => $asset->name,
            'category' => $asset->category,
            'category_label' => $asset->categoryLabel(),
            'url' => $asset->url(),
            'mime' => $asset->mime,
            'alt' => $asset->alt,
            'width' => $asset->width,
            'height' => $asset->height,
        ];
    }
}
