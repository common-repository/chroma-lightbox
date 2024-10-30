<?php

// The public-facing functionality of the plugin.
class chroma_lightbox_Public {

	// The ID of this plugin.
	private $chroma_lightbox;

	// The version of this plugin.
	private $version;

	public $img_sizes;

	// Initialize the class and set its properties.
	public function __construct( $chroma_lightbox, $version ) {

		$this->chroma_lightbox = $chroma_lightbox;
		$this->version = $version;
		$this->img_sizes = $this->get_image_sizes();



	}

	// Register the stylesheets for the public-facing side of the site.
	public function enqueue_styles() {
		wp_enqueue_style( $this->chroma_lightbox."-public", plugin_dir_url( __FILE__ ) . 'css/chroma_lightbox-public.css', array(), $this->version, 'all' );



	}

	// Register the JavaScript for the public-facing side of the site.
	public function enqueue_scripts() {
		wp_enqueue_script( $this->chroma_lightbox."-public", plugin_dir_url( __FILE__ ) . 'js/chroma_lightbox-public.min.js', array( 'jquery' ), $this->version, true );


		$array = array(
			'debug' => chroma_lightbox::$debug,
			'options' => chroma_lightbox::$options,
			'img_sizes' => $this->img_sizes,
			'plugin_path' => plugin_dir_url( __FILE__ ),
		);
		// localize lightbox scripts
		wp_localize_script( $this->chroma_lightbox."-public", 'chroma_lightbox_public_vars', $array );

	}

	public function get_image_sizes() {
	global $_wp_additional_image_sizes;

	$sizes = array();

	foreach ( get_intermediate_image_sizes() as $_size ) {
		if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
			$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
			$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
			$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
		} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] = array(
				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
			);
		}
	}

	return $sizes;
}





}
