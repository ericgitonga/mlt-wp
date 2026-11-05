<?php
/**
 * Gallery Page Template matching src/app/gallery/page.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$images = function_exists( 'mlt_get_gallery_images' ) ? mlt_get_gallery_images() : array();

get_header();
?>
<main data-testid="gallery-page" class="mx-auto max-w-5xl px-6 py-16">
	<?php
	mlt_render_breadcrumb(
		array(
			array( 'label' => 'Home', 'href' => '/' ),
			array( 'label' => 'Gallery' ),
		)
	);
	?>
	<h1 class="text-3xl font-bold">Gallery</h1>
	<p class="mt-4 text-neutral-600">
		Moments from Men Let&apos;s Talk events, gatherings and community.
	</p>

	<?php if ( empty( $images ) ) : ?>
		<p data-testid="gallery-empty-state" class="mt-8 text-neutral-600">
			No photos are shared here yet — check back soon.
		</p>
	<?php else : ?>
		<div
			data-testid="gallery-grid"
			class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
		>
			<?php foreach ( $images as $photo ) : ?>
				<figure
					data-testid="gallery-item"
					class="group relative aspect-square overflow-hidden rounded-lg bg-neutral-100"
				>
					<a
						href="<?php echo esc_url( $photo['image_url'] ); ?>"
						target="_blank"
						rel="noopener noreferrer"
						class="block h-full w-full"
					>
						<img
							src="<?php echo esc_url( $photo['image_url'] ); ?>"
							alt="<?php echo esc_attr( ! empty( $photo['caption'] ) ? $photo['caption'] : "Photo from a Men Let's Talk event" ); ?>"
							class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
						/>
					</a>
				</figure>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();
