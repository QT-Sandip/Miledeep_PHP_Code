<?php
//Enable error reporting
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Load Server ENV variables
/*
require __DIR__ . '/../Libraries/vendor/autoload.php'; // Correct path to autoload.php
use Dotenv\Dotenv;

// Path to the directory containing the .env file
//$envPath = realpath(__DIR__ . '/..');

// Initialize Dotenv
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
*/

//Load Server ENV variables
$env_path = realpath(__DIR__ . '/..');
require_once($env_path.'/Libraries/vendor/autoload.php');	
$dotenv = new Dotenv\Dotenv($env_path);
//$dotenv->safeLoad();
$dotenv->load();

?>
