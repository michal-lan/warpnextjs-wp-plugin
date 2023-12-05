<?php
/**
 * Content Functions
 * 
 * Description: adjust wordpress settings for using next.js app
 */

// add theme support for menus
function warpnextjs_add_theme_support() {
    add_theme_support( 'menus' );
    
}
add_action( 'after_setup_theme', 'warpnextjs_add_theme_support' );

// add Menu locations
function warpnextjs_register_menu_locations() {
    $menu_locations = MENU_LOCATIONS;
    $menu_locations = preg_replace('/\s+/', '', $menu_locations);
    $menu_locations = explode(',', $menu_locations);

    if ( is_array( $menu_locations ) && !empty( $menu_locations )) {
        foreach( $menu_locations as $menu_location ) {
        	register_nav_menu(strtoupper($menu_location), ucfirst(strtolower($menu_location)));
        }
    }
}
add_action( 'init', 'warpnextjs_register_menu_locations' );

// WPGRAPHQL - Increase perPage for all queries
add_filter( 'graphql_connection_max_query_amount', function ( int $max_amount, $source, array $args, $context, $info ) {
	return WPGRAPHQL_MAX_QUERY_AMOUNT;
}, 10, 5 );

// add JWT secret key for graphql
add_filter( 'graphql_jwt_auth_secret_key', function() {
    return GRAPHQL_SECRET_KEY;
  });

// set expiration of JWT
function warpnextjs_jwt_expiration( $expiration ) {
    return 60;
}
add_filter('graphql_jwt_auth_expire', 'warpnextjs_jwt_expiration', 10);

// add domain to allowed hosts
function warpnextjs_allowed_redirect_hosts( $hosts ) {
    $hosts = wp_parse_args( $hosts, array( 'localhost', '0.0.0.0' ) );
    $nextjs_app_url = wp_parse_url( NEXTJS_APP_URL, PHP_URL_HOST );

    if ( $nextjs_app_url ) {
        $hosts[] = $nextjs_app_url;
    }

	return $hosts;
};
add_filter( 'allowed_redirect_hosts', 'warpnextjs_allowed_redirect_hosts' );