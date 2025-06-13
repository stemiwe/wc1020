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

    // Check if name already exists.
    $stmt = $DB->pdo->prepare("SELECT COUNT(*) FROM players WHERE name = :name");
    $stmt->bindParam(':name', $name);
    $stmt->execute();
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        $error = 'Dein Namen gibts scho, oida!';
        $valid = false;
    }

    // Start transaction.
    if ($valid) {

        $DB->pdo->beginTransaction();
        $player = [
            'name' => $name,
            'bg' => $bg,
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

?>

<body>
    <h1>Add Player</h1>
    <?php if (!empty($error)) echo '<p class="error">' . $error . '</p>'; ?>
    <form method="post">
        <div class="form inputform">

            <div class="form-element">
                <label>Name</label>
                <input id="player-name-input" name="name"></input>
            </div>

            <div class="form-element color-picker-container">
                <label>Color</label>
                <div class="color-picker">
                    <input type="color" name="bg" id="color-wheel" value="#7f7f7f">
                    <div class="preview player-name" style="background-color: #7f7f7f;">Player</div>
                </div>
            </div>

            <div class="footer">
                <button class="button xl" type="submit">OK</button>
                <a href="/players.php" class="button xl button-secondary">Cancel</a>
            </div>
        </div>
    </form>
</body>
</html>

<!-- Color wheel, thx deepseek -->
<script>
    const colorWheel = document.getElementById('color-wheel');
    const preview = document.querySelector('.preview');
    const nameInput = document.querySelector('#player-name-input');

    function updatePreview() {
    preview.style.backgroundColor = colorWheel.value;
    preview.textContent = nameInput.value || 'Player';
    }

    colorWheel.addEventListener('input', updatePreview);
    nameInput.addEventListener('input', updatePreview);
</script>