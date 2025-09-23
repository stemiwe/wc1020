<?php

require_once __DIR__ . '/lib/config.php';
require_login();

// Get game to be edited.
$oldgame = $DB->get("games", "*", ["id" => $_GET['id']]);

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
$default_p1 = null;
$default_p2 = null;
$default_p3 = null;
$default_p4 = null;
$default_wg = 7;
$default_lg = 5;

// Submit.
if (count($_POST) > 0) {
    $game = $DB->get("games", "*", ["id" => $_GET['id']]);
    foreach($_POST as $key => $value) {
        if ($key == 'p1' || $key == 'p2' || $key == 'p3' || $key == 'p4') {
            continue;
        }
        if ($key == 'timestamp') {
            $value = strtotime($value);
        }
        $game[$key] = $value;
    }

    $DB->update("games", $game, ["id" => $_GET['id']]);
    header("Location: ./lib/scripts/recalculate_elo.php");
}

// Set defaults for form.
$winner = $DB->get('teams', '*', ['id' => $oldgame['winner']]);
$loser = $DB->get('teams', '*', ['id' => $oldgame['loser']]);
$default_p1 = $winner['p1'];
$default_p2 = $winner['p2'];
$default_p3 = $loser['p1'];
$default_p4 = $loser['p2'];
foreach ($oldgame as $key => $value) {
    $varname = "default_$key";
    $$varname = $value;
}

?>

<body class="modal-page">
    <h1>Edit Game</h1>
    <?php if (!empty($error)) echo '<p class="error">' . $error . '</p>'; ?>
    <form method="post">
        <div class="form inputform">

            <div class="form-card winner">
                <h2>Winner</h2>

                <div class="form-element">
                    <label>P1</label>
                    <select name="p1">
                        <?php foreach ($playeroptions as $id => $name): ?>
                            <option value="<?= $id ?>"  <?= $id == $default_p1 ? 'selected' : '' ?>>
                                <?= htmlspecialchars($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-element">
                    <label>P2</label>
                    <select name="p2">
                        <?php foreach ($playeroptions as $id => $name): ?>
                            <option value="<?= $id ?>"  <?= $id == $default_p2 ? 'selected' : '' ?>>
                                <?= htmlspecialchars($name) ?>
                            </option>
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
                            <option value="<?= $id ?>"  <?= $id == $default_p3 ? 'selected' : '' ?>>
                                <?= htmlspecialchars($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-element">
                    <label>P2</label>
                    <select name="p4">
                        <?php foreach ($playeroptions as $id => $name): ?>
                            <option value="<?= $id ?>"  <?= $id == $default_p4 ? 'selected' : '' ?>>
                                <?= htmlspecialchars($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-element">
                <label>Date</label>
                <input type="datetime-local" name="timestamp"
                        value="<?= date('Y-m-d\TH:i', $oldgame['timestamp'] ?? time()) ?>">
            </div>

            <div class="footer">
                <button class="button xl" type="submit">OK</button>
                <a href="./games.php?time=session" class="button xl button-secondary">Cancel</a>
            </div>
        </div>
    </form>
</body>
</html>