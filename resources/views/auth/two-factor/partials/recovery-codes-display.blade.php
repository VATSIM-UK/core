<div class="two-factor-recovery-codes-display" x-data="{
    copied: false,
    copyCodes() {
        navigator.clipboard.writeText(this.$refs.codes.textContent.trim()).then(() => {
            this.copied = true;
            setTimeout(() => { this.copied = false; }, 2000);
        });
    },
    downloadCodes() {
        const text = this.$refs.codes.textContent.trim();
        const blob = new Blob([text + '\n'], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'vatsim-uk-recovery-codes.txt';
        link.click();
        URL.revokeObjectURL(url);
    },
}">
	<div class="well well-sm two-factor-recovery-codes-well">
		<pre class="two-factor-recovery-codes"><code x-ref="codes">{{ implode("\n", $recoveryCodes) }}</code></pre>
	</div>
	<div class="two-factor-recovery-codes-actions">
		<a href="#" class="two-factor-recovery-codes-action" @click.prevent="copyCodes()">
			<i class="fa fa-copy" aria-hidden="true"></i> Copy to clipboard
		</a>
		<a href="#" class="two-factor-recovery-codes-action" @click.prevent="downloadCodes()">
			<i class="fa fa-download" aria-hidden="true"></i> Download as text file
		</a>
		<span x-show="copied" x-cloak class="text-success two-factor-recovery-codes-copied">Copied!</span>
	</div>
</div>
