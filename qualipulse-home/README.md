# QualiPulse homepage

Full homepage for qualipulse.tech, built from design F (Mist Review).

## Files

- `index.html`: the complete page, with styles, schema and the review animation inline
- `images/logos/`: customer logos (QAlified, OnPath Testing, Zeetius, Netra, pCloudy)
- `images/search-console-impressions.png`: cropped Search Console chart

## Before going live

1. Create `/ai-seo-services/` and `/ai-email-marketing/`. The page links to both.
2. Get written permission from each customer to show their logo.
3. Upload the images to the WordPress media library and update the `images/` paths.
4. In Rank Math, set the title and meta description to match the `<head>` of this file.
5. Rank Math already outputs Organization and WebSite schema. Only add the FAQ and service schema from this file.
6. Purge the LiteSpeed cache after publishing.

## Putting it into Elementor

- Section by section: rebuild each `<section>` with Elementor widgets, or
- One HTML widget per section: paste the section markup plus the CSS from the `<style>` block.
- The review animation (hero) must go in an HTML widget, with its CSS and the second script block.
