<?php
/**
 * Plugin Name: WP Persian Calendar Pro
 * Description: تقویم شمسی حرفه‌ای با مناسبت‌های ایران/جهان، اوقات شرعی، شورتکد، ابزارک، بلوک گوتنبرگ و ویجت المنتور.
 * Version: 1.0.0
 * Author: Codex
 * Text Domain: wp-persian-calendar-pro
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WPCP_VERSION', '1.0.0');
define('WPCP_FILE', __FILE__);
define('WPCP_PATH', plugin_dir_path(__FILE__));
define('WPCP_URL', plugin_dir_url(__FILE__));

require_once WPCP_PATH . 'includes/class-wpcp-plugin.php';

WPCP_Plugin::instance();
