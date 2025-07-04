<?php

require_once __DIR__ . '/lib/config.php';
require_login();

// Submit.
if (count($_POST) > 0) {

    // Validation.
    if ($_POST['name'] == '') {
        $error = 'Ohne Namen geht nix, oida!';
        $valid = false;
    } else {
        $valid = true;
    }

    // Get params.
    $name = $_POST['name'];
    $bg = $_POST['bg'];
    $color = $_POST['color'];

    // Check if name already exists.
    $stmt = $DB->pdo->prepare("SELECT COUNT(*) FROM players WHERE name = :name");
    $stmt->bindParam(':name', $name);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        $error = 'Dein Namen gibts scho, oida!';

        // Temporary solution to change player color.
        $DB->update("players", ['bg' => $bg, 'color' => $color], ['name' => $name]);
        header("Location: players.php?time=session");
        exit();

        $valid = false;
    }

    // Start transaction.
    if ($valid) {

        $DB->pdo->beginTransaction();
        $player = [
            'name' => $name,
            'bg' => $bg,
            'color' => $color,
            'elo' => 1000, // Default ELO.
            'joined' => time(),
        ];
        $DB->insert("players", $player);
        $DB->pdo->commit();

        // Redirect.
        header("Location: players.php");
        exit();
    }
}

// Create random background color.
$bgcolor = '#' . str_pad(dechex(mt_rand(0x0, 0x7FFFFF)), 6, '0', STR_PAD_LEFT);

?>

<body class="modal-page">
    <h1>Add Player</h1>
    <?php if (!empty($error)) echo '<p class="error">' . $error . '</p>'; ?>
    <form method="post">
        <div class="form inputform">

            <div class="form-element">
                <label>Name</label>
                <input id="player-name-input" name="name"></input>
            </div>

            <div class="form-element color-picker-container">
                <label>Colors</label>
                <div class="color-picker">
                    <input type="color" class="color-wheel" name="color" id="color-wheel" value="#ffffff">
                    <input type="color" class="color-wheel" name="bg" id="bg-wheel" value="<?php echo $bgcolor ?>">
                    <div class="preview player-name" style="background-color: #7f7f7f;">Player</div>
                </div>
            </div>

            <div class="footer">
                <button class="button xl" type="submit">OK</button>
                <a href="./players.php?time=session" class="button xl button-secondary">Cancel</a>
            </div>
        </div>
    </form>
</body>
</html>

<!-- Color wheel, thx deepseek -->
<script>
    const colorWheel = document.getElementById('color-wheel');
    const bgWheel = document.getElementById('bg-wheel');
    const preview = document.querySelector('.preview');
    const nameInput = document.querySelector('#player-name-input');

    function updatePreview() {
        preview.style.color = colorWheel.value;
        preview.style.backgroundColor = bgWheel.value;
        preview.textContent = nameInput.value || 'Player';
    }

    colorWheel.addEventListener('input', updatePreview);
    bgWheel.addEventListener('input', updatePreview);
    nameInput.addEventListener('input', updatePreview);
    updatePreview()
</script>