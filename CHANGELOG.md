# Changelog

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
