<?php

namespace App\Http\Controllers\Admin\Storage;

use App\Models\CustodyEvent;
use App\Models\StorageAgreement;
use App\Models\StorageFile;
use App\Models\StorageItem;
use App\Support\CustodyDisk;
use App\Support\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Shared write path used by both EstatesController and
 * SelfStorageController. Every intake wraps agreement + items + intake
 * events + files in a single transaction so a mid-batch failure never
 * leaves half a firearm on the register.
 *
 * Not a service-provider registered service — resolved via method
 * injection on the two controllers. Kept in the same namespace to keep
 * the storage flow self-contained.
 */
class IntakeService
{
    public function handle(array $validated, Request $request): StorageAgreement
    {
        return DB::transaction(function () use ($validated, $request) {
            $agreement = StorageAgreement::create([
                'type'         => $validated['type'],
                'status'       => 'active',
                'estate_late'  => $validated['estate_late']  ?? null,
                'bank'         => $validated['bank']         ?? null,
                'attorneys'    => $validated['attorneys']    ?? null,
                'client_name'  => $validated['client_name']  ?? null,
                'email'        => $validated['email']        ?? null,
                'storage_rate' => $validated['storage_rate'] ?? null,
                'notes'        => $validated['notes']        ?? null,
            ]);

            foreach ($validated['items'] as $index => $itemData) {
                $item = StorageItem::create([
                    'storage_agreement_id' => $agreement->id,
                    'register_book_id'     => $itemData['register_book_id'],
                    'page'                 => $itemData['page'],
                    'position'             => $itemData['position'],
                    'shelf'                => strtoupper($itemData['shelf']),
                    'tag_colour'           => strtoupper($itemData['tag_colour']),
                    'tag_number'           => (int) $itemData['tag_number'],
                    'firearm_make'         => $itemData['firearm_make'],
                    'cartridge'            => $itemData['cartridge'],
                    'serial_number'        => $itemData['serial_number'],
                    'firearm_type'         => $itemData['firearm_type'],
                    'action_type'          => $itemData['action_type'],
                    'condition_notes'      => $itemData['condition_notes'] ?? null,
                    'date_in'              => $itemData['date_in'],
                    'status'               => StorageItem::STATUS_IN_CUSTODY,
                ]);

                CustodyEvent::create([
                    'storage_item_id' => $item->id,
                    'user_id'         => $request->user()->id,
                    'event_type'      => CustodyEvent::TYPE_INTAKE,
                    'notes'           => 'Received into safe custody. Tag '.$item->tag_ref.', register '.$item->register_ref.'.',
                    'occurred_at'     => now(),
                ]);

                $this->attachFiles($request, $index, $item);
            }

            return $agreement;
        });
    }

    /**
     * Upload photos + licence for a single item to R2 and record them in
     * storage_files. Wrapped in try/catch so that an R2 hiccup doesn't
     * abort the entire batch — the item + intake event are already valid
     * without the file, and files can be re-uploaded from the item page.
     */
    private function attachFiles(Request $request, int $index, StorageItem $item): void
    {
        $bookCode = $item->book?->code ?? 'unknown';

        $photos = $request->file("items.$index.photos") ?? [];
        if (is_array($photos)) {
            foreach ($photos as $photo) {
                if (! $photo) {
                    continue;
                }
                try {
                    $bytes = ImageOptimizerBridge::optimize($photo);
                    $path = CustodyDisk::pathFor($bookCode, $item->id, 'photo', 'jpg');
                    CustodyDisk::disk()->put($path, $bytes);

                    StorageFile::create([
                        'storage_item_id' => $item->id,
                        'kind'            => StorageFile::KIND_PHOTO,
                        'disk'            => 'custody',
                        'path'            => $path,
                        'original_name'   => $photo->getClientOriginalName(),
                    ]);
                } catch (\Throwable $e) {
                    logger()->warning('Storage intake photo upload failed', [
                        'item_id' => $item->id,
                        'index'   => $index,
                        'error'   => $e->getMessage(),
                    ]);
                }
            }
        }

        $licence = $request->file("items.$index.licence");
        if ($licence) {
            try {
                $ext = strtolower($licence->getClientOriginalExtension() ?: 'bin');
                $path = CustodyDisk::pathFor($bookCode, $item->id, 'licence', $ext);
                CustodyDisk::disk()->put($path, file_get_contents($licence->getRealPath()));

                StorageFile::create([
                    'storage_item_id' => $item->id,
                    'kind'            => StorageFile::KIND_LICENCE,
                    'disk'            => 'custody',
                    'path'            => $path,
                    'original_name'   => $licence->getClientOriginalName(),
                ]);
            } catch (\Throwable $e) {
                logger()->warning('Storage intake licence upload failed', [
                    'item_id' => $item->id,
                    'index'   => $index,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }
}
