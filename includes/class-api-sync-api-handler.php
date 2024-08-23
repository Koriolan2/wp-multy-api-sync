<?php
class Api_Sync_Api_Handler {

    // Метод для підключення до API і отримання даних
    public function get_api_data($api_url, $api_token) {
        // Виконання запиту до API
        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'X-Shopify-Access-Token' => $api_token,
                'Content-Type' => 'application/json'
            )
        ));

        // Перевірка на помилку
        if (is_wp_error($response)) {
            $this->write_log('Помилка при отриманні даних: ' . $response->get_error_message());
            return new WP_Error('api_error', 'Помилка при отриманні даних: ' . $response->get_error_message());
        }

        // Отримуємо тіло відповіді
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Перевірка на наявність помилок у відповіді API
        if (isset($data['errors'])) {
            $this->write_log('Помилка API: ' . print_r($data['errors'], true));
            return new WP_Error('api_error', 'Помилка API: ' . $data['errors']);
        }

        // Повертаємо дані або помилку, якщо дані порожні
        if (empty($data)) {
            $this->write_log('Немає даних для збереження');
            return new WP_Error('no_data', 'Немає даних для збереження');
        }

        // Логування отриманих даних
        $this->write_log('Отримані дані з API: ' . print_r($data, true));

        return $data;
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
