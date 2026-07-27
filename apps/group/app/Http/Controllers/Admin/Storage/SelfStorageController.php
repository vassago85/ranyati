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

class SelfStorageController extends Controller
{
    public function index()
    {
        $agreements = StorageAgreement::self()
            ->withCount('items', 'activeItems')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.storage.self.index', compact('agreements'));
    }

    public function create()
    {
        return view('admin.storage.self.create', [
            'agreement' => new StorageAgreement(['type' => StorageAgreement::TYPE_SELF_STORAGE]),
            'book' => RegisterBook::forType(StorageAgreement::TYPE_SELF_STORAGE),
            'nextSlot' => optional(RegisterBook::forType(StorageAgreement::TYPE_SELF_STORAGE))?->nextOpenSlot(),
            'firearmTypes' => FirearmTypes::all(),
            'makes' => FirearmMakes::all(),
            'cartridges' => Cartridges::all(),
            'strictMakes' => FirearmMakes::hasCanonicalList(),
            'strictCartridges' => Cartridges::hasCanonicalList(),
            'defaultRate' => (float) Setting::get('storage.default_rate', 100),
        ]);
    }

    public function store(StoreAgreementRequest $request, IntakeService $service)
    {
        abort_unless($request->input('type') === StorageAgreement::TYPE_SELF_STORAGE, 422);

        $agreement = $service->handle($request->validated(), $request);

        return redirect()
            ->route('admin.storage.agreements.show', $agreement)
            ->with('success', 'Self-storage agreement created with '.$agreement->items()->count().' firearm(s).');
    }
}
