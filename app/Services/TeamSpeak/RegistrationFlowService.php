<?php

namespace App\Services\TeamSpeak;

use App\Models\Mship\Account;
use App\Models\TeamSpeak\Confirmation as ConfirmationModel;
use App\Models\TeamSpeak\Registration as RegistrationModel;
use App\Services\TeamSpeak\DTO\RegistrationStatusResult;
use Illuminate\Support\Str;

class RegistrationFlowService
{
    private const MAX_REGISTRATIONS_PER_ACCOUNT = 25;

    public function canStartRegistration(Account $account): bool
    {
        return $account->teamspeakRegistrations()->count() <= self::MAX_REGISTRATIONS_PER_ACCOUNT;
    }

    public function getOrCreateRegistration(Account $account, string $registrationIp): RegistrationModel
    {
        if (! $account->new_ts_registration) {
            return $this->createRegistration($account->id, $registrationIp);
        }

        return $account->new_ts_registration->load('confirmation');
    }

    public function getOrCreateConfirmation(RegistrationModel $registration, int $accountId): ConfirmationModel
    {
        if ($registration->confirmation) {
            return $registration->confirmation;
        }

        return $this->createConfirmation($registration->id, $accountId);
    }

    public function deleteRegistration(Account $account, RegistrationModel $registration): bool
    {
        if ($account->id !== $registration->account_id) {
            return false;
        }

        $registration->delete();

        return true;
    }

    public function getRegistrationStatus(Account $account, RegistrationModel $registration): RegistrationStatusResult
    {
        if ($account->id !== $registration->account_id) {
            return RegistrationStatusResult::forbidden();
        }

        return RegistrationStatusResult::success($registration->dbid === null ? 'new' : 'active');
    }

    public function getRegistrationStatusResponseBody(Account $account, RegistrationModel $registration): string
    {
        $result = $this->getRegistrationStatus($account, $registration);

        if (! $result->ok) {
            return 'Cannot retrieve registration status.';
        }

        return (string) $result->status;
    }

    public function generateAutoUrl(Account $account, ConfirmationModel $confirmation): string
    {
        $base = sprintf('%s%s%s', 'ts3server://', config('services.teamspeak.host'), '?');
        $query = http_build_query([
            'nickname' => sprintf('%s %s', $account->name, $account->id),
            'token' => $confirmation->privilege_key,
        ], encoding_type: PHP_QUERY_RFC3986);

        return sprintf('%s%s', $base, $query);
    }

    private function createRegistration(int $accountId, string $registrationIp): RegistrationModel
    {
        $registration = new RegistrationModel;
        $registration->account_id = $accountId;
        $registration->registration_ip = $registrationIp;
        $registration->save();

        return $registration;
    }

    private function createConfirmation(int $registrationId, int $accountId): ConfirmationModel
    {
        $keyDescription = 'CID:'.$accountId.' RegID:'.$registrationId;
        $keyCustomInfo = 'ident=registration_id value='.$registrationId;

        $confirmation = new ConfirmationModel;
        $confirmation->registration_id = $registrationId;
        $confirmation->privilege_key = \App\Libraries\TeamSpeak::enabled()
            ? \App\Libraries\TeamSpeak::run()
                ->serverGroupGetByName('New')
                ->privilegeKeyCreate($keyDescription, $keyCustomInfo)
            : Str::upper(Str::random(32));
        $confirmation->save();

        return $confirmation;
    }
}
