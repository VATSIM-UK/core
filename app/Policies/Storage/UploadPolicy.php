<?php

namespace App\Policies\Storage;

use App\Models\Mship\Account;
use App\Models\Storage\Upload;
use Illuminate\Auth\Access\HandlesAuthorization;

class UploadPolicy
{
    use HandlesAuthorization;

    public function viewAny(Account $account): bool
    {
        return $account->checkPermissionTo('storage.uploads.view');
    }

    public function view(?Account $account, Upload $upload): bool
    {
        if ($upload->purged_at !== null || $upload->isExpired()) {
            return false;
        }

        if ($upload->isPublic()) {
            return true;
        }

        if ($account === null) {
            return false;
        }

        return $this->owns($account, $upload)
            || $account->checkPermissionTo('storage.uploads.view');
    }

    public function delete(Account $account, Upload $upload): bool
    {
        return $this->owns($account, $upload)
            || $account->checkPermissionTo('storage.uploads.delete');
    }

    private function owns(Account $account, Upload $upload): bool
    {
        return in_array($account->id, [$upload->account_id, $upload->uploaded_by], true);
    }
}
