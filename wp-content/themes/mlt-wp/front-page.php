<?php
/**
 * Homepage Template matching src/app/page.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings           = function_exists( 'mlt_get_site_settings' ) ? mlt_get_site_settings() : array();
$upcoming_events    = function_exists( 'mlt_get_upcoming_events' ) ? mlt_get_upcoming_events( 3 ) : array();
$story_previews     = function_exists( 'mlt_get_stories' ) ? mlt_get_stories( 2 ) : array();
$topic_labels       = function_exists( 'mlt_get_topic_labels' ) ? mlt_get_topic_labels() : array();
$event_cat_labels   = function_exists( 'mlt_get_event_category_labels' ) ? mlt_get_event_category_labels() : array();
$hero_img_url       = ! empty( $settings['heroImageUrl'] ) ? $settings['heroImageUrl'] : mlt_theme_image_url( 'hero-men-talking.jpg' );
$hero_title         = $settings['heroTitle'] ?? 'No man should walk alone.';
$hero_supporting    = $settings['heroSupportingMessage'] ?? 'Talk. Listen. Heal. Grow. Lead.';
$hero_description   = $settings['heroDescription'] ?? "Men Let's Talk creates safe spaces where men can be honest, heard, supported and equipped to navigate life together.";
$pillars            = $settings['pillars'] ?? array();
$featured_topics    = $settings['featuredTopics'] ?? array_keys( $topic_labels );
$community_features = $settings['communityFeatures'] ?? array();

get_header();
?>
<main data-testid="homepage">
	<!-- Hero -->
	<section data-testid="hero-section" class="relative px-6 py-32 text-center text-white">
		<img
			src="<?php echo esc_url( $hero_img_url ); ?>"
			alt="Men gathered in conversation at a Men Let&apos;s Talk event"
			class="absolute inset-0 h-full w-full object-cover"
		/>
		<div class="absolute inset-0 bg-neutral-900/70"></div>
		<div class="relative">
			<h1 class="mx-auto max-w-3xl text-4xl font-bold sm:text-5xl"><?php echo esc_html( $hero_title ); ?></h1>
			<p
				data-testid="supporting-message"
				class="mx-auto mt-4 text-sm font-semibold tracking-widest text-neutral-300 uppercase"
			><?php echo esc_html( $hero_supporting ); ?></p>
			<p class="mx-auto mt-6 max-w-xl text-lg text-neutral-200"><?php echo esc_html( $hero_description ); ?></p>
			<div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
				<a
					href="/get-involved"
					class="rounded-md bg-white px-6 py-3 font-semibold text-neutral-900"
				>Join the Conversation</a>
				<a
					href="/events"
					class="rounded-md border border-white px-6 py-3 font-semibold text-white"
				>See Upcoming Events</a>
			</div>
		</div>
	</section>

	<!-- What is Men Let's Talk -->
	<section data-testid="what-is-mlt-section" class="mx-auto max-w-4xl px-6 py-20">
		<p class="text-center text-lg text-neutral-700">
			Men Let&apos;s Talk is a movement creating spaces where men can have honest conversations
			about the things we often struggle to talk about.
		</p>
		<div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
			<?php foreach ( $pillars as $pillar ) : ?>
				<div class="text-center">
					<h3 class="text-lg font-bold uppercase tracking-wide"><?php echo esc_html( $pillar['name'] ); ?></h3>
					<p class="mt-2 text-sm text-neutral-600"><?php echo esc_html( $pillar['description'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="mt-12 text-center">
			<a href="/about" class="font-semibold underline">Our Story →</a>
		</div>
	</section>

	<!-- Signature statement visual break -->
	<section data-testid="signature-statement" class="bg-neutral-100 px-6 py-16 text-center">
		<p class="text-2xl font-bold sm:text-3xl">Strong men need strong community.</p>
	</section>

	<!-- What Are You Carrying? -->
	<section data-testid="carrying-section" class="mx-auto max-w-4xl px-6 py-20">
		<h2 class="text-center text-3xl font-bold">What Are You Carrying?</h2>
		<p class="mx-auto mt-4 max-w-xl text-center text-neutral-600">
			Sometimes the hardest thing for a man is finding a place to say, &ldquo;I&apos;m not
			okay.&rdquo;
		</p>
		<div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
			<?php foreach ( $featured_topics as $topic_val ) : ?>
				<?php $topic_label = $topic_labels[ $topic_val ] ?? $topic_val; ?>
				<a
					href="<?php echo esc_url( '/resources?topic=' . rawurlencode( $topic_val ) ); ?>"
					class="rounded-lg border border-neutral-200 px-4 py-6 text-center font-medium hover:border-neutral-400"
				><?php echo esc_html( $topic_label ); ?></a>
			<?php endforeach; ?>
		</div>
		<div class="mt-10 text-center">
			<a href="/resources" class="font-semibold underline">Explore Resources →</a>
		</div>
	</section>

	<!-- Upcoming Events -->
	<?php if ( ! empty( $upcoming_events ) ) : ?>
		<section data-testid="home-events-section" class="mx-auto max-w-5xl px-6 py-20">
			<h2 class="text-center text-3xl font-bold">Upcoming Events</h2>
			<div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
				<?php foreach ( $upcoming_events as $event ) : ?>
					<div class="rounded-lg border border-neutral-200 p-6">
						<?php if ( ! empty( $event['image_url'] ) ) : ?>
							<img
								src="<?php echo esc_url( $event['image_url'] ); ?>"
								alt="<?php echo esc_attr( $event['name'] ); ?>"
								width="400"
								height="225"
								class="mb-4 rounded-lg object-cover"
							/>
						<?php endif; ?>
						<p class="text-xs font-medium uppercase tracking-wide text-neutral-500">
							<?php echo esc_html( $event_cat_labels[ $event['category'] ] ?? $event['category'] ); ?>
						</p>
						<h3 class="mt-1 text-lg font-semibold"><?php echo esc_html( $event['name'] ); ?></h3>
						<p class="mt-1 text-sm text-neutral-600">
							<?php echo esc_html( date_i18n( 'l, j F Y', strtotime( $event['date'] ) ) ); ?> &middot; <?php echo esc_html( $event['location'] ); ?>
						</p>
						<p class="mt-3 text-sm"><?php echo esc_html( $event['description'] ); ?></p>
						<?php if ( ! empty( $event['registrationOpen'] ) ) : ?>
							<?php mlt_render_register_form( $event['id'], $event['name'] ); ?>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="mt-10 text-center">
				<a href="/events" class="rounded-md bg-neutral-900 px-6 py-3 font-semibold text-white">View All Events →</a>
			</div>
		</section>
	<?php endif; ?>

	<!-- Real stories teaser -->
	<?php if ( ! empty( $story_previews ) ) : ?>
		<section data-testid="stories-preview-section" class="mx-auto max-w-4xl px-6 py-20">
			<h2 class="text-center text-3xl font-bold">Real Men. Real Stories. Real Conversations.</h2>
			<p class="mx-auto mt-4 max-w-xl text-center text-neutral-600">
				You&apos;re not the only one carrying this. Here&apos;s what other men have shared.
			</p>
			<div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2">
				<?php foreach ( $story_previews as $story ) : ?>
					<div class="rounded-lg border border-neutral-200 p-6">
						<p class="text-sm font-medium text-neutral-500">
							<?php echo esc_html( $story['name'] ?: 'Anonymous' ); ?><?php echo ! empty( $story['ageOrCategory'] ) ? ', ' . esc_html( $story['ageOrCategory'] ) : ''; ?>
						</p>
						<?php if ( ! empty( $story['excerpt'] ) ) : ?>
							<p class="mt-3 text-neutral-700"><?php echo esc_html( mlt_truncate_text( $story['excerpt'], 160 ) ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="mt-10 text-center">
				<a href="/stories" class="font-semibold underline">Read more stories →</a>
			</div>
		</section>
	<?php endif; ?>

	<!-- Find Your Space -->
	<section data-testid="find-your-space-section" class="mx-auto max-w-4xl px-6 py-20">
		<h2 class="text-center text-3xl font-bold">Find Your Space.</h2>
		<p class="mx-auto mt-4 max-w-xl text-center text-neutral-600">
			Not every man needs the same kind of community.
		</p>
		<div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2">
			<?php foreach ( $community_features as $feature ) : ?>
				<div class="rounded-lg border border-neutral-200 p-6">
					<h3 class="text-lg font-bold uppercase tracking-wide"><?php echo esc_html( $feature['name'] ); ?></h3>
					<p class="mt-2 text-sm text-neutral-600"><?php echo esc_html( $feature['description'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="mt-10 text-center">
			<a href="/community" class="rounded-md bg-neutral-900 px-6 py-3 font-semibold text-white">Find Your Community →</a>
		</div>
	</section>

	<!-- Stay Connected -->
	<section data-testid="stay-connected-section" class="bg-neutral-100 px-6 py-16">
		<div class="mx-auto max-w-md text-center">
			<h2 class="text-2xl font-bold">Stay Connected</h2>
			<p class="mt-2 text-neutral-600">
				Get updates on events, new resources, and conversations — pick what matters to you.
			</p>
			<div class="mt-6 text-left">
				<?php mlt_render_subscribe_form(); ?>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
