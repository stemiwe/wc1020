<?php

require_once __DIR__ . '/lib/config.php';
require_login();

// Options.
$players = $DB->query("SELECT id, name FROM players")->fetchAll();
$players = array_column($players, 'name', 'id');
uasort($players, function($a, $b) {
    return strcasecmp($a, $b);
});
$playeroptions = [0 => ' --- select ---'];
$playeroptions += $players;

$wgoaloptions = [10, 9, 8, 7];
$lgoaloptions = [9, 8, 7, 6, 5, 4, 3, 2, 1, 0];
$default_wg = 7;
$default_lg = 5;

// Submit.
if (count($_POST) > 0) {

    // Validation.
    $valid = true;
    $ps = [];
    for ($i = 1; $i < 5; $i++) {
        // Check for empty fields.
        $p = $_POST['p' . $i];
        if (($p == 0)) {
            $error = 'Do fehlt wos oida!';
            $valid = false;
            break;
        }

        // Check for duplicates.
        if (in_array($_POST['p' . $i], $ps)) {
            $error = 'Koana spielt doppelt oida!';
            $valid = false;
            break;
        }
        $ps[] = $_POST['p' . $i];
    }

    // Get params.
    $p1 = $_POST['p1'];
    $p2 = $_POST['p2'];
    $p3 = $_POST['p3'];
    $p4 = $_POST['p4'];
    $wg = $_POST['wg'];
    $lg = $_POST['lg'];

    // Goal validation.
    if ($lg >= $wg) {
        $error = 'Da Siega muss mehr Tore hom oida!';
        $valid = false;
    }
    if ($wg < 10 && !($wg > ($lg + 1))) {
        $error = 'Mir spieln mit zwoa unterschied oida!';
        $valid = false;
    }
    if ($wg < 7) {
        $error = 'Des Spiel is no ned aus oida!';
        $valid = false;
    }
    if ($wg > 7 && $lg < 6) {
        $error = 'Bisi viel Tore fürn Sieger, oda?';
        $valid = false;
    }

    // Start transaction.
    if ($valid) {

        // Get player IDs.
        if ($p1 < $p2) {
            $p1_id = $p1;
            $p2_id = $p2;
        } else {
            $p1_id = $p2;
            $p2_id = $p1;
        }

        // Calculate ELO.
        $player1 = $DB->get("players", "*", ["id" => $p1]);
        $player2 = $DB->get("players", "*", ["id" => $p2]);
        $player3 = $DB->get("players", "*", ["id" => $p3]);
        $player4 = $DB->get("players", "*", ["id" => $p4]);
        $elo1 = [$player1['elo'], $player2['elo']];
        $elo2 = [$player3['elo'], $player4['elo']];
        $elo_diff = elo_difference($elo1, $elo2, $wg - $lg);

        // Redirect.
        $_SESSION['game'] = [
            'player1' => $player1,
            'player2' => $player2,
            'player3' => $player3,
            'player4' => $player4,
            'wg' => $wg,
            'lg' => $lg,
            'elo_diff' => $elo_diff
        ];
        header("Location: addgame_confirm.php");
        exit();
    }
}

?>

<body>
    <h1>Add Game</h1>
    <?php if (!empty($error)) echo '<p class="error">' . $error . '</p>'; ?>
    <form method="post">
        <div class="form inputform">

            <div class="form-card winner">
                <h2>Winner</h2>

                <div class="form-element">
                    <label>P1</label>
                    <select name="p1">
                        <?php foreach ($playeroptions as $id => $name): ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-element">
                    <label>P2</label>
                    <select name="p2">
                        <?php foreach ($playeroptions as $id => $name): ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-element">
                    <label>Goals</label>
                    <select name="wg" selected="7">
                        <?php foreach ($wgoaloptions as $goal): ?>
                            <option value="<?= $goal ?>" <?= $goal == $default_wg ? 'selected' : '' ?>>
                                <?= $goal ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-card loser">
                <h2>Loser</h2>

                <div class="form-element">
                    <label>Goals</label>
                    <select name="lg">
                        <?php foreach ($lgoaloptions as $goal): ?>
                            <option value="<?= $goal ?>" <?= $goal == $default_lg ? 'selected' : '' ?>>
                                <?= $goal ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-element">
                    <label>P1</label>
                    <select name="p3">
                        <?php foreach ($playeroptions as $id => $name): ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-element">
                    <label>P2</label>
                    <select name="p4">
                        <?php foreach ($playeroptions as $id => $name): ?>
                            <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="footer">
                <button class="button xl" type="submit">OK</button>
                <a href="/games.php?time=session" class="button xl button-secondary">Cancel</a>
            </div>
        </div>
    </form>
</body>
</html>