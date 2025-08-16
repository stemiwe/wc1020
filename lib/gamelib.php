<?php

/**
 * Gets the SQL WHERE condition for the type of timefilter.
 *
 * @return array
 */
function get_timefilter($include_regulars = true) {
    global $DB;

    // Get filter parameters.
    if (isset($_GET['time'])) {
        $timefilter = $_GET["time"];
    } else {
        $timefilter = "session";
    }

    // No regulars filter?
    if (!$include_regulars && $timefilter == 'regulars') {
        $timefilter = 'session';
    }

    $filter = [
        'col' => '',
        'filter' => $timefilter,
    ];

    // Set col to filter, because i misnamed the session col initially.
    // ToDo: rename session col.
    if ($timefilter == 'session') {
        $col = 'date';
    } elseif ($timefilter == 'season') {
        $col = 'season';
    } else {
        return $filter;
    }

    // Get disctinct rows.
    $query = "SELECT DISTINCT $col from games ORDER BY $col ASC";
    $entities = $DB->query($query)->fetchAll(PDO::FETCH_COLUMN);

    $options = [];
    foreach ($entities as $entity) {
        $options[] = $entity;
    }
    if ($entities) {
        $default = end($entities);
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
            'col' => $col,
            'filter' => $timefilter];
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
    $name = htmlspecialchars($player['name']);
    $img = html::player_image($name);
    $string = '<a href="' . $link  . '"class="player-name" style="' . $style . '"><div>' .
        $name . '</div>' . $img .'</a>';
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

    // Get player IDs.
    if ($game['p1'] > $game['p2']) {
        $p1_id = $game['p2'];
        $p2_id = $game['p1'];
    } else {
        $p1_id = $game['p1'];
        $p2_id = $game['p2'];
    }
    if ($game['p3'] > $game['p4']) {
        $p3_id = $game['p4'];
        $p4_id = $game['p3'];
    } else {
        $p3_id = $game['p3'];
        $p4_id = $game['p4'];
    }

    // Get team IDs.
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
    $player1 = $DB->get("players", "*", ["id" => $p1_id]);
    $player2 = $DB->get("players", "*", ["id" => $p2_id]);
    $player3 = $DB->get("players", "*", ["id" => $p3_id]);
    $player4 = $DB->get("players", "*", ["id" => $p4_id]);

    // Create game record.
    $session = date('Y-m-d', $date);
    $record = [
        'day' => get_weekday($session),
        'winner' => $t1_id,
        'loser' => $t2_id,
        'wg' => $game['wg'],
        'lg' => $game['lg'],
        'date' => $session,
        'season' => $CFG->season,
        'timestamp' => time(),
        'elo_p1' => $player1['elo'],
        'elo_p2' => $player2['elo'],
        'elo_p3' => $player3['elo'],
        'elo_p4' => $player4['elo'],
        's_elo_p1' => $player1['s_elo'],
        's_elo_p2' => $player2['s_elo'],
        's_elo_p3' => $player3['s_elo'],
        's_elo_p4' => $player4['s_elo'],
    ];

    // Calculate ELO if not provided.
    if (!isset($game['elo_diff']) || !isset($game['s_elo_diff'])) {
        $record['elo_diff'] = ELO::diff($record);
        $record['s_elo_diff'] = ELO::diff($record, 's_elo');
    } else {
        $record['elo_diff'] = $game['elo_diff'];
        $record['s_elo_diff'] = $game['s_elo_diff'];
    }

    // Insert game record.
    $DB->insert("games", $record);

    // Update ELO for players.
    $elo1 = $player1['elo'] + $record['elo_diff'];
    $elo2 = $player2['elo'] + $record['elo_diff'];
    $elo3 = $player3['elo'] - $record['elo_diff'];
    $elo4 = $player4['elo'] - $record['elo_diff'];
    $s_elo1 = $player1['s_elo'] + $record['s_elo_diff'];
    $s_elo2 = $player2['s_elo'] + $record['s_elo_diff'];
    $s_elo3 = $player3['s_elo'] - $record['s_elo_diff'];
    $s_elo4 = $player4['s_elo'] - $record['s_elo_diff'];
    for ($i = 1; $i <= 4; $i++) {
        $elokey = "elo$i";
        $s_elokey = "s_elo$i";
        $playerkey = "p$i" . "_id";
        $DB->update("players", [
            'elo' => $$elokey,
            's_elo' => $$s_elokey
        ], [
            'id' => $$playerkey
        ]);
    }

    // Commit.
    $result = $DB->pdo->commit();

    // Unset session.
    unset($_SESSION['game']);

    // Return result.
    if ($result) {
        return 'game added. ELO: ' . $record['elo_diff'];
    } else {
        return 'something went wrong';
    }
}

/**
 * Deletes a game, rolling back the ELO changes and updating stats.
 *
 * @param array $game
 *
 */

function remove_game($game) {

    global $DB;

    // Get params.
    $winner = $DB->query("SELECT * FROM teams WHERE ID = " . $game['winner'])->fetch();
    $loser = $DB->query("SELECT * FROM teams WHERE ID = " . $game['loser'])->fetch();
    $player1 = $DB->query("SELECT * FROM players WHERE ID = " . $winner['p1'])->fetch();
    $player2 = $DB->query("SELECT * FROM players WHERE ID = " . $winner['p2'])->fetch();
    $player3 = $DB->query("SELECT * FROM players WHERE ID = " . $loser['p1'])->fetch();
    $player4 = $DB->query("SELECT * FROM players WHERE ID = " . $loser['p2'])->fetch();

    // Give back ELO.
    $elo = $game['elo_diff'];
    $player1['elo'] -= $elo;
    $player2['elo'] -= $elo;
    $player3['elo'] += $elo;
    $player4['elo'] += $elo;
    $DB->update("players", ['elo' => $player1['elo']], ['id' => $player1['id']]);
    $DB->update("players", ['elo' => $player2['elo']], ['id' => $player2['id']]);
    $DB->update("players", ['elo' => $player3['elo']], ['id' => $player3['id']]);
    $DB->update("players", ['elo' => $player4['elo']], ['id' => $player4['id']]);

    // Delete game.
    $DB->delete("games", ['id' => $game['id']]);
}

/**
 * Gets the number of sessions for a player.
 *
 * @param int $player_id
 * @param bool $only_thursdays
 * @param int $season
 *
 * @return int
 */
function get_player_sessions($player_id, $only_thursdays = true, $season = null) {
    global $DB;

    // Get the number of sessions for the player.
    $sql = "SELECT COUNT(DISTINCT date) as count FROM games
            WHERE (winner IN (SELECT id FROM teams WHERE p1 = ? OR p2 = ?)
            OR loser IN (SELECT id FROM teams WHERE p1 = ? OR p2 = ?))";
    if ($only_thursdays) {
        $sql .= " AND day = 'Do' ";
    }
    if ($season) {
        $sql .= " AND season = $season";
    }
    $params = [$player_id, $player_id, $player_id, $player_id];
    $stmt = $DB->pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetchAll();
    $result = reset($result);
    return $result['count'];
}
