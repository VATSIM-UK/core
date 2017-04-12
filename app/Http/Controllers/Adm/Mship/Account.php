<?php

namespace App\Http\Controllers\Adm\Mship;

use DB;
use URL;
use Auth;
use Input;
use Session;
use Redirect;
use App\Models\Mship\State;
use App\Models\Mship\Note\Type;
use App\Models\Mship\Ban\Reason;
use Illuminate\Support\Collection;
use App\Models\Mship\Role as RoleData;
use App\Notifications\Mship\BanCreated;
use App\Notifications\Mship\BanModified;
use App\Notifications\Mship\BanRepealed;
use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account as AccountData;
use App\Models\Mship\Note\Type as NoteTypeData;
use App\Http\Requests\Mship\Account\Ban\CreateRequest;
use App\Http\Requests\Mship\Account\Ban\ModifyRequest;
use App\Http\Requests\Mship\Account\Ban\RepealRequest;
use App\Http\Requests\Mship\Account\Ban\CommentRequest;

class Account extends AdmController
{
    public function getIndex($scope = 'division')
    {
        // Sorting and searching!
        $sortBy = in_array(
            Input::get('sort_by'),
            ['id', 'name_first', 'name_last']
        ) ? Input::get('sort_by') : 'id';
        $sortDir = in_array(Input::get('sort_dir'), ['ASC', 'DESC']) ? Input::get('sort_dir') : 'ASC';

        // ORM it all!
        $memberSearch = AccountData::isNotSystem()
                                   ->orderBy($sortBy, $sortDir)
                                   ->with('qualifications')
                                   ->with('states')
                                   ->with('bans')
                                   ->with('secondaryEmails');

        switch ($scope) {
            case 'active':
                $memberSearch = $memberSearch->where('status', '=', 0);
                break;

            case 'inactive':
                $memberSearch = $memberSearch->where(
                    DB::raw('status&'.AccountData::STATUS_INACTIVE),
                    '=',
                    AccountData::STATUS_INACTIVE
                );
                break;

            case 'suspended':
                $memberSearch = $memberSearch->where(
                    DB::raw('status&'.AccountData::STATUS_NETWORK_SUSPENDED),
                    '=',
                    AccountData::STATUS_NETWORK_SUSPENDED
                );
                break;

            case 'nondivision':
                $nonDivIds = collect(DB::table('mship_account_state')
                                       ->whereNull('end_at')
                                       ->where('state_id', '!=', State::findByCode('DIVISION')->id)
                                       ->select('account_id')
                                       ->get())->pluck('account_id')->toArray();

                $memberSearch = AccountData::whereIn('id', $nonDivIds)
                                           ->with('qualifications')
                                           ->with('states')
                                           ->with('bans')
                                           ->with('secondaryEmails');
                break;

            case 'division':
            default:
                $divIds = collect(DB::table('mship_account_state')
                                    ->whereNull('end_at')
                                    ->where('state_id', '=', State::findByCode('DIVISION')->id)
                                    ->select('account_id')
                                    ->get())->pluck('account_id')->toArray();

                $memberSearch = AccountData::whereIn('id', $divIds)
                                           ->with('qualifications')
                                           ->with('states')
                                           ->with('bans')
                                           ->with('secondaryEmails');
                break;
        }

        $memberSearch = $memberSearch->paginate(50);

        // Now we need to prepare the collection as a result for the view!
        $members = new Collection();
        foreach ($memberSearch as $m) {
            $members->prepend(($m->account ? $m->account : $m));
        }
        $members = $members->reverse();

        return $this->viewMake('adm.mship.account.index')
                    ->with('members', $members)
                    ->with('membersQuery', $memberSearch)
                    ->with('sortBy', $sortBy)
                    ->with('sortDir', $sortDir)
                    ->with('sortDirSwitch', ($sortDir == 'DESC' ? 'ASC' : 'DESC'));
    }

    public function getDetail(AccountData $account, $tab = 'basic', $tabId = 0)
    {
        if (!$account or $account->is_system) {
            return Redirect::route('adm.mship.account.index');
        }

        // Do they have permission to view their own profile?
        // This is to prevent people doing silly things....
        if ($this->account->id == $account->id && !$this->account->hasPermission('adm/mship/account/own')) {
            return Redirect::route('adm.mship.account.index')
                           ->withError('You cannot view or manage your own profile.');
        }

        // Lazy eager loading
        $account->load(
            'bans',
            'bans.banner',
            'bans.reason',
            'bans.notes',
            'bans.notes.writer',
            'notes',
            'notes.type',
            'notes.writer',
            'notes.attachment',
            'dataChanges',
            'roles',
            'roles.permissions',
            'qualifications',
            'states',
            'secondaryEmails',
            'feedback'
        );

        // Get all possible roles!
        $availableRoles = RoleData::all()
                                  ->diff($account->roles);

        // Get all ban reasons.
        $banReasons = Reason::all();

        // Get all possible note types.
        $noteTypes = NoteTypeData::usable()
                                 ->orderBy('name', 'ASC')
                                 ->get();
        $noteTypesAll = NoteTypeData::withTrashed()
                                    ->orderBy('name', 'ASC')
                                    ->get();

        $this->setTitle('Account Details: '.$account->name);

        return $this->viewMake('adm.mship.account.detail')
                    ->with('selectedTab', $tab)
                    ->with('selectedTabId', $tabId)
                    ->with('account', $account)
                    ->with('availableRoles', $availableRoles)
                    ->with('banReasons', $banReasons)
                    ->with('noteTypes', $noteTypes)
                    ->with('noteTypesAll', $noteTypesAll)
                    ->with('feedback', $account->feedback()->orderBy('created_at', 'desc')->get());
    }

    public function postRoleAttach(AccountData $account)
    {
        if (!$account) {
            return Redirect::route('adm.mship.account.index');
        }

        // Let's try and load this RoleData
        $role = RoleData::find(Input::get('role'));

        if (!$role) {
            return Redirect::route('adm.mship.account.details', [$account->id, 'roles'])
                           ->withError('The selected role does not exist.');
        }

        // Let's add!
        if (!$account->roles->contains($role->id)) {
            $account->roles()
                    ->attach($role);
        }

        return Redirect::route('adm.mship.account.details', [$account->id, 'roles'])
                       ->withSuccess($role->name.' role attached successfully. This user inherited '.count($role->permissions).' permissions.');
    }

    public function getRoleDetach(AccountData $account, RoleData $role)
    {
        if (!$account) {
            return Redirect::route('adm.mship.account.index');
        }

        if (!$role) {
            return Redirect::route('adm.mship.account.details', [$account->id, 'roles'])
                           ->withError('The selected role does not exist.');
        }

        if (!$account->roles->contains($role->id)) {
            return Redirect::route('adm.mship.account.details', [$account->id, 'roles'])
                           ->withError('This role is not attached to this user.');
        }

        // Let's remove!
        $account->roles()
                ->detach($role);

        return Redirect::route('adm.mship.account.details', [$account->id, 'roles'])
                       ->withSuccess($role->name.' role detached successfully. This user lost '.count($role->permissions).' permissions.');
    }

    public function postSecurityEnable(AccountData $account)
    {
        if (!$account) {
            return Redirect::route('adm.mship.account.index');
        }

        // Let's check the user doesn't currently have security on their account.
        // We don't want to just override it for no reason, as that's bad.
        $currentSecurity = $account->current_security;

        if ($currentSecurity && $currentSecurity->exists) {
            return Redirect::route('adm.mship.account.details', [$account->id, 'security'])
                           ->withError('You cannot enable security on this account.');
        }

        // Check the selected security ID exists!
        $security = SecurityData::find(Input::get('securityLevel', 0));

        if (!$security) {
            return Redirect::route('adm.mship.account.details', [$account->id, 'security'])
                           ->withError('Invalid security ID specified.');
        }

        // Create them a blank security entry!
        $newSecurity = new AccountSecurityData();
        $newSecurity->save();
        $account->security()
                ->save($newSecurity);
        $security->accountSecurity()
                 ->save($newSecurity);

        return Redirect::route('adm.mship.account.details', [$account->id, 'security'])
                       ->withSuccess('Security enabled for this account.');
    }

    public function postSecurityReset(AccountData $account)
    {
        if (!$account) {
            return Redirect::route('adm.mship.account.index');
        }

        // Let's check the user doesn't currently have security on their account.
        // We can't reset non-existant security!
        $currentSecurity = $account->current_security;

        if (!$currentSecurity or !$currentSecurity->exists) {
            return Redirect::route('adm.mship.account.details', [$account->id, 'security'])
                           ->withError('You cannot reset non-existant security.');
        }

        return Redirect::route('adm.mship.account.details', [$account->id, 'security'])
                       ->withSuccess('Security reset requested - user will receive an email.');
    }

    public function postSecurityChange(AccountData $account)
    {
        if (!$account) {
            return Redirect::route('adm.mship.account.index');
        }

        // Check the selected security ID exists!
        $security = SecurityData::find(Input::get('securityLevel', 0));

        if (!$security) {
            return Redirect::route('adm.mship.account.details', [$account->id, 'security'])
                           ->withError('Invalid security ID specified.');
        }

        // Let's check the user doesn't currently have security on their account.
        // We don't want to just override it for no reason, as that's bad.
        $currentSecurity = $account->current_security;

        // It's also pointless changing to the same security ID.
        if (!$currentSecurity or !$currentSecurity->exists or $currentSecurity->security_id == $security->security_id) {
            return Redirect::route('adm.mship.account.details', [$account->id, 'security'])
                           ->withError('You cannot change security on this account.');
        }

        // Let's expire the current security
        $currentSecurity->expire();
        $currentSecurity->delete();

        // Now, let's make a new one!
        $newSecurity = new AccountSecurityData();
        $newSecurity->save();
        $account->security()
                ->save($newSecurity);
        $security->accountSecurity()
                 ->save($newSecurity);

        return Redirect::route('adm.mship.account.details', [$account->id, 'security'])
                       ->withSuccess('Security has been upgraded on this account.');
    }

    public function postBanAdd(CreateRequest $request, AccountData $account)
    {
        if (!$account) {
            return Redirect::route('adm.mship.account.index');
        }

        if ($account->is_banned) {
            return Redirect::route('adm.mship.account.details', [$account->id, 'bans'])
                           ->withError('You are not able to ban a member that is already banned.');
        }

        $banReason = Reason::find(Input::get('ban_reason_id'));

        // Create the user's ban
        $ban = $account->addBan(
            $banReason,
            Input::get('ban_reason_extra'),
            Input::get('ban_note_content'),
            $this->account->id
        );

        $this->account->notify(new BanCreated($ban));

        return Redirect::route('adm.mship.account.details', [$account->id, 'bans', $ban->id])
                       ->withSuccess('You have successfully banned this member.');
    }

    public function getBanRepeal(AccountData\Ban $ban)
    {
        if (!$ban) {
            // TODO: Could got to the master ban list?
            return Redirect::route('adm.mship.account.index');
        }

        $this->setTitle('Ban Repeal');

        return $this->viewMake('adm.mship.account.ban.repeal')
                    ->with('ban', $ban);
    }

    public function postBanRepeal(RepealRequest $request, AccountData\Ban $ban)
    {
        if (!$ban) {
            // TODO: Could got to the master ban list?
            return Redirect::route('adm.mship.account.index');
        }

        // Attach the note.
        $note = $ban->account->addNote(Type::isShortCode('discipline')->first(), Input::get('reason'), Auth::getUser());
        $ban->notes()->save($note);
        $ban->repeal();

        $this->account->notify(new BanRepealed($ban));

        return Redirect::route('adm.mship.account.details', [$ban->account_id, 'bans', $ban->id])
                       ->withSuccess('Ban has been repealed.');
    }

    public function getBanComment(AccountData\Ban $ban)
    {
        if (!$ban) {
            // TODO: Could got to the master ban list?
            return Redirect::route('adm.mship.account.index');
        }

        $this->setTitle('Ban Comment');

        return $this->viewMake('adm.mship.account.ban.comment')
                    ->with('ban', $ban);
    }

    public function postBanComment(CommentRequest $request, AccountData\Ban $ban)
    {
        if (!$ban) {
            // TODO: Could got to the master ban list?
            return Redirect::route('adm.mship.account.index');
        }

        // Attach the note.
        $note = $ban->account->addNote(
            Type::isShortCode('discipline')->first(),
            Input::get('comment'),
            Auth::getUser()
        );
        $ban->notes()->save($note);

        return Redirect::route('adm.mship.account.details', [$ban->account_id, 'bans', $ban->id])
                       ->withSuccess('Your comment for this ban has been noted.');
    }

    public function getBanModify(AccountData\Ban $ban)
    {
        if (!$ban) {
            // TODO: Could got to the master ban list?
            return Redirect::route('adm.mship.account.index');
        }

        $this->setTitle('Ban Modification');

        return $this->viewMake('adm.mship.account.ban.modify')
                    ->with('ban', $ban);
    }

    public function postBanModify(ModifyRequest $request, AccountData\Ban $ban)
    {
        if (!$ban) {
            // TODO: Could got to the master ban list?
            return Redirect::route('adm.mship.account.index');
        }

        $period_finish = \Carbon\Carbon::parse(Input::get('finish_date').' '.Input::get('finish_time'), 'UTC');
        if ($ban->period_finish->eq($period_finish)) {
            return Redirect::back()->withInput()->withError("You didn't change the ban period.");
        }

        if ($ban->period_finish->gt($period_finish)) {
            $noteComment = 'Ban has been reduced from '.$ban->period_finish->toDateTimeString().".\n";
        } else {
            $noteComment = 'Ban has been extended from '.$ban->period_finish->toDateTimeString().".\n";
        }
        $noteComment .= 'New finish: '.$period_finish->toDateTimeString()."\n";
        $noteComment .= Input::get('note');

        // Attach the note.
        $note = $ban->account->addNote(Type::isShortCode('discipline')->first(), $noteComment, Auth::getUser());
        $ban->notes()->save($note);

        // Modify the ban
        $ban->reason_extra = $ban->reason_extra."\n".Input::get('reason_extra');
        $ban->period_finish = $period_finish;
        $ban->save();

        $this->account->notify(new BanModified($ban));

        return Redirect::route('adm.mship.account.details', [$ban->account_id, 'bans', $ban->id])
                       ->withSuccess('Your comment for this ban has been noted.');
    }

    public function postNoteCreate(AccountData $account)
    {
        if (!$account) {
            return Redirect::route('adm.mship.account.index');
        }

        // Is there any content?
        if (strlen(Input::get('content')) < 10) {
            return Redirect::route('adm.mship.account.details', [$account->id, 'notes'])
                           ->withError('You cannot add such a short note!');
        }

        // Check this type exists!
        $noteType = NoteTypeData::find(Input::get('note_type_id'));
        if (!$noteType or !$noteType->exists) {
            return Redirect::route('adm.mship.account.details', [$account->id, 'notes'])
                           ->withError('You selected an invalid note type.');
        }

        // Let's make a note and attach it to the user!
        $account->addNote($noteType, Input::get('content'), Auth::user());

        return Redirect::route('adm.mship.account.details', [$account->id, 'notes'])
                       ->withSuccess('The note has been saved successfully!');
    }

    public function postNoteFilter(AccountData $account)
    {
        if (!$account) {
            return Redirect::route('adm.mship.account.index');
        }

        // Get all filters
        $filters = Input::get('filter', []);
        $qs = '';
        foreach ($filters as $f) {
            $qs .= 'filter['.$f.']=1&';
        }

        return Redirect::to(URL::route('adm.mship.account.details', [$account->id, 'notes']).'?'.$qs);
    }

    public function postImpersonate(AccountData $account)
    {
        if (!$account) {
            return Redirect::route('adm.mship.account.index');
        }

        // TODO: LOG.

        // Let's do the login!
        Auth::loginUsingId($account->id, false);
        Session::put('auth_override', true);

        return Redirect::to(URL::route('mship.manage.dashboard'))
                       ->withSuccess('You are now impersonating this user - your reason has been logged. Be good!');
    }
}
