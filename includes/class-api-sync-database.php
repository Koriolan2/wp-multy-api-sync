<?php
class Api_Sync_Database {

    // Отримання даних з API та обробка
    public function fetch_data() {
        check_ajax_referer('fetch_api_data_nonce', 'security');

        $post_id = absint($_POST['post_id']);
        $api_url = get_post_meta($post_id, '_api_url', true);
        $api_token = get_post_meta($post_id, '_api_token', true);
        $table_suffix = get_post_meta($post_id, '_table_suffix', true);

        global $wpdb;
        $table_name = $wpdb->prefix . $table_suffix;

        // Отримуємо дані з API
        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'X-Shopify-Access-Token' => $api_token,
                'Content-Type' => 'application/json'
            )
        ));

        if (is_wp_error($response)) {
            $this->write_log('Помилка при отриманні даних: ' . $response->get_error_message());
            wp_send_json_error('Помилка при отриманні даних');
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Логування отриманих даних
        $this->write_log('Отримані дані з API: ' . print_r($data, true));

        if (isset($data['errors'])) {
            $this->write_log('Помилка API: ' . print_r($data['errors'], true));
            wp_send_json_error('Помилка API: ' . $data['errors']);
            return;
        }

        if (empty($data['products'])) {
            $this->write_log('Немає даних для збереження');
            wp_send_json_error('Немає даних для збереження');
            return;
        }

        // Створюємо таблицю динамічно на основі отриманих даних
        $this->create_dynamic_table($table_name, $data['products']);

        // Очищуємо таблицю перед додаванням нових даних
        $wpdb->query("TRUNCATE TABLE $table_name");

        // Вставляємо нові дані
        foreach ($data['products'] as $product) {
            $flat_data = $this->flatten_array($product);
            $this->write_log('Дані для вставки: ' . print_r($flat_data, true));

            $wpdb->insert($table_name, $flat_data);
        }

        // Оновлюємо метадані про кількість отриманих даних і дату звернення
        update_post_meta($post_id, '_last_fetched', current_time('mysql'));
        update_post_meta($post_id, '_data_count', count($data['products']));

        // Додаємо повідомлення про успіх
        add_settings_error('api_sync_messages', 'api_sync_success', 'Дані успішно отримані та збережені.', 'updated');

        wp_send_json_success('Дані успішно отримані та збережені');
    }

    // Динамічне створення таблиці на основі отриманих даних
    private function create_dynamic_table($table_name, $data) {
        global $wpdb;

        if (empty($data)) {
            $this->write_log("Немає даних для створення таблиці");
            return;
        }

        // Визначаємо всі унікальні поля
        $fields = array();
        foreach ($data as $record) {
            $flat_record = $this->flatten_array($record);
            foreach ($flat_record as $column => $value) {
                // Перевіряємо наявність поля, щоб уникнути дублювання імен
                if (!isset($fields[$column])) {
                    $fields[$column] = $this->determine_field_type($value);
                }
            }
        }

        // Якщо немає полів, то не створюємо таблицю
        if (empty($fields)) {
            $this->write_log('Поля для створення таблиці відсутні.');
            wp_send_json_error('Неможливо створити таблицю, відсутні поля.');
            return;
        }

        // Формуємо SQL для створення таблиці
        $fields_sql = '';
        foreach ($fields as $field_name => $field_type) {
            // Уникаємо дублювання стовпця 'id', який уже є PRIMARY KEY
            if ($field_name !== 'id') {
                $fields_sql .= "`$field_name` $field_type, ";
            }
        }

        $fields_sql = rtrim($fields_sql, ', ');

        // Створюємо таблицю з PRIMARY KEY 'id', якщо вона не існує
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            $fields_sql
        ) {$wpdb->get_charset_collate()};";

        $this->write_log("SQL-запит для створення таблиці: " . $sql);

        // Виконуємо SQL-запит
        $result = $wpdb->query($sql);
        if ($result === false) {
            $this->write_log('Помилка при створенні таблиці: ' . $wpdb->last_error);
            wp_send_json_error('Помилка при створенні таблиці: ' . $wpdb->last_error);
        } else {
            $this->write_log('Таблиця створена успішно.');
        }
    }

    // Визначаємо тип поля на основі значення
    private function determine_field_type($value) {
        if (is_numeric($value)) {
            return 'BIGINT';
        } elseif (is_string($value) && strtotime($value) !== false) {
            return 'DATETIME';
        } elseif (is_bool($value)) {
            return 'TINYINT(1)';
        } else {
            return 'TEXT';
        }
    }

    // Перетворює багатовимірний масив у плоский масив (розгортає вкладені масиви)
    private function flatten_array($array, $prefix = '') {
        $result = array();
        foreach ($array as $key => $value) {
            // Додаємо префікс до ключів, щоб уникнути дублювання імен
            $new_key = $prefix ? "{$prefix}_{$key}" : $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flatten_array($value, $new_key));
            } else {
                $result[$new_key] = $value;
            }
        }
        return $result;
    }

    // Логування повідомлень у файл logs.txt
    private function write_log($message) {
        $log_file = plugin_dir_path(__FILE__) . '../logs.txt'; // Створюємо файл logs.txt у папці плагіну

        $current_time = date("Y-m-d H:i:s");
        $formatted_message = "[" . $current_time . "] " . $message . "\n";

        // Записуємо повідомлення у файл
        file_put_contents($log_file, $formatted_message, FILE_APPEND);
    }
}
