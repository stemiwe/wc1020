<?php
/**
 * Drupal-style dd function.
 * @param mixed $var
 *
 * @return void
 */
function dd($var) {
    echo '<pre>';
    var_dump($var);
    die();
}

/**
 * Returns the current url.
 */
function current_url() {
    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    return $current_url;
}

/**
 * Formats a elo number.
 *
 * @param int elo
 * @param string $wordhigher
 * @param string $wordlower
 *
 * @return string
 */
function format_elo($elo, $wordhigher = 'elo', $wordlower = 'elo', $reversecolor = false) {
    if ($reversecolor) {
        $classhigher = 'loss';
        $classlower = 'gain';
    } else {
        $classhigher = 'gain';
        $classlower = 'loss';
    }
    if ($elo > 0) {
        $elo = '<span class="' . "elo-$classhigher" . '">+' . $elo . ' ' . $wordhigher . '</span>';
    } elseif ($elo < 0) {
        $elo = '<span class="' . "elo-$classlower" . '">' . $elo . ' ' . $wordlower . '</span>';
    } else {
        $elo = '<span class="elo-neutral">+' . $elo . ' ' . $wordhigher . '</span>';
    }
    return $elo;
}

/**
 * Gets day of the week from a string date.
 *
 * @param string $dateString
 *
 * @return bool|string
 */
function get_weekday(string $dateString): string {
    // Create DateTime object from input string
    $date = new DateTime($dateString);

    // Set up the formatter with German locale
    $formatter = new IntlDateFormatter(
        'de_DE',                      // Locale: German
        IntlDateFormatter::FULL,     // Full date (not used)
        IntlDateFormatter::NONE,     // No time
        $date->getTimezone(),        // Timezone from DateTime
        IntlDateFormatter::GREGORIAN,
        'EEEE'                        // 'EEEE' = Full weekday name
    );

    $weekday = $formatter->format($date); // e.g. "Montag"

    // Return first 2 letters capitalized.
    $weekday = mb_substr($weekday, 0, 2);
    return $weekday;
}

/**
 * Get a smooth color based on a percentage value.
 * @param mixed $value
 * @return string
 */
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

/**
 * Convert HSL to HEX color.
 *
 * @param float $h Hue (0-360)
 * @param float $s Saturation (0-100)
 * @param float $l Lightness (0-100)
 *
 * @return string HEX color code
 */
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