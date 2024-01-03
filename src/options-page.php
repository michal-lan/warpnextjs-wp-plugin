<?php
/**
 * Options Page
 * 
 * Description: add options page to the plugin
 * 
 * This solution is based on https://github.com/wpengine/faustjs/blob/canary/plugins/faustwp/includes/settings
 */

add_action( 'admin_menu', 'register_settings_menu' );
function register_settings_menu() {
	add_submenu_page(
		'options-general.php',
		__( 'WarpNext.js', 'warpnextjs-wp-plugin' ),
		__( 'WarpNext.js', 'warpnextjs-wp-plugin' ),
		'manage_options',
		'warpnextjs-wp-plugin-settings',
		'display_settings_page'
	);
}

function display_settings_page() {
    ?>
    <h2><?php esc_html_e( 'WarpNext.js Headless Settings', 'warpnextjs-wp-plugin' ); ?></h2>

    <form action="options.php" method="POST">
        <?php settings_fields( 'warpnextjs_settings' ); ?>
        <?php do_settings_sections( 'warpnextjs-wp-plugin-settings' ); ?>
        <?php submit_button(); ?>
    </form>
    <?php
}

add_action( 'admin_init', 'register_settings' );
function register_settings() {
	register_setting(
        'warpnextjs_settings',
        'warpnextjs_settings',
    );
}

add_action( 'admin_init', 'register_settings_section' );
function register_settings_section() {
	add_settings_section(
		'settings_section',
		null,
		null,
		'warpnextjs-wp-plugin-settings'
	);
}
add_action( 'admin_init', 'register_settings_fields' );

function register_settings_fields() {
	add_settings_field(
		'frontend_url',
		__( 'Front-end site URL', 'warpnextjs-wp-plugin' ),
		'display_frontend_url_field',
		'warpnextjs-wp-plugin-settings',
		'settings_section',
		array(
			'class'     => 'align-middle',
			'label_for' => 'frontend_url',
		)
	);

	add_settings_field(
		'secret_key',
		__( 'Secret Key', 'warpnextjs-wp-plugin' ),
		__NAMESPACE__ . '\\display_secret_key_field',
		'warpnextjs-wp-plugin-settings',
		'settings_section',
		array(
			'class'     => 'align-middle',
			'label_for' => 'secret_key',
		)
	);

	add_settings_field(
		'menu_locations',
		__( 'Menu Locations', 'warpnextjs-wp-plugin' ),
		__NAMESPACE__ . '\\display_menu_locations_field',
		'warpnextjs-wp-plugin-settings',
		'settings_section',
		array(
			'class'     => 'align-middle',
			'label_for' => 'menu_locations',
		)
	);
}

function display_frontend_url_field() {
    $frontend_url = WarpNextSetting( 'frontend_url', 'http://localhost:3000' );

    ?>
    <input type="text" id="frontend_url" name="warpnextjs_settings[frontend_url]" value="<?php echo esc_attr( $frontend_url ); ?>" class="regular-text" />
    <p class="description">
        <?php esc_html_e( 'The full URL to your headless front-end, including https:// or http://. This is used for authenticated post previews and for rewriting links to point to your front-end site.', 'warpnextjs-wp-plugin' ); ?>
    </p>
    <?php
}

function display_secret_key_field() {
    $secret_key     = WarpNextSetting('secret_key' , '');
	$regenerate_url = wp_nonce_url(
        admin_url( 'options-general.php?page=warpnextjs-wp-plugin-settings' ),
		'regenerate_secret',
		'regenerate_nonce'
	);
    
	?>
	<input type="text" id="secret_key" value="<?php echo esc_attr( $secret_key ); ?>" class="regular-text code" readonly />
	<input type="hidden" name="warpnextjs_settings[secret_key]" value="<?php echo esc_attr( $secret_key ); ?>" />

	<a
		href="<?php echo esc_url( $regenerate_url ); ?>"
		title="<?php esc_attr_e( 'Regenerate Secret Key', 'warpnextjs-wp-plugin' ); ?>"
		onclick="confirm_regenerate_key( event )"
		class="field-action"
	>
		<?php esc_html_e( 'Regenerate', 'warpnextjs-wp-plugin' ); ?>
	</a>

	<script type="text/javascript">
		function confirm_regenerate_key( event ) {
			if ( ! confirm( 'Are you sure you want to regenerate your secret key?' ) ) {
				event.preventDefault();
			}
		}
	</script>

	<p class="description">
		<?php
            esc_html_e( 'This key is used to enable headless post previews and make authenticated GraphQL requests for schema generation.', 'warpnextjs-wp-plugin' );
		?>
	</p>
	<?php
}

function display_menu_locations_field() {
    $menu_locations = WarpNextSetting( 'menu_locations', 'Primary, Footer' );

    ?>
    <input type="text" id="menu_locations" name="warpnextjs_settings[menu_locations]" value="<?php echo esc_attr( $menu_locations ); ?>" class="regular-text" />

    <p class="description">
        <?php esc_html_e( 'A comma-separated list of menu locations. Assign menus to locations at Appearance → Menus.', 'warpnextjs-wp-plugin' ); ?>
    </p>
    <?php
}

add_action( 'load-settings_page_warpnextjs-wp-plugin-settings', 'handle_regenerate_secret_key', 5 );
function handle_regenerate_secret_key() {
	$screen = get_current_screen();
	if ( 'settings_page_warpnextjs-wp-plugin-settings' !== $screen->id ) {
		return;
	}

	if ( empty( $_GET['regenerate_nonce'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	check_admin_referer( 'regenerate_secret', 'regenerate_nonce' );

	WarpNextUpdateSetting( 'secret_key', wp_generate_uuid4() );

	wp_safe_redirect(
		admin_url( '/options-general.php?page=warpnextjs-wp-plugin-settings' )
	);

	exit;
}


add_filter( 'sanitize_option_warpnextjs_settings', 'sanitize_warpnextjs_wp_plugin_settings', 10, 2 );
function sanitize_warpnextjs_wp_plugin_settings(  $settings, $option ) {
    $errors    = null;
	$protocols = array( 'http', 'https' );
	foreach ( $settings as $name => $value ) {
		switch ( $name ) {
			case 'frontend_url':
				if ( '' === $value || preg_match( '#http(s?)://(.+)#i', $value ) ) {
					$settings[ $name ] = esc_url_raw( $value, $protocols );
				} else {
					$errors[ $name ]   = __( 'The Front-end site URL you entered did not appear to be a valid URL. Please enter a valid URL.', 'warpnextjs-wp-plugin' );
					$settings[ $name ] = WarpNextSetting( $name );
				}
				break;

			case 'secret_key':
				if ( ! wp_is_uuid( $value, 4 ) ) {
					$errors[ $name ]   = __( 'The secret key you entered did not appear to be a valid UUID.', 'warpnextjs-wp-plugin' );
					$settings[ $name ] = WarpNextSetting( $name );
				}
				break;

			case 'menu_locations':
				$settings[ $name ] = sanitize_text_field( $value );
				break;

            default:
				// Remove any settings we don't expect.
				unset( $settings[ $name ] );
		}
	}

	if ( null !== $errors && is_array( $errors ) ) {
		foreach ( $errors as $name => $error ) {
			add_settings_error( $option, "warpnextjs-wp-plugin_invalid_{$name}", $error );
		}
	}

	return $settings;
}