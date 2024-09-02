<?php
/**
 * Клас для створення та обробки мета-боксу для налаштувань конкретного API.
 */

class API_Settings_Metabox {

    public function __construct() {
        // Додаємо мета-бокс для налаштувань API
        add_action('add_meta_boxes', array($this, 'add_api_settings_metabox'));

        // Зберігаємо налаштування API при збереженні запису
        add_action('save_post', array($this, 'save_api_settings'));
    }

    public function add_api_settings_metabox() {
        add_meta_box(
            'api_settings',
            __('API Settings', 'api-connector'),
            array($this, 'render_api_settings_metabox'),
            'api_connector',
            'advanced',
            'high'
        );
    }

    public function render_api_settings_metabox($post) {
        // Отримуємо обране API
        $selected_api = get_post_meta($post->ID, '_selected_api', true);
        if (!$selected_api) {
            echo '<div class="select-api-notice">' . __('Please select an API first.', 'api-connector') . '</div>';
            return;
        }
    
        echo '<div class="api-connector-metabox">';
    
        $fields_exist = false;
    
        foreach (glob(plugin_dir_path(__FILE__) . '../api/class-*-api.php') as $filename) {
            $class_name = str_replace('class-', '', basename($filename, '.php'));
            $class_name = str_replace('-', '_', $class_name);
            $class_name = ucfirst($class_name);
    
            require_once $filename;
    
            if (class_exists($class_name)) {
                $api_instance = new $class_name();
                if ($api_instance->api_name === $selected_api && method_exists($api_instance, 'get_settings_fields')) {
                    $fields = $api_instance->get_settings_fields();
                    if (!empty($fields)) {
                        $fields_exist = true;
                        foreach ($fields as $field_id => $field_label) {
                            $field_value = get_post_meta($post->ID, "_api_{$field_id}", true);
                            echo "<label for='api_$field_id'>$field_label:</label>";
                            echo "<input type='text' name='api_$field_id' id='api_$field_id' value='" . esc_attr($field_value) . "' /><br/>";
                        }
                    }
                }
            }
        }
    
        if (!$fields_exist) {
            echo '<div class="notice">' . __('The API does not contain fields to configure', 'api-connector') . '</div>';
        }
    
        echo '</div>'; // Закриваємо div з класом "api-connector-metabox"
    }

    public function save_api_settings($post_id) {
        $selected_api = get_post_meta($post_id, '_selected_api', true);

        if ($selected_api) {
            foreach (glob(plugin_dir_path(__FILE__) . '../api/class-*-api.php') as $filename) {
                $class_name = str_replace('class-', '', basename($filename, '.php'));
                $class_name = str_replace('-', '_', $class_name);
                $class_name = ucfirst($class_name);

                require_once $filename;

                if (class_exists($class_name)) {
                    $api_instance = new $class_name();
                    if ($api_instance->api_name === $selected_api && method_exists($api_instance, 'get_settings_fields')) {
                        $fields = $api_instance->get_settings_fields();
                        foreach ($fields as $field_id => $field_label) {
                            if (isset($_POST["api_$field_id"])) {
                                update_post_meta($post_id, "_api_$field_id", sanitize_text_field($_POST["api_$field_id"]));
                            }
                        }
                    }
                }
            }
        }
    }
}

