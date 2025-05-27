<?php
session_start();


// Initialize.
require_once __DIR__ . '/lib/lib.php';
echo print_header();

// Credentials.
$user = 'wc1020';
$pass = 'wc1020';

// Login.
if (isset($_POST['user']) && isset($_POST['pass'])) {
    if ($_POST['user'] === $user && $_POST['pass'] === $pass) {
        $_SESSION['loggedin'] = true;
    } else {
        $error = 'Geh scheissn!';
    }
}

// Login form.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true):
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
                <button class="button" type="submit">OK</button>            
            </div>            
        </div>
    </form>
</body>
</html>
<?php

// Reroute to games.
else:
header("Location: games.php");
die();

endif; 
?>
