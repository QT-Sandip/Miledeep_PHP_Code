

<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/SalesNew/Constants.php");

include ($cp."Includes/IncludePage.php");
include ("TestAutoMail.php");


$PageTitleFolder="SalesNew";
$PageTitle="New Proforma Invoice";
$ContinueSeries=false;


//$PIId=$_GET['PIID'];

$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='SALES' order by ABS(OPTIONS) asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}

          $SqlPorts1="SELECT * FROM ScrmPortCities WHERE IsPortofLoading=1";
          $StmtPorts=$iconn->query($SqlPorts1);
	

	$SqlExporter="SELECT * FROM AdminExporterDetails WHERE ExpType in(1,3)";
	$StmtExporter=$iconn->query($SqlExporter);
  
    $SqlProcessor="SELECT * FROM AdminExporterDetails WHERE ExpType in(2,3)";
	$StmtProcessor=$iconn->query($SqlProcessor);

	$SqlImporter="SELECT * FROM `ScrmImporterOfRecord` WHERE Status=1";
	$StmtImporter=$iconn->query($SqlImporter);
	
	$SqlNotifyparty="SELECT * FROM `ScrmNotifyparty` WHERE Status=1";
	$StmtNotifyparty=$iconn->query($SqlNotifyparty);
	
	$SqlCertification="SELECT * FROM `ScrmOrderCertification` WHERE Status=1";
	$StmtCertification=$iconn->query($SqlCertification);
	
	$SqlConsignee="SELECT * FROM ScrmConsigneeDetails WHERE Status=1";
	$StmtConsignee=$iconn->query($SqlConsignee);
	
	$SqlUltimateConsignee="SELECT * FROM ScrmUltimateConsigneeDetails WHERE Status=1";
	$StmtUltimateConsignee=$iconn->query($SqlUltimateConsignee);
	
	$SqlBuyer="SELECT * FROM ScrmBuyerDetails WHERE Status=1";
	$StmtBuyer=$iconn->query($SqlBuyer);
	
	
	$SqlBank="SELECT * FROM `ScrmBankDetailsMaster` WHERE `BankStatus` = '1'";
	$StmtBank=$iconn->query($SqlBank);
	
	$SqlCurrency="SELECT * FROM `ScrmCurrencyMaster` WHERE `Status` = '1'";
	$StmtCurrency=$iconn->query($SqlCurrency);
	
    $SqlCountryList="SELECT * FROM ScrmCountriesList";
	$StmtCountryList=$iconn->query($SqlCountryList); 

	$SqlPaymentTerms="SELECT * FROM ScrmPaymentTerms  WHERE `Status` = '1'";
	$StmtPaymentTerms=$iconn->query($SqlPaymentTerms);
	
	$SqlAgent="SELECT * FROM `ScrmPiAgentMaster` WHERE Status=1";
	$StmtAgent=$iconn->query($SqlAgent);
	
	
	$SqlShipmentTerms="SELECT * FROM `ScrmShipmentTermsMaster` WHERE Status=1";
	$StmtShipmentTerms=$iconn->query($SqlShipmentTerms);
	
	

$SqlAdminUsers="SELECT * FROM $TblAdminUserDetails WHERE DepartmentID=9 and PoUserStatus='1'";
	$StmtAdminUsers=$iconn->query($SqlAdminUsers);

		$AccAval=true;
			$CYear=date("Y");
			$AYear=$CYear+1;
			while($AccAval){
			$PoRefId=generateAccountId();
			$sql="SELECT count(*) as Total FROM `$TblScrmPoData` where PoRefId='$PoRefId'";
			$sqlca=$iconn->query($sql);
			$rlt = mysqli_fetch_array($sqlca,MYSQLI_ASSOC);
			if($rlt['Total'] == 0 ){
				$AccAval=false;
			}			
			}
	
	 function generateAccountId(){
				$generator = "1357902468"; 
                $result = ""; 
                for ($i = 1; $i <= 5; $i++) { 
                    $result .= substr($generator, (rand()%(strlen($generator))), 1); 
                }  
				
				return $result;
		}
		
	function checkInvoiceNo($iconn,$StartYear,$InvNo,$ExporterID){
		
			  $EndYear=$StartYear+1;
			  $FinStartDate=$StartYear.'-04-01'; 
			  $FinEndDate=$EndYear.'-03-31';
	
	/*if($ExporterID==5){
	$sqlCount="SELECT count<span style="color:red">*</span> as Count FROM `ScrmPIData` where InvDate BETWEEN '$FinStartDate' and '$FinEndDate' and
	InvNo='$InvNo' and ExporterID='$ExporterID'  and Status not in(2,4,7)";
	}else{*/
	$sqlCount="SELECT count(*) as Count FROM `ScrmPIData` where InvDate BETWEEN '$FinStartDate' and '$FinEndDate' and
	InvNo='$InvNo' and Status not in(2,4,7) and ExporterID ='$ExporterID'";	
	//}
	//echo $sqlCount; exit;
	$StmtCount=$iconn->query($sqlCount);
	$RowCount=$StmtCount->fetch_array();
	if($RowCount['Count']!=0){	
		return false;		
	}else{
		return true;
	}
  
		}

		

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	 	
	$SqlCountryType="SELECT CountryType FROM `ScrmCountriesList` where id='$DestinationCountry' ";
	$StmtCountryType=$iconn->query($SqlCountryType);
	$RowCountryType=$StmtCountryType->fetch_array();
	
	$TypeCountry=$RowCountryType['CountryType'];

	$StartYear=date('Y',strtotime($InvDate));
	$LoginId=$_SESSION["UserID"];
	
	 for ($i=0; $i < $NoContainers ; $i++){
		$Series=$i+1;
		$GenInvNo=$InvNo+$i;
		$GenInvNo = sprintf( '%03d', $GenInvNo);
		
		/*if($PoNo!=''&&$PoDate!=''){
			$Status=5;
			$ApprovedBy=;
		}
		else{
		$Status=0;	
		}*/
		if(checkInvoiceNo($iconn,$StartYear,$GenInvNo,$ExporterID)){
			$SqlContainerValues[]="('$PoRefId','$GenInvNo','$InvDate','$ExporterID','$ProcessorID',
			'$LotNo','$SciName','$ConsigneeID','$BuyerID','$PoNo','$PoDate','$DestinationCountry',
			'$TypeCountry','$PoStatus','$YEAR','$BankID','$PortOfDischarge',
			'$FinalDestination','$ByPayment','$ShippingTerms','$DeliveryTerms',
			'$PreparedBy' ,'$PRInstrctns','$ShipByDate',0,'$LoginId','$QtyUnit','$UnitCurrency','$Remarks','$ImporterID',
			'$NotifyPartyID','$ReferenceNo','$Agent','$Certification','$PortofLoading','$ShowBank',
			'$UltimateConsigneeID','$Freight'
			)";
		}else{
		$NoContainers++;
		}
		
		}
		

	 
	 $SqlContainerValues=implode(',',$SqlContainerValues);
	
	$sql_org_insert="INSERT INTO `$TblScrmPIData`( `PoRefId`, `InvNo`, `InvDate`, `ExporterID`, `ProcessorID`, `LotNo`, `SciName`,
`ConsigneeID`, `BuyerID`, `PoNo`, `PoDate`, `DestinationCountry`, `TypeCountry`, `PoStatus`, `Year`, 
`BankID`, `PortOfDischarge`, `FinalDestination`, `PaymentTerms`, `ShippingTerms`, `DeliveryTerms`, 
`PreparedBy`, `PRInstrctns`, `ShipByDate`,  `Status`,`LoginId`,`QtyUnit`,`UnitCurrency`,`Remarks`,`ImporterID`, `NotifyPartyID`
, `ReferenceNo`, `Agent`, `Certification`, `PortofLoading`, 
`ShowBank`, `UltimateConsigneeID`,`Freight`) 
VALUES  $SqlContainerValues";


	$stmt1=$iconn->query($sql_org_insert);
	$INV_ID = $iconn->insert_id;	
//	}
	if($stmt1){
		legacyAuditLogInsertById($TblScrmPIData, 'PoID', $INV_ID, $_POST, array(), 'Sales');
		
		header('Location: PoShipmentDetails.php?PIID='.$INV_ID);
		header('Location: PoBreakUp.php?id='.$INV_ID);
	}

}


















$PIId=$_GET['PIID'];
$ExporterID=$_GET['ExporterID'];
//From Global
$AryPriceWeightData=getInvoiceWeight($iconn,$GradeID,$PIId);
$AryPriceData=getPaymentValues($iconn,$PIId);
//print_r($AryPriceData); exit;
 $NetWeight=$AryPriceWeightData['Qty']; 
 $GrossWeight=$AryPriceWeightData['GrossWeightKgs'];
 $DutiesPrice=$AryPriceData['DutiesPrice'];

$SQL_OPTIONS="SELECT * FROM `AdminGeneralOptions` WHERE  DeptID='SALES'  order by SortOrder asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}

$SqlBank="SELECT * FROM AdminBankDetails";
$StmtBank=$iconn->query($SqlBank);
	
$SqlLiner="SELECT * FROM `ScrmLinerMaster` WHERE Status=1";
$StmtLiner=$iconn->query($SqlLiner);

$SqlExchangeRate="SELECT * FROM `ScrmExchangeRates` ORDER by FromDate DESC LIMIT 1";
$StmtExchangeRate=$iconn->query($SqlExchangeRate);
$RowExchangeRate=$StmtExchangeRate->fetch_array();
$ExchangeRate=$RowExchangeRate['ExchangeRate'];
//script


 $SqlPackingStyle="SELECT DISTINCT(PackingStyleID) as PackingStyleID FROM `ScrmPoGradeData` WHERE InvId='$PIId'";
$StmtPackingStyle=$iconn->query($SqlPackingStyle);


$AryPackingStyleIds=array();
while($RowPackingStyle=$StmtPackingStyle-> fetch_array()){
$AryPackingStyleIds[]=$RowPackingStyle['PackingStyleID'];
}

$AryPoGradeID=array();

//script


 $SqlPI="SELECT ExporterID,Freight,QtyUnit FROM `ScrmPoData`  WHERE PoID='$PIId'"; 
$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();

$ExpID=$RowPI['ExporterID'];
$PIFreight=$RowPI['Freight'];
$QtyUnit=$RowPI['QtyUnit'];
		
$CalculatedValues=getPaymentValues($iconn,$PIId);		
 $SqlCount="SELECT * FROM `ScrmCustomInvoices` WHERE PiID='$PIId'";
		
		$StmtCount=$iconn->query($SqlCount);
		
		//echo $SqlCount;
		$RowCustomInvoice=$StmtCount->fetch_array();
		$InvNo=$RowCustomInvoice['CInvNo'];
		$ExRate=$RowCustomInvoice['ExRate'];
		
		$TotalGrossWt=$RowCustomInvoice['TotalGrossWt'];
		if($TotalGrossWt!=''){
		$GrossWeight=$TotalGrossWt;
		}

if($PIFreight==''){
$Freight=$RowCustomInvoice['Freight'];	
}
else{
$Freight=$PIFreight;	
}
	
if($ExRate==''){
$ExchangeRate=$RowExchangeRate['ExchangeRate'];	
}
else{
$ExchangeRate=$ExRate;	
}
		//$RowPI=$StmtCount->fetch_array();

//echo $SqlCount;	
		
		$Count=mysqli_num_rows($StmtCount);	

	 
$SqlGradeData="SELECT * FROM `ScrmCIGradeData` where InvId='$PIId'  ORDER BY ID DESC"; 
$StmtGradeData=$iconn->query($SqlGradeData); 
 $CIGradeCount=mysqli_num_rows($StmtGradeData); 

 $SqlPIVarietyList="SELECT * FROM `ScrmPIVarietyList`"; 
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);

$SqlUserDetails="SELECT * FROM `AdminUserDetails` WHERE CustomUserID='1' and DepartmentID=9";
$StmtUserDetails=$iconn->query($SqlUserDetails);

//exit;		


extract($_POST);
extract($_GET);

function postComBreakUp(){
	global $iconn,$PIId;
	
 $SqlGradeData="SELECT * FROM `ScrmPoGradeData` where InvId='$PIId'  ORDER BY ID ASC"; 
$StmtGradeData=$iconn->query($SqlGradeData); 
   while($RowGradeData=$StmtGradeData->fetch_array()){

	
	$PoGradeID=$RowGradeData['ID'];
	$PoGrade=$RowGradeData['Grade'];
	$PoMcs=$RowGradeData['Mcs'];
	$PoFreezingType=$RowGradeData['FreezingType'];
	$PoPackingStyleID=$RowGradeData['PackingStyleID'];
	$PoGlazeType=$RowGradeData['GlazeType'];
	$PoVariety=$RowGradeData['Variety'];
	$PoGlazePerc=$RowGradeData['GlazePerc'];
	$PoTreatment=$RowGradeData['Treatment'];
	$PoBrand=$RowGradeData['Brand'];
	$PoPrice=$RowGradeData['Price'];
	$PoBAP=$RowGradeData['BAP'];
	$PoVarietyID=$RowGradeData['VarietyID'];
	$BuyerItemCode=$RowGradeData['BuyerItemCode'];
		
		
		
		
	 	$SqlGradeInsert="INSERT INTO `ScrmCIGradeData`(`PoGradeID`,`Grade`, `Mcs`, `FreezingType`, `InvId`, `PackingStyleID`,
	 `GlazeType`, `Variety`, `GlazePerc`, `Treatment`, `Brand`, 
	 `Price`,`BAP`,`VarietyID`,`BuyerItemCode`) VALUES ('$PoGradeID',
	'$PoGrade','$PoMcs','$PoFreezingType','$PIId','$PoPackingStyleID','$PoGlazeType','$PoVariety','$PoGlazePerc',
	 '$PoTreatment','$PoBrand','$PoPrice','$PoBAP','$PoVarietyID','$BuyerItemCode')"; 
		$StmtGradeInsert=$iconn->query($SqlGradeInsert);
		if($StmtGradeInsert){
			$lastGradeId = $iconn->insert_id;
			legacyAuditLogInsertById('ScrmCIGradeData', 'ID', $lastGradeId, $_POST, array('PIID' => $PIId), 'Sales');
		}
		
	
	}
	if($StmtGradeInsert){
		return true;
	}else{
	return false;
	}
}



if(isset($_POST['submit'])){	
			if($Count==0){
			 
			  $StartYear=date('Y',strtotime($CInvDate));
			  $EndYear=$StartYear+1;
			  $FinStartDate=$StartYear.'-04-01';
			  $FinEndDate=$EndYear.'-03-31';
			 
			 if($ExpID==5){
			 $SqlInvCount="SELECT * FROM `ScrmCustomInvoices`
			 WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' 
			 and ExporterID='$ExpID' and CInvNo='$CInvNo'";
			 }else{
				$SqlInvCount="SELECT * FROM `ScrmCustomInvoices`
				WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' and ExporterID BETWEEN '1' and '3' and CInvNo='$CInvNo'";
			 }
				
		
			 $StmtInvCount=$iconn->query($SqlInvCount);
			 $InvCount=mysqli_num_rows($StmtInvCount);		    
			 
		if($InvCount==0){

			 
        $SqlInsert="INSERT INTO `ScrmCustomInvoices`
		(`PiID`, `CInvNo`, `CInvDate`,`CInvCode`, `IGST`, `Discount`, 
		`BankID`, `NoOfDays`,`ExporterID`,`AdvanceAmount`,
		`OverSeasCommission`,`Description`, `CIDescription`, 
		`NameofRepresentative`, `PackDescription`,
		`QCertificateNo`, `ContainerNo`, `ProductTypes`, `USClearance`,
		`PaymentType`, `ByRoad`, `IRNNumber`, `ClearenceCharges`,
		`TotalNetWt`, `TotalGrossWt`, `Duty`, `Freight`, `ExRate`,
         `StockOutDate`, `Vessel`, `VoyageNo`, `Remarks`, `CIDescriptionspl`)		   
	VALUES ('$PIId','$CInvNo','$CInvDate','$CInvCode','$IGST','$Discount',
	'$BankID','$NoOfDays','$ExpID','$AdvanceAmount','$OverSeasCommission',
	'$Description','$CIDescription','$NameofRepresentative',
	'$PackDescription','$QCertificateNo','$ContainerNo',
	'$ProductTypes','$USClearance','$PaymentType',
	'$ByRoad','$IRNNumber','$ClearenceCharges','$TotalNetWt','$TotalGrossWt'
	,'$Duty','$Freight','$ExRate','$StockOutDate','$Vessel'
	,'$VoyageNo','$Remarks','$CIDescriptionspl')"; 
		
		
			$StmtInsert=$iconn->query($SqlInsert);
			$AryCInvNo=explode("-",$CInvDate);
			$FinYear=$AryCInvNo[0];
			$CurrentMonth=$AryCInvNo[1];
			
			if($CurrentMonth > 3){	
				$FinYear=$FinYear;	
			}else{
				$FinYear=$FinYear-1;
			}
			
			$oldSdb = legacyAuditFetchRowById('ScrmShipmentDetailsBreif', 'PiID', $PIId, 'Sales');
		$SqlSdb="UPDATE `ScrmShipmentDetailsBreif` SET 
			`CInvNo`='$CInvNo',
			`CInvDate`='$CInvDate',
			`FinYear`='$FinYear' WHERE PiID='$PIId'";
	
			$StmtSdb=$iconn->query($SqlSdb);
			if($StmtSdb){
				legacyAuditLogUpdateById('ScrmShipmentDetailsBreif', 'PiID', $PIId, $oldSdb, $_POST, array(), 'Sales');
			}

	if($StmtInsert){
		$ciLastId = $iconn->insert_id;
		legacyAuditLogInsertById('ScrmCustomInvoices', 'CInvID', $ciLastId, $_POST, array(), 'Sales');
		postUpdateShipmentBreif($iconn,$CInvDate,$CInvNo,$PIId);
	
	if($CIGradeCount==0){	
		postComBreakUp();
	}
	
	echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
				window.close();
              
        </script>';
		//header('Location: PIList.php');
		
					 	//	 automail($PIId,"1","");
	}	 
			 
			 }	 
		else{
			echo '<script language="javascript" type="text/javascript"> 
                alert("'.$CInvNo.'Invoice Number Already Exists");
                window.location = "AddCustomInvoice.php?PIID='.$PIId.'&ExporterID='.$ExporterID.'";
        </script>';
		}	 
			 
			 
			 }else{
				
				if($InvNo==$CInvNo){
					$oldCiData1 = legacyAuditFetchRowById('ScrmCustomInvoices', 'PiID', $PIId, 'Sales');
		 $SqlCIInsert= "UPDATE `ScrmCustomInvoices` SET
		`CInvNo`='$CInvNo',
        `CInvDate`='$CInvDate',
		`CInvCode`='$CInvCode',
		`IGST`='$IGST',
		`Discount`='$Discount',
         `BankID`='$BankID',
		 `NoOfDays`='$NoOfDays',
		 `AdvanceAmount`='$AdvanceAmount',
		 `OverSeasCommission`='$OverSeasCommission',
		 `Description`='$Description',
		 `QCertificateNo`='$QCertificateNo',
		 `ContainerNo`='$ContainerNo',
		 `ProductTypes`='$ProductTypes',
		 `USClearance`='$USClearance',
		 `PaymentType`='$PaymentType',
		 `ByRoad`='$ByRoad',
		 `IRNNumber`='$IRNNumber',
		 `TotalNetWt`='$TotalNetWt',
		 `TotalGrossWt`='$TotalGrossWt',
		 `Duty`='$Duty',
		 `Vessel`='$Vessel',
		 `VoyageNo`='$VoyageNo',
		 `ExRate`='$ExRate',
		 `Remarks`='$Remarks',
		 `StockOutDate`='$StockOutDate',
		 `Freight`='$Freight',
		 `CIDescriptionspl`='$CIDescriptionspl',
		 `ClearenceCharges`='$ClearenceCharges'
		 
		 WHERE `PiID`='$PIId'"; 

		
			$StmtCIInsert=$iconn->query($SqlCIInsert);
			if($StmtCIInsert){
				legacyAuditLogUpdateById('ScrmCustomInvoices', 'PiID', $PIId, $oldCiData1, $_POST, array(), 'Sales');
			postUpdateShipmentBreif($iconn,$CInvDate,$CInvNo,$PIId);
			if($CIGradeCount==0){	
					postComBreakUp();
			}
			}


	echo '<script language="javascript" type="text/javascript"> 
       alert("Successfully Updated");
window.close();
                // window.location = "DocAddReports.php?PoID='.$PIId.'";
        </script>';
					 		// automail($PIId,"1","Edited");

			 }else{
		
	$StartYear=date('Y',strtotime($CInvDate));
	 $EndYear=$StartYear+1; 
			  $FinStartDate=$StartYear.'-04-01';
			  
             $FinEndDate=$EndYear.'-03-31';
			 
			 	//if($ExpID==5){

			 $SqlInvCount="SELECT * FROM `ScrmCustomInvoices`
			 WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' and ExporterID='$ExpID' and CInvNo='$CInvNo'";
 
				/*}
				else{
				$SqlInvCount="SELECT * FROM `ScrmCustomInvoices`
			 WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' and ExporterID BETWEEN '1' and '3' and CInvNo='$CInvNo'";
 
				}*/
				//echo $SqlInvCount; exit;
				
				$StmtInvCount=$iconn->query($SqlInvCount);
			 $InvCount=mysqli_num_rows($StmtInvCount);		    
			 
			 if($InvCount!=0){
	 echo '<script language="javascript" type="text/javascript"> 
	 alert("Update Failed  Invoice Number already exists");
	                 window.location = "AddCustomInvoice.php?PIID='.$PIId.'";
</script>';
			 }
		else{	 
		$oldCiData2 = legacyAuditFetchRowById('ScrmCustomInvoices', 'PiID', $PIId, 'Sales');
       $SqlCIInsert= "UPDATE `ScrmCustomInvoices` SET
		`CInvNo`='$CInvNo',
        `CInvDate`='$CInvDate',
		`CInvCode`='$CInvCode',
		`IGST`='$IGST',
		`Discount`='$Discount',
         `BankID`='$BankID',
		 `NoOfDays`='$NoOfDays',
		 `AdvanceAmount`='$AdvanceAmount',
		 `OverSeasCommission`='$OverSeasCommission',
		 `Description`='$Description',
		 `QCertificateNo`='$QCertificateNo',
		 `ContainerNo`='$ContainerNo',
		 `ProductTypes`='$ProductTypes',
		 `USClearance`='$USClearance',
		 `PaymentType`='$PaymentType',
		 `ByRoad`='$ByRoad',
		 `IRNNumber`='$IRNNumber',
		 `TotalNetWt`='$TotalNetWt',
		 `TotalGrossWt`='$TotalGrossWt',
		 `Duty`='$Duty',
		 `Vessel`='$Vessel',
		 `VoyageNo`='$VoyageNo',
		 `ExRate`='$ExRate',
		 `Remarks`='$Remarks',
		 `StockOutDate`='$StockOutDate',
		 `Freight`='$Freight',
		 `ClearenceCharges`='$ClearenceCharges'
		
		 WHERE `PiID`='$PIId'"; 
			
		
			$StmtCIInsert=$iconn->query($SqlCIInsert);
						if($StmtCIInsert){
							legacyAuditLogUpdateById('ScrmCustomInvoices', 'PiID', $PIId, $oldCiData2, $_POST, array(), 'Sales');
							if($CIGradeCount==0){	
					postComBreakUp();
			}
							postUpdateShipmentBreif($iconn,$CInvDate,$CInvNo,$PIId);

			}
			 	
		echo '<script language="javascript" type="text/javascript"> 
                	 alert("Successfully Updated");
					 window.close();
        </script>';
					 		// automail($PIId,"1","Edited");
	}
			
		}	 
			
			 }
	
}














$SqlPI="SELECT 
SPD.PoID,SPD.InvNo,SPD.InvDate,SPD.PoNo,SPD.ConsigneeID,
SPD.Status,SCD.ConsigneeName,
SCD.ConsigneeAddress,SCD.ConsigneeCountry,SCI.CInvNo
FROM `ScrmPIData` SPD 
LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID=SPD.ConsigneeID
LEFT JOIN ScrmCustomInvoices SCI ON SCI.PiID=SPD.PoID
WHERE SPD.PoID='$PIId'"; 
$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();

$InvNo=$RowPI['InvNo'];
$InvDate=$RowPI['InvDate'];
$PoNo=$RowPI['PoNo'];
$CInvNo=$RowPI['CInvNo'];
$ConsigneeName=$RowPI['ConsigneeName'];
$ConsigneeAddress=$RowPI['ConsigneeAddress'];


 
 $SqlISFDetails="SELECT * FROM `ScrmISFDetails` WHERE PIID='$PIId'";
$StmtISFDetails=$iconn->query($SqlISFDetails);
$RowISFDetails=$StmtISFDetails->fetch_array();
$Count=mysqli_num_rows($StmtISFDetails);

$SqlProcessor="SELECT * FROM AdminExporterDetails WHERE ExpType in(2,3)";
$StmtProcessor=$iconn->query($SqlProcessor);

$SqlLiner="SELECT * FROM `ScrmLinerMaster` WHERE Status=1";
$StmtLiner=$iconn->query($SqlLiner);

$SqlPorts1="SELECT * FROM ScrmPortCities ";
$StmtPorts=$iconn->query($SqlPorts1);
	

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){	
			 
	IF($Count==0){		
        $SqlInsert="INSERT INTO `ScrmISFDetails`(`PIID`,
 `TranshipmentPort`, `TranshipmentDate`, `StuffingLocation`, 
`ReportPayments`, `LoadingDate`, `STOWPosition`, `DataLogger`,
 `DataLoggerPositions`, `SDD`, `ISFTransactionNo`, `ISFEntryNO`,
 `ISFEntryDate`, `ADDuty`, `Portdues1`, `Portdues2`, `Others`,
 `GrandTotal`, `BankRefNo`, `PaymentDate`, `FDAClearDate`, `FDAExam`,
 `StartDate`, `EndDate`, `GetOutFull`, `Remarks`, `Status`,
 `ShipmentIssues`,`TemperatureOfRecod`, `SizeNType`, `ETA_FD`, `ETA_POD`,
 `EstimatedETA`,`ImportingVessel`, `BLDate`, `ShippingOnboardDt`, `VoyOrg`,`VoyFinal`, 
 `OriginatingVessel`, `ContainerNo`, `SealNo`, `BLNo`, `FreezingDate`,
 `ConsumptionDate`, `ProductionDate`, `BestBeforeDate`,`ShippingLineID`,`ByRoad`,`LcNo`,`LcDate`,`LcIssuedBy`) 		   
	VALUES ('$PIId','$TranshipmentPort','$TranshipmentDate',
'$StuffingLocation','$ReportPayments','$LoadingDate',
	'$STOWPosition','$DataLogger','$DataLoggerPositions','$SDD',
'$ISFTransactionNo','$ISFEntryNO','$ISFEntryDate','$ADDuty',
	'$Portdues1','$Portdues2','$Others','$GrandTotal','$BankRefNo','$PaymentDate',
	'$FDAClearDate','$FDAExam','$StartDate','$EndDate','$GetOutFull'
	,'$Remarks','$Status','$ShipmentIssues','$TemperatureOfRecod','$SizeNType'
	,'$ETA_FD','$ETA_POD','$ETA_POD','$ImportingVessel','$BLDate'
	,'$ShippingOnboardDt','$VoyOrg','$VoyFinal','$OriginatingVessel','$ContainerNo'
	,'$SealNo','$BLNo','$FreezingDate','$ConsumptionDate','$ProductionDate'
	,'$BestBeforeDate','$ShippingLineID','$ByRoad','$LcNo','$LcDate','$LcIssuedBy')"; 
	}ELSE{
		
		
	$oldIsfData = legacyAuditFetchRowById('ScrmISFDetails', 'PIID', $PIId, 'Sales');
	$SqlInsert="UPDATE `ScrmISFDetails` SET 
	`TranshipmentPort`='$TranshipmentPort',`TranshipmentDate`='$TranshipmentDate',
	`StuffingLocation`='$StuffingLocation',`ReportPayments`='$ReportPayments',
	`LoadingDate`='$LoadingDate',`STOWPosition`='$STOWPosition',
	`DataLogger`='$DataLogger',`DataLoggerPositions`='$DataLoggerPositions',
	`SDD`='$SDD',`ISFTransactionNo`='$ISFTransactionNo',
	`ISFEntryNO`='$ISFEntryNO',`ISFEntryDate`='$ISFEntryDate',
	`ADDuty`='$ADDuty',`Portdues1`='$Portdues1',`Portdues2`='$Portdues2',
	`Others`='$Others',`GrandTotal`='$GrandTotal',`BankRefNo`='$BankRefNo',
	`PaymentDate`='$PaymentDate',`FDAClearDate`='$FDAClearDate',
	`FDAExam`='$FDAExam',`StartDate`='$StartDate',
	`EndDate`='$EndDate',`GetOutFull`='$GetOutFull',`Remarks`='$Remarks',
	`Status`='$Status',`ShipmentIssues`='$ShipmentIssues',
	`TemperatureOfRecod`='$TemperatureOfRecod',`SizeNType`='$SizeNType',
	`ETA_FD`='$ETA_FD',`ETA_POD`='$ETA_POD',`ImportingVessel`='$ImportingVessel',
	`BLDate`='$BLDate',`ShippingOnboardDt`='$ShippingOnboardDt',
	`VoyFinal`='$VoyFinal',`VoyOrg`='$VoyOrg',
	`OriginatingVessel`='$OriginatingVessel',`ContainerNo`='$ContainerNo',
	`SealNo`='$SealNo',`BLNo`='$BLNo',`FreezingDate`='$FreezingDate',
	`ConsumptionDate`='$ConsumptionDate',`ProductionDate`='$ProductionDate',
	`BestBeforeDate`='$BestBeforeDate',
	`ByRoad`='$ByRoad',
	`ShippingLineID`='$ShippingLineID',
	`LcNo`='$LcNo',
	`LcDate`='$LcDate',
	`LcIssuedBy`='$LcIssuedBy' WHERE PIID='$PIId'";	
		
		
		
		
	}
		
	//echo	$SqlInsert; exit;
			$StmtInsert=$iconn->query($SqlInsert);
		
			
		
	if($StmtInsert){
		if($Count==0){
			legacyAuditLogInsertById('ScrmISFDetails', 'PIID', $PIId, $_POST, array(), 'Sales');
		} else {
			legacyAuditLogUpdateById('ScrmISFDetails', 'PIID', $PIId, $oldIsfData, $_POST, array(), 'Sales');
		}
	
	echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "DocAddReports.php?PoID='.$PIId.'";
        </script>';
		
					 		
	}	 
			 			 
	
	
}
 ?>


<!DOCTYPE html>
<html lang="en">

    <head>
        
            <?php include ($IncludesPath."title-meta.php"); ?>
		 	<?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php
			include ($IncludesPath."PreLoader.php"); ?>

        <!-- Begin page -->
        <div id="layout-wrapper">


            <?php
			include ($IncludesPath."topbar.php");
			include ($IncludesPath."sidebar.php"); 
			?>



 <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
					
						<?php //	if($Status !='') { 	//Show Alert  	?>	 
                        <?php  // } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post" enctype="multipart/form-data">
                                    <div class="card-body">
									
										<div class="row">
										
										
			<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Select Exporter<span style="color:red">*</span></label>              			   
				  <select required class="form-control select2" name="ExporterID" id="ExporterID">
				   <option value="">Select Exporter</option>
				<?php while($RowExporter=$StmtExporter->fetch_array()) { ?>  
				
				<option Value="<?php echo $RowExporter['ExpID'];?>">
				<?php echo $RowExporter['ExpName'];?></OPTION>
				
				<?php } ?>
				
				<select>   
                </div>							

			
			<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Select Processor<span style="color:red">*</span></label>              			   
				  <select required class="form-control select2" name="ProcessorID" id="ProcessorID">
				   <option value="">Select Processor</option>
				<?php while($RowProcessor=$StmtProcessor->fetch_array()) { ?>  
				
				<option Value="<?php echo $RowProcessor['ExpID'];?>"><?php echo $RowProcessor['ExpName'].'('.$RowProcessor['ExpApprovalNo'].')';?></OPTION>
				
				<?php } ?>
				
				<select>   
                </div> 
			
				   
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">No Of Containers</label>
                
				  
				  <div class="input-group" >
				 
    <span class="input-group-btn">
        <button class="btn btn-white btn-minuse" id="Cminus" type="button">-</button>
    </span>
   
	
	 <input readonly 	 required type="text" class="form-control no-padding add-color text-center height-25"
				  name="NoContainers"  id="NoContainers" 
				  placeholder="Enter Number Of Containers" maxlength="3" value="1" >
    <span class="input-group-btn">
        <button class="btn btn-red btn-pluss" id="Cplus" type="button">+</button>
    </span>
</div>
                </div>
				
                  <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Reference Number</label>
                  <input   type="text" class="form-control" name='ReferenceNo' 
				  id="ReferenceNo" placeholder="Enter Reference Number" value="">
                </div>
					
					<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Proforma Invoice No<span style="color:red">*</span></label>
                  <input required  type="number" class="form-control" name='InvNo' 
				  id="InvNo" placeholder="Enter Proforma Invoice No" value="">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Proforma Invoice Date<span style="color:red">*</span></label>
                  <input required type="date" value="<?php  echo date("Y-m-d");?>" 
				  class="form-control" name='InvDate' id="InvDate" 
				  placeholder="Enter Proforma Invoice No ">
                </div>
			  
			  <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> P.O No</label>
                  <input  type="text" class="form-control" name='PoNo' 
				  id="PoNo" placeholder="Enter P.O Number">
                </div> 
				
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> P.O Date</label>
                  <input  type="date" value="" 
				  class="form-control" name='PoDate' id="PoDate" placeholder="Enter P.O Date">
                </div>
			  
			  
               	<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Consignee<span style="color:red">*</span></label>
                   <select  required class="form-control select2" name="ConsigneeID" id="ConsigneeID">
				  <option value="">Select Consignee</option>
				   <?php while ($RowConsignee=$StmtConsignee->fetch_array()){ ?>
				     <option value="<?php echo $RowConsignee['ConID'] ?>">
					 <?php echo $RowConsignee['ConsigneeName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div> 			
		 		
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Ultimate Consignee</label>
                   <select   class="form-control select2" name="UltimateConsigneeID" id="UltimateConsigneeID">
				  <option value="">Select</option>
				   <?php while ($RowUltimateConsignee=$StmtUltimateConsignee->fetch_array()){ ?>
				     <option value="<?php echo $RowUltimateConsignee['ConID'] ?>">
					 <?php echo $RowUltimateConsignee['ConsigneeName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div> 			
		 	
		       
				
			
		  <div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Buyer Name</label>
                   <select   class="form-control select2" name="BuyerID" id="BuyerID">
				  <option value="">Select Buyer</option>
				   <?php while ($RowBuyer=$StmtBuyer->fetch_array()){ ?>
				     <option value="<?php echo $RowBuyer['BuyerID'] ?>">
					 <?php echo $RowBuyer['BuyerName']?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>

                <div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Notify Party</label>
                   <select   class="form-control select2" name="NotifyPartyID" id="NotifyPartyID">
				  <option value="">Select</option>
				   <?php while ($RowNotifyparty=$StmtNotifyparty->fetch_array()){ ?>
				     <option value="<?php echo $RowNotifyparty['NotifyPartyID'] ?>">
					 <?php echo $RowNotifyparty['NotifyPartyName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
			   
			   
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Importer of Record</label>
                   <select   class="form-control select2" name="ImporterID" id="ImporterID">
				  <option value="">Select</option>
				   <?php while ($RowImporter=$StmtImporter->fetch_array()){ ?>
				     <option value="<?php echo $RowImporter['ImporterID'] ?>">
					 <?php echo $RowImporter['ImporterName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Agent</label>
                   <select   class="form-control select2" name="Agent" id="Agent">
				  <option value="">Select</option>
				   <?php while ($RowAgent=$StmtAgent->fetch_array()){ ?>
				     <option value="<?php echo $RowAgent['AgentID'] ?>">
					 <?php echo $RowAgent['AgentName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Certification</label>
                   <select   class="form-control select2" name="Certification" id="Certification">
				  <option value="">Select</option>
				   <?php while ($RowCertification=$StmtCertification->fetch_array()){ ?>
				     <option value="<?php echo $RowCertification['CerificationID'] ?>">
					 <?php echo $RowCertification['CerificationName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Port of Loading<span style="color:red">*</span></label>
                   <select required  class="form-control select2" name="PortofLoading" id="PortofLoading">
				  <option value="">Select</option>
				   <?php while ($RowPorts=$StmtPorts->fetch_array()){ ?>
				     <option value="<?php echo $RowPorts['PortId'] ?>">
					 <?php echo $RowPorts['Name'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				 <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Destination Country<span style="color:red">*</span></label>
                 
				  
				  <select required class="form-control select2" name="DestinationCountry" id="DestinationCountry">
				  <option value="">Select Country</option>
				   <?php while ($RowCountry=$StmtCountryList->fetch_array()){ ?>
				     <option value="<?php echo $RowCountry['id'] ?>">
					 <?php echo strtoupper($RowCountry['name'])?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				 <div class="form-group col-lg-3">
			      <label for="exampleInputPassword1">Port of Discharge<span style="color:red">*</span></label>
		          <select  required class="form-control select2" name="PortOfDischarge" id="PortOfDischarge">
				  <option value="">Select Port</option>
				   <?php 
				   $PortOfDischarge=$RowPIData['PortOfDischarge'];
				   foreach ($AryPorts as $RowPort ){ ?>
				     <option <?php if($PortOfDischarge==$RowPort['PortId']){ ?> selected="selected"  <?php } ?>
					 
					 value="<?php echo $RowPort['PortId'] ?>">
					 <?php echo $RowPort['Name']?></option>
				   
				 <?php  }?>	  
				  </select>				  
				  
	             </div>	
	
	             <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Final Destination<span style="color:red">*</span></label>
                  
				  <!--<input required type="text" class="form-control" name='FinalDestination' 
				  id="FinalDestination" placeholder="Enter Final Destination" value="<?php echo $RowPIData['FinalDestination'];?>">
               -->
			   
			   <select  required class="form-control select2" name="FinalDestination" id="FinalDestination">
				  <option value="">Select Final Destination</option>
				   <?php 
				   $FinalDestination=$RowPIData['FinalDestination'];
				   foreach ($AryPorts as $RowPort ){ ?>
				     <option 
					 
					 value="<?php echo $RowPort['PortId'] ?>">
					 <?php echo $RowPort['Name']?></option>
				   
				 <?php  }?>	  
				  </select>

			   </div> 
				
		        <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">ShipBy Date</label>
                  <input    type="date" class="form-control" name="ShipByDate"
				  id="ShipByDate" placeholder="Enter ShipBy Date" Value="<?php echo $RowPIData['ShipByDate'] ;?>"
				 ></input>
                </div> 
				
				
					
					<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Bank Details<span style="color:red">*</span></label>
                   <select required class="form-control select2" name="BankID" id="BankID">
				  <option value="">Select Bank</option>
				   <?php while ($RowBank=$StmtBank->fetch_array()){ ?>
				     <option value="<?php echo $RowBank['ID'] ?>">
					 <?php echo $RowBank['AccntName'].' - 
					 '.$RowBank['BankName'] .'-'.$RowBank['SwiftCode'] .'-'.$RowBank['AccountNumber']?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				 <div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Show Bank<span style="color:red">*</span></label>
				<select required class="form-control select2" name="ShowBank" id="ShowBank">
				 	<option  value="0">No</option>
					<option  value="1">Yes</option>
									
				</select>
                </div>
				
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Payment Terms<span style="color:red">*</span></label>
                 
				   <select  required class="form-control select2" name="ByPayment" id="ByPayment">
				  <option value="">Select Payment</option>
				   <?php while ($RowPayment=$StmtPaymentTerms->fetch_array()){
									  if($RowPayment['LcStatus']==1){
						 $LcStatus='LC';
  
									  }
									  else{
                         $LcStatus='Non LC';
	  
									  }
   ?>
				     <option value="<?php echo $RowPayment['ID'] ?>">
					 <?php echo $RowPayment['PaymentName'].' - '.$LcStatus?></option>
				   
				 <?php  }?>	  
				  </select>
                </div> 
				
				
				
              <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Shipment Terms<span style="color:red">*</span></label>

                <select required class="form-control select2" name="ShippingTerms" id="ShippingTerms">
				            <option value="">Select Shipment Terms</option>

					 <?php while($RowShipterms=$StmtShipmentTerms->fetch_array()){?>
			 
					<option value="<?php echo $RowShipterms['TermName'] ?>">
					<?php echo $RowShipterms['TermName'] ?></option>
					<?php  }?>
				<select>
				
				</div>
				
				
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Delivery Terms<span style="color:red">*</span></label>
				<select required class="form-control select2" name="DeliveryTerms" id="DeliveryTerms">
				 	<option  value="">Select</option>
				 	<option  value="CY/CY">CY/CY</option>
					<option  value="CY/SD">CY/SD</option>
									
				</select>
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Quantity Units<span style="color:red">*</span></label>

                <select required class="form-control select2" name="QtyUnit" id="QtyUnit">
				                  <option value="">Select Quantity  Units</option>

					 <?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='UOM'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
				<select>
				
				</div>
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Currency<span style="color:red">*</span></label>
				  <select  required class="form-control select2" name="UnitCurrency" id="UnitCurrency">
                  <option value="">Select Currency</option>
                  
				   <?php while ($RowCurrency=$StmtCurrency->fetch_array()){ ?>
				     <option value="<?php echo $RowCurrency['CurrencyCode'] ?>">
					 <?php echo $RowCurrency['CurrencyName'].' - 
					 '.$RowCurrency['CurrencyCode'] .'('.$RowCurrency['CurrenySymbol'].')'?></option>
				   
				 <?php  }?>	 
				  </select>
                </div>
				
				
				
				<!--<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Prepared By<span style="color:red">*</span></label>
                 
                <select required class="form-control select2" name="PreparedBy" id="PreparedBy">
					  <option   value="">Select Prepared By</option>
					<?php while($RowAdminUsers=$StmtAdminUsers->fetch_array()){
					$UserName=$RowAdminUsers['UserName']; ?>
			 
					<option value="<?php echo $UserName ?>"><?php echo $UserName ?></option>
					<?php  }?>
				<select>
				</div>--> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Freight</label>
				<input  type="text" class="form-control" name='Freight' id="Freight" 
				  placeholder="Enter Freight" value="<?php echo $RowCustomInvoice['Freight']?>">				
                </div>
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">Remarks</label>
				  
				  <textarea name="Remarks" class="form-control" rows="2" cols="50"
                    id="Remarks"   
					placeholder="Enter Remarks" ></textarea>
				 		
		
                </div>
				
				</div> 
				
				
				<div class="col-xs-12 box-footer"  align="right">
                <button hidden type="submit"  name='submit' id="Create" class="btn btn-success">Next</button>
              </div>
				<div class="col-xs-12 "  align="right">
				  <!--<a target="" href="PIList.php" class="btn btn-danger  "> Close</a>-->
				 <button type="button" id="close"  name="close"  class="btn btn-danger">Close </button>
            
			 <button type="button"  id="CreatePI" class="btn btn-success">Next</button>
			 </div>
				</div>
				
            </form>
			
       <!-- end col -->
                        </div> <!-- end row -->
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->






         
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
	<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Custom Invoice<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='CInvNo' id="CInvNo" 
				  placeholder="Enter Custom Invoice No" value="<?php echo $RowCustomInvoice['CInvNo']?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Custom Invoice Date<span style="color:red">*</span></label>
 <input  required type="date" class="form-control" name='CInvDate' id="CInvDate" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['CInvDate']?>">
                </div>

					
					 
					
				<!--	 <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Custom Invoice Code</label>
                 
                <select class="form-control select2" name="CInvCode" id="CInvCode">
					 <?php 
					 //$SciName=$RowPIData['SciName']; 
					 foreach($ARY_OPTIONS as $value){
					if($value['OptionType']=='CInvCode'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['Value']; ?>
			 
					<option  <?php if($RowCustomInvoice['CInvCode']==$VALUE){ ?> selected="selected"  <?php } ?>
					
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS?></option>
					<?php  }}?>
				<select>
				
				</div>-->
					
					
				  <div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">Invoice Type (IGST/LUT)</label>
                 <select class="form-control select2" id="IGST" name="IGST" >
				  <option  value="">Select</option>
				  <option <?php if($RowCustomInvoice['IGST']=='IGST') {?>selected="selected"<?php } ?>
				  
				  value="IGST">IGST</option>
				  <option  <?php if($RowCustomInvoice['IGST']=='LUT') {?>selected="selected"<?php } ?>
				  value="LUT">LUT</option>
				
				
				
						</select>
					</div>
					
					<div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">Product Type</label>
                 <select class="form-control select2" id="ProductTypes" name="ProductTypes" >
				  <option  value="">Select</option>
				  <option <?php if($RowCustomInvoice['ProductTypes']=='PTA/FTA') {?>selected="selected"<?php } ?>	  
				  value="PTA/FTA">PTA/FTA</option>
				  <option  <?php if($RowCustomInvoice['ProductTypes']=='NO PTA/FTA') {?>selected="selected"<?php } ?>
				  value="NO PTA/FTA">NO PTA/FTA</option>		
						</select>
					</div>
					
					<div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">Payment Type<span style="color:red">*</span></label>
                 <select required class="form-control select2" id="PaymentType" name="PaymentType" >
				  <option  value="">Select</option>
				  <option <?php if($RowCustomInvoice['PaymentType']=='Collections') {?>selected="selected"<?php } ?>				  
				  value="Collections">Collections</option>
				  <option  <?php if($RowCustomInvoice['PaymentType']=='Negotiations') {?>selected="selected"<?php } ?>
				  value="Negotiations">Negotiations</option>		
						</select>
					</div>
					
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Q Certificate No</label>
				<input  type="text" class="form-control" name='QCertificateNo' id="QCertificateNo" 
				  placeholder="Enter Q Certificate No" value="<?php echo $RowCustomInvoice['QCertificateNo']?>">		
		
                </div>
					
					
					
					
					<!--<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Bank Details</label>
                   <select  class="form-control select2" name="BankID" id="BankID">
				 				  <option value="">Select Bank</option>

				   <?php if($RowCustomInvoice['BankID']==0){
				   $BankID=$RowPIData['BankID'];
				   }
				   else{
				   $BankID=$RowCustomInvoice['BankID'];
				   }
				   while ($RowBank=$StmtBank->fetch_array()){ ?>
				     <option <?php if($BankID==$RowBank['ID']){ ?> selected="selected"  <?php } ?>
					 
					 
					 value="<?php echo $RowBank['ID'] ?>">
					 <?php echo $RowBank['AccntName'].' - 
					 '.$RowBank['BankName'].'-'.$RowBank['AccountNumber'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>-->
					
					<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Discount</label>
				<input  type="decimal" class="form-control" name='Discount' id="Discount" 
				  placeholder="Enter Discount" value="<?php echo $RowCustomInvoice['Discount']?>">		
		
                </div>
				

				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Terms No.of Days</label>
				<input  type="number" class="form-control" name='NoOfDays' id="NoOfDays" 
				  placeholder="Enter No.of Days" value="<?php echo $RowCustomInvoice['NoOfDays']?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Advance Amount</label>
				<input  type="text" class="form-control" name='AdvanceAmount' id="AdvanceAmount" 
				  placeholder="Enter Advance Amount" value="<?php echo $RowCustomInvoice['AdvanceAmount']?>">		
		
                </div>
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">OverSeas Commission</label>
				<input  type="text" class="form-control" name='OverSeasCommission' id="OverSeasCommission" 
				  placeholder="Enter OverSeas Commission" value="<?php echo $RowCustomInvoice['OverSeasCommission']?>">		
		
                </div>
				
			 <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">US Clearance Charges</label>
				<input  type="text" class="form-control" name='ClearenceCharges' id="ClearenceCharges" 
				  placeholder="Enter US Clearance Charges" value="<?php echo $RowCustomInvoice['ClearenceCharges']?>">		
		
                </div>
				
				 <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Transport Date</label>
				<input  type="Date" class="form-control" name='StockOutDate' id="StockOutDate" 
				  placeholder="Enter Transport Date" value="<?php echo $RowCustomInvoice['StockOutDate']?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Container No</label>
				<input  type="text" class="form-control" name='ContainerNo' id="ContainerNo" 
				  placeholder="Enter Container No" value="<?php echo $RowCustomInvoice['ContainerNo']?>">		
		
                </div>
				
				<!--<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Shipping Line</label>
                   <select  class="form-control select2" name="ShippingLine" id="ShippingLine">
				 				  <option value="">Select</option>

				   <?php  while ($RowLiner=$StmtLiner->fetch_array()){ ?>
				     <option <?php if($RowCustomInvoice['ShippingLine']==$RowLiner['LinerID']){ ?> selected="selected"  <?php } ?>
					value="<?php echo $RowLiner['RowLiner'] ?>"><?php echo $RowLiner['LinerName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Vessel</label>
				<input  type="text" class="form-control" name='Vessel' id="Vessel" 
				  placeholder="Enter Vessel" value="<?php echo $RowCustomInvoice['Vessel']?>">		
		
                </div>
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Voyage No</label>
				<input  type="text" class="form-control" name='VoyageNo' id="VoyageNo" 
				  placeholder="Enter Voyage No" value="<?php echo $RowCustomInvoice['VoyageNo']?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">By Road</label>
				<input  type="text" class="form-control" name='ByRoad' id="ByRoad" 
				  placeholder="Enter By Road Details" value="<?php echo $RowCustomInvoice['ByRoad']?>">		
		
                </div>-->
				
				
                </div>
				<HR></HR>

                <div class="row">
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Total NetWt</label>
				<input readonly  type="text" class="form-control" name='TotalNetWt' id="TotalNetWt" 
				style="background: gray;color: white;"
				  placeholder="Enter Total Net Weight" value="<?php echo $NetWeight?>">		
		
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Total GrossWt(<?php echo $QtyUnit; ?>)<span style="color:red">*</span></label>
				 <button  id="AddGrossWtPerBrkUp" data-id="<?php echo$PIId ?>" data-value="
	           <?php echo $PIId ?>" class="text">
				 <b> Add + </b></button>

				
				<input readonly type="text" style="background: gray;color: white;" class="form-control" name='TotalGrossWt' id="TotalGrossWt" 
				  placeholder="Enter Total Gross Weight" value="<?php echo $GrossWeight?>">		
		
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Duty</label>
				<input readonly type="text" class="form-control" name='Duty' id="Duty" style="background: gray;color: white;" 
				  placeholder="Enter Duty" value="<?php echo $DutiesPrice?>">		
		
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Freight<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='Freight' id="Freight" 
				  placeholder="Enter Freight" value="<?php echo $Freight; ?>">		
		
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ExRate<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='ExRate' id="ExRate" 
				  placeholder="Enter Exchange Rate" value="<?php echo $ExchangeRate; ?>">		
		
                </div>
				
				
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">IRN Number</label>
				  
				  <textarea name="IRNNumber" class="form-control" rows="2" cols="50"
                    id="IRNNumber"   
					placeholder="Enter IRN Number" ><?php echo $RowCustomInvoice['IRNNumber'] ?></textarea>
				 		
		
                </div>
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">Remarks</label>
				  
				  <textarea name="Remarks" class="form-control" rows="2" cols="50"
                    id="Remarks"   
					placeholder="Enter Remarks" ><?php echo $RowCustomInvoice['Remarks'] ?></textarea>
				 		
		
                </div>	
				
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">Special Notes(CI Description)</label>
				  
				  <textarea name="CIDescriptionspl" class="form-control" rows="2" cols="50"
                    id="CIDescriptionspl"   
					placeholder="Enter Special Notes" ><?php echo $RowCustomInvoice['CIDescriptionspl'] ?></textarea>
				 		
		
                </div>	
				
				</div>
				
				
				
				
					<?php if($Count==0){	 ?>
			  <div class="col-xs-12 box-footer"  align="right">
			  <button type="button" id="close"  name="close"  class="btn btn-danger">Close </button>
            
  		
                <button type="submit"  name='submit' class="btn btn-primary">Add</button>
			 <!--  <a href="CIBreakUp.php?PIId=<?php echo $PIId; ?>" id="<?php echo $PIId; ?>"  class="btn btn-success">ShortShipment  </a>
-->
              </div>
					<?php } else{	 ?>
					
			  <div class="col-xs-12 box-footer"  align="right">
			  <button type="button" id="close"  name="close"  class="btn btn-danger">Close </button>
            
  		
                <button type="submit"  name='submit' class="btn btn-primary">Update</button>
			<!-- <a href="CIBreakUp.php?PIId=<?php echo $PIId; ?>"  id="<?php echo $PIId; ?>"  class="btn btn-success">ShortShipment  </a>
-->
<button id="<?php echo  $PIId;?>" style="width:100px;"
	onClick="reply_click(this.id)" class="btn btn-warning "> Delete</button>
	
								 


              </div>
					<?php } ?>

            </form>
       <!-- end col -->
                        </div> <!-- end row -->

                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
                        
                    </div> <!-- container-fluid -->





<?php include "PopUpTable.php"; ?>
    
               <div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
										
										
	        <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"> Custom Invoice</label>
				<input readonly required type="text" class="form-control" name='CInvNo' id="CInvNo" 
				  style="background: gray;color: white;"placeholder="Enter Custom Invoice No" value="<?php echo $PoNo;?>">		
            </div>
			
			 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">PO No</label>
				<input readonly required type="text" class="form-control" name='CInvNo' id="CInvNo" 
				style="background: gray;color: white;"  placeholder="Enter  PO No" value="<?php echo $PoNo;?>">		
            </div>
			
		
		<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Consignee</label>
				<input readonly required type="text" class="form-control" name='ConsigneeName' id="ConsigneeName" 
				style="background: gray;color: white;"  placeholder="Enter Consignee Name" value="<?php echo $ConsigneeName?>">		
		
         </div>


         		
				
				
		<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Address</label>
				<input readonly required type="text" class="form-control" name='ConsigneeAddress' id="ConsigneeAddress" 
				style="background: gray;color: white;"  placeholder="Enter Consignee Address" value="<?php echo $ConsigneeAddress?>">		
		
         </div>
		 
		 
		 
	 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Container Number<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='ContainerNo' id="ContainerNo" 
				  placeholder="Enter Container Number" value="<?php echo $RowISFDetails['ContainerNo'];?>">		
		
         </div>
		 
		 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Seal Number<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='SealNo' id="SealNo" 
				  placeholder="Enter Seal Number" value="<?php echo $RowISFDetails['SealNo'];?>">		
		
         </div>


<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BL Number<span style="color:red">*</span></label>
				<input required  type="text" class="form-control" name='BLNo' id="BLNo" 
				  placeholder="Enter BL Number" value="<?php echo $RowISFDetails['BLNo'];?>">		
		
         </div>
		 
		 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BL Date<span style="color:red">*</span></label>
                  <input required  type="date" class="form-control" name='BLDate' id="BLDate" 
				  placeholder=" " value="<?php echo $RowISFDetails['BLDate'];?>">
         </div>
 
		
		 
		 
		
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Shipping Line<span style="color:red">*</span></label>
                   <select  required class="form-control select2" name="ShippingLineID" id="ShippingLineID">
				 				  <option value="">Select</option>

				   <?php  while ($RowLiner=$StmtLiner->fetch_array()){ ?>
				     <option <?php if($RowISFDetails['ShippingLineID']==$RowLiner['LinerID']){ ?> selected="selected"  <?php } ?>
					value="<?php echo $RowLiner['LinerID'] ?>"><?php echo $RowLiner['LinerName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Shipping Onboard Date<span style="color:red">*</span></label>
                  <input required  type="date" class="form-control" name='ShippingOnboardDt' id="ShippingOnboardDt" 
				  placeholder=" " value="<?php echo $RowISFDetails['ShippingOnboardDt'];?>">
         </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Originating Vessel<span style="color:red">*</span></label>
				<input required  type="text" class="form-control" name='OriginatingVessel' id="OriginatingVessel" 
				  placeholder="Enter Originating Vessel Name" value="<?php echo $RowISFDetails['OriginatingVessel'];?>">		
		
			</div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Originating Voyage No<span style="color:red">*</span></label>
				<input  required type="text" class="form-control" name='VoyOrg' id="VoyOrg" 
				  placeholder="Enter Originating Voyage No" value="<?php echo $RowISFDetails['VoyOrg']?>">		
		
                </div>
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Final Importing Vessel</label>
                  <input   type="text" class="form-control" name='ImportingVessel' id="ImportingVessel" 
				  placeholder="Enter Importing Vessel Details" value="<?php echo $RowISFDetails['ImportingVessel'];?>">
         </div>			 
				
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Final Voyage No</label>
				<input  type="text" class="form-control" name='VoyFinal' id="VoyFinal" 
				  placeholder="Enter Voyage No" value="<?php echo $RowISFDetails['VoyFinal'];?>">		
		
         </div>
		
		 <div class="form-group col-lg-3">
			      <label for="exampleInputPassword1">Port of Transhipment<span style="color:red">*</span></label>
		          <select  required class="form-control select2" name="TranshipmentPort" id="TranshipmentPort">
				  <option value="">Select Port</option>
				   <?php 
				   $TranshipmentPort=$RowISFDetails['TranshipmentPort'];
				   while ($RowPorts=$StmtPorts->fetch_array()){ ?>
				     <option <?php if($TranshipmentPort==$RowPorts['Name']){ ?> selected="selected"  <?php } ?>
					 
					 value="<?php echo $RowPorts['Name'] ?>">
					 <?php echo $RowPorts['Name']?></option>
				   
				 <?php  }?>	  
				  </select>				  
				  
	             </div>	
		 

        <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Transhipment Date</label>
                  <input   type="date" class="form-control" name='TranshipmentDate' id="TranshipmentDate" 
				  placeholder="Enter Transhipment Date " value="<?php echo $RowISFDetails['TranshipmentDate']?>">
         </div>		 
			
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Stuffing Location<span style="color:red">*</span></label>              			   
				  <select class="form-control select2" name="StuffingLocation" id="StuffingLocation">
				 
				<?php while($RowProcessor=$StmtProcessor->fetch_array()) { ?>  
				
				 <option <?php if($RowISFDetails['StuffingLocation']==$RowLiner['ExpID']){ ?> selected="selected"  <?php } ?>
					value="<?php echo $RowProcessor['ExpName'] ?>"><?php echo '('.$RowProcessor['ExpApprovalNo'].')';?></option>
				   
			
				<?php } ?>
				
				<select>   
                </div> 

				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">ETA POD<span style="color:red">*</span></label>
				<input required type="date" class="form-control" name='ETA_POD' id="ETA_POD" 
				  placeholder="Enter ETA POD" value="<?php echo $RowISFDetails['ETA_POD'];?>">		
		
                </div>
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">ETA FD<span style="color:red">*</span></label>
				<input  required type="date" class="form-control" name='ETA_FD' id="ETA_FD" 
				  placeholder="Enter ETA FD" value="<?php echo $RowISFDetails['ETA_FD']?>">		
		
                </div>
						
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Date of Loading</label>
				<input  type="date" class="form-control" name='LoadingDate' id="LoadingDate" 
				  placeholder="Enter Date of Loading" value="<?php echo $RowISFDetails['LoadingDate']?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Size and Type</label>
				<input  type="text" class="form-control" name='SizeNType' id="SizeNType" 
				  placeholder="40 FEET" value="<?php echo $RowISFDetails['SizeNType'] ?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">STOW Position</label>
				<input  type="text" class="form-control" name='STOWPosition' id="STOWPosition" 
				  placeholder="Enter STOW Position" value="<?php echo $RowISFDetails['STOWPosition']?>">		
		
                </div>
				
			<!--	<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Freight</label>
				<input  type="text" class="form-control" name='Freight' id="Freight" 
				  placeholder="Enter Freight" value="<?php echo $RowISFDetails['Freight']?>">		
		
                </div>  -->
                
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Data Logger</label>
				<input  type="text" class="form-control" name='DataLogger' id="DataLogger" 
				  placeholder="Enter Data Logger Details" value="<?php echo $RowISFDetails['DataLogger']?>">		
		
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Placed AT</label>
				<input  type="text" class="form-control" name='DataLoggerPositions' id="DataLoggerPositions" 
				  placeholder="Enter Data Logger Positions" value="<?php echo $RowISFDetails['DataLoggerPositions']?>">		
		
                </div>	

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">SDD</label>
				<input  type="text" class="form-control" name='SDD' id="SDD" 
				  placeholder="Enter SDD" value="<?php echo $RowISFDetails['SDD']?>">				
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">By Road</label>
				<input  type="text" class="form-control" name='ByRoad' id="ByRoad" 
				  placeholder="Enter By Road Details" value="<?php echo $RowCustomInvoice['ByRoad']?>">		
		
                </div>
                <div class="form-group col-lg-6" >
				
					<a target="_blank" id="PoDetails" href="#"
				data-id="<?php echo $PIId; ?>" 
				data-value="<?php echo $PoNo; ?>"
				class="btn btn-danger  ">More Transhipments</a>
                </div>
                </div>
				<HR></HR>
				<H5 style="color: BROWN;">Transactions</H5>
                <div class="row">
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ISF Transaction No</label>
				<input  type="text" class="form-control" name='ISFTransactionNo' id="ISFTransactionNo" 
				  placeholder="Enter ISF Transaction No" value="<?php echo $RowISFDetails['ISFTransactionNo']?>">				
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Enrty Number</label>
				<input  type="text" class="form-control" name='ISFEntryNO' id="ISFEntryNO" 
				  placeholder="Enter ISF Entry NO" value="<?php echo $RowISFDetails['ISFEntryNO']?>">				
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Enrty Date</label>
				<input  type="date" class="form-control" name='ISFEntryDate' id="ISFEntryDate" 
				  placeholder="Enter ISF Entry Date" value="<?php echo $RowISFDetails['ISFEntryDate']?>">				
                </div>
				</div>

				<HR></HR>

<H5 style="color: BROWN;">Duty</H5>

				
                <div class="row">
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">AD Duty</label>
				<input  type="text" class="form-control" name='ADDuty' id="ADDuty" 
				  placeholder="Enter AD Duty" value="<?php echo $RowISFDetails['ADDuty']?>">				
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Port dues1</label>
				<input  type="text" class="form-control" name='Portdues1' id="Portdues1" 
				  placeholder="Enter Portdues1" value="<?php echo $RowISFDetails['Portdues1']?>">				
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Port dues2</label>
				<input  type="text" class="form-control" name='Portdues2' id="Portdues2" 
				  placeholder="Enter Port dues2" value="<?php echo $RowISFDetails['Portdues2']?>">				
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Others</label>
				<input  type="text" class="form-control" name='Others' id="Others" 
				  placeholder="Enter Others" value="<?php echo $RowISFDetails['Others']?>">				
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Grand Total</label>
				<input  type="text" class="form-control" name='GrandTotal' id="GrandTotal" 
				  placeholder="Enter Grand Total" value="<?php echo $RowISFDetails['GrandTotal']?>">				
                </div>
				
								
				
</div>

				<HR></HR>

<H5 style="color: BROWN;">Payment</H5>

				
                <div class="row">
				

               <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Bank Ref No</label>
				<input  type="text" class="form-control" name='BankRefNo' id="BankRefNo" 
				  placeholder="Enter Bank Ref No" value="<?php echo $RowISFDetails['BankRefNo'];?>">				
                </div>

               <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Date</label>
				<input  type="Date" class="form-control" name='PaymentDate' id="PaymentDate" 
				  placeholder="Enter Payment Date" value="<?php echo $RowISFDetails['PaymentDate'];?>">				
                </div>

                </div>
				<HR></HR>

<H5 style="color: BROWN;">Description</H5>

				
                <div class="row">
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Temperature of Record</label>
				<input  type="text" class="form-control" name='TemperatureOfRecod' id="TemperatureOfRecod" 
				  placeholder="-20 DEGREES CELSIUS" value="-20 DEGREES CELSIUS">				
                </div>


              </div>
				<HR></HR>

<H5 style="color: BROWN;">FDA</H5>

				
                <div class="row">
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">FDA Clear Date</label>
				<input  type="date" class="form-control" name='FDAClearDate' id="FDAClearDate" 
				  placeholder="Enter FDA Clear Date" value="<?php echo $RowISFDetails['FDAClearDate']; ?>">				
                </div>

               <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">FDA Exam</label>
				<input  type="text" class="form-control" name='FDAExam' id="FDAExam" 
				  placeholder="Enter FDA Exam" value="<?php echo $RowISFDetails['FDAExam'];?>">				
                </div>

              <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Start Date</label>
				<input  type="date" class="form-control" name='StartDate' id="StartDate" 
				  placeholder="Enter Start Date" value="<?php echo $RowISFDetails['StartDate']; ?>">				
                </div>
                
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">End Date</label>
				<input  type="date" class="form-control" name='EndDate' id="EndDate" 
				  placeholder="Enter End Date" value="<?php echo $RowISFDetails['EndDate'];?>">				
                </div>

               <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Get Out Full</label>
				<input  type="date" class="form-control" name='GetOutFull' id="GetOutFull" 
				  placeholder="Enter GetOutFull" value="<?php echo $RowISFDetails['GetOutFull'];?>">				
                </div>
              <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Remarks</label>
				<input  type="TEXT" class="form-control" name='Remarks' id="Remarks" 
				  placeholder="Enter Remarks" value="<?php echo $RowISFDetails['Remarks']?>">				
                </div>
             
             <div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">status</label>
                 <select class="form-control select2" id="Status" name="Status" >
				  <option  value="">Select</option>
				  <option <?php if($RowISFDetails['Status']=='Rejected') {?>selected="selected"<?php } ?> 
				  value="Rejected">Rejected</option>
				  <option  <?php if($RowISFDetails['Status']=='Cancelled') {?>selected="selected"<?php } ?>
				  value="Cancelled">Cancelled</option>
                  <option  <?php if($RowISFDetails['Status']=='Destroyed') {?>selected="selected"<?php } ?>
				  value="Destroyed">Destroyed</option>
                 <option  <?php if($RowISFDetails['Status']=='ROB') {?>selected="selected"<?php } ?>
				  value="ROB">Container ROB</option>
                 <option  <?php if($RowISFDetails['Status']=='Return') {?>selected="selected"<?php } ?>
				  value="Return">Return Container</option>
              </select>
			</div>

         <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Shipment Issues</label>
				<input  type="TEXT" class="form-control" name='ShipmentIssues' id="ShipmentIssues" 
				  placeholder="Enter Shipment Issues" value="<?php echo $RowISFDetails['ShipmentIssues'];?>">				
                </div>
				
				</div>
				<HR></HR>
         <H5 style="color: BROWN;">Dates</H5>				
                <div class="row">
				
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Freezing Date</label>
				<input  type="date" class="form-control" name='FreezingDate' id="FreezingDate" 
				  placeholder="Enter End Date" value="<?php echo $RowISFDetails['FreezingDate'];?>">				
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Consumption Date</label>
				<input  type="date" class="form-control" name='ConsumptionDate' id="ConsumptionDate" 
				  placeholder="Enter End Date" value="<?php echo $RowISFDetails['ConsumptionDate'];?>">				
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Production Date</label>
				<input  type="date" class="form-control" name='ProductionDate' id="ProductionDate" 
				  placeholder="Enter End Date" value="<?php echo $RowISFDetails['ProductionDate'];?>">				
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Best Before Date</label>
				<input  type="date" class="form-control" name='BestBeforeDate' id="BestBeforeDate" 
				  placeholder="Enter End Date" value="<?php echo $RowISFDetails['BestBeforeDate'];?>">				
                </div>


				
				</div>
				
				<HR></HR>
         <H5 style="color: BROWN;">Lc Details</H5>				
                <div class="row">
				
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Lc No </label>
				<input  type="text" class="form-control" name='LcNo' id="LcNo" 
				  placeholder="Enter Lc No" value="<?php echo $RowISFDetails['LcNo'];?>">				
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Lc Date</label>
				<input  type="date" class="form-control" name='LcDate' id="LcDate" 
				  placeholder="Enter End Date" value="<?php echo $RowISFDetails['LcDate'];?>">				
                </div>
				<div class="form-group col-lg-4" >
                  <label for="exampleInputEmail1">Issued By</label>
				  
				  <textarea name="LcIssuedBy" class="form-control" rows="4" cols="50" 
                    id="LcIssuedBy"   
					placeholder="Enter Issued By" ><?php echo $RowISFDetails['LcIssuedBy'] ?></textarea>
				 		
		
                </div>
				
				
				</div>
				
			
				
				
					
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
       class="btn btn-danger  "> Close</a>
  		<?php if($Count==0){	 ?>
                <button type="submit"  name='submit' class="btn btn-primary">Add</button>
			 
            
					<?php }  else{	 ?>
					
                <button type="submit"  name='submit' class="btn btn-primary">Update</button>
				<?php } ?>
<button id="<?php echo  $PIId;?>" style="width:100px;"
	onClick="reply_click(this.id)" class="btn btn-warning "> Delete</button>
	
								
              </div>
			</div> <!-- end row -->   









                </div>
                <!-- End Page-content -->

                 
                
            </div>
			
            <!-- end main content-->

        </div>
		<?php include ($IncludesPath."footer.php");?>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		
  
       
    </body>
<script>
$('#Cminus').on('click', function(){
	
      var NoCon= $("#NoContainers").val();
	  
	  if(NoCon > 1){
		   NoCon= parseInt(NoCon)-1;
		 $("#NoContainers").val(NoCon); 
	  }
});

$('#Cplus').on('click', function(){
	 
       var NoCon= $("#NoContainers").val();
		  if(NoCon < 10){
			 NoCon= parseInt(NoCon)+1;
		 $("#NoContainers").val(NoCon); 
	  }
});
</script>

<script>

$("document").ready(function(){
	  $("#ConsigneeID").select2();
	  $("#BuyerID").select2();
	  $("#DestinationCountry").select2();

    $("#file").change(function() {
        alert('changed!');
    });
	
	

	
});
  	

</script>


<script>

 $('#close').click(function(e) {
 window.close();
 });
 

$("document").ready(function(){
	
	  $("#ConsigneeID").select2();
	  $("#PortOfDischarge").select2();
	  
	  getBankDetails();
});

$('#ConsigneeID').change(function(){
	 var BuyerId= $('#ConsigneeID').val(); 
	 if(BuyerId==''){
		 return;
	 }
   $.ajax({
    type: 'POST',
    url: 'Ajax/AjBuyerDtls.php',
	dataType: 'json',
    data: {'BUYERID' : BuyerId},  
    success: function(response){
		 var myJSON = JSON.parse(JSON.stringify(response));
		
		 $("#DestinationCountry").val(myJSON['ConsigneeCountry']).change();
		 /*$("#BRAND").val(myJSON['ConsigneeBrand']);
		
		 $("#PORT_OF_DIS").val(myJSON['ConsigneePort']);
		 $("#ADDRESS_OF_CONSIGNEE").val(myJSON['ConsigneeAddress']);
		 $("#FISHING_PMT_NO").val(myJSON['ConsigneeFishingNo']);
		 */
	 }
  });
  });
  
    $('#ExporterID').change(function(){

	  getBankDetails();
	 
	});
	
	function getBankDetails(){
			 var ExpID= $('#ExporterID').val(); 
	    $.ajax({
    type: 'POST',
    url: 'Ajax/AjExpBankDetails.php',
	//dataType: 'json',
    data: {'ExpID' : ExpID},  
    success: function(response){
		 $("#BankID").html(response);		 
	 }
  });
	 
	}
  
  $('#DestinationCountry').change(function(){
	 var CountryId= $('#DestinationCountry').val(); 
	 if(CountryId==''){
		 return;
	 }
   $.ajax({
    type: 'POST',
    url: 'Ajax/AjPortDtls.php',
	//dataType: 'json',
    data: {'CountryId' : CountryId},  
    success: function(response){
		 //var myJSON = JSON.parse(JSON.stringify(response));
		// console.log(response);
		 $("#PortOfDischarge").html(response);
		// $("#FinalDestination").html(response);
		
		 
	 }
  });
  
     $.ajax({
    type: 'POST',
    url: 'Ajax/AjPortFinalDest.php',
	//dataType: 'json',
    data: {'CountryId' : CountryId},  
    success: function(response){
		 //var myJSON = JSON.parse(JSON.stringify(response));
		// console.log(response);
		// $("#PortOfDischarge").html(response);
		 $("#FinalDestination").html(response);
		
		 
	 }
  });
  });

</script>


<Script>

$('document').ready(function(){
	
	
	$('#CreatePI').on('click',function(){
    var submitButton = document.getElementById("Create");
	
	var InvNo = $('#InvNo').val();
	var NoContainers = $('#NoContainers').val();
	var InvDate = $('#InvDate').val();
	var ExporterID = $('#ExporterID').val();
	
	if(InvNo==''){
		submitButton.click();
	return;
	}
	
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjCheckPINumberAvalabitity.php',
	dataType: 'json',
    data: {'InvNo' : InvNo,'NoContainers':NoContainers,'InvDate':InvDate,'ExporterID':ExporterID},  
    success: function(response){
		 var myJSON = JSON.parse(JSON.stringify(response));
		 var NotAvalNo="Invoice Number are Not Aval ";
		 var Aval="Aval Invoice Number are  -"+myJSON['AvalNo'];
		 
		 
		 
		 if(myJSON['Error'] ==null){
			submitButton.click();
		 }else{
		var r = confirm(NotAvalNo + "" +Aval);
		if (r == true) {
			submitButton.click();
		}else{ 
		//return;
		}
		 }			 
		 
	 }
  });
	
	

    	});

  //alert('hiiiii');
    $('#ConsigneeID').on("keyup", function(){
        /* Get input value on change */
		alert('hiiii');
        var inputVal = $(this).val();
		
		//alert(inputVal);
		var resultDropdown = $(this).siblings(".result");
        if(inputVal.length){
            $.get("Ajax/AjaxConsigneeSearch.php", {term: inputVal}).done(function(data){
                // Display the returned data in browser
				
				//console.log('test');
				console.log(data);
				//alert(data);
                resultDropdown.html(data);
            });
        } else{
            resultDropdown.empty();
        }
		
    });
	
	    // Set search input value on click of result item
$(document).on("click", ".result p", function(){
        var iid=$(this).attr('id');
//alert(iid);
$("#ConsigneeID").val(iid);
        $(this).parent(".result").empty();
         //alert(cat);
		$.ajax({
			type:'POST',
			url:'Ajax/AjaxConsigneeDetails.php',
			data:{'id':iid},
		success:function(data){
			//alert(data);
			console.log(data);
var obj = JSON.parse(data);


var C_NAME=obj.ConsigneeName;
var C_ADD_id=obj.ConsigneeAddress;
var C_PORT_OF_DIS=obj.ConsigneePort;
var BUYER_COUNTRY=obj.ConsigneeCountry;
var BRAND_NAME=obj.ConsigneeBrand;
var SPECIAL_INST=obj.ConsigneeSplIns;
var FISHING_PMT_NO=obj.ConsigneeFishingNo;
alert(C_NAME);


$('#ConsigneeId').val(iid);
$('#ConsigneeAddress').val(C_NAME);
$('#ConsigneeBrand').val(BRAND_NAME); 
$('#ConsigneeCountry').val(BUYER_COUNTRY); 
$('#ConsigneePort').val(C_PORT_OF_DIS); 
$('#ConsigneeAddress').val(C_ADD_id); 
$('#ConsigneeSplIns').val(SPECIAL_INST);  
$('#ConsigneeFishingNo').val(FISHING_PMT_NO); 

		} 		
	});
$(this).parent(".result").empty();
        
});

});   	

</script>










<script>

 $('#close').click(function(e) {
 window.close();
 });
function reply_click(clicked_id) {

   		var r = confirm("Are You Sure Do You Want To Delete "+clicked_id+"?");
  if (r == true) { 
		 //alert(clicked_id);		  
		$.ajax({
		  url:'Ajax/AjaxDelete.php',
		  type:'POST',
		  data:{'TYPE':'CustomDelete','PiID':clicked_id},
		  success:function(data){
			  
			 alert("Deleted Successfully");
			 window.close();
			
			}
			});	

} else {
			
  }			
  }

</script>

<script>

function showData(data){
			
					//$("#PIList").html(data);
 
 		var Stable=    $('#scroll-vertical-datatable').DataTable({
        "scrollY":        "450px",
        "scrollCollapse": true,
        "paging":         false,
        "language": {
            "paginate": {
                "previous": "<i class='mdi mdi-chevron-left'>",
                "next": "<i class='mdi mdi-chevron-right'>"
            }
        },
        "drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        },
		buttons: ['copy', 'excel', 'pdf', 'colvis']
    });
	
	Stable.buttons().container()
        .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
		}

</script>



<script>


 $(document).on('click','#AddGrossWtPerBrkUp',function(e){
	 			e.preventDefault();

	var PoNo=$(this).data('value');
	var PoID =$(this).data('id');
	  
	     $('#Update').val(PoID);	 
	     showPoPopUp(PoNo,PoID);
	  
    	});
		
function showPoPopUp(PoNo,PoID){
	
			 $.ajax({
		   url:'Ajax/AjaxGrossWtPopUp.php',
		  type:'POST',
		  data:{'PoID':PoID},
		  success:function(data){
			//  $("#Update").hide();

			 $('.modal-title').text('PO Status Details - '+PoNo);
			$("#PopUpTable").html(data);

			$('#myModal').modal('show');
					}
			});

}	

			  	
$(document).on('click','#Update',function(e){
	updateGrossWeight();
});
function updateGrossWeight(){
	
	  var InvoiceID='<?php echo $PIId?>';
	  var AryPackingStyleIds=<?php echo json_encode($AryPackingStyleIds); ?>;
	  var ClickedId =$(this).data('id');
	 
	  	  var AryBreakUp = [];
	  	 
	   
	   for (i = 0; i < AryPackingStyleIds.length; i++) {
		   ClickedId=AryPackingStyleIds[i];
		   
	  var GrossWeight='#GrossWeight'+ClickedId;
	  var GrossWeight=$(GrossWeight+"").val();
	 
	  
	  
	  var jsonData = {};
	  
	  jsonData['PckId'] = ClickedId;
	  jsonData['GrossWeight'] = GrossWeight;
	  
	  
	  AryBreakUp.push(jsonData);
					  
					  
	   }
	
	 
	   	
	 
		if(GrossWeight==''){
			alert('Please Enter Gross wt');
			return;	
		}
		
	
	$.ajax({
    type: 'POST',
	    dataType: "json",
    url: 'Ajax/AjCsUpdates.php',
    data: {'InvoiceID':InvoiceID,
	'Type':'GrosswtUpdate','AryBreakUp':JSON.stringify(AryBreakUp)},
	
    
     success:function(data){
			  
			  var obj = JSON.parse(JSON.stringify(data));  
		 if(obj.error == null){
			 $('#TotalGrossWt').val(obj.TotalGrossWt);
			 
			 alert("Updated Successfully");
				
		 }else{
			 alert(obj.error);
		 }
			  
			  

			}
  });

}	
//});	

</script>



<script>


 $(document).on('click','#PoDetails',function(e){
	 			e.preventDefault();

	var PoNo=$(this).data('value');
	  var PoID =$(this).data('id');
	  
	 $('#Update').val(PoID);
	 
	 showPoPopUp(PoNo,PoID);
	  
	

			
    	});
		
function showPoPopUp(PoNo,PoID){
	
			 $.ajax({
		   url:'Ajax/AjTranshipList.php',
		  type:'POST',
		  data:{'PoID':PoID},
		  success:function(data){
			  $("#Update").hide();

			 $('.modal-title').text('PO Status Details - '+PoNo);
			$("#PopUpTable").html(data);

			$('#myModal').modal('show');
					}
			});

}	

$(document).on('click','.StatusBulkUpdate',function(e){
	e.preventDefault();
		var InvId=$(this).attr('id');
		var FromTOPID=$('#FromTOPID').val();
		var ToTOPID=$('#ToTOPID').val();
		var FromDate=$('#FromDate').val();
		var TranshipDt=$('#TranshipDt').val();
		var VesselName=$('#VesselName').val();
		var VOYNO=$('#VOYNO').val();
		
		
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxTranshipmentPortUpdate.php',
    data: {'FromTOPID':FromTOPID,'ToTOPID':ToTOPID,
	'FromDate':FromDate,'TranshipDt':TranshipDt,'InvId':InvId,'VesselName':VesselName,'VOYNO':VOYNO,
	'Type':'TranshipmentPortInsert'},
	
	
    
    success: function(response){
		var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);
			

			}else{
				$('#myModal').modal('hide');
			alert(data['msg']);
							

			}
	 }
  });

	
});

 $(document).on('click', '.Delete', function(e){
	  var TspID =$(this).data('id');


   		var r = confirm("Are  you sure Do you Want To Delete ?");
  if (r == true) { 
			  
		$.ajax({
		
		  url:'Ajax/AjaxTranshipmentPortUpdate.php',
		  type:'POST',
		  data:{'Type':'TranshipmentPortDelete','TspID':TspID},
		  
			 success: function(response){
		var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);
			

			}else{
				
				
							alert(data['msg']);

				location.reload();
							

			}
	 }
			});	

} else {
			
  }			
  });

</script>



<script>
function reply_click(clicked_id) {

   		var r = confirm("Are You Sure Do You Want To Delete "+clicked_id+"?");
  if (r == true) { 
		 //alert(clicked_id);		  
		$.ajax({
		  url:'Ajax/AjaxDelete.php',
		  type:'POST',
		  data:{'TYPE':'CustomDelete','PiID':clicked_id},
		  success:function(data){
			  
			  
			// $("#example1").html(data);
			 alert("Deleted Successfully");
			location.reload();
			
			}
			});	

} else {
			
  }			
  }

</script>

<script>

function showData(data){
			
					//$("#PIList").html(data);
 
 		var Stable=    $('#scroll-vertical-datatable').DataTable({
        "scrollY":        "450px",
        "scrollCollapse": true,
        "paging":         false,
        "language": {
            "paginate": {
                "previous": "<i class='mdi mdi-chevron-left'>",
                "next": "<i class='mdi mdi-chevron-right'>"
            }
        },
        "drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        },
		buttons: ['copy', 'excel', 'pdf', 'colvis']
    });
	
	Stable.buttons().container()
        .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
		}

</script>

</html>

			

