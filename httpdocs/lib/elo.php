<?php

function elo_difference(array $winners, array $losers, int $gd): float {
    $k = 32; 

    $team_winner_elo = calculate_team_elo($winners);
    $team_loser_elo = calculate_team_elo($losers);

    $expected = expected_score($team_winner_elo, $team_loser_elo);
    $delta = $k * (1 - $expected);

    // Adjust for goal difference.
    $delta *= (1 + ($gd / 4)); // Adjust the factor as needed for your game.

    if ($gd == 7) {
        $delta *= 2; 
    }

    return round($delta, 0);
}

function calculate_team_elo(array $team): float {
    return array_sum($team) / count($team);
}

function expected_score(float $rating_a, float $rating_b): float {
    return 1 / (1 + pow(10, ($rating_b - $rating_a) / 400));
}