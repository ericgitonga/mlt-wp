<?php
/**
 * 7-Layer Public Form & Outbound SMTP Anti-Spam Engine for Men Let's Talk.
 *
 * Ports src/lib/contactSpam.ts to WordPress using HMAC-SHA256 challenges
 * and WordPress transients for single-use replay locking and rate limiting.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mlt_get_contact_secret(): string {
	if ( defined( 'MLT_CONTACT_SECRET' ) && strlen( (string) MLT_CONTACT_SECRET ) >= 16 ) {
		return (string) MLT_CONTACT_SECRET;
	}
	if ( defined( 'AUTH_SALT' ) && strlen( (string) AUTH_SALT ) >= 16 ) {
		return (string) AUTH_SALT;
	}
	return wp_salt( 'auth' );
}

function mlt_sign_contact_payload( string $ts, string $seed ): string {
	return hash_hmac( 'sha256', $ts . '.' . $seed, mlt_get_contact_secret() );
}

function mlt_build_js_token( string $ts, string $seed ): string {
	return strrev( $seed ) . '-' . substr( $ts, -4 );
}

function mlt_create_contact_challenge( ?int $now_ms = null ): array {
	$ts   = (string) ( $now_ms ?? (int) round( microtime( true ) * 1000 ) );
	$seed = bin2hex( random_bytes( 12 ) );
	$sig  = mlt_sign_contact_payload( $ts, $seed );

	return array(
		'ts'   => $ts,
		'seed' => $seed,
		'sig'  => $sig,
	);
}

function mlt_verify_challenge_and_interaction( array $input, ?int $now_ms = null ): bool {
	$ts       = isset( $input['ts'] ) && is_string( $input['ts'] ) ? $input['ts'] : '';
	$seed     = isset( $input['seed'] ) && is_string( $input['seed'] ) ? $input['seed'] : '';
	$sig      = isset( $input['sig'] ) && is_string( $input['sig'] ) ? $input['sig'] : '';
	$js_token = isset( $input['jsToken'] ) && is_string( $input['jsToken'] ) ? $input['jsToken'] : '';

	if ( '' === $ts || '' === $seed || '' === $sig || '' === $js_token ) {
		return false;
	}
	if ( ! preg_match( '/^\d{10,15}$/', $ts ) || ! preg_match( '/^[a-f0-9]{16,64}$/', $seed ) ) {
		return false;
	}

	$expected_sig = mlt_sign_contact_payload( $ts, $seed );
	if ( ! hash_equals( $expected_sig, $sig ) ) {
		return false;
	}

	$now     = $now_ms ?? (int) round( microtime( true ) * 1000 );
	$elapsed = $now - (int) $ts;
	if ( $elapsed < 4000 || $elapsed > 2 * 60 * 60 * 1000 ) {
		return false;
	}

	if ( mlt_build_js_token( $ts, $seed ) !== $js_token ) {
		return false;
	}

	$replay_key = 'mlt_rpl_' . md5( $sig );
	if ( get_transient( $replay_key ) ) {
		return false;
	}
	set_transient( $replay_key, 1, 2 * HOUR_IN_SECONDS );

	return true;
}

function mlt_count_urls( string $value ): int {
	if ( preg_match_all( '#(?:https?://|www\.)[^\s<>"]+#i', $value, $matches ) ) {
		return count( $matches[0] );
	}
	return 0;
}

function mlt_has_blocked_domain( string $value ): bool {
	$lower = strtolower( $value );
	$hosts = array(
		'telegra.ph',
		't.me',
		'bit.ly',
		'tinyurl.com',
		'tinyurl.one',
		'shorturl.at',
		'rb.gy',
		'is.gd',
		'cutt.ly',
		'ow.ly',
		'rebrand.ly',
		'pastebin.com',
		'rentry.co',
	);

	foreach ( $hosts as $host ) {
		if ( false !== strpos( $lower, $host ) ) {
			return true;
		}
	}

	if ( preg_match( '/\b[a-z0-9-]+(?:\.[a-z0-9-]+)*\.(?:ru|xyz|top|click|work|gq|tk|ml|cf|loan|buzz)\b/i', $lower ) ) {
		return true;
	}

	return false;
}

function mlt_is_all_caps_shouting( string $value ): bool {
	$letters = preg_replace( '/[^a-zA-Z]/', '', $value );
	$total   = strlen( $letters );
	if ( $total < 15 ) {
		return false;
	}
	$upper = strlen( preg_replace( '/[^A-Z]/', '', $letters ) );
	return ( $upper / $total ) > 0.6;
}

function mlt_is_camel_case_bot_name( string $name ): bool {
	$trimmed = trim( $name );
	if ( preg_match( '/\s/', $trimmed ) ) {
		return false;
	}
	return (bool) preg_match( '/^[A-Z][a-z]{2,}[A-Z][a-z]{2,}[A-Za-z]*$/', $trimmed );
}

function mlt_is_spam_submission( array $fields ): bool {
	$name    = trim( (string) ( $fields['name'] ?? '' ) );
	$email   = strtolower( trim( (string) ( $fields['email'] ?? '' ) ) );
	$topic   = trim( (string) ( $fields['topic'] ?? '' ) );
	$message = trim( (string) ( $fields['message'] ?? '' ) );

	if ( mlt_count_urls( $name ) > 0 || mlt_count_urls( $topic ) > 0 ) {
		return true;
	}
	if ( mlt_count_urls( $message ) > 1 ) {
		return true;
	}

	$combined = $name . ' ' . $email . ' ' . $topic . ' ' . $message;
	if ( mlt_has_blocked_domain( $combined ) ) {
		return true;
	}
	if ( mlt_is_camel_case_bot_name( $name ) ) {
		return true;
	}
	if ( mlt_is_all_caps_shouting( $message ) || mlt_is_all_caps_shouting( $topic ) ) {
		return true;
	}

	$scam_patterns = array(
		'/\bcrypto(?:currency)?\s+(?:recovery|investment|airdrop|giveaway)\b/i',
		'/\b(?:bitcoin|usdt|ethereum|forex)\s+(?:mining|signals|profit|investment|trading)\b/i',
		'/\b(?:seo|backlink|guest\s*post|domain\s*authority|google\s*ranking)\s+(?:service|package|agency|specialist|offer)\b/i',
		'/\b(?:rank\s+#?1\s+on\s+google|buy\s+backlinks|high\s+da\s+backlinks)\b/i',
		'/\b(?:casino|slot\s+online|poker\s+online|betting\s+bonus)\b/i',
		'/\b(?:passive\s+income\s+guaranteed|earn\s+\$\d+[,\d]*\s+(?:per|a)\s+(?:day|week))\b/i',
	);
	foreach ( $scam_patterns as $pattern ) {
		if ( preg_match( $pattern, $combined ) ) {
			return true;
		}
	}

	return false;
}

function mlt_is_rate_limited( string $ip ): bool {
	$ip_key      = md5( trim( $ip ) ?: '0.0.0.0' );
	$ip_15m_key  = 'mlt_rl_15m_' . $ip_key;
	$ip_24h_key  = 'mlt_rl_24h_' . $ip_key;
	$global_key  = 'mlt_rl_global_1h';

	$count_15m    = (int) get_transient( $ip_15m_key );
	$count_24h    = (int) get_transient( $ip_24h_key );
	$count_global = (int) get_transient( $global_key );

	if ( $count_15m >= 2 || $count_24h >= 5 || $count_global >= 15 ) {
		return true;
	}

	set_transient( $ip_15m_key, $count_15m + 1, 15 * MINUTE_IN_SECONDS );
	set_transient( $ip_24h_key, $count_24h + 1, DAY_IN_SECONDS );
	set_transient( $global_key, $count_global + 1, HOUR_IN_SECONDS );

	return false;
}

function mlt_sanitize_reply_to_name( string $name ): string {
	$cleaned = trim( preg_replace( '/\s+/', ' ', preg_replace( '/[\r\n<>",;\\\\]+/', ' ', $name ) ) );
	return '' !== $cleaned ? substr( $cleaned, 0, 80 ) : "Men Let's Talk Visitor";
}
