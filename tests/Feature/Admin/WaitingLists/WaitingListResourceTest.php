<?php

namespace Tests\Feature\Admin\WaitingLists;

use Tests\Feature\Admin\BaseAdminResourceTestCase;

class WaitingListResourceTest extends BaseAdminResourceTestCase
{
    protected ?string $resourceClass = WaitingListResource::class;

    protected ?string $policy = WaitingListPolicy::class;
}
