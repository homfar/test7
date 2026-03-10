<?php

if (!defined('ABSPATH')) {
    exit;
}

class WPCP_Widget extends WP_Widget
{
    public static function instance()
    {
        add_action('widgets_init', function () {
            register_widget(self::class);
        });

        return new self();
    }

    public function __construct()
    {
        parent::__construct('wpcp_widget', 'Persian Calendar Pro', ['description' => 'تقویم شمسی حرفه‌ای']);
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . esc_html($instance['title']) . $args['after_title'];
        }
        echo WPCP_Calendar::render_shortcode();
        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = $instance['title'] ?? 'تقویم';
        ?>
        <p>
            <label>عنوان</label>
            <input class="widefat" name="<?php echo esc_attr($this->get_field_name('title')); ?>" value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        return ['title' => sanitize_text_field($new_instance['title'] ?? '')];
    }
}
