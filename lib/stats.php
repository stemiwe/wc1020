<?php

/**
 * Stats for winners.
 * @param int $id
 * @param string $entity_type
 * @param string $result
 */

function update_stats($id, $entity_type, $result) {

    global $DB;
    $now = time();

    // Streaks.
    $type = $entity_type . '_streak';

    // End streak.
    if ($result == 'loss') {
        if ($record = $DB->get('stats', '*',
            ['entity_id' => $id, 'type' => $type, 'end' => 0])) {
            $record['end'] = $now;
            $DB->update('stats', $record, ['id' => $record['id']]);
        }

    } elseif ($result == 'win') {

        // Prolong streak.
        if ($record = $DB->get('stats', '*',
            ['entity_id' => $id, 'type' => $type, 'end' => 0])) {
            $record['value'] += 1;
            $DB->update('stats', $record, ['id' => $record['id']]);

        // Start streak.
        } else {
            $record = [
                'entity_id' => $id,
                'type' => $type,
                'value' => 1,
                'start' => $now,
                'end' => 0,
            ];
            $DB->insert('stats', $record);
        }
    }
}

/**
 * Gets a player from DB.
 * @param int $id
 */
function get_player($id) {
    global $DB;
    return $DB->get('players', '*', ['id' => $id]);
}

/**
 * Gets the medals for a given date.
 *
 * @param string $date
 * @return array
 */
function get_medals($date) {

    global $DB;

    // Get MVP.
    $mvp_scores = [];

    // Use a prepared statement (recommended to avoid SQL injection)
    $sql = "SELECT
                g.elo_diff,
                winner.p1 AS winner_p1, winner.p2 AS winner_p2,
                loser.p1 AS loser_p1, loser.p2 AS loser_p2
            FROM games AS g
            LEFT JOIN teams AS winner ON g.winner = winner.id
            LEFT JOIN teams AS loser ON g.loser = loser.id
            WHERE g.date = :date";
    $games = $DB->query($sql, [':date' => $date])->fetchAll();

    foreach ($games as $g) {
        $elo = (int)$g['elo_diff'];

        // Add elo for winner team players
        foreach (['winner_p1', 'winner_p2'] as $col) {
            $pid = $g[$col];
            if ($pid) {
                if (!isset($mvp_scores[$pid])) $mvp_scores[$pid] = 0;
                $mvp_scores[$pid] += $elo;
            }
        }

        // Subtract elo for loser team players
        foreach (['loser_p1', 'loser_p2'] as $col) {
            $pid = $g[$col];
            if ($pid) {
                if (!isset($mvp_scores[$pid])) $mvp_scores[$pid] = 0;
                $mvp_scores[$pid] -= $elo;
            }
        }
    }

    // Sort mvp scores in descending order
    arsort($mvp_scores);

    // Get the first three players.
    $medals = ['gold' => [],
               'silver' => [],
               'bronze' => [],
            ];
    $last = [];
    $skipnext = false;
    foreach ($mvp_scores as $pid => $elo) {
        foreach ($medals as $key => $medal) {
            if ($skipnext) {
                $skipnext = false;
                continue;
            }
            if (empty($medal)) {
                $last = ['elo' => $elo,
                         'medal' => $key
                ];
                $medals[$key][] = ['player' => get_player($pid), 'elo' => $elo];
                break;
            } elseif ($last['elo'] == $elo) {
                $medalkey = $last['medal'];
                $medals[$medalkey][] = ['player' => get_player($pid), 'elo' => $elo];
                $skipnext = true;
                break;
            }
        }
    }

    return $medals;
}