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
      <th>Session</th>
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
    $rows = $DB->query($sql)->fetchAll();

    foreach ($rows as $row) {

        // Prepare data.
        $date = $row['date'];

        // Medals.
        $medals = get_medals($date);

        echo '<tr>';
        echo '<td class="date-cell">' . "$date</td>";
        echo '<td class="player-cell">';
        foreach ($medals['gold'] as $medal) {
            echo write_player($medal['player']);
        }
        echo '</td><td class="player-cell">';
        foreach ($medals['silver'] as $medal) {
            echo write_player($medal['player']);
        }
        echo '</td><td class="player-cell">';
        foreach ($medals['bronze'] as $medal) {
            echo write_player($medal['player']);
        }
        echo '</td></tr>';
    }
    ?>
  </tbody>
</table>

<a class="button add-button" href="/addgame.php"></a>

<script>
$(document).ready(function() {
    var dtOptions = <?php echo json_encode($datatables_config); ?>;
    dtOptions.order = [[0, 'desc']];
    $('#table').DataTable(dtOptions);
});
</script>

<?php echo print_footer();?>