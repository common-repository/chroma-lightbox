<?php

/**
 * Plugin Name:       Chroma Lightbox
 * Description:       Dynamic image lightbox
 * Version:           1.1
 * Author:            ChromaDot
 * Author URI:        http://chromadot.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       chroma_lightbox
 */

 // Delete chroma_lightbox option for testing
 // delete_option("chroma_lightbox");

 // var_dump funtion for debugging
 if (!function_exists('dumper')) {
     function dumper($mixed = null) {
         echo '<pre class="var-dump">';
         var_dump($mixed);
         echo '</pre>';
         return null;
     }
 }

 // If this file is called directly, abort.
 if ( ! defined( 'WPINC' ) ) {
     die;
 }

 // The code that runs during plugin activation.
 function activate_chroma_lightbox() {
     require_once plugin_dir_path( __FILE__ ) . 'includes/class-chroma_lightbox-activator.php';
     chroma_lightbox_Activator::activate();
 }

 // The code that runs during plugin deactivation.
 function deactivate_chroma_lightbox() {
     require_once plugin_dir_path( __FILE__ ) . 'includes/class-chroma_lightbox-deactivator.php';
     chroma_lightbox_Deactivator::deactivate();
 }

 register_activation_hook( __FILE__, 'activate_chroma_lightbox' );
 register_deactivation_hook( __FILE__, 'deactivate_chroma_lightbox' );

 // The core plugin class that is used to define internationalization,
 // admin-specific hooks, and public-facing site hooks.
 require plugin_dir_path( __FILE__ ) . 'includes/class-chroma_lightbox.php';


 // Begins execution of the plugin.
 function run_chroma_lightbox() {
     $plugin = new chroma_lightbox();
     $plugin->run();
 }
 add_action('plugins_loaded','run_chroma_lightbox');
