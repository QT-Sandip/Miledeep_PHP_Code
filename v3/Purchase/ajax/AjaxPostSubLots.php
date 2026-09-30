<?php

session_start();

include ("../../RestAPI/RestAPI.php");
$bodyContent=$_POST;
//$bodyContent = json_decode($_POST);
//var_dump($bodyContent);

$api = PostSubLots;
       

 $resGetShipingBillsAPI = CallAPI($api, "POST", "", $bodyContent);

 echo json_encode($resGetShipingBillsAPI);
 exit;

//$List = $resGetShipingBillsAPI->Data->Data;


?>