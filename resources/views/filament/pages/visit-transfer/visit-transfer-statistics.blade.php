<x-filament-panels::page>
	@php
		[$start, $end] = $this->getQuarterRange($this->year, $this->quarter);
		$vtLists = \App\Models\Training\WaitingList::where('feature_toggles->is_vt', true)->get();
	@endphp

	<div class="mb-4">
		<p class="mb-2 text-sm text-white text-right">
			{{ $this->getDescription() }}
		</p>
	</div>

	<div>
		<h1>VT Waiting Lists</h1>
		<p class="text-sm text-gray-400">
			Waiting list counts are not filtered by year, quarter, or application type.
		</p>
	</div>

	<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
		@foreach ($vtLists as $list)
			@livewire(\App\Filament\Admin\Pages\VisitTransfer\Widgets\VTWaitingListWidget::class, ['list' => $list], key('vt-list-' . $list->id))
		@endforeach
	</div>

	<hr class="my-6" />

	<div class="flex justify-between items-start mb-6">
		<h2 class="text-xl font-bold">Application's Overview</h2>
	</div>

	@livewire(
	    \App\Filament\Admin\Pages\VisitTransfer\Widgets\ApplicationOverviewWidget::class,
	    [
	        'year' => $this->year,
	        'type' => $this->type,
	        'start' => $start,
	        'end' => $end,
	    ],
	    key('overview-' . $this->year . '-' . ($this->type ?? 'all') . '-' . $this->quarter)
	)

	@livewire(
	    \App\Filament\Admin\Pages\VisitTransfer\Widgets\ApplicationTrendChart::class,
	    [
	        'year' => $this->year,
	        'type' => $this->type,
	        'start' => $start,
	        'end' => $end,
	    ],
	    key('trend-' . $this->year . '-' . ($this->type ?? 'all') . '-' . $this->quarter)
	)

	@livewire(
	    \App\Filament\Admin\Pages\VisitTransfer\Widgets\FacilityBreakdownWidget::class,
	    [
	        'year' => $this->year,
	        'type' => $this->type,
	        'start' => $start,
	        'end' => $end,
	    ],
	    key('facility-' . $this->year . '-' . ($this->type ?? 'all') . '-' . $this->quarter)
	)

	@livewire(
	    \App\Filament\Admin\Pages\VisitTransfer\Widgets\RatingBreakdownWidget::class,
	    [
	        'year' => $this->year,
	        'type' => $this->type,
	        'quarter' => $this->quarter,
	        'start' => $start,
	        'end' => $end,
	    ],
	    key('rating-breakdown-' . $this->year . '-' . ($this->type ?? 'all') . '-' . $this->quarter)
	)
</x-filament-panels::page>
