<?php
require_once __DIR__ . '/../lib/config.php';

// Set header to return JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST requests are allowed']);
    exit;
}

// Check api key.
$api_user = $DB->query("SELECT * FROM users WHERE username = 'api'")->fetch();
$api_key = $api_user['password_hash'];

if (!isset($_POST['apikey']) || $_POST['apikey'] !== $api_key) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Invalid API key']);
    die();
}

// Get params.
$params = ['p1', 'p2', 'p3', 'p4', 'gw', 'gl'];
foreach ($params as $param) {
    if (isset($_POST[$param])) {
        $$param = $_POST[$param];
    } else {
        http_response_code(400); // Bad Request
        echo json_encode([
            'error' => "Missing required parameter: $param"
        ]);
        die();
    }
}

// Get players.
$game = [];
for ( $i = 1; $i < 5; $i++ ) {
    $varname = "p". $i;
    $name = $$varname;
    $sql = "SELECT * FROM players WHERE name = '$name'";
    if (!$player = $DB->query($sql)->fetch()) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'error' => "Player $i not found",
        ]);
        die();
    } else {
        $game[$varname] = $player['id'];
    }
}

// Add game.
$game['wg'] = $_POST['gw']; // sigh.
$game['lg'] = $_POST['gl']; // ikr?
$error = validate_game($game);

if ($error == 0) {
    $result = add_game($game);
} else {
    http_response_code(422); // OK
    echo json_encode([
        'status' => "Could not add game. Error: $error",
    ]);
    die();
}

// Send back a JSON response
http_response_code(200); // OK
echo json_encode([
    'status' => $result,
]);