<?php

namespace App\Http\Controllers\Mship;

use App\Models\Messages\Thread;
use App\Models\Messages\Thread\Participant;
use App\Models\Messages\Thread\Post;
use App\Models\Mship\Account;
use App\Notifications\Mship\MemberEmail;
use Carbon\Carbon;
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

    protected function recipientErrors(Account $member)
    {
        $hasDivision = $member->hasState('DIVISION')
            || $member->hasState('VISITING')
            || $member->hasState('TRANSFERRING');
        $isActive = !$member->is_inactive;
        $emailKnown = !empty($member->email);

        $errors = [];
        if (!$hasDivision) {
            $errors[] = 'Recipient is not a member of or associated with the division.';
        }

        if (!$isActive) {
            $errors[] = 'Recipient is not an active member.';
        }

        if (!$emailKnown) {
            $errors[] = 'Recipient\'s email address is unknown.';
        }

        return $errors;
    }

    public function postEmail(Request $request)
    {
        $this->validate($request, [
            'recipient' => 'required|integer|min:800000|not_in:'.$this->account->id,
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:65535',
        ]);

        $recipient = Account::find($request->input('recipient'));
        if (!$recipient) {
            return back()->withErrors('Unknown recipient.');
        }

        $recipientErrors = $this->recipientErrors($recipient);
        if (!empty($recipientErrors)) {
            return back()->withErrors($recipientErrors);
        }

        $thread = Thread::create(['subject' => $request->input('subject')]);
        $thread->participants()->save($this->account, ['status' => Participant::STATUS_OWNER, 'read_at' => Carbon::now()]);
        $thread->participants()->save($recipient, ['status' => Participant::STATUS_VIEWER]);

        $post = new Post(['content' => $request->input('message')]);
        $post->thread()->associate($thread);
        $post->author()->associate($this->account);
        $post->save();

        $allowReply = true;
        if ($request->has('hide-email')) {
            $allowReply = false;
        }

        $recipient->notify(new MemberEmail($post, $allowReply));

        return back()->with('success', 'Message has been successfully added to the mail queue.');
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
            return $this->cidSearch($searchQuery);
        } elseif ($searchType === 'name') {
            return $this->nameSearch($searchQuery);
        } else {
            return JsonResponse::create(['error' => ['Unknown search type.']], 422);
        }
    }

    protected function cidSearch($searchQuery)
    {
        try {
            $member = Account::findOrFail($searchQuery);

            $recipientErrors = $this->recipientErrors($member);
            if (empty($recipientErrors)) {
                return JsonResponse::create(
                    ['match' => 'exact', 'data' => ['id' => $member->id, 'name' => $member->name]]
                );
            } else {
                return JsonResponse::create(['error' => $recipientErrors], 422);
            }
        } catch (ModelNotFoundException $e) {
            return JsonResponse::create(['error' => ['Unknown recipient.']], 422);
        }
    }

    protected function nameSearch($searchQuery)
    {
        $results = [];
        Account::where(function ($query) use ($searchQuery) {
            $query->where(DB::raw('CONCAT(name_first, \' \', name_last)'), $searchQuery)
                ->orWhere(DB::raw('CONCAT(nickname, \' \', name_last)'), $searchQuery);
        })->orderBy('last_login', 'desc')
            ->get()
            ->each(function ($member) use (&$results) {
                $recipientErrors = $this->recipientErrors($member);
                if (empty($recipientErrors)) {
                    $results[] = ['valid' => true, 'id' => $member->id, 'name' => $member->name];
                } else {
                    $results[] = ['valid' => false, 'error' => $recipientErrors[0], 'id' => $member->id, 'name' => $member->name];
                }
            });

        $total = count($results);
        if ($total > 0) {
            return JsonResponse::create(['match' => 'partial', 'data' => $results]);
        } else {
            return JsonResponse::create(['error' => ['No matches found.']], 422);
        }
    }
}
