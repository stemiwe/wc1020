<?php
require_once 'config.php';
echo menu();
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
    $teams = $DB->query("SELECT * FROM teams")->fetchAll();        
    $players = $DB->query("SELECT * FROM players")->fetchAll();      
    $players = array_column($players, null, 'id');    

    foreach ($teams as $team) {
        echo '<tr>';
        $p1_id = $team['p1'];
        $p2_id = $team['p2'];
        $team_ids = [$team['id']];
        $p1 = $players[$p1_id];
        $p2 = $players[$p2_id];
        echo '<td class="player-cell">' . write_player($p1). write_player($p2) . '</td>';                                             
        
        $wins = get_wins($team_ids);        
        $losses = get_losses($team_ids);
        
        $gp = 0;
        $gm = 0;
        foreach ($wins as $win) {
            $gp += $win['wg'];
            $gm += $win['lg'];
        }
        
        foreach ($losses as $loss) {
            $gm += $loss['wg'];
            $gp += $loss['lg'];
        }

        $win = count($wins);
        $loss = count($losses);
        $games = $win + $loss;
        
        $elo = round(($p1['elo'] + $p2['elo']) / 2, 0);        

        echo '<td class="number-cell">' . $games . '</td>';
        echo '<td class="number-cell">' . $win . '</td>';
        echo '<td class="number-cell">' . $loss . '</td>';
        echo '<td class="gp-cell">' . $gp . '</td>';
        echo '<td class="gm-cell">' . $gm . '</td>';
        echo '<td class="number-cell">' . $elo . '</td>';
        echo '</tr>';
    }
    ?>
  </tbody>
</table>

<script>
  $(document).ready(function() {
    $('#table').DataTable({
      pageLength: 25,
      order: [[6, 'desc']],
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