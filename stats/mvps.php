<?php
require_once __DIR__ . '/../lib/config.php';
echo print_menu();

// Save submenu selection.
$_SESSION['stats'] = basename(__FILE__);

// Define table columns.
$table['cols'] = [
    'Session',
    'Gold',
    'Silver',
    'Bronze'
];

// Get all thursdays.
$sql = "SELECT DISTINCT date FROM games WHERE day = 'Do'";
$games = $DB->query($sql)->fetchAll();
foreach ($games as $game) {

    // Prepare data.
    $date = $game['date'];

    // Medals.
    $medals = get_medals($date);

    $gold = '';
    foreach ($medals['gold'] as $medal) {
        $gold .= write_player($medal['player']);
    }
    $silver = '';
    foreach ($medals['silver'] as $medal) {
        $silver .= write_player($medal['player']);
    }
    $bronze = '';
    foreach ($medals['bronze'] as $medal) {
        $bronze .= write_player($medal['player']);
    }

    $row = [];
    $row[] = ['class' => 'date-cell', 'value' => $date];
    $row[] = ['class' => 'player-cell', 'value' => $gold];
    $row[] = ['class' => 'player-cell', 'value' => $silver];
    $row[] = ['class' => 'player-cell', 'value' => $bronze];
    $rows[] = $row;
    $table['rows'][] = $row;
}

echo print_table($table);

?>

<a class="button add-button" href="/addgame.php"></a>

<script>
$(document).ready(function() {
    var dtOptions = <?php echo json_encode($datatables_config); ?>;
    dtOptions.order = [[0, 'desc']];
    $('#table').DataTable(dtOptions);
});
</script>

<?php echo print_footer();?>