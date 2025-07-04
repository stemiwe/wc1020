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
$sql = "SELECT * FROM teams WHERE p1 = $playerid OR p2 = $playerid";
$teams = $DB->query($sql)->fetchAll();
$teamids = [];
foreach ($teams as $team) {
    $teamids[] = $team['id'];
}
$teamids_sql = '(' . implode(',', $teamids) . ')';

// Get games.
$sql = "SELECT * FROM games WHERE winner IN $teamids_sql OR loser IN $teamids_sql";
$games = $DB->query($sql)->fetchAll();

// Initialize game variables.
$elograph = [];
$elo = 1000;
$form = [];
$partners = [];
$opponents = [];
$gs = 0;
$wins = 0;
$losses = 0;
$wgs = 0;
$lgs = 0;
$sessions = [];

// Get data from games.
foreach ($games as $game) {

    $gs++;
    $sessions[$game['date']] = 1;

    // Get teams.
    $winnerteam = $DB->query("SELECT * FROM teams WHERE id = " . $game['winner'])->fetch();
    $loserteam = $DB->query("SELECT * FROM teams WHERE id = " . $game['loser'])->fetch();

    // Won.
    if (in_array($game['winner'], $teamids)) {

        // Game stats.
        $form[] = 'W';
        $elo = $elo + $game['elo_diff'];
        $wgs += $game['wg'] - $game['lg'];
        $wins ++;

        // Get partners and opponents.
        if ($winnerteam['p1'] == $playerid) {
            $partnerid = $winnerteam['p2'];
        } else {
            $partnerid = $winnerteam['p1'];
        }
        $opponentids = [$loserteam['p1'], $loserteam['p2']];

        // Partner.
        $partner = $partners[$partnerid] ?? [
            'g'  => 0,  // games.
            'w'  => 0,  // won.
            'l'  => 0,  // lost.
            'gp' => 0,  // goals plus.
            'gm' => 0,  // goals minus.
            'elo' => 0, // elo.
        ];
        $partner['g']  += 1;
        $partner['w']  += 1;
        $partner['gp'] += $game['wg'];
        $partner['gm'] += $game['lg'];
        $partner['elo'] += $game['elo_diff'];
        $partners[$partnerid] = $partner;

        // Opponents.
        foreach ($opponentids as $opponentid) {
            $opponent = $opponents[$opponentid] ?? [
                'g'  => 0,  // games.
                'w'  => 0,  // won.
                'l'  => 0,  // lost.
                'gp' => 0,  // goals plus.
                'gm' => 0,  // goals minus.
                'elo' => 0, // elo.
            ];
            $opponent['g']  += 1;
            $opponent['w']  += 1;
            $opponent['gp'] += $game['wg'];
            $opponent['gm'] += $game['lg'];
            $opponent['elo'] += $game['elo_diff'];
            $opponents[$opponentid] = $opponent;
        }

    // Lost.
    } else {

        // Game stats.
        $form[] = 'L';
        $elo = $elo - $game['elo_diff'];
        $lgs += $game['lg'] - $game['wg'];
        $losses ++;

        // Get partners and opponents.
        if ($loserteam['p1'] == $playerid) {
            $partnerid = $loserteam['p2'];
        } else {
            $partnerid = $loserteam['p1'];
        }
        $opponentids = [$winnerteam['p1'], $winnerteam['p2']];

        // Partner.
        $partner = $partners[$partnerid] ?? [
            'g'  => 0,  // games.
            'w'  => 0,  // won.
            'l'  => 0,  // lost.
            'gp' => 0,  // goals plus.
            'gm' => 0,  // goals minus.
            'elo' => 0, // elo.
        ];
        $partner['g']  += 1;
        $partner['l']  += 1;
        $partner['gp'] += $game['lg'];
        $partner['gm'] += $game['wg'];
        $partner['elo'] -= $game['elo_diff'];
        $partners[$partnerid] = $partner;

        // Opponents.
        foreach ($opponentids as $opponentid) {
            $opponent = $opponents[$opponentid] ?? [
                'g'  => 0,  // games.
                'w'  => 0,  // won.
                'l'  => 0,  // lost.
                'gp' => 0,  // goals plus.
                'gm' => 0,  // goals minus.
                'elo' => 0, // elo.
            ];
            $opponent['g']  += 1;
            $opponent['l']  += 1;
            $opponent['gp'] += $game['lg'];
            $opponent['gm'] += $game['wg'];
            $opponent['elo'] -= $game['elo_diff'];
            $opponents[$opponentid] = $opponent;
        }
    }
    $elograph[] = $elo;
}

// Get player vars.
$name = $player['name'];
$bg = $player['bg'];
$color = $player['color'];
$graphcolor = '"' . $color .'"';
$playerstyle = '"color: ' . $color . '; background-color: ' . $bg . ';"';

?>
<!DOCTYPE html>
<?php

echo '<div class="player-header" style=' . $playerstyle . '>';
echo '<div class="player-header-name">' . $name . '</div>';
echo '<a href="' . $returnurl . '" class="close-button"></a>';
echo '</div>';

// -------------------- Details --------------------
echo '<div class="player-section">';
// echo '<h2 class="player-subheader">Player Details</h2>';

// Last 10 games.
echo '<div class="player-stats-details">';
echo '<div class="stats-label">Last 10 games:</div>';
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

// Get partner stats.
$main_partner = ' ';
$best_partner = ' ';
$worst_partner = ' ';
$main_partner_gs = 0;
$best_partner_elo = -1000;
$worst_partner_elo = 1000;
foreach ($partners as $partnerid => $partner) {
    if ($partner['g'] > $main_partner_gs) {
        $main_partner_id = $partnerid;
        $main_partner_gs = $partner['g'];
    }
    if ($partner['elo'] > $best_partner_elo) {
        $best_partner_id = $partnerid;
        $best_partner_elo = $partner['elo'];
    }
    if ($partner['elo'] < $worst_partner_elo) {
        $worst_partner_id = $partnerid;
        $worst_partner_elo = $partner['elo'];
    }
}

// Get opponent stats.
$main_opponent = ' ';
$best_opponent = ' ';
$worst_opponent = ' ';
$main_opponent_gs = 0;
$best_opponent_elo = -1000;
$worst_opponent_elo = 1000;
foreach ($opponents as $opponentid => $opponent) {
    if ($opponent['g'] > $main_opponent_gs) {
        $main_opponent_id = $opponentid;
        $main_opponent_gs = $opponent['g'];
    }
    if ($opponent['elo'] > $best_opponent_elo) {
        $best_opponent_id = $opponentid;
        $best_opponent_elo = $opponent['elo'];
    }
    if ($opponent['elo'] < $worst_opponent_elo) {
        $worst_opponent_id = $opponentid;
        $worst_opponent_elo = $opponent['elo'];
    }
}

// Sessions.
$sessioncount = count($sessions);
$gps = round(count($games) / $sessioncount, 1);
echo write_stats_detail_line('Played', ["$sessioncount sessions", "$gps games/sess"]);

// Avg margins.
if ($wins > 0) {
    $wg_avg = number_format(round($wgs / $wins, 2), 2);
} else {
    $wg_avg = 0;
}
if ($losses > 0) {
    $lg_avg = number_format(round($lgs / $losses, 2), 2);
} else {
    $lg_avg = 0;
}
echo write_stats_detail_line('Avg goal diff',
    ["+$wg_avg ahead", "$lg_avg behind"]);

// Main partner.
if (isset($main_partner_id)) {
    $main_partner = write_player(get_player($main_partner_id));
}
echo write_stats_detail_line('Main partner', [$main_partner, "$main_partner_gs games"]);

// Best partner.
if (isset($best_partner_id)) {
    $best_partner = write_player(get_player($best_partner_id));
}
echo write_stats_detail_line('Best partner', [$best_partner, format_elo($best_partner_elo)]);

// Worst partner.
if (isset($worst_partner_id)) {
    $worst_partner = write_player(get_player($worst_partner_id));
}
echo write_stats_detail_line('Worst partner', [$worst_partner, format_elo($worst_partner_elo)]);

// Main opponent.
if (isset($main_opponent_id)) {
    $main_opponent = write_player(get_player($main_opponent_id));
}
echo write_stats_detail_line('Main opponent', [$main_opponent, "$main_opponent_gs games"]);

// Favourite opponent.
if (isset($best_opponent_id)) {
    $best_opponent = write_player(get_player($best_opponent_id));
}
echo write_stats_detail_line('Fav opponent', [$best_opponent, format_elo($best_opponent_elo)]);

// Nemesis.
if (isset($worst_opponent_id)) {
    $worst_opponent = write_player(get_player($worst_opponent_id));
}
echo write_stats_detail_line('Nemesis', [$worst_opponent, format_elo($worst_opponent_elo)]);

// Trophies.


echo '</div>';

// -------------------- Graph for elo --------------------
echo '<div class="player-section odd">';
echo '<div class="player-subheader" style=' . $playerstyle . '>ELO Progression</div>';
echo '<div class="chart-container">';
echo '<canvas id="elo-chart"></canvas>';

?>
<script src="/js/ext/chart.js"> </script>
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

<!-- --------------------Table of partners-------------------- -->
<div class="player-section">
    <div class="player-subheader" style=<?php echo $playerstyle?>>Partners</div>
    <table id="table-partners">
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

        foreach ($partners as $partnerid => $partner) {
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

<!-- --------------------Table of opponents-------------------- -->
<div class="player-section odd">
    <div class="player-subheader" style=<?php echo $playerstyle?>>Opponents</div>
    <table id="table-opponents">
    <thead>
        <tr>
        <th>Opponent</th>
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

        foreach ($opponents as $partnerid => $partner) {
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

<!-- Back button -->
<a class="back-button" href="<?php echo $returnurl?>">Back</a>

<script>
$(document).ready(function() {
    var dtOptions = <?php echo json_encode($datatables_config); ?>;
    dtOptions.order = [[6, 'desc']];
    dtOptions.language.info = '_TOTAL_ partners';
    $('#table-partners').DataTable(dtOptions);
    dtOptions.order = [[6, 'asc']];
    dtOptions.language.info = '_TOTAL_ opponents';
    $('#table-opponents').DataTable(dtOptions);
});
</script>

<?
echo print_footer();