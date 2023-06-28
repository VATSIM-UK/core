<?php

namespace Tests\Feature\Admin\Account;

use App\Filament\Resources\AccountResource;
use Tests\Feature\Admin\BaseAdminResourceTestCase;

class AccountResourceTest extends BaseAdminResourceTestCase
{
    protected ?string $resourceClass = AccountResource::class;
}
