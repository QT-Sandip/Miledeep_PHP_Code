<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

		    $Method = $_POST['Type'];
			$Data=$Method();
			echo json_encode($Data);
			
	function postNewTracebility(){
global $iconn;
	     $IsSplitTracebilityAndPondID = $_SESSION['ClientConfigData']['TracebilityIDAndPondIDSeparator'];
	     $Tracebility=$_POST['Tracebility'];
	     $FarmerName=$_POST['FarmerName'];
	     $AddressContinued=$_POST['AddressContinued'];
	     $RegAuthority=$_POST['RegAuthority'];
	     $FarmPhoneNumber=$_POST['FarmPhoneNumber'];
	     $FarmerCode=$_POST['FarmerCode'];
	     $PondNumber= (isset($_POST['PondNumber']) && $_POST['PondNumber'] != "") ? $_POST['PondNumber'] : "";
		 
		 $PondArea=$_POST['PondArea'];
	     $Tonnage=$_POST['Tonnage'];
	     $StartDate=$_POST['StartDate'];
	     $ExpiryDate=$_POST['ExpiryDate'];
	     $Bap=rtrim(ltrim($_POST['Bap']));
	     $Certification=$_POST['Certification'];
		 $L2Status=$_POST['L2Status'];
	     $FarmType=$_POST['FarmType'];
	     $FromDateForLease=$_POST['FromDateForLease'];
	     $EndDateForLease=$_POST['EndDateForLease'];

	if($IsSplitTracebilityAndPondID){
		$Tracebility = $Tracebility.'-'.$PondNumber;	
 		$SqlCount = "SELECT COUNT(*) AS Total FROM QcTracebilityList WHERE Tracebility='$Tracebility' AND PondNumber='$PondNumber'";		
	}
 	else{
 		$SqlCount="SELECT COUNT(*) AS Total FROM QcTracebilityList WHERE Tracebility='$Tracebility'";
 	}

        $StmtCount=$iconn->query($SqlCount);
		$RowCount=$StmtCount->fetch_array();
		$Count=$RowCount['Total'];


   if($Count>0){
	   
	   $Data['Msg']='Traceability already added ,Please avoid Duplicate entries';

     }else{
		$SqlPI="INSERT INTO QcTracebilityList
				(`FarmerName`, `AddressContinued`, `Tracebility`, `PondNumber`, `RegAuthority`, `FarmPhoneNumber`, `FarmerCode`,
				 `PondArea`, `Tonnage`,`ExpiryDate`,`StartDate`,`Bap`,Certification,L2Status,FarmType,FromDateForLease,EndDateForLease) VALUES 
				('$FarmerName','$AddressContinued','$Tracebility','$PondNumber','$RegAuthority','$FarmPhoneNumber','$FarmerCode',
				'$PondArea','$Tonnage','$StartDate','$ExpiryDate','$Bap','$Certification','$L2Status','$FarmType','$FromDateForLease','$EndDateForLease')"; 
	 }
		$StmtPI=$iconn->query($SqlPI);
		$TraceID = $iconn->insert_id;
		//$StmtPI=true;
		
	 if($StmtPI){	
		legacyAuditLogInsertById('QcTracebilityList', 'ID', $TraceID, null, $_POST, [
			'ReferenceNo' => $TraceID,
			'Remarks' => 'New farmer traceability inserted',
		], 'QualityControl');
		$Data['Error']=null;
		$Data['Msg']='Tracebility Added Sucessfully';
		$Data['TracebilityList']=getTracebilityList($TraceID);
		$Data['test']=$SqlCount;
		
		return $Data;
	 }else{
		$Data['Error']="Traceability already added ,Please avoid Duplicate entries";
		$Data['Msg']="Traceability already added ,Please avoid Duplicate entries";
		$Data['test']=$SqlCount;
		return $Data;
	 }
	 
	}
	
	
	function getTracebilityList($TraceID){
		$ClientConfigData = (object)$_SESSION['ClientConfigData'];
		$ShowTracebilityIds = $ClientConfigData->ShowTracebilityIds;  //anil
		
		global $iconn;	
		$where = '';	
		if(!empty($ShowTracebilityIds)){
				$where = ' WHERE Certification=1 AND L2Status=1';
			}
			$SqlTrace="SELECT * FROM QcTracebilityList $where  ORDER by ABS(Tracebility)";
			
			$StmtTrace=$iconn->query($SqlTrace);
		
		$TraceData='<option value="">Select Farm Reg Number</option>';
		$TraceData.='<!--<option value="">Test</option>-->';
				   while ($RowTrace=$StmtTrace->fetch_array()){
		if($TraceID==$RowTrace['TraceID']){					   
		$TraceData.='<option selected=="selected" value="'.$RowTrace['TraceID'].'">'.$RowTrace['Tracebility'] .'</option>';
		}else{
		$TraceData.='<option value="'.$RowTrace['TraceID'].'">'.$RowTrace['Tracebility'] .'</option>';	
		}			
				  }	  
		return $TraceData;
	}
	
?>
