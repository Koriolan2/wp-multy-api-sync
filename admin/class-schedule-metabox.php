<?php
/**
 * Клас для створення та обробки мета-боксу для налаштувань графіку отримання даних з API.
 */

class Schedule_Metabox {

    public function __construct() {
        // Додаємо мета-бокс для налаштувань графіку
        add_action('add_meta_boxes', array($this, 'add_schedule_metabox'));
        // Зберігаємо налаштування графіку при збереженні запису
        add_action('save_post', array($this, 'save_schedule_settings'));
    }

    public function add_schedule_metabox() {
        add_meta_box(
            'schedule_settings',
            __('Schedule Settings', 'api-connector'),
            array($this, 'render_schedule_metabox'),
            'api_connector',
            'side',
            'default'
        );
    }

    public function render_schedule_metabox($post) {
        // Отримуємо обране API
        $selected_api = get_post_meta($post->ID, '_selected_api', true);
        if (!$selected_api) {
            echo '<p>' . __('Please select an API to configure the schedule settings.', 'api-connector') . '</p>';
            return;
        }

        // Завантажуємо відповідний клас API та перевіряємо, чи підтримує він налаштування графіку
        foreach (glob(plugin_dir_path(__FILE__) . '../api/class-*-api.php') as $filename) {
            $class_name = str_replace('class-', '', basename($filename, '.php'));
            $class_name = str_replace('-', '_', $class_name);
            $class_name = ucfirst($class_name);

            require_once $filename;

            if (class_exists($class_name)) {
                $api_instance = new $class_name();
                if ($api_instance->api_name === $selected_api) {
                    if (!$api_instance->has_schedule_settings) {
                        echo '<p>' . __('This API does not support schedule settings.', 'api-connector') . '</p>';
                        return;
                    }
                }
            }
        }

        // Відображаємо мета-бокс, якщо API підтримує налаштування графіку
        ?>
        <div class="api-connector-metabox">
            <label for="schedule_interval"><?php _e('Interval (in hours):', 'api-connector'); ?></label>
            <input type="number" name="schedule_interval" id="schedule_interval" value="<?php echo esc_attr(get_post_meta($post->ID, '_schedule_interval', true)); ?>" min="1" /><br/>

            <label for="schedule_time"><?php _e('Start Time:', 'api-connector'); ?></label>
            <input type="time" name="schedule_time" id="schedule_time" value="<?php echo esc_attr(get_post_meta($post->ID, '_schedule_time', true)); ?>" />
        </div>
        <?php
    }

    public function save_schedule_settings($post_id) {
        if (isset($_POST['schedule_interval'])) {
            update_post_meta($post_id, '_schedule_interval', sanitize_text_field($_POST['schedule_interval']));
        }
        if (isset($_POST['schedule_time'])) {
            update_post_meta($post_id, '_schedule_time', sanitize_text_field($_POST['schedule_time']));
        }
    }
}
