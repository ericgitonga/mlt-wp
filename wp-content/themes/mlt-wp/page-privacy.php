<?php
/**
 * Privacy Notice Page Template matching src/app/privacy/page.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$contact = function_exists( 'mlt_get_contact_info' ) ? mlt_get_contact_info() : array(
	'whatsapp_number' => '+254727246599',
	'whatsapp_url'    => 'https://wa.me/254727246599',
	'email'           => 'info@menletstalk.co.ke',
	'email_url'       => 'mailto:info@menletstalk.co.ke',
);

get_header();
?>
<main data-testid="privacy-page" class="mx-auto max-w-2xl px-6 py-16">
	<?php
	mlt_render_breadcrumb(
		array(
			array( 'label' => 'Home', 'href' => '/' ),
			array( 'label' => 'Privacy' ),
		)
	);
	?>
	<h1 class="text-3xl font-bold">Privacy Notice</h1>
	<p class="mt-4 text-neutral-600">
		This notice explains what personal data Men Let&apos;s Talk collects through this
		website, why, and what rights you have over it. It follows Kenya&apos;s Data Protection
		Act, 2019.
	</p>

	<section class="mt-10">
		<h2 class="text-xl font-bold">Who we are</h2>
		<p class="mt-3 text-neutral-700">
			Men Let&apos;s Talk is the data controller for the information described here. Reach us
			on
			<a href="<?php echo esc_url( $contact['whatsapp_url'] ); ?>" class="underline">WhatsApp (<?php echo esc_html( $contact['whatsapp_number'] ); ?>)</a>
			or by email at
			<a href="<?php echo esc_url( $contact['email_url'] ); ?>" class="underline"><?php echo esc_html( $contact['email'] ); ?></a>
			with any question about your data.
		</p>
	</section>

	<section class="mt-10">
		<h2 class="text-xl font-bold">What we collect, and why</h2>
		<ul class="mt-3 list-disc space-y-3 pl-5 text-neutral-700">
			<li>
				<strong>Contact enquiries</strong> — your name, email, phone (optional), topic, and
				message, sent directly to our team&apos;s email inbox so we can reply to you.
			</li>
			<li>
				<strong>Event registration</strong> — your name, email, and phone (optional), so we
				can confirm your place and reach you about the event.
			</li>
			<li>
				<strong>Staying connected</strong> — your email and the update types you choose, so
				we can send you what you&apos;ve asked for and nothing else.
			</li>
			<li>
				<strong>Sharing a story</strong> — your name and age/category (both optional), the
				topics your story covers, and the story itself (text and/or a video link). Every
				story is reviewed by our team before publishing, and only published with your
				explicit consent.
			</li>
		</ul>
	</section>

	<section class="mt-10">
		<h2 class="text-xl font-bold">Who else sees it</h2>
		<p class="mt-3 text-neutral-700">
			We store event registrations, subscriptions, and stories in our website database, and
			use our email provider to deliver contact form enquiries to our team inbox. Because our
			hosting and email infrastructure providers operate outside Kenya, your information is
			processed abroad as part of running this site — we rely on their standard
			data-processing terms as a safeguard for that transfer. We don&apos;t sell your data or
			share it with anyone else.
		</p>
	</section>

	<section class="mt-10">
		<h2 class="text-xl font-bold">How long we keep it</h2>
		<p class="mt-3 text-neutral-700">
			We don&apos;t currently apply a fixed retention period to this information. If we adopt
			one in future, we&apos;ll update this notice. In the meantime, you can ask us to delete
			your information at any time — see your rights below.
		</p>
	</section>

	<section class="mt-10">
		<h2 class="text-xl font-bold">Your rights</h2>
		<p class="mt-3 text-neutral-700">
			You can ask us to access, correct, or delete the information we hold about you at any
			time by
			<a href="<?php echo esc_url( $contact['whatsapp_url'] ); ?>" class="underline">messaging us on WhatsApp</a>.
			We&apos;ll respond within 14 days.
		</p>
	</section>
</main>
<?php
get_footer();
