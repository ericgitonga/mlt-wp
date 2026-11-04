<?php
/**
 * CLI Sanity importer run via:
 *   wp eval-file local-preview/migrate-from-sanity.php
 */
if ( function_exists( 'mlt_sanity_import_run' ) ) {
	$res = mlt_sanity_import_run();
	echo 'Sanity import result: ' . wp_json_encode( $res ) . PHP_EOL;
}
