<?php
/*ini_set( 'display_errors', 1 );
error_reporting( E_ALL );*/

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");

extract($_POST);
extract($_GET);



$Month=$_GET['Month'];
$EmpID=$_GET['EmpID'];
$Year=$_GET['Year'];
if($SubDeptID!=''){
	$SubQuery="and HED.SubDeptID='$SubDeptID'";
}else if($DeptID !=''){
	$SubQuery="and HED.DeptID='$DeptID'";
}else{
	$SubQuery="";
}	
	

$dateObj = DateTime::createFromFormat('!m', $Month);
$monthName = $dateObj->format('F');
$SqlEmpList = "SELECT * FROM `HrmEmployeeDetails`  as ED 
left join  HrmDepartments as HD on HD.DeptID=ED.DeptID 
left join  HrmDesignations as HDE on HDE.DesigID=ED.DesigID 
left join  HrmBranchMaster as HBM on HBM.BranchID=ED.BranchID 
where EmpID=$EmpID";
$query = $iconn->query($SqlEmpList);
$EmpDetails = $query->fetch_array();
$BioMetricID = $EmpDetails['BioMetricID'];
$BranchName=$EmpDetails['BranchName'];

echo '<h1> '.$BranchName.'</h1>'; 
echo '<h3 align="center">Employee Punches Report -'.$monthName.'</h3>'; 

echo '<p><B>Name           : </B>  '.$EmpDetails['EmpName'].'	 </p>'; 
echo '<p><B>Bio Metric ID  : </B>'.$EmpDetails['BioMetricID'].'	 </p>'; 
   

$file_name='Employee Punches Report.pdf';
			
			
			

	
		
 
  $SqlOnTimeEmpDetails="SELECT  Date,TimeIn,Status FROM `HrmDailyAttendnessBackUp` as HB
LEFT JOIN HrmEmployeeDetails HED  ON HED.BioMetricID=HB.BioMetricID
 WHERE  HB.BioMetricID='$BioMetricID' and 
 Month(Date)='$Month' and year(Date)='$Year'  order by Date,TimeIn Asc";
		

    $table_data=getP_TABLE($iconn,$SqlOnTimeEmpDetails);     
    echo $table_data['TABLE'];

function getP_TABLE($iconn,$SqlOnTimeEmpDetails){

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");	
			  $StmtOnTimeEmpDetails=$iconn->query($SqlOnTimeEmpDetails);
			 
global $EmpID;
	$content='';


	$size=40;

	$size1=60;




			  $S_NO=1;
			  $width=25;
			  $width_s=$width-5;
			  $width_w=$width+5;


			  $content .= '
	  <table border="1" cellspacing="0" cellpadding="3">
                           
			   <tr>



                <th  align="center"  width="'.$width.'%"><B>S No.</B></th>
				
                <th  align="center"  width="'.$width.'%" ><B>Date </B></th> 
                <th  align="center" width="'.$width.'%"><B>Punching Time </B></th>
                <th  align="center" width="'.$width.'%"><B>Punching Type </B></th>
                


               </tr>

			  ';

	
		while($RowEmpCompltDtils = $StmtOnTimeEmpDetails->fetch_array()){
		 $Date=$RowEmpCompltDtils['Date'];
		 $TimeIn=$RowEmpCompltDtils['TimeIn'];
		 $Status=$RowEmpCompltDtils['Status'];
		 if($Status==0){
			$PunchType= "In Punch";
		 }else IF($Status==1){
				$PunchType= "Out Punch";
			}else IF($Status==2){
				$PunchType= "Break Out";
			}else IF($Status==3){
				$PunchType= "Break In";
			}
			else{
				$PunchType= "NA";
			}
		 
	
 
 

				    $content .= 

					'<tr>
		<td  align="center" width="'.$width.'%">'.$S_NO.'</td>
<td  align="left" width="'.$width.'%">'.date('d-m-Y',strtotime($Date)).'</td>
		<td  align="left" width="'.$width.'%">'.$TimeIn.'</td>
		<td  align="left" width="'.$width.'%">'.$PunchType.'</td>
		 

				 </tr>';

 $S_NO++;
		}	


		$content .= '</table>';

		$Data['TABLE']=$content;
		
	return $Data;

}

?>
