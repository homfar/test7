<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once WPCP_PATH . 'includes/class-wpcp-settings.php';
require_once WPCP_PATH . 'includes/class-wpcp-calendar.php';
require_once WPCP_PATH . 'includes/class-wpcp-widget.php';
require_once WPCP_PATH . 'includes/class-wpcp-elementor.php';
require_once WPCP_PATH . 'includes/class-wpcp-gutenberg.php';

class WPCP_Plugin
{
    private static $instance = null;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        register_activation_hook(WPCP_FILE, [$this, 'activate']);

        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('init', [$this, 'register_assets']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('init', [$this, 'register_shortcode']);

        WPCP_Settings::instance();
        WPCP_Calendar::instance();
        WPCP_Widget::instance();
        WPCP_Gutenberg::instance();
        WPCP_Elementor::instance();
    }

    public function activate()
    {
        if (!get_option('wpcp_settings')) {
            update_option('wpcp_settings', WPCP_Settings::default_settings());
        }
        flush_rewrite_rules();
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('wp-persian-calendar-pro', false, dirname(plugin_basename(WPCP_FILE)) . '/languages');
    }

    public function register_assets()
    {
        wp_register_style('wpcp-frontend', WPCP_URL . 'assets/css/frontend.css', [], WPCP_VERSION);
        wp_register_script('wpcp-frontend', WPCP_URL . 'assets/js/frontend.js', ['jquery'], WPCP_VERSION, true);

        wp_register_style('wpcp-admin', WPCP_URL . 'assets/css/admin.css', [], WPCP_VERSION);
        wp_register_script('wpcp-admin', WPCP_URL . 'assets/js/admin.js', ['jquery', 'jquery-ui-sortable'], WPCP_VERSION, true);
    }

    public function frontend_assets()
    {
        wp_enqueue_style('wpcp-frontend');
        wp_enqueue_script('wpcp-frontend');

        wp_localize_script('wpcp-frontend', 'wpcpData', [
            'settings' => WPCP_Settings::get_settings(),
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
    }

    public function admin_assets($hook)
    {
        if ($hook !== 'toplevel_page_wpcp-settings') {
            return;
        }

        wp_enqueue_style('wpcp-admin');
        wp_enqueue_script('wpcp-admin');
    }

    public function register_shortcode()
    {
        add_shortcode('persian_calendar_pro', ['WPCP_Calendar', 'render_shortcode']);
    }
}
