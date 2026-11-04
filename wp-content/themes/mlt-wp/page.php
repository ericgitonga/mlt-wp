<?php
/**
 * Generic Page Template.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?>
<main class="mx-auto max-w-3xl px-6 py-16">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<h1 class="text-3xl font-bold"><?php the_title(); ?></h1>
		<div class="prose prose-neutral mt-6 max-w-none">
			<?php the_content(); ?>
		</div>
	<?php endwhile; ?>
</main>
<?php
get_footer();
