<?php
/**
 * Let's Talk Page Template matching src/app/talk/page.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories = array(
	array(
		'label'     => 'Mental Health',
		'topic'     => 'mental-health',
		'subtopics' => array( 'Stress', 'Anxiety', 'Burnout', 'Asking for help', 'Emotional health' ),
	),
	array(
		'label'     => 'Relationships',
		'topic'     => 'relationships',
		'subtopics' => array( 'Marriage', 'Dating', 'Friendship', 'Communication', 'Conflict' ),
	),
	array(
		'label'     => 'Fatherhood',
		'topic'     => 'fatherhood',
		'subtopics' => array( 'Being present', 'Raising sons', 'Raising daughters', 'Parenting', 'Legacy' ),
	),
	array(
		'label'     => 'Purpose',
		'topic'     => 'purpose',
		'subtopics' => array( 'Identity', 'Career', 'Failure', 'Starting again', 'Leadership' ),
	),
	array(
		'label'     => 'Money',
		'topic'     => 'money',
		'subtopics' => array( 'Financial pressure', 'Work', 'Debt', 'Investments', 'Providing' ),
	),
	array(
		'label'     => 'Masculinity',
		'topic'     => 'masculinity',
		'subtopics' => array(
			'What does it mean to be a man?',
			'Vulnerability',
			'Strength',
			'Responsibility',
			'Emotional expression',
		),
	),
	array(
		'label'     => 'Faith',
		'topic'     => 'faith',
		'subtopics' => array( 'God and manhood', 'Faith during difficult seasons', 'Spiritual leadership', 'Purpose' ),
	),
);

get_header();
?>
<main data-testid="talk-page" class="mx-auto max-w-4xl px-6 py-16">
	<div class="relative -mx-6 mb-10 h-56 overflow-hidden rounded-lg sm:h-72">
		<img
			src="<?php echo esc_url( mlt_theme_image_url( 'talk-listening.jpg' ) ); ?>"
			alt="A circle of men in quiet conversation, listening closely to one another"
			class="h-full w-full object-cover"
		/>
	</div>
	<?php
	mlt_render_breadcrumb(
		array(
			array( 'label' => 'Home', 'href' => '/' ),
			array( 'label' => "Let's Talk" ),
		)
	);
	?>
	<h1 class="text-3xl font-bold">Let&apos;s Talk</h1>
	<p class="mt-4 text-neutral-600">
		This is the content hub — real conversations, organised by what you&apos;re actually
		carrying.
	</p>

	<p data-testid="signature-statement" class="mt-10 text-center text-xl font-bold text-neutral-800">You don&apos;t have to carry it alone.</p>

	<div data-testid="talk-categories" class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2">
		<?php foreach ( $categories as $category ) : ?>
			<div class="rounded-lg border border-neutral-200 p-6">
				<h2 class="text-lg font-bold uppercase tracking-wide"><?php echo esc_html( $category['label'] ); ?></h2>
				<ul class="mt-3 space-y-1 text-sm text-neutral-600">
					<?php foreach ( $category['subtopics'] as $subtopic ) : ?>
						<li><?php echo esc_html( $subtopic ); ?></li>
					<?php endforeach; ?>
				</ul>
				<a
					href="<?php echo esc_url( '/resources?topic=' . rawurlencode( $category['topic'] ) ); ?>"
					class="mt-4 inline-block text-sm font-semibold underline"
				>See <?php echo esc_html( $category['label'] ); ?> resources →</a>
			</div>
		<?php endforeach; ?>
	</div>
</main>
<?php
get_footer();
