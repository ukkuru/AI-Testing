# TestMetry video publishing guide

Everything below runs on the **Testmetry Videos** plugin (v1.0.3). Videos stay on Dailymotion. Nothing is uploaded to the WordPress media library.

## Publish a video (5 minutes)

1. Upload the video to Dailymotion and copy the share link (for example `https://dai.ly/xbzhxle`).
2. In WordPress go to **Videos > Add New**.
3. Type the **title**.
4. In **Video details** paste the Dailymotion URL. Leave Duration empty; it is read from Dailymotion when you save.
5. Write the **short description** in the Excerpt box and the **detailed description** in the main editor.
6. Pick a **Video Category** and add **Video Tags**.
7. Optional: set a **Thumbnail** (16:9 works best). If you skip it, the Dailymotion thumbnail is used.
8. Optional: paste a **Transcript** and, in the Rank Math box, an SEO title and description.
9. Click **Publish**.

The video appears on `/videos/` and in the homepage Latest videos block straight away. WP Rocket may keep an old copy of the homepage for a short while. Purge it if you do not want to wait.

## What the fields do

| Field | Where it shows |
| --- | --- |
| Title | Page heading, cards, schema |
| Dailymotion URL | Click to play player, schema embedUrl |
| Thumbnail | Cards, player poster, schema thumbnailUrl |
| Excerpt | Short description under the player, schema description |
| Main editor | Detailed description |
| Duration | Card badge, video page, schema duration (auto if empty) |
| Transcript | Collapsible block on the video page, schema transcript |
| Related articles | Three article links. Leave empty and the page picks blog posts that share a tag name with the video tags |
| Hide from search engines | Adds noindex and removes the video from the video sitemap |

## Where things live

* Library page: **Pages > Videos**. It holds a normal paragraph and the `[tm_videos per_page="12"]` shortcode. Edit the intro text freely. Change 12 to show more or fewer per page.
* Homepage block: shows the six newest videos. Code lives in `testmetry-home` (v1.7.0) and calls the Videos plugin.
* Menu links: header and footer in `testmetry-site` (v1.5.0).
* Single video page, cards, colours and spacing: the CSS block inside `testmetry-videos.php` (search for `tmv_css`). They use the same navy and yellow as the rest of the site.

## Limits to know

* The video page, library and homepage block are not drag and drop in Elementor. They are plugin templates styled to match the site. Wording around them (library intro, titles, descriptions) is fully editable.
* Category filtering on `/videos/` works through links such as `/videos/?vcat=qa-tips`. Videos have no separate category archive pages by design.
* The player loads only after a visitor clicks the thumbnail on a video page. Dailymotion may show its own ads or branding.
* The video sitemap is at `/tm-video-map.xml`. WordPress.com controls robots.txt, so submit that URL in Google Search Console under Sitemaps once.
* Rank Math handles the SEO title, description and the normal page sitemap for videos. This plugin adds VideoObject schema and the video sitemap.

## Quick checks after publishing

1. Open the video page. Click the thumbnail. The Dailymotion player should load inside the page and play.
2. Test the page URL in Google's Rich Results Test and look for **Video**.
3. In Search Console use URL Inspection and request indexing for the new video page.

## If the player shows "Forbidden"

Dailymotion's default embed address can refuse to load. Create a player in Dailymotion Studio (Settings, Players), add `testmetry.com` and `www.testmetry.com` to its allowed domains, then paste its ID under **Videos > Settings** in WordPress. Every video page also carries a "Watch it on Dailymotion" link as a fallback.
