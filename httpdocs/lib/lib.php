<?php

require_once __DIR__ . '/elo.php';

/**
 * Create header.
 * @return string
 */
function print_header() {
    // Header.    $
    $string = '<!DOCTYPE html>';
    $string .= '<html lang="en">';
    $string .= '<head>';
    $string .= '<meta charset="UTF-8">';
    $string .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    $string .= '<title>WC1020</title>';

    // Add styles and CDNs.
    $string .= '<link rel="stylesheet" href="/styles/styles.css">';
    $string .= '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">';
    $string .= '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
    $string .= '<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>';
    $string .= '<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">';
    $string .= '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">';
    $string .= '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
    $string .= '<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>';

    // Close head.
    $string .= '</head>';

    // Menu.
    $string .= '<body>';
    $string .= '<div class="content">';    

    return $string;
}


/**
 * Create menu.
 * @return string
 */
function menu() {
    $string = '<div class="header">';    
    $string .= '<a class="menu button" href="/sessions.php">Sessions</a>';
    $string .= '<a class="menu button" href="/games.php">Games</a>';
    $string .= '<a class="menu button" href="/players.php">Players</a>';    
    $string .= '<a class="menu button" href="/teams.php">Teams</a>';
    $string .= '</div>';

    return $string;    
}

/**
 * Create a player HTML element.
 * 
 * @param mixed $player
 * @return string
 */
function write_player($player) {
    $style = 'background-color: ' . htmlspecialchars($player['bg']) . '; color: ' . htmlspecialchars($player['color']) . ';';
    $string = '<div class="player-name" style="' . $style . '">' . htmlspecialchars($player['name']) . '</div>';
    return $string;
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
 * Print footer.
 * @return string
 */
function print_footer() {
    $js = '<script src="/js/scripts.js"></script>';
    return $js; 
}