<?php

// The admin/settings functionality of the plugin.
class chroma_lightbox_Settings {

    // The ID of this plugin.
    private $chroma_lightbox;

    // The version of this plugin.
    private $version;

    public $tabbed = 0;


    public function __construct( $chroma_lightbox, $version ) {
        $this->chroma_lightbox = $chroma_lightbox;
        $this->version = $version;

        $this->define_section_titles();

    }

    // Enqueue settings page CSS
    public function enqueue_styles(){
        wp_enqueue_style( $this->chroma_lightbox."-settings", plugin_dir_url( __FILE__ ) . 'css/chroma_lightbox-settings.css', array(), $this->version, 'all' );

        // Add the color picker css file
        wp_enqueue_style( 'wp-color-picker' );

    }


    // Enqueue settings page scripts
    public function enqueue_scripts() {
        wp_enqueue_media();

        wp_enqueue_script( $this->chroma_lightbox."-settings", plugin_dir_url( __FILE__ ) . 'js/chroma_lightbox-settings.min.js', array("jquery","jquery-ui-tabs","wp-color-picker"), $this->version, true );

        // default options array
        $array = array(
            'options' =>  chroma_lightbox::$options,
        );
        // localize chromabox scripts
        wp_localize_script( $this->chroma_lightbox."-settings", 'chroma_lightbox_settings_vars', $array );



    }

    // Universal add form function
    public function add_form($type, $subtype, $name, $value, $args = array()) {

        if ($type != "custom") {

            // Start Field HTML string
            $field = "<".$type." ";
            $field .= "type='".$subtype."' ";
            $field .= "id='".$name."' ";
            $field .= "name='chroma_lightbox[settings][".$name."]' ";
            if (isset($args["dep"])) {
                $field .= "data_dep='".$args["dep"]."' ";
            }

            if ($subtype=="number") {

                if (array_key_exists("step",$args)) {
                    $field .= "step='".$args["step"]."' ";
                }
                if (array_key_exists("min",$args)) {
                    $field .= "min='".$args["min"]."' ";
                }
                if (array_key_exists("max",$args)) {
                    $field .= "max='".$args["max"]."' ";
                }
            }

            if ($subtype=="text" || $subtype=="number" || $subtype=="textarea" || $subtype=="hidden"){
                $field .= "value='".$value."' ";
            } elseif ($subtype=="checkbox" ){
                if ($value == "on") {
                    $field .= "checked='checked' ";
                }
            }

            $field .= ">";
            // Close Field opening tag

            if ($type=="textarea") {
                $field .= $value."</textarea>";
            } elseif ($type=="select") {
                foreach ($args as $op) {
                    $op = strtolower($op);
                    $sel = ($op == $value) ? "selected" : "";
                    $field .= "<option value='".$op."' ".$sel.">" .ucfirst($op)."</option>";
                }
                $field .= "</select>";
            }

            // Complete field HTML and echo
            echo $field;
        } else {
            if ($name == "chroma_lightbox-spinner-img-box") {
                ?>
                <div id="chroma_lightbox-spinner-container" style="background-image:url(<?php echo chroma_lightbox::$settings['chroma_lightbox-spinner-url'] ?>)"></div>
                <div id="chroma_lightbox-spinner-buttons">
                    <a href="#" id="chroma_lightbox-choose-spinner" class="button">Choose Icon</a>
                    <a href="#" id="chroma_lightbox-reset-spinner" class="button">X</a>
                </div>
                <?php
            }
        }

    }

    public function add_tab_buttons() {
        if ($this->tabbed == 1) { ?>
            <ul id="chroma_lightbox-settings-tab-buttons">
                <?php foreach ($this->section_titles as $key => $value) { ?>
                    <li><a href="#chroma_lightbox-settings-section-section_<?php echo $key+1 ?>_start" class="chroma_lightbox-settings-button"><p><?php echo $value ?></p></a></li>
                    <?php } ?>
                </ul>
                <?php }
            }

            public function define_section_titles() {
                $this->section_titles = array (
                    "Link Target",
                    "Gallery Settings",
                    "Background Style",
                    "Main Image Style",
                    "Gallery Thumbnails Style",

                );

            }

            // Add options page
            public function settings() {
                add_options_page( 'Chroma Lightbox Options', 'Chroma Lightbox', 'manage_options', 'chroma_lightbox_settings', array($this,'settings_callback') );
            }

            // Settings Callback
            public function settings_callback() {
                ?>
                <div id="chroma_lightbox-settings-container" class="wrap">
                    <h2><?php echo 'Chroma Lightbox Settings' ?></h2>

                    <?php $this->add_tab_buttons() ?>

                    <form action="options.php" method="POST">
                        <?php settings_fields('chroma_lightbox-settings-group'); ?>
                        <?php do_settings_sections('chroma_lightbox_settings'); ?>
                        <?php submit_button(); ?>
                    </form>
                </div>
                <?php
            }

            // Filter settings Saved
            // to add version key to options array
            public function filter_update_option($new_value, $old_value){
                $new_value["version"] = $this->version;
                return $new_value;
            }

            public function settings_init() {

                // Filter settings Saved
                add_filter( 'pre_update_option_chroma_lightbox', array($this,'filter_update_option'), 10, 2 );

                // REGISTER SETTING
                // register_setting( $option_group, $option_name, $sanitize_callback );
                register_setting( 'chroma_lightbox-settings-group', 'chroma_lightbox', array($this, "validate_settings") );

                // SECTIONS
                // --------
                // add_settings_section( $id, $title, $callback, $page );
                foreach ($this->section_titles as $key => $value) {
                    $this->settings_sections("section_".($key+1),$value);
                };



                // FIELDS
                // ------
                // $this->settings_fields( $section, $name, $label, $type, $subtype, $args );
                $this->settings_fields("section_1", "target", "Link Target", "input", "text", array());
                $this->settings_fields("section_2", "gallery-active", "Activate Gallery", "input", "checkbox", array());
                $this->settings_fields("section_2", "thumbs-active", "Show Thumbnails", "input", "checkbox", array("dep"=>"gallery-active"));
                $this->settings_fields("section_2", "wrapper-element", "Wrapper Element", "input", "text", array("dep"=>"gallery-active"));
                $this->settings_fields("section_3", "background-color", "Background Color", "input", "text", array());
                $this->settings_fields("section_3", "background-opacity", "Background Opacity", "input", "number", array());
                $this->settings_fields("section_4", "frame-active", "Activate Frame", "input", "checkbox", array());
                $this->settings_fields("section_4", "frame-width", "Frame Width (px)", "input", "number", array("dep"=>"frame-active"));
                $this->settings_fields("section_4", "frame-color", "Frame Color", "input", "text", array("dep"=>"frame-active"));
                $this->settings_fields("section_4", "frame-opacity", "Frame Opacity", "input", "number", array("dep"=>"frame-active","step"=>0.01, "min"=>0, "max"=>1));

                $this->settings_fields("section_5", "frame-active-tn", "Activate Frame", "input", "checkbox", array());
                $this->settings_fields("section_5", "frame-width-tn", "Frame Width (px)", "input", "number", array("dep"=>"frame-active-tn"));
                $this->settings_fields("section_5", "frame-color-tn", "Frame Color", "input", "text", array("dep"=>"frame-active-tn"));
                $this->settings_fields("section_5", "frame-opacity-tn", "Frame Opacity", "input", "number", array("dep"=>"frame-active-tn","step"=>0.01, "min"=>0, "max"=>1));
                //dumper(chroma_lightbox::$settings);

            }

            // Universal add settings section function
            public function settings_sections($section,$text){
                add_settings_section( $section.'_start', "", array($this,'section_start_callback'), 'chroma_lightbox_settings');
                add_settings_section( $section, $text, array($this, $section."_callback"), 'chroma_lightbox_settings' );
                add_settings_section( $section.'_end', "", array($this,'section_end_callback'), 'chroma_lightbox_settings' );
            }

            // Universal add field function
            public function settings_fields($section,$name,$label,$type,$subtype,$args){
                add_settings_field( $name, $label, array($this, 'field_callback'), 'chroma_lightbox_settings', $section, array($type,$subtype,$name,$args,"class"=>$name."-row") );

            }

            // Universal field callback
            public function field_callback(array $args) {
                $type = $args[0];
                $subtype = $args[1];
                $name = $args[2];
                $args = $args[3];

                if (isset(chroma_lightbox::$settings[$name])) {
                    $value = esc_attr( chroma_lightbox::$settings[$name] );
                } else {
                    $value = null;
                }
                $this->add_form($type,$subtype,$name,$value,$args);
            }

            // Callback responsible for section wrapper opening tag
            public function section_start_callback($args) {
                echo "<div id='chroma_lightbox-settings-section-".$args["id"]."' class='chroma_lightbox-settings-section-container'>";
            }

            // Callback responsible for section wrapper closing tag
            public function section_end_callback() {
                echo "</div>";
            }

            // Callback responsible description wrapper
            public function section_callback($text) {
                echo "<p class='chroma_lightbox-settings-description'>";
                echo $text;
                echo "</p>";
            }

            // Section Descriptions
            public function section_1_callback() {
                $text = 'The link elements targeted by Chroma Lightbox. Default is "a" which will target all links. Links will only be targeted if :-<br><ul><li>The link HREF property must point to an image file<br></li><li>The link element must contain a single image.</li></ul>';
                $this->section_callback($text);
            }

            public function section_2_callback() {
                $text = 'If Gallery is activated, Lightbox will capture all the images contained in the same post/page as the clicked link. The user can then browse through all the images without leaving the Lightbox. The default <b>Wrapper Element</b> setting of <b>.entry-content</b> is the standard wordpress wrapper for a post/page. Change the <b>Wrapper Element</b> setting if your theme uses a different wrapper class/ID.';
                $this->section_callback($text);
            }

            public function section_3_callback() {
                $text = 'Define how the Lightbox background looks.';
                $this->section_callback($text);
            }

            public function section_4_callback() {
                $text = 'Define how the Lightbox main image looks.';
                $this->section_callback($text);
            }

            public function section_5_callback() {
                $text = 'Define how the Lightbox gallery thumbnails look.';
                $this->section_callback($text);
            }

            // INPUT VALIDATION
            public function validate_settings( $input ) {

                $output = $input;


                return $output;
            }

        }
        ?>
