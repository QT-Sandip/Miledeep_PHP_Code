<?php 

$METHOD_NAME='CHEMWELL METHOD';
$REPORT_CODE='SME/';
if($ID !=''){
	

$sql_inv="SELECT * FROM `ScrmPoData`  WHERE `PoID`='$ID'";
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

$EXPORTOR=$row_exp['ExpName'];
$EXPORTOR_ADD=$row_exp['ExpAddress'];
$EXPORTOR_APP_NUM=$row_exp['ExpApprovalNo'];
$EXPORTOR_LOGO=$row_exp['ExpLogo'];
$EXPORTOR_WM=$row_exp['ExpWaterMark'];
$EXPORTOR_FO=$row_exp['ExpFooter'];
$EXPORTOR_USFDA=$row_exp['ExpUsfdaNo'];
$EXPORTOR_FEI=$row_exp['ExpFeiNo'];
$EXPORTOR_CUSTOMS=$row_exp['ExpIeCustomsNo'];

$PROCESSOR=$row_pro['ExpName'];
$PROCESSOR_ADD=$row_pro['ExpAddress'];
$PROCESSOR_APP_NUM=$row_pro['ExpApprovalNo'];
$PROCESSOR_LOGO=$row_pro['ExpLogo'];
$PROCESSOR_WM=$row_pro['ExpWaterMark'];
$PROCESSOR_FO=$row_pro['ExpFooter'];
$PROCESSOR_USFDA=$row_exp['ExpUsfdaNo'];
$PROCESSOR_FEI=$row_exp['ExpFeiNo'];
$PROCESSOR_CUSTOMS=$row_exp['ExpIeCustomsNo'];



/*

*/
}else{
	
$COMPANY_S_NAME='SME';
$COMPANY_NAME='SURYA MITRA EXIM PVT LTD';
/*
$COMPANY_NAME=$row_det['exp_name'];
$COMPANY_ADD='R.S.NO-130 YANAMADURRU VILLAGE BHIMAVARAM MANDAL AP 534239, INDIA';

$PROCESSOR=$row_det['exp_name'];
$PROCESSOR_ADD=$row_det['exp_name'];
$PROCESSOR_APP_NUM=$row_det['exp_name'];
	
$UNIT_1=$row_det['exp_name'];
$UNIT_2=$row_det['PROC_NAME'];


$UNIT_1_ADD='R.S.NO-130, YANAMADURRU VILLAGE, BHIMAVARAM-534 239 ANDHRA PRADESH , INDIA';
$UNIT_2_ADD='DR.NO-2-138 YANAMADURRU VILLAGE, BHIMAVARAM-534 239 ANDHRA PRADESH, INDIA';



$UNIT1_APPROVAL_NO='1819';
$UNIT2_APPROVAL_NO='901';

*/
	
}

$COMPANY_LOGO='https://sme.silverlights.in/qc/images/sme_logo.jpg';

$UNIT_1_LOGO='https://sme.silverlights.in/qc/images/sme_logo.jpg';

$UNIT_2_LOGO='https://sme.silverlights.in/qc/images/sme_logo.jpg';


$COMPANY_LOGO_WIDTH='https://sme.silverlights.in/qc/images/sme_logo.jpg';
$COMPANY_LOGO_HEIGHT='https://sme.silverlights.in/qc/images/sme_logo.jpg';

$COMPANY_FOOTER_LOGO='https://sme.silverlights.in/qc/images/sme_logo.jpg';

$COMPANY_FOOTER_TEXT='https://sme.silverlights.in/qc/images/sme_logo.jpg';






 
?>