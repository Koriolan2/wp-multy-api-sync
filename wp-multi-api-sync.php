<?php
/**
 * Plugin Name: WP Multi API Sync
 * Description: Sync products from multiple APIs and store them in the database.
 * Version: 2.1
 * Author: Yuriy Kozmin aka Yuriy Knysh
 */

// Визначаємо константу шляху до плагіну
define('WP_MULTI_API_SYNC_PATH', plugin_dir_path(__FILE__));

// Підключаємо необхідні файли
require_once WP_MULTI_API_SYNC_PATH . 'includes/class-api-sync-post-type.php';
require_once WP_MULTI_API_SYNC_PATH . 'includes/class-api-sync-metabox.php';
require_once WP_MULTI_API_SYNC_PATH . 'includes/class-api-sync-database.php';
require_once WP_MULTI_API_SYNC_PATH . 'includes/class-api-sync-fetch-metabox.php'; // Новий клас для метабоксу "Отримати дані"

// Реєструємо користувацький тип запису та метабокси
function wp_multi_api_sync_init() {
    // Реєстрація користувацького типу запису
    $post_type = new Api_Sync_Post_Type();
    $post_type->register_post_type();
    
    // Реєстрація метабоксів
    add_action('add_meta_boxes', function() {
        // Основний метабокс
        $metabox = new Api_Sync_Metabox();
        $metabox->register_metabox();

        // Метабокс для отримання даних
        $fetch_metabox = new Api_Sync_Fetch_Metabox();
        $fetch_metabox->register();
    });
}
add_action('init', 'wp_multi_api_sync_init');

// Збереження метаданих при збереженні поста
function wp_multi_api_sync_save_post($post_id) {
    // Збереження даних з основного метабоксу
    $metabox = new Api_Sync_Metabox();
    $metabox->save_metabox($post_id);

    // Збереження вибору способу отримання даних
    $fetch_metabox = new Api_Sync_Fetch_Metabox();
    $fetch_metabox->save_metabox($post_id);
}

add_action('save_post', 'wp_multi_api_sync_save_post');

// Обробка запиту для отримання даних через AJAX
function wp_multi_api_sync_fetch_data() {
    // Підключення класу для роботи з базою даних
    $database = new Api_Sync_Database();
    $database->fetch_data();
}
add_action('wp_ajax_fetch_api_data', 'wp_multi_api_sync_fetch_data');

// Виведення повідомлень на сторінці редагування запису
function wp_multi_api_sync_display_admin_notices() {
    settings_errors('api_sync_messages');
}
add_action('admin_notices', 'wp_multi_api_sync_display_admin_notices');

// Підключаємо JavaScript для метабоксу з уникненням кешування
function wp_multi_api_sync_enqueue_scripts() {
    // Додаємо timestamp до JS файлу
    $version = filemtime(plugin_dir_path(__FILE__) . 'assets/js/api-sync-metabox.js');

    wp_enqueue_script('api_sync_metabox_script', plugins_url('assets/js/api-sync-metabox.js', __FILE__), array('jquery'), $version, true);
    
    // Локалізуємо скрипт, щоб передати AJAX URL і nonce
    wp_localize_script('api_sync_metabox_script', 'apiSyncData', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('fetch_api_data_nonce')
    ));
}


add_action('admin_enqueue_scripts', 'wp_multi_api_sync_enqueue_scripts');
