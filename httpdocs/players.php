<?php
require_once 'config.php';
echo menu();
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
    $players = $DB->query("SELECT * FROM players")->fetchAll();        

    foreach ($players as $player) {
        echo '<tr>';
        echo '<td class="player-cell">' . write_player($player) . '</td>';                        
        $player_id = $player['id'];
        $team_ids = get_team_ids($player_id);                
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
        
        $elo = $player['elo'];        

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

<a class="button add-button" href="/addplayer.php">+</a>

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