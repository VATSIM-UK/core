<x-filament-panels::page>
	<div class="flex items-center gap-4 mb-6">
		@if ($this->record->image)
			<img src="{{ Storage::url($this->record->image) }}" alt="{{ $this->record->name }}"
				class="w-20 h-20 rounded-full object-cover">
		@endif
		<div>
			<h1 class="text-4xl font-bold tracking-tight">{{ $this->record->name }}</h1>
			<p class="text-lg mt-1 text-gray-500">{{ $this->record->description }}</p>
		</div>
	</div>
	{{ $this->table }}
</x-filament-panels::page>
