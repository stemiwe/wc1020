<?php

session_start();

// Config.
$CFG = new stdClass();
$CFG->nocache = true;
$CFG->season = 2;
$CFG->autologinip = '213.147.166.79';
date_default_timezone_set('Europe/Vienna'); // Or 'Europe/Berlin', both use CET/CEST

// Determine if this is a test site.
$CFG->docroot = dirname(__FILE__);
if (str_contains($CFG->docroot,'test')) {
    $CFG->testsite = true;
} else {
    $CFG->testsite = false;
}

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
    'option' => [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]

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

// Start page output for non-api sites.
$current_url = current_url();
if (!strpos($current_url,'/api')) {
    echo html::header();
}

