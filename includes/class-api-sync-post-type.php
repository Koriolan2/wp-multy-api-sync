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

    // Додаємо нові колонки до таблиці API Конфігурацій
    public function add_custom_columns($columns) {
        // Додаємо нові колонки після стандартних
        $columns['fetch_method'] = 'Спосіб отримання';
        $columns['last_fetched'] = 'Останнє підключення';

        return $columns;
    }

    // Заповнюємо колонки значеннями
    public function custom_column_content($column_name, $post_id) {
        if ($column_name === 'fetch_method') {
            // Отримуємо спосіб отримання даних з метаданих
            $fetch_method = get_post_meta($post_id, '_api_fetch_method', true);
            switch ($fetch_method) {
                case 'manual':
                    echo 'Отримати вручну';
                    break;
                case 'schedule':
                    echo 'Отримати за графіком';
                    break;
                default:
                    echo 'Не вказано';
            }
        }

        if ($column_name === 'last_fetched') {
            // Отримуємо дату останнього підключення до API
            $last_fetched = get_post_meta($post_id, '_last_fetched', true);
            if ($last_fetched) {
                echo esc_html($last_fetched);
            } else {
                echo 'Немає даних';
            }
        }
    }

    // Робимо колонки доступними для сортування
    public function make_columns_sortable($columns) {
        $columns['fetch_method'] = 'fetch_method';
        $columns['last_fetched'] = 'last_fetched';
        return $columns;
    }

    // Реєстрація колонок, їх заповнення і сортування
    public function register_columns() {
        add_filter('manage_api_config_posts_columns', array($this, 'add_custom_columns'));
        add_action('manage_api_config_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
        add_filter('manage_edit-api_config_sortable_columns', array($this, 'make_columns_sortable'));
    }

    // Ініціалізація
    public function init() {
        $this->register_post_type();
        $this->register_columns();
    }
}

// Ініціалізація класу при виклику init
$api_sync_post_type = new Api_Sync_Post_Type();
add_action('init', array($api_sync_post_type, 'init'));
