<?php

class Meow_MWL_Rest
{
  private $core;
	private $namespace = 'meow-lightbox/v1';

	public function __construct( $core ) {
    $this->core = $core;

		// FOR DEBUG
		// For experiencing the UI behavior on a slower install.
		// sleep(1);
		// For experiencing the UI behavior on a buggy install.
		// trigger_error( "Error", E_USER_ERROR);
		// trigger_error( "Warning", E_USER_WARNING);
		// trigger_error( "Notice", E_USER_NOTICE);
		// trigger_error( "Deprecated", E_USER_DEPRECATED);

		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	function rest_api_init() {
		register_rest_route( $this->namespace, '/update_option', array(
			'methods' => 'POST',
			'permission_callback' => array( $this->core, 'can_access_settings' ),
			'callback' => array( $this, 'rest_update_option' )
		) );
		register_rest_route( $this->namespace, '/all_settings', array(
			'methods' => 'GET',
			'permission_callback' => array( $this->core, 'can_access_settings' ),
			'callback' => array( $this, 'rest_all_settings' )
		) );
		register_rest_route( $this->namespace, '/reset_cache', array(
			'methods' => 'POST',
			'permission_callback' => array( $this->core, 'can_access_settings' ),
			'callback' => array( $this, 'rest_reset_cache' )
		) );
  }

	function rest_all_settings() {
		return new WP_REST_Response( [ 'success' => true, 'data' => $this->get_all_options() ], 200 );
	}

	function rest_reset_cache() {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_mwl_exif_%'" );
		return new WP_REST_Response( [ 'success' => true ], 200 );
	}

	// List all the options with their default values.
	function list_options() {
		return array(
			'mwl_theme' => 'dark',
			'mwl_download_link' => false,
			'mwl_image_size' => 'srcset',
			'mwl_deep_linking' => false,
			'mwl_low_res_placeholder' => false,
			'mwl_slideshow' => false,
			'mwl_slideshow_timer' => 3000,
			'mwl_map' => false,
			'mwl_exif_title' => true,
			'mwl_exif_caption' => true,
			'mwl_exif_camera' => true,
			'mwl_exif_lens' => true,
			'mwl_exif_shutter_speed' => true,
			'mwl_exif_aperture' => true,
			'mwl_exif_focal_length' => true,
			'mwl_exif_iso' => true,
			'mwl_exif_date' => false,
			'mwl_caption_origin' => 'caption',
			'mwl_magnification' => true,
			'mwl_right_click' => false,
			'mwl_social_sharing' => false,
			'mwl_output_buffering' => true,
			'mwl_parsing_engine' => 'HtmlDomParser',
			'mwl_selector' => '.entry-content, .gallery, .mgl-gallery, .wp-block-gallery',
      'mwl_anti_selector' => '.blog, .archive, .emoji, .attachment-post-image, .no-lightbox',
      'mwl_map_engine' => 'googlemaps',
			'mwl_googlemaps_token' => '',
			'mwl_googlemaps_style' => $this->create_default_googlemaps_style(),
			'mwl_mapbox_token' => '',
			'mwl_mapbox_style' => $this->create_default_mapbox_style(),
			'mwl_maptiler_token' => '',
			'mwl_disable_cache' => '',
			'mwl_map_zoom_level' => 12,
		);
	}

	function get_all_options() {
		$options = $this->list_options();
		$current_options = array();
		foreach ( $options as $option => $default ) {
			$current_options[$option] = get_option( $option, $default );
		}
		return $current_options;
	}

  function create_default_googlemaps_style( $force = false ) {
		$style = get_option( 'mwl_googlemaps_style', "" );
		if ( $force || empty( $style ) ) {
			$style = '[]';
			update_option( 'mwl_googlemaps_style', $style );
		}
		return $style;
	}

	function create_default_mapbox_style( $force = false ) {
		$style = get_option( 'mwl_mapbox_style', "" );
		if ( $force || empty( $style ) ) {
			$style = '{"username":"", "style_id":""}';
			update_option( 'mwl_mapbox_style', $style );
		}
		return $style;
	}

	function rest_update_option( $request ) {
		$params = $request->get_json_params();
		try {
			$name = $params['name'];
			$options = $this->list_options();
			if ( !array_key_exists( $name, $options ) ) {
				return new WP_REST_Response([ 'success' => false, 'message' => 'This option does not exist.' ], 200 );
			}
			$value = is_bool( $params['value'] ) ? ( $params['value'] ? '1' : '' ) : $params['value'];
			$success = update_option( $name, $value );
			if ( !$success ) {
				return new WP_REST_Response([ 'success' => false, 'message' => 'Could not update option.' ], 200 );
			}
			return new WP_REST_Response([ 'success' => true, 'data' => $value ], 200 );
		} 
		catch (Exception $e) {
			return new WP_REST_Response([ 'success' => false, 'message' => $e->getMessage() ], 500 );
		}
	}

}

?>