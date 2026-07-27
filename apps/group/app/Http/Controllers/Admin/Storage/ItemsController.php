<?php

namespace App\Http\Controllers\Admin\Storage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storage\CollectRequest;
use App\Models\CustodyEvent;
use App\Models\StorageItem;
use App\Support\CustodyDisk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ItemsController extends Controller
{
    public function show(StorageItem $item)
    {
        $item->load(['agreement', 'book', 'events.user', 'files']);

        $fee = $item->calculateFee();

        return view('admin.storage.items.show', compact('item', 'fee'));
    }

    public function collectForm(StorageItem $item)
    {
        abort_unless($item->isInCustody(), 404);
        $item->load(['agreement', 'book']);

        $fee = $item->calculateFee();

        return view('admin.storage.items.collect', compact('item', 'fee'));
    }

    public function collect(CollectRequest $request, StorageItem $item)
    {
        abort_unless($item->isInCustody(), 404);
        $item->load(['agreement', 'book']);

        $fee = $item->calculateFee();
        $data = $request->validated();

        DB::transaction(function () use ($item, $data, $request) {
            $item->update(['status' => StorageItem::STATUS_RELEASED]);

            CustodyEvent::create([
                'storage_item_id'       => $item->id,
                'user_id'               => $request->user()->id,
                'event_type'            => CustodyEvent::TYPE_RELEASE,
                'notes'                 => $data['notes'] ?? null,
                'released_to_name'      => $data['released_to_name'],
                'released_to_id_number' => $data['released_to_id_number'],
                'occurred_at'           => now(),
            ]);
        });

        // Self-storage clients get a confirmation email; estates don't in
        // v1. Wrapped so mail delivery hiccups never undo the release.
        if ($item->agreement?->isSelfStorage() && $item->agreement?->email) {
            try {
                Mail::send(new \App\Mail\StorageCollectionConfirmation(
                    item: $item->fresh(['agreement', 'book']),
                    collectedByName: $data['released_to_name'],
                    collectedByIdNumber: $data['released_to_id_number'],
                    feeAmount: $fee,
                ));
            } catch (\Throwable $e) {
                Log::warning('Storage collection confirmation email failed', [
                    'item_id' => $item->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('admin.storage.items.show', $item)
            ->with('success', 'Firearm marked as released. Tag '.$item->tag_ref.' is now free.');
    }

    /**
     * Record an inspection / internal transfer / note as an append-only
     * custody event. Uses ->store() semantics because event_type is
     * user-selectable, but we lock down which types are allowed here so
     * "intake" and "release" only ever come from their dedicated flows.
     */
    public function storeEvent(Request $request, StorageItem $item)
    {
        $validated = $request->validate([
            'event_type' => ['required', Rule::in([
                CustodyEvent::TYPE_INSPECTION,
                CustodyEvent::TYPE_TRANSFER_INTERNAL,
                CustodyEvent::TYPE_CORRECTION,
                CustodyEvent::TYPE_NOTE,
            ])],
            'notes'   => ['required', 'string', 'max:2000'],
            'old_tag' => ['nullable', 'string', 'max:20'],
            'new_tag' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($item, $validated, $request) {
            CustodyEvent::create([
                'storage_item_id' => $item->id,
                'user_id'         => $request->user()->id,
                'event_type'      => $validated['event_type'],
                'notes'           => $validated['notes'],
                'old_tag'         => $validated['old_tag'] ?? null,
                'new_tag'         => $validated['new_tag'] ?? null,
                'occurred_at'     => now(),
            ]);

            // If the event is a physical-tag transfer the item's tag
            // fields need to move too, so future scans of the new tag
            // resolve to this item. Wrapped in the same txn as the event.
            if ($validated['event_type'] === CustodyEvent::TYPE_TRANSFER_INTERNAL
                && ! empty($validated['new_tag'])
                && preg_match('/^([A-Za-z]{2})-([A-Za-z])-(\d{1,4})$/', $validated['new_tag'], $m)) {
                $item->update([
                    'shelf'      => strtoupper($m[1]),
                    'tag_colour' => strtoupper($m[2]),
                    'tag_number' => (int) $m[3],
                ]);
            }
        });

        return back()->with('success', 'Custody event recorded.');
    }

    /**
     * Presigned R2 download for a single file on this item. R2 bucket is
     * private, so all downloads go through this admin-only route with a
     * short-lived signed URL.
     */
    public function downloadFile(StorageItem $item, \App\Models\StorageFile $file)
    {
        abort_unless($file->storage_item_id === $item->id, 404);

        return redirect()->away($file->temporaryUrl());
    }
}
