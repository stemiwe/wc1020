<?php
require_once __DIR__ . '/../lib/config.php';
echo print_menu();

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
        $mvp_stats = get_medals($date);          
        $gold = $mvp_stats[0]['player'];
        $gold_elo = $mvp_stats[0]['elo'];
        $silver = $mvp_stats[1]['player'];
        $silver_elo = $mvp_stats[1]['elo'];
        $bronze = $mvp_stats[2]['player'];
        $bronze_elo = $mvp_stats[2]['elo'];
        
        echo '<tr>';
        echo '<td class="date-cell">' . "$date</td>";                
        echo '<td class="player-cell">' . write_player($gold) . '</td>';                                                
        // echo '<td class="elo-cell elo-gain">+' . $gold_elo . '</td>';                                                
        echo '<td class="player-cell">' . write_player($silver) . '</td>';                                                
        // echo '<td class="elo-cell elo-gain">+' . $silver_elo . '</td>';                                                
        echo '<td class="player-cell">' . write_player($bronze) . '</td>';                                                
        // echo '<td class="elo-cell elo-gain">+' . $bronze_elo . '</td>';                                                
        echo '</tr>';
    }
    ?>
  </tbody>
</table>

<a class="button add-button" href="/addgame.php">+</a>

<script>
$(document).ready(function() {    
    var dtOptions = <?php echo json_encode($datatables_config); ?>;
    dtOptions.order = [[0, 'desc']];
    $('#table').DataTable(dtOptions);
});
</script>

<?php echo print_footer();?>