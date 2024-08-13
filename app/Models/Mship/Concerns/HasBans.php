<?php

namespace App\Models\Mship\Concerns;

use App\Enums\BanTypeEnum;
use App\Events\Mship\AccountAltered;
use App\Events\Mship\Bans\BanUpdated;
use App\Models\Mship\Account\Ban;
use App\Models\Mship\Ban\Reason;
use App\Models\Mship\Note\Type;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;

trait HasBans
{
    public function scopeBanned(Builder $query)
    {
        return $query->whereHas('bans', function (Builder $banQuery) {
            $banQuery->isActive();
        });
    }

    public function scopeNotBanned(Builder $query)
    {
        return $query->whereNot(fn (Builder $subQuery) => $subQuery->banned());
    }

    public function bans()
    {
        return $this->hasMany(\App\Models\Mship\Account\Ban::class, 'account_id')->orderBy(
            'created_at',
            'DESC'
        );
    }

    public function addBan(Reason $banReason, $banExtraReason = null, $banNote = null, $writerId = null, $type = BanTypeEnum::Local)
    {
        if ($writerId == null) {
            $writerId = 0;
        } elseif (is_object($writerId)) {
            $writerId = $writerId->getKey();
        }

        // Attach the note.
        $note = $this->addNote(Type::isShortCode('discipline')->first(), $banNote, $writerId);

        // Make a ban.
        $ban = new Ban;
        $ban->account_id = $this->id;
        $ban->banned_by = $writerId;
        $ban->type = $type;
        $ban->reason_id = $banReason->id;
        $ban->reason_extra = $banExtraReason;
        $ban->period_start = Carbon::now()->second(0);
        $ban->period_finish = Carbon::now()->addHours($banReason->period_hours)->second(0);
        $ban->save();

        $ban->notes()->save($note);
        event(new BanUpdated($ban));

        return $ban;
    }

    public function getIsSystemBannedAttribute()
    {
        $bans = $this->bans->filter(function ($ban) {
            return $ban->is_active && $ban->is_local;
        });

        return $bans->count() > 0;
    }

    public function getSystemBanAttribute()
    {
        $bans = $this->bans->filter(function ($ban) {
            return $ban->is_active && $ban->is_local;
        });

        return $bans->first();
    }

    public function getIsNetworkBannedAttribute()
    {
        $bans = $this->bans->filter(function ($ban) {
            return $ban->is_active && $ban->is_network;
        });

        return $bans->count() > 0;
    }

    public function getNetworkBanAttribute()
    {
        $bans = $this->bans->filter(function ($ban) {
            return $ban->is_active && $ban->is_network;
        });

        return $bans->first();
    }

    public function addNetworkBan($reason = 'Network ban discovered.')
    {
        if ($this->is_network_banned === false) {
            $ban = new \App\Models\Mship\Account\Ban;
            $ban->type = BanTypeEnum::Network;
            $ban->reason_extra = $reason;
            $ban->period_start = Carbon::now();

            $ban->account()->associate($this);

            $ban->save();
            event(new AccountAltered($this));
        }
    }

    public function removeNetworkBan()
    {
        if ($this->is_network_banned === true) {
            $ban = $this->network_ban;
            $ban->period_finish = Carbon::now();
            $ban->save();
            event(new AccountAltered($this));
        }
    }

    public function getIsBannedAttribute()
    {
        return $this->is_system_banned || $this->is_network_banned;
    }
}
