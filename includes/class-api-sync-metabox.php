<?php
class Api_Sync_Metabox {

    // Реєстрація метабоксу
    public function register_metabox() {
        add_meta_box(
            'api_sync_metabox',
            'Деталі API',
            array($this, 'render_metabox'),
            'api_config',
            'normal',
            'default'
        );
    }

    // Виведення полів у метабоксі
    public function render_metabox($post) {
        // Генеруємо nonce для перевірки при збереженні
        wp_nonce_field('save_api_sync_metabox', 'api_sync_metabox_nonce');

        // Отримуємо значення з метаданих
        $api_url = get_post_meta($post->ID, '_api_url', true);
        $api_token = get_post_meta($post->ID, '_api_token', true);
        $table_suffix = get_post_meta($post->ID, '_table_suffix', true);
        $sync_schedule = get_post_meta($post->ID, '_sync_schedule', true); // Отримуємо графік синхронізації

        // Стилі для метабоксу
        echo '<div style="display: flex; flex-direction: column; gap: 15px; padding: 10px;">';

        // Поле для URL API
        echo '<div style="display: flex; flex-direction: column;">';
        echo '<label for="api_url" style="font-weight: bold;">URL API:</label>';
        echo '<input type="text" id="api_url" name="api_url" value="' . esc_attr($api_url) . '" size="50" style="padding: 8px; width: 100%;" />';
        echo '</div>';

        // Поле для Access Token або ключа безпеки
        echo '<div style="display: flex; flex-direction: column;">';
        echo '<label for="api_token" style="font-weight: bold;">Access Token або ключ безпеки:</label>';
        echo '<input type="text" id="api_token" name="api_token" value="' . esc_attr($api_token) . '" size="50" style="padding: 8px; width: 100%;" />';
        echo '</div>';

        // Поле для суфіксу таблиці
        echo '<div style="display: flex; flex-direction: column;">';
        echo '<label for="table_suffix" style="font-weight: bold;">Суфікс таблиці БД:</label>';
        echo '<input type="text" id="table_suffix" name="table_suffix" value="' . esc_attr($table_suffix) . '" size="25" style="padding: 8px; width: 100%;" />';
        echo '</div>';

        // Поле для вибору графіку синхронізації
        echo '<div style="display: flex; flex-direction: column;">';
        echo '<label for="sync_schedule" style="font-weight: bold;">Графік отримання даних:</label>';
        echo '<select id="sync_schedule" name="sync_schedule" style="padding: 8px; width: 100%;">';
        echo '<option value="5_min" ' . selected($sync_schedule, '5_min', false) . '>Кожні 5 хвилин</option>';
        echo '<option value="hourly" ' . selected($sync_schedule, 'hourly', false) . '>Щогодини</option>';
        echo '<option value="daily" ' . selected($sync_schedule, 'daily', false) . '>Раз на добу</option>';
        echo '</select>';
        echo '</div>';

        echo '</div>';

        // Кнопка для отримання даних негайно
        echo '<button type="button" id="fetch_data_button" class="button button-primary" data-post-id="' . $post->ID . '" style="margin-top: 20px;">Отримати дані</button>';
    }

    // Збереження метаданих при збереженні поста
    public function save_metabox($post_id) {
        // Перевірка, чи існує nonce і чи правильний він
        if (!isset($_POST['api_sync_metabox_nonce']) || !wp_verify_nonce($_POST['api_sync_metabox_nonce'], 'save_api_sync_metabox')) {
            return;
        }

        // Перевірка, чи це не автозбереження
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Перевірка прав користувача
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Збереження URL API
        if (isset($_POST['api_url'])) {
            update_post_meta($post_id, '_api_url', esc_url_raw($_POST['api_url']));
        }

        // Збереження токена
        if (isset($_POST['api_token'])) {
            update_post_meta($post_id, '_api_token', sanitize_textarea_field($_POST['api_token']));
        }

        // Збереження суфіксу таблиці
        if (isset($_POST['table_suffix'])) {
            update_post_meta($post_id, '_table_suffix', sanitize_text_field($_POST['table_suffix']));
        }

        // Збереження графіку синхронізації
        if (isset($_POST['sync_schedule'])) {
            update_post_meta($post_id, '_sync_schedule', sanitize_text_field($_POST['sync_schedule']));
            // Запускаємо завдання WP-Cron
            $scheduler = new Api_Sync_Scheduler();
            $scheduler->schedule_sync($post_id, $_POST['sync_schedule']);
        }
    }
}
