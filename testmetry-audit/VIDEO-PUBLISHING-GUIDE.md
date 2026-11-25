# TestMetry video publishing guide

Everything below runs on the **Testmetry Videos** plugin (v2.0.0). Videos stay on Vimeo. Nothing is uploaded to the WordPress media library.

## Publish a video (5 minutes)

1. Upload the video to Vimeo. In its sharing settings, turn embedding on and keep privacy on Public.
2. Copy the Vimeo link (for example `https://vimeo.com/1237480616`). Extra text such as `?share=copy` is fine.
3. In WordPress go to **Videos > Add New**.
4. Type the **title**. It becomes the page H1.
5. In **Video details** paste the Vimeo link. Leave Duration empty; it is read from Vimeo when you save.
6. Write a **short description** in the Excerpt box and a **detailed description of about 300 words** in the main editor.
7. Pick a **Video Category** and add **Video Tags**.
8. Optional: set a **Thumbnail** (16:9 works best). If you skip it, the Vimeo thumbnail is used.
9. Optional: paste a **Transcript**, and add **Related articles** (links, comma separated).
10. Click **Publish**.

The video appears on `/videos/` and in the homepage Latest videos block straight away. WP Rocket may keep an old copy of the homepage for a short while, so purge it if you do not want to wait.

## Where things live

* Library page: **Pages > Videos**. It holds an intro paragraph and the `[tm_videos per_page="12"]` shortcode. Change 12 to show more or fewer per page.
* Homepage block: the six newest videos. Code lives in `testmetry-home` (v1.7.0).
* Menu links: header and footer in `testmetry-site` (v1.5.0).
* Single video page, cards, colours and spacing: the CSS inside `testmetry-videos.php` (search for `tmv_css`).

## How it behaves

* The Vimeo player loads only on a video page, and only after the visitor clicks the thumbnail. Nothing plays by itself on page load.
* Each video page has VideoObject structured data. The video sitemap is at `/tm-video-map.xml`. Submit it once in Google Search Console.
* Tick "Hide this video from search engines" on any video you want to keep out of Google and the sitemap.
* Free Vimeo accounts allow 3 GB of embeddable video. Past that you cannot embed new uploads without upgrading or deleting videos.
* The Dailymotion player returned "Forbidden" for the site owner, which is why the plugin uses Vimeo.
