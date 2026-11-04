<?php
/**
 * Stories Page Template matching src/app/stories/page.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stories      = function_exists( 'mlt_get_stories' ) ? mlt_get_stories() : array();
$topic_labels = function_exists( 'mlt_get_topic_labels' ) ? mlt_get_topic_labels() : array();

get_header();
?>
<main data-testid="stories-page" class="mx-auto max-w-3xl px-6 py-16">
	<div class="relative -mx-6 mb-10 h-56 overflow-hidden rounded-lg sm:h-72">
		<img
			src="<?php echo esc_url( mlt_theme_image_url( 'stories-laughing.jpg' ) ); ?>"
			alt="A group of Men Let&apos;s Talk members laughing together at a table"
			class="h-full w-full object-cover"
		/>
	</div>
	<?php
	mlt_render_breadcrumb(
		array(
			array( 'label' => 'Home', 'href' => '/' ),
			array( 'label' => 'Stories' ),
		)
	);
	?>
	<h1 class="text-3xl font-bold">Real Men. Real Stories. Real Conversations.</h1>

	<p data-testid="signature-statement" class="mt-6 text-center text-xl font-bold text-neutral-800">Your story matters.</p>

	<div class="mt-6 text-center">
		<a
			href="/share-your-story"
			class="inline-block rounded-md bg-neutral-900 px-6 py-3 font-semibold text-white"
		>Share Your Story →</a>
	</div>

	<?php if ( empty( $stories ) ) : ?>
		<p data-testid="stories-empty-state" class="mt-8 text-neutral-600">
			No stories are shared here yet — check back soon.
		</p>
	<?php else : ?>
		<ul data-testid="stories-list" class="mt-8 space-y-10">
			<?php foreach ( $stories as $story ) : ?>
				<li id="<?php echo esc_attr( $story['id'] ); ?>" data-testid="story-item" class="scroll-mt-24 border-b border-neutral-200 pb-10">
					<p class="mt-1 flex flex-wrap gap-2">
						<?php foreach ( $story['topics'] as $t ) : ?>
							<span class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs text-neutral-600">
								<?php echo esc_html( $topic_labels[ $t ] ?? $t ); ?>
							</span>
						<?php endforeach; ?>
					</p>
					<p class="mt-3 text-sm font-medium text-neutral-500">
						<?php echo esc_html( $story['name'] ?: 'Anonymous' ); ?><?php echo ! empty( $story['ageOrCategory'] ) ? ', ' . esc_html( $story['ageOrCategory'] ) : ''; ?>
					</p>
					<?php if ( ! empty( $story['body'] ) ) : ?>
						<div class="prose prose-neutral mt-3 max-w-none">
							<?php echo wp_kses_post( wpautop( $story['body'] ) ); ?>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $story['videoUrl'] ) && function_exists( 'mlt_render_video_embed' ) ) : ?>
						<?php echo mlt_render_video_embed( $story['videoUrl'], ( $story['name'] ?: 'Anonymous' ) . "'s story" ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</main>
<?php
get_footer();
