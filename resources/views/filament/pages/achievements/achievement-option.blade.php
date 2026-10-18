<div class="flex items-center gap-2">
	@if ($achievement->image)
		<img src="{{ asset('storage/' . $achievement->image) }}" alt="" class="w-8 h-8 rounded-full">
	@endif
	<span class="text-sm font-medium">{{ $achievement->name }}</span>
</div>
