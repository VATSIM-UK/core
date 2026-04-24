<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Enums\PositionValidationStatusEnum;
use App\Models\Cts\Member;
use App\Models\Cts\Position;
use App\Models\Cts\PositionValidation;
use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class MentorPermissionService
{
    public const ATC_CATEGORY_ROLE_MAP = [
        'OBS to S1 Training' => 'ATC Mentor (OBS)',
        'S2 Training' => 'ATC Mentor (TWR)',
        'S3 Training' => 'ATC Mentor (APP)',
        'C1 Training' => 'ATC Mentor (ENR)',
        'Heathrow GMC' => 'ATC Mentor (Heathrow)',
        'Heathrow AIR' => 'ATC Mentor (Heathrow)',
        'Heathrow APC' => 'ATC Mentor (Heathrow)',
    ];

    public const PILOT_CATEGORY_ROLE_MAP = [
        // TODO: add pilot category => role mappings when pilot categories are finalized.
    ];

    public static function atcCategories(): array
    {
        return array_keys(self::ATC_CATEGORY_ROLE_MAP);
    }

    public static function pilotCategories(): array
    {
        return array_keys(self::PILOT_CATEGORY_ROLE_MAP);
    }

    public static function categoryType(string $category): string
    {
        return in_array($category, self::atcCategories(), true) ? 'atc' : 'pilot';
    }

    public static function roleForCategory(string $category): ?string
    {
        return self::ATC_CATEGORY_ROLE_MAP[$category] ?? self::PILOT_CATEGORY_ROLE_MAP[$category] ?? null;
    }

    public function assignToPositions(Account $account, Collection $positions, Account $actor, string $category): void
    {
        foreach ($positions as $position) {
            $this->assignToPosition($account, $position, $actor);
        }

        $this->syncRole($account, $category);
    }

    public function syncPositionsInCategory(Account $account, string $category, Collection $newPositionIds, Account $actor): void
    {
        $scopedPositions = TrainingPosition::where('category', $category)->get();

        $scopedPositions
            ->whereNotIn('id', $newPositionIds)
            ->each(fn (TrainingPosition $pos) => $this->revokeFromPosition($account, $pos));

        $scopedPositions
            ->whereIn('id', $newPositionIds)
            ->each(fn (TrainingPosition $pos) => $this->assignToPosition($account, $pos, $actor));

        $this->syncRole($account, $category);
    }

    public function revokeFromCategory(Account $account, string $category): void
    {
        $positions = TrainingPosition::where('category', $category)->get();
        $positions->each(fn (TrainingPosition $pos) => $this->revokeFromPosition($account, $pos));
        $this->syncRole($account, $category);
    }

    private function assignToPosition(Account $account, TrainingPosition $trainingPosition, Account $actor): void
    {
        MentorTrainingPosition::firstOrCreate(
            [
                'account_id' => $account->id,
                'training_position_id' => $trainingPosition->id,
            ],
            ['created_by' => $actor->id]
        );

        $this->syncCtsAssign($account, $trainingPosition);
    }

    private function revokeFromPosition(Account $account, TrainingPosition $trainingPosition): void
    {
        MentorTrainingPosition::where('account_id', $account->id)
            ->where('training_position_id', $trainingPosition->id)
            ->delete();

        $this->syncCtsRevoke($account, $trainingPosition);
    }

    private function syncRole(Account $account, string $category): void
    {
        $roleName = self::roleForCategory($category);

        if (! $roleName) {
            return;
        }

        $categoriesSharingRole = array_keys(
            array_filter(self::ATC_CATEGORY_ROLE_MAP, fn ($role) => $role === $roleName)
        );

        $hasMentorPermissionsForRole = $account->mentorTrainingPositions()
            ->whereHas('trainingPosition', fn ($query) => $query->whereIn('category', $categoriesSharingRole))
            ->exists();

        if ($hasMentorPermissionsForRole) {
            if (! $account->hasRole($roleName)) {
                $account->assignRole($roleName);
            }
        } else {
            if ($account->hasRole($roleName)) {
                $account->removeRole($roleName);
            }
        }
    }

    private function resolveMember(Account $account): ?Member
    {
        if (! $account->member) {
            Log::error("MentorPermissionService: account {$account->id} has no CTS member model");

            return null;
        }

        return $account->member;
    }

    private function syncCtsAssign(Account $account, TrainingPosition $trainingPosition): void
    {
        if (($member = $this->resolveMember($account)) === null) {
            return;
        }

        foreach ($trainingPosition->cts_positions as $callsign) {
            $ctsPosition = Position::where('callsign', $callsign)->first();

            if (! $ctsPosition) {
                Log::error("MentorPermissionService: CTS position {$callsign} not found");

                continue;
            }

            $exists = PositionValidation::where('member_id', $member->id)
                ->where('position_id', $ctsPosition->id)
                ->where('status', PositionValidationStatusEnum::Mentor->value)
                ->exists();

            if ($exists) {
                continue;
            }

            PositionValidation::create([
                'member_id' => $member->id,
                'position_id' => $ctsPosition->id,
                'status' => PositionValidationStatusEnum::Mentor->value,
                'changed_by' => $member->id, // TODO: This should ideally be the actor making the change
                'date_changed' => now(),
            ]);
        }
    }

    private function syncCtsRevoke(Account $account, TrainingPosition $trainingPosition): void
    {
        if (($member = $this->resolveMember($account)) === null) {
            return;
        }

        foreach ($trainingPosition->cts_positions as $callsign) {
            $ctsPosition = Position::where('callsign', $callsign)->first();

            if (! $ctsPosition) {
                Log::error("MentorPermissionService: CTS position {$callsign} not found");

                continue;
            }

            PositionValidation::where('member_id', $member->id)
                ->where('position_id', $ctsPosition->id)
                ->where('status', PositionValidationStatusEnum::Mentor->value)
                ->delete();
        }
    }
}
