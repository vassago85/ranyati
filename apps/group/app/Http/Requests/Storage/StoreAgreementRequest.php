<?php

namespace App\Http\Requests\Storage;

use App\Models\StorageAgreement;
use App\Support\SaBanks;
use Illuminate\Validation\Rule;

/**
 * Validates the "create an agreement + N firearms in one submit" intake
 * flow used by both the Deceased Estates and Self Storage sections.
 *
 * The agreement type is fixed by the route (estates vs self), so it
 * arrives as a hidden field the operator can't change on the form.
 *
 * Item-level rules live in StorageItemsRequest, shared with the flow that
 * adds further firearms to an agreement that already exists.
 */
class StoreAgreementRequest extends StorageItemsRequest
{
    protected function agreementType(): ?string
    {
        return $this->input('type');
    }

    public function rules(): array
    {
        $isEstate = $this->agreementType() === StorageAgreement::TYPE_DECEASED_ESTATE;
        $isSelf   = $this->agreementType() === StorageAgreement::TYPE_SELF_STORAGE;

        return array_merge([
            'type'  => ['required', Rule::in([StorageAgreement::TYPE_DECEASED_ESTATE, StorageAgreement::TYPE_SELF_STORAGE])],
            'notes' => ['nullable', 'string', 'max:5000'],

            // Estate fields
            'estate_late' => [$isEstate ? 'required' : 'nullable', 'string', 'max:255'],
            'bank'        => ['nullable', 'string', 'max:255', function ($attr, $value, $fail) {
                if (! SaBanks::isValid($value)) {
                    $fail('Selected bank is not on the supported list.');
                }
            }],
            'attorneys'   => ['nullable', 'string', 'max:255'],

            // Self-storage fields
            'client_name'  => [$isSelf ? 'required' : 'nullable', 'string', 'max:255'],
            'email'        => [$isSelf ? 'required' : 'nullable', 'email', 'max:255'],
            'storage_rate' => [$isSelf ? 'required' : 'nullable', 'numeric', 'min:0', 'max:99999.99'],
        ], $this->itemRules());
    }
}
