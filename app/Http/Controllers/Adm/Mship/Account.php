<?php

namespace App\Http\Controllers\Adm\Mship;

use App\Events\Mship\AccountAltered;
use App\Http\Controllers\Adm\AdmController;
use App\Http\Requests\Mship\Account\Ban\CommentRequest;
use App\Http\Requests\Mship\Account\Ban\CreateRequest;
use App\Http\Requests\Mship\Account\Ban\ModifyRequest;
use App\Http\Requests\Mship\Account\Ban\RepealRequest;
use App\Listeners\Mship\SyncSubscriber;
use App\Models\Contact;
use App\Models\Mship\Account as AccountData;
use App\Models\Mship\Account\Ban as BanData;
use App\Models\Mship\Ban\Reason;
use App\Models\Mship\Note\Type;
use App\Models\Mship\Note\Type as NoteTypeData;
use App\Notifications\Mship\BanCreated;
use App\Notifications\Mship\BanModified;
use App\Notifications\Mship\BanRepealed;
use App\Notifications\Mship\UserImpersonated;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Input;
use Redirect;
use Session;
use Spatie\Permission\Models\Role as RoleData;
use URL;

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
        $memberSearch = AccountData::orderBy($sortBy, $sortDir)
            ->has('states')
            ->with('qualifications')
            ->with('states')
            ->with('bans')
            ->with('secondaryEmails');

        switch ($scope) {
            case 'all':
                break;

            case 'active':
                $memberSearch = $memberSearch->where('inactive', 0);
                break;

            case 'inactive':
                $memberSearch = $memberSearch->where('inactive', 1);
                break;

            case 'suspended':
                $memberSearch = $memberSearch->has('bans');
                break;

            case 'nondivision':
                $memberSearch = $memberSearch->whereHas('states', function ($query) {
                    $query->where('code', '!=', 'DIVISION')
                        ->whereNull('end_at');
                });
                break;

            case 'division':
            default:
                $memberSearch = $memberSearch->whereHas('states', function ($query) {
                    $query->where('code', 'DIVISION')
                        ->whereNull('end_at');
                });
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

    public function getDetail(AccountData $mshipAccount, $tab = 'basic', $tabId = 0)
    {
        if (!$mshipAccount or $mshipAccount->is_system) {
            return Redirect::route('adm.mship.account.index');
        }

        // Do they have permission to view their own profile?
        // This is to prevent people doing silly things....
        if ($this->account->id == $mshipAccount->id && !$this->account->can('use-permission', 'adm/mship/account/own')) {
            return Redirect::route('adm.mship.account.index')
                ->withError('You cannot view or manage your own profile.');
        }

        // Lazy eager loading
        $mshipAccount->load(
            'bans',
            'bans.banner',
            'bans.reason',
            'bans.notes',
            'bans.notes.writer',
            'notes',
            'notes.type',
            'notes.writer',
            'notes.attachment',
            'roles',
            'roles.permissions',
            'qualifications',
            'states',
            'secondaryEmails',
            'feedback'
        );

        // Get all possible roles!
        $availableRoles = RoleData::all()
            ->diff($mshipAccount->roles);

        // Get all ban reasons.
        $banReasons = Reason::all();

        // Get all possible note types.
        $noteTypes = NoteTypeData::usable()
            ->orderBy('name', 'ASC')
            ->get();
        $noteTypesAll = NoteTypeData::withTrashed()
            ->orderBy('name', 'ASC')
            ->get();

        $feedbackTargeted = $mshipAccount->feedback()->orderBy('created_at', 'desc')->get();

        $vtapplications = $mshipAccount->visitTransferApplications()->orderBy('updated_at', 'desc')->get();

        $this->setTitle('Account Details: '.$mshipAccount->name);

        return $this->viewMake('adm.mship.account.detail')
            ->with('selectedTab', $tab)
            ->with('selectedTabId', $tabId)
            ->with('account', $mshipAccount)
            ->with('availableRoles', $availableRoles)
            ->with('banReasons', $banReasons)
            ->with('noteTypes', $noteTypes)
            ->with('noteTypesAll', $noteTypesAll)
            ->with('feedback', $feedbackTargeted)
            ->with('vtapplications', $vtapplications);
    }

    public function postRoleAttach(AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        // Let's try and load this RoleData
        $role = RoleData::find(Input::get('role'));

        if (!$role) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'roles'])
                ->withError('The selected role does not exist.');
        }

        // Let's add!
        if (!$mshipAccount->roles->contains($role->id)) {
            $mshipAccount->roles()
                ->attach($role);
        }

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'roles'])
            ->withSuccess($role->name.' role attached successfully. This user inherited '.count($role->permissions).' permissions.');
    }

    public function getRoleDetach(AccountData $mshipAccount, RoleData $role)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        if (!$role) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'roles'])
                ->withError('The selected role does not exist.');
        }

        if (!$mshipAccount->roles->contains($role->id)) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'roles'])
                ->withError('This role is not attached to this user.');
        }

        // Let's remove!
        $mshipAccount->roles()
            ->detach($role);

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'roles'])
            ->withSuccess($role->name.' role detached successfully. This user lost '.count($role->permissions).' permissions.');
    }

    public function postSecurityEnable(AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        // Let's check the user doesn't currently have security on their account.
        // We don't want to just override it for no reason, as that's bad.
        $currentSecurity = $mshipAccount->current_security;

        if ($currentSecurity && $currentSecurity->exists) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
                ->withError('You cannot enable security on this account.');
        }

        // Check the selected security ID exists!
        $security = SecurityData::find(Input::get('securityLevel', 0));

        if (!$security) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
                ->withError('Invalid security ID specified.');
        }

        // Create them a blank security entry!
        $newSecurity = new AccountSecurityData();
        $newSecurity->save();
        $mshipAccount->security()
            ->save($newSecurity);
        $security->accountSecurity()
            ->save($newSecurity);

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
            ->withSuccess('Security enabled for this account.');
    }

    public function postSecurityReset(AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        // Let's check the user doesn't currently have security on their account.
        // We can't reset non-existant security!
        $currentSecurity = $mshipAccount->current_security;

        if (!$currentSecurity or !$currentSecurity->exists) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
                ->withError('You cannot reset non-existant security.');
        }

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
            ->withSuccess('Security reset requested - user will receive an email.');
    }

    public function postSecurityChange(AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        // Check the selected security ID exists!
        $security = SecurityData::find(Input::get('securityLevel', 0));

        if (!$security) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
                ->withError('Invalid security ID specified.');
        }

        // Let's check the user doesn't currently have security on their account.
        // We don't want to just override it for no reason, as that's bad.
        $currentSecurity = $mshipAccount->current_security;

        // It's also pointless changing to the same security ID.
        if (!$currentSecurity or !$currentSecurity->exists or $currentSecurity->security_id == $security->security_id) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
                ->withError('You cannot change security on this account.');
        }

        // Let's expire the current security
        $currentSecurity->expire();
        $currentSecurity->delete();

        // Now, let's make a new one!
        $newSecurity = new AccountSecurityData();
        $newSecurity->save();
        $mshipAccount->security()
            ->save($newSecurity);
        $security->accountSecurity()
            ->save($newSecurity);

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
            ->withSuccess('Security has been upgraded on this account.');
    }

    public function postBanAdd(CreateRequest $request, AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        if ($mshipAccount->is_banned) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'bans'])
                ->withError('You are not able to ban a member that is already banned.');
        }

        $banReason = Reason::find(Input::get('ban_reason_id'));

        // Create the user's ban
        $ban = $mshipAccount->addBan(
            $banReason,
            Input::get('ban_reason_extra'),
            Input::get('ban_note_content'),
            $this->account->id
        );

        $mshipAccount->notify(new BanCreated($ban));

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'bans', $ban->id])
            ->withSuccess('You have successfully banned this member.');
    }

    public function getBans()
    {
        $bans = BanData::isLocal()
            ->orderByDesc('created_at')
            ->paginate(15);

        return $this->viewMake('adm.mship.account.ban.index')
            ->with('bans', $bans);
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

        $ban->account->notify(new BanRepealed($ban));

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

        $period_finish = Carbon::parse(Input::get('finish_date').' '.Input::get('finish_time'), 'UTC');
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
        $noteComment .= Input::get('note');

        // Attach the note.
        $note = $ban->account->addNote(Type::isShortCode('discipline')->first(), $noteComment, Auth::getUser());
        $ban->notes()->save($note);

        // Modify the ban
        $ban->reason_extra = $ban->reason_extra."\n".Input::get('reason_extra');
        $ban->period_finish = $period_finish;
        $ban->save();

        $ban->account->notify(new BanModified($ban));

        return Redirect::route('adm.mship.account.details', [$ban->account_id, 'bans', $ban->id])
            ->withSuccess('This ban has been modified.');
    }

    public function postNoteCreate(AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        // Is there any content?
        if (strlen(Input::get('content')) < 10) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'notes'])
                ->withError('You cannot add such a short note!');
        }

        // Check this type exists!
        $noteType = NoteTypeData::find(Input::get('note_type_id'));
        if (!$noteType or !$noteType->exists) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'notes'])
                ->withError('You selected an invalid note type.');
        }

        // Let's make a note and attach it to the user!
        $mshipAccount->addNote($noteType, Input::get('content'), Auth::user());

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'notes'])
            ->withSuccess('The note has been saved successfully!');
    }

    public function postNoteFilter(AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        // Get all filters
        $filters = Input::get('filter', []);
        $qs = '';
        foreach ($filters as $f) {
            $qs .= 'filter['.$f.']=1&';
        }

        return Redirect::to(URL::route('adm.mship.account.details', [$mshipAccount->id, 'notes']).'?'.$qs);
    }

    public function postImpersonate(Request $request, AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        $attributes = $this->validate($request, [
            'reason' => 'required|string|min:5',
        ]);

        Contact::where('key', 'PRIVACC')->first()
            ->notify(new UserImpersonated($mshipAccount, $request->user(), $attributes['reason']));

        // Let's do the login!
        Auth::loginUsingId($mshipAccount->id, false);
        Session::put('auth_override', true);

        return Redirect::to(URL::route('mship.manage.dashboard'))
            ->withSuccess('You are now impersonating this user - your reason has been logged. Be good!');
    }

    public function sync(AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        $subscriber = new SyncSubscriber();
        $subscriber->syncToAllServices(new AccountAltered($mshipAccount));
        
        return Redirect::back()
            ->withSuccess('User queued to sync to external services!');
    }
}
