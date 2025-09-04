<?php
/**
 * Plugin Name: Capture - Screenshots & PDF
 * Plugin URI: https://capture.page
 * Description: Embed website screenshots and PDFs using Capture API with simple shortcodes.
 * Version: 1.1.0
 * Author: Arjun Komath
 * Author URI: https://techulus.com
 * License: GPLv3
 * Text Domain: capture-screenshots-pdf
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CAPTURE_PLUGIN_VERSION', '1.1.0');
define('CAPTURE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CAPTURE_PLUGIN_PATH', plugin_dir_path(__FILE__));

class CapturePage
{
    private static $instance = null;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->init();
    }

    private function init()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

        require_once CAPTURE_PLUGIN_PATH . 'includes/class-capture-api.php';
        require_once CAPTURE_PLUGIN_PATH . 'includes/class-capture-admin.php';

        new CaptureAdmin();

        add_shortcode('capture_screenshot', array($this, 'screenshot_shortcode'));
        add_shortcode('capture_pdf', array($this, 'pdf_shortcode'));

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }


    public function enqueue_scripts()
    {
        wp_enqueue_style('capture-style', CAPTURE_PLUGIN_URL . 'assets/css/capture-style.css', array(), CAPTURE_PLUGIN_VERSION);
    }

    public function admin_enqueue_scripts($hook)
    {
        if ('settings_page_capture-screenshots-pdf' !== $hook) {
            return;
        }
        wp_enqueue_script('capture-admin', CAPTURE_PLUGIN_URL . 'assets/js/capture-admin.js', array('jquery'), CAPTURE_PLUGIN_VERSION, true);
        wp_localize_script('capture-admin', 'capture_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('capture_admin_nonce')
        ));
    }

    public function screenshot_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            // Required
            'url' => '',
            
            // Viewport & Capture Area
            'vw' => 1440,
            'vh' => 900,
            'scalefactor' => 1,
            'top' => 0,
            'left' => 0,
            'width' => null,
            'height' => null,
            
            // Timing & Waiting
            'waitfor' => '',
            'waitforid' => '',
            'delay' => 0,
            
            // Screenshot Options
            'full' => false,
            'darkmode' => false,
            'blockcookiebanners' => false,
            'blockads' => false,
            'bypassbotdetection' => false,
            
            // Selectors
            'selector' => '',
            'selectorid' => '',
            'transparent' => false,
            
            // Device & Rendering
            'useragent' => '',
            'emulatedevice' => '',
            'httpauth' => '',
            
            // Image Processing
            'resizewidth' => null,
            'resizeheight' => null,
            'type' => 'png',
            'bestformat' => true,
            
            // Caching
            'fresh' => false,
            
            // WordPress Specific
            'class' => 'capture-screenshot',
            'alt' => 'Website Screenshot',
            'loading' => 'lazy'
        ), $atts, 'capture_screenshot');

        if (empty($atts['url'])) {
            return '<p class="capture-error">' . esc_html__('URL is required for screenshot shortcode.', 'capture-screenshots-pdf') . '</p>';
        }

        $api = new CaptureAPI();
        if (!$api->is_configured()) {
            return '<p class="capture-error">' . esc_html__('Capture API credentials not configured. Please check plugin settings.', 'capture-screenshots-pdf') . '</p>';
        }

        // Build API options with proper parameter names
        $api_options = array();
        
        // Viewport & Capture Area
        $api_options['vw'] = intval($atts['vw']);
        $api_options['vh'] = intval($atts['vh']);
        if ($atts['scalefactor'] != 1) $api_options['scaleFactor'] = floatval($atts['scalefactor']);
        if ($atts['top'] != 0) $api_options['top'] = intval($atts['top']);
        if ($atts['left'] != 0) $api_options['left'] = intval($atts['left']);
        if (!is_null($atts['width'])) $api_options['width'] = intval($atts['width']);
        if (!is_null($atts['height'])) $api_options['height'] = intval($atts['height']);
        
        // Timing & Waiting
        if (!empty($atts['waitfor'])) $api_options['waitFor'] = sanitize_text_field($atts['waitfor']);
        if (!empty($atts['waitforid'])) $api_options['waitForId'] = sanitize_text_field($atts['waitforid']);
        if ($atts['delay'] != 0) $api_options['delay'] = intval($atts['delay']);
        
        // Screenshot Options
        if (filter_var($atts['full'], FILTER_VALIDATE_BOOLEAN)) $api_options['full'] = true;
        if (filter_var($atts['darkmode'], FILTER_VALIDATE_BOOLEAN)) $api_options['darkMode'] = true;
        if (filter_var($atts['blockcookiebanners'], FILTER_VALIDATE_BOOLEAN)) $api_options['blockCookieBanners'] = true;
        if (filter_var($atts['blockads'], FILTER_VALIDATE_BOOLEAN)) $api_options['blockAds'] = true;
        if (filter_var($atts['bypassbotdetection'], FILTER_VALIDATE_BOOLEAN)) $api_options['bypassBotDetection'] = true;
        
        // Selectors
        if (!empty($atts['selector'])) $api_options['selector'] = sanitize_text_field($atts['selector']);
        if (!empty($atts['selectorid'])) $api_options['selectorId'] = sanitize_text_field($atts['selectorid']);
        if (filter_var($atts['transparent'], FILTER_VALIDATE_BOOLEAN)) $api_options['transparent'] = true;
        
        // Device & Rendering
        if (!empty($atts['useragent'])) $api_options['userAgent'] = sanitize_text_field($atts['useragent']);
        if (!empty($atts['emulatedevice'])) $api_options['emulateDevice'] = sanitize_text_field($atts['emulatedevice']);
        if (!empty($atts['httpauth'])) $api_options['httpAuth'] = sanitize_text_field($atts['httpauth']);
        
        // Image Processing
        if (!is_null($atts['resizewidth'])) $api_options['resizeWidth'] = intval($atts['resizewidth']);
        if (!is_null($atts['resizeheight'])) $api_options['resizeHeight'] = intval($atts['resizeheight']);
        if ($atts['type'] !== 'png') $api_options['type'] = sanitize_text_field($atts['type']);
        if (!filter_var($atts['bestformat'], FILTER_VALIDATE_BOOLEAN)) $api_options['bestFormat'] = false;
        
        // Caching
        if (filter_var($atts['fresh'], FILTER_VALIDATE_BOOLEAN)) $api_options['fresh'] = true;

        $image_url = $api->build_image_url(esc_url_raw($atts['url']), $api_options);

        return sprintf(
            '<img src="%s" alt="%s" class="%s" loading="%s" />',
            esc_url($image_url),
            esc_attr($atts['alt']),
            esc_attr($atts['class']),
            esc_attr($atts['loading'])
        );
    }

    public function pdf_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            // Required
            'url' => '',
            
            // Page Dimensions
            'width' => null,
            'height' => null,
            'format' => 'A4',
            
            // Margins
            'margintop' => null,
            'marginright' => null,
            'marginbottom' => null,
            'marginleft' => null,
            
            // Rendering
            'scale' => 1,
            'landscape' => false,
            'printbackground' => false,
            
            // Timing
            'delay' => 0,
            'timestamp' => null,
            
            // Authentication
            'httpauth' => '',
            'useragent' => '',
            
            // WordPress Specific
            'text' => 'Download PDF',
            'target' => '_blank',
            'class' => 'capture-pdf'
        ), $atts, 'capture_pdf');

        if (empty($atts['url'])) {
            return '<p class="capture-error">' . esc_html__('URL is required for PDF shortcode.', 'capture-screenshots-pdf') . '</p>';
        }

        $api = new CaptureAPI();
        if (!$api->is_configured()) {
            return '<p class="capture-error">' . esc_html__('Capture API credentials not configured. Please check plugin settings.', 'capture-screenshots-pdf') . '</p>';
        }

        // Build API options with proper parameter names
        $api_options = array();
        
        // Page Dimensions
        if (!is_null($atts['width'])) $api_options['width'] = sanitize_text_field($atts['width']);
        if (!is_null($atts['height'])) $api_options['height'] = sanitize_text_field($atts['height']);
        if ($atts['format'] !== 'A4') $api_options['format'] = sanitize_text_field($atts['format']);
        
        // Margins
        if (!is_null($atts['margintop'])) $api_options['marginTop'] = sanitize_text_field($atts['margintop']);
        if (!is_null($atts['marginright'])) $api_options['marginRight'] = sanitize_text_field($atts['marginright']);
        if (!is_null($atts['marginbottom'])) $api_options['marginBottom'] = sanitize_text_field($atts['marginbottom']);
        if (!is_null($atts['marginleft'])) $api_options['marginLeft'] = sanitize_text_field($atts['marginleft']);
        
        // Rendering
        if ($atts['scale'] != 1) $api_options['scale'] = floatval($atts['scale']);
        if (filter_var($atts['landscape'], FILTER_VALIDATE_BOOLEAN)) $api_options['landscape'] = true;
        if (filter_var($atts['printbackground'], FILTER_VALIDATE_BOOLEAN)) $api_options['printBackground'] = true;
        
        // Timing
        if ($atts['delay'] != 0) $api_options['delay'] = intval($atts['delay']);
        if (!is_null($atts['timestamp'])) $api_options['timestamp'] = sanitize_text_field($atts['timestamp']);
        
        // Authentication
        if (!empty($atts['httpauth'])) $api_options['httpAuth'] = sanitize_text_field($atts['httpauth']);
        if (!empty($atts['useragent'])) $api_options['userAgent'] = sanitize_text_field($atts['useragent']);

        $pdf_url = $api->build_pdf_url(esc_url_raw($atts['url']), $api_options);

        return sprintf(
            '<a href="%s" target="%s" class="%s">%s</a>',
            esc_url($pdf_url),
            esc_attr($atts['target']),
            esc_attr($atts['class']),
            esc_html($atts['text'])
        );
    }

    public function activate()
    {
        $default_options = array(
            'api_key' => '',
            'api_secret' => '',
            'default_vw' => 1200,
            'default_vh' => 800,
            'default_delay' => 0,
            'default_quality' => 90
        );
        
        add_option('capture_page_settings', $default_options);
    }

    public function deactivate()
    {
        // Cleanup if needed
    }
}

CapturePage::get_instance();
