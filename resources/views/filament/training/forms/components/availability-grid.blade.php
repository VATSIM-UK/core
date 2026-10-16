{{-- Dark variants of bg-white / text-gray-500 are !important: the filament-date-range plugin ships unlayered copies of those utilities. --}}
@php
	$dates = $field->getDates();
	$lockedCells = $field->getLockedCells();
	$now = $field->getNow();
	$step = $field->getStep();
	$minimum = $field->getMinimumSlotMinutes();
	$today = $now->toDateString();
	$nowSlot = $now->copy()->ceilMinutes($step)->format('Y-m-d H:i');
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
	<div wire:key="availability-grid-{{ $dates[0] ?? 'empty' }}" x-data="{
    state: $wire.$entangle(@js($getStatePath())),
    painting: false,
    mode: true,
    last: null,
    overnight: {{ $field->isOvernightOpen() ? 'true' : 'false' }},
    locked: new Set(@js($lockedCells)),
    dates: @js($dates),
    now: @js($now->getTimestampMs()),
    hours: @js([sprintf('%02d:00', $field->getFromHour()), sprintf('%02d:00', $field->getToHour())]),
    hasOvernight: false,
    painted: new Set(),
    warned: new Set(),
    has(slot) {
        return this.painted.has(slot)
    },
    set(slots, value) {
        const current = new Set(this.state ?? [])
        let changed = false

        for (const slot of slots) {
            if (this.locked.has(slot) || current.has(slot) === value) {
                continue
            }

            value ? current.add(slot) : current.delete(slot)
            changed = true
        }

        if (changed) {
            this.state = [...current]
            this.recompute()
            this.changed()
        }
    },
    changed() {
        this.$store.availabilityAutosave.queue(this.$wire)
    },
    saveStatus() {
        return this.$store.availabilityAutosave?.status ?? 'saved'
    },
    start(event) {
        {{-- Primary button only; touch and pen report 0 too. --}}
        if (event.button !== 0) {
            return
        }

        const slot = event.target.closest('[data-slot]')?.dataset.slot

        if (!slot) {
            return
        }

        this.painting = true
        this.mode = !this.has(slot)
        this.last = { x: event.clientX, y: event.clientY }
        this.set([slot], this.mode)
    },
    move(event) {
        if (!this.painting) {
            return
        }

        {{-- Fast drags skip cells between events, so sample the path from the last event every 4px. --}}
        const from = this.last
        const steps = Math.max(1, Math.ceil(Math.hypot(event.clientX - from.x, event.clientY - from.y) / 4))
        const slots = []

        for (let i = 1; i <= steps; i++) {
            const slot = document.elementFromPoint(
                from.x + (event.clientX - from.x) * i / steps,
                from.y + (event.clientY - from.y) * i / steps,
            )?.closest('[data-slot]')?.dataset.slot

            if (slot) {
                slots.push(slot)
            }
        }

        this.last = { x: event.clientX, y: event.clientY }
        this.set(slots, this.mode)
    },
    inWeek(slot) {
        return this.dates.includes(slot.slice(0, 10))
    },
    notify(title, status = 'success') {
        new FilamentNotification().title(title).status(status).send()
    },
    clearWeek() {
        this.state = (this.state ?? []).filter((slot) => !this.inWeek(slot))
        this.recompute()
        this.changed()
    },
    {{-- Kept in a store as weekday + time, since the grid re-initialises on every week change. --}}
    copyWeek() {
        const cells = [...(this.state ?? []), ...this.locked]
            .filter((slot) => this.inWeek(slot))
            .map((slot) => ({ day: this.dates.indexOf(slot.slice(0, 10)), time: slot.slice(11) }))

        if (cells.length === 0) {
            this.notify('Nothing to copy this week', 'warning')

            return
        }

        this.$store.availabilityClipboard.cells = cells
        this.notify('Week copied, open another week and paste it there')
    },
    requestPaste() {
        if ((this.state ?? []).some((slot) => this.inWeek(slot))) {
            this.$wire.mountAction('pasteWeek')

            return
        }

        this.pasteWeek()
    },
    pasteWeek() {
        const cells = this.$store.availabilityClipboard.cells ?? []
        const targets = cells.map((cell) => this.dates[cell.day] + ' ' + cell.time)
        const pasted = targets.filter((slot) =>
            !this.locked.has(slot) && Date.parse(slot.replace(' ', 'T') + ':00Z') >= this.now)

        this.state = [...(this.state ?? []).filter((slot) => !this.inWeek(slot)), ...pasted]
        this.recompute()
        this.changed()

        const skipped = targets.length - pasted.length

        this.notify(skipped > 0 ?
            'Week pasted, ' + skipped + ' slots in the past or locked were skipped' :
            'Week pasted')
    },
    ranges() {
        const keys = [...(this.state ?? [])].sort()
        const runs = []
        let current = null

        for (const slot of keys) {
            const date = slot.slice(0, 10)
            const start = Date.parse(slot.replace(' ', 'T') + ':00Z')

            if (current && current.date === date && current.end === start) {
                current.end = start + {{ $step }} * 60000
                current.cells.push(slot)
            } else {
                current = { date: date, start: start, end: start + {{ $step }} * 60000, cells: [slot] }
                runs.push(current)
            }
        }

        return runs
    },
    recompute() {
        const runs = this.ranges()
        const warned = new Set()

        for (const run of runs) {
            if (this.isShort(run)) {
                run.cells.forEach((slot) => warned.add(slot))
            }
        }

        this.painted = new Set(this.state ?? [])
        this.warned = warned
        this.hasOvernight = [...this.painted, ...this.locked].some((slot) =>
            this.inWeek(slot) && (slot.slice(11) < this.hours[0] || slot.slice(11) >= this.hours[1]))

        if (this.hasOvernight) {
            this.overnight = true
        }
    },
    isShort(run) {
        return run.end - run.start < {{ $minimum }} * 60000
    },
    init() {
        if (!Alpine.store('availabilityClipboard')) {
            Alpine.store('availabilityClipboard', { cells: null })
        }

        {{-- Lives in a store so a pending save outlasts the grid re-initialising on week change. --}}
        if (!Alpine.store('availabilityAutosave')) {
            Alpine.store('availabilityAutosave', {
                status: 'saved',
                version: 0,
                timer: null,
                queue(wire) {
                    this.version++
                    this.status = 'pending'
                    clearTimeout(this.timer)
                    this.timer = setTimeout(() => this.flush(wire), 1000)
                },
                async flush(wire) {
                    const version = this.version
                    let saved = false
                    this.status = 'saving'

                    try {
                        saved = await wire.save()
                    } catch {}

                    if (version === this.version) {
                        this.status = saved ? 'saved' : 'error'
                    }
                },
            })

            window.addEventListener('beforeunload', (event) => {
                if (Alpine.store('availabilityAutosave').status !== 'saved') {
                    event.preventDefault()
                }
            })
        }

        this.recompute()
        this.$watch('state', () => this.recompute())
    },
}"
		data-overnight="{{ $field->isOvernightOpen() ? '1' : '0' }}"
		x-effect="if ($el.dataset.overnight === '1') overnight = true" x-on:availability-paste-week.window="pasteWeek()"
		{{ $getExtraAttributeBag()->class(['flex flex-col gap-4']) }}>
		<div class="flex flex-wrap items-center justify-between gap-3">
			<span class="flex items-center gap-1.5 text-sm text-gray-500 dark:!text-gray-400">
				<x-filament::icon icon="heroicon-m-check-circle" class="size-4 text-success-500" x-show="saveStatus() === 'saved'" />
				<x-filament::icon icon="heroicon-m-pencil" class="size-4" x-show="saveStatus() === 'pending'" x-cloak />
				<x-filament::loading-indicator class="size-4" x-show="saveStatus() === 'saving'" x-cloak />
				<x-filament::icon icon="heroicon-m-exclamation-circle" class="size-4 text-danger-500"
					x-show="saveStatus() === 'error'" x-cloak />
				<span
					x-text="{ saved: 'All changes saved', pending: 'Unsaved changes', saving: 'Saving…', error: 'Not saved' }[saveStatus()]">All
					changes saved</span>
			</span>

			<div class="flex items-center gap-2">
				<x-filament::button size="sm" color="gray" x-on:click="overnight = ! overnight"
					x-bind:disabled="hasOvernight">
					<x-filament::icon icon="heroicon-m-moon" :size="\Filament\Support\Enums\IconSize::Small" x-show="! overnight" />
					<x-filament::icon icon="heroicon-m-sun" :size="\Filament\Support\Enums\IconSize::Small" x-show="overnight" x-cloak />
					<span x-show="! overnight">Show overnight</span>
					<span x-show="overnight" x-cloak>Hide overnight</span>
				</x-filament::button>

				<x-filament::dropdown placement="bottom-end">
					<x-slot name="trigger">
						<x-filament::button size="sm" color="gray" icon="heroicon-m-chevron-down" icon-position="after">
							Week
						</x-filament::button>
					</x-slot>

					<x-filament::dropdown.list>
						<x-filament::dropdown.list.item icon="heroicon-m-document-duplicate" x-on:click="close(); copyWeek()">
							Copy week
						</x-filament::dropdown.list.item>

						<x-filament::dropdown.list.item icon="heroicon-m-clipboard-document" x-on:click="close(); requestPaste()"
							x-bind:disabled="!$store.availabilityClipboard?.cells">
							Paste week
						</x-filament::dropdown.list.item>

						<x-filament::dropdown.list.item icon="heroicon-m-trash" color="danger" x-on:click="close(); clearWeek()">
							Clear week
						</x-filament::dropdown.list.item>
					</x-filament::dropdown.list>
				</x-filament::dropdown>
			</div>
		</div>

		<div class="overflow-x-auto rounded-lg border border-gray-200 bg-white dark:border-white/10 dark:!bg-gray-900">
			<div class="min-w-[36rem] touch-none select-none" x-on:pointerdown.prevent="start($event)"
				x-on:pointermove="move($event)" x-on:pointerup.window="painting = false"
				x-on:pointercancel.window="painting = false">
				<div class="flex border-b border-gray-200 bg-white dark:border-white/10 dark:!bg-gray-900">
					<div
						class="sticky left-0 z-10 flex w-14 shrink-0 items-center justify-center bg-white text-xs font-medium text-gray-500 dark:!bg-gray-900 dark:!text-gray-400">
						Z
					</div>

					@foreach ($dates as $date)
						@php
							$isToday = $date === $today;
							$day = \Carbon\Carbon::parse($date, 'UTC');
						@endphp

						<div @class([
							'flex flex-1 basis-0 flex-col items-center border-l border-gray-200 py-2 dark:border-white/10',
							'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400' => $isToday,
							'text-gray-950 dark:text-white' => !$isToday,
						])>
							<span @class([
								'text-xs font-medium uppercase',
								'text-gray-500 dark:!text-gray-400' => !$isToday,
							])>
								{{ $day->format('D') }}
							</span>
							<span class="text-sm font-semibold tabular-nums">
								{{ $day->format('d. m.') }}
							</span>
						</div>
					@endforeach
				</div>

				@foreach ($field->getTimes() as $time)
					@php
						$minutes = (int) substr($time, 3, 2);

						$gridline = match ($minutes) {
						    0 => 'border-t border-t-gray-200 dark:border-t-white/10',
						    30 => 'border-t border-dashed border-t-gray-100 dark:border-t-white/5',
						    default => '',
						};
					@endphp

					<div class="flex h-3" @if (!$field->isWithinHours($time)) x-show="overnight" x-cloak @endif>
						<div
							class="sticky left-0 z-10 w-14 shrink-0 bg-white pe-2 text-end text-xs font-medium leading-3 tabular-nums text-gray-500 dark:!bg-gray-900 dark:!text-gray-400 {{ $gridline }}">
							@if ($minutes === 0)
								{{ $time }}
							@endif
						</div>

						@foreach ($dates as $date)
							@php
								$slot = "{$date} {$time}";
								$isLocked = in_array($slot, $lockedCells, true);
								$isPast = !$isLocked && \Carbon\Carbon::parse($slot, 'UTC')->lessThan($now);
								$isEditable = !$isLocked && !$isPast;

								// Painted runs keep hour lines but hide half-hour ones.
								$paintedBorder = $minutes === 0 ? '!border-t-black/15 dark:!border-t-black/40' : '!border-t-transparent';
							@endphp

							<div data-slot="{{ $slot }}" @class([
								'flex-1 basis-0 border-l border-l-gray-200 dark:border-l-white/10',
								$gridline,
								'border-t-2 border-solid !border-t-danger-500' => $slot === $nowSlot,
								'pointer-events-none bg-success-500/40' => $isLocked,
								'pointer-events-none bg-gray-100 dark:bg-white/5' => $isPast,
								'cursor-pointer' => $isEditable,
								'bg-primary-50/50 dark:bg-primary-500/5' => $isEditable && $date === $today,
							])
								@if ($isLocked) title="Saved availability that can't be edited here" @endif
								@if ($isEditable) :class="{
								    '{{ $paintedBorder }}': has('{{ $slot }}'),
								    '!bg-success-500': has('{{ $slot }}') && !warned.has('{{ $slot }}'),
								    '!bg-warning-400 dark:!bg-warning-500': has('{{ $slot }}') && warned.has('{{ $slot }}'),
								    'hover:bg-success-100 dark:hover:bg-success-500/20': !has('{{ $slot }}'),
								}" @endif>
							</div>
						@endforeach
					</div>
				@endforeach
			</div>
		</div>

		<div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:!text-gray-400">
			@foreach ([
								'Available' => 'bg-success-500',
								"Under {$minimum} minutes, not saved" => 'bg-warning-400 dark:bg-warning-500',
								"Saved, can't be edited" => 'bg-success-500/40',
								'Past' => 'bg-gray-100 ring-1 ring-gray-200 dark:bg-white/5 dark:ring-white/10',
				] as $label => $swatch)
				<span class="flex items-center gap-1.5">
					<span class="size-3 rounded-sm {{ $swatch }}"></span>
					{{ $label }}
				</span>
			@endforeach
		</div>
	</div>
</x-dynamic-component>
