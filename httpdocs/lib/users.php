<?php
// Hashing during registration
$password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

// Verification during login
if (password_verify($input_password, $stored_hash)) {
  // Grant access
}