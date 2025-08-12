<?php

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

/**
 * Converts a timestamp to metric time (CET).
 *
 * @param int $timestamp
 * @return float
 */
function convert_to_metric_time(int $timestamp): float {

    // Shift 8 hours earlier to avoid post-midnight wraparound
    $timestamp = $timestamp - (8 * 3600);

    // Create DateTime from Unix timestamp (UTC base)
    $date = new DateTime('@' . $timestamp);
    $date->setTimezone(new DateTimeZone('Europe/Berlin')); // CET + handles daylight saving

    $hours = (int) $date->format('G'); // 0–23 (no leading zero)
    $minutes = (int) $date->format('i'); // 00–59

    return round($hours + ($minutes / 60), 2); // Metric format (e.g. 14.75)
}

/**
 * Converts back to real time.
 *
 * @param float $metric_time
 * @return string
 */
function convert_to_real_time(float $metric_time): string {
    // Convert metric time back to hours and minutes
    $hours = floor($metric_time);
    $minutes = round(($metric_time - $hours) * 60);

    // Create DateTime object in CET
    $date = new DateTime();
    $date->setTime($hours, $minutes);
    $date->setTimezone(new DateTimeZone('Europe/Berlin')); // CET + handles daylight saving

    // Shift +8 hours
    $date->add(new DateInterval('PT8H'));

    return $date->format('H:i'); // Format as HH:MM
}

/**
 * Converts a timestamp to metric time (CET).
 *
 * @param int $timestamp
 * @return float
 */
function time_to_decimal(int $timestamp): float {

    $timestamp = $timestamp - 8 * 3600; // Reduce by 8 hours to not jump over midnight.
    $hour = date('H', $timestamp);
    $minutes = date('i', $timestamp);
    $minutes = $minutes / 60;
    return $hour + $minutes;
}

/**
 * Converts back to real time.
 *
 * @param array $time
 *
 * @return string
 */

function decimal_to_time($time): string {

    // Step 3: Normalize value to 0–24 (wrap around if needed)
    $time = fmod($time, 24);
    if ($time < 0) {
        $time += 24;
    }

    // Step 4: Convert decimal back to hours and minutes
    $avgHour = floor($time);
    $avgMinutes = round(($time - $avgHour) * 60);

    // Step 5: Optional - pad with leading zeroes for formatting
    $formattedTime = sprintf('%02d:%02d', $avgHour, $avgMinutes);

    return $formattedTime;

}
