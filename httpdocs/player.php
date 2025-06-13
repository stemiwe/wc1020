<?php
require_once __DIR__ . '/lib/config.php';

// Get returnurl.
if (isset($_GET['returnto'])) {
    $returnurl = $_GET['returnto'];
} else {
    $returnurl = '/players.php';
}

// Get player.
$playerid = $_GET['id'];
$player = $DB->query("SELECT * FROM players where id = $playerid")->fetch();

// Get teams.
$sql = "SELECT * from teams WHERE p1 = $playerid OR p2 = $playerid";
$teams = $DB->query($sql)->fetchAll();

// Get partners.
$partners = [];
foreach ($teams as $team) {
    $teamid = $team['id'];
    $partner = [];
    if ($team['p1'] == $playerid) {
        $partner['id'] = $team['p2'];
    } else {
        $partner['id'] = $team['p1'];
    }
    $partner['g'] = 0;  // games.
    $partner['w'] = 0;  // won.
    $partner['l'] = 0;  // lost.
    $partner['gp'] = 0; // goals plus.
    $partner['gm'] = 0; // goals minus.
    $partner['elo'] = 0;
    $partners[$teamid] = $partner;
}

// Data for elo graph.
$elograph = [];
$elo = 1000;
$form = [];

// Get wins for each partner.
foreach ($partners as $teamid => $partner) {

    // Get wins.
    $sql = "SELECT * from games WHERE winner = $teamid OR loser = $teamid ORDER BY date ASC";
    $games = $DB->query($sql)->fetchAll();
    foreach ($games as $game) {
        if ($game['winner'] == $teamid) {
            $partner['g']++;
            $partner['w']++;
            $partner['gp'] += $game['wg'];
            $partner['gm'] += $game['lg'];
            $partner['elo'] += $game['elo_diff'];
            $elo += $game['elo_diff'];
            $form[] = 'W';
        } else {
            $partner['g']++;
            $partner['l']++;
            $partner['gp'] += $game['lg'];
            $partner['gm'] += $game['wg'];
            $partner['elo'] -= $game['elo_diff'];
            $elo -= $game['elo_diff'];
            $form[] = 'L';

        }
        $elograph[] = $elo;
    }
    $partners[$teamid] = $partner;
}

// Sort by game.
usort($partners, function($a, $b) {
    return $b['g'] - $a['g']; // For descending order
});

// Reverse form array.
$form = array_reverse($form);

// Get player vars.
$name = $player['name'];
$bg = $player['bg'];
$color = $player['color'];
$graphcolor = '"' . $color .'"';

?>
<!DOCTYPE html>
<?php

echo '<div class="player-header" style="color: ' . $color . '; background-color: ' . $bg . ';">';
echo '<div class="player-header-name">' . $name . '</div>';
echo '<a href="' . $returnurl . '" class="close-button"></a>';
echo '</div>';

// Form.
echo '<div class="player-section">';
echo '<h2 class="player-sub-header">Last 10 games</h2>';
echo '<div class="player-form">';
$i = 0;
foreach ($form as $f) {
    echo '<div class="form-' . strtolower($f) . '">' . $f . '</div>';
    $i++;
    if ($i > 9) {
        break;
    }
}
echo '</div>';
echo '</div>';

// Graph for elo.
echo '<div class="player-section odd">';
echo '<h2 class="player-subheader">ELO Progression</h2>';
echo '<div class="chart-container">';
echo '<canvas id="elo-chart"></canvas>';

?>
<!-- <script src="/js/ext/chart.min.js"> </script> -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const data = {
    labels: <?php echo json_encode(array_keys($elograph)); ?>,
    datasets: [{
      label: 'ELO progression',
      data: <?php echo json_encode($elograph); ?>,
      borderColor: '<?php echo $color;?>',
      backgroundColor: '<?php echo $bg;?>',
      borderWidth: 5,
      pointRadius: 0,
      fill: true
    }]
  };

  const config = {
    type: 'line',
    data: data,
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: false // Hides the dataset label ("ELO progression")
        }
      },
      scales: {
        x: {
            color: 'white',
            ticks: {
              display: false
            },
            grid: {
              display: false
            },
            title: {
              display: false
            }
        },
        y: {
            ticks: {
              display: false,
            },
            grid: {
            display: false // Hide Y-axis grid lines
            },
            title: {
            display: false // Hide Y-axis label
            }
        }
      }
    }
  };

  new Chart(
    document.getElementById('elo-chart'),
    config
  );
</script>
</div>
</div>

<!-- Table of partners -->
<div class="player-section">
    <h2 class="player-sub-header">Partners</h2>
    <table id="table">
    <thead>
        <tr>
        <th>Partner</th>
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

        foreach ($partners as $partner) {

            $partnerid = $partner['id'];
            $player = $DB->query("SELECT * FROM players where id = $partnerid")->fetch();
            $elo = $partner['elo'];
            $elo_class = 'elo-loss';
            if ($elo > 0) {
                $elo = "+$elo";
                $elo_class = 'elo-gain';
            }

            echo '<tr>';
            echo '<td class="player-cell">' . write_player($player) . '</td>';
            echo '<td class="number-cell">' . $partner['g'] . '</td>';
            echo '<td class="number-cell">' . $partner['w'] . '</td>';
            echo '<td class="number-cell">' . $partner['l'] . '</td>';
            echo '<td class="gp-cell">' . $partner['gp'] . '</td>';
            echo '<td class="gm-cell">' . $partner['gm'] . '</td>';
            echo '<td class="elo-cell ' . $elo_class . '">' . $elo . '</td>';
            echo '</tr>';
        }
        ?>
    </tbody>
    </table>
</div>
<a class="back-button" href="<?php echo $returnurl?>">Back</a>

<script>
$(document).ready(function() {
    var dtOptions = <?php echo json_encode($datatables_config); ?>;
    dtOptions.order = [[2, 'desc']];
    dtOptions.language.info = '_TOTAL_ partners';
    $('#table').DataTable(dtOptions);
});
</script>

<?
echo print_footer();