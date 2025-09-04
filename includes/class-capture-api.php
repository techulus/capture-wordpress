<?php

if (!defined('ABSPATH')) {
    exit;
}

class CaptureAPI
{
    const CDN_URL = 'https://cdn.capture.page';
    
    private $api_key;
    private $api_secret;
    private $settings;

    public function __construct()
    {
        $this->settings = get_option('capture_page_settings', array());
        $this->api_key = isset($this->settings['api_key']) ? $this->settings['api_key'] : '';
        $this->api_secret = isset($this->settings['api_secret']) ? $this->settings['api_secret'] : '';
    }

    public function is_configured()
    {
        return !empty($this->api_key) && !empty($this->api_secret);
    }

    private function generate_token($secret, $query_string)
    {
        return md5($secret . $query_string);
    }

    private function build_query_string($options)
    {
        $filtered_options = array();
        
        foreach ($options as $key => $value) {
            if ($key === 'format') {
                continue;
            }
            if (empty($value) && $value !== 0 && $value !== false) {
                continue;
            }
            
            if (is_bool($value)) {
                $filtered_options[$key] = $value ? 'true' : 'false';
            } else {
                $filtered_options[$key] = $value;
            }
        }

        return http_build_query($filtered_options, '', '&', PHP_QUERY_RFC3986);
    }

    private function build_url($type, $url, $options = array())
    {
        if (!$this->is_configured()) {
            throw new Exception('API key and secret are required');
        }

        if (empty($url)) {
            throw new Exception('URL is required');
        }

        $options['url'] = $url;
        $query_string = $this->build_query_string($options);
        $token = $this->generate_token($this->api_secret, $query_string);

        return sprintf(
            '%s/%s/%s/%s?%s',
            self::CDN_URL,
            $this->api_key,
            $token,
            $type,
            $query_string
        );
    }

    public function build_image_url($url, $options = array())
    {
        return $this->build_url('image', $url, $options);
    }

    public function build_pdf_url($url, $options = array())
    {
        return $this->build_url('pdf', $url, $options);
    }

    public function build_content_url($url, $options = array())
    {
        return $this->build_url('content', $url, $options);
    }

    public function build_metadata_url($url, $options = array())
    {
        return $this->build_url('metadata', $url, $options);
    }

    public function test_connection($test_url = 'https://example.com')
    {
        if (!$this->is_configured()) {
            return array(
                'success' => false,
                'message' => __('API credentials are not configured.', 'capture-screenshots-pdf')
            );
        }

        try {
            $image_url = $this->build_image_url($test_url, array('vw' => 400, 'vh' => 300, 'fresh' => true));
            
            $response = wp_remote_head($image_url, array(
                'timeout' => 10,
                'user-agent' => 'Capture WordPress Plugin/' . CAPTURE_PLUGIN_VERSION
            ));

            if (is_wp_error($response)) {
                return array(
                    'success' => false,
                    'message' => __('Connection failed: ', 'capture-screenshots-pdf') . $response->get_error_message()
                );
            }

            $response_code = wp_remote_retrieve_response_code($response);
            
            if ($response_code === 200) {
                return array(
                    'success' => true,
                    'message' => __('Connection successful! API credentials are working.', 'capture-screenshots-pdf')
                );
            } else {
                return array(
                    'success' => false,
                    /* translators: %d is the HTTP response code from the API */
                    'message' => sprintf(__('API returned error code: %d', 'capture-screenshots-pdf'), $response_code)
                );
            }

        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => __('Error: ', 'capture-screenshots-pdf') . $e->getMessage()
            );
        }
    }

    public function get_default_options()
    {
        return array(
            'vw' => isset($this->settings['default_vw']) ? intval($this->settings['default_vw']) : 1200,
            'vh' => isset($this->settings['default_vh']) ? intval($this->settings['default_vh']) : 800,
            'delay' => isset($this->settings['default_delay']) ? intval($this->settings['default_delay']) : 0
        );
    }
}