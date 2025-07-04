<?php
require_once __DIR__ . '/lib/config.php';
echo print_menu();

?>

<!DOCTYPE html>

<table id="table">
  <thead>
    <tr>
      <th>Name</th>
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

    // Table.
    $players = $DB->query("SELECT * FROM players")->fetchAll();
    foreach ($players as $player) {

        $player_id = $player['id'];
        $team_ids = get_team_ids($player_id);
        $wins = get_wins($team_ids);
        $losses = get_losses($team_ids);

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

        echo '<tr>';
        echo '<td class="player-cell">' . write_player($player) . '</td>';
        echo '<td class="number-cell">' . $games . '</td>';
        echo '<td class="number-cell">' . $win . '</td>';
        echo '<td class="number-cell">' . $loss . '</td>';
        echo '<td class="gp-cell">' . $gp . '</td>';
        echo '<td class="gm-cell">' . $gm . '</td>';
        if ($usefilter) {
            echo '<td class="elo-cell ' . $elo_class . '">' . $elo_diff . '</td>';
        } else {
            echo '<td class="number-cell">' . $elo . '</td>';
        }
        echo '</tr>';
    }
    ?>
  </tbody>
</table>

<a class="button add-button" href="./addplayer.php"></a>

<script>
$(document).ready(function() {
    var dtOptions = <?php echo json_encode($datatables_config); ?>;
    dtOptions.order = [[6, 'desc']];
    dtOptions.language.info = '_TOTAL_ players';
    $('#table').DataTable(dtOptions);
});
</script>

<?php echo print_footer();?>