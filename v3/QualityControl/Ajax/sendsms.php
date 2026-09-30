<?php 

include "../../includes/config.php";

// header('Location: ../../includes/config.php');
 
$TYPE=$_REQUEST['TYPE'];
$DOA=$_REQUEST['DATE'];

IF($TYPE=='SHOW_DATA'){
	
	$DOAA = date("Y-m-d", strtotime($DOA));
	$DATA_TYPE=$_REQUEST['DATA_TYPE'];
	
 	  $SQL_ANTIBIO_STATUS="SELECT `SAMPLE_DETAILS`,`LOT_NUMBER`,`LOADING_STATUS` FROM `DAILY_LAB_REPORT_LIST` WHERE REPORT_DATE='$DOAA' 
	and LOADING_STATUS='$DATA_TYPE'";
			$STMT_ANTIBIO_STATUS=$iconn->query($SQL_ANTIBIO_STATUS);
			
			$MSG='';
			$T_MSG='';
			$L_STATUS='';
			
			while($ANTIBIO_STATUS_ROW=$STMT_ANTIBIO_STATUS->fetch_array()){
				 $SAMPLE_DETAILS=$ANTIBIO_STATUS_ROW['SAMPLE_DETAILS'];
				
				
				
				if($SAMPLE_DETAILS=='Pre Harvest'){
					$MSG='PH -'.$ANTIBIO_STATUS_ROW['LOT_NUMBER'];					
				}else if($SAMPLE_DETAILS=='Harvest'){
					$MSG='HA -'.$ANTIBIO_STATUS_ROW['LOT_NUMBER'];
				}else if($SAMPLE_DETAILS=='Finished'){
					$MSG='FP -'.$ANTIBIO_STATUS_ROW['LOT_NUMBER'];
				}else if($SAMPLE_DETAILS=='Store Material'){
					$MSG='SM -'.$ANTIBIO_STATUS_ROW['LOT_NUMBER'];
				}else if($SAMPLE_DETAILS=='Babu Basker'){
					$MSG='BB -'.$ANTIBIO_STATUS_ROW['LOT_NUMBER'];
				}else if($SAMPLE_DETAILS=='SPC Material'){
					$MSG='SP -'.$ANTIBIO_STATUS_ROW['LOT_NUMBER'];
				}
				
				if($ANTIBIO_STATUS_ROW['LOADING_STATUS']=='RED'){
					$L_STATUS='REJECT';
				}else if($ANTIBIO_STATUS_ROW['LOADING_STATUS']=='BLUE'){
					$L_STATUS='HOLD';
				}else{
					$L_STATUS='ACCEPT';
				}
				
				if($T_MSG==''){
					$T_MSG=$MSG.'-'.$L_STATUS;
				}else{
					$T_MSG=$T_MSG.'<br>'.$MSG.'-'.$L_STATUS;
					
				}
				
				
				
				
				 
				 
			}
			
			//print($T_MSG);
			
			echo $T_MSG;
			
	
	
}ELSE{
	

$T_MSG=$_REQUEST['MSG'];
$NUMBER=$_REQUEST['NUMBER'];

$ARY_NUMBER = explode(',', $NUMBER);
	
foreach($ARY_NUMBER as $value){	

//$API_KEY='VP32DKN3DW6E6NBJJEYCU1H45BHCM28Z'; // TEST
$API_KEY='DXJSYEQGSEFH76IWJZH2VSL664OLMEFS';  //Live
//$SECRET='2T6CRKJH5ON3PO4I'; //test
$SECRET='L47JFGGXM1GHRBWO';  //LIVE
//$USER_TYPE='stage';//test
$USER_TYPE='prod';//LIVE
$PHONE=$value;
$SENDER_ID='JMEQCL';
$MSG=$T_MSG;

$url="https://www.way2sms.com/api/v1/sendCampaign";
$message = urlencode($MSG);// urlencode your message
$curl = curl_init();
curl_setopt($curl, CURLOPT_POST, 1);// set post data to true
curl_setopt($curl, CURLOPT_POSTFIELDS, "apikey=".$API_KEY."&secret=".$SECRET."&usetype=".$USER_TYPE."&phone=".$PHONE."&senderid=".$SENDER_ID."&message=".$message);// post data
// query parameter values must be given without squarebrackets.
 // Optional Authentication:
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($curl);
curl_close($curl);
}
echo $result;
EXIT;


	
}


	
	 		
?>