<?php

class Meow_MWL_Admin extends MeowCommon_Admin {

	public function __construct() {
		parent::__construct( MWL_PREFIX, MWL_ENTRY, MWL_DOMAIN, class_exists( 'MeowPro_MWL_Core' ) );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'app_menu' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );

			// Only loads the Lightbox Admin if we are on the Meow Dashboard or the Lightbox Settings
			// I didn't want to do this, but unfortunately the JS breaks Rank Math SEO...
			$isJsNeeded = isset( $_GET['page'] ) && ( $_GET['page'] === 'meowapps-main-menu' || $_GET['page'] === 'mwl_settings' );
			if ( $isJsNeeded ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			}
			
			$mwl_map_api_key = get_option( 'mwl_map_api_key' );
			if ( !empty( $mwl_map_api_key ) ) {
				update_option( 'mwl_googlemaps_token', $mwl_map_api_key );
				delete_option( 'mwl_map_api_key' );
			}
			$mwl_map_style = get_option( 'mwl_map_style' );
			if ( !empty( $mwl_map_style ) ) {
				update_option( 'mwl_googlemaps_style', $mwl_map_style );
				delete_option( 'mwl_map_style' );
			}
			$mwl_selector = get_option( 'mwl_selector', '.entry-content, .gallery, .mwl-gallery, .wp-block-gallery' );
			if ( empty( $mwl_selector ) ) {
				update_option( 'mwl_selector', '.entry-content, .gallery, .mwl-gallery, .wp-block-gallery' );
			}
		}
	}

	public function mwl_settings() {
		echo '<div id="mwl-admin-settings"></div>';
	}

	function enqueue_scripts() {

		// Load the "vendor" scripts
		$physical_file = MWL_PATH . '/app/admin.js';
		$cache_buster = file_exists( $physical_file ) ? filemtime( $physical_file ) : MWL_VERSION;
		wp_register_script( 'mwl-admin-js-vendor', MWL_URL . '/app/vendor.js',
			['wp-editor', 'wp-element', 'wp-i18n'], $cache_buster
		);

		// Load the "admin" scripts
		$physical_file = MWL_PATH . '/app/admin.js';
		wp_register_script( 'mwl-admin-js', MWL_URL . '/app/admin.js', array( 'mwl-admin-js-vendor' ), $cache_buster );

		// Load the fonts
		wp_register_style( 'meow-neko-ui-lato-font', '//fonts.googleapis.com/css2?family=Lato:wght@100;300;400;700;900&display=swap');
		wp_enqueue_style( 'meow-neko-ui-lato-font' );

		// Localize and options
		global $wplr;
		wp_localize_script( 'mwl-admin-js', 'mwl_meow_lightbox', array_merge( [
			//'api_nonce' => wp_create_nonce( 'mfrh_media_file_renamer' ),
			'api_url' => get_rest_url( null, '/meow-lightbox/v1/' ),
			'rest_url' => get_rest_url(),
			'plugin_url' => MWL_URL,
			'prefix' => MWL_PREFIX,
			'domain' => MWL_DOMAIN,
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			'is_pro' => class_exists( 'MeowPro_MWL_Core' ),
			'is_registered' => !!$this->is_registered()
		] ) );

		wp_enqueue_script( 'mwl-admin-js' );
	}

	function admin_notices() {
		$permastruct = get_option( 'permalink_structure' );
		if ( empty( $permastruct ) ) {
		?>
			<div class="notice notice-error is-dismissible">
					<p><?php _e( 'Meow Lightbox will not work properly if your permalinks are set up on "Plain". Please pick a dynamic structure for your permalinks (Settings > Permalinks).', 'meow-lightbox' ); ?></p>
			</div>
		<?php
		}
		if ( !function_exists( "exif_read_data" ) ) {
			?>
			<div class="notice notice-error is-dismissible">
					<p><?php _e( 'The function <i>exif_read_data</i> is not available on your server, but it is required by the Meow Lightbox. Please ask your hosting service to enable the <i>php_exif</i> module.', 'meow-lightbox' ); ?></p>
			</div>
			<?php
		}
	}

	function app_menu() {
		add_submenu_page( 'meowapps-main-menu', __( 'Lightbox', MWL_DOMAIN ), __( 'Lightbox', MWL_DOMAIN ), 
			'manage_options', 'mwl_settings', array( $this, 'mwl_settings' )
		);
	}
}

?>