<x-filament-widgets::widget>
	<x-filament::section>
		<x-slot name="heading">
			Breakdown by Rating
		</x-slot>

		@php $breakdown = $this->getBreakdown(); @endphp

		@if (empty($breakdown))
			<p class="text-sm text-gray-500 dark:text-gray-400">
				No applications with a rating found for this period.
			</p>
		@else
			<div class="overflow-x-auto">
				<table class="w-full text-sm text-left">
					<thead>
						<tr class="border-b border-gray-200 dark:border-white/10">
							<th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">Rating</th>
							<th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">Total</th>
							<th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">Under Review</th>
							<th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">Accepted</th>
							<th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">Rejected</th>
							<th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">Cancelled</th>
							<th class="py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">Acceptance Rate</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($breakdown as $row)
							<tr class="border-b border-gray-100 dark:border-white/5">
								<td class="py-2 pr-4 font-medium">{{ $row['name'] }}</td>
								<td class="py-2 pr-4">{{ $row['total'] }}</td>
								<td class="py-2 pr-4">{{ $row['under_review'] }}</td>
								<td class="py-2 pr-4 text-success-600 dark:text-success-400">{{ $row['accepted'] }}</td>
								<td class="py-2 pr-4 text-danger-600 dark:text-danger-400">{{ $row['rejected'] }}</td>
								<td class="py-2 pr-4">{{ $row['cancelled'] }}</td>
								<td class="py-2 pr-4">
									{{ $row['acceptance_rate'] !== null ? $row['acceptance_rate'] . '%' : '—' }}
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		@endif
	</x-filament::section>
</x-filament-widgets::widget>
