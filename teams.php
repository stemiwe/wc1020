<?php
require_once __DIR__ . '/lib/config.php';
echo html::menu();

// Get filter vars.
$filter = get_timefilter();
$usefilter = false;
if (isset($filter['sql'])) {
    $usefilter = true;
    echo html::filter($filter);
}

// Define table columns.
$table['cols'] = [
    'Name',
    'G',
    'W',
    'L'
];
if ($filter['col'] == 'date') {
    $table['cols'][] = 'G+';
    $table['cols'][] = 'G-';
} else {
    $table['cols'][] = 'W%';
    $table['cols'][] = 'G+/-';
}
if ($filter['col'] == 'season') {
    $table['cols'][] = 'sELO';
    $elokey = 's_elo_diff';
} else {
    $table['cols'][] = 'ELO';
    $elokey = 'elo_diff';
}

// Regulars only?
if ($filter['filter'] == 'regulars') {
    $gamecount = $DB->count('games');
    $cutoff = $gamecount / 25;
}

// Table rows.
$teams = $DB->query("SELECT * FROM teams")->fetchAll();
$players = $DB->query("SELECT * FROM players")->fetchAll();
$players = array_column($players, null, 'id');
foreach ($teams as $team) {

    $p1_id = $team['p1'];
    $p2_id = $team['p2'];
    $team_id = [$team['id']];
    $p1 = $players[$p1_id];
    $p2 = $players[$p2_id];

    $wins = get_wins($team_id);
    $losses = get_losses($team_id);

    $gp = 0;
    $gm = 0;
    $elo = 0;
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
        $elo = $elo + $win[$elokey];
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
        $elo = $elo - $loss[$elokey];
    }

    $win = count($wins);
    $loss = count($losses);
    $games = $win + $loss;
    $elo_class = 'elo-loss';
    if ($elo > 0) {
        $elo = "+$elo";
        $elo_class = 'elo-gain';
    }

    // Skip teams that have no games in this session/season.
    if ($games == 0 || (isset($cutoff) && $games < $cutoff)) {
        continue;
    }

    // Add to table.nein
    $row = [];
    $row[] = ['class' => 'player-cell', 'value' => write_player($p1) . write_player($p2)];
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
        $row[] = ['class' => 'number-cell', 'value' => $avg_goals];
    }
    $row[] = ['class' => 'elo-cell ' . $elo_class, 'value' => $elo];
    $table['rows'][] = $row;
}

echo html::table($table);

?>

<script>
$(document).ready(function() {
    var dtOptions = <?php echo json_encode($datatables_config); ?>;
    dtOptions.order = [[6, 'desc']];
    dtOptions.language.info = '_TOTAL_ teams';
    $('#table').DataTable(dtOptions);
});
</script>

<?php echo html::footer();?>