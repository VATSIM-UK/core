<div class="two-factor-setup-faqs" x-data="{ expanded: false }">
	<button type="button" class="two-factor-setup-faqs-toggle" @click="expanded = ! expanded" :aria-expanded="expanded">
		<i class="fa fa-chevron-right two-factor-setup-faqs-chevron"
			:class="{ 'two-factor-setup-faqs-chevron--expanded': expanded }" aria-hidden="true"></i>
		@if (Auth::user()->mandatory_two_factor)
			<strong>Why is this required?</strong>
		@else
			<strong>Why use two-factor authentication?</strong>
		@endif
		<span class="text-muted" x-text="expanded ? '(Hide help)' : '(Show help)'">(Show help)</span>
	</button>

	<div x-show="expanded" x-collapse x-cloak style="display: none;">
		<div class="two-factor-setup-faq">
			@if (Auth::user()->mandatory_two_factor)
				<p class="text-muted" style="margin-bottom: 0;">Your account role requires two-factor authentication as part of
					VATSIM UK security policy. This helps protect member accounts and sensitive systems from unauthorised access, even
					if a password is compromised. You must complete setup before you can access VATSIM UK services.</p>
			@else
				<p class="text-muted" style="margin-bottom: 0;">Two-factor authentication is strongly recommended for all VATSIM UK
					members. It adds an extra layer of protection to your account and helps keep member data safe if your password is
					ever compromised. While your role does not require it, enabling 2FA is one of the simplest ways to improve your
					account security.</p>
			@endif
		</div>
	</div>
</div>
