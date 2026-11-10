<?php
/**
 * Plugin Name: Testmetry H1 Fix
 * Description: The theme already prints each post title as the page H1. Many old posts also have H1 headings inside the article, so the page ends up with two or more. This turns in-article H1 headings into H2 at display time and drops empty ones. Post content in the database is not changed. Deactivate to undo.
 * Version: 1.0.0
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
