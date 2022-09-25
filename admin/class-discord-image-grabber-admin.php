<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://kaiserrobin.eu
 * @since      1.0.0
 *
 * @package    Discord_Image_Grabber
 * @subpackage Discord_Image_Grabber/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Discord_Image_Grabber
 * @subpackage Discord_Image_Grabber/admin
 * @author     Robin Kaiser <t@r-k.mx>
 */
class Discord_Image_Grabber_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Discord_Image_Grabber_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Discord_Image_Grabber_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/discord-image-grabber-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Discord_Image_Grabber_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Discord_Image_Grabber_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/discord-image-grabber-admin.js', array( 'jquery' ), $this->version, false );

    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */

    public function add_plugin_admin_menu() {

        /*
         * Add a settings page for this plugin to the Settings menu.
         *
         * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
         *
         *        Administration Menus: http://codex.wordpress.org/Administration_Menus
         *
         */
        add_options_page( 'Discord Image Grabber', 'Discord Image Grabber', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page')
        );
    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */

    public function add_action_links( $links ) {
        /*
        *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
        */
        $settings_link = array(
            '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
        );
        return array_merge(  $settings_link, $links );

    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */

    public function display_plugin_setup_page() {
        include_once 'partials/discord-image-grabber-admin-display.php';
    }
    
    public function options_update() {
        register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
    }

    public function validate($input) {
    
        $user_id = get_current_user_id();
        $user = new WP_User($user_id);
        if($user->roles[0] != 'administrator') {
            header("Location /wp-admin");
            die;
        }

        $valid = array();

        $options = get_option($this->plugin_name);
       
        if (isset($_POST['btn_base_settings'])) {
    
            if (!isset($_REQUEST['dig_form_base_settings_nonce_field'])) {
                wp_nonce_ays('dig_form_base_settings_nonce_field');
            }
            $nonceValue = $_REQUEST['dig_form_base_settings_nonce_field'];
            if (!wp_verify_nonce($nonceValue, 'dig_form_base_settings_submit')) {
                wp_nonce_ays('dig_form_base_settings_submit');
            }
            
            $valid['base_channel_ids'] = esc_textarea($input['base_channel_ids']);
	
	        $valid['auth_bot_token'] = sanitize_text_field($options['auth_bot_token']);
    
            $valid['download_folder_name'] = sanitize_text_field($options['download_folder_name']);
            $valid['download_enabled'] = $options['download_enabled'];
            $valid['download_file_formats'] = esc_textarea($options['download_file_formats']);
            $valid['download_last_message_id'] = sanitize_text_field($options['download_last_message_id']);
            $valid['download_max_file_num'] = sanitize_text_field($options['download_max_file_num']);
        }
	    if (isset($_POST['btn_auth_settings'])) {

            if (!isset($_REQUEST['dig_form_auth_settings_nonce_field'])) {
                wp_nonce_ays('dig_form_auth_settings_nonce_field');
            }
            $nonceValue = $_REQUEST['dig_form_auth_settings_nonce_field'];
            if (!wp_verify_nonce($nonceValue, 'dig_form_auth_settings_submit')) {
                wp_nonce_ays('dig_form_auth_settings_submit');
            }

            $valid['base_channel_ids'] = esc_textarea($options['base_channel_ids']);

            $valid['auth_bot_token'] = sanitize_text_field($input['auth_bot_token']);

            $valid['download_folder_name'] = sanitize_text_field($options['download_folder_name']);
            $valid['download_enabled'] = $options['download_enabled'];
            $valid['download_file_formats'] = esc_textarea($options['download_file_formats']);
            $valid['download_last_message_id'] = sanitize_text_field($options['download_last_message_id']);
            $valid['download_max_file_num'] = sanitize_text_field($options['download_max_file_num']);
	    }
        if (isset($_POST['btn_download_settings'])) {
    
            if (!isset($_REQUEST['dig_form_download_settings_nonce_field'])) {
                wp_nonce_ays('dig_form_download_settings_nonce_field');
            }
            $nonceValue = $_REQUEST['dig_form_download_settings_nonce_field'];
            if (!wp_verify_nonce($nonceValue, 'dig_form_download_settings_submit')) {
                wp_nonce_ays('dig_form_download_settings_submit');
            }

            $valid['base_channel_ids'] = esc_textarea($options['base_channel_ids']);

            $valid['auth_bot_token'] = sanitize_text_field($options['auth_bot_token']);

            $valid['download_folder_name'] = sanitize_text_field($input['download_folder_name']);
            $valid['download_enabled'] = $input['download_enabled'];
            $valid['download_file_formats'] = esc_textarea($input['download_file_formats']);
            $valid['download_last_message_id'] = sanitize_text_field($input['download_last_message_id']);
            $valid['download_max_file_num'] = sanitize_text_field($input['download_max_file_num']);
        }
        if (isset($_POST['btn_import_settings'])) {
    
            if (!isset($_REQUEST['dig_form_import_settings_nonce_field'])) {
                wp_nonce_ays('dig_form_import_settings_nonce_field');
            }
            $nonceValue = $_REQUEST['dig_form_import_settings_nonce_field'];
            if (!wp_verify_nonce($nonceValue, 'dig_form_import_settings_submit')) {
                wp_nonce_ays('dig_form_import_settings_submit');
            }
            
            if (isset($_FILES['discord-image-grabber']['tmp_name']['import_file'])) {

                $cont = trim(file_get_contents($_FILES['discord-image-grabber']['tmp_name']['import_file']));
                if ($this->isJson($cont)) {
                    $json = json_decode($cont, true);
	                foreach ( $json as $key => $value ) {
		                if (array_key_exists($key, $options)) {
		                	$valid[$key] = $json[$key];
		                }
                    }
	
	                foreach ( $valid as $key => $value ) {
		                if (empty($valid[$key])) {
			                $valid[$key] = $options[$key];
		                }
	                }
                } else {
                	die('invalid json!');
                }
                
            }
            if (!empty($input['import_settings_text'])) {
                if ($this->isJson($input['import_settings_text'])) {
                    $json = json_decode($input['import_settings_text'], true);
	                foreach ( $json as $key => $value ) {
		                if (array_key_exists($key, $options)) {
			                $valid[$key] = $json[$key];
		                }
	                }
	
	                foreach ( $valid as $key => $value ) {
		                if (empty($valid[$key])) {
			                $valid[$key] = $options[$key];
		                }
	                }
                } else {
	                die('invalid json!');
                }
            }
        }
        if (isset($_POST['btn_export_settings'])) {
        	$json = json_encode($options, JSON_PRETTY_PRINT);
	        header('Content-Type: application/json');
	        header('Content-Disposition: attachment; filename="dig-settings.json"');
	        echo $json;
	        die;
        }
        
        return $valid;
    }
    
    private function isJson($str) {
        $json = json_decode($str);
        return $json && $str != $json;
    }
}

