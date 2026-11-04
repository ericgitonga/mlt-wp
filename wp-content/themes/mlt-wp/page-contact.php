<?php
/**
 * Contact Page Template matching src/app/contact/page.tsx & ContactForm.tsx 1:1.
 *
 * Friction-free form (no consent checkbox required to send; Privacy Notice link below).
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
$uid = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'mlt-cf-' ) : 'mlt-cf-1';

get_header();
?>
<main data-testid="contact-page" class="mx-auto max-w-2xl px-6 py-16">
	<?php
	mlt_render_breadcrumb(
		array(
			array( 'label' => 'Home', 'href' => '/' ),
			array( 'label' => 'Contact' ),
		)
	);
	?>
	<h1 class="text-3xl font-bold">Let&apos;s Talk.</h1>

	<div class="mt-6 space-y-2 text-neutral-700">
		<p>
			WhatsApp:
			<a href="<?php echo esc_url( $contact['whatsapp_url'] ); ?>" class="underline"><?php echo esc_html( $contact['whatsapp_number'] ); ?></a>
		</p>
		<p>
			Instagram:
			<a href="<?php echo esc_url( $contact['instagram_url'] ); ?>" class="underline">@<?php echo esc_html( $contact['instagram_handle'] ); ?></a>
		</p>
		<p>
			Email:
			<a href="<?php echo esc_url( $contact['email_url'] ); ?>" class="underline"><?php echo esc_html( $contact['email'] ); ?></a>
		</p>
	</div>

	<form data-testid="contact-form" class="mt-8 space-y-4">
		<div
			aria-hidden="true"
			class="pointer-events-none absolute -left-[9999px] top-auto h-px w-px overflow-hidden opacity-0"
		>
			<label for="<?php echo esc_attr( $uid . '-website' ); ?>">Website</label>
			<input
				id="<?php echo esc_attr( $uid . '-website' ); ?>"
				name="website"
				type="text"
				value=""
				tabindex="-1"
				autocomplete="nope"
			/>
		</div>

		<div>
			<label for="<?php echo esc_attr( $uid . '-name' ); ?>" class="block text-sm font-medium">Name</label>
			<input
				id="<?php echo esc_attr( $uid . '-name' ); ?>"
				name="name"
				type="text"
				required
				maxlength="100"
				class="mt-1 w-full rounded-md border border-neutral-300 px-3 py-2"
			/>
		</div>
		<div>
			<label for="<?php echo esc_attr( $uid . '-email' ); ?>" class="block text-sm font-medium">Email</label>
			<input
				id="<?php echo esc_attr( $uid . '-email' ); ?>"
				name="email"
				type="email"
				required
				class="mt-1 w-full rounded-md border border-neutral-300 px-3 py-2"
			/>
		</div>
		<div>
			<label for="<?php echo esc_attr( $uid . '-phone' ); ?>" class="block text-sm font-medium">Phone</label>
			<input
				id="<?php echo esc_attr( $uid . '-phone' ); ?>"
				name="phone"
				type="tel"
				maxlength="40"
				class="mt-1 w-full rounded-md border border-neutral-300 px-3 py-2"
			/>
		</div>
		<div>
			<label for="<?php echo esc_attr( $uid . '-topic' ); ?>" class="block text-sm font-medium">What would you like to talk to us about?</label>
			<input
				id="<?php echo esc_attr( $uid . '-topic' ); ?>"
				name="topic"
				type="text"
				maxlength="160"
				class="mt-1 w-full rounded-md border border-neutral-300 px-3 py-2"
			/>
		</div>
		<div>
			<label for="<?php echo esc_attr( $uid . '-message' ); ?>" class="block text-sm font-medium">Message</label>
			<textarea
				id="<?php echo esc_attr( $uid . '-message' ); ?>"
				name="message"
				rows="5"
				required
				maxlength="5000"
				class="mt-1 w-full rounded-md border border-neutral-300 px-3 py-2"
			></textarea>
		</div>

		<p data-testid="contact-error" class="text-sm text-red-600" style="display:none;"></p>

		<button
			type="submit"
			class="rounded-md bg-neutral-900 px-6 py-3 font-semibold text-white disabled:opacity-60"
		>Send</button>
	</form>

	<p data-testid="contact-form-submitted" class="mt-8 text-neutral-700" style="display:none;">
		Thanks for reaching out — we&apos;ve received your message and will get back to you soon.
	</p>

	<p class="mt-6 text-sm text-neutral-500">
		See our <a href="/privacy" class="underline">Privacy Notice</a> for how we
		handle the information you share with us.
	</p>
</main>
<?php
get_footer();
