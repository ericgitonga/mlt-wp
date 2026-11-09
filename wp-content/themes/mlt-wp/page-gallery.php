<?php
/**
 * Gallery Page & Album Folder Template matching src/app/gallery/page.tsx and src/app/gallery/[album]/page.tsx.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$albums            = function_exists( 'mlt_get_gallery_albums' ) ? mlt_get_gallery_albums() : array();
$active_album_slug = isset( $GLOBALS['mlt_active_gallery_album'] )
	? (string) $GLOBALS['mlt_active_gallery_album']
	: ( isset( $_GET['album'] ) ? sanitize_key( wp_unslash( $_GET['album'] ) ) : '' );
$active_album      = ( '' !== $active_album_slug && isset( $albums[ $active_album_slug ] ) )
	? $albums[ $active_album_slug ]
	: null;

get_header();
?>
<?php if ( null !== $active_album ) : ?>
	<?php
	$album_images = function_exists( 'mlt_get_gallery_images' )
		? mlt_get_gallery_images( $active_album['slug'] )
		: array();
	?>
	<main data-testid="gallery-album-page" class="mx-auto max-w-5xl px-6 py-16">
		<?php
		mlt_render_breadcrumb(
			array(
				array( 'label' => 'Home', 'href' => '/' ),
				array( 'label' => 'Gallery', 'href' => '/gallery' ),
				array( 'label' => $active_album['title'] ),
			)
		);
		?>
		<h1 class="text-3xl font-bold"><?php echo esc_html( $active_album['title'] ); ?></h1>
		<p class="mt-4 text-neutral-600"><?php echo esc_html( $active_album['description'] ); ?></p>

		<?php if ( empty( $album_images ) ) : ?>
			<p data-testid="gallery-empty-state" class="mt-8 text-neutral-600">
				No photos are shared here yet — check back soon.
			</p>
		<?php else : ?>
			<div
				data-testid="gallery-grid"
				class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
			>
				<?php foreach ( $album_images as $photo ) : ?>
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
								alt="<?php echo esc_attr( ! empty( $photo['caption'] ) ? $photo['caption'] : 'Photo from ' . $active_album['title'] . " — Men Let's Talk" ); ?>"
								class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
							/>
						</a>
					</figure>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</main>
<?php else : ?>
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

		<div
			data-testid="gallery-albums"
			class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2"
		>
			<?php foreach ( $albums as $album ) : ?>
				<?php
				$photos    = function_exists( 'mlt_get_gallery_images' ) ? mlt_get_gallery_images( $album['slug'] ) : array();
				$count     = count( $photos );
				$cover_url = ! empty( $photos[0]['image_url'] ) ? $photos[0]['image_url'] : '';
				?>
				<a
					href="<?php echo esc_url( '/gallery/' . $album['slug'] ); ?>"
					data-testid="gallery-album-card"
					class="group block overflow-hidden rounded-lg border border-neutral-200 bg-white hover:border-neutral-400"
				>
					<div class="relative h-56 overflow-hidden bg-neutral-100 sm:h-72">
						<?php if ( '' !== $cover_url ) : ?>
							<img
								src="<?php echo esc_url( $cover_url ); ?>"
								alt="<?php echo esc_attr( $album['title'] . ' gallery cover' ); ?>"
								class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
							/>
						<?php endif; ?>
					</div>
					<div class="p-6">
						<div class="flex items-center justify-between gap-4">
							<h2 class="text-xl font-bold group-hover:underline">
								<?php echo esc_html( $album['title'] ); ?>
							</h2>
							<span
								data-testid="gallery-album-count"
								class="text-sm font-medium text-neutral-500"
							>
								<?php echo esc_html( $count . ' ' . ( 1 === $count ? 'photo' : 'photos' ) ); ?>
							</span>
						</div>
						<p class="mt-2 text-sm text-neutral-600">
							<?php echo esc_html( $album['description'] ); ?>
						</p>
						<p class="mt-4 text-sm font-semibold text-neutral-900">
							Open gallery →
						</p>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</main>
<?php endif; ?>
<?php
get_footer();
