<?php

namespace App\Http\Controllers\Admin\Storage;

use App\Http\Controllers\Controller;
use App\Models\CustodyEvent;
use App\Models\RegisterBook;
use App\Models\StorageItem;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $books = RegisterBook::orderBy('code')->get();

        $stats = $books->map(function (RegisterBook $book) {
            $inCustody = StorageItem::inCustody()->where('register_book_id', $book->id)->count();
            $totalUsed = $book->occupiedSlots();

            return [
                'book' => $book,
                'in_custody' => $inCustody,
                'released' => $totalUsed - $inCustody,
                'total_slots' => $book->totalSlots(),
                'occupied_slots' => $totalUsed,
                'remaining_slots' => $book->remainingSlots(),
            ];
        });

        $oldest = StorageItem::inCustody()
            ->with(['agreement', 'book'])
            ->orderBy('date_in')
            ->limit(10)
            ->get();

        // Tag utilisation by colour, active items only.
        $tagUsage = StorageItem::inCustody()
            ->selectRaw('UPPER(tag_colour) as colour, COUNT(*) as in_use')
            ->groupBy('colour')
            ->orderBy('colour')
            ->get();

        $recentEvents = CustodyEvent::with(['item.agreement', 'item.book', 'user'])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return view('admin.storage.dashboard', compact('stats', 'oldest', 'tagUsage', 'recentEvents'));
    }
}
