<?php
/**
 * JSON API Route Handlers for Men Let's Talk:
 *   - GET  /api/health
 *   - POST /api/register
 *   - POST /api/subscribe
 *   - POST /api/share-story
 *   - GET  /api/contact
 *   - POST /api/contact
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mlt_send_json( array $data, int $status = 200, array $extra_headers = array() ): void {
	status_header( $status );
	header( 'Content-Type: application/json; charset=utf-8' );
	foreach ( $extra_headers as $k => $v ) {
		header( $k . ': ' . $v );
	}
	echo wp_json_encode( $data );
	exit;
}

function mlt_read_json_body(): ?array {
	$raw = file_get_contents( 'php://input' );
	if ( false === $raw || '' === trim( $raw ) ) {
		return null;
	}
	$decoded = json_decode( $raw, true );
	return is_array( $decoded ) ? $decoded : null;
}

function mlt_is_valid_email( string $email ): bool {
	return (bool) preg_match( '/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email );
}

function mlt_get_client_ip(): string {
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$parts = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
		$first = trim( $parts[0] );
		if ( '' !== $first ) {
			return $first;
		}
	}
	if ( ! empty( $_SERVER['HTTP_X_REAL_IP'] ) ) {
		return trim( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REAL_IP'] ) ) );
	}
	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
}

function mlt_are_public_writes_configured(): bool {
	if ( defined( 'MLT_PUBLIC_SUBMISSIONS_ENABLED' ) ) {
		return (bool) MLT_PUBLIC_SUBMISSIONS_ENABLED;
	}
	return (bool) get_option( 'mlt_public_submissions_enabled', false );
}

function mlt_handle_api_requests(): void {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}

	$path   = rtrim( (string) wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ), '/' );
	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';

	if ( '/api/health' === $path && 'GET' === $method ) {
		mlt_send_json( array( 'status' => 'ok' ), 200 );
	}

	if ( '/api/register' === $path && 'POST' === $method ) {
		mlt_handle_api_register();
	}

	if ( '/api/subscribe' === $path && 'POST' === $method ) {
		mlt_handle_api_subscribe();
	}

	if ( '/api/share-story' === $path && 'POST' === $method ) {
		mlt_handle_api_share_story();
	}

	if ( '/api/contact' === $path ) {
		if ( 'GET' === $method ) {
			mlt_send_json(
				mlt_create_contact_challenge(),
				200,
				array( 'Cache-Control' => 'no-store, max-age=0' )
			);
		}
		if ( 'POST' === $method ) {
			mlt_handle_api_contact();
		}
	}
}
add_action( 'init', 'mlt_handle_api_requests', 1 );

function mlt_handle_api_register(): void {
	$body = mlt_read_json_body();
	if ( null === $body ) {
		mlt_send_json( array( 'error' => 'Invalid request body.' ), 400 );
	}

	$event_id      = $body['eventId'] ?? null;
	$name          = $body['name'] ?? null;
	$email         = $body['email'] ?? null;
	$phone         = $body['phone'] ?? null;
	$consent_given = $body['consentGiven'] ?? null;

	if ( ! is_string( $event_id ) || '' === $event_id ) {
		mlt_send_json( array( 'error' => 'Missing event.' ), 400 );
	}
	if ( ! is_string( $name ) || '' === trim( $name ) ) {
		mlt_send_json( array( 'error' => 'Name is required.' ), 400 );
	}
	if ( ! is_string( $email ) || ! mlt_is_valid_email( $email ) ) {
		mlt_send_json( array( 'error' => 'A valid email is required.' ), 400 );
	}
	if ( null !== $phone && ! is_string( $phone ) ) {
		mlt_send_json( array( 'error' => 'Invalid phone.' ), 400 );
	}
	if ( true !== $consent_given ) {
		mlt_send_json( array( 'error' => 'Consent is required.' ), 400 );
	}

	if ( ! mlt_are_public_writes_configured() ) {
		mlt_send_json( array( 'error' => 'Registration is not configured.' ), 503 );
	}

	$now     = gmdate( 'c' );
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'mlt_registration',
			'post_status' => 'publish',
			'post_title'  => sanitize_text_field( trim( $name ) . ' — ' . $event_id ),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		mlt_send_json( array( 'error' => 'Could not save your registration. Please try again.' ), 502 );
	}

	update_post_meta( $post_id, '_mlt_event_id', sanitize_text_field( $event_id ) );
	update_post_meta( $post_id, '_mlt_name', sanitize_text_field( trim( $name ) ) );
	update_post_meta( $post_id, '_mlt_email', sanitize_email( trim( $email ) ) );
	if ( is_string( $phone ) && '' !== trim( $phone ) ) {
		update_post_meta( $post_id, '_mlt_phone', sanitize_text_field( trim( $phone ) ) );
	}
	update_post_meta( $post_id, '_mlt_consent_given', '1' );
	update_post_meta( $post_id, '_mlt_consent_version', '2026-04-17' );
	update_post_meta( $post_id, '_mlt_consented_at', $now );

	mlt_send_json( array( 'ok' => true ), 200 );
}

function mlt_handle_api_subscribe(): void {
	$body = mlt_read_json_body();
	if ( null === $body ) {
		mlt_send_json( array( 'error' => 'Invalid request body.' ), 400 );
	}

	$email           = $body['email'] ?? null;
	$interests       = $body['interests'] ?? null;
	$consent_given   = $body['consentGiven'] ?? null;
	$valid_interests = array( 'events', 'resources', 'conversations', 'newsletter' );

	if ( ! is_string( $email ) || ! mlt_is_valid_email( $email ) ) {
		mlt_send_json( array( 'error' => 'A valid email is required.' ), 400 );
	}
	if ( null !== $interests ) {
		if ( ! is_array( $interests ) ) {
			mlt_send_json( array( 'error' => 'Invalid interests.' ), 400 );
		}
		foreach ( $interests as $item ) {
			if ( ! is_string( $item ) || ! in_array( $item, $valid_interests, true ) ) {
				mlt_send_json( array( 'error' => 'Invalid interests.' ), 400 );
			}
		}
	}
	if ( true !== $consent_given ) {
		mlt_send_json( array( 'error' => 'Consent is required.' ), 400 );
	}

	if ( ! mlt_are_public_writes_configured() ) {
		mlt_send_json( array( 'error' => 'Subscriptions are not configured.' ), 503 );
	}

	$now     = gmdate( 'c' );
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'mlt_subscriber',
			'post_status' => 'publish',
			'post_title'  => sanitize_email( trim( $email ) ),
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		mlt_send_json( array( 'error' => 'Could not save your subscription. Please try again.' ), 502 );
	}

	update_post_meta( $post_id, '_mlt_email', sanitize_email( trim( $email ) ) );
	update_post_meta( $post_id, '_mlt_interests', is_array( $interests ) ? array_map( 'sanitize_text_field', $interests ) : array() );
	update_post_meta( $post_id, '_mlt_consent_given', '1' );
	update_post_meta( $post_id, '_mlt_consent_version', '2026-04-17' );
	update_post_meta( $post_id, '_mlt_consented_at', $now );

	mlt_send_json( array( 'ok' => true ), 200 );
}

function mlt_handle_api_share_story(): void {
	$body = mlt_read_json_body();
	if ( null === $body ) {
		mlt_send_json( array( 'error' => 'Invalid request body.' ), 400 );
	}

	$name                     = $body['name'] ?? null;
	$age_or_category          = $body['ageOrCategory'] ?? null;
	$topics                   = $body['topics'] ?? null;
	$story_text               = $body['storyText'] ?? null;
	$video_url                = $body['videoUrl'] ?? null;
	$processing_consent_given = $body['processingConsentGiven'] ?? null;
	$consent_given            = $body['consentGiven'] ?? null;
	$valid_topics             = array_keys( mlt_get_topic_labels() );

	if ( null !== $name && ( ! is_string( $name ) || mb_strlen( $name ) > 100 ) ) {
		mlt_send_json( array( 'error' => 'Invalid name.' ), 400 );
	}
	if ( null !== $age_or_category && ( ! is_string( $age_or_category ) || mb_strlen( $age_or_category ) > 100 ) ) {
		mlt_send_json( array( 'error' => 'Invalid age/category.' ), 400 );
	}
	if ( ! is_array( $topics ) || empty( $topics ) ) {
		mlt_send_json( array( 'error' => 'At least one topic is required.' ), 400 );
	}
	foreach ( $topics as $t ) {
		if ( ! is_string( $t ) || ! in_array( $t, $valid_topics, true ) ) {
			mlt_send_json( array( 'error' => 'At least one topic is required.' ), 400 );
		}
	}
	if ( null !== $story_text && ( ! is_string( $story_text ) || mb_strlen( $story_text ) > 5000 ) ) {
		mlt_send_json( array( 'error' => 'Story text is too long.' ), 400 );
	}
	if ( null !== $video_url ) {
		if ( ! is_string( $video_url ) || ! preg_match( '#^https?://#i', $video_url ) || ! wp_http_validate_url( $video_url ) ) {
			mlt_send_json( array( 'error' => 'Invalid video URL.' ), 400 );
		}
	}

	$has_story_text = is_string( $story_text ) && '' !== trim( $story_text );
	$has_video_url  = is_string( $video_url ) && '' !== trim( $video_url );
	if ( ! $has_story_text && ! $has_video_url ) {
		mlt_send_json( array( 'error' => 'Share your story as text or a video link.' ), 400 );
	}
	if ( true !== $processing_consent_given || true !== $consent_given ) {
		mlt_send_json( array( 'error' => 'Both consent checkboxes are required.' ), 400 );
	}

	if ( ! mlt_are_public_writes_configured() ) {
		mlt_send_json( array( 'error' => 'Story submission is not configured.' ), 503 );
	}

	$now        = gmdate( 'c' );
	$clean_name = is_string( $name ) && '' !== trim( $name ) ? sanitize_text_field( trim( $name ) ) : 'Anonymous';
	$post_id    = wp_insert_post(
		array(
			'post_type'    => 'mlt_story',
			'post_status'  => 'pending',
			'post_title'   => $clean_name,
			'post_content' => $has_story_text ? wp_kses_post( trim( (string) $story_text ) ) : '',
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		mlt_send_json( array( 'error' => 'Could not save your story. Please try again.' ), 502 );
	}

	update_post_meta( $post_id, '_mlt_story_name', $clean_name );
	if ( is_string( $age_or_category ) && '' !== trim( $age_or_category ) ) {
		update_post_meta( $post_id, '_mlt_story_age_or_category', sanitize_text_field( trim( $age_or_category ) ) );
	}
	update_post_meta( $post_id, '_mlt_story_topics', array_map( 'sanitize_text_field', $topics ) );
	if ( $has_video_url ) {
		update_post_meta( $post_id, '_mlt_video_url', esc_url_raw( trim( (string) $video_url ) ) );
	}
	update_post_meta( $post_id, '_mlt_consent_given', '1' );
	update_post_meta( $post_id, '_mlt_processing_consent_given', '1' );
	update_post_meta( $post_id, '_mlt_consent_version', '2026-04-17' );
	update_post_meta( $post_id, '_mlt_consented_at', $now );

	mlt_send_json( array( 'ok' => true ), 200 );
}

function mlt_handle_api_contact(): void {
	$body = mlt_read_json_body();
	if ( null === $body ) {
		mlt_send_json( array( 'error' => 'Invalid request body.' ), 400 );
	}

	$name     = $body['name'] ?? null;
	$email    = $body['email'] ?? null;
	$phone    = $body['phone'] ?? null;
	$topic    = $body['topic'] ?? null;
	$message  = $body['message'] ?? null;
	$website  = $body['website'] ?? null;
	$ts       = $body['ts'] ?? null;
	$seed     = $body['seed'] ?? null;
	$sig      = $body['sig'] ?? null;
	$js_token = $body['jsToken'] ?? null;

	if ( ! is_string( $name ) || '' === trim( $name ) || mb_strlen( trim( $name ) ) > 100 ) {
		mlt_send_json( array( 'error' => 'Name is required.' ), 400 );
	}
	if ( ! is_string( $email ) || ! mlt_is_valid_email( trim( $email ) ) ) {
		mlt_send_json( array( 'error' => 'A valid email is required.' ), 400 );
	}
	if ( null !== $phone && ( ! is_string( $phone ) || mb_strlen( trim( $phone ) ) > 40 ) ) {
		mlt_send_json( array( 'error' => 'Invalid phone.' ), 400 );
	}
	if ( null !== $topic && ( ! is_string( $topic ) || mb_strlen( trim( $topic ) ) > 160 ) ) {
		mlt_send_json( array( 'error' => 'Topic is too long.' ), 400 );
	}
	if ( ! is_string( $message ) || '' === trim( $message ) || mb_strlen( trim( $message ) ) > 5000 ) {
		mlt_send_json( array( 'error' => 'Message is required.' ), 400 );
	}

	$simulate_emails = ( defined( 'MLT_CONTACT_SIMULATE_EMAILS' ) && 'yes' === MLT_CONTACT_SIMULATE_EMAILS )
		|| ( 'yes' === getenv( 'CONTACT_SIMULATE_EMAILS' ) );
	$smtp_user       = defined( 'MLT_SMTP_USER' ) ? MLT_SMTP_USER : ( getenv( 'SMTP_USER' ) ?: '' );
	$smtp_pass       = defined( 'MLT_SMTP_PASS' ) ? MLT_SMTP_PASS : ( getenv( 'SMTP_PASS' ) ?: '' );
	$contact_info    = mlt_get_contact_info();
	$recipient_email = defined( 'MLT_CONTACT_RECIPIENT' ) && MLT_CONTACT_RECIPIENT
		? MLT_CONTACT_RECIPIENT
		: ( getenv( 'CONTACT_RECIPIENT_EMAIL' ) ?: $contact_info['email'] );

	if ( ! mlt_are_public_writes_configured() || ( ! $simulate_emails && ( empty( $smtp_user ) || empty( $smtp_pass ) ) ) ) {
		mlt_send_json( array( 'error' => 'Contact form is not configured.' ), 503 );
	}

	// Layer 1 — Disguised text honeypot (`website`). Silently accept so bots cannot probe defenses.
	if ( is_string( $website ) && '' !== trim( $website ) ) {
		mlt_send_json( array( 'ok' => true ), 200 );
	}

	// Layer 2 & Layer 3 — Signed timestamp time-trap (4s–2h window + single-use replay lock)
	// and client-side human interaction proof token.
	if (
		! mlt_verify_challenge_and_interaction(
			array(
				'ts'      => $ts,
				'seed'    => $seed,
				'sig'     => $sig,
				'jsToken' => $js_token,
			)
		)
	) {
		mlt_send_json( array( 'ok' => true ), 200 );
	}

	$trimmed_name    = trim( $name );
	$trimmed_email   = trim( $email );
	$trimmed_phone   = is_string( $phone ) ? trim( $phone ) : '';
	$trimmed_topic   = is_string( $topic ) ? trim( $topic ) : '';
	$trimmed_message = trim( $message );

	// Layer 4 — Heuristic content & URL filter.
	if (
		mlt_is_spam_submission(
			array(
				'name'    => $trimmed_name,
				'email'   => $trimmed_email,
				'topic'   => $trimmed_topic,
				'message' => $trimmed_message,
			)
		)
	) {
		mlt_send_json( array( 'ok' => true ), 200 );
	}

	// Layer 5 — Per-IP and global circuit-breaker rate limiting.
	if ( mlt_is_rate_limited( mlt_get_client_ip() ) ) {
		mlt_send_json( array( 'ok' => true ), 200 );
	}

	// Layer 7 — Staging / preview simulation mode.
	if ( $simulate_emails ) {
		mlt_send_json(
			array(
				'ok'        => true,
				'simulated' => true,
			),
			200
		);
	}

	// Layer 6 — Sanitize display name in Reply-To header to prevent header injection.
	$safe_reply_name = mlt_sanitize_reply_to_name( $trimmed_name );
	$mail_subject    = "[Men Let's Talk] " . ( '' !== $trimmed_topic ? $trimmed_topic : 'New message from ' . $safe_reply_name );
	$mail_text       = implode(
		"\n",
		array(
			'Name: ' . $trimmed_name,
			'Email: ' . $trimmed_email,
			'Phone: ' . ( '' !== $trimmed_phone ? $trimmed_phone : 'Not provided' ),
			'Topic: ' . ( '' !== $trimmed_topic ? $trimmed_topic : 'Not specified' ),
			'',
			'Message:',
			$trimmed_message,
		)
	);

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		sprintf( 'Reply-To: "%s" <%s>', $safe_reply_name, $trimmed_email ),
	);

	$sent = wp_mail( $recipient_email, $mail_subject, $mail_text, $headers );
	if ( ! $sent ) {
		mlt_send_json(
			array( 'error' => 'Could not send your message. Please try again or reach us on WhatsApp.' ),
			502
		);
	}

	$sub_id = wp_insert_post(
		array(
			'post_type'    => 'mlt_submission',
			'post_status'  => 'publish',
			'post_title'   => sanitize_text_field( $mail_subject ),
			'post_content' => sanitize_textarea_field( $mail_text ),
		)
	);
	if ( ! is_wp_error( $sub_id ) ) {
		update_post_meta( $sub_id, '_mlt_name', sanitize_text_field( $trimmed_name ) );
		update_post_meta( $sub_id, '_mlt_email', sanitize_email( $trimmed_email ) );
		update_post_meta( $sub_id, '_mlt_phone', sanitize_text_field( $trimmed_phone ) );
		update_post_meta( $sub_id, '_mlt_topic', sanitize_text_field( $trimmed_topic ) );
	}

	mlt_send_json( array( 'ok' => true ), 200 );
}
