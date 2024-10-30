<?php

// The core plugin class.
class chroma_lightbox {


	// The loader that's responsible for maintaining and registering all hooks that power
	// the plugin.
	protected $loader;


	// The unique identifier of this plugin.
	protected $chroma_lightbox;


	// The current version of the plugin.
	protected $version;


	// Default boilerplate variables
	public static $options;
	public static $settings;
	public static $default_options;
	public static $debug = 0;

	// Plug-in specific variables


	// Define the core functionality of the plugin.
	public function __construct() {
		//delete_option("chroma_lightbox");

		$this->chroma_lightbox = 'chroma_lightbox';
		$this->version = '1.1';

		$this->define_options();
		//$this->fill_missing_options();

		// Define option/settins static variables
		self::$options = get_option('chroma_lightbox');
		self::$settings = self::$options["settings"];

		// If options dont exist, create them
		if (!self::$options || !self::$settings) {
			$this->fill_missing_options();
		};

		// If plugin updated, update options
		if (self::$options["version"] != $this->version ) {
			$this->new_version();
		}

		$this->load_dependencies();

		if (is_admin()) {
			$this->define_settings_hooks();
		} elseif (!is_admin()) {
			$this->define_public_hooks();
		}

	}

	// If new version detected, update plugin
	public function new_version() {
		self::$options["version"] = $this->version;
		update_option("chroma_lightbox", self::$options);
	}

	// Load the required dependencies for this plugin.
	private function load_dependencies() {

		// The class responsible for orchestrating the actions and filters of the core plugin.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-chroma_lightbox-loader.php';

		// The class responsible for defining all actions that occur in the admin settings area.
		if (is_admin())
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-chroma_lightbox-settings.php';

		// The class responsible for defining all actions that occur in the public-facing side of the site.
		if (!is_admin())
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-chroma_lightbox-public.php';

		$this->loader = new chroma_lightbox_Loader();
	}

	// Register all of the hooks related to the admin area functionality of the plugin.
	private function define_settings_hooks() {

		$plugin_settings = new chroma_lightbox_Settings( $this->get_chroma_lightbox(), $this->get_version() );

		$this->loader->add_action( 'admin_menu', $plugin_settings, 'settings' );
		$this->loader->add_action( 'admin_init', $plugin_settings, 'settings_init' );

		// enqueue scrips and styles if on settings page
		if (isset($_GET["page"]) && $_GET["page"] == "chroma_lightbox_settings" ) {
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_settings, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_settings, 'enqueue_scripts' );
		}

	}

	// Register all of the hooks related to the public-facing functionality of the plugin.
	private function define_public_hooks() {
		$plugin_public = new chroma_lightbox_Public( $this->get_chroma_lightbox(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'get_image_sizes' );



		//$this->loader->add_filter( 'post_gallery', $plugin_public, 'my_post_gallery',10,2 );


	}

	// Define all the default options
	private function define_options() {
		self::$default_options = array(
			'settings' => array(
				'target' => "a",
				'gallery-active' => "on",
				'thumbs-active' => "on",
				'wrapper-element' => ".entry-content",
				'background-color' => "#000",
				'background-opacity' => "0.75",
				'frame-active' => "on",
				'frame-width' => 20,
				'frame-color' => "#ffffff",
				'frame-opacity' => 1,
				'frame-active-tn' => "on",
				'frame-width-tn' => 4,
				'frame-color-tn' => "#ffffff",
				'frame-opacity-tn' => 1,
		),
			'version' => $this->version,
		);
	}

	// Fix any missing options
	public function fill_missing_options(){
		if (!self::$options || !self::$settings) {
			update_option("chroma_lightbox", self::$default_options);
			self::$options = self::$default_options;
			self::$settings = self::$options["settings"];
		} else {
			$default_settings = self::$default_options['settings'];
			foreach ($default_settings as $key=>$values) {
				if (!isset(self::$settings[$key])) {
					self::$settings[$key] = $values;
				}
			}

			self::$options["settings"] = self::$settings;
			update_option("chroma_lightbox", self::$options);
		}
	}

	// Run the loader to execute all of the hooks with WordPress.
	public function run() {
		$this->loader->run();
	}

	// The name of the plugin used to uniquely identify it within the context of
	// WordPress and to define internationalization functionality.
	public function get_chroma_lightbox() {
		return $this->chroma_lightbox;
	}

	// The reference to the class that orchestrates the hooks with the plugin.
	public function get_loader() {
		return $this->loader;
	}

	// Retrieve the version number of the plugin.
	public function get_version() {
		return $this->version;
	}

}
