<?php

namespace App\Http\Controllers\Adm\Atc;

use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Mship\Account;
use App\Http\Controllers\Adm\AdmController;
use App\Models\Atc\Endorsement as EndorsementModel;

class Endorsement extends AdmController
{
    public function getIndex(Request $request)
    {
        $validator = Validator::make($request->all(), [
          'endorsement' => 'required',
          'cid' => 'required|integer',
      ]);

        if (! $validator->fails()) {
            return $this->getUserEndorsement($request);
        }

        $endorsements = EndorsementModel::get(['endorsement'])->pluck('endorsement')->unique();

        return $this->viewMake('adm.atc.endorsement.index')
                  ->with('endorsements', $endorsements);
    }

    public function getUserEndorsement(Request $request)
    {
        $requirements = EndorsementModel::where('endorsement', $request->input('endorsement'))->get();
        $user = Account::find($request->input('cid'));
        if (! $user) {
            return redirect()
                    ->back()
                    ->withErrors(['That CID does not exist!']);
        }

        if ($requirements->count() < 1) {
            abort(404);
        }

        $criteria = $requirements->map(function ($r) use ($user) {
            $data = $user->networkDataAtc()
                ->withCallsignIn(json_decode($r->required_airfields))
                ->whereBetween('connected_at', [Carbon::now()->subMonth($r->hours_months), Carbon::now()])
                ->get(['minutes_online', 'callsign'])
                ->mapToGroups(function ($item) {
                    return [substr($item['callsign'], 0, 4) => ($item['minutes_online'] / 60)];
                })->transform(function ($item) {
                    return $item->sum();
                });

            // Make critera human-friendly
            $r->required_airfields = str_replace('%', 'XXX', json_decode($r->required_airfields));

            return (object) ['requirements' => $r, 'hours' => $data, 'met' => ($data->max() >= $r->required_hours)];
        });

        return $this->viewMake('adm.atc.endorsement.check')
            ->with('account', $user)
            ->with('endorsement', $request->input('endorsement'))
            ->with('criteria', $criteria);
    }
}
