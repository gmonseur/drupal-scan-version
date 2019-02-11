<?php
// Autoload
$loader = require_once 'vendor/autoload.php';

// Helpers
require_once 'helpers/Debug.php';
require_once 'helpers/Strings.php';

// Constant
define('MODE_DEBUG', true);

// Config array
$config = array(
    'api_url' => [
        '8.x' => 'https://updates.drupal.org/release-history/drupal/8.x',
        '7.x' => 'https://updates.drupal.org/release-history/drupal/7.x'
    ]
);
