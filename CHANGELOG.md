# Changelog

## [0.7.1] - 2026-11-23
- Supply Let's Encrypt Gen-Y intermediate CA bundle (.github/certs/letsencrypt_geny_intermediates.pem) and effective TLS servername in .github/workflows/deploy-ftp.yml so FTPS certificate chain validation succeeds across GitHub Actions runners (closes #34).

## [0.7.0] - 2026-11-23
- Enforce strict TLS certificate and hostname validation via ssl.create_default_context() in FTPS deployment workflow .github/workflows/deploy-ftp.yml (closes #21).
- Remove unauthenticated web-initiated cutover and configuration copy from bootstrap in includes/page-setup.php and add concurrency lock (mlt_page_setup_acquire_lock()) (closes #22).
- Set explicit method="post" across all public forms (page-contact.php, page-share-your-story.php, and functions.php) to prevent PII exposure in query strings (closes #23).
- Filter REST API (rest_mlt_story_query) and public queries (pre_get_posts) to require publication consent (_mlt_consent_given === '1'), and demote unconsented stories on save in wp-admin (closes #24).
- Restrict operational PII custom post types (mlt_registration, mlt_subscriber, mlt_submission) to dedicated management capabilities mapped exclusively to the administrator role in mlt-wp-content.php (closes #25).
- Implement 7-layer abuse controls, honeypot checks, and client IP rate limiting across all public write API endpoints (/api/register, /api/subscribe, /api/share-story) in includes/api-routes.php (closes #26).
- Make form single-use replay locks (mlt_acquire_replay_lock()) and rate limit enforcement (mlt_atomic_increment()) atomic via wp_options mutex with HMAC-SHA256 IP pseudonyms (closes #27).
- Purge suppressed biker gallery photograph residuals (gallery-12.jpg, gallery-14.jpg, gallery-15.jpg, gallery-16.jpg, gallery-17.jpg) from theme media and preserve them in safe quarantine (closes #28).
- Add Kenya DPA 2019 Section 33 adult/guardian consent confirmation (isAdult) and health/child data safeguards to story intake in page-share-your-story.php and api-routes.php (closes #29).
- Align privacy policy notice with Kenya DPA 2019 statutory requirements (7-day access response, 14-day rectification/erasure, database retention disclosure) in page-privacy.php (closes #30).
- Enforce manage_options capability requirement across Homepage and global site settings in includes/admin-meta-boxes.php (closes #31).
- Add automated security unit test suite (tests/test_security.php, tests/run_security_tests.php) and CI gate in .github/workflows/php-lint.yml (closes #32).
- Complete 2026-11-23 Codex Adversarial Audit & Kenya DPA 2019 remediations (closes #20).

## [0.6.0] - 2026-11-09
- Organise `/gallery` (`page-gallery.php`, `functions.php`, `mlt-wp-content.php`) into album folder cards (`data-testid="gallery-albums"`) with breadcrumb drill-down navigation (`/gallery/mens-garage` with 64 event photographs `mlt-mg_01.jpg` … `mlt-mg_64.jpg`, and `/gallery/community-gatherings` with the 24 existing community photographs) and an Album/Folder selector in `wp-admin`.
- Add `mlt_get_past_events()` and a **Past Events** section (`data-testid="past-events-section"`) on `/events` (`page-events.php`) below **Upcoming Events** so past events remain visible on site ordered most-recent first without an active registration form (closes #18).

## [0.5.0] - 2026-11-07
- Add WordPress Admin Meta Boxes, `Settings -> MLT Site Settings` (`mlt_site_settings`), Classic Editor layout for custom post types, and custom list-table columns in `includes/admin-meta-boxes.php` so all `_mlt_*` post meta fields across Events, Resources, Stories (including Kenya DPA 2019 publication consent), Community Groups, Partners, Books & Projects, Gallery Photos, Registrations, Subscribers, and Contact Enquiries can be managed directly in `wp-admin`.

## [0.4.1] - 2026-11-06
- Add `mlt_ensure_production_db_defaults()` in `includes/page-setup.php` to update the raw `wp_options` (`siteurl` and `home`) and any stored `wp_postmeta` / `wp_posts` URLs to `https://www.menletstalk.co.ke`, and ensure `mlt_ensure_dynamic_host_wp_config()` updates existing fallback host strings to `www.menletstalk.co.ke` (closes #16).

## [0.4.0] - 2026-11-06
- Cut over the full WordPress + `mlt-wp` installation from `ericgitonga.com/mlt/html` to the main domain docroot `menletstalk.co.ke/www/html` on Angry Hosting with dynamic `WP_HOME` / `WP_SITEURL` host detection (`www.menletstalk.co.ke`, `menletstalk.co.ke`, and `mlt.ericgitonga.com`), normalize stored `_mlt_image_url` theme asset paths across domain cutovers, and update `.github/workflows/deploy-ftp.yml` to deploy `wp-content/` changes to both docroots (closes #14).

## [0.3.2] - 2026-11-06
- Remove the vertical-format video (`odek-the-conversation-is-growing.mp4`) from `/about` (`page-about.php`) so the landscape overview video (`about-men-lets-talk.mp4`) renders at full 16:9 width, keeping the vertical video exclusively on `/community` (`page-community.php`) (closes #12).

## [0.3.1] - 2026-11-05
- Update the *"The Conversation Is Growing"* copy on `/community` (`page-community.php`) to reflect that different chapters meet on different days — Nakuru on Tuesdays and Nairobi on Thursdays — alongside growing circles in Kampala and beyond (closes #10).

## [0.3.0] - 2026-11-05
- Add 12 new Men Let's Talk conversation-circle photographs (`gallery-18.jpg` through `gallery-29.jpg`, IDs `gallery-image-18` through `gallery-image-29`) to `assets/images/gallery/` and `mlt_get_default_gallery_images()`, bringing `/gallery` to 24 unique photographs with automatic deduplication by ID and URL in `mlt_get_gallery_images()` (closes #8).
- Add the web-optimised chapter-growth video `assets/media/odek-the-conversation-is-growing.mp4` (*"The Conversation Is Growing — Nairobi • Nakuru • Kampala"*) in a dedicated `data-testid="community-video-section"` section on `/community` (`page-community.php`) and alongside the overview video in *"Inside Men Let's Talk"* on `/about` (`page-about.php`) (closes #8).

## [0.2.1] - 2026-11-05
- Remove the 5 biker/motorcycle photographs (`gallery-image-12`, `gallery-image-14`, `gallery-image-15`, `gallery-image-16`, `gallery-image-17`) from the gallery list and purge any seeded CPT records for those IDs per client feedback, retaining the 12 Men Let's Talk conversation-circle photographs on `/gallery` (closes #6).

## [0.2.0] - 2026-11-05
- Add upcoming events *Men's Garage: What's Under the Hood?* (`2026-11-08` at Junction Mall, `assets/images/events/mens-garage-flier.jpg`) and *Son & Father Dinner* (`2026-12-12` at Heri Heights Hotel, Kolobot Drive, `assets/images/events/son-and-father-dinner-flier.jpg`) with full-frame portrait flier rendering on `/events` and the homepage Upcoming Events section, 17 event and biker brotherhood gallery photographs (`assets/images/gallery/gallery-01.jpg` … `gallery-17.jpg`) without caption overlays on `/gallery`, and the "Inside Men Let's Talk" video section (`assets/media/about-men-lets-talk.mp4`) on `/about` (closes #4).

## [0.1.2] - 2026-11-04
- Replace WordPress fallback favicon (`w-logo-gray-white-bg.png`) with the Men Let's Talk organization emblem (`assets/images/icon.png` `512×512` and multi-size `assets/images/favicon.ico`), add `<link rel="icon">` / `<link rel="apple-touch-icon">` tags in `header.php`, filter `get_site_icon_url` + `do_faviconico`, and emit `Organization` + `WebSite` JSON-LD (`logo`, `image`) and `og:image` in `functions.php` (#2).

## [0.1.1] - 2026-11-04
- Add Public Form Submissions toggle under **Tools > MLT Setup & Import** (`mlt_public_submissions_enabled`) and enable live visitor form submissions on `https://mlt.ericgitonga.com/`.

## [0.1.0] - 2026-11-04
- Initial WordPress theme (`mlt-wp`) and companion plugin (`mlt-wp-content`) port of Men Let's Talk (`men-lets-talk` + `mlt-cms`), complete with Sanity dataset importer, 7-layer anti-spam engine, Angry Hosting SMTP integration, and 55-test Playwright E2E parity.
