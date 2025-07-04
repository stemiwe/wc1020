<?php

/**
 * Gets the SQL WHERE condition for the type of timefilter.
 *
 * @return array
 */
function get_timefilter() {
    global $DB;

    // Get filter parameters.
    if (isset($_GET['time'])) {
        $timefilter = $_GET["time"];
    } else {
        $timefilter = "session";
    }

    // Set col to filter, because i misnamed the session col initially..
    // ToDo: rename session col.
    if ($timefilter == 'session') {
        $col = 'date';
    } elseif ($timefilter == 'season') {
        $col = 'season';
    } else {
        return '';
    }

    // Get disctinct rows.
    $query = "SELECT DISTINCT $col from games ORDER BY $col DESC";
    $entities = $DB->query($query)->fetchAll();

    $options = [];
    foreach ($entities as $entity) {
        $options[] = $entity[0];
    }
    if ($entities) {
        $default = $entities[0][0];
        if (isset($_GET['value'])) {
            $default = $_GET['value'];
        }
    } else {
        $default = '';
    }

    $sql = "WHERE $col = '$default'";

    return ['options' => $options,
            'default' => $default,
            'sql' => $sql,
            'col' => $col];
}


/**
 * Get the lost games for an array of team ids.
 * @param array $team_ids
 * @return array
 */
function get_losses($team_ids) {
    global $DB;
    if (empty($team_ids) || !is_array($team_ids)) {
        return [];
    }
    $team_ids_param = '(' . implode(',', $team_ids) . ')';
    $query = "SELECT * FROM games WHERE loser IN $team_ids_param";
    return $DB->query($query)->fetchAll();
}

// Function to get or create a team
function get_or_create_team($DB, $p1_id, $p2_id) {
    $team_id = $DB->get("teams", "id", [
        "AND" => [
            "p1" => $p1_id,
            "p2" => $p2_id
        ]
    ]);

    if (!$team_id) {
        $DB->insert("teams", [
            'p1' => $p1_id,
            'p2' => $p2_id
        ]);
        $team_id = $DB->id();
    }

    return $team_id;
}

/**
 * Get the won games for an array of team ids.
 * @param array $team_ids
 * @return array
 */
function get_wins($team_ids) {
    global $DB;
    if (empty($team_ids) || !is_array($team_ids)) {
        return [];
    }
    $team_ids_param = '(' . implode(',', $team_ids) . ')';
    $query = "SELECT * FROM games WHERE winner IN $team_ids_param";
    return $DB->query($query)->fetchAll();
}

/**
 * Gets team ids for a player.
 * @param string $player_id
 * @return array
 */
function get_team_ids($player_id) {
    global $DB;
    $query = "SELECT id FROM teams WHERE p1 = ? OR p2 = ?";
    $params = [1 => $player_id, 2 => $player_id];
    $teams = $DB->query($query, $params)->fetchAll();
    return array_column($teams, 'id');
}

/**
 * Create a player HTML element.
 *
 * @param mixed $player
 * @return string
 */
function write_player($player) {
    $style = 'color: ' . htmlspecialchars($player['color']) . ' !important; ';
    $style .= 'background-color: ' . htmlspecialchars($player['bg']) . ';';

    // Get returnurl.
    if (isset($_GET['returnto'])) {
        $returnurl = $_GET['returnto'];
    } else {
        $returnurl = urlencode(current_url());
    }
    $id = $player['id'];
    $link = "/player.php?id=$id&returnto=$returnurl";
    $string = '<a href="' . $link  . '"class="player-name" style="' . $style . '">' . htmlspecialchars($player['name']) . '</a>';
    return $string;
}

/**
 * Validates game data.
 *
 * @param array $data
 *
 * @return mixed 0 if successful, errormsg otherwise.
 */
function validate_game($data) {
    // Validation.
    $valid = true;
    $ps = [];
    for ($i = 1; $i < 5; $i++) {
        // Check for empty fields.
        $p = $data['p' . $i];
        if (($p == 0)) {
            $error = 'Do fehlt wos oida!';
            $valid = false;
            break;
        }

        // Check for duplicates.
        if (in_array($data['p' . $i], $ps)) {
            $error = 'Koana spielt doppelt oida!';
            $valid = false;
            break;
        }
        $ps[] = $data['p' . $i];
    }

    // Get params.
    $p1 = $data['p1'];
    $p2 = $data['p2'];
    $p3 = $data['p3'];
    $p4 = $data['p4'];
    $wg = $data['wg'];
    $lg = $data['lg'];

    // Goal validation.
    if ($lg >= $wg) {
        $error = 'Da Siega muss mehr Tore hom oida!';
        $valid = false;
    }
    if ($wg < 10 && !($wg > ($lg + 1))) {
        $error = 'Mir spieln mit zwoa unterschied oida!';
        $valid = false;
    }
    if ($wg < 7) {
        $error = 'Des Spiel is no ned aus oida!';
        $valid = false;
    }
    if ($wg > 7 && $lg < 6) {
        $error = 'Bisi viel Tore fürn Sieger, oda?';
        $valid = false;
    }

    if ($valid) {
        return 0;
    } else {
        return $error;
    }
}


/**
 * Adds a game.
 *
 * @param array $game
 * @return string
 */
// Get player IDs.
function add_game($game) {

    global $CFG, $DB;

    $DB->pdo->beginTransaction();
    $p1_id = $game['p1'];
    $p2_id = $game['p2'];
    $p3_id = $game['p3'];
    $p4_id = $game['p4'];

    $t1p1_id = $p1_id < $p2_id ? $p1_id : $p2_id;
    $t1p2_id = $p1_id < $p2_id ? $p2_id : $p1_id;
    $t2p1_id = $p3_id < $p4_id ? $p3_id : $p4_id;
    $t2p2_id = $p3_id < $p4_id ? $p4_id : $p3_id;

    // Get or create both teams.
    $t1_id = get_or_create_team($DB, $t1p1_id, $t1p2_id);
    $t2_id = get_or_create_team($DB, $t2p1_id, $t2p2_id);

    // Shift current time 8 hours back because our day doesnt end at midnight.
    $date = time() - 8 * 3600;

    // Get players.
    $player1 = $DB->get("players", "*", ["id" => $game['p1']]);
    $player2 = $DB->get("players", "*", ["id" => $game['p2']]);
    $player3 = $DB->get("players", "*", ["id" => $game['p3']]);
    $player4 = $DB->get("players", "*", ["id" => $game['p4']]);

    // Calculate ELO if not provided.
    if (!isset($game['elo_diff'])) {
        $elo1 = [$player1['elo'], $player2['elo']];
        $elo2 = [$player3['elo'], $player4['elo']];
        $game['elo_diff'] = elo_difference($elo1, $elo2, $game['wg'] - $game['lg']);
    }

    // Create game record.
    $record = [
        'winner' => $t1_id,
        'loser' => $t2_id,
        'wg' => $game['wg'],
        'lg' => $game['lg'],
        'date' => date('Y-m-d', $date),
        'season' => $CFG->season,
        'timestamp' => time(),
        'elo_diff' => $game['elo_diff'],
    ];
    $DB->insert("games", $record);

    // Update ELO for players.
    $elo1 = $player1['elo'] + $game['elo_diff'];
    $elo2 = $player2['elo'] + $game['elo_diff'];
    $elo3 = $player3['elo'] - $game['elo_diff'];
    $elo4 = $player4['elo'] - $game['elo_diff'];
    for ($i = 1; $i <= 4; $i++) {
        $elokey = "elo$i";
        $playerkey = "p$i" . "_id";
        $DB->update("players", [
            'elo' => $$elokey
        ], [
            'id' => $$playerkey
        ]);
    }

    // Update stats.
    update_stats($t1_id, 'team', 'win');
    update_stats($t2_id, 'team', 'loss');
    update_stats($p1_id, 'player', 'win');
    update_stats($p2_id, 'player', 'win');
    update_stats($p3_id, 'player', 'loss');
    update_stats($p4_id, 'player', 'loss');

    // Commit.
    $result = $DB->pdo->commit();

    // Unset session.
    unset($_SESSION['game']);

    // Return result.
    if ($result) {
        return 'game added. ELO: ' . $game['elo_diff'];
    } else {
        return 'something went wrong';
    }
}

