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
 
   $content1 .= '<h3 align="center">Commission  Report -'.$MonthName.'('.$Year.')</h3>';
echo $content1;


      


$file_name='Commission Report.pdf';
			
			
			
if($SubDeptID!='' ){
	
$query=" AND HED.SubDeptID='$SubDeptID' ";
}elseIF($DeptID!=''){
	$query="AND HED.DeptID='$DeptID' ";
}

	
  $SqlContractor="SELECT * FROM `HrmContractorList` order by ContractorName asc"; 
		
//echo $SqlOnTimeEmpDetails;

    $table_data=getP_TABLE($iconn,$SqlContractor);     
    echo $table_data['TABLE'];

function getP_TABLE($iconn,$SqlContractor){

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");	
global $Month,$Year,$query;

$StmtContractor=$iconn->query($SqlContractor);
			  

	$content='';


	$size=40;

	$size1=60;




			  $S_NO=1;
			  $width=120/14;
			  $width_s=$width-5;
			  $width_w=$width+5;


			  $content .= '
	  <table  class="blue" border="1" cellspacing="0" cellpadding="3">
                           
			   <tr>



                <th  align="center"  width="'.$width_s.'%"><B>S No.</B></th>
                <th  align="center"  width="'.$width.'%"><B>Contractor Name</B></th>				
                <th  align="center"  width="'.$width.'%"><B>No.Of Wage Days</B></th>
				<th  align="center" width="'.$width.'%"><B>Amount</B></th>
                <th  align="center" width="'.$width.'%"><B>Signature</B></th>
                
                
               </tr>

			  ';
while($RowContractor = $StmtContractor->fetch_array()){
	
	$ContractorID=$RowContractor['ContractorID'];
	$ContractorName=$RowContractor['ContractorName'];
	$Commission=$RowContractor['Commission'];
	$content .= '		<tr>
		<td  align="center" width="'.$width_s.'%">'.$S_NO.'</td>
		<td  align="left" width="'.$width.'%">'.$ContractorName.'</td>';
		
 $SqlOnTimeEmpDetails="SELECT ContractorName, sum(SingleDuty)  as TotalWorkingdays from HrmEmployeeDetails HED
LEFT JOIN HrmMonthlySalaryDetails HMS ON HMS.BioMetricID=HED.BioMetricID 
LEFT JOIN HrmContractorList HCL ON HCL.ContractorID=HED.ContractorID 
WHERE EmpStatus=1 and HMS.Month='$Month' and HMS.Year='$Year' and HED.ContractorID='$ContractorID' $query"; 


/*SELECT ContractorName, sum(SingleDuty)  as TotalWorkingdays from HrmEmployeeDetails HED
LEFT JOIN HrmMonthlySalaryDetails HMS ON HMS.BioMetricID=HED.BioMetricID 
LEFT JOIN HrmContractorList HCL ON HCL.ContractorID=HED.ContractorID 
WHERE EmpStatus=1 and HMS.Month='$Month' and HMS.Year='$Year' and HED.ContractorID='$ContractorID' $query;*/


			  /*$StmtOnTimeEmpDetails=$iconn->query($SqlOnTimeEmpDetails);
			  $RowEmpCompltDtils = $StmtOnTimeEmpDetails->fetch_array();*/
				
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

 
}
$S_NO++; }

		$content .= '</table>';

		$Data['TABLE']=$content;
		
	return $Data;

}

?>

