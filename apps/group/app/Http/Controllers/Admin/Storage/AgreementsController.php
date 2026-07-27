<?php

namespace App\Http\Controllers\Admin\Storage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storage\AddAgreementItemsRequest;
use App\Models\RegisterBook;
use App\Models\StorageAgreement;
use App\Support\Cartridges;
use App\Support\FirearmMakes;
use App\Support\FirearmTypes;

class AgreementsController extends Controller
{
    public function show(StorageAgreement $agreement)
    {
        $agreement->load(['items.book', 'items.events.user']);

        return view('admin.storage.agreements.show', compact('agreement'));
    }

    /**
     * Form for booking further firearms onto an existing agreement. Reuses
     * the same repeater partial as first intake, so the register-slot and
     * tag-reference behaviour an operator has already learned is identical.
     */
    public function addItems(StorageAgreement $agreement)
    {
        $book = RegisterBook::forType($agreement->type);

        return view('admin.storage.agreements.add-items', [
            'agreement' => $agreement,
            'book' => $book,
            'nextSlot' => $book?->nextOpenSlot(),
            'firearmTypes' => FirearmTypes::all(),
            'makes' => FirearmMakes::all(),
            'cartridges' => Cartridges::all(),
            'strictMakes' => FirearmMakes::hasCanonicalList(),
            'strictCartridges' => Cartridges::hasCanonicalList(),
        ]);
    }

    public function storeItems(AddAgreementItemsRequest $request, StorageAgreement $agreement, IntakeService $service)
    {
        $count = $service->addItems($agreement, $request->validated()['items'], $request);

        return redirect()
            ->route('admin.storage.agreements.show', $agreement)
            ->with('success', $count.' firearm(s) added to this agreement.');
    }
}
