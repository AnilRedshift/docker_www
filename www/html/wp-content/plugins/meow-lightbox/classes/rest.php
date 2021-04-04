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
		if ( !current_user_can( 'administrator' ) ) {
			return;
		} 

		// SETTINGS
		register_rest_route( $this->namespace, '/update_option', array(
			'methods' => 'POST',
			'permission_callback' => '__return_true',
			'callback' => array( $this, 'rest_update_option' )
		) );
		register_rest_route( $this->namespace, '/all_settings', array(
			'methods' => 'GET',
			'permission_callback' => '__return_true',
			'callback' => array( $this, 'rest_all_settings' )
		) );
		register_rest_route( $this->namespace, '/reset_cache', array(
			'methods' => 'POST',
			'permission_callback' => '__return_true',
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

	function get_all_options() {
		return array(
			'mwl_theme' => get_option( 'mwl_theme', 'dark' ),
			'mwl_download_link' => get_option( 'mwl_download_link', false ),
			'mwl_image_size' => get_option( 'mwl_image_size', 'srcset' ),
			'mwl_deep_linking' => get_option( 'mwl_deep_linking', false ),
			'mwl_low_res_placeholder' => get_option( 'mwl_low_res_placeholder', false ),
			'mwl_slideshow' => get_option( 'mwl_slideshow', false ),
			'mwl_slideshow_timer' => get_option( 'mwl_slideshow_timer', 3000 ),
			'mwl_map' => get_option( 'mwl_map', false),
			'mwl_exif_title' => get_option( 'mwl_exif_title', true ),
			'mwl_exif_caption' => get_option( 'mwl_exif_caption', true ),
			'mwl_exif_camera' => get_option( 'mwl_exif_camera', true ),
			'mwl_exif_lens' => get_option( 'mwl_exif_lens', true ),
			'mwl_exif_shutter_speed' => get_option( 'mwl_exif_shutter_speed', true ),
			'mwl_exif_aperture' => get_option( 'mwl_exif_aperture', true ),
			'mwl_exif_focal_length' => get_option( 'mwl_exif_focal_length', true ),
			'mwl_exif_iso' => get_option( 'mwl_exif_iso', true ),
			'mwl_exif_date' => get_option( 'mwl_exif_date', false ),
			'mwl_caption_origin' => get_option( 'mwl_caption_origin', 'caption' ),
			'mwl_magnification' => get_option( 'mwl_magnification', true ),
			'mwl_right_click' => get_option( 'mwl_right_click', false ),
			'mwl_output_buffering' => get_option( 'mwl_output_buffering', true ),
			'mwl_parsing_engine' => get_option( 'mwl_parsing_engine', 'HtmlDomParser' ),
			'mwl_selector' => get_option( 'mwl_selector', '.entry-content, .gallery, .mgl-gallery, .wp-block-gallery' ),
      'mwl_anti_selector' => get_option( 'mwl_anti_selector', '.blog, .archive, .emoji, .attachment-post-image' ),
      'mwl_map_engine' => get_option( 'mwl_map_engine', 'googlemaps' ),
			'mwl_googlemaps_token' => get_option( 'mwl_googlemaps_token', '' ),
			'mwl_googlemaps_style' => get_option( 'mwl_googlemaps_style', $this->create_default_googlemaps_style() ),
			'mwl_mapbox_token' => get_option( 'mwl_mapbox_token', '' ),
			'mwl_mapbox_style' => get_option( 'mwl_mapbox_style', $this->create_default_mapbox_style() ),
			'mwl_maptiler_token' => get_option( 'mwl_maptiler_token', '' ),
			'mwl_disable_cache' => get_option( 'mwl_disable_cache', '' ),
		);
	}

	// function create_default_style( $force = false ) {
	// 	$style = get_option( 'mwl_map_style', "" );
	// 	if ( $force || empty( $style ) ) {
	// 		$style = '[{"featureType":"landscape.man_made","elementType":"geometry","stylers":[{"color":"#f7f1df"}]},{"featureType":"landscape.natural","elementType":"geometry","stylers":[{"color":"#d0e3b4"}]},{"featureType":"landscape.natural.terrain","elementType":"geometry","stylers":[{"visibility":"simplified"}]},{"featureType":"poi","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi.business","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#bde6ab"},{"visibility":"simplified"}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffe15f"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#efd151"}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"road.local","elementType":"geometry.fill","stylers":[{"color":"black"}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#a2daf2"}]}]';
	// 		update_option( 'mwl_map_style', $style );
	// 	}
	// 	return $style;
	// }

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