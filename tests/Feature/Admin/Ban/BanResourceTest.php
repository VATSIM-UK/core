<?php

namespace Tests\Feature\Admin\Account;

use App\Filament\Admin\Resources\BanResource;
use App\Policies\Mship\Account\BanPolicy;
use Tests\Feature\Admin\BaseAdminResourceTestCase;

class BanResourceTest extends BaseAdminResourceTestCase
{
    protected static ?string $resourceClass = BanResource::class;

    protected ?string $policy = BanPolicy::class;
}
