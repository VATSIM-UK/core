<?php

namespace Vatsimuk\WaitingListsManager\Http;

use Illuminate\Http\Request;
use App\Models\Mship\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Models\Training\WaitingList;
use Illuminate\Support\Facades\Auth;
use App\Services\Training\OfferTrainingPlace;
use App\Models\Training\WaitingList\WaitingListStatus;
use App\Models\Training\TrainingPlace\TrainingPosition;
use App\Models\Training\WaitingList\WaitingListAccount;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Events\Training\AccountChangedStatusInWaitingList;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WaitingListsManagerController extends Controller
{
    private $waitingList;

    public function __construct(WaitingList $waitingList)
    {
        $this->waitingList = $waitingList;
    }

    public function index(WaitingList $waitingList)
    {
        return $this->getWaitingListAccounts($waitingList, false);
    }

    public function activeIndex(WaitingList $waitingList)
    {
        return $this->getWaitingListAccounts($waitingList, true);
    }

    public function destroy(WaitingList $waitingList, Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));

        $waitingList->removeFromWaitingList($account);

        return [];
    }

    public function defer(WaitingList $waitingList, Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));

        $status = WaitingListStatus::find(WaitingListStatus::DEFERRED);

        $this->findWaitingListAccount($account, $waitingList)->addStatus($status);

        event(new AccountChangedStatusInWaitingList($account, $waitingList, $request->user()));

        return [];
    }

    public function active(WaitingList $waitingList, Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));

        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);

        $this->findWaitingListAccount($account, $waitingList)->addStatus($status);

        event(new AccountChangedStatusInWaitingList($account, $waitingList, $request->user()));

        return [];
    }

    public function getAvailablePlaces(WaitingList &$waitingList)
    {
        return response()->json([
            'places' => TrainingPosition::availablePlacesForWaitingList($waitingList)
        ]);
    }

    public function offerTrainingPlace(WaitingList &$waitingList, TrainingPosition $trainingPosition, Request $request) : JsonResponse
    {
        try {
            $account = Account::findOrFail($request->get('account_id'));
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Account not found'], 400);
        }

        if (!$this->getWaitingListAccounts($waitingList, true)->pluck('id')->contains($account->id)) {
            return response()->json(['message' => 'Account not eligible for training place.'], 403);
        }

        handleService(new OfferTrainingPlace(
            $trainingPosition,
            $account,
            Auth::user()
        ));

        return response()->json([], 201);
    }

    private function findWaitingListAccount(Account &$account, WaitingList &$waitingList): WaitingListAccount
    {
        return $account->waitingLists
            ->where('pivot.deleted_at', '==', null)
            ->where('id', $waitingList->id)
            ->first()
            ->pivot;
    }

    private function getWaitingListAccounts(WaitingList &$waitingList, bool $eligibility): AnonymousResourceCollection
    {
        return WaitingListAccountResource::collection(
            $waitingList->load(['accounts', 'flags'])->accounts
                ->where('pivot.deleted_at', '==', null)
                ->sortBy('pivot.created_at')
                ->filter(function ($model) use ($eligibility) {
                    return $model->pivot->eligibility == $eligibility;
                }));
    }
}
