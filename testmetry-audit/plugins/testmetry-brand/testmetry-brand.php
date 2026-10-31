<?php
/**
 * Plugin Name: Testmetry Brand
 * Description: Applies the homepage's dark navy and yellow theme across the site with CSS only. Content, URLs, links and SEO tags are untouched. While TMB_LIVE is false the theme shows only with ?tmbrand=1 in the URL, for testing.
 * Version: 1.2.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'TMB_LIVE', false );

function tmb_active() {
	if ( is_admin() ) { return false; }
	if ( function_exists( 'tmh_is_tm_page' ) && tmh_is_tm_page() ) { return false; }
	if ( TMB_LIVE ) { return true; }
	return isset( $_GET['tmbrand'] ); // phpcs:ignore WordPress.Security.NonceVerification
}

// Always on: the menu's old Home link (?page_id=6875, the retired Elementor homepage) goes to the front page.
add_action( 'parse_request', function () {
	if ( isset( $_GET['page_id'] ) && '6875' === (string) $_GET['page_id'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_safe_redirect( home_url( '/' ), 301, 'Testmetry Brand' );
		exit;
	}
}, 0 );

// Always on: show post featured images at their natural shape instead of cropping them into a fixed-height box.
add_action( 'wp_head', function () {
	if ( is_admin() ) { return; }
	echo '<style id="tmb-fix">.elementor-widget-theme-post-featured-image img,.single .elementor-widget-image img.wp-post-image{height:auto!important;max-height:none!important;aspect-ratio:auto!important;object-fit:contain!important;width:100%!important;border-radius:10px}</style>';
}, 98 );

add_filter( 'body_class', function ( $classes ) {
	if ( tmb_active() ) { $classes[] = 'tmb'; }
	return $classes;
} );

// Same as the homepage: logged-out visitors do not need the login toolbar.
add_filter( 'show_admin_bar', function ( $show ) {
	if ( ! is_user_logged_in() && tmb_active() ) { return false; }
	return $show;
}, 99 );

add_action( 'wp_head', function () {
	if ( ! tmb_active() ) { return; }
	echo '<style id="tmb-css">' . tmb_css() . '</style>'; // phpcs:ignore
}, 99 );

function tmb_css() {
	return <<<'CSS'
body.tmb{--tmb-bg:#0a1530;--tmb-bg2:#0e1d3f;--tmb-card:#12234a;--tmb-line:#22345e;--tmb-text:#d3dbe8;--tmb-muted:#a8b4c9;--tmb-head:#ffffff;--tmb-yellow:#f6cf4b;--tmb-navy:#0b2a5b;
--e-global-color-primary:#f6cf4b;--e-global-color-secondary:#a8b4c9;--e-global-color-text:#d3dbe8;--e-global-color-accent:#f6cf4b;
background:var(--tmb-bg)!important;color:var(--tmb-text)!important;color-scheme:dark}
body.tmb #page,body.tmb .site,body.tmb main,body.tmb .site-main,body.tmb .page-content,body.tmb .elementor,body.tmb .elementor-section,body.tmb .e-con,body.tmb .e-con-inner,body.tmb .elementor-column,body.tmb .elementor-widget-wrap,body.tmb .elementor-element-populated,body.tmb .elementor-widget-container,body.tmb .elementor-location-header,body.tmb .elementor-location-footer,body.tmb header,body.tmb footer,body.tmb #masthead,body.tmb .ehf-header #masthead,body.tmb .hfe-nav-menu,body.tmb article,body.tmb .comments-area,body.tmb #comments{background-color:transparent!important}
body.tmb .ehf-header #masthead,body.tmb [data-elementor-type="header"],body.tmb .elementor-location-header{background-color:var(--tmb-bg)!important;border-bottom:1px solid var(--tmb-line)}
body.tmb footer,body.tmb .elementor-location-footer,body.tmb [data-elementor-type="footer"],body.tmb #colophon{background-color:var(--tmb-bg2)!important;background-image:none!important;border-top:1px solid var(--tmb-line)!important}
body.tmb [data-elementor-type="footer"] .elementor-section,body.tmb [data-elementor-type="footer"] .e-con,body.tmb #colophon .elementor-section{background-image:none!important;border-color:var(--tmb-line)!important}
body.tmb,body.tmb p,body.tmb li,body.tmb td,body.tmb th,body.tmb dd,body.tmb dt,body.tmb blockquote,body.tmb figcaption,body.tmb label,body.tmb span,body.tmb .elementor-widget-text-editor,body.tmb .elementor-post__excerpt,body.tmb .elementor-icon-list-text,body.tmb .elementor-post-info__item,body.tmb .elementor-widget-theme-post-content{color:var(--tmb-text)!important}
body.tmb h1,body.tmb h2,body.tmb h3,body.tmb h4,body.tmb h5,body.tmb h6,body.tmb .elementor-heading-title,body.tmb .elementor-post__title,body.tmb .elementor-post__title a,body.tmb .entry-title,body.tmb .entry-title a,body.tmb .page-title{color:var(--tmb-head)!important}
body.tmb a,body.tmb .elementor-widget-theme-post-content a,body.tmb .elementor-widget-text-editor a,body.tmb .elementor-post__read-more,body.tmb .elementor-breadcrumbs a,body.tmb .rank-math-breadcrumb a{color:var(--tmb-yellow)!important}
body.tmb .elementor-widget-theme-post-content p a,body.tmb .elementor-widget-theme-post-content li a,body.tmb .elementor-widget-text-editor p a,body.tmb .entry-content p a,body.tmb .entry-content li a{text-decoration:underline!important;text-underline-offset:3px}
body.tmb .elementor-post__title a,body.tmb h1 a,body.tmb h2 a,body.tmb h3 a,body.tmb h4 a{color:var(--tmb-head)!important}
body.tmb .elementor-post__title a:hover,body.tmb h2 a:hover,body.tmb h3 a:hover{color:var(--tmb-yellow)!important}
body.tmb .hfe-nav-menu a,body.tmb .hfe-nav-menu .hfe-menu-item,body.tmb .elementor-nav-menu a,body.tmb .menu-item a,body.tmb .hfe-nav-menu__toggle,body.tmb .hfe-menu-toggle,body.tmb .hfe-search-button-wrapper,body.tmb .hfe-search-icon-toggle i,body.tmb .hfe-nav-menu-icon{color:var(--tmb-text)!important;fill:var(--tmb-text)!important}
body.tmb .hfe-nav-menu a:hover,body.tmb .hfe-nav-menu .current-menu-item>a,body.tmb .elementor-nav-menu a:hover{color:var(--tmb-yellow)!important}
body.tmb .sub-menu,body.tmb .hfe-nav-menu .sub-menu,body.tmb .elementor-nav-menu--dropdown,body.tmb .hfe-dropdown,body.tmb .hfe-nav-menu__layout-horizontal .sub-menu{background-color:var(--tmb-card)!important;border:1px solid var(--tmb-line)!important}
body.tmb svg:not([fill]){fill:currentColor}
body.tmb .elementor-widget-sidebar,body.tmb .widget,body.tmb #ez-toc-container,body.tmb .ez-toc-v2_0_81,body.tmb .jp-relatedposts,body.tmb .elementor-author-box,body.tmb .comment-respond,body.tmb .wp-block-table,body.tmb .elementor-widget-posts .elementor-post__card,body.tmb .elementor-widget-wp-widget-recent-posts{background-color:var(--tmb-card)!important;border-color:var(--tmb-line)!important;color:var(--tmb-text)!important}
body.tmb #ez-toc-container{border:1px solid var(--tmb-line)!important;border-radius:12px;box-shadow:none!important}
body.tmb #ez-toc-container a,body.tmb #ez-toc-container .ez-toc-title{color:var(--tmb-text)!important}
body.tmb #ez-toc-container a:hover{color:var(--tmb-yellow)!important}
body.tmb .ez-toc-btn,body.tmb .ez-toc-pull-right{background:transparent!important;border-color:var(--tmb-line)!important}
body.tmb table,body.tmb th,body.tmb td{border-color:var(--tmb-line)!important}
body.tmb thead th,body.tmb th{background-color:var(--tmb-bg2)!important;color:var(--tmb-head)!important}
body.tmb tbody tr:nth-child(even) td{background-color:rgba(255,255,255,.03)!important}
body.tmb pre,body.tmb code,body.tmb kbd{background-color:#0d1a38!important;color:#e8edf6!important;border-color:var(--tmb-line)!important}
body.tmb blockquote{border-left-color:var(--tmb-yellow)!important;background:transparent!important}
body.tmb hr,body.tmb .elementor-divider-separator{border-color:var(--tmb-line)!important}
body.tmb input[type=text],body.tmb input[type=email],body.tmb input[type=search],body.tmb input[type=url],body.tmb input[type=number],body.tmb textarea,body.tmb select{background-color:var(--tmb-bg2)!important;color:#fff!important;border:1px solid #3a4f80!important}
body.tmb input::placeholder,body.tmb textarea::placeholder{color:#8f9cb5!important}
body.tmb button,body.tmb input[type=submit],body.tmb .elementor-button,body.tmb .wp-block-button__link,body.tmb .elementor-share-btn,body.tmb .elementor-post__read-more-wrapper a.elementor-button{background-color:var(--tmb-yellow)!important;color:var(--tmb-navy)!important;border-color:var(--tmb-yellow)!important}
body.tmb .elementor-share-btn .elementor-share-btn__icon,body.tmb .elementor-share-btn i,body.tmb .elementor-share-btn svg,body.tmb .elementor-social-icon i,body.tmb .elementor-social-icon svg{color:var(--tmb-navy)!important;fill:var(--tmb-navy)!important}
body.tmb .elementor-social-icon{background-color:var(--tmb-yellow)!important}
body.tmb .elementor-widget-heading .elementor-heading-title[style*="background"],body.tmb .elementor-widget-heading.elementor-widget .elementor-widget-container[style*="background"]{background-color:var(--tmb-yellow)!important;color:var(--tmb-navy)!important}
body.tmb .elementor-icon-list-icon i,body.tmb .elementor-icon-list-icon svg,body.tmb .elementor-post-info__item-prefix,body.tmb .elementor-icon i,body.tmb .elementor-icon svg{color:var(--tmb-yellow)!important;fill:var(--tmb-yellow)!important}
body.tmb .elementor-post-info__item,body.tmb .elementor-post-date,body.tmb .elementor-post__meta-data,body.tmb .posted-on,body.tmb time{color:var(--tmb-muted)!important}
body.tmb .elementor-widget-theme-post-content img,body.tmb .entry-content img,body.tmb .elementor-post__thumbnail img,body.tmb .wp-post-image{border-radius:10px}
body.tmb .elementor-post__badge,body.tmb .elementor-post__terms a,body.tmb .elementor-post-info__terms-list-item{background-color:var(--tmb-yellow)!important;color:var(--tmb-navy)!important}
body.tmb #colophon .e-con,body.tmb #colophon .elementor-element,body.tmb #colophon .elementor{background-image:none!important;background-color:transparent!important}
body.tmb .elementor-element-3a714203{background-color:var(--tmb-yellow)!important}
body.tmb .elementor-element-3a714203 .elementor-heading-title{color:var(--tmb-navy)!important}
body.tmb .gt_switcher_wrapper a,body.tmb .gt_switcher_wrapper span,body.tmb .gt_switcher a{color:#333!important;text-decoration:none!important}
body.tmb #ez-toc-container a{text-decoration:none!important}
body.tmb .hfe-nav-menu .sub-arrow,body.tmb .hfe-nav-menu .sub-arrow *,body.tmb .hfe-search-icon-toggle,body.tmb .hfe-search-icon-toggle *{color:var(--tmb-text)!important;fill:var(--tmb-text)!important}
body.tmb.ehf-header #masthead{padding-top:30px}
body.tmb ::selection{background:var(--tmb-yellow);color:var(--tmb-navy)}
body.tmb .tmt-box{background:var(--tmb-card)!important;border-color:var(--tmb-line)!important}
body.tmb .tmt-box .tmt-title{color:#fff!important}
body.tmb .tmt-box .tmt-sub,body.tmb .tmt-box .tmt-msg{color:var(--tmb-muted)!important}
body.tmb .tmt-box label{color:#d3dbe8!important}
body.tmb .tmt-box .tmt-btn.tmt-alt{background:transparent!important;color:var(--tmb-yellow)!important;border-color:var(--tmb-yellow)!important}
body.tmb .tmt-box .tmt-result{background:var(--tmb-bg2)!important;border-color:var(--tmb-line)!important;color:var(--tmb-text)!important}
body.tmb .tmt-box .tmt-result.is-ok{background:#0f2e25!important;border-color:#1f6b4f!important}
body.tmb .tmt-box .tmt-result.is-bad{background:#3a1620!important;border-color:#7a2d3e!important}
body.tmb .tmt-box .tmt-ok{color:#4ade80!important}
body.tmb .tmt-box .tmt-bad{color:#ff9b9b!important}
body.tmb .tmt-box .tmt-out{background:#0a1530!important;border-color:var(--tmb-line)!important}
CSS;
}
