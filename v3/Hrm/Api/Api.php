	<?php
header('Access-Control-Allow-Origin: *'); 

	
	require_once("Rest.inc.php");
	
	class API extends REST {
		
		
		public $data = "";
		
	
	    const DB_SERVER = "localhost"; 
		const DB_USER = "u177334400_jlsf";
		const DB_PASSWORD = "@4y=D7nqK1:Ze$";
		const DB = "u177334400_jlsf";
	
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}
		
	

		
		private function dbConnect(){			
			$this->db = mysql_connect(self::DB_SERVER,self::DB_USER,self::DB_PASSWORD);		
			if($this->db){
			mysql_select_db(self::DB,$this->db);
		//	echo "Connected";
			}			
		}
		


	    public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['f'])));
			// $func = trim(str_replace("/","",$_REQUEST['f']));
			
			if((int)method_exists($this,$func) > 0){
				$headers = apache_request_headers();
				$USER_ID=0;
			foreach ($headers as $header => $value) {
				IF($header==strtolower('USER_ID')){
				$USER_ID=$value;
			}
			}
			//$USER_ID=$this->logData($func);
		    $this->$func($USER_ID);
			}else{
			//	$this->response('',404);
			}
			
			 $this->$func($USER_ID);
			}
	



		public function postPoCreate(){
		
			
		$JsonData = file_get_contents('php://input');
		$JsonData = json_decode($JsonData,true);
		
		$LotNo=$JsonData['LotNo'];
		$ExporterID=$JsonData['ExporterID'];
		$ProcessorID=$JsonData['ProcessorID'];
		$InvNo=$JsonData['InvNo'];
		$InvDate=$JsonData['InvDate'];
		$PoNo=$JsonData['PoNo'];
		$PoDate=$JsonData['PoDate'];
		$TypeCountry=$JsonData['TypeCountry'];
		$SciName=$JsonData['SciName'];
		$ConsigneeID=$JsonData['ConsigneeID'];
		$Brand=$JsonData['Brand'];
		$FinalDestination=$JsonData['FinalDestination'];
		$PostedUserID=$JsonData['PostedUserID'];
		$ClientID=$JsonData['ClientID'];
		
		$PoStatus='1';
				$CreatedDate=date('Y-m-d');

		//$Year=date('Y', strtotime($PoDate));
		$Year=date('Y', strtotime($CreatedDate));
		$Month=date('m', strtotime($PoDate));
		if($Month > 3){
		$StartDate=($Year).'-04-01';
		$EndDate=($Year+1).'-03-31';
		}else{
		$StartDate=($Year-1).'-04-01';
		$EndDate=($Year).'-03-31';
		}
		
			$SqlPoTotal="SELECT count(*) as PoTotal,PoNo from ScrmPoData WHERE InvNo='$InvNo' and ClientID='$ClientID' 
		and PoDate between '$StartDate' and '$EndDate'";
		$PoTotalExec=mysql_query($SqlPoTotal);
		$RowPoTotal=mysql_fetch_assoc($PoTotalExec);
		
		$IsValid=false;
		if($RowPoTotal['PoTotal'] > 0){
			$PoNumber=$RowPoTotal['PoNo'];
			$ErrorMsg="Invoice Number Already Present With Po Number $PoNumber";
		}else{
			$IsValid=true;
		}
		
		
		if($IsValid){
		 $SqlPoData="INSERT INTO `ScrmPoData`(`InvNo`, `InvDate`, `ExporterID`, `ProcessorID`, `LotNo`, `SciName`,
		`ConsigneeID`, `PoNo`, `PoDate`, `TypeCountry`, `PoStatus`, `Year`, `FinalDestination`, 
		`Brand`,`PostedUserID`,`ClientID`,`CreatedDate`) VALUES ('$InvNo','$InvDate','$ExporterID','$ProcessorID','$LotNo','$SciName','$ConsigneeID',
		'$PoNo','$PoDate','$TypeCountry','$PoStatus','$Year','$FinalDestination','$Brand','$PostedUserID','$ClientID','$CreatedDate')";
		//exit;
		$PoExec = mysql_query($SqlPoData,$this->db);
		$PoID = mysql_insert_id();
		
		$QueryParms='PoID='.$PoID;
		$QueryParms=urlEncode64($QueryParms);
		}		
		
		if($IsValid && $PoExec){					
			$records = array('Error' => null, "Msg" => "Po Created Successfully","POID" => $QueryParms);
			$this->response($this->json($records), 200);
			header('Location: DocPoList.php');
		}else{
			// echo("Error description: " . $this->db -> error);
			$DbError=mysql_errno($this->db);
			$records = array('Error' => $ErrorMsg, "Msg" => $DbError);
			$this->response($this->json($records), 200);
		}												
}


public function postBioMetricData(){


		$CheckIn=0;
		$CheckOut=1;	
		
		$JsonIpData = file_get_contents('php://input');
		$AryData = json_decode($JsonIpData,true);  		
			
		$Error= $AryData['Error'];
		$ClientID= $AryData['ClientID'];
		$UserID= $AryData['UserID'];
		$MachineIp=$AryData['MachineIp'];
		$UserIp=$AryData['UserIp'];
		$AttendanceData=$AryData['AttendanceData'];
	 
			 
	$TempTable='HrmDailyAttendnessTemp';
	
	$SqlPreviousDate="Truncate $TempTable";
	$SqlcaPreviousDate= mysql_query($SqlPreviousDate,$this->db);

	

 
foreach($AttendanceData as $RowAttData){
	
  $BioMetricId=$RowAttData['BioMetricId'];
  $Date=date( "Y-m-d", strtotime( $RowAttData['Date']));	
  $TimeIn=date( "H:i:s", strtotime( $RowAttData['Date']));
  $Status=$RowAttData['PunchType'];
  $DateBioID=str_replace('-','',$Date);
  $DateBioID=$DateBioID.$BioMetricId;  
  $SqlTempArray[]="('$BioMetricId','$DateBioID','$Date','$TimeIn','$Status','$MachineIp')";
}

$SqlTempValues=implode(',',$SqlTempArray);

$SqlTemp="INSERT INTO $TempTable (`BioMetricId`,`DateBioID`, `Date`, `TimeIn`, `Status`,`DeviceID`) VALUES $SqlTempValues";
$sqlca_post = mysql_query($SqlTemp,$this->db);
$SqlTemp="INSERT INTO HrmDailyAttendnessBackUp (`BioMetricId`,`DateBioID`, `Date`, `TimeIn`, `Status`,`DeviceID`) VALUES $SqlTempValues";
$sqlca_post = mysql_query($SqlTemp,$this->db);

if($sqlca_post){	
	
	
	$SqlDeleteDuplicateRecords="DELETE t1 FROM HrmDailyAttendnessTemp  t1 INNER JOIN HrmDailyAttendnessTemp  t2 
					WHERE t1.AttID < t2.AttID AND  
					t1.Date = t2.Date and 
					t1.BioMetricID=t2.BioMetricID and 
					t1.TimeIn=t2.TimeIn and 
					t2.TimeOut=t2.TimeOut;";
	$DeleteDuplicateRecordsExec = mysql_query($SqlDeleteDuplicateRecords,$this->db);

	
	$SqlMaxTimeINReocrds="DELETE t1.* FROM HrmDailyAttendnessTemp t1 LEFT JOIN (SELECT AttID, min(TimeIn) TimeIn
								FROM HrmDailyAttendnessTemp WHERE Status=0 GROUP BY Date,BioMetricID) 
								t2 ON t1.AttID = t2.AttID WHERE Status=0 and t2.TimeIn IS NULL;";
	
	
	
	$MaxTimeINReocrdsExec = mysql_query($SqlMaxTimeINReocrds,$this->db);


	
	
	$SqlMinTimeOutReocrds="DELETE t1.* FROM HrmDailyAttendnessTemp t1
								LEFT JOIN (SELECT AttID, max(TimeIn) TimeIn
								FROM HrmDailyAttendnessTemp WHERE Status=1
								GROUP BY Date,BioMetricID) t2 ON t1.AttID = t2.AttID WHERE  Status=1  and  t2.TimeIn IS NULL;";
	$MinTimeOutReocrdsExec = mysql_query($SqlMinTimeOutReocrds,$this->db);

	
}



 $sql="select ai.BioMetricID,ai.DateBioID,ai.Date,ai.Status,COALESCE(AL.DateBioID ,0) as Ref,
(SELECT COALESCE(min(TimeIn),'0') from HrmDailyAttendnessTemp WHERE BioMetricID=ai.BioMetricID and Date=ai.Date and Status=0 ) as TimeIn,
(SELECT COALESCE(max(TimeIn),'0') from HrmDailyAttendnessTemp WHERE BioMetricID=ai.BioMetricID and Date=ai.Date and Status=1 ) as TimeOut,
count(*) as count,ai.DeviceID
from HrmDailyAttendnessTemp ai  LEFT JOIN HrmDailyAttendnessLog AL on  ai.DateBioID=AL.DateBioID
group by BioMetricID,Date;";
$sqlca_bplco = mysql_query($sql,$this->db);

while($row = mysql_fetch_array($sqlca_bplco,MYSQL_ASSOC)){
$DateBioID=$row['DateBioID'];
$employee_id=$row['BioMetricID'];
$date=$row['Date'];
$TimeIn=$row['TimeIn'];
$TimeOut=$row['TimeOut'];
$DeviceID=$row['DeviceID'];
$Status=$row['Status'];
$Status=$row['Ref'];

if($row['Ref']==0){
$SqlAryValues[]="('$employee_id','$DateBioID','$date','$TimeIn','$TimeOut','$Status','$DeviceID')";	
}else {
	
	if($row['TimeIn']=='0' && $row['TimeOut'] !='0'){
		$SqlUpdate="UPDATE HrmDailyAttendnessLog set TimeOut='$TimeOut' WHERE DateBioID='$DateBioID'";
		
	}else if($row['TimeOut']=='0' && $row['TimeIn'] !='0'){
		$SqlUpdate="UPDATE HrmDailyAttendnessLog set TimeIn='$TimeIn' WHERE DateBioID='$DateBioID'";
	}

	$UpdateExec = mysql_query($SqlUpdate,$this->db);	

}
}

$SqlAttValues=implode(',',$SqlAryValues);


  $SqlAtt="INSERT INTO `HrmDailyAttendnessLog`(`BioMetricID`,`DateBioID`, `Date`, `TimeIn`, `TimeOut`,`Status`,`DeviceID`) VALUES 
$SqlAttValues";
if($SqlAttValues!=''){
$StmtAtt = mysql_query($SqlAtt,$this->db);
}else{
$StmtAtt=true;	
}

if($StmtAtt){
$SqlDeleteDup="DELETE t1 FROM HrmDailyAttendnessLog  t1 INNER JOIN HrmDailyAttendnessLog  t2 
WHERE t1.AttID < t2.AttID AND  
t1.Date = t2.Date and 
t1.BioMetricID=t2.BioMetricID and 
t1.TimeIn=t2.TimeIn and 
t2.TimeOut=t2.TimeOut";
//$StmtDup = mysql_query($SqlDeleteDup,$this->db);
	$records = array('Error' => false, "Msg" => "Data Saved successfully");
	$this->response($this->json($records), 200);
}else{
	$records = array('Error' => "Unable To Submit Data", "Msg" => "Data saved successfully");
	$this->response($this->json($records), 200);
}
	

}


		private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}			
			mysql_close($this->db);			
		}
		}
		
		function urlEncode64($input) {
 return strtr(base64_encode($input), '+/=', '._-');
}

function urlDecode64($input) {
 return base64_decode(strtr($input, '._-', '+/='));
} 


		$api = new API;
		$api->processApi();
?>