<?php
/*
Plugin Name: API Connector
Plugin URI: https://yourwebsite.com/api-connector
Description: Плагін дозволяє налаштовувати та підключати різні API, отримувати дані вручну або за розкладом та зберігати їх у базі даних WordPress.
Version: 3.0.0
Author: Yuriy Kozmin aka Yuriy Knysh
Author URI: https://www.linkedin.com/in/kozminjury/
Text Domain: api-connector
Domain Path: /languages
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Підключаємо класи
require_once plugin_dir_path(__FILE__) . 'includes/class-api-custom-post-type.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-plugin-logger.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-api-metabox.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-api-settings-metabox.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-schedule-metabox.php';

// Ініціалізуємо класи
new API_Custom_Post_Type();
new API_Metabox();
new API_Settings_Metabox();
new Schedule_Metabox();

// Автоматичне підключення класів API
function api_connector_autoload_api_classes() {
    $logger = new Plugin_Logger();
    $api_classes_dir = plugin_dir_path(__FILE__) . 'api/';
    
    foreach (glob($api_classes_dir . '*.php') as $filename) {
        require_once $filename;
        $logger->log("Підключено файл API: $filename");
    }
}
api_connector_autoload_api_classes();

// Підключаємо стилі для адміністративної панелі
function api_connector_admin_styles($hook_suffix) {
    global $post_type;

    if ($post_type === 'api_connector') {
        $css_file = plugin_dir_path(__FILE__) . 'assets/admin-style.css';
        $css_url = plugin_dir_url(__FILE__) . 'assets/admin-style.css';
        $version = filemtime($css_file); // Отримуємо час останньої зміни файлу

        wp_enqueue_style(
            'api-connector-admin-style',
            $css_url,
            array(),
            $version // Додаємо версію на основі часу зміни файлу
        );
    }
}
add_action('admin_enqueue_scripts', 'api_connector_admin_styles');

