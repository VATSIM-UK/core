<?php

namespace Tests\Feature\Admin\Account;

use App\Filament\Resources\AccountResource;
use App\Policies\AccountPolicy;
use Tests\Feature\Admin\BaseAdminResourceTestCase;

class AccountResourceTest extends BaseAdminResourceTestCase
{
    protected static ?string $resourceClass = AccountResource::class;

    protected ?string $policy = AccountPolicy::class;
}
