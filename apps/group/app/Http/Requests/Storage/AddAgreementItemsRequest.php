<?php

namespace App\Http\Requests\Storage;

use App\Models\StorageAgreement;

/**
 * Validates firearms being added to an agreement that already exists — a
 * client returning months later with another firearm belongs on their
 * existing agreement rather than a duplicate one.
 *
 * Unlike StoreAgreementRequest there is no `type` field to trust: the
 * agreement is resolved from the route, so the register book must match
 * that agreement's own type.
 */
class AddAgreementItemsRequest extends StorageItemsRequest
{
    protected function agreementType(): ?string
    {
        $agreement = $this->route('agreement');

        return $agreement instanceof StorageAgreement ? $agreement->type : null;
    }

    public function rules(): array
    {
        return $this->itemRules();
    }
}
