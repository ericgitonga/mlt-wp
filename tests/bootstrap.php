<?php
/**
 * Test bootstrap & lightweight WordPress API double for pure unit testing.
 * Provides in-memory options, post meta, capabilities, and hashing
 * without requiring a running web server or MySQL connection.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../');
}

// Global in-memory storage for test isolation
$GLOBALS['_test_options'] = [];
$GLOBALS['_test_post_meta'] = [];
$GLOBALS['_test_posts'] = [];
$GLOBALS['_test_current_user_caps'] = [];
$GLOBALS['_test_registered_cpts'] = [];
$GLOBALS['_test_role_caps'] = [];

function test_reset_wp_state(): void {
    $GLOBALS['_test_options'] = [];
    $GLOBALS['_test_post_meta'] = [];
    $GLOBALS['_test_posts'] = [];
    $GLOBALS['_test_current_user_caps'] = [];
    $GLOBALS['_test_registered_cpts'] = [];
    $GLOBALS['_test_role_caps'] = [];
}

if (!function_exists('get_option')) {
    function get_option(string $key, $default = false) {
        return $GLOBALS['_test_options'][$key] ?? $default;
    }
}

if (!function_exists('update_option')) {
    function update_option(string $key, $value, $autoload = null): bool {
        $GLOBALS['_test_options'][$key] = $value;
        return true;
    }
}

if (!function_exists('add_option')) {
    function add_option(string $key, $value = '', string $deprecated = '', $autoload = 'yes'): bool {
        if (array_key_exists($key, $GLOBALS['_test_options'])) {
            return false;
        }
        $GLOBALS['_test_options'][$key] = $value;
        return true;
    }
}

if (!function_exists('delete_option')) {
    function delete_option(string $key): bool {
        if (array_key_exists($key, $GLOBALS['_test_options'])) {
            unset($GLOBALS['_test_options'][$key]);
            return true;
        }
        return false;
    }
}

if (!function_exists('get_transient')) {
    function get_transient(string $transient) {
        return get_option('_transient_' . $transient);
    }
}

if (!function_exists('set_transient')) {
    function set_transient(string $transient, $value, int $expiration = 0): bool {
        update_option('_transient_' . $transient, $value);
        if ($expiration > 0) {
            update_option('_transient_timeout_' . $transient, time() + $expiration);
        }
        return true;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can(string $capability, ...$args): bool {
        return !empty($GLOBALS['_test_current_user_caps'][$capability]);
    }
}

if (!function_exists('test_set_current_user_can')) {
    function test_set_current_user_can(string $capability, bool $can): void {
        $GLOBALS['_test_current_user_caps'][$capability] = $can;
    }
}

if (!function_exists('get_post_meta')) {
    function get_post_meta(int $post_id, string $key = '', bool $single = false) {
        if (!isset($GLOBALS['_test_post_meta'][$post_id])) {
            return $single ? '' : [];
        }
        if ($key === '') {
            return $GLOBALS['_test_post_meta'][$post_id];
        }
        if (!isset($GLOBALS['_test_post_meta'][$post_id][$key])) {
            return $single ? '' : [];
        }
        return $single ? $GLOBALS['_test_post_meta'][$post_id][$key] : [$GLOBALS['_test_post_meta'][$post_id][$key]];
    }
}

if (!function_exists('update_post_meta')) {
    function update_post_meta(int $post_id, string $key, $value, $prev_value = ''): bool {
        if (!isset($GLOBALS['_test_post_meta'][$post_id])) {
            $GLOBALS['_test_post_meta'][$post_id] = [];
        }
        $GLOBALS['_test_post_meta'][$post_id][$key] = $value;
        return true;
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field(string $str): string {
        $filtered = strip_tags($str);
        $filtered = preg_replace('/[\r\n\t ]+/', ' ', $filtered);
        return trim((string)$filtered);
    }
}

if (!function_exists('sanitize_textarea_field')) {
    function sanitize_textarea_field(string $str): string {
        return trim(strip_tags($str));
    }
}

if (!function_exists('sanitize_email')) {
    function sanitize_email(string $email): string {
        return filter_var($email, FILTER_SANITIZE_EMAIL) ?: '';
    }
}

if (!function_exists('wp_unslash')) {
    function wp_unslash($val) {
        return is_string($val) ? stripslashes($val) : $val;
    }
}

if (!function_exists('wp_salt')) {
    function wp_salt(string $scheme = 'auth'): string {
        return 'mlt_test_secret_salt_1234567890';
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) {
        return 1;
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, int $options = 0): string {
        return json_encode($data, $options);
    }
}

if (!function_exists('wp_kses_post')) {
    function wp_kses_post(string $str): string {
        return strip_tags($str, '<p><a><br><strong><em>');
    }
}

if (!function_exists('wp_strip_all_tags')) {
    function wp_strip_all_tags(string $str): string {
        return strip_tags($str);
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr(string $str): string {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html')) {
    function esc_html(string $str): string {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url(string $str): string {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url_raw')) {
    function esc_url_raw(string $url): string {
        return filter_var($url, FILTER_SANITIZE_URL) ?: '';
    }
}

if (!function_exists('checked')) {
    function checked($checked, $current = true, bool $echo = true): string {
        $result = ((string)$checked === (string)$current) ? "checked='checked'" : '';
        if ($echo) {
            echo $result;
        }
        return $result;
    }
}

if (!function_exists('register_post_type')) {
    function register_post_type(string $post_type, array $args = []): void {
        $GLOBALS['_test_registered_cpts'][$post_type] = $args;
    }
}

if (!function_exists('register_taxonomy')) {
    function register_taxonomy(string $taxonomy, $object_type, array $args = []): void {}
}

if (!function_exists('wp_set_object_terms')) {
    function wp_set_object_terms($post_id, $terms, $taxonomy, $append = false) {
        return [];
    }
}

class WP_Post {
    public int $ID = 0;
    public string $post_title = '';
    public string $post_content = '';
    public string $post_status = 'publish';
    public string $post_type = 'post';
    public function __construct(int $id, string $title = '', string $content = '', string $status = 'publish', string $type = 'post') {
        $this->ID = $id;
        $this->post_title = $title;
        $this->post_content = $content;
        $this->post_status = $status;
        $this->post_type = $type;
    }
}

if (!function_exists('get_post')) {
    function get_post($post = null) {
        if ($post instanceof WP_Post) {
            return $post;
        }
        $id = (int)$post;
        return $GLOBALS['_test_posts'][$id] ?? null;
    }
}

if (!function_exists('get_post_status')) {
    function get_post_status($post = null): string {
        $p = get_post($post);
        return $p ? $p->post_status : '';
    }
}

if (!function_exists('wp_update_post')) {
    function wp_update_post(array $postarr = []): int {
        $id = $postarr['ID'] ?? 0;
        if (isset($GLOBALS['_test_posts'][$id])) {
            if (isset($postarr['post_status'])) {
                $GLOBALS['_test_posts'][$id]->post_status = $postarr['post_status'];
            }
        }
        return $id;
    }
}

if (!function_exists('wp_is_post_revision')) {
    function wp_is_post_revision($post): bool {
        return false;
    }
}

class WP_Role {
    public string $name = '';
    public array $capabilities = [];
    public function __construct(string $name) {
        $this->name = $name;
    }
    public function add_cap(string $cap, bool $grant = true): void {
        $this->capabilities[$cap] = $grant;
        $GLOBALS['_test_role_caps'][$this->name][$cap] = $grant;
    }
    public function has_cap(string $cap): bool {
        return !empty($this->capabilities[$cap]);
    }
}

if (!function_exists('get_role')) {
    function get_role(string $role): ?WP_Role {
        static $roles = [];
        if (!isset($roles[$role])) {
            $roles[$role] = new WP_Role($role);
        }
        return $roles[$role];
    }
}

if (!function_exists('add_action')) {
    function add_action(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): bool {
        return true;
    }
}

if (!function_exists('remove_action')) {
    function remove_action(string $hook_name, callable $callback, int $priority = 10): bool {
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): bool {
        return true;
    }
}

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path(string $file): string {
        return dirname($file) . '/';
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url(string $file): string {
        return 'https://example.com/wp-content/plugins/mlt-wp-content/';
    }
}

if (!defined('MINUTE_IN_SECONDS')) define('MINUTE_IN_SECONDS', 60);
if (!defined('HOUR_IN_SECONDS')) define('HOUR_IN_SECONDS', 3600);
if (!defined('DAY_IN_SECONDS')) define('DAY_IN_SECONDS', 86400);

class Test_WPDB {
    public string $options = 'wp_options';
    public function prepare(string $query, ...$args): string {
        foreach ($args as $arg) {
            $val = is_numeric($arg) ? $arg : "'" . addslashes((string)$arg) . "'";
            $query = preg_replace('/%[sd]/', (string)$val, $query, 1);
        }
        return $query;
    }
    public function query(string $query) {
        if (preg_match("/UPDATE .* SET option_value = option_value \+ 1 WHERE option_name = '([^']+)'/", $query, $m)) {
            $key = $m[1];
            $current = (int)($GLOBALS['_test_options'][$key] ?? 0);
            $GLOBALS['_test_options'][$key] = $current + 1;
        }
        return 1;
    }
    public function get_var(string $query) {
        if (preg_match("/SELECT option_value FROM .* WHERE option_name = '([^']+)'/", $query, $m)) {
            $key = $m[1];
            return $GLOBALS['_test_options'][$key] ?? null;
        }
        return null;
    }
}

global $wpdb;
$wpdb = new Test_WPDB();

class WP_REST_Request {}
