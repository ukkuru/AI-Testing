<?php
/**
 * Plugin Name: Teastory Cleanup Redirects
 * Description: 301 redirects for merged or retired posts, and rewrites internal links in post content that point at them. Deactivate to undo.
 * Version: 1.0.0
 * Author: Tea Story
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function tscr_redirects() {
	return array(
		'/2024/04/24/a-global-brew-top-tea-producers-and-their-unique-flavors' => 'https://teastory.blog/2024/05/02/top-10-tea-production-in-the-world-you-need-to-know-about/',
		'/2024/04/25/exploring-the-legacy-of-the-biggest-tea-producer-in-the-world' => 'https://teastory.blog/2024/04/25/exploring-the-realm-of-the-world-largest-tea-producer/',
		'/2024/04/28/exploring-the-world-largest-producer-of-tea' => 'https://teastory.blog/2024/04/25/exploring-the-realm-of-the-world-largest-tea-producer/',
		'/2024/04/28/the-largest-tea-exporter-in-the-world' => 'https://teastory.blog/2024/04/26/top-12-biggest-tea-exporter-in-the-world-2024/',
		'/2024/05/05/boba-tea-origin-the-story-of-its-creation-and-rise-to-fame' => 'https://teastory.blog/2024/05/13/the-boba-history-how-bubble-tea-became-a-global-phenomenon/',
		'/2024/05/05/bubble-tea-origin-what-is-it-and-where-did-it-come-from' => 'https://teastory.blog/2024/05/13/the-boba-history-how-bubble-tea-became-a-global-phenomenon/',
		'/2024/05/10/bubble-tea-history-a-fascinating-journey' => 'https://teastory.blog/2024/05/13/the-boba-history-how-bubble-tea-became-a-global-phenomenon/',
		'/2025/06/19/butterfly-pea-flower-tea-benefits-for-mind-and-body' => 'https://teastory.blog/2025/10/12/blue-flower-tea-benefits-uses-and-brewing-guide/',
		'/2025/10/05/brewing-the-queen-of-hills-darjeeling-heritage-flavor' => 'https://teastory.blog/2025/10/05/ultimate-travel-guide-to-darjeeling-hill-station/',
		'/2025/10/05/green-tea-price-guide-factors-quality-and-smart-buying' => 'https://teastory.blog/2025/10/12/best-green-tea-brands-for-flavor-health-and-sustainability/',
		'/2025/10/05/inspirational-tea-quotes-in-english-for-mindful-living' => 'https://teastory.blog/2025/10/05/inspirational-tea-quotes-for-calm-and-mindful-living/',
		'/2025/10/05/inspiring-chai-quotes-in-english-for-mindful-moments' => 'https://teastory.blog/2025/10/05/inspirational-tea-quotes-for-calm-and-mindful-living/',
		'/2025/10/05/inspiring-tea-lover-quotes-for-mindful-living' => 'https://teastory.blog/2025/10/05/inspirational-tea-quotes-for-calm-and-mindful-living/',
		'/2025/10/05/kashmiri-kahwa-tea-aroma-brewing-and-wellness-guide' => 'https://teastory.blog/2025/10/05/kashmiri-kahwa-benefits-for-health-skin-and-wellness/',
		'/2025/10/05/morning-tea-quotes-for-mindful-and-inspiring-mornings' => 'https://teastory.blog/2025/10/05/inspirational-tea-quotes-for-calm-and-mindful-living/',
		'/2025/10/05/ultimate-travel-guide-to-the-queen-of-hill-station' => 'https://teastory.blog/2025/10/05/ultimate-travel-guide-to-darjeeling-hill-station/',
		'/2025/10/06/how-to-apply-green-tea-on-face-for-glowing-skin' => 'https://teastory.blog/2025/10/06/how-to-use-green-tea-for-face-care-and-glowing-skin/',
		'/2025/10/06/how-to-make-black-tea-with-lemon-benefits-and-tips' => 'https://teastory.blog/2025/10/05/health-benefits-of-black-tea-with-lemon-explained/',
		'/2025/10/06/inspiring-cup-of-tea-quotes-for-mindful-living' => 'https://teastory.blog/2025/10/05/inspirational-tea-quotes-for-calm-and-mindful-living/',
		'/2025/10/06/jasmine-green-tea-guide-aroma-brewing-and-benefits' => 'https://teastory.blog/2025/10/05/jasmine-tea-guide-aroma-brewing-and-health-benefits/',
		'/2025/10/06/the-art-and-benefits-of-brewing-a-fresh-tea' => 'https://teastory.blog/2025/10/12/the-art-and-benefits-of-a-fresh-tea/',
		'/2025/10/12/black-tea-with-lemon-benefits-tradition-and-wellness' => 'https://teastory.blog/2025/10/05/health-benefits-of-black-tea-with-lemon-explained/',
		'/2025/10/12/blue-pea-tea-guide-benefits-brewing-and-wellness-tips' => 'https://teastory.blog/2025/10/12/blue-flower-tea-benefits-uses-and-brewing-guide/',
		'/2025/10/12/chamomile-tea-price-guide-2024-trends-and-buying-tips' => 'https://teastory.blog/2025/10/12/best-chamomile-tea-bags-for-daily-relaxation-and-wellness/',
		'/2025/10/12/green-tea-price-guide-2024-trends-value-and-buying-tips' => 'https://teastory.blog/2025/10/12/best-green-tea-brands-for-flavor-health-and-sustainability/',
		'/2025/10/12/jasmine-green-tea-benefits-origins-and-brewing-guide' => 'https://teastory.blog/2025/10/05/jasmine-tea-guide-aroma-brewing-and-health-benefits/',
		'/2025/10/12/kahwa-tea-origins-benefits-and-brewing-tradition' => 'https://teastory.blog/2025/10/05/kashmiri-kahwa-benefits-for-health-skin-and-wellness/',
		'/2025/10/12/mindful-tea-quotes-for-serenity-and-wellbeing' => 'https://teastory.blog/2025/10/05/inspirational-tea-quotes-for-calm-and-mindful-living/',
		'/2025/10/13/best-green-tea-brands-in-india-for-health-and-wellness-2' => 'https://teastory.blog/2025/10/06/best-green-tea-brands-in-india-for-health-and-wellness/',
		'/2025/10/13/blue-tea-guide-benefits-origin-taste-and-brewing-tips' => 'https://teastory.blog/2025/10/12/blue-flower-tea-benefits-uses-and-brewing-guide/',
		'/2025/10/13/blue-tea-shark-tank-success-story-and-brand-journey' => 'https://teastory.blog/2025/10/14/blue-tea-shark-tank-journey-wellness-innovation-growth/',
		'/2025/10/13/chamomile-benefits-uses-and-honest-wellness-insights' => 'https://teastory.blog/2025/10/14/chamomile-tea-benefits-reviews-and-wellness-uses/',
		'/2025/10/13/honey-citrus-mint-tea-starbucks-recipe-easy-diy-guide' => 'https://teastory.blog/2025/10/13/honey-citrus-mint-tea-guide-benefits-flavor-brewing-tips/',
		'/2025/10/13/how-to-brew-the-perfect-cup-of-tea' => 'https://teastory.blog/2024/05/01/the-art-of-brewing-a-beginner-guide-to-making-tea-process/',
		'/2025/10/13/mint-majesty-tea-recipe-and-its-shocking-secret-unveiled' => 'https://teastory.blog/2025/10/13/mint-majesty-tea-benefits-flavor-and-brewing-guide/',
		'/2025/10/14/blue-tea-guide-benefits-brewing-tips-honest-reviews' => 'https://teastory.blog/2025/10/12/blue-flower-tea-benefits-uses-and-brewing-guide/',
		'/2025/10/14/the-ultimate-guide-to-a-perfect-cup-of-tea' => 'https://teastory.blog/2024/05/01/the-art-of-brewing-a-beginner-guide-to-making-tea-process/',
		'/2025/10/26/does-cobizco-green-tea-break-a-fast-or-support-it' => 'https://teastory.blog/2025/10/26/does-green-tea-break-a-fast-cobizco-and-fasting-explained/',
		'/2025/10/26/does-green-tea-break-a-fast-and-is-it-hydrating' => 'https://teastory.blog/2025/10/26/does-green-tea-break-a-fast-cobizco-and-fasting-explained/',
		'/2025/10/26/does-herbal-tea-dehydrate-you-or-hydrate-your-body' => 'https://teastory.blog/2025/10/26/is-herbal-tea-a-diuretic-benefits-of-lee-mf-rhino-tea/',
		'/2025/10/26/does-herbal-tea-stain-teeth-while-aiding-water-retention' => 'https://teastory.blog/2025/10/26/does-colon-cleanser-herbal-tea-stain-teeth/',
		'/2025/10/26/does-lee-mf-rhino-herbal-tea-dehydrate-or-hydrate-you' => 'https://teastory.blog/2025/10/26/is-herbal-tea-a-diuretic-benefits-of-lee-mf-rhino-tea/',
		'/2025/10/26/is-herbal-tea-a-natural-diuretic-for-your-advent-calendar' => 'https://teastory.blog/2025/10/26/is-herbal-tea-a-diuretic-benefits-of-lee-mf-rhino-tea/',
		'/2025/10/26/kusumi-tea-a-blend-of-heritage-wellness-and-artistry' => 'https://teastory.blog/2025/10/26/kusumi-tea-heritage-wellness-and-modern-artistry/',
		'/2025/10/26/kusumi-tea-where-french-artistry-meets-russian-tradition' => 'https://teastory.blog/2025/10/26/kusumi-tea-heritage-wellness-and-modern-artistry/',
		'/2025/10/31/camellia-sinensis-the-plant-behind-all-tea-varieties' => 'https://teastory.blog/2025/10/31/camellia-sinensis-the-plant-behind-every-cup-of-tea/',
		'/2025/10/31/does-herbal-tea-count-as-water-intake-peppermint-benefits' => 'https://teastory.blog/2025/10/26/is-drinking-herbal-tea-the-same-as-drinking-water/',
		'/2025/10/31/does-peppermint-tea-count-as-water-intake-benefits-explained' => 'https://teastory.blog/2025/11/11/peppermint-tea-does-it-count-towards-daily-hydration/',
		'/2025/10/31/find-the-best-herbal-store-near-you-for-camellia-sinensis' => 'https://teastory.blog/2025/10/31/camellia-sinensis-the-plant-behind-every-cup-of-tea/',
		'/2025/10/31/herbal-tea-and-hydration-soil-water-and-nutrient-impact' => 'https://teastory.blog/2025/11/11/hydration-and-soil-tips-for-herbal-tea-enthusiasts/',
		'/2025/11/02/camellia-sinensis-the-essence-of-tea-and-its-benefits' => 'https://teastory.blog/2025/10/31/camellia-sinensis-the-plant-behind-every-cup-of-tea/',
		'/2025/11/02/does-herbal-tea-count-toward-your-daily-water-intake' => 'https://teastory.blog/2025/10/26/is-drinking-herbal-tea-the-same-as-drinking-water/',
		'/2025/11/02/find-the-best-herbal-store-for-camellia-sinensis-teas' => 'https://teastory.blog/2025/10/31/camellia-sinensis-the-plant-behind-every-cup-of-tea/',
		'/2025/11/02/is-green-tea-good-for-pimples-and-clear-skin' => 'https://teastory.blog/2025/10/06/how-to-use-green-tea-for-face-care-and-glowing-skin/',
		'/2025/11/02/is-green-tea-good-for-pimples-and-daily-skin-health' => 'https://teastory.blog/2025/10/06/how-to-use-green-tea-for-face-care-and-glowing-skin/',
		'/2025/11/02/what-is-the-unhealthiest-tea-to-drink-for-your-health' => 'https://teastory.blog/2025/11/11/exploring-the-unhealthiest-teas-and-choosing-healthier-options/',
		'/2025/11/04/exploring-camellia-sinensis-the-heart-of-tea-varieties' => 'https://teastory.blog/2025/10/31/camellia-sinensis-the-plant-behind-every-cup-of-tea/',
		'/2025/11/04/exploring-camellia-sinensis-your-guide-to-herbal-stores' => 'https://teastory.blog/2025/10/31/camellia-sinensis-the-plant-behind-every-cup-of-tea/',
		'/2025/11/04/local-herbal-stores-a-guide-to-quality-camellia-sinensis' => 'https://teastory.blog/2025/10/31/camellia-sinensis-the-plant-behind-every-cup-of-tea/',
		'/2025/11/04/peppermint-tea-hydration-benefits-health-perks' => 'https://teastory.blog/2025/11/11/peppermint-tea-does-it-count-towards-daily-hydration/',
		'/2025/11/11/ultimate-guide-to-camellia-sinensis-and-local-herbal-stores' => 'https://teastory.blog/2025/10/31/camellia-sinensis-the-plant-behind-every-cup-of-tea/',
		'/best-energizing-herbal-infusions' => 'https://teastory.blog/2025/11/02/healthiest-herbal-teas-benefits-antioxidants-and-healing/',
		'/best-teas-for-intermittent-fasting' => 'https://teastory.blog/2025/10/26/does-herbal-tea-break-a-fast-or-affect-fasting-benefits/',
		'/discovering-fine-tea-traditions' => 'https://teastory.blog/2024/05/03/steeped-in-tradition-exploring-the-rich-tea-cultures-of-the-world/',
		'/how-to-store-loose-leaf-tea-fresh' => 'https://teastory.blog/2025/11/11/maximizing-loose-leaf-tea-freshness-and-longevity/',
		'/tea-meditation-rituals' => 'https://teastory.blog/2025/10/14/the-complete-guide-to-mindful-tea-time-rituals/',
	);
}

function tscr_removed() {
	return array(
		'/2024/04/02/beyond-the-obstacle',
		'/2024/04/02/collaboration-magic',
		'/2024/04/02/growth-unlocked',
		'/2024/04/02/teamwork-triumphs',
		'/2024/04/02/the-art-of-connection',
		'/2024/07/14/the-latest-scoop-on-your-shopping-cart-with-golden-tips-tea',
		'/2024/07/17/an-invisible-symphony-of-tags-and-prices',
		'/2024/07/17/reaching-out-the-tale-of-golden-tips-tea',
		'/2025/04/29/unlock-radiant-skin-with-innisfree-green-tea-seed-serum',
		'/2025/10/05/perfect-caption-for-tea-lover-tips-for-authentic-expression',
		'/2025/10/05/tea-captions-for-social-media-mindful-and-authentic-ideas',
		'/2025/10/06/disha-patani-abs-workout-and-diet-secrets-explained',
		'/2025/10/06/disha-patani-fitness-guide-workouts-diet-and-lifestyle',
		'/2025/10/06/refreshing-quotes-to-inspire-creativity-and-authentic-content',
		'/2025/10/06/what-sattu-is-made-of-ingredients-benefits-and-uses',
		'/2025/10/12/fuse-chocolate-guide-taste-benefits-and-pairing-tips',
		'/2025/10/12/how-to-design-a-memorable-tea-shop-logo',
		'/2025/10/12/tea-earn-guide-mindful-living-and-sustainable-profit',
		'/2025/10/13/authentic-chimichurri-recipe-secrets-for-perfect-flavor',
		'/2025/10/13/chai-ai-where-tradition-meets-technology-in-every-cup',
		'/2025/10/13/chai-latte-rezept-geschichte-geschmack-und-zubereitung',
		'/2025/10/13/chai-sirup-between-tradition-taste-and-modern-convenience',
		'/2025/10/13/chai-sirup-rezept-guide-spice-balance-and-flavor-mastery',
		'/2025/10/13/chai-tee-taste-guide-love-it-or-not-your-cup-of-tea',
		'/2025/10/13/chai-thee-why-this-spiced-tea-divides-drink-lovers',
		'/2025/10/13/easy-tea-biscuit-recipe-with-a-surprising-secret-ingredient',
		'/2025/10/13/grey-fruit-guide-nutrition-benefits-and-culinary-uses',
		'/2025/10/13/lee-chai-min-the-polarizing-tea-loved-and-hated-worldwide',
		'/2025/10/13/magic-mushroom-tea-recipe-origins-safety-and-mindful-brewing',
		'/2025/10/13/openai-news-today-latest-updates-reviews-and-insights',
		'/2025/10/13/passion-fruit-syrup-tropical-flavor-and-health-benefits',
		'/2025/10/13/perfecting-the-chai-latte-recept-spice-balance-and-culture',
		'/2025/10/13/secrets-to-the-perfect-apple-crumble-recipe',
		'/2025/10/13/shocking-secrets-behind-the-traditional-irish-tea-brack-recipe',
		'/2025/10/13/shocking-secrets-to-the-perfect-cinnamon-tea-cake-recipe',
		'/2025/10/13/spicy-chat-guide-authentic-flavors-recipes-tea-pairing',
		'/2025/10/13/the-love-hate-flavors-of-wan-chai-hong-kongs-culinary-soul',
		'/2025/10/13/the-secret-to-perfect-tea-cake-cookies-recipe-revealed',
		'/2025/10/14/ashwagandha-price-guide-2024-global-trends-and-value-tips',
		'/2025/10/14/tea-tree-oil-benefits-uses-and-honest-reviews-guide',
	);
}

add_action( 'template_redirect', function () {
	if ( is_admin() ) { return; }
	$path = untrailingslashit( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ) );
	$map  = tscr_redirects();
	if ( isset( $map[ $path ] ) ) {
		wp_safe_redirect( $map[ $path ], 301, 'Teastory Cleanup' );
		exit;
	}
}, 1 );

add_filter( 'the_content', function ( $content ) {
	if ( false === strpos( $content, 'teastory.blog' ) ) { return $content; }
	$map     = tscr_redirects();
	$removed = array_flip( tscr_removed() );
	return preg_replace_callback(
		'#<a\s[^>]*href=(["\'])(https?://(?:www\.)?teastory\.blog(/[^"\'?\#]*)[^"\']*)\1[^>]*>(.*?)</a>#is',
		function ( $m ) use ( $map, $removed ) {
			$p = untrailingslashit( $m[3] );
			if ( isset( $map[ $p ] ) ) {
				return str_replace( $m[2], $map[ $p ], $m[0] );
			}
			if ( isset( $removed[ $p ] ) ) {
				return $m[4];
			}
			return $m[0];
		},
		$content
	);
}, 20 );
