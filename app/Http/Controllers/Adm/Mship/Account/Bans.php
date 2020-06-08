<?php

namespace App\Http\Controllers\Adm\Mship\Account;

use App\Http\Controllers\Adm\AdmController;
use App\Http\Requests\Mship\Account\Ban\CommentRequest;
use App\Http\Requests\Mship\Account\Ban\CreateRequest;
use App\Http\Requests\Mship\Account\Ban\ModifyRequest;
use App\Http\Requests\Mship\Account\Ban\RepealRequest;
use App\Models\Mship\Account as AccountData;
use App\Models\Mship\Account\Ban as BanData;
use App\Models\Mship\Ban\Reason;
use App\Models\Mship\Note\Type;
use App\Notifications\Mship\BanCreated;
use App\Notifications\Mship\BanModified;
use App\Notifications\Mship\BanRepealed;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
use Redirect;

class Bans extends AdmController
{
    public function getBans()
    {
        $bans = BanData::isLocal()
            ->orderByDesc('created_at')
            ->paginate(15);

        return $this->viewMake('adm.mship.account.ban.index')
            ->with('bans', $bans);
    }

    /*
     * Additions
     */

    public function postBanAdd(CreateRequest $request, AccountData $mshipAccount)
    {
        if (! $mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        if ($mshipAccount->is_banned) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'bans'])
                ->withError('You are not able to ban a member that is already banned.');
        }

        $banReason = Reason::find(Request::input('ban_reason_id'));

        // Create the user's ban
        $ban = $mshipAccount->addBan(
            $banReason,
            Request::input('ban_reason_extra'),
            Request::input('ban_note_content'),
            $this->account->id
        );

        $mshipAccount->notify(new BanCreated($ban));

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'bans', $ban->id])
            ->withSuccess('You have successfully banned this member.');
    }

    /*
     * Repeals
     */

    public function getBanRepeal(AccountData\Ban $ban)
    {
        if (! $ban) {
            // TODO: Could got to the master ban list?
            return Redirect::route('adm.mship.account.index');
        }

        $this->setTitle('Ban Repeal');

        return $this->viewMake('adm.mship.account.ban.repeal')
            ->with('ban', $ban);
    }

    public function postBanRepeal(RepealRequest $request, AccountData\Ban $ban)
    {
        if (! $ban) {
            // TODO: Could got to the master ban list?
            return Redirect::route('adm.mship.account.index');
        }

        // Attach the note.
        $note = $ban->account->addNote(Type::isShortCode('discipline')->first(), Request::input('reason'), Auth::getUser());
        $ban->notes()->save($note);
        $ban->repeal();

        $ban->account->notify(new BanRepealed($ban));

        return Redirect::route('adm.mship.account.details', [$ban->account_id, 'bans', $ban->id])
            ->withSuccess('Ban has been repealed.');
    }

    /*
     * Modifications
     */

    public function getBanModify(AccountData\Ban $ban)
    {
        if (! $ban) {
            // TODO: Could got to the master ban list?
            return Redirect::route('adm.mship.account.index');
        }

        $this->setTitle('Ban Modification');

        return $this->viewMake('adm.mship.account.ban.modify')
            ->with('ban', $ban);
    }

    public function postBanModify(ModifyRequest $request, AccountData\Ban $ban)
    {
        if (! $ban) {
            // TODO: Could got to the master ban list?
            return Redirect::route('adm.mship.account.index');
        }

        $period_finish = Carbon::parse(Request::input('finish_date').' '.Request::input('finish_time'), 'UTC');
        $max_timestamp = Carbon::create(2038, 1, 1, 0, 0, 0);
        if ($period_finish->gt($max_timestamp)) {
            $period_finish = $max_timestamp;
        }

        if ($ban->period_finish->eq($period_finish)) {
            return Redirect::back()->withInput()->withError("You didn't change the ban period.");
        }

        if ($ban->period_finish->gt($period_finish)) {
            $noteComment = 'Ban has been reduced from '.$ban->period_finish->toDateTimeString().".\n";
        } else {
            $noteComment = 'Ban has been extended from '.$ban->period_finish->toDateTimeString().".\n";
        }
        $noteComment .= 'New finish: '.$period_finish->toDateTimeString()."\n";
        $noteComment .= Request::input('note');

        // Attach the note.
        $note = $ban->account->addNote(Type::isShortCode('discipline')->first(), $noteComment, Auth::getUser());
        $ban->notes()->save($note);

        // Modify the ban
        $ban->reason_extra = $ban->reason_extra."\n".Request::input('reason_extra');
        $ban->period_finish = $period_finish;
        $ban->save();

        $ban->account->notify(new BanModified($ban));

        return Redirect::route('adm.mship.account.details', [$ban->account_id, 'bans', $ban->id])
            ->withSuccess('This ban has been modified.');
    }

    /*
     * Comments
     */

    public function getBanComment(AccountData\Ban $ban)
    {
        if (! $ban) {
            // TODO: Could got to the master ban list?
            return Redirect::route('adm.mship.account.index');
        }

        $this->setTitle('Ban Comment');

        return $this->viewMake('adm.mship.account.ban.comment')
            ->with('ban', $ban);
    }

    public function postBanComment(CommentRequest $request, AccountData\Ban $ban)
    {
        if (! $ban) {
            // TODO: Could got to the master ban list?
            return Redirect::route('adm.mship.account.index');
        }

        // Attach the note.
        $note = $ban->account->addNote(
            Type::isShortCode('discipline')->first(),
            Request::input('comment'),
            Auth::getUser()
        );
        $ban->notes()->save($note);

        return Redirect::route('adm.mship.account.details', [$ban->account_id, 'bans', $ban->id])
            ->withSuccess('Your comment for this ban has been noted.');
    }
}
