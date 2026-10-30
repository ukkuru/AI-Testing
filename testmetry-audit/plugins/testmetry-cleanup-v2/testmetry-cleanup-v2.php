<?php
/**
 * Plugin Name: Testmetry Cleanup Redirects v2
 * Description: 301 redirects for merged, expired or retired posts; rewrites or removes internal links to them and to known dead or placeholder URLs. Deactivate to undo.
 * Version: 1.0.4
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function tmc2_redirects() { return array(
		'/5-common-mistakes-to-avoid-in-checklist-based-testing' => 'https://testmetry.com/what-is-checklist-based-testing/',
		'/a-comprehensive-guide-to-test-estimation-techniques' => 'https://testmetry.com/software-test-estimation-techniques/',
		'/application-testing-service' => 'https://testmetry.com/automation-testing-service/',
		'/automated-software-testing' => 'https://testmetry.com/benefits-of-automation-testing/',
		'/automated-testing' => 'https://testmetry.com/benefits-of-automation-testing/',
		'/azure-chaos-studio' => 'https://testmetry.com/azure-chaos-engineering-studio-explained/',
		'/bdd-tool' => 'https://testmetry.com/cucumber-framework/',
		'/benefits-of-test-automation' => 'https://testmetry.com/benefits-of-automation-testing/',
		'/bug-triage' => 'https://testmetry.com/defect-triage/',
		'/chaos-engineering-principles-for-ensuring-system-resilience' => 'https://testmetry.com/what-is-chaos-engineering/',
		'/checklist-based-testing-approach' => 'https://testmetry.com/what-is-checklist-based-testing/',
		'/comprehensive-guide-what-is-an-sdet' => 'https://testmetry.com/sdet-guide-roles/',
		'/crm-test' => 'https://testmetry.com/crm-test-cases/',
		'/crm-testing' => 'https://testmetry.com/crm-test-cases/',
		'/cucumber-testing-tool' => 'https://testmetry.com/cucumber-framework/',
		'/defect-analysis' => 'https://testmetry.com/software-defects/',
		'/defect-reporting' => 'https://testmetry.com/software-defects/',
		'/defect-triage-process' => 'https://testmetry.com/defect-triage/',
		'/difference-between-functional-and-non-functional' => 'https://testmetry.com/functional-and-non-functional-requirements-in/',
		'/dynamics-365' => 'https://testmetry.com/dynamics-365-fo/',
		'/extent-reports' => 'https://testmetry.com/extent-reporting/',
		'/github-copilot-for-test-automation' => 'https://testmetry.com/github-copilot-in-test/',
		'/how-to-manage-software-testing-defects-efficiently' => 'https://testmetry.com/software-defects/',
		'/how-to-use-openai-operator-ai-agent' => 'https://testmetry.com/need-to-know-about-openais-operator-ai-agent/',
		'/low-code-development-platform' => 'https://testmetry.com/low-code-platforms/',
		'/mistakes-to-avoid-in-checklist-based-testing' => 'https://testmetry.com/what-is-checklist-based-testing/',
		'/mobile-performance-testing-services' => 'https://testmetry.com/performance-testing-for-mobile-apps/',
		'/model-based-testing-2' => 'https://testmetry.com/model-based-testing/',
		'/openai-operator' => 'https://testmetry.com/need-to-know-about-openais-operator-ai-agent/',
		'/optimizing-test-environment-management' => 'https://testmetry.com/test-environment-management/',
		'/outsource-qa' => 'https://testmetry.com/qa-outsourcing/',
		'/outsourcing-qa' => 'https://testmetry.com/qa-outsourcing/',
		'/performance-testing-for-mobile-app' => 'https://testmetry.com/performance-testing-for-mobile-apps/',
		'/performance-testing-mobile-apps' => 'https://testmetry.com/performance-testing-for-mobile-apps/',
		'/postman-postbot' => 'https://testmetry.com/postbot/',
		'/pyautogui-faq' => 'https://testmetry.com/the-pyautogui-in-python-about/',
		'/qa-outsource' => 'https://testmetry.com/qa-outsourcing/',
		'/quality-assurance-metrics' => 'https://testmetry.com/qa-metrics/',
		'/regression-in-testing' => 'https://testmetry.com/what-is-regression-testing/',
		'/regression-test' => 'https://testmetry.com/what-is-regression-testing/',
		'/regression-testing' => 'https://testmetry.com/what-is-regression-testing/',
		'/requirements-traceability-matrix-rtm2' => 'https://testmetry.com/requirements-traceability-matrix/',
		'/risk-based-test' => 'https://testmetry.com/a-comprehensive-guide-to-risk-based-testing/',
		'/risk-based-testing' => 'https://testmetry.com/a-comprehensive-guide-to-risk-based-testing/',
		'/sanity-testing-meaning-definition' => 'https://testmetry.com/smoke-test-in-software-testing-what-it-is-and-why-it-matters/',
		'/sdet-certification' => 'https://testmetry.com/sdet-course/',
		'/sdet-role-responsibilities' => 'https://testmetry.com/sdet-guide-roles/',
		'/sdet-roles-and-responsibilities' => 'https://testmetry.com/sdet-guide-roles/',
		'/sdet-skills' => 'https://testmetry.com/sdet-guide-roles/',
		'/smoke-testing-vs-sanity-testing' => 'https://testmetry.com/smoke-test-in-software-testing-what-it-is-and-why-it-matters/',
		'/smoke-testing-vs-sanity-testing-distinguishing-key-differences-and-their-roles-in-software-qa' => 'https://testmetry.com/smoke-test-in-software-testing-what-it-is-and-why-it-matters/',
		'/software-development-engineer-in-test' => 'https://testmetry.com/sdet-guide-roles/',
		'/software-development-engineer-test' => 'https://testmetry.com/sdet-guide-roles/',
		'/software-test-estimation' => 'https://testmetry.com/software-test-estimation-techniques/',
		'/software-testnig' => 'https://testmetry.com/what-is-testing-in-software/',
		'/streamlining-your-user-acceptance-testing-process' => 'https://testmetry.com/user-acceptance-testing-template/',
		'/streamlining-your-user-acceptance-testing-process-tips-and-tricks' => 'https://testmetry.com/user-acceptance-testing-template/',
		'/test-automation-framework' => 'https://testmetry.com/test-automation-frameworks-types/',
		'/test-estimate' => 'https://testmetry.com/software-test-estimation-techniques/',
		'/test-strategy-document-what-to' => 'https://testmetry.com/software-testing-strategies/',
		'/test-strategy-in-software-testing' => 'https://testmetry.com/software-testing-strategies/',
		'/testng-framework' => 'https://testmetry.com/testng-framework-a-comprehensive-guide/',
		'/types-of-performance-tests' => 'https://testmetry.com/different-types-of-performance-testing/',
		'/uat' => 'https://testmetry.com/user-acceptance-testing-template/',
		'/uat-full-form-in-testing-everything-you-need-to-know' => 'https://testmetry.com/user-acceptance-testing-template/',
		'/understanding-efficiency-of-smoke-and-sanity-testing-in-software-development' => 'https://testmetry.com/smoke-test-in-software-testing-what-it-is-and-why-it-matters/',
		'/understanding-software-testing-basics-a-complete-beginners-guide' => 'https://testmetry.com/what-is-testing-in-software/',
		'/user-acceptance-testing-checklist-explained' => 'https://testmetry.com/user-acceptance-testing-template/',
		'/user-acceptance-testing-example' => 'https://testmetry.com/user-acceptance-testing-template/',
		'/user-acceptance-testing-process' => 'https://testmetry.com/user-acceptance-testing-template/',
		'/visual-regression-testing' => 'https://testmetry.com/visual-testing/',
		'/web-testing-tools' => 'https://testmetry.com/automation-testing-tools-for-web-apps/',
		'/website-testing' => 'https://testmetry.com/automation-testing-tools-for-web-apps/',
		'/what-is-automated-software-testing' => 'https://testmetry.com/benefits-of-automation-testing/',
		'/what-is-sdet-in-testing-skills-importance-explained' => 'https://testmetry.com/sdet-guide-roles/',
		'/what-is-software-testing' => 'https://testmetry.com/what-is-testing-in-software/',
		'/what-is-software-testing-ways' => 'https://testmetry.com/what-is-testing-in-software/',
		'/what-is-visual-testing-in-qa' => 'https://testmetry.com/visual-testing/',
	); }
function tmc2_removed() { return array(
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
		'/credit-card-generator',
		'/easy-content-gallery',
		'/effective-test-automation-strategies',
		'/exciting-launchable-features',
		'/expert-qa-best-practices',
		'/fake-payment-latest-version',
		'/gallery',
		'/home-2',
		'/importance-of-test-automation-in-cross-browser-testing',
		'/maintenance-page',
		'/my-podcasts',
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
		'/post',
		'/qa-automation-framework',
		'/qa-best-practices',
		'/qa-best-practices-checklist',
		'/qa-best-practices-for-agile-teams',
		'/qa-best-practices-for-teams',
		'/random-ip-address-generator',
		'/search-results',
		'/software-testing-blog',
		'/software-testing-in-2023',
		'/subscribe-sign-me-up',
		'/test-automation-architecture-do-s-and-don-ts',
		'/test-automation-scalability',
		'/test-automation-strategy',
		'/test-automation-strategy-guide',
		'/testgrid-funding',
		'/the-unravelling-of-global-it-systems-crowdstrikes-catastrophic-update',
		'/tricentis-acquires-sealights',
		'/tutorials-2',
		'/what-is-performance-engineering',
	); }
add_action( 'parse_request', function () {
	if ( is_admin() ) { return; }
	$path = untrailingslashit( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ) );
	$map  = tmc2_redirects();
	if ( isset( $map[ $path ] ) ) { wp_safe_redirect( $map[ $path ], 301, 'Testmetry Cleanup' ); exit; }
}, 1 );
add_filter( 'the_content', function ( $content ) {
	if ( false === stripos( $content, '<a' ) ) { return $content; }
	$map = tmc2_redirects(); $removed = array_flip( tmc2_removed() );
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
