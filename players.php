<?php
require_once __DIR__ . '/lib/config.php';
echo print_menu();

// Get filter vars.
$filter = get_timefilter();
$usefilter = false;
if (isset($filter['sql'])) {
    $usefilter = true;
    echo print_filter($filter);
}

// Define table columns.
$table['cols'] = ['Name'];
if ($filter['col'] != 'date') {
    $table['cols'][] = 'S';
}
$table['cols'][] = 'G';
$table['cols'][] = 'W';
$table['cols'][] = 'L';
if ($filter['col'] == 'date') {
    $table['cols'][] = 'G+';
    $table['cols'][] = 'G-';
    $orderby = 6;
} else {
    $table['cols'][] = 'W%';
    $table['cols'][] = 'G+/-';
    $orderby = 7;
}
$table['cols'][] = 'ELO';

// Table rows.
$table['rows'] = [];
$players = $DB->query("SELECT * FROM players")->fetchAll();
foreach ($players as $player) {

    $player_id = $player['id'];
    $team_ids = get_team_ids($player_id);
    $wins = get_wins($team_ids);
    $losses = get_losses($team_ids);
    $sessions = get_player_sessions($player_id, false);

    $gp = 0;
    $gm = 0;
    $elo_diff = 0;
    foreach ($wins as $key => $win) {

        // Filter - ToDo: move to SQL query.
        if ($usefilter) {
            $value = $filter['default'];
            $col = $filter['col'];
            if ($win[$col] != $value) {
                unset($wins[$key]);
                continue;
            }
        }

        $gp += $win['wg'];
        $gm += $win['lg'];
        $elo_diff += $win['elo_diff'];
    }

    foreach ($losses as $key => $loss) {

        // Filter - ToDo: move to SQL query.
        if ($usefilter) {
            $value = $filter['default'];
            $col = $filter['col'];
            if ($loss[$col] != $value) {
                unset($losses[$key]);
                continue;
            }
        }

        $gm += $loss['wg'];
        $gp += $loss['lg'];
        $elo_diff -= $loss['elo_diff'];
    }

    $win = count($wins);
    $loss = count($losses);
    $games = $win + $loss;
    $elo = $player['elo'];
    $elo_class = 'elo-loss';
    if ($elo_diff > 0) {
        $elo_diff = "+$elo_diff";
        $elo_class = 'elo-gain';
    }

    // Skip players that didnt compete this session/season.
    if ($games == 0) {
        continue;
    }

    // Add to table.
    $row = [];
    $row[] = ['class' => 'player-cell', 'value' => write_player($player)];
    if ($filter['col'] != 'date') {
        $row[] = ['class' => 'number-cell', 'value' => $sessions];
    }
    $row[] = ['class' => 'number-cell', 'value' => $games];
    $row[] = ['class' => 'number-cell', 'value' => $win];
    $row[] = ['class' => 'number-cell', 'value' => $loss];
    if ($filter['col'] == 'date') {
        $row[] = ['class' => 'gp-cell', 'value' => $gp];
        $row[] = ['class' => 'gm-cell', 'value' => $gm];
    } else {
        $win_percentage = $games > 0 ? number_format(($win / $games) * 100, 1) . '%' : '0%';
        $row[] = ['class' => 'number-cell ', 'value' => $win_percentage];
        $avg_goals = number_format(($gp - $gm) / $games, 2);
        $row[] = ['class' => 'number-cell' , 'value' => $avg_goals];
    }
    if ($usefilter) {
        $row[] = ['class' => 'elo-cell ' . $elo_class, 'value' => $elo_diff];
    } else {
        $row[] = ['class' => 'number-cell', 'value' => $elo];
    }
    $table['rows'][] = $row;
}

echo print_table($table);

?>

<a class="button add-button" href="./addplayer.php"></a>

<script>
$(document).ready(function() {
    var dtOptions = <?php echo json_encode($datatables_config); ?>;
    dtOptions.order = [[<?php echo $orderby ?>, 'desc']];
    dtOptions.language.info = '_TOTAL_ players';
    $('#table').DataTable(dtOptions);
});
</script>

<?php echo print_footer();?>