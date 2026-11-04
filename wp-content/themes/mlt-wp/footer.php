<?php
/**
 * Site Footer + Floating WhatsApp Button matching src/components/Footer.tsx and WhatsAppButton.tsx.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$contact = function_exists( 'mlt_get_contact_info' ) ? mlt_get_contact_info() : array(
	'whatsapp_number'  => '+254727246599',
	'whatsapp_url'     => 'https://wa.me/254727246599',
	'instagram_handle' => 'menletstalk254',
	'instagram_url'    => 'https://instagram.com/menletstalk254',
	'email'            => 'info@menletstalk.co.ke',
	'email_url'        => 'mailto:info@menletstalk.co.ke',
);
?>
</div><!-- /.flex-1 -->

<footer data-testid="site-footer" class="border-t border-neutral-200 bg-neutral-50">
	<div class="mx-auto max-w-6xl py-8 pl-6 pr-24 text-sm text-neutral-600">
		<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
			<p>Men Let&apos;s Talk — No man should walk alone.</p>
			<div class="flex flex-wrap gap-x-6 gap-y-2">
				<a href="<?php echo esc_url( $contact['whatsapp_url'] ); ?>" class="hover:underline">WhatsApp: <?php echo esc_html( $contact['whatsapp_number'] ); ?></a>
				<a href="<?php echo esc_url( $contact['instagram_url'] ); ?>" class="hover:underline">Instagram: @<?php echo esc_html( $contact['instagram_handle'] ); ?></a>
				<a href="<?php echo esc_url( $contact['email_url'] ); ?>" class="hover:underline">Email: <?php echo esc_html( $contact['email'] ); ?></a>
				<a href="/privacy" class="hover:underline">Privacy</a>
			</div>
		</div>
	</div>
</footer>

<a
	href="<?php echo esc_url( $contact['whatsapp_url'] ); ?>"
	target="_blank"
	rel="noopener noreferrer"
	aria-label="Chat with us on WhatsApp"
	data-testid="whatsapp-button"
	class="fixed bottom-6 right-6 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg transition-transform hover:scale-105"
>
	<svg viewBox="0 0 24 24" fill="currentColor" class="h-7 w-7" aria-hidden="true">
		<path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.96L2.05 22l5.25-1.38a9.9 9.9 0 0 0 4.74 1.21h.01c5.46 0 9.91-4.45 9.91-9.91C21.96 6.45 17.5 2 12.04 2Zm5.8 14.13c-.24.68-1.4 1.3-1.93 1.38-.5.08-1.12.11-1.81-.12-.42-.13-.95-.31-1.64-.6-2.9-1.25-4.79-4.16-4.94-4.35-.14-.19-1.18-1.57-1.18-3 0-1.42.75-2.12 1.02-2.41.27-.29.58-.36.78-.36.19 0 .39 0 .56.01.18.01.42-.07.65.5.24.58.82 2 .89 2.15.07.15.12.32.02.51-.1.19-.15.31-.29.48-.15.17-.31.38-.44.51-.15.15-.3.31-.13.6.17.29.76 1.26 1.64 2.04 1.13.99 2.08 1.3 2.37 1.45.29.15.46.13.63-.05.17-.17.72-.83.92-1.12.19-.29.39-.24.65-.15.27.1 1.68.79 1.97.93.29.15.48.22.55.34.07.13.07.72-.17 1.4Z" />
	</svg>
</a>

<?php wp_footer(); ?>
</body>
</html>
