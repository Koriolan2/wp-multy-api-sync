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

        if (empty($data)) {
            $this->write_log('Немає даних для збереження');
            wp_send_json_error('Немає даних для збереження');
            return;
        }

        if (!is_array($data)) {
            $this->write_log('Дані не є масивом. Перетворення об\'єкта на масив.');
            $data = (array)$data;
        }

        if (count($data) == 1 && is_array(reset($data))) {
            $this->write_log('Дані містять вкладений масив, перехід на цей рівень.');
            $data = reset($data);
        }

        // Створюємо таблицю динамічно на основі отриманих даних
        $this->create_dynamic_table($table_name, $data);

        // Очищуємо таблицю перед додаванням нових даних
        $wpdb->query("TRUNCATE TABLE $table_name");

        // Вставляємо нові дані
        foreach ($data as $record) {
            // Серіалізуємо вкладені дані
            foreach ($record as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $record[$key] = json_encode($value);
                }
            }

            // Логування даних для вставки
            $this->write_log('Дані для вставки: ' . print_r($record, true));

            // Вставляємо дані у таблицю
            $wpdb->insert($table_name, $record);
        }

        // Оновлюємо метадані про кількість отриманих даних і дату звернення
        update_post_meta($post_id, '_last_fetched', current_time('mysql'));
        update_post_meta($post_id, '_data_count', count($data));

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

        // Отримуємо перший запис для визначення полів
        $first_record = reset($data);
        $fields = array();

        // Автоматичне визначення типу даних для кожного поля
        foreach ($first_record as $column => $value) {
            // Пропускаємо поле 'id', якщо воно є
            if ($column === 'id') {
                continue;
            }

            if (is_array($value) || is_object($value)) {
                $field_type = 'LONGTEXT';  // Серіалізовані масиви/об'єкти
            } elseif (is_numeric($value)) {
                $field_type = 'BIGINT';  // Числові значення
            } elseif (strtotime($value) !== false && preg_match('/\d{4}-\d{2}-\d{2}/', $value)) {
                $field_type = 'DATETIME';  // Формат дати
            } else {
                $field_type = 'TEXT';  // За замовчуванням всі інші дані - TEXT
            }

            $fields[] = "`$column` $field_type";
        }

        if (empty($fields)) {
            $this->write_log('Поля для створення таблиці відсутні.');
            wp_send_json_error('Неможливо створити таблицю, відсутні поля.');
            return;
        }

        $fields_sql = implode(',', $fields);

        // Логування SQL-запиту перед виконанням
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            $fields_sql
        ) {$wpdb->get_charset_collate()};";

        $this->write_log("SQL-запит для створення таблиці: " . $sql);

        // Виконуємо SQL-запит напряму без dbDelta() для точнішого контролю
        $result = $wpdb->query($sql);
        if ($result === false) {
            $this->write_log('Помилка при створенні таблиці: ' . $wpdb->last_error);
            wp_send_json_error('Помилка при створенні таблиці: ' . $wpdb->last_error);
        } else {
            $this->write_log('Таблиця створена успішно.');
        }
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
