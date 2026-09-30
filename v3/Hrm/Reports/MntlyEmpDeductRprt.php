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
 
   $content1 .= '<h3 align="center">Employee Deductions Report -'.$MonthName.'('.$Year.')</h3>';
echo $content1;


      


$file_name='Employee Deductions Report.pdf';
			
			
			
if($SubDeptID!='' ){
	
$query=" AND HED.SubDeptID='$SubDeptID' ";
}elseIF($DeptID!=''){
	$query="AND HED.DeptID='$DeptID' ";
}

	
  $SqlNewEmpDetails="SELECT * FROM  $HrmEmployeeDetails HED  
LEFT JOIN $HrmDepartments HD  ON HD.DeptID=HED.DeptID
LEFT JOIN $HrmSubDepartments HSD  ON HSD.SubDeptID=HED.SubDeptID
LEFT JOIN $HrmDesignations HDG  ON HDG.DesigID=HED.DesigID

WHERE month(JoiningDate)='$Month' AND Year(JoiningDate)='$Year' $query"; 
		
 //echo $SqlNewEmpDetails;

    $table_data=getP_TABLE($iconn,$SqlNewEmpDetails);     
    echo $table_data['TABLE'];

function getP_TABLE($iconn,$SqlNewEmpDetails){

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");	

			  $StmtNewEmpDetails=$iconn->query($SqlNewEmpDetails);
			  $NewEmpDetails = $StmtNewEmpDetails->fetch_array();
				
			  $StmtNewEmpDetails=$iconn->query($SqlNewEmpDetails);


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
                <th  align="center" width="'.$width.'%" ><B>Joining Date</B></th>
                


               </tr>

			  ';

	
		while($NewEmpDetails = $StmtNewEmpDetails->fetch_array()){

$EmpName=$NewEmpDetails['EmpName'];
$BioMetricID=$NewEmpDetails['BioMetricID'];
$DeptName=$NewEmpDetails['DepartmentName'];
$SubDeptName=$NewEmpDetails['SubDeptName'];
$Designation=$NewEmpDetails['Designation'];
$JoiningDate=$NewEmpDetails['JoiningDate'];
$ShiftName=$NewEmpDetails['ShiftName'];
$JoiningDate = date("d-m-Y", strtotime($JoiningDate));




				    $content .=

					'<tr>
		<td  align="center" width="'.$width_s.'%">'.$S_NO.'</td>
		<td  align="center" width="'.$width.'%">'.$BioMetricID.'</td>
		<td  align="left" width="'.$width.'%">'.$EmpName.'</td>
		<td  align="left" width="'.$width.'%">'.$DeptName.'</td>
		<td  align="left" width="'.$width.'%">'.$SubDeptName.'</td>
		<td  align="left" width="'.$width.'%" >'.$Designation.'</td>
		<td  align="left" width="'.$width.'%" >'.$JoiningDate.'</td>
		
				 </tr>';

 $S_NO++;
}


		$content .= '</table>';

		$Data['TABLE']=$content;
		
	return $Data;

}

?>
