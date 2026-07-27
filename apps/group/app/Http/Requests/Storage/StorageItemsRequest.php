<?php

namespace App\Http\Requests\Storage;

use App\Models\RegisterBook;
use App\Models\StorageItem;
use App\Support\Cartridges;
use App\Support\FirearmMakes;
use App\Support\FirearmTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Shared validation for any submit that books firearms onto a register —
 * whether that is a brand new agreement (StoreAgreementRequest) or extra
 * firearms added to an existing one (AddAgreementItemsRequest).
 *
 * These rules are deliberately shared rather than duplicated: register slots
 * and physical tags are legal references, so both entry points have to
 * enforce exactly the same uniqueness guarantees. A divergence between the
 * two would be a compliance bug, not just an inconsistency.
 */
abstract class StorageItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * The agreement type these items belong to. Used to reject a register
     * book belonging to the other book series (D01 vs S01).
     */
    abstract protected function agreementType(): ?string;

    /**
     * Validation rules for the items[] array shared by both entry points.
     */
    protected function itemRules(): array
    {
        $rules = [
            'items'   => ['required', 'array', 'min:1', 'max:100'],
            'items.*' => ['array'],

            'items.*.register_book_id' => ['required', 'integer', 'exists:register_books,id'],
            'items.*.page'             => ['required', 'integer', 'min:1', 'max:101'],
            'items.*.position'         => ['required', 'integer', 'min:1', 'max:26'],

            'items.*.shelf'      => ['required', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],
            'items.*.tag_colour' => ['required', 'string', 'size:1', 'regex:/^[A-Za-z]$/'],
            'items.*.tag_number' => ['required', 'integer', 'min:1', 'max:1000'],

            'items.*.firearm_make'    => ['required', 'string', 'max:255'],
            'items.*.cartridge'       => ['required', 'string', 'max:255'],
            'items.*.serial_number'   => ['required', 'string', 'max:255'],
            'items.*.firearm_type'    => ['required', Rule::in(FirearmTypes::typeKeys())],
            'items.*.action_type'     => ['required', 'string', 'max:255'],
            'items.*.condition_notes' => ['nullable', 'string', 'max:2000'],
            'items.*.date_in'         => ['required', 'date'],

            // Files (optional, per item)
            'items.*.photos'   => ['nullable', 'array', 'max:10'],
            'items.*.photos.*' => ['image', 'max:15360'],
            'items.*.licence'  => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:15360'],
        ];

        // If the reference lists were populated from NRAPA we tighten the
        // validation to the canonical set. Until then, keep as free text
        // so ops isn't blocked on the paste job.
        if (FirearmMakes::hasCanonicalList()) {
            $rules['items.*.firearm_make'] = ['required', Rule::in(FirearmMakes::all())];
        }
        if (Cartridges::hasCanonicalList()) {
            $rules['items.*.cartridge'] = ['required', Rule::in(Cartridges::all())];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $items = $this->input('items', []);
            if (! is_array($items)) {
                return;
            }

            $agreementType = $this->agreementType();

            $slotSeen = [];
            $tagSeen  = [];

            foreach ($items as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }

                // firearm_type + action_type must be consistent
                $type   = $item['firearm_type'] ?? null;
                $action = $item['action_type']  ?? null;
                if ($type && $action && ! FirearmTypes::isValid($type, $action)) {
                    $v->errors()->add(
                        "items.$i.action_type",
                        'Action type "'.$action.'" is not valid for '.FirearmTypes::typeLabel($type).'.'
                    );
                }

                // Register book type must match the agreement type
                $bookId = $item['register_book_id'] ?? null;
                if ($bookId) {
                    $book = RegisterBook::find($bookId);
                    if ($book && $book->type !== $agreementType) {
                        $v->errors()->add(
                            "items.$i.register_book_id",
                            'Selected register book ('.$book->code.') is for '.$book->type.', not the current '.$agreementType.' intake.'
                        );
                    }
                    if ($book && (int) ($item['page'] ?? 0) > $book->pages) {
                        $v->errors()->add(
                            "items.$i.page",
                            'Page number is beyond the last page of '.$book->code.' (max '.$book->pages.').'
                        );
                    }
                    if ($book && (int) ($item['position'] ?? 0) > $book->positions_per_page) {
                        $v->errors()->add(
                            "items.$i.position",
                            'Position is beyond the last position on a page of '.$book->code.' (max '.$book->positions_per_page.').'
                        );
                    }
                }

                // Within-form register-slot uniqueness — two items on the same
                // submit can't claim the same slot.
                $slotKey = sprintf('%s:%s:%s', $bookId, $item['page'] ?? '', $item['position'] ?? '');
                if (isset($slotSeen[$slotKey])) {
                    $v->errors()->add(
                        "items.$i.position",
                        'Duplicate register slot in this intake — item #'.($slotSeen[$slotKey] + 1).' already uses this book/page/position.'
                    );
                } else {
                    $slotSeen[$slotKey] = $i;
                }

                // DB-level register-slot uniqueness — a page/position on a
                // book is a permanent legal reference.
                if ($bookId && isset($item['page'], $item['position'])) {
                    $exists = StorageItem::where('register_book_id', $bookId)
                        ->where('page', $item['page'])
                        ->where('position', $item['position'])
                        ->exists();
                    if ($exists) {
                        $v->errors()->add(
                            "items.$i.position",
                            'That book/page/position is already recorded against another firearm. Register slots are never reused.'
                        );
                    }
                }

                // Within-form tag uniqueness
                $tagKey = strtoupper(($item['shelf'] ?? '').'-'.($item['tag_colour'] ?? '').'-'.($item['tag_number'] ?? ''));
                if (isset($tagSeen[$tagKey])) {
                    $v->errors()->add(
                        "items.$i.tag_number",
                        'Duplicate physical tag in this intake — item #'.($tagSeen[$tagKey] + 1).' already uses tag '.$tagKey.'.'
                    );
                } else {
                    $tagSeen[$tagKey] = $i;
                }

                // Active-tag uniqueness — a tag combination is only free
                // if no currently in-custody firearm holds it.
                if (isset($item['shelf'], $item['tag_colour'], $item['tag_number'])) {
                    $inUse = StorageItem::where('status', StorageItem::STATUS_IN_CUSTODY)
                        ->whereRaw('UPPER(shelf) = ?', [strtoupper((string) $item['shelf'])])
                        ->whereRaw('UPPER(tag_colour) = ?', [strtoupper((string) $item['tag_colour'])])
                        ->where('tag_number', (int) $item['tag_number'])
                        ->exists();
                    if ($inUse) {
                        $v->errors()->add(
                            "items.$i.tag_number",
                            'Tag '.$tagKey.' is already in use by another in-custody firearm. Choose an unused shelf/colour/number.'
                        );
                    }
                }
            }
        });
    }

    /**
     * Normalise casing on shelf/tag_colour so uniqueness comparisons stay
     * consistent regardless of what the operator typed.
     */
    protected function prepareForValidation(): void
    {
        $items = $this->input('items');
        if (! is_array($items)) {
            return;
        }

        foreach ($items as $i => $item) {
            if (! is_array($item)) {
                continue;
            }
            if (isset($item['shelf'])) {
                $items[$i]['shelf'] = strtoupper(trim((string) $item['shelf']));
            }
            if (isset($item['tag_colour'])) {
                $items[$i]['tag_colour'] = strtoupper(trim((string) $item['tag_colour']));
            }
        }

        $this->merge(['items' => $items]);
    }
}
