<?php
/**
 * Plugin Name: Testmetry Related Links
 * Description: Adds a short "Related reading" box to articles, pointing at pages that deserve more internal links. Display time only, post content is not changed, existing links are never touched. Deactivate to undo.
 * Version: 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* target path => array( label, array of source slugs that should link to it ) */
function tmrl_map() {
	return array(
		'/web-testing-tools/' => array( 'Web testing tools: 16 best tools by type', array( 'automation-testing-tools-for-web-apps', 'automation-in-web-testing', 'selenium-pros-cons-no-one-talks-about', 'list-of-test-automation-tools', 'cypress-vs-selenium', 'efficient-cross-browser-testing-with-selenium-basics-importance-and-best-practices', 'what-is-visual-testing-in-qa', 'what-is-selenium-a-clear-explanation', 'how-to-install-selenium' ) ),
		'/scaffolding-in-software-testing/' => array( 'What is scaffolding in software testing', array( 'bi-directional-integration-testing-a-strategic-approach-to-software-quality', 'test-automation-frameworks-types', 'achieve-success-with-test-automation', 'test-automation-framework', 'test-automation-pyramid', 'desktop-automation' ) ),
		'/edi-testing/' => array( 'EDI testing: process, test cases and tools', array( 'compliance-testing-guide-to-success-automation-ai-role-and-more', 'bi-directional-integration-testing-a-strategic-approach-to-software-quality', 'inventory-system-test-cases-that-work', 'effective-e-commerce-test-cases-sample', 'test-cases-for-things', 'banking-application-test-cases-for', 'how-rpa-and-test-automation' ) ),
		'/decision-table-testing/' => array( 'Decision table testing explained', array( 'test-case-design', 'black-box-testing-techniques', 'unveiling-smarter-testing-a-comprehensive-guide-to-black-box-testing', 'experience-based-testing-technique', 'maximizing-software-quality-a-comprehensive-guide-to-boundary-value-analysis-in-testing', 'mastering-domain-testing-in-software-testing-efficient-techniques-and-challenges', 'test-cases-for-login-page-howto', 'writing-test-cases-that-are-clear', 'best-practices-optimizing-white-box-and-black-box-software-testing' ) ),
		'/crm-test-cases/' => array( 'CRM testing: 11 real test cases', array( 'salesforce-qa-interview-questions', 'shopping-website-test-cases-that-wow-users', 'inventory-system-test-cases-that-work', 'hospital-system-test-cases-real-examples', 'effective-e-commerce-test-cases-sample', 'test-case-and-test-scenario', 'test-cases-for-login-page-howto' ) ),
		'/benchmark-test/' => array( 'Benchmark testing explained', array( 'types-of-performance-tests', 'different-types-of-performance-testing', 'throughput-in-performance-testing', 'performance-testing-interview-questions-for', 'avoiding-common-mistakes-in-the-performance-testing-life-cycle-a-guide', 'azure-load-testing', 'comprehensive-guide-to-software-performance-testing-strategies-tools-and-benefits' ) ),
		'/3-point-estimation-calculator/' => array( '3 point estimation calculator (PERT)', array( 'software-test-estimation-techniques', 'a-comprehensive-guide-to-test-estimation-techniques', 'manual-testing-estimation-model-can-perform-estimation-for-complex-projects', 'planning-poker', 'why-testing-without-planning-can-backfire-understanding-what-happens', 'how-to-scope-your-automation-testing-like-a-pro' ) ),
		'/defect-triage/' => array( 'Defect triage: process, severity vs priority', array( 'software-defects', 'defect-classification-explained-simply', 'how-to-manage-software-testing-defects-efficiently', 'why-is-bug-status-set-to-postponed-find-out', 'latent-defect', 'understanding-high-severity-and-low-priority-examples-in-software-testing-and-quality-assurance', 'understanding-high-priority-and-low-severity-defects-in-software-testing', 'top-consequences-of-bugs-in-software-testing', 'defect-analysis', 'bug-testing', 'understanding-and-avoiding-common-mistakes-in-software-testing-and-defect-management' ) ),
		'/what-is-quality-assurance/' => array( 'What is quality assurance?', array( 'differentiate-between-quality-assurance-and-quality-control-explained', 'what-is-quality-control', 'quality-assurance', 'quality-assurance-metrics', 'qa-metrics', 'qa-testing', 'inspection-vs-quality-control-5-key-differences', 'cost-of-quality', 'quality-software-engineer', 'qa-process-in-agile' ) ),
		'/shift-right-testing-in-production/' => array( 'Shift right testing in production', array( 'software-testing-in-agile', 'qa-process-in-agile', 'azure-load-testing', '5-must-know-tips-for-agile-testing-teams', 'role-of-qa-in-agile', 'test-automation-at-a-healthcare' ) ),
	);
}

add_action( 'wp_head', function () {
	if ( is_singular( 'post' ) ) { echo '<style id="tmrl-css">.tmrl{margin:28px 0;padding:16px 20px;border:1px solid #dbe3ef;border-radius:12px;background:#fff}.tmrl p{margin:0 0 6px!important;color:#0b2a5b!important}.tmrl ul{margin:0!important;padding-left:20px!important}.tmrl a{color:#0b2a5b!important;text-decoration:underline}</style>'; }
} );

add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) { return $content; }
	$post = get_post();
	if ( ! $post ) { return $content; }
	$slug = $post->post_name;
	$items = array();
	foreach ( tmrl_map() as $path => $def ) {
		if ( ! in_array( $slug, $def[1], true ) ) { continue; }
		if ( untrailingslashit( (string) wp_parse_url( get_permalink(), PHP_URL_PATH ) ) === untrailingslashit( $path ) ) { continue; }
		if ( false !== stripos( $content, 'testmetry.com' . untrailingslashit( $path ) ) ) { continue; } // already linked in the article
		$items[ $path ] = $def[0];
		if ( count( $items ) >= 3 ) { break; }
	}
	if ( ! $items ) { return $content; }
	$out = '<div class="tmrl"><p><strong>Related reading</strong></p><ul>';
	foreach ( $items as $p => $label ) { $out .= '<li><a href="' . esc_url( home_url( $p ) ) . '">' . esc_html( $label ) . '</a></li>'; }
	return $content . $out . '</ul></div>';
}, 27 );
