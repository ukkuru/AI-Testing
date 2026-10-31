<?php
/**
 * Plugin Name: Testmetry Home
 * Description: The testmetry.com homepage, rendered by the [tm_home] shortcode with its own CSS. Deactivate and set the front page back to Elementor to undo.
 * Version: 1.3.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'TMH_PREVIEW_SLUG', 'home-preview' );

function tmh_is_tm_page() {
	if ( ! is_singular() ) { return false; }
	$post = get_post();
	return $post && has_shortcode( $post->post_content, 'tm_home' );
}

function tmh_is_preview() {
	if ( ! is_singular() ) { return false; }
	$post = get_post();
	return $post && TMH_PREVIEW_SLUG === $post->post_name;
}

// The old Elementor homepage (/home/) is kept as a draft for rollback; send its address to the front page.
add_action( 'parse_request', function () {
	$path = untrailingslashit( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ) );
	if ( '/home' === $path ) { wp_safe_redirect( home_url( '/' ), 301, 'Testmetry Home' ); exit; }
}, 0 );

// Keep the test copy out of search engines and the sitemap.
add_action( 'send_headers', function () {
	if ( tmh_is_preview() ) { header( 'X-Robots-Tag: noindex, nofollow', true ); }
} );
add_filter( 'rank_math/frontend/robots', function ( $robots ) {
	if ( tmh_is_preview() ) { $robots['index'] = 'noindex'; $robots['follow'] = 'nofollow'; }
	return $robots;
} );
add_filter( 'rank_math/sitemap/entry', function ( $url, $type, $object ) {
	if ( 'post' === $type && is_object( $object ) && isset( $object->post_name ) && TMH_PREVIEW_SLUG === $object->post_name ) { return false; }
	return $url;
}, 10, 3 );

add_action( 'wp_head', function () {
	if ( ! tmh_is_tm_page() ) { return; }
	echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
	echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&amp;family=JetBrains+Mono&amp;family=Poppins:wght@600;700&amp;display=swap">';
	echo '<style id="tmh-css">' . tmh_css() . '</style>'; // phpcs:ignore
}, 5 );

function tmh_css() {
	return <<<'CSS'
body.tmh-body{margin:0!important;background:#0a1530!important}
.tmh{--bg:#0a1530;--bg2:#0e1d3f;--card:#12234a;--line:#22345e;--text:#e8edf6;--muted:#a8b4c9;--yellow:#f6cf4b;--navy:#0b2a5b;--pass:#4ade80;
background:var(--bg);color:var(--text);font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif;font-size:17px;line-height:1.6;-webkit-font-smoothing:antialiased}
.tmh *,.tmh *::before,.tmh *::after{box-sizing:border-box}
.tmh a{color:var(--yellow);text-decoration:none}
.tmh a:hover{text-decoration:underline}
.tmh p a,.tmh details a{text-decoration:underline;text-underline-offset:3px}
.tmh a:focus-visible,.tmh button:focus-visible,.tmh input:focus-visible,.tmh summary:focus-visible{outline:3px solid var(--yellow);outline-offset:3px}
.tmh h1,.tmh h2,.tmh h3{font-family:Poppins,Inter,sans-serif;color:#fff;margin:0;letter-spacing:-0.01em}
.tmh p{margin:0}
.tmh .tmh-wrap{max-width:1200px;margin:0 auto;padding:0 20px}
.tmh .tmh-skip{position:absolute;left:-9999px;top:8px;background:var(--yellow);color:var(--navy);padding:10px 14px;border-radius:6px;z-index:100}
.tmh .tmh-skip:focus{left:12px}
.tmh .tmh-sr{position:absolute!important;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.tmh header.tmh-top{border-bottom:1px solid var(--line);padding-top:30px}
#grow-me-in-content-recs-root{display:none!important}
.tmh .tmh-bar{display:flex;align-items:center;justify-content:space-between;gap:12px 24px;flex-wrap:wrap;padding-top:14px;padding-bottom:14px}
.tmh .tmh-logo{display:flex;align-items:center;gap:10px;color:#fff;font-family:Poppins,sans-serif;font-weight:700;font-size:23px;line-height:1}
.tmh .tmh-logo:hover{text-decoration:none}
.tmh .tmh-nav{display:flex;gap:6px 22px;flex-wrap:wrap;font-size:15px}
.tmh .tmh-nav a{color:var(--text);padding:8px 0}
.tmh .tmh-btn{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:12px 22px;border-radius:10px;font-weight:600;font-size:16px;line-height:1.2;border:1px solid transparent;cursor:pointer}
.tmh .tmh-btn:hover{text-decoration:none}
.tmh .tmh-btn-y{background:var(--yellow);color:var(--navy)}
.tmh .tmh-btn-y:hover{background:#ffe07a;color:var(--navy)}
.tmh .tmh-btn-o{border-color:#3a4f80;color:#fff}
.tmh .tmh-btn-o:hover{border-color:var(--yellow);color:#fff}
.tmh .tmh-hero{display:flex;flex-wrap:wrap;gap:40px;align-items:center;padding-top:56px;padding-bottom:64px}
.tmh .tmh-hero-copy{flex:1 1 480px;min-width:0}
.tmh .tmh-eyebrow{font-family:"JetBrains Mono",ui-monospace,monospace;font-size:13px;letter-spacing:.06em;color:var(--yellow);margin-bottom:14px}
.tmh h1{font-size:clamp(34px,5.2vw,54px);line-height:1.1;font-weight:700;margin-bottom:18px}
.tmh .tmh-lead{font-size:clamp(17px,2vw,19px);color:var(--muted);margin-bottom:26px;max-width:620px}
.tmh .tmh-ctas{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px}
.tmh form.tmh-search{display:flex;gap:8px;flex-wrap:wrap;max-width:560px}
.tmh .tmh-search input{flex:1 1 240px;min-height:48px;padding:0 14px;border:1px solid #3a4f80;background:var(--bg2);color:#fff;border-radius:10px;font-size:16px;font-family:inherit}
.tmh .tmh-search input::placeholder{color:#8f9cb5}
.tmh .tmh-search button{min-height:48px;padding:0 20px;border:1px solid #3a4f80;border-radius:10px;background:var(--card);color:#fff;font-weight:600;font-size:16px;font-family:inherit;cursor:pointer}
.tmh .tmh-term{flex:1 1 420px;min-width:0;background:#0d1a38;border:1px solid var(--line);border-radius:14px;overflow:hidden;box-shadow:0 24px 60px rgba(0,0,0,.4)}
.tmh .tmh-term-bar{display:flex;gap:8px;align-items:center;padding:12px 14px;border-bottom:1px solid var(--line)}
.tmh .tmh-dot{width:11px;height:11px;border-radius:50%;background:#3a4f80}
.tmh .tmh-term-file{margin-left:8px;font-family:"JetBrains Mono",monospace;font-size:12px;color:#8f9cb5}
.tmh .tmh-term-body{padding:18px 20px;font-family:"JetBrains Mono",ui-monospace,monospace;font-size:14px;line-height:2;color:var(--text);overflow-x:auto}
.tmh .tmh-term-body p{white-space:nowrap}
.tmh .t-cmd{color:#8f9cb5}.tmh .t-pass{color:var(--pass)}.tmh .t-rev{color:var(--yellow)}.tmh .t-fail{color:#ff8a8a}
@keyframes tmhline{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
.tmh .tmh-l{opacity:0;animation:tmhline .4s ease-out forwards}
.tmh .tmh-l1{animation-delay:.2s}.tmh .tmh-l2{animation-delay:.8s}.tmh .tmh-l3{animation-delay:1.4s}.tmh .tmh-l4{animation-delay:2s}.tmh .tmh-l5{animation-delay:2.6s}.tmh .tmh-l6{animation-delay:3.3s}
@keyframes tmhblink{50%{opacity:0}}
.tmh .tmh-cursor{animation:tmhblink 1s steps(1) infinite}
@media (prefers-reduced-motion:reduce){.tmh .tmh-l{opacity:1;animation:none}.tmh .tmh-cursor{animation:none}}
.tmh .tmh-band{background:var(--bg2);border-top:1px solid var(--line);border-bottom:1px solid var(--line)}
.tmh .tmh-author{display:flex;gap:18px;align-items:center;flex-wrap:wrap;padding-top:22px;padding-bottom:22px}
.tmh .tmh-author img{width:56px;height:56px;border-radius:50%;flex:none;background:#fff}
.tmh .tmh-author p{flex:1 1 420px;color:#d3dbe8;font-size:16px}
.tmh .tmh-author strong a{color:#fff}
.tmh section.tmh-sec{padding-top:64px;padding-bottom:24px}
.tmh .tmh-sec-head{display:flex;justify-content:space-between;align-items:flex-end;gap:12px 24px;flex-wrap:wrap;margin-bottom:10px}
.tmh h2{font-size:clamp(27px,3.4vw,36px);line-height:1.2;font-weight:700}
.tmh .tmh-intro{color:var(--muted);max-width:760px;margin-bottom:26px}
.tmh .tmh-more{font-weight:600;font-size:15px}
.tmh .tmh-feature-row{display:flex;flex-wrap:wrap;gap:22px}
.tmh .tmh-feature{flex:2 1 520px;min-width:0;display:block;background:var(--card);border:1px solid var(--line);border-radius:16px;overflow:hidden;color:var(--text)}
.tmh .tmh-feature img{display:block;width:100%;height:auto;aspect-ratio:1734/907;object-fit:cover;background:#0d1a38}
.tmh .tmh-feature-body{padding:22px}
.tmh .tmh-kicker{font-size:13px;font-weight:600;letter-spacing:.05em;color:var(--yellow);margin-bottom:8px}
.tmh .tmh-feature h3{font-size:clamp(22px,2.6vw,28px);line-height:1.25;margin-bottom:10px}
.tmh .tmh-feature p.tmh-desc{color:var(--muted)}
.tmh .tmh-side{flex:1 1 320px;min-width:0;display:flex;flex-direction:column;gap:14px}
.tmh .tmh-mini{display:block;background:var(--card);border:1px solid var(--line);border-radius:14px;padding:18px 20px;color:var(--text)}
.tmh .tmh-mini span{display:block;font-size:13px;color:var(--muted);margin-bottom:4px}
.tmh .tmh-mini h3{font-family:Inter,sans-serif;font-size:18px;font-weight:600;line-height:1.4;color:#fff}
.tmh .tmh-feature:hover,.tmh .tmh-mini:hover,.tmh .tmh-tile:hover,.tmh .tmh-tool:hover{border-color:var(--yellow);text-decoration:none}
.tmh .tmh-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
.tmh .tmh-tile{display:block;padding:22px;border-radius:12px;border:1px solid var(--line);background:var(--bg2);color:var(--text)}
.tmh .tmh-tile h3{font-size:20px;margin-bottom:6px}
.tmh .tmh-tile p{font-size:15px;color:var(--muted)}
.tmh .tmh-tools{background:var(--bg2);border-top:1px solid var(--line);border-bottom:1px solid var(--line);margin-top:48px;padding-bottom:64px}
.tmh .tmh-tool{display:block;background:var(--card);border:1px solid var(--line);border-radius:14px;padding:24px;color:var(--text)}
.tmh .tmh-tool h3{font-size:19px;margin:14px 0 6px}
.tmh .tmh-tool p{font-size:15px;color:var(--muted);margin-bottom:14px}
.tmh .tmh-tool code{display:block;background:#0a1530;border-radius:8px;padding:10px 12px;font-family:"JetBrains Mono",monospace;font-size:13px;color:#d3dbe8;line-height:1.7;white-space:pre}
.tmh .tmh-tool .tmh-ext{font-size:13px;color:var(--muted)}
.tmh .tmh-split{display:flex;flex-wrap:wrap;gap:36px;padding-top:64px;padding-bottom:24px}
.tmh .tmh-latest{flex:999 1 560px;min-width:0}
.tmh .tmh-latest ul{list-style:none;margin:22px 0 0;padding:0}
.tmh .tmh-latest li{border-bottom:1px solid var(--line)}
.tmh .tmh-latest li a{display:flex;justify-content:space-between;gap:16px;padding:16px 0;color:var(--text);font-weight:600;font-size:18px}
.tmh .tmh-latest time{flex:none;font-weight:400;font-size:14px;color:var(--muted)}
.tmh .tmh-jobs{flex:1 1 320px;min-width:0;align-self:flex-start;background:var(--card);border:1px solid var(--line);border-radius:16px;padding:24px}
.tmh .tmh-jobs h2{font-size:22px;margin-bottom:8px}
.tmh .tmh-jobs p{font-size:15px;color:var(--muted);margin-bottom:16px}
.tmh .tmh-jobs p.tmh-guest{margin:18px 0 0}
.tmh .tmh-faq{max-width:900px}
.tmh details{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:16px 20px;margin-top:12px}
.tmh summary{cursor:pointer;font-weight:600;font-size:18px;color:#fff;min-height:28px}
.tmh details p{margin-top:12px;color:#d3dbe8}
.tmh .tmh-news{background:var(--bg2);border-top:1px solid var(--line);margin-top:64px}
.tmh .tmh-news-in{display:flex;flex-wrap:wrap;gap:24px 40px;align-items:center;justify-content:space-between;padding-top:48px;padding-bottom:48px}
.tmh .tmh-news-copy{flex:1 1 420px;min-width:0}
.tmh .tmh-news-copy h2{font-size:clamp(24px,3vw,28px);margin-bottom:8px}
.tmh .tmh-news-copy p{color:var(--muted)}
.tmh .tmh-news-form{flex:1 1 360px;min-width:0}
.tmh .tmh-news-form .wpcf7 p{display:flex;gap:8px;flex-wrap:wrap;margin:0}
.tmh .tmh-news-form .wpcf7-form-control-wrap{flex:1 1 220px}
.tmh .tmh-news-form input[type=email]{width:100%;min-height:50px;padding:0 14px;border:1px solid #3a4f80;background:#0a1530;color:#fff;border-radius:10px;font-size:16px;font-family:inherit}
.tmh .tmh-news-form input[type=email]::placeholder{color:#8f9cb5}
.tmh .tmh-news-form input[type=submit]{min-height:50px;padding:0 22px;border:0;border-radius:10px;background:var(--yellow);color:var(--navy);font-weight:600;font-size:16px;font-family:inherit;cursor:pointer}
.tmh .tmh-news-form .wpcf7-response-output{margin:10px 0 0;color:#d3dbe8;font-size:14px;border-color:#3a4f80}
.tmh .tmh-news-form .wpcf7-not-valid-tip{color:#ffb4b4;font-size:14px}
.tmh footer.tmh-foot{border-top:1px solid var(--line)}
.tmh .tmh-foot-in{display:flex;flex-wrap:wrap;gap:12px 32px;justify-content:space-between;align-items:center;padding-top:28px;padding-bottom:28px;font-size:14px;color:var(--muted)}
.tmh .tmh-foot nav{display:flex;gap:6px 20px;flex-wrap:wrap}
.tmh .tmh-foot nav a{color:#d3dbe8;padding:6px 0}
@media (max-width:640px){.tmh .tmh-nav{width:100%;gap:2px 18px}.tmh .tmh-bar>.tmh-btn{display:none}.tmh .tmh-hero{padding-top:36px}.tmh .tmh-ctas .tmh-btn{flex:1 1 100%}.tmh .tmh-latest li a{flex-direction:column;gap:4px}}
CSS;
}

// Logged-out visitors should not see the BuddyPress login toolbar on the homepage.
add_filter( 'show_admin_bar', function ( $show ) {
	if ( ! is_user_logged_in() && tmh_is_tm_page() ) { return false; }
	return $show;
}, 99 );

// The homepage does not use page builder widgets, so skip their assets here only.
function tmh_unneeded_src( $src ) {
	return is_string( $src ) && preg_match( '#/wp-content/plugins/(elementskit-lite|elementor|elementor-pro|header-footer-elementor|premium-addons-for-elementor|widgetkit-for-elementor|feedzy-rss-feeds|otter-blocks|pdf-embedder|pdf-viewer-for-elementor|reading-progress-bar|buddypress)/|/wp-content/uploads/elementor/|/plugins/gutenberg/build/styles/block-library|/wp-includes/css/dist/block-library|/wp-includes/js/mediaelement/#', $src );
}
function tmh_dequeue_unneeded() {
	if ( is_admin() || ! tmh_is_tm_page() ) { return; }
	foreach ( array( wp_styles(), wp_scripts() ) as $deps ) {
		foreach ( (array) $deps->queue as $handle ) {
			$item = isset( $deps->registered[ $handle ] ) ? $deps->registered[ $handle ] : null;
			if ( $item && tmh_unneeded_src( $item->src ) ) {
				if ( $deps instanceof WP_Styles ) { wp_dequeue_style( $handle ); } else { wp_dequeue_script( $handle ); }
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'tmh_dequeue_unneeded', 9999 );
add_action( 'template_redirect', function () {
	if ( ! tmh_is_tm_page() ) { return; }
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
} );
add_action( 'wp_print_styles', 'tmh_dequeue_unneeded', 0 );
add_action( 'wp_print_scripts', 'tmh_dequeue_unneeded', 0 );
add_action( 'wp_print_footer_scripts', 'tmh_dequeue_unneeded', 0 );

add_filter( 'body_class', function ( $classes ) {
	if ( tmh_is_tm_page() ) { $classes[] = 'tmh-body'; }
	return $classes;
} );

function tmh_logo_svg() {
	return '<svg width="38" height="38" viewBox="0 0 64 64" aria-hidden="true" focusable="false"><rect x="6" y="3" width="52" height="38" rx="8" fill="#f6cf4b"/><text x="32" y="18" text-anchor="middle" font-family="Poppins,Arial,sans-serif" font-weight="700" font-size="11.5" letter-spacing="1.2" fill="#0b2a5b">TEST</text><polyline points="12,29 18,29 21,23 25,35 29,23 33,35 36,29 40,29 43,24 47,34 50,29 53,29" fill="none" stroke="#0b2a5b" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M24 44h16l2 4H22z" fill="#f6cf4b"/><path d="M14 51h36l5 9H9z" fill="#f6cf4b"/></svg>';
}

function tmh_icon( $name ) {
	$p = array(
		'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
		'card'  => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h4"/>',
		'story' => '<path d="M5 4h10l4 4v12H5z"/><path d="M14 4v5h5M8 13h8M8 17h5"/>',
	);
	return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#f6cf4b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $p[ $name ] . '</svg>';
}

function tmh_latest_posts() {
	$q = new WP_Query( array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 4,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'category__not_in'    => array( 1588 ),
	) );
	$out = '';
	foreach ( $q->posts as $p ) {
		$out .= '<li><a href="' . esc_url( get_permalink( $p ) ) . '"><span>' . esc_html( get_the_title( $p ) ) . '</span><time datetime="' . esc_attr( get_the_date( 'c', $p ) ) . '">' . esc_html( get_the_date( 'j M Y', $p ) ) . '</time></a></li>';
	}
	return $out;
}

add_shortcode( 'tm_home', function () {
	$u    = function ( $path ) { return esc_url( home_url( $path ) ); };
	$img  = 'https://testmetry.com/wp-content/uploads/2026/10/Test-Case-Design-with-AI.png';
	$face = 'https://testmetry.com/wp-content/uploads/2022/12/hv-round-1663607056705-1.png';
	$news = shortcode_exists( 'contact-form-7' ) ? do_shortcode( '[contact-form-7 id="1439"]' ) : '';
	ob_start();
	?>
<div class="tmh">
<header class="tmh-top">
<a class="tmh-skip" href="#tmh-main">Skip to content</a>
<div class="tmh-wrap tmh-bar">
<a class="tmh-logo" href="<?php echo $u( '/' ); ?>" aria-label="TestMetry home"><?php echo tmh_logo_svg(); ?><span>TestMetry</span></a>
<nav class="tmh-nav" aria-label="Main">
<a href="#ai-guides">AI in QA</a>
<a href="#fundamentals">Fundamentals</a>
<a href="#tools">Tools</a>
<a href="<?php echo $u( '/daily-software-testing-jobs/' ); ?>">Jobs</a>
<a href="<?php echo $u( '/about-us/' ); ?>">About</a>
</nav>
<a class="tmh-btn tmh-btn-y" href="#subscribe">Subscribe</a>
</div>
</header>

<main id="tmh-main">
<div class="tmh-wrap tmh-hero">
<div class="tmh-hero-copy">
<p class="tmh-eyebrow">AI SOFTWARE TESTING FOR QA TEAMS</p>
<h1>AI Software Testing and Test Automation, Explained by a QA Practitioner</h1>
<p class="tmh-lead">Practical software testing guides from 25+ years in QA. Learn where AI test automation genuinely helps, where AI QA still needs a human check, and the fundamentals every tester needs.</p>
<div class="tmh-ctas">
<a class="tmh-btn tmh-btn-y" href="#ai-guides">Start with AI test automation</a>
<a class="tmh-btn tmh-btn-o" href="#tools">Free QA automation tools</a>
</div>
<form class="tmh-search" role="search" method="get" action="<?php echo $u( '/' ); ?>">
<label class="tmh-sr" for="tmh-s">Search software testing guides</label>
<input id="tmh-s" type="search" name="s" placeholder="Search guides, e.g. regression testing">
<button type="submit">Search</button>
</form>
</div>
<div class="tmh-term" role="img" aria-label="A test run where AI written tests pass, three cases are flagged for review and one fails because the AI skipped the 3D Secure path.">
<div class="tmh-term-bar" aria-hidden="true"><span class="tmh-dot"></span><span class="tmh-dot"></span><span class="tmh-dot"></span><span class="tmh-term-file">ai-generated.spec.ts</span></div>
<div class="tmh-term-body" aria-hidden="true">
<p class="tmh-l tmh-l1 t-cmd">$ npx playwright test checkout</p>
<p class="tmh-l tmh-l2"><span class="t-pass">PASS</span> pays with Visa test card</p>
<p class="tmh-l tmh-l3"><span class="t-pass">PASS</span> handles insufficient funds</p>
<p class="tmh-l tmh-l4"><span class="t-rev">REVIEW</span> 3 AI written cases assert nothing</p>
<p class="tmh-l tmh-l5"><span class="t-fail">FAIL</span> AI skipped the 3D Secure path</p>
<p class="tmh-l tmh-l6">AI writes tests. You decide what is tested.<span class="tmh-cursor">_</span></p>
</div>
</div>
</div>

<div class="tmh-band">
<div class="tmh-wrap tmh-author">
<img src="<?php echo esc_url( $face . '?w=112' ); ?>" width="56" height="56" alt="George Ukkuru" loading="lazy" decoding="async">
<p>Written by <strong><a href="<?php echo $u( '/about-us/' ); ?>">George Ukkuru</a></strong>, 25+ years in quality assurance across test automation, manual testing, performance engineering and AI in QA.</p>
</div>
</div>

<section class="tmh-wrap tmh-sec" id="ai-guides" aria-labelledby="h-ai">
<div class="tmh-sec-head"><h2 id="h-ai">AI test automation guides</h2><a class="tmh-more" href="<?php echo $u( '/category/blog/' ); ?>">Browse all guides</a></div>
<p class="tmh-intro">AI in test automation can write cases in seconds. These guides show how to check them, where AI software testing saves real time, and where it quietly misses risk.</p>
<div class="tmh-feature-row">
<a class="tmh-feature" href="<?php echo $u( '/test-case-design-ai/' ); ?>">
<img src="<?php echo esc_url( $img . '?w=760' ); ?>" srcset="<?php echo esc_url( $img . '?w=480' ); ?> 480w, <?php echo esc_url( $img . '?w=760' ); ?> 760w, <?php echo esc_url( $img . '?w=1100' ); ?> 1100w" sizes="(max-width: 800px) 100vw, 760px" width="760" height="398" alt="" loading="lazy" decoding="async">
<div class="tmh-feature-body"><p class="tmh-kicker">AI IN QA</p><h3>Test Case Design With AI: 15 Checks Before You Trust It</h3><p class="tmh-desc">Give a model a user story and it returns thirty cases. Here is how to tell which ones are worth keeping.</p></div>
</a>
<div class="tmh-side">
<a class="tmh-mini" href="<?php echo $u( '/ai-in-automated-testing-how/' ); ?>"><span>AI test automation</span><h3>How AI is changing automated testing</h3></a>
<a class="tmh-mini" href="<?php echo $u( '/chatgpt-for-software-testing/' ); ?>"><span>AI QA in practice</span><h3>ChatGPT for software testing</h3></a>
<a class="tmh-mini" href="<?php echo $u( '/customer-experience-testing/' ); ?>"><span>Opinion</span><h3>Customer Experience Testing: Are We Building Agents Faster Than We Listen?</h3></a>
</div>
</div>
</section>

<section class="tmh-wrap tmh-sec" id="fundamentals" aria-labelledby="h-fun">
<h2 id="h-fun">Software testing fundamentals</h2>
<p class="tmh-intro" style="margin-top:10px">AI only helps if the basics are solid. Start with the software testing topics teams search for most.</p>
<div class="tmh-grid">
<a class="tmh-tile" href="<?php echo $u( '/what-is-regression-testing/' ); ?>"><h3>Regression testing</h3><p>What to rerun after every change, and what to automate first.</p></a>
<a class="tmh-tile" href="<?php echo $u( '/user-acceptance-testing-template/' ); ?>"><h3>User acceptance testing</h3><p>A UAT template, checklist and process that business users can follow.</p></a>
<a class="tmh-tile" href="<?php echo $u( '/load-testing-tools/' ); ?>"><h3>Load testing</h3><p>The load testing tools worth your time, compared honestly.</p></a>
<a class="tmh-tile" href="<?php echo $u( '/category/test-automation/' ); ?>"><h3>QA automation</h3><p>Frameworks, BDD, TestNG and suites that do not flake.</p></a>
<a class="tmh-tile" href="<?php echo $u( '/what-is-quality-assurance/' ); ?>"><h3>Quality assurance</h3><p>What QA really covers, beyond running test cases.</p></a>
<a class="tmh-tile" href="<?php echo $u( '/sdet-guide-roles/' ); ?>"><h3>SDET career</h3><p>The SDET role, skills and path from manual tester to engineer.</p></a>
</div>
</section>

<section class="tmh-tools" id="tools" aria-labelledby="h-tools">
<div class="tmh-wrap" style="padding-top:64px">
<h2 id="h-tools">QA automation tools</h2>
<p class="tmh-intro" style="margin-top:10px">Free tools for testers. The test data tools run in your browser, so nothing you generate is sent to our server.</p>
<div class="tmh-grid">
<a class="tmh-tool" href="<?php echo $u( '/random-ip-address-generator/' ); ?>"><?php echo tmh_icon( 'globe' ); ?><h3>Random IP address generator</h3><p>IPv4 and IPv6 in public, private or documentation ranges. Export TXT, CSV or JSON.</p><code>198.51.100.23
203.0.113.187</code></a>
<a class="tmh-tool" href="<?php echo $u( '/credit-card-generator/' ); ?>"><?php echo tmh_icon( 'card' ); ?><h3>Test card numbers and Luhn checker</h3><p>Official sandbox cards, decline triggers and 3D Secure test cards.</p><code>4242 4242 4242 4242
4000 0000 0000 0002</code></a>
<a class="tmh-tool" href="https://qpulse.sbs/login" target="_blank" rel="noopener"><?php echo tmh_icon( 'story' ); ?><h3>User Story Analyzer</h3><p>Check user stories for gaps and unclear acceptance criteria before they reach testing.</p><span class="tmh-ext">Opens qpulse.sbs in a new tab. Sign in required.</span></a>
</div>
</div>
</section>

<div class="tmh-wrap tmh-split">
<section class="tmh-latest" aria-labelledby="h-latest">
<h2 id="h-latest">Latest from the lab</h2>
<ul><?php echo tmh_latest_posts(); ?></ul>
</section>
<aside class="tmh-jobs" aria-labelledby="h-jobs">
<h2 id="h-jobs">Software testing jobs</h2>
<p>Fresh QA, SDET and automation openings, collected in one place.</p>
<a class="tmh-btn tmh-btn-y" href="<?php echo $u( '/daily-software-testing-jobs/' ); ?>">See today's jobs</a>
<p class="tmh-guest">Writing about testing? <a href="<?php echo $u( '/guest-post/' ); ?>">Submit a guest post</a></p>
</aside>
</div>

<section class="tmh-wrap tmh-sec tmh-faq" aria-labelledby="h-faq">
<h2 id="h-faq">AI software testing FAQ</h2>
<details open><summary>What is AI software testing?</summary><p>AI software testing uses machine learning and large language models to help design, generate, run and maintain tests. It speeds up the routine work, while testers still decide what matters and review what the AI produces.</p></details>
<details><summary>Will AI test automation replace QA testers?</summary><p>No. AI test automation changes the job more than it removes it. Someone still has to judge risk, question requirements and catch the cases an AI confidently skips.</p></details>
<details><summary>Where should I start with AI in test automation?</summary><p>Start small: use AI to draft test cases for one feature, then review them against a checklist. Our guide to <a href="<?php echo $u( '/test-case-design-ai/' ); ?>">test case design with AI</a> gives you 15 checks to run first.</p></details>
</section>

<section class="tmh-news" id="subscribe" aria-labelledby="h-news">
<div class="tmh-wrap tmh-news-in">
<div class="tmh-news-copy"><h2 id="h-news">The best of Testmetry, once a month.</h2><p>New AI software testing guides, free QA tools and testing jobs, in one short email.</p></div>
<div class="tmh-news-form"><?php echo $news; // phpcs:ignore ?></div>
</div>
</section>
</main>

<footer class="tmh-foot">
<div class="tmh-wrap tmh-foot-in">
<p>TestMetry: AI software testing and test automation guides</p>
<nav aria-label="Footer">
<a href="<?php echo $u( '/about-us/' ); ?>">About</a>
<a href="<?php echo $u( '/software-test-consulting-session/' ); ?>">Consulting session</a>
<a href="<?php echo $u( '/about-us/test-in-production-e-book/' ); ?>">Test in production e-book</a>
<a href="<?php echo $u( '/software-testing-conferences/' ); ?>">Conferences</a>
<a href="mailto:george@testmetry.com">Contact</a>
</nav>
</div>
</footer>
</div>
	<?php
	return ob_get_clean();
} );

register_activation_hook( __FILE__, function () {
	if ( function_exists( 'rocket_clean_home' ) ) { rocket_clean_home(); }
} );
