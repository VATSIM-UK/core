<x-filament-widgets::widget>
	<div class="fi-wi-stats-overview-stat relative rounded-xl p-6 shadow-sm ring-1 ring-gray-950/5">
		<div class="grid gap-y-2">
			<div class="flex items-center justify-between gap-x-3">
				<span class="fi-wi-stats-overview-stat-label text-sm font-medium text-gray-400 dark:text-gray-400">
					{{ $this->list->name }}
				</span>

				<x-filament::icon-button icon="heroicon-o-arrow-top-right-on-square" tag="a" :href="$this->getViewUrl()"
					label="View waiting list" color="gray" size="sm" />
			</div>

			<span class="fi-wi-stats-overview-stat-value text-3xl font-semibold tracking-tight text-primary-400">
				{{ $this->getCount() }}
			</span>

			<div class="flex items-center gap-x-1">
				<span class="fi-wi-stats-overview-stat-description text-sm">
					Members in this waiting list
				</span>
			</div>
		</div>
	</div>
</x-filament-widgets::widget>
