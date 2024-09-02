<?php
/**
 * Клас для взаємодії з Shopify API.
 */

class Shopify_API {
    public $api_name = 'Shopify';
    public $has_schedule_settings = true; // Цей API підтримує налаштування графіка

    public function __construct() {
        // Можливі інші ініціалізації
    }

    public function get_settings_fields() {
        return array(
            'url_api' => 'URL API',
            'access_token' => 'Access Token',
            'table_suffix' => 'Table Suffix',
        );
    }
}
