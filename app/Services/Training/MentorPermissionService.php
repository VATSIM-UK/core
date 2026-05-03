<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Enums\PositionValidationStatusEnum;
use App\Models\Cts\Member;
use App\Models\Cts\Position;
use App\Models\Cts\PositionValidation;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
        'P1 Training' => 'Pilot Mentor',
        'P2 Training' => 'Pilot Mentor',
        'P3 Training' => 'Pilot Mentor',
    ];

    public const PILOT_CATEGORY_QUALIFICATION_MAP = [
        'P1 Training' => 'PPL',
        'P2 Training' => 'IR',
        'P3 Training' => 'CMEL',
    ];

    public const QUALIFICATION_CTS_POSITION_MAP = [
        'PPL' => 'P1_MENTOR',
        'IR' => 'P2_MENTOR',
        'CMEL' => 'P3_MENTOR',
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

    public function getModelClassForCategory(string $category): string
    {
        return self::categoryType($category) === 'atc' ? TrainingPosition::class : Qualification::class;
    }

    public function mentorableBelongsToCategory($mentorable, string $category): bool
    {
        if ($mentorable instanceof TrainingPosition) {
            return $mentorable->category === $category;
        }

        if ($mentorable instanceof Qualification) {
            $allowedCode = self::PILOT_CATEGORY_QUALIFICATION_MAP[$category] ?? null;

            return $allowedCode !== null && $mentorable->code === $allowedCode;
        }

        return false;
    }

    public function accountsWithMentoringInCategoryQuery(string $category): Builder
    {
        $modelClass = $this->getModelClassForCategory($category);

        return Account::query()
            ->whereHas('mentorTrainingPositions', function (Builder $q) use ($category, $modelClass) {
                $q->where('mentorable_type', $modelClass)
                    ->whereHasMorph('mentorable', [$modelClass], function ($query) use ($category, $modelClass) {
                        if ($modelClass === TrainingPosition::class) {
                            $query->where('category', $category);
                        } else {
                            $code = self::PILOT_CATEGORY_QUALIFICATION_MAP[$category] ?? null;
                            $query->where('code', $code);
                        }
                    });
            });
    }

    public function assignToMentorable(Account $account, $mentorable, Account $actor, string $category): void
    {
        MentorTrainingPosition::firstOrCreate([
            'account_id' => $account->id,
            'mentorable_type' => get_class($mentorable),
            'mentorable_id' => $mentorable->id,
        ], [
            'created_by' => $actor->id,
        ]);

        $this->syncCtsAssign($account, $mentorable, $actor);
        $this->syncRole($account, $category);
    }

    public function syncPositionsInCategory(Account $account, string $category, Collection $newIds, Account $actor): void
    {
        $modelClass = $this->getModelClassForCategory($category);

        $currentPermissions = MentorTrainingPosition::where('account_id', $account->id)
            ->where('mentorable_type', $modelClass)
            ->get()
            ->filter(fn ($permission) => $permission->mentorable && $this->mentorableBelongsToCategory($permission->mentorable, $category));

        $currentPermissions->reject(fn ($p) => $newIds->contains($p->mentorable_id))
            ->each(fn ($p) => $this->revokePermission($p));

        $newIds->each(function ($id) use ($account, $modelClass, $actor, $category) {
            $model = $modelClass::find($id);
            if ($model) {
                $this->assignToMentorable($account, $model, $actor, $category);
            }
        });
    }

    public function revokeFromCategory(Account $account, string $category): void
    {
        $modelClass = $this->getModelClassForCategory($category);

        MentorTrainingPosition::where('account_id', $account->id)
            ->where('mentorable_type', $modelClass)
            ->get()
            ->filter(fn ($permission) => $permission->mentorable && $this->mentorableBelongsToCategory($permission->mentorable, $category))
            ->each(fn ($p) => $this->revokePermission($p));

        $this->syncRole($account, $category);
    }

    protected function revokePermission(MentorTrainingPosition $permission): void
    {
        if ($permission->mentorable) {
            $this->syncCtsRevoke($permission->account, $permission->mentorable);
        }
        $permission->delete();
    }

    private function syncRole(Account $account, string $category): void
    {
        $roleName = self::roleForCategory($category);

        if (! $roleName) {
            return;
        }

        $categoriesSharingRole = array_keys(array_filter(array_merge(self::ATC_CATEGORY_ROLE_MAP, self::PILOT_CATEGORY_ROLE_MAP), fn ($role) => $role === $roleName));

        $hasMentorPermissionsForRole = MentorTrainingPosition::where('account_id', $account->id)
            ->get()
            ->contains(function ($permission) use ($categoriesSharingRole) {
                return collect($categoriesSharingRole)->contains(function ($cat) use ($permission) {
                    return $permission->mentorable && $this->mentorableBelongsToCategory($permission->mentorable, $cat);
                });
            });

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

    private function getCtsCallsignsForMentorable($mentorable): array
    {
        if ($mentorable instanceof TrainingPosition) {
            return $mentorable->cts_positions ?? [];
        }

        if ($mentorable instanceof Qualification) {
            $callsign = self::QUALIFICATION_CTS_POSITION_MAP[$mentorable->code] ?? null;

            return $callsign ? [$callsign] : [];
        }

        return [];
    }

    private function syncCtsAssign(Account $account, $mentorable, Account $actor): void
    {
        if (($member = $this->resolveMember($account)) === null) {
            return;
        }

        $actorMember = $this->resolveMember($actor);
        $changedBy = $actorMember ? $actorMember->id : $member->id;

        $callsigns = $this->getCtsCallsignsForMentorable($mentorable);

        foreach ($callsigns as $callsign) {
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
                'changed_by' => $changedBy,
                'date_changed' => now(),
            ]);
        }
    }

    private function syncCtsRevoke(Account $account, $mentorable): void
    {
        if (($member = $this->resolveMember($account)) === null) {
            return;
        }

        $callsigns = $this->getCtsCallsignsForMentorable($mentorable);

        foreach ($callsigns as $callsign) {
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

    public function getLastMentoredDate(Account $account, string $category)
    {
        $ctsMemberId = DB::connection('cts')
            ->table('members')
            ->where('cid', $account->id)
            ->value('id');

        $callsigns = $this->getAssignedCtsCallsigns($account, $category);

        if (empty($callsigns)) {
            return null;
        }

        $lastMentoredDate = DB::connection('cts')
            ->table('sessions')
            ->where('mentor_id', $ctsMemberId)
            ->whereIn('position', $callsigns)
            ->where('taken', 1)
            ->where('session_done', 1)
            ->whereNull('cancelled_datetime')
            ->max('taken_date');

        if (! $lastMentoredDate) {
            return null;
        }

        return Carbon::parse($lastMentoredDate);
    }

    public function getAssignedCtsCallsigns(Account $account, string $category): array
    {
        $callsigns = collect();

        $account->mentorTrainingPositions
            ->filter(fn ($mtp) => $mtp->mentorable && $this->mentorableBelongsToCategory($mtp->mentorable, $category))
            ->each(function ($mtp) use ($callsigns) {
                if ($mtp->mentorable instanceof TrainingPosition) {
                    $ctsPositions = $mtp->mentorable->cts_positions;
                    if (is_array($ctsPositions)) {
                        $callsigns->push(...$ctsPositions);
                    }
                } elseif ($mtp->mentorable instanceof Qualification) {
                    $map = self::QUALIFICATION_CTS_POSITION_MAP;

                    if (array_key_exists($mtp->mentorable->code, $map)) {
                        $mappedCallsigns = Arr::wrap($map[$mtp->mentorable->code]);
                        $callsigns->push(...$mappedCallsigns);
                    }
                }
            });

        return $callsigns->unique()->filter()->values()->toArray();
    }
}
