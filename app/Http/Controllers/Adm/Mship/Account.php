<?php

namespace App\Http\Controllers\Adm\Mship;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account as AccountData;
use App\Models\Mship\Ban\Reason;
use App\Models\Mship\Note\Type as NoteTypeData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Redirect;
use Spatie\Permission\Models\Role as RoleData;

class Account extends AdmController
{
    public function getIndex($scope = 'division')
    {
        // Sorting and searching!
        $sortBy = in_array(
            Request::input('sort_by'),
            ['id', 'name_first', 'name_last']
        ) ? Request::input('sort_by') : 'id';
        $sortDir = in_array(Request::input('sort_dir'), ['ASC', 'DESC']) ? Request::input('sort_dir') : 'ASC';

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
            $members->prepend($m->account ? $m->account : $m);
        }
        $members = $members->reverse();

        return $this->viewMake('adm.mship.account.index')
            ->with('members', $members)
            ->with('membersQuery', $memberSearch)
            ->with('sortBy', $sortBy)
            ->with('sortDir', $sortDir)
            ->with('sortDirSwitch', $sortDir == 'DESC' ? 'ASC' : 'DESC');
    }

    public function getDetail(AccountData $mshipAccount, $tab = 'basic', $tabId = 0)
    {
        if (! $mshipAccount or $mshipAccount->is_system) {
            return Redirect::route('adm.mship.account.index');
        }

        // Do they have permission to view their own profile?
        // This is to prevent people doing silly things....
        if ($this->account->id == $mshipAccount->id && ! $this->account->can('use-permission', 'adm/mship/account/own')) {
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
            'secondaryEmails'
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

        $vtapplications = $mshipAccount->visitTransferApplications()->orderBy('updated_at', 'desc')->get();

        $this->setTitle("Account Details:  {$mshipAccount->name}");

        return $this->viewMake('adm.mship.account.detail')
            ->with('selectedTab', $tab)
            ->with('selectedTabId', $tabId)
            ->with('account', $mshipAccount)
            ->with('availableRoles', $availableRoles)
            ->with('banReasons', $banReasons)
            ->with('noteTypes', $noteTypes)
            ->with('noteTypesAll', $noteTypesAll)
            ->with('vtapplications', $vtapplications);
    }
}
