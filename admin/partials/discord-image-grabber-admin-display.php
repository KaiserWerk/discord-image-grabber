<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://kaiserrobin.eu
 * @since      1.0.0
 *
 * @package    Discord_Image_Grabber
 * @subpackage Discord_Image_Grabber/admin/partials
 */

$user_id = get_current_user_id();
$user = new WP_User($user_id);
if($user->roles[0] != 'administrator') {
    header("Location /wp-admin");
    die;
}

//Grab all options
$options = get_option($this->plugin_name);
if (empty($options))
    $options = array();

$channel_ids = !empty($options['base_channel_ids']) ? $options['base_channel_ids'] : '';
$bot_token = !empty($options['auth_bot_token']) ? $options['auth_bot_token'] : '';
$folder_name = !empty($options['download_folder_name']) ? $options['download_folder_name'] : '';
$enabled = !empty($options['download_enabled']) && $options['download_enabled'] === 'on';
$file_formats = !empty($options['download_file_formats']) ? $options['download_file_formats'] : '';
$last_message_id = !empty($options['download_last_message_id']) ? $options['download_last_message_id'] : '';
$max_file_num = !empty($options['download_max_file_num']) ? (int)$options['download_max_file_num'] : 500;

settings_fields($this->plugin_name);
do_settings_sections($this->plugin_name);

global $dig_associations;

$menu_tabs = array(
	'base_settings' => __('Base Settings', $this->plugin_name),
	'auth_settings' => __('Auth Settings', $this->plugin_name),
	'download_settings' => __('Download Settings', $this->plugin_name),
	'import_export' => __('Settings Import & Export', $this->plugin_name),
);

$tab_keys = array_keys($menu_tabs);
$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : $tab_keys[0];

?>

<div class="wrap">
    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>

    <?php
    echo '<h2 class="nav-tab-wrapper">';
    foreach ( $menu_tabs as $tab_key => $tab_caption )
    {
	    $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
	    echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_name . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
    }
    echo '</h2>';
    
    if ($current_tab === 'base_settings') {
	    ?>

        <form method="post" name="form_base_settings" action="options.php" enctype="multipart/form-data">
		    <?php settings_fields($this->plugin_name); ?>
            <?php wp_nonce_field('dig_form_base_settings_submit', 'dig_form_base_settings_nonce_field', false); ?>

            <div class="postbox">
                <div class="inside">
                    <h3 class="handle"><?php _e( 'Discord Image Grabber Base Settings', $this->plugin_name ); ?></h3>

                    <table class="form-table">
                        <tbody>
                        <tr valign="top">

                            <th scope="row"><?php _e( 'Channel IDs', $this->plugin_name ); ?>:</th>
                            <td>
                                <textarea id="<?php echo $this->plugin_name; ?>-base-channel-ids"
                                          name="<?php echo $this->plugin_name; ?>[base_channel_ids]" cols="80"
                                          rows="10"><?php if (!empty($channel_ids)) echo $channel_ids; ?></textarea>
                                <br><span class="description"><?php _e( 'Please enter at least one channel ID. Enter multiple IDs one by line.',
                                        $this->plugin_name ); ?></span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <input type="submit" name="btn_base_settings"
                           value="<?php _e( 'Save base settings', $this->plugin_name ); ?>" class="button-primary">
                </div>
            </div>
        </form>
	
	    <?php
    }
    if ($current_tab === 'auth_settings') {
	    ?>
        <form method="post" name="form_auth_settings" action="options.php" enctype="multipart/form-data">
		    <?php settings_fields( $this->plugin_name ); ?>
            <?php wp_nonce_field('dig_form_auth_settings_submit', 'dig_form_auth_settings_nonce_field', false); ?>

            <div class="postbox">
                <div class="inside">
                    <h3 class="handle"><?php _e( 'Discord Image Grabber Update Settings', $this->plugin_name ); ?></h3>

                    <table class="form-table">
                        <tbody>

                        <tr valign="top">
                            <th scope="row"><?php _e( 'Bot Token', $this->plugin_name ); ?>:</th>
                            <td>
                                <input type="text" id="<?php echo $this->plugin_name; ?>-auth-bot-token"
                                       name="<?php echo $this->plugin_name; ?>[auth_bot_token]"
                                       value="<?php if ( ! empty( $bot_token ) ) {
								           echo $bot_token;
							           } ?>" class="regular-text" style="width: 600px;">
                                <span class="description"><?php _e( 'The token used by the bot for authenticating with the Discord servers.',
									    $this->plugin_name ); ?></span>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                    <input type="submit" name="btn_auth_settings"
                           value="<?php _e( 'Save auth settings', $this->plugin_name ); ?>" class="button-primary">
                </div>
            </div>
        </form>
	    <?php
    }
    if ($current_tab === 'download_settings') {
	    ?>
        <form method="post" name="form_download_settings" action="options.php" enctype="multipart/form-data">
		    <?php settings_fields( $this->plugin_name ); ?>
            <?php wp_nonce_field('dig_form_download_settings_submit', 'dig_form_download_settings_nonce_field', false); ?>
            <div class="postbox">
                <div class="inside">
                    <h3 class="handle"><?php _e( 'Discord Image Grabber Download Settings', $this->plugin_name ); ?></h3>

                    <table class="form-table">
                        <tbody>
                        <tr valign="top">
                            <th scope="row"><?php _e( 'Enabled', $this->plugin_name ); ?>:</th>
                            <td>
                                <label><input type="checkbox" id="<?php echo $this->plugin_name; ?>-download-enabled"
                                              name="<?php echo $this->plugin_name; ?>[download_enabled]" <?php checked( $enabled,
                                        '1', true ); ?>> Whether downloading images is enabled.</label>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e( 'Folder name', $this->plugin_name ); ?>:</th>
                            <td>
                                <input type="text" id="<?php echo $this->plugin_name; ?>-download-folder-name"
                                       name="<?php echo $this->plugin_name; ?>[download_folder_name]" class="regular-text"
                                       value="<?php echo $folder_name; ?>">
                                <br><span class="description"><?php _e( 'The folder downloaded images are saved into for the current iteration.
                                If the folder is not absolute, it is considered to be relative to the folder "wp-content/discord-image-grabber" and will
                                be created if it does not exist.',
                                        $this->plugin_name ); ?></span>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e( 'Allowed file formats', $this->plugin_name ); ?>:</th>
                            <td>
                                <input type="text" id="<?php echo $this->plugin_name; ?>-download-file-formats"
                                       name="<?php echo $this->plugin_name; ?>[download_file_formats]" class="regular-text"
                                       value="<?php echo $file_formats; ?>" placeholder="image/jpeg,image/png">
                                <br><span class="description"><?php _e( 'Please enter allowed file formats, separated with a comma or space.',
									    $this->plugin_name ); ?></span>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e( 'Last Message ID', $this->plugin_name ); ?>:</th>
                            <td>
                                <input type="text" id="<?php echo $this->plugin_name; ?>-download-last-message-id"
                                       name="<?php echo $this->plugin_name; ?>[download_last_message_id]" class="regular-text"
                                       value="<?php echo $last_message_id; ?>">
                                <br><span class="description"><?php _e( 'The ID of the last downloaded message. If not set, 
                                the DIG will download ALL available messages. This value is updated automatically!',
                                        $this->plugin_name ); ?></span>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e( 'Max. number of files per iteration', $this->plugin_name ); ?>:</th>
                            <td>
                                <input type="text" id="<?php echo $this->plugin_name; ?>-download-max-file-num"
                                       name="<?php echo $this->plugin_name; ?>[download_max_file_num]" class="regular-text"
                                       value="<?php echo $max_file_num; ?>">
                                <br><span class="description"><?php _e( 'For every download iteration this is the maximum number of files
                                that will be downloaded per channel. Maximum 100.',
                                        $this->plugin_name ); ?></span>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                    <input type="submit" name="btn_download_settings"
                           value="<?php _e( 'Save download settings', $this->plugin_name ); ?>"
                           class="button-primary">
                </div>
            </div>
        </form>
	    <?php
    }
    if ($current_tab === 'import_export') {
	    ?>
        <form method="post" name="form_import_settings" action="options.php" enctype="multipart/form-data">
		    <?php settings_fields( $this->plugin_name ); ?>
            <?php wp_nonce_field('dig_form_import_settings_submit', 'dig_form_import_settings_nonce_field', false); ?>
            <div class="postbox">
                <div class="inside">
                    <h3 class="handle"><?php _e( 'Import settings', $this->plugin_name ); ?></h3>
                    <span class="description"><?php _e( 'Use this section to import your Discord Image Grabber settings
                from a file. Alternatively, copy/paste the contents of your import file into the textarea below.',
						    $this->plugin_name ); ?></span>
                    <table class="form-table">
                        <tbody>
                        <tr valign="top">

                            <th scope="row"><?php _e( 'Import File', $this->plugin_name ); ?>:</th>
                            <td>
                                <input type="file" id="<?php echo $this->plugin_name; ?>-import-file"
                                       name="<?php echo $this->plugin_name; ?>[import_file]">
                                <p class="description"><?php _e( 'After selecting your file, click the button below to apply the settings to your site.',
									    $this->plugin_name ); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e( 'Copy/Paste Import Data', $this->plugin_name ); ?>:</th>
                            <td>
                                <textarea id="<?php echo $this->plugin_name; ?>-import-settings-text"
                                          name="<?php echo $this->plugin_name; ?>[import_settings_text]" cols="80"
                                          rows="10"></textarea>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <input type="submit" name="btn_import_settings"
                           value="<?php _e( 'Import settings', $this->plugin_name ); ?>" class="button-primary">

                </div>
            </div>
        </form>

        <form method="post" name="form_export_settings" action="options.php" enctype="multipart/form-data">
		    <?php settings_fields( $this->plugin_name ); ?>
            <div class="postbox">
                <div class="inside">
                    <h3 class="hndle"><?php _e( 'Export settings', $this->plugin_name ); ?></h3>
                    <span class="description"><?php _e( 'Click the button below to export your Discord Image Grabber settings into a file for you to download. This leaves your settings unchanged.',
						    $this->plugin_name ); ?></span>
                    <table class="form-table">

                    </table>
                    <input type="submit" name="btn_export_settings"
                           value="<?php _e( 'Export settings', $this->plugin_name ); ?>" class="button-primary">

                </div>
            </div>
        </form>
	    <?php
    }
    ?>
</div>
