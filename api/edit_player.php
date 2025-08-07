<?php
require_once __DIR__ . '/../lib/config.php';

global $DB;

// API header & check key.
API::init();

// Get params.
$player = [];
if (isset($_POST['id'])) {
    $player['id'] = $_POST['id'];
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing required parameter: id']);
    die();
}

// Check if player exists.
if (!$player = $DB->get('players', '*', ['id' => $player['id']])) {
    $error = 'Den Spieler gibts ned, oida!';
    http_response_code(409); // Conflict
    echo json_encode(['error' => $error]);
    die();
}

// Change player values.
foreach ($_POST as $key => $value) {
    if (in_array($key, ['name', 'bg', 'color', 'chipid'])) {
        $player[$key] = $value;
    }
}

// Save player changes.
$result = $DB->update('players', $player, ['id' => $player['id']]);

// Send back a JSON response
http_response_code(200); // OK
echo json_encode([
    'Updated player ' . $player['id'],
]);