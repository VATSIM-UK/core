@php
	$authenticatorFaqs = [
	    [
	        'title' => 'Google Authenticator',
	        'body' =>
	            'Tap +, then choose Scan a QR code or Enter a setup key. Use the QR code on the right, or paste the manual setup code if scanning is not available.',
	    ],
	    [
	        'title' => 'Microsoft Authenticator',
	        'body' =>
	            'Tap + and choose Other account (Google, Facebook, etc.). Scan the QR code, or choose Enter code manually and paste the setup key shown on this page.',
	    ],
	    [
	        'title' => 'Authy',
	        'body' =>
	            'Tap +, then Scan QR Code or Enter key manually. Give the account a name, paste the setup key if needed, and leave the token type as Time-based.',
	    ],
	    [
	        'title' => '1Password',
	        'body' =>
	            'Open 1Password, add a One-Time Password item, and scan the QR code. If you cannot scan, choose Enter setup key and paste the manual code from this page.',
	    ],
	];
@endphp

<div class="two-factor-setup-faqs" x-data="{ expanded: false }">
	<button type="button" class="two-factor-setup-faqs-toggle" @click="expanded = ! expanded" :aria-expanded="expanded">
		<i class="fa fa-chevron-right two-factor-setup-faqs-chevron"
			:class="{ 'two-factor-setup-faqs-chevron--expanded': expanded }" aria-hidden="true"></i>
		<strong>Common authenticator apps</strong>
		<span class="text-muted" x-text="expanded ? '(Hide help)' : '(Show help)'">(Show help)</span>
	</button>

	<div x-show="expanded" x-collapse x-cloak style="display: none;">
		@foreach ($authenticatorFaqs as $faq)
			<div class="two-factor-setup-faq">
				<p style="margin-bottom: 0.25em;"><strong>{{ $faq['title'] }}</strong></p>
				<p class="text-muted" style="margin-bottom: 1em;">{{ $faq['body'] }}</p>
			</div>
		@endforeach
	</div>
</div>
