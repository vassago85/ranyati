<?php

namespace App\Http\Controllers\Admin\Storage;

use App\Http\Controllers\Controller;
use App\Models\StorageAgreement;

class AgreementsController extends Controller
{
    public function show(StorageAgreement $agreement)
    {
        $agreement->load(['items.book', 'items.events.user']);

        return view('admin.storage.agreements.show', compact('agreement'));
    }
}
