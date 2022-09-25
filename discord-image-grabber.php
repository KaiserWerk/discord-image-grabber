<?php
/*
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://kaiserrobin.eu
 * @since             1.0.0
 * @package           Discord_Image_Grabber
 *
 * @wordpress-plugin
 * Plugin Name:       Discord Image Grabber
 * Description:       Every hour, grabs all image attachments from Discord messages of a specific channel and downloads them into a selectable folder.
 * Version:           0.0.1
 * Author:            Robin Kaiser
 * Author URI:        https://www.kaiserrobin.eu
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       discord-image-grabber
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$dig_plugin_data = get_file_data(__FILE__, [
    'version' => 'Version',
    'slug'    => 'Text Domain',
    'name'    => 'Plugin Name',
], 'plugin');




/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-discord-image-grabber-activator.php
 *
 * @param $dig_plugin_data
 */
function activate_discord_image_grabber($dig_plugin_data)
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-discord-image-grabber-activator.php';
    Discord_Image_Grabber_Activator::activate($dig_plugin_data);
    
    if (!wp_next_scheduled('dig_hourly_grab')) {
        wp_schedule_event(time(), 'hourly', 'dig_hourly_grab');
    }
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-discord-image-grabber-deactivator.php
 *
 * @param $dig_plugin_data
 */
function deactivate_discord_image_grabber($dig_plugin_data)
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-discord-image-grabber-deactivator.php';
    Discord_Image_Grabber_Deactivator::deactivate($dig_plugin_data);
}

register_activation_hook(__FILE__, 'activate_Discord_Image_Grabber');
register_deactivation_hook(__FILE__, 'deactivate_Discord_Image_Grabber');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-discord-image-grabber.php';

/**
 * Begins execution of the plugin.
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 * @since    1.0.0
 */
function run_discord_image_grabber()
{
    $plugin = new Discord_Image_Grabber();
    $plugin->run();
}

run_Discord_Image_Grabber();

/**
 * Initialize the plugin updater
 */
require_once plugin_dir_path(__FILE__) . 'discord-image-grabber-updater.php';
$Discord_Image_Grabber_updater = new DiscordImageGrabberUpdater($dig_plugin_data);
