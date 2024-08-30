<?php
/**
 * Клас для створення та обробки мета-боксу для вибору API.
 */

class API_Metabox {

    public function __construct() {
        // Додаємо мета-бокс для вибору API з високим пріоритетом (тобто на початку)
        add_action('add_meta_boxes', array($this, 'add_api_selection_metabox'), 10);

        // Зберігаємо вибір API при збереженні запису
        add_action('save_post', array($this, 'save_api_selection'));
    }

    public function add_api_selection_metabox() {
        add_meta_box(
            'api_selection',
            __('Select API', 'api-connector'),
            array($this, 'render_api_selection_metabox'),
            'api_connector',
            'side',
            'high' // Встановлюємо високий пріоритет
        );
    }

    public function render_api_selection_metabox($post) {
        $logger = new Plugin_Logger();
        $selected_api = get_post_meta($post->ID, '_selected_api', true);
    
        echo '<div class="api-connector-metabox">';
        ?>
        <label for="api_selection_field"><?php _e('Choose an API:', 'api-connector'); ?></label>
        <select name="api_selection_field" id="api_selection_field" onchange="jQuery('#post').submit();">
            <option value=""><?php _e('Select an API', 'api-connector'); ?></option>
            <?php
            foreach (glob(plugin_dir_path(__FILE__) . '../api/class-*-api.php') as $filename) {
                $class_name = str_replace('class-', '', basename($filename, '.php'));
                $class_name = str_replace('-', '_', $class_name);
                $class_name = ucfirst($class_name);
    
                require_once $filename;
    
                if (class_exists($class_name)) {
                    $api_instance = new $class_name();
                    if (property_exists($api_instance, 'api_name')) {
                        $api_name = $api_instance->api_name;
                        ?>
                        <option value="<?php echo esc_attr($api_name); ?>" <?php selected($selected_api, $api_name); ?>>
                            <?php echo esc_html($api_name); ?>
                        </option>
                        <?php
                    } else {
                        $logger->log("Клас $class_name не містить властивості 'api_name'.");
                    }
                } else {
                    $logger->log("Клас $class_name не знайдено у файлі $filename.");
                }
            }
            ?>
        </select>
        <?php
        echo '</div>'; // Закриваємо div з класом "api-connector-metabox"
    }
    

    public function save_api_selection($post_id) {
        if (isset($_POST['api_selection_field'])) {
            update_post_meta($post_id, '_selected_api', sanitize_text_field($_POST['api_selection_field']));
        }
    }
}


