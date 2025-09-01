<?php

namespace Tests\Feature\Admin\Role;

use App\Filament\Admin\Resources\RoleResource;
use App\Policies\RolePolicy;
use Tests\Feature\Admin\BaseAdminResourceTestCase;

class RoleResourceTest extends BaseAdminResourceTestCase
{
    protected static ?string $resourceClass = RoleResource::class;

    protected ?string $policy = RolePolicy::class;
}
