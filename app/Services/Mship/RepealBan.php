<?php

namespace App\Services\Mship;

use App\Models\Mship\Account\Ban;
use App\Notifications\Mship\BanRepealed;
use App\Services\BaseService;

class RepealBan implements BaseService
{
    protected $ban;

    public function __construct(Ban $ban)
    {
        $this->ban = $ban;
    }

    public function handle()
    {
        $this->ban->repeal();

        $this->ban->account->notify(new BanRepealed($this->ban));
    }
}
