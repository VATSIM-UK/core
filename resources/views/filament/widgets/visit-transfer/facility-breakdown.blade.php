<x-filament-widgets::widget>
	<x-filament::section heading="By Facility">
		<table class="w-full text-sm">
			<thead>
				<tr class="text-left text-gray-500">
					<th class="py-1">Facility</th>
					<th class="py-1 text-right">Total</th>
					<th class="py-1 text-right">Accepted</th>
					<th class="py-1 text-right">Rejected</th>
				</tr>
			</thead>
			<tbody>
				@forelse ($this->getRows() as $row)
					<tr class="border-t border-gray-100 dark:border-white/5">
						<td class="py-1">{{ $row['name'] }}</td>
						<td class="py-1 text-right">{{ $row['total'] }}</td>
						<td class="py-1 text-right text-success-600">{{ $row['accepted'] }}</td>
						<td class="py-1 text-right text-danger-600">{{ $row['rejected'] }}</td>
					</tr>
				@empty
					<tr>
						<td colspan="4" class="py-4 text-center text-gray-500">No data for this period.</td>
					</tr>
				@endforelse
			</tbody>
		</table>
	</x-filament::section>
</x-filament-widgets::widget>
