<?php

session_start();

// Config.
$CFG = new stdClass();
$CFG->nocache = true;
$CFG->season = 1;
$CFG->testsite = false;
$CFG->autologinip = '213.147.167.227';

// Reporting.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Lib.
foreach (glob(__DIR__ . '/*.php') as $file) {
    require_once $file;
}

// Classes.
foreach (glob(__DIR__ . '/classes/*.php') as $file) {
    require_once $file;
}

// DB.
require_once __DIR__ . '/ext/Medoo.php';
if ($CFG->testsite) {
    $db = 'wc1020test';
} else {
    $db = 'wc1020';
}
$DB = new Medoo\Medoo([
    'type' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'database' => $db,
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
$current_url = current_url();
if (!strpos($current_url,'/api')) {
    echo html::header();
}

