<?php
/** ELO Rating System */
class ELO {

    /**
     * Calculate ELO difference for a game.
     *
     * @param array $game Game data.
     *
     * @return float ELO difference for this game.
     */
    public static function diff($game): float {

        // K factor - adjusts the sensitivity of the rating change.
        $k = 28;

        $team_winner_elo = ($game['elo_p1'] + $game['elo_p2']) / 2;
        $team_loser_elo = ($game['elo_p3'] + $game['elo_p4']) / 2;

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