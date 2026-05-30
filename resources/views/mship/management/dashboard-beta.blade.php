@extends('layout')

@php
	$certSyncTitle = !is_null($_account->cert_checked_at)
	    ? 'Last updated with VATSIM.net ' . $_account->cert_checked_at->diffForHumans()
	    : 'Not yet updated with VATSIM.net.';
	$initials = strtoupper(mb_substr($_account->name, 0, 1));
	$currentAtcRating = $_account->qualification_atc;
	$currentPilotRating = $_account->qualification_pilot;
	$currentMilitaryPilotRating = $_account->qualification_pilot_military;
@endphp

@section('styles')
	@vite('resources/assets/css/mship-dashboard.css')
@endsection

@section('content')
	<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5 text-sm leading-normal text-gray-900"
		x-data="{ emailModal: false, copied: false }">
		<div
			class="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-amber-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
			<span>You're viewing the <strong>beta dashboard</strong>. We'd love your feedback while we refine it.</span>
			<a href="{{ route('mship.manage.dashboard') }}" class="font-medium text-amber-950 hover:underline shrink-0">
				Switch to classic dashboard
			</a>
		</div>

		<x-mship.dashboard.card title="Personal Details" icon="fa fa-user">
			<x-slot name="actions">
				<a href="{{ route('mship.manage.cert.update') }}" class="text-white hover:text-sky-100" title="{{ $certSyncTitle }}">
					<i class="fa fa-sync"></i>
				</a>
			</x-slot>

			<div class="px-5 py-4">
				<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
					<div class="flex items-center gap-3">
						<div
							class="size-11 shrink-0 rounded-full bg-brand/10 text-brand flex items-center justify-center text-lg font-semibold">
							{{ $initials }}
						</div>
						<div>
							<p class="text-lg font-semibold tracking-tight text-gray-900 m-0">{{ $_account->name }}</p>
							<p class="mt-0.5 text-sm text-gray-500 mb-0">
								CID
								<button type="button"
									class="font-medium text-brand hover:text-sky-700 underline decoration-dotted underline-offset-2 border-0 bg-transparent p-0 cursor-pointer"
									@click="navigator.clipboard.writeText('{{ $_account->id }}'); copied = true; setTimeout(() => copied = false, 2000)">
									{{ $_account->id }}
								</button>
								<span x-show="copied" x-cloak x-transition class="ml-2 text-xs text-gray-600">Copied</span>
							</p>
						</div>
					</div>
					@if ($roster)
						<a href="{{ route('site.roster.show', ['account' => $_account->id]) }}"
							class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20 hover:bg-green-100 no-underline">
							<span class="size-1.5 rounded-full bg-green-500"></span>
							Active on roster
						</a>
					@else
						<a href="{{ route('site.roster.index') }}"
							class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20 hover:bg-red-100 no-underline">
							<span class="size-1.5 rounded-full bg-red-500"></span>
							Inactive on roster
						</a>
					@endif
				</div>
				<dl class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-3 text-sm">
					<div>
						<dt class="text-gray-500">Membership</dt>
						<dd class="mt-0.5 font-medium text-gray-900 mb-0">
							{{ $_account->status_string }}
							{{ !is_null($_account->primary_state) ? $_account->primary_state->name : 'unknown state' }}
							Member
						</dd>
					</div>
					<div>
						<dt class="text-gray-500">Last SSO login</dt>
						<dd class="mt-0.5 font-medium text-gray-900 mb-0">
							@if ($_account->last_login_ip)
								<span class="font-mono text-xs sm:text-sm">{{ $_account->last_login_ip }}</span>
							@else
								<span class="text-gray-500 italic">No login history available</span>
							@endif
						</dd>
					</div>
					<div>
						<dt class="text-gray-500">Controller roster</dt>
						<dd class="mt-0.5 font-medium mb-0">
							@if ($roster)
								<a href="{{ route('site.roster.show', ['account' => $_account->id]) }}"
									class="text-brand hover:text-sky-700">View your roster profile</a>
							@else
								<a href="{{ route('site.roster.index') }}" class="text-brand hover:text-sky-700">Browse the roster</a>
							@endif
						</dd>
					</div>
				</dl>

				<div class="mt-4 border-t border-gray-100 pt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
					<x-mship.dashboard.current-rating label="ATC rating" :qualification="$currentAtcRating" />
					<x-mship.dashboard.current-rating label="Pilot rating" :qualification="$currentPilotRating" />
					@if ($currentMilitaryPilotRating)
						<x-mship.dashboard.current-rating label="Military pilot rating" :qualification="$currentMilitaryPilotRating" class="sm:col-span-2" />
					@endif
				</div>
			</div>

			<x-slot name="footer">
				<a href="{{ route('mship.manage.cert.update') }}"
					class="text-white hover:text-sky-50 inline-flex items-center gap-2 no-underline" title="{{ $certSyncTitle }}">
					<i class="fa fa-sync text-xs"></i>
					Details look incorrect? Sync from VATSIM.net
				</a>
			</x-slot>
		</x-mship.dashboard.card>

		<div class="grid grid-cols-1 items-stretch gap-5 lg:grid-cols-2 lg:gap-6">
			<div class="flex flex-col gap-5">

				<x-mship.dashboard.card title="Email addresses" icon="fa fa-envelope">
					<x-slot name="actions">
						<a href="{{ route('mship.manage.email.add') }}" class="p-1 text-white hover:text-sky-100" title="Add email">
							<i class="fa fa-plus"></i>
						</a>
						<a href="{{ route('mship.manage.email.assignments') }}" class="p-1 text-white hover:text-sky-100"
							title="Assignments">
							<i class="fa fa-cog"></i>
						</a>
					</x-slot>

					<ul class="divide-y divide-gray-100 list-none m-0 p-0">
						<li class="px-5 py-3 flex flex-col sm:flex-row sm:items-center gap-2 sm:justify-between">
							<div class="min-w-0 flex-1">
								<p class="text-xs text-gray-500 mb-0">Primary email</p>
								<p class="mt-0.5 font-medium text-gray-900 truncate mb-0">{{ $_account->email }}</p>
							</div>
							<div class="flex flex-wrap items-center gap-3 sm:gap-4">
								<span
									class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Verified</span>
								<button type="button" @click="emailModal = true"
									class="rounded-md bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 ring-1 ring-inset ring-amber-600/20 hover:bg-amber-100 border-0 cursor-pointer">
									Change
								</button>
							</div>
						</li>
						@forelse ($_account->secondaryEmails as $email)
							<li class="px-5 py-3 flex flex-col sm:flex-row sm:items-center gap-2 sm:justify-between">
								<div class="min-w-0 flex-1">
									<p class="text-xs text-gray-500 mb-0">Secondary email</p>
									<p class="mt-0.5 font-medium text-gray-900 truncate mb-0">{{ $email->email }}</p>
									<p class="mt-1 text-xs text-gray-400 mb-0">Added {{ $email->created_at->toFormattedDateString() }}</p>
								</div>
								<div class="flex flex-wrap items-center gap-3 sm:gap-4">
									@if ($email->verified_at === null)
										<span
											class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-inset ring-amber-600/20">Unverified</span>
									@else
										<span
											class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Verified</span>
									@endif
									<a href="{{ route('mship.manage.email.delete', ['email' => $email->id]) }}"
										class="text-sm font-medium text-red-600 hover:text-red-700 no-underline">Delete</a>
								</div>
							</li>
						@empty
							<li class="px-5 py-3 text-sm text-gray-500">You have no secondary email addresses.</li>
						@endforelse
					</ul>
				</x-mship.dashboard.card>

				<x-mship.dashboard.card title="Security" icon="fa fa-shield">
					<div class="divide-y divide-gray-100">
						@if ($_account->hasEnabledTwoFactorAuthentication())
							<div class="px-5 py-3 flex flex-col sm:flex-row sm:items-start gap-3 sm:justify-between">
								<div class="flex gap-3 min-w-0">
									<div class="size-9 shrink-0 rounded-lg bg-brand/10 flex items-center justify-center text-brand">
										<i class="fa fa-shield"></i>
									</div>
									<div class="min-w-0">
										<p class="text-xs font-semibold text-gray-900 m-0">Two-factor authentication</p>
										<p class="mt-1 text-sm text-gray-500 mb-0">
											Extra protection using an authenticator app on your phone or computer.
										</p>
									</div>
								</div>
								<div class="flex flex-col items-start sm:items-end gap-2 sm:pl-4 shrink-0">
									<span
										class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Enabled</span>
									<a href="{{ route('two-factor.setup') }}"
										class="text-sm font-medium text-brand hover:text-sky-700 no-underline">Manage settings</a>
									@if ($_account->mandatory_two_factor)
										<span class="text-xs text-gray-400">Cannot be disabled</span>
									@endif
								</div>
							</div>
						@endif

						<div class="px-5 py-3 flex flex-col sm:flex-row sm:items-start gap-3 sm:justify-between">
							<div class="flex gap-3">
								<div class="size-9 shrink-0 rounded-lg bg-gray-100 flex items-center justify-center text-gray-600">
									<i class="fa fa-lock"></i>
								</div>
								<div>
									<p class="text-xs font-semibold text-gray-900 m-0">Secondary password</p>
									<p class="mt-1 text-sm text-gray-500 max-w-prose mb-0">
										Fallback when VATSIM.net certificate services are unavailable. When enabled, you may be asked for this password
										after login.
									</p>
								</div>
							</div>
							<div class="flex flex-col items-start sm:items-end gap-2 sm:pl-4 shrink-0">
								@if ($_account->password)
									<span
										class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Enabled</span>
									<a href="{{ route('password.change') }}"
										class="text-sm font-medium text-brand hover:text-sky-700 no-underline">Modify password</a>
									@if (!$_account->mandatory_password)
										<a href="{{ route('password.delete') }}"
											class="text-xs text-gray-500 hover:text-gray-700 no-underline">Disable</a>
									@else
										<span class="text-xs text-gray-400">Cannot be disabled</span>
									@endif
								@else
									<span
										class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-300">Disabled</span>
									<a href="{{ route('password.create') }}"
										class="text-sm font-medium text-brand hover:text-sky-700 no-underline">Enable password</a>
								@endif
							</div>
						</div>
					</div>
				</x-mship.dashboard.card>

				<x-mship.dashboard.card title="ATC & pilot qualifications" icon="fa fa-graduation-cap">
					<x-slot name="actions">
						<a href="{{ route('mship.manage.cert.update') }}" class="text-white hover:text-sky-100"
							title="{{ $certSyncTitle }}">
							<i class="fa fa-sync"></i>
						</a>
					</x-slot>

					<div class="px-5 py-4 grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4 items-start">
						<div class="min-w-0">
							<p class="text-xs font-semibold text-gray-900 m-0">ATC qualifications</p>
							<p class="text-xs text-gray-500 mt-0.5 mb-0">All achieved ratings</p>
							<div class="mt-3 flex flex-wrap gap-1.5">
								@foreach ($_account->qualifications_atc as $qual)
									@include('mship.management.dashboard-beta._qualification-pill', [
										'qualification' => $qual,
										'grantedAt' => $qual->pivot->created_at,
										'tone' => 'atc',
									])
								@endforeach
								@foreach ($_account->qualifications_atc_training as $qual)
									@include('mship.management.dashboard-beta._qualification-pill', [
										'qualification' => $qual,
										'grantedAt' => $qual->pivot->created_at,
										'tone' => 'training',
									])
								@endforeach
								@if (count($_account->qualifications_atc) < 1 && count($_account->qualifications_atc_training) < 1)
									<p class="text-sm text-gray-500 mb-0">You have no ATC ratings.</p>
								@endif
							</div>
						</div>
						<div class="min-w-0">
							<p class="text-xs font-semibold text-gray-900 m-0">Pilot qualifications</p>
							<p class="text-xs text-gray-500 mt-0.5 mb-0">Including military</p>
							<div class="mt-3 flex flex-wrap gap-1.5">
								@foreach ($_account->qualifications_pilot as $qual)
									@include('mship.management.dashboard-beta._qualification-pill', [
										'qualification' => $qual,
										'grantedAt' => $qual->pivot->created_at,
										'tone' => 'pilot',
									])
								@endforeach
								@foreach ($_account->qualifications_pilot_training as $qual)
									@include('mship.management.dashboard-beta._qualification-pill', [
										'qualification' => $qual,
										'grantedAt' => $qual->pivot->created_at,
										'tone' => 'training',
									])
								@endforeach
								@foreach ($_account->qualifications_pilot_military as $qual)
									@include('mship.management.dashboard-beta._qualification-pill', [
										'qualification' => $qual,
										'grantedAt' => $qual->pivot->created_at,
										'tone' => 'pilot',
									])
								@endforeach
								@if (count($_account->qualifications_pilot) < 1 &&
										count($_account->qualifications_pilot_training) < 1 &&
										count($_account->qualifications_pilot_military) < 1)
									<p class="text-sm text-gray-500 mb-0">You have no pilot ratings.</p>
								@endif
							</div>
						</div>
					</div>

					<x-slot name="footer">
						<a href="{{ route('mship.manage.cert.update') }}" class="text-white hover:text-sky-50 no-underline"
							title="{{ $certSyncTitle }}">
							Sync qualifications from VATSIM.net
						</a>
					</x-slot>
				</x-mship.dashboard.card>

			</div>

			<div class="flex flex-col gap-5">

				@if (!$_account->is_banned)
					<x-mship.dashboard.card title="TeamSpeak" icon="fab fa-teamspeak">
						<x-slot name="actions">
							<a href="{{ route('teamspeak.new') }}" class="text-white hover:text-sky-100" title="New registration">
								<i class="fa fa-plus"></i>
							</a>
						</x-slot>

						<div class="px-5 py-4 space-y-3">
							@if (count($_account->teamspeakRegistrations) === 0)
								<p class="text-sm text-gray-500 mb-0">No registrations found.</p>
							@endif
							@foreach ($_account->teamspeakRegistrations as $tsreg)
								<article class="rounded-lg bg-gray-50 p-4 ring-1 ring-gray-200/60">
									<div class="flex items-center justify-between gap-2">
										<span class="text-xs font-semibold text-gray-900">Registration #{{ $tsreg->id }}</span>
										<a href="{{ route('teamspeak.delete', [$tsreg->id]) }}"
											class="text-xs font-medium text-red-600 hover:text-red-700 no-underline">Remove</a>
									</div>
									<dl class="mt-3 grid grid-cols-2 gap-x-3 gap-y-2 text-xs mb-0">
										<div>
											<dt class="text-gray-500">Created</dt>
											<dd class="font-medium mb-0" title="{{ $tsreg->created_at }}">{{ $tsreg->created_at->diffForHumans() }}
											</dd>
										</div>
										@if (is_null($tsreg->dbid))
											<div class="col-span-2">
												<dt class="text-gray-500">Status</dt>
												<dd class="mb-0"><a href="{{ route('teamspeak.new') }}"
														class="text-brand font-medium no-underline">Complete registration</a></dd>
											</div>
										@else
											<div class="col-span-2">
												<dt class="text-gray-500">Unique ID</dt>
												<dd class="font-mono font-medium truncate mb-0">{{ $tsreg->uid }}</dd>
											</div>
											<div>
												<dt class="text-gray-500">Last IP</dt>
												<dd class="font-medium font-mono mb-0">{{ $tsreg->last_ip }}</dd>
											</div>
											<div>
												<dt class="text-gray-500">Last login</dt>
												<dd class="font-medium mb-0">{{ $tsreg->last_login }}</dd>
											</div>
											<div class="col-span-2">
												<dt class="text-gray-500">OS</dt>
												<dd class="font-medium mb-0">{{ $tsreg->last_os }}</dd>
											</div>
										@endif
									</dl>
								</article>
							@endforeach
						</div>

						<x-slot name="footer">
							<a href="{{ route('site.community.teamspeak') }}" class="text-white hover:text-sky-50 no-underline">
								<i class="fa fa-info-circle mr-1"></i>TeamSpeak guide
							</a>
						</x-slot>
					</x-mship.dashboard.card>
				@endif

				<x-mship.dashboard.card title="Discord" icon="fab fa-discord" :stretch="true">
					<x-slot name="actions">
						@if (!$_account->discord_id)
							<a href="{{ route('discord.create') }}" class="text-white hover:text-sky-100" title="Link Discord">
								<i class="fa fa-plus-circle"></i>
							</a>
						@endif
					</x-slot>

					<div class="flex-1 flex flex-col justify-center px-5 py-4 space-y-3 min-h-[7rem]">
						<p class="text-sm text-gray-600 mb-0">
							Our community Discord server is the place to chat with other members of the UK Division and the wider network.
						</p>
						@if (!$_account->discord_id)
							<p class="text-sm text-gray-600 mb-0">
								You'll be asked to authorise VATSIM UK to add you to our server and assign the relevant permissions.
							</p>
							<a href="{{ route('discord.create') }}"
								class="flex w-full items-center justify-center gap-2 rounded-md bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-600 no-underline">
								<i class="fab fa-discord"></i>
								Link Discord account
							</a>
						@else
							<div class="flex items-center gap-2 rounded-lg bg-indigo-50 px-3 py-2.5 ring-1 ring-indigo-200">
								<i class="fab fa-discord text-indigo-600"></i>
								<span class="text-sm font-medium text-indigo-900">
									@if ($_account->discord_user)
										{{ '@' . ($_account->discord_user['username'] ?? 'Unknown') }}
									@else
										Discord ID {{ $_account->discord_id }}
									@endif
								</span>
							</div>
							<p class="text-center mb-0">
								<a href="{{ route('discord.destroy') }}"
									class="text-sm font-medium text-gray-600 hover:text-red-600 no-underline">Unlink account</a>
							</p>
						@endif
					</div>
				</x-mship.dashboard.card>

				<x-mship.dashboard.card title="UK Controller Plugin" icon="fa fa-signal">
					<div class="px-5 py-4 space-y-3">
						@if (count($pluginKeys) > 0)
							<ul class="space-y-3 list-none m-0 p-0">
								@foreach ($pluginKeys as $key)
									<li class="rounded-lg bg-gray-50 p-3 ring-1 ring-gray-200/60 text-sm">
										<span class="font-semibold">Key {{ \App\Libraries\UKCP::getKeyForToken($key) }}</span>
										<dl class="mt-2 grid grid-cols-2 gap-2 text-xs mb-0">
											<div>
												<dt class="text-gray-500">Created</dt>
												<dd class="mb-0" title="{{ $key->created_at }}">
													{{ \Carbon\Carbon::createFromTimeString($key->created_at)->diffForHumans() }}</dd>
											</div>
											<div>
												<dt class="text-gray-500">Expires</dt>
												<dd class="mb-0" title="{{ $key->expires_at }}">
													{{ \Carbon\Carbon::createFromTimeString($key->expires_at)->diffForHumans() }}</dd>
											</div>
										</dl>
									</li>
								@endforeach
							</ul>
							<a href="{{ route('ukcp.token.invalidate') }}"
								class="flex w-full items-center justify-center rounded-md bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-900 ring-1 ring-inset ring-amber-600/20 hover:bg-amber-100 no-underline">
								Invalidate token(s)
							</a>
							<p class="text-xs text-gray-500 mb-0">
								If you are currently online, some operations such as squawk assignments may fail.
							</p>
						@else
							<p class="text-sm text-gray-500 mb-0">No keys found. EuroScope will guide you through setup when required.</p>
						@endif
						<p class="text-xs text-gray-500 mb-0">
							Keys identify you in EuroScope. <strong class="text-gray-700">Do not share them</strong> — actions are logged to
							your account.
						</p>
					</div>

					<x-slot name="footer">
						<a href="{{ route('ukcp.guide') }}" class="text-white hover:text-sky-50 no-underline">
							<i class="fa fa-info-circle mr-1"></i>UK Controller Plugin guide
						</a>
					</x-slot>
				</x-mship.dashboard.card>

			</div>
		</div>
	</div>

	<div x-show="emailModal" x-cloak class="fixed inset-0 z-[1060] flex items-center justify-center p-4" role="dialog"
		aria-modal="true" aria-labelledby="primary-email-modal-title">
		<div class="fixed inset-0 bg-gray-900/50" @click="emailModal = false"></div>
		<div class="relative w-full max-w-2xl rounded-xl bg-white shadow-xl ring-1 ring-gray-200 overflow-hidden">
			<div class="flex items-center justify-between gap-3 bg-uknavy px-4 py-2.5 text-white">
				<p id="primary-email-modal-title" role="heading" aria-level="2"
					class="m-0 text-base font-semibold leading-snug text-white">
					Change primary VATSIM email
				</p>
				<div class="flex shrink-0 items-center gap-2 text-white [&_svg]:text-white">
					<button type="button" @click="emailModal = false"
						class="cursor-pointer border-0 bg-transparent p-0 leading-none text-white hover:text-sky-100">
						<i class="fa fa-times"></i>
					</button>
				</div>
			</div>
			<div class="px-6 py-4 space-y-4">
				<p class="text-sm text-center text-gray-600 mb-0">
					<strong class="text-gray-900">All primary email changes are handled by central membership</strong>
					and may take up to 24 hours to be reflected on our systems.
				</p>
				<div class="rounded-lg overflow-hidden ring-1 ring-gray-200">
					<div class="bg-uknavy px-4 py-2 text-xs italic leading-snug text-white">https://my.vatsim.net/user/email</div>
					<div class="aspect-video">
						<iframe class="size-full border-0" src="https://my.vatsim.net/user/email"
							title="VATSIM email settings"></iframe>
					</div>
				</div>
			</div>
			<div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100">
				<button type="button" @click="emailModal = false"
					class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-gray-300 hover:bg-gray-50 border-0 cursor-pointer">
					Close
				</button>
			</div>
		</div>
	</div>
@endsection
