<?php

// Functions for elo, thx deepseek.
function elo_difference(array $winners, array $losers, int $gd): float {

    // K factor - adjusts the sensitivity of the rating change.
    $k = 28;

    $team_winner_elo = array_sum($winners) / count($winners);
    $team_loser_elo = array_sum($losers) / count($losers);

    $expected = expected_score($team_winner_elo, $team_loser_elo);
    $delta = $k * (1 - $expected);

    // Adjust for goal difference.
    $delta *= (1 + ($gd / 5));

    // Zu null zählt doppelt.
    if ($gd == 7) {
        $delta *= 2;
    }

    return round($delta, 0);
}

function expected_score(float $rating_a, float $rating_b): float {
    return 1 / (1 + pow(10, ($rating_b - $rating_a) / 400));
}

// Functions for elo color, thx deepseek.
function get_smooth_color_by_percentage($value) {

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

// Keep these helper functions the same
function hsl_to_hex($h, $s, $l) {
    $h /= 360;
    $s /= 100;
    $l /= 100;

    if ($s == 0) {
        $r = $g = $b = $l;
    } else {
        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        $r = hue_to_rgb($p, $q, $h + 1/3);
        $g = hue_to_rgb($p, $q, $h);
        $b = hue_to_rgb($p, $q, $h - 1/3);
    }

    return sprintf("#%02x%02x%02x", $r * 255, $g * 255, $b * 255);
}

function hue_to_rgb($p, $q, $t) {
    if ($t < 0) $t += 1;
    if ($t > 1) $t -= 1;
    if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
    if ($t < 1/2) return $q;
    if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
    return $p;
}