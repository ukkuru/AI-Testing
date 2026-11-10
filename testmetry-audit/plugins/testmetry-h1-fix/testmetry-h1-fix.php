<?php
/**
 * Plugin Name: Testmetry H1 Fix
 * Description: The theme already prints each post title as the page H1. Many old posts also have H1 headings inside the article, so the page ends up with two or more. This turns in-article H1 headings into H2 at display time and drops empty ones. Post content in the database is not changed. Deactivate to undo.
 * Version: 1.1.1
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) { return $content; }
	if ( false === stripos( $content, '<h1' ) ) { return $content; }
	// remove empty H1s (only whitespace, nbsp or tags inside)
	$content = preg_replace( '#<h1\b[^>]*>(?:\s|&nbsp;|<br\s*/?>|<span[^>]*>|</span>)*</h1>#i', '', $content );
	$content = preg_replace( '#<h1\b#i', '<h2', $content );
	$content = preg_replace( '#</h1>#i', '</h2>', $content );
	return $content;
}, 9 );

/* Pages that print their title as H1 and also carry a second H1 in the page body. */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'page' ) || ! is_main_query() ) { return $content; }
	$id = get_queried_object_id();
	if ( in_array( $id, array( 9805, 5700, 5691 ), true ) ) {
		$content = preg_replace( '#<h1\b#i', '<h2', $content );
		$content = preg_replace( '#</h1>#i', '</h2>', $content );
	}
	// The Testing Conferences page headed itself "Gallery".
	if ( 1845 === $id ) {
		$content = preg_replace( '#(<h1\b[^>]*>)\s*Gallery\s*(</h1>)#i', '$1Testing Conferences$2', $content );
	}
	return $content;
}, 99 );
