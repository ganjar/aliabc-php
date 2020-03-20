<?php
/**
 * For run all test, start command "./vendor/bin/phpunit" from root directory
 */

defined('TEST_DATA_PATH') or define('TEST_DATA_PATH', __DIR__ . DIRECTORY_SEPARATOR . '_data');

// ensure we get report on all possible php errors
error_reporting(E_ALL);

$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (!is_file($composerAutoload)) {
    die('You need to set up the project dependencies using Composer');
}
require_once $composerAutoload;
