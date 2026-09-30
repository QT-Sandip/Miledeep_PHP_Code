<style>
table tr td{
	
	    font-size: 11px;
    text-align: left;
}

table tr th{
	
	    font-size: 12px;
    text-align: left;
	 background: white;
  position: sticky;
  top: 0;
}



</style>
<html>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

</html>

<?php

//ini_set( 'display_errors', 1 );
//error_reporting( E_ALL );

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
 
   $content1 .= '<h3 align="center">Salary  Report -'.$MonthName.'('.$Year.')</h3>';
echo $content1;


      


$file_name='Salary Report.pdf';
			
			
			
if($SubDeptID!='' ){
	
$query=" AND HED.SubDeptID='$SubDeptID' ";
}elseIF($DeptID!=''){
	$query="AND HED.DeptID='$DeptID' ";
}

	
  $SqlOnTimeEmpDetails="SELECT *,HED.BioMetricID as BioMetricID,
 (SELECT COALESCE(NoOfShortDays,0)  FROM HrmMonthlyShortDaysList as HMSL WHERE HMSL.EmpID=HED.EmpID and HMSL.Month='$Month' and HMSL.Year='$Year' ) as NoOfShortDays
  from HrmEmployeeDetails HED
LEFT JOIN HrmDepartments HD  ON HD.DeptID=HED.DeptID
LEFT JOIN HrmSubDepartments HSD  ON HSD.SubDeptID=HED.SubDeptID
LEFT JOIN HrmDesignations HDG  ON HDG.DesigID=HED.DesigID
LEFT JOIN HrmEmpPersonalDetails HPD ON HPD.EmpID=HED.EmpID

LEFT JOIN HrmEmpBankAccountDetails HMBD ON HMBD.EmpID=HED.EmpID
LEFT JOIN HrmMonthlySalaryDetails HMSD ON HMSD.BioMetricID=HED.BioMetricID

WHERE  EmpStatus=1 and HMSD.Month='$Month' and HMSD.Year='$Year' and EmpCategory='Staff' 
$query order by DepartmentName,HED.BioMetricID asc"; 
		
//echo $SqlOnTimeEmpDetails;

    $table_data=getP_TABLE($iconn,$SqlOnTimeEmpDetails);     
    echo $table_data['TABLE'];

function getP_TABLE($iconn,$SqlOnTimeEmpDetails){
global  $Month,$Year;//uma
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");	

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
	  <table width=100% class="blue" border="1" cellspacing="0" cellpadding="3">
                           
			   <tr>
                <th  align="center"><B>S No.</B></th>
				<th  align="center"><B> Bio ID </B></th>
                <th  align="center"><B>Emp Name</B></th>				
                <th  align="center"><B>Deptartment </B></th> 
                <th  align="center"><B>SubDeptartment </B></th> 
		        <th  align="center"><B>Designation</B></th>		      
                <th  align="center"><B>Basic</B></th>
                <th  align="center"><B>Single Duty</B></th>
                <th  align="center"><B>Double Duty </B></th>
                <th  align="center"><B>On Duty </B></th>
                <th  align="center"><B>Week off</B></th>
                <th  align="center"><B>Half Day</B></th>
                <th  align="center"><B>Absent</B></th>
                <th  align="center"><B>Leave</B></th>
                <th  align="center"><B>Holiday</B></th>
                <th  align="center"><B>ML</B></th>
                <th  align="center"><B>TOTAL</B></th>
                <th  align="center"><B>PAYMENT</B></th>
                <th  align="center"><B>RESERVE</B></th>
                <th  align="center"><B>Short Days</B></th>
                <th  align="center"><B>GROSS SAL</B></th>
                <th  align="center"><B>Mess</B></th>
                <th  align="center"><B>PF</B></th>
                <th  align="center"><B>ESI</B></th>
                <th  align="center"> TDS<B></B></th>
                <th  align="center"> PT<B></B></th>
                <th  align="center"> Advance<B></B></th>
                <th  align="center"> Salary Arrars<B></B></th>
                <th  align="center"> <B>Travel</B></th>
                <th  align="center"> <B>Incentive</B></th>
                <th  align="center"> <B>Penality</B></th>
                <th  align="center"> <B>W.Days Salary</B></th>
                <th  align="center"> <B>Account No</B></th>
		        <th  align="center"> <B>IFSC</B></th>
       </tr>

			  ';

	
		while($RowEmpCompltDtils = $StmtOnTimeEmpDetails->fetch_array()){

$EmpID=$RowEmpCompltDtils['EmpID'];
$EmpName=$RowEmpCompltDtils['EmpName'];
$BioMetricID=$RowEmpCompltDtils['BioMetricID'];
$DeptName=$RowEmpCompltDtils['DepartmentName'];
$SubDeptName=$RowEmpCompltDtils['SubDeptName'];
$Designation=$RowEmpCompltDtils['Designation'];
$leavecount=$RowEmpCompltDtils['leavecount'];
$StartDate=$RowEmpCompltDtils['StartDate'];
$EndDate=$RowEmpCompltDtils['EndDate'];

$TotalPresents=$RowEmpCompltDtils['TotalPresents'];
$SingleDuty=$RowEmpCompltDtils['SingleDuty'];
$DoubleDuty=$RowEmpCompltDtils['DoubleDuty'];
$OnDuty=$RowEmpCompltDtils['DaysTotal'];
$HalfDay=$RowEmpCompltDtils['HalfDay'];
$Absent=$RowEmpCompltDtils['Absent'];
$WeekOff=$RowEmpCompltDtils['WeekOff'];
$Holidays=$RowEmpCompltDtils['Holidays'];
$Leaves=$RowEmpCompltDtils['Leaves'];
$ML=$RowEmpCompltDtils['ML'];
$WageDays=$RowEmpCompltDtils['WageDays'];
$Payement=$RowEmpCompltDtils['EarnedSalaryWithoutDud'];
$PF=$RowEmpCompltDtils['PF'];
$ESI=$RowEmpCompltDtils['ESI'];
$Advance=$RowEmpCompltDtils['Adavance'];
$NoOfShortDays=$RowEmpCompltDtils['NoOfShortDays'];
$BankIFSC=$RowEmpCompltDtils['IFSC'];
$AccNo=$RowEmpCompltDtils['AccNumber'];
$MessInDays=$RowEmpCompltDtils['MessInDays'];

$GrossSalary=$Payement;
$NetSalary=round($GrossSalary-(($GrossSalary*$NoOfShortDays)/30));


$NetSalary=$NetSalary-$PF-$ESI-$Advance-$MessInDays;

$SqlOnDuty="SELECT sum(DaysTotal) as onduty from HrmOnDutyRegister where EmpID='$EmpID' and
 Month(StartDate)='$Month' and Year(StartDate)='$Year' ";
$StmtOnDuty=$iconn->query($SqlOnDuty);
$RowOnDuty = $StmtOnDuty->fetch_array();
				
 $OnDuty=$RowOnDuty['onduty'];
$content .=

					'<tr>
		<td  align="center">'.$S_NO.'</td>
		<td  align="center">'.$BioMetricID.'</td>
		<td  align="left">'.$EmpName.'</td>
		<td  align="left">'.$DeptName.'</td>
		<td  align="left">'.$SubDeptName.'</td>
		<td  align="left">'.$Designation.'</td>
		<td  align="left">'.$Basic.'</td>
		<td  align="center">'.$SingleDuty.'</td>
		<td  align="center">'.$DoubleDuty.'</td>
		<td  align="center">'.$OnDuty.'</td>
		<td  align="center">'.$WeekOff.'</td>
		<td  align="center">'.$HalfDay.'</td>
		<td  align="center">'.$Absent.'</td>
		<td  align="center">'.$Leaves.'</td>
		<td  align="center">'.$Holidays.'</td>
		<td  align="center">'.$ML.'</td>
		<td  align="center">'.$WageDays.'</td>
		<td  align="center">'.$Payement.'</td>
		<td  align="center">'.$Reserve.'</td>
		<td  align="center">'.$NoOfShortDays.'</td>
		<td  align="center">'.$GrossSalary.'</td>
		<td  align="center">'.$MessInDays.'</td>
		<td  align="center">'.$PF.'</td>
		<td  align="center">'.$ESI.'</td>
		<td  align="center">'.$TDS.'</td>
		<td  align="center">'.$PT.'</td>
		<td  align="center">'.$Advance.'</td>
		<td  align="center">'.$SalaryArars.'</td>
		<td  align="center">0</td>
		<td  align="center">0</td>
		<td  align="center">0</td>
		<td  align="center">'.$NetSalary.'</td>
		<td  align="left">'.$AccNo.'</td>
		<td  align="left">'.$BankIFSC.'</td>

				 </tr>';

 $S_NO++; 
}


		$content .= '</table>';

		$Data['TABLE']=$content;
		
	return $Data;

}

?>
