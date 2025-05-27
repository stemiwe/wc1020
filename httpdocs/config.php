<?php

// Check session.
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
	header("Location: index.php");
	die();
}

// Reporting.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Lib.
require_once __DIR__ . '/lib/lib.php';

// DB.
require_once __DIR__ . '/lib/Medoo.php';
use Medoo\Medoo;
$DB = new Medoo([
    'type' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'wc1020',
    'username' => 'wc1010',
    'password' => 'wUZLkELLAH420?',
    'charset' => 'utf8mb4',
]);

// Start page output.
echo print_header();

