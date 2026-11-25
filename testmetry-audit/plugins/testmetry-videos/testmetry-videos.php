<?php
/**
 * Plugin Name: Testmetry Videos
 * Description: Video publishing for testmetry.com. A Videos post type for Vimeo hosted videos (nothing is uploaded to the media library), a [tm_videos] library grid, a Latest Videos block for the homepage, a single video page with click to play embed, related videos and articles, VideoObject schema and a video sitemap. Deactivate to undo; video posts stay in the database.
 * Version: 2.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'TMV_VER', '2.0.0' );

/* ------------------------------------------------------------------
 * Post type, taxonomies and fields
 * ---------------------------------------------------------------- */

add_action( 'init', function () {
	register_post_type( 'tm_video', array(
		'labels'       => array(
			'name'               => 'Videos',
			'singular_name'      => 'Video',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Video',
			'edit_item'          => 'Edit Video',
			'new_item'           => 'New Video',
			'view_item'          => 'View Video',
			'search_items'       => 'Search Videos',
			'not_found'          => 'No videos found',
			'all_items'          => 'All Videos',
			'menu_name'          => 'Videos',
			'featured_image'     => 'Thumbnail',
			'set_featured_image' => 'Set thumbnail',
			'remove_featured_image' => 'Remove thumbnail',
			'use_featured_image' => 'Use as thumbnail',
		),
		'public'       => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-video-alt3',
		'menu_position'=> 5,
		'has_archive'  => false,
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions', 'custom-fields' ),
		'rewrite'      => array( 'slug' => 'videos', 'with_front' => false ),
	) );

	// Filtering happens on the /videos/ page, so these have no public archive pages of their own.
	register_taxonomy( 'tm_video_cat', 'tm_video', array(
		'labels'            => array( 'name' => 'Video Categories', 'singular_name' => 'Video Category', 'menu_name' => 'Categories', 'all_items' => 'All Categories', 'add_new_item' => 'Add New Category' ),
		'hierarchical'      => true,
		'public'            => false,
		'publicly_queryable'=> false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => false,
	) );
	register_taxonomy( 'tm_video_tag', 'tm_video', array(
		'labels'            => array( 'name' => 'Video Tags', 'singular_name' => 'Video Tag', 'menu_name' => 'Tags', 'all_items' => 'All Tags', 'add_new_item' => 'Add New Tag' ),
		'hierarchical'      => false,
		'public'            => false,
		'publicly_queryable'=> false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'rewrite'           => false,
	) );

	$auth = function () { return current_user_can( 'edit_posts' ); };
	foreach ( array( 'tm_video_url', 'tm_duration', 'tm_transcript', 'tm_related_posts', 'tm_noindex' ) as $key ) {
		register_post_meta( 'tm_video', $key, array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'tm_video_url' === $key ? 'esc_url_raw' : ( 'tm_transcript' === $key ? 'sanitize_textarea_field' : 'sanitize_text_field' ),
			'auth_callback'     => $auth,
		) );
	}
}, 5 );

// Rewrite rules: flush once per plugin version.
add_action( 'init', function () {
	if ( get_option( 'tmv_rewrite_ver' ) !== TMV_VER ) {
		delete_option( 'rewrite_rules' );
		flush_rewrite_rules( true );
		update_option( 'tmv_rewrite_ver', TMV_VER, false );
	}
}, 99 );

// /videos/page/2/ would otherwise be read as a video called "page". Send it to the library page.
add_filter( 'request', function ( $qv ) {
	if ( isset( $qv['tm_video'] ) && 'page' === $qv['tm_video'] && ! empty( $qv['paged'] ) ) {
		return array( 'pagename' => 'videos', 'paged' => (int) $qv['paged'] );
	}
	return $qv;
} );

// Use the block editor for videos even though Classic Editor is active for everything else.
add_filter( 'use_block_editor_for_post_type', function ( $use, $type ) {
	return 'tm_video' === $type ? true : $use;
}, 101, 2 );
add_filter( 'classic_editor_enabled_editors_for_post_type', function ( $editors, $type ) {
	if ( 'tm_video' === $type ) { return array( 'classic_editor' => false, 'block_editor' => true ); }
	return $editors;
}, 10, 2 );

/* ------------------------------------------------------------------
 * Vimeo helpers
 * ---------------------------------------------------------------- */

// Accepts vimeo.com/ID, vimeo.com/ID/HASH (unlisted), player.vimeo.com/video/ID?h=HASH and share links with extra query text.
function tmv_parse( $url ) {
	$url = trim( (string) $url );
	$out = array( 'id' => '', 'hash' => '' );
	if ( preg_match( '~^\d{6,}$~', $url ) ) { $out['id'] = $url; return $out; }
	if ( preg_match( '~vimeo\.com/(?:[^?#]*/)?(\d{6,})(?:/([0-9a-f]{8,}))?~i', $url, $m ) ) {
		$out['id']   = $m[1];
		$out['hash'] = isset( $m[2] ) ? strtolower( $m[2] ) : '';
		if ( ! $out['hash'] && preg_match( '~[?&]h=([0-9a-f]{8,})~i', $url, $h ) ) { $out['hash'] = strtolower( $h[1] ); }
	}
	return $out;
}

function tmv_parse_id( $url ) { $p = tmv_parse( $url ); return $p['id']; }

function tmv_seconds( $label ) {
	$label = trim( (string) $label );
	if ( '' === $label ) { return 0; }
	if ( ctype_digit( $label ) ) { return (int) $label; }
	$parts = array_reverse( array_map( 'intval', explode( ':', $label ) ) );
	$secs  = 0;
	foreach ( $parts as $i => $v ) { $secs += $v * pow( 60, $i ); }
	return $secs;
}

function tmv_label( $secs ) {
	$secs = (int) $secs;
	if ( $secs <= 0 ) { return ''; }
	$h = intdiv( $secs, 3600 );
	$m = intdiv( $secs % 3600, 60 );
	$s = $secs % 60;
	return $h ? sprintf( '%d:%02d:%02d', $h, $m, $s ) : sprintf( '%d:%02d', $m, $s );
}

// Ask Vimeo (oEmbed, no key needed) for duration, thumbnail and shape when a video is saved.
function tmv_fetch( $post_id ) {
	$p = tmv_parse( get_post_meta( $post_id, 'tm_video_url', true ) );
	if ( ! $p['id'] ) { return; }
	update_post_meta( $post_id, 'tm_vm_id', $p['id'] );
	update_post_meta( $post_id, 'tm_vm_hash', $p['hash'] );
	$target = 'https://vimeo.com/' . $p['id'] . ( $p['hash'] ? '/' . $p['hash'] : '' );
	$res    = wp_remote_get( 'https://vimeo.com/api/oembed.json?width=1280&url=' . rawurlencode( $target ), array( 'timeout' => 8 ) );
	if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) { return; }
	$d = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $d ) ) { return; }
	if ( ! empty( $d['duration'] ) ) { update_post_meta( $post_id, 'tm_vm_seconds', (int) $d['duration'] ); }
	if ( ! empty( $d['thumbnail_url'] ) ) { update_post_meta( $post_id, 'tm_vm_thumb', esc_url_raw( $d['thumbnail_url'] ) ); }
	if ( ! empty( $d['width'] ) && ! empty( $d['height'] ) ) { update_post_meta( $post_id, 'tm_vm_ratio', round( (float) $d['width'] / (float) $d['height'], 4 ) ); }
}

function tmv_data( $post ) {
	$post = get_post( $post );
	$id   = get_post_meta( $post->ID, 'tm_vm_id', true );
	$hash = get_post_meta( $post->ID, 'tm_vm_hash', true );
	if ( ! $id ) { $p = tmv_parse( get_post_meta( $post->ID, 'tm_video_url', true ) ); $id = $p['id']; $hash = $p['hash']; }
	$secs = tmv_seconds( get_post_meta( $post->ID, 'tm_duration', true ) );
	if ( ! $secs ) { $secs = (int) get_post_meta( $post->ID, 'tm_vm_seconds', true ); }
	$ratio = (float) get_post_meta( $post->ID, 'tm_vm_ratio', true );
	if ( $ratio <= 0 ) { $ratio = 16 / 9; }
	$thumb = '';
	$w     = 0;
	$h     = 0;
	if ( has_post_thumbnail( $post ) ) {
		$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'large' );
		if ( $img ) { $thumb = $img[0]; $w = $img[1]; $h = $img[2]; }
	}
	if ( ! $thumb ) {
		$thumb = (string) get_post_meta( $post->ID, 'tm_vm_thumb', true );
		$w     = 1280;
		$h     = (int) round( 1280 / $ratio );
	}
	$play = $id ? 'https://player.vimeo.com/video/' . rawurlencode( $id ) . ( $hash ? '?h=' . rawurlencode( $hash ) : '' ) : '';
	// Same form as Vimeo's own oEmbed code, which includes the app_id.
	$embed = $id ? 'https://player.vimeo.com/video/' . rawurlencode( $id ) . '?' . ( $hash ? 'h=' . rawurlencode( $hash ) . '&' : '' ) . 'app_id=122963' : '';
	return array(
		'id'    => $id,
		'secs'  => $secs,
		'label' => tmv_label( $secs ),
		'ratio' => $ratio,
		'thumb' => $thumb,
		'w'     => $w,
		'h'     => $h,
		'embed' => $embed,
		'play'  => $play,
		'watch' => $id ? 'https://vimeo.com/' . $id . ( $hash ? '/' . $hash : '' ) : '',
	);
}

function tmv_library_url() {
	$p = get_page_by_path( 'videos' );
	return $p ? get_permalink( $p ) : home_url( '/videos/' );
}

/* ------------------------------------------------------------------
 * Editor screen
 * ---------------------------------------------------------------- */

add_action( 'add_meta_boxes', function () {
	remove_meta_box( 'postcustom', 'tm_video', 'normal' );
	add_meta_box( 'tmv_details', 'Video details', 'tmv_box', 'tm_video', 'normal', 'high' );
} );

function tmv_box( $post ) {
	wp_nonce_field( 'tmv_save', 'tmv_nonce' );
	$g = function ( $k ) use ( $post ) { return get_post_meta( $post->ID, $k, true ); };
	$auto = (int) $g( 'tm_vm_seconds' );
	?>
<style>.tmv-f p{margin:0 0 14px}.tmv-f label{display:block;font-weight:600;margin-bottom:4px}.tmv-f input[type=text],.tmv-f input[type=url],.tmv-f textarea{width:100%}.tmv-f small{color:#646970}</style>
<div class="tmv-f">
<p><label for="tm_video_url">Vimeo video URL</label><input type="url" id="tm_video_url" name="tm_video_url" value="<?php echo esc_attr( $g( 'tm_video_url' ) ); ?>" placeholder="https://vimeo.com/1237480616"><small>Paste the share link from Vimeo (the video must allow embedding). The player, thumbnail and duration are picked up when you save.</small></p>
<p><label for="tm_duration">Duration</label><input type="text" id="tm_duration" name="tm_duration" value="<?php echo esc_attr( $g( 'tm_duration' ) ); ?>" placeholder="<?php echo $auto ? esc_attr( tmv_label( $auto ) . ' (found automatically)' ) : 'Filled in automatically, or type 3:45'; ?>"><small>Leave empty to use the length Vimeo reports.</small></p>
<p><label for="tm_transcript">Transcript</label><textarea id="tm_transcript" name="tm_transcript" rows="10"><?php echo esc_textarea( $g( 'tm_transcript' ) ); ?></textarea><small>Plain text. It shows on the video page and helps the page rank.</small></p>
<p><label for="tm_related_posts">Related articles (optional)</label><input type="text" id="tm_related_posts" name="tm_related_posts" value="<?php echo esc_attr( $g( 'tm_related_posts' ) ); ?>" placeholder="https://testmetry.com/some-article/, https://testmetry.com/another/"><small>Comma separated article links. Leave empty and the page picks blog articles that share a tag name with the video tags or category.</small></p>
<p><label><input type="checkbox" name="tm_noindex" value="1" <?php checked( $g( 'tm_noindex' ), '1' ); ?>> Hide this video from search engines and the video sitemap</label></p>
<p><small>Short description: use the Excerpt box. Detailed description (about 300 words): use the main editor. Thumbnail: use the Thumbnail panel. Leave it empty to use the Vimeo thumbnail.</small></p>
</div>
	<?php
}

add_action( 'save_post_tm_video', function ( $post_id ) {
	if ( ! isset( $_POST['tmv_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tmv_nonce'] ) ), 'tmv_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
	$old = get_post_meta( $post_id, 'tm_video_url', true );
	update_post_meta( $post_id, 'tm_video_url', esc_url_raw( wp_unslash( $_POST['tm_video_url'] ?? '' ) ) );
	update_post_meta( $post_id, 'tm_duration', sanitize_text_field( wp_unslash( $_POST['tm_duration'] ?? '' ) ) );
	update_post_meta( $post_id, 'tm_transcript', sanitize_textarea_field( wp_unslash( $_POST['tm_transcript'] ?? '' ) ) );
	update_post_meta( $post_id, 'tm_related_posts', sanitize_text_field( wp_unslash( $_POST['tm_related_posts'] ?? '' ) ) );
	update_post_meta( $post_id, 'tm_noindex', ! empty( $_POST['tm_noindex'] ) ? '1' : '' );
	if ( $old !== get_post_meta( $post_id, 'tm_video_url', true ) || ! get_post_meta( $post_id, 'tm_vm_id', true ) ) { tmv_fetch( $post_id ); }
} );

// Also fetch when a video is created or changed through the REST API (the block editor and automation).
add_action( 'rest_after_insert_tm_video', function ( $post ) { tmv_fetch( $post->ID ); } );

/* ------------------------------------------------------------------
 * Cards, library grid and homepage block
 * ---------------------------------------------------------------- */

function tmv_card( $post ) {
	$d     = tmv_data( $post );
	if ( false !== strpos( $d['thumb'], 'i.vimeocdn.com' ) ) {
		// Cards only need a smaller picture than the video page does.
		$d['thumb'] = preg_replace( '~-d_\d+~', '-d_640', $d['thumb'] );
		$d['h']     = (int) round( 640 / max( 0.1, $d['ratio'] ) );
		$d['w']     = 640;
	}
	$terms = get_the_terms( $post, 'tm_video_cat' );
	$cat   = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
	$title = get_the_title( $post );
	$out   = '<a class="tmv-card" href="' . esc_url( get_permalink( $post ) ) . '"><span class="tmv-thumb' . ( $d['h'] > $d['w'] ? ' tmv-port' : '' ) . '"' . ( ( $d['h'] > $d['w'] && $d['thumb'] ) ? ' style="--tmv-bg:url(' . esc_url( $d['thumb'] ) . ')"' : '' ) . '>';
	if ( $d['thumb'] ) {
		$out .= '<img src="' . esc_url( $d['thumb'] ) . '" width="' . (int) $d['w'] . '" height="' . (int) $d['h'] . '" alt="" loading="lazy" decoding="async">';
	}
	$out .= '<span class="tmv-play" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22"><path d="M8 5v14l11-7z" fill="currentColor"/></svg></span>';
	if ( $d['label'] ) { $out .= '<span class="tmv-dur">' . esc_html( $d['label'] ) . '</span>'; }
	$out .= '</span><span class="tmv-body">';
	if ( $cat ) { $out .= '<span class="tmv-cat">' . esc_html( $cat ) . '</span>'; }
	$out .= '<h3>' . esc_html( $title ) . '</h3></span></a>';
	return $out;
}

function tmv_latest( $n ) {
	return get_posts( array( 'post_type' => 'tm_video', 'post_status' => 'publish', 'posts_per_page' => $n, 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true ) );
}

// Used by the homepage plugin. Cards only, no player, nothing from Vimeo loads except thumbnails.
function tmv_home_section( $n = 6 ) {
	$videos = tmv_latest( $n );
	if ( ! $videos ) { return ''; }
	$o = '<section class="tmh-wrap tmh-sec" id="videos" aria-labelledby="h-vid"><div class="tmh-sec-head"><h2 id="h-vid">Latest videos</h2><a class="tmh-more" href="' . esc_url( tmv_library_url() ) . '">View all videos</a></div><div class="tmv-grid">';
	foreach ( $videos as $v ) { $o .= tmv_card( $v ); }
	return $o . '</div></section>';
}

add_shortcode( 'tm_videos', function ( $atts ) {
	$atts  = shortcode_atts( array( 'per_page' => 12 ), $atts, 'tm_videos' );
	$per   = max( 1, (int) $atts['per_page'] );
	$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$cat   = isset( $_GET['vcat'] ) ? sanitize_title( wp_unslash( $_GET['vcat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$q     = isset( $_GET['vq'] ) ? sanitize_text_field( wp_unslash( $_GET['vq'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$args  = array( 'post_type' => 'tm_video', 'post_status' => 'publish', 'posts_per_page' => $per, 'paged' => $paged, 'orderby' => 'date', 'order' => 'DESC', 'ignore_sticky_posts' => true );
	if ( $cat ) { $args['tax_query'] = array( array( 'taxonomy' => 'tm_video_cat', 'field' => 'slug', 'terms' => $cat ) ); }
	if ( '' !== $q ) { $args['s'] = $q; }
	$query = new WP_Query( $args );
	$base  = trailingslashit( tmv_library_url() );
	$keep  = array_filter( array( 'vcat' => $cat, 'vq' => $q ) );

	$o  = '<div class="tmv-lib"><div class="tmv-bar">';
	$o .= '<form class="tmv-search" role="search" method="get" action="' . esc_url( $base ) . '"><label class="tmv-sr" for="tmv-q">Search videos</label><input id="tmv-q" type="search" name="vq" value="' . esc_attr( $q ) . '" placeholder="Search videos">';
	if ( $cat ) { $o .= '<input type="hidden" name="vcat" value="' . esc_attr( $cat ) . '">'; }
	$o .= '<button type="submit">Search</button></form>';
	$terms = get_terms( array( 'taxonomy' => 'tm_video_cat', 'hide_empty' => true ) );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$o .= '<nav class="tmv-pills" aria-label="Video categories"><a href="' . esc_url( $q ? add_query_arg( 'vq', rawurlencode( $q ), $base ) : $base ) . '"' . ( $cat ? '' : ' aria-current="true"' ) . '>All</a>';
		foreach ( $terms as $t ) {
			$link = add_query_arg( array_filter( array( 'vcat' => $t->slug, 'vq' => $q ) ), $base );
			$o   .= '<a href="' . esc_url( $link ) . '"' . ( $cat === $t->slug ? ' aria-current="true"' : '' ) . '>' . esc_html( $t->name ) . '</a>';
		}
		$o .= '</nav>';
	}
	$o .= '</div>';

	if ( $query->have_posts() ) {
		$o .= '<div class="tmv-grid">';
		foreach ( $query->posts as $v ) { $o .= tmv_card( $v ); }
		$o .= '</div>';
		$links = paginate_links( array(
			'base'      => $base . 'page/%#%/',
			'format'    => '',
			'current'   => $paged,
			'total'     => (int) $query->max_num_pages,
			'add_args'  => $keep,
			'prev_text' => 'Previous',
			'next_text' => 'Next',
			'type'      => 'plain',
		) );
		if ( $links ) { $o .= '<nav class="tmv-pager" aria-label="Video pages">' . $links . '</nav>'; }
	} else {
		$o .= '<p class="tmv-empty">No videos match that yet. <a href="' . esc_url( $base ) . '">Show all videos</a></p>';
	}
	wp_reset_postdata();
	return $o . '</div>';
} );

/* ------------------------------------------------------------------
 * Single video page
 * ---------------------------------------------------------------- */

function tmv_related_articles( $post ) {
	$found = array();
	$manual = trim( (string) get_post_meta( $post->ID, 'tm_related_posts', true ) );
	if ( '' !== $manual ) {
		foreach ( preg_split( '~[\s,]+~', $manual ) as $item ) {
			$pid = ctype_digit( $item ) ? (int) $item : url_to_postid( $item );
			$p   = $pid ? get_post( $pid ) : null;
			if ( $p && 'publish' === $p->post_status && 'post' === $p->post_type ) { $found[ $p->ID ] = $p; }
		}
	}
	if ( count( $found ) < 3 ) {
		$names = array();
		foreach ( array( 'tm_video_tag', 'tm_video_cat' ) as $tax ) {
			$t = get_the_terms( $post, $tax );
			if ( $t && ! is_wp_error( $t ) ) { foreach ( $t as $term ) { $names[] = $term->name; } }
		}
		$slugs = array_filter( array_map( 'sanitize_title', $names ) );
		if ( $slugs ) {
			$qq = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3, 'no_found_rows' => true, 'ignore_sticky_posts' => true, 'tag_slug__in' => $slugs ) );
			foreach ( $qq->posts as $p ) { $found[ $p->ID ] = $p; }
		}
	}
	return array_slice( array_values( $found ), 0, 3 );
}

add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'tm_video' ) || ! did_action( 'wp_head' ) || doing_filter( 'get_the_excerpt' ) || doing_filter( 'wp_head' ) ) { return $content; }
	$post = get_queried_object();
	if ( ! $post || get_the_ID() !== $post->ID ) { return $content; }
	static $done = false;
	if ( $done ) { return $content; }
	$done = true;

	$d     = tmv_data( $post );
	$title = get_the_title( $post );
	$url   = get_permalink( $post );
	$o     = '<div class="tmv-single">';

	if ( $d['embed'] ) {
		$max = $d['ratio'] < 1 ? ' --tmv-max:380px;' : '';
		$o  .= '<div class="tmv-player" data-embed="' . esc_url( $d['embed'] ) . '" data-title="' . esc_attr( $title ) . '" style="--tmv-r:' . esc_attr( round( $d['ratio'], 4 ) ) . ';' . $max . '">';
		$o  .= '<button type="button" class="tmv-facade" aria-label="' . esc_attr( 'Play video: ' . $title ) . '">';
		if ( $d['thumb'] ) { $o .= '<img src="' . esc_url( $d['thumb'] ) . '" width="' . (int) $d['w'] . '" height="' . (int) $d['h'] . '" alt="" decoding="async">'; }
		$o  .= '<span class="tmv-play tmv-play-lg" aria-hidden="true"><svg viewBox="0 0 24 24" width="34" height="34"><path d="M8 5v14l11-7z" fill="currentColor"/></svg></span></button>';
		$o  .= '<noscript><iframe src="' . esc_url( $d['embed'] ) . '" title="' . esc_attr( $title ) . '" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe></noscript></div>';
	}

	if ( $d['id'] ) { $o .= '<p class="tmv-alt"><a href="' . esc_url( $d['watch'] ) . '" target="_blank" rel="noopener">Video not loading? Watch it on Vimeo</a></p>'; }

	$bits = array( 'By ' . esc_html( get_the_author_meta( 'display_name', $post->post_author ) ), '<time datetime="' . esc_attr( get_the_date( 'c', $post ) ) . '">' . esc_html( get_the_date( 'j F Y', $post ) ) . '</time>' );
	if ( $d['label'] ) { $bits[] = 'Duration ' . esc_html( $d['label'] ); }
	$o .= '<p class="tmv-meta">' . implode( ' <span aria-hidden="true">&bull;</span> ', $bits ) . '</p>';

	if ( has_excerpt( $post ) ) { $o .= '<p class="tmv-short">' . esc_html( get_the_excerpt( $post ) ) . '</p>'; }
	if ( '' !== trim( wp_strip_all_tags( $content ) ) ) { $o .= '<div class="tmv-detail">' . $content . '</div>'; }

	$tr = trim( (string) get_post_meta( $post->ID, 'tm_transcript', true ) );
	if ( '' !== $tr ) { $o .= '<details class="tmv-transcript"><summary>Video transcript</summary>' . wpautop( esc_html( $tr ) ) . '</details>'; }

	$e  = rawurlencode( $url );
	$t  = rawurlencode( $title );
	$o .= '<div class="tmv-share"><span>Share this video</span>'
		. '<a href="https://www.linkedin.com/sharing/share-offsite/?url=' . $e . '" target="_blank" rel="noopener">LinkedIn</a>'
		. '<a href="https://twitter.com/intent/tweet?url=' . $e . '&amp;text=' . $t . '" target="_blank" rel="noopener">X</a>'
		. '<a href="https://www.facebook.com/sharer/sharer.php?u=' . $e . '" target="_blank" rel="noopener">Facebook</a>'
		. '<a href="https://wa.me/?text=' . $t . '%20' . $e . '" target="_blank" rel="noopener">WhatsApp</a>'
		. '<a href="mailto:?subject=' . $t . '&amp;body=' . $e . '">Email</a></div>';

	$o .= '<p class="tmv-back"><a href="' . esc_url( tmv_library_url() ) . '">&larr; Back to All Videos</a></p>';

	// Related videos: same category first, then the newest.
	$rel   = array();
	$terms = get_the_terms( $post, 'tm_video_cat' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$rel = get_posts( array( 'post_type' => 'tm_video', 'post_status' => 'publish', 'posts_per_page' => 3, 'post__not_in' => array( $post->ID ), 'tax_query' => array( array( 'taxonomy' => 'tm_video_cat', 'field' => 'term_id', 'terms' => wp_list_pluck( $terms, 'term_id' ) ) ), 'no_found_rows' => true ) );
	}
	if ( count( $rel ) < 3 ) {
		$have = array_merge( array( $post->ID ), wp_list_pluck( $rel, 'ID' ) );
		$rel  = array_merge( $rel, get_posts( array( 'post_type' => 'tm_video', 'post_status' => 'publish', 'posts_per_page' => 3 - count( $rel ), 'post__not_in' => $have, 'no_found_rows' => true ) ) );
	}
	if ( $rel ) {
		$o .= '<section class="tmv-rel"><h2>Related videos</h2><div class="tmv-grid">';
		foreach ( $rel as $v ) { $o .= tmv_card( $v ); }
		$o .= '</div></section>';
	}

	$arts = tmv_related_articles( $post );
	if ( $arts ) {
		$o .= '<section class="tmv-rel"><h2>Related articles</h2><ul class="tmv-arts">';
		foreach ( $arts as $a ) { $o .= '<li><a href="' . esc_url( get_permalink( $a ) ) . '">' . esc_html( get_the_title( $a ) ) . '</a></li>'; }
		$o .= '</ul></section>';
	}
	return $o . '</div>';
}, 20 );

// Click to play: the Vimeo player only loads after the visitor asks for it.
add_action( 'wp_footer', function () {
	if ( ! is_singular( 'tm_video' ) ) { return; }
	echo '<script id="tmv-js">document.addEventListener("click",function(e){var b=e.target.closest&&e.target.closest(".tmv-facade");if(!b){return}var w=b.parentNode,f=document.createElement("iframe");f.src=w.getAttribute("data-embed")+"&autoplay=1";f.title=w.getAttribute("data-title")||"Video";f.allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share";f.referrerPolicy="strict-origin-when-cross-origin";f.setAttribute("allowfullscreen","");f.setAttribute("frameborder","0");w.replaceChild(f,b)});</script>'; // phpcs:ignore
}, 50 );

/* ------------------------------------------------------------------
 * SEO: VideoObject, robots and the video sitemap
 * ---------------------------------------------------------------- */

add_action( 'wp_head', function () {
	if ( ! is_singular( 'tm_video' ) ) { return; }
	$post = get_queried_object();
	$d    = tmv_data( $post );
	if ( ! $d['embed'] ) { return; }
	$desc = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 40, '' );
	if ( '' === trim( $desc ) ) { $desc = get_the_title( $post ); }
	$schema = array(
		'@context'     => 'https://schema.org',
		'@type'        => 'VideoObject',
		'name'         => get_the_title( $post ),
		'description'  => wp_strip_all_tags( $desc ),
		'thumbnailUrl' => array_values( array_filter( array( $d['thumb'] ) ) ),
		'uploadDate'   => get_the_date( 'c', $post ),
		'embedUrl'     => $d['play'],
		'url'          => get_permalink( $post ),
		'inLanguage'   => 'en',
		'author'       => array( '@type' => 'Person', 'name' => get_the_author_meta( 'display_name', $post->post_author ) ),
		'publisher'    => array( '@type' => 'Organization', 'name' => 'TestMetry', 'url' => home_url( '/' ) ),
	);
	if ( $d['secs'] ) { $schema['duration'] = 'PT' . intdiv( $d['secs'], 3600 ) . 'H' . intdiv( $d['secs'] % 3600, 60 ) . 'M' . ( $d['secs'] % 60 ) . 'S'; }
	$tr = trim( (string) get_post_meta( $post->ID, 'tm_transcript', true ) );
	if ( '' !== $tr ) { $schema['transcript'] = $tr; }
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore
}, 20 );

add_filter( 'rank_math/frontend/robots', function ( $robots ) {
	if ( is_singular( 'tm_video' ) && '1' === get_post_meta( get_queried_object_id(), 'tm_noindex', true ) ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}
	return $robots;
} );
add_action( 'send_headers', function () {
	if ( is_singular( 'tm_video' ) && '1' === get_post_meta( get_queried_object_id(), 'tm_noindex', true ) ) { header( 'X-Robots-Tag: noindex, follow', true ); }
} );
add_filter( 'rank_math/sitemap/entry', function ( $url, $type, $object ) {
	if ( 'post' === $type && is_object( $object ) && isset( $object->post_type ) && 'tm_video' === $object->post_type && '1' === get_post_meta( $object->ID, 'tm_noindex', true ) ) { return false; }
	return $url;
}, 10, 3 );

add_action( 'init', function () {
	add_rewrite_rule( '^tm-video-map\.xml$', 'index.php?tmv_sitemap=1', 'top' );
} );
add_filter( 'query_vars', function ( $v ) { $v[] = 'tmv_sitemap'; return $v; } );
add_filter( 'redirect_canonical', function ( $redirect ) { return get_query_var( 'tmv_sitemap' ) ? false : $redirect; } );
add_action( 'template_redirect', function () {
	if ( ! get_query_var( 'tmv_sitemap' ) ) { return; }
	$videos = get_posts( array( 'post_type' => 'tm_video', 'post_status' => 'publish', 'posts_per_page' => 1000, 'no_found_rows' => true, 'meta_query' => array( 'relation' => 'OR', array( 'key' => 'tm_noindex', 'compare' => 'NOT EXISTS' ), array( 'key' => 'tm_noindex', 'value' => '1', 'compare' => '!=' ) ) ) );
	nocache_headers();
	header( 'Content-Type: application/xml; charset=UTF-8' );
	header( 'X-Robots-Tag: noindex, follow' );
	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";
	foreach ( $videos as $v ) {
		$d = tmv_data( $v );
		if ( ! $d['embed'] || ! $d['thumb'] ) { continue; }
		$desc = has_excerpt( $v ) ? get_the_excerpt( $v ) : wp_trim_words( wp_strip_all_tags( $v->post_content ), 40, '' );
		if ( '' === trim( $desc ) ) { $desc = get_the_title( $v ); }
		echo '<url><loc>' . esc_url( get_permalink( $v ) ) . '</loc><video:video><video:thumbnail_loc>' . esc_url( $d['thumb'] ) . '</video:thumbnail_loc><video:title>' . esc_html( get_the_title( $v ) ) . '</video:title><video:description>' . esc_html( mb_substr( wp_strip_all_tags( $desc ), 0, 2000 ) ) . '</video:description><video:player_loc>' . esc_url( $d['play'] ) . '</video:player_loc>';
		if ( $d['secs'] ) { echo '<video:duration>' . (int) $d['secs'] . '</video:duration>'; }
		echo '<video:publication_date>' . esc_html( get_the_date( 'c', $v ) ) . '</video:publication_date><video:family_friendly>yes</video:family_friendly></video:video></url>' . "\n";
	}
	echo '</urlset>';
	exit;
} );
add_filter( 'robots_txt', function ( $txt ) {
	return $txt . "\nSitemap: " . home_url( '/tm-video-map.xml' ) . "\n";
} );

/* ------------------------------------------------------------------
 * Styles (only where videos appear)
 * ---------------------------------------------------------------- */

function tmv_needs_css() {
	if ( is_singular( 'tm_video' ) ) { return true; }
	if ( is_singular() ) {
		$p = get_post();
		return $p && ( has_shortcode( $p->post_content, 'tm_videos' ) || has_shortcode( $p->post_content, 'tm_home' ) );
	}
	return false;
}

add_action( 'wp_head', function () {
	if ( ! tmv_needs_css() ) { return; }
	echo '<style id="tmv-css">' . tmv_css() . '</style>'; // phpcs:ignore
}, 100 );

function tmv_css() {
	return <<<'CSS'
.tmv-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:24px}
.tmv-card{display:flex;flex-direction:column;background:var(--card,#fff);border:1px solid var(--line,#dbe3ef);border-radius:14px;overflow:hidden;text-decoration:none!important;color:var(--text,#334155)!important;transition:box-shadow .15s,border-color .15s}
.tmv-card:hover{border-color:#f6cf4b;box-shadow:0 12px 30px rgba(11,42,91,.12)}
.tmv-card:focus-visible,.tmv-facade:focus-visible{outline:3px solid #0b2a5b;outline-offset:3px}
.tmv-thumb{position:relative;display:block;aspect-ratio:16/9;background:#0b2a5b;overflow:hidden}
.tmv-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center 30%;display:block}
.tmv-port::before{content:"";position:absolute;inset:-12px;background:var(--tmv-bg) center/cover;filter:blur(16px) brightness(.65)}
.tmv-port img{object-fit:contain;object-position:center;z-index:1}
.tmv-play{position:absolute;left:50%;top:50%;width:54px;height:54px;margin:-27px 0 0 -27px;border-radius:50%;background:#f6cf4b;color:#0b2a5b;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(0,0,0,.35);padding-left:3px;box-sizing:border-box}
.tmv-play-lg{width:76px;height:76px;margin:-38px 0 0 -38px}
.tmv-card:hover .tmv-play,.tmv-facade:hover .tmv-play{background:#ffe07a}
.tmv-dur{position:absolute;right:10px;bottom:10px;background:rgba(11,26,56,.88);color:#fff;font:600 13px/1 Inter,system-ui,sans-serif;padding:5px 8px;border-radius:6px}
.tmv-body{display:flex;flex-direction:column;gap:6px;padding:16px 18px 20px}
.tmv-cat{font-size:12px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--muted,#5b6780)}
.tmv-body h3{margin:0;font-size:18px;line-height:1.35;color:#0b2a5b}
.tmv-bar{display:flex;flex-wrap:wrap;gap:14px 20px;align-items:center;justify-content:space-between;margin-bottom:26px}
.tmv-search{display:flex;gap:8px;flex:1 1 280px;max-width:440px}
.tmv-search input[type=search]{flex:1;min-height:46px;padding:0 14px;border:1px solid #c7d2e3;border-radius:10px;font-size:16px;background:#fff;color:#0b2a5b}
.tmv-search button{min-height:46px;padding:0 20px;border:0;border-radius:10px;background:#f6cf4b;color:#0b2a5b;font-weight:600;font-size:15px;cursor:pointer}
.tmv-pills{display:flex;flex-wrap:wrap;gap:8px}
.tmv-pills a{border:1px solid #c7d2e3;border-radius:999px;padding:8px 16px;font-size:14px;text-decoration:none;color:#0b2a5b;background:#fff}
.tmv-pills a[aria-current]{background:#0b2a5b;border-color:#0b2a5b;color:#fff}
.tmv-sr{position:absolute;left:-9999px}
.tmv-pager{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin-top:36px}
.tmv-pager .page-numbers{border:1px solid #c7d2e3;color:#0b2a5b;text-decoration:none;padding:10px 16px;border-radius:10px;font-size:15px;background:#fff}
.tmv-pager .page-numbers.current{background:#f6cf4b;border-color:#f6cf4b;font-weight:600}
.tmv-empty{font-size:18px;padding:30px 0}
.tmv-single{max-width:860px;margin:0 auto}
.tmv-player{position:relative;width:100%;max-width:var(--tmv-max,100%);aspect-ratio:var(--tmv-r,1.7778);margin:0 auto 20px;background:#0b2a5b;border-radius:14px;overflow:hidden}
.tmv-player iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
.tmv-facade{position:absolute;inset:0;width:100%;height:100%;padding:0;border:0;background:#0b2a5b;cursor:pointer;display:block}
.tmv-facade img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block}
.tmv-alt{text-align:center;font-size:14px;margin:-8px 0 14px}.tmv-alt a{color:#5b6780}
.tmv-meta{color:#5b6780;font-size:15px;margin:0 0 18px;text-align:center}
.tmv-short{font-size:19px;line-height:1.6;color:#0b2a5b;margin:0 0 18px}
.tmv-detail{margin-bottom:22px}
.tmv-transcript{background:#f5f7fa;border:1px solid #dbe3ef;border-radius:12px;padding:16px 20px;margin:22px 0}
.tmv-transcript summary{cursor:pointer;font-weight:600;color:#0b2a5b}
.tmv-share{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin:22px 0}
.tmv-share span{font-weight:600;color:#0b2a5b;margin-right:4px}
.tmv-share a{border:1px solid #c7d2e3;border-radius:999px;padding:7px 14px;font-size:14px;text-decoration:none;color:#0b2a5b}
.tmv-share a:hover{background:#eef2f8}
.tmv-back{margin:0 0 34px}
.tmv-back a{font-weight:600;color:#0b2a5b}
.tmv-rel{margin-top:38px}
.tmv-rel h2{font-size:24px;margin:0 0 16px;color:#0b2a5b}
.tmv-arts{margin:0;padding-left:20px;display:flex;flex-direction:column;gap:10px;font-size:17px}
.tmv-single .tmv-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}
@media (max-width:900px){.tmv-grid,.tmv-single .tmv-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width:600px){.tmv-grid,.tmv-single .tmv-grid{grid-template-columns:1fr}.tmv-search{max-width:none}.tmv-short{font-size:17px}}
CSS;
}
