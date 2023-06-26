<?php

namespace App\Http\Controllers\Adm\Atc;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Atc\Endorsement as EndorsementModel;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Endorsement extends AdmController
{
    public function getIndex(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:endorsements',
            'cid' => 'required|integer',
        ]);

        if (! $validator->fails()) {
            return $this->getUserEndorsement($request);
        }

        $endorsements = EndorsementModel::get(['id', 'name']);

        return $this->viewMake('adm.atc.endorsement.index')
            ->with('endorsements', $endorsements);
    }

    public function getUserEndorsement(Request $request)
    {
        $endorsement = EndorsementModel::with('conditions')->find($request->input('id'));

        try {
            $user = Account::findOrFail($request->input('cid'));
        } catch (ModelNotFoundException $exception) {
            return redirect()
                ->back()
                ->withErrors(['That CID does not exist!']);
        }

        return $this->viewMake('adm.atc.endorsement.check')
            ->with('account', $user)
            ->with('endorsement', $endorsement);
    }
}
