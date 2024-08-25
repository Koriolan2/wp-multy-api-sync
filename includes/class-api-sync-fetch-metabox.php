<?php
class Api_Sync_Fetch_Metabox {

    // Реєстрація метабоксу
    public function register() {
        add_meta_box(
            'api_sync_fetch_metabox',
            'Отримання даних з API',
            array($this, 'render_metabox'),
            'api_config',
            'side', // Розташування метабоксу (у бічній колонці)
            'default'
        );
    }

    // Відображення метабоксу
    public function render_metabox($post) {
        // Отримуємо збережені значення метаданих
        $fetch_method = get_post_meta($post->ID, '_api_fetch_method', true) ?: 'default';
        $schedule = get_post_meta($post->ID, '_api_fetch_schedule', true) ?: 'default';
        $last_fetched = get_post_meta($post->ID, '_last_fetched', true); // Останнє звернення до API

        // Поле вибору способу отримання даних
        echo '<label for="api_fetch_method" style="font-weight: bold;">Спосіб отримання даних:</label>';
        echo '<select name="api_fetch_method" id="api_fetch_method" style="width: 100%; margin-bottom: 10px;">';
        echo '<option value="default"' . selected($fetch_method, 'default', false) . '>Оберіть спосіб отримання</option>';
        echo '<option value="manual"' . selected($fetch_method, 'manual', false) . '>Отримати вручну</option>';
        echo '<option value="schedule"' . selected($fetch_method, 'schedule', false) . '>Отримати за графіком</option>';
        echo '</select>';

        // Кнопка для отримання даних, прихована за замовчуванням
        echo '<button type="button" id="fetch_data_button" class="button button-primary" data-post-id="' . $post->ID . '" style="width: 100%; margin-bottom: 10px; display: none;">Отримати дані</button>';

        // Випадаючий список для графіка, прихований за замовчуванням
        echo '<label for="api_fetch_schedule" style="font-weight: bold; display: none;" id="fetch_schedule_label">Графік отримання даних:</label>';
        echo '<select name="api_fetch_schedule" id="api_fetch_schedule" style="width: 100%; margin-bottom: 10px; display: none;">';
        echo '<option value="default"' . selected($schedule, 'default', false) . '>Оберіть графік</option>';
        echo '<option value="5min"' . selected($schedule, '5min', false) . '>Раз на 5 хвилин</option>';
        echo '<option value="hourly"' . selected($schedule, 'hourly', false) . '>Щогодини</option>';
        echo '<option value="daily"' . selected($schedule, 'daily', false) . '>Щодня</option>';
        echo '</select>';

        // Інформаційне поле для дати останнього отримання даних
        if ($last_fetched) {
            echo '<p id="last_fetched_info" style="font-weight: bold;">Останнє отримання даних: ' . esc_html($last_fetched) . '</p>';
        } else {
            echo '<p id="last_fetched_info" style="display: none;"></p>';
        }
    }

    // Збереження метаданих при збереженні поста
    public function save_metabox($post_id) {
        // Перевірка nonce
        if (!isset($_POST['api_sync_metabox_nonce']) || !wp_verify_nonce($_POST['api_sync_metabox_nonce'], 'save_api_sync_metabox')) {
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

        // Збереження способу отримання даних
        if (isset($_POST['api_fetch_method'])) {
            $fetch_method = sanitize_text_field($_POST['api_fetch_method']);
            update_post_meta($post_id, '_api_fetch_method', $fetch_method);

            // Якщо обрано "Отримати вручну", видаляємо метадані про графік
            if ($fetch_method === 'manual') {
                delete_post_meta($post_id, '_api_fetch_schedule');
            }
        }

        // Збереження графіка отримання даних, якщо обрано "Отримати за графіком"
        if (isset($_POST['api_fetch_schedule']) && $_POST['api_fetch_method'] === 'schedule') {
            update_post_meta($post_id, '_api_fetch_schedule', sanitize_text_field($_POST['api_fetch_schedule']));
        }
    }
}
