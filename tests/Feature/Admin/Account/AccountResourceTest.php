<?php

namespace Tests\Feature\Admin\Account;

use App\Filament\Admin\Forms\Components\AccountSelect;
use App\Filament\Admin\Resources\Accounts\AccountResource;
use App\Policies\AccountPolicy;
use Tests\Feature\Admin\BaseAdminResourceTestCase;

class AccountResourceTest extends BaseAdminResourceTestCase
{
    protected static ?string $resourceClass = AccountResource::class;

    protected ?string $policy = AccountPolicy::class;

    public function test_account_select_does_not_search_by_discord_id(): void
    {
        $this->assertSame(
            ['id', 'name_first', 'name_last', 'nickname'],
            AccountSelect::make()->getSearchColumns(),
        );
    }
}
