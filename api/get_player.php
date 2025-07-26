<?php
require_once __DIR__ . '/../lib/config.php';

// API header & check key.
API::init();

// Get params.
if (isset($_POST['id'])) {
    $id = $_POST['id'];
} elseif (isset($_POST['name'])) {
    $name = $_POST['name'];
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing required parameter: id or name']);
    die();
}

// Get player.
if (isset($id)) {
    $player = $DB->get('players', '*', ['id' => $id]);
} else {
    $player = $DB->get('players', '*', ['name' => $name]);
}

if (!$player) {
    http_response_code(404); // Not Found
    echo json_encode(['error' => 'Player not found']);
    die();
}

// Send back a JSON response
http_response_code(200); // OK
echo json_encode([
    $player,
]);