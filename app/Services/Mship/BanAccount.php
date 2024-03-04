<?php

namespace App\Services\Mship;

use App\Models\Mship\Account;
use App\Models\Mship\Ban\Reason;
use App\Notifications\Mship\BanCreated;
use App\Services\BaseService;
use Illuminate\Contracts\Auth\Authenticatable;

class BanAccount implements BaseService
{
    protected $account;

    protected $reason;

    protected $banner;

    protected $data;

    private $ban;

    /**
     * BanAccount Service constructor.
     */
    public function __construct(Account $account, Reason $reason, Authenticatable $banner, array $data)
    {
        $this->account = $account;
        $this->reason = $reason;
        $this->data = $data;
        $this->banner = $banner;
    }

    public function handle()
    {
        $this->ban = $this->account->addBan(
            $this->reason,
            $this->data['ban_reason_extra'],
            $this->data['ban_internal_note'],
            $this->banner
        );

        $this->account->notify(new BanCreated($this->ban));
    }

    public function getBanIdentifier()
    {
        return $this->banProcessed() ? $this->ban->id : null;
    }

    private function banProcessed()
    {
        return isset($this->ban);
    }

    public function getBanInstance()
    {
        return $this->banProcessed() ? $this->ban : null;
    }
}
