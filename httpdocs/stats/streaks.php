<?php
require_once __DIR__ . '/../lib/config.php';
echo print_menu();

// Save submenu selection.
$_SESSION['stats'] = basename(__FILE__);

?>

<!DOCTYPE html>

<table id="table">
  <thead>
    <tr>
      <th>Player</th>
      <th>Wins</th>
      <th>started</th>
      <th>ended</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $sql = "SELECT * FROM stats
            JOIN players ON stats.entity_id = players.id
            WHERE stats.type = 'player_streak'
            AND stats.value > 2
            ORDER BY stats.value DESC";

    $stats = $DB->query($sql)->fetchAll();

    foreach ($stats as $stat) {

        // Prepare data.
        $player = ['id' => $stat['entity_id'],
                   'name' => $stat['name'],
                   'bg' => $stat['bg'],
                   'color' => $stat['color']
                  ];
        $games = $stat['value'];
        $start = $stat['start'];
        $startdate = date('Y-m-d', $start);
        $starttime = date('H:i', $start);
        $startstring = "<div>$startdate</div><div>$starttime</div>";
        if (!$stat['end']) {
            $endstring = 'ongoing!';
        } else{
            $end = $stat['end'];
            $enddate = date('Y-m-d', $end);
            $endtime = date('H:i', $end);
            $endstring = "<div>$enddate</div><div>$endtime</div>";
        }

        // Write table.
        echo '<tr>';
        echo '<td class="player-cell">' . write_player($player) . '</td>';
        echo '<td class="number-cell">' . "$games</td>";
        echo '<td class="date-cell date-cell-2">' . "$startstring</td>";
        echo '<td class="date-cell date-cell-2">' . "$endstring</td>";
        echo '</tr>';
    }
    ?>
  </tbody>
</table>

<a class="button add-button" href="/addgame.php"></a>

<script>
$(document).ready(function() {
    var dtOptions = <?php echo json_encode($datatables_config); ?>;
    dtOptions.order = [[1, 'desc']];
    dtOptions.language.info = '_TOTAL_ streaks';
    $('#table').DataTable(dtOptions);
});
</script>

<?php echo print_footer();?>