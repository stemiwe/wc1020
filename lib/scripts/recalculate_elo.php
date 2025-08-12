<?php

require_once __DIR__ . '/../config.php';
require_login();

global $DB;

$playerid = 32;
$elo = ELO::current($playerid, 1);
var_dump($elo);
die();

$games = $DB->select('games', '*', [
    'ORDER' => ['timestamp' => 'ASC']
]);
$elos = [];
$season = [];
$players = [];

echo "<pre>";

foreach ($games as $game) {
    $winner = $DB->get('teams', '*', ['id' => $game['winner']]);
    $loser = $DB->get('teams', '*', ['id' => $game['loser']]);
    $elo_diff_old = $game['elo_diff'];
    $s_elo_diff_old = $game['s_elo_diff'];
    $players[1] = $winner['p1'];
    $players[2] = $winner['p2'];
    $players[3] = $loser['p1'];
    $players[4] = $loser['p2'];

    foreach ($players as $key => $playerid) {

        // Get player ELO.
        if (array_key_exists($playerid, $elos)) {
            $elo = $elos[$playerid];
        } else {
            $elo = 1000;
        }

        // Get player S_ELO.
        if (array_key_exists($playerid, $season)
            && $season[$playerid] == $game['season']
            && array_key_exists($playerid, $s_elos)) {
            $s_elo = $s_elos[$playerid];
        } else {
            $s_elo = 1000;
            $s_elos[$playerid] = $s_elo;
            $season[$playerid] = $game['season'];
        }

        // Update player ELO.
        $game['elo_p' . $key] = $elo;
        $game['s_elo_p' . $key] = $s_elo;
    }

    // Recalculate ELO diff.
    $elo_diff_new = ELO::diff($game);
    $s_elo_diff_new = ELO::diff($game, 's_elo');

    // Update player ELO array.
    foreach ($players as $key => $playerid) {

        if ($key == 1 || $key == 2) {
            // Player is on the winning team.
            $elos[$playerid] = ($elos[$playerid] ?? 1000) + $elo_diff_new;
            $s_elos[$playerid] = ($s_elos[$playerid] ?? 1000) + $s_elo_diff_new;
        } else {
            // Player is on the losing team.
            $elos[$playerid] = ($elos[$playerid] ?? 1000) - $elo_diff_new;
            $s_elos[$playerid] = ($s_elos[$playerid] ?? 1000) - $s_elo_diff_new;
        }
    }

    // Save updated game data.
    $DB->update('games', [
        'elo_p1' => $game['elo_p1'],
        'elo_p2' => $game['elo_p2'],
        'elo_p3' => $game['elo_p3'],
        'elo_p4' => $game['elo_p4'],
        'elo_diff' => $elo_diff_new,
        's_elo_p1' => $game['s_elo_p1'],
        's_elo_p2' => $game['s_elo_p2'],
        's_elo_p3' => $game['s_elo_p3'],
        's_elo_p4' => $game['s_elo_p4'],
        's_elo_diff' => $s_elo_diff_new,
    ], ['id' => $game['id']]);

    echo "Season {$game['season']} Game ID: {$game['id']}, ELO Diff old: $elo_diff_old, new: $elo_diff_new, S_ELO Diff old: $s_elo_diff_old, new: $s_elo_diff_new\n";
}

// Update player ELOs in the database.
foreach ($elos as $playerid => $elo) {
    $DB->update('players', ['elo' => $elo, 's_elo' => $s_elos[$playerid]], ['id' => $playerid]);
}

die();