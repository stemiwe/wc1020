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