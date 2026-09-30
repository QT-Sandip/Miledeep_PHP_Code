<?php
//session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ("../RestAPI.php");

$DeleteTemplate = trim($_POST["DeleteTemplate"]);
$PaymentDeleteOperation = trim($_POST["PaymentDeleteOperation"]);
$SavePaymentIns = trim($_POST["SavePaymentIns"]);


/* Payment Temmplate POPUP Save */
if(isset($SavePaymentIns) && !empty($SavePaymentIns)) {

	$InsurancePaymentID = trim($_POST["InsurancePaymentID"]);
	$OurReferenceNo = trim($_POST["OurReferenceNo"]);
	$ReferenceNo = trim($_POST["ReferenceNo"]);
	$PaymentDate = trim($_POST["PaymentDate"]);
	$Amount = trim($_POST["Amount"]);
	$PostedUserId = trim($_POST["PostedUserId"]);
	$Remarks = trim($_POST["Remarks"]);
	$PaymentInsExchangeRate = trim($_POST["PaymentInsExchangeRate"]);

	$api = PostPaymentsInsuranceData_API;
	
	$bodyContent = array(
					"InsurancePaymentID"=> $InsurancePaymentID,
					"OurReferenceNo"=>$OurReferenceNo,
					"ReferenceNo"=> $ReferenceNo,
					"PaymentDate"=> $PaymentDate,
					"Amount"=> $Amount,
					"PostedUserId"=> $PostedUserId,
					"Remarks"=> $Remarks,
					"PaymentInsExchangeRate"=> $PaymentInsExchangeRate,
					"DebitNote"=> trim($_POST["DebitNote"]),
				);
	// var_dump($bodyContent);

	$resPostPaymentsInsuranceDataAPI = CallAPI($api, "GET", "", $bodyContent);
	// echo "<br>";
	// var_dump($resPostPaymentsInsuranceDataAPI);
	$resPostPayments = $resPostPaymentsInsuranceDataAPI->Data;

	if(isset($resPostPayments->Msg) && !empty($resPostPayments->Msg)){
		echo 'success';
	}else{
		echo 'error';
	}
}


/* Payment Temmplate Delete */
if(isset($PaymentDeleteOperation) && !empty($PaymentDeleteOperation)) {

	$InsurancePaymentID = trim($_POST["InsurancePaymentID"]);

	$api = DeleteInsurancePaymentsTemplate_API;
	$bodyContent = array("InsurancePaymentID"=> $InsurancePaymentID);

	$resDeleteInsurancePaymentsTemplateAPI = CallAPI($api, "GET", "", $bodyContent);
	$res = $resDeleteInsurancePaymentsTemplateAPI->Data;

	if(isset($res->Msg) && !empty($res->Msg)){
		echo "success";
	}else{
		echo "error";
	}

}


/* Insurance Template Delete */
if(isset($DeleteTemplate) && !empty($DeleteTemplate)) {

	$PoID = trim($_POST["PoID"]);
	$InsuranceID = trim($_POST["InsuranceID"]);
	$PostedUserId = trim($_POST["PostedUserId"]);

	$api = DeleteInsuranceTemplate_API;
	$bodyContent = array(
		"PoID"=> $PoID,
		"InsuranceID"=>$InsuranceID,
		"PostedUserId"=>$PostedUserId);

	$resDeleteInsuranceTemplateAPI = CallAPI($api, "GET", "", $bodyContent);
	$resList = $resDeleteInsuranceTemplateAPI->Data;

	if(isset($resList->Msg) && !empty($resList->Msg)){
		echo "success";
	}else{
		echo "error";
	}
}

?>