<?php

namespace App\Modules\Visittransfer\Http\Controllers\Admin;

use Auth;
use Input;
use Redirect;
use App\Models\Mship\Account;
use App\Http\Controllers\Adm\AdmController;
use App\Modules\Visittransfer\Models\Reference as ReferenceModel;
use App\Modules\Visittransfer\Http\Requests\ApplicationAcceptRequest;
use App\Modules\Visittransfer\Http\Requests\ApplicationRejectRequest;
use App\Modules\Visittransfer\Models\Application as ApplicationModel;
use App\Modules\Visittransfer\Http\Requests\ApplicationCheckOutcomeRequest;
use App\Modules\Visittransfer\Http\Requests\ApplicationSettingToggleRequest;

class Application extends AdmController
{
    public function getList($scope = 'all')
    {
        $permittedScope = ['all', 'open', 'closed', 'review', 'accepted'];
        $scope = ($scope != null && in_array($scope, $permittedScope)) ? $scope : 'all';

        // Sorting and searching!
        $sortBy = in_array(
            Input::get('sort_by'),
            ['id', 'account_id', 'type', 'created_at', 'updated_at']
        ) ? Input::get('sort_by') : 'updated_at';
        $sortDir = in_array(Input::get('sort_dir'), ['ASC', 'DESC']) ? Input::get('sort_dir') : 'DESC';

        $applications = ApplicationModel::orderBy($sortBy, $sortDir)
                                        ->with('account')
                                        ->with('facility')
                                        ->with('referees');

        switch ($scope) {
            case 'open':
                $this->setSubTitle('Open Applications');
                $applications = $applications->open()->notStatus(ApplicationModel::STATUS_IN_PROGRESS);
                break;
            case 'review':
                $this->setSubTitle('Under Review Applications');
                $applications = $applications->status(ApplicationModel::STATUS_UNDER_REVIEW);
                break;
            case 'accepted':
                $this->setSubTitle('Accepted Applications');
                $applications = $applications->status(ApplicationModel::STATUS_ACCEPTED);
                break;
            case 'closed':
                $this->setSubTitle('Closed Applications');
                $applications = $applications->closed();
                break;
            case 'all':
            default:
                $this->setSubTitle('All Applications');
        }

        $applications = $applications->paginate(50);

        return $this->viewMake('visittransfer::admin.application.list')
                    ->with('applications', $applications)
                    ->with('sortBy', $sortBy)
                    ->with('sortDir', $sortDir)
                    ->with('sortDirSwitch', ($sortDir == 'DESC' ? 'ASC' : 'DESC'));
    }

    public function getView(ApplicationModel $application)
    {
        $this->setSubTitle('Application #'.$application->public_id);

        $unacceptedReferences = $application->referees->filter(function ($ref) {
            return $ref->status == ReferenceModel::STATUS_UNDER_REVIEW;
        });

        return $this->viewMake('visittransfer::admin.application.view')
                    ->with('application', $application)
                    ->with('unacceptedReferences', $unacceptedReferences);
    }

    public function postReject(ApplicationRejectRequest $request, ApplicationModel $application)
    {
        $rejectionReason = '';

        if (Input::get('rejection_reason') != 'other') {
            $rejectionReason = Input::get('rejection_reason');
        }

        if (Input::get('rejection_reason_extra', null)) {
            $rejectionReason .= "\n".Input::get('rejection_reason_extra');
        }

        try {
            $application->reject($rejectionReason, Input::get('rejection_staff_note', null), Auth::user());
        } catch (\Exception $e) {
            return Redirect::back()->withError($e->getMessage());
        }

        return Redirect::back()
                       ->withSuccess('Application #'.$application->public_id.' - '.$application->account->name.' rejected &amp; candidate notified.');
    }

    public function postAccept(ApplicationAcceptRequest $request, ApplicationModel $application)
    {
        try {
            $application->accept(Input::get('accept_staff_note', null), Auth::user());
        } catch (\Exception $e) {
            return Redirect::back()->withError($e->getMessage());
        }

        return Redirect::back()
                       ->withSuccess('Application #'.$application->public_id.' - '.$application->account->name.' accepted &amp; candidate notified.');
    }

    public function postCheckMet(ApplicationCheckOutcomeRequest $request, ApplicationModel $application)
    {
        try {
            $application->setCheckOutcome(Input::get('check', null), true);
        } catch (\Exception $e) {
            return Redirect::back()->withError($e->getMessage());
        }

        return Redirect::route('visiting.admin.application.view', $application->id)->withSuccess(str_replace(
            '_',
            ' ',
            Input::get('check', null)
        )." check was marked as 'MET'!");
    }

    public function postCheckNotMet(ApplicationCheckOutcomeRequest $request, ApplicationModel $application)
    {
        try {
            $application->setCheckOutcome(Input::get('check', null), false);
        } catch (\Exception $e) {
            return Redirect::back()->withError($e->getMessage());
        }

        return Redirect::route('visiting.admin.application.view', $application->id)->withSuccess(str_replace(
            '_',
            ' ',
            Input::get('check', null)
        )." check was marked as 'NOT MET'!");
    }

    public function postSettingToggle(ApplicationSettingToggleRequest $request, ApplicationModel $application)
    {
        $application->settingToggle(Input::get('setting'));

        return Redirect::back()->withSuccess("Setting '".Input::get('setting')."' toggled successfully!");
    }
}
