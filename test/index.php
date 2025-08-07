<?php

// Initialize.
require_once __DIR__ . '/lib/config.php';

// Login.
if (isset($_POST['user']) && isset($_POST['pass'])) {
    login();
}

// Get error message.
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

// Try autologin.
if (autologin()) {
    header("Location: games.php?time=session");
    exit();
}

// Login form.
?>
    <body>
        <div class="container">
            <img class="logo" width=200 height=200 src="/styles/wc1020logo.jpg">
        </div>
        <?php if (!empty($error)) echo '<p class="error">' . $error . '</p>'; ?>
        <form method="post">
            <div class="container loginform">
                <label>User <input type="text" name="user"></label><br>
                <label>Pass <input type="password" name="pass"></label><br>
                <div class="footer">
                    <button class="button xl" type="submit">Login</button>
                    <a class="button" href="./games.php?time=session">Nur schaun</a>
                </div>
            </div>
        </form>
    </body>
</html>
<?php

