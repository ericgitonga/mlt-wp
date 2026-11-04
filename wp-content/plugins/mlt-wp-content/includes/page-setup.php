<?php
/**
 * One-click Page Setup & Static Front Page Provisioner for Men Let's Talk.
 *
 * Ensures all 15 standard routes exist as published WordPress pages and configures
 * Home as the static front page (`show_on_front = page`).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mlt_get_required_pages(): array {
	return array(
		'home'             => 'Home',
		'about'            => "About Men Let's Talk",
		'talk'             => "Let's Talk",
		'events'           => 'Upcoming Events',
		'resources'        => 'Resources',
		'stories'          => 'Real Men. Real Stories. Real Conversations.',
		'share-your-story' => 'Share Your Story',
		'community'        => 'Find Your Space.',
		'get-involved'     => "There's a Place for You Here.",
		'partners'         => 'Partners & Sponsors',
		'books'            => 'Books & Projects',
		'gallery'          => 'Gallery',
		'contact'          => "Let's Talk.",
		'privacy'          => 'Privacy Notice',
		'search'           => 'Search',
	);
}

function mlt_page_setup_run(): array {
	$pages   = mlt_get_required_pages();
	$created = array();
	$updated = array();
	$home_id = 0;

	foreach ( $pages as $slug => $title ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing ) {
			if ( 'publish' !== $existing->post_status ) {
				wp_update_post(
					array(
						'ID'          => $existing->ID,
						'post_status' => 'publish',
					)
				);
			}
			$updated[] = $slug;
			$page_id   = (int) $existing->ID;
		} else {
			$page_id = (int) wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $title,
					'post_name'    => $slug,
					'post_content' => '',
				)
			);
			$created[] = $slug;
		}

		if ( 'home' === $slug && $page_id > 0 ) {
			$home_id = $page_id;
		}
	}

	if ( $home_id > 0 ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
	}

	$sample = get_page_by_path( 'sample-page', OBJECT, 'page' );
	if ( $sample ) {
		wp_delete_post( $sample->ID, true );
	}

	update_option( 'permalink_structure', '/%postname%/' );
	flush_rewrite_rules();

	return array(
		'created' => $created,
		'updated' => $updated,
		'home_id' => $home_id,
	);
}

function mlt_register_admin_menus(): void {
	add_management_page(
		'MLT Page Setup & Sanity Import',
		'MLT Setup & Import',
		'manage_options',
		'mlt-setup',
		'mlt_render_admin_setup_page'
	);
}
add_action( 'admin_menu', 'mlt_register_admin_menus' );

function mlt_render_admin_setup_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$notice = '';
	if ( isset( $_POST['mlt_run_page_setup'] ) && check_admin_referer( 'mlt_setup_action' ) ) {
		$res    = mlt_page_setup_run();
		$notice = sprintf(
			'Page setup complete. Created: %d, Verified: %d.',
			count( $res['created'] ),
			count( $res['updated'] )
		);
	}

	if ( isset( $_POST['mlt_run_sanity_import'] ) && check_admin_referer( 'mlt_setup_action' ) ) {
		$token  = isset( $_POST['sanity_read_token'] ) ? sanitize_text_field( wp_unslash( $_POST['sanity_read_token'] ) ) : '';
		$res    = mlt_sanity_import_run( $token );
		$notice = 'Sanity import result: ' . wp_json_encode( $res );
	}

	?>
	<div class="wrap">
		<h1>Men Let&apos;s Talk — Setup &amp; Sanity Migration</h1>
		<?php if ( $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>

		<h2>1. Provision Required Pages</h2>
		<p>Creates all 15 Men Let&apos;s Talk routes and configures the static front page.</p>
		<form method="post">
			<?php wp_nonce_field( 'mlt_setup_action' ); ?>
			<p><button type="submit" name="mlt_run_page_setup" class="button button-primary">Run Page Setup</button></p>
		</form>

		<hr />

		<h2>2. Import Content from Sanity (<code>9ks0sgl2 / production</code>)</h2>
		<p>Fetches <code>siteSettings</code>, events, resources, stories, community groups, partners, books, and gallery images from Sanity and imports them idempotently.</p>
		<form method="post">
			<?php wp_nonce_field( 'mlt_setup_action' ); ?>
			<p>
				<label for="sanity_read_token"><strong>Sanity Read Token:</strong></label><br />
				<input type="password" id="sanity_read_token" name="sanity_read_token" class="regular-text" autocomplete="off" />
			</p>
			<p><button type="submit" name="mlt_run_sanity_import" class="button button-secondary">Run Sanity Import</button></p>
		</form>
	</div>
	<?php
}
