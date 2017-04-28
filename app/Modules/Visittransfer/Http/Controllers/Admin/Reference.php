<?php

namespace App\Modules\Visittransfer\Http\Controllers\Admin;

use Auth;
use Input;
use Redirect;
use App\Models\Mship\Account;
use App\Http\Controllers\Adm\AdmController;
use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Models\Reference as ReferenceModel;
use App\Modules\Visittransfer\Http\Requests\ReferenceAcceptRequest;
use App\Modules\Visittransfer\Http\Requests\ReferenceRejectRequest;

class Reference extends AdmController
{
    public function getList($scope = 'all')
    {
        $permittedScope = ['all', 'pending-submission', 'submitted', 'under-review', 'accepted', 'rejected'];
        $scope = ($scope != null && in_array($scope, $permittedScope)) ? $scope : 'all';

        $references = ReferenceModel::with('application')
                                    ->with('application.account')
                                    ->with('account');

        switch ($scope) {
            case 'pending-submission':
                $this->setSubTitle('References Pending Submission');
                $references = $references->requested();
                break;
            case 'submitted':
                $this->setSubTitle('Submitted References');
                $references = $references->submitted();
                break;
            case 'under-review':
                $this->setSubTitle('References Under Review');
                $references = $references->underReview();
                break;
            case 'accepted':
                $this->setSubTitle('Accepted References');
                $references = $references->accepted();
                break;
            case 'rejected':
                $this->setSubTitle('Rejected References');
                $references = $references->rejected();
                break;
            case 'all':
            default:
                $this->setSubTitle('All References');
        }

        $references = $references->paginate(50);

        return $this->viewMake('visittransfer::admin.reference.list')
                    ->with('references', $references);
    }

    public function postReject(ReferenceRejectRequest $request, ReferenceModel $reference)
    {
        $rejectionReason = '';

        if (Input::get('rejection_reason') != 'other') {
            $rejectionReason = Input::get('rejection_reason');
        }

        if (Input::get('rejection_reason_extra', null)) {
            $rejectionReason .= "\n".Input::get('rejection_reason_extra');
        }

        try {
            $reference->reject($rejectionReason, Input::get('rejection_staff_note', null), Auth::user());
        } catch (\Exception $e) {
            return Redirect::back()->withError($e->getMessage());
        }

        return Redirect::back()->withSuccess('Reference #'.$reference->id.' - '.$reference->account->name.' rejected &amp; candidate notified.');
    }

    public function postAccept(ReferenceAcceptRequest $request, ReferenceModel $reference)
    {
        try {
            $reference->accept(Input::get('accept_staff_note', null), Auth::user());
        } catch (\Exception $e) {
            return Redirect::back()->withError($e->getMessage());
        }

        return Redirect::back()->withSuccess('Reference #'.$reference->id.' - '.$reference->account->name.' accepted &amp; candidate notified.');
    }
}
