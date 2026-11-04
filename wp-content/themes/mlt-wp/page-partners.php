<?php
/**
 * Partners & Sponsors Page Template matching src/app/partners/page.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$partners                = function_exists( 'mlt_get_partners' ) ? mlt_get_partners() : array();
$partnership_type_labels = function_exists( 'mlt_get_partnership_type_labels' ) ? mlt_get_partnership_type_labels() : array();

get_header();
?>
<main data-testid="partners-page" class="mx-auto max-w-3xl px-6 py-16">
	<?php
	mlt_render_breadcrumb(
		array(
			array( 'label' => 'Home', 'href' => '/' ),
			array( 'label' => 'Partners & Sponsors' ),
		)
	);
	?>
	<h1 class="text-3xl font-bold">Partners &amp; Sponsors</h1>

	<section data-testid="why-partner-section" class="mt-8">
		<h2 class="text-xl font-bold">Why partner with us?</h2>
		<p class="mt-3 text-neutral-700">
			Create a professional partnership with Men Let&apos;s Talk to help reach more men
			with honest conversations, community and practical support.
		</p>
		<ul class="mt-4 grid grid-cols-1 gap-2 text-sm text-neutral-600 sm:grid-cols-2">
			<li>Corporate men&apos;s wellness</li>
			<li>Event sponsorship</li>
			<li>Community initiatives</li>
			<li>Mental-health initiatives</li>
			<li>Media partnerships</li>
			<li>Church partnerships</li>
		</ul>
		<a
			href="/contact"
			class="mt-6 inline-block rounded-md bg-neutral-900 px-6 py-3 font-semibold text-white"
		>Become a Partner</a>
	</section>

	<h2 data-testid="current-partners-heading" class="mt-16 text-xl font-bold">Our Current Partners</h2>

	<?php if ( empty( $partners ) ) : ?>
		<p data-testid="partners-empty-state" class="mt-8 text-neutral-600">
			No partners listed here yet — check back soon.
		</p>
	<?php else : ?>
		<ul data-testid="partners-list" class="mt-8 space-y-8">
			<?php foreach ( $partners as $partner ) : ?>
				<li data-testid="partner-item" class="border-b border-neutral-200 pb-8">
					<?php if ( ! empty( $partner['logo_url'] ) ) : ?>
						<img
							src="<?php echo esc_url( $partner['logo_url'] ); ?>"
							alt="<?php echo esc_attr( $partner['name'] ); ?>"
							width="200"
							height="120"
							class="mb-4 object-contain"
						/>
					<?php endif; ?>
					<p class="text-sm font-medium uppercase tracking-wide text-neutral-500">
						<?php echo esc_html( $partnership_type_labels[ $partner['partnershipType'] ] ?? $partner['partnershipType'] ); ?>
					</p>
					<h2 class="mt-1 text-xl font-semibold"><?php echo esc_html( $partner['name'] ); ?></h2>
					<p class="mt-2"><?php echo esc_html( $partner['description'] ); ?></p>
					<?php if ( ! empty( $partner['websiteUrl'] ) ) : ?>
						<a href="<?php echo esc_url( $partner['websiteUrl'] ); ?>" class="mt-3 inline-block underline">Visit website</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</main>
<?php
get_footer();
