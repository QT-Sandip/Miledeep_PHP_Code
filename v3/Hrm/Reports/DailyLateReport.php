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
  $Date=$_GET['Date'];  


$newDate = date("d-m-Y", strtotime($Date));
   $content1 .= '<h3 align="center">Employee Late Report -'.$newDate.'</h3>';
echo $content1;


      


$file_name='Employee On-Time Report.pdf';
			
			
			
if($SubDeptID!='' ){
	
$query=" AND HED.SubDeptID='$SubDeptID' ";
}elseIF($DeptID!=''){
	$query="AND HED.DeptID='$DeptID' ";
}

	
		
 $SqlOnTimeEmpDetails="SELECT *,HDAL.BioMetricID as BioId FROM $HrmDailyAttendnessLog HDAL
LEFT JOIN $HrmEmployeeDetails HED  ON HED.BioMetricID=HDAL.BioMetricID
LEFT JOIN $HrmContractorList HCL ON HED.ContractorID=HCL.ContractorID 
LEFT JOIN $HrmDepartments HD  ON HD.DeptID=HED.DeptID
LEFT JOIN $HrmSubDepartments HSD  ON HSD.SubDeptID=HED.SubDeptID
LEFT JOIN $HrmDesignations HDG  ON HDG.DesigID=HED.DesigID
LEFT JOIN $HrmShiftDetails HSFTD  ON HSFTD.ShiftID=HDAL.Shift
WHERE OnTime='0' AND Date='$Date' $query Order by HD.DepartmentName,HSD.SubDeptName,EmpName,TimeIn ASC";
		
 $SqlOnTimeEmpDetails;

    $table_data=getP_TABLE($iconn,$SqlOnTimeEmpDetails);     
    echo $table_data['TABLE'];

function getP_TABLE($iconn,$SqlOnTimeEmpDetails){

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");	
//echo $SqlOnTimeEmpDetails; exit;
			  $StmtOnTimeEmpDetails=$iconn->query($SqlOnTimeEmpDetails);
			  $RowEmpCompltDtils = $StmtOnTimeEmpDetails->fetch_array();
				
			  $StmtOnTimeEmpDetails=$iconn->query($SqlOnTimeEmpDetails);


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
				<th  align="center" width="'.$width.'%" ><B> Emp ID </B></th>
                <th  align="center"  width="'.$width.'%"><B>Emp Name</B></th>
                <th  align="center"  width="'.$width.'%" ><B>Dept </B></th> 
                <th  align="center" width="'.$width.'%"><B>Sub Dept </B></th>
                <th  align="center" width="'.$width.'%" ><B>Designation</B></th>
                <th  align="center" width="'.$width.'%" ><B>Contractor</B></th>
                <th  align="center" width="'.$width.'%" ><B>Shift</B></th>
                <th  align="center" width="'.$width.'%" ><B>Time In</B></th>
                <th  align="center" width="'.$width.'%" ><B>Time Out</B></th>
                <th  align="center" width="'.$width.'%" ><B>Late By</B></th>



               </tr>

			  ';

	
		while($RowEmpCompltDtils = $StmtOnTimeEmpDetails->fetch_array()){

$EmpName=$RowEmpCompltDtils['EmpName'];
//$BioMetricID=$RowEmpCompltDtils['BioMetricID'];
 $BioMetricID=$RowEmpCompltDtils['BioId'];
$DeptName=$RowEmpCompltDtils['DepartmentName'];
$SubDeptName=$RowEmpCompltDtils['SubDeptName'];
$Designation=$RowEmpCompltDtils['Designation'];
$TimeIn=$RowEmpCompltDtils['TimeIn'];
$TimeOut=$RowEmpCompltDtils['TimeOut'];
$ShiftName=$RowEmpCompltDtils['ShiftName'];
$StartTime=$RowEmpCompltDtils['StartTime'];
$ContractorName=$RowEmpCompltDtils['ContractorName'];
$Time1= strtotime($TimeIn);
$Time2= strtotime($StartTime);
$LateBy=round(abs($Time1 - $Time2) / 60,2);


				    $content .= 

					'<tr>
		<td  align="center" width="'.$width_s.'%">'.$S_NO.'</td>
		<td  align="center" width="'.$width.'%">'.$BioMetricID.'</td>
		<td  align="left" width="'.$width.'%">'.$EmpName.'</td>
		<td  align="left" width="'.$width.'%">'.$DeptName.'</td>
		<td  align="left" width="'.$width.'%">'.$SubDeptName.'</td>
		<td  align="left" width="'.$width.'%" >'.$Designation.'</td>
		<td  align="left" width="'.$width.'%" >'.$ContractorName.'</td>
		<td  align="left" width="'.$width.'%" >'.$ShiftName.'</td>
		<td  align="left" width="'.$width.'%" >'.$TimeIn.'</td>
		<td  align="left" width="'.$width.'%" >'.$TimeOut.'</td>
		<td  align="left" width="'.$width.'%" >'.$LateBy.'.Mins</td>
        

				 </tr>';

 $S_NO++;
}


		$content .= '</table>';

		$Data['TABLE']=$content;
		
	return $Data;

}

?>
