<?php
/**
 * Plugin Name: WarpNext.js WP Plugin
 * Plugin URI: https://github.com/michal-lan/warpnextjs-wp-plugin
 * Description: This is a WordPress plugin that allows easy integration with WarpNext.js
 * Author: Michał Łań
 * Author URI: https://github.com/michal-lan
 * Text Domain: warpnextjs-wp-plugin
 * Version: 1.0
 * Requires PHP: 8.0
 * Requires at least: 6.3.2
 */

// File Security Check.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// It's recommended that you use something like the WordPress Salt generator (https://api.wordpress.org/secret-key/1.1/salt/) to generate a Secret.
define( 'GRAPHQL_SECRET_KEY', 'Fc@<WLh|kevB{p({Ti?ta|LNu?O`y-9jzdiKaR%L-f)set=?SnU3psI;UO+BvS40' );
define( 'WPGRAPHQL_MAX_QUERY_AMOUNT', 1000);

define( 'WP_HOMEPAGE', get_home_url() );
define( 'WARPNEXTJS_WP_PLUGIN_VERSION', '1.0' );
define( 'WARPNEXTJS_WP_PLUGIN_FILE', __FILE__ );
define( 'WARPNEXTJS_WP_PLUGIN_DIR', untrailingslashit( dirname( WARPNEXTJS_WP_PLUGIN_FILE ) ) );

require_once WARPNEXTJS_WP_PLUGIN_DIR . '/src/content-functions.php';
require_once WARPNEXTJS_WP_PLUGIN_DIR . '/src/admin-panel.php';
require_once WARPNEXTJS_WP_PLUGIN_DIR . '/src/options-page.php';
