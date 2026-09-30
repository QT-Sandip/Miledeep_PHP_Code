<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
$LotID=$_POST['LotID']; 
$Type=$_POST['Type'];

if($Type=='BillPreview'){
	$mData=getBillPreview($iconn,$LotID);
	echo json_encode($mData);
}else if($Type=='BillCreate'){
	
	$AryBillData=getBillPreview($iconn,$LotID);
	
	$AryBillData['LotID']=$_POST['LotID'];
	$AryBillData['PostedUserID']=$_POST['PostedUserID'];
	$AryBillData['FinancierID']=$_POST['FinancierID'];
	$AryBillData['Deductions']=$_POST['Deductions'];
	$AryBillData['Remarks']=$_POST['Remarks'];
	getBillCreate($iconn,$AryBillData);

	 
}


function getBillCreate($iconn,$AryBillData){
	
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$CreatedDateTime=$dateTime->format("Y-m-d  H:i:s");
	
	
	$LotID=$AryBillData['LotID'];
	$PostedUserID=$AryBillData['PostedUserID'];
	$FinancierID=$AryBillData['FinancierID'];
	$Deductions=$AryBillData['Deductions'];
	$Remarks=$AryBillData['Remarks'];
	$Yield=$AryBillData['Yield'];
	$PurchaseDate=$AryBillData['PurchaseDate'];
	
	$BillHonWeight=$AryBillData['HlWeight'];
	$BillCount=$AryBillData['BillCount'];
	$BillRate=$AryBillData['BillRate'];

	
	
	$SqlRmBillDetails="INSERT INTO `RmBillDetails`
			(`LotID`, `UserID`,`FinancierID`, `CreatedDateTime`,`Deductions`,`Yield`,`Remarks`)
			VALUES ('$LotID','$PostedUserID','$FinancierID','$CreatedDateTime','$Deductions',
					'$Yield','$Remarks')";	
	$StmtBillDetails=$iconn->query($SqlRmBillDetails);
	$RmBillDetailsId = $iconn->insert_id;

	if($StmtBillDetails){
		legacyAuditLogInsertById('RmBillDetails', 'ID', $RmBillDetailsId, array('LotID'=>$LotID,'UserID'=>$PostedUserID,'FinancierID'=>$FinancierID), array(), 'Accounts');
		
		$SqlRmBillBreakUp="INSERT INTO `RmBillBreakup`
		(`LotID`,`Description`, `Weight`, `Count`, `Rate`) 
		VALUES ('$LotID','Grading Hon','$BillHonWeight','$BillCount','$BillRate')";
		$StmtRmBillBreakUp=$iconn->query($SqlRmBillBreakUp);
		if($StmtRmBillBreakUp){
		  legacyAuditLogInsertById('RmBillBreakup', 'ID', $iconn->insert_id, array('LotID'=>$LotID,'Description'=>'Grading Hon'), array(), 'Accounts');
		}
		
		$SqlRmHonBrkUpList="SELECT * FROM `PpcHonGradingBreakup` where LotID='$LotID'";
		$StmtRmHonBrkUpList=$iconn->query($SqlRmHonBrkUpList);
		
		while($MRowBrkList=$StmtRmHonBrkUpList->fetch_array()){
			
			$HonWeight=$MRowBrkList['HonWeight'];
			$HonCount=$MRowBrkList['HonCount'];
			$PriceData=getPrice($iconn,$PurchaseDate,$HonCount);
			
	
			
			 $Rate= $PriceData['RmPrice'];
			
			
			$SqlRmBillBreakUp="INSERT INTO `RmBillBreakup` 
			(`LotID`,`Description`, `Weight`, `Count`, `Rate`) 
				VALUES ('$LotID','Hon Packed','$HonWeight','$HonCount','$Rate')";
			$StmtRmBillBreakUp=$iconn->query($SqlRmBillBreakUp);
			if($StmtRmBillBreakUp){
			  legacyAuditLogInsertById('RmBillBreakup', 'ID', $iconn->insert_id, array('LotID'=>$LotID,'Description'=>'Hon Packed','HonWeight'=>$HonWeight,'HonCount'=>$HonCount,'Rate'=>$Rate), array(), 'Accounts');
			}
		}
		
		
		
		$SqlDefectList="SELECT PurchaseDate,DefectWeight,
									DefectName,DefectPerc FROM RmLotDetatils as RMD 
						left JOIN RmDefectsDetails as RDD on RMD.RmLotID=RDD.LotID 
						left JOIN RmDefectsList as RDL on RDD.DefectID=RDL.DefectID WHERE LotID='$LotID'";
		$StmtDefectList=$iconn->query($SqlDefectList);
		
		while($MRowDefList=$StmtDefectList->fetch_array()){
			
			$DefectName=$MRowDefList['DefectName'];
			$DefectWeight=$MRowDefList['DefectWeight'];
			$DefectPerc=$MRowDefList['DefectPerc'];
			$PurchaseDate=$MRowDefList['PurchaseDate'];
			
			$PriceData=getPrice($iconn,$PurchaseDate,$BillCount);
			
			$Rate= round(($PriceData['RmPrice']*$MRowDefList['DefectPerc'])/100,2);
			
			
			$SqlRmBillBreakUp="INSERT INTO `RmBillBreakup` 
			(`LotID`,`Description`, `Weight`, `Count`, `Rate`) 
				VALUES ('$LotID','$DefectName','$DefectWeight','$BillCount','$Rate')";
			$StmtRmBillBreakUp=$iconn->query($SqlRmBillBreakUp);
			if($StmtRmBillBreakUp){
			  legacyAuditLogInsertById('RmBillBreakup', 'ID', $iconn->insert_id, array('LotID'=>$LotID,'Description'=>$DefectName,'DefectWeight'=>$DefectWeight,'Rate'=>$Rate), array(), 'Accounts');
			}
		}
		
	}
	
	if($StmtBillDetails){
	$Response = array('Error' => null, "Msg" => "Bill Created Successfully");
    echo json_encode($Response);
	}else{
	$Response = array('Error' => "Error Unable To Create Bill");
    echo json_encode($Response);
	}

	

}

function getBillPreview($iconn,$LotID){


   $SqlRmLotDetails="SELECT
 im.PurchaseDate, 
 im.LotNumber,
 im.AgentID,
 RPC.IsCalcVales,
 im.DriverID,
 im.VehicalID,
 im.FarmAddress,
 im.PurchaseWeight AS ExpQty,
 im.PurchaseCount AS ExpCount,
 im.VehicalID ,
 im.DriverID  FROM
RmLotDetatils as im
left JOIN RmPurchaseCategory as RPC  on RPC.CatID=im.PurchaseCategoryID WHERE im.RmLotID = '$LotID'";  
			
			$StmtLotDtls=$iconn->query($SqlRmLotDetails);
			$RowLotDetails=$StmtLotDtls->fetch_array();
			
			$PurchaseDate=$RowLotDetails['PurchaseDate'];			 
			$ExpCount=$RowLotDetails['ExpCount'];
			$ExpQty=$RowLotDetails['ExpQty'];
			 
		
			$SqlHonGrading="SELECT sum(HonWeight) as HonPacked FROM 
			`PpcHonGradingBreakup` WHERE LotID='$LotID'";
			$StmtHonGrading=$iconn->query($SqlHonGrading);
			$RowHonGrading=$StmtHonGrading->fetch_array();
			$HonPacked=$RowHonGrading['HonPacked'];

			$SqlGrading="SELECT * FROM `PpcHLGradingBreakup` where LotID='$LotID' Order by HLCount ASC";
			$StmtGrading=$iconn->query($SqlGrading);

			$AryGrades=array();
			while($RowGrading = $StmtGrading->fetch_assoc()){
			$HLWeight=$HLWeight+$RowGrading['HLWeight'];
			$AryGrades[]=$RowGrading ;
			}

				if($RowLotDetails['IsCalcVales'] == 0){
					
					$StandardYield=getStandardYeild($iconn,$ExpCount,'HL');
					$PriceData=getPrice($iconn,$PurchaseDate,$ExpCount);					
					$HonQty=$ExpQty-$HonPacked;
					
					$BillHonWeight=$ExpQty;
					$BillHonCount=$ExpCount;
					$BillYield=round(($HLWeight/$HonQty)*100,2);
					$BillRate=$PriceData['RmPrice'];
					$RmPriceDate=$PriceData['RmPriceDate'];					
					$YieldError=0;
				}else{
					
		  			$TempYield=getStandardYeild($iconn,$ExpCount,'HL');
					$TempCount=getActualCount($TempYield,$AryGrades);
					$AvgCount=getActualCount($TempYield,$AryGrades);
					$StandardYield=getStandardYeild($iconn,$AvgCount,'HL');
					$LoopCount=0;
					$YieldError=0;
					
					while($TempYield !=$StandardYield){
					 $TempCount=getActualCount($StandardYield,$AryGrades);
					 $TempYield=getStandardYeild($iconn,$TempCount,'HL');
					 $AvgCount=getActualCount($TempYield,$AryGrades);
					 $StandardYield=getStandardYeild($iconn,$AvgCount,'HL');

					if($LoopCount > 10){
						$YieldError=1;
						if($TempYield  < $StandardYield ){
						$StandardYield=$TempYield;						
						$AvgCount=$TempCount;
						}
						break;
					}
					$LoopCount++;
					}
					
					$BillYield=$StandardYield;
					$BillHonWeight=round(($HLWeight/$BillYield)*100,2);
					$BillHonCount=$AvgCount;			
					$PriceData=getPrice($iconn,$PurchaseDate,$BillHonCount);					
					$BillRate=$PriceData['RmPrice'];
					$RmPriceDate=$PriceData['RmPriceDate'];
					$BillHonCount=$PriceData['BillHonCount'];
				}

		if($BillHonCount > 99){
			$DIFF=$BillHonCount-100;
			$BillRate=$BillRate-$DIFF;
		}

		if($RowLotDetails['IsCalcVales'] == 0){
			$BillRate=$BillRate+2;
		}
		
		
			$DefectsQuery="SELECT sum(DefectWeight) as DefectWeight FROM 
			`RmDefectsDetails` WHERE LotID='$LotID'";
			$DefectsExec=$iconn->query($DefectsQuery);
			$DefectsRow=$DefectsExec->fetch_array();
			$DefectsWeight=$DefectsRow['DefectWeight'];
			
			$BillHonWeight=$BillHonWeight-$DefectsWeight;
			
		
		$DATA['PurchaseDate']=$PurchaseDate; //Hl In terms Of Headon
		$DATA['HlWeight']=$BillHonWeight; //Hl In terms Of Headon
		$DATA['BillCount']=$BillHonCount;
		$DATA['GradingTable']=getGradingBreakUpTbl($iconn,$ExpCount,$BillHonCount,$HLWeight,$BillYield,$AryGrades);
		$DATA['PurchaseTable']=getRmLotData($iconn,$LotID);
		$DATA['BillRate']=$BillRate;
		$DATA['RmPriceDate']=$RmPriceDate;
		$DATA['Yield']=$BillYield;
		$DATA['YieldError']=$YieldError;
		$DATA['HonWeight']=$HonUsed;
		$DATA['HonCount']=$PackedCount;
		$DATA['PackedPrice']=$PackedPrice;
		$DATA['BillDetails']=getBillDetailsTbl($iconn,$LotID,$BillHonCount);
		$DATA['FinalBillDetails']=getFinalBill($iconn,$LotID);

	return $DATA;

}


function getGradingBreakUpTbl($iconn,$ExpCount,$BillHonCount,$HLWeight,$FinalYield,$AryGrades){
	
	//print_r($AryGrades);
	
	$size=40;
	$size1=60;
	$textw='30%';
	$text=2;

	 $content = '<table >


				<tr>
				<th  align="CENTER" width="4%"><font size="'.$text.'"> <B>S.L</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>HL Count</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>HL Qty</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>ESTD HON</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>Packed %</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>ESTD HON CT</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>Total  Pcs</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>Average HON CT</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>Purchase Count</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"><B>Diff</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"><B>Yield</B></font></th>
                <th  align="CENTER" width="6%"><font size="'.$text.'"><B>Stndrd.Yield</B></font></th>
                </tr>';

				$c=1+$start_from;
				$cell_size=100-30;
				$cell_size=$cell_size/count($AryGrades);
				$c=1;
				$TOTAL_HLWeight=0;
				$TOTAL_ESTD_HON=0;
				$TOTAL_PERC=0;
				$TOTAL_PCS=0;
				$TOTAL_COUNTS=0;

				$AvgCount=0;
				foreach($AryGrades  as $EachGrade){

//echo $EachGrade;

			$EachGradeHlWeight=$EachGrade['HLWeight'];
			$count=$EachGrade['HLCount'];
			$pack_Perc=0;
			
			$PERC=($EachGradeHlWeight/$HLWeight)*100;
			 $ESTD_HON=($EachGradeHlWeight/$FinalYield)*100;
			$PCS=$EachGradeHlWeight * $count * 2.2;

			$ESTD_HON_COUNT=$PCS/$ESTD_HON;


			$TOTAL_HLWeight=$TOTAL_HLWeight+$EachGradeHlWeight;
			$TOTAL_ESTD_HON=$TOTAL_ESTD_HON+$ESTD_HON;
			$TOTAL_PERC = $TOTAL_PERC + $PERC;
			$TOTAL_PCS=$TOTAL_PCS+$PCS;
			$TOTAL_COUNTS=$TOTAL_COUNTS+$ESTD_HON_COUNT;

					$content .= ' <tr>

				<th  align="CENTER" width="4%"><font size="'.$text.'"> <B>'.$c.'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>'.$count.'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>'.$EachGradeHlWeight.'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>'.ROUND($ESTD_HON,3).'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>'.ROUND($PERC,3).'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>'.ROUND($ESTD_HON_COUNT,3).'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>'.ROUND($PCS,3).'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B></B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B></B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></th>
                <th  align="CENTER" width="6%"><font size="'.$text.'"><B></B></font></th>
                </tr>';
					$c++;

				}

				$AvgCount=$TOTAL_PCS/$TOTAL_ESTD_HON;
				$DIFExpCount=$ExpCount-$AvgCount;

				$AVG_YEILD=($TOTAL_HLWeight/round($TOTAL_ESTD_HON,2))*100;



				$FINAL_COUNT=$AvgCount;
				// $TEMP_C=getAvgCount($AvgCount);
				 
				$content .= '

				<tr>

                <th  align="CENTER" width="4%"><font size="'.$text.'"> <B></B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>TOTAL</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"> <B>'.$TOTAL_HLWeight.'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>'.round($TOTAL_ESTD_HON,2).'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>'.round($TOTAL_PERC,2).'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B></B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B>'.round($TOTAL_PCS,2).'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B style="color:red" >'.round($BillHonCount,2).'</B></font></th>
                <th  align="CENTER" width="10%"><font size="'.$text.'"><B style="color:red">'.round($ExpCount,2).'</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"><B style="color:red">'.round($DIFExpCount,2).'</B></font></th>
                <th id="S_yield" align="CENTER" width="5%"><font size="'.$text.'"><B style="color:red">'.round($FinalYield,2).'</B></font></th>
                <th  align="CENTER" width="6%"><font size="'.$text.'"><B style="color:blue">'.getStandardYeild($iconn,round($BillHonCount,2),'HL').'</B></font></th></tr>



		</table>';


return $content;
}


function getRmLotData($iconn,$LotID){
	
	
	
$SqlRmLotDetails="SELECT *,FinancierName,PanNumber,
(SELECT COUNT(*) from QcRmOrgnReport as ORGD WHERE  ORGD.LotID=RMD.RmLotID ) as OrgnReport
FROM `RmLotDetatils` as RMD
left JOIN RmPurchaseCategory as RPC  on RPC.CatID=RMD.PurchaseCategoryID
left JOIN RmBillDetails as RBD  on RBD.LotID=RMD.RmLotID
left JOIN RmFinanciers as RFD  on RBD.FinancierID=RFD.ID
where RmLotID='$LotID'";
$StmtRmLots=$iconn->query($SqlRmLotDetails);
$RowLotDetails=$StmtRmLots->fetch_array();

if($RowLotDetails['OrgnReport'] > 0){
	
	
 $sql_INSP="SELECT *,
(SELECT UserName FROM QcRmOrgnDetails  as RMOD
LEFT JOIN AdminLoginDetails as ALD on RMOD.PostedUserID =ALD.UserID limit 1) as InspectedBy
FROM `QcRmOrgnReport`  as RMOR
LEFT JOIN AdminLoginDetails as ALD on RMOR.PostedUserID =ALD.UserID
WHERE LotID='$LotID'";
    $stmt_INSP=$iconn->query($sql_INSP); 
	$row_INSP=$stmt_INSP->fetch_array();
	
	$InspectedBy=$row_INSP['InspectedBy'];	
	$VerifiedBy=$row_INSP['UserName'];	
	$Remarks=$row_INSP['Remarks'];	 
	$Deductions=$row_INSP['SuggestedDeductions']; 
	
	
}




$content='<table>
<thead>

                                <tr>
								
								<th><strong>Purchase Date</strong></th>
								<th><strong>Lot Number</strong></th>
								<th><strong>Bill Name</strong></th>
								<th><strong>Purchase Category</strong></th>
								<th><strong>HON Qty (Kgs)</strong></th>
								<th><strong>Count</strong></th>
								 
								
								</tr>
								
									</thead>
									
										<tbody>
								<tr>
									<td>'.$RowLotDetails['PurchaseDate'] .'</td>
									<td>'.$RowLotDetails['LotNumber'].'</td>
								    <td>'.$RowLotDetails['FinancierName'].'-'.$RowLotDetails['PanNumber'].'</td>
									<td>'.$RowLotDetails['PurchaseType'].'</td>
									<td>'.$RowLotDetails['PurchaseWeight'].'</strong></td>
								
									<td>'.$RowLotDetails['PurchaseCount'].'</strong></td>
									
								
								</tr>
								
								
								
								'
								
								;
								
								
								
								
								$content.=	'</tbody>
							
								
								</table>';
								
								
								
								
$content.='<div class="row"> 

<table>
						<thead>

                                <tr>
								
								<th><strong>Inspected By</strong></th>
								<th><strong>Approved By</strong></th>
								<th><strong>Remarks</strong></th>
								<th><strong>Deduction /kg</strong></th>
								
								
								</tr>
								
									</thead>
									
										<tbody>
								

								
								<tr>
									<td>'. $InspectedBy .'</td>
									<td>'. $VerifiedBy.'</td>
								    <td>'.$Remarks.'</td>
									<td>'.$Deductions.'</td>
									
								
								</tr>'
								
								;
								
								
								
								
								$content.=	'</tbody>
							
								
								</table>';
								
								
return $content;
	

	
}

function getAvgCount($FINAL_COUNT){

			$TEMP_DIFF=$FINAL_COUNT - round($FINAL_COUNT);
			if(abs($TEMP_DIFF) > 0.25 && abs($TEMP_DIFF) < 0.76 ){
				 $TEMP_DIFF;
				if($TEMP_DIFF > 0){
					$LC=round($FINAL_COUNT);
					$UC=ceil($FINAL_COUNT);
				}else{
					 $LC=round($FINAL_COUNT)-1;
					 $UC=round($FINAL_COUNT);
			}

			$temp_c=$LC+0.5;
			$sql_acr="SELECT SUM(USD/2) as USD,SUM(COUNT/2) AS COUNT FROM ACC_PURCHASE_RATES
				WHERE TARCK_ID='$TARCK_ID' and COUNT >='$LC' and COUNT <='$UC'";
			}else{
				$temp_c=round($FINAL_COUNT);
				$sql_acr="SELECT * FROM ACC_PURCHASE_RATES WHERE TARCK_ID='$TARCK_ID' and COUNT ='$temp_c'";
			}

			return $temp_c;
			}


function getActualCount($StandardYield,$AryGrades){
				$Weight=0;
				$Pieces=0;

				//print_r($AryGrades);

				foreach($AryGrades as $Item){
					 $Weight +=$Item['HLWeight'];
					 $Pieces +=$Item['HLWeight'] * $Item['HLCount'] * 2.2;
					}

				$HonQty=($Weight/$StandardYield)*100;

				 $ActCount=$Pieces/$HonQty;
			  $ActCount=round($ActCount,2);
			//  $ActCount= getAvgCount($ActCount);

/*			if($ActCount != round($ActCount)){
			$ActCount=$ActCount-0.5;  //LOT ID ISSUE  6311
			}
			*/
				 $ActCount;

			return $ActCount;
			}


function getPrice($iconn,$PurchaseDate,$FINAL_COUNT){



		$REExpCount='NA';
		$RmPrice ='NA';




		  $SqlTrackId="SELECT * FROM `RmPurchaseRatesHistory` where RmPriceDate <= '$PurchaseDate 23:59:59' 
		order by RmPriceDate DESC, ID DESC  LIMIT 1";
		$StmtTrackId=$iconn->query($SqlTrackId);
		$RowTrackId=$StmtTrackId->fetch_array();
		$TARCK_ID=$RowTrackId['ID'];
		$RmPriceDate=$RowTrackId['RmPriceDate'];

		$FINAL_COUNT;

			if($FINAL_COUNT>99){
			$temp_c=100;
			$sql_acr="SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$temp_c'";
			$FINAL_COUNT=round($FINAL_COUNT);
			}else if($FINAL_COUNT > 52.25 && $FINAL_COUNT < 60.01){
				$FINAL_COUNT=ceil($FINAL_COUNT);
				$sql_acr="SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
			}else if($FINAL_COUNT > 62.25 && $FINAL_COUNT < 70.01){
				$FINAL_COUNT=ceil($FINAL_COUNT);
				$sql_acr="SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
			}else if($FINAL_COUNT > 72.25 && $FINAL_COUNT < 80.01){
				$FINAL_COUNT=ceil($FINAL_COUNT);
				$sql_acr="SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
			}else if($FINAL_COUNT > 82.25 && $FINAL_COUNT < 90.01){
				$FINAL_COUNT=ceil($FINAL_COUNT);
				$sql_acr="SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
			}else if($FINAL_COUNT > 92.25 && $FINAL_COUNT < 100){
				$FINAL_COUNT=ceil($FINAL_COUNT);
				$sql_acr="SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
			}else{

			 $TEMP_DIFF=$FINAL_COUNT - round($FINAL_COUNT);

			if(abs($TEMP_DIFF) > 0.25 && abs($TEMP_DIFF) < 0.76 ){

				 $TEMP_DIFF;
				if($TEMP_DIFF > 0){
					$LC=round($FINAL_COUNT);
					$UC=ceil($FINAL_COUNT);


				}else{
					 $LC=round($FINAL_COUNT)-1;
					 $UC=round($FINAL_COUNT);


			}

			 $sql_acr="SELECT SUM(RmPrice/2) as RmPrice,SUM(RmCount/2) AS COUNT FROM RmPurchaseRates
				WHERE DateID='$TARCK_ID' and RmCount >='$LC' and RmCount <='$UC'";
			}else{

				$temp_c=round($FINAL_COUNT);
				 $sql_acr="SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$temp_c'";



			}


			}
			
			//echo $sql_acr;


		 $stmt_acr=$iconn->query($sql_acr);
		 $row=$stmt_acr->fetch_array();
		 $RmPrice=round($row['RmPrice'],2);
		 $data['RmPriceDate']=$RmPriceDate;
		 $data['RmPrice']=$RmPrice;
		 $data['BillHonCount']=$FINAL_COUNT;
	return	 $data;

}

function getBillDetailsTbl($iconn,$LotID,$BillHonCount){
	
	            $BillTbl='<div class="card-body" >
				  <h4 class="card-title">HeadLess Grading Details</h4><hr>
				<div class="row">
                 
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Hon Quantity</label>
                  <input readonly required type="text" class="form-control" name="" id="WEIGHT"
				  placeholder="Enter HeadON Quantity" 
				  style="color: white; background: gray;">
                </div>
			
				
				<div class="form-group col-sm-3"  >
                  <label for="exampleInputEmail1">Hon Count</label>
                  <input readonly  required type="text" class="form-control" name="" id="COUNT"
				  placeholder="Enter HeadON Count"
				  style="color: white; background: gray;">
                </div>  
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Price /Kg</label>
                  <input readonly  required type="text" class="form-control"  name="RATE" id="RATE" 
				  placeholder="Enter Price/Kg"
				  style="color: white; background: gray;">
                </div>
				
				<div class="  form-group col-sm-3" >
                  <label for="exampleInputEmail1">Yield</label>
                  <input  readonly type="text" class="form-control" name="" id="YIELD"
				  placeholder="Enter Hon Packed" value="0"
				  style="color: white; background: gray;">
                </div>';
				
				
		 $SqlHonGrading="SELECT * FROM RmLotDetatils as RMD 
						left JOIN PpcHonGradingBreakup as HOG on RMD.RmLotID=HOG.LotID WHERE LotID='$LotID'";
		$StmtHonGrading=$iconn->query($SqlHonGrading);
		
				if($StmtHonGrading->num_rows > 0){
			
				$BillTbl.='<div class="form-group col-sm-12" >
				<h4 class="card-title">Head On Packing Details</h4><hr>
				</div>';
				}
		
				
				while($RowHonGrading=$StmtHonGrading->fetch_array()){
				
				$PriceData=getPrice($iconn,$RowHonGrading['PurchaseDate'],$RowHonGrading['HonCount']);
				$BillRate=$PriceData['RmPrice'];
				
				$BillTbl.='
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Hon Packed Quantity</label>
                  <input readonly  required type="text" class="form-control" 
				  value="'.$RowHonGrading['HonWeight'].'"  
				  style="color: white; background: gray;">
                </div>
				
				
				<div class="form-group col-sm-3"  >
                  <label for="exampleInputEmail1">Packed Count</label>
                  <input readonly required type="text" class="form-control" 
				  value="'.$RowHonGrading['HonCount'].'" 
				  style="color: white; background: gray;">
                </div> 
				
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Packed Price /Kg</label>
                  <input readonly required type="text" class="form-control" 
				  value="'.$BillRate.'"
				  style="color: white; background: gray;">
                </div>
				
				<div class="  form-group col-sm-3" >
                  <label for="exampleInputEmail1">Yield</label>
                  <input  readonly type="text" class="form-control"  value="NA"
				  style="color: white; background: gray;">
                </div>';
				
				}
				
				
				$SqlDefects="SELECT PurchaseDate,DefectWeight,
									DefectName,DefectPerc FROM RmLotDetatils as RMD 
						left JOIN RmDefectsDetails as RDD on RMD.RmLotID=RDD.LotID 
						left JOIN RmDefectsList as RDL on RDD.DefectID=RDL.DefectID WHERE LotID='$LotID'";
				$StmtDefects=$iconn->query($SqlDefects);
				
				if($StmtDefects->num_rows > 0){
					$BillTbl.=
				'<div class="form-group col-sm-12" >
				<h4 class="card-title">Defects Details</h4><hr>
				</div>';
				}
				
				while($RowDefects=$StmtDefects->fetch_array()){
				
				$PriceData=getPrice($iconn,$RowDefects['PurchaseDate'],$BillHonCount);
				$BillRate=$PriceData['RmPrice'];
				$BillRate=round(($BillRate*$RowDefects['DefectPerc'])/100,2);
				$BillTbl.=
				'
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Defect Details</label>
                  <input required type="text" readonly class="form-control" 
				  name=""
				  id="DEF_WEIGHT" value="'.$RowDefects['DefectName'].'"
				  placeholder="Enter HeadON Quantity"
					style="color: white; background: gray;"				  >
                </div>
				
				
				
				
				<div class="form-group col-sm-3">
                  <label id="price_head">Weight</label>
                  <input required type="text" class="form-control"  
				  name="" 
				  id="DEF_RATE" 
				  placeholder="Enter Price/Kg" value="'.$RowDefects['DefectWeight'].'"
				  style="color: white; background: gray;">
				  </div>
				  
				  <div class="form-group col-sm-3">
                  <label id="price_head">Price /Kg</label>
                  <input required type="number" class="form-control" 
				  name="" 
				  id="DEF_RATE" 
				  placeholder="Enter Price/Kg" value="'.$BillRate.'"
				  style="color: white; background: gray;">
				  </div>
				  
				  <div class="form-group col-sm-3">
                  <label id="price_head">Yield</label>
                  <input required type="text" class="form-control" 
				  name="" 
				  id="DEF_RATE" 
				  placeholder="Enter Price/Kg" value="NA"
				  style="color: white; background: gray;">
				  </div>'
				 ;
				}
			
		
return $BillTbl;;		
				
	
}



function getFinalBill($iconn,$LotID){
	

			
			
 $BillQuery = "SELECT *,
(SELECT UserName FROM `RmUsersList` WHERE ID=RMD.AgentID ) as AgentName,
(SELECT UserName FROM `RmUsersList` WHERE ID=RMD.DriverID ) as DriverName,
(SELECT UserName FROM `RmUsersList` WHERE ID=RMD.GraderID ) as GraderName,
(SELECT AbStatus FROM `QcRmAbReport` WHERE LotID=RMD.RmLotID ) as ABStatus,
(SELECT Location FROM `RmPurchaseStations` WHERE PurchaseStationID=RMD.PurchaseStationID ) as PurchaseCenter,
(SELECT PurchaseType FROM `RmPurchaseCategory` WHERE CatID=RMD.PurchaseCategoryID ) as PurchaseType,
(SELECT VehicalNumber FROM `RmVehicalDetails` WHERE ID=RMD.VehicalID ) as TruckNumber,
RBD.Remarks as BillRemarks 
FROM `RmLotDetatils` as RMD
LEFT JOIN RmBillDetails RBD on RMD.RmLotID = RBD.LotID
LEFT JOIN QcRmOrgnReport QCOR on RMD.RmLotID = QCOR.LotID 
LEFT JOIN RmFinanciers RFD on RFD.ID = RBD.FinancierID 
WHERE RMD.RmLotID = '$LotID'";

$BillExec=$iconn->query($BillQuery);

$BillPanNumber= $center = $PurchaseDate = $headlessCount = $headlessQty = $LotNumber = "";
if(!empty($BillExec)){
	
	$BillRow = $BillExec->fetch_assoc();
	
	$ABStatusID=$BillRow['ABStatus'];
		
		
    $SqlABReport="SELECT * FROM `QcABOptions` WHERE OptID='$ABStatusID'";
	$StmtABReport=$iconn->query($SqlABReport);
    $RowABReport= $StmtABReport->fetch_assoc();

	
    $ABStatus = $RowABReport['Name'];
    $center = $BillRow['PurchaseCenter'];
	$PurchaseDate = $BillRow['PurchaseDate'];
	$BillPanNumber = $BillRow['PanNumber'];
	$Tdsperc = $BillRow['Tds'];
	$FarmAddress = $BillRow['FarmAddress'];
	$C_COUNT = $BillRow['C_COUNT'];
	$PurchaseCount = $BillRow['PurchaseCount'];
	$PurchaseWeight = $BillRow['PurchaseWeight'];
	$TruckNumber = $BillRow['TruckNumber'];
	$DriverName = $BillRow['DriverName'];
	$C_QTY = $BillRow['WEIGHT'];
	$RATE =$BillRow['RATE'];
	$LotNumber = $BillRow['LotNumber'];
	$TUBS = $BillRow['TUBS'];
	$FinancierName = $BillRow['FinancierName'];
	$AgentName = $BillRow['AgentName'];
	$VARITY = $BillRow['VARITY'];	
	$AccDedKg= $BillRow['Deductions'];
	$LabActDedKg= $BillRow['ActualDeductions'];
	$LabSugDedKg= $BillRow['SuggestedDeductions'];
	$PurchaseType= $BillRow['PurchaseType'];
	$GraderName= $BillRow['GraderName'];
	 $ACCOUNT_REMARKS= $BillRow['BillRemarks']; 
	
	




}
  $SqlBillBrkUpQuery = "SELECT * from RmBillBreakup WHERE  LotID = '$LotID'";
  $SqlBillBrkUpExec=$iconn->query($SqlBillBrkUpQuery);
			   
			   while($SqlBillRow = $SqlBillBrkUpExec->fetch_assoc()){
				   
				   $BillWeight+=$SqlBillRow['Weight'];
				   $BillCount=$SqlBillRow['Count'];
				   
			   }


	$content='';
	
		
	
	$size1=100/6;
	$size2=100/6;
	$size=100-$size1-$size2;	
	$size3=60;
	
				
				
				
	 $content .= '<table border="1" cellspacing="0" cellpadding="3"> 
	
	 <tr>  
           		<th  align="center" > Variety/Quality </th>
           		<th  align="center" > Count  </th>
           		<th  align="center" > Quantity </th>
           		<th  align="center" > Rate/Kg </th>
           		<th  align="center" > Deduction/Kg </th>
           		<th  align="center" > Amount  </th>
           		
               </tr>';
			   
			   
			   $BillBrkUpQuery = "SELECT * FROM `RmBillBreakup`  left join RmDefectsList on RmBillBreakup.Description=RmDefectsList.DefectID 
									WHERE LotID='$LotID'";

			   $BillBrkUpExec=$iconn->query($BillBrkUpQuery);
			   $BillWeight=0;
			   while($BillRow = $BillBrkUpExec->fetch_assoc()){
				   
				   if($BillRow['DefectName']!=''){
					 $Description= $BillRow['DefectName'];
				   }else{
					 $Description= $BillRow['Description'];
				   }
				   
				   $BillWeight+=$BillRow['Weight'];
				   $BillPrice+=round($BillRow['Weight'] * $BillRow['Rate']);
			   
			   $content .= '<tr>  
           		<td  align="left"  ><B>'.$Description.'</B></td>
           		<td  align="right" > '.$BillRow['Count'] .'  C </td>
           		<td  align="right" > '.$BillRow['Weight'] .'  Kgs</td>
           		<td  align="right" >'.$BillRow['Rate'] .' / Kgs</td>
           		<td  align="right" ><B></b></td>
           		<td  align="right"  ><B>'.round($BillRow['Weight'] * $BillRow['Rate']) .' /-  </b></td>
           		
               </tr>';
			   
			   };
			   
			   
			
			   
			   if($LabSugDedKg!=0){
				   $LabRemarks='A DEDUCTION OF RS.'.round($LabSugDedKg).'/- IS RECOMMENDED FOR QUALITY LOSS';
			   }else{
				  $LabRemarks='NILL'; 
			   }
			   
			 if($AccDedKg > 0){
				 $Sing='+';
				 
			 }else{
				 $Sing='';
			 }
			 
			   
			   $LabActDed= round($LabActDedKg*$BillWeight);
			   $AccDed= round($AccDedKg*$BillWeight);
			   
	
			   
			    $content .= '
				
				 <tr>  
           		<td  align="left" ><b>Sub Total</b></td>
           		<td  align="right"  > </td>
           		<td  align="right"  ><b>'.round($BillWeight,2).' Kgs</b></td>
           		<td  align="right"  ></td>
           		<td  align="right"  ></td>
           		<td  align="right"  ><B>'.$BillPrice .' /- </b> </td>
           		
               </tr> 
			   <tr>  
           		<td  align="left"  ><b>Lab </b></td>
				<td  align="center">'.$LabRemarks.'</td>
           		<td  align="right"  ></td>          		
           		<td  align="right"  ></td>
           		<td  align="right"  >'.$LabActDedKg.'</font></td>
           		<td  align="right" ><B> - '.$LabActDed .' /- </b> </td>
           		
               </tr>
			   <tr>  
           		<td  align="left" ><b>Accounts </b></td>	
           		<td  align="center" >'.$ACCOUNT_REMARKS.'</td>
				<td  align="right"  ></td>          		
           		<td  align="right"  ></td>
           		<td  align="right"  >'.$AccDedKg.'</td>
           		<td  align="right" ><B>'.$AccDed .' /- </b> </td>
           		
               </tr>'; 
			   
			   if($PurchaseDate > '2021-06-30'){
				   
				   $BillTotal=$BillPrice + $AccDed -$LabActDed;
			       
				   $TdsAmount=($BillTotal*$Tdsperc)/100;
				 
				// comment after bill creation updated
				   if($BillPanNumber !='' && 
				   strlen($BillPanNumber) ==10){
					  $TDSperc='0.1%'; 
					 $TdsAmount=($BillTotal*0.1)/100;  
				   }else{
					   $TDSperc='5%';
					   $BillPanNumber='NA';
					  $TdsAmount=($BillTotal*5)/100;
				   }
				   
				  //echo $TdsAmount; exit; 
			   $content.='<tr>  
           		<td  align="left"  ><b>TDS </b></td>	
           		<td  align="left" >PAN Number : '.$BillPanNumber.'</td>
				<td  align="center"  ></td>          		
           		<td  align="center"  ></td>
           		<td  align="right"  >'.$TDSperc.'</font></td>          		
           		<td  align="right"  ><B>'.round($TdsAmount) .' /- </b> </td>
           		
               </tr>';
			   $BillTotal=$BillTotal-$TdsAmount;
			    
			   }else{
			   //$BillTotal=$PURCHASE_AMOUNT + $DefectsAmount +$HonUsedAmount + $AccDeductions -$LabDeduction;
			    $BillTotal=$BillPrice + $AccDed -$LabActDed;
			   }
			   
			    $content.=' <tr>  
           		<td  align="left"  ><B> Total</B></td>          		
           		<td  align="center"  ></td>          		
           		<td  align="center"  ></td>          		
           		<td  align="center"  ></td>          		
           		<td  align="center"  ></td>          		
           		<td  align="right"  >Rs. <B>'.round($BillTotal) .' /- </b></td>
           		
               </tr>
				
				
			   
               
				
				
				
				</table>'; 
	
	return $content;	
	
}

?>
