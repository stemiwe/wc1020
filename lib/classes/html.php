<?php

/**
 * Functions that return HTML snippets.
 *
 */
class html {

    /**
     * Create header.
     * @return string
     */
    public static function header() {

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

HTML
. ($CFG->testsite ? "\n    <link rel=\"stylesheet\" href=\"{$CFG->wwwroot}/styles/testsite.css?v={$nocache}\">" : '') .
<<<HTML

    </head>
    <body>
        <div class="content">
    HTML;
    }

    /**
     * Create menu.
     * @return string
     */
    public static function menu() {

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
            <a class="button submenu-button menu-item" data-parent="stats" href="{$CFG->wwwroot}/stats/seasons.php">Seasons</a>
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
            HTML;

            if (str_contains($path, 'players.php') || str_contains($path, 'teams.php')) {
                $menu .= <<<HTML
                <a class="button submenu-button submenu-item" data-param="regulars" href="?time=regulars">Regulars</a>
                HTML;
            }

            $menu .= <<<HTML
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
    public static function filter($filter) {

        // Labels for season dropdown.
        $col = $filter['col'];
        if ($col == 'season') {
            $label = ucfirst($col);
        } else {
            $label = '';
        }

        // Dropdown.
        $html = '<div class="table-filter">';
        $html .= '<div class="filter-arrow" id="filter-prev">&#9664;</div>';
        $html .= '<select id="table-filter" name="' . $col . '" onchange="tableFilter()">';
        foreach ($filter['options'] as $option) {

            // Add weekday to session date.
            if ($col == 'date') {
                $label = get_weekday($option);
            }

            // Select default option.
            if ($option == $filter['default']) {
                $selected = ' selected';
            } else {
                $selected = '';
            }

            $html .= '<option ' . $selected . ' value="' . $option . '">' . "$label $option</option>";
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
    public static function footer() {
        return <<<HTML
    <script src="/js/menu.js"></script>
    <script src="/js/filter.js"></script>
    </div> <!-- Close content -->
    </body>
    </html>
    HTML;
    }

    /**
     * Writes a line for detailled stats.
     *
     * @param string $title
     * @param array $value
     *
     * @return string
     */
    public static function stats_detail_line($title, $values) {
        $string = '';
        if ($title) {
            $string .= '<div class="player-stats-details">';
            $string .= '<div class="stats-label">' . $title . ': </div>';
        } else {
            $string .= '<div class="player-stats-details no-title">';
        }
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
     * Prints a table.
     *
     * @param array $table
     *
     * @return string
     */
    public static function table($table, $id = 'table') {

        $html = '<table id="' . $id . '">';

        // thead.
        $html .= '<thead>';
        $html .= '<tr>';
        foreach ($table['cols'] as $col) {
            $html .= '<th>' . $col . '</th>';
        }
        $html .= '</tr>';
        $html .= '</thead>';

        // tbody.
        $html .= '<tbody>';
        foreach ($table['rows'] as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $class = $cell['class'] ?? '';
                $value = $cell['value'] ?? '';
                $html .= '<td class="' . $class . '">' . $value . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    /**
     * Prints a game row for the games table.
     *
     * @param array $game
     * @param bool $s_elo Whether to include sELO in the row.
     *
     * @return array $row
     */
    public static function game_row($game, $s_elo = false) {

        global $DB;

        $winnerid = $game["winner"];
        $loserid = $game["loser"];
        $winner = $DB->query("SELECT * FROM teams WHERE ID = $winnerid")->fetch();
        $loser = $DB->query("SELECT * FROM teams WHERE ID = $loserid")->fetch();

        $winnerp1_id = $winner['p1'];
        $winnerp2_id = $winner['p2'];
        $winner_p1 = $DB->query("SELECT * FROM players WHERE ID = $winnerp1_id")->fetch();
        $winner_p2 = $DB->query("SELECT * FROM players WHERE ID = $winnerp2_id")->fetch();

        $loserp1_id = $loser['p1'];
        $loserp2_id = $loser['p2'];
        $loser_p1 = $DB->query("SELECT * FROM players WHERE ID = $loserp1_id")->fetch();
        $loser_p2 = $DB->query("SELECT * FROM players WHERE ID = $loserp2_id")->fetch();

        $year = date('Y', $game['timestamp']);
        $date = date('m-d', $game['timestamp']);
        $time = date('H:i', $game['timestamp']);
        $elo_diff = $game['elo_diff'];
        $elofontsize = ELO::fontsize($elo_diff);
        $elocolor = ELO::color($elo_diff);
        $s_elo_diff = $game['s_elo_diff'];
        $s_elofontsize = ELO::fontsize($s_elo_diff);
        $s_elocolor = ELO::color($s_elo_diff);

        // Add to table.
        $row = [];
        $row[] = ['class' => 'date-cell', 'value' => "<div>$year</div><div>$date</div><div>$time</div>"];
        $row[] = ['class' => 'player-cell', 'value' => write_player($winner_p1). write_player($winner_p2)];
        $row[] = ['class' => 'gp-cell goal-cell', 'value' => $game['wg']];
        $row[] = ['class' => 'gm-cell goal-cell', 'value' => $game['lg']];
        $row[] = ['class' => 'player-cell', 'value' => write_player($loser_p1) . write_player($loser_p2)];
        if ($s_elo) {
            $row[] = ['class' => 'elo-cell', 'value' => "<span style='font-size: {$s_elofontsize}rem; color: {$s_elocolor};'>{$s_elo_diff}</span>"];
        } else {
            $row[] = ['class' => 'elo-cell', 'value' => "<span style='font-size: {$elofontsize}rem; color: {$elocolor};'>{$elo_diff}</span>"];
        }

        return $row;
    }

    /**
     * Prints an award.
     *
     * @param array $award
     *
     * @return string
     */
    public static function award($award) {

        global $DB;

        $p1 = $DB->get('players', '*', ['id' => $award['p1']]);
        $html = '<div class="award">';
        if (isset($award['season'])) {
            $html .= '<div class="award-season">Season ' . $award['season'] . '</div>';
        }
        $html .= '<div class="award-label">' . $award['label'] . '</div>';
        $html .= '<img class="award-img" src="/img/awards/' . $award['img'] . '.png" alt="' . $award['description'] . '">';
        $html .= '<div class="award-player">' . write_player($p1) . '</div>';
        if ($award['p2']) {
            $p2 = $DB->get('players', '*', ['id' => $award['p2']]);
            $html .= '<div class="award-player">' . write_player($p2) . '</div>';
        }
        $html .= '<div class="award-description">' . $award['description'] . '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Prints a player image.
     *
     * @param string $name
     *
     * @return string
     */
    public static function player_image($name) {

        global $CFG;

        // Check if player image exists.
        $name = strtolower( $name);
        $imgpath = '/img/avatars/';
        $extensions = ['.png', '.jpg', '.jpeg', '.gif', '.webp', '.svg'];
        $img = null;
        foreach ($extensions as $ext) {
            $fullimgpath = __DIR__ . '/../../img/avatars/' . $name . $ext;
            if (file_exists($fullimgpath)) {
                $img = $imgpath . $name . $ext;
                break;
            }
        }

        if ($img) {
            return '<img class="player-image" src="' . $img . '" alt="' . $name . '">';
        } else {
            return null;
        }
    }
}