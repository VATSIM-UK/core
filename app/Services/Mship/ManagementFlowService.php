<?php

namespace App\Services\Mship;

use App\Jobs\UpdateMember;
use App\Libraries\UKCP as UKCPLibrary;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Email as AccountEmail;
use App\Models\Roster;
use App\Models\Sys\Token as SystemToken;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Client as OAuthClient;

class ManagementFlowService
{
    public function __construct(private UKCPLibrary $ukcp) {}

    /**
     * @return array{pluginKeys: mixed, roster: bool}
     */
    public function getDashboardData(Account $account): array
    {
        return [
            'pluginKeys' => $this->ukcp->getValidTokensFor($account),
            'roster' => Roster::where('account_id', $account->id)->exists(),
        ];
    }

    /**
     * @return array{ok: bool, message?: string, level?: string, route?: string}
     */
    public function addSecondaryEmail(Account $account, string $email, string $emailConfirmation): array
    {
        $normalisedEmail = strtolower($email);
        $normalisedEmailConfirmation = strtolower($emailConfirmation);

        $validator = Validator::make(
            ['email' => $normalisedEmail],
            ['email' => ['required', 'email']]
        );

        if ($validator->fails()) {
            return [
                'ok' => false,
                'level' => 'error',
                'route' => 'mship.manage.email.add',
                'message' => 'You have entered an invalid email address.',
            ];
        }

        if (strcasecmp($normalisedEmail, $normalisedEmailConfirmation) !== 0) {
            return [
                'ok' => false,
                'level' => 'error',
                'route' => 'mship.manage.email.add',
                'message' => 'Emails entered are different.  You need to enter the same email, twice.',
            ];
        }

        if ($account->hasEmail($normalisedEmail)) {
            return [
                'ok' => false,
                'level' => 'error',
                'route' => 'mship.manage.dashboard',
                'message' => 'This email has already been added to your account.',
            ];
        }

        $account->addSecondaryEmail($normalisedEmail);

        return [
            'ok' => true,
            'message' => 'Your new email ('.$normalisedEmail.') has been added successfully! You will be sent a verification link to activate this email address.',
        ];
    }

    public function deleteSecondaryEmail(Account $account, AccountEmail $email): bool
    {
        if ($email->account->id !== $account->id) {
            return false;
        }

        $email->delete();

        return true;
    }

    /**
     * @return array{userPrimaryEmail: string, userSecondaryVerified: Collection, userMatrix: array<int, array{sso_system: OAuthClient, assigned_email_id: mixed}>}
     */
    public function getEmailAssignmentsData(Account $account): array
    {
        $ssoSystems = OAuthClient::all();
        $userPrimaryEmail = $account->email;
        $userVerifiedEmails = $account->verified_secondary_emails;
        $userSsoEmails = $account->ssoEmails;

        $userMatrix = [];
        foreach ($ssoSystems as $sys) {
            $hasEmails = $userSsoEmails->filter(function ($ssoemail) use ($sys) {
                return $ssoemail->sso_account_id == $sys->id;
            })->values();

            $userMatrix[] = [
                'sso_system' => $sys,
                'assigned_email_id' => ($hasEmails && count($hasEmails) > 0) ? $hasEmails[0]->account_email_id : $userPrimaryEmail,
            ];
        }

        return [
            'userPrimaryEmail' => $userPrimaryEmail,
            'userSecondaryVerified' => $userVerifiedEmails,
            'userMatrix' => $userMatrix,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function updateEmailAssignments(Account $account, array $input): void
    {
        $ssoSystems = OAuthClient::all();
        $userVerifiedEmails = $account->verified_secondary_emails;
        $userSsoEmails = $account->ssoEmails;

        foreach ($userSsoEmails as $ssoEmail) {
            if (($input['assign_'.$ssoEmail->sso_account_id] ?? 'pri') == 'pri') {
                $ssoEmail->delete();
            }
        }

        foreach ($ssoSystems as $ssosys) {
            if (($input['assign_'.$ssosys->id] ?? 'pri') == 'pri') {
                continue;
            }

            $assignedEmailID = $input['assign_'.$ssosys->id] ?? null;

            if (! $userVerifiedEmails->contains($assignedEmailID)) {
                continue;
            }

            $email = AccountEmail::find($assignedEmailID);
            $email?->assignToSso($ssosys);
        }
    }

    /**
     * @return array{ok: bool, message: string, email?: string}
     */
    public function verifyEmailToken(string $code): array
    {
        $token = SystemToken::where('code', '=', $code)->valid()->first();
        if (! $token) {
            return ['ok' => false, 'message' => 'You have provided an invalid email verification token. (ERR1)'];
        }

        if ($token->is_used) {
            return ['ok' => false, 'message' => 'You have provided an invalid email verification token. (ERR2)'];
        }

        if ($token->is_expired) {
            return ['ok' => false, 'message' => 'You have provided an invalid email verification token. (ERR3)'];
        }

        if (! $token->related || $token->type != 'mship_account_email_verify') {
            return ['ok' => false, 'message' => 'You have provided an invalid email verification token. (ERR4)'];
        }

        $token->consume();
        $token->related->verify();

        return ['ok' => true, 'message' => 'Your new email address ('.$token->related->email.') has been verified!', 'email' => $token->related->email];
    }

    public function requestCertCheck(int $accountId): bool
    {
        $ranRecently = ! Cache::add('USER_REQUEST_CERTCHECK_'.$accountId, '1', now()->addHour());
        if ($ranRecently) {
            return false;
        }

        UpdateMember::dispatch($accountId);

        return true;
    }
}
