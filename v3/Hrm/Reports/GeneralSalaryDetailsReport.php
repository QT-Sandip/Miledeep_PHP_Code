<?php
//ini_set( 'display_errors', 1 );
//error_reporting( E_ALL );
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");

extract($_POST);
extract($_GET);

  $QueryParms=$_GET['Q']; 


if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
  
  
  $DeptID=$_GET['DeptID']; 
  $SubDeptID=$_GET['SubDeptID'];  
  $Status=$_GET['Date'];  



   $content1 .= '<h3 align="center">Employee Salary Details </h3>';
echo $content1;


      


$file_name='Employee List.pdf';
			
			
if($SubDeptID!='' ){
	if($Status!=''){
	$query="Where HED.SubDeptID='$SubDeptID' and HED.EmpStatus='$Status'";
	}
	else{
		$query="Where HED.SubDeptID='$SubDeptID'";
	}
}
else if($DeptID !=''){
	if($Status!=''){
	$query="Where HED.DeptID='$DeptID' and HED.EmpStatus='$Status'";
	}
	else{
		$query="Where HED.DeptID='$DeptID' ";
	
	}
}
else{
	//$query="";
	if($Status!=''){
	$query="Where HED.EmpStatus='$Status'";
	}
	else{
$query="";	
	}
}	
	
	
		
$SqlEmployeeCompleteDetails="SELECT 
HED.EmpID, HED.BioMetricID, HED.DeptID, HED.SubDeptID, HED.EmpName,
HED.EmpCategory,  HED.DesigID, HED.ShiftID,  HED.JoiningDate,
 HED.EmpStatus,HED.EmpGender,HED.ContractorID,
HD.DeptID,HD.DepartmentName,
HSD.SubDeptID,HSD.SubDeptName,
HEPD.EmpID,HEPD.PhoneNumber,HEPD.PresentAddress,HEPD.PermanentAddress,
HEPD.DateOfBirth,HEPD.AadharNumber,HEPD.ESICNo,HEPD.PFNO,HEPD.PANNo,HEPD.MaritalStatus,
HESD.Basic,HESD.HRA,
HESD.DailyAllowance,HESD.SpecialAllowance,HESD.MedicalAllowance,
HESD.Conveyence,HESD.Others,HESD.Incentives,
HEBAD.EmpID,HEBAD.AccNumber,HEBAD.BankID,HEBAD.IFSC,
HDG.DesigID,HDG.Designation,HCL.ContractorID,
HCL.ContractorName,
(SELECT sum(Basic+HRA+DailyAllowance+SpecialAllowance+MedicalAllowance+Conveyence+Others+Incentives)
FROM `HrmEmpSalaryDetails` WHERE EmpID=HED.EmpID) as GrossSalary 

FROM HrmEmployeeDetails HED 
LEFT JOIN HrmDepartments HD ON HD.DeptID=HED.DeptID
LEFT JOIN HrmContractorList HCL ON HCL.ContractorID=HED.ContractorID
LEFT JOIN HrmSubDepartments HSD ON HSD.SubDeptID=HED.SubDeptID
LEFT JOIN HrmDesignations HDG ON HDG.DesigID=HED.DesigID
LEFT JOIN HrmEmpPersonalDetails HEPD ON HEPD.EmpID=HED.EmpID
LEFT JOIN HrmEmpSalaryDetails HESD ON HESD.EmpID=HED.EmpID
LEFT JOIN HrmEmpBankAccountDetails HEBAD ON HEBAD.EmpID=HED.EmpID

 $query order by EmpName asc";
//ABD.BankName, LEFT JOIN AdminBankDetails ABD ON ABD.BankID=HEBAD.BankID
		
//echo $SqlEmployeeCompleteDetails;
//EXIT;
    $table_data=getP_TABLE($iconn,$SqlEmployeeCompleteDetails);     
    echo $table_data['TABLE'];

function getP_TABLE($iconn,$SqlEmployeeCompleteDetails){

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");	

			  $StmtEmpCompltDtils=$iconn->query($SqlEmployeeCompleteDetails);
			  $RowEmpCompltDtils = $StmtEmpCompltDtils->fetch_array();
			//  $StmtEmpCompltDtils=$iconn->query($SqlEmployeeCompleteDetails);
				


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
				<th  align="center" width="'.$width.'%" ><B>ID NO</B></th>
                <th  align="center"  width="'.$width.'%"><B>NAME</B></th>
                <th  align="center"  width="'.$width.'%" ><B>DEPT </B></th> 
                <th  align="center" width="'.$width.'%"><B>DESIGNATION</B></th>
                <th  align="center" width="'.$width.'%"><B>CONTRACTOR</B></th>
                <th  align="center" width="'.$width.'%" ><B>DOJ/DOB</B></th>
                <th  align="center" width="'.$width.'%" ><B>GENDER</B></th>
                <th  align="center" width="'.$width.'%" ><B>SALARY</B></th>
                <th  align="center" width="'.$width.'%" ><B>PHONE NO</B></th>
                <th  align="center" width="'.$width.'%" ><B>ACC</B></th>
				<th  align="center" width="'.$width.'%" ><B>AADHAR NUMBER</B></th>
				<th  align="center" width="'.$width.'%" ><B>PF NO</B></th>
				<th  align="center" width="'.$width.'%" ><B>ESI NO</B></th>
				<!--<th  align="center" width="'.$width.'%" ><B>INSURANCE NO</B></th> -->
                <th  align="center" width="'.$width.'%" ><B>PAN NO</B></th>



               </tr>

			  ';

	
		while($RowEmpCompltDtils = $StmtEmpCompltDtils->fetch_array()){

$EmpName=$RowEmpCompltDtils['EmpName'];
$BioMetricID=$RowEmpCompltDtils['BioMetricID'];
$DeptName=$RowEmpCompltDtils['DepartmentName'];
$SubDeptName=$RowEmpCompltDtils['SubDeptName'];
$Designation=$RowEmpCompltDtils['Designation'];
$ContractorName=$RowEmpCompltDtils['ContractorName'];
$Designation=$RowEmpCompltDtils['Designation'];
$PhoneNumber=$RowEmpCompltDtils['PhoneNumber'];
$PresentAddress=$RowEmpCompltDtils['PresentAddress'];
$PermanentAddress=$RowEmpCompltDtils['PermanentAddress'];
$AadharNumber=$RowEmpCompltDtils['AadharNumber'];
$PANNo=$RowEmpCompltDtils['PANNo'];
$DOB=$RowEmpCompltDtils['DateOfBirth'];
$DOJ=$RowEmpCompltDtils['JoiningDate'];
if($DOB!=''){
 $DOB=date('d-m-Y', strtotime($DOB)); 
}

if($DOJ!=''){
 $DOJ=date('d-m-Y', strtotime($DOJ)); 
}


$Basic=$RowEmpCompltDtils['Basic'];
$GrossSalary=$RowEmpCompltDtils['GrossSalary'];
$HRA=$RowEmpCompltDtils['HRA'];
$AccNumber=$RowEmpCompltDtils['AccNumber'];
$BankID=$RowEmpCompltDtils['BankID'];
$MaritalStatus=$RowEmpCompltDtils['MaritalStatus'];
$ESICNo=$RowEmpCompltDtils['ESICNo'];
$PFNO=$RowEmpCompltDtils['PFNO'];
$EmpGender=$RowEmpCompltDtils['EmpGender'];


//$SqlBankName="SELECT * FROM AdminBankDetails WHERE ID='$BankID'";
//$StmtBankName=$iconn->query($SqlBankName);
//$RowBankName = $StmtBankName->fetch_array();
$BankName=$RowBankName['BankName'];

				    $content .=

					'<tr>
		<td  align="left" width="'.$width_s.'%">'.$S_NO.'</td>
		<td  align="left" width="'.$width.'%">'.$BioMetricID.'</td>
		<td  align="left" width="'.$width.'%">'.$EmpName.'</td>
		<td  align="left" width="'.$width.'%">'.$DeptName.'</td>
		<td  align="left" width="'.$width.'%" >'.$Designation.'</td>
		<td  align="left" width="'.$width.'%" >'.$ContractorName.'</td>
		<td  align="left" width="'.$width.'%" >'.$DOJ.'/'.$DOB.'</td>
		<td  align="left" width="'.$width.'%" >'.$EmpGender.'</td>		
        <td  align="left" width="'.$width.'%">'. $GrossSalary .'</td>
        <td  align="left" width="'.$width.'%">'.$PhoneNumber.'</td>
		<td  align="left" width="'.$width.'%">'. $AccNumber .'</td>
		<td  align="left" width="'.$width.'%" >'.$AadharNumber.'</td>
		
		<td  align="left" width="'.$width.'%" >'.$PFNO.'</td>
		<td  align="left" width="'.$width.'%" >'.$ESICNo.'</td>
		<td  align="left" width="'.$width.'%" >'.$PANNo.'</td>


				 </tr>';

 $S_NO++;
}


		$content .= '</table>';

		$Data['TABLE']=$content;
		
	return $Data;

}

?>
