<?php
/**
 * Men Let's Talk Theme Functions & Shared UI Helpers.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mlt_wp_theme_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
}
add_action( 'after_setup_theme', 'mlt_wp_theme_setup' );

function mlt_wp_enqueue_assets(): void {
	$theme_uri = get_template_directory_uri();
	wp_enqueue_style(
		'mlt-wp-main',
		$theme_uri . '/assets/css/main.css',
		array(),
		'0.3.1'
	);
	wp_enqueue_script(
		'mlt-wp-main',
		$theme_uri . '/assets/js/main.js',
		array(),
		'0.3.1',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'mlt_wp_enqueue_assets' );

/**
 * Ensure WordPress always uses the Men Let's Talk emblem instead of the built-in
 * WordPress "W" fallback icon (w-logo-gray-white-bg.png) in search results.
 */
function mlt_wp_default_site_icon_url( $url, $size, $blog_id ): string {
	if ( empty( $url ) || false !== strpos( (string) $url, 'w-logo-' ) ) {
		return get_template_directory_uri() . '/assets/images/icon.png';
	}
	return (string) $url;
}
add_filter( 'get_site_icon_url', 'mlt_wp_default_site_icon_url', 10, 3 );

/**
 * Serve the theme's favicon.ico directly on /favicon.ico requests when no custom
 * DB site_icon is configured, avoiding WordPress's 302 redirect to w-logo-gray-white-bg.png.
 */
function mlt_wp_serve_favicon_ico(): void {
	if ( (int) get_option( 'site_icon' ) > 0 ) {
		return;
	}
	$ico_path = get_template_directory() . '/assets/images/favicon.ico';
	if ( file_exists( $ico_path ) ) {
		if ( ! headers_sent() ) {
			status_header( 200 );
			header( 'Content-Type: image/x-icon' );
			header( 'Content-Length: ' . (string) filesize( $ico_path ) );
			header( 'Cache-Control: public, max-age=86400' );
		}
		readfile( $ico_path );
		exit;
	}
	wp_safe_redirect( get_template_directory_uri() . '/assets/images/icon.png', 302 );
	exit;
}
add_action( 'do_faviconico', 'mlt_wp_serve_favicon_ico' );

/**
 * Output Organization + WebSite JSON-LD structured data and OpenGraph image tags
 * so search engines associate the Men Let's Talk emblem with the domain.
 */
function mlt_wp_output_brand_schema_and_meta(): void {
	$site_name = "Men Let's Talk";
	$site_url  = home_url( '/' );
	$icon_url  = get_template_directory_uri() . '/assets/images/icon.png';
	$schema    = array(
		'@context' => 'https://schema.org',
		'@graph'   => array(
			array(
				'@type' => 'Organization',
				'@id'   => $site_url . '#organization',
				'name'  => $site_name,
				'url'   => $site_url,
				'logo'  => array(
					'@type'      => 'ImageObject',
					'@id'        => $site_url . '#logo',
					'url'        => $icon_url,
					'contentUrl' => $icon_url,
					'width'      => 512,
					'height'     => 512,
					'caption'    => $site_name,
				),
				'image' => $icon_url,
			),
			array(
				'@type'     => 'WebSite',
				'@id'       => $site_url . '#website',
				'url'       => $site_url,
				'name'      => $site_name,
				'publisher' => array(
					'@id' => $site_url . '#organization',
				),
			),
		),
	);
	?>
	<meta property="og:site_name" content="<?php echo esc_attr( $site_name ); ?>" />
	<meta property="og:image" content="<?php echo esc_url( $icon_url ); ?>" />
	<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
	<?php
}
add_action( 'wp_head', 'mlt_wp_output_brand_schema_and_meta', 5 );

/**
 * Helper to return theme static image URL.
 */
function mlt_theme_image_url( string $filename ): string {
	return get_template_directory_uri() . '/assets/images/' . ltrim( $filename, '/' );
}

/**
 * Render Breadcrumb nav matching src/components/Breadcrumb.tsx.
 *
 * @param array<int, array{label: string, href?: string}> $items
 */
function mlt_render_breadcrumb( array $items, string $test_id = 'breadcrumb' ): void {
	?>
	<nav data-testid="<?php echo esc_attr( $test_id ); ?>" class="mb-4 flex flex-wrap items-center gap-1 text-sm text-zinc-500">
		<?php foreach ( $items as $i => $item ) : ?>
			<span class="flex items-center gap-1">
				<?php if ( $i > 0 ) : ?>
					<span class="text-zinc-300">/</span>
				<?php endif; ?>
				<?php if ( ! empty( $item['href'] ) ) : ?>
					<a
						href="<?php echo esc_url( $item['href'] ); ?>"
						<?php if ( '' !== $test_id ) : ?>
							data-testid="<?php echo esc_attr( $test_id . '-item' ); ?>"
						<?php endif; ?>
						class="hover:text-zinc-900 hover:underline"
					><?php echo esc_html( $item['label'] ); ?></a>
				<?php else : ?>
					<span class="font-medium text-zinc-900"><?php echo esc_html( $item['label'] ); ?></span>
				<?php endif; ?>
			</span>
		<?php endforeach; ?>
	</nav>
	<?php
}

/**
 * Render Event Registration toggle & form matching src/components/RegisterForm.tsx.
 */
function mlt_render_register_form( string $event_id, string $event_name ): void {
	?>
	<div class="mlt-register-widget" data-event-id="<?php echo esc_attr( $event_id ); ?>" data-event-name="<?php echo esc_attr( $event_name ); ?>">
		<button
			type="button"
			data-testid="register-open-button"
			class="mlt-register-open-btn mt-4 inline-block rounded-md bg-neutral-900 px-5 py-2 text-white"
		>Register</button>

		<form data-testid="register-form" class="mlt-register-form mt-4 space-y-3" style="display:none;">
			<input
				name="name"
				type="text"
				placeholder="Name"
				required
				class="w-full rounded-md border border-neutral-300 px-3 py-2"
			/>
			<input
				name="email"
				type="email"
				placeholder="Email"
				required
				class="w-full rounded-md border border-neutral-300 px-3 py-2"
			/>
			<input
				name="phone"
				type="tel"
				placeholder="Phone (optional)"
				class="w-full rounded-md border border-neutral-300 px-3 py-2"
			/>
			<label class="flex items-start gap-2 text-sm text-neutral-700">
				<input type="checkbox" name="consentGiven" required class="mt-1" />
				<span>
					I consent to Men Let&apos;s Talk collecting and storing this information to process my
					event registration, as described in our
					<a href="/privacy" class="underline">Privacy Notice</a>.
				</span>
			</label>
			<p data-testid="register-error" class="mlt-register-error text-sm text-red-600" style="display:none;"></p>
			<button
				type="submit"
				class="mlt-register-submit rounded-md bg-neutral-900 px-5 py-2 text-sm text-white disabled:opacity-60"
			>Confirm Registration</button>
		</form>

		<p data-testid="register-success" class="mlt-register-success mt-4 text-sm font-semibold text-neutral-700" style="display:none;">
			You&apos;re registered for <?php echo esc_html( $event_name ); ?>. See you there!
		</p>
	</div>
	<?php
}

/**
 * Render Stay Connected / Subscribe form matching src/components/SubscribeForm.tsx.
 */
function mlt_render_subscribe_form(): void {
	$interests = array(
		array( 'value' => 'events', 'label' => 'Upcoming events' ),
		array( 'value' => 'resources', 'label' => 'New resources' ),
		array( 'value' => 'conversations', 'label' => "Men's conversations" ),
		array( 'value' => 'newsletter', 'label' => 'Newsletters' ),
	);
	?>
	<div class="mlt-subscribe-widget">
		<form data-testid="subscribe-form" class="mlt-subscribe-form space-y-3">
			<input
				name="email"
				type="email"
				placeholder="Your email"
				required
				class="w-full rounded-md border border-neutral-300 px-3 py-2"
			/>
			<div class="flex flex-wrap gap-x-4 gap-y-2 text-sm text-neutral-600">
				<?php foreach ( $interests as $interest ) : ?>
					<label class="flex items-center gap-2">
						<input type="checkbox" name="<?php echo esc_attr( $interest['value'] ); ?>" />
						<?php echo esc_html( $interest['label'] ); ?>
					</label>
				<?php endforeach; ?>
			</div>
			<label class="flex items-start gap-2 text-sm text-neutral-600">
				<input type="checkbox" name="consentGiven" required class="mt-1" />
				<span>
					I consent to Men Let&apos;s Talk collecting and storing this information to keep me
					connected, as described in our <a href="/privacy" class="underline">Privacy Notice</a>.
				</span>
			</label>
			<p data-testid="subscribe-error" class="mlt-subscribe-error text-sm text-red-600" style="display:none;"></p>
			<button
				type="submit"
				class="mlt-subscribe-submit rounded-md bg-neutral-900 px-5 py-2 text-sm font-semibold text-white disabled:opacity-60"
			>Subscribe</button>
		</form>
		<p data-testid="subscribe-success" class="mlt-subscribe-success text-sm font-semibold text-neutral-700" style="display:none;">
			You&apos;re on the list — thanks for staying connected.
		</p>
	</div>
	<?php
}

/**
 * Ensure all 14 route slugs resolve to their matching page-<slug>.php template with HTTP 200
 * even before WP pages are manually created.
 */
function mlt_route_template_fallback( string $template ): string {
	$path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH ), '/' );
	if ( '' === $path ) {
		$front = get_template_directory() . '/front-page.php';
		if ( file_exists( $front ) ) {
			status_header( 200 );
			return $front;
		}
	}

	$slugs = array(
		'about',
		'talk',
		'events',
		'resources',
		'stories',
		'share-your-story',
		'community',
		'get-involved',
		'partners',
		'books',
		'gallery',
		'contact',
		'privacy',
		'search',
	);

	if ( in_array( $path, $slugs, true ) ) {
		$candidate = get_template_directory() . '/page-' . $path . '.php';
		if ( file_exists( $candidate ) ) {
			global $wp_query;
			if ( isset( $wp_query ) ) {
				$wp_query->is_404  = false;
				$wp_query->is_page = true;
			}
			status_header( 200 );
			return $candidate;
		}
	}

	return $template;
}
add_filter( 'template_include', 'mlt_route_template_fallback', 99 );


// Disable canonical trailing-slash redirects so routes (/privacy, /gallery, /resources?topic=...)
// resolve at their exact Next.js paths without 301-redirecting to a trailing slash.
remove_filter( 'template_redirect', 'redirect_canonical' );
add_filter( 'redirect_canonical', '__return_false' );
remove_action( 'init', 'wp_cron' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
