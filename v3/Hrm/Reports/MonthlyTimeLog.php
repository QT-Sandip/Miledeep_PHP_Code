<style>
	td {
        font-size: 12px;
    }
	
	th {
        font-size: 12px;
    }
</style>

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

$Month=$_GET['Month'];
$Year=$_GET['Year'];
$EmpID=$_GET['EmpID'];
$DeptID=$_GET['DeptID'];
$SubDeptID=$_GET['SubDeptID'];

if($SubDeptID!=''){
	$SubQuery="and HED.SubDeptID='$SubDeptID'";
}else if($DeptID !=''){
	$SubQuery="and HED.DeptID='$DeptID'";
}else{
	$SubQuery="";
}	
	
if($DeptID==''){	
	echo "Please Select Department";
	exit;	
}
$dateObj   = DateTime::createFromFormat('!m', $Month);
$monthName = $dateObj->format('F');

echo '<h1> Jaya Lakshmi Sea Foods Pvt Ltd </h1>'; 
echo '<h3 align="center">Employee Punches Report -'.$monthName.'</h3>'; 



$file_name='Employee Punches Report.pdf';
			
			
			

	
		$SqlEmpList="select BioMetricID,EmpName,
		(select count(*) from HrmDailyAttendnessLog WHERE Month(Date)='$Month' 
and Year(Date)='$Year' and BioMetricID=HED.BioMetricID) as AttCount
		from HrmEmployeeDetails as HED 
		WHERE HED.EmpStatus=1 $SubQuery order by BioMetricID asc";
		$StmtEmpList=$iconn->query($SqlEmpList);
		
		while($EmpDetails = $StmtEmpList->fetch_array()){
			if($EmpDetails['AttCount']!=0){
			
		 $BioMetricID=$EmpDetails['BioMetricID'];
		 
		
		echo '<p><B>Name           : </B>  '.$EmpDetails['EmpName'].'	 </p>'; 
		echo '<p><B>Bio Metric ID  : </B>'.$EmpDetails['BioMetricID'].'	 </p>'; 
   
		
 
  $SqlOnTimeEmpDetails="SELECT  Date,TimeIn,Status FROM `HrmDailyAttendnessBackUp` as HB
LEFT JOIN HrmEmployeeDetails HED  ON HED.BioMetricID=HB.BioMetricID
 WHERE  HB.BioMetricID='$BioMetricID' and 
 Month(Date)='$Month' and year(Date)='$Year'  order by Date,TimeIn Asc";
		

    $table_data=getP_TABLE($iconn,$SqlOnTimeEmpDetails);     
    echo $table_data['TABLE'];
			}
	}

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
