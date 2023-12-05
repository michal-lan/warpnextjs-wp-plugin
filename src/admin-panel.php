<?php
/**
 * Admin Panel
 * 
 * Description: adjust admin panel for using next.js app
 */

// disable comments system
add_action(
    'admin_init',
    function () {
        global $page;

        if ( $page === 'edit-comments.php' ) {
            wp_redirect( admin_url() );
            exit;
        }
        
        remove_post_type_support( 'post', 'comments' );
        remove_post_type_support( 'page', 'comments' );
    }
);

// remove comments page from menu
add_action(
    'admin_menu',
    function () {
        remove_menu_page( 'edit-comments.php' ); // comments
        remove_submenu_page( 'options-general.php', 'options-discussion.php' ); // discussion under settings
    }
);

// remove comments from admin bar
function warpnextjs_toolbar_render() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
}
add_action( 'wp_before_admin_bar_render', 'warpnextjs_toolbar_render' );

// set preview link that it direct to nextjs app
add_filter( 'preview_post_link', function ( $preview_link, $post ) {
    $parsed_link_query = wp_parse_url( $preview_link, PHP_URL_QUERY );
    $args              = wp_parse_args( $parsed_link_query );
    $preview_id        = isset( $args['preview_id'] ) ? $args['preview_id'] : $post->ID;

    // remove params from link
    $preview_link = remove_query_arg(
        array_keys( $args ),
        $preview_link
    );

    // set preview to true
    if ( !isset( $args['preview'] )) {
        $args['preview'] = true;
    }

    // add post type
    if ( !isset( $args['post_type'] )) {
        $args['post_type'] = $post->post_type;
    }
    
    // add post/page id
    if ( !isset( $args['p'] ) || !isset( $args['page_id'] ) && $post->post_type === 'page' ) {
        $args['p'] = $preview_id;
    }

    // add unique id for cache purposes
    if ( !isset( $args['ver'] ) ) {
        $args['ver'] = uniqid();
    }

    // add params to link and change link to preview page on nextjs app
    $preview_link = add_query_arg( array($args), untrailingslashit( WP_HOME ) . '/preview/' );
    $preview_link = str_replace( untrailingslashit( WP_HOME ), untrailingslashit( NEXTJS_APP_URL ), $preview_link);

    return $preview_link;
}, 10, 2 );

// change view link for post
add_filter(
    'post_link',
    function ( $permalink, $post, $leavename ) {
        return str_replace( untrailingslashit( WP_HOME ), untrailingslashit( NEXTJS_APP_URL ), $permalink );
    },
    10,
    3
);

// change view link for page
add_filter(
    'page_link',
    function ( $permalink, $post, $leavename ) {
        return str_replace( untrailingslashit( WP_HOME ), untrailingslashit( NEXTJS_APP_URL ), $permalink );
    },
    10,
    3
);

// change view link for custom post type
add_filter(
    'post_type_link',
    function ( $permalink, $post, $leavename ) {
        return str_replace( untrailingslashit( WP_HOME ), untrailingslashit( NEXTJS_APP_URL ), $permalink );
    },
    10,
    3
);

// change view link for category
add_filter(
    'category_link',
    function ( $termlink, $term_id ) {
        return str_replace( untrailingslashit( WP_HOME ), untrailingslashit( NEXTJS_APP_URL ), $termlink );
    },
    10,
    2
);

// change view link for tag
add_filter(
    'tag_link',
    function ( $termlink, $term_id ) {
        return str_replace( untrailingslashit( WP_HOME ), untrailingslashit( NEXTJS_APP_URL ), $termlink );
    },
    10,
    2
);

// add script that fix problem of wordpress and change preview link in editor 
add_action( 'enqueue_block_editor_assets', 'enqueue_preview_scripts' );
function enqueue_preview_scripts() {
	wp_enqueue_script( 'warpnextjs-preview-links', plugins_url( 'src/assets/js/preview-links.js', dirname( __FILE__ ) ), array(), WARPNEXTJS_WP_PLUGIN_VERSION, true );
	wp_localize_script(
		'warpnextjs-preview-links',
		'_warpnextjs_data',
		array(
			'_preview_link' => get_preview_post_link(),
			'_wp_version'   => get_bloginfo( 'version' ),
		)
	);
}
