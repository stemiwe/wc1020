<?php
require_once __DIR__ . '/lib/config.php';
echo html::menu();

// Get filter vars.
$filter = get_timefilter(false);
$usefilter = false;
if (isset($filter['sql'])) {
    $usefilter = true;
    echo html::filter($filter);
}

// Define table columns.
$table['cols'] = ['Date',
                  'Winner',
                  'G+',
                  'G-',
                  'Loser',
                  'ELO'];


// Build SQL.
$sql = "SELECT * FROM games ";
if ($usefilter) {
    $sql .= $filter['sql'];
}
$sql .= " ORDER BY timestamp DESC";
$games = $DB->query($sql)->fetchAll();

// Table rows.
$table['rows'] = [];
foreach ($games as $game) {
    $row = html::game_row($game);
    $table['rows'][] = $row;
}

echo html::table($table);

?>

<a class="button add-button" href="./addgame.php"></a>
<a class="button remove-button" href="./removegame.php"></a>

<script>
$(document).ready(function() {
    var dtOptions = <?php echo json_encode($datatables_config); ?>;
    dtOptions.order = [[0, 'desc']];
    dtOptions.language.info = '_TOTAL_ games';
    $('#table').DataTable(dtOptions);
});
</script>

<?php echo html::footer();?>