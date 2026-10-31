<?php
/**
 * Plugin Name: Testmetry Site
 * Description: Light logo-palette theme for the whole site: one shared header and footer, a restyled blog and archive layout, and light styling for posts and pages. Markup and CSS only; post content, URLs and SEO tags are untouched. While TMS_LIVE is false it shows only when ?tmsite=1 is in the address, for testing.
 * Version: 1.1.1
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'TMS_LIVE', true );
define( 'TMS_DIR', plugin_dir_path( __FILE__ ) );

/* ---------- Always on (safe for everyone) ---------- */

// The menu's old Home link (?page_id=6875, the retired Elementor homepage) goes to the front page.
add_action( 'parse_request', function () {
	if ( isset( $_GET['page_id'] ) && '6875' === (string) $_GET['page_id'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( home_url( '/' ), 301, 'Testmetry Site' );
		exit;
	}
}, 0 );

// Show post featured images at their natural shape instead of cropping them into a fixed-height box.
add_action( 'wp_head', function () {
	if ( is_admin() ) { return; }
	echo '<style id="tms-fix">.elementor-widget-theme-post-featured-image img,.single .elementor-widget-image img.wp-post-image{height:auto!important;max-height:none!important;aspect-ratio:auto!important;object-fit:contain!important;width:100%!important;border-radius:10px}</style>';
}, 98 );

/* ---------- Switch ---------- */

function tms_active() {
	if ( is_admin() ) { return false; }
	if ( isset( $_GET['elementor-preview'] ) ) { return false; } // phpcs:ignore WordPress.Security.NonceVerification
	if ( TMS_LIVE ) { return true; }
	return isset( $_GET['tmsite'] ); // phpcs:ignore WordPress.Security.NonceVerification
}

add_filter( 'body_class', function ( $classes ) {
	if ( tms_active() ) { $classes[] = 'tms'; }
	return $classes;
} );

add_filter( 'show_admin_bar', function ( $show ) {
	if ( ! is_user_logged_in() && tms_active() ) { return false; }
	return $show;
}, 99 );

add_action( 'pre_get_posts', function ( $q ) {
	if ( tms_active() && $q->is_main_query() && $q->is_home() ) { $q->set( 'category__not_in', array( 1588 ) ); }
} );

add_filter( 'template_include', function ( $template ) {
	if ( tms_active() && ( is_category() || is_home() || is_tag() ) && file_exists( TMS_DIR . 'templates/archive.php' ) ) {
		return TMS_DIR . 'templates/archive.php';
	}
	return $template;
}, 99 );

/* ---------- Shared pieces ---------- */

function tms_u( $path ) { return esc_url( home_url( $path ) ); }

function tms_logo_svg( $size = 38 ) {
	return '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 64 64" aria-hidden="true" focusable="false"><rect x="6" y="3" width="52" height="38" rx="8" fill="#f6cf4b"/><text x="32" y="18" text-anchor="middle" font-family="Poppins,Arial,sans-serif" font-weight="700" font-size="11.5" letter-spacing="1.2" fill="#0b2a5b">TEST</text><polyline points="12,29 18,29 21,23 25,35 29,23 33,35 36,29 40,29 43,24 47,34 50,29 53,29" fill="none" stroke="#0b2a5b" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M24 44h16l2 4H22z" fill="#f6cf4b"/><path d="M14 51h36l5 9H9z" fill="#f6cf4b"/></svg>';
}

function tms_latest_links( $n = 2 ) {
	$q   = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => (int) $n, 'ignore_sticky_posts' => true, 'no_found_rows' => true, 'category__not_in' => array( 1588 ) ) );
	$out = '';
	foreach ( $q->posts as $p ) {
		$out .= '<a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a>';
	}
	wp_reset_postdata();
	return $out;
}

function tms_header_html() {
	ob_start();
	?>
<a class="tms-skip" href="#tms-main-start">Skip to content</a>
<header class="tms-header" id="tms-header">
<div class="tms-wrap tms-bar">
<a class="tms-logo" href="<?php echo tms_u( '/' ); ?>" aria-label="TestMetry home"><?php echo tms_logo_svg(); ?><span>TestMetry</span></a>
<nav class="tms-nav" id="tms-nav" aria-label="Main">
<div class="tms-dd"><button type="button" aria-expanded="false" aria-haspopup="true">Learn <span aria-hidden="true">&#9662;</span></button>
<div class="tms-panel tms-wide"><div class="tms-cols">
<div><p>Topics</p><a href="<?php echo tms_u( '/ai-in-automated-testing-how/' ); ?>">AI in QA</a><a href="<?php echo tms_u( '/category/test-automation/' ); ?>">Test automation</a><a href="<?php echo tms_u( '/category/blog/' ); ?>">Software testing</a><a href="<?php echo tms_u( '/category/security/' ); ?>">Security testing</a><a href="<?php echo tms_u( '/category/news/' ); ?>">News</a></div>
<div><p>Start here</p><a href="<?php echo tms_u( '/what-is-regression-testing/' ); ?>">Regression testing</a><a href="<?php echo tms_u( '/user-acceptance-testing-template/' ); ?>">User acceptance testing</a><a href="<?php echo tms_u( '/load-testing-tools/' ); ?>">Load testing tools</a><a href="<?php echo tms_u( '/sdet-guide-roles/' ); ?>">SDET career guide</a><a href="<?php echo tms_u( '/what-is-quality-assurance/' ); ?>">Quality assurance basics</a></div>
<div><p>Latest</p><?php echo tms_latest_links( 2 ); // phpcs:ignore ?><a class="tms-all" href="<?php echo tms_u( '/category/blog/' ); ?>">All blog posts</a></div>
</div></div></div>
<div class="tms-dd"><button type="button" aria-expanded="false" aria-haspopup="true">Tools <span aria-hidden="true">&#9662;</span></button>
<div class="tms-panel"><a href="<?php echo tms_u( '/random-ip-address-generator/' ); ?>">Random IP generator</a><a href="<?php echo tms_u( '/credit-card-generator/' ); ?>">Test card numbers</a><a href="https://qpulse.sbs/login" target="_blank" rel="noopener">User Story Analyzer</a></div></div>
<a href="<?php echo tms_u( '/daily-software-testing-jobs/' ); ?>">Jobs</a>
<div class="tms-dd"><button type="button" aria-expanded="false" aria-haspopup="true">About <span aria-hidden="true">&#9662;</span></button>
<div class="tms-panel"><a href="<?php echo tms_u( '/about-us/' ); ?>">About George</a><a href="<?php echo tms_u( '/software-test-consulting-session/' ); ?>">Book a session</a><a href="<?php echo tms_u( '/guest-post/' ); ?>">Guest posts</a><a href="<?php echo tms_u( '/about-us/test-in-production-e-book/' ); ?>">Free e-book</a><a href="<?php echo tms_u( '/software-testing-conferences/' ); ?>">Testing conferences</a></div></div>
</nav>
<details class="tms-search"><summary aria-label="Search"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg></summary>
<form role="search" method="get" action="<?php echo tms_u( '/' ); ?>"><label class="tms-sr" for="tms-s">Search</label><input id="tms-s" type="search" name="s" placeholder="Search the blog"><button type="submit">Search</button></form></details>
<a class="tms-sub" href="#subscribe">Subscribe</a>
<button type="button" class="tms-burger" aria-expanded="false" aria-controls="tms-nav"><span class="tms-sr">Menu</span><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
</div>
</header>
<div id="tms-main-start" tabindex="-1"></div>
<script nowprocket>
(function(){var h=document.getElementById('tms-header');if(!h)return;var b=h.querySelector('.tms-burger');
function closeDd(){h.querySelectorAll('.tms-dd.open').forEach(function(y){y.classList.remove('open');y.firstElementChild.setAttribute('aria-expanded','false');});}
b.addEventListener('click',function(){var o=h.classList.toggle('open');b.setAttribute('aria-expanded',o?'true':'false');});
h.querySelectorAll('.tms-dd>button').forEach(function(x){x.addEventListener('click',function(e){var d=x.parentNode,o=!d.classList.contains('open');closeDd();if(o){d.classList.add('open');x.setAttribute('aria-expanded','true');}e.stopPropagation();});});
document.addEventListener('click',function(e){if(!h.contains(e.target))closeDd();});
document.addEventListener('keydown',function(e){if(e.key==='Escape'){closeDd();h.classList.remove('open');}});})();
</script>
	<?php
	return ob_get_clean();
}

function tms_newsletter_form() {
	if ( shortcode_exists( 'jetpack_subscription_form' ) ) {
		return do_shortcode( '[jetpack_subscription_form title="" subscribe_text="" subscribe_placeholder="you@company.com" subscribe_button="Subscribe" show_subscribers_total="false" show_only_email_and_button="true" success_message="Thanks! Check your inbox to confirm your subscription."]' );
	}
	return '';
}

function tms_footer_html() {
	ob_start();
	?>
<footer class="tms-footer" id="tms-footer">
<div class="tms-wrap">
<div class="tms-fgrid">
<div class="tms-fbrand" id="subscribe">
<a class="tms-flogo" href="<?php echo tms_u( '/' ); ?>"><?php echo tms_logo_svg( 34 ); ?><span>TestMetry</span></a>
<p>AI software testing and test automation guides from 25+ years in quality assurance. Written by George Ukkuru.</p>
<p class="tms-fcontact"><a href="mailto:george@testmetry.com">george@testmetry.com</a><br><a href="tel:+919895780269">+91-9895780269</a></p>
<div class="tms-fnews"><p class="tms-fnews-h">The best of Testmetry, once a month.</p><?php echo tms_newsletter_form(); // phpcs:ignore ?></div>
</div>
<nav aria-label="Learn"><p>Learn</p><a href="<?php echo tms_u( '/ai-in-automated-testing-how/' ); ?>">AI in QA</a><a href="<?php echo tms_u( '/category/test-automation/' ); ?>">Test automation</a><a href="<?php echo tms_u( '/category/blog/' ); ?>">Software testing</a><a href="<?php echo tms_u( '/category/security/' ); ?>">Security testing</a><a href="<?php echo tms_u( '/category/news/' ); ?>">News</a></nav>
<nav aria-label="Free tools"><p>Free tools</p><a href="<?php echo tms_u( '/random-ip-address-generator/' ); ?>">Random IP generator</a><a href="<?php echo tms_u( '/credit-card-generator/' ); ?>">Test card numbers</a><a href="https://qpulse.sbs/login" target="_blank" rel="noopener">User Story Analyzer</a></nav>
<nav aria-label="Resources"><p>Resources</p><a href="<?php echo tms_u( '/daily-software-testing-jobs/' ); ?>">Testing jobs</a><a href="<?php echo tms_u( '/about-us/test-in-production-e-book/' ); ?>">Free e-book</a><a href="<?php echo tms_u( '/software-testing-conferences/' ); ?>">Testing conferences</a><a href="<?php echo tms_u( '/chatgpt-for-software-testing/' ); ?>">ChatGPT for testing</a></nav>
<nav aria-label="Company"><p>Company</p><a href="<?php echo tms_u( '/about-us/' ); ?>">About</a><a href="<?php echo tms_u( '/software-test-consulting-session/' ); ?>">Book a session</a><a href="<?php echo tms_u( '/guest-post/' ); ?>">Guest posts</a><a href="mailto:george@testmetry.com">Contact</a><a href="https://goo.gl/maps/xr2Ja1i2xT71pxjL6" target="_blank" rel="noopener">Our office on the map</a></nav>
</div>
<div class="tms-fbottom"><span>Copyright <?php echo esc_html( gmdate( 'Y' ) ); ?> TestMetry. All rights reserved.</span>
<span class="tms-social"><a href="https://www.linkedin.com/in/ukkuru" target="_blank" rel="noopener">LinkedIn</a><a href="https://www.instagram.com/automationhangout/" target="_blank" rel="noopener">Instagram</a><a href="https://twitter.com/testmetry" target="_blank" rel="noopener">X (Twitter)</a></span></div>
</div>
</footer>
	<?php
	return ob_get_clean();
}

/* ---------- CSS ---------- */

function tms_chrome_css() {
	return <<<'CSS'
.tms-sr{position:absolute!important;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.tms-wrap{max-width:1200px;margin:0 auto;padding:0 20px}
.tms-skip{position:absolute;left:-9999px;top:8px;background:#f6cf4b;color:#0b2a5b;padding:10px 14px;border-radius:6px;z-index:1000;font-weight:600}
.tms-skip:focus{left:12px}
.tms-header,.tms-footer{font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif;font-size:16px;line-height:1.5;-webkit-font-smoothing:antialiased}
.tms-header *,.tms-footer *{box-sizing:border-box}
.tms-header{background:#fff;border-bottom:1px solid #dbe3ef;position:relative;z-index:60}
.tms-bar{display:flex;align-items:center;gap:8px 20px;min-height:76px}
.tms-logo{display:flex;align-items:center;gap:10px;color:#0b2a5b!important;font-family:Poppins,Inter,sans-serif;font-weight:700;font-size:23px;line-height:1;text-decoration:none!important;flex:none}
.tms-nav{display:flex;align-items:center;gap:2px 6px;flex:1 1 auto}
.tms-nav>a,.tms-dd>button{font:500 15px/1.2 Inter,system-ui,sans-serif;color:#0b2a5b!important;background:none;border:0;padding:12px 12px;cursor:pointer;text-decoration:none!important;border-radius:8px;text-transform:none;letter-spacing:0;box-shadow:none}
.tms-nav>a:hover,.tms-dd>button:hover,.tms-dd.open>button{background:#eef2f8!important;color:#0b2a5b!important}
.tms-dd{position:relative}
.tms-panel{display:none;position:absolute;top:100%;left:0;min-width:250px;background:#fff;border:1px solid #dbe3ef;border-radius:14px;box-shadow:0 18px 44px rgba(11,42,91,.14);padding:12px;z-index:70}
.tms-panel a{display:block;color:#0b2a5b!important;text-decoration:none!important;padding:9px 12px;border-radius:8px;font-size:15px;line-height:1.35}
.tms-panel a:hover{background:#eef2f8}
.tms-panel p{margin:4px 12px 6px;font-size:12px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:#5b6780}
.tms-wide{width:720px}
.tms-cols{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.tms-panel a.tms-all{font-weight:600;text-decoration:underline!important}
.tms-dd.open .tms-panel{display:block}
@media (hover:hover) and (min-width:901px){.tms-dd:hover .tms-panel{display:block}}
.tms-search{position:relative;flex:none}
.tms-search summary{list-style:none;cursor:pointer;color:#0b2a5b;padding:10px;display:flex;border-radius:8px}
.tms-search summary::-webkit-details-marker{display:none}
.tms-search summary:hover{background:#eef2f8}
.tms-search form{position:absolute;right:0;top:100%;margin-top:6px;display:flex;gap:8px;background:#fff;border:1px solid #dbe3ef;border-radius:14px;box-shadow:0 18px 44px rgba(11,42,91,.14);padding:12px;width:340px;z-index:70}
.tms-search input[type=search]{flex:1;min-width:0;height:44px;padding:0 12px;border:1px solid #c7d2e3;border-radius:10px;font-size:15px;color:#0b2a5b;background:#fff}
.tms-search button{height:44px;padding:0 16px;border:0;border-radius:10px;background:#0b2a5b;color:#fff;font-weight:600;cursor:pointer}
.tms-sub{flex:none;background:#f6cf4b;color:#0b2a5b!important;text-decoration:none!important;font-weight:600;font-size:15px;padding:12px 20px;border-radius:10px;line-height:1.2}
.tms-sub:hover{background:#ffdb6a}
.tms-burger{display:none;background:none;border:1px solid #c7d2e3;border-radius:10px;color:#0b2a5b;padding:9px;cursor:pointer}
.tms-header a:focus-visible,.tms-header button:focus-visible,.tms-footer a:focus-visible,.tms-header summary:focus-visible{outline:3px solid #0b2a5b;outline-offset:2px}
.tms-footer a:focus-visible{outline-color:#f6cf4b}
@media (max-width:900px){
.tms-bar{flex-wrap:wrap;min-height:68px}
.tms-logo{margin-right:auto}
.tms-burger{display:flex;order:5}
.tms-sub{order:4;padding:10px 14px;font-size:14px}
.tms-search{order:3}
.tms-nav{display:none;order:9;flex:1 0 100%;flex-direction:column;align-items:stretch;padding:8px 0 14px;border-top:1px solid #dbe3ef}
.tms-header.open .tms-nav{display:flex}
.tms-nav>a,.tms-dd>button{text-align:left;width:100%;padding:14px 8px;font-size:16px}
.tms-panel,.tms-wide{position:static;display:none;width:auto;min-width:0;box-shadow:none;border:0;border-left:3px solid #f6cf4b;border-radius:0;margin:0 0 8px 8px;padding:4px 0 4px 8px}
.tms-dd.open .tms-panel{display:block}
.tms-cols{grid-template-columns:1fr}
.tms-search form{position:fixed;left:12px;right:12px;width:auto;top:70px}
}
.tms-search,.tms-search summary{border:0!important;box-shadow:none!important;background:none;margin:0;min-width:0;width:auto;height:auto}
.tms-search summary{padding:10px!important}
.tms-header .tms-search{padding:0!important;margin:0!important}.tms-header .tms-logo{margin-bottom:0!important}
.gtranslate_wrapper,html body div.gt_switcher_wrapper{position:fixed!important;top:auto!important;right:auto!important;bottom:14px!important;left:14px!important;z-index:40!important}
@media (max-width:900px){.tms-bar{padding-left:14px!important;padding-right:14px!important}.tms-logo svg{width:30px;height:30px}.tms-sub{padding:9px 12px!important}.tms-logo{font-size:20px!important}.tms-bar{gap:8px 8px!important}.tms-sub{white-space:nowrap}}
.tms-footer{background:#0b2a5b;color:#d3dbe8;margin-top:56px}
.tms-footer .tms-wrap{padding-top:56px;padding-bottom:28px}
.tms-fgrid{display:grid;grid-template-columns:minmax(260px,2fr) repeat(4,minmax(140px,1fr));gap:36px}
.tms-flogo{display:flex;align-items:center;gap:10px;color:#fff!important;text-decoration:none!important;font-family:Poppins,Inter,sans-serif;font-weight:700;font-size:22px;margin-bottom:14px}
.tms-fbrand p{margin:0 0 14px;color:#d3dbe8;font-size:15px;line-height:1.65;max-width:420px}
.tms-fcontact a{color:#f6cf4b!important;text-decoration:none}
.tms-fnews .tms-fnews-h{margin:18px 0 10px;color:#fff;font-weight:600}
.tms-fnews form{display:flex;gap:8px;flex-wrap:wrap;margin:0;max-width:400px}
.tms-fnews form p{margin:0;max-width:none}
.tms-fnews form p:has(input[type=email]){flex:1 1 200px}
.tms-fnews input[type=email]{width:100%;height:46px;padding:0 14px;border:1px solid #3a5a96;background:#0a2350;color:#fff;border-radius:10px;font-size:15px}
.tms-fnews input[type=email]::placeholder{color:#a8b4c9}
.tms-fnews button[type=submit]{height:46px;padding:0 18px;border:0;border-radius:10px;background:#f6cf4b!important;color:#0b2a5b!important;font-weight:600;font-size:15px;cursor:pointer}
.tms-fnews .success,.tms-fnews .error{color:#d3dbe8;font-size:14px}
.tms-footer nav p{margin:0 0 8px;color:#fff;font-weight:600}
.tms-footer nav a{display:block;color:#d3dbe8!important;text-decoration:none!important;padding:5px 0;font-size:15px}
.tms-footer nav a:hover{color:#f6cf4b!important;text-decoration:underline!important}
.tms-fbottom{display:flex;flex-wrap:wrap;justify-content:space-between;gap:12px 24px;border-top:1px solid #2a4a85;margin-top:40px;padding-top:22px;font-size:14px;color:#a8b4c9}
.tms-social{display:flex;gap:20px;flex-wrap:wrap}
.tms-social a{color:#d3dbe8!important;text-decoration:underline!important;text-underline-offset:3px}
@media (max-width:900px){.tms-fgrid{grid-template-columns:repeat(2,minmax(0,1fr))}.tms-fbrand{grid-column:1/-1}}
@media (max-width:480px){.tms-fgrid{grid-template-columns:1fr}}
CSS;
}

function tms_content_css() {
	return <<<'CSS'
body.tms:not(.tmh-body){--e-global-color-primary:#0b2a5b;--e-global-color-secondary:#5b6780;--e-global-color-text:#334155;--e-global-color-accent:#f6cf4b;background:#fff!important;color:#334155!important}
body.tms:not(.tmh-body) .elementor-widget-theme-post-content,body.tms:not(.tmh-body) .elementor-widget-theme-post-content p,body.tms:not(.tmh-body) .elementor-widget-theme-post-content li,body.tms:not(.tmh-body) .elementor-widget-text-editor,body.tms:not(.tmh-body) .entry-content p,body.tms:not(.tmh-body) .entry-content li,body.tms:not(.tmh-body) td{color:#334155}
body.tms:not(.tmh-body) h1,body.tms:not(.tmh-body) h2,body.tms:not(.tmh-body) h3,body.tms:not(.tmh-body) h4,body.tms:not(.tmh-body) h5,body.tms:not(.tmh-body) h6,body.tms:not(.tmh-body) .elementor-heading-title,body.tms:not(.tmh-body) .entry-title{color:#0b2a5b!important;font-family:Poppins,Inter,sans-serif}
body.tms:not(.tmh-body) .elementor-widget-theme-post-content a,body.tms:not(.tmh-body) .entry-content a,body.tms:not(.tmh-body) .elementor-widget-text-editor a{color:#0b2a5b!important;text-decoration:underline!important;text-underline-offset:3px}
body.tms:not(.tmh-body) .elementor-widget-theme-post-content a:hover,body.tms:not(.tmh-body) .entry-content a:hover{background:#fdf1c4}
body.tms:not(.tmh-body) .elementor-breadcrumbs a,body.tms:not(.tmh-body) .elementor-breadcrumbs span,body.tms:not(.tmh-body) .rank-math-breadcrumb a{color:#0b2a5b!important}
body.tms .elementor-breadcrumbs,body.tms .elementor-breadcrumbs *,body.tms [class*=breadcrumb] *{color:#5b6780!important}
body.tms .elementor-breadcrumbs a,body.tms .elementor-icon-list-item a,body.tms .elementor-icon-list-text a{color:#0b2a5b!important}
body.tms .jp-relatedposts-post-date,body.tms .jp-relatedposts-post-context{color:#475569!important;opacity:1!important}
body.tms .tms-lightify,body.tms .tms-lightify>.elementor-container,body.tms .tms-lightify .elementor-background-overlay{background:#fff!important;background-image:none!important}
body.tms .tms-lightify .elementor-background-overlay{display:none!important}
body.tms .gt_switcher_wrapper,body.tms .gtranslate_wrapper{position:fixed!important;top:auto!important;right:auto!important;bottom:14px!important;left:14px!important;z-index:40}
body.tms:not(.tmh-body) .elementor-post__title a,body.tms:not(.tmh-body) h2 a,body.tms:not(.tmh-body) h3 a{color:#0b2a5b!important;text-decoration:none}
body.tms:not(.tmh-body) .elementor-post__read-more{color:#0b2a5b!important;font-weight:600;text-decoration:underline}
body.tms:not(.tmh-body) .elementor-element-3a714203{background-color:#f6cf4b!important}
body.tms:not(.tmh-body) .elementor-element-3a714203 .elementor-heading-title{color:#0b2a5b!important}
body.tms:not(.tmh-body) .elementor-icon-list-icon i,body.tms:not(.tmh-body) .elementor-icon-list-icon svg,body.tms:not(.tmh-body) .elementor-post-info__item-prefix,body.tms:not(.tmh-body) .elementor-icon i,body.tms:not(.tmh-body) .elementor-icon svg{color:#0b2a5b!important;fill:#0b2a5b!important}
body.tms:not(.tmh-body) .elementor-post-info__item,body.tms:not(.tmh-body) .elementor-post-date,body.tms:not(.tmh-body) time{color:#5b6780!important}
body.tms:not(.tmh-body) .elementor-share-btn,body.tms:not(.tmh-body) .elementor-share-btn__icon{background-color:#0b2a5b!important}
body.tms:not(.tmh-body) .elementor-share-btn__icon i,body.tms:not(.tmh-body) .elementor-share-btn__icon svg{color:#fff!important;fill:#fff!important}
body.tms:not(.tmh-body) .elementor-social-icon{background-color:#0b2a5b!important}
body.tms:not(.tmh-body) .elementor-divider-separator,body.tms:not(.tmh-body) hr{border-color:#dbe3ef!important}
body.tms:not(.tmh-body) #ez-toc-container{background:#f5f7fa!important;border:1px solid #dbe3ef!important;border-radius:12px;box-shadow:none!important}
body.tms:not(.tmh-body) #ez-toc-container a,body.tms:not(.tmh-body) #ez-toc-container .ez-toc-title{color:#0b2a5b!important;text-decoration:none!important}
body.tms:not(.tmh-body) #ez-toc-container a:hover{text-decoration:underline!important}
body.tms:not(.tmh-body) .ez-toc-btn{background:transparent!important;border-color:#c7d2e3!important}
body.tms:not(.tmh-body) button,body.tms:not(.tmh-body) input[type=submit],body.tms:not(.tmh-body) .elementor-button,body.tms:not(.tmh-body) .wp-block-button__link{background-color:#f6cf4b!important;color:#0b2a5b!important;border-color:#f6cf4b!important}
body.tms:not(.tmh-body) .tms-header button,body.tms:not(.tmh-body) .tms-footer button{background-color:revert-layer}
body.tms:not(.tmh-body) .tms-header .tms-dd>button,body.tms:not(.tmh-body) .tms-header .tms-burger{background:none!important;color:#0b2a5b!important;border-color:transparent!important}
body.tms:not(.tmh-body) .tms-header .tms-burger{border-color:#c7d2e3!important}
body.tms:not(.tmh-body) .tms-header .tms-search button{background:#0b2a5b!important;color:#fff!important}
body.tms:not(.tmh-body) input[type=text],body.tms:not(.tmh-body) input[type=email],body.tms:not(.tmh-body) input[type=search],body.tms:not(.tmh-body) textarea,body.tms:not(.tmh-body) select{background:#fff!important;color:#0b2a5b!important;border:1px solid #c7d2e3!important}
body.tms:not(.tmh-body) thead th,body.tms:not(.tmh-body) th{background:#eef2f8!important;color:#0b2a5b!important}
body.tms:not(.tmh-body) table,body.tms:not(.tmh-body) td,body.tms:not(.tmh-body) th{border-color:#dbe3ef!important}
body.tms:not(.tmh-body) blockquote{border-left:4px solid #f6cf4b!important}
body.tms:not(.tmh-body) ::selection{background:#f6cf4b;color:#0b2a5b}
body.tms:not(.tmh-body) .gt_switcher_wrapper{top:4px!important}
CSS;
}

function tms_archive_css() {
	return <<<'CSS'
.tms-arch{font-family:Inter,system-ui,sans-serif;color:#334155;background:#fff}
.tms-arch *{box-sizing:border-box}
.tms-arch h1,.tms-arch h2,.tms-arch h3{font-family:Poppins,Inter,sans-serif;color:#0b2a5b;margin:0}
.tms-arch p{margin:0}
.tms-arch a{color:#0b2a5b}
.tms-ahead{padding:48px 0 24px}
.tms-crumb{font-size:14px;color:#5b6780;margin-bottom:14px}
.tms-crumb a{color:#5b6780}
.tms-ahead h1{font-size:clamp(32px,4.6vw,46px);line-height:1.12;margin-bottom:12px}
.tms-aintro{font-size:18px;line-height:1.65;color:#5b6780;max-width:700px;margin-bottom:26px}
.tms-chiprow{display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between}
.tms-chips{display:flex;flex-wrap:wrap;gap:8px}
.tms-chips a{border:1px solid #c7d2e3;color:#0b2a5b;text-decoration:none;font-size:14px;padding:9px 16px;border-radius:999px;background:#fff}
.tms-chips a:hover{border-color:#0b2a5b}
.tms-chips a.on{background:#f6cf4b;border-color:#f6cf4b;font-weight:600}
.tms-asearch input{height:44px;width:240px;max-width:100%;padding:0 14px;border:1px solid #c7d2e3;background:#fff;color:#0b2a5b;border-radius:10px;font-size:15px}
.tms-feat{display:flex;flex-wrap:wrap;text-decoration:none;color:#334155!important;background:#f5f7fa;border:1px solid #dbe3ef;border-radius:18px;overflow:hidden;margin:10px 0 8px}
.tms-feat .tms-fimg{flex:1 1 520px;min-height:300px;background:#0b2a5b;position:relative}
.tms-feat .tms-fimg img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block}
.tms-feat .tms-ftxt{flex:1 1 420px;padding:36px;display:flex;flex-direction:column;justify-content:center}
.tms-feat h2{font-size:clamp(24px,3vw,32px);line-height:1.2;margin:12px 0 14px}
.tms-feat p.ex{font-size:17px;line-height:1.65;color:#5b6780;margin-bottom:16px}
.tms-meta{font-size:13px;color:#5b6780}
.tms-badge{display:inline-block;background:#f6cf4b;color:#0b2a5b;font-size:12px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;padding:5px 10px;border-radius:999px;align-self:flex-start}
.tms-body{display:flex;flex-wrap:wrap;gap:36px;padding:24px 0 8px}
.tms-main{flex:999 1 640px;min-width:0}
.tms-side{flex:1 1 280px;min-width:0;display:flex;flex-direction:column;gap:20px;align-self:flex-start}
.tms-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:24px}
.tms-card{display:flex;flex-direction:column;text-decoration:none;color:#334155!important;background:#fff;border:1px solid #dbe3ef;border-radius:14px;overflow:hidden;transition:box-shadow .15s,border-color .15s}
.tms-card:hover{box-shadow:0 12px 30px rgba(11,42,91,.10);border-color:#b8c6dc}
.tms-cimg{aspect-ratio:16/9;background:#0b2a5b;position:relative;overflow:hidden}
.tms-cimg img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block}
.tms-cimg .tms-ph{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;opacity:.9}
.tms-cbody{padding:20px;display:flex;flex-direction:column;gap:10px;flex:1}
.tms-card h3{font-size:19px;line-height:1.3}
.tms-card .ex{font-size:15px;line-height:1.6;color:#5b6780}
.tms-card .tms-meta{margin-top:auto}
.tms-pager{display:flex;justify-content:center;gap:8px;flex-wrap:wrap;margin-top:36px}
.tms-pager a,.tms-pager span{border:1px solid #c7d2e3;color:#0b2a5b;text-decoration:none;padding:11px 18px;border-radius:10px;font-size:15px;background:#fff}
.tms-pager span.current{background:#f6cf4b;border-color:#f6cf4b;font-weight:600}
.tms-box{background:#f5f7fa;border:1px solid #dbe3ef;border-radius:14px;padding:22px}
.tms-box h2{font-size:18px;margin-bottom:12px}
.tms-box p{font-size:15px;line-height:1.55;color:#5b6780;margin-bottom:14px}
.tms-box ol{margin:0;padding-left:20px;display:flex;flex-direction:column;gap:12px;font-size:15px;line-height:1.45}
.tms-box ol a{text-decoration:none}
.tms-box ol a:hover{text-decoration:underline}
.tms-btn{display:inline-block;background:#f6cf4b;color:#0b2a5b!important;text-decoration:none;font-weight:600;font-size:15px;padding:12px 18px;border-radius:10px}
.tms-box form{display:flex;flex-direction:column;gap:10px;margin:0}
.tms-box form p{margin:0}
.tms-box input[type=email]{width:100%;height:46px;padding:0 14px;border:1px solid #c7d2e3;background:#fff;color:#0b2a5b;border-radius:10px;font-size:15px}
.tms-box button[type=submit]{height:46px;border:0;border-radius:10px;background:#f6cf4b;color:#0b2a5b;font-weight:600;font-size:15px;cursor:pointer;width:100%}
.tms-empty{padding:40px 0;font-size:18px}
CSS;
}

add_action( 'wp_head', function () {
	if ( ! tms_active() ) { return; }
	echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
	echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Poppins:wght@600;700&amp;display=swap">';
	echo '<style id="tms-css">' . tms_chrome_css() . tms_content_css() . tms_archive_css() . '</style>'; // phpcs:ignore
}, 99 );

/* ---------- Replace the old header and footer on pages the theme builds ---------- */

function tms_balanced_end( $html, $start, $tag ) {
	$depth = 0;
	$pos   = $start;
	$open  = '<' . $tag;
	$close = '</' . $tag . '>';
	while ( true ) {
		$o = stripos( $html, $open, $pos );
		$c = stripos( $html, $close, $pos );
		if ( false === $c ) { return false; }
		if ( false !== $o && $o < $c ) {
			$next = substr( $html, $o + strlen( $open ), 1 );
			if ( ctype_space( $next ) || '>' === $next ) { $depth++; }
			$pos = $o + strlen( $open );
			continue;
		}
		$depth--;
		$pos = $c + strlen( $close );
		if ( $depth <= 0 ) { return $pos; }
	}
}

function tms_replace_block( $html, $pattern, $tag, $replacement ) {
	if ( ! preg_match( $pattern, $html, $m, PREG_OFFSET_CAPTURE ) ) { return $html; }
	$start = $m[0][1];
	$end   = tms_balanced_end( $html, $start, $tag );
	if ( false === $end ) { return $html; }
	return substr( $html, 0, $start ) . $replacement . substr( $html, $end );
}

function tms_swap_chrome( $html, $header, $footer ) {
	if ( false !== strpos( $html, 'id="tms-header"' ) ) { return $html; }
	$new = tms_replace_block( $html, '#<header[^>]*id=["\']masthead["\'][^>]*>#i', 'header', $header );
	$new = tms_replace_block( $new, '#<footer[^>]*id=["\']colophon["\'][^>]*>#i', 'footer', $footer );
	return $new;
}

add_action( 'template_redirect', function () {
	if ( ! tms_active() || is_feed() || is_embed() ) { return; }
	if ( ( is_category() || is_home() || is_tag() ) ) { return; } // these use our own template
	if ( function_exists( 'tmh_is_tm_page' ) && tmh_is_tm_page() ) { return; } // the homepage plugin builds its own
	// Build the new markup now: output buffering callbacks cannot start buffers of their own.
	$header = tms_header_html();
	$footer = tms_footer_html();
	ob_start( function ( $html ) use ( $header, $footer ) {
		return tms_swap_chrome( $html, $header, $footer );
	} );
}, 0 );

/* ---------- Archive pieces ---------- */

function tms_card_category( $post_id ) {
	$cats = get_the_category( $post_id );
	foreach ( $cats as $c ) {
		if ( ! in_array( (int) $c->term_id, array( 1364, 97 ), true ) ) { return $c->name; }
	}
	return 'Blog';
}

function tms_card_image( $post_id, $size ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, $size, array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) );
	}
	return '<span class="tms-ph">' . tms_logo_svg( 64 ) . '</span>';
}


/* Elementor sections with dark backgrounds keep light text (heading colours are forced to navy elsewhere). */
add_action( 'wp_footer', function () {
	if ( ! tms_active() || is_category() || is_home() || is_tag() || is_single() ) { return; }
	if ( function_exists( 'tmh_is_tm_page' ) && tmh_is_tm_page() ) { return; }
	?>
<script nowprocket>(function(){function lum(c){var m=c.match(/[\d.]+/g);if(!m||(m.length>3&&+m[3]<0.5))return null;return 0.299*m[0]+0.587*m[1]+0.114*m[2]}
document.querySelectorAll('.elementor-section,.e-con').forEach(function(e){if(e.closest('.tms-header,.tms-footer'))return;var cs=getComputedStyle(e),l=lum(cs.backgroundColor);var img=cs.backgroundImage!=='none'&&!/gradient/.test(cs.backgroundImage);var ov=e.querySelector(':scope>.elementor-background-overlay,:scope>.elementor-container>.elementor-background-overlay');if((l!==null&&l<110)||img||ov)e.classList.add('tms-lightify')})})();</script>
<?php
}, 99 );
