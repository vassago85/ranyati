<?php

namespace App\Http\Controllers\Admin\Storage;

use App\Http\Controllers\Controller;
use App\Models\CustodyEvent;
use App\Models\RegisterBook;

class RegisterController extends Controller
{
    public function show(RegisterBook $book)
    {
        $items = $book->items()
            ->with(['agreement', 'events' => function ($q) {
                $q->where('event_type', CustodyEvent::TYPE_RELEASE);
            }])
            ->orderBy('page')
            ->orderBy('position')
            ->get();

        return view('admin.storage.register.show', compact('book', 'items'));
    }
}
