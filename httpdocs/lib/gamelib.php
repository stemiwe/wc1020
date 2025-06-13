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
    $default = $entities[0][0];
    if (isset($_GET['value'])) {
        $default = $_GET['value'];
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
    $url = urlencode(current_url());
    $id = $player['id'];
    $link = "/player.php?id=$id&returnto=$url";
    $string = '<a href="' . $link  . '"class="player-name" style="' . $style . '">' . htmlspecialchars($player['name']) . '</a>';
    return $string;
}