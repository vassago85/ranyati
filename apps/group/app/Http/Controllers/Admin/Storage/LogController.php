<?php

namespace App\Http\Controllers\Admin\Storage;

use App\Http\Controllers\Controller;
use App\Models\CustodyEvent;
use App\Models\RegisterBook;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = CustodyEvent::with(['item.agreement', 'item.book', 'user'])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');

        if ($book = $request->input('book')) {
            $query->whereHas('item', fn ($q) => $q->where('register_book_id', $book));
        }

        if ($type = $request->input('type')) {
            $query->where('event_type', $type);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('occurred_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('occurred_at', '<=', $to);
        }

        $events = $query->paginate(50)->withQueryString();

        return view('admin.storage.log', [
            'events' => $events,
            'books'  => RegisterBook::orderBy('code')->get(),
            'types'  => CustodyEvent::types(),
            'filters' => $request->only(['book', 'type', 'from', 'to']),
        ]);
    }
}
