<?php

/**
 * The file that defines the core plugin class
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 * @link       https://kaiserrobin.eu
 * @since      1.0.0
 * @package    Discord_Image_Grabber
 * @subpackage Discord_Image_Grabber/includes
 */

/**
 * The core plugin class.
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 * @since      1.0.0
 * @package    Discord_Image_Grabber
 * @subpackage Discord_Image_Grabber/includes
 * @author     Robin Kaiser <t@r-k.mx>
 */
class Discord_Image_Grabber
{
    
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     * @since    1.0.0
     * @access   protected
     * @var      Discord_Image_Grabber_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;
    
    /**
     * The unique identifier of this plugin.
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;
    
    /**
     * The current version of the plugin.
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;
    
    protected $options;
    
    /**
     * Define the core functionality of the plugin.
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     * @since    1.0.0
     */
    public function __construct()
    {
        global $dig_plugin_data;
        if (isset($dig_plugin_data['version'])) {
            $this->version = $dig_plugin_data['version'];
        } else {
            $this->version = '0.0.0';
        }
        $this->plugin_name = $dig_plugin_data['slug'];
    
        $this->options = get_option($dig_plugin_data['slug']);
        
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        
        add_action('dig_hourly_grab', [$this, 'hourly_grab']);
    }
    
    /**
     * Load the required dependencies for this plugin.
     * Include the following files that make up the plugin:
     * - Discord_Image_Grabber_Loader. Orchestrates the hooks of the plugin.
     * - Discord_Image_Grabber_i18n. Defines internationalization functionality.
     * - Discord_Image_Grabber_Admin. Defines all hooks for the admin area.
     * - Discord_Image_Grabber_Public. Defines all hooks for the public side of the site.
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(__DIR__) . 'includes/class-discord-image-grabber-loader.php';
        
        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(__DIR__) . 'includes/class-discord-image-grabber-i18n.php';
        
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(__DIR__) . 'admin/class-discord-image-grabber-admin.php';
        
        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(__DIR__) . 'public/class-discord-image-grabber-public.php';
        
        $this->loader = new Discord_Image_Grabber_Loader();
        
    }
    
    /**
     * Define the locale for this plugin for internationalization.
     * Uses the Discord_Image_Grabber_i18n class in order to set the domain and to register the hook
     * with WordPress.
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        
        $plugin_i18n = new Discord_Image_Grabber_i18n();
        
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
        
    }
    
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        
        $plugin_admin = new Discord_Image_Grabber_Admin($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // Add menu item
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        
        // Add Settings link to the plugin
        $plugin_basename = plugin_basename(plugin_dir_path(__DIR__) . $this->plugin_name . '.php');
        $this->loader->add_filter('plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links');
        
        // Save/Update our plugin options
        $this->loader->add_action('admin_init', $plugin_admin, 'options_update');
    }
    
    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     * @return    string    The name of the plugin.
     * @since     1.0.0
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }
    
    /**
     * Retrieve the version number of the plugin.
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version()
    {
        return $this->version;
    }
    
    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        
        $plugin_public = new Discord_Image_Grabber_Public($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
    }
    
    public function hourly_grab()
    {
        $this->writeLog('step 1', 'dig', true);

        // Get all plugin options
        $this->writeLog(print_r($this->options, true), 'options', false);
        
        $this->writeLog('step 2', 'dig', true);

        // 1. set up channel IDs
        $channel_ids = explode("\n", $this->options['base_channel_ids']);
        array_map('trim', $channel_ids);

        // 2. set up basic data
        $base_url = 'https://discord.com/api/v6/channels/%s/messages?limit=%d';
        $limit = (int)$this->options['download_max_file_num'] ?? 100;
        $after = (string)$this->options['download_last_message_id'] ?? '';

        foreach ($channel_ids as $id) {
            if ($id === '') {
                continue;
            }

            // 3. prepare URL
            $url = sprintf($base_url, $id, $limit);
            if ($after !== '') {
                $url .= '&after=' . $after;
            }

            $response = $this->make_get_request($url);
            if ($response === false) {
                $this->writeLog("result is null", "dig", true);
                return;
            }

//            if ($response->code >= 400) {
//                $this->writeLog(sprintf("got status code %s", $response->code), 'dig', true);
//                continue;
//            }

            $json = json_decode($response->body, true);

            $this->writeLog(sprintf("got %d elements in resulting JSON array from URL '%s' (%s)", count($json), $url, $response->body), 'request', false);

            // first message element is the newest (= last message id)
        }


    }
    
    private function send_discord_notification($message)
    {
        if ((int) $this->options['discord_enabled'] === 1) {
            $fields   = json_encode(['content' => $message]);
            $response = $this->make_get_request($this->options['discord_webhook'], ['body' => $fields]);
            $this->writeLog(print_r($response, true), 'discord_response');
            return true;
        }
        $this->writeLog('step 11', 'steps', true);
        return false;
    }
    
    private function send_slack_notification($message)
    {
        if ((int) $this->options['slack_enabled'] === 1) {
            $fields = [
                'token'    => $this->options['slack_apikey'],
                'channel'  => $this->options['slack_channel'], // prefix with a '#'
                'text'     => $message,
                'username' => $this->options['slack_botname'], // freely name the sender
            ];
        
            $response = $this->make_get_request('https://slack.com/api/chat.postMessage', ['body' => $fields]);
            $this->writeLog(print_r($response, true), 'slack_response');
            return true;
        }
        $this->writeLog('step 12', 'steps', true);
        return false;
    }
    
    private function send_email_notification($message)
    {
        if ((int) $this->options['email_enabled'] === 1) {
            $emails  = explode(',', $this->options['email_list']);
            $headers = [
                'From' => get_bloginfo('admin_email'),
            ];
            foreach ($emails as $email) {
                wp_mail(trim($email), 'New images grabbed', $message, $headers);
            }
            return true;
        }
        $this->writeLog('step 15 (end)', 'steps', true);
        return false;
    }
    
    private function writeLog(string $cont, string $filename = 'default', bool $append = false)
    {
        if (WP_DEBUG === true) {
            $logDir = WP_PLUGIN_DIR. '/discord-image-grabber/logs/';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0775, true);
            }
            //$logDir = "C:\\Local\\";
            if (!$append) {
                file_put_contents($logDir . $filename . '.log', $cont);
            } else {
                file_put_contents($logDir . $filename . '.log', $cont . PHP_EOL, FILE_APPEND);
            }
        }
    }

    private function make_get_request(string $url) : ?Response
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CAINFO, WP_PLUGIN_DIR . '/discord-image-grabber/cacert.pem');

        $headers = [
            //'Accept: application/json',
            //'Accept-Encoding: gzip, deflate',
            //'Accept-Language: en-US,en;q=0.5',
            //'Cache-Control: no-cache',
            //'Content-Type: application/json',
            'Authorization: Bot ' . $this->options['auth_bot_token'],
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        if(curl_errno($ch)) {
            $this->writeLog('Request Error:' . curl_error($ch), "dig", true);
            return null;
        }
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->writeLog("Status code was " . $status_code, "dig", true);
        curl_close ($ch);

        $r = new Response();
        $r->body = $response;
//        $r->code = $status_code;
//        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
//        $r->headers = substr($response, 0, $header_size);
//        $r->body = substr($response, $header_size);

        return $r;
    }

    private function make_request(string $url, string $method, array $data = null, array $additional_headers = [])
    {
        $args = array(
            'timeout' => 60,
            'headers' => array(
                'User-Agent' => 'Wordpress/' . get_bloginfo('version'),
                'Authorization' => 'Bot ' . trim($this->options['auth_bot_token']),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ),
        );
        $this->writeLog(print_r($args, true), "args", false);

        foreach ($additional_headers as $key => $value) {
            $args['headers'][$key] = $value;
        }

        if ($method === 'post') {
            $args['headers']['body'] = $data;
            $raw_response = wp_safe_remote_post($url, $args);
        } else if ($method === 'get') {
            $raw_response = wp_safe_remote_get($url, $args);
        } else {
            $this->writeLog('Invalid method ' . $method);
            return false;
        }
        if ($raw_response instanceof WP_Error) {
            $this->writeLog('Could not fetch url ' . $url . ': ' . $raw_response->get_error_message());
            
            return false;
        }
        
//        $body = wp_remote_retrieve_body($raw_response);
//        if (empty($body)) {
//            $this->writeLog('Response body is empty from url ' . $url);
//
//            return false;
//        }
        
//        return $body;
        return $raw_response;
    }

    private function remove_posts(array $post_array)
    {
        foreach ($post_array as $entry) {
            wp_delete_post($entry['wp_post_entry_id'], true);
        }
    }
    
    /**
     * Run the loader to execute all of the hooks with WordPress.
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }
    
    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     * @return    Discord_Image_Grabber_Loader    Orchestrates the hooks of the plugin.
     * @since     1.0.0
     */
    public function get_loader()
    {
        return $this->loader;
    }
    
    
}

class Response
{
    public $headers;

    public $code;

    public $body;
}