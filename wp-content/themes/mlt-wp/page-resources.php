<?php
/**
 * Resources Page Template matching src/app/resources/page.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$topic                = isset( $_GET['topic'] ) ? sanitize_text_field( wp_unslash( $_GET['topic'] ) ) : '';
$articles             = function_exists( 'mlt_get_resources' ) ? mlt_get_resources( $topic ) : array();
$topic_labels         = function_exists( 'mlt_get_topic_labels' ) ? mlt_get_topic_labels() : array();
$resource_type_labels = function_exists( 'mlt_get_resource_type_labels' ) ? mlt_get_resource_type_labels() : array();

get_header();
?>
<main data-testid="resources-page" class="mx-auto max-w-3xl px-6 py-16">
	<?php
	mlt_render_breadcrumb(
		array(
			array( 'label' => 'Home', 'href' => '/' ),
			array( 'label' => 'Resources' ),
		)
	);
	?>
	<h1 class="text-3xl font-bold">You don&apos;t have to figure it all out alone.</h1>

	<nav data-testid="topic-filter" class="mt-6 flex flex-wrap gap-2">
		<a
			href="/resources"
			class="rounded-full border px-3 py-1 text-sm <?php echo '' === $topic ? 'bg-neutral-900 text-white' : 'border-neutral-300'; ?>"
		>All</a>
		<?php foreach ( $topic_labels as $value => $label ) : ?>
			<a
				href="<?php echo esc_url( '/resources?topic=' . rawurlencode( $value ) ); ?>"
				class="rounded-full border px-3 py-1 text-sm <?php echo $topic === $value ? 'bg-neutral-900 text-white' : 'border-neutral-300'; ?>"
			><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
	</nav>

	<?php if ( empty( $articles ) ) : ?>
		<p data-testid="resources-empty-state" class="mt-8 text-neutral-600">
			No resources here yet — check back soon.
		</p>
	<?php else : ?>
		<ul data-testid="resources-list" class="mt-8 space-y-8">
			<?php foreach ( $articles as $article ) : ?>
				<li id="<?php echo esc_attr( $article['id'] ); ?>" data-testid="resource-item" class="scroll-mt-24 border-b border-neutral-200 pb-8">
					<?php if ( ! empty( $article['featuredImage'] ) ) : ?>
						<img
							src="<?php echo esc_url( $article['featuredImage'] ); ?>"
							alt="<?php echo esc_attr( $article['title'] ); ?>"
							width="800"
							height="450"
							class="mb-4 rounded-lg object-cover"
						/>
					<?php endif; ?>
					<p class="text-sm font-medium uppercase tracking-wide text-neutral-500">
						<?php echo esc_html( $resource_type_labels[ $article['resourceType'] ] ?? $article['resourceType'] ); ?>
					</p>
					<h2 class="mt-1 text-xl font-semibold"><?php echo esc_html( $article['title'] ); ?></h2>
					<p class="mt-2 flex flex-wrap gap-2">
						<?php foreach ( $article['topics'] as $t ) : ?>
							<span class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs text-neutral-600">
								<?php echo esc_html( $topic_labels[ $t ] ?? $t ); ?>
							</span>
						<?php endforeach; ?>
					</p>
					<?php if ( ! empty( $article['videoUrl'] ) && function_exists( 'mlt_render_video_embed' ) ) : ?>
						<?php echo mlt_render_video_embed( $article['videoUrl'], $article['title'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
					<?php if ( ! empty( $article['downloadUrl'] ) ) : ?>
						<a href="<?php echo esc_url( $article['downloadUrl'] ); ?>" download class="mt-3 inline-block underline">Download</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</main>
<?php
get_footer();
