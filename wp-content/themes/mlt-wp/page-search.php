<?php
/**
 * Search Page Template matching src/app/search/page.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term                 = isset( $_GET['q'] ) ? trim( sanitize_text_field( wp_unslash( $_GET['q'] ) ) ) : '';
$results              = ( '' !== $term && function_exists( 'mlt_search_content' ) ) ? mlt_search_content( $term ) : array(
	'articles' => array(),
	'events'   => array(),
	'stories'  => array(),
);
$total_count          = count( $results['articles'] ) + count( $results['events'] ) + count( $results['stories'] );
$resource_type_labels = function_exists( 'mlt_get_resource_type_labels' ) ? mlt_get_resource_type_labels() : array();

get_header();
?>
<main data-testid="search-page" class="mx-auto max-w-3xl px-6 py-16">
	<?php
	mlt_render_breadcrumb(
		array(
			array( 'label' => 'Home', 'href' => '/' ),
			array( 'label' => 'Search' ),
		)
	);
	?>
	<h1 class="text-3xl font-bold">Search</h1>

	<form action="/search" method="get" class="mt-6 flex gap-2">
		<input
			type="search"
			name="q"
			value="<?php echo esc_attr( $term ); ?>"
			placeholder="Search articles, resources, events, stories…"
			class="w-full rounded-md border border-neutral-300 px-3 py-2"
		/>
		<button type="submit" class="rounded-md bg-neutral-900 px-5 py-2 font-semibold text-white">Search</button>
	</form>

	<?php if ( '' === $term ) : ?>
		<p data-testid="search-prompt" class="mt-8 text-neutral-600">
			Type something above to search across events, resources, and stories.
		</p>
	<?php elseif ( 0 === $total_count ) : ?>
		<p data-testid="search-empty-state" class="mt-8 text-neutral-600">
			No results for &ldquo;<?php echo esc_html( $term ); ?>&rdquo; — try a different word.
		</p>
	<?php else : ?>
		<div data-testid="search-results" class="mt-8 space-y-10">
			<?php if ( ! empty( $results['events'] ) ) : ?>
				<section>
					<h2 class="text-lg font-bold uppercase tracking-wide text-neutral-500">Events</h2>
					<ul class="mt-3 space-y-2">
						<?php foreach ( $results['events'] as $event ) : ?>
							<li>
								<a href="<?php echo esc_url( '/events#' . $event['id'] ); ?>" class="underline"><?php echo esc_html( $event['name'] ); ?></a>
								<span class="text-sm text-neutral-500">— <?php echo esc_html( $event['location'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $results['articles'] ) ) : ?>
				<section>
					<h2 class="text-lg font-bold uppercase tracking-wide text-neutral-500">Resources</h2>
					<ul class="mt-3 space-y-2">
						<?php foreach ( $results['articles'] as $article ) : ?>
							<li>
								<a href="<?php echo esc_url( '/resources#' . $article['id'] ); ?>" class="underline"><?php echo esc_html( $article['title'] ); ?></a>
								<span class="text-sm text-neutral-500">— <?php echo esc_html( $resource_type_labels[ $article['resourceType'] ] ?? $article['resourceType'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<?php if ( ! empty( $results['stories'] ) ) : ?>
				<section>
					<h2 class="text-lg font-bold uppercase tracking-wide text-neutral-500">Stories</h2>
					<ul class="mt-3 space-y-2">
						<?php foreach ( $results['stories'] as $story ) : ?>
							<li>
								<a href="<?php echo esc_url( '/stories#' . $story['id'] ); ?>" class="underline"><?php echo esc_html( $story['name'] ?: 'Anonymous' ); ?></a>
								<?php if ( ! empty( $story['excerpt'] ) ) : ?>
									<span class="text-sm text-neutral-500"> — <?php echo esc_html( mlt_truncate_text( $story['excerpt'], 160 ) ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();
