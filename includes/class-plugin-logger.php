<?php
/**
 * Клас для логування повідомлень та помилок плагіну API Connector.
 */

class Plugin_Logger {

    // Шлях до файлу логів
    private $log_file;

    /**
     * Конструктор класу.
     * Визначаємо шлях до файлу логів.
     */
    public function __construct() {
        // Встановлюємо шлях до файлу log.txt у кореневій папці плагіну
        $this->log_file = plugin_dir_path(__FILE__) . '../log.txt';
    }

    /**
     * Логування повідомлень.
     *
     * @param string $message Повідомлення, яке потрібно залогувати.
     */
    public function log($message) {
        // Додаємо дату та час до повідомлення
        $date = date('Y-m-d H:i:s');
        $log_message = "[{$date}] {$message}\n";

        // Записуємо повідомлення у файл log.txt
        error_log($log_message, 3, $this->log_file);
    }
}

