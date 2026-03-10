<?php

if (!defined('ABSPATH')) {
    exit;
}

class WPCP_Settings
{
    private static $instance = null;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function default_settings()
    {
        return [
            'title' => 'PersianCalendar',
            'subtitle' => 'تقویم حرفه‌ای فارسی',
            'theme' => 'material',
            'dark_mode' => 0,
            'show_gregorian' => 1,
            'show_week_number' => 1,
            'show_today_button' => 1,
            'week_start' => 'sat',
            'city' => 'tehran',
            'widget_width' => '100%',
            'font_family' => 'inherit',
            'custom_css' => '',
            'colors' => [
                'primary' => '#1976d2',
                'button_text' => '#ffffff',
                'background' => '#ffffff',
                'surface' => '#f4f6f8',
                'text' => '#212121',
                'border' => '#d7dde5',
                'today' => '#e3f2fd',
                'holiday' => '#ef5350',
            ],
            'radius' => 14,
            'shadow' => 25,
            'enable_cache' => 1,
            'cache_ttl' => 300,
            'custom_events' => [],
            'custom_occasions_json' => '',
        ];
    }

    public static function get_settings()
    {
        $saved = get_option('wpcp_settings', []);
        return wp_parse_args($saved, self::default_settings());
    }

    private function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_menu()
    {
        add_menu_page(
            'Persian Calendar Pro',
            'Persian Calendar Pro',
            'manage_options',
            'wpcp-settings',
            [$this, 'settings_page'],
            'dashicons-calendar-alt',
            58
        );
    }

    public function register_settings()
    {
        register_setting('wpcp_settings_group', 'wpcp_settings', [$this, 'sanitize']);
    }

    public function sanitize($input)
    {
        $defaults = self::default_settings();
        if (!empty($input['_import_json'])) {
            $decoded = json_decode(wp_unslash($input['_import_json']), true);
            if (is_array($decoded)) {
                if (!empty($decoded['settings']) && is_array($decoded['settings'])) {
                    $input = $decoded['settings'];
                } else {
                    $input = $decoded;
                }
            }
        }
        $out = wp_parse_args((array) $input, $defaults);

        $out['title'] = sanitize_text_field($out['title']);
        $out['subtitle'] = sanitize_text_field($out['subtitle']);
        $out['theme'] = sanitize_text_field($out['theme']);
        $out['dark_mode'] = !empty($out['dark_mode']) ? 1 : 0;
        $out['show_gregorian'] = !empty($out['show_gregorian']) ? 1 : 0;
        $out['show_week_number'] = !empty($out['show_week_number']) ? 1 : 0;
        $out['show_today_button'] = !empty($out['show_today_button']) ? 1 : 0;
        $out['week_start'] = in_array($out['week_start'], ['sat', 'sun'], true) ? $out['week_start'] : 'sat';
        $out['city'] = sanitize_text_field($out['city']);
        $out['widget_width'] = sanitize_text_field($out['widget_width']);
        $out['font_family'] = sanitize_text_field($out['font_family']);
        $out['custom_css'] = wp_kses_post($out['custom_css']);
        $out['radius'] = absint($out['radius']);
        $out['shadow'] = absint($out['shadow']);
        $out['enable_cache'] = !empty($out['enable_cache']) ? 1 : 0;
        $out['cache_ttl'] = max(30, absint($out['cache_ttl']));
        $out['custom_occasions_json'] = wp_kses_post($out['custom_occasions_json']);

        foreach ($out['colors'] as $key => $value) {
            $out['colors'][$key] = sanitize_hex_color($value) ?: $defaults['colors'][$key];
        }

        $events = [];
        if (!empty($out['custom_events']) && is_array($out['custom_events'])) {
            foreach ($out['custom_events'] as $event) {
                if (empty($event['date']) || empty($event['title'])) {
                    continue;
                }
                $events[] = [
                    'date' => sanitize_text_field($event['date']),
                    'title' => sanitize_text_field($event['title']),
                    'type' => sanitize_text_field($event['type'] ?? 'custom'),
                ];
            }
        }
        $out['custom_events'] = $events;

        return $out;
    }

    public function settings_page()
    {
        $settings = self::get_settings();
        $export = wp_json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        ?>
        <div class="wrap wpcp-admin-wrap">
            <h1>WP Persian Calendar Pro</h1>
            <p>تنظیمات حرفه‌ای تقویم، رویدادها، اوقات شرعی و سفارشی‌سازی کامل ظاهر.</p>

            <form method="post" action="options.php" id="wpcp-settings-form">
                <?php settings_fields('wpcp_settings_group'); ?>

                <div class="wpcp-tabs">
                    <button type="button" class="wpcp-tab active" data-tab="general">عمومی</button>
                    <button type="button" class="wpcp-tab" data-tab="theme">تم و استایل</button>
                    <button type="button" class="wpcp-tab" data-tab="events">رویدادها</button>
                    <button type="button" class="wpcp-tab" data-tab="integrations">ابزارک‌ها</button>
                    <button type="button" class="wpcp-tab" data-tab="import-export">درون‌ریزی/برون‌ریزی</button>
                </div>

                <div class="wpcp-panel active" data-panel="general">
                    <label>عنوان
                        <input type="text" name="wpcp_settings[title]" value="<?php echo esc_attr($settings['title']); ?>">
                    </label>
                    <label>زیرعنوان
                        <input type="text" name="wpcp_settings[subtitle]" value="<?php echo esc_attr($settings['subtitle']); ?>">
                    </label>
                    <label><input type="checkbox" name="wpcp_settings[show_gregorian]" value="1" <?php checked($settings['show_gregorian'], 1); ?>> نمایش تاریخ میلادی</label><br>
                    <label><input type="checkbox" name="wpcp_settings[show_week_number]" value="1" <?php checked($settings['show_week_number'], 1); ?>> نمایش شماره هفته</label><br>
                    <label><input type="checkbox" name="wpcp_settings[show_today_button]" value="1" <?php checked($settings['show_today_button'], 1); ?>> دکمه امروز</label><br>
                    <label>شروع هفته
                        <select name="wpcp_settings[week_start]">
                            <option value="sat" <?php selected($settings['week_start'], 'sat'); ?>>شنبه</option>
                            <option value="sun" <?php selected($settings['week_start'], 'sun'); ?>>یکشنبه</option>
                        </select>
                    </label><br>
                    <label>شهر اوقات شرعی
                        <input type="text" name="wpcp_settings[city]" value="<?php echo esc_attr($settings['city']); ?>" placeholder="tehran">
                    </label>
                </div>

                <div class="wpcp-panel" data-panel="theme">
                    <label>پریست تم
                        <select name="wpcp_settings[theme]">
                            <option value="material" <?php selected($settings['theme'], 'material'); ?>>Material</option>
                            <option value="minimal" <?php selected($settings['theme'], 'minimal'); ?>>Minimal</option>
                            <option value="dark" <?php selected($settings['theme'], 'dark'); ?>>Dark</option>
                        </select>
                    </label>
                    <label><input type="checkbox" name="wpcp_settings[dark_mode]" value="1" <?php checked($settings['dark_mode'], 1); ?>> فعال‌سازی تم تیره</label>
                    <div class="wpcp-color-grid">
                        <?php foreach ($settings['colors'] as $key => $value): ?>
                            <label><?php echo esc_html($key); ?>
                                <input type="color" name="wpcp_settings[colors][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>">
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <label>گردی گوشه
                        <input type="number" name="wpcp_settings[radius]" value="<?php echo esc_attr($settings['radius']); ?>" min="0" max="30">
                    </label>
                    <label>شدت سایه
                        <input type="number" name="wpcp_settings[shadow]" value="<?php echo esc_attr($settings['shadow']); ?>" min="0" max="100">
                    </label>
                    <label>فونت
                        <input type="text" name="wpcp_settings[font_family]" value="<?php echo esc_attr($settings['font_family']); ?>">
                    </label>
                    <label>CSS سفارشی
                        <textarea name="wpcp_settings[custom_css]" rows="6"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                    </label>
                </div>

                <div class="wpcp-panel" data-panel="events">
                    <p>رویدادهای سفارشی (Drag & Drop):</p>
                    <div id="wpcp-events-list">
                        <?php foreach ($settings['custom_events'] as $index => $event): ?>
                            <div class="wpcp-event-item">
                                <span class="dashicons dashicons-menu"></span>
                                <input type="text" name="wpcp_settings[custom_events][<?php echo esc_attr($index); ?>][date]" value="<?php echo esc_attr($event['date']); ?>" placeholder="YYYY-MM-DD">
                                <input type="text" name="wpcp_settings[custom_events][<?php echo esc_attr($index); ?>][title]" value="<?php echo esc_attr($event['title']); ?>" placeholder="عنوان">
                                <input type="text" name="wpcp_settings[custom_events][<?php echo esc_attr($index); ?>][type]" value="<?php echo esc_attr($event['type']); ?>" placeholder="نوع">
                                <button type="button" class="button-link-delete wpcp-remove-row">حذف</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button" id="wpcp-add-event">افزودن رویداد</button>

                    <label>مناسبت‌های سفارشی JSON
                        <textarea name="wpcp_settings[custom_occasions_json]" rows="8" placeholder='[{"date":"2026-03-21","title":"Nowruz"}]'><?php echo esc_textarea($settings['custom_occasions_json']); ?></textarea>
                    </label>
                </div>

                <div class="wpcp-panel" data-panel="integrations">
                    <p><code>[persian_calendar_pro]</code> برای نمایش در صفحات و برگه‌ها.</p>
                    <p>ابزارک کلاسیک وردپرس فعال است. ویجت المنتور در صورت فعال بودن Elementor ثبت می‌شود.</p>
                    <label>عرض ویجت
                        <input type="text" name="wpcp_settings[widget_width]" value="<?php echo esc_attr($settings['widget_width']); ?>">
                    </label>
                    <label><input type="checkbox" name="wpcp_settings[enable_cache]" value="1" <?php checked($settings['enable_cache'], 1); ?>> کش فعال</label>
                    <label>مدت کش (ثانیه)
                        <input type="number" name="wpcp_settings[cache_ttl]" value="<?php echo esc_attr($settings['cache_ttl']); ?>" min="30" step="30">
                    </label>
                </div>

                <div class="wpcp-panel" data-panel="import-export">
                    <p>JSON خروجی:</p>
                    <textarea readonly rows="10"><?php echo esc_textarea($export); ?></textarea>
                    <p>ورودی JSON با عنوان:</p>
                    <textarea name="wpcp_settings[_import_json]" rows="10" placeholder='{"title":"My preset","settings":{...}}'></textarea>
                    <p class="description">در صورت وجود فیلد بالا، در زمان ذخیره تلاش می‌شود تنظیمات از JSON خوانده شود.</p>
                </div>

                <?php submit_button('ذخیره تنظیمات'); ?>
            </form>

            <template id="wpcp-event-template">
                <div class="wpcp-event-item">
                    <span class="dashicons dashicons-menu"></span>
                    <input type="text" data-name="date" placeholder="YYYY-MM-DD">
                    <input type="text" data-name="title" placeholder="عنوان">
                    <input type="text" data-name="type" placeholder="نوع">
                    <button type="button" class="button-link-delete wpcp-remove-row">حذف</button>
                </div>
            </template>
        </div>
        <?php
    }
}
