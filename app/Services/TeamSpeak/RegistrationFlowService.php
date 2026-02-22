<?php

namespace App\Services\TeamSpeak;

use App\Models\Mship\Account;
use App\Models\TeamSpeak\Confirmation as ConfirmationModel;
use App\Models\TeamSpeak\Registration as RegistrationModel;

class RegistrationFlowService
{
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

    public function getRegistrationStatus(Account $account, RegistrationModel $registration): ?string
    {
        if ($account->id !== $registration->account_id) {
            return null;
        }

        return $registration->dbid === null ? 'new' : 'active';
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
        $confirmation->privilege_key = \App\Libraries\TeamSpeak::run()
            ->serverGroupGetByName('New')
            ->privilegeKeyCreate($keyDescription, $keyCustomInfo);
        $confirmation->save();

        return $confirmation;
    }
}
