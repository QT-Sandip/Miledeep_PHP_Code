<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $METHOD_NAME='CHEMWELL METHOD';
$REPORT_CODE='SME/';
 
if($PoID !=''){
	

  $sql_inv="SELECT * FROM `ScrmPoData`  WHERE `PoID`='$PoID'";   
$stmt_inv=$iconn->query($sql_inv);
$row_inv=$stmt_inv->fetch_array();

$exp_id=$row_inv['ExporterID'];
$proc_id=$row_inv['ProcessorID'];

$sql_exp="SELECT * FROM `AdminExporterDetails` WHERE ExpID='$exp_id'";
$stmt_exp=$iconn->query($sql_exp);
$row_exp=$stmt_exp->fetch_array();

 $sql_pro="SELECT * FROM `AdminExporterDetails` WHERE ExpID='$proc_id'";
$stmt_pro=$iconn->query($sql_pro);
$row_pro=$stmt_pro->fetch_array();


/*
 


*/

/*$EXPORTOR=$row_exp['ExpName'];
$EXPORTOR_ADD=$row_exp['ExpAddress'];*/
$EXPORTOR=$row_exp['EiaExpName'];
$EXPORTOR_ADD=$row_exp['EiaExpAddress'];
$EXPORTOR_APP_NUM=$row_exp['ExpApprovalNo'];
$EXPORTOR_LOGO=$row_exp['ExpLogo'];
$EXPORTOR_WM=$row_exp['ExpWaterMark'];
$EXPORTOR_FO=$row_exp['ExpFooter'];
$EXPORTOR_GST=$row_exp['ExpGstNo'];
$EXPORTOR_MAIL='qc@suryamitraexim.com';

/*$PROCESSOR=$row_pro['ExpName'];
$PROCESSOR_ADD=$row_pro['ExpAddress'];*/
$PROCESSOR=$row_pro['EiaExpName'];
$PROCESSOR_ADD=$row_pro['EiaExpAddress'];
$PROCESSOR_APP_NUM=$row_pro['ExpApprovalNo'];
//$PROCESSOR_EMAIL='wellcomebvrm@gmail.com';
//$PROCESSOR_PHONE='91-8816 224193';
$PROCESSOR_LOGO=$row_pro['ExpLogo'];
$PROCESSOR_WM=$row_pro['ExpWaterMark'];
$PROCESSOR_FO=$row_pro['ExpFooter'];
$PROCESSOR_MAIL=$row_exp['ExpMailID'];


}else{
	
$COMPANY_S_NAME='SME';
$COMPANY_NAME='SURYA MITRA EXIM PVT LTD';
$GST='37AAJCS6258G1ZY';

}


$BASE_URL='https://sme.silverlights.in/';
$COMPANY_LOGO=$BASE_URL.'QualityControl/images/logo.jpg';
$COMPANY_LOGO_ma=$BASE_URL.'QualityControl/images/logo.jpg';


$UNIT_1_LOGO=$BASE_URL.'QualityControl/images/logo.jpg';

$UNIT_2_LOGO=$BASE_URL.'QualityControl/images/logo.jpg';


$COMPANY_LOGO_WIDTH=$BASE_URL.'QualityControl/images/logo.jpg';
$COMPANY_LOGO_HEIGHT=$BASE_URL.'QualityControl/images/logo.jpg';

$COMPANY_FOOTER_LOGO=$BASE_URL.'QualityControl/images/logo.jpg';

$COMPANY_FOOTER_TEXT=$BASE_URL.'QualityControl/images/logo.jpg';



/*$CustomInvoiceSql="SELECT *,PI.ExporterID as ExporterID FROM ScrmPIData  as PI
left join ScrmCustomInvoices as SC on SC.PiID=PI.PoID WHERE PiID='$PoID'";
*/

     $CustomInvoiceSql="SELECT *,PI.ExporterID as ExporterID FROM ScrmPIData  as PI
left join ScrmCustomInvoices as SC on SC.PiID=PI.PoID WHERE PiID in ($PoID)"; 
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	$CInvNo=$CustomInvoiceRow['CInvNo'];
	
	$CurrentMonth = date('n',strtotime($CustomInvoiceRow['CInvDate']));   
if($CustomInvoiceRow['ExporterID']==5){		
if($CurrentMonth<=3){
	
 /*$NxtYear = strftime("%y");  	
 $CurrentYear = strftime("%y")-1; */
 $NxtYear = date("y",strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date("y",strtotime($CustomInvoiceRow['CInvDate']))-1;  	
	
} 
else{ 
$CurrentYear = date("y",strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date("y",strtotime($CustomInvoiceRow['CInvDate']))+1;  	
 
}
}else{		
if($CurrentMonth<=3){
 $NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']))-1;  		
} else{ 
$CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))+1;  	
  
}
		}
	
/********uma*****/



 
?>