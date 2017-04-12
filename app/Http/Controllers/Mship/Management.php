<?php

namespace App\Http\Controllers\Mship;

use Auth;
use Input;
use Redirect;
use Validator;
use App\Models\Sso\Account as SSOSystem;
use App\Models\Sys\Token as SystemToken;
use App\Exceptions\Mship\DuplicateEmailException;
use App\Models\Mship\Account\Email as AccountEmail;

class Management extends \App\Http\Controllers\BaseController
{
    public function getLanding()
    {
        if (Auth::check()) {
            return Redirect::route('mship.auth.redirect');
        }

        return $this->viewMake('mship.management.landing');
    }

    public function getDashboard()
    {
        // Load necessary data, early!
        $this->account->load(
            'secondaryEmails',
            'qualifications',
            'states',
            'teamspeakRegistrations'
        );

        return $this->viewMake('mship.management.dashboard');
    }

    public function getEmailAdd()
    {
        return $this->viewMake('mship.management.email.add');
    }

    public function postEmailAdd()
    {
        $email = strtolower(Input::get('new_email'));
        $email2 = strtolower(Input::get('new_email2'));

        $validator = Validator::make(
            ['email' => $email],
            ['email' => ['required', 'email']]
        );

        if ($validator->fails()) {
            return Redirect::route('mship.manage.email.add')
                           ->withError('You have entered an invalid email address.');
        }

        // Check they match!
        if (strcasecmp($email, $email2) != 0) {
            return Redirect::route('mship.manage.email.add')
                           ->withError('Emails entered are different.  You need to enter the same email, twice.');
        }

        try {
            $this->account->addSecondaryEmail($email);
        } catch (DuplicateEmailException $e) {
            return Redirect::route('mship.manage.email.add')
                           ->withError($e);
        }

        return Redirect::route('mship.manage.dashboard')
                       ->withSuccess('Your new email ('.$email.') has been added successfully! You will be sent a verification link to activate this email address.');
    }

    public function getEmailDelete(AccountEmail $email)
    {
        // Is this the user's email?
        if ($email->account->id !== $this->account->id) {
            return Redirect::route('mship.manage.dashboard');
        }

        return $this->viewMake('mship.management.email.delete')
                    ->with('email', $email)
                    ->with('assignments', $email->ssoEmails);
    }

    public function postEmailDelete(AccountEmail $email)
    {
        // Is this the user's email?
        if ($email->account->id !== $this->account->id) {
            return Redirect::route('mship.manage.dashboard');
        }

        // Delete the secondary email
        $email->delete();

        return Redirect::route('mship.manage.dashboard')
                       ->withSuccess('Your secondary email ('.$email->email.') has been removed!');
    }

    public function getEmailAssignments()
    {
        // Get all SSO systems
        $ssoSystems = SSOSystem::all();

        // Get all user emails that are currently verified!
        $userPrimaryEmail = $this->account->email;
        $userVerifiedEmails = $this->account->verified_secondary_emails;

        // Get user SSO email assignments!
        $userSsoEmails = $this->account->ssoEmails;

        // Now build the user's matrix!
        $userMatrix = [];
        foreach ($ssoSystems as $sys) {
            $umEntry = [];
            $umEntry['sso_system'] = $sys;

            // Let's see if the user has this system!
            $hasEmails = $userSsoEmails->filter(function ($ssoemail) use ($sys) {
                return $ssoemail->sso_account_id == $sys->id;
            });
            $hasEmails = $hasEmails->values();

            if ($hasEmails && count($hasEmails) > 0) {
                $umEntry['assigned_email_id'] = $hasEmails[0]->account_email_id;
            } else {
                $umEntry['assigned_email_id'] = $userPrimaryEmail;
            }

            $userMatrix[] = $umEntry;
        }

        return $this->viewMake('mship.management.email.assignments')
                    ->with('userPrimaryEmail', $userPrimaryEmail)
                    ->with('userSecondaryVerified', $userVerifiedEmails)
                    ->with('userMatrix', $userMatrix);
    }

    public function postEmailAssignments()
    {
        // Get all SSO systems
        $ssoSystems = SSOSystem::all();

        // Get all user emails that are currently verified!
        $userPrimaryEmail = $this->account->email;
        $userVerifiedEmails = $this->account->verified_secondary_emails;

        // Get user SSO email assignments!
        $userSsoEmails = $this->account->ssoEmails;

        // Now, let's go through and see if any that are CURRENTLY assigned have switched back to PRIMARY
        // If they have, we can just delete them!
        foreach ($userSsoEmails as $ssoEmail) {
            if (Input::get('assign_'.$ssoEmail->sso_account_id, 'pri') == 'pri') {
                $ssoEmail->delete();
            }
        }

        // NOW, let's go through all the other systems and check if we have NONE primary assignments
        foreach ($ssoSystems as $ssosys) {
            // SKIP PRIMARY ASSIGNMENTS!
            if (Input::get('assign_'.$ssosys->id, 'pri') == 'pri') {
                continue;
            }

            // We have an assignment - woohoo!
            $assignedEmailID = Input::get('assign_'.$ssosys->id);

            // Let's do the assignment
            // The model will take care of checking if it exists or not, itself!
            if (!$userVerifiedEmails->contains($assignedEmailID)) {
                continue; // This isn't a valid EMAIL ID for this user.
            }

            // Let's now just load and assign!
            $email = AccountEmail::find($assignedEmailID);
            $email->assignToSso($ssosys);
        }

        return Redirect::route('mship.manage.dashboard')->withSuccess('Email assignments updated successfully! These will take effect the next time you login to the system.');
    }

    public function getVerifyEmail($code)
    {
        // Search tokens for this code!
        $token = SystemToken::where('code', '=', $code)->valid()->first();

        // Is it valid? Has it expired? Etc?
        if (!$token) {
            return $this->viewMake('mship.management.email.verify')->with(
                'error',
                'You have provided an invalid email verification token. (ERR1)'
            );
        }

        // Is it valid? Has it expired? Etc?
        if ($token->is_used) {
            return $this->viewMake('mship.management.email.verify')->with(
                'error',
                'You have provided an invalid email verification token. (ERR2)'
            );
        }

        // Is it valid? Has it expired? Etc?
        if ($token->is_expired) {
            return $this->viewMake('mship.management.email.verify')->with(
                'error',
                'You have provided an invalid email verification token. (ERR3)'
            );
        }

        // Is it valid and linked to something?!?!
        if (!$token->related or $token->type != 'mship_account_email_verify') {
            return $this->viewMake('mship.management.email.verify')->with(
                'error',
                'You have provided an invalid email verification token. (ERR4)'
            );
        }

        // Let's now consume this token.
        $token->consume();

        // Mark the email as verified!
        $token->related->verified_at = \Carbon\Carbon::now();
        $token->related->save();

        // Consumed, let's send away!
        if ($this->account) {
            return Redirect::route('mship.manage.dashboard')->withSuccess('Your new email address ('.$token->related->email.') has been verified!');
        } else {
            return $this->viewMake('mship.management.email.verify')->with(
                'success',
                'Your new email address ('.$token->related->email.') has been verified!'
            );
        }
    }
}
