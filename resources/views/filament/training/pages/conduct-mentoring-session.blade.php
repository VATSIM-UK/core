<x-filament-panels::page>
	<div wire:poll.5s="autosave"></div>
	{{ $this->sessionDetailsInfolist }}

	{{ $this->form }}

	{{ $this->additionalCommentsForm }}
</x-filament-panels::page>
