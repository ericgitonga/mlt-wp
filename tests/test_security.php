<?php
/**
 * Security & Data Protection Unit Test Suite for Men Let's Talk (`mlt-wp`).
 *
 * Verifies 7-layer anti-spam, atomic replay locking, rate limiting,
 * story consent boundaries, operational CPT capability mapping, and administrative RBAC.
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../wp-content/plugins/mlt-wp-content/includes/anti-spam.php';
require_once __DIR__ . '/../wp-content/plugins/mlt-wp-content/includes/admin-meta-boxes.php';
require_once __DIR__ . '/../wp-content/plugins/mlt-wp-content/mlt-wp-content.php';

function sec_assert(bool $condition, string $message): void {
    if (!$condition) {
        throw new \RuntimeException("Assertion failed: " . $message);
    }
}

$SECURITY_TESTS = [];

$SECURITY_TESTS['test_form_method_post_enforcement'] = function (): void {
    $contact_tpl = (string) file_get_contents(__DIR__ . '/../wp-content/themes/mlt-wp/page-contact.php');
    $story_tpl   = (string) file_get_contents(__DIR__ . '/../wp-content/themes/mlt-wp/page-share-your-story.php');
    $func_file   = (string) file_get_contents(__DIR__ . '/../wp-content/themes/mlt-wp/functions.php');

    sec_assert(
        (bool) preg_match('/<form[^>]+method=["\']post["\'][^>]+data-testid=["\']contact-form["\']|<form[^>]+data-testid=["\']contact-form["\'][^>]+method=["\']post["\']/', $contact_tpl),
        'Contact form must have explicit method="post"'
    );
    sec_assert(
        (bool) preg_match('/<form[^>]+method=["\']post["\'][^>]+data-testid=["\']share-story-form["\']|<form[^>]+data-testid=["\']share-story-form["\'][^>]+method=["\']post["\']/', $story_tpl),
        'Share story form must have explicit method="post"'
    );
    sec_assert(
        (bool) preg_match('/<form[^>]+method=["\']post["\'][^>]+data-testid=["\']register-form["\']|<form[^>]+data-testid=["\']register-form["\'][^>]+method=["\']post["\']/', $func_file),
        'Register form must have explicit method="post"'
    );
    sec_assert(
        (bool) preg_match('/<form[^>]+method=["\']post["\'][^>]+data-testid=["\']subscribe-form["\']|<form[^>]+data-testid=["\']subscribe-form["\'][^>]+method=["\']post["\']/', $func_file),
        'Subscribe form must have explicit method="post"'
    );
};

$SECURITY_TESTS['test_contact_replay_lock_atomic_mutex'] = function (): void {
    test_reset_wp_state();

    $sig = 'hash_signature_token_mlt_123';

    // First attempt should acquire the lock
    $acquired1 = mlt_acquire_replay_lock($sig);
    sec_assert($acquired1 === true, 'First submission with signature must acquire replay lock');

    // Immediate replay with identical signature must be rejected
    $acquired2 = mlt_acquire_replay_lock($sig);
    sec_assert($acquired2 === false, 'Replayed submission with identical signature must fail lock acquisition');

    // A different signature should succeed
    $acquired3 = mlt_acquire_replay_lock('different_signature_mlt_456');
    sec_assert($acquired3 === true, 'Unique signature must acquire its own lock');
};

$SECURITY_TESTS['test_rate_limiting_atomic_monotony'] = function (): void {
    test_reset_wp_state();

    $key = 'test_mlt_ip_rate_key';
    $limit = 2;

    $attempt1 = mlt_atomic_increment($key, 900, $limit);
    sec_assert($attempt1 === true, 'Attempt 1 within limit of 2');

    $attempt2 = mlt_atomic_increment($key, 900, $limit);
    sec_assert($attempt2 === true, 'Attempt 2 within limit of 2');

    $attempt3 = mlt_atomic_increment($key, 900, $limit);
    sec_assert($attempt3 === false, 'Attempt 3 exceeds limit of 2 and must be blocked');
};

$SECURITY_TESTS['test_operational_cpt_capability_isolation'] = function (): void {
    test_reset_wp_state();

    mlt_register_content_types();

    global $_test_registered_cpts;
    sec_assert(
        isset($_test_registered_cpts['mlt_registration']['capability_type']),
        'mlt_registration must define custom capability_type'
    );
    sec_assert(
        isset($_test_registered_cpts['mlt_subscriber']['capability_type']),
        'mlt_subscriber must define custom capability_type'
    );
    sec_assert(
        isset($_test_registered_cpts['mlt_submission']['capability_type']),
        'mlt_submission must define custom capability_type'
    );

    mlt_map_operational_capabilities();
    global $_test_role_caps;

    sec_assert(
        !empty($_test_role_caps['administrator']['read_mlt_submission']),
        'Administrator role must receive read_mlt_submission capability'
    );
    sec_assert(
        !empty($_test_role_caps['administrator']['read_mlt_registration']),
        'Administrator role must receive read_mlt_registration capability'
    );
    sec_assert(
        !empty($_test_role_caps['administrator']['read_mlt_subscriber']),
        'Administrator role must receive read_mlt_subscriber capability'
    );
};

$SECURITY_TESTS['test_story_publication_consent_demotion'] = function (): void {
    test_reset_wp_state();

    $post_id = 501;
    $post = new WP_Post($post_id, 'My Test Testimony', 'I overcame depression...', 'publish', 'mlt_story');
    $GLOBALS['_test_posts'][$post_id] = $post;

    test_set_current_user_can('edit_post', true);

    // Save with publication consent unchecked
    $_POST = [
        'mlt_story_meta_nonce'          => 'valid',
        'mlt_story_name'                => 'John D.',
        'mlt_processing_consent_given' => '1',
        // 'mlt_consent_given' omitted
    ];

    mlt_save_admin_meta_boxes($post_id, $post);

    sec_assert(
        $post->post_status === 'pending',
        'Story without explicit publication consent must be demoted from publish to pending'
    );
    sec_assert(
        get_post_meta($post_id, '_mlt_consent_given', true) === '0',
        '_mlt_consent_given must be 0'
    );
};

$SECURITY_TESTS['test_story_rest_query_consent_filter'] = function (): void {
    test_reset_wp_state();

    // Anonymous or subscriber user without edit_posts
    test_set_current_user_can('edit_posts', false);

    $args = ['post_type' => 'mlt_story'];
    $filtered = mlt_filter_rest_story_query($args, new WP_REST_Request());

    sec_assert(
        !empty($filtered['meta_query']),
        'REST query for non-editor must inject meta_query'
    );
    sec_assert(
        $filtered['meta_query'][0]['key'] === '_mlt_consent_given' &&
        $filtered['meta_query'][0]['value'] === '1',
        'REST query must strictly require _mlt_consent_given === 1'
    );

    // Editor should not be restricted
    test_set_current_user_can('edit_posts', true);
    $editor_args = ['post_type' => 'mlt_story'];
    $editor_filtered = mlt_filter_rest_story_query($editor_args, new WP_REST_Request());
    sec_assert(
        empty($editor_filtered['meta_query']),
        'REST query for editor must not restrict unconsented pending stories'
    );
};

$SECURITY_TESTS['test_site_settings_manage_options_rbac'] = function (): void {
    test_reset_wp_state();

    $defaults = mlt_get_default_site_settings();
    update_option('mlt_site_settings', $defaults);

    // Non-admin without manage_options
    test_set_current_user_can('manage_options', false);

    mlt_save_site_settings_from_request([
        'heroTitle' => 'ATTACKER_OVERWRITE',
    ]);

    $current = get_option('mlt_site_settings');
    sec_assert(
        $current['heroTitle'] !== 'ATTACKER_OVERWRITE',
        'Non-admin without manage_options must not be able to modify global site settings'
    );

    // Admin with manage_options
    test_set_current_user_can('manage_options', true);
    mlt_save_site_settings_from_request([
        'heroTitle' => 'AUTHORIZED_TITLE',
    ]);

    $updated = get_option('mlt_site_settings');
    sec_assert(
        $updated['heroTitle'] === 'AUTHORIZED_TITLE',
        'Admin with manage_options must be able to modify global site settings'
    );
};

$SECURITY_TESTS['test_honeypot_and_time_trap_bounds'] = function (): void {
    test_reset_wp_state();

    $now_ms = 1700000000000;
    $challenge = mlt_create_contact_challenge($now_ms);

    // Fast submission (< 4 seconds)
    $fast_input = [
        'ts'      => (string)$now_ms,
        'seed'    => $challenge['seed'],
        'sig'     => $challenge['sig'],
        'jsToken' => mlt_build_js_token((string)$now_ms, $challenge['seed']),
    ];
    $fast_res = mlt_verify_challenge_and_interaction($fast_input, $now_ms + 2000);
    sec_assert($fast_res === false, 'Submission under 4s must be rejected');

    // Valid submission (10 seconds)
    $valid_input = [
        'ts'      => (string)$now_ms,
        'seed'    => $challenge['seed'],
        'sig'     => $challenge['sig'],
        'jsToken' => mlt_build_js_token((string)$now_ms, $challenge['seed']),
    ];
    $valid_res = mlt_verify_challenge_and_interaction($valid_input, $now_ms + 10000);
    sec_assert($valid_res === true, 'Submission after 10s must be accepted');

    // Expired submission (> 2 hours)
    $expired_challenge = mlt_create_contact_challenge($now_ms);
    $expired_input = [
        'ts'      => (string)$now_ms,
        'seed'    => $expired_challenge['seed'],
        'sig'     => $expired_challenge['sig'],
        'jsToken' => mlt_build_js_token((string)$now_ms, $expired_challenge['seed']),
    ];
    $expired_res = mlt_verify_challenge_and_interaction($expired_input, $now_ms + (3 * 3600 * 1000));
    sec_assert($expired_res === false, 'Submission over 2 hours must be rejected');
};
