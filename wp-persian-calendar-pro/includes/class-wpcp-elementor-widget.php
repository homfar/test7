<?php

if (!defined('ABSPATH')) {
    exit;
}

if (class_exists('\\Elementor\\Widget_Base') && !class_exists('WPCP_Elementor_Widget')) {
    class WPCP_Elementor_Widget extends \Elementor\Widget_Base
    {
        public function get_name()
        {
            return 'wpcp_calendar';
        }

        public function get_title()
        {
            return 'Persian Calendar Pro';
        }

        public function get_icon()
        {
            return 'eicon-calendar';
        }

        public function get_categories()
        {
            return ['general'];
        }

        protected function render()
        {
            echo WPCP_Calendar::render_shortcode();
        }
    }
}
