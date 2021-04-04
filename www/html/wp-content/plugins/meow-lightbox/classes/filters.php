<?php

class Meow_MWL_Filters {

	public function __construct() {
		// add_filter( 'mwl_img_title', array( $this, 'mwl_img_camera' ), 5, 3 );
		// add_filter( 'mwl_img_caption', array( $this, 'mwl_img_camera' ), 5, 3 );
		// add_filter( 'mwl_img_description', array( $this, 'mwl_img_camera' ), 5, 3 );
		add_filter( 'mwl_img_camera', array( $this, 'mwl_img_camera' ), 5, 3 );
		add_filter( 'mwl_img_lens', array( $this, 'mwl_img_lens' ), 5, 3 );
		add_filter( 'mwl_img_gps', array( $this, 'mwl_img_gps' ), 5, 3 );
		add_filter( 'mwl_img_aperture', array( $this, 'mwl_img_aperture' ), 5, 3 );
		add_filter( 'mwl_img_focal_length', array( $this, 'mwl_img_focal_length' ), 5, 3 );
		add_filter( 'mwl_img_iso', array( $this, 'mwl_img_iso' ), 5, 3 );
		add_filter( 'mwl_img_shutter_speed', array( $this, 'mwl_img_shutter_speed' ), 5, 3 );
		add_filter( 'mwl_img_copyright', array( $this, 'mwl_img_copyright' ), 5, 3 );
	}

	// This function will be improved over time
	function nice_lens( $lens ) {
		if ( empty( $lens ) )
			return $lens;
		$lenses = array(
			// Generic
			"----" => "N/A",
			"0.0 mm f/0.0" => "N/A",
			"70.0-200.0 mm f/2.8" => "70-200mm f/2.8",
			"85.0 mm f/1.4" => "85mm f/1.4",
			"24.0-70.0 mm f/2.8" => "24-70mm f/2.8",
			"14.0-24.0 mm f/2.8" => "14-24mm f/2.8",
			// Nikon
			"AF-S Zoom-Nikkor 14-24mm f/2.8G ED" => "14-24mm f/2.8",
			// Canon
			"EF-S17-55mm f/2.8 IS USM" => "17-55mm f/2.8",
			"EF11-24mm f/4L USM" => "11-24mm f/4",
			"EF24-70mm f/2.8L II USM" => "24-70mm f/2.8",
			// Rolleiflex
			"XCD 45" => "XCD 45mm"
		);
		if ( isset( $lenses[$lens] ) )
			return $lenses[$lens];
		else
			return $lens;
	}

	// This function will be improved over time
	function nice_camera( $camera ) {
		if ( empty( $camera ) )
			return $camera;
		$cameras = array(
			"ILCE-6000" => "SONY α6000",
			"ILCE-7RM2" => "SONY α7R II",
			"ILCE-7RM3" => "SONY α7R III",
			"X1D II 50C" => "Rolleiflex X1D II",
			"GA645Zi" => "Fujifilm GA645Zi",
			"045F-2" => "Chamonix 045F-2",
			"MK3" => "Intrepid MK3",
			"X-T2" => "FUJIFILM X-T2",
			"Canon EOS 5D Mark IV" => "Canon 5D Mark IV",
			"GA645Zi" => "Fujifilm GA645Zi",
			"045F-2" => "Chamonix 045F-2",
			"MK3" => "Intrepid MK3",
			"XG-1" => "Minolta XG-1",
			"OM10" => "Olympus OM10",
			"F50" => "Nikon F50",
			"V-Lux 20" => "Leica V-Lux 20",
			"Optio E70L" => "Pentax Optio E70L",
			"E990" => "Nikon E990",
		);
		if ( isset( $cameras[$camera] ) )
			return $cameras[$camera];
		else
			return $camera;
	}

	function nice_shutter_speed( $shutter_speed ) {
		$str = "";
		if ( ( 1 / $shutter_speed ) > 1) {
			$str .= "1/";
			if ( number_format( ( 1 / $shutter_speed ), 1) ==  number_format( ( 1 / $shutter_speed ), 0 ) )
				$str .= number_format( ( 1 / $shutter_speed ), 0, '.', '' ) . '';
			else
				$str .= number_format( ( 1 / $shutter_speed ), 0, '.', '' ) . '';
		}
		else
			$str .= $shutter_speed . ' sec';
		return $str;
	}

	function mwl_img_lens( $value, $mediaId, $meta ) {
		$text = empty( $value ) ? "N/A" : $this->nice_lens( $value );
		return $text;
	}

	function mwl_img_camera( $value, $mediaId, $meta ) {
		$text = empty( $value ) ? "N/A" : $this->nice_camera( $value );
		return $text;
	}

	function mwl_img_aperture( $value, $mediaId, $meta ) {
		$text = empty( $value ) ? "N/A" : ( "f/" . $value );
		return $text;
	}

	function mwl_img_focal_length( $value, $mediaId, $meta ) {
		$text = empty( $value ) ? "N/A" : ( round( $value, 0 ) . "mm" );
		return $text;
	}

	function mwl_img_iso( $value, $mediaId, $meta ) {
		$text = empty( $value ) ? "N/A" : ( "ISO " . $value );
		return $text;
	}

	function mwl_img_shutter_speed( $value, $mediaId, $meta ) {
		$text = empty( $value ) ? "N/A" : $this->nice_shutter_speed( $value );
		return $text;
	}

	function mwl_img_copyright( $value, $mediaId, $meta ) {
		$text = empty( $value ) ? "N/A" : $value;
		return $text;
	}

	function mwl_img_gps( $value, $mediaId, $meta ) {
		$text = empty( $value ) ? "N/A" : $value;
		return $text;
	}
}

?>