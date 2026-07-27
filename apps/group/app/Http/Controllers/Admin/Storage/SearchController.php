<?php

namespace App\Http\Controllers\Admin\Storage;

use App\Http\Controllers\Controller;
use App\Models\StorageItem;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $items = collect();
        $parsed = null;

        if ($q !== '') {
            $query = StorageItem::with(['agreement', 'book']);

            // "AB-R-0042" tag ref shortcut
            if (preg_match('/^([A-Za-z]{2})-([A-Za-z])-(\d{1,4})$/', $q, $m)) {
                $parsed = 'tag';
                $query->whereRaw('UPPER(shelf) = ?', [strtoupper($m[1])])
                      ->whereRaw('UPPER(tag_colour) = ?', [strtoupper($m[2])])
                      ->where('tag_number', (int) $m[3]);
            }
            // "D01-P045-13" register ref shortcut
            elseif (preg_match('/^([A-Za-z]\d{2})-P?(\d{1,3})-(\d{1,2})$/i', $q, $m)) {
                $parsed = 'register';
                $query->whereHas('book', fn ($b) => $b->where('code', strtoupper($m[1])))
                      ->where('page', (int) $m[2])
                      ->where('position', (int) $m[3]);
            }
            else {
                $like = '%'.$q.'%';
                $query->where(function ($sub) use ($like) {
                    $sub->where('serial_number', 'like', $like)
                        ->orWhereHas('agreement', function ($a) use ($like) {
                            $a->where('client_name', 'like', $like)
                              ->orWhere('estate_late', 'like', $like)
                              ->orWhere('email', 'like', $like);
                        });
                });
            }

            $items = $query->orderByDesc('id')->limit(50)->get();
        }

        return view('admin.storage.search', compact('items', 'q', 'parsed'));
    }
}
