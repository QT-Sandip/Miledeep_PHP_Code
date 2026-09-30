<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$action = (isset($_POST['action']) && $_POST['action']) ? $_POST['action'] : "";


function updateKitsInfo(
	$iconn,
	$LastInsertedID,
	$KitBreakUpLotIDs,
	$EnteredDamagedWells,
	$EnteredUsedWells,
	$KitOpeningDate,
	$StandardUsedWells
) {
	if ($KitBreakUpLotIDs) {
		$SqlGetKitID = "SELECT 
            mKitID,
            mNoOfWells,
            mKitBreakupID,
            mUtilizedWells,
            mBalanceWells,
            mKitOpeningDate 
            FROM `mediakitsbreakupdetails`
            WHERE mKitBreakupID IN ($KitBreakUpLotIDs)
            ORDER BY mBalanceWells ASC";
		$StmtGetKitBreakUpID = $iconn->query($SqlGetKitID);
		$lastusedwells = 0;
		$originalUsedwells = $EnteredUsedWells;

		while ($RowgetKitBreakUpID = $StmtGetKitBreakUpID->fetch_assoc()) {
			$mUtilizedWells = $RowgetKitBreakUpID['mUtilizedWells'];
			$KitID = $RowgetKitBreakUpID['mKitID'];
			$KitLotBreakupID = $RowgetKitBreakUpID['mKitBreakupID'];
			$BalanceWells = $RowgetKitBreakUpID['mBalanceWells'];
			$OldKitOpeningDate = $RowgetKitBreakUpID['mKitOpeningDate'];
			$OpeningDate = empty($OldKitOpeningDate) ? $KitOpeningDate : $OldKitOpeningDate;

			$EnteredUsedWells = $originalUsedwells - $lastusedwells;
			if ($BalanceWells <= $EnteredUsedWells) {
				$EnteredUsedWells = $BalanceWells;
			}
			$RemainingWells = $BalanceWells - $EnteredUsedWells;

			// Insert record in utilized kit breakup table
			$SqlInsertBreakUp = "
                INSERT INTO `mediarmihmputilizedkitbreakupdetails`
                (`swabproductsID`, `KitID`, `KitBreakUpID`, `BreakUpwiseUsedWells`, `fromPage`) 
                VALUES ('$LastInsertedID', '$KitID', '$KitLotBreakupID', '$EnteredUsedWells', 'product')
            ";
			$StmtInsertBreakUp = $iconn->query($SqlInsertBreakUp);

			if ($StmtInsertBreakUp) {
				legacyAuditLogInsertById('mediarmihmputilizedkitbreakupdetails', 'ID', $iconn->insert_id, null, $_POST, [
					'ReferenceNo' => $LastInsertedID,
					'Remarks' => 'Utilized kit breakup inserted (product)',
				], 'QualityControl');
				$totalmUtilizedWell = $EnteredUsedWells + $mUtilizedWells;
				$SqlUpdatekitBreakupDetails = "
                    UPDATE `mediakitsbreakupdetails` 
                    SET 
                        `mKitOpeningDate`='$OpeningDate',
                        `mUtilizedWells`='$totalmUtilizedWell',
                        `mBalanceWells`='$RemainingWells'
                    WHERE `mKitBreakupID`='$KitLotBreakupID'
                ";
				$StmtUpdatekitBreakupDetails = $iconn->query($SqlUpdatekitBreakupDetails);
				if ($StmtUpdatekitBreakupDetails) {
					legacyAuditLogUpdateById('mediakitsbreakupdetails', 'mKitBreakupID', $KitLotBreakupID, null, $_POST, [
						'ReferenceNo' => $LastInsertedID,
						'Remarks' => 'Kit breakup wells updated (product)',
					], 'QualityControl');
				}
				$lastusedwells += $EnteredUsedWells;
			}
		}
	}
}



if ($action == "getMediaList") {
	$ParameterID = isset($_POST['parameterId']) ? $_POST['parameterId'] : 0;
	//validate input
	if (!is_numeric($ParameterID)) {
		echo "Bad request";
		exit;
	} else if ($ParameterID < 0) {
		echo "Bad request";
		exit;
	} else {

		//Media list
		$SqlMediaList = "SELECT MediaID,MediaName FROM `usedmediamaster` where ParameterID = '" . $ParameterID . "' ";
		$SqlMediaList = $iconn->query($SqlMediaList);
		//end here
		$data = "<option value='0'>Select</option>";
		while ($rowMediaList = $SqlMediaList->fetch_array()) {
			$data .= "<option value='" . $rowMediaList['MediaID'] . "'>" . $rowMediaList['MediaName'] . "</option>";
		}
		echo $data;
		exit;
	}
} else if ($action == "getEquipmentList") {
	$mediaId = isset($_POST['mediaId']) ? $_POST['mediaId'] : 0;
	//validate input
	if ($mediaId == 0) {
		echo "Bad request";
		exit;
	} else {

		//Instrumentlist
		$SqlInstumentList = "SELECT InstrumentId,InstrumentName FROM `instrumentmaster` where MediaID in ( " . $mediaId . " )";
		$SqlInstumentList = $iconn->query($SqlInstumentList);
		//end here

		$data = "<option value='0'>Select</option>";
		while ($rowInstumentList = $SqlInstumentList->fetch_array()) {
			$data .= "<option value='" . $rowInstumentList['InstrumentId'] . "'>" . $rowInstumentList['InstrumentName'] . "</option>";
		}
		echo $data;
		exit;
	}
} else if ($action == "getMediaKitsList") {
	$mediaId = isset($_POST['mediaId']) ? $_POST['mediaId'] : 0;
	//validate input
	if ($mediaId == 0) {
		echo "Bad request";
		exit;
	} else {

		//MediaKitslist
		$SqlInstumentList = "
		SELECT 
			MBD.mKitBreakupID,MBD.mKitBreakupLotNo 
		FROM `mediakitsbreakupdetails` AS MBD 
		inner Join 
			mediakitsdetails as MD on MD.mKitID = MBD.mKitID
		where 
			MD.mMedia in ( " . $mediaId . " ) and 
			MBD.mBalanceWells>0 
	";
		$SqlInstumentList = $iconn->query($SqlInstumentList);
		// print_r($SqlInstumentList);
		//end here

		$data = "<option value='0'>Select</option>";
		while ($rowInstumentList = $SqlInstumentList->fetch_array()) {
			$data .= "<option value='" . $rowInstumentList['mKitBreakupID'] . "'>" . $rowInstumentList['mKitBreakupLotNo'] . "</option>";
		}
		echo $data;
		exit;
	}
} else if ($action == "getBalance") {
	$Kitlotno = isset($_POST['Kitlotno']) ? $_POST['Kitlotno'] : 0;
	//validate input
	if ($Kitlotno == 0) {
		echo "Bad request";
		exit;
	} else {

		//MediaKitslist
		$SqlInstumentList = "SELECT SUM(mNoOfWells) AS OpeningWells, SUM(mBalanceWells) AS BalanceWells, mKitOpeningDate FROM `mediakitsbreakupdetails` where mKitBreakupID in ( " . $Kitlotno . " )";
		$SqlInstumentList = $iconn->query($SqlInstumentList);
		//end here

		$data = [
			"OpeningWells" => 0,
			"BalanceWells" => 0,
		];
		$dynamicEntryTypes = [
			'tpcDynamicEntries',
			'ecDynamicEntries',
			'saDynamicEntries',
			'vcDynamicEntries',
			'vpDynamicEntries',
			'salDynamicEntries'
		];
		$rowInstumentList = $SqlInstumentList->fetch_assoc();
		$getBal =  getBalLimit($iconn,$dynamicEntryTypes,$_POST,$rowInstumentList);
		//echo '<pre>';print_r($getBal);die;
		//$BalanceWells = $rowInstumentList['BalanceWells'];
		$OpeningWells = $rowInstumentList['BalanceWells'];
		if(!empty($getBal)){			
			$KitlotnoArr = explode(',',$Kitlotno);
			$KitlotArr = [];
			for($i=0; $i<count($KitlotnoArr);$i++){
				if (array_key_exists($KitlotnoArr[$i], $getBal)) {//echo '<pre>';print_r($getBal);die;
					$BalanceWells += $getBal[$KitlotnoArr[$i]];
					$KitlotArr[] = $KitlotnoArr[$i];
				}
			}
			if($BalanceWells == 0){
				$lotArr = array_diff($KitlotnoArr, $KitlotArr);
				$lotIds = implode(',',$lotArr);
				//echo '<pre>';print_r($lotArr);die;
				if(!empty($lotIds)){
					$SqlInstumentList = "SELECT  SUM(mBalanceWells) AS BalanceWells FROM `mediakitsbreakupdetails` where mKitBreakupID in ( " . $lotIds . " )";
					$SqlInstumentList = $iconn->query($SqlInstumentList);
					$welRes = $SqlInstumentList->fetch_assoc();
					$OpeningWells = $BalanceWells = $welRes['BalanceWells'];
				}else{					
					//$BalanceWells = $rowInstumentList['BalanceWells'];
					$OpeningWells = $BalanceWells = 0;
				}
			}else{
				$lotIds = implode(',',$KitlotArr);
				if(!empty($lotIds)){
					$SqlInstumentList = "SELECT  (SUM(mBalanceWells) - $BalanceWells)  AS BalanceWells FROM `mediakitsbreakupdetails` where mKitBreakupID in ( " . $lotIds . " )";
					$SqlInstumentList = $iconn->query($SqlInstumentList);
				    $BalanceWells = $SqlInstumentList->fetch_assoc()['BalanceWells'];
					$OpeningWells = $OpeningWells-$BalanceWells;
				}else{
					$OpeningWells = $BalanceWells;
				}
			}
		}else{
			$BalanceWells = $rowInstumentList['BalanceWells'];
		}
		
		
		//while ($rowInstumentList = $SqlInstumentList->fetch_assoc()) {
			$data = [
				"OpeningWells" => $OpeningWells,
				//"BalanceWells" => $rowInstumentList['BalanceWells'],
				"BalanceWells" => $BalanceWells ,
				"mKitOpeningDate" => $rowInstumentList['mKitOpeningDate']
			];
		//}
		echo json_encode([
			"Error" => false,
			"Message" => "Done",
			"data" => $data
		]);
		exit;
	}
} else if ($action == "insert") {
	//echo '<pre>';print_r($_POST);die;
	extract($_POST);
	extract($_GET);

	if (isset($id) && $id > 0) {
		if (1) {
			$sqlInsert = "UPDATE `swabproducts` 
	    		SET 
	    		`BatchNumber` = '" . $BatchNumber . "',
		    	`Plant` = '" . $Plant . "',
		    	`SampleDate` = '" . $SampleDate . "',
		    	`DrawnBy` = '" . $DrawnBy . "',
	            `type_of_method` = '" . $type_of_method . "',		    	
		    	`AnalysisDate` = '" . $AnalysisDate . "',
		    	`ReportDate` = '" . $ReportDate . "',
		    	`Variety` = '" . $Variety . "',
		    	`sample_collection` = '" . $sample_collection . "',	    			    	

		    	`analysed_by` = '" . $analysed_by . "',
		    	`CheckedBy` = '" . $CheckedBy . "',
		    	`VerfiedBy` = '" . $VerfiedBy . "',
		    	`Remarks` = '" . $Remarks . "' where id = '" . $id . "' ";
			$stmt1 = $iconn->query($sqlInsert);
			if ($stmt1) {
				legacyAuditLogUpdateById('swabproducts', 'id', $id, null, $_POST, [
					'ReferenceNo' => $id,
					'Remarks' => 'Swab product updated',
				], 'QualityControl');
			}
		} else {
			$sqlInsert = "UPDATE `swabproducts` 
	    		SET 
	    		`BatchNumber` = '" . $BatchNumber . "',
		    	`Plant` = '" . $Plant . "',
		    	`SampleDate` = '" . $SampleDate . "',
		    	`DrawnBy` = '" . $DrawnBy . "',
		    	`GatePassNo` = '" . $GatePassNo . "',
		    	`AnalysisDate` = '" . $AnalysisDate . "',
		    	`ReportDate` = '" . $ReportDate . "',
		    	`Variety` = '" . $Variety . "',
		    	`sample_collection` = '" . $sample_collection . "',	    	

		    	`Parameter` = '" . $Parameter . "',
		    	`NameMediaUsed` = '" . $NameMediaUsed . "',
		    	`EquipmentUsed` = '" . $EquipmentUsed . "',
				`type_of_method` = '".$type_of_method."',
		    	`TpcResult` = '" . $TpcResult . "',
		    	`tp_completed_date` = '" . $tp_completed_date . "',

		    	`ec_parameter` = '" . $ec_parameter . "',
		    	`ec_nameMediaUsed` = '" . $ec_nameMediaUsed . "',
		    	`ec_equipmentUsed` = '" . $ec_equipmentUsed . "',    	
		    	`EcoliResult` = '" . $EcoliResult . "',
		    	`ec_completed_date` = '" . $ec_completed_date . "',

		    	`sa_parameter` = '" . $sa_parameter . "',
		    	`sa_nameMediaUsed` = '" . $sa_nameMediaUsed . "',
		    	`sa_equipmentUsed` = '" . $sa_equipmentUsed . "',    	
		    	`SaureusResult` = '" . $SaureusResult . "',
		    	`sa_completed_date` = '" . $sa_completed_date . "',

		    	`vc_parameter` = '" . $vc_parameter . "',
		    	`vc_nameMediaUsed` = '" . $vc_nameMediaUsed . "',
		    	`vc_equipmentUsed` = '" . $vc_equipmentUsed . "',    	
		    	`VcholeraeResult` = '" . $VcholeraeResult . "',
		    	`vc_completed_date` = '" . $vc_completed_date . "',

		    	`vp_parameter` = '" . $vp_parameter . "',
		    	`vp_nameMediaUsed` = '" . $vp_nameMediaUsed . "',
		    	`vp_equipmentUsed` = '" . $vp_equipmentUsed . "',    	
		    	`VparahaemolyticusResult` = '" . $VparahaemolyticusResult . "',
		    	`vp_completed_date` = '" . $vp_completed_date . "',

		    	`sal_parameter` = '" . $sal_parameter . "',
		    	`sal_nameMediaUsed` = '" . $sal_nameMediaUsed . "',
		    	`sal_equipmentUsed` = '" . $sal_equipmentUsed . "',	    	
		    	`Salmonella` = '" . $Salmonella . "',
		    	`sal_completed_date` = '" . $sal_completed_date . "',

		    	`analysed_by` = '" . $analysed_by . "',
		    	`CheckedBy` = '" . $CheckedBy . "',
		    	`VerfiedBy` = '" . $VerfiedBy . "',
		    	`Remarks` = '" . $Remarks . "' where id = '" . $id . "' ";
		}
		//echo $sqlInsert;  exit;
	} else {
		$dynamicEntryTypes = [
			'tpcDynamicEntries',
			'ecDynamicEntries',
			'saDynamicEntries',
			'vcDynamicEntries',
			'vpDynamicEntries',
			'salDynamicEntries'
		];
		$checkBal =  checkBalLimit($iconn,$dynamicEntryTypes,$_POST);
		if(!empty($checkBal['BalMessage'])){
			echo json_encode($checkBal);exit;
		}
		//echo '<pre>';print_r($checkBal);die;
		

		$sqlInsert = "INSERT INTO `swabproducts`(
	    	`BatchNumber`,
	    	`Plant`,
	    	`SampleDate`,
	    	`DrawnBy`,
	    	`GatePassNo`,
	    	`AnalysisDate`,
	    	`ReportDate`,
	    	`Variety`,
	    	`sample_collection`,

	    	`Parameter`,
	    	`NameMediaUsed`,
	    	`Kitlotno`,
	    	`EquipmentUsed`,
			`type_of_method`,

	    	`TpcResult`,
	    	`tp_completed_date`,

	    	`ec_parameter`,
	    	`ec_nameMediaUsed`,
	    	`ec_Kitlotno`,
	    	`ec_equipmentUsed`,    	
	    	`EcoliResult`,
	    	`ec_completed_date`,

	    	`sa_parameter`,
	    	`sa_nameMediaUsed`,
	    	`sa_Kitlotno`,
	    	`sa_equipmentUsed`,    	
	    	`SaureusResult`,
	    	`sa_completed_date`,

	    	`vc_parameter`,
	    	`vc_nameMediaUsed`,
	    	`vc_Kitlotno`,
	    	`vc_equipmentUsed`,    	
	    	`VcholeraeResult`,
	    	`vc_completed_date`,

	    	`vp_parameter`,
	    	`vp_nameMediaUsed`,
	    	`vp_Kitlotno`,
	    	`vp_equipmentUsed`,    	
	    	`VparahaemolyticusResult`,
	    	`vp_completed_date`,

	    	`sal_parameter`,
	    	`sal_nameMediaUsed`,
	    	`sal_Kitlotno`,
	    	`sal_equipmentUsed`,    	
	    	`Salmonella`,
	    	`sal_completed_date`,

	    	`analysed_by`,
	    	`CheckedBy`,
	    	`VerfiedBy`,
	    	`Remarks`) VALUES ('" . $BatchNumber . "',
	    	'" . $Plant . "',
	    	'" . $SampleDate . "',
	    	'" . $DrawnBy . "',
	    	'" . $GatePassNo . "',
	    	'" . $AnalysisDate . "',
	    	'" . $ReportDate . "',
	    	'" . $Variety . "',
	    	'" . $sample_collection . "',

	    	'" . $Parameter . "',
	    	'" . $NameMediaUsed . "',
	    	'" . $Kitlotno . "',
	    	'" . $EquipmentUsed . "',
			'" . $type_of_method . "',
	    	'" . $TpcResult . "',
	    	'" . $tp_completed_date . "',

	    	'" . $ec_parameter . "',
	    	'" . $ec_nameMediaUsed . "',
	    	'" . $ec_Kitlotno . "',
	    	'" . $ec_equipmentUsed . "',
	    	'" . $EcoliResult . "',
	    	'" . $ec_completed_date . "',

	    	'" . $sa_parameter . "',
	    	'" . $sa_nameMediaUsed . "',
	    	'" . $sa_Kitlotno . "',
	    	'" . $sa_equipmentUsed . "',
	    	'" . $SaureusResult . "',
	    	'" . $sa_completed_date . "',

	    	'" . $vc_parameter . "',
	    	'" . $vc_nameMediaUsed . "',
	    	'" . $vc_Kitlotno . "',
	    	'" . $vc_equipmentUsed . "',    	
	    	'" . $VcholeraeResult . "',
	    	'" . $vc_completed_date . "',

	    	'" . $vp_parameter . "',
	    	'" . $vp_nameMediaUsed . "',
	    	'" . $vp_Kitlotno . "',
	    	'" . $vp_equipmentUsed . "',    	
	    	'" . $VparahaemolyticusResult . "',
	    	'" . $vp_completed_date . "',

	    	'" . $sal_parameter . "',
	    	'" . $sal_nameMediaUsed . "',
	    	'" . $sal_Kitlotno . "',
	    	'" . $sal_equipmentUsed . "',    	
	    	'" . $Salmonella . "',
	    	'" . $sal_completed_date . "',

	    	'" . $analysed_by . "',
	    	'" . $CheckedBy . "',
	    	'" . $VerfiedBy . "',
	    	'" . $Remarks . "'
	    )";

		//$stmt1 = $iconn->query($sqlInsert);

		if (!$iconn->query($sqlInsert)) {
			$iconn->rollback();
			die(json_encode([
				"Error" => true,
				"Message" => $iconn->error
			]));
		} else {

			// Get the last inserted ID directly from MySQL
			$LastInsertedID = $iconn->insert_id;
			$IDSource = 'insert_id';

			// Fallback if insert_id returns 0 (e.g., triggers, special table settings)
			if (empty($LastInsertedID) || $LastInsertedID == 0) {
				$res = $iconn->query("SELECT id FROM swabproducts ORDER BY id DESC LIMIT 1");
				if ($res && $row = $res->fetch_assoc()) {
					$LastInsertedID = $row['id'];
					$IDSource = 'fallback';
				}
			}

			error_log("Final LastInsertedID: " . $LastInsertedID);
			legacyAuditLogInsertById('swabproducts', 'id', $LastInsertedID, null, $_POST, [
				'ReferenceNo' => $LastInsertedID,
				'Remarks' => 'Swab product inserted',
			], 'QualityControl');
			//$stmt1->close();

			// --- Insert dynamic entries ---
			$dynamicEntryTypes = [
				'tpcDynamicEntries',
				'ecDynamicEntries',
				'saDynamicEntries',
				'vcDynamicEntries',
				'vpDynamicEntries',
				'salDynamicEntries'
			];
			$entryNo = 0;
			foreach ($dynamicEntryTypes as $entryType) {
				if (!empty($_POST[$entryType])) {
					$entriesArray = json_decode($_POST[$entryType], true);
					if (is_array($entriesArray)) {
						foreach ($entriesArray as $entry) {
							//echo '<pre>';print_r($entriesArray);die;
							$NameMediaUsed = isset($entry['NameMediaUsed']) ? $iconn->real_escape_string($entry['NameMediaUsed']) : '';
							$Parameter = 0; // adjust if needed
							$Kitlotno = isset($entry['Kitlotno']) ? (is_array($entry['Kitlotno']) ? implode(',', $entry['Kitlotno']) : $entry['Kitlotno']) : '';
							$EquipmentUsed = isset($entry['EquipmentUsed']) ? (is_array($entry['EquipmentUsed']) ? implode(',', $entry['EquipmentUsed']) : $entry['EquipmentUsed']) : '';
							$Result = isset($entry['Result']) ? $iconn->real_escape_string($entry['Result']) : '';
							$CompletedDate = isset($entry['CompletedDate']) ? $iconn->real_escape_string($entry['CompletedDate']) : date('Y-m-d');

							$sql = "
                    INSERT INTO MicroParamList
                    (swabproduct_id, Parameter, NameMediaUsed, Kitlotno, EquipmentUsed, Result, Completed_Date, createdDate)
                    VALUES (
                        '" . intval($LastInsertedID) . "',
                        '" . intval($Parameter) . "',
                        '" . $NameMediaUsed . "',
                        '" . $Kitlotno . "',
                        '" . $EquipmentUsed . "',
                        '" . $Result . "',
                        '" . $CompletedDate . "',
                        NOW()
                    )
                ";

							if (!$iconn->query($sql)) {
								die("Insert Error: " . $iconn->error);
							}else{
								legacyAuditLogInsertById('MicroParamList', 'ID', $iconn->insert_id, null, $_POST, [
									'ReferenceNo' => $LastInsertedID,
									'Remarks' => 'Swab product dynamic parameter inserted',
								], 'QualityControl');

								if(!empty($Kitlotno)){
									$kitIds = explode(',',$Kitlotno);									
									$BalWells = isset($entry['UsedWells']) ? $entry['UsedWells'] : 0;									
									$deduct = $BalWells;//65,66,67-30,100,100>100
									//deduct=70
									for($i=0;$i<count($kitIds);$i++){//66,65-50
										//deduct=50
										if ($deduct <= 0) break;
										$balSql = "SELECT mNoOfWells,mBalanceWells,mUtilizedWells FROM `mediakitsbreakupdetails` WHERE mKitBreakupID IN ( $kitIds[$i] )";
										$StmtBalSql = $iconn->query($balSql);
										$res = $StmtBalSql->fetch_assoc();	
										$mNoOfWells = !empty($res['mNoOfWells'])?$res['mNoOfWells']:0;//100
										$mBalanceWells = !empty($res['mBalanceWells'])?$res['mBalanceWells']:0;//0
										$mUtilizedWells = !empty($res['mUtilizedWells'])?$res['mUtilizedWells']:0;//80
										if($mBalanceWells > 0){
											if ($mBalanceWells >= $deduct) {//30>=100=30
												// this value can cover the deduction fully
												$BalWells = $mBalanceWells - $deduct;//30
												$utilizeWell = $mNoOfWells-$BalWells;//50
												$deduct = 0;
											} else {
												// consume entire value, still some left to deduct
												$BalWells = 0;
												$utilizeWell = $mNoOfWells;//80
												$deduct -= $mBalanceWells;//70
											}
											/////
											
											$UpdateSql = "UPDATE mediakitsbreakupdetails SET mBalanceWells='$BalWells',mUtilizedWells='$utilizeWell' WHERE mKitBreakupID=$kitIds[$i]";
											$iconn->query($UpdateSql);
										}										
									}
								}
							}
							$entryNo++;
						}
					}
				}
			}

			//$stmt->close();





			// $iconn->commit();

			echo json_encode([
				"Error" => false,
				"Message" => "Done",
				"LastInsertedID" => $LastInsertedID,
				"IDSource" => $IDSource
			]);
			exit;
		}
	}
} else if ($action == "update") {
	extract($_POST);
	extract($_GET);
	$SqlUpdate = "UPDATE `swabproducts` 
	    		SET 
	    		
		    	`Plant` = '" . $Plant . "',
		    	`SampleDate` = '" . $SampleDate . "',
		    	`DrawnBy` = '" . $DrawnBy . "',
				`type_of_method` = '" . $type_of_method . "',
		    	`GatePassNo` = '" . $GatePassNo . "',
		    	`AnalysisDate` = '" . $AnalysisDate . "',
		    	`ReportDate` = '" . $ReportDate . "',
		    	`Variety` = '" . $Variety . "',
		    	`sample_collection` = '" . $sample_collection . "',
		    	`analysed_by` = '" . $analysed_by . "',
		    	`CheckedBy` = '" . $CheckedBy . "',
		    	`VerfiedBy` = '" . $VerfiedBy . "',
		    	`Remarks` = '" . $Remarks . "' where id = '" . $id . "' 
	";
	$stmt1 = $iconn->query($SqlUpdate);
	if ($stmt1) {
		legacyAuditLogUpdateById('swabproducts', 'id', $id, null, $_POST, [
			'ReferenceNo' => $id,
			'Remarks' => 'Swab product updated (update action)',
		], 'QualityControl');
	}
	echo json_encode([
		"Error" => false,
		"Message" => "Done"
	]);
	exit;
} else if ($action == "Delete") {

	$id = isset($_POST['id']) ? $_POST['id'] : 0;
	//validate input
	if (!is_numeric($id)) {
		echo "Bad request";
		exit;
	} else if ($id < 0) {
		echo "Bad request";
		exit;
	} else {

		$sqlDelete = "DELETE from swabproducts where id='" . $id . "' limit 1 ";
		$smtDeleteQuery = $iconn->query($sqlDelete);
		if ($smtDeleteQuery) {
			legacyAuditLogDeleteById('swabproducts', 'id', $id, null, $_POST, [
				'ReferenceNo' => $id,
				'Remarks' => 'Swab product deleted',
			], 'QualityControl');
			$getUsedBreackUpData = " SELECT KitBreakUpID, sum(BreakUpwiseUsedWells) as used FROM `mediarmihmputilizedkitbreakupdetails` WHERE  swabproductsID = '$id' and fromPage='product' GROUP by KitBreakUpID ";
			//error_log($getUsedBreackUpData);
			$smtUsedBreackUpData = $iconn->query($getUsedBreackUpData);
			while ($rowUsedBreackUpData = $smtUsedBreackUpData->fetch_assoc()) {
				$updateBreackDetails = "UPDATE mediakitsbreakupdetails SET 
    mUtilizedWells = mUtilizedWells - " . $rowUsedBreackUpData['used'] . ", 
    mBalanceWells = mBalanceWells + " . $rowUsedBreackUpData['used'] . " 
    WHERE mKitBreakupID = '" . $rowUsedBreackUpData['KitBreakUpID'] . "'";
				//error_log($updateBreackDetails);
				$StmtUpd = $iconn->query($updateBreackDetails);
				if ($StmtUpd) {
					legacyAuditLogUpdateById('mediakitsbreakupdetails', 'mKitBreakupID', $rowUsedBreackUpData['KitBreakUpID'], null, $_POST, [
						'ReferenceNo' => $id,
						'Remarks' => 'Kit breakup wells restored on delete (product)',
					], 'QualityControl');
				}
			}
		}
		echo json_encode([
			"Error" => false,
			"Message" => "Done"
		]);
		exit;
	}
} else {
	echo "Bad request";
	exit;
}

function checkBalLimit($iconn,$dynamicEntryTypes = [], $postData = []){
	$Kitlotno = [];$UsedWellsArr = [];$BalWellsArr = [];$lotNo = [];$data = [];
	$entryNo = 0;
	foreach ($dynamicEntryTypes as $entryType) {		
		if (!empty($postData[$entryType])) {
			$entriesArray = json_decode($_POST[$entryType], true);
			if (is_array($entriesArray)) {
				foreach ($entriesArray as $entry) {
					$KitlotId = isset($entry['Kitlotno']) ? (!is_array($entry['Kitlotno']) ? explode(',', $entry['Kitlotno']) : $entry['Kitlotno']) : '';
					$UsedWells= isset($entry['UsedWells']) ? $entry['UsedWells'] : 0;//15					
					if(count($KitlotId)>1){
						for($i=0;$i<count($KitlotId);$i++){		
							if(isset($UsedWellsArr[$entryNo])){
								$UsedWells = $UsedWellsArr[$entryNo];//30
								if ($UsedWells <= 0) break;
							}	
							if(isset($BalWellsArr[$KitlotId[$i]])){
								$mBalanceWells = $BalWellsArr[$KitlotId[$i]];//60
								$mKitBreakupLotNo = $lotNo[$KitlotId[$i]];
							}else{									
								$SqlInstumentList = "SELECT mNoOfWells, mBalanceWells,mKitBreakupLotNo FROM `mediakitsbreakupdetails` where mKitBreakupID in ( " . $KitlotId[$i] . " )";
								$StmtInstumentList = $iconn->query($SqlInstumentList);
								$res = $StmtInstumentList->fetch_assoc();
								$lotNo[$KitlotId[$i]] = $mKitBreakupLotNo = $res['mKitBreakupLotNo'];
								$mBalanceWells = $res['mBalanceWells'];//65 = 60,66=100
							}					
							if($UsedWells >= $mBalanceWells){
								if($i == count($KitlotId)-1 && $UsedWells != $mBalanceWells){
									return $data[] = ['BalMessage' => "Balance exceeded for $mKitBreakupLotNo"];
									//echo 'exceeded for '.$mKitBreakupLotNo.'well used '.$UsedWells.' entryNo '.$entryNo;die;
								}
								$UsedWells -= $mBalanceWells;//15-5 = 10
								$BalWellsArr[$KitlotId[$i]] = 0;//65=0
								$UsedWellsArr[$entryNo] = $UsedWells;//30
							}else{
								if($mBalanceWells <= $UsedWells){
									$UsedWells -= $mBalanceWells;//20-15
									$UsedWellsArr[$entryNo] = $UsedWells;
								}else{
									$UsedWells = $mBalanceWells - $UsedWells;//100-20=80
									$UsedWellsArr[$entryNo] = 0;
								}
								if($UsedWells < 0){
									return $data[] = ['BalMessage' => "Balance exceeded for $mKitBreakupLotNo"];
								} 
								$BalWellsArr[$KitlotId[$i]] = $UsedWells;//BalWellsArr[66] = 80
							}
						}
					}else{
						//$UsedWells=50
						if(isset($UsedWellsArr[$entryNo])){
							$UsedWells = $UsedWellsArr[$entryNo];//20
							if ($UsedWells <= 0) break;
						}
						if(isset($BalWellsArr[$KitlotId[0]])){
							$mBalanceWells = $BalWellsArr[$KitlotId[0]];//BalWellsArr[66] = 30
							$mKitBreakupLotNo = $lotNo[$KitlotId[0]];
						}else{
							$SqlInstumentList = "SELECT mNoOfWells, mBalanceWells,mKitBreakupLotNo FROM `mediakitsbreakupdetails` where mKitBreakupID in ( " . $KitlotId[0] . " )";
							$StmtInstumentList = $iconn->query($SqlInstumentList);
							$res = $StmtInstumentList->fetch_assoc();
							$lotNo[$KitlotId[0]] = $mKitBreakupLotNo = $res['mKitBreakupLotNo'];							
							$mBalanceWells = $res['mBalanceWells'];
						}
						
						//UsedWells = 50,mBalanceWells = 30
						if($UsedWells > $mBalanceWells){
							return $data[] = ['BalMessage' => "Balance exceeded for $mKitBreakupLotNo"];
						}
						if($UsedWells == $mBalanceWells){
							$UsedWells -= $mBalanceWells;//15-5 = 10
							$BalWellsArr[$KitlotId[0]] = 0;//66=20
							$UsedWellsArr[$entryNo] = 0;
						}else{
							if($mBalanceWells <= $UsedWells){
								$UsedWells -= $mBalanceWells;//20-15
							}else{
								$UsedWells = $mBalanceWells - $UsedWells;//30
							}
							if($UsedWells < 0){
								return $data[] = ['BalMessage' => "Balance exceeded for $mKitBreakupLotNo"];
							} 
							$BalWellsArr[$KitlotId[0]] = $UsedWells;
							$UsedWellsArr[$entryNo] = 0;
						}
					}
					//echo '<pre>';print_r($lotNo);die;
					$entryNo++;
				}
			}
		}
	}
	return $data;
}
function getBalLimit($iconn,$dynamicEntryTypes = [], $postData = [],$rowInstumentList = []){
	$entryNo = 0;$BalWellsArr = [];$UsedWellsArr = [];$openingArr = [];
	foreach ($dynamicEntryTypes as $entryType) {		
		if (!empty($postData[$entryType])) {
			$entriesArray = json_decode($_POST[$entryType], true);
			if (is_array($entriesArray)) {
				foreach ($entriesArray as $entry) {
					$KitlotId = isset($entry['Kitlotno']) ? (!is_array($entry['Kitlotno']) ? explode(',', $entry['Kitlotno']) : $entry['Kitlotno']) : '';
					$UsedWells = isset($entry['UsedWells']) ? $entry['UsedWells'] : 0;//5
					$BalanceWells= isset($entry['BalanceWells']) ? $entry['BalanceWells'] : 0;
					/* if($BalanceWells == 0){
						return $BalanceWells;
					} */
					//if(count($KitlotId)>1){
						for($i=0;$i<count($KitlotId);$i++){
							if(isset($UsedWellsArr[$entryNo])){
								//echo '<pre>';print_r($UsedWellsArr);die;
								$UsedWells = $UsedWellsArr[$entryNo];//30								
								if ($UsedWells <= 0) break;
							}
							if($KitlotId[$i] && $UsedWells){							
								if(isset($BalWellsArr[$KitlotId[$i]])){
									$mBalanceWells = $BalWellsArr[$KitlotId[$i]];//60
								}else{	
									$SqlInstumentList = "SELECT mNoOfWells, mBalanceWells FROM `mediakitsbreakupdetails` where mKitBreakupID in ( " . $KitlotId[$i] . " )";
									$StmtInstumentList = $iconn->query($SqlInstumentList);
									$res = $StmtInstumentList->fetch_assoc();
									$mBalanceWells = $res['mBalanceWells'];//20
								}
								
								if($UsedWells >= $mBalanceWells){//80>=20
									$UsedWells -= $mBalanceWells;
									$BalWellsArr[$KitlotId[$i]] = 0;//66=0
									$openingArr[$KitlotId[$i]] = 0;
									$UsedWellsArr[$entryNo] = $UsedWells;//30
								}else{
									if($mBalanceWells <= $UsedWells){
										$UsedWells -= $mBalanceWells;//20-15
										$UsedWellsArr[$entryNo] = $UsedWells;
									}else{								
										$UsedWells = $mBalanceWells - $UsedWells;//80-5=75
										$UsedWellsArr[$entryNo] = 0;
									}
									$BalWellsArr[$KitlotId[$i]] = $UsedWells;//[67]=75
									$openingArr[$KitlotId[$i]] = $UsedWells;
									//$BalWellsArr[$KitlotId[$i]] += $entry['UsedWells'];
								}							
								//$UsedWellsArr[$entryNo] = $UsedWells;
								//$UsedWellsArr[$KitlotId[$i]] = $UsedWells;
							}
						}
					/* }else{

					} */
					$entryNo++;
				}
			}
		}
	}
	return $BalWellsArr;
	return array_sum($BalWellsArr);
}
