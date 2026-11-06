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

	mlt_seed_default_media_content();

	return array(
		'created' => $created,
		'updated' => $updated,
		'home_id' => $home_id,
	);
}

/**
 * Idempotently seed the bundled upcoming event and 17 gallery photos into CPTs
 * so they appear in wp-admin as well as on the frontend.
 */
function mlt_seed_default_media_content(): void {
	if ( function_exists( 'mlt_get_default_events' ) && function_exists( 'mlt_find_post_by_sanity_id' ) ) {
		foreach ( mlt_get_default_events() as $ev ) {
			$post_id = mlt_find_post_by_sanity_id( 'mlt_event', $ev['id'] );
			if ( 0 === $post_id ) {
				$post_id = (int) wp_insert_post(
					array(
						'post_type'    => 'mlt_event',
						'post_status'  => 'publish',
						'post_title'   => $ev['name'],
						'post_name'    => $ev['slug'],
						'post_content' => $ev['description'],
					)
				);
			}
			if ( $post_id > 0 ) {
				update_post_meta( $post_id, '_mlt_sanity_id', $ev['id'] );
				update_post_meta( $post_id, '_mlt_event_date', $ev['date'] );
				update_post_meta( $post_id, '_mlt_event_location', $ev['location'] );
				update_post_meta( $post_id, '_mlt_event_category', $ev['category'] );
				update_post_meta( $post_id, '_mlt_event_registration_open', $ev['registrationOpen'] ? '1' : '0' );
				update_post_meta( $post_id, '_mlt_image_url', $ev['image_url'] );
			}
		}
	}

	if ( function_exists( 'mlt_get_default_gallery_images' ) && function_exists( 'mlt_find_post_by_sanity_id' ) ) {
		foreach ( array( 'gallery-image-12', 'gallery-image-14', 'gallery-image-15', 'gallery-image-16', 'gallery-image-17' ) as $removed_id ) {
			$old_post_id = mlt_find_post_by_sanity_id( 'mlt_gallery_image', $removed_id );
			if ( $old_post_id > 0 ) {
				wp_delete_post( $old_post_id, true );
			}
		}
		foreach ( mlt_get_default_gallery_images() as $img ) {
			$post_id = mlt_find_post_by_sanity_id( 'mlt_gallery_image', $img['id'] );
			if ( 0 === $post_id ) {
				$post_id = (int) wp_insert_post(
					array(
						'post_type'   => 'mlt_gallery_image',
						'post_status' => 'publish',
						'post_title'  => $img['caption'] ?: 'Gallery Photo',
					)
				);
			}
			if ( $post_id > 0 ) {
				update_post_meta( $post_id, '_mlt_sanity_id', $img['id'] );
				update_post_meta( $post_id, '_mlt_gallery_caption', $img['caption'] );
				update_post_meta( $post_id, '_mlt_image_url', $img['image_url'] );
			}
		}
	}
}

/**
 * Recursively copy directory contents from $src to $dst.
 */
function mlt_recursive_copy_dir( string $src, string $dst ): int {
	$count = 0;
	if ( ! is_dir( $src ) ) {
		return 0;
	}
	if ( ! is_dir( $dst ) && ! @mkdir( $dst, 0755, true ) ) {
		return 0;
	}
	$items = @scandir( $src );
	if ( ! is_array( $items ) ) {
		return 0;
	}
	foreach ( $items as $item ) {
		if ( '.' === $item || '..' === $item || 'local-preview' === $item || '.wp-runtime' === $item ) {
			continue;
		}
		$src_path = $src . '/' . $item;
		$dst_path = $dst . '/' . $item;
		if ( is_dir( $src_path ) ) {
			$count += mlt_recursive_copy_dir( $src_path, $dst_path );
		} elseif ( is_file( $src_path ) ) {
			if ( @copy( $src_path, $dst_path ) ) {
				$count++;
			}
		}
	}
	return $count;
}

/**
 * Ensure wp-config.php in $docroot has dynamic multi-host WP_HOME / WP_SITEURL support
 * for www.menletstalk.co.ke, menletstalk.co.ke, and mlt.ericgitonga.com.
 */
function mlt_ensure_dynamic_host_wp_config( string $wp_config_path, string $default_host = 'www.menletstalk.co.ke' ): void {
	if ( ! is_file( $wp_config_path ) || ! is_readable( $wp_config_path ) || ! is_writable( $wp_config_path ) ) {
		return;
	}
	$contents = (string) file_get_contents( $wp_config_path );
	if ( false !== strpos( $contents, 'MLT_DYNAMIC_HOST_CONFIG' ) ) {
		return;
	}
	$snippet  = "\n// MLT_DYNAMIC_HOST_CONFIG: support menletstalk.co.ke and mlt.ericgitonga.com seamlessly.\n";
	$snippet .= "\$mlt_allowed_hosts = array( 'www.menletstalk.co.ke', 'menletstalk.co.ke', 'mlt.ericgitonga.com' );\n";
	$snippet .= "\$mlt_req_host      = isset( \$_SERVER['HTTP_HOST'] ) ? strtolower( preg_replace( '/:\\d+\$/', '', (string) \$_SERVER['HTTP_HOST'] ) ) : '';\n";
	$snippet .= "\$mlt_active_host   = in_array( \$mlt_req_host, \$mlt_allowed_hosts, true ) ? \$mlt_req_host : '" . addslashes( $default_host ) . "';\n";
	$snippet .= "if ( ! defined( 'WP_HOME' ) ) {\n\tdefine( 'WP_HOME', 'https://' . \$mlt_active_host );\n}\n";
	$snippet .= "if ( ! defined( 'WP_SITEURL' ) ) {\n\tdefine( 'WP_SITEURL', 'https://' . \$mlt_active_host );\n}\n\n";

	$target = "if ( ! defined( 'ABSPATH' ) )";
	if ( false !== strpos( $contents, $target ) ) {
		$updated = str_replace( $target, $snippet . $target, $contents );
		@file_put_contents( $wp_config_path, $updated );
	}
}

/**
 * Sync the complete WordPress + mlt-wp installation from ericgitonga.com/mlt/html
 * into sibling menletstalk.co.ke/www/html on Angry Hosting if present.
 */
function mlt_sync_production_docroot(): array {
	$src_root     = rtrim( ABSPATH, '/\\' );
	$account_root = dirname( $src_root, 3 );
	$prod_docroot = $account_root . '/menletstalk.co.ke/www/html';

	mlt_ensure_dynamic_host_wp_config( $src_root . '/wp-config.php', 'mlt.ericgitonga.com' );

	if ( ! is_dir( $prod_docroot ) || realpath( $prod_docroot ) === realpath( $src_root ) ) {
		return array(
			'synced' => false,
			'files'  => 0,
			'target' => $prod_docroot,
		);
	}

	$copied = mlt_recursive_copy_dir( $src_root, $prod_docroot );
	mlt_ensure_dynamic_host_wp_config( $prod_docroot . '/wp-config.php', 'www.menletstalk.co.ke' );
	update_option( 'mlt_prod_docroot_synced', MLT_WP_CONTENT_VERSION );

	return array(
		'synced' => true,
		'files'  => $copied,
		'target' => $prod_docroot,
	);
}

function mlt_maybe_auto_sync_production_docroot(): void {
	if ( get_option( 'mlt_prod_docroot_synced' ) === MLT_WP_CONTENT_VERSION ) {
		return;
	}
	mlt_sync_production_docroot();
}
add_action( 'init', 'mlt_maybe_auto_sync_production_docroot', 20 );

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

	if ( isset( $_POST['mlt_save_public_writes'] ) && check_admin_referer( 'mlt_setup_action' ) ) {
		$enabled = ! empty( $_POST['mlt_public_submissions_enabled'] ) ? '1' : '0';
		update_option( 'mlt_public_submissions_enabled', $enabled );
		$notice = '1' === $enabled
			? 'Public form submissions (Contact, Event Registration, Newsletter Subscribe, Share Your Story) are now ENABLED.'
			: 'Public form submissions are now DISABLED (endpoints return 503).';
	}

	$public_writes_enabled = mlt_are_public_writes_configured();

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

		<hr />

		<h2>3. Public Form Submissions &amp; Outbound SMTP</h2>
		<p>Controls whether public write endpoints (<code>/api/contact</code>, <code>/api/register</code>, <code>/api/subscribe</code>, <code>/api/share-story</code>) accept live visitor submissions or return <code>503 Service Unavailable</code>.</p>
		<form method="post">
			<?php wp_nonce_field( 'mlt_setup_action' ); ?>
			<p>
				<label>
					<input type="checkbox" name="mlt_public_submissions_enabled" value="1" <?php checked( $public_writes_enabled ); ?> />
					<strong>Enable public visitor form submissions</strong>
				</label>
			</p>
			<p><button type="submit" name="mlt_save_public_writes" class="button button-primary">Save Submission Settings</button></p>
		</form>
	</div>
	<?php
}
