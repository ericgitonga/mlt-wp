<?php
/**
 * Idempotent Sanity Dataset Importer for Men Let's Talk (`9ks0sgl2` / `production`).
 *
 * Imports:
 *   - `siteSettings` singleton -> `mlt_site_settings` option (and hero image URL)
 *   - `event` -> `mlt_event` CPT
 *   - `article` -> `mlt_resource` CPT
 *   - `story` -> `mlt_story` CPT
 *   - `communityGroup` -> `mlt_community_group` CPT
 *   - `partner` -> `mlt_partner` CPT
 *   - `book` -> `mlt_book` CPT
 *   - `galleryImage` -> `mlt_gallery_image` CPT
 *   - `registration` -> `mlt_registration` CPT
 *   - `subscriber` -> `mlt_subscriber` CPT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MLT_SANITY_PROJECT_ID', '9ks0sgl2' );
define( 'MLT_SANITY_DATASET', 'production' );
define( 'MLT_SANITY_API_VERSION', '2026-04-16' );

/**
 * Convert a Sanity image asset reference (`image-<hash>-<dims>-<ext>`) to its CDN URL.
 */
function mlt_sanity_image_ref_to_url( $image_field ): string {
	if ( ! is_array( $image_field ) || empty( $image_field['asset']['_ref'] ) ) {
		return '';
	}
	$ref = (string) $image_field['asset']['_ref'];
	if ( ! preg_match( '/^image-([a-f0-9]+)-(\d+x\d+)-([a-z0-9]+)$/i', $ref, $m ) ) {
		return '';
	}
	return sprintf(
		'https://cdn.sanity.io/images/%s/%s/%s-%s.%s',
		MLT_SANITY_PROJECT_ID,
		MLT_SANITY_DATASET,
		$m[1],
		$m[2],
		$m[3]
	);
}

/**
 * Convert Sanity Portable Text blocks array into clean HTML paragraphs.
 */
function mlt_portable_text_to_html( $blocks ): string {
	if ( ! is_array( $blocks ) ) {
		return '';
	}
	$paragraphs = array();
	foreach ( $blocks as $block ) {
		if ( ! is_array( $block ) || ( $block['_type'] ?? '' ) !== 'block' ) {
			continue;
		}
		$text_parts = array();
		if ( ! empty( $block['children'] ) && is_array( $block['children'] ) ) {
			foreach ( $block['children'] as $span ) {
				if ( isset( $span['text'] ) && is_string( $span['text'] ) ) {
					$text_parts[] = esc_html( $span['text'] );
				}
			}
		}
		$line = trim( implode( '', $text_parts ) );
		if ( '' !== $line ) {
			$paragraphs[] = '<p>' . $line . '</p>';
		}
	}
	return implode( "\n", $paragraphs );
}

/**
 * Find an existing post by `_mlt_sanity_id` meta value.
 */
function mlt_find_post_by_sanity_id( string $post_type, string $sanity_id ): int {
	$existing = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => array( 'publish', 'pending', 'draft', 'private' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => '_mlt_sanity_id',
					'value' => $sanity_id,
				),
			),
		)
	);
	return ! empty( $existing ) ? (int) $existing[0] : 0;
}

/**
 * Download and attach a remote image URL to the WordPress Media Library (idempotent by source URL).
 */
function mlt_sideload_image_url( string $url, int $post_id = 0, string $desc = '' ): int {
	if ( '' === $url ) {
		return 0;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => '_mlt_source_url',
					'value' => $url,
				),
			),
		)
	);
	if ( ! empty( $existing ) ) {
		$att_id = (int) $existing[0];
		if ( $post_id > 0 ) {
			set_post_thumbnail( $post_id, $att_id );
		}
		return $att_id;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = download_url( $url, 30 );
	if ( is_wp_error( $tmp ) ) {
		return 0;
	}

	$filename   = basename( (string) wp_parse_url( $url, PHP_URL_PATH ) ) ?: 'mlt-image.jpg';
	$file_array = array(
		'name'     => sanitize_file_name( $filename ),
		'tmp_name' => $tmp,
	);

	$att_id = media_handle_sideload( $file_array, $post_id, $desc );
	if ( is_wp_error( $att_id ) ) {
		@unlink( $tmp );
		return 0;
	}

	update_post_meta( (int) $att_id, '_mlt_source_url', $url );
	if ( $post_id > 0 ) {
		set_post_thumbnail( $post_id, (int) $att_id );
	}

	return (int) $att_id;
}

/**
 * Execute GROQ query against Sanity HTTP API and import all documents.
 */
function mlt_sanity_import_run( string $read_token = '' ): array {
	if ( '' === $read_token ) {
		$read_token = defined( 'MLT_SANITY_READ_TOKEN' ) ? (string) MLT_SANITY_READ_TOKEN : ( getenv( 'SANITY_API_READ_TOKEN' ) ?: '' );
	}

	$groq  = '*[_type in ["siteSettings","event","article","story","communityGroup","partner","book","galleryImage","registration","subscriber"]]';
	$url   = sprintf(
		'https://%s.api.sanity.io/v%s/data/query/%s?query=%s',
		MLT_SANITY_PROJECT_ID,
		MLT_SANITY_API_VERSION,
		MLT_SANITY_DATASET,
		rawurlencode( $groq )
	);
	$args  = array( 'timeout' => 30 );
	if ( '' !== $read_token ) {
		$args['headers'] = array(
			'Authorization' => 'Bearer ' . $read_token,
		);
	}

	$response = wp_remote_get( $url, $args );
	if ( is_wp_error( $response ) ) {
		return array(
			'ok'    => false,
			'error' => $response->get_error_message(),
		);
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$body = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	if ( 200 !== $code || ! is_array( $body ) || ! isset( $body['result'] ) || ! is_array( $body['result'] ) ) {
		return array(
			'ok'     => false,
			'status' => $code,
			'error'  => 'Unexpected response from Sanity query endpoint.',
		);
	}

	return mlt_sanity_import_documents( $body['result'] );
}

/**
 * Import an array of decoded Sanity documents into WordPress options and CPTs.
 */
function mlt_sanity_import_documents( array $docs ): array {
	$counts = array(
		'siteSettings'   => 0,
		'event'          => 0,
		'article'        => 0,
		'story'          => 0,
		'communityGroup' => 0,
		'partner'        => 0,
		'book'           => 0,
		'galleryImage'   => 0,
		'registration'   => 0,
		'subscriber'     => 0,
	);

	foreach ( $docs as $doc ) {
		if ( ! is_array( $doc ) || empty( $doc['_type'] ) || empty( $doc['_id'] ) ) {
			continue;
		}
		$type = (string) $doc['_type'];
		$id   = (string) $doc['_id'];

		if ( 'siteSettings' === $type ) {
			$settings = mlt_get_site_settings();
			if ( ! empty( $doc['heroTitle'] ) ) {
				$settings['heroTitle'] = sanitize_text_field( (string) $doc['heroTitle'] );
			}
			if ( ! empty( $doc['heroSupportingMessage'] ) ) {
				$settings['heroSupportingMessage'] = sanitize_text_field( (string) $doc['heroSupportingMessage'] );
			}
			if ( ! empty( $doc['heroDescription'] ) ) {
				$settings['heroDescription'] = sanitize_textarea_field( (string) $doc['heroDescription'] );
			}
			$hero_cdn_url = mlt_sanity_image_ref_to_url( $doc['heroImage'] ?? null );
			if ( '' !== $hero_cdn_url ) {
				$att_id = mlt_sideload_image_url( $hero_cdn_url, 0, "Men Let's Talk Hero Image" );
				if ( $att_id > 0 ) {
					$local_url = wp_get_attachment_image_url( $att_id, 'full' );
					if ( $local_url ) {
						$settings['heroImageUrl'] = $local_url;
					}
				}
			}
			if ( ! empty( $doc['pillars'] ) && is_array( $doc['pillars'] ) ) {
				$pillars = array();
				foreach ( $doc['pillars'] as $p ) {
					if ( ! empty( $p['name'] ) ) {
						$pillars[] = array(
							'name'        => sanitize_text_field( (string) $p['name'] ),
							'description' => sanitize_textarea_field( (string) ( $p['description'] ?? '' ) ),
						);
					}
				}
				if ( ! empty( $pillars ) ) {
					$settings['pillars'] = $pillars;
				}
			}
			if ( ! empty( $doc['featuredTopics'] ) && is_array( $doc['featuredTopics'] ) ) {
				$settings['featuredTopics'] = array_map( 'sanitize_text_field', $doc['featuredTopics'] );
			}
			if ( ! empty( $doc['communityFeatures'] ) && is_array( $doc['communityFeatures'] ) ) {
				$features = array();
				foreach ( $doc['communityFeatures'] as $f ) {
					if ( ! empty( $f['name'] ) ) {
						$features[] = array(
							'name'        => sanitize_text_field( (string) $f['name'] ),
							'description' => sanitize_textarea_field( (string) ( $f['description'] ?? '' ) ),
						);
					}
				}
				if ( ! empty( $features ) ) {
					$settings['communityFeatures'] = $features;
				}
			}
			if ( ! empty( $doc['values'] ) && is_array( $doc['values'] ) ) {
				$values = array();
				foreach ( $doc['values'] as $v ) {
					if ( ! empty( $v['name'] ) ) {
						$values[] = array(
							'name'        => sanitize_text_field( (string) $v['name'] ),
							'description' => sanitize_textarea_field( (string) ( $v['description'] ?? '' ) ),
						);
					}
				}
				if ( ! empty( $values ) ) {
					$settings['values'] = $values;
				}
			}
			update_option( 'mlt_site_settings', $settings );
			++$counts['siteSettings'];
			continue;
		}

		if ( 'event' === $type ) {
			$post_id = mlt_find_post_by_sanity_id( 'mlt_event', $id );
			$data    = array(
				'post_type'    => 'mlt_event',
				'post_status'  => str_starts_with( $id, 'drafts.' ) ? 'draft' : 'publish',
				'post_title'   => sanitize_text_field( (string) ( $doc['name'] ?? 'Event' ) ),
				'post_name'    => sanitize_title( (string) ( $doc['slug']['current'] ?? $doc['name'] ?? $id ) ),
				'post_content' => sanitize_textarea_field( (string) ( $doc['description'] ?? '' ) ),
			);
			if ( $post_id > 0 ) {
				$data['ID'] = $post_id;
				wp_update_post( $data );
			} else {
				$post_id = (int) wp_insert_post( $data );
			}
			if ( $post_id > 0 ) {
				update_post_meta( $post_id, '_mlt_sanity_id', $id );
				update_post_meta( $post_id, '_mlt_event_date', sanitize_text_field( (string) ( $doc['date'] ?? '' ) ) );
				update_post_meta( $post_id, '_mlt_event_location', sanitize_text_field( (string) ( $doc['location'] ?? '' ) ) );
				update_post_meta( $post_id, '_mlt_event_category', sanitize_text_field( (string) ( $doc['category'] ?? '' ) ) );
				update_post_meta( $post_id, '_mlt_event_registration_open', ! empty( $doc['registrationOpen'] ) ? '1' : '0' );
				$img_url = mlt_sanity_image_ref_to_url( $doc['image'] ?? null );
				if ( '' !== $img_url ) {
					mlt_sideload_image_url( $img_url, $post_id, $data['post_title'] );
				}
				++$counts['event'];
			}
			continue;
		}

		if ( 'article' === $type ) {
			$post_id = mlt_find_post_by_sanity_id( 'mlt_resource', $id );
			$data    = array(
				'post_type'    => 'mlt_resource',
				'post_status'  => str_starts_with( $id, 'drafts.' ) ? 'draft' : 'publish',
				'post_title'   => sanitize_text_field( (string) ( $doc['title'] ?? 'Resource' ) ),
				'post_name'    => sanitize_title( (string) ( $doc['slug']['current'] ?? $doc['title'] ?? $id ) ),
				'post_content' => mlt_portable_text_to_html( $doc['body'] ?? array() ),
			);
			if ( $post_id > 0 ) {
				$data['ID'] = $post_id;
				wp_update_post( $data );
			} else {
				$post_id = (int) wp_insert_post( $data );
			}
			if ( $post_id > 0 ) {
				update_post_meta( $post_id, '_mlt_sanity_id', $id );
				update_post_meta( $post_id, '_mlt_resource_type', sanitize_text_field( (string) ( $doc['resourceType'] ?? 'article' ) ) );
				$topics = is_array( $doc['topics'] ?? null ) ? array_map( 'sanitize_text_field', $doc['topics'] ) : array();
				update_post_meta( $post_id, '_mlt_resource_topics', $topics );
				if ( ! empty( $doc['videoUrl'] ) ) {
					update_post_meta( $post_id, '_mlt_video_url', esc_url_raw( (string) $doc['videoUrl'] ) );
				}
				$img_url = mlt_sanity_image_ref_to_url( $doc['featuredImage'] ?? null );
				if ( '' !== $img_url ) {
					mlt_sideload_image_url( $img_url, $post_id, $data['post_title'] );
				}
				++$counts['article'];
			}
			continue;
		}

		if ( 'story' === $type ) {
			$post_id = mlt_find_post_by_sanity_id( 'mlt_story', $id );
			$name    = sanitize_text_field( (string) ( $doc['name'] ?? 'Anonymous' ) ) ?: 'Anonymous';
			$data    = array(
				'post_type'    => 'mlt_story',
				'post_status'  => ( str_starts_with( $id, 'drafts.' ) || empty( $doc['consentGiven'] ) ) ? 'pending' : 'publish',
				'post_title'   => $name,
				'post_content' => mlt_portable_text_to_html( $doc['body'] ?? array() ),
			);
			if ( $post_id > 0 ) {
				$data['ID'] = $post_id;
				wp_update_post( $data );
			} else {
				$post_id = (int) wp_insert_post( $data );
			}
			if ( $post_id > 0 ) {
				update_post_meta( $post_id, '_mlt_sanity_id', $id );
				update_post_meta( $post_id, '_mlt_story_name', $name );
				update_post_meta( $post_id, '_mlt_story_age_or_category', sanitize_text_field( (string) ( $doc['ageOrCategory'] ?? '' ) ) );
				$topics = is_array( $doc['topics'] ?? null ) ? array_map( 'sanitize_text_field', $doc['topics'] ) : array();
				update_post_meta( $post_id, '_mlt_story_topics', $topics );
				if ( ! empty( $doc['videoUrl'] ) ) {
					update_post_meta( $post_id, '_mlt_video_url', esc_url_raw( (string) $doc['videoUrl'] ) );
				}
				update_post_meta( $post_id, '_mlt_consent_given', ! empty( $doc['consentGiven'] ) ? '1' : '0' );
				++$counts['story'];
			}
			continue;
		}

		if ( 'communityGroup' === $type ) {
			$post_id = mlt_find_post_by_sanity_id( 'mlt_community_group', $id );
			$data    = array(
				'post_type'    => 'mlt_community_group',
				'post_status'  => str_starts_with( $id, 'drafts.' ) ? 'draft' : 'publish',
				'post_title'   => sanitize_text_field( (string) ( $doc['name'] ?? 'Community Group' ) ),
				'post_content' => sanitize_textarea_field( (string) ( $doc['description'] ?? '' ) ),
			);
			if ( $post_id > 0 ) {
				$data['ID'] = $post_id;
				wp_update_post( $data );
			} else {
				$post_id = (int) wp_insert_post( $data );
			}
			if ( $post_id > 0 ) {
				update_post_meta( $post_id, '_mlt_sanity_id', $id );
				update_post_meta( $post_id, '_mlt_group_audience', sanitize_text_field( (string) ( $doc['audience'] ?? '' ) ) );
				$img_url = mlt_sanity_image_ref_to_url( $doc['image'] ?? null );
				if ( '' !== $img_url ) {
					mlt_sideload_image_url( $img_url, $post_id, $data['post_title'] );
				}
				++$counts['communityGroup'];
			}
			continue;
		}

		if ( 'partner' === $type ) {
			$post_id = mlt_find_post_by_sanity_id( 'mlt_partner', $id );
			$data    = array(
				'post_type'    => 'mlt_partner',
				'post_status'  => str_starts_with( $id, 'drafts.' ) ? 'draft' : 'publish',
				'post_title'   => sanitize_text_field( (string) ( $doc['name'] ?? 'Partner' ) ),
				'post_content' => sanitize_textarea_field( (string) ( $doc['description'] ?? '' ) ),
			);
			if ( $post_id > 0 ) {
				$data['ID'] = $post_id;
				wp_update_post( $data );
			} else {
				$post_id = (int) wp_insert_post( $data );
			}
			if ( $post_id > 0 ) {
				update_post_meta( $post_id, '_mlt_sanity_id', $id );
				update_post_meta( $post_id, '_mlt_partnership_type', sanitize_text_field( (string) ( $doc['partnershipType'] ?? '' ) ) );
				if ( ! empty( $doc['websiteUrl'] ) ) {
					update_post_meta( $post_id, '_mlt_website_url', esc_url_raw( (string) $doc['websiteUrl'] ) );
				}
				$img_url = mlt_sanity_image_ref_to_url( $doc['logo'] ?? null );
				if ( '' !== $img_url ) {
					mlt_sideload_image_url( $img_url, $post_id, $data['post_title'] );
				}
				++$counts['partner'];
			}
			continue;
		}

		if ( 'book' === $type ) {
			$post_id = mlt_find_post_by_sanity_id( 'mlt_book', $id );
			$data    = array(
				'post_type'    => 'mlt_book',
				'post_status'  => str_starts_with( $id, 'drafts.' ) ? 'draft' : 'publish',
				'post_title'   => sanitize_text_field( (string) ( $doc['title'] ?? 'Book' ) ),
				'post_content' => sanitize_textarea_field( (string) ( $doc['description'] ?? '' ) ),
			);
			if ( $post_id > 0 ) {
				$data['ID'] = $post_id;
				wp_update_post( $data );
			} else {
				$post_id = (int) wp_insert_post( $data );
			}
			if ( $post_id > 0 ) {
				update_post_meta( $post_id, '_mlt_sanity_id', $id );
				update_post_meta( $post_id, '_mlt_book_author', sanitize_text_field( (string) ( $doc['author'] ?? '' ) ) );
				update_post_meta( $post_id, '_mlt_book_why_written', sanitize_textarea_field( (string) ( $doc['whyWritten'] ?? '' ) ) );
				if ( ! empty( $doc['purchaseUrl'] ) ) {
					update_post_meta( $post_id, '_mlt_book_purchase_url', esc_url_raw( (string) $doc['purchaseUrl'] ) );
				}
				if ( ! empty( $doc['testimonials'] ) && is_array( $doc['testimonials'] ) ) {
					update_post_meta( $post_id, '_mlt_book_testimonials', $doc['testimonials'] );
				}
				$img_url = mlt_sanity_image_ref_to_url( $doc['coverImage'] ?? null );
				if ( '' !== $img_url ) {
					mlt_sideload_image_url( $img_url, $post_id, $data['post_title'] );
				}
				++$counts['book'];
			}
			continue;
		}

		if ( 'galleryImage' === $type ) {
			$post_id = mlt_find_post_by_sanity_id( 'mlt_gallery_image', $id );
			$caption = sanitize_text_field( (string) ( $doc['caption'] ?? '' ) );
			$data    = array(
				'post_type'   => 'mlt_gallery_image',
				'post_status' => str_starts_with( $id, 'drafts.' ) ? 'draft' : 'publish',
				'post_title'  => $caption ?: 'Gallery Photo',
			);
			if ( $post_id > 0 ) {
				$data['ID'] = $post_id;
				wp_update_post( $data );
			} else {
				$post_id = (int) wp_insert_post( $data );
			}
			if ( $post_id > 0 ) {
				update_post_meta( $post_id, '_mlt_sanity_id', $id );
				update_post_meta( $post_id, '_mlt_gallery_caption', $caption );
				$img_url = mlt_sanity_image_ref_to_url( $doc['image'] ?? null );
				if ( '' !== $img_url ) {
					update_post_meta( $post_id, '_mlt_image_url', $img_url );
					mlt_sideload_image_url( $img_url, $post_id, $caption ?: 'Gallery Photo' );
				}
				++$counts['galleryImage'];
			}
			continue;
		}
	}

	return array(
		'ok'     => true,
		'counts' => $counts,
	);
}
