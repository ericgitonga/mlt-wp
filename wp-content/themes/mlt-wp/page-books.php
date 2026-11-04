<?php
/**
 * Books & Projects Page Template matching src/app/books/page.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$books = function_exists( 'mlt_get_books' ) ? mlt_get_books() : array();

get_header();
?>
<main data-testid="books-page" class="mx-auto max-w-3xl px-6 py-16">
	<?php
	mlt_render_breadcrumb(
		array(
			array( 'label' => 'Home', 'href' => '/' ),
			array( 'label' => 'Books & Projects' ),
		)
	);
	?>
	<h1 class="text-3xl font-bold">Books &amp; Projects</h1>

	<?php if ( empty( $books ) ) : ?>
		<p data-testid="books-empty-state" class="mt-8 text-neutral-600">
			No books or projects listed here yet — check back soon.
		</p>
	<?php else : ?>
		<ul data-testid="books-list" class="mt-8 space-y-12">
			<?php foreach ( $books as $book ) : ?>
				<li data-testid="book-item" class="border-b border-neutral-200 pb-12">
					<div class="flex flex-col gap-6 sm:flex-row">
						<?php if ( ! empty( $book['cover_url'] ) ) : ?>
							<img
								src="<?php echo esc_url( $book['cover_url'] ); ?>"
								alt="<?php echo esc_attr( $book['title'] ); ?>"
								width="240"
								height="360"
								class="rounded-lg object-cover"
							/>
						<?php endif; ?>
						<div>
							<h2 class="text-xl font-semibold"><?php echo esc_html( $book['title'] ); ?></h2>
							<p class="mt-1 text-neutral-600">by <?php echo esc_html( $book['author'] ); ?></p>
							<?php if ( ! empty( $book['description'] ) ) : ?>
								<p class="mt-3"><?php echo esc_html( $book['description'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $book['whyWritten'] ) ) : ?>
								<p class="mt-3 text-neutral-700">
									<span class="font-medium">Why this book: </span><?php echo esc_html( $book['whyWritten'] ); ?>
								</p>
							<?php endif; ?>
							<?php if ( ! empty( $book['purchaseUrl'] ) ) : ?>
								<a
									href="<?php echo esc_url( $book['purchaseUrl'] ); ?>"
									class="mt-4 inline-block rounded-md bg-neutral-900 px-5 py-2 text-white"
								>Order now</a>
							<?php endif; ?>
						</div>
					</div>
					<?php if ( ! empty( $book['testimonials'] ) ) : ?>
						<ul class="mt-6 space-y-3 border-l-2 border-neutral-200 pl-4">
							<?php foreach ( $book['testimonials'] as $t ) : ?>
								<li>
									<p class="italic">&ldquo;<?php echo esc_html( $t['quote'] ?? '' ); ?>&rdquo;</p>
									<?php if ( ! empty( $t['attribution'] ) ) : ?>
										<p class="mt-1 text-sm text-neutral-500">— <?php echo esc_html( $t['attribution'] ); ?></p>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</main>
<?php
get_footer();
