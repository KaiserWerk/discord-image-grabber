<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://kaiserrobin.eu
 * @since      1.0.0
 *
 * @package    Discord_Image_Grabber
 * @subpackage Discord_Image_Grabber/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Discord_Image_Grabber
 * @subpackage Discord_Image_Grabber/includes
 * @author     Robin Kaiser <t@r-k.mx>
 */
class Discord_Image_Grabber_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'discord-image-grabber',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
