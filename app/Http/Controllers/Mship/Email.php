<?php

namespace App\Http\Controllers\Mship;

use App\Models\Mship\Account;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Email extends \App\Http\Controllers\BaseController
{
    public function getEmail()
    {
        return $this->viewMake('mship.email');
    }

    public function postEmail(Request $request)
    {
        $this->validate($request, [
            'recipient' => 'required|integer|min:800000',
            'subject' => 'required|string',
            'message' => 'required|string'
        ]);

        if ($request->has('hide-email')) {
            //
        }
    }

    public function getRecipientSearch(Request $request)
    {
        $this->validate($request, [
            'query' => 'required|string|min:3',
            'type' => 'required|string|in:cid,name',
        ]);

        $searchQuery = $request->input('query');
        $searchType = $request->input('type');

        if ($searchType === 'cid') {
            try {
                $member = Account::findOrFail($searchQuery);
                $hasDivision = $member->hasState('DIVISION');
                $isActive = !$member->is_inactive;
                if ($hasDivision && $isActive) {
                    return JsonResponse::create(
                        ['match' => 'exact', 'data' => ['id' => $member->id, 'name' => $member->name]]
                    );
                } else if (!$hasDivision) {
                    return JsonResponse::create(['error' => ['Recipient is not a member of the division.']], 422);
                } else if (!$isActive) {
                    return JsonResponse::create(['error' => ['Recipient is not an active member.']], 422);
                }
            } catch (ModelNotFoundException $e) {
                return JsonResponse::create(['error' => ['Unknown recipient.']], 422);
            }
        } else if ($searchType === 'name') {
            $results = [];
            Account::where(function ($query) use ($searchQuery) {
                $query->where(DB::raw('CONCAT(name_first, \' \', name_last)'), $searchQuery)
                    ->orWhere(DB::raw('CONCAT(nickname, \' \', name_last)'), $searchQuery);
            })->orderBy('last_login', 'desc')
                ->get()
                ->each(function ($member) use (&$results) {
                    $hasDivision = $member->hasState('DIVISION');
                    $isActive = !$member->is_inactive;

                    if ($hasDivision && $isActive) {
                        $results[] = ['valid' => true, 'id' => $member->id, 'name' => $member->name];
                    } else if (!$hasDivision) {
                        $results[] = ['valid' => false, 'error' => 'Non-division member.', 'id' => $member->id, 'name' => $member->name];
                    } else if (!$isActive) {
                        $results[] = ['valid' => false, 'error' => 'Inactive member.', 'id' => $member->id, 'name' => $member->name];
                    }
                });

            $total = count($results);
            if ($total > 0) {
                return JsonResponse::create(['match' => 'partial', 'data' => $results]);
            } else {
                return JsonResponse::create(['error' => ['No matches found.']], 422);
            }
        } else {
            return JsonResponse::create(['error' => ['Unknown search type.']], 422);
        }
    }
}
