<div class="two-factor-setup-faqs" x-data="{ expanded: false }">
	<button type="button" class="two-factor-setup-faqs-toggle" @click="expanded = ! expanded" :aria-expanded="expanded">
		<i class="fa fa-chevron-right two-factor-setup-faqs-chevron"
			:class="{ 'two-factor-setup-faqs-chevron--expanded': expanded }" aria-hidden="true"></i>
		<strong>What is two-factor authentication?</strong>
		<span class="text-muted" x-text="expanded ? '(Hide help)' : '(Show help)'">(Show help)</span>
	</button>

	<div x-show="expanded" x-collapse x-cloak style="display: none;">
		<div class="two-factor-setup-faq">
			<p class="text-muted" style="margin-bottom: 0;">Two-factor authentication (also called MFA) adds a second check when
				you sign in. Along with your password, you enter a short code from an authenticator app on your phone. This helps
				protect your VATSIM UK account if your password is ever compromised. You will need to install an authenticator app
				to complete setup.</p>
		</div>
	</div>
</div>
