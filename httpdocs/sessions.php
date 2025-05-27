<?php
require_once 'config.php';
echo menu();
echo "TBA";
die();
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
      <th>W%</th>
    </tr>
  </thead>
  <tbody>
    <?php    
    $players = $DB->query("SELECT * FROM players")->fetchAll();        

    foreach ($players as $player) {
        echo '<tr>';
        echo '<td>' . write_player($player) . '</td>';                
        $games = 0;
        $win = 0;
        $loss = 0;
        $gp = 0;
        $gm = 0;
        $wp = 0;        
        echo '<td class="number-cell">' . $games . '</td>';
        echo '<td class="number-cell">' . $win . '</td>';
        echo '<td class="number-cell">' . $loss . '</td>';
        echo '<td class="gp-cell">' . $gp . '</td>';
        echo '<td class="gm-cell">' . $gm . '</td>';
        echo '<td class="number-cell">' . $wp . '</td>';
        echo '</tr>';
    }
    ?>
  </tbody>
</table>

<div class="footer">
  <a class="button" href="/addplayer.php">Add Player</a>
</div>

<script>
  $(document).ready(function() {
    $('#table').DataTable({
      pageLength: 25
    });
  });
</script>

<?php echo print_footer();?>