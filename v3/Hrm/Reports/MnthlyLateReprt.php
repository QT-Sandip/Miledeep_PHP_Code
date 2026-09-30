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
 
   $content1 .= '<h3 align="center">Employee Late Comings Report -'.$MonthName.'('.$Year.')</h3>';
echo $content1;


      


$file_name='Employee Late Comings Report.pdf';
			
			
			
if($SubDeptID!='' ){
	
$query=" AND HED.SubDeptID='$SubDeptID' ";
}elseIF($DeptID!=''){
	$query="AND HED.DeptID='$DeptID' ";
}

 $SqlMntlyLateRprt="SELECT *,
(SELECT COUNT(*) from HrmDailyAttendnessLog WHERE BioMetricID=HED.BioMetricID AND Month='$Month'and
 Year='$Year' and OnTime=0 ) as LateCount
from HrmEmployeeDetails HED
LEFT JOIN HrmDepartments HD  ON HD.DeptID=HED.DeptID
LEFT JOIN HrmSubDepartments HSD  ON HSD.SubDeptID=HED.SubDeptID
LEFT JOIN HrmDesignations HDG  ON HDG.DesigID=HED.DesigID

WHERE EmpStatus=1 $query  ORDER BY LateCount DESC";
	
  		
 //echo $SqlMntlyLateRprt;

    $table_data=getP_TABLE($iconn,$SqlMntlyLateRprt);     
    echo $table_data['TABLE'];

function getP_TABLE($iconn,$SqlMntlyLateRprt){

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");	

			  $StmtMntlyLateRprt=$iconn->query($SqlMntlyLateRprt);
			  $RowMonthlyLateComngs = $StmtMntlyLateRprt->fetch_array();
				
			  $StmtMntlyLateRprt=$iconn->query($SqlMntlyLateRprt);


	$content='';


	$size=40;

	$size1=60;




			  $S_NO=1;
			  $width=120/14;
			  $width_s=$width-5;
			  $width_w=$width+5;


			  $content .= '
	  <table border="1" cellspacing="0" cellpadding="3">
                           
			   <tr>



                <th  align="center"  width="'.$width_s.'%"><B>S No.</B></th>
				<th  align="center" width="'.$width.'%" ><B> BioMetric ID </B></th>
                <th  align="center"  width="'.$width.'%"><B>Emp Name</B></th>
                <th  align="center"  width="'.$width.'%" ><B>Dept </B></th> 
                <th  align="center" width="'.$width.'%"><B>Sub Dept </B></th>
                <th  align="center" width="'.$width.'%" ><B>Designation</B></th>
                <th  align="center" width="'.$width.'%" ><B>No Of LateComing</B></th>
                


               </tr>

			  ';

	
		while($RowMonthlyLateComngs = $StmtMntlyLateRprt->fetch_array()){

$EmpName=$RowMonthlyLateComngs['EmpName'];
$BioMetricID=$RowMonthlyLateComngs['BioMetricID'];
$DeptName=$RowMonthlyLateComngs['DepartmentName'];
$SubDeptName=$RowMonthlyLateComngs['SubDeptName'];
$Designation=$RowMonthlyLateComngs['Designation'];
$LateCount=$RowMonthlyLateComngs['LateCount'];



				    $content .=

					'<tr>
		<td  align="center" width="'.$width_s.'%">'.$S_NO.'</td>
		<td  align="center" width="'.$width.'%">'.$BioMetricID.'</td>
		<td  align="left" width="'.$width.'%">'.$EmpName.'</td>
		<td  align="left" width="'.$width.'%">'.$DeptName.'</td>
		<td  align="left" width="'.$width.'%">'.$SubDeptName.'</td>
		<td  align="left" width="'.$width.'%" >'.$Designation.'</td>
		<td  align="left" width="'.$width.'%" >'.$LateCount.'</td>
		
				 </tr>';

 $S_NO++;
}


		$content .= '</table>';

		$Data['TABLE']=$content;
		
	return $Data;

}

?>
