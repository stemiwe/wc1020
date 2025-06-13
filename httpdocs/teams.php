<?php
require_once __DIR__ . '/lib/config.php';
echo print_menu();
?>

<!DOCTYPE html>

<table id="table">
  <thead>
    <tr>
      <th>Team</th>
      <th>G</th>
      <th>W</th>
      <th>L</th>
      <th>G+</th>
      <th>G-</th>
      <th>ELO</th>
    </tr>
  </thead>
  <tbody>
    <?php

    // Get filter vars.
    $filter = get_timefilter();
    $usefilter = false;
    if (isset($filter['sql'])) {
        $usefilter = true;
        echo print_filter($filter);
    }

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
            $elo = $elo + $win['elo_diff'];
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
            $elo = $elo - $loss['elo_diff'];
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
        if ($games == 0) {
            continue;
        }

        echo '<tr>';
        echo '<td class="player-cell">' . write_player($p1). write_player($p2) . '</td>';
        echo '<td class="number-cell">' . $games . '</td>';
        echo '<td class="number-cell">' . $win . '</td>';
        echo '<td class="number-cell">' . $loss . '</td>';
        echo '<td class="gp-cell">' . $gp . '</td>';
        echo '<td class="gm-cell">' . $gm . '</td>';
        echo '<td class="elo-cell ' . $elo_class . '">' . $elo . '</td>';
        echo '</tr>';
    }
    ?>
  </tbody>
</table>

<script>
$(document).ready(function() {
    var dtOptions = <?php echo json_encode($datatables_config); ?>;
    dtOptions.order = [[6, 'desc']];
    dtOptions.language.info = '_TOTAL_ teams';
    $('#table').DataTable(dtOptions);
});
</script>

<?php echo print_footer();?>