<x-filament-panels::page>
	@foreach ($this->getCategories() as $category)
		<x-filament::section :heading="$category" collapsible>
			@livewire(\App\Filament\Training\Pages\Statistics\Widgets\TrainingGroupStatisticsWidget::class, ['category' => $category], key('training-group-stats-' . str($category)->slug('_')))
		</x-filament::section>
	@endforeach
</x-filament-panels::page>
