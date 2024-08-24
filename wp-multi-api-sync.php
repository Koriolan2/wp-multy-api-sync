<?php
/**
 * Plugin Name: WP Multi API Sync
 * Description: Sync products from multiple APIs and store them in the database.
 * Version: 2.2
 * Author: Yuriy Kozmin aka Yuriy Knysh
 */

// Визначаємо константу шляху до плагіну
define('WP_MULTI_API_SYNC_PATH', plugin_dir_path(__FILE__));

// Підключаємо необхідні файли
require_once WP_MULTI_API_SYNC_PATH . 'includes/class-api-sync-post-type.php';
require_once WP_MULTI_API_SYNC_PATH . 'includes/class-api-sync-metabox.php';
require_once WP_MULTI_API_SYNC_PATH . 'includes/class-api-sync-database.php';
require_once WP_MULTI_API_SYNC_PATH . 'includes/class-api-sync-api-handler.php';
require_once WP_MULTI_API_SYNC_PATH . 'includes/class-api-sync-scheduler.php';
require_once WP_MULTI_API_SYNC_PATH . 'includes/class-api-sync-schedule-metabox.php';


// Реєструємо користувацький тип запису
function wp_multi_api_sync_register_post_type() {
    $post_type = new Api_Sync_Post_Type();
    $post_type->register_post_type();
}
add_action('init', 'wp_multi_api_sync_register_post_type');

// Підключаємо метабокси
function wp_multi_api_sync_register_metabox() {
    $metabox = new Api_Sync_Metabox();
    $metabox->register_metabox();
}
add_action('add_meta_boxes', 'wp_multi_api_sync_register_metabox');

// Обробка збереження метаданих і натискання кнопки "Отримати дані"
function wp_multi_api_sync_save_post($post_id) {
    $metabox = new Api_Sync_Metabox();
    $metabox->save_metabox($post_id);
}
add_action('save_post', 'wp_multi_api_sync_save_post');

// Підключаємо код для роботи з базою даних
function wp_multi_api_sync_fetch_data() {
    $database = new Api_Sync_Database();
    $database->fetch_data();
}
add_action('wp_ajax_fetch_api_data', 'wp_multi_api_sync_fetch_data');

// Виведення повідомлень на сторінці редагування запису
function wp_multi_api_sync_display_admin_notices() {
    settings_errors('api_sync_messages');
}
add_action('admin_notices', 'wp_multi_api_sync_display_admin_notices');

// Додаємо JavaScript для кнопки "Отримати дані"
function wp_multi_api_sync_enqueue_scripts($hook) {
    // Перевіряємо, чи ми на сторінці редагування кастомного типу запису "api_config"
    if ('post.php' !== $hook && 'post-new.php' !== $hook) {
        return;
    }

    global $post;
    if ($post->post_type !== 'api_config') {
        return;
    }

    // Додаємо таймстамп до JS, щоб уникнути кешування
    $version = filemtime(plugin_dir_path(__FILE__) . 'assets/js/api-sync.js');  // Використовуємо час останньої зміни файлу як версію

    wp_enqueue_script('api_sync_script', plugins_url('assets/js/api-sync.js', __FILE__), array('jquery'), $version, true);

    // Передаємо дані для AJAX
    wp_localize_script('api_sync_script', 'apiSyncData', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('fetch_api_data_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'wp_multi_api_sync_enqueue_scripts');


function wp_multi_api_sync_register_schedule_metabox() {
    $schedule_metabox = new Api_Sync_Schedule_Metabox();
    $schedule_metabox->register_metabox();
}
add_action('add_meta_boxes', 'wp_multi_api_sync_register_schedule_metabox');