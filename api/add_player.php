<?php
require_once __DIR__ . '/../lib/config.php';

global $DB;

// API header & check key.
API::init();

// Get params.
$player = [];
if (isset($_POST['name'])) {
    $player['name'] = $_POST['name'];
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing required parameter: name']);
    die();
}

// Check if name already exists.
if ($dupe = $DB->get('players', '*', ['name' => $player['name']])) {
    $error = 'Den Namen gibts scho, oida!';
    http_response_code(409); // Conflict
    echo json_encode(['error' => $error]);
    die();
}

// Colors.
if (isset($_POST['bg'])) {
    $player['bg'] = $_POST['bg'];
} else {
    $player['bg'] = '#' . str_pad(dechex(mt_rand(0x0, 0x7FFFFF)), 6, '0', STR_PAD_LEFT);
}
if (isset($_POST['color'])) {
    $player['color'] = $_POST['color'];
} else {
    $player['color'] = '#ffffff';
}

// Rest.
$player['elo'] = 1000; // Default ELO.
$player['joined'] = time();

// Add player.
$result = $DB->insert("players", $player);

// Send back a JSON response
http_response_code(200); // OK
echo json_encode([
    'Added player ' . $DB->id(),
]);