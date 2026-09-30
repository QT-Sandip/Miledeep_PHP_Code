<?php

session_start();

include ("../../RestAPI/RestAPI.php");
$bodyContent=$_POST;
//$bodyContent = json_decode($bodyContent);
//var_dump($bodyContent);

$api = PostNewRmBooking;
       

 $resGetShipingBillsAPI = CallAPI($api, "POST", "", $bodyContent);

 echo json_encode($resGetShipingBillsAPI);
 exit;

//$List = $resGetShipingBillsAPI->Data->Data;


?>