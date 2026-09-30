<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
if (!isset($_SESSION["MenuList"]) || empty($_SESSION["MenuList"])) {
  include_once($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
  include_once ROOT . 'config/api-call.php';
  if (
    $_SESSION["ClientID"] == "serp"
    || $_SESSION["ClientID"] == "SLS2021005"
    || $_SESSION["ClientID"] == "SLS2021007"
    || $_SESSION["ClientID"] == "SLS2021001"
  ) {
    $api = API_MENU;
  } else {
    $api = API_MENU_V4;
  }
  if ($_SESSION["ClientID"] == "JMEV3" && $_SESSION["DeptID"] == 7) {
    $api = API_MENU_V4;
  }
  $resMenuList = CallAPI($api, "GET", "", "");
  $_SESSION["MenuList"] = $resMenuList->Data;
  $_SESSION["MenuError"] = $resMenuList->Error;
}
// echo json_encode( $_SESSION["MenuList"], true);
?>

<div id="topnav-mobile" class="d-block d-lg-none">
  <?php include 'mobile_menu_ui.php'; ?>
</div>



<div id="topnav-desktop" class="d-none d-lg-block">

  <?php
    include 'web_menu_ui.php';
  ?>
</div>