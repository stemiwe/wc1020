<?php
/**
 * API class for handling API requests.
 */
class API {

    /**
     * Require a valid API key for the request.
     */
    public static function init() {
        global $DB;

        // Set header to return JSON
        header('Content-Type: application/json');

        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['error' => 'Only POST requests are allowed']);
            exit;
        }

        // Get API user.
        $api_user = $DB->query("SELECT * FROM users WHERE username = 'api'")->fetch();
        $api_key = $api_user['password_hash'];

        // Check if API key is provided and valid.
        if (!isset($_POST['apikey']) || $_POST['apikey'] !== $api_key) {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'Invalid API key']);
            die();
        }
    }
}
