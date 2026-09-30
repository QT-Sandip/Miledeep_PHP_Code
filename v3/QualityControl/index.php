<?php

session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


// Temporary condition implemented for Coastal client to meet urgent requirements.
// Change approved by Deepika.

if ($_SESSION["ClientID"] && $_SESSION["ClientID"] == 'CAPLV3') {
    include $_SERVER['DOCUMENT_ROOT'] . '/v3/QualityControl/dashboard.php';
} else {
    include $_SERVER['DOCUMENT_ROOT'] . '/v3/QualityControl/po-list.php';
}
// include $_SERVER['DOCUMENT_ROOT'] . '/v3/QualityControl/dashboardGraphs.php';

// include 'po-list.php';
?>