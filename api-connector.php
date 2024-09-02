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

// Підключаємо необхідні класи
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

// Підключаємо стилі та скрипти для адміністративної панелі
function api_connector_admin_scripts($hook_suffix) {
    global $post_type;

    if ($post_type === 'api_connector') {
        // Підключення стилів
        $css_file = plugin_dir_path(__FILE__) . 'assets/admin-style.css';
        $css_url = plugin_dir_url(__FILE__) . 'assets/admin-style.css';
        $css_version = filemtime($css_file); // Отримуємо час останньої зміни файлу

        wp_enqueue_style(
            'api-connector-admin-style',
            $css_url,
            array(),
            $css_version // Додаємо версію на основі часу зміни файлу
        );

        // Підключення скриптів
        $js_file = plugin_dir_path(__FILE__) . 'assets/admin.js';
        $js_url = plugin_dir_url(__FILE__) . 'assets/admin.js';
        $js_version = filemtime($js_file); // Отримуємо час останньої зміни файлу

        wp_enqueue_script(
            'api-connector-admin-js',
            $js_url,
            array('jquery'),
            $js_version, // Додаємо версію на основі часу зміни файлу
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'api_connector_admin_scripts');

// AJAX обробник для збереження вибору API
function save_api_selection_ajax() {
    if (!isset($_POST['post_id']) || !isset($_POST['api_selection'])) {
        wp_send_json_error('Invalid data');
        return;
    }

    $post_id = intval($_POST['post_id']);
    $api_selection = sanitize_text_field($_POST['api_selection']);

    // Оновлюємо мета-дані для вибраного API
    update_post_meta($post_id, '_selected_api', $api_selection);

    // Перезавантажуємо мета-бокси "API Settings" та "Schedule Settings"
    ob_start();
    $metabox = new API_Settings_Metabox();
    $metabox->render_api_settings_metabox(get_post($post_id));
    $api_settings_content = ob_get_clean();

    ob_start();
    $schedule_metabox = new Schedule_Metabox();
    $schedule_metabox->render_schedule_metabox(get_post($post_id));
    $schedule_settings_content = ob_get_clean();

    wp_send_json_success([
        'api_settings' => $api_settings_content,
        'schedule_settings' => $schedule_settings_content
    ]);
}
add_action('wp_ajax_save_api_selection', 'save_api_selection_ajax');
