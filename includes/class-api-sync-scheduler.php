<?php

class Api_Sync_Scheduler {

    // Логування повідомлень у файл logs.txt
    private function write_log($message) {
        $log_file = plugin_dir_path(__FILE__) . '../logs.txt'; // Шлях до файлу logs.txt
        $current_time = date("Y-m-d H:i:s");
        $formatted_message = "[" . $current_time . "] " . $message . "\n";
        file_put_contents($log_file, $formatted_message, FILE_APPEND);
    }

    // Запускає синхронізацію на основі вибраного графіка
    public function schedule_sync($post_id, $schedule) {
        // Видаляємо попереднє завдання
        $this->unschedule_sync($post_id);
    
        // Лог для перевірки, що графік отриманий і обробляється
        $this->write_log("Запускаємо schedule_sync з графіком: $schedule для поста: $post_id");
    
        // Створюємо новий хук для WP-Cron
        $hook = 'api_sync_cron_' . $post_id;
    
        // Лог для перевірки запланованих завдань до нового планування
        if ($next_scheduled = wp_next_scheduled($hook)) {
            $this->write_log("Наступне заплановане завдання вже існує для поста $post_id: $next_scheduled.");
        }
    
        // Перевіряємо, чи вже заплановане завдання
        if (!wp_next_scheduled($hook)) {
            switch ($schedule) {
                case '5_min':
                    $this->write_log("Заплановано кожні 5 хвилин для поста: $post_id.");
                    wp_schedule_event(time() + 300, 'five_minutes', $hook, array($post_id));
                    break;
                case 'hourly':
                    $this->write_log("Заплановано щогодини для поста: $post_id.");
                    wp_schedule_event(time() + 3600, 'hourly', $hook, array($post_id));
                    break;
                case 'daily':
                    $this->write_log("Заплановано раз на добу для поста: $post_id.");
                    wp_schedule_event(time() + 86400, 'daily', $hook, array($post_id));
                    break;
                default:
                    $this->write_log("Невідомий графік: $schedule для поста: $post_id.");
                    break;
            }
        } else {
            $this->write_log("Завдання для поста $post_id вже заплановане, нове не створюється.");
        }
    }
    
    
    

    // Видаляє попередні завдання для поста
    public function unschedule_sync($post_id) {
        $hook = 'api_sync_cron_' . $post_id;
        
        // Видаляємо всі заплановані завдання
        while ($timestamp = wp_next_scheduled($hook)) {
            wp_unschedule_event($timestamp, $hook, array($post_id));
            $this->write_log("Завдання для поста $post_id видалено о $timestamp.");
        }
    }
    
    

    // Функція для виконання синхронізації
    public function perform_sync($post_id) {
        $current_time = date("Y-m-d H:i:s");
        $this->write_log("Виконується синхронізація для поста $post_id о $current_time.");
        
        $api_database = new Api_Sync_Database();
        $api_database->fetch_data($post_id); // Реалізуємо метод для отримання даних
    }
}

// Реєструємо кастомний інтервал у WP-Cron
function api_sync_custom_cron_intervals($schedules) {
    $schedules['five_minutes'] = array(
        'interval' => 300, // 5 хвилин = 300 секунд
        'display' => __('Кожні 5 хвилин')
    );
    return $schedules;
}
add_filter('cron_schedules', 'api_sync_custom_cron_intervals');

// Реєстрація завдань для WP-Cron
function api_sync_cron_register() {
    $scheduler = new Api_Sync_Scheduler();

    // Отримуємо всі записи з типом api_config
    $posts = get_posts(array(
        'post_type' => 'api_config',
        'numberposts' => -1
    ));

    // Реєструємо завдання для кожного поста
    foreach ($posts as $post) {
        $hook = 'api_sync_cron_' . $post->ID;
        if (!has_action($hook)) {
            add_action($hook, array($scheduler, 'perform_sync'), 10, 1);
        }
    }
}
add_action('init', 'api_sync_cron_register');
