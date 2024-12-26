<?php

namespace App\Registrars;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Access\Gate;
use Spatie\Permission\PermissionRegistrar as PermissionPermissionRegistrar;

class PermissionRegistrar extends PermissionPermissionRegistrar
{
    public function registerPermissions(Gate $gate): bool
    {
        $gate->before(function (Authorizable $user, string $ability, $arguments) {
            // Modification here to only check permissions when arguments (i.e. typically a modal instance) are **not** passed.
            // This prevents a wildcard permission (e.g. *) from returning true to ANY policy ->can() call

            if (method_exists($user, 'checkPermissionTo') && ! isset($arguments[0])) {
                return $user->checkPermissionTo($ability) ?: null;
            }
        });

        return true;
    }
}
