<?php
/**
 * Plugin Name: Testmetry Topic Clusters
 * Description: Adds a "Part of our guide" box to supporting articles, linking back to the cluster hub page and across to the sibling articles. Shown at display time only; post content is not changed. Deactivate to undo.
 * Version: 1.5.0
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
		array(
			'title' => 'AI in Software Testing',
			'hub'   => '/the-ai-in-software-testing-the-guide/',
			'extra' => array( '/category/ai-in-testing/' => 'Browse all AI in Testing articles' ),
			'max'   => 6,
			'pages' => array(
				'/ai-test-generator/'            => 'AI test generator: what actually works',
				'/test-case-design-ai/'          => 'Test case design with AI: 15 checks',
				'/ai-in-testing-metrics/'        => 'AI in testing: 5 metrics for real ROI',
				'/ai-in-automated-testing-how/'  => 'AI in automated testing: what top QA teams do',
				'/claude-for-test-automation/'   => 'Claude for test automation',
				'/playwright-mcp-server/'        => 'MCP servers worth a tester\'s time',
				'/github-copilot-in-test/'       => 'GitHub Copilot in test automation',
				'/copilot4devops-use-cases/'     => 'Copilot4DevOps for QA',
				'/chatgpt-to-identify-test/'     => 'Using ChatGPT to identify test scenarios',
				'/defect-analysis/'              => 'Defect analysis with ChatGPT',
				'/postman-ai/'                   => 'Postman AI for API testing',
				'/postbot/'                      => 'PostBot: write API tests with Postman AI',
				'/chatbot-testing/'              => 'Chatbot testing tools and strategy',
				'/voice-agent-testing/'          => 'Testing voice agents',
				'/ai-agent-security/'            => 'AI agent security',
				'/customer-experience-testing/'  => 'Customer experience testing for AI agents',
				'/ai-software-testing/'          => 'AI software testing strategies',
				'/ai-in-software-testing/'       => 'Top AI tools for software testing',
				'/new-trends-in-software-testing/' => 'New trends in software testing',
				'/ai-testing/'                   => 'AI testing use cases',
			),
		),
		array(
			'title' => 'Performance Testing',
			'hub'   => '/comprehensive-guide-to-software-performance-testing-strategies-tools-and-benefits/',
			'max'   => 6,
			'pages' => array(
				'/types-of-performance-tests/'          => 'Types of performance tests',
				'/different-types-of-performance-testing/' => 'Different types of performance testing',
				'/load-testing-tools/'                  => 'Load testing tools compared',
				'/benchmark-test/'                      => 'Benchmark testing explained',
				'/throughput-in-performance-testing/'   => 'Throughput in performance testing',
				'/avoiding-common-mistakes-in-the-performance-testing-life-cycle-a-guide/' => 'Performance testing life cycle mistakes',
				'/shift-left-performance-testing/'      => 'Shift left performance testing',
				'/azure-load-testing/'                  => 'Azure Load Testing gaps',
				'/performance-testing-interview-questions-for/' => 'Performance testing interview questions',
			),
		),
		array(
			'title' => 'SDET (Software Development Engineer in Test)',
			'hub'   => '/sdet-guide-roles/',
			'max'   => 6,
			'pages' => array(
				'/comprehensive-guide-what-is-an-sdet/'     => 'What is an SDET: full form and meaning',
				'/sdet-roles-and-responsibilities/'         => 'SDET roles and responsibilities',
				'/software-development-engineer-test/'      => 'Software development engineer test: skills, tools and practices',
				'/7-key-differences-between-sdet-and-qa/'   => 'SDET vs QA: 7 key differences',
				'/sdet-course/'                             => 'How to choose an SDET course',
				'/sdet-certification/'                      => 'SDET certification: which ones are worth it',
				'/sdet-resume/'                             => 'SDET resume tips and examples',
				'/sdet-interview/'                          => 'SDET interview questions',
				'/sdet-salary/'                             => 'SDET salary in India',
			),
		),
		array(
			'title' => 'Mobile App Performance Testing',
			'hub'   => '/performance-testing-mobile-apps/',
			'extra' => array( '/comprehensive-guide-to-software-performance-testing-strategies-tools-and-benefits/' => 'Software performance testing: the full guide' ),
			'pages' => array(
				'/performance-testing-for-mobile-apps/'      => 'Mobile app performance testing strategy',
				'/performance-testing-mobile-applications/'  => 'Mobile app performance test environment',
				'/load-testing-tools/'                       => 'Load testing tools compared',
				'/mobile-app-testing/'                       => 'Mobile app testing overview',
			),
		),
		array(
			'title' => 'QA Metrics',
			'hub'   => '/qa-metrics/',
			'pages' => array(
				'/quality-assurance-metrics/'   => 'Quality assurance metrics: implement, report and set targets',
				'/cost-of-quality/'             => 'Cost of quality explained',
				'/what-is-quality-assurance/'   => 'What is quality assurance',
				'/defect-triage/'               => 'Defect triage: severity, priority and process',
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
		$keys    = array_map( 'untrailingslashit', array_keys( $c['pages'] ) );
		$is_hub  = ( untrailingslashit( $c['hub'] ) === $path );
		if ( ! $is_hub && ! in_array( $path, $keys, true ) ) { continue; }
		$max   = isset( $c['max'] ) ? (int) $c['max'] : 0;
		$pages = array();
		foreach ( $c['pages'] as $p => $label ) {
			if ( untrailingslashit( $p ) !== $path ) { $pages[ $p ] = $label; }
		}
		if ( $max && count( $pages ) > $max ) {
			// rotate the list by this page's position so every member gets inbound links
			$start = $is_hub ? 0 : (int) array_search( $path, $keys, true );
			$all   = array_keys( $pages );
			$n     = count( $all );
			$pick  = array();
			for ( $i = 0; $i < $max; $i++ ) { $k = $all[ ( $start + $i ) % $n ]; $pick[ $k ] = $pages[ $k ]; }
			$pages = $pick;
		}
		if ( $is_hub ) {
			$out = '<div class="tmcl"><p><strong>Go deeper on ' . esc_html( $c['title'] ) . '.</strong> Related articles:</p><ul>';
		} else {
			$out = '<div class="tmcl"><p><strong>Part of our guide to ' . esc_html( $c['title'] ) . '.</strong> Start with the <a href="' . esc_url( home_url( $c['hub'] ) ) . '">complete ' . esc_html( $c['title'] ) . ' guide</a>, or read the related articles:</p><ul>';
		}
		foreach ( $pages as $p => $label ) {
			$out .= '<li><a href="' . esc_url( home_url( $p ) ) . '">' . esc_html( $label ) . '</a></li>';
		}
		if ( ! empty( $c['extra'] ) ) {
			foreach ( $c['extra'] as $p => $label ) { $out .= '<li><a href="' . esc_url( home_url( $p ) ) . '">' . esc_html( $label ) . '</a></li>'; }
		}
		return $content . $out . '</ul></div>';
	}
	return $content;
}, 26 );
