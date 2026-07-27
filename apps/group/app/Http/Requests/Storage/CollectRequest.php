<?php

namespace App\Http\Requests\Storage;

use Illuminate\Foundation\Http\FormRequest;

class CollectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'released_to_name'      => ['required', 'string', 'max:255'],
            'released_to_id_number' => ['required', 'string', 'max:50'],
            'notes'                 => ['nullable', 'string', 'max:2000'],
        ];
    }
}
