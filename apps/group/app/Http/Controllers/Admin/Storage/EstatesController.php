<?php

namespace App\Http\Controllers\Admin\Storage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storage\StoreAgreementRequest;
use App\Models\RegisterBook;
use App\Models\Setting;
use App\Models\StorageAgreement;
use App\Support\Cartridges;
use App\Support\FirearmMakes;
use App\Support\FirearmTypes;
use App\Support\SaBanks;

/**
 * Deceased-estates section of the Storage module. Same shape as the
 * SelfStorageController (both delegate to IntakeService for the write
 * path); we keep them as two controllers so the two brand nav items and
 * the type-specific view templates read cleanly.
 */
class EstatesController extends Controller
{
    public function index()
    {
        $agreements = StorageAgreement::estates()
            ->withCount('items', 'activeItems')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.storage.estates.index', compact('agreements'));
    }

    public function create()
    {
        return view('admin.storage.estates.create', [
            'agreement' => new StorageAgreement(['type' => StorageAgreement::TYPE_DECEASED_ESTATE]),
            'book' => RegisterBook::forType(StorageAgreement::TYPE_DECEASED_ESTATE),
            'nextSlot' => optional(RegisterBook::forType(StorageAgreement::TYPE_DECEASED_ESTATE))?->nextOpenSlot(),
            'firearmTypes' => FirearmTypes::all(),
            'banks' => SaBanks::all(),
            'makes' => FirearmMakes::all(),
            'cartridges' => Cartridges::all(),
            'strictMakes' => FirearmMakes::hasCanonicalList(),
            'strictCartridges' => Cartridges::hasCanonicalList(),
            'defaultRate' => (float) Setting::get('storage.default_rate', 100),
        ]);
    }

    public function store(StoreAgreementRequest $request, IntakeService $service)
    {
        abort_unless($request->input('type') === StorageAgreement::TYPE_DECEASED_ESTATE, 422);

        $agreement = $service->handle($request->validated(), $request);

        return redirect()
            ->route('admin.storage.agreements.show', $agreement)
            ->with('success', 'Estate agreement created with '.$agreement->items()->count().' firearm(s).');
    }
}
