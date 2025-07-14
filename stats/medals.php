<?php
require_once __DIR__ . '/../lib/config.php';
echo print_menu();

// Save submenu selection.
$_SESSION['stats'] = basename(__FILE__);

// Define table columns.
$table['cols'] = [
    'Player',
    'Sess',
    'Gold&nbsp;&nbsp;',
    'Silver',
    'Bronze',
    'Points'
];

// Get all thursdays.
$sql = "SELECT DISTINCT date FROM games WHERE day = 'Do'";
$dates = $DB->query($sql)->fetchAll();

// Get all players.
$sql = "SELECT * FROM players";
$players = $DB->query($sql)->fetchAll();
$medals =[];

// Accumulate medals.
foreach ($dates as $date) {
    $date_medals = get_medals($date['date']);
    foreach ($date_medals as $key => $playerarray) {
        foreach ($playerarray as $playerdata) {
            $playerid = $playerdata['player']['id'];
            $medals[$playerid][$key] = isset($medals[$playerid][$key]) ? $medals[$playerid][$key] + 1 : 1;
        }
    }
}

// Write table.
foreach ($players as $player) {
    $row = [];
    $playerid = $player['id'];
    $gold = isset($medals[$playerid]['gold']) ? $medals[$playerid]['gold'] : 0;
    $silver = isset($medals[$playerid]['silver']) ? $medals[$playerid]['silver'] : 0;
    $bronze = isset($medals[$playerid]['bronze']) ? $medals[$playerid]['bronze'] : 0;

    // Skip players with no medals.
    if (($gold + $silver + $bronze) == 0) {
        continue;
    }

    $row[] = ['class' => 'player-cell', 'value' => write_player($player)];
    $row[] = ['class' => 'number-cell', 'value' => get_player_sessions($playerid, 'sessions')];
    $row[] = ['class' => 'number-cell', 'value' => $gold];
    $row[] = ['class' => 'number-cell', 'value' => $silver];
    $row[] = ['class' => 'number-cell', 'value' => $bronze];
    $row[] = ['class' => 'number-cell', 'value' => $gold * 3 + $silver * 2 + $bronze];
    $rows[] = $row;
    $table['rows'][] = $row;
}

echo print_table($table);

?>

<a class="button add-button" href="/addgame.php"></a>

<script>
$(document).ready(function() {
    var dtOptions = <?php echo json_encode($datatables_config); ?>;
    dtOptions.order = [[5, 'desc']];
    dtOptions.language.info = '_TOTAL_ players';
    $('#table').DataTable(dtOptions);
});
</script>

<?php echo print_footer();?>