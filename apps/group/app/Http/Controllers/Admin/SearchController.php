<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MotivationRequest;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Global admin search backing the topbar search field (Phase 2,
 * mockup 01). Searches the Motivations area across:
 *
 *   - clients (users with role=client) by name, email, phone
 *   - client profiles by id_number, cell, email
 *   - motivation requests by reference (RM-XXXXXX)
 *
 * The query is intentionally kept simple: a small `LIKE` search per
 * surface. Phase 5 unifies clients into a single table, which will
 * replace the clientProfile + user double-join with a single `clients`
 * query. Until then, the two JOINs cover the live data.
 */
class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $raw = trim((string) $request->query('q', ''));
        // Normalise the query up front: strip spaces, dashes and the
        // leading "RM-" so an admin can paste "RM-000012" or "12" or
        // "0820001234" and hit the same results.
        $qNormalised = preg_replace('/[\s\-]+/', '', $raw) ?? '';
        $qDigits = preg_replace('/\D+/', '', $qNormalised) ?? '';

        $clients = collect();
        $motivations = collect();

        if ($raw !== '' && mb_strlen($raw) >= 2) {
            // ----- Clients ------------------------------------------
            $like = '%'.$raw.'%';
            $digitLike = $qDigits !== '' ? '%'.$qDigits.'%' : null;

            $clients = User::query()
                ->where('role', User::ROLE_CLIENT)
                ->where(function ($q) use ($like, $digitLike) {
                    $q->where('users.name', 'like', $like)
                        ->orWhere('users.email', 'like', $like);
                    if ($digitLike) {
                        $q->orWhereHas('clientProfile', function ($p) use ($digitLike) {
                            $p->where('id_number', 'like', $digitLike)
                                ->orWhere('cell', 'like', $digitLike);
                        });
                    }
                })
                ->with('clientProfile')
                ->orderBy('name')
                ->limit(20)
                ->get();

            // ----- Motivations by reference ------------------------
            // Accept "RM-000012", "RM12", "000012", or just "12".
            $refDigits = ltrim($qDigits, '0');
            $motivationsQuery = MotivationRequest::query()
                ->with(['user.clientProfile', 'applicationType']);
            if ($refDigits !== '') {
                $motivationsQuery->where('id', $refDigits);
            } else {
                // No digits in the query — look up by the client's name
                // or email so the "most useful hit" still appears.
                $motivationsQuery->whereHas('user', function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            }
            $motivations = $motivationsQuery->orderByDesc('id')->limit(20)->get();
        }

        return view('admin.search', [
            'q' => $raw,
            'clients' => $clients,
            'motivations' => $motivations,
            'minLength' => 2,
        ]);
    }
}
