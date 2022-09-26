<?php

namespace App\Http\Controllers\Adm\VisitTransfer;

use App\Http\Controllers\Adm\AdmController;
use App\Http\Requests\VisitTransfer\ReferenceAcceptRequest;
use App\Http\Requests\VisitTransfer\ReferenceRejectRequest;
use App\Models\VisitTransfer\Reference as ReferenceModel;
use Auth;
use Illuminate\Support\Facades\Request;
use Redirect;

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

        return $this->viewMake('visit-transfer.admin.reference.list')
            ->with('references', $references);
    }

    public function postReject(ReferenceRejectRequest $request, ReferenceModel $reference)
    {
        $rejectionReason = '';

        if (Request::input('rejection_reason') != 'other') {
            $rejectionReason = Request::input('rejection_reason');
        }

        if (Request::input('rejection_reason_extra', null)) {
            $rejectionReason .= "\n" . Request::input('rejection_reason_extra');
        }

        try {
            $reference->reject($rejectionReason, Request::input('rejection_staff_note', null), Auth::user());
        } catch (\Exception $e) {
            return Redirect::back()->withError($e->getMessage());
        }

        return Redirect::back()->withSuccess('Reference #' . $reference->id . ' - ' . $reference->account->name . ' rejected & candidate notified.');
    }

    public function postAccept(ReferenceAcceptRequest $request, ReferenceModel $reference)
    {
        try {
            $reference->accept(Request::input('accept_staff_note', null), Auth::user());
        } catch (\Exception $e) {
            return Redirect::back()->withError($e->getMessage());
        }

        return Redirect::back()->withSuccess('Reference #' . $reference->id . ' - ' . $reference->account->name . ' accepted & candidate notified.');
    }
}
