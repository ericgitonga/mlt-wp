# Changelog

## [0.1.2] - 2026-11-04
- Replace WordPress fallback favicon (`w-logo-gray-white-bg.png`) with the Men Let's Talk organization emblem (`assets/images/icon.png` `512×512` and multi-size `assets/images/favicon.ico`), add `<link rel="icon">` / `<link rel="apple-touch-icon">` tags in `header.php`, filter `get_site_icon_url` + `do_faviconico`, and emit `Organization` + `WebSite` JSON-LD (`logo`, `image`) and `og:image` in `functions.php` (#2).

## [0.1.1] - 2026-11-04
- Add Public Form Submissions toggle under **Tools > MLT Setup & Import** (`mlt_public_submissions_enabled`) and enable live visitor form submissions on `https://mlt.ericgitonga.com/`.

## [0.1.0] - 2026-11-04
- Initial WordPress theme (`mlt-wp`) and companion plugin (`mlt-wp-content`) port of Men Let's Talk (`men-lets-talk` + `mlt-cms`), complete with Sanity dataset importer, 7-layer anti-spam engine, Angry Hosting SMTP integration, and 55-test Playwright E2E parity.
