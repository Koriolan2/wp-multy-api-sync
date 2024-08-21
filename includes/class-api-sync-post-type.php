<?php
class Api_Sync_Post_Type {

    // Реєструємо користувацький тип запису для API конфігурацій
    public function register_post_type() {
        $labels = array(
            'name' => 'API Конфігурації',
            'singular_name' => 'API Конфігурація',
            'menu_name' => 'API Конфігурації',
            'name_admin_bar' => 'API Конфігурація',
            'add_new' => 'Додати нову',
            'add_new_item' => 'Додати нову API Конфігурацію',
            'edit_item' => 'Редагувати API Конфігурацію',
            'new_item' => 'Нова API Конфігурація',
            'view_item' => 'Переглянути API Конфігурацію',
            'all_items' => 'Усі API Конфігурації',
            'search_items' => 'Пошук API Конфігурацій',
            'not_found' => 'Не знайдено API Конфігурацій',
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'supports' => array('title'),
            'show_in_rest' => true,
        );

        register_post_type('api_config', $args);
    }
}
