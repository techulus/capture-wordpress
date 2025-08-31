<?php

if (!defined('ABSPATH')) {
    exit;
}

class CaptureAdmin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('wp_ajax_capture_test_connection', array($this, 'test_connection_ajax'));
    }

    public function add_admin_menu()
    {
        add_options_page(
            __('Capture Settings', 'capture-page'),
            __('Capture', 'capture-page'),
            'manage_options',
            'capture-page',
            array($this, 'options_page')
        );
    }

    public function settings_init()
    {
        register_setting('capture_page_settings_group', 'capture_page_settings', array($this, 'sanitize_settings'));

        add_settings_section(
            'capture_page_api_section',
            __('API Configuration', 'capture-page'),
            array($this, 'api_section_callback'),
            'capture-page'
        );

        add_settings_field(
            'api_key',
            __('API Key', 'capture-page'),
            array($this, 'api_key_render'),
            'capture-page',
            'capture_page_api_section'
        );

        add_settings_field(
            'api_secret',
            __('API Secret', 'capture-page'),
            array($this, 'api_secret_render'),
            'capture-page',
            'capture_page_api_section'
        );

        add_settings_section(
            'capture_page_defaults_section',
            __('Default Settings', 'capture-page'),
            array($this, 'defaults_section_callback'),
            'capture-page'
        );

        add_settings_field(
            'default_vw',
            __('Default Viewport Width', 'capture-page'),
            array($this, 'default_vw_render'),
            'capture-page',
            'capture_page_defaults_section'
        );

        add_settings_field(
            'default_vh',
            __('Default Viewport Height', 'capture-page'),
            array($this, 'default_vh_render'),
            'capture-page',
            'capture_page_defaults_section'
        );

        add_settings_field(
            'default_delay',
            __('Default Delay (seconds)', 'capture-page'),
            array($this, 'default_delay_render'),
            'capture-page',
            'capture_page_defaults_section'
        );

    }

    public function sanitize_settings($input)
    {
        $sanitized = array();

        if (isset($input['api_key'])) {
            $sanitized['api_key'] = sanitize_text_field($input['api_key']);
        }

        if (isset($input['api_secret'])) {
            $sanitized['api_secret'] = sanitize_text_field($input['api_secret']);
        }

        if (isset($input['default_vw'])) {
            $sanitized['default_vw'] = absint($input['default_vw']);
            if ($sanitized['default_vw'] < 100) {
                $sanitized['default_vw'] = 1200;
            }
        }

        if (isset($input['default_vh'])) {
            $sanitized['default_vh'] = absint($input['default_vh']);
            if ($sanitized['default_vh'] < 100) {
                $sanitized['default_vh'] = 800;
            }
        }

        if (isset($input['default_delay'])) {
            $sanitized['default_delay'] = absint($input['default_delay']);
            if ($sanitized['default_delay'] > 30) {
                $sanitized['default_delay'] = 30;
            }
        }


        return $sanitized;
    }

    public function api_section_callback()
    {
        printf(
            '<p>%s <a href="https://capture.page/console" target="_blank">%s</a></p>',
            esc_html__('Get your API credentials from', 'capture-page'),
            esc_html__('Capture Console', 'capture-page')
        );
    }

    public function defaults_section_callback()
    {
        printf(
            '<p>%s</p>',
            esc_html__('Set default values for screenshot options. These can be overridden in individual shortcodes.', 'capture-page')
        );
    }

    public function api_key_render()
    {
        $settings = get_option('capture_page_settings');
        $value = isset($settings['api_key']) ? $settings['api_key'] : '';
        ?>
        <input type='text' name='capture_page_settings[api_key]' value='<?php echo esc_attr($value); ?>' class="regular-text" />
        <p class="description"><?php esc_html_e('Your Capture API key', 'capture-page'); ?></p>
        <?php
    }

    public function api_secret_render()
    {
        $settings = get_option('capture_page_settings');
        $value = isset($settings['api_secret']) ? $settings['api_secret'] : '';
        ?>
        <input type='password' name='capture_page_settings[api_secret]' value='<?php echo esc_attr($value); ?>' class="regular-text" />
        <p class="description"><?php esc_html_e('Your Capture API secret', 'capture-page'); ?></p>
        <?php
    }

    public function default_vw_render()
    {
        $settings = get_option('capture_page_settings');
        $value = isset($settings['default_vw']) ? $settings['default_vw'] : 1200;
        ?>
        <input type='number' name='capture_page_settings[default_vw]' value='<?php echo esc_attr($value); ?>' min="100" max="3000" />
        <p class="description"><?php esc_html_e('Default viewport width in pixels (minimum: 100)', 'capture-page'); ?></p>
        <?php
    }

    public function default_vh_render()
    {
        $settings = get_option('capture_page_settings');
        $value = isset($settings['default_vh']) ? $settings['default_vh'] : 800;
        ?>
        <input type='number' name='capture_page_settings[default_vh]' value='<?php echo esc_attr($value); ?>' min="100" max="3000" />
        <p class="description"><?php esc_html_e('Default viewport height in pixels (minimum: 100)', 'capture-page'); ?></p>
        <?php
    }

    public function default_delay_render()
    {
        $settings = get_option('capture_page_settings');
        $value = isset($settings['default_delay']) ? $settings['default_delay'] : 0;
        ?>
        <input type='number' name='capture_page_settings[default_delay]' value='<?php echo esc_attr($value); ?>' min="0" max="30" />
        <p class="description"><?php esc_html_e('Default delay in seconds before taking screenshot (maximum: 30)', 'capture-page'); ?></p>
        <?php
    }


    public function test_connection_ajax()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'capture-page'));
        }

        check_ajax_referer('capture_admin_nonce', 'nonce');

        $api = new CaptureAPI();
        $result = $api->test_connection();

        wp_send_json($result);
    }

    public function options_page()
    {
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            add_settings_error(
                'capture_page_messages',
                'capture_page_message',
                __('Settings saved successfully!', 'capture-page'),
                'updated'
            );
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php settings_errors('capture_page_messages'); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('capture_page_settings_group');
                do_settings_sections('capture-page');
                submit_button();
                ?>
            </form>
            
            <div class="capture-test-connection">
                <h3><?php esc_html_e('Test Connection', 'capture-page'); ?></h3>
                <p><?php esc_html_e('Test your API credentials to make sure they are working correctly.', 'capture-page'); ?></p>
                <button type="button" id="capture-test-btn" class="button button-secondary">
                    <?php esc_html_e('Test Connection', 'capture-page'); ?>
                </button>
                <div id="capture-test-result" style="margin-top: 10px;"></div>
            </div>
            
            <div class="capture-usage-examples" style="margin-top: 30px;">
                <h3><?php esc_html_e('Usage Examples', 'capture-page'); ?></h3>
                
                <h4><?php esc_html_e('Basic Screenshot', 'capture-page'); ?></h4>
                <code>[capture_screenshot url="https://example.com" vw=1200 vh=800 full=true]</code>
                
                <h4><?php esc_html_e('Advanced Screenshot with Options', 'capture-page'); ?></h4>
                <code>[capture_screenshot url="https://example.com" vw=1440 vh=900 full=true darkMode=true blockAds=true delay=2 type="webp"]</code>
                
                <h4><?php esc_html_e('Element-Specific Screenshot', 'capture-page'); ?></h4>
                <code>[capture_screenshot url="https://example.com" selector=".main-content" transparent=true]</code>
                
                <h4><?php esc_html_e('Basic PDF', 'capture-page'); ?></h4>
                <code>[capture_pdf url="https://example.com" format="A4" landscape=false text="Download PDF"]</code>
                
                <h4><?php esc_html_e('PDF with Custom Margins', 'capture-page'); ?></h4>
                <code>[capture_pdf url="https://example.com" format="Letter" marginTop="20mm" marginBottom="20mm" printBackground=true]</code>
                
                <div style="margin-top: 20px; padding: 15px; background: #f0f8ff; border-left: 4px solid #0073aa;">
                    <h4><?php esc_html_e('Documentation Links', 'capture-page'); ?></h4>
                    <p>
                        <strong><?php esc_html_e('Complete Parameter Reference:', 'capture-page'); ?></strong><br>
                        <a href="https://docs.capture.page/docs/screenshot-options" target="_blank"><?php esc_html_e('Screenshot Options', 'capture-page'); ?></a> | 
                        <a href="https://docs.capture.page/docs/pdf-options" target="_blank"><?php esc_html_e('PDF Options', 'capture-page'); ?></a><br>
                        <a href="https://docs.capture.page" target="_blank"><?php esc_html_e('Full Documentation', 'capture-page'); ?></a>
                    </p>
                </div>
                
                <h4><?php esc_html_e('Key Parameters', 'capture-page'); ?></h4>
                
                <h5><?php esc_html_e('Screenshot Parameters', 'capture-page'); ?></h5>
                <ul>
                    <li><strong>url</strong> - <?php esc_html_e('Target website URL (required)', 'capture-page'); ?></li>
                    <li><strong>vw/vh</strong> - <?php esc_html_e('Viewport width/height in pixels', 'capture-page'); ?></li>
                    <li><strong>full</strong> - <?php esc_html_e('Capture full page (true/false)', 'capture-page'); ?></li>
                    <li><strong>darkMode</strong> - <?php esc_html_e('Take dark mode screenshot (true/false)', 'capture-page'); ?></li>
                    <li><strong>blockAds</strong> - <?php esc_html_e('Block advertisements (true/false)', 'capture-page'); ?></li>
                    <li><strong>blockCookieBanners</strong> - <?php esc_html_e('Dismiss cookie consent (true/false)', 'capture-page'); ?></li>
                    <li><strong>selector</strong> - <?php esc_html_e('Screenshot specific CSS selector', 'capture-page'); ?></li>
                    <li><strong>waitFor</strong> - <?php esc_html_e('CSS selector to wait for before capture', 'capture-page'); ?></li>
                    <li><strong>delay</strong> - <?php esc_html_e('Delay in seconds before capture', 'capture-page'); ?></li>
                    <li><strong>type</strong> - <?php esc_html_e('Image format (png, jpeg, webp)', 'capture-page'); ?></li>
                    <li><strong>fresh</strong> - <?php esc_html_e('Force new screenshot, bypass cache (true/false)', 'capture-page'); ?></li>
                </ul>
                
                <h5><?php esc_html_e('PDF Parameters', 'capture-page'); ?></h5>
                <ul>
                    <li><strong>url</strong> - <?php esc_html_e('Target website URL (required)', 'capture-page'); ?></li>
                    <li><strong>format</strong> - <?php esc_html_e('Paper size (A4, Letter, Legal, etc.)', 'capture-page'); ?></li>
                    <li><strong>landscape</strong> - <?php esc_html_e('Paper orientation (true/false)', 'capture-page'); ?></li>
                    <li><strong>marginTop/marginBottom/marginLeft/marginRight</strong> - <?php esc_html_e('Page margins (with units)', 'capture-page'); ?></li>
                    <li><strong>printBackground</strong> - <?php esc_html_e('Print background graphics (true/false)', 'capture-page'); ?></li>
                    <li><strong>scale</strong> - <?php esc_html_e('Rendering scale (default: 1)', 'capture-page'); ?></li>
                    <li><strong>delay</strong> - <?php esc_html_e('Seconds to wait before capturing', 'capture-page'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
}