<?php

namespace App\Http\Controllers\Adm\Atc;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Atc\Endorsement as EndorsementModel;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Endorsement extends AdmController
{
    public function getIndex(Request $request)
    {
        $validator = Validator::make($request->all(), [
          'id' => 'required',
          'cid' => 'required|integer',
      ]);

        if (!$validator->fails()) {
            return $this->getUserEndorsement($request);
        }

        $endorsements = EndorsementModel::get(['id', 'name']);

        return $this->viewMake('adm.atc.endorsement.index')
                  ->with('endorsements', $endorsements);
    }

    public function getUserEndorsement(Request $request)
    {
        $endorsement = EndorsementModel::with('conditions')->find($request->input('id'))->first();
        $user = Account::find($request->input('cid'));
        if (!$user) {
            return redirect()
                    ->back()
                    ->withErrors(['That CID does not exist!']);
        }

        if (!$endorsement) {
            abort(404);
        }
        return $this->viewMake('adm.atc.endorsement.check')
            ->with('account', $user)
            ->with('endorsement', $endorsement);
    }
}
