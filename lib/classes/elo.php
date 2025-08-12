<?php
/** ELO Rating System */
class ELO {

    /**
     * Gets current ELO or sELOfor a player.
     *
     * @param int playerid
     * @param int season
     *
     * @return int Current ELO rating for the player.
     */
    public static function current($playerid, $season = null) {

        global $DB;

        // Get teams for player.
        $team_ids = get_team_ids($playerid);

        // Get last game for the player.
        $sql = 'SELECT *
                FROM games
                WHERE (winner IN (' . implode(',', $team_ids) . ')
                OR loser IN (' . implode(',', $team_ids) . '))';
        if ($season) {
            $sql .= ' AND season = ' . $season;
        }
        $sql .= ' ORDER BY timestamp DESC
                 LIMIT 1';
        $game = $DB->query($sql)->fetch();

        // Default ELO if no games found.
        if (!$game) {
            return 1000;
        }

        // Get player position in the game.
        if (in_array($game['winner'], $team_ids)) {
            $team = $DB->get('teams', '*', ['id' => $game['winner']]);
            if ($team['p1'] == $playerid) {
                $pos = 1;
            } elseif ($team['p2'] == $playerid) {
                $pos = 2;
            }
        } elseif (in_array($game['loser'], $team_ids)) {
            $team = $DB->get('teams', '*', ['id' => $game['loser']]);
            if ($team['p1'] == $playerid) {
                $pos = 3;
            } elseif ($team['p2'] == $playerid) {
                $pos = 4;
            }
        }

        // ELO or sELO?
        if ($season) {
            $elokey = 's_elo_p';
            $elodiffkey = 's_elo_diff';
        } else {
            $elokey = 'elo_p';
            $elodiffkey = 'elo_diff';
        }
        $elo = $game[$elokey . $pos];

        // Win.
        if ($pos < 3) {
            $elo += $game[$elodiffkey];
        } else {
            $elo -= $game[$elodiffkey];
        }

        return $elo;
    }

    /**
     * Calculate ELO difference for a game.
     *
     * @param array $game Game data.
     * @param string $key elo or s_elo (default: elo)
     *
     * @return float ELO difference for this game.
     */
    public static function diff($game, $key = 'elo'): float {

        // K factor - adjusts the sensitivity of the rating change.
        $k = 28;

        $team_winner_elo = ($game[$key . '_p1'] + $game[$key . '_p2']) / 2;
        $team_loser_elo = ($game[$key . '_p3'] + $game[$key . '_p4']) / 2;

        $expected = self::expected_score($team_winner_elo, $team_loser_elo);
        $delta = $k * (1 - $expected);

        // Adjust for goal difference.
        $gd = $game['wg'] - $game['lg'];
        $delta *= (1 + ($gd / 5));

        // Zu null zählt doppelt.
        if ($gd == 7) {
            $delta *= 2;
        }

        return round($delta, 0);
    }

    /**
     * Gives fontsize for ELO diff to render in.
     *
     * @param int $elo_diff ELO difference.
     *
     * @return string Font size for ELO difference.
     */
    public static function fontsize($elo_diff): string {
        $elofontsize = $elo_diff / 20;
        return round(max(1, min(2, $elofontsize)), 1);
    }

    /**
     * Gives color for ELO diff to render in.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function color($value) {

        $percentage = ($value - 10) * (100 / 28);
        $percentage = max(0, min(100, $percentage));

        // Hue: 120° (green) to 0° (red)
        $hue = 120 * (1 - $percentage / 100);

        // Full saturation for vibrant colors
        $saturation = 100;

        // Adjust lightness for better visual progression
        $lightness = 50 - 15 * cos($percentage * M_PI / 100);

        // Darken the extremes slightly
        if ($percentage < 10) $lightness *= 0.9;  // Darker greens
        if ($percentage > 90) $lightness *= 0.7; // Darker reds

        return hsl_to_hex($hue, $saturation, $lightness);
    }

    /**
     * Helper function for ELO difference: calculate the expected score for a player.
     *
     * @param float $rating_a Player A's rating
     * @param float $rating_b Player B's rating
     *
     * @return float Expected score for Player A
     */
    private static function expected_score(float $rating_a, float $rating_b): float {
        return 1 / (1 + pow(10, ($rating_b - $rating_a) / 400));
    }


}