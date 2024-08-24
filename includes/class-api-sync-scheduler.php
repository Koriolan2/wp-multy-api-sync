<?php
class Api_Sync_Scheduler {

    // Запускає синхронізацію на основі вибраного графіка
    public function schedule_sync($post_id, $schedule) {
        // Видаляємо попереднє завдання, якщо таке є
        $this->unschedule_sync($post_id);

        // Додаємо нове завдання
        $hook = 'api_sync_cron_' . $post_id;
        if (!wp_next_scheduled($hook)) {
            switch ($schedule) {
                case '5_min':
                    wp_schedule_event(time(), 'five_minutes', $hook, array($post_id));
                    break;
                case 'hourly':
                    wp_schedule_event(time(), 'hourly', $hook, array($post_id));
                    break;
                case 'daily':
                    wp_schedule_event(time(), 'daily', $hook, array($post_id));
                    break;
            }
        }
    }

    // Видаляє попередні завдання для поста
    public function unschedule_sync($post_id) {
        $hook = 'api_sync_cron_' . $post_id;
        $timestamp = wp_next_scheduled($hook);
        if ($timestamp) {
            wp_unschedule_event($timestamp, $hook, array($post_id));
        }
    }

    // Функція для виконання синхронізації
    public function perform_sync($post_id) {
        $api_database = new Api_Sync_Database();
        $api_database->fetch_data($post_id); // Реалізуємо метод для отримання даних
    }
}

// Реєструємо кастомний інтервал у WP-Cron
function api_sync_custom_cron_intervals($schedules) {
    $schedules['five_minutes'] = array(
        'interval' => 300, // 5 хвилин
        'display' => __('Кожні 5 хвилин')
    );
    return $schedules;
}
add_filter('cron_schedules', 'api_sync_custom_cron_intervals');

// Реєстрація завдань для WP-Cron
function api_sync_cron_register() {
    $scheduler = new Api_Sync_Scheduler();

    // Додаємо callback для всіх WP-Cron хуків
    $posts = get_posts(array(
        'post_type' => 'api_config',
        'numberposts' => -1
    ));

    foreach ($posts as $post) {
        add_action('api_sync_cron_' . $post->ID, array($scheduler, 'perform_sync'), 10, 1);
    }
}
add_action('init', 'api_sync_cron_register');
