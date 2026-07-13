<?php

namespace App\Http\Controllers\Site;

use App\Models\Atc\PositionGroup;
use App\Models\NetworkData\Atc;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ATCPagesController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->addBreadcrumb('ATC', route('site.atc.landing'));
    }

    public function viewLanding()
    {
        $this->setTitle('ATC Training');

        return $this->viewMake('site.atc.landing');
    }

    public function viewNewController()
    {
        $this->setTitle('New Controller');
        $this->addBreadcrumb('New Controller', route('site.atc.newController'));

        return $this->viewMake('site.atc.newcontroller');
    }

    public function viewEndorsements()
    {
        $this->setTitle('ATC Endorsements');
        $this->addBreadcrumb('Endorsements', route('site.atc.endorsements'));

        return $this->viewMake('site.atc.endorsements');
    }

    public function viewHeathrow()
    {
        $this->setTitle('Heathrow Endorsements');
        $this->addBreadcrumb('Heathrow Endorsements', route('site.atc.heathrow'));

        $viewData = ['account' => $this->account];

        if ($this->account->exists) {
            $heathrowPositionGroups = PositionGroup::whereIn('name', [
                'Heathrow (GND)',
                'Heathrow (TWR)',
                'Heathrow (APP)',
            ])->get()->keyBy('name');

            $existingEndorsements = $this->account->endorsements()
                ->active()
                ->where('endorsable_type', PositionGroup::class)
                ->whereIn('endorsable_id', $heathrowPositionGroups->pluck('id'))
                ->pluck('endorsable_id')
                ->toArray();

            $atcQualificationLevel = $this->account->qualification_atc?->vatsim ?? 0;

            $gndPgId = $heathrowPositionGroups->get('Heathrow (GND)')?->id;
            $twrPgId = $heathrowPositionGroups->get('Heathrow (TWR)')?->id;

            $hasGndEndorsement = $gndPgId && in_array($gndPgId, $existingEndorsements);
            $hasTwrEndorsement = $twrPgId && in_array($twrPgId, $existingEndorsements);

            $endorsementProgress = [];
            $endorsementChain = ['Heathrow (GND)', 'Heathrow (TWR)', 'Heathrow (APP)'];

            foreach ($endorsementChain as $pgName) {
                $pg = $heathrowPositionGroups->get($pgName);
                if (! $pg || in_array($pg->id, $existingEndorsements)) {
                    continue;
                }

                $eligible = match ($pgName) {
                    'Heathrow (GND)' => $atcQualificationLevel >= 3,
                    'Heathrow (TWR)' => $atcQualificationLevel >= 3 && $hasGndEndorsement,
                    'Heathrow (APP)' => $atcQualificationLevel >= 4 && $hasTwrEndorsement,
                };

                if (! $eligible) {
                    continue;
                }

                switch ($pgName) {
                    case 'Heathrow (GND)':
                        $delGndTwrBase = $this->account->networkDataAtc()
                            ->isUK()
                            ->withoutAfis()
                            ->withoutMilitary()
                            ->atMinimumQualification(3)
                            ->where(function (Builder $b) {
                                $b->where('facility_type', Atc::TYPE_DEL)
                                    ->orWhere('facility_type', Atc::TYPE_GND)
                                    ->orWhere('facility_type', Atc::TYPE_TWR);
                            });

                        $endorsementProgress[] = [
                            'name' => 'Heathrow DEL/GND (S2+)',
                            'bars' => [
                                ['label' => 'Total UK DEL/GND/TWR', 'hours' => (clone $delGndTwrBase)->sum('minutes_online') / 60, 'required' => 40],
                                ['label' => 'Gatwick DEL/GND/TWR', 'hours' => (clone $delGndTwrBase)->where('callsign', 'like', 'EGKK%')->sum('minutes_online') / 60, 'required' => 10],
                                ['label' => 'Manchester DEL/GND/TWR', 'hours' => (clone $delGndTwrBase)->where('callsign', 'like', 'EGCC%')->sum('minutes_online') / 60, 'required' => 10],
                            ],
                        ];
                        break;

                    case 'Heathrow (TWR)':
                        $heathrowGndRecent = $this->account->networkDataAtc()
                            ->where('callsign', 'like', 'EGLL%')
                            ->where(function (Builder $b) {
                                $b->where('facility_type', Atc::TYPE_GND)
                                    ->orWhere('facility_type', Atc::TYPE_DEL);
                            })
                            ->atMinimumQualification(3)
                            ->whereBetween('connected_at', [Carbon::now()->subMonths(3), Carbon::now()])
                            ->sum('minutes_online') / 60;

                        $ukTwrBase = $this->account->networkDataAtc()
                            ->isUK()
                            ->withoutAfis()
                            ->withoutMilitary()
                            ->atMinimumQualification(3)
                            ->where('facility_type', Atc::TYPE_TWR);

                        $endorsementProgress[] = [
                            'name' => 'Heathrow TWR (S2+)',
                            'bars' => [
                                ['label' => 'Heathrow GND/DEL (last 3 months)', 'hours' => $heathrowGndRecent, 'required' => 10],
                                ['label' => 'Total UK TWR', 'hours' => (clone $ukTwrBase)->sum('minutes_online') / 60, 'required' => 100],
                                ['label' => 'Manchester TWR', 'hours' => (clone $ukTwrBase)->where('callsign', 'like', 'EGCC%')->sum('minutes_online') / 60, 'required' => 30],
                                ['label' => 'Gatwick TWR', 'hours' => (clone $ukTwrBase)->where('callsign', 'like', 'EGKK%')->sum('minutes_online') / 60, 'required' => 30],
                            ],
                        ];
                        break;

                    case 'Heathrow (APP)':
                        $heathrowTwrRecent = $this->account->networkDataAtc()
                            ->where('callsign', 'like', 'EGLL%')
                            ->where('facility_type', Atc::TYPE_TWR)
                            ->atMinimumQualification(4)
                            ->whereBetween('connected_at', [Carbon::now()->subMonths(3), Carbon::now()])
                            ->sum('minutes_online') / 60;

                        $ukAppBase = $this->account->networkDataAtc()
                            ->isUK()
                            ->withoutMilitary()
                            ->atMinimumQualification(4)
                            ->where('facility_type', Atc::TYPE_APP);

                        $endorsementProgress[] = [
                            'name' => 'Heathrow APP (S3+)',
                            'bars' => [
                                ['label' => 'Heathrow TWR (last 3 months)', 'hours' => $heathrowTwrRecent, 'required' => 10],
                                ['label' => 'Total UK APP', 'hours' => (clone $ukAppBase)->sum('minutes_online') / 60, 'required' => 120],
                                ['label' => 'Manchester APP', 'hours' => (clone $ukAppBase)->where('callsign', 'like', 'EGCC%')->sum('minutes_online') / 60, 'required' => 30],
                                ['label' => 'Gatwick APP', 'hours' => (clone $ukAppBase)->where('callsign', 'like', 'EGKK%')->sum('minutes_online') / 60, 'required' => 30],
                            ],
                        ];
                        break;
                }

                break;
            }

            $viewData['endorsementProgress'] = $endorsementProgress;
        }

        return $this->viewMake('site.atc.heathrow')->with($viewData);
    }

    public function viewBecomingAMentor()
    {
        $this->setTitle('Becoming a Mentor');
        $this->addBreadcrumb('Becoming a Mentor', route('site.atc.mentor'));

        return $this->viewMake('site.atc.mentor');
    }

    public function viewBookings()
    {
        $this->setTitle('Bookings');
        $this->addBreadcrumb('Bookings', route('site.atc.bookings'));

        return $this->viewMake('site.atc.bookings');
    }
}
