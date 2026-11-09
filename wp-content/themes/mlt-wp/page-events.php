<?php
/**
 * Events Page Template matching src/app/events/page.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$events           = function_exists( 'mlt_get_upcoming_events' ) ? mlt_get_upcoming_events() : array();
$past_events      = function_exists( 'mlt_get_past_events' ) ? mlt_get_past_events() : array();
$event_cat_labels = function_exists( 'mlt_get_event_category_labels' ) ? mlt_get_event_category_labels() : array();

get_header();
?>
<main data-testid="events-page" class="mx-auto max-w-3xl px-6 py-16">
	<?php
	mlt_render_breadcrumb(
		array(
			array( 'label' => 'Home', 'href' => '/' ),
			array( 'label' => 'Events' ),
		)
	);
	?>
	<h1 class="text-3xl font-bold">Upcoming Events</h1>

	<?php if ( empty( $events ) ) : ?>
		<p data-testid="events-empty-state" class="mt-8 text-neutral-600">
			No events are scheduled right now — check back soon.
		</p>
	<?php else : ?>
		<ul data-testid="events-list" class="mt-8 space-y-8">
			<?php foreach ( $events as $event ) : ?>
				<li id="<?php echo esc_attr( $event['id'] ); ?>" data-testid="event-item" class="scroll-mt-24 border-b border-neutral-200 pb-8">
					<?php if ( ! empty( $event['image_url'] ) ) : ?>
						<img
							src="<?php echo esc_url( $event['image_url'] ); ?>"
							alt="<?php echo esc_attr( $event['name'] ); ?>"
							width="1200"
							height="1600"
							class="mb-4 h-auto w-full rounded-lg"
						/>
					<?php endif; ?>
					<p class="text-sm font-medium uppercase tracking-wide text-neutral-500">
						<?php echo esc_html( $event_cat_labels[ $event['category'] ] ?? $event['category'] ); ?>
					</p>
					<h2 class="mt-1 text-xl font-semibold"><?php echo esc_html( $event['name'] ); ?></h2>
					<p class="mt-1 text-neutral-600">
						<?php echo esc_html( date_i18n( 'l, j F Y', strtotime( $event['date'] ) ) ); ?> &middot; <?php echo esc_html( $event['location'] ); ?>
					</p>
					<p class="mt-3"><?php echo esc_html( $event['description'] ); ?></p>
					<?php if ( ! empty( $event['registrationOpen'] ) ) : ?>
						<?php mlt_render_register_form( $event['id'], $event['name'] ); ?>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( ! empty( $past_events ) ) : ?>
		<section data-testid="past-events-section" class="mt-16 border-t border-neutral-200 pt-12">
			<h2 class="text-2xl font-bold">Past Events</h2>
			<ul data-testid="past-events-list" class="mt-8 space-y-8">
				<?php foreach ( $past_events as $event ) : ?>
					<li id="<?php echo esc_attr( $event['id'] ); ?>" data-testid="past-event-item" class="scroll-mt-24 border-b border-neutral-200 pb-8">
						<?php if ( ! empty( $event['image_url'] ) ) : ?>
							<img
								src="<?php echo esc_url( $event['image_url'] ); ?>"
								alt="<?php echo esc_attr( $event['name'] ); ?>"
								width="1200"
								height="1600"
								class="mb-4 h-auto w-full rounded-lg"
							/>
						<?php endif; ?>
						<p class="text-sm font-medium uppercase tracking-wide text-neutral-500">
							<?php echo esc_html( $event_cat_labels[ $event['category'] ] ?? $event['category'] ); ?>
						</p>
						<h3 class="mt-1 text-xl font-semibold"><?php echo esc_html( $event['name'] ); ?></h3>
						<p class="mt-1 text-neutral-600">
							<?php echo esc_html( date_i18n( 'l, j F Y', strtotime( $event['date'] ) ) ); ?> &middot; <?php echo esc_html( $event['location'] ); ?>
						</p>
						<p class="mt-3"><?php echo esc_html( $event['description'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
