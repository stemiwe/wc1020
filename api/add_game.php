<?php
require_once __DIR__ . '/../lib/config.php';

// API header & check key.
API::init();

// Do not allow registering games too quickly.
$timestamp = time();
$lastgame = $DB->query("SELECT * FROM games ORDER BY id DESC LIMIT 1")->fetch();
if ($lastgame) {
    $lastgame_time = $lastgame['timestamp'];
    if ($timestamp - $lastgame_time < 10) { // 10 seconds cooldown
        http_response_code(429); // Too Many Requests
        echo json_encode([
            'error' => 'You can only register a game every 10 seconds.'
        ]);
        die();
    }
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
$cols = ['id', 'name', 'chipid'];
for ( $i = 1; $i < 5; $i++ ) {
    $varname = "p". $i;
    $id = $$varname;
    foreach ($cols as $col) {
        $sql = "SELECT * FROM players WHERE $col = '$id'";
        if ($player = $DB->query($sql)->fetch()) {
            $game[$varname] = $player['id'];
            break;
        }
    }
    if (!isset($game[$varname])) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'error' => "Player $i not found. Provide valid id, name or chipid.",
        ]);
        die();
    }
}

// Add game.
$game['wg'] = $_POST['gw']; // sigh.
$game['lg'] = $_POST['gl']; // ikr?
$game['duration'] = $_POST['duration'] ?? null;
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