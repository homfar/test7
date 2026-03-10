<?php

if (!defined('ABSPATH')) {
    exit;
}

class WPCP_Calendar
{
    private static $instance = null;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        add_action('wp_ajax_wpcp_pray_times', [$this, 'ajax_pray_times']);
        add_action('wp_ajax_nopriv_wpcp_pray_times', [$this, 'ajax_pray_times']);
    }

    public static function render_shortcode($atts = [])
    {
        $settings = WPCP_Settings::get_settings();
        $cache_key = 'wpcp_html_' . md5(wp_json_encode($settings));

        if (!empty($settings['enable_cache'])) {
            $cached = get_transient($cache_key);
            if ($cached) {
                return $cached;
            }
        }

        $occasions = self::default_occasions();
        $custom = json_decode($settings['custom_occasions_json'], true);
        if (is_array($custom)) {
            $occasions = array_merge($occasions, $custom);
        }
        $events = $settings['custom_events'];

        ob_start();
        ?>
        <div class="wpcp-calendar theme-<?php echo esc_attr($settings['theme']); ?> <?php echo $settings['dark_mode'] ? 'is-dark' : ''; ?>" style="--wpcp-primary:<?php echo esc_attr($settings['colors']['primary']); ?>;--wpcp-btn-text:<?php echo esc_attr($settings['colors']['button_text']); ?>;--wpcp-bg:<?php echo esc_attr($settings['colors']['background']); ?>;--wpcp-surface:<?php echo esc_attr($settings['colors']['surface']); ?>;--wpcp-text:<?php echo esc_attr($settings['colors']['text']); ?>;--wpcp-border:<?php echo esc_attr($settings['colors']['border']); ?>;--wpcp-today:<?php echo esc_attr($settings['colors']['today']); ?>;--wpcp-holiday:<?php echo esc_attr($settings['colors']['holiday']); ?>;--wpcp-radius:<?php echo (int) $settings['radius']; ?>px;--wpcp-shadow:<?php echo (int) $settings['shadow']; ?>;max-width:<?php echo esc_attr($settings['widget_width']); ?>;font-family:<?php echo esc_attr($settings['font_family']); ?>;">
            <div class="wpcp-header">
                <div class="wpcp-title-wrap"><h3><?php echo esc_html($settings['title']); ?></h3><small><?php echo esc_html($settings['subtitle']); ?></small></div>
                <?php if (!empty($settings['show_today_button'])): ?><button class="wpcp-today-btn">امروز</button><?php endif; ?>
            </div>
            <div class="wpcp-nav">
                <button class="wpcp-prev">◀</button>
                <div class="wpcp-month-year">
                    <select class="wpcp-month"></select>
                    <select class="wpcp-year"></select>
                </div>
                <button class="wpcp-next">▶</button>
            </div>
            <div class="wpcp-weekdays"></div>
            <div class="wpcp-grid" data-events='<?php echo esc_attr(wp_json_encode($events)); ?>' data-occasions='<?php echo esc_attr(wp_json_encode($occasions)); ?>' data-show-week-number="<?php echo esc_attr((int) $settings['show_week_number']); ?>" data-week-start="<?php echo esc_attr($settings['week_start']); ?>"></div>
            <div class="wpcp-footer">
                <div class="wpcp-pray-times" data-city="<?php echo esc_attr($settings['city']); ?>">درحال بارگذاری اوقات شرعی...</div>
                <?php if (!empty($settings['show_gregorian'])): ?><small class="wpcp-gregorian-date"></small><?php endif; ?><small class="wpcp-hijri-date"></small><small class="wpcp-week-number"></small>
            </div>
            <style><?php echo wp_strip_all_tags($settings['custom_css']); ?></style>
        </div>
        <?php
        $html = ob_get_clean();

        if (!empty($settings['enable_cache'])) {
            set_transient($cache_key, $html, (int) $settings['cache_ttl']);
        }

        return $html;
    }

    public function ajax_pray_times()
    {
        $city = sanitize_text_field($_GET['city'] ?? 'tehran');
        $cache_key = 'wpcp_pray_' . md5($city . gmdate('Y-m-d'));
        $cached = get_transient($cache_key);
        if ($cached) {
            wp_send_json_success($cached);
        }

        $url = add_query_arg([
            'city' => $city,
            'country' => 'Iran',
            'method' => 7,
        ], 'https://api.aladhan.com/v1/timingsByCity');

        $response = wp_remote_get($url, ['timeout' => 10]);
        if (is_wp_error($response)) {
            wp_send_json_error('service_unavailable');
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['data']['timings'])) {
            wp_send_json_error('invalid_data');
        }

        $timings = [
            'Fajr' => $body['data']['timings']['Fajr'] ?? '',
            'Sunrise' => $body['data']['timings']['Sunrise'] ?? '',
            'Dhuhr' => $body['data']['timings']['Dhuhr'] ?? '',
            'Maghrib' => $body['data']['timings']['Maghrib'] ?? '',
            'Midnight' => $body['data']['timings']['Midnight'] ?? '',
        ];

        set_transient($cache_key, $timings, HOUR_IN_SECONDS);
        wp_send_json_success($timings);
    }

    private static function default_occasions()
    {
        return [
            ['date' => '2026-03-21', 'title' => 'نوروز', 'type' => 'iran'],
            ['date' => '2026-03-20', 'title' => 'چهارشنبه‌سوری', 'type' => 'iran'],
            ['date' => '2026-02-11', 'title' => 'پیروزی انقلاب اسلامی', 'type' => 'iran'],
            ['date' => '2026-04-01', 'title' => 'روز جمهوری اسلامی', 'type' => 'iran'],
            ['date' => '2026-01-01', 'title' => 'سال نو میلادی', 'type' => 'global'],
            ['date' => '2026-03-08', 'title' => 'روز جهانی زن', 'type' => 'global'],
            ['date' => '2026-05-01', 'title' => 'روز جهانی کارگر', 'type' => 'global'],
            ['date' => '2026-06-17', 'title' => 'عید قربان (قمری)', 'type' => 'hijri'],
        ];
    }
}
