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
$sql = "SELECT * FROM games WHERE winner IN $teamids_sql OR loser IN $teamids_sql ORDER BY timestamp ASC";
$games = $DB->query($sql)->fetchAll();

// Initialize game variables.
$elograph = [];
$elo = 1000;
$elo_high = 1000;
$elo_low = 1000;
$own_elo = 0;
$partner_elo = 0;
$opponent_elo = 0;
$form = [];
$partners = [];
$opponents = [];
$gs = 0;
$wins = 0;
$losses = 0;
$wgs = 0;
$lgs = 0;
$sessions = [];
$highlights = [];
$lowpoints = [];
$streak = ['current' => 0,
           'top' => 0,
           'ended' => false
];
$gametimes = [];
$wintimes = [];
$losstimes = [];

// Get data from games.
foreach ($games as $game) {

    $gs++;
    $sessions[$game['date']] = 1;

    // Make game time decimal.
    $hour = date('H', $game['timestamp']) - 8;
    $minutes = date('i', $game['timestamp']);
    $minutes = $minutes / 60;
    $time = $hour + $minutes;
    $gametimes[] = $time;

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
        $highlights[$game['elo_diff']] = $game;
        $wintimes[] = $time;

        // Streak.
        $streak['current']++;
        if ($streak['current'] > $streak['top']) {
            $streak['top'] = $streak['current'];
            $streak['ended'] = false;
        }

        // Get partners and opponents.
        if ($winnerteam['p1'] == $playerid) {
            $partnerid = $winnerteam['p2'];
            $own_elo += $game['elo_p1'];
            $partner_elo += $game['elo_p2'];
        } else {
            $partnerid = $winnerteam['p1'];
            $own_elo += $game['elo_p2'];
            $partner_elo += $game['elo_p1'];
        }
        $opponentids = [$loserteam['p1'], $loserteam['p2']];
        $opponent_elo += $game['elo_p3'] + $game['elo_p4'];

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
        $lowpoints[$game['elo_diff']] = $game;
        $losstimes[] = $time;

        // Streak.
        $streak['current'] = 0;
        $streak['ended'] = $game['date'];

        // Get partners and opponents.
        if ($loserteam['p1'] == $playerid) {
            $partnerid = $loserteam['p2'];
            $own_elo += $game['elo_p3'];
            $partner_elo += $game['elo_p4'];
        } else {
            $partnerid = $loserteam['p1'];
            $own_elo += $game['elo_p4'];
            $partner_elo += $game['elo_p3'];
        }
        $opponentids = [$winnerteam['p1'], $winnerteam['p2']];
        $opponent_elo += $game['elo_p1'] + $game['elo_p2'];

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

    // Update elo high and low.
    if ($elo > $elo_high) {
        $elo_high = $elo;
    }
    if ($elo < $elo_low) {
        $elo_low = $elo;
    }
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
$gamecount = count($games);
$winrate = $gamecount > 0 ? round($wins / $gamecount * 100, 1) : 0;
$gps = round($gamecount / $sessioncount, 1);
echo html::stats_detail_line('Joined', [date('Y-m-d', $player['joined']), ' ']);
echo html::stats_detail_line('Sessions', ["$sessioncount sessions", "$gps gms/sess"]);
echo html::stats_detail_line('Games', ["$gamecount games", "$winrate% won"]);

// Last 10 games.
echo '<div class="player-stats-details">';
echo '<div class="stats-label">Last 10 games:</div>';
echo '<div class="player-form">';
$i = 0;
$form = array_reverse($form);
foreach ($form as $f) {
    echo '<div class="form-' . strtolower($f) . '">' . $f . '</div>';
    $i++;
    if ($i > 9) {
        break;
    }
}
echo '</div>';
echo '</div>';

// Streak.
if ($streak['top'] > 0) {
    $streakend = $streak['ended'] ? 'ended on <br>' . $streak['ended'] : 'ongoing!';
    echo html::stats_detail_line('Top winning streak',
    [$streak['top'] . ' games', $streakend]);
}

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
echo html::stats_detail_line('~Goal diff',
    ["+$wg_avg ahead", "$lg_avg behind"]);

// Avg ELOs.
$own_elo = round($own_elo / $gs, 0);
$partner_elo = round($partner_elo / $gs, 0);
$opponent_elo = round($opponent_elo / $gs / 2, 0);
$own_elo_delta = format_elo($partner_elo - $own_elo, 'higher', 'lower', true);
$opponent_elo_delta = format_elo($opponent_elo - $own_elo, 'higher', 'lower');
echo html::stats_detail_line('~Partner ELO',
    [$partner_elo, $own_elo_delta]);
echo html::stats_detail_line('~Opponent ELO',
    [$opponent_elo, $opponent_elo_delta]);

// Times played.
$avg_time = convert_back_to_time(array_sum($gametimes) / count($gametimes) + 8);
$avg_time_won = convert_back_to_time(array_sum($wintimes) / count($wintimes) + 8);
$avg_time_lost = convert_back_to_time(array_sum($losstimes) / count($losstimes) + 8);
$min_time = convert_back_to_time(min($gametimes) + 8);
$max_time = convert_back_to_time(max($gametimes) + 8);
$min_time_won = convert_back_to_time(min($wintimes) + 8);
$max_time_won = convert_back_to_time(max($wintimes) + 8);
$min_time_lost = convert_back_to_time(min($losstimes) + 8);
$max_time_lost = convert_back_to_time(max($losstimes) + 8);
// echo html::stats_detail_line('Times played', ["$min_time - $max_time", "~$avg_time"]);
// echo html::stats_detail_line('Times won', ["$min_time_won - $max_time_won", "~$avg_time_won"]);
// echo html::stats_detail_line('Times lost', ["$min_time_lost - $max_time_lost", "~$avg_time_lost"]);

// Main partner.
if (isset($main_partner_id)) {
    $main_partner = write_player(get_player($main_partner_id));
}
echo html::stats_detail_line('Main partner', [$main_partner, "$main_partner_gs games"]);

// Best partner.
if (isset($best_partner_id)) {
    $best_partner = write_player(get_player($best_partner_id));
}
echo html::stats_detail_line('Best partner', [$best_partner, format_elo($best_partner_elo)]);

// Worst partner.
if (isset($worst_partner_id)) {
    $worst_partner = write_player(get_player($worst_partner_id));
}
echo html::stats_detail_line('Worst partner', [$worst_partner, format_elo($worst_partner_elo)]);

// Main opponent.
if (isset($main_opponent_id)) {
    $main_opponent = write_player(get_player($main_opponent_id));
}
echo html::stats_detail_line('Main opponent', [$main_opponent, "$main_opponent_gs games"]);

// Favourite opponent.
if (isset($best_opponent_id)) {
    $best_opponent = write_player(get_player($best_opponent_id));
}
echo html::stats_detail_line('Fav opponent', [$best_opponent, format_elo($best_opponent_elo)]);

// Nemesis.
if (isset($worst_opponent_id)) {
    $worst_opponent = write_player(get_player($worst_opponent_id));
}
echo html::stats_detail_line('Nemesis', [$worst_opponent, format_elo($worst_opponent_elo)]);

// Trophies.


echo '</div>';

// -------------------- Graph for elo --------------------
echo '<div class="player-section odd">';
echo '<div class="player-subheader" style=' . $playerstyle . '>ELO Progression</div>';
echo '<div class="elo-stats-details">';
echo "<span>Current: $elo</span><span>High: $elo_high</span><span>Low: $elo_low</span>";
echo '</div>';
echo '<div class="chart-container">';
echo '<canvas id="elo-chart"></canvas></div>';
echo '</div>';

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


<!-- --------------------Highlights-------------------- -->

<div class="player-section">
    <div class="player-subheader" style=<?php echo $playerstyle?>>Highlights</div>

    <?php
    //Define table columns.
    $cols = ['Date',
            'Winner',
            'G+',
            'G-',
            'Loser',
            'ELO'];
    $table['cols'] = $cols;
    $table['rows'] = [];

    // Sort & limit to 5 highlights.
    krsort($highlights);
    if (count($highlights) > 5) {
        $highlights = array_slice($highlights, 0, 5, true);
    }
    foreach ($highlights as $game) {
        $row = html::game_row($game);
        $table['rows'][] = $row;
    }
    echo html::table($table, 'table-highlights');
    ?>
</div>

<!-- --------------------Low points-------------------- -->

<div class="player-section-odd">
    <div class="player-subheader" style=<?php echo $playerstyle?>>Low Points</div>

    <?php
    //Define table columns.
    $cols = ['Date',
            'Winner',
            'G+',
            'G-',
            'Loser',
            'ELO'];
    $table['cols'] = $cols;
    $table['rows'] = [];

    // Sort & limit to 5 low points.
    krsort($lowpoints);
    if (count($lowpoints) > 5) {
        $lowpoints = array_slice($lowpoints, 0, 5, true);
    }
    foreach ($lowpoints as $game) {
        $row = html::game_row($game);
        $table['rows'][] = $row;
    }
    echo html::table($table, 'table-lowpoints');
    ?>

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

<?php
// --------------------Awards --------------------
$sql = "SELECT * FROM awards WHERE p1 = :pid OR p2 = :pid ORDER BY season ASC, weight ASC";
$awards = $DB->query($sql, [':pid' => $playerid])->fetchAll();

if ($awards) {
    // Awards.
    echo '<div class="player-section">';
    echo '<div class="player-subheader" style=' . $playerstyle . '>Awards</div>';

    echo '<div class="award-container">';
    foreach ($awards as $award) {
        echo html::award($award);
    }
    echo '</div>';
}

?>

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
    dtOptions.order = [[5, 'desc']];
    dtOptions.language.info = '_TOTAL_ highlights';
    $('#table-highlights').DataTable(dtOptions);
    dtOptions.language.info = '_TOTAL_ low points';
    $('#table-lowpoints').DataTable(dtOptions);
});
</script>

<?php

echo html::footer();