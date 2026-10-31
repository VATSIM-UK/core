<div class="staff-directory text-gray-800">
	@if (count($directory->leads) > 0)
		<div class="mb-10 grid grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-6 max-md:mb-8">
			@foreach ($directory->leads as $entry)
				<div class="staff-directory__lead text-center">
					<div
						class="mx-auto mb-5 flex size-[6.5rem] items-center justify-center rounded-full bg-[#edf2f7] text-[2.6rem] font-bold text-uknavy max-md:size-[5.5rem] max-md:text-[2.2rem]"
						aria-hidden="true">{{ $entry->initials }}</div>
					<p @class([
						'm-0 text-[2.2rem] font-bold leading-[1.2] text-uknavy max-md:text-[2rem]',
						'font-medium italic text-gray-500' => $entry->name === 'Vacant',
					])>
						{{ $entry->name }}</p>
					<div class="mt-3 flex flex-wrap justify-center gap-2">
						@foreach ($entry->jobTitles as $jobTitle)
							<span
								class="inline-block rounded-[.4rem] bg-[#edf2f7] px-3 py-[.35rem] text-[1.3rem] leading-[1.3] text-gray-700">
								{!! nl2br(e($jobTitle)) !!}
							</span>
						@endforeach
					</div>
				</div>
			@endforeach
		</div>
	@endif

	@if (count($directory->team) > 0)
		<h4 class="m-0 mb-2 text-[1.8rem] font-bold text-uknavy">Staff members</h4>
		<div class="border-t border-[#dbe1e8]">
			@foreach ($directory->team as $entry)
				<div
					class="staff-directory__member flex items-center gap-[.8rem] border-b border-[#dbe1e8] py-[.65rem] last:border-b-0">
					<div
						class="flex size-[3.2rem] shrink-0 items-center justify-center rounded-full bg-[#edf2f7] text-[1.3rem] font-bold text-uknavy"
						aria-hidden="true">{{ $entry->initials }}</div>
					<div class="min-w-0">
						<p @class([
							'm-0 text-[1.4rem] font-bold leading-tight text-uknavy',
							'font-medium italic text-gray-500' => $entry->name === 'Vacant',
						])>
							{{ $entry->name }}</p>
						<p class="m-0 mt-[.1rem] text-[1.2rem] leading-[1.25] text-gray-600">
							@foreach ($entry->jobTitles as $jobTitle)
								<span>{!! nl2br(e($jobTitle)) !!}</span>
								@if (!$loop->last)
									<span aria-hidden="true"> · </span>
								@endif
							@endforeach
						</p>
					</div>
				</div>
			@endforeach
		</div>
	@endif
</div>
