<?php
/**
 * Site Header matching src/components/Header.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$nav_links = array(
	array( 'href' => '/', 'label' => 'Home' ),
	array( 'href' => '/about', 'label' => 'About' ),
	array( 'href' => '/talk', 'label' => "Let's Talk" ),
	array( 'href' => '/events', 'label' => 'Events' ),
	array( 'href' => '/community', 'label' => 'Community' ),
	array( 'href' => '/resources', 'label' => 'Resources' ),
	array( 'href' => '/stories', 'label' => 'Stories' ),
	array( 'href' => '/gallery', 'label' => 'Gallery' ),
	array( 'href' => '/get-involved', 'label' => 'Get Involved' ),
);
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="h-full antialiased">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/favicon.ico' ); ?>" sizes="48x48" type="image/x-icon" />
	<link rel="icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icon.png' ); ?>" sizes="512x512" type="image/png" />
	<link rel="apple-touch-icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/icon.png' ); ?>" sizes="180x180" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'min-h-full flex flex-col bg-white' ); ?>>
<?php wp_body_open(); ?>
<header
	id="mlt-site-header"
	data-testid="site-header"
	class="sticky top-0 z-50 border-b border-neutral-200 bg-white"
>
	<div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
		<a href="/" class="text-lg font-bold tracking-tight">Men Let&apos;s Talk</a>

		<!-- Desktop nav -->
		<nav data-testid="desktop-nav" class="hidden items-center gap-6 md:flex">
			<?php foreach ( $nav_links as $link ) : ?>
				<a href="<?php echo esc_url( $link['href'] ); ?>" class="text-sm font-medium hover:underline"><?php echo esc_html( $link['label'] ); ?></a>
			<?php endforeach; ?>
			<a href="/search" aria-label="Search" data-testid="search-link" class="text-neutral-600 hover:text-neutral-900">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5">
					<circle cx="11" cy="11" r="7" />
					<path d="m21 21-4.3-4.3" />
				</svg>
			</a>
			<a
				href="/get-involved"
				class="rounded-md bg-neutral-900 px-4 py-2 text-sm font-semibold text-white"
			>Join the Conversation</a>
		</nav>

		<!-- Mobile hamburger -->
		<button
			type="button"
			aria-label="Open menu"
			aria-expanded="false"
			data-testid="mobile-menu-toggle"
			class="flex h-11 w-11 flex-col items-center justify-center gap-1.5 md:hidden"
		>
			<span class="h-0.5 w-6 bg-neutral-900"></span>
			<span class="h-0.5 w-6 bg-neutral-900"></span>
			<span class="h-0.5 w-6 bg-neutral-900"></span>
		</button>
	</div>

	<!-- Mobile menu (hidden until hamburger toggle clicked) -->
	<nav
		data-testid="mobile-nav"
		class="flex flex-col gap-1 border-t border-neutral-200 px-6 py-4 md:hidden"
		style="display: none;"
	>
		<?php foreach ( $nav_links as $link ) : ?>
			<a
				href="<?php echo esc_url( $link['href'] ); ?>"
				class="block py-3 text-sm font-medium"
			><?php echo esc_html( $link['label'] ); ?></a>
		<?php endforeach; ?>
		<a href="/search" class="block py-3 text-sm font-medium">Search</a>
		<a
			href="/get-involved"
			class="mt-2 block rounded-md bg-neutral-900 px-4 py-3 text-center text-sm font-semibold text-white"
		>Join the Conversation</a>
	</nav>
</header>
<div class="flex-1">
