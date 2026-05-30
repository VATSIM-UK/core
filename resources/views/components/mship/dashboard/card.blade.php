@props(['title', 'icon' => null, 'stretch' => false])

<section
	{{ $attributes->class([
	    'bg-white rounded-xl shadow-sm ring-1 ring-gray-200/80 overflow-hidden flex flex-col',
	    'flex-1 min-h-0' => $stretch,
	]) }}>
	<div class="bg-uknavy text-white px-4 py-2.5 flex items-center justify-between gap-3 shrink-0">
		<p role="heading" aria-level="2"
			class="m-0 flex items-center gap-1.5 text-base font-semibold leading-snug text-white [&_svg]:text-white">
			@if ($icon)
				<i class="{{ $icon }} shrink-0 text-sm text-white" aria-hidden="true"></i>
			@endif
			{{ $title }}
		</p>
		@if (isset($actions))
			<div
				class="flex shrink-0 items-center gap-2 text-white [&_a]:text-white [&_a:hover]:text-sky-100 [&_a]:no-underline [&_button]:text-white [&_button:hover]:text-sky-100 [&_svg]:text-white">
				{{ $actions }}
			</div>
		@endif
	</div>

	<div @class([
		'flex-1 min-h-0 flex flex-col' => $stretch,
	])>
		{{ $slot }}
	</div>

	@if (isset($footer))
		<div class="bg-brand shrink-0 px-4 py-2.5 text-sm text-white">
			{{ $footer }}
		</div>
	@endif
</section>
