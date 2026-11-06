<?php
/**
 * Plugin Name: Men Let's Talk Content & Setup
 * Description: Custom post types, taxonomies, Sanity migration importer, 7-layer anti-spam engine, JSON API endpoints, and one-click page setup for Men Let's Talk.
 * Version:     0.3.2
 * Author:      Men Let's Talk
 * Text Domain: mlt-wp-content
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MLT_WP_CONTENT_VERSION', '0.3.2' );
define( 'MLT_WP_CONTENT_DIR', plugin_dir_path( __FILE__ ) );
define( 'MLT_WP_CONTENT_URL', plugin_dir_url( __FILE__ ) );

require_once MLT_WP_CONTENT_DIR . 'includes/anti-spam.php';
require_once MLT_WP_CONTENT_DIR . 'includes/api-routes.php';
require_once MLT_WP_CONTENT_DIR . 'includes/page-setup.php';
require_once MLT_WP_CONTENT_DIR . 'includes/sanity-import.php';

/**
 * Contact channel constants matching src/lib/contactInfo.ts.
 */
function mlt_get_contact_info(): array {
	$whatsapp_number  = '+254727246599';
	$instagram_handle = 'menletstalk254';
	$email            = 'info@menletstalk.co.ke';

	return array(
		'whatsapp_number'  => $whatsapp_number,
		'whatsapp_url'     => 'https://wa.me/' . str_replace( '+', '', $whatsapp_number ),
		'instagram_handle' => $instagram_handle,
		'instagram_url'    => 'https://instagram.com/' . $instagram_handle,
		'email'            => $email,
		'email_url'        => 'mailto:' . $email,
	);
}

/**
 * Taxonomy & Enum Label Maps matching src/sanity/lib/queries.ts.
 */
function mlt_get_event_category_labels(): array {
	return array(
		'mens-breakfast'       => "Men's Breakfast",
		'mens-conversations'   => "Men's Conversations",
		'father-son'           => 'Father & Son',
		'father-daughter'      => 'Father & Daughter',
		'mens-dinner'          => "Men's Dinner",
		'retreats'             => 'Retreats',
		'special-forums'       => 'Special Forums',
		'campus-conversations' => 'Campus Conversations',
		'mentorship-events'    => 'Mentorship Events',
	);
}

function mlt_get_resource_type_labels(): array {
	return array(
		'article'              => 'Article',
		'video'                => 'Video',
		'podcast'              => 'Podcast',
		'discussion-guide'     => 'Discussion Guide',
		'devotional'           => 'Devotional',
		'conversation-starter' => 'Conversation Starter',
		'book'                 => 'Book',
		'recommended-resource' => 'Recommended Resource',
	);
}

function mlt_get_topic_labels(): array {
	return array(
		'pressure'       => 'Pressure',
		'failure'        => 'Failure',
		'marriage'       => 'Marriage',
		'fatherhood'     => 'Fatherhood',
		'money'          => 'Money',
		'purpose'        => 'Purpose',
		'loneliness'     => 'Loneliness',
		'grief'          => 'Grief',
		'relationships'  => 'Relationships',
		'mental-health'  => 'Mental Health',
		'masculinity'    => 'Masculinity',
		'faith'          => 'Faith',
		'starting-again' => 'Starting Again',
	);
}

function mlt_get_audience_labels(): array {
	return array(
		'mentoring' => 'Older Men Mentoring the Young',
		'families'  => 'Men with Children & Families',
		'campus'    => 'Men in Campus',
	);
}

function mlt_get_partnership_type_labels(): array {
	return array(
		'corporate-wellness'        => "Corporate Men's Wellness",
		'event-sponsorship'         => 'Event Sponsorship',
		'community-initiatives'     => 'Community Initiatives',
		'mental-health-initiatives' => 'Mental-Health Initiatives',
		'media-partnerships'        => 'Media Partnerships',
		'church-partnerships'       => 'Church Partnerships',
	);
}

/**
 * Register Custom Post Types and Taxonomies.
 */
function mlt_register_content_types(): void {
	register_post_type(
		'mlt_event',
		array(
			'labels'       => array(
				'name'          => 'Events',
				'singular_name' => 'Event',
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-calendar-alt',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		)
	);

	register_post_type(
		'mlt_resource',
		array(
			'labels'       => array(
				'name'          => 'Resources',
				'singular_name' => 'Resource',
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-media-document',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		)
	);

	register_post_type(
		'mlt_story',
		array(
			'labels'       => array(
				'name'          => 'Stories',
				'singular_name' => 'Story',
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-format-quote',
			'supports'     => array( 'title', 'editor', 'custom-fields' ),
		)
	);

	register_post_type(
		'mlt_community_group',
		array(
			'labels'       => array(
				'name'          => 'Community Groups',
				'singular_name' => 'Community Group',
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-groups',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		)
	);

	register_post_type(
		'mlt_partner',
		array(
			'labels'       => array(
				'name'          => 'Partners',
				'singular_name' => 'Partner',
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-networking',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		)
	);

	register_post_type(
		'mlt_book',
		array(
			'labels'       => array(
				'name'          => 'Books & Projects',
				'singular_name' => 'Book / Project',
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-book-alt',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		)
	);

	register_post_type(
		'mlt_gallery_image',
		array(
			'labels'       => array(
				'name'          => 'Gallery Photos',
				'singular_name' => 'Gallery Photo',
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-format-gallery',
			'supports'     => array( 'title', 'thumbnail', 'custom-fields' ),
		)
	);

	register_post_type(
		'mlt_registration',
		array(
			'labels'       => array(
				'name'          => 'Registrations',
				'singular_name' => 'Registration',
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'menu_icon'    => 'dashicons-tickets-alt',
			'supports'     => array( 'title', 'custom-fields' ),
		)
	);

	register_post_type(
		'mlt_subscriber',
		array(
			'labels'       => array(
				'name'          => 'Subscribers',
				'singular_name' => 'Subscriber',
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'menu_icon'    => 'dashicons-email-alt',
			'supports'     => array( 'title', 'custom-fields' ),
		)
	);

	register_post_type(
		'mlt_submission',
		array(
			'labels'       => array(
				'name'          => 'Contact Enquiries',
				'singular_name' => 'Contact Enquiry',
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'menu_icon'    => 'dashicons-email',
			'supports'     => array( 'title', 'editor', 'custom-fields' ),
		)
	);

	register_taxonomy(
		'mlt_topic',
		array( 'mlt_resource', 'mlt_story' ),
		array(
			'labels'       => array(
				'name'          => 'Topics',
				'singular_name' => 'Topic',
			),
			'public'       => true,
			'hierarchical' => false,
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'mlt_register_content_types' );

/**
 * Configure PHPMailer with Angry Hosting SMTP when MLT_SMTP_* constants are defined.
 */
function mlt_configure_smtp( $phpmailer ): void {
	$smtp_user = defined( 'MLT_SMTP_USER' ) ? MLT_SMTP_USER : ( getenv( 'SMTP_USER' ) ?: '' );
	$smtp_pass = defined( 'MLT_SMTP_PASS' ) ? MLT_SMTP_PASS : ( getenv( 'SMTP_PASS' ) ?: '' );
	if ( empty( $smtp_user ) || empty( $smtp_pass ) ) {
		return;
	}

	$smtp_host = defined( 'MLT_SMTP_HOST' ) ? MLT_SMTP_HOST : ( getenv( 'SMTP_HOST' ) ?: 'mx1.angryhosting.com' );
	$smtp_port = (int) ( defined( 'MLT_SMTP_PORT' ) ? MLT_SMTP_PORT : ( getenv( 'SMTP_PORT' ) ?: 465 ) );

	$phpmailer->isSMTP();
	$phpmailer->Host       = $smtp_host;
	$phpmailer->SMTPAuth   = true;
	$phpmailer->Port       = $smtp_port;
	$phpmailer->Username   = $smtp_user;
	$phpmailer->Password   = $smtp_pass;
	$phpmailer->SMTPSecure = ( 465 === $smtp_port ) ? 'ssl' : 'tls';
	$phpmailer->From       = $smtp_user;
	$phpmailer->FromName   = "Men Let's Talk";
}
add_action( 'phpmailer_init', 'mlt_configure_smtp' );

/**
 * Default & stored Site Settings (matches Sanity siteSettings singleton & fallbacks).
 */
function mlt_get_default_site_settings(): array {
	return array(
		'heroTitle'             => 'No man should walk alone.',
		'heroSupportingMessage' => 'Talk. Listen. Heal. Grow. Lead.',
		'heroDescription'       => "Men Let's Talk creates safe spaces where men can be honest, heard, supported and equipped to navigate life together.",
		'heroImageUrl'          => '',
		'pillars'               => array(
			array(
				'name'        => 'Talk',
				'description' => 'Create safe spaces for honest conversations.',
			),
			array(
				'name'        => 'Connect',
				'description' => 'Build meaningful relationships and community.',
			),
			array(
				'name'        => 'Grow',
				'description' => 'Equip men to become healthier men, fathers, husbands, leaders and friends.',
			),
			array(
				'name'        => 'Lead',
				'description' => 'Raise men who positively influence their families and communities.',
			),
		),
		'featuredTopics'        => array(
			'pressure',
			'failure',
			'marriage',
			'fatherhood',
			'money',
			'purpose',
			'loneliness',
			'grief',
			'relationships',
			'mental-health',
			'faith',
			'starting-again',
		),
		'communityFeatures'     => array(
			array(
				'name'        => 'No Man Walks Alone',
				'description' => "A space where men support one another through life's different seasons.",
			),
			array(
				'name'        => 'Older Men Mentoring the Young',
				'description' => 'Creating intentional relationships between older and younger men.',
			),
			array(
				'name'        => 'Men with Children & Families',
				'description' => 'Conversations around fatherhood, marriage and family.',
			),
			array(
				'name'        => 'Men in Campus',
				'description' => 'Helping young men navigate identity, relationships, purpose and life.',
			),
		),
		'values'                => array(
			array(
				'name'        => 'Honesty',
				'description' => 'We tell the truth about our journeys.',
			),
			array(
				'name'        => 'Community',
				'description' => 'No man should walk alone.',
			),
			array(
				'name'        => 'Growth',
				'description' => 'We believe men can change and become better.',
			),
			array(
				'name'        => 'Responsibility',
				'description' => 'We take ownership of our lives and influence.',
			),
			array(
				'name'        => 'Legacy',
				'description' => 'We live beyond ourselves.',
			),
			array(
				'name'        => 'Faith',
				'description' => "Where appropriate, we recognise the role of faith and spiritual formation in a man's journey.",
			),
		),
	);
}

function mlt_get_site_settings(): array {
	$defaults = mlt_get_default_site_settings();
	$saved    = get_option( 'mlt_site_settings', array() );
	if ( ! is_array( $saved ) ) {
		return $defaults;
	}

	return array(
		'heroTitle'             => ! empty( $saved['heroTitle'] ) ? (string) $saved['heroTitle'] : $defaults['heroTitle'],
		'heroSupportingMessage' => ! empty( $saved['heroSupportingMessage'] ) ? (string) $saved['heroSupportingMessage'] : $defaults['heroSupportingMessage'],
		'heroDescription'       => ! empty( $saved['heroDescription'] ) ? (string) $saved['heroDescription'] : $defaults['heroDescription'],
		'heroImageUrl'          => ! empty( $saved['heroImageUrl'] ) ? (string) $saved['heroImageUrl'] : $defaults['heroImageUrl'],
		'pillars'               => ! empty( $saved['pillars'] ) && is_array( $saved['pillars'] ) ? $saved['pillars'] : $defaults['pillars'],
		'featuredTopics'        => ! empty( $saved['featuredTopics'] ) && is_array( $saved['featuredTopics'] ) ? $saved['featuredTopics'] : $defaults['featuredTopics'],
		'communityFeatures'     => ! empty( $saved['communityFeatures'] ) && is_array( $saved['communityFeatures'] ) ? $saved['communityFeatures'] : $defaults['communityFeatures'],
		'values'                => ! empty( $saved['values'] ) && is_array( $saved['values'] ) ? $saved['values'] : $defaults['values'],
	);
}

/**
 * Default bundled events (mirrors src/lib/defaultMedia.ts in men-lets-talk).
 */
function mlt_get_default_events(): array {
	$events_base = get_template_directory_uri() . '/assets/images/events/';
	return array(
		array(
			'id'               => 'event-mens-garage-2026-11-08',
			'wp_id'            => 0,
			'name'             => "Men's Garage: What's Under the Hood?",
			'slug'             => 'mens-garage-whats-under-the-hood',
			'date'             => '2026-11-08T10:00:00.000Z',
			'location'         => 'Junction Mall',
			'category'         => 'special-forums',
			'description'      => "The Junction (All About Men) x Men Let's Talk x JimmiGathu Connects presents Men's Garage: What's Under the Hood? Real conversations. Beyond the surface. Tickets KES 1,000 (KES 500 redeemable). Men-only space till 6 PM; free arcade games, F&B offers & Man Utd vs Aston Villa at 19:30.",
			'image_url'        => $events_base . 'mens-garage-flier.jpg',
			'registrationOpen' => true,
		),
		array(
			'id'               => 'event-son-and-father-dinner-2026-12-12',
			'wp_id'            => 0,
			'name'             => 'Son & Father Dinner',
			'slug'             => 'son-and-father-dinner',
			'date'             => '2026-12-12T15:00:00.000Z',
			'location'         => 'Heri Heights Hotel, Kolobot Drive',
			'category'         => 'father-son',
			'description'      => '"Sons take your fathers out for dinner." An intentional evening of conversation, connection and honour for fathers and sons at Heri Heights Hotel, Kolobot Drive. KES 2,500 per person — limited slots.',
			'image_url'        => $events_base . 'son-and-father-dinner-flier.jpg',
			'registrationOpen' => true,
		),
	);
}

/**
 * Default bundled gallery photos (mirrors src/lib/defaultMedia.ts in men-lets-talk).
 */
function mlt_get_default_gallery_images(): array {
	$base_url = get_template_directory_uri() . '/assets/images/gallery/';
	$nums     = array(
		'01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '13',
		'18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29',
	);
	$out      = array();
	foreach ( $nums as $num ) {
		$out[] = array(
			'id'        => 'gallery-image-' . $num,
			'image_url' => $base_url . 'gallery-' . $num . '.jpg',
			'caption'   => '',
		);
	}
	return $out;
}

/**
 * Query upcoming events (date >= now, ordered soonest first).
 */
function mlt_get_upcoming_events( int $limit = -1 ): array {
	$posts = get_posts(
		array(
			'post_type'      => 'mlt_event',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'meta_value',
			'meta_key'       => '_mlt_event_date',
			'order'          => 'ASC',
		)
	);

	$now_ts = time();
	$events = array();

	foreach ( $posts as $post ) {
		$date_str = (string) get_post_meta( $post->ID, '_mlt_event_date', true );
		$date_ts  = $date_str ? strtotime( $date_str ) : false;
		if ( false !== $date_ts && $date_ts < $now_ts ) {
			continue;
		}
		$sanity_id = (string) get_post_meta( $post->ID, '_mlt_sanity_id', true );
		$img_url   = get_the_post_thumbnail_url( $post->ID, 'full' ) ?: (string) get_post_meta( $post->ID, '_mlt_image_url', true );
		$events[]  = array(
			'id'               => $sanity_id ? $sanity_id : 'event-' . $post->ID,
			'wp_id'            => $post->ID,
			'name'             => get_the_title( $post ),
			'slug'             => $post->post_name,
			'date'             => $date_str ?: $post->post_date,
			'location'         => (string) get_post_meta( $post->ID, '_mlt_event_location', true ),
			'category'         => (string) get_post_meta( $post->ID, '_mlt_event_category', true ),
			'description'      => wp_strip_all_tags( $post->post_content ),
			'image_url'        => $img_url,
			'registrationOpen' => (bool) get_post_meta( $post->ID, '_mlt_event_registration_open', true ),
		);
	}

	if ( empty( $events ) ) {
		foreach ( mlt_get_default_events() as $def_ev ) {
			$def_ts = strtotime( $def_ev['date'] );
			if ( false === $def_ts || $def_ts >= $now_ts ) {
				$events[] = $def_ev;
			}
		}
	}

	if ( $limit > 0 ) {
		return array_slice( $events, 0, $limit );
	}
	return $events;
}

/**
 * Query resources/articles, optionally filtered by topic slug.
 */
function mlt_get_resources( string $topic = '' ): array {
	$posts = get_posts(
		array(
			'post_type'      => 'mlt_resource',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	$resources = array();
	foreach ( $posts as $post ) {
		$topics = get_post_meta( $post->ID, '_mlt_resource_topics', true );
		if ( ! is_array( $topics ) ) {
			$topics = array();
		}
		if ( '' !== $topic && ! in_array( $topic, $topics, true ) ) {
			continue;
		}
		$sanity_id   = (string) get_post_meta( $post->ID, '_mlt_sanity_id', true );
		$resources[] = array(
			'id'            => $sanity_id ? $sanity_id : 'resource-' . $post->ID,
			'wp_id'         => $post->ID,
			'title'         => get_the_title( $post ),
			'slug'          => $post->post_name,
			'resourceType'  => (string) get_post_meta( $post->ID, '_mlt_resource_type', true ) ?: 'article',
			'topics'        => $topics,
			'featuredImage' => get_the_post_thumbnail_url( $post->ID, 'large' ) ?: '',
			'videoUrl'      => (string) get_post_meta( $post->ID, '_mlt_video_url', true ),
			'downloadUrl'   => (string) get_post_meta( $post->ID, '_mlt_download_url', true ),
			'body'          => $post->post_content,
		);
	}

	return $resources;
}

/**
 * Query published stories where consentGiven == true.
 */
function mlt_get_stories( int $limit = -1 ): array {
	$posts = get_posts(
		array(
			'post_type'      => 'mlt_story',
			'post_status'    => 'publish',
			'posts_per_page' => $limit > 0 ? $limit : -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'   => '_mlt_consent_given',
					'value' => '1',
				),
			),
		)
	);

	$stories = array();
	foreach ( $posts as $post ) {
		$topics = get_post_meta( $post->ID, '_mlt_story_topics', true );
		if ( ! is_array( $topics ) ) {
			$topics = array();
		}
		$sanity_id = (string) get_post_meta( $post->ID, '_mlt_sanity_id', true );
		$name      = (string) get_post_meta( $post->ID, '_mlt_story_name', true );
		$stories[] = array(
			'id'            => $sanity_id ? $sanity_id : 'story-' . $post->ID,
			'wp_id'         => $post->ID,
			'name'          => $name ?: 'Anonymous',
			'ageOrCategory' => (string) get_post_meta( $post->ID, '_mlt_story_age_or_category', true ),
			'topics'        => $topics,
			'body'          => $post->post_content,
			'excerpt'       => wp_strip_all_tags( $post->post_content ),
			'videoUrl'      => (string) get_post_meta( $post->ID, '_mlt_video_url', true ),
		);
	}

	return $stories;
}

/**
 * Query community groups.
 */
function mlt_get_community_groups(): array {
	$posts = get_posts(
		array(
			'post_type'      => 'mlt_community_group',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	$groups = array();
	foreach ( $posts as $post ) {
		$sanity_id = (string) get_post_meta( $post->ID, '_mlt_sanity_id', true );
		$groups[]  = array(
			'id'          => $sanity_id ? $sanity_id : 'group-' . $post->ID,
			'name'        => get_the_title( $post ),
			'description' => wp_strip_all_tags( $post->post_content ),
			'audience'    => (string) get_post_meta( $post->ID, '_mlt_group_audience', true ),
			'image_url'   => get_the_post_thumbnail_url( $post->ID, 'large' ) ?: '',
		);
	}

	return $groups;
}

/**
 * Query partners.
 */
function mlt_get_partners(): array {
	$posts = get_posts(
		array(
			'post_type'      => 'mlt_partner',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	$partners = array();
	foreach ( $posts as $post ) {
		$sanity_id  = (string) get_post_meta( $post->ID, '_mlt_sanity_id', true );
		$partners[] = array(
			'id'              => $sanity_id ? $sanity_id : 'partner-' . $post->ID,
			'name'            => get_the_title( $post ),
			'description'     => wp_strip_all_tags( $post->post_content ),
			'partnershipType' => (string) get_post_meta( $post->ID, '_mlt_partnership_type', true ),
			'websiteUrl'      => (string) get_post_meta( $post->ID, '_mlt_website_url', true ),
			'logo_url'        => get_the_post_thumbnail_url( $post->ID, 'medium' ) ?: '',
		);
	}

	return $partners;
}

/**
 * Query books & projects.
 */
function mlt_get_books(): array {
	$posts = get_posts(
		array(
			'post_type'      => 'mlt_book',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	$books = array();
	foreach ( $posts as $post ) {
		$sanity_id    = (string) get_post_meta( $post->ID, '_mlt_sanity_id', true );
		$testimonials = get_post_meta( $post->ID, '_mlt_book_testimonials', true );
		$books[]      = array(
			'id'           => $sanity_id ? $sanity_id : 'book-' . $post->ID,
			'title'        => get_the_title( $post ),
			'author'       => (string) get_post_meta( $post->ID, '_mlt_book_author', true ),
			'description'  => wp_strip_all_tags( $post->post_content ),
			'whyWritten'   => (string) get_post_meta( $post->ID, '_mlt_book_why_written', true ),
			'purchaseUrl'  => (string) get_post_meta( $post->ID, '_mlt_book_purchase_url', true ),
			'cover_url'    => get_the_post_thumbnail_url( $post->ID, 'large' ) ?: '',
			'testimonials' => is_array( $testimonials ) ? $testimonials : array(),
		);
	}

	return $books;
}

/**
 * Query gallery photos.
 */
function mlt_get_gallery_images(): array {
	$posts = get_posts(
		array(
			'post_type'      => 'mlt_gallery_image',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);

	$withdrawn = array(
		'gallery-image-12',
		'gallery-image-14',
		'gallery-image-15',
		'gallery-image-16',
		'gallery-image-17',
	);
	$seen_ids  = array();
	$seen_urls = array();
	$images    = array();
	foreach ( $posts as $post ) {
		$sanity_id = (string) get_post_meta( $post->ID, '_mlt_sanity_id', true );
		if ( in_array( $sanity_id, $withdrawn, true ) ) {
			continue;
		}
		$img_url = get_the_post_thumbnail_url( $post->ID, 'large' ) ?: (string) get_post_meta( $post->ID, '_mlt_image_url', true );
		if ( ! $img_url ) {
			continue;
		}
		$id = $sanity_id ? $sanity_id : 'gallery-' . $post->ID;
		if ( isset( $seen_ids[ $id ] ) || isset( $seen_urls[ $img_url ] ) ) {
			continue;
		}
		$seen_ids[ $id ]       = true;
		$seen_urls[ $img_url ] = true;
		$images[]              = array(
			'id'        => $id,
			'image_url' => $img_url,
			'caption'   => (string) get_post_meta( $post->ID, '_mlt_gallery_caption', true ),
		);
	}

	foreach ( mlt_get_default_gallery_images() as $default_img ) {
		$def_id  = $default_img['id'];
		$def_url = $default_img['image_url'];
		if ( isset( $seen_ids[ $def_id ] ) || isset( $seen_urls[ $def_url ] ) ) {
			continue;
		}
		$seen_ids[ $def_id ]   = true;
		$seen_urls[ $def_url ] = true;
		$images[]              = $default_img;
	}

	return $images;
}

/**
 * Search across events, resources, and consented stories.
 */
function mlt_search_content( string $term ): array {
	$term = trim( $term );
	if ( '' === $term ) {
		return array(
			'articles' => array(),
			'events'   => array(),
			'stories'  => array(),
		);
	}

	$needle   = mb_strtolower( $term );
	$articles = array();
	foreach ( mlt_get_resources() as $res ) {
		$haystack = mb_strtolower( $res['title'] . ' ' . wp_strip_all_tags( $res['body'] ) );
		if ( false !== mb_strpos( $haystack, $needle ) ) {
			$articles[] = $res;
		}
	}

	$events = array();
	foreach ( mlt_get_upcoming_events() as $ev ) {
		$haystack = mb_strtolower( $ev['name'] . ' ' . $ev['description'] . ' ' . $ev['location'] );
		if ( false !== mb_strpos( $haystack, $needle ) ) {
			$events[] = $ev;
		}
	}

	$stories = array();
	foreach ( mlt_get_stories() as $st ) {
		$haystack = mb_strtolower( $st['name'] . ' ' . $st['excerpt'] );
		if ( false !== mb_strpos( $haystack, $needle ) ) {
			$stories[] = $st;
		}
	}

	return array(
		'articles' => array_slice( $articles, 0, 20 ),
		'events'   => array_slice( $events, 0, 20 ),
		'stories'  => array_slice( $stories, 0, 20 ),
	);
}

/**
 * Truncate helper matching src/lib/text.ts.
 */
function mlt_truncate_text( string $text, int $max = 160 ): string {
	$trimmed = trim( $text );
	if ( mb_strlen( $trimmed ) <= $max ) {
		return $trimmed;
	}
	return rtrim( mb_substr( $trimmed, 0, $max - 1 ) ) . '…';
}

/**
 * Video embed URL parser matching src/components/VideoEmbed.tsx.
 */
function mlt_get_embed_url( string $url ): ?string {
	$parsed = wp_parse_url( $url );
	if ( empty( $parsed['host'] ) ) {
		return null;
	}
	$host = preg_replace( '/^www\./i', '', strtolower( $parsed['host'] ) );
	$path = $parsed['path'] ?? '';

	if ( 'youtube.com' === $host || 'm.youtube.com' === $host ) {
		if ( ! empty( $parsed['query'] ) ) {
			parse_str( $parsed['query'], $query_vars );
			if ( ! empty( $query_vars['v'] ) ) {
				return 'https://www.youtube.com/embed/' . rawurlencode( (string) $query_vars['v'] );
			}
		}
		if ( preg_match( '#^/embed/([^/]+)#', $path, $m ) ) {
			return 'https://www.youtube.com/embed/' . rawurlencode( $m[1] );
		}
		return null;
	}

	if ( 'youtu.be' === $host ) {
		$id = ltrim( $path, '/' );
		return '' !== $id ? 'https://www.youtube.com/embed/' . rawurlencode( $id ) : null;
	}

	if ( 'vimeo.com' === $host ) {
		$id = ltrim( $path, '/' );
		return preg_match( '/^\d+$/', $id ) ? 'https://player.vimeo.com/video/' . $id : null;
	}

	return null;
}

function mlt_render_video_embed( string $url, string $title ): string {
	$embed_url = mlt_get_embed_url( $url );
	if ( ! $embed_url ) {
		return sprintf(
			'<a href="%s" data-testid="video-fallback-link" class="mt-3 inline-block underline">Watch / Listen</a>',
			esc_url( $url )
		);
	}

	return sprintf(
		'<div data-testid="video-embed" class="relative mt-3 aspect-video w-full overflow-hidden rounded-lg"><iframe src="%s" title="%s" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen class="absolute inset-0 h-full w-full"></iframe></div>',
		esc_url( $embed_url ),
		esc_attr( $title )
	);
}
