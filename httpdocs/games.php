<?php
require_once 'config.php';
echo menu();
?>

<!DOCTYPE html>

<table id="table">
  <thead>
    <tr>
      <th>Date</th>            
      <th>Winner</th>                  
      <th>G+</th>      
      <th>G-</th>             
      <th>Loser</th>                  
    </tr>
  </thead>
  <tbody>    
    <?php    
    $games = $DB->query("SELECT * FROM games ORDER BY timestamp DESC")->fetchAll();        

    foreach ($games as $game) {
        $winnerid = $game["winner"];
        $loserid = $game["loser"];
        $winner = $DB->query("SELECT * FROM teams WHERE ID = $winnerid")->fetch();        
        $loser = $DB->query("SELECT * FROM teams WHERE ID = $loserid")->fetch();              

        $winnerp1_id = $winner['p1'];
        $winnerp2_id = $winner['p2'];
        $winner_p1 = $DB->query("SELECT * FROM players WHERE ID = $winnerp1_id")->fetch();        
        $winner_p2 = $DB->query("SELECT * FROM players WHERE ID = $winnerp2_id")->fetch();        

        $loserp1_id = $loser['p1'];
        $loserp2_id = $loser['p2'];
        $loser_p1 = $DB->query("SELECT * FROM players WHERE ID = $loserp1_id")->fetch();        
        $loser_p2 = $DB->query("SELECT * FROM players WHERE ID = $loserp2_id")->fetch();        

        $year = date('Y', $game['timestamp']);
        $date = date('m-d', $game['timestamp']);
        $time = date('H:i', $game['timestamp']);
        
        echo '<tr>';
        echo '<td class="date-cell">' . "<div>$year</div><div>$date</div><div>$time</div></td>";                
        echo '<td class="player-cell">' . write_player($winner_p1). write_player($winner_p2) . '</td>';                                     
        echo '<td class="gp-cell goal-cell">' . $game['wg'] . '</td>';
        echo '<td class="gm-cell goal-cell">' . $game['lg'] . '</td>';
        echo '<td class="player-cell">' . write_player($loser_p1) . write_player($loser_p2) . '</td>';       
        echo '</tr>';
    }
    ?>
  </tbody>
</table>

<a class="button add-button" href="/addgame.php">+</a>

<script>
  $(document).ready(function() {
    $('#table').DataTable({
      pageLength: 25,      
      order: [[0, 'desc']],
      columnDefs: [
        {
          targets: '_all',
          orderSequence: ['desc', 'asc'] // first click = DESC
        }
      ],
      language: {
        lengthMenu: "_MENU_ #"
      }
    });
  });
</script>

<?php echo print_footer();?>