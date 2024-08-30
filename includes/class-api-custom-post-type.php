<?php
/**
 * Клас для реєстрації користувацького типу запису для зберігання даних підключених API.
 */

class API_Custom_Post_Type {

    public function __construct() {
        // Викликаємо метод реєстрації типу запису при ініціалізації
        add_action('init', array($this, 'register_custom_post_type'));

        // Видаляємо непотрібні мета-бокси на сторінці редагування запису
        add_action('add_meta_boxes', array($this, 'remove_unwanted_metaboxes'), 99);
    }

    /**
     * Реєстрація користувацького типу запису 'api_connector'.
     */
    public function register_custom_post_type() {
        $labels = array(
            'name'               => __('API Connectors', 'api-connector'),
            'singular_name'      => __('API Connector', 'api-connector'),
            'menu_name'          => __('API Connectors', 'api-connector'),
            'name_admin_bar'     => __('API Connector', 'api-connector'),
            'add_new'            => __('Add New', 'api-connector'),
            'add_new_item'       => __('Add New API Connector', 'api-connector'),
            'new_item'           => __('New API Connector', 'api-connector'),
            'edit_item'          => __('Edit API Connector', 'api-connector'),
            'view_item'          => __('View API Connector', 'api-connector'),
            'all_items'          => __('All API Connectors', 'api-connector'),
            'search_items'       => __('Search API Connectors', 'api-connector'),
            'parent_item_colon'  => __('Parent API Connectors:', 'api-connector'),
            'not_found'          => __('No API Connectors found.', 'api-connector'),
            'not_found_in_trash' => __('No API Connectors found in Trash.', 'api-connector')
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'api-connector'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title'),
        );

        register_post_type('api_connector', $args);
    }

    /**
     * Видалення непотрібних мета-боксів.
     */
    public function remove_unwanted_metaboxes() {
        global $post_type;

        if ($post_type === 'api_connector') {
            // Видаляємо всі мета-бокси, окрім тих, що створені вашим плагіном
            // remove_meta_box('submitdiv', 'api_connector', 'core');  // Стандартний мета-бокс для збереження
            remove_meta_box('slugdiv', 'api_connector', 'normal'); // Мета-бокс для редагування слагу
            remove_meta_box('postcustom', 'api_connector', 'normal'); // Мета-бокс для користувацьких полів
            remove_meta_box('wpseo_meta', 'api_connector', 'normal'); // Yoast SEO
            remove_meta_box('aam-access-manager', 'api_connector', 'normal'); // Access manager
            remove_meta_box('qetl_meta_box', 'api_connector', 'normal'); 
            remove_meta_box('force_refresh_specific_page_refresh', 'api_connector', 'normal'); 
            remove_meta_box('customsidebars-mb', 'api_connector', 'normal'); 

            // Додайте сюди видалення інших мета-боксів, які вам не потрібні
            // Приклад:
            // remove_meta_box('authordiv', 'api_connector', 'normal'); // Мета-бокс для автора
        }
    }
}
