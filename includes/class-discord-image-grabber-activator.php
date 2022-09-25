<?php

/**
 * Fired during plugin activation
 *
 * @link       https://kaiserrobin.eu
 * @since      1.0.0
 *
 * @package    Discord_Image_Grabber
 * @subpackage Discord_Image_Grabber/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Discord_Image_Grabber
 * @subpackage Discord_Image_Grabber/includes
 * @author     Robin Kaiser <t@r-k.mx>
 */
class Discord_Image_Grabber_Activator {
    
    /**
     * Short Description. (use period)
     * Long Description.
     *
     * @param $dig_plugin_data
     *
     * @since    1.0.0
     */
	public static function activate($dig_plugin_data) {
	    $Discord_Image_Grabber_updater = new DiscordImageGrabberUpdater($dig_plugin_data, true);
	    if ($Discord_Image_Grabber_updater !== null) {
		    $Discord_Image_Grabber_updater->trackInstallations( 'installed' );
	    }
	}
    
 

}
