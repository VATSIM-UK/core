<?php

declare(strict_types=1);

namespace App\Repositories\Cts;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingRepository
{
    private const TYPE_MAP = [
        Booking::TYPE_STANDARD => 'BK',
        Booking::TYPE_EXAM => 'EX',
        Booking::TYPE_MENTORING => 'ME',
        Booking::TYPE_EVENT => 'EV',
        Booking::TYPE_GROUP_SEMINAR => 'GS',
    ];

    public function getBookings(Carbon $date): Collection
    {
        $core = Booking::whereDate('starts_at', $date->toDateString())
            ->with('member', 'position')
            ->orderBy('starts_at')
            ->get();

        $importedIds = $core->pluck('cts_booking_id')->filter()->map(fn ($id) => (int) $id)->values()->all();

        $ctsOnly = CtsBooking::query()
            ->whereDate('date', $date->toDateString())
            ->when(! empty($importedIds), fn ($q) => $q->whereNotIn('id', $importedIds))
            ->orderBy('from')
            ->get();

        // Best-effort match of CTS positions to core positions; may fail for positions
        // that have no core counterpart, in which case the raw CTS position is used.
        $ctsCallsigns = $ctsOnly->pluck('position')->filter()->unique()->values();
        $ctsPositions = Position::whereIn('callsign', $ctsCallsigns)->get()->keyBy('callsign');

        $ctsMemberIds = $ctsOnly->pluck('member_id')->filter()->unique()->values();
        $ctsMembers = CtsMember::whereIn('id', $ctsMemberIds)->get()->keyBy('id');
        $ctsCids = $ctsMembers->pluck('cid')->filter()->unique()->values();
        $ctsAccounts = Account::whereIn('id', $ctsCids)->get()->keyBy('id');

        return $this->formatBookings($core)
            ->concat($ctsOnly->map(fn (CtsBooking $c) => $this->formatCtsBooking($c, $ctsPositions, $ctsMembers, $ctsAccounts)))
            ->values();
    }

    public function getTodaysBookings(): Collection
    {
        return $this->getBookings(Carbon::now());
    }

    public function getTodaysLiveAtcBookings(): Collection
    {
        $bookings = Booking::whereDate('starts_at', Carbon::now()->toDateString())
            ->liveAtc()
            ->with('member', 'position')
            ->orderBy('starts_at')
            ->get();

        return $this->formatBookings($bookings);
    }

    public function getTodaysLiveAtcBookingsWithoutEvents(): Collection
    {
        $bookings = Booking::whereDate('starts_at', Carbon::now()->toDateString())
            ->liveAtc()
            ->notEvent()
            ->with('member', 'position')
            ->orderBy('starts_at')
            ->get();

        return $this->formatBookings($bookings);
    }

    private function formatBookings(Collection $bookings): Collection
    {
        return $bookings->map(function (Booking $booking) {
            $type = self::TYPE_MAP[$booking->type] ?? 'BK';

            return $this->makeBooking(
                id: (string) $booking->id,
                source: 'core',
                ctsBookingId: $booking->cts_booking_id !== null ? (int) $booking->cts_booking_id : null,
                positionId: $booking->position_id,
                positionCallsign: $booking->position?->callsign,
                date: $booking->starts_at->format('Y-m-d'),
                from: $booking->starts_at->format('H:i'),
                to: $booking->ends_at->format('H:i'),
                type: $type,
                member: $this->formatMember($booking->member, $type),
            );
        });
    }

    private function formatCtsBooking(CtsBooking $cts, Collection $positions, Collection $members, Collection $accounts): object
    {
        $type = (string) $cts->type;
        $position = $positions->get($cts->position);
        $member = $members->get((int) $cts->member_id);
        $account = $member !== null ? $accounts->get((int) $member->cid) : null;

        return $this->makeBooking(
            id: null,
            source: 'cts',
            ctsBookingId: (int) $cts->id,
            positionId: $position?->id,
            positionCallsign: $cts->position,
            date: Carbon::parse($cts->date)->format('Y-m-d'),
            from: substr((string) $cts->from, 0, 5),
            to: substr((string) $cts->to, 0, 5),
            type: $type,
            member: $this->formatMember($account, $type),
        );
    }

    private function makeBooking(?string $id, string $source, ?int $ctsBookingId, ?int $positionId, ?string $positionCallsign, string $date, string $from, string $to, string $type, array $member): object
    {
        return (object) [
            'id' => $id,
            'source' => $source,
            'cts_booking_id' => $ctsBookingId,
            'position_id' => $positionId,
            'position' => $positionCallsign,
            'date' => $date,
            'from' => $from,
            'to' => $to,
            'type' => $type,
            'member' => $member,
        ];
    }

    private function formatMember(?Account $account, string $displayType): array
    {
        if ($displayType === 'EX') {
            return ['id' => '', 'cid' => '', 'name' => 'Hidden', 'display_name' => 'Hidden'];
        }

        if (! $account) {
            return ['id' => '', 'cid' => '', 'name' => 'Unknown', 'display_name' => 'Unknown'];
        }

        $firstName = $account->name_first;
        $lastInitial = mb_substr($account->name_last, 0, 1).'.';

        return [
            'id' => (string) $account->id,
            'cid' => (string) $account->id,
            'name' => $account->name,
            'display_name' => $firstName.' '.$lastInitial,
        ];
    }
}
