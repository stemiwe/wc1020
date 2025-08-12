<?php
require_once __DIR__ . '/../lib/config.php';
echo html::menu();

// Save submenu selection.
$_SESSION['stats'] = basename(__FILE__);

?>

<!DOCTYPE html>

<table id="table">
  <thead>
    <tr>
      <th>Sai</th>
      <th>D</th>
      <th>Session</th>
      <th>Players</th>
      <th>Games</th>
      <th>MVP</th>
      <th>ELO</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $sql = "SELECT
        g.season,
        g.date,
        SUM(g.wg) AS total_wg,
        SUM(g.lg) AS total_lg,
        COUNT(*) AS game_count,
        GROUP_CONCAT(DISTINCT winner.p1) AS winner_p1_ids,
        GROUP_CONCAT(DISTINCT winner.p2) AS winner_p2_ids,
        GROUP_CONCAT(DISTINCT loser.p1) AS loser_p1_ids,
        GROUP_CONCAT(DISTINCT loser.p2) AS loser_p2_ids
        FROM games AS g
        LEFT JOIN teams AS winner ON g.winner = winner.id
        LEFT JOIN teams AS loser ON g.loser = loser.id
        GROUP BY g.date
        ORDER BY g.date DESC";
    $rows = $DB->query($sql)->fetchAll();

    foreach ($rows as $row) {

        // Prepare data.
        $date = $row['date'];
        $rows = $row['game_count'];
        $goals = $row['total_wg'] + $total_lg = $row['total_lg'];

        // Get total distinct players.
        $all_ids = array_merge(
            explode(',', $row['winner_p1_ids']),
            explode(',', $row['winner_p2_ids']),
            explode(',', $row['loser_p1_ids']),
            explode(',', $row['loser_p2_ids'])
        );
        $all_ids = array_filter($all_ids, function($id) {
            return !empty($id);
        });
        $players = count(array_unique($all_ids));

        // Get MVP.
        $medals = get_medals($date);

        echo '<tr>';
        echo '<td class="number-cell">' . $row['season'] . '</td>';
        echo '<td>' . get_weekday($date) . '</td>';
        echo '<td class="date-cell">' . "$date</td>";
        echo '<td class="number-cell">' . "$players</td>";
        echo '<td class="number-cell">' . "$rows</td>";
        echo '<td class="player-cell">';
        foreach ($medals['gold'] as $mvp) {
            $player = $mvp['player'];
            $elo = $mvp['elo'];
            echo write_player($player);
        }
        echo '</td>';
        echo '<td class="elo-cell elo-gain">+' . $elo . '</td>';
        echo '</tr>';
    }
    ?>
  </tbody>
</table>

<a class="button add-button" href="/addgame.php"></a>

<script>
$(document).ready(function() {
    var dtOptions = <?php echo json_encode($datatables_config); ?>;
    dtOptions.order = [[2, 'desc']];
    dtOptions.language.info = '_TOTAL_ sessions';
    $('#table').DataTable(dtOptions);
});
</script>

<?php echo html::footer();?>