<?php
// Production error reporting
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'zxtfmwrs_zxtfmwrs');
define('DB_PASSWORD', 'ws;0V;5YG2p0Az');
define('DB_NAME', 'zxtfmwrs_mnr_course');

// API Key
// Improvement: It's recommended to store sensitive keys like this in environment variables instead of hardcoding them.
define('API_KEY', 'frostfoe1337');

// Establish database connection
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    // You can handle the connection error here if you want to log it or show a generic error page.
    // For the purpose of showing status on the login page, we will let the login page handle the error display.
}
