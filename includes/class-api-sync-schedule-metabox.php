<?php

class Api_Sync_Schedule_Metabox {

    // Реєстрація метабоксу
    public function register_metabox() {
        add_meta_box(
            'api_sync_schedule_metabox',
            'Налаштування графіку синхронізації',
            array($this, 'render_metabox'),
            'api_config',
            'side',
            'high'
        );
    }

    // Виведення полів у метабоксі
    public function render_metabox($post) {
        // Генеруємо nonce для перевірки при збереженні
        wp_nonce_field('save_api_sync_schedule_metabox', 'api_sync_schedule_metabox_nonce');

        // Отримуємо значення з мета-даних
        $sync_schedule = get_post_meta($post->ID, '_sync_schedule', true);

        // Якщо значення не знайдено, задаємо значення за замовчуванням
        if (!$sync_schedule) {
            $sync_schedule = ''; // Значення за замовчуванням
        }

        // Поле для вибору графіку синхронізації
        echo '<div style="display: flex; flex-direction: column; gap: 15px; padding: 10px;">';
        echo '<label for="sync_schedule" style="font-weight: bold;">Графік отримання даних:</label>';
        echo '<select id="sync_schedule" name="sync_schedule" style="padding: 8px; width: 100%;">';
        echo '<option value="" ' . selected($sync_schedule, '', false) . '>Оберіть графік</option>';  // Значення за замовчуванням
        echo '<option value="manual" ' . selected($sync_schedule, 'manual', false) . '>Отримати вручну</option>';
        echo '<option value="5_min" ' . selected($sync_schedule, '5_min', false) . '>Кожні 5 хвилин</option>';
        echo '<option value="hourly" ' . selected($sync_schedule, 'hourly', false) . '>Щогодини</option>';
        echo '<option value="daily" ' . selected($sync_schedule, 'daily', false) . '>Раз на добу</option>';
        echo '</select>';
        echo '</div>';
    }

    // Збереження мета-даних при збереженні поста
    public function save_metabox($post_id) {
        // Перевірка nonce
        if (!isset($_POST['api_sync_schedule_metabox_nonce']) || !wp_verify_nonce($_POST['api_sync_schedule_metabox_nonce'], 'save_api_sync_schedule_metabox')) {
            return;
        }

        // Перевірка на автозбереження
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Перевірка прав користувача
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Валідація: якщо графік не обрано
        if (!isset($_POST['sync_schedule']) || $_POST['sync_schedule'] === '') {
            return;
        }

        // Збереження графіку синхронізації
        $schedule = sanitize_text_field($_POST['sync_schedule']);

        // Оновлюємо мета-дані
        update_post_meta($post_id, '_sync_schedule', $schedule);

        // Викликаємо планувальник для синхронізації
        $scheduler = new Api_Sync_Scheduler();
        $scheduler->schedule_sync($post_id, $schedule);
    }

    // Логування повідомлень (якщо потрібно)
    private function write_log($message) {
        $log_file = plugin_dir_path(__FILE__) . '../logs.txt';
        $current_time = date("Y-m-d H:i:s");
        $formatted_message = "[" . $current_time . "] " . $message . "\n";
        file_put_contents($log_file, $formatted_message, FILE_APPEND);
    }
}
