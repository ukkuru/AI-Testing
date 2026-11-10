<?php
/**
 * Plugin Name: Testmetry Topic Clusters
 * Description: Adds a "Part of our guide" box to supporting articles, linking back to the cluster hub page and across to the sibling articles. Shown at display time only; post content is not changed. Deactivate to undo.
 * Version: 1.1.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function tmcl_clusters() {
	return array(
		array(
			'title' => 'User Acceptance Testing (UAT)',
			'hub'   => '/uat/',
			'pages' => array(
				'/user-acceptance-testing-template/'               => 'UAT template',
				'/user-acceptance-testing-checklist-explained/'    => 'UAT checklist',
				'/user-acceptance-testing-example/'                => 'Acceptance criteria and examples',
				'/user-acceptance-testing-process/'                => 'The UAT process and common mistakes',
				'/uat-full-form-in-testing-everything-you-need-to-know/' => 'UAT full form',
				'/what-acceptance-testing-really-focuses-on/'      => 'What acceptance testing focuses on',
				'/uat-interview-questions-that-reveal-true-skills/' => 'UAT interview questions',
			),
		),
		array(
			'title' => 'Shift Left Testing',
			'hub'   => '/shift-left-testing/',
			'pages' => array(
				'/shift-left-vs-shift-right/'              => 'Shift left vs shift right',
				'/shift-left-performance-testing/'         => 'Shift left performance testing',
				'/shift-right-testing-in-production/'      => 'Shift right testing in production',
			),
		),
	);
}

add_action( 'wp_head', function () {
	if ( is_singular( 'post' ) ) { echo '<style id="tmcl-css">.tmcl{margin:32px 0;padding:18px 20px;border:1px solid #dbe3ef;border-radius:12px;background:#f5f7fa}.tmcl p{margin:0 0 8px!important;color:#0b2a5b!important}.tmcl ul{margin:0!important;padding-left:20px!important}.tmcl a{color:#0b2a5b!important;text-decoration:underline}</style>'; }
} );

add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) { return $content; }
	$path = untrailingslashit( (string) wp_parse_url( get_permalink(), PHP_URL_PATH ) );
	foreach ( tmcl_clusters() as $c ) {
		$keys = array_map( 'untrailingslashit', array_keys( $c['pages'] ) );
		if ( ! in_array( $path, $keys, true ) ) { continue; }
		$out = '<div class="tmcl"><p><strong>Part of our guide to ' . esc_html( $c['title'] ) . '.</strong> Start with the <a href="' . esc_url( home_url( $c['hub'] ) ) . '">complete ' . esc_html( $c['title'] ) . ' guide</a>, or read the related articles:</p><ul>';
		foreach ( $c['pages'] as $p => $label ) {
			if ( untrailingslashit( $p ) === $path ) { continue; }
			$out .= '<li><a href="' . esc_url( home_url( $p ) ) . '">' . esc_html( $label ) . '</a></li>';
		}
		return $content . $out . '</ul></div>';
	}
	return $content;
}, 26 );
