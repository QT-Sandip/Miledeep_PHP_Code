<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

// echo "hi";
	/*********update purchase paymentvoucher numbers****** */
	// $SqlRackNames="SELECT PaymentMode,PaymentID,VoucherNo FROM `RmPaymentDetails`WHERE StationID=15 and Date between '2023-04-01' and '2024-03-31' ORDER BY `PaymentID` ASC ";//store1
	// // SELECT * FROM `RmPaymentDetails` WHERE StationID=15 and Date between '2023-04-01' and '2024-03-31' ORDER BY `PaymentID` ASC
	// $StmtRackNmes=$iconn->query($SqlRackNames);
	
	// while($RowRackNames=$StmtRackNmes->fetch_array()){

	// 		 $PaymentID=$RowRackNames['PaymentID'];
	// 		 $VoucherNo=$RowRackNames['VoucherNo'];
    //          if($VoucherNo<10){
    //             $VoucherNo='0'.$RowRackNames['VoucherNo'];
    //          }
	// 		 $PaymentRefNo=$RowRackNames['PaymentMode'].'PDP'.$VoucherNo;
			
	//  	  echo  $sqlUp = "UPDATE `RmPaymentDetails` SET `PaymentRefNo`='$PaymentRefNo' WHERE `PaymentID`='$PaymentID'";  
	// 	$StmtUp = $iconn->query($sqlUp);
	// 	if($StmtUp){
	// 	echo "success";
	// 	}
		
		 
	// }
	/*********update purchase paymentvoucher numbers****** */

	/*********update purchase Arrival numbers****** */


	// $SqlRackNames="SELECT RmArrivalID,LotCode,AutoArrivalReferenceNo FROM `RmArrivalDetatils` as RAD left join RmPurchaseStations as RPS on RPS.PurchaseStationID=RAD.ArrivalStationID
	// WHERE BillDate BETWEEN '2024-04-01' and '2025-03-31'   ORDER BY `RmArrivalID` DESC;";//store1
	// // SELECT * FROM `RmPaymentDetails` WHERE StationID=15 and Date between '2023-04-01' and '2024-03-31' ORDER BY `PaymentID` ASC
	// $StmtRackNmes=$iconn->query($SqlRackNames);
	
	// while($RowRackNames=$StmtRackNmes->fetch_array()){

	// 		 $RmArrivalID=$RowRackNames['RmArrivalID'];
	// 		 $AutoArrivalReferenceNo=$RowRackNames['AutoArrivalReferenceNo'];
	// 		 $LotCode=$RowRackNames['LotCode'];
    //          if($AutoArrivalReferenceNo<10){
    //             $AutoArrivalReferenceNo='000'.$RowRackNames['AutoArrivalReferenceNo'];
    //          } else if($AutoArrivalReferenceNo>=10 && $AutoArrivalReferenceNo<100){
    //             $AutoArrivalReferenceNo='00'.$RowRackNames['AutoArrivalReferenceNo'];
    //          }
	// 		 else if($AutoArrivalReferenceNo>=100){
    //             $AutoArrivalReferenceNo='0'.$RowRackNames['AutoArrivalReferenceNo'];
    //          }
			 
	// 		 $ArrivalNumber=$RowRackNames['LotCode'].'/'.'2425/'.$AutoArrivalReferenceNo;
			
	//  	  echo  $sqlUp = "UPDATE `RmArrivalDetatils` SET `ArrivalNumber`='$ArrivalNumber' WHERE `RmArrivalID`='$RmArrivalID'";  
	// 	$StmtUp = $iconn->query($sqlUp);
	// 	if($StmtUp){
	// 	echo "success";
	// 	}

	
	// /*********update purchase Arrival numbers****** */	
	
	/*********update Rm lot details****** */


	// $SqlRackNames="SELECT ArrivalNumber,RmArrivalID FROM `RmArrivalDetatils` 
	// WHERE BillDate BETWEEN '2024-04-01' and '2025-03-31'   ORDER BY `RmArrivalID` DESC";//store1
	// // SELECT * FROM `RmPaymentDetails` WHERE StationID=15 and Date between '2023-04-01' and '2024-03-31' ORDER BY `PaymentID` ASC
	// $StmtRackNmes=$iconn->query($SqlRackNames);
	
	// while($RowRackNames=$StmtRackNmes->fetch_array()){

	// 		 $RmArrivalID=$RowRackNames['RmArrivalID'];
	// 		 $ArrivalNumber=$RowRackNames['ArrivalNumber'];		 
	//  	  echo  $sqlUp = "UPDATE `RmLotDetatils` SET `LotNumber`='$ArrivalNumber' WHERE `RmReferenceID`='$RmArrivalID'";  
	// 	$StmtUp = $iconn->query($sqlUp);
	// 	if($StmtUp){
	// 	echo "success";
	// 	}
	// }
	/*********update Rm lot details****** */	
	
	/*********update Tds Amount****** */


// 	$SqlTdsData="SELECT RmArrivalID FROM `RmArrivalDetatils` order by RmArrivalID asc ";	

// 	$SqlTdsData="SELECT RmArrivalID,AutoArrivalReferenceNo,BillDate,ArrivalBillAmount,ArrivalHarvestAmount,
// 	RPS.PanNumber as SupplierPanNumber,AgentID, RUL.PanNumber as AgentPanNumber FROM `RmArrivalDetatils` as RAD 
// 	left join RmSuppliers as RPS on RPS.ID=RAD.RmSupplierID left join RmUsersList as RUL on RUL.ID=RAD.AgentID order by RmArrivalID asc ";	
// 	$StmtTdsData=$iconn->query($SqlTdsData);
	
// 	while($RowTdsData=$StmtTdsData->fetch_array()){

// 			 $ArrivalID=$RowTdsData['RmArrivalID'];
// 			 $AutoArrivalReferenceNo=$RowTdsData['AutoArrivalReferenceNo'];		 
// 			 $BillDate=$RowTdsData['BillDate'];	
// 			 $ArrivalBillAmount=$RowTdsData['ArrivalBillAmount'];	
// 			 $ArrivalHarvestAmount=$RowTdsData['ArrivalHarvestAmount'];	
// 			 $SupplierPanNumber=$RowTdsData['SupplierPanNumber'];	
// 			 $AgentID=$RowTdsData['AgentID'];	
// 			 $AgentPanNumber=$RowTdsData['AgentPanNumber'];	

// 			 if($AgentID==0){
// 				$AgentPanNumber= $SupplierPanNumber;
// 			 }
// 	 if($ArrivalBillAmount==''){
// 				$ArrivalBillAmount= 0;
// 			 }
			 
// 			 if($ArrivalHarvestAmount==''){
// 				$ArrivalHarvestAmount= 0;
// 			 }

// 	 	    $sqlUp = "	INSERT INTO `RmPanWiseSuppliedAmount`( `PanwiseArrivalID`, `PanwiseBillDate`, `PanwiseAutoArrivalReferenceNO`,
// 		   `PanwiseAmount`, `BillPanNumber`, `PanAmountType`) 
// 		  VALUES ('$ArrivalID','$BillDate','$AutoArrivalReferenceNo','$ArrivalBillAmount','$SupplierPanNumber',0),
// 		  ('$ArrivalID','$BillDate','$AutoArrivalReferenceNo','$ArrivalHarvestAmount','$AgentPanNumber',1)
// 				  ";
   
// 		$StmtUp = $iconn->query($sqlUp);
// 		if($StmtUp){
// 		echo "success";
// 		}
// 	}
	/*********update Tds Amount****** */

?>