<?php

global $CFG;

require_once __DIR__ . '/lib/config.php';
require_login();

// Reroute back.
if (!array_key_exists('game', $_SESSION)) {
    header('Location: ./addgame.php');
    exit;
}

// Get params.
$player1 = $_SESSION['game']['player1'];
$player2 = $_SESSION['game']['player2'];
$player3 = $_SESSION['game']['player3'];
$player4 = $_SESSION['game']['player4'];
$game['p1'] = $player1['id'];
$game['p2'] = $player2['id'];
$game['p3'] = $player3['id'];
$game['p4'] = $player4['id'];
$game['wg'] = $_SESSION['game']['wg'] ?? null;
$game['lg'] = $_SESSION['game']['lg'] ?? null;
$game['elo_diff'] = $_SESSION['game']['elo_diff'] ?? null;

// Update DB on confirm.
if (array_key_exists('confirm', $_POST)) {

    // Add game.
    try {
        add_game($game);
        header("Location: ./games.php");
        exit();

    // Rollback on error.
    } catch (Exception $e) {
        $DB->pdo->rollBack();
        $error = "Error saving game: " . $e->getMessage();
    }
}

?>
<body class="modal-page">
    <h1>Confirm Game</h1>
    <?php if (!empty($error)) echo '<p class="error">' . $error . '</p>'; ?>
    <form method="post">
        <input type="hidden" name="confirm" value="1"></input>
        <div class="form inputform confirm">

            <div class="form-card winner winner-confirm">
                <h2>Winner</h2>
                <div class="form-element">
                    <div class="elo-confirm elo-winner">+<?= $game['elo_diff'] ?></div>
                    <div>
                        <div class="player-cell"><?= write_player($player1) ?></div>
                        <div class="player-cell"><?= write_player($player2) ?></div>
                    </div>
                    <div class="goal-confirm"><?= $game['wg'] ?></div>
                </div>
            </div>

            <div class="form-card loser loser-confirm">
                <div class="form-element">
                    <div class="elo-confirm elo-loser">-<?= $game['elo_diff'] ?></div>
                    <div>
                        <div class="player-cell"><?= write_player($player3) ?></div>
                        <div class="player-cell"><?= write_player($player4) ?></div>
                    </div>
                    <div class="goal-confirm"><?= $game['lg'] ?></div>
                </div>
                <h2>Loser</h2>
            </div>

            <div class="footer">
                <button class="button xl" type="submit">OK</button>
                <a href="./games.php" class="button xl">Cancel</a>
            </div>
        </div>
    </form>
</body>
</html>