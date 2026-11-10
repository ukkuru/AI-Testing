<?php
/**
 * Plugin Name: Testmetry Merge Redirects
 * Description: 301 redirects for pages merged into a stronger page, and rewrites internal links that pointed at the merged page. Deactivate to undo.
 * Version: 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function tmmr_map() {
	return array(
		'/pyautogui-faq' => '/the-pyautogui-in-python-about/',
	);
}

add_action( 'parse_request', function () {
	if ( is_admin() ) { return; }
	$path = untrailingslashit( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ) );
	$map  = tmmr_map();
	if ( isset( $map[ $path ] ) ) { wp_safe_redirect( $map[ $path ], 301, 'Testmetry Merge' ); exit; }
}, 0 );

add_filter( 'the_content', function ( $content ) {
	if ( false === stripos( $content, 'href=' ) ) { return $content; }
	foreach ( tmmr_map() as $from => $to ) {
		$content = preg_replace( '#href=(["\'])https?://(www\.)?testmetry\.com' . preg_quote( $from, '#' ) . '/?\1#i', 'href=$1' . home_url( $to ) . '$1', $content );
	}
	return $content;
}, 20 );
