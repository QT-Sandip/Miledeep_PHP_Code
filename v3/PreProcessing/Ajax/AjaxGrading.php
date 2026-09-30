<?php

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

  $jsonData=$_POST['Data'];
  $Type=$_POST['Type'];

  if($Type=='BREAK_UP'){
    $api = API_PPC_GRADING_BREAK_UP;
  }else if($Type=='Report'){
    $api = API_PPC_GRADING;
  }
  

 $bodyContent=json_decode($jsonData,true);


  $result = CallAPI($api, "POST", "", $bodyContent);

//var_dump( $result);

echo json_encode($result);

?>