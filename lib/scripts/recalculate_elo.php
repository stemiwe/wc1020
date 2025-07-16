<?php

require_once __DIR__ . '/../config.php';

global $DB;

$games = $DB->select('games', '*', ['ORDER' => ['timestamp' => 'ASC']]);
$elos = [];
$players = [];

echo "<pre>";

foreach ($games as $game) {
    $winner = $DB->get('teams', '*', ['id' => $game['winner']]);
    $loser = $DB->get('teams', '*', ['id' => $game['loser']]);
    $elo_diff_old = $game['elo_diff'];
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

        // Write player ELO into games table.
        $game['elo_p' . $key] = $elo;
    }

    // Recalculate ELO diff.
    $elo_diff_new = elo_difference([$game['elo_p1'], $game['elo_p2']], [$game['elo_p3'], $game['elo_p4']], $game['wg'] - $game['lg']);

    // Update player ELO array.
    foreach ($players as $key => $playerid) {
        if ($key == 1 || $key == 2) {
            // Player is on the winning team.
            $elos[$playerid] = ($elos[$playerid] ?? 1000) + $elo_diff_new;
        } else {
            // Player is on the losing team.
            $elos[$playerid] = ($elos[$playerid] ?? 1000) - $elo_diff_new;
        }
    }

    // Save updated game data.
    $DB->update('games', [
        'elo_p1' => $game['elo_p1'],
        'elo_p2' => $game['elo_p2'],
        'elo_p3' => $game['elo_p3'],
        'elo_p4' => $game['elo_p4'],
        'elo_diff' => $elo_diff_new,
    ], ['id' => $game['id']]);

    echo "Game ID: {$game['id']}, Old ELO Diff: $elo_diff_old, New ELO Diff: $elo_diff_new\n";
}

// Update player ELOs in the database.
foreach ($elos as $playerid => $elo) {
    $DB->update('players', ['elo' => $elo], ['id' => $playerid]);

    echo "Updated player ID $playerid with ELO $elo\n";
}

die();