<div class="two-factor-setup-faqs two-factor-setup-faqs--above-recovery-codes" x-data="{ expanded: false }">
	<button type="button" class="two-factor-setup-faqs-toggle" @click="expanded = ! expanded" :aria-expanded="expanded">
		<i class="fa fa-chevron-right two-factor-setup-faqs-chevron"
			:class="{ 'two-factor-setup-faqs-chevron--expanded': expanded }" aria-hidden="true"></i>
		<strong>Why are recovery codes important?</strong>
		<span class="text-muted" x-text="expanded ? '(Hide help)' : '(Show help)'">(Show help)</span>
	</button>

	<div x-show="expanded" x-collapse x-cloak style="display: none;">
		<div class="two-factor-setup-faq">
			<p class="text-muted">
				Your authenticator application generates the codes you normally use to sign in. If you lose your phone, replace
				your device, or uninstall the app without backing it up, you may no longer be able to sign in.
			</p>
			<p class="text-muted">
				Recovery codes are a one-time fallback for exactly that situation. Each code works once, so save the full set
				now in a secure place — such as a password manager or encrypted note — separate from your authenticator app.
			</p>
			<p class="text-muted" style="margin-bottom: 0;">
				Without recovery codes, regaining access to your account can take longer and may require staff assistance.
			</p>
		</div>
	</div>
</div>
