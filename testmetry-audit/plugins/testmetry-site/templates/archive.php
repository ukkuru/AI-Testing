<?php
/**
 * Blog and category archive layout used while the Testmetry Site theme is active.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$is_cat   = is_category();
$cat_id   = $is_cat ? (int) get_queried_object_id() : 0;
$is_blog  = ( ! $is_cat ) || 1364 === $cat_id;
$h1       = $is_blog ? 'Software testing blog' : single_term_title( '', false );
$desc     = $is_cat ? trim( wp_strip_all_tags( term_description() ) ) : '';
if ( '' === $desc ) {
	$desc = $is_blog
		? 'Practical guides on AI software testing, test automation, QA tools and careers, from 25+ years in quality assurance.'
		: 'Guides and articles on ' . strtolower( $h1 ) . ' from 25+ years in quality assurance.';
}

$chip_ids = array( 1364 => 'All', 1363 => 'Test automation', 1585 => 'Performance', 1554 => 'Security', 1562 => 'Dev tools', 1563 => 'ChatGPT', 1366 => 'News' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'tms-archive' ); ?>>
<?php wp_body_open(); ?>
<?php echo tms_header_html(); // phpcs:ignore ?>
<div class="tms-arch">
<main>
<section class="tms-wrap tms-ahead">
<nav class="tms-crumb" aria-label="Breadcrumb"><a href="<?php echo tms_u( '/' ); ?>">Home</a> / <?php echo $is_blog ? 'Blog' : '<a href="' . tms_u( '/category/blog/' ) . '">Blog</a> / ' . esc_html( $h1 ); // phpcs:ignore ?></nav>
<h1><?php echo esc_html( $h1 ); ?></h1>
<p class="tms-aintro"><?php echo esc_html( $desc ); ?></p>
<div class="tms-chiprow">
<div class="tms-chips" role="group" aria-label="Filter by topic">
<?php
foreach ( $chip_ids as $id => $label ) {
	$link = get_category_link( $id );
	if ( is_wp_error( $link ) || ! $link ) { continue; }
	$on = ( $is_cat && $cat_id === $id ) || ( ! $is_cat && 1364 === $id );
	echo '<a href="' . esc_url( $link ) . '"' . ( $on ? ' class="on" aria-current="page"' : '' ) . '>' . esc_html( $label ) . '</a>';
}
?>
</div>
<form class="tms-asearch" role="search" method="get" action="<?php echo tms_u( '/' ); ?>"><label class="tms-sr" for="tms-as">Search the blog</label><input id="tms-as" type="search" name="s" placeholder="Search the blog"></form>
</div>
</section>

<section class="tms-wrap tms-body">
<div class="tms-main">
<?php if ( have_posts() ) : ?>
	<?php
	$i = 0;
	ob_start();
	while ( have_posts() ) :
		the_post();
		$i++;
		$pid  = get_the_ID();
		$ex   = wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 26 );
		$cat  = tms_card_category( $pid );
		$date = get_the_date( 'j M Y' );
		if ( 1 === $i && ! is_paged() ) {
			$feature = '<a class="tms-feat" href="' . esc_url( get_permalink() ) . '"><div class="tms-fimg">' . tms_card_image( $pid, 'large' ) . '</div><div class="tms-ftxt"><span class="tms-badge">' . esc_html( $cat ) . '</span><h2>' . esc_html( get_the_title() ) . '</h2><p class="ex">' . esc_html( $ex ) . '</p><p class="tms-meta">George Ukkuru, ' . esc_html( $date ) . '</p></div></a>';
			continue;
		}
		?>
		<a class="tms-card" href="<?php the_permalink(); ?>">
		<div class="tms-cimg"><?php echo tms_card_image( $pid, 'medium_large' ); // phpcs:ignore ?></div>
		<div class="tms-cbody"><span class="tms-badge"><?php echo esc_html( $cat ); ?></span><h3><?php echo esc_html( get_the_title() ); ?></h3><p class="ex"><?php echo esc_html( $ex ); ?></p><p class="tms-meta"><?php echo esc_html( $date ); ?></p></div>
		</a>
		<?php
	endwhile;
	$cards = ob_get_clean();
	echo isset( $feature ) ? $feature : ''; // phpcs:ignore
	?>
	<div class="tms-grid" style="margin-top:24px"><?php echo $cards; // phpcs:ignore ?></div>
	<?php
	$links = paginate_links( array( 'type' => 'array', 'prev_text' => 'Previous', 'next_text' => 'Next', 'mid_size' => 1 ) );
	if ( $links ) {
		echo '<nav class="tms-pager" aria-label="Pagination">' . implode( '', $links ) . '</nav>'; // phpcs:ignore
	}
	?>
<?php else : ?>
	<p class="tms-empty">Nothing here yet. Try the <a href="<?php echo tms_u( '/category/blog/' ); ?>">full blog</a>.</p>
<?php endif; ?>
</div>
<aside class="tms-side" aria-label="Sidebar">
<div class="tms-box"><h2>Most read</h2><ol>
<li><a href="<?php echo tms_u( '/user-acceptance-testing-template/' ); ?>">User acceptance testing template</a></li>
<li><a href="<?php echo tms_u( '/load-testing-tools/' ); ?>">Load testing tools compared</a></li>
<li><a href="<?php echo tms_u( '/sdet-guide-roles/' ); ?>">The SDET guide: role and career path</a></li>
<li><a href="<?php echo tms_u( '/what-is-regression-testing/' ); ?>">What is regression testing</a></li>
</ol></div>
<div class="tms-box"><h2>Software testing jobs</h2><p>Fresh QA, SDET and automation openings in one place.</p><a class="tms-btn" href="<?php echo tms_u( '/daily-software-testing-jobs/' ); ?>">See today's jobs</a></div>
<div class="tms-box"><h2>The best of Testmetry, once a month</h2><?php echo tms_newsletter_form(); // phpcs:ignore ?></div>
</aside>
</section>
</main>
</div>
<?php echo tms_footer_html(); // phpcs:ignore ?>
<?php wp_footer(); ?>
</body>
</html>
