<?php

session_start();

// Config.
$CFG = new stdClass();
$CFG->nocache = true;
$CFG->season = 1;

// Reporting.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Lib.
require_once __DIR__ . '/html.php';
require_once __DIR__ . '/elo.php';
require_once __DIR__ . '/login.php';
require_once __DIR__ . '/gamelib.php';
require_once __DIR__ . '/stats.php';

// DB.
require_once __DIR__ . '/ext/Medoo.php';
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

// Datatables js config.
$datatables_config = [
    'paging' => false,
    'lengthMenu' => false,
    'dom' => '<"top"fi>rt<"bottom"p>',
    'columnDefs' => [
        [
            'targets' => '_all',
            'orderSequence' => ['desc', 'asc']
        ]
    ],
    'language' => [
        'info' => '_TOTAL_ entries',
        'lengthMenu' => '_MENU_ #',
        'infoFiltered' => '(_MAX_ total)',
    ]
];

// Start page output.
echo print_header();

