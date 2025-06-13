<?php

/**
 * Checks login status.
 */
function require_login() {
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: index.php?error=Ausweis bitte.");
        die();
    }
}

/**
 * Logs in a user.
 */
function login() {
    global $DB;

    // Get user input.
    $user = $_POST['user'];
    $pass = $_POST['pass'];

    // Validate input.
    if (empty($user) || empty($pass)) {
        header("Location: index.php?error=Geh scheissn!");
        exit();
    }

    // Fetch user from database.
    $stmt = $DB->pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $user);
    $stmt->execute();

    if(!$user_data = $stmt->fetch(PDO::FETCH_ASSOC)) {
        header("Location: index.php?error=Dich kenn ma ned.");
        exit();
    }

    // Verify password.
    if ($user_data && $user_data['password_hash'] == $pass) {
        session_start();
        $_SESSION['loggedin'] = true;
        $_SESSION['userid'] = $user_data['id'];
        $_SESSION['role'] = $user_data['role'];
        header("Location: games.php?time=session");
        exit();
    } else {
        header("Location: index.php?error=Do stimmt wos ned.");
        exit();
    }
}

