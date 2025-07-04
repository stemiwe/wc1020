<?php

/**
 * Create header.
 * @return string
 */
function print_header() {

    global $CFG;

    // Disable css caching.
    if ($CFG->nocache) {
        $nocache = rand(100000, 999999);
    } else {
        $nocache = date('Ymd');
    }

    // Testsite?
    if ($CFG->testsite) {
        $CFG->wwwroot = 'https://wc1020.at/test';
    } else {
        $CFG->wwwroot = 'https://wc1020.at';
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WC1020</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">

    <!-- Font -->
    <link rel="preload" href="{$CFG->wwwroot}/styles/fonts/press_start_p2.woff2" as="font" type="font/woff2" crossorigin>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{$CFG->wwwroot}/styles/favicon_io/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{$CFG->wwwroot}/styles/favicon_io/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="{$CFG->wwwroot}/styles/favicon_io/favicon-16x16.png">
    <link rel="manifest" href="{$CFG->wwwroot}/styles/favicon_io//site.webmanifest">

    <!-- Styles and JS -->
    <link rel="stylesheet" href="{$CFG->wwwroot}/styles/styles.css?v={$nocache}">
    <link rel="stylesheet" href="{$CFG->wwwroot}/styles/ext/jquery.dataTables.min.css">
    <script src="{$CFG->wwwroot}/js/ext/jquery-3.6.0.min.js"></script>
    <script src="{$CFG->wwwroot}/js/ext/jquery.dataTables.min.js"></script>

</head>
<body>
    <div class="content">
HTML;
}

/**
 * Create menu.
 * @return string
 */
function print_menu() {

    global $CFG;

    // Get saved menu selections.
    if (isset($_SESSION['stats'])) {
        $stats = $_SESSION['stats'];
    } else {
        $stats = 'sessions.php';
    }

    if (isset($_GET['time'])) {
        $time = $_GET['time'];
    } else {
        $time = 'session';
    }

    // Print main menu.
    $menu =  <<<HTML
<div class="header">
    <div class="menu">
        <a class="menu-tab menu-item" data-id="games" href="{$CFG->wwwroot}/games.php?time=$time">Games</a>
        <a class="menu-tab menu-item" data-id="players"href="{$CFG->wwwroot}/players.php?time=$time">Players</a>
        <a class="menu-tab menu-item" data-id="teams" href="{$CFG->wwwroot}/teams.php?time=$time">Teams</a>
        <a class="menu-tab menu-item" data-id="stats" href="{$CFG->wwwroot}/stats/$stats">Stats</a>
    </div>

HTML;

    /// Get path for secondary menu.
    $path = $_SERVER['REQUEST_URI'];

    // Stats menu.
    if (str_contains($path, 'stats/')) {

        $menu .=  <<<HTML
    <div class="submenu">
        <a class="button submenu-button menu-item" data-parent="stats" href="{$CFG->wwwroot}/stats/sessions.php">Sessions</a>
        <a class="button submenu-button menu-item" data-parent="stats" href="{$CFG->wwwroot}/stats/mvps.php">MVPs</a>
        <a class="button submenu-button menu-item" data-parent="stats" href="{$CFG->wwwroot}/stats/medals.php">Medals</a>
        <a class="button submenu-button menu-item" data-parent="stats" href="{$CFG->wwwroot}/stats/streaks.php">Streaks</a>
    </div>
</div>
HTML;

    // Menu for all other tables.
    } elseif (!str_contains($path, 'index')) {

        $menu .=  <<<HTML
    <div class="submenu">
        <a class="button submenu-button submenu-item" data-param="session" href="?time=session">Session</a>
        <a class="button submenu-button submenu-item" data-param="season" href="?time=season">Season</a>
        <a class="button submenu-button submenu-item" data-param="alltime" href="?time=alltime">Alltime</a>
    </div>
</div>
HTML;

    }

    return $menu;
}

/**
 * Print filter options dropdown.
 *
 * @param array $filter
 *
 * @return string $html
 */
function print_filter($filter) {

    $col = $filter['col'];
    if ($col == 'season') {
        $label = ucfirst($col);
    } else {
        $label = '';
    }

    $html = '<div class="table-filter">';
    $html .= '<div class="filter-arrow" id="filter-prev">&#9664;</div>';
    $html .= '<select id="table-filter" name="' . $col . '" onchange="tableFilter()">';
    foreach ($filter['options'] as $option) {
        $html .= '<option value="' . $option . '">' . "$label $option</option>";
    }
    $html .= "</select>";
    $html .= '<div class="filter-arrow" id="filter-next">&#9654;</div>';
    $html .= "</div>";

    return $html;
}

/**
 * Print footer.
 * @return string
 */
function print_footer() {
    return <<<HTML
<script src="/js/menu.js"></script>
<script src="/js/filter.js"></script>
</div> <!-- Close content -->
</body>
</html>
HTML;
}

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
 * Writes a line for detailled stats.
 *
 * @param string $title
 * @param array $value
 *
 * @return string
 */
function write_stats_detail_line($title, $values) {
    $string = '';
    $string .= '<div class="player-stats-details">';
    $string .= '<div class="stats-label">' . $title . ': </div>';
    foreach ($values as $value) {
        $value = (string) $value;
        $valueclass = '';
        $firstchar = $value[0];
        if ($firstchar == '+') {
            $valueclass = 'elo-gain';
        } elseif ($firstchar == '-') {
            $valueclass = 'elo-loss';
        }
        if (!strpos($value, 'player-name')) {
            $string .= '<div class="stats-value ' . $valueclass . '">' . $value . '</div>';
        } else {
            $string .= $value;
        }
    }
    $string .= '</div>';
    return $string;
}

/**
 * Formats a elo number.
 *
 * @param int elo
 *
 * @return string
 */
function format_elo($elo) {
    if ($elo > 0) {
        $elo = '<span class="elo-gain">+' . $elo . ' elo</span>';
    } elseif ($elo < 0) {
        $elo = '<span class="elo-loss">' . $elo . ' elo</span>';
    } else {
        $elo = '<span class="elo-neutral">+' . $elo . ' elo</span>';
    }
    return $elo;
}