<x-filament-panels::page>
	<div wire:poll.2s="autosave" x-data +
		x-on:beforeunload.window="if ($wire.hasUnsavedChanges) { $event.preventDefault(); $event.returnValue = '' }"></div>
	{{ $this->sessionDetailsInfolist }}

	{{ $this->form }}

	{{ $this->additionalCommentsForm }}
</x-filament-panels::page>
