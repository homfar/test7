<?php

if (!defined('ABSPATH')) {
    exit;
}

class WPCP_Gutenberg
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
        add_action('init', [$this, 'register_block']);
    }

    public function register_block()
    {
        wp_register_script(
            'wpcp-block',
            WPCP_URL . 'build/block.js',
            ['wp-blocks', 'wp-element', 'wp-editor'],
            WPCP_VERSION,
            true
        );

        register_block_type('wpcp/calendar', [
            'editor_script' => 'wpcp-block',
            'render_callback' => function () {
                return WPCP_Calendar::render_shortcode();
            },
        ]);
    }
}
