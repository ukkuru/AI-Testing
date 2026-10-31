<?php
/**
 * Plugin Name: Testmetry Category Redirect
 * Description: 301 redirects the old misspelled category /category/utomation/ (and everything under it, such as sub-categories, pagination and feeds) to /category/test-automation/.
 * Version: 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'parse_request', function () {
	if ( is_admin() ) { return; }
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	if ( ! preg_match( '#^/category/utomation(/.*)?$#i', $path, $m ) ) { return; }
	$rest  = ( isset( $m[1] ) && '' !== $m[1] ) ? $m[1] : '/';
	$query = (string) wp_parse_url( $uri, PHP_URL_QUERY );
	$to    = home_url( '/category/test-automation' . $rest ) . ( '' !== $query ? '?' . $query : '' );
	wp_safe_redirect( $to, 301, 'Testmetry Category Redirect' );
	exit;
}, 0 );
