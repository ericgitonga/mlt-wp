<?php
/**
 * WordPress Admin Meta Boxes, Site Settings UI, and Custom List-Table Columns
 * for Men Let's Talk (`mlt-wp-content`).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Use the streamlined Classic Editor layout for Men Let's Talk Custom Post Types
 * and the Home page so Title, Content, Featured Image, Publish box, and Custom Meta Boxes
 * appear together on a single uncluttered screen.
 */
function mlt_use_classic_editor_for_mlt_types( bool $use_block_editor, WP_Post $post ): bool {
	if ( 0 === strpos( $post->post_type, 'mlt_' ) ) {
		return false;
	}
	if ( 'page' === $post->post_type && mlt_is_home_page_post( (int) $post->ID ) ) {
		return false;
	}
	return $use_block_editor;
}
add_filter( 'use_block_editor_for_post', 'mlt_use_classic_editor_for_mlt_types', 10, 2 );

/**
 * Determine whether a given post ID is the Homepage ("Home" page).
 */
function mlt_is_home_page_post( int $post_id ): bool {
	if ( $post_id <= 0 ) {
		return false;
	}
	$front_page_id = (int) get_option( 'page_on_front' );
	if ( $front_page_id > 0 && $front_page_id === $post_id ) {
		return true;
	}
	$post = get_post( $post_id );
	return $post instanceof WP_Post && 'page' === $post->post_type && 'home' === $post->post_name;
}

/**
 * Register Meta Boxes across all Men Let's Talk Custom Post Types and the Home Page.
 */
function mlt_register_admin_meta_boxes(): void {
	add_meta_box(
		'mlt_event_details',
		'Event Details (Date, Venue, Category & Registration)',
		'mlt_render_event_meta_box',
		'mlt_event',
		'normal',
		'high'
	);

	add_meta_box(
		'mlt_resource_details',
		'Resource Details (Type, Topics, Video & Download Link)',
		'mlt_render_resource_meta_box',
		'mlt_resource',
		'normal',
		'high'
	);

	add_meta_box(
		'mlt_story_details',
		'Story Details & Kenya DPA 2019 Publication Consent',
		'mlt_render_story_meta_box',
		'mlt_story',
		'normal',
		'high'
	);

	add_meta_box(
		'mlt_community_group_details',
		'Community Group Details (Target Audience)',
		'mlt_render_community_group_meta_box',
		'mlt_community_group',
		'normal',
		'high'
	);

	add_meta_box(
		'mlt_partner_details',
		'Partner Details (Partnership Category & Website)',
		'mlt_render_partner_meta_box',
		'mlt_partner',
		'normal',
		'high'
	);

	add_meta_box(
		'mlt_book_details',
		'Book / Project Details (Author, Purpose, Order Link & Testimonials)',
		'mlt_render_book_meta_box',
		'mlt_book',
		'normal',
		'high'
	);

	add_meta_box(
		'mlt_gallery_image_details',
		'Gallery Photo Details (Caption & Image Source)',
		'mlt_render_gallery_image_meta_box',
		'mlt_gallery_image',
		'normal',
		'high'
	);

	add_meta_box(
		'mlt_registration_details',
		'Event Registration Details (Submitted via Website)',
		'mlt_render_registration_meta_box',
		'mlt_registration',
		'normal',
		'high'
	);

	add_meta_box(
		'mlt_subscriber_details',
		'Newsletter & Updates Subscriber Details',
		'mlt_render_subscriber_meta_box',
		'mlt_subscriber',
		'normal',
		'high'
	);

	add_meta_box(
		'mlt_submission_details',
		'Contact Enquiry Sender Details',
		'mlt_render_submission_meta_box',
		'mlt_submission',
		'normal',
		'high'
	);

	// Register Homepage & Global Site Settings meta box on the Home page editor screen.
	global $post;
	$current_id = 0;
	if ( $post instanceof WP_Post ) {
		$current_id = (int) $post->ID;
	} elseif ( isset( $_GET['post'] ) ) {
		$current_id = (int) $_GET['post'];
	} elseif ( isset( $_POST['post_ID'] ) ) {
		$current_id = (int) $_POST['post_ID'];
	}

	if ( $current_id > 0 && mlt_is_home_page_post( $current_id ) ) {
		add_meta_box(
			'mlt_site_settings_metabox',
			"Men Let's Talk — Homepage Hero, Pillars, Topics, Community & Core Values",
			'mlt_render_site_settings_meta_box',
			'page',
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'mlt_register_admin_meta_boxes' );

/**
 * Render Homepage & Site Settings fields (shared between Home Page meta box and Settings page).
 */
function mlt_render_site_settings_fields(): void {
	$settings     = mlt_get_site_settings();
	$topic_labels = mlt_get_topic_labels();
	wp_nonce_field( 'mlt_save_site_settings', 'mlt_site_settings_nonce' );
	?>
	<style>
		.mlt-admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 8px; }
		.mlt-admin-card { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; }
		.mlt-admin-card h4 { margin: 0 0 8px; font-size: 13px; color: #1e293b; }
		.mlt-topic-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(175px, 1fr)); gap: 8px; margin-top: 6px; }
		.mlt-topic-item { display: flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 6px 10px; border-radius: 4px; }
		.mlt-section-heading { margin: 20px 0 8px; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0; font-size: 14px; font-weight: 600; color: #0f172a; }
	</style>

	<h3 class="mlt-section-heading">1. Homepage Hero Banner</h3>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="mlt_hero_title">Hero Headline</label></th>
			<td>
				<input type="text" id="mlt_hero_title" name="mlt_site_settings[heroTitle]" value="<?php echo esc_attr( $settings['heroTitle'] ); ?>" class="large-text" />
				<p class="description">Main headline displayed at the top of the Homepage (e.g. <em>No man should walk alone.</em>).</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_hero_supporting">Supporting Tagline</label></th>
			<td>
				<input type="text" id="mlt_hero_supporting" name="mlt_site_settings[heroSupportingMessage]" value="<?php echo esc_attr( $settings['heroSupportingMessage'] ); ?>" class="large-text" />
				<p class="description">Sub-headline below the main hero title (e.g. <em>Talk. Listen. Heal. Grow. Lead.</em>).</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_hero_description">Hero Intro Paragraph</label></th>
			<td>
				<textarea id="mlt_hero_description" name="mlt_site_settings[heroDescription]" rows="3" class="large-text"><?php echo esc_textarea( $settings['heroDescription'] ); ?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_hero_image_url">Hero Banner Image URL</label></th>
			<td>
				<input type="url" id="mlt_hero_image_url" name="mlt_site_settings[heroImageUrl]" value="<?php echo esc_attr( $settings['heroImageUrl'] ); ?>" class="large-text" placeholder="Leave blank to use the default Men Let's Talk hero photograph" />
				<p class="description">Paste an image URL from <strong>Media &rarr; Library</strong> or leave empty to use the bundled homepage hero portrait.</p>
			</td>
		</tr>
	</table>

	<h3 class="mlt-section-heading">2. Four Core Pillars (Homepage &amp; About Us)</h3>
	<div class="mlt-admin-grid">
		<?php foreach ( $settings['pillars'] as $idx => $pillar ) : ?>
			<div class="mlt-admin-card">
				<h4>Pillar #<?php echo (int) ( $idx + 1 ); ?></h4>
				<p>
					<label><strong>Title:</strong></label><br />
					<input type="text" name="mlt_site_settings[pillars][<?php echo (int) $idx; ?>][name]" value="<?php echo esc_attr( $pillar['name'] ?? '' ); ?>" class="widefat" />
				</p>
				<p style="margin-bottom:0;">
					<label><strong>Description:</strong></label><br />
					<textarea name="mlt_site_settings[pillars][<?php echo (int) $idx; ?>][description]" rows="2" class="widefat"><?php echo esc_textarea( $pillar['description'] ?? '' ); ?></textarea>
				</p>
			</div>
		<?php endforeach; ?>
	</div>

	<h3 class="mlt-section-heading">3. Featured Conversation Topics (Homepage &amp; Let's Talk Hub)</h3>
	<p class="description">Check the topics to highlight on the Homepage and Let's Talk page:</p>
	<div class="mlt-topic-grid">
		<?php foreach ( $topic_labels as $slug => $label ) : ?>
			<label class="mlt-topic-item">
				<input
					type="checkbox"
					name="mlt_site_settings[featuredTopics][]"
					value="<?php echo esc_attr( $slug ); ?>"
					<?php checked( in_array( $slug, $settings['featuredTopics'], true ) ); ?>
				/>
				<span><?php echo esc_html( $label ); ?></span>
			</label>
		<?php endforeach; ?>
	</div>

	<h3 class="mlt-section-heading">4. Community Highlights (Homepage Community Section)</h3>
	<div class="mlt-admin-grid">
		<?php foreach ( $settings['communityFeatures'] as $idx => $feat ) : ?>
			<div class="mlt-admin-card">
				<h4>Community Highlight #<?php echo (int) ( $idx + 1 ); ?></h4>
				<p>
					<label><strong>Name:</strong></label><br />
					<input type="text" name="mlt_site_settings[communityFeatures][<?php echo (int) $idx; ?>][name]" value="<?php echo esc_attr( $feat['name'] ?? '' ); ?>" class="widefat" />
				</p>
				<p style="margin-bottom:0;">
					<label><strong>Description:</strong></label><br />
					<textarea name="mlt_site_settings[communityFeatures][<?php echo (int) $idx; ?>][description]" rows="2" class="widefat"><?php echo esc_textarea( $feat['description'] ?? '' ); ?></textarea>
				</p>
			</div>
		<?php endforeach; ?>
	</div>

	<h3 class="mlt-section-heading">5. Six Core Values (Displayed on the About Us Page)</h3>
	<div class="mlt-admin-grid">
		<?php foreach ( $settings['values'] as $idx => $val ) : ?>
			<div class="mlt-admin-card">
				<h4>Value #<?php echo (int) ( $idx + 1 ); ?></h4>
				<p>
					<label><strong>Value Name:</strong></label><br />
					<input type="text" name="mlt_site_settings[values][<?php echo (int) $idx; ?>][name]" value="<?php echo esc_attr( $val['name'] ?? '' ); ?>" class="widefat" />
				</p>
				<p style="margin-bottom:0;">
					<label><strong>Description:</strong></label><br />
					<textarea name="mlt_site_settings[values][<?php echo (int) $idx; ?>][description]" rows="2" class="widefat"><?php echo esc_textarea( $val['description'] ?? '' ); ?></textarea>
				</p>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

function mlt_render_site_settings_meta_box( WP_Post $post ): void {
	mlt_render_site_settings_fields();
}

/**
 * Sanitize and persist `mlt_site_settings` array from POST payload.
 */
function mlt_save_site_settings_from_request( array $raw ): void {
	$defaults     = mlt_get_default_site_settings();
	$valid_topics = array_keys( mlt_get_topic_labels() );

	$hero_title       = isset( $raw['heroTitle'] ) ? sanitize_text_field( wp_unslash( $raw['heroTitle'] ) ) : $defaults['heroTitle'];
	$hero_supporting  = isset( $raw['heroSupportingMessage'] ) ? sanitize_text_field( wp_unslash( $raw['heroSupportingMessage'] ) ) : $defaults['heroSupportingMessage'];
	$hero_description = isset( $raw['heroDescription'] ) ? sanitize_textarea_field( wp_unslash( $raw['heroDescription'] ) ) : $defaults['heroDescription'];
	$hero_image_url   = isset( $raw['heroImageUrl'] ) ? esc_url_raw( trim( wp_unslash( $raw['heroImageUrl'] ) ) ) : '';

	$pillars = array();
	if ( ! empty( $raw['pillars'] ) && is_array( $raw['pillars'] ) ) {
		foreach ( $raw['pillars'] as $item ) {
			$name = isset( $item['name'] ) ? sanitize_text_field( wp_unslash( $item['name'] ) ) : '';
			$desc = isset( $item['description'] ) ? sanitize_textarea_field( wp_unslash( $item['description'] ) ) : '';
			if ( '' !== $name ) {
				$pillars[] = array(
					'name'        => $name,
					'description' => $desc,
				);
			}
		}
	}

	$featured_topics = array();
	if ( ! empty( $raw['featuredTopics'] ) && is_array( $raw['featuredTopics'] ) ) {
		foreach ( $raw['featuredTopics'] as $t ) {
			$slug = sanitize_text_field( wp_unslash( $t ) );
			if ( in_array( $slug, $valid_topics, true ) ) {
				$featured_topics[] = $slug;
			}
		}
	}

	$community_features = array();
	if ( ! empty( $raw['communityFeatures'] ) && is_array( $raw['communityFeatures'] ) ) {
		foreach ( $raw['communityFeatures'] as $item ) {
			$name = isset( $item['name'] ) ? sanitize_text_field( wp_unslash( $item['name'] ) ) : '';
			$desc = isset( $item['description'] ) ? sanitize_textarea_field( wp_unslash( $item['description'] ) ) : '';
			if ( '' !== $name ) {
				$community_features[] = array(
					'name'        => $name,
					'description' => $desc,
				);
			}
		}
	}

	$values = array();
	if ( ! empty( $raw['values'] ) && is_array( $raw['values'] ) ) {
		foreach ( $raw['values'] as $item ) {
			$name = isset( $item['name'] ) ? sanitize_text_field( wp_unslash( $item['name'] ) ) : '';
			$desc = isset( $item['description'] ) ? sanitize_textarea_field( wp_unslash( $item['description'] ) ) : '';
			if ( '' !== $name ) {
				$values[] = array(
					'name'        => $name,
					'description' => $desc,
				);
			}
		}
	}

	update_option(
		'mlt_site_settings',
		array(
			'heroTitle'             => $hero_title ?: $defaults['heroTitle'],
			'heroSupportingMessage' => $hero_supporting ?: $defaults['heroSupportingMessage'],
			'heroDescription'       => $hero_description ?: $defaults['heroDescription'],
			'heroImageUrl'          => $hero_image_url,
			'pillars'               => ! empty( $pillars ) ? $pillars : $defaults['pillars'],
			'featuredTopics'        => ! empty( $featured_topics ) ? $featured_topics : $defaults['featuredTopics'],
			'communityFeatures'     => ! empty( $community_features ) ? $community_features : $defaults['communityFeatures'],
			'values'                => ! empty( $values ) ? $values : $defaults['values'],
		)
	);
}

/**
 * Also register Settings -> MLT Site Settings for quick global access.
 */
function mlt_register_site_settings_menu(): void {
	add_options_page(
		"Men Let's Talk Site Settings",
		'MLT Site Settings',
		'manage_options',
		'mlt-site-settings',
		'mlt_render_site_settings_admin_page'
	);
}
add_action( 'admin_menu', 'mlt_register_site_settings_menu' );

function mlt_render_site_settings_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$saved_notice = false;
	if (
		isset( $_POST['mlt_site_settings_nonce'], $_POST['mlt_site_settings'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mlt_site_settings_nonce'] ) ), 'mlt_save_site_settings' ) &&
		is_array( $_POST['mlt_site_settings'] )
	) {
		mlt_save_site_settings_from_request( $_POST['mlt_site_settings'] );
		$saved_notice = true;
	}
	?>
	<div class="wrap">
		<h1>Men Let's Talk — Homepage &amp; Global Site Settings</h1>
		<?php if ( $saved_notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><strong>Site settings saved.</strong> Your changes are now live on the Homepage and About Us page.</p></div>
		<?php endif; ?>
		<form method="post" action="">
			<?php
			mlt_render_site_settings_fields();
			submit_button( 'Save Site Settings' );
			?>
		</form>
	</div>
	<?php
}

/**
 * 1. Event Meta Box (`mlt_event`)
 */
function mlt_render_event_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'mlt_save_event_meta', 'mlt_event_meta_nonce' );
	$raw_date    = (string) get_post_meta( $post->ID, '_mlt_event_date', true );
	$location    = (string) get_post_meta( $post->ID, '_mlt_event_location', true );
	$category    = (string) get_post_meta( $post->ID, '_mlt_event_category', true );
	$reg_meta    = get_post_meta( $post->ID, '_mlt_event_registration_open', true );
	$reg_open    = ( '' === $reg_meta ) ? true : ( '1' === (string) $reg_meta || true === $reg_meta );
	$image_url   = (string) get_post_meta( $post->ID, '_mlt_image_url', true );
	$categories  = mlt_get_event_category_labels();

	$dt_local = '';
	if ( '' !== $raw_date ) {
		$ts = strtotime( $raw_date );
		if ( false !== $ts ) {
			$dt_local = gmdate( 'Y-m-d\TH:i', $ts );
		}
	}
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="mlt_event_date">Event Date &amp; Start Time</label></th>
			<td>
				<input type="datetime-local" id="mlt_event_date" name="mlt_event_date" value="<?php echo esc_attr( $dt_local ); ?>" class="regular-text" />
				<p class="description">Events with a future date appear automatically under <strong>Upcoming Events</strong> on the Events page and Homepage. Past events move off the upcoming list automatically.</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_event_location">Venue / Location</label></th>
			<td>
				<input type="text" id="mlt_event_location" name="mlt_event_location" value="<?php echo esc_attr( $location ); ?>" class="regular-text" placeholder="e.g. Junction Mall, Ngong Road or Heri Heights Hotel, Kolobot Drive" />
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_event_category">Event Category</label></th>
			<td>
				<select id="mlt_event_category" name="mlt_event_category">
					<option value="">— Select Category —</option>
					<?php foreach ( $categories as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $category, $slug ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">Online Registration</th>
			<td>
				<label for="mlt_event_registration_open">
					<input type="checkbox" id="mlt_event_registration_open" name="mlt_event_registration_open" value="1" <?php checked( $reg_open ); ?> />
					<strong>Registration is open</strong> (display this event in the Event Registration dropdown on <code>/events</code>)
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_event_image_url">Event Flier / Poster URL</label></th>
			<td>
				<input type="url" id="mlt_event_image_url" name="mlt_image_url" value="<?php echo esc_attr( $image_url ); ?>" class="large-text" placeholder="Optional if Featured Image is set in the right sidebar" />
				<p class="description">Recommended: Use the <strong>Featured Image</strong> box in the right-hand sidebar to upload or select the event poster. This URL field serves as an optional fallback.</p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * 2. Resource Meta Box (`mlt_resource`)
 */
function mlt_render_resource_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'mlt_save_resource_meta', 'mlt_resource_meta_nonce' );
	$res_type     = (string) get_post_meta( $post->ID, '_mlt_resource_type', true ) ?: 'article';
	$topics       = get_post_meta( $post->ID, '_mlt_resource_topics', true );
	$topics       = is_array( $topics ) ? $topics : array();
	$video_url    = (string) get_post_meta( $post->ID, '_mlt_video_url', true );
	$download_url = (string) get_post_meta( $post->ID, '_mlt_download_url', true );
	$types        = mlt_get_resource_type_labels();
	$topic_labels = mlt_get_topic_labels();
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="mlt_resource_type">Resource Format / Type</label></th>
			<td>
				<select id="mlt_resource_type" name="mlt_resource_type">
					<?php foreach ( $types as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $res_type, $slug ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description">Displayed as the badge above the resource title on <code>/resources</code>.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">Conversation Topics</th>
			<td>
				<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:8px;">
					<?php foreach ( $topic_labels as $slug => $label ) : ?>
						<label style="display:flex;align-items:center;gap:6px;">
							<input type="checkbox" name="mlt_resource_topics[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $topics, true ) ); ?> />
							<span><?php echo esc_html( $label ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
				<p class="description">Visitors can filter resources by these topics on <code>/resources</code> and <code>/talk</code>.</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_resource_video_url">Video Embed URL (Optional)</label></th>
			<td>
				<input type="url" id="mlt_resource_video_url" name="mlt_video_url" value="<?php echo esc_attr( $video_url ); ?>" class="large-text" placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/..." />
				<p class="description">YouTube and Vimeo links are automatically embedded in a responsive video player on the resource detail page.</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_resource_download_url">Downloadable Guide / PDF URL (Optional)</label></th>
			<td>
				<input type="url" id="mlt_resource_download_url" name="mlt_download_url" value="<?php echo esc_attr( $download_url ); ?>" class="large-text" placeholder="https://www.menletstalk.co.ke/wp-content/uploads/..." />
				<p class="description">Upload a PDF discussion guide or worksheet under <strong>Media &rarr; Add Media File</strong>, copy its File URL, and paste it here to show a <strong>Download guide</strong> link.</p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * 3. Story Meta Box (`mlt_story`)
 */
function mlt_render_story_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'mlt_save_story_meta', 'mlt_story_meta_nonce' );
	$story_name       = (string) get_post_meta( $post->ID, '_mlt_story_name', true );
	$age_or_category  = (string) get_post_meta( $post->ID, '_mlt_story_age_or_category', true );
	$topics           = get_post_meta( $post->ID, '_mlt_story_topics', true );
	$topics           = is_array( $topics ) ? $topics : array();
	$video_url        = (string) get_post_meta( $post->ID, '_mlt_video_url', true );
	$consent_meta     = get_post_meta( $post->ID, '_mlt_consent_given', true );
	$consent_given    = ( '' === $consent_meta ) ? true : ( '1' === (string) $consent_meta || true === $consent_meta );
	$proc_meta        = get_post_meta( $post->ID, '_mlt_processing_consent_given', true );
	$proc_consent     = ( '' === $proc_meta ) ? true : ( '1' === (string) $proc_meta || true === $proc_meta );
	$consent_version  = (string) get_post_meta( $post->ID, '_mlt_consent_version', true ) ?: '2026-04-17';
	$consented_at     = (string) get_post_meta( $post->ID, '_mlt_consented_at', true );
	$topic_labels     = mlt_get_topic_labels();
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="mlt_story_name">Author Display Name</label></th>
			<td>
				<input type="text" id="mlt_story_name" name="mlt_story_name" value="<?php echo esc_attr( $story_name ); ?>" class="regular-text" placeholder="Anonymous (or first name / pseudonym, e.g. David M.)" />
				<p class="description">Leave blank or enter <code>Anonymous</code> to publish anonymously.</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_story_age_or_category">Age or Life Stage / Category</label></th>
			<td>
				<input type="text" id="mlt_story_age_or_category" name="mlt_story_age_or_category" value="<?php echo esc_attr( $age_or_category ); ?>" class="regular-text" placeholder="e.g. 36, Father of two · Nairobi" />
				<p class="description">Shown next to the author's display name on the story card.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">Story Topics</th>
			<td>
				<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:8px;">
					<?php foreach ( $topic_labels as $slug => $label ) : ?>
						<label style="display:flex;align-items:center;gap:6px;">
							<input type="checkbox" name="mlt_story_topics[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $topics, true ) ); ?> />
							<span><?php echo esc_html( $label ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_story_video_url">Video Testimony URL (Optional)</label></th>
			<td>
				<input type="url" id="mlt_story_video_url" name="mlt_video_url" value="<?php echo esc_attr( $video_url ); ?>" class="large-text" placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/..." />
			</td>
		</tr>
		<tr>
			<th scope="row">Kenya DPA 2019 Publication Consent</th>
			<td>
				<p>
					<label for="mlt_story_consent_given">
						<input type="checkbox" id="mlt_story_consent_given" name="mlt_consent_given" value="1" <?php checked( $consent_given ); ?> />
						<strong>Publication Consent Confirmed</strong> — Author explicitly consented to publish this story on Men Let's Talk.
					</label>
				</p>
				<p style="margin-top:6px;">
					<label for="mlt_story_processing_consent">
						<input type="checkbox" id="mlt_story_processing_consent" name="mlt_processing_consent_given" value="1" <?php checked( $proc_consent ); ?> />
						<strong>Data Processing Consent Confirmed</strong> — Author consented to sensitive personal data processing under the Kenya Data Protection Act, 2019.
					</label>
				</p>
				<p class="description">
					<strong>Important:</strong> Stories only appear publicly on <code>/stories</code> when <strong>Status = Published</strong> AND <strong>Publication Consent Confirmed</strong> is checked. Unchecking this box immediately withdraws the story from public display without deleting the record.
					<?php if ( '' !== $consented_at ) : ?>
						<br /><em>Recorded consent timestamp: <code><?php echo esc_html( $consented_at ); ?></code> (Policy version: <code><?php echo esc_html( $consent_version ); ?></code>)</em>
					<?php endif; ?>
				</p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * 4. Community Group Meta Box (`mlt_community_group`)
 */
function mlt_render_community_group_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'mlt_save_community_group_meta', 'mlt_community_group_meta_nonce' );
	$audience  = (string) get_post_meta( $post->ID, '_mlt_group_audience', true );
	$audiences = mlt_get_audience_labels();
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="mlt_group_audience">Target Audience Group</label></th>
			<td>
				<select id="mlt_group_audience" name="mlt_group_audience">
					<option value="">— Select Audience —</option>
					<?php foreach ( $audiences as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $audience, $slug ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description">Determines the audience badge displayed on the Community page (<code>/community</code>).</p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * 5. Partner Meta Box (`mlt_partner`)
 */
function mlt_render_partner_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'mlt_save_partner_meta', 'mlt_partner_meta_nonce' );
	$partner_type = (string) get_post_meta( $post->ID, '_mlt_partnership_type', true );
	$website_url  = (string) get_post_meta( $post->ID, '_mlt_website_url', true );
	$types        = mlt_get_partnership_type_labels();
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="mlt_partnership_type">Partnership Category</label></th>
			<td>
				<select id="mlt_partnership_type" name="mlt_partnership_type">
					<option value="">— Select Partnership Type —</option>
					<?php foreach ( $types as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $partner_type, $slug ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_website_url">Partner Website URL (Optional)</label></th>
			<td>
				<input type="url" id="mlt_website_url" name="mlt_website_url" value="<?php echo esc_attr( $website_url ); ?>" class="large-text" placeholder="https://example.org" />
				<p class="description">Set the partner's logo using the <strong>Featured Image</strong> panel in the right sidebar.</p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * 6. Book / Project Meta Box (`mlt_book`)
 */
function mlt_render_book_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'mlt_save_book_meta', 'mlt_book_meta_nonce' );
	$author       = (string) get_post_meta( $post->ID, '_mlt_book_author', true );
	$why_written  = (string) get_post_meta( $post->ID, '_mlt_book_why_written', true );
	$purchase_url = (string) get_post_meta( $post->ID, '_mlt_book_purchase_url', true );
	$testimonials = get_post_meta( $post->ID, '_mlt_book_testimonials', true );
	$lines        = array();
	if ( is_array( $testimonials ) ) {
		foreach ( $testimonials as $item ) {
			if ( is_array( $item ) && ! empty( $item['quote'] ) ) {
				$line = trim( (string) $item['quote'] );
				if ( ! empty( $item['attribution'] ) ) {
					$line .= ' | ' . trim( (string) $item['attribution'] );
				}
				$lines[] = $line;
			}
		}
	}
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="mlt_book_author">Author Name</label></th>
			<td>
				<input type="text" id="mlt_book_author" name="mlt_book_author" value="<?php echo esc_attr( $author ); ?>" class="regular-text" placeholder="e.g. Men Let's Talk Editorial Team" />
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_book_why_written">Why This Book Was Written</label></th>
			<td>
				<textarea id="mlt_book_why_written" name="mlt_book_why_written" rows="3" class="large-text" placeholder="Explain the heart and purpose behind this book or project..."><?php echo esc_textarea( $why_written ); ?></textarea>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_book_purchase_url">Order / Purchase Link URL</label></th>
			<td>
				<input type="url" id="mlt_book_purchase_url" name="mlt_book_purchase_url" value="<?php echo esc_attr( $purchase_url ); ?>" class="large-text" placeholder="https://wa.me/254727246599 or bookstore link" />
				<p class="description">When filled in, an <strong>Order now</strong> button is displayed on <code>/books</code>. Upload the book cover image in the <strong>Featured Image</strong> box on the right.</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_book_testimonials_raw">Reader Endorsements / Testimonials</label></th>
			<td>
				<textarea id="mlt_book_testimonials_raw" name="mlt_book_testimonials_raw" rows="4" class="large-text" placeholder="One testimonial per line in the format: Quote text | Reader Name &amp; Role"><?php echo esc_textarea( implode( "\n", $lines ) ); ?></textarea>
				<p class="description">Enter one endorsement per line using <code>Quote | Attribution</code> (for example: <code>An honest companion for every father. | James K., Nairobi</code>).</p>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * 7. Gallery Photo Meta Box (`mlt_gallery_image`)
 */
function mlt_render_gallery_image_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'mlt_save_gallery_image_meta', 'mlt_gallery_image_meta_nonce' );
	$album     = (string) get_post_meta( $post->ID, '_mlt_gallery_album', true ) ?: 'community-gatherings';
	$albums    = function_exists( 'mlt_get_gallery_albums' ) ? mlt_get_gallery_albums() : array();
	$caption   = (string) get_post_meta( $post->ID, '_mlt_gallery_caption', true );
	$image_url = (string) get_post_meta( $post->ID, '_mlt_image_url', true );
	$thumb_url = get_the_post_thumbnail_url( $post->ID, 'medium' ) ?: mlt_normalize_theme_asset_url( $image_url );
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="mlt_gallery_album">Gallery Album / Folder</label></th>
			<td>
				<select id="mlt_gallery_album" name="mlt_gallery_album">
					<?php foreach ( $albums as $slug => $meta ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $album, $slug ); ?>>
							<?php echo esc_html( $meta['title'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_gallery_caption">Photo Caption</label></th>
			<td>
				<input type="text" id="mlt_gallery_caption" name="mlt_gallery_caption" value="<?php echo esc_attr( $caption ); ?>" class="large-text" placeholder="e.g. Men's Breakfast conversation at Heri Heights Hotel" />
				<p class="description">Shown beneath the photograph on the <code>/gallery</code> page and used as accessible alt text.</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="mlt_gallery_image_url">Fallback Image URL (Optional)</label></th>
			<td>
				<input type="url" id="mlt_gallery_image_url" name="mlt_image_url" value="<?php echo esc_attr( $image_url ); ?>" class="large-text" placeholder="Upload photo via Featured Image in the right sidebar (recommended)" />
				<?php if ( '' !== $thumb_url ) : ?>
					<div style="margin-top:10px;">
						<img src="<?php echo esc_url( $thumb_url ); ?>" alt="" style="max-width:220px;height:auto;border-radius:6px;border:1px solid #cbd5e1;" />
					</div>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * 8. Visitor Submissions Meta Boxes (`mlt_registration`, `mlt_subscriber`, `mlt_submission`)
 */
function mlt_render_registration_meta_box( WP_Post $post ): void {
	$event_id     = (string) get_post_meta( $post->ID, '_mlt_event_id', true );
	$name         = (string) get_post_meta( $post->ID, '_mlt_name', true );
	$email        = (string) get_post_meta( $post->ID, '_mlt_email', true );
	$phone        = (string) get_post_meta( $post->ID, '_mlt_phone', true );
	$consent      = (string) get_post_meta( $post->ID, '_mlt_consent_given', true );
	$consent_ver  = (string) get_post_meta( $post->ID, '_mlt_consent_version', true );
	$consented_at = (string) get_post_meta( $post->ID, '_mlt_consented_at', true );
	?>
	<table class="form-table" role="presentation">
		<tr><th scope="row">Attendee Name</th><td><strong><?php echo esc_html( $name ?: get_the_title( $post ) ); ?></strong></td></tr>
		<tr><th scope="row">Email Address</th><td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ?: '—' ); ?></a></td></tr>
		<tr><th scope="row">Phone Number</th><td><?php echo esc_html( $phone ?: 'Not provided' ); ?></td></tr>
		<tr><th scope="row">Event Identifier</th><td><code><?php echo esc_html( $event_id ?: '—' ); ?></code></td></tr>
		<tr><th scope="row">Kenya DPA 2019 Consent</th><td><?php echo '1' === $consent ? 'Confirmed' : 'Not recorded'; ?> <?php echo $consented_at ? '(' . esc_html( $consented_at ) . ', Policy v' . esc_html( $consent_ver ) . ')' : ''; ?></td></tr>
	</table>
	<?php
}

function mlt_render_subscriber_meta_box( WP_Post $post ): void {
	$email        = (string) get_post_meta( $post->ID, '_mlt_email', true ) ?: get_the_title( $post );
	$interests    = get_post_meta( $post->ID, '_mlt_interests', true );
	$interests    = is_array( $interests ) ? $interests : array();
	$consent      = (string) get_post_meta( $post->ID, '_mlt_consent_given', true );
	$consent_ver  = (string) get_post_meta( $post->ID, '_mlt_consent_version', true );
	$consented_at = (string) get_post_meta( $post->ID, '_mlt_consented_at', true );
	?>
	<table class="form-table" role="presentation">
		<tr><th scope="row">Subscriber Email</th><td><a href="mailto:<?php echo esc_attr( $email ); ?>"><strong><?php echo esc_html( $email ); ?></strong></a></td></tr>
		<tr><th scope="row">Selected Interests</th><td><?php echo ! empty( $interests ) ? esc_html( implode( ', ', $interests ) ) : 'All updates'; ?></td></tr>
		<tr><th scope="row">Kenya DPA 2019 Consent</th><td><?php echo '1' === $consent ? 'Confirmed' : 'Not recorded'; ?> <?php echo $consented_at ? '(' . esc_html( $consented_at ) . ', Policy v' . esc_html( $consent_ver ) . ')' : ''; ?></td></tr>
	</table>
	<?php
}

function mlt_render_submission_meta_box( WP_Post $post ): void {
	$name  = (string) get_post_meta( $post->ID, '_mlt_name', true );
	$email = (string) get_post_meta( $post->ID, '_mlt_email', true );
	$phone = (string) get_post_meta( $post->ID, '_mlt_phone', true );
	$topic = (string) get_post_meta( $post->ID, '_mlt_topic', true );
	?>
	<table class="form-table" role="presentation">
		<tr><th scope="row">Sender Name</th><td><strong><?php echo esc_html( $name ?: '—' ); ?></strong></td></tr>
		<tr><th scope="row">Reply Email</th><td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ?: '—' ); ?></a></td></tr>
		<tr><th scope="row">Phone Number</th><td><?php echo esc_html( $phone ?: 'Not provided' ); ?></td></tr>
		<tr><th scope="row">Enquiry Topic</th><td><?php echo esc_html( $topic ?: 'General Enquiry' ); ?></td></tr>
	</table>
	<?php
}

/**
 * Save Meta Box Fields on Post Save.
 */
function mlt_save_admin_meta_boxes( int $post_id, WP_Post $post ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Save Homepage Site Settings when editing the Home page.
	if (
		'page' === $post->post_type &&
		isset( $_POST['mlt_site_settings_nonce'], $_POST['mlt_site_settings'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mlt_site_settings_nonce'] ) ), 'mlt_save_site_settings' ) &&
		is_array( $_POST['mlt_site_settings'] )
	) {
		mlt_save_site_settings_from_request( $_POST['mlt_site_settings'] );
	}

	// 1. Save Event Meta
	if (
		'mlt_event' === $post->post_type &&
		isset( $_POST['mlt_event_meta_nonce'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mlt_event_meta_nonce'] ) ), 'mlt_save_event_meta' )
	) {
		$raw_date = isset( $_POST['mlt_event_date'] ) ? sanitize_text_field( wp_unslash( $_POST['mlt_event_date'] ) ) : '';
		if ( '' !== $raw_date ) {
			$ts = strtotime( $raw_date );
			$iso_date = ( false !== $ts ) ? gmdate( 'Y-m-d\TH:i:00.000\Z', $ts ) : $raw_date;
			update_post_meta( $post_id, '_mlt_event_date', $iso_date );
		}
		if ( isset( $_POST['mlt_event_location'] ) ) {
			update_post_meta( $post_id, '_mlt_event_location', sanitize_text_field( wp_unslash( $_POST['mlt_event_location'] ) ) );
		}
		if ( isset( $_POST['mlt_event_category'] ) ) {
			$cat = sanitize_text_field( wp_unslash( $_POST['mlt_event_category'] ) );
			if ( '' === $cat || array_key_exists( $cat, mlt_get_event_category_labels() ) ) {
				update_post_meta( $post_id, '_mlt_event_category', $cat );
			}
		}
		$reg_open = isset( $_POST['mlt_event_registration_open'] ) ? '1' : '0';
		update_post_meta( $post_id, '_mlt_event_registration_open', $reg_open );

		if ( isset( $_POST['mlt_image_url'] ) ) {
			update_post_meta( $post_id, '_mlt_image_url', esc_url_raw( trim( wp_unslash( $_POST['mlt_image_url'] ) ) ) );
		}
	}

	// 2. Save Resource Meta
	if (
		'mlt_resource' === $post->post_type &&
		isset( $_POST['mlt_resource_meta_nonce'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mlt_resource_meta_nonce'] ) ), 'mlt_save_resource_meta' )
	) {
		if ( isset( $_POST['mlt_resource_type'] ) ) {
			$rt = sanitize_text_field( wp_unslash( $_POST['mlt_resource_type'] ) );
			if ( array_key_exists( $rt, mlt_get_resource_type_labels() ) ) {
				update_post_meta( $post_id, '_mlt_resource_type', $rt );
			}
		}
		$valid_topics = array_keys( mlt_get_topic_labels() );
		$chosen       = array();
		if ( ! empty( $_POST['mlt_resource_topics'] ) && is_array( $_POST['mlt_resource_topics'] ) ) {
			foreach ( $_POST['mlt_resource_topics'] as $t ) {
				$slug = sanitize_text_field( wp_unslash( $t ) );
				if ( in_array( $slug, $valid_topics, true ) ) {
					$chosen[] = $slug;
				}
			}
		}
		update_post_meta( $post_id, '_mlt_resource_topics', $chosen );
		wp_set_object_terms( $post_id, $chosen, 'mlt_topic', false );

		if ( isset( $_POST['mlt_video_url'] ) ) {
			update_post_meta( $post_id, '_mlt_video_url', esc_url_raw( trim( wp_unslash( $_POST['mlt_video_url'] ) ) ) );
		}
		if ( isset( $_POST['mlt_download_url'] ) ) {
			update_post_meta( $post_id, '_mlt_download_url', esc_url_raw( trim( wp_unslash( $_POST['mlt_download_url'] ) ) ) );
		}
	}

	// 3. Save Story Meta
	if (
		'mlt_story' === $post->post_type &&
		isset( $_POST['mlt_story_meta_nonce'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mlt_story_meta_nonce'] ) ), 'mlt_save_story_meta' )
	) {
		$name = isset( $_POST['mlt_story_name'] ) ? sanitize_text_field( trim( wp_unslash( $_POST['mlt_story_name'] ) ) ) : '';
		update_post_meta( $post_id, '_mlt_story_name', $name ?: 'Anonymous' );

		if ( isset( $_POST['mlt_story_age_or_category'] ) ) {
			update_post_meta( $post_id, '_mlt_story_age_or_category', sanitize_text_field( wp_unslash( $_POST['mlt_story_age_or_category'] ) ) );
		}

		$valid_topics = array_keys( mlt_get_topic_labels() );
		$chosen       = array();
		if ( ! empty( $_POST['mlt_story_topics'] ) && is_array( $_POST['mlt_story_topics'] ) ) {
			foreach ( $_POST['mlt_story_topics'] as $t ) {
				$slug = sanitize_text_field( wp_unslash( $t ) );
				if ( in_array( $slug, $valid_topics, true ) ) {
					$chosen[] = $slug;
				}
			}
		}
		update_post_meta( $post_id, '_mlt_story_topics', $chosen );
		wp_set_object_terms( $post_id, $chosen, 'mlt_topic', false );

		if ( isset( $_POST['mlt_video_url'] ) ) {
			update_post_meta( $post_id, '_mlt_video_url', esc_url_raw( trim( wp_unslash( $_POST['mlt_video_url'] ) ) ) );
		}

		$consent_given = isset( $_POST['mlt_consent_given'] ) ? '1' : '0';
		$proc_consent  = isset( $_POST['mlt_processing_consent_given'] ) ? '1' : '0';
		update_post_meta( $post_id, '_mlt_consent_given', $consent_given );
		update_post_meta( $post_id, '_mlt_processing_consent_given', $proc_consent );

		if ( ! get_post_meta( $post_id, '_mlt_consent_version', true ) ) {
			update_post_meta( $post_id, '_mlt_consent_version', '2026-04-17' );
		}
		if ( ! get_post_meta( $post_id, '_mlt_consented_at', true ) ) {
			update_post_meta( $post_id, '_mlt_consented_at', gmdate( 'c' ) );
		}
	}

	// 4. Save Community Group Meta
	if (
		'mlt_community_group' === $post->post_type &&
		isset( $_POST['mlt_community_group_meta_nonce'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mlt_community_group_meta_nonce'] ) ), 'mlt_save_community_group_meta' )
	) {
		if ( isset( $_POST['mlt_group_audience'] ) ) {
			$aud = sanitize_text_field( wp_unslash( $_POST['mlt_group_audience'] ) );
			if ( '' === $aud || array_key_exists( $aud, mlt_get_audience_labels() ) ) {
				update_post_meta( $post_id, '_mlt_group_audience', $aud );
			}
		}
	}

	// 5. Save Partner Meta
	if (
		'mlt_partner' === $post->post_type &&
		isset( $_POST['mlt_partner_meta_nonce'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mlt_partner_meta_nonce'] ) ), 'mlt_save_partner_meta' )
	) {
		if ( isset( $_POST['mlt_partnership_type'] ) ) {
			$pt = sanitize_text_field( wp_unslash( $_POST['mlt_partnership_type'] ) );
			if ( '' === $pt || array_key_exists( $pt, mlt_get_partnership_type_labels() ) ) {
				update_post_meta( $post_id, '_mlt_partnership_type', $pt );
			}
		}
		if ( isset( $_POST['mlt_website_url'] ) ) {
			update_post_meta( $post_id, '_mlt_website_url', esc_url_raw( trim( wp_unslash( $_POST['mlt_website_url'] ) ) ) );
		}
	}

	// 6. Save Book Meta
	if (
		'mlt_book' === $post->post_type &&
		isset( $_POST['mlt_book_meta_nonce'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mlt_book_meta_nonce'] ) ), 'mlt_save_book_meta' )
	) {
		if ( isset( $_POST['mlt_book_author'] ) ) {
			update_post_meta( $post_id, '_mlt_book_author', sanitize_text_field( wp_unslash( $_POST['mlt_book_author'] ) ) );
		}
		if ( isset( $_POST['mlt_book_why_written'] ) ) {
			update_post_meta( $post_id, '_mlt_book_why_written', sanitize_textarea_field( wp_unslash( $_POST['mlt_book_why_written'] ) ) );
		}
		if ( isset( $_POST['mlt_book_purchase_url'] ) ) {
			update_post_meta( $post_id, '_mlt_book_purchase_url', esc_url_raw( trim( wp_unslash( $_POST['mlt_book_purchase_url'] ) ) ) );
		}
		if ( isset( $_POST['mlt_book_testimonials_raw'] ) ) {
			$raw_lines    = explode( "\n", sanitize_textarea_field( wp_unslash( $_POST['mlt_book_testimonials_raw'] ) ) );
			$testimonials = array();
			foreach ( $raw_lines as $line ) {
				$line = trim( $line );
				if ( '' === $line ) {
					continue;
				}
				$parts       = explode( '|', $line, 2 );
				$quote       = trim( $parts[0] );
				$attribution = isset( $parts[1] ) ? trim( $parts[1] ) : '';
				if ( '' !== $quote ) {
					$testimonials[] = array(
						'quote'       => $quote,
						'attribution' => $attribution,
					);
				}
			}
			update_post_meta( $post_id, '_mlt_book_testimonials', $testimonials );
		}
	}

	// 7. Save Gallery Image Meta
	if (
		'mlt_gallery_image' === $post->post_type &&
		isset( $_POST['mlt_gallery_image_meta_nonce'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mlt_gallery_image_meta_nonce'] ) ), 'mlt_save_gallery_image_meta' )
	) {
		if ( isset( $_POST['mlt_gallery_album'] ) ) {
			$album_slug = sanitize_key( wp_unslash( $_POST['mlt_gallery_album'] ) );
			$albums     = function_exists( 'mlt_get_gallery_albums' ) ? mlt_get_gallery_albums() : array();
			if ( isset( $albums[ $album_slug ] ) ) {
				update_post_meta( $post_id, '_mlt_gallery_album', $album_slug );
			}
		}
		if ( isset( $_POST['mlt_gallery_caption'] ) ) {
			update_post_meta( $post_id, '_mlt_gallery_caption', sanitize_text_field( wp_unslash( $_POST['mlt_gallery_caption'] ) ) );
		}
		if ( isset( $_POST['mlt_image_url'] ) ) {
			update_post_meta( $post_id, '_mlt_image_url', esc_url_raw( trim( wp_unslash( $_POST['mlt_image_url'] ) ) ) );
		}
	}
}
add_action( 'save_post', 'mlt_save_admin_meta_boxes', 10, 2 );

/**
 * Custom Admin List-Table Columns for Events, Resources, Stories, Groups, Partners, Books, Gallery, and Submissions.
 */
function mlt_event_admin_columns( array $columns ): array {
	return array(
		'cb'           => $columns['cb'] ?? '<input type="checkbox" />',
		'title'        => 'Event Name',
		'event_date'   => 'Event Date',
		'location'     => 'Venue / Location',
		'category'     => 'Category',
		'registration' => 'Registration',
		'date'         => 'Published',
	);
}
add_filter( 'manage_mlt_event_posts_columns', 'mlt_event_admin_columns' );

function mlt_event_admin_column_content( string $column, int $post_id ): void {
	if ( 'event_date' === $column ) {
		$d = (string) get_post_meta( $post_id, '_mlt_event_date', true );
		$ts = $d ? strtotime( $d ) : false;
		echo esc_html( false !== $ts ? gmdate( 'j M Y, H:i', $ts ) : ( $d ?: '—' ) );
	} elseif ( 'location' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_mlt_event_location', true ) ?: '—' );
	} elseif ( 'category' === $column ) {
		$slug   = (string) get_post_meta( $post_id, '_mlt_event_category', true );
		$labels = mlt_get_event_category_labels();
		echo esc_html( $labels[ $slug ] ?? ( $slug ?: '—' ) );
	} elseif ( 'registration' === $column ) {
		$open = (bool) get_post_meta( $post_id, '_mlt_event_registration_open', true );
		echo $open ? '<span style="color:#15803d;font-weight:600;">Open</span>' : '<span style="color:#64748b;">Closed</span>';
	}
}
add_action( 'manage_mlt_event_posts_custom_column', 'mlt_event_admin_column_content', 10, 2 );

function mlt_resource_admin_columns( array $columns ): array {
	return array(
		'cb'            => $columns['cb'] ?? '<input type="checkbox" />',
		'title'         => 'Resource Title',
		'resource_type' => 'Format / Type',
		'topics'        => 'Topics',
		'date'          => 'Published',
	);
}
add_filter( 'manage_mlt_resource_posts_columns', 'mlt_resource_admin_columns' );

function mlt_resource_admin_column_content( string $column, int $post_id ): void {
	if ( 'resource_type' === $column ) {
		$slug   = (string) get_post_meta( $post_id, '_mlt_resource_type', true ) ?: 'article';
		$labels = mlt_get_resource_type_labels();
		echo esc_html( $labels[ $slug ] ?? $slug );
	} elseif ( 'topics' === $column ) {
		$topics = get_post_meta( $post_id, '_mlt_resource_topics', true );
		$labels = mlt_get_topic_labels();
		if ( is_array( $topics ) && ! empty( $topics ) ) {
			$names = array_map( fn( $s ) => $labels[ $s ] ?? $s, $topics );
			echo esc_html( implode( ', ', $names ) );
		} else {
			echo '—';
		}
	}
}
add_action( 'manage_mlt_resource_posts_custom_column', 'mlt_resource_admin_column_content', 10, 2 );

function mlt_story_admin_columns( array $columns ): array {
	return array(
		'cb'          => $columns['cb'] ?? '<input type="checkbox" />',
		'title'       => 'Story Title / Author',
		'age_cat'     => 'Age / Category',
		'topics'      => 'Topics',
		'dpa_consent' => 'Kenya DPA Consent',
		'date'        => 'Submitted',
	);
}
add_filter( 'manage_mlt_story_posts_columns', 'mlt_story_admin_columns' );

function mlt_story_admin_column_content( string $column, int $post_id ): void {
	if ( 'age_cat' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_mlt_story_age_or_category', true ) ?: '—' );
	} elseif ( 'topics' === $column ) {
		$topics = get_post_meta( $post_id, '_mlt_story_topics', true );
		$labels = mlt_get_topic_labels();
		if ( is_array( $topics ) && ! empty( $topics ) ) {
			$names = array_map( fn( $s ) => $labels[ $s ] ?? $s, $topics );
			echo esc_html( implode( ', ', $names ) );
		} else {
			echo '—';
		}
	} elseif ( 'dpa_consent' === $column ) {
		$consent = (string) get_post_meta( $post_id, '_mlt_consent_given', true );
		echo '1' === $consent
			? '<span style="color:#15803d;font-weight:600;">Consented</span>'
			: '<span style="color:#b91c1c;font-weight:600;">Withdrawn / Pending</span>';
	}
}
add_action( 'manage_mlt_story_posts_custom_column', 'mlt_story_admin_column_content', 10, 2 );

function mlt_registration_admin_columns( array $columns ): array {
	return array(
		'cb'       => $columns['cb'] ?? '<input type="checkbox" />',
		'title'    => 'Registration',
		'attendee' => 'Attendee Name',
		'email'    => 'Email',
		'phone'    => 'Phone',
		'event_id' => 'Event ID',
		'date'     => 'Submitted',
	);
}
add_filter( 'manage_mlt_registration_posts_columns', 'mlt_registration_admin_columns' );

function mlt_registration_admin_column_content( string $column, int $post_id ): void {
	if ( 'attendee' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_mlt_name', true ) ?: '—' );
	} elseif ( 'email' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_mlt_email', true ) ?: '—' );
	} elseif ( 'phone' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_mlt_phone', true ) ?: '—' );
	} elseif ( 'event_id' === $column ) {
		echo '<code>' . esc_html( (string) get_post_meta( $post_id, '_mlt_event_id', true ) ?: '—' ) . '</code>';
	}
}
add_action( 'manage_mlt_registration_posts_custom_column', 'mlt_registration_admin_column_content', 10, 2 );
