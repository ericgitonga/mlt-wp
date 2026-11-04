#!/usr/bin/env bash
# Bootstraps a local WordPress instance with SQLite to preview and run E2E tests against mlt-wp.
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RUNTIME_DIR="$REPO_DIR/.wp-runtime"
PORT="${MLT_WP_PORT:-8890}"
URL="http://localhost:${PORT}"

mkdir -p "$RUNTIME_DIR"

if [ ! -f "$RUNTIME_DIR/wp-load.php" ]; then
	if [ -f "/home/gitonga/Develop/projects/sef-wp/.wp-runtime/wp-load.php" ]; then
		cp -r /home/gitonga/Develop/projects/sef-wp/.wp-runtime/. "$RUNTIME_DIR/"
		rm -f "$RUNTIME_DIR/wp-config.php"
		rm -rf "$RUNTIME_DIR/wp-content/database"
	else
		curl -fsSL --proto '=https' --tlsv1.2 https://wordpress.org/latest.zip -o "$RUNTIME_DIR/wp-core.zip"
		unzip -q "$RUNTIME_DIR/wp-core.zip" -d "$RUNTIME_DIR/_extract"
		cp -r "$RUNTIME_DIR/_extract/wordpress/." "$RUNTIME_DIR/"
		rm -rf "$RUNTIME_DIR/_extract" "$RUNTIME_DIR/wp-core.zip"
	fi
fi

WPCLI="$RUNTIME_DIR/wp-cli.phar"
if [ ! -f "$WPCLI" ]; then
	curl -fsSL --proto '=https' --tlsv1.2 https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o "$WPCLI"
	chmod +x "$WPCLI"
fi
wp() { php "$WPCLI" --path="$RUNTIME_DIR" --allow-root "$@"; }

SQLITE_PLUGIN_DIR="$RUNTIME_DIR/wp-content/plugins/sqlite-database-integration"
if [ ! -d "$SQLITE_PLUGIN_DIR" ]; then
	curl -fsSL --proto '=https' --tlsv1.2 https://downloads.wordpress.org/plugin/sqlite-database-integration.latest-stable.zip -o "$RUNTIME_DIR/sqlite.zip"
	unzip -q "$RUNTIME_DIR/sqlite.zip" -d "$RUNTIME_DIR/wp-content/plugins/"
	rm -f "$RUNTIME_DIR/sqlite.zip"
fi
if [ ! -f "$RUNTIME_DIR/wp-content/db.php" ]; then
	cp "$SQLITE_PLUGIN_DIR/db.copy" "$RUNTIME_DIR/wp-content/db.php"
fi

mkdir -p "$RUNTIME_DIR/wp-content/themes" "$RUNTIME_DIR/wp-content/plugins"
ln -sfn "$REPO_DIR/wp-content/themes/mlt-wp" "$RUNTIME_DIR/wp-content/themes/mlt-wp"
ln -sfn "$REPO_DIR/wp-content/plugins/mlt-wp-content" "$RUNTIME_DIR/wp-content/plugins/mlt-wp-content"

if [ ! -f "$RUNTIME_DIR/wp-config.php" ]; then
	wp config create --dbname=mlt_wp --dbuser=unused --dbpass=unused --skip-check --force
	wp config set WP_HOME "$URL" --type=constant
	wp config set WP_SITEURL "$URL" --type=constant
fi

if ! wp core is-installed 2>/dev/null; then
	wp core install \
		--url="$URL" \
		--title="Men Let's Talk" \
		--admin_user=admin \
		--admin_password=admin \
		--admin_email=admin@example.com \
		--skip-email
fi

wp theme activate mlt-wp
wp plugin activate mlt-wp-content
wp eval-file "$REPO_DIR/local-preview/seed.php"

cp "$REPO_DIR/local-preview/router.php" "$RUNTIME_DIR/router.php"
php -S "127.0.0.1:${PORT}" -t "$RUNTIME_DIR" "$RUNTIME_DIR/router.php"
