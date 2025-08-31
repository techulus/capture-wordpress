<?php
/**
 * Plugin Name: Capture
 * Plugin URI: https://capture.page
 * Description: Embed website screenshots and PDFs using Capture API with simple shortcodes.
 * Version: 1.0.0
 * Author: Arjun Komath
 * Author URI: https://capture.page
 * License: MIT
 * Text Domain: capture-page
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CAPTURE_PLUGIN_VERSION', '1.0.0');
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
        add_action('init', array($this, 'load_textdomain'));
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

    public function load_textdomain()
    {
        load_plugin_textdomain('capture-page', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function enqueue_scripts()
    {
        wp_enqueue_style('capture-style', CAPTURE_PLUGIN_URL . 'assets/css/capture-style.css', array(), CAPTURE_PLUGIN_VERSION);
    }

    public function admin_enqueue_scripts($hook)
    {
        if ('settings_page_capture-page' !== $hook) {
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
            'url' => '',
            'vw' => 1200,
            'vh' => 800,
            'full' => false,
            'delay' => 0,
            'format' => 'png',
            'quality' => 90,
            'class' => 'capture-screenshot',
            'alt' => 'Website Screenshot',
            'loading' => 'lazy',
            'cache' => true
        ), $atts, 'capture_screenshot');

        if (empty($atts['url'])) {
            return '<p class="capture-error">' . esc_html__('URL is required for screenshot shortcode.', 'capture-page') . '</p>';
        }

        $api = new CaptureAPI();
        if (!$api->is_configured()) {
            return '<p class="capture-error">' . esc_html__('Capture API credentials not configured. Please check plugin settings.', 'capture-page') . '</p>';
        }

        $options = array(
            'vw' => intval($atts['vw']),
            'vh' => intval($atts['vh']),
            'full' => filter_var($atts['full'], FILTER_VALIDATE_BOOLEAN),
            'delay' => intval($atts['delay']),
            'format' => sanitize_text_field($atts['format']),
            'quality' => intval($atts['quality'])
        );

        if (filter_var($atts['cache'], FILTER_VALIDATE_BOOLEAN)) {
            $options['t'] = time();
        }

        $image_url = $api->build_image_url(esc_url_raw($atts['url']), $options);

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
            'url' => '',
            'format' => 'A4',
            'orientation' => 'portrait',
            'full' => false,
            'delay' => 0,
            'class' => 'capture-pdf',
            'text' => 'Download PDF',
            'target' => '_blank',
            'cache' => true
        ), $atts, 'capture_pdf');

        if (empty($atts['url'])) {
            return '<p class="capture-error">' . esc_html__('URL is required for PDF shortcode.', 'capture-page') . '</p>';
        }

        $api = new CaptureAPI();
        if (!$api->is_configured()) {
            return '<p class="capture-error">' . esc_html__('Capture API credentials not configured. Please check plugin settings.', 'capture-page') . '</p>';
        }

        $options = array(
            'format' => sanitize_text_field($atts['format']),
            'orientation' => sanitize_text_field($atts['orientation']),
            'full' => filter_var($atts['full'], FILTER_VALIDATE_BOOLEAN),
            'delay' => intval($atts['delay'])
        );

        if (filter_var($atts['cache'], FILTER_VALIDATE_BOOLEAN)) {
            $options['t'] = time();
        }

        $pdf_url = $api->build_pdf_url(esc_url_raw($atts['url']), $options);

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