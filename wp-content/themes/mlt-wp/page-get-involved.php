<?php
/**
 * Get Involved Page Template matching src/app/get-involved/page.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = array(
	array(
		'name'        => 'Attend',
		'description' => 'Come to a conversation or event.',
		'cta'         => 'Attend an Event',
		'href'        => '/events',
	),
	array(
		'name'        => 'Volunteer',
		'description' => 'Use your skills and time to serve the movement.',
		'cta'         => 'Get in Touch',
		'href'        => '/contact',
	),
	array(
		'name'        => 'Mentor',
		'description' => 'Walk alongside another man.',
		'cta'         => 'Get in Touch',
		'href'        => '/contact',
	),
	array(
		'name'        => 'Partner',
		'description' => "Partner with Men Let's Talk through your organisation.",
		'cta'         => 'Become a Partner',
		'href'        => '/partners',
	),
	array(
		'name'        => 'Sponsor',
		'description' => 'Help make conversations and programmes accessible to more men.',
		'cta'         => 'Become a Sponsor',
		'href'        => '/partners',
	),
	array(
		'name'        => 'Support',
		'description' => 'Financially support the work of the movement.',
		'cta'         => 'Get in Touch',
		'href'        => '/contact',
	),
);

get_header();
?>
<main data-testid="get-involved-page" class="mx-auto max-w-4xl px-6 py-16">
	<div class="relative -mx-6 mb-10 h-56 overflow-hidden rounded-lg sm:h-72">
		<img
			src="<?php echo esc_url( mlt_theme_image_url( 'get-involved-gathering.jpg' ) ); ?>"
			alt="A small group of Men Let&apos;s Talk members gathered around a table at an evening event"
			class="h-full w-full object-cover"
		/>
	</div>
	<?php
	mlt_render_breadcrumb(
		array(
			array( 'label' => 'Home', 'href' => '/' ),
			array( 'label' => 'Get Involved' ),
		)
	);
	?>
	<h1 class="text-3xl font-bold">There&apos;s a Place for You Here.</h1>

	<p data-testid="signature-statement" class="mt-10 text-center text-xl font-bold text-neutral-800">There is strength in speaking.</p>

	<div data-testid="get-involved-options" class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
		<?php foreach ( $options as $option ) : ?>
			<div class="rounded-lg border border-neutral-200 p-6">
				<h2 class="text-lg font-bold uppercase tracking-wide"><?php echo esc_html( $option['name'] ); ?></h2>
				<p class="mt-2 text-sm text-neutral-600"><?php echo esc_html( $option['description'] ); ?></p>
				<a href="<?php echo esc_url( $option['href'] ); ?>" class="mt-4 inline-block text-sm font-semibold underline"><?php echo esc_html( $option['cta'] ); ?> →</a>
			</div>
		<?php endforeach; ?>
	</div>
</main>
<?php
get_footer();
