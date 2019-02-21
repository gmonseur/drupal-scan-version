<?php
// Max exec time
ini_set('max_execution_time', 600);

// Autoload
$loader = require_once 'vendor/autoload.php';

// Helpers
require_once 'helpers/Debug.php';
require_once 'helpers/Strings.php';

// Constant
define('MODE_DEBUG', true);

if (MODE_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Config array
$config = array(
    'api_url' => [
        '8.x' => 'https://updates.drupal.org/release-history/drupal/8.x',
        '7.x' => 'https://updates.drupal.org/release-history/drupal/7.x'
    ],
    'cachettl' => 86400, // 1 day
    'finder' => [
        'path' => '../',
        'depth' => ['> 2', '< 6']
    ]
);
