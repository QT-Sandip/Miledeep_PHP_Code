<?php


ini_set('memory_limit', '-1');
set_time_limit(600); 
$days_ago = date('Y-m-d', strtotime('-3 days', strtotime('2021-09-04')));
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");
include ($cp."Includes/IncludePage.php");
exit;
$PaymentDate = date('Y-m-d H:i:s');
 $QueryParms=$_GET['Q']; 
if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
  $CurrentMonth = date('m');

  $SelectedMonth=$_GET['Month'];
  $SelectedYear=$_GET['Year'];

  $DaysInMonth = cal_days_in_month( 0, $SelectedMonth, $SelectedYear);
  $MaxWeekOffs=$WorkingWeeks=floor($DaysInMonth/6);
  $ReqWorkingDays=$DaysInMonth-$MaxWeekOffs;
  
   $SqlLog= "SELECT count(*) as Total FROM HrmCashAdvancePayments 
 WHERE PaymentMonth='$SelectedMonth' 
 and  PaymentYear='$SelectedYear';";
 $StmtLog=$iconn->query($SqlLog);
 $RowLog=$StmtLog->fetch_array();
 
 
 $Total=$RowLog['Total'];
 if($Total > 0){
	 updateAdvance();
echo "Salary Already Generated";
exit;
 }
 else if(($CurrentMonth - $SelectedMonth)!=1)
 {
echo "Only For Previous Month";
 updateAdvance();
exit;	 
 }else {
	  updateAdvance();
 }
  
  
  
  
  
  
function updateAdvance(){
	  global $iconn,$SelectedMonth,$SelectedYear,$PaymentDate;
	  $SqlCashAdvances="SELECT *,
(SELECT COALESCE(sum(PaymentAmount),0) from HrmCashAdvancePayments WHERE
 EmpID=HCA.EmpID and PaymentMonth !='$SelectedMonth' and PaymentYear='$SelectedYear' 
 and CAdvID=HCA.CAdvID) as PaidAmount,
 (SELECT count(*) from HrmCashAdvancePayments WHERE EmpID=HCA.EmpID and 
 PaymentMonth ='$SelectedMonth' and PaymentYear='$SelectedYear' and CAdvID=HCA.CAdvID ) as PaidCount
 FROM `HrmCashAdvance` as HCA ";
 
 $SqlAdvArray=array();
 $SqlPayArray=array();
 $StmtCashAdvances=$iconn->query($SqlCashAdvances);
 while($RowCashAdvances=$StmtCashAdvances -> fetch_array()){
	 $AdvAmount=$RowCashAdvances['AdvAmount'];
	 $PaidAmount=$RowCashAdvances['PaidAmount'];
	 $Installment=$RowCashAdvances['Installments'];
	 $PaidCount=$RowCashAdvances['PaidCount'];
	 $EmpID=$RowCashAdvances['EmpID'];
	 $CAdvID=$RowCashAdvances['CAdvID'];


	 if(($PaidAmount > $AdvAmount) > $Installment ){
		 $AmountToBePaid=0;
		 $NewCurrent=0;
	 }else if(($AdvAmount-$PaidAmount) > $Installment ){
		 $AmountToBePaid=$Installment;
		 $NewCurrent=$AdvAmount-($PaidAmount+$Installment);
	 }else{
		 $AmountToBePaid=$AdvAmount-$PaidAmount;
		  $NewCurrent=0;
	 }
	 
	// echo $AmountToBePaid;
	 
	 
	 $SqlAdvArray[]="('$CAdvID','$NewCurrent','$AmountToBePaid')";
	 
	 if($AmountToBePaid==0 && $Installment!=0 ){
	 }else if($PaidCount==0){
	 $SqlPayArray[]="('$CAdvID','$EmpID','$AmountToBePaid','$AmountToBePaid','$PaymentDate','$SelectedMonth','$SelectedYear')";
	 }else{		 
		 $SqlAdvUpdate="UPDATE `HrmCashAdvancePayments` 
		SET DueAmount='$AmountToBePaid',PaymentAmount='$AmountToBePaid' ,PaymentDate='$PaymentDate'
		WHERE EmpID='$EmpID' and PaymentMonth ='$SelectedMonth'
		and PaymentYear='$SelectedYear' and CAdvID='$CAdvID' "; 
		$StmtAdvUpdate=$iconn->query($SqlAdvUpdate); 
	 }
	
	echo "Updating Advace Payments...<br>";
	 
 }
 
  $SqlPayArray=implode(',',$SqlPayArray);
 $SqlAdvArray=implode(',',$SqlAdvArray);
 
  $SqlPaymentLog="INSERT into `HrmCashAdvancePayments` 
	(`CAdvID`, `EmpID`,`DueAmount`, `PaymentAmount`, `PaymentDate`,PaymentMonth,PaymentYear)
		VALUES $SqlPayArray ";
	
$StmtPaymentLog=$iconn->query($SqlPaymentLog);


/* Update BioMetric ID */

	$SqlPaymentLog="UPDATE `HrmCashAdvancePayments` 
	SET BioMetricID=(SELECT BioMetricID from HrmEmployeeDetails 
	WHERE EmpID=HrmCashAdvancePayments.EmpID) ";
	
	$StmtPaymentLog=$iconn->query($SqlPaymentLog);	
	
	
/* Update Current Pending */	
	  $SqlAdvUpdate="INSERT into `HrmCashAdvance` (CAdvID,CurrentPending,LastPayment)
		VALUES $SqlAdvArray ON DUPLICATE KEY UPDATE 
	CurrentPending=VALUES(CurrentPending),
	LastPayment=VALUES(LastPayment)";
	
	$StmtAdvUpdate=$iconn->query($SqlAdvUpdate);
	
	
	$SqlPaymentLog="UPDATE `HrmMonthlySalaryDetails` SET Adavance=0,MessInDays=0 
					WHERE Month='$SelectedMonth' and Year='$SelectedYear';";
	$StmtPaymentLog=$iconn->query($SqlPaymentLog);
	
		  	$SqlPaymentLog="UPDATE `HrmMonthlySalaryDetails` SET 
Adavance=(SELECT COALESCE(SUM(PaymentAmount),0) FROM HrmCashAdvancePayments WHERE
 BioMetricID=HrmMonthlySalaryDetails.BioMetricID and PaymentMonth='$SelectedMonth' 
 and PaymentYear='$SelectedYear'),
 MessInDays=(SELECT COALESCE(SUM(Amount),0) FROM EmpMessDeductionDetails WHERE
 BioID=HrmMonthlySalaryDetails.BioMetricID and Month='$SelectedMonth' 
 and Year='$SelectedYear')
WHERE Month='$SelectedMonth' and Year='$SelectedYear';";
	
	echo 'Salary Advance Updated <br>';
	$StmtPaymentLog=$iconn->query($SqlPaymentLog);
	
	if($StmtPaymentLog){
	echo "Salary Advance Updated"	;
	}else{
		echo "Failed";	
	}
	 echo '<br>';
	  
  }
 
  
   


	

?>