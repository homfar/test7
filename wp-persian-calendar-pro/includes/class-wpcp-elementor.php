<?php

if (!defined('ABSPATH')) {
    exit;
}

class WPCP_Elementor
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
        add_action('elementor/widgets/register', [$this, 'register_widget']);
    }

    public function register_widget($widgets_manager)
    {
        if (!did_action('elementor/loaded')) {
            return;
        }

        require_once WPCP_PATH . 'includes/class-wpcp-elementor-widget.php';

        if (class_exists('WPCP_Elementor_Widget')) {
            $widgets_manager->register(new WPCP_Elementor_Widget());
        }
    }
}
