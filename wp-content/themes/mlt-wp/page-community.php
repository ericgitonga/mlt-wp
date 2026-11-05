<?php
/**
 * Community Page Template matching src/app/community/page.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$groups          = function_exists( 'mlt_get_community_groups' ) ? mlt_get_community_groups() : array();
$audience_labels = function_exists( 'mlt_get_audience_labels' ) ? mlt_get_audience_labels() : array();

get_header();
?>
<main data-testid="community-page" class="mx-auto max-w-3xl px-6 py-16">
	<?php
	mlt_render_breadcrumb(
		array(
			array( 'label' => 'Home', 'href' => '/' ),
			array( 'label' => 'Community' ),
		)
	);
	?>
	<h1 class="text-3xl font-bold">Find Your Space.</h1>
	<p class="mt-4 text-neutral-600">
		Not every man needs the same kind of community — a space where men support one another
		through life&apos;s different seasons.
	</p>

	<section data-testid="community-video-section" class="mt-10">
		<h2 class="text-2xl font-bold">The Conversation Is Growing</h2>
		<p class="mt-2 text-neutral-600">
			Every Tuesday, men gather across Nairobi, Nakuru, Kampala and beyond to share honestly,
			connect and walk alongside one another.
		</p>
		<div class="mx-auto mt-4 max-w-md overflow-hidden rounded-lg bg-neutral-900">
			<video
				data-testid="community-video"
				controls
				preload="metadata"
				class="h-auto w-full"
			>
				<source src="<?php echo esc_url( get_template_directory_uri() . '/assets/media/odek-the-conversation-is-growing.mp4' ); ?>" type="video/mp4" />
				Your browser does not support the video tag.
			</video>
		</div>
	</section>

	<?php if ( empty( $groups ) ) : ?>
		<p data-testid="community-empty-state" class="mt-8 text-neutral-600">
			No community groups listed here yet — check back soon.
		</p>
	<?php else : ?>
		<ul data-testid="community-list" class="mt-8 space-y-8">
			<?php foreach ( $groups as $group ) : ?>
				<li data-testid="community-item" class="border-b border-neutral-200 pb-8">
					<?php if ( ! empty( $group['image_url'] ) ) : ?>
						<img
							src="<?php echo esc_url( $group['image_url'] ); ?>"
							alt="<?php echo esc_attr( $group['name'] ); ?>"
							width="800"
							height="450"
							class="mb-4 rounded-lg object-cover"
						/>
					<?php endif; ?>
					<p class="text-sm font-medium uppercase tracking-wide text-neutral-500">
						<?php echo esc_html( $audience_labels[ $group['audience'] ] ?? $group['audience'] ); ?>
					</p>
					<h2 class="mt-1 text-xl font-semibold"><?php echo esc_html( $group['name'] ); ?></h2>
					<p class="mt-2"><?php echo esc_html( $group['description'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</main>
<?php
get_footer();
