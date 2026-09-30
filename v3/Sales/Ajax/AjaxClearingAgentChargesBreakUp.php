<?php
//session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ("../RestAPI.php");

$DeleteCaBreakupCharges = trim($_POST["DeleteCaBreakupCharges"]);
$SaveCharges = trim($_POST["SaveCharges"]);

/* Save Charges POPUP Save */
if(isset($SaveCharges) && !empty($SaveCharges)) {

	$PoID = trim($_POST["PoID"]);
	$TypeofCharges = trim($_POST["TypeofCharges"]);
	$BillNumber = trim($_POST["BillNumber"]);
	$BillDate = trim($_POST["BillDate"]);
	$BillAmount = trim($_POST["BillAmount"]);
	$PostedUserId = trim($_POST["PostedUserId"]);
	

	$api = PostClearingAgentBreakUpDetailsAPI;
	
	$bodyContent = array(
					"PoID"=> $PoID,
					"TypeofCharges"=>$TypeofCharges,
					"BillNumber"=> $BillNumber,
					"BillDate"=> $BillDate,
					"BillAmount"=> $BillAmount,
					"PostedUserId"=> $PostedUserId
					
				);
	// var_dump($bodyContent);

	$resPostClearingAgentBreakUpDetailsAPI = CallAPI($api, "GET", "", $bodyContent);
	$resPostClearingAgentBreakUp = $resPostClearingAgentBreakUpDetailsAPI->Data;

	// var_dump($resPostClearingAgentBreakUp);

	if(isset($resPostClearingAgentBreakUp->Msg) && !empty($resPostClearingAgentBreakUp->Msg)){
		echo "success";
	}else{
		echo "error";
	}
}


/* Payment Temmplate Delete */
if(isset($DeleteCaBreakupCharges) && !empty($DeleteCaBreakupCharges)) {

	$PoID = trim($_POST["PoID"]);

	$api = DeleteClearingAgentDetailsAPI;
	$bodyContent = array("PoID"=> $PoID);

	$resDeleteClearingAgentDetailsAPI = CallAPI($api, "GET", "", $bodyContent);
	$res = $resDeleteClearingAgentDetailsAPI->Data;

	if(isset($res->Msg) && !empty($res->Msg)){
		echo "success";
	}else{
		echo "error";
	}

}






?>