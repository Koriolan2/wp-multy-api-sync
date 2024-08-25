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
        // Кнопка для отримання даних
        echo '<button type="button" id="fetch_data_button" class="button button-primary" data-post-id="' . $post->ID . '" style="width: 100%;">Отримати дані</button>';
    }
}
