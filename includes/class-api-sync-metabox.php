<?php
class Api_Sync_Metabox {

    // Реєстрація метабоксів
    public function register_metabox() {
        // Основний метабокс з полями для API
        add_meta_box(
            'api_sync_metabox',
            'Деталі API',
            array($this, 'render_api_details_metabox'),
            'api_config',
            'normal',
            'default'
        );

        // Додаємо новий метабокс для кнопки "Отримати дані"
        add_meta_box(
            'api_sync_fetch_metabox',
            'Отримання даних з API',
            array($this, 'render_fetch_button_metabox'),
            'api_config',
            'side', // Розташування метабоксу (у бічній колонці)
            'default'
        );
    }

    // Виведення полів у основному метабоксі
    public function render_api_details_metabox($post) {
        wp_nonce_field('save_api_sync_metabox', 'api_sync_metabox_nonce');

        // Отримуємо значення з метаданих
        $api_url = get_post_meta($post->ID, '_api_url', true);
        $api_token = get_post_meta($post->ID, '_api_token', true);
        $table_suffix = get_post_meta($post->ID, '_table_suffix', true);

        // Поля метабоксу для API деталей
        echo '<div style="display: flex; flex-direction: column; gap: 10px;">';

        // Поле для URL API
        echo '<label for="api_url" style="font-weight: bold;">URL API:</label>';
        echo '<input type="text" id="api_url" name="api_url" value="' . esc_attr($api_url) . '" size="50" />';

        // Поле для Access Token або ключа безпеки
        echo '<label for="api_token" style="font-weight: bold;">Access Token або ключ безпеки:</label>';
        echo '<input type="text" id="api_token" name="api_token" value="' . esc_attr($api_token) . '" size="50" />';

        // Поле для суфіксу таблиці
        echo '<label for="table_suffix" style="font-weight: bold;">Суфікс таблиці БД:</label>';
        echo '<input type="text" id="table_suffix" name="table_suffix" value="' . esc_attr($table_suffix) . '" size="25" />';

        echo '</div>';
    }

    // Виведення кнопки в новому метабоксі
    public function render_fetch_button_metabox($post) {
        // Кнопка для отримання даних
        echo '<button type="button" id="fetch_data_button" class="button button-primary" data-post-id="' . $post->ID . '" style="width: 100%;">Отримати дані</button>';
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

        // Збереження URL API з використанням esc_url_raw
        if (isset($_POST['api_url'])) {
            update_post_meta($post_id, '_api_url', esc_url_raw($_POST['api_url']));
        }

        // Збереження токена без sanitize_text_field, оскільки токен може містити спеціальні символи
        if (isset($_POST['api_token'])) {
            update_post_meta($post_id, '_api_token', sanitize_textarea_field($_POST['api_token']));
        }

        // Збереження суфіксу таблиці
        if (isset($_POST['table_suffix'])) {
            update_post_meta($post_id, '_table_suffix', sanitize_text_field($_POST['table_suffix']));
        }
    }
}
