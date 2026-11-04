<?php
/**
 * Idempotent local seed script run by `local-preview/setup.sh` via `wp eval-file`.
 */
if ( function_exists( 'mlt_page_setup_run' ) ) {
	$res = mlt_page_setup_run();
	echo 'Provisioned pages: ' . wp_json_encode( $res ) . PHP_EOL;
}
