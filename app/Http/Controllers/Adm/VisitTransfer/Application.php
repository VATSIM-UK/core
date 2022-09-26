<?php

namespace App\Http\Controllers\Adm\VisitTransfer;

use App\Http\Controllers\Adm\AdmController;
use App\Http\Requests\VisitTransfer\ApplicationAcceptRequest;
use App\Http\Requests\VisitTransfer\ApplicationCancelRequest;
use App\Http\Requests\VisitTransfer\ApplicationCheckOutcomeRequest;
use App\Http\Requests\VisitTransfer\ApplicationCompleteRequest;
use App\Http\Requests\VisitTransfer\ApplicationRejectRequest;
use App\Http\Requests\VisitTransfer\ApplicationSettingToggleRequest;
use App\Models\VisitTransfer\Application as ApplicationModel;
use App\Models\VisitTransfer\Reference as ReferenceModel;
use Auth;
use Illuminate\Support\Facades\Request;
use Redirect;

class Application extends AdmController
{
    public function getList($scope = 'all')
    {
        $permittedScope = ['all', 'open', 'closed', 'review', 'accepted'];
        $scope = ($scope != null && in_array($scope, $permittedScope)) ? $scope : 'all';

        // Sorting and searching!
        $sortBy = in_array(
            Request::input('sort_by'),
            ['id', 'account_id', 'type', 'created_at', 'updated_at']
        ) ? Request::input('sort_by') : 'updated_at';
        $sortDir = in_array(Request::input('sort_dir'), ['ASC', 'DESC']) ? Request::input('sort_dir') : 'DESC';

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

        return $this->viewMake('visit-transfer.admin.application.list')
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

        return $this->viewMake('visit-transfer.admin.application.view')
            ->with('application', $application)
            ->with('unacceptedReferences', $unacceptedReferences);
    }

    public function postReject(ApplicationRejectRequest $request, ApplicationModel $application)
    {
        $rejectionReason = '';

        if (Request::input('rejection_reason') != 'other') {
            $rejectionReason = Request::input('rejection_reason');
        }

        if (Request::input('rejection_reason_extra', null)) {
            $rejectionReason .= "\n".Request::input('rejection_reason_extra');
        }

        try {
            $application->reject($rejectionReason, Request::input('rejection_staff_note', null), Auth::user());
        } catch (\Exception $e) {
            return Redirect::back()->withError($e->getMessage());
        }

        return Redirect::back()
            ->withSuccess('Application #'.$application->public_id.' - '.$application->account->name.' rejected & candidate notified.');
    }

    public function postAccept(ApplicationAcceptRequest $request, ApplicationModel $application)
    {
        try {
            $application->accept(Request::input('accept_staff_note', null), Auth::user());
        } catch (\Exception $e) {
            return Redirect::back()->withError($e->getMessage());
        }

        return Redirect::back()
            ->withSuccess('Application #'.$application->public_id.' - '.$application->account->name.' accepted & candidate notified.');
    }

    public function postComplete(ApplicationCompleteRequest $request, ApplicationModel $application)
    {
        try {
            $application->complete(Request::input('complete_staff_note', null), Auth::user());
        } catch (\Exception $e) {
            return Redirect::back()->withError($e->getMessage());
        }

        return Redirect::back()
            ->withSuccess('Application #'.$application->public_id.' - '.$application->account->name.' completed.');
    }

    public function postCancel(ApplicationCancelRequest $request, ApplicationModel $application)
    {
        try {
            $application->cancel(Request::input('cancel_reason', null), Request::input('cancel_staff_note', null), Auth::user());
        } catch (\Exception $e) {
            return Redirect::back()->withError($e->getMessage());
        }

        return Redirect::back()
            ->withSuccess('Application #'.$application->public_id.' - '.$application->account->name.' cancelled & candidate notified.');
    }

    public function postCheckMet(ApplicationCheckOutcomeRequest $request, ApplicationModel $application)
    {
        try {
            $application->setCheckOutcome(Request::input('check', null), true);
        } catch (\Exception $e) {
            return Redirect::back()->withError($e->getMessage());
        }

        return Redirect::route('adm.visiting.application.view', $application->id)->withSuccess(str_replace(
            '_',
            ' ',
            Request::input('check', null)
        )." check was marked as 'MET'!");
    }

    public function postCheckNotMet(ApplicationCheckOutcomeRequest $request, ApplicationModel $application)
    {
        try {
            $application->setCheckOutcome(Request::input('check', null), false);
        } catch (\Exception $e) {
            return Redirect::back()->withError($e->getMessage());
        }

        return Redirect::route('adm.visiting.application.view', $application->id)->withSuccess(str_replace(
            '_',
            ' ',
            Request::input('check', null)
        )." check was marked as 'NOT MET'!");
    }

    public function postSettingToggle(ApplicationSettingToggleRequest $request, ApplicationModel $application)
    {
        $application->settingToggle(Request::input('setting'));

        return Redirect::back()->withSuccess("Setting '".Request::input('setting')."' toggled successfully!");
    }
}
