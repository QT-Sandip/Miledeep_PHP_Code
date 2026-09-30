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
 
   $content1 .= '<h3 align="center">Employee ONDUTY Report -'.$MonthName.'('.$Year.')</h3>';
echo $content1;


      


$file_name='Employee ONDUTY Report.pdf';
			
			
			
if($SubDeptID!='' ){
	
$query=" AND HED.SubDeptID='$SubDeptID' ";
}elseIF($DeptID!=''){
	$query="AND HED.DeptID='$DeptID' ";
}

	
  $OnDutyEmpDetails="SELECT * FROM  HrmOnDutyRegister HODR 
LEFT JOIN  HrmEmployeeDetails HED  ON HED.EmpID=HODR.EmpID
LEFT JOIN HrmDepartments HD  ON HD.DeptID=HED.DeptID
LEFT JOIN HrmSubDepartments HSD  ON HSD.SubDeptID=HED.SubDeptID
LEFT JOIN HrmDesignations HDG  ON HDG.DesigID=HED.DesigID

WHERE month(StartDate)='$Month' AND Year(StartDate)='$Year' $query"; 
		
 //echo $OnDutyEmpDetails;

    $table_data=getP_TABLE($iconn,$OnDutyEmpDetails);     
    echo $table_data['TABLE'];

function getP_TABLE($iconn,$OnDutyEmpDetails){

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");	

			  $StmtOnDutyEmpDetails=$iconn->query($OnDutyEmpDetails);
			  $RowOnDutyEmpDetails = $StmtOnDutyEmpDetails->fetch_array();
				
			  $StmtOnDutyEmpDetails=$iconn->query($OnDutyEmpDetails);


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
                <th  align="center" width="'.$width.'%" ><B>From Date</B></th>
                


               </tr>

			  ';

	
		while($RowOnDutyEmpDetails = $StmtOnDutyEmpDetails->fetch_array()){

$EmpName=$RowOnDutyEmpDetails['EmpName'];
$BioMetricID=$RowOnDutyEmpDetails['BioMetricID'];
$DeptName=$RowOnDutyEmpDetails['DepartmentName'];
$SubDeptName=$RowOnDutyEmpDetails['SubDeptName'];
$Designation=$RowOnDutyEmpDetails['Designation'];
$StartDate=$RowOnDutyEmpDetails['StartDate'];
$ShiftName=$RowOnDutyEmpDetails['ShiftName'];
$StartDate = date("d-m-Y", strtotime($StartDate));




				    $content .=

					'<tr>
		<td  align="center" width="'.$width_s.'%">'.$S_NO.'</td>
		<td  align="center" width="'.$width.'%">'.$BioMetricID.'</td>
		<td  align="left" width="'.$width.'%">'.$EmpName.'</td>
		<td  align="left" width="'.$width.'%">'.$DeptName.'</td>
		<td  align="left" width="'.$width.'%">'.$SubDeptName.'</td>
		<td  align="left" width="'.$width.'%" >'.$Designation.'</td>
		<td  align="left" width="'.$width.'%" >'.$StartDate.'</td>
		
				 </tr>';

 $S_NO++;
}


		$content .= '</table>';

		$Data['TABLE']=$content;
		
	return $Data;

}

?>
