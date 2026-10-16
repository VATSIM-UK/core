<?php

namespace App\Filament\Training\Forms\Components;

use App\Services\Training\Availability\AvailabilityPlanner;
use App\Services\Training\AvailabilityService;
use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\Field;

class AvailabilityGrid extends Field
{
    protected string $view = 'filament.training.forms.components.availability-grid';

    protected int|Closure $fromHour = 6;

    protected int|Closure $toHour = 24;

    protected array|Closure $dates = [];

    protected array|Closure $lockedCells = [];

    protected bool|Closure $overnightOpen = false;

    protected Carbon|Closure|null $now = null;

    protected Carbon|Closure|null $lastAllowedDate = null;

    /** A $to of 24 is midnight at the end of the day. */
    public function hours(int $from, int $to): static
    {
        $this->fromHour = $from;
        $this->toHour = $to;

        return $this;
    }

    /**
     * @param  array<int, string>|Closure  $dates  Y-m-d strings, one per rendered column.
     */
    public function dates(array|Closure $dates): static
    {
        $this->dates = $dates;

        return $this;
    }

    /**
     * @param  array<int, string>|Closure  $cells  Y-m-d H:i cell keys that cannot be painted.
     */
    public function lockedCells(array|Closure $cells): static
    {
        $this->lockedCells = $cells;

        return $this;
    }

    public function overnightOpen(bool|Closure $open): static
    {
        $this->overnightOpen = $open;

        return $this;
    }

    public function now(Carbon|Closure $now): static
    {
        $this->now = $now;

        return $this;
    }

    public function lastAllowedDate(Carbon|Closure $date): static
    {
        $this->lastAllowedDate = $date;

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getDates(): array
    {
        return array_values($this->evaluate($this->dates));
    }

    /**
     * @return array<int, string>
     */
    public function getTimes(): array
    {
        $times = [];

        for ($minutes = 0; $minutes < 24 * 60; $minutes += AvailabilityPlanner::STEP_MINUTES) {
            $times[] = sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
        }

        return $times;
    }

    public function isWithinHours(string $time): bool
    {
        return $time >= sprintf('%02d:00', $this->getFromHour())
            && $time < sprintf('%02d:00', $this->getToHour());
    }

    public function getFromHour(): int
    {
        return (int) $this->evaluate($this->fromHour);
    }

    public function getToHour(): int
    {
        return (int) $this->evaluate($this->toHour);
    }

    public function getStep(): int
    {
        return AvailabilityPlanner::STEP_MINUTES;
    }

    public function getMinimumSlotMinutes(): int
    {
        return AvailabilityService::MINIMUM_SLOT_DURATION_MINUTES;
    }

    /**
     * @return array<int, string>
     */
    public function getLockedCells(): array
    {
        $cells = array_values(array_unique($this->evaluate($this->lockedCells)));
        sort($cells);

        return $cells;
    }

    public function getNow(): Carbon
    {
        return $this->evaluate($this->now) ?? now()->utc();
    }

    public function getLastAllowedDate(): Carbon
    {
        return $this->evaluate($this->lastAllowedDate) ?? now()->utc();
    }

    public function isOvernightOpen(): bool
    {
        return (bool) $this->evaluate($this->overnightOpen);
    }
}
