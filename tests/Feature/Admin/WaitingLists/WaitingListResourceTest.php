<?php

namespace Tests\Feature\Admin\WaitingLists;

use App\Filament\Resources\WaitingListResource;
use App\Policies\Training\WaitingListPolicy;
use Tests\Feature\Admin\BaseAdminResourceTestCase;

class WaitingListResourceTest extends BaseAdminResourceTestCase
{
    protected static ?string $resourceClass = WaitingListResource::class;

    protected ?string $policy = WaitingListPolicy::class;
}
