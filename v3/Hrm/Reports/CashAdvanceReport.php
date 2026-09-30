<html>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

</html>

<?php



session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");

extract($_POST);
extract($_GET);


$ClientID=$_SESSION["ClientID"];

 $QueryParms=$_GET['Q']; 

if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }

  $DeptID=$_GET['DeptID']; 
  $SubDeptID=$_GET['SubDeptID'];  
  $Month=$_GET['Month'];  
  $Year=$_GET['Year']; 
  
 $MonthName = date('F', mktime(0, 0, 0, $Month, 10)); 
 
   $content1 .= '<h3 align="center">Cash Advance  Report -'.$MonthName.'('.$Year.')</h3>';
   
echo $content1;


      


$file_name='Cash Advance Report.pdf';
			
			
			
if($SubDeptID!='' ){
	
$query=" AND HED.SubDeptID='$SubDeptID' ";
}elseIF($DeptID!=''){
	$query="AND HED.DeptID='$DeptID' ";
}

	
  $SqlSalaryDetails="SELECT * FROM `HrmMonthlySalaryDetails`  as HMS
  left join HrmEmployeeDetails as HED on  HED.BioMetricID=HMS.BioMetricID
  WHERE Month='$Month'and Year='$Year'  and   Adavance!='0'"; 
	
		
//echo $SqlSalaryDetails; exit;

    $table_data=getP_TABLE($iconn,$SqlSalaryDetails);     
    echo $table_data['TABLE'];

function getP_TABLE($iconn,$SqlSalaryDetails){

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");	
global $Month,$Year,$query,$MonthName;

$StmtSalaryDetails=$iconn->query($SqlSalaryDetails);
$SalaryRecordsCount=mysqli_num_rows($StmtSalaryDetails);
			  

	$content='';


	$size=40;

	$size1=60;




			  $S_NO=1;
			  $width=120/14;
			  $width_s=$width-5;
			  $width_w=$width+5;
if($SalaryRecordsCount>0){
	
	$StmtSalaryDetails=$iconn->query($SqlSalaryDetails);

$content .= '<h4 align="center" style="color:Green;">Salary already generated for '.$MonthName.'('.$Year.')</h4>';}
else{
	$SqlSalaryDetails="SELECT * FROM `HrmCashAdvancePayments`  as HMS
  left join HrmEmployeeDetails as HED on  HED.BioMetricID=HMS.BioMetricID
  WHERE PaymentMonth='$Month'and PaymentYear='$Year'  and   DueAmount!='0'"; 
  $StmtSalaryDetails=$iconn->query($SqlSalaryDetails);
	
$content .= '<h4 align="center" style="color:Red;">Salary not yet generated for '.$MonthName.'('.$Year.') the below amounts will deducted</h4>';}
	

			  $content .= '
	  <table  class="blue" border="1" cellspacing="0" cellpadding="3">
                           
			   <tr>



                <th  align="center"  width="'.$width_s.'%"><B>S No.</B></th>
                <th  align="center"  width="'.$width.'%"><B>Bio Id</B></th>				
                <th  align="center"  width="'.$width.'%"><B>Employee Name</B></th>
				<th  align="center" width="'.$width.'%"><B>Amount</B></th>
               <!-- <th  align="center" width="'.$width.'%"><B>PaymentDate</B></th>-->
                
                
               </tr>

			  ';
while($RowContractor = $StmtSalaryDetails->fetch_array()){
	
	if($SalaryRecordsCount>0){
	$Adavance=$RowContractor['Adavance'];	
	}
	
	else{
	$Adavance=$RowContractor['DueAmount'];	
	}
	
	$content .= '		<tr>
		<td  align="center" width="'.$width_s.'%">'.$S_NO.'</td>
		<td  align="left" width="'.$width.'%">'.$RowContractor['BioMetricID'].'</td>
		<td  align="left" width="'.$width.'%">'.$RowContractor['EmpName'].'</td>
		<td  align="left" width="'.$width.'%">'.$Adavance.'</td>
		';
		
/* $SqlOnTimeEmpDetails="SELECT ContractorName, sum(SingleDuty)  as TotalWorkingdays from HrmEmployeeDetails HED
LEFT JOIN HrmMonthlySalaryDetails HMS ON HMS.BioMetricID=HED.BioMetricID 
LEFT JOIN HrmContractorList HCL ON HCL.ContractorID=HED.ContractorID 
WHERE EmpStatus=1 and HMS.Month='$Month' and HMS.Year='$Year' and HED.ContractorID='$ContractorID' $query"; 


				
			  $StmtOnTimeEmpDetails=$iconn->query($SqlOnTimeEmpDetails);

	
		while($RowEmpCompltDtils = $StmtOnTimeEmpDetails->fetch_array()){

//$EmpName=$RowEmpCompltDtils['EmpName'];

 $TotalWorkingdays=$RowEmpCompltDtils['TotalWorkingdays'];
 if($TotalWorkingdays==''){
	$TotalWorkingdays='-' ;
 }
 else{
	$TotalWorkingdays; 
 }
 $TotalCommission=$TotalWorkingdays*$Commission;

$content .=

					'
		
		<td  align="center" width="'.$width.'%" >'.$TotalWorkingdays.'</td>
		
		<td  align="left" width="'.$width.'%" >'.$TotalCommission.'</td>
		<td  align="left" width="'.$width.'%" ></td>

				 </tr>';

 
}*/
$S_NO++; }

		$content .= '</table>';

		$Data['TABLE']=$content;
		
	return $Data;

}

?>

