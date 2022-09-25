<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://kaiserrobin.eu
 * @since      1.0.0
 *
 * @package    Discord_Image_Grabber
 * @subpackage Discord_Image_Grabber/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Discord_Image_Grabber
 * @subpackage Discord_Image_Grabber/includes
 * @author     Robin Kaiser <t@r-k.mx>
 */
class Discord_Image_Grabber_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate($dig_plugin_data) {
		// remove the cronjob!
        wp_clear_scheduled_hook('dig_hourly_grab');
        
        $Discord_Image_Grabber_updater = new DiscordImageGrabberUpdater($dig_plugin_data, true);
        if ($Discord_Image_Grabber_updater !== null) {
	        $Discord_Image_Grabber_updater->trackInstallations( 'uninstalled' );
        }
	}

}
