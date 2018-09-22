<?php

namespace App\Services\Mship;

use Illuminate\Contracts\Auth\Authenticatable;
use App\Notifications\Mship\BanCreated;
use App\Models\Mship\Ban\Reason;
use App\Models\Mship\Account;
use App\Services\BaseService;

class BanAccount implements BaseService
{
    protected $account;
    protected $reason;
    protected $banner;
    protected $data;
    private $ban;

    /**
     * BanAccount Service constructor.
     *
     * @param Account $account
     * @param Reason $reason
     * @param Authenticatable $banner
     * @param array $data
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
        if (isset($this->ban)) {
            return $this->ban->id;
        }
    }
}