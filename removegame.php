<?php

global $CFG, $DB;

require_once __DIR__ . '/lib/config.php';
require_login();

// Get game.
$sql = "SELECT * FROM games ORDER BY id DESC LIMIT 1";
$game = $DB->query($sql)->fetch();
$winner = $DB->query("SELECT * FROM teams WHERE ID = " . $game['winner'])->fetch();
$loser = $DB->query("SELECT * FROM teams WHERE ID = " . $game['loser'])->fetch();
$player1 = $DB->query("SELECT * FROM players WHERE ID = " . $winner['p1'])->fetch();
$player2 = $DB->query("SELECT * FROM players WHERE ID = " . $winner['p2'])->fetch();
$player3 = $DB->query("SELECT * FROM players WHERE ID = " . $loser['p1'])->fetch();
$player4 = $DB->query("SELECT * FROM players WHERE ID = " . $loser['p2'])->fetch();

// Update DB on confirm.
if (array_key_exists('confirm', $_POST)) {

    // Add game.
    try {
        remove_game($game);
        header("Location: ./games.php");
        exit();

    // Rollback on error.
    } catch (Exception $e) {
        $error = "Error deleting game: " . $e->getMessage();
    }
}

?>
<body class="modal-page">
    <h1>Delete Game</h1>
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
                <button class="button xl danger" type="submit">DELETE</button>
                <a href="./games.php" class="button xl">Cancel</a>
            </div>
        </div>
    </form>
</body>
</html>