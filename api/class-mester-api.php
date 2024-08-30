<?php
/**
 * Клас для взаємодії з Mester API.
 */

class Mester_API {
    public $api_name = 'Mester';

    public function __construct() {
        // Можливі інші ініціалізації
    }

    public function get_settings_fields() {
        return array(
            'url_api' => 'URL API',
            'client_id' => 'Client ID',
            'access_token' => 'Access Token',
            'table_suffix' => 'Table Suffix',
        );
    }
}
