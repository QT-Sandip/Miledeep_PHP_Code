<?php
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";

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
	//echo "LastInsertedID: ".$LastInsertedID." KitBreakUpLotIDs: ".$KitBreakUpLotIDs." EnteredDamagedWells: ".$EnteredDamagedWells." EnteredUsedWells: ".$EnteredUsedWells." KitOpeningDate: ".$KitOpeningDate." StandardUsedWells: ".$StandardUsedWells;
	if ($KitBreakUpLotIDs) {
		$SqlGetKitID = " SELECT 
    		mKitID,
    		mNoOfWells,
    		mKitBreakupID,
    		mUtilizedWells,
    		mBalanceWells,    		
    		mKitOpeningDate 
			FROM `mediakitsbreakupdetails`
			WHERE 
				mKitBreakupID in($KitBreakUpLotIDs) 
			Order by mBalanceWells asc
		";
		$StmtGetKitBreakUpID = $iconn->query($SqlGetKitID);
		//echo $SqlGetKitID; exit;
		$lastusedwells = 0;
		$originalUsedwells = $EnteredUsedWells;
		while ($RowgetKitBreakUpID = $StmtGetKitBreakUpID->fetch_assoc()) {

			$mUtilizedWells = $RowgetKitBreakUpID['mUtilizedWells'];
			$KitID = $RowgetKitBreakUpID['mKitID'];
			$KitLotBreakupID = $RowgetKitBreakUpID['mKitBreakupID'];
			$BalanceWells = $RowgetKitBreakUpID['mBalanceWells'];
			$NoOfWells = $RowgetKitBreakUpID['mNoOfWells'];
			$OldKitOpeningDate = $RowgetKitBreakUpID['mKitOpeningDate'];
			$OpeningDate = empty($OldKitOpeningDate) ? $KitOpeningDate : $OldKitOpeningDate;

			// fet utilised breakup detali where prod id


			$EnteredUsedWells = $originalUsedwells - $lastusedwells;


			if ($BalanceWells <= $EnteredUsedWells) {
				$EnteredUsedWells = $BalanceWells;
			}
			$RemainingWells = $BalanceWells - $EnteredUsedWells;

			$SqlInsertBreakUp = "
            	INSERT INTO `mediarmihmputilizedkitbreakupdetails`
            	(
            		`swabproductsID`, 
            		`KitID`, 
            		`KitBreakUpID`,
			 		`BreakUpwiseUsedWells`,
			 		fromPage
			 	) VALUES (
			 		'$LastInsertedID',
			 		'$KitID',
			 		'$KitLotBreakupID',
			 		'$EnteredUsedWells',
			 		'iceandwater'			 		
			 	)
			";
			$StmtInsertBreakUp = $iconn->query($SqlInsertBreakUp);
			//echo "<div>$SqlInsertBreakUp</div>";
			if ($StmtInsertBreakUp) {
				legacyAuditLogInsertById('mediarmihmputilizedkitbreakupdetails', 'ID', $iconn->insert_id, null, $_POST, [
					'ReferenceNo' => $LastInsertedID,
					'Remarks' => 'Utilized kit breakup inserted (iceandwater)',
				], 'QualityControl');
				$totalmUtilizedWell = $EnteredUsedWells + $mUtilizedWells;
				$SqlUpdatekitBreakupDetails = "
            			UPDATE `mediakitsbreakupdetails` 
            				SET 
            					`mKitOpeningDate`='$OpeningDate',
            					`mUtilizedWells`='$totalmUtilizedWell',
            					`mBalanceWells`='$RemainingWells'
            				WHERE
            					`mKitBreakupID`='$KitLotBreakupID'
            		";
				//echo "<div>$SqlUpdatekitBreakupDetails</div>";
				//error_log("upk".$SqlUpdatekitBreakupDetails);
				$StmtUpdatekitBreakupDetails = $iconn->query($SqlUpdatekitBreakupDetails);
				if ($StmtUpdatekitBreakupDetails) {
					legacyAuditLogUpdateById('mediakitsbreakupdetails', 'mKitBreakupID', $KitLotBreakupID, null, $_POST, [
						'ReferenceNo' => $LastInsertedID,
						'Remarks' => 'Kit breakup wells updated (iceandwater)',
					], 'QualityControl');
				}
				$lastusedwells = $EnteredUsedWells;
			} else {
			}
		}
	}
}

if ($action == "insert") {
	extract($_POST);
	extract($_GET);

	if (isset($id) && $id > 0) {
		$sqlInsert = "
    		UPDATE `micro_biology_water_ice` SET
    		
	    	`sample_drawn_by` = '" . $sample_drawn_by . "',
	    	`sample_collection` = '" . $sample_collection . "',
			`AnalyzedDate` = '" . $AnalyzedDate . "',
	    	`plant_id` = '" . $plant_id . "',
	    	`sample_desc` = '" . $sample_desc . "',
	    	`location` = '" . $location . "',
	    	`tap_no_for_water` = '" . $tap_no_for_water . "',
	    	`itype_of_ice` = '" . $itype_of_ice . "',
				
			    	

	    	`analysed_by` = '" . $analysed_by . "',
	    	`checked_by` = '" . $checked_by . "',
	    	`verified_by` = '" . $verified_by . "',
			`instant_swab` = '" . $instant_swab . "',
	    	`remarks` = '" . $remarks . "' where mbwi_id = '" . $id . "' ";
		$stmt1 = $iconn->query($sqlInsert);
		if ($stmt1) {
			legacyAuditLogUpdateById('micro_biology_water_ice', 'mbwi_id', $id, null, $_POST, [
				'ReferenceNo' => $id,
				'Remarks' => 'Water ice updated',
			], 'QualityControl');
		}
		//echo $sqlInsert; exit;
	} else {

		$sqlInsert = "INSERT INTO `micro_biology_water_ice`(
	    	 `report_type`,
	    	 `sample_drawn_by`,
	    	 `sample_collection`,
			  `AnalyzedDate`,
	    	 `plant_id`,
	    	 `sample_desc`,
	    	 `location`,
	    	 `tap_no_for_water`,
	    	 `itype_of_ice`,
			 
			 

	    	 `tpc_paramater`,
	    	 `tpc_media`,
	    	 tpc_Kitlotno,
	    	 tpc_UsedWells,
	    	 `tpc_equipement`,
	    	 `tpc_result1`,
	    	 `tpc_result2`,
	    	 `tpc_completion_date`,

	    	 `vcholerae_paramater`,
	    	 `vcholerae_media`,
	    	 vcholerae_Kitlotno,
	    	 vcholerae_UsedWells,
	    	 `vcholerae_equipement`,
	    	 `vcholerae_result1`,
	    	 `vcholerae_result2`,
	    	 `vcholerae_completion_date`,

	    	 `cholerae_paramater`,
	    	 `cholerae_media`,
	    	 cholerae_Kitlotno,
	    	 cholerae_UsedWells,
	    	 `cholerae_equipement`,
	    	 `cholerae_result1`,	    	 
	    	 `cholerae_completion_date`,

	    	 `analysed_by`,
	    	 `checked_by`,
	    	 `verified_by`,
			 `instant_swab`,
	    	 `remarks`
	    	 ) VALUES (
	    	 '" . $report_type . "',
	    	 '" . $sample_drawn_by . "',
	    	 '" . $sample_collection . "',
			 '" . $AnalyzedDate . "',
	    	 '" . $plant_id . "',
	    	 '" . $sample_desc . "',
	    	 '" . $location . "',
	    	 '" . $tap_no_for_water . "',
	    	 '" . $itype_of_ice . "',
			
			 

	    	 '" . $tpc_paramater . "',
	    	 '" . $tpc_media . "',
	    	 '" . $tpc_Kitlotno . "',
	    	 '" . $tpc_UsedWells . "',
	    	 '" . $tpc_equipement . "',
	    	 '" . $tpc_result1 . "',
	    	 '" . $tpc_result2 . "',
	    	 '" . $tpc_completion_date . "',

	    	 '" . $vcholerae_paramater . "',
	    	 '" . $vcholerae_media . "',
	    	 '" . $vcholerae_Kitlotno . "',
	    	 '" . $vcholerae_UsedWells . "',
	    	 '" . $vcholerae_equipement . "',
	    	 '" . $vcholerae_result1 . "',
	    	 '" . $vcholerae_result2 . "',
	    	 '" . $vcholerae_completion_date . "',

	    	 '" . $cholerae_paramater . "',
	    	 '" . $cholerae_media . "',
	    	 '" . $cholerae_Kitlotno . "',
	    	 '" . $cholerae_UsedWells . "',
	    	 '" . $cholerae_equipement . "',
	    	 '" . $cholerae_result1 . "',	    	 
	    	 '" . $cholerae_completion_date . "',

	    	 '" . $analysed_by . "',
	    	 '" . $checked_by . "',
	    	 '" . $verified_by . "',
			 '" . $instant_swab . "',
	    	 '" . $remarks . "'
	    )";

		$stmt1 = $iconn->query($sqlInsert);

		if ($stmt1) {
			$LastInsertedID = $iconn->insert_id;
			legacyAuditLogInsertById('micro_biology_water_ice', 'mbwi_id', $LastInsertedID, null, $_POST, [
				'ReferenceNo' => $LastInsertedID,
				'Remarks' => 'Water ice inserted',
			], 'QualityControl');

			// Update kit info for each main parameter
			if ($tpc_Kitlotno) {
				updateKitsInfo($iconn, $LastInsertedID, $tpc_Kitlotno, 0, $tpc_UsedWells, $tpc_KitOpeningDate, 0);
			}
			if ($vcholerae_Kitlotno) {
				updateKitsInfo($iconn, $LastInsertedID, $vcholerae_Kitlotno, 0, $vcholerae_UsedWells, $vcholerae_KitOpeningDate, 0);
			}
			if ($cholerae_Kitlotno) {
				updateKitsInfo($iconn, $LastInsertedID, $cholerae_Kitlotno, 0, $cholerae_UsedWells, $cholerae_KitOpeningDate, 0);
			}

			// Handle dynamic entries safely
			$dynamicEntryTypes = [
				'tpcDynamicEntries' => 'tpc',
				'vcholeraeDynamicEntries' => 'vcholerae',
				'choleraeDynamicEntries' => 'cholerae'
			];

			foreach ($dynamicEntryTypes as $entryType => $paramType) {
				if (!empty($_POST[$entryType])) {
					$entriesArray = json_decode($_POST[$entryType], true);
					if ($entriesArray === null) {
						die(json_encode(["Error" => true, "Message" => "Invalid JSON for $entryType"]));
					}

					foreach ($entriesArray as $entry) {
						if (empty($entry['NameMediaUsed']) && empty($entry['Kitlotno']))
							continue;

						$NameMediaUsed = isset($entry['NameMediaUsed']) ? $iconn->real_escape_string($entry['NameMediaUsed']) : '';
						$Kitlotno = isset($entry['Kitlotno']) ? $iconn->real_escape_string($entry['Kitlotno']) : '';
						$EquipmentUsed = isset($entry['Equipement']) ? $iconn->real_escape_string($entry['Equipement']) : '';
						$Result = isset($entry['Result1']) ? $iconn->real_escape_string($entry['Result1']) : '';
						$Result1 = isset($entry['Result2']) ? $iconn->real_escape_string($entry['Result2']) : '';
						$CompletedDate = !empty($entry['CompletionDate']) ? $iconn->real_escape_string($entry['CompletionDate']) : date('Y-m-d');




						$sqlDynamic = "
						INSERT INTO MicroParamListWaterIce
						(micro_water_ice_id, Parameter, NameMediaUsed, Kitlotno, EquipmentUsed, Result, Result1, Completed_Date, createdDate)
						VALUES (
							" . intval($LastInsertedID) . ",
							'$paramType',
							'$NameMediaUsed',
							'$Kitlotno',
							'$EquipmentUsed',
							'$Result',
							'$Result1',
							'$CompletedDate',
							NOW()
						)";

						if ($iconn->query($sqlDynamic)) {
							legacyAuditLogInsertById('MicroParamListWaterIce', 'ID', $iconn->insert_id, null, $_POST, [
								'ReferenceNo' => $LastInsertedID,
								'Remarks' => 'Water ice dynamic parameter inserted',
							], 'QualityControl');
						} else {
							$iconn->rollback();
							die(json_encode([
								"Error" => true,
								"Message" => $iconn->error
							]));
						}
					}
				}
			}


			echo json_encode([
				"Error" => false,
				"Message" => "Record inserted successfully",
				"LastInsertedID" => $LastInsertedID
			]);
			exit;
		} else {
			echo json_encode([
				"Error" => true,
				"Message" => $iconn->error,
			]);
			exit;
		}
	}
}


//echo $sqlInsert; exit;
// echo json_encode([
// "Error" => false,
// "Message" => "Done",
// ]);
// exit;
else if ($action == "Delete") {

	$id = isset($_POST['id']) ? $_POST['id'] : 0;
	//validate input
	if (!is_numeric($id)) {
		echo "Bad request";
		exit;
	} else if ($id < 0) {
		echo "Bad request";
		exit;
	}

	$sqlDelete = "DELETE from micro_biology_water_ice where mbwi_id='" . $id . "' limit 1 ";
	$smtDeleteQuery = $iconn->query($sqlDelete);
	if ($smtDeleteQuery) {
		legacyAuditLogDeleteById('micro_biology_water_ice', 'mbwi_id', $id, null, $_POST, [
			'ReferenceNo' => $id,
			'Remarks' => 'Water ice deleted',
		], 'QualityControl');
		$getUsedBreackUpData = " SELECT KitBreakUpID, sum(BreakUpwiseUsedWells) as used FROM `mediarmihmputilizedkitbreakupdetails` WHERE  swabproductsID = '$id' and fromPage='iceandwater' GROUP by KitBreakUpID ";
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
					'Remarks' => 'Kit breakup wells restored on delete (iceandwater)',
				], 'QualityControl');
			}
		}
	}
	echo json_encode([
		"Error" => false,
		"Message" => "Done",
	]);
	exit;
} else {
	echo "Bad request";
	exit;
}
