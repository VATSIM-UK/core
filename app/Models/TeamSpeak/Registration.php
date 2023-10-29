<?php

namespace App\Models\TeamSpeak;

use App\Libraries\TeamSpeak;
use App\Models\Model;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use TeamSpeak3;
use TeamSpeak3_Adapter_ServerQuery_Exception;

/**
 * App\Models\TeamSpeak\Registration.
 *
 * @property int $id
 * @property int $account_id
 * @property string $registration_ip
 * @property string $last_ip
 * @property string|null $last_login
 * @property string|null $last_os
 * @property string|null $uid
 * @property int|null $dbid
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \App\Models\TeamSpeak\Confirmation $confirmation
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Registration onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Registration whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Registration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Registration whereDbid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Registration whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Registration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Registration whereLastIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Registration whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Registration whereLastOs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Registration whereRegistrationIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Registration whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\TeamSpeak\Registration whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Registration withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\TeamSpeak\Registration withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Registration extends Model
{
    use SoftDeletingTrait;

    protected $table = 'teamspeak_registration';

    protected $primaryKey = 'id';

    protected $fillable = ['*'];

    protected $attributes = ['registration_ip' => '0.0.0.0', 'last_ip' => '0.0.0.0'];

    protected $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function delete($tscon = null)
    {
        if (Teamspeak::enabled()) {
            if ($tscon == null) {
                $tscon = TeamSpeak::run('VATSIM UK Registrations');
            }

            if ($this->confirmation) {
                try {
                    $tscon->privilegeKeyDelete($this->confirmation->privilege_key);
                } catch (TeamSpeak3_Adapter_ServerQuery_Exception $e) {
                    if ($e->getMessage() === 'ok') {
                        $this->confirmation->delete();
                    }
                }

                $this->confirmation->delete();
            }

            foreach ($tscon->clientList() as $client) {
                if ($client['client_database_id'] == $this->dbid || $client['client_unique_identifier'] == $this->uid) {
                    $client->kick(TeamSpeak3::KICK_SERVER, 'Registration deleted.');
                }
            }

            try {
                if (is_numeric($this->dbid)) {
                    $tscon->clientDeleteDb($this->dbid);
                }
            } catch (\Exception $e) {
            }
        }

        parent::delete();
    }

    public function confirmation()
    {
        return $this->hasOne(Confirmation::class, 'registration_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
