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
      <th class="gold">Gold</th>
      <!-- <th class="gold">ELO</th>       -->
      <th class="silver">Silver</th>
      <!-- <th class="silver">ELO</th>       -->
      <th class="bronze">Bronze</th>
      <!-- <th class="bronze">ELO</th>       -->
    </tr>
  </thead>
  <tbody>
    <?php
    $sql = "SELECT DISTINCT date FROM games";
    $dates = $DB->query($sql)->fetchAll();

    $sql = "SELECT * FROM players";
    $players = $DB->query($sql)->fetchAll();
    $medals =[];

    // Accumulate medals.
    foreach ($dates as $date) {
        $mvp_stats = get_medals($date['date']);
        $goldid = $mvp_stats[0]['player']['id'];
        $medals[$goldid]['gold'] = isset($medals[$goldid]['gold']) ? $medals[$goldid]['gold'] + 1 : 1;
        $silverid = $mvp_stats[1]['player']['id'];
        $medals[$silverid]['silver'] = isset($medals[$silverid]['silver']) ? $medals[$silverid]['silver'] + 1 : 1;
        $bronzeid = $mvp_stats[2]['player']['id'];
        $medals[$bronzeid]['bronze'] = isset($medals[$bronzeid]['bronze']) ? $medals[$bronzeid]['bronze'] + 1 : 1;
    }

    foreach ($players as $player) {
        $playerid = $player['id'];
        $gold = isset($medals[$playerid]['gold']) ? $medals[$playerid]['gold'] : 0;
        $silver = isset($medals[$playerid]['silver']) ? $medals[$playerid]['silver'] : 0;
        $bronze = isset($medals[$playerid]['bronze']) ? $medals[$playerid]['bronze'] : 0;

        // Skip players with no medals.
        if (($gold + $silver + $bronze) == 0) {
            continue;
        }

        echo '<tr>';
        echo '<td class="player-cell">' . write_player($player) . '</td>';
        echo '<td class="number-cell">' . $gold . '</td>';
        echo '<td class="number-cell">' . $silver . '</td>';
        echo '<td class="number-cell">' . $bronze . '</td>';
        echo '</tr>';
    }
    ?>
  </tbody>
</table>

<a class="button add-button" href="/addgame.php"></a>

<script>
$(document).ready(function() {
    var dtOptions = <?php echo json_encode($datatables_config); ?>;
    dtOptions.order = [[1, 'desc'],[2, 'desc'], [3, 'desc']];
    dtOptions.language.info = '_TOTAL_ players';
    $('#table').DataTable(dtOptions);
});
</script>

<?php echo print_footer();?>