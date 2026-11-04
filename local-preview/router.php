<?php
/**
 * Router for PHP's built-in server (`php -S`), so static asset requests
 * (images, CSS, JS, woff2) are served directly and everything else routes
 * to WordPress's index.php.
 */
$path = urldecode( (string) parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
$file = __DIR__ . $path;

if ( '/' !== $path && file_exists( $file ) && ! is_dir( $file ) ) {
	return false;
}

chdir( __DIR__ );
require __DIR__ . '/index.php';
