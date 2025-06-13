<?php

global $CFG;

require_once __DIR__ . '/lib/config.php';
require_login();

// Reroute back.
if (!array_key_exists('game', $_SESSION)) {
    header('Location: /addgame.php');
    exit;
}

// Get params.
$player1 = $_SESSION['game']['player1'] ?? null;
$player2 = $_SESSION['game']['player2'] ?? null;
$player3 = $_SESSION['game']['player3'] ?? null;
$player4 = $_SESSION['game']['player4'] ?? null;
$wg = $_SESSION['game']['wg'] ?? null;
$lg = $_SESSION['game']['lg'] ?? null;
$elo_diff = $_SESSION['game']['elo_diff'] ?? null;

// Update DB on confirm.
if (array_key_exists('confirm', $_POST)) {
    $DB->pdo->beginTransaction();

    try {

        // Get player IDs.
        $p1_id = $player1['id'];
        $p2_id = $player2['id'];
        $p3_id = $player3['id'];
        $p4_id = $player4['id'];

        $t1p1_id = $p1_id < $p2_id ? $p1_id : $p2_id;
        $t1p2_id = $p1_id < $p2_id ? $p2_id : $p1_id;
        $t2p1_id = $p3_id < $p4_id ? $p3_id : $p4_id;
        $t2p2_id = $p3_id < $p4_id ? $p4_id : $p3_id;

        // Get or create both teams.
        $t1_id = get_or_create_team($DB, $t1p1_id, $t1p2_id);
        $t2_id = get_or_create_team($DB, $t2p1_id, $t2p2_id);

        // Shift current time 8 hours back because our day doesnt end at midnight.
        $date = time() - 8 * 3600;

        // Create game record.
        $game = [
            'winner' => $t1_id,
            'loser' => $t2_id,
            'wg' => $wg,
            'lg' => $lg,
            'date' => date('Y-m-d', $date),
            'season' => $CFG->season,
            'timestamp' => time(),
            'elo_diff' => $elo_diff,
        ];
        $DB->insert("games", $game);

        // Update ELO for players.
        $elo1 = $player1['elo'] + $elo_diff;
        $elo2 = $player2['elo'] + $elo_diff;
        $elo3 = $player3['elo'] - $elo_diff;
        $elo4 = $player4['elo'] - $elo_diff;
        for ($i = 1; $i <= 4; $i++) {
            $elokey = "elo$i";
            $playerkey = "p$i" . "_id";
            $DB->update("players", [
                'elo' => $$elokey
            ], [
                'id' => $$playerkey
            ]);
        }

        // Commit.
        $DB->pdo->commit();

    } catch (Exception $e) {
        // Rollback on error
        $DB->pdo->rollBack();
        $error = "Error saving game: " . $e->getMessage();
        // Show error to user or log it
    }

    // Update stats.
    update_stats($t1_id, 'team', 'win');
    update_stats($t2_id, 'team', 'loss');
    update_stats($p1_id, 'player', 'win');
    update_stats($p2_id, 'player', 'win');
    update_stats($p3_id, 'player', 'loss');
    update_stats($p4_id, 'player', 'loss');

    // Clear session.
    unset($_SESSION['game']);

    // Redirect to games page.
    header("Location: games.php");
    exit();
}



?>

<body>
    <h1>Confirm Game</h1>
    <?php if (!empty($error)) echo '<p class="error">' . $error . '</p>'; ?>
    <form method="post">
        <input type="hidden" name="confirm" value="1"></input>
        <div class="form inputform confirm">

            <div class="form-card winner winner-confirm">
                <h2>Winner</h2>
                <div class="form-element">
                    <div class="elo-confirm elo-winner">+<?= $elo_diff ?></div>
                    <div>
                        <div class="player-cell"><?= write_player($player1) ?></div>
                        <div class="player-cell"><?= write_player($player2) ?></div>
                    </div>
                    <div class="goal-confirm"><?= $wg ?></div>
                </div>
            </div>

            <div class="form-card loser loser-confirm">
                <div class="form-element">
                    <div class="elo-confirm elo-loser">-<?= $elo_diff ?></div>
                    <div>
                        <div class="player-cell"><?= write_player($player3) ?></div>
                        <div class="player-cell"><?= write_player($player4) ?></div>
                    </div>
                    <div class="goal-confirm"><?= $lg ?></div>
                </div>
                <h2>Loser</h2>
            </div>

            <div class="footer">
                <button class="button" type="submit">OK</button>
                <a href="/games.php" class="button">Cancel</a>
            </div>
        </div>
    </form>
</body>
</html>