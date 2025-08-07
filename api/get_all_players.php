<?php
require_once __DIR__ . '/../lib/config.php';

// API header & check key.
API::init();

// Get players.
$players = $DB->select('players', '*');

// Send back a JSON response
http_response_code(200); // OK
echo json_encode([
    'players' => $players,
]);