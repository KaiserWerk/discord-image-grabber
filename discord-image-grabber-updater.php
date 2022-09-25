<?php
/**
 * @author Robin Kaiser
 * @github https://github.com/KaiserWerk/WPCustomRepository
 * This plugin updater class belongs to the GitHub Repository called
 * WPCustomRepository and is published under GNU GENERAL PUBLIC LICENSE v2. *
 */

class DiscordImageGrabberUpdater
{
    
    /**
     * @var array
     */
    protected $dig_plugin_data;
    /**
     * @var string
     */
    protected $plugin_name;
    /**
     * @var string
     */
    protected $slug;
    /**
     * @var string
     */
    protected $version;
    /**
     * @var string
     */
    protected $update_endpoint;
    /**
     * @var bool
     */
    protected $update_disable_sslverify;
    
    /**
     * plugin_updater_Discord_Image_Grabber constructor.
     *
     * @param array $dig_plugin_data
     * @param bool $constructor_only
     */
    public function __construct($dig_plugin_data, $constructor_only = false)
    {
        if ($constructor_only === false) {
            add_filter('plugins_api', array(&$this, 'plugins_api'), 10, 3);
            add_filter('pre_set_site_transient_update_plugins', array(&$this, 'update_plugins'), 10, 1);
            
            #$this->plugin_data = $dig_plugin_data;
            $this->plugin_name = $dig_plugin_data['name'];
            $this->slug        = $dig_plugin_data['slug'];
            $this->version     = $dig_plugin_data['version'];
            
            $options                        = get_option($this->slug);
            $this->update_endpoint          = isset($options['update_endpoint']) ? sanitize_text_field($options['update_endpoint']) : '';
            $this->update_disable_sslverify = isset($options['update_disable_sslverify']) ? (bool) $options['update_disable_sslverify'] : false;
        } else {
            $this->slug    = $dig_plugin_data['slug'];
            $this->version = $dig_plugin_data['version'];
        }
    }
    
    /**
     * @param mixed $false
     * @param mixed $action
     * @param object $args
     *
     * @return object
     */
    public function plugins_api($false, $action, $args)
    {
        if (!isset($args->slug) || $args->slug !== $this->slug) {
            return $false;
        }
        
        $response = $this->api_request('get-plugin-information');
        // they may or may not be all set
        @$response->ratings = (array) $response->ratings;
        @$response->sections = (array) $response->sections;
        @$response->banners = (array) $response->banners;
        
        $this->writeLog(print_r($response, true), 'get-plugin-information_response');
        
        return $response;
    }
    
    /**
     * @param string $action
     *
     * @return object|bool
     */
    private function api_request($action)
    {
        $url = $this->update_endpoint . '/api/plugins/' . $action . '/' . $this->slug;
        
        $params = ['timeout' => 10];
        
        if ($this->update_disable_sslverify === true) {
            $params['sslverify'] = false;
        }
        
        $this->writeLog(print_r($params, true), 'api_request_params', true);
        
        $request = wp_remote_get($url, $params);
        
        #$this->writeLog(print_r($request['body'], true), 'api_request_response', true);
        
        if (is_wp_error($request)) {
            return false;
        }
        
        $code = wp_remote_retrieve_response_code($request);
        if ($code !== 200) {
            return false;
        }
        
        $response = json_decode(wp_remote_retrieve_body($request));
        
        if ($response instanceof WP_Error) {
            return false;
        }
        
        if (!is_object($response)) {
            return false;
        }
        
        $this->writeLog(print_r($response, true), 'action_' . $action . '_response');
        
        return $response;
    }
    
    /**
     * @param string $cont
     * @param string $filename
     * @param bool $append
     */
    public function writeLog($cont, $filename = 'default', $append = false)
    {
        if (WP_DEBUG === true) {
            if (!is_dir(__DIR__ . '/logs')) {
                @mkdir(__DIR__ . '/logs', 0775, true);
            }
            if (!$append) {
                file_put_contents(__DIR__ . '/logs/' . $filename . '.log', $cont);
            } else {
                file_put_contents(__DIR__ . '/logs/' . $filename . '.log', $cont . PHP_EOL, FILE_APPEND);
            }
        }
    }
    
    /**
     * @param object $transient
     *
     * @return object
     */
    public function update_plugins($transient)
    {
        $this->writeLog(print_r($transient, true), 'transient');
        if (empty($transient->checked)) {
            return $transient;
        }
        
        $plugin_path = $this->slug . '/' . $this->slug . '.php';
        
        $this->writeLog($plugin_path, 'plugin_path');
        
        $response = $this->api_request('check-latest-version');
        
        if ($response !== false) {
            
            #$this->writeLog( print_r( $response, true ), 'check-latest-version_response' );
            
            if (is_object($response) && !($response instanceof WP_Error) && version_compare($response->new_version, $transient->checked[$plugin_path], '>')) {
                $transient->response[$plugin_path] = $response;
            }
        }
        
        return $transient;
        
    }
    
    /**
     * @param string $action
     *
     * @return bool
     */
    public function trackInstallations($action)
    {
        $options = get_option($this->slug);
        
        $url = $options['update_endpoint'] . '/api/plugins/track-installations';
        
        $fields = [
            'slug'    => $this->slug,
            'version' => $this->version,
            'action'  => $action,
        ];
        
        $response = wp_remote_request($url, [
            'method'  => 'POST',
            'body'    => $fields,
            'timeout' => 10,
        ]);
        
        if ($response instanceof WP_Error) {
            return false;
        }
        
        return true;
    }
}