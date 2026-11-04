<?php
/**
 * About Page Template matching src/app/about/page.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = function_exists( 'mlt_get_site_settings' ) ? mlt_get_site_settings() : array();
$values   = $settings['values'] ?? array();

get_header();
?>
<main data-testid="about-page" class="mx-auto max-w-3xl px-6 py-16">
	<div class="relative -mx-6 mb-10 h-56 overflow-hidden rounded-lg sm:h-72">
		<img
			src="<?php echo esc_url( mlt_theme_image_url( 'about-brotherhood.jpg' ) ); ?>"
			alt="Five Men Let&apos;s Talk members standing together, arms around each other&apos;s shoulders, laughing"
			class="h-full w-full object-cover object-top"
		/>
	</div>
	<?php
	mlt_render_breadcrumb(
		array(
			array( 'label' => 'Home', 'href' => '/' ),
			array( 'label' => 'About' ),
		)
	);
	?>
	<h1 class="text-3xl font-bold">About Men Let&apos;s Talk</h1>
	<p class="mt-4 text-lg text-neutral-700">
		Men Let&apos;s Talk is a digital home where men can find honest conversations,
		meaningful community, practical resources and opportunities to grow.
	</p>

	<section data-testid="our-story-section" class="mt-12">
		<h2 class="text-2xl font-bold">Our Story</h2>
		<p class="mt-3 text-neutral-700">
			Men Let&apos;s Talk began as a response to a simple but urgent problem: many men have
			been taught to be strong, provide, protect and remain silent — but very few spaces
			teach men how to process what they carry. What started as a handful of men choosing to
			talk honestly, over tea and around ordinary tables, has grown into a movement — regular
			conversations, breakfasts and retreats where men show up for each other, season after
			season.
		</p>
	</section>

	<section data-testid="our-why-section" class="mt-10">
		<h2 class="text-2xl font-bold">Our Why</h2>
		<p class="mt-3 text-neutral-700">
			Many men have been taught to be strong, provide, protect and remain silent — but very
			few spaces teach men how to process what they carry.
		</p>
	</section>

	<section data-testid="our-vision-section" class="mt-10">
		<h2 class="text-2xl font-bold">Our Vision</h2>
		<p class="mt-3 text-neutral-700">
			A generation of men who are emotionally healthy, connected, responsible and
			intentional about the lives they influence.
		</p>
	</section>

	<p data-testid="signature-statement" class="mt-10 text-center text-xl font-bold text-neutral-800">Your next chapter can be different.</p>

	<section data-testid="our-mission-section" class="mt-10">
		<h2 class="text-2xl font-bold">Our Mission</h2>
		<p class="mt-3 text-neutral-700">
			To create safe spaces where men can talk honestly, build meaningful community and grow
			into healthier men who positively impact their families and communities.
		</p>
	</section>

	<section data-testid="our-values-section" class="mt-10">
		<h2 class="text-2xl font-bold">Our Values</h2>
		<div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
			<?php foreach ( $values as $val ) : ?>
				<div>
					<h3 class="font-bold uppercase tracking-wide"><?php echo esc_html( $val['name'] ); ?></h3>
					<p class="mt-1 text-sm text-neutral-600"><?php echo esc_html( $val['description'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
</main>
<?php
get_footer();
