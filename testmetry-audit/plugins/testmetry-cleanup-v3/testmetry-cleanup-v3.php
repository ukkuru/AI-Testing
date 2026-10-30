<?php
/**
 * Plugin Name: Testmetry Cleanup Redirects v3
 * Description: 301 redirects for merged, expired or retired posts; rewrites or removes internal links to them and to known dead or placeholder URLs. Deactivate to undo.
 * Version: 1.0.5
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function tmc3_redirects() { return array(
		'/5-common-mistakes-to-avoid-in-checklist-based-testing' => 'https://testmetry.com/what-is-checklist-based-testing/',
		'/automated-software-testing' => 'https://testmetry.com/benefits-of-automation-testing/',
		'/azure-chaos-studio' => 'https://testmetry.com/azure-chaos-engineering-studio-explained/',
		'/bdd-tool' => 'https://testmetry.com/cucumber-framework/',
		'/benefits-of-test-automation' => 'https://testmetry.com/benefits-of-automation-testing/',
		'/chaos-engineering-principles-for-ensuring-system-resilience' => 'https://testmetry.com/what-is-chaos-engineering/',
		'/crm-test' => 'https://testmetry.com/crm-test-cases/',
		'/crm-testing' => 'https://testmetry.com/crm-test-cases/',
		'/cucumber-testing-tool' => 'https://testmetry.com/cucumber-framework/',
		'/defect-reporting' => 'https://testmetry.com/software-defects/',
		'/defect-triage-process' => 'https://testmetry.com/defect-triage/',
		'/github-copilot-for-test-automation' => 'https://testmetry.com/github-copilot-in-test/',
		'/low-code-development-platform' => 'https://testmetry.com/low-code-platforms/',
		'/mistakes-to-avoid-in-checklist-based-testing' => 'https://testmetry.com/what-is-checklist-based-testing/',
		'/mobile-performance-testing-services' => 'https://testmetry.com/performance-testing-for-mobile-apps/',
		'/outsource-qa' => 'https://testmetry.com/qa-outsourcing/',
		'/outsourcing-qa' => 'https://testmetry.com/qa-outsourcing/',
		'/performance-testing-for-mobile-app' => 'https://testmetry.com/performance-testing-for-mobile-apps/',
		'/qa-outsource' => 'https://testmetry.com/qa-outsourcing/',
		'/regression-in-testing' => 'https://testmetry.com/what-is-regression-testing/',
		'/regression-test' => 'https://testmetry.com/what-is-regression-testing/',
		'/requirements-traceability-matrix-rtm2' => 'https://testmetry.com/requirements-traceability-matrix/',
		'/risk-based-test' => 'https://testmetry.com/a-comprehensive-guide-to-risk-based-testing/',
		'/risk-based-testing' => 'https://testmetry.com/a-comprehensive-guide-to-risk-based-testing/',
		'/sanity-testing-meaning-definition' => 'https://testmetry.com/smoke-test-in-software-testing-what-it-is-and-why-it-matters/',
		'/sdet-role-responsibilities' => 'https://testmetry.com/sdet-guide-roles/',
		'/sdet-skills' => 'https://testmetry.com/sdet-guide-roles/',
		'/smoke-testing-vs-sanity-testing' => 'https://testmetry.com/smoke-test-in-software-testing-what-it-is-and-why-it-matters/',
		'/software-development-engineer-in-test' => 'https://testmetry.com/sdet-guide-roles/',
		'/software-test-estimation' => 'https://testmetry.com/software-test-estimation-techniques/',
		'/software-testnig' => 'https://testmetry.com/what-is-testing-in-software/',
		'/streamlining-your-user-acceptance-testing-process-tips-and-tricks' => 'https://testmetry.com/user-acceptance-testing-template/',
		'/test-estimate' => 'https://testmetry.com/software-test-estimation-techniques/',
		'/understanding-efficiency-of-smoke-and-sanity-testing-in-software-development' => 'https://testmetry.com/smoke-test-in-software-testing-what-it-is-and-why-it-matters/',
		'/understanding-software-testing-basics-a-complete-beginners-guide' => 'https://testmetry.com/what-is-testing-in-software/',
		'/website-testing' => 'https://testmetry.com/automation-testing-tools-for-web-apps/',
		'/what-is-sdet-in-testing-skills-importance-explained' => 'https://testmetry.com/sdet-guide-roles/',
		'/what-is-software-testing' => 'https://testmetry.com/what-is-testing-in-software/',
		'/what-is-software-testing-ways' => 'https://testmetry.com/what-is-testing-in-software/',
	); }
function tmc3_removed() { return array(
		'/2024-2',
		'/3-key-takeaways-from-twitters-recent-downtime',
		'/accessibility-testing-automation',
		'/ai-in-testing-benefits',
		'/ai-in-testing-future-potential',
		'/ai-in-testing-how-it-works',
		'/ai-in-testing-strategies',
		'/ai-testing-trends',
		'/automation-framework-for-healthcare',
		'/automation-hangout',
		'/automotive-testing-expo-korea-2024-opens-next-month',
		'/blog/ai-in-testing',
		'/blog/when-automation-testing-is-needed',
		'/conf',
		'/easy-content-gallery',
		'/effective-test-automation-strategies',
		'/expert-qa-best-practices',
		'/fake-payment-latest-version',
		'/gallery',
		'/home-2',
		'/importance-of-test-automation-in-cross-browser-testing',
		'/maintenance-page',
		'/performance-engineering-basics',
		'/performance-engineering-checklist',
		'/performance-engineering-essentials',
		'/performance-engineering-in-modern-devops',
		'/performance-engineering-in-qa',
		'/performance-engineering-in-testing',
		'/performance-engineering-strategies',
		'/performance-testing-hospital-systems',
		'/performance-testing-principles',
		'/performance-vs-load-testing',
		'/podcast',
		'/qa-automation-framework',
		'/qa-best-practices',
		'/qa-best-practices-checklist',
		'/qa-best-practices-for-agile-teams',
		'/qa-best-practices-for-teams',
		'/search-results',
		'/software-testing-blog',
		'/software-testing-in-2023',
		'/subscribe-sign-me-up',
		'/test-automation-architecture-do-s-and-don-ts',
		'/test-automation-scalability',
		'/test-automation-strategy',
		'/test-automation-strategy-guide',
		'/the-unravelling-of-global-it-systems-crowdstrikes-catastrophic-update',
		'/tricentis-acquires-sealights',
		'/what-is-performance-engineering',
	); }
add_action( 'parse_request', function () {
	if ( is_admin() ) { return; }
	$path = untrailingslashit( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ) );
	$map  = tmc3_redirects();
	if ( isset( $map[ $path ] ) ) { wp_safe_redirect( $map[ $path ], 301, 'Testmetry Cleanup' ); exit; }
}, 1 );
add_filter( 'the_content', function ( $content ) {
	if ( false === stripos( $content, '<a' ) ) { return $content; }
	$map = tmc3_redirects(); $removed = array_flip( tmc3_removed() );
	return preg_replace_callback( '#<a\s[^>]*href=(["\'])([^"\']*)\1[^>]*>(.*?)</a>#is', function ( $m ) use ( $map, $removed ) {
		$href = trim( $m[2] );
		if ( '' === $href || preg_match( '#^(\#|mailto:|tel:|/(?!/))#i', $href ) ) { return $m[0]; }
		if ( ! preg_match( '#^https?://#i', $href ) ) {
			if ( preg_match( '#^(www\.)?testmetry\.com/#i', $href ) || preg_match( '#^(internal|external)-link\d*$#i', $href ) || preg_match( '#^testmetry\w*\.html$#i', $href ) ) { return $m[3]; }
			return $m[0];
		}
		if ( ! preg_match( '#^https?://(www\.)?testmetry\.com(/[^?\#]*)?#i', $href, $h ) ) { return $m[0]; }
		$p = untrailingslashit( isset( $h[2] ) ? $h[2] : '' );
		if ( isset( $map[ $p ] ) ) { return str_replace( $m[2], $map[ $p ], $m[0] ); }
		if ( isset( $removed[ $p ] ) ) { return $m[3]; }
		return $m[0];
	}, $content );
}, 20 );
