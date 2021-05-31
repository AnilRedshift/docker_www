<?php

require_once MWL_PATH . '/vendor/autoload.php';

use DiDom\Document;
use KubAT\PhpSimple\HtmlDomParser;

class Meow_MWL_Core {

	public $images = [];

	public $isInfinite = false;
	public $isObMode = true; // use OB on the whole page, or only go through the the_content ($renderingMode will be ignored)
	public $parsingEngine = 'HtmlDomParser'; // 'HtmlDomParser' (less prone to break badly formatted HTML) or 'DiDom' (faster)
	public $renderingMode = 'rewrite'; // 'replace' within the HTML or 'rewrite' the DOM completely
	public $imageSize = false;
	public $disableCache = false;

	public function __construct() {

		if ( MeowCommon_Helpers::is_rest() ) {
			new Meow_MWL_Rest( $this );
		}

		if ( class_exists( 'MeowPro_MWL_Core' ) ) {
			new MeowPro_MWL_Core( $this );
		}

		// The Lightbox should be completely off if the request is asynchronous
		$recent_common = method_exists( 'MeowCommon_Helpers', 'is_pagebuilder_request' );
		if ( MeowCommon_Helpers::is_asynchronous_request() || ( $recent_common && MeowCommon_Helpers::is_pagebuilder_request() ) ) {
			return;
		}

		$this->imageSize = get_option( 'mwl_image_size', 'srcset' );
		$this->disableCache = get_option( 'mwl_disable_cache' );
		$this->isInfinite = get_option( 'mgl_infinite', false );
		$this->isObMode = get_option( 'mwl_obmode', $this->isObMode );
		$this->parsingEngine = get_option( 'mwl_parsing_engine', $this->parsingEngine );
		$this->renderingMode = $this->isObMode ? 'rewrite' : get_option( 'mwl_rendering_mode', $this->renderingMode );

		//$this->parsingEngine = 'HtmlDomParser';

		// Admin
		if ( is_admin() ) {
			load_plugin_textdomain( MWL_DOMAIN, false, MWL_PATH . '/languages' );
			add_action( 'edit_attachment', array( $this, 'edit_attachment' ), 10, 1 );
			new Meow_MWL_Admin();
		}
		// Client
		else if ( !MeowCommon_Helpers::is_rest() ) {
			new Meow_MWL_Filters();
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			if ( !$this->isObMode ) {
				// Standard Mode
				
				// Try to take advantage of the Responsive Images feature of WP 4.4+ to make things faster.
				add_filter( 'wp_get_attachment_image_attributes', array( $this, 'wp_get_attachment_image_attributes' ), 10, 2 );
				
				// Analyze only page/post content and write the data in the footer.
				add_filter( 'the_content', array( $this, 'lightboxify' ), 20 );
				add_action( 'wp_footer', array( $this, 'wp_footer' ), 100 );
			}
			else {
				// OB Mode
				// Read the whole page, and add the mwl_data in the head.
				add_action( 'init', array( $this, 'start_ob' ) );
				add_action( 'shutdown', array( $this, 'end_ob' ), 100 );
				$this->renderingMode = 'rewrite';
			}
		}
	}

	function enqueue_scripts() {
		// CSS
    $physical_file = MWL_PATH . '/app/style.min.css';
    $cache_buster = file_exists( $physical_file ) ? filemtime( $physical_file ) : MWL_VERSION;
    wp_enqueue_style( 'leaflet-css', plugins_url( '/app/style.min.css', __DIR__ ), null, $cache_buster );

		// Touchswipe
		$physical_file = MWL_PATH . '/app/touchswipe.min.js';
    $cache_buster = file_exists( $physical_file ) ? filemtime( $physical_file ) : MWL_VERSION;
    // wp_enqueue_script( 'touchswipe-js', plugins_url( '/app/touchswipe.min.js', __DIR__ ), array('jquery'), $cache_buster, false );

		// JS
		$physical_file = MWL_PATH . '/app/lightbox.js';
    $cache_buster = file_exists( $physical_file ) ? filemtime( $physical_file ) : MWL_VERSION;
    wp_enqueue_script( 'mwl-build-js', plugins_url( '/app/lightbox.js', __DIR__ ), array( 'jquery' ), $cache_buster, false );

		wp_localize_script( 'mwl-build-js', 'mwl',
			array(
				'plugin_url' => plugin_dir_url(__FILE__),
				'settings' => array(
					'theme' => get_option( 'mwl_theme', 'dark' ),
					'orientation' => get_option( 'mwl_orientation', 'auto' ),
					'selector' => get_option( 'mwl_selector', '.entry-content, .gallery, .mgl-gallery, .wp-block-gallery' ),
					'deep_linking' => get_option( 'mwl_deep_linking', false ),
					'low_res_placeholder' => get_option( 'mwl_low_res_placeholder', false ),
					'right_click_protection' => !get_option( 'mwl_right_click', false ),
					'magnification' => get_option( 'mwl_magnification', true ),
					'anti_selector' => get_option( 'mwl_anti_selector', '.blog, .archive, .emoji, .attachment-post-image' ),
					'preloading' => get_option( 'mwl_preloading', false ),
					'download_link' => get_option( 'mwl_download_link', false ),
					'caption_source' => get_option( 'mwl_caption_origin', 'caption' ),
					'animation' => get_option( 'mwl_animation', 'zoomIn' ),
					'exif' => array(
						'title' => get_option( 'mwl_exif_title', true ),
						'caption' => get_option( 'mwl_exif_caption', true ),
						'camera' => get_option( 'mwl_exif_camera', true ),
						'date' => get_option( 'mwl_exif_date', true ),
						'lens' => get_option( 'mwl_exif_lens', false ),
						'shutter_speed' => get_option( 'mwl_exif_shutter_speed', true ),
						'aperture' => get_option( 'mwl_exif_aperture', true ),
						'focal_length' => get_option( 'mwl_exif_focal_length', true ),
						'iso' => get_option( 'mwl_exif_iso', true ),
						'date' => get_option( 'mwl_exif_date', false )
					),
					'slideshow' => array(
						'enabled' => get_option( 'mwl_slideshow', false ),
						'timer' => get_option( 'mwl_slideshow_timer', 3000 )
					),
					'map' => array(
						'enabled' => get_option( 'mwl_map', false ),
						// 'engine' => get_option( 'mwl_map_engine', 'googlemaps' ),
						// 'api_key' => get_option( 'mwl_map_api_key', "" ),
						// 'style' => json_decode( get_option( 'mwl_map_style', null ) )
						// 'position' => get_option( 'mwl_map_position', 'bottom-right' ),
						// 'margin' => (int)get_option( 'mwl_map_margin', 10 ),
						// 'size' => (int)get_option( 'mwl_map_size', 70 )
					)
				)
			)
		);

		// Remove PrettyPhoto (Visual Composer's Lightbox)
		wp_enqueue_script( 'prettyphoto' );
		wp_deregister_script( 'prettyphoto' );
	}

	/*******************************************************************************
	 * RUNNING OPERATIONS
	 ******************************************************************************/

	function edit_attachment( $post_id ) {
		delete_transient( 'mwl_exif_' . $post_id . '_XX' );
		delete_transient( 'mwl_exif_' . $post_id . '_OO' );
		delete_transient( 'mwl_exif_' . $post_id . '_XO' );
		delete_transient( 'mwl_exif_' . $post_id . '_OX' );
	}

	/*******************************************************************************
	 * HELPERS
	 ******************************************************************************/

	function get_exif_info( $id ) {

		// The transient should only match a certain media entry with three given options, as only those three options
		// has an influence on the process that follows
		if ( !$this->disableCache ) {
			$transient_name = 'mwl_exif_' . $id . '_' . ( get_option( 'mwl_map', false ) ? 'O' : 'X' ) .
				( get_option( 'mwl_exif_lens', false ) ? 'O' : 'X' ) . ( $this->imageSize === 'srcset' ? '' : ( '_' . $this->imageSize ) );
			$info = get_transient( $transient_name );
			if ( $info ) {
				return $info;
			}
		}
		
		// Get the attachment and its meta
		$p = get_post( $id );
		$meta = wp_get_attachment_metadata( $id );

		// If missing meta or attachment
		if ( empty( $meta ) || empty( $p ) ) {
			$message = "No meta was found for this ID.";
			if ( !wp_attachment_is_image( $id ) )
				$message = "This attachment does not exist or is not an image.";
			return array(
				'success' => false,
				'message' => $message
			);
		}

		// Check for special metadata (gps, lens)
		if ( !isset( $meta['image_meta']['geo_coordinates'] ) && get_option( 'mwl_map', false ) ) {
			Meow_MWL_Exif::get_gps_data( $id, $meta );
		}
		$displayLens = get_option( 'mwl_exif_lens', false );
		if ( $displayLens && !isset( $meta['image_meta']['lens'] ) ) {
			$file = get_attached_file( $id );
			$pp = pathinfo( $file );
			$meta['image_meta']['lens'] = "";
			if ( in_array( strtolower( $pp['extension'] ), array( 'jpg', 'jpeg', 'tiff' ) ) ) {
				$exif = @exif_read_data( $file );
				if ( $exif && isset( $exif['UndefinedTag:0xA434'] ) )
					$meta['image_meta']['lens'] = empty( $exif['UndefinedTag:0xA434'] ) ? "" : $exif['UndefinedTag:0xA434'];
			}
			wp_update_attachment_metadata( $id, $meta );
		}

		// Prepare the final info variable containing the metadata
		$title = isset( $p->post_title ) ? $p->post_title : "";
		$caption =  isset( $p->post_excerpt ) ? $p->post_excerpt : "";
		$description = isset( $p->post_content ) ? $p->post_content : "";
		$file = null;
		$file_srcset = null;
		$file_sizes = null;
		if ( $this->imageSize === 'srcset' ) {
			$file = wp_get_attachment_url( $id );
			$file_srcset = wp_get_attachment_image_srcset( $id, 'full' );
			$file_sizes = wp_get_attachment_image_sizes( $id, 'full' );
		}
		else {
			$arr = wp_get_attachment_image_src( $id, $this->imageSize );;
			$file = $arr[0];
		}
		if ( !isset(  $meta['image_meta']['geo_coordinates'] ) )
			$meta['image_meta']['geo_coordinates'] = '';

		$date_format = get_option( 'date_format' ) . ' - ' . get_option( 'time_format' );
		$date = date( $date_format, $meta['image_meta']['created_timestamp'] );

		$info = array(
			'success' => true,
			'file' => $file,
			'file_srcset' => $file_srcset,
			'file_sizes' => $file_sizes,
			'dimension' => array( 'width' => $meta['width'], 'height' => $meta['height'] ),
			'data' => array(
				'id' => (int)$id,
				'title' => apply_filters( 'mwl_img_title', $title, $id, $meta ),
				'caption' => apply_filters( 'mwl_img_caption', $caption, $id, $meta ),
				'description' => apply_filters( 'mwl_img_description', $description, $id, $meta ),
				'gps' => apply_filters( 'mwl_img_gps', $meta['image_meta']['geo_coordinates'],	$id, $meta ),
				'copyright' => apply_filters( 'mwl_img_copyright', $meta['image_meta']['copyright'], $id, $meta ),
				'camera' => apply_filters( 'mwl_img_camera',  $meta['image_meta']['camera'], $id, $meta ),
				'date' => apply_filters( 'mwl_img_date', $date, $id, $meta ),
				'lens' => apply_filters( 'mwl_img_lens', $displayLens ? $meta['image_meta']['lens'] : '', $id, $meta ),
				'aperture' => apply_filters( 'mwl_img_aperture', $meta['image_meta']['aperture'], $id, $meta ),
				'focal_length' => apply_filters( 'mwl_img_focal_length', $meta['image_meta']['focal_length'], $id, $meta ),
				'iso' => apply_filters( 'mwl_img_iso', $meta['image_meta']['iso'], $id, $meta ),
				'shutter_speed' => apply_filters( 'mwl_img_shutter_speed', $meta['image_meta']['shutter_speed'], $id, $meta )
			)
		);
		if ( !$this->disableCache ) {
			set_transient( $transient_name, $info, 3 * MONTH_IN_SECONDS );
		}
		return $info;
	}

	static function installed() {
		return true;
	}

	/******************************************************************************
		FUNCTIONS TO CLEAN AND GET THE MEDIA IDS FROM ATTACHMENT URLS
	******************************************************************************/

	// Clean the path from the domain and common folders
	// Originally written for the WP Retina 2x plugin
	function get_pathinfo_from_image_src( $image_src ) {
		$uploads = wp_upload_dir();
		$uploads_url = trailingslashit( $uploads['baseurl'] );
		if ( strpos( $image_src, $uploads_url ) === 0 )
			return ltrim( substr( $image_src, strlen( $uploads_url ) ), '/');
		else if ( strpos( $image_src, wp_make_link_relative( $uploads_url ) ) === 0 )
			return ltrim( substr( $image_src, strlen( wp_make_link_relative( $uploads_url ) ) ), '/');
		$img_info = parse_url( $image_src );
		return ltrim( $img_info['path'], '/' );
	}

	function resolve_image_id( $url ) {
		global $wpdb;
		$pattern = '/[_-]\d+x\d+(?=\.[a-z]{3,4}$)/';
		$url = preg_replace( $pattern, '', $url );
		$url = $this->get_pathinfo_from_image_src( $url );
		$query = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid LIKE '%s'", '%' . $url . '%' );
		$attachment = $wpdb->get_col( $query );
		return empty( $attachment ) ? null : $attachment[0];
	}

	/******************************************************************************
		GENERATING PAGE PROCESS
	******************************************************************************/

	// When we are lucky (within a gallery), we can do this nicely, no need of the ob
	function wp_get_attachment_image_attributes( $attr, $attachment ) {
		$id = $attachment->ID;
		if( !strpos( $attr['class'], 'wp-image-' . $id ) )
			$attr['class'] .= ' wp-image-' . $id;
		if ( empty( $attr['data-mwl-img-id'] ) ) {
			if ( !in_array( $id, $this->images ) )
				array_push( $this->images, $id );
			$attr['data-mwl-img-id'] = $id;
		}
		return $attr;
	}

	function lightboxify_element( $element, $buffer ) {
		$mediaId = null;
		$classes = '';
		$from = substr( $element, 0 );

		// Get the classes
		if ( $this->parsingEngine === 'HtmlDomParser' ) {
			$classes = $element->class;
		}
		else {
			$classes = $element->attr('class');
		}

		if ( preg_match( '/wp-image-([0-9]{1,10})/i', $classes, $matches ) ) {
			// The wp-image-xxx class exists, let's use it.
			$mediaId = $matches[1];
		}
		else {
			// Otherwise, resolve the ID from the URL.
			$src = null;
			$mglSrc = null;
			$url = null;
			if ( $this->parsingEngine === 'HtmlDomParser' ) {
				$src = $element->src;
				$mglSrc = $element->{'mgl-src'};
			}
			else {
				$src = $element->attr('src');
				$mglSrc = $element->attr('mgl-src');
			}

			// Look for the url and its mediaId.
			if ( $this->isInfinite )
				$url = $mglSrc;
			if ( empty( $url ) )
				$url = $src;
			if ( !empty( $url ) )
				$mediaId = $this->resolve_image_id( $url );
		}

		if ( $mediaId ) {
			// If the mediaId exists, let's add it to the DOM.
			if ( $this->parsingEngine === 'HtmlDomParser' ) {
				$element->{'data-mwl-img-id'} = $mediaId;
			}
			else {
				$element->attr( 'data-mwl-img-id', $mediaId );
			}
			if ( !in_array( $mediaId, $this->images ) )
				array_push( $this->images, $mediaId );
			return $this->renderingMode === 'replace' ? str_replace( trim( $from, "</> "), trim( $element, "</> " ), $buffer ) : 1;
		}
		return $this->renderingMode === 'replace' ? false : $buffer;
	}

	function lightboxify( $buffer ) {
		if ( !isset( $buffer ) || trim( $buffer ) === '' )
			return $buffer;

		// Initialize engine
		$html = null;
		$hasChanges = false;

		if ( $this->parsingEngine === 'HtmlDomParser' ) {
			$html = new HtmlDomParser();
			$html = $html->str_get_html( $buffer, true, true, DEFAULT_TARGET_CHARSET, false );
		}
		else {
			$html = new Document();
			if ( defined( 'LIBXML_HTML_NOIMPLIED' ) && defined( 'LIBXML_HTML_NODEFDTD' ) )
				$html->loadHtml( $buffer, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
			else
				$html->loadHtml( $buffer, 0 );

		}

		if ( !$html ) {
			error_log( 'Meow Lightbox: the DOM is empty.' );
			return $buffer;
		}

		// Browses CSS classes
		//$classes = get_option( 'mwl_selector', '.entry-content, .gallery, .mgl-gallery, .wp-block-gallery' );
		//$classes = explode( ',', $classes );
		$classes = array( '' ); // Go through all the images in the content.
		foreach ( $classes as $class ) {
			$class = trim( $class );
			foreach ( $html->find( $class . ' img' ) as $element ) {
				if ( $this->renderingMode === 'replace' ) {
					$buffer = $this->lightboxify_element( $element, $buffer );
				}
				else {
					$hasChanges = $this->lightboxify_element( $element, $buffer ) || $hasChanges;
				}
			}
		}

		if ( $this->isObMode ) {
			$mwlData = $this->write_mwl_data( true );
			$matches = preg_split('/(<body.*?>)/i', $html, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			$head = isset( $matches[0] ) ? $matches[0] : '';
			$body = isset( $matches[1] ) ? $matches[1] : '';
			$footer = isset( $matches[2] ) ? $matches[2] : '';
			$html = $head . $body . $mwlData . $footer;
		}

		if ( $this->renderingMode === 'replace' )
			return $buffer;
		return $hasChanges ? $html : $buffer;
	}

	function write_mwl_data( $returnOnly = false ) {
		$images_info = [];
		foreach ( $this->images as $image ) {
			$images_info[$image] = $this->get_exif_info( $image );
		}
		$html = '<script type="application/javascript">' . PHP_EOL;
		$html .= 'var mwl_data = ' . json_encode( $images_info ) . ';' . PHP_EOL;
		$html .= '</script>' . PHP_EOL;
		if ( $returnOnly )
			return $html;
		echo $html;
	}

	function wp_footer() {
		$this->write_mwl_data();
	}

	/******************************************************************************
		OB MODE
	******************************************************************************/

	function start_ob() {
		ob_start( array( $this, "lightboxify" ) );
	}

	function end_ob() {
		@ob_end_flush();
	}

}

?>