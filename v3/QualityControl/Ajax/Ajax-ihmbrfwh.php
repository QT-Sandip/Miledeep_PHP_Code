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
	$StandardUsedWells )
{
	//echo "LastInsertedID: ".$LastInsertedID." KitBreakUpLotIDs: ".$KitBreakUpLotIDs." EnteredDamagedWells: ".$EnteredDamagedWells." EnteredUsedWells: ".$EnteredUsedWells." KitOpeningDate: ".$KitOpeningDate." StandardUsedWells: ".$StandardUsedWells;
    if($KitBreakUpLotIDs){
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
	        

	        if( $BalanceWells <= $EnteredUsedWells){
	        	$EnteredUsedWells = $BalanceWells;
	        }      
	        $RemainingWells = $BalanceWells -$EnteredUsedWells;

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
			 		'workhand'			 		
			 	)
			";
        	$StmtInsertBreakUp = $iconn->query($SqlInsertBreakUp);
        	//echo "<div>$SqlInsertBreakUp</div>";
        	if ($StmtInsertBreakUp) {
				legacyAuditLogInsertById('mediarmihmputilizedkitbreakupdetails', 'ID', $iconn->insert_id, null, $_POST, [
					'ReferenceNo' => $LastInsertedID,
					'Remarks' => 'Utilized kit breakup inserted (workhand)',
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
						'Remarks' => 'Kit breakup wells updated (workhand)',
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
    		UPDATE `micro_biology_workers_hand` SET    		
	    	`sample_drawn_by` = '" . $sample_drawn_by . "',
	    	`sample_collection_area` = '" . $sample_collection_area . "',
	    	`name_food_worker_hand` = '" . $name_food_worker_hand . "',
	    	`emp_id` = '" . $emp_id . "',
	    	
			`SampleDate` = '" . $SampleDate . "',
	    	`AnalysisDate` = '" . $AnalysisDate . "',
	    	`checked_by` = '" . $checked_by . "',
	    	`verified_by` = '" . $verified_by . "',
	    	`remarks` = '" . $remarks . "' where mbwh_id = '" . $id . "' ";
 	    	$stmt1 = $iconn->query($sqlInsert);
	    	if ($stmt1) {
			legacyAuditLogUpdateById('micro_biology_workers_hand', 'mbwh_id', $id, null, $_POST, [
				'ReferenceNo' => $id,
				'Remarks' => 'Workers hand updated',
			], 'QualityControl');
		}
        //echo $sqlInsert;  exit;
    } else {

		 $sqlInsert = "INSERT INTO `micro_biology_workers_hand`(
     sample_drawn_by,
     sample_collection_area,
     SampleDate,
     AnalysisDate,
     name_food_worker_hand,
     emp_id,
     tpc_paramater,
     tpc_media,
     tpc_Kitlotno,
     tpc_UsedWells,
     tpc_equipement,
     tpc_result,
     tpc_completion_date,
     vcholerae_paramater,
     vcholerae_media,
     vcholerae_Kitlotno,
     vcholerae_UsedWells,
     vcholerae_equipement,
     vcholerae_result,
     vcholerae_completion_date,
     analysed_by,
     checked_by,
     verified_by,
     remarks
    
) VALUES (
     '$sample_drawn_by',
     '$sample_collection_area',
     '$SampleDate',
     '$AnalysisDate',
     '$name_food_worker_hand',
     '$emp_id',
     '$tpc_paramater',
     '$tpc_media',
     '$tpc_Kitlotno',
     '$tpc_UsedWells',
     '$tpc_equipement',
     '$tpc_result',
     '$tpc_completion_date',
     '$vcholerae_paramater',
     '$vcholerae_media',
     '$vcholerae_Kitlotno',
     '$vcholerae_UsedWells',
     '$vcholerae_equipement',
     '$vcholerae_result',
     '$vcholerae_completion_date',
     '$analysed_by',
     '$checked_by',
     '$verified_by',
     '$remarks'
)";

	    $stmt1 = $iconn->query($sqlInsert);
	    if( $stmt1 ){
	    	$LastInsertedID=$iconn->insert_id;
	    	legacyAuditLogInsertById('micro_biology_workers_hand', 'mbwh_id', $LastInsertedID, null, $_POST, [
			'ReferenceNo' => $LastInsertedID,
			'Remarks' => 'Workers hand inserted',
		], 'QualityControl');
	    	//echo "test ".$LastInsertedID; 
	    	if($tpc_Kitlotno){
	    		updateKitsInfo( $iconn, $LastInsertedID, $tpc_Kitlotno, 0, $tpc_UsedWells, $tpc_KitOpeningDate, 0 );
	    	}
	    	//exit;
	    	if($vcholerae_Kitlotno){
	    		updateKitsInfo( $iconn, $LastInsertedID, $vcholerae_Kitlotno, 0, $vcholerae_UsedWells, $vcholerae_KitOpeningDate, 0 );
	    	}



		// --- Insert dynamic entries ---
$dynamicEntryTypes = [
    'tpcDynamicEntries' => 'tpc',
    'vcholeraeDynamicEntries' => 'vcholerae'
];

foreach ($dynamicEntryTypes as $entryType => $paramType) {
    if (!empty($_POST[$entryType])) {
        $entriesArray = json_decode($_POST[$entryType], true);
        if ($entriesArray !== null) {
            foreach ($entriesArray as $entry) {
                // Check exact JSON keys here
                $NameMediaUsed = isset($entry['NameMediaUsed']) ? $iconn->real_escape_string($entry['NameMediaUsed']) : '';
                $Kitlotno = isset($entry['Kitlotno']) ? $iconn->real_escape_string(is_array($entry['Kitlotno']) ? implode(',', $entry['Kitlotno']) : $entry['Kitlotno']) : '';
                $EquipmentUsed = isset($entry['Equipement']) ? $iconn->real_escape_string(is_array($entry['Equipement']) ? implode(',', $entry['Equipement']) : $entry['Equipement']) : '';
                $Result = isset($entry['Result']) ? $iconn->real_escape_string($entry['Result']) : '';

                $CompletedDate = !empty($entry['CompletionDate']) ? $iconn->real_escape_string($entry['CompletionDate']) : date('Y-m-d');

                if(empty($NameMediaUsed) && empty($Kitlotno)) continue;

                $sqlDynamic = "
                    INSERT INTO `MicroParamListWorkerHand`
                    (worker_hand_id, Parameter, NameMediaUsed, Kitlotno, EquipmentUsed, Result, Completed_Date, createdDate)
                    VALUES (
                        $LastInsertedID, '$paramType', '$NameMediaUsed', '$Kitlotno', '$EquipmentUsed', '$Result', '$CompletedDate', NOW()
                    )
                ";

                if($iconn->query($sqlDynamic)){
                    legacyAuditLogInsertById('MicroParamListWorkerHand', 'ID', $iconn->insert_id, null, $_POST, [
                        'ReferenceNo' => $LastInsertedID,
                        'Remarks' => 'Workers hand dynamic parameter inserted',
                    ], 'QualityControl');
                } else {
                    die(json_encode([
                        "Error" => true,
                        "Message" => "Dynamic insert failed: " . $iconn->error
                    ]));
                }
            }
        } else {
            die(json_encode([
                "Error" => true,
                "Message" => "Invalid JSON for $entryType"
            ]));
        }
    }
}
		

	    	echo json_encode([
		        "Error"   => false,
		        "Message" => "Done",
		        "LastInsertedID" => $LastInsertedID
		    ]);
		    exit;
	    } else {
	    	echo json_encode([
		        "Error"   => true,
		        "Message" => $iconn->error,
		    ]);
		    exit;
	    }
	}
			

    //echo $sqlInsert; exit;
    // echo json_encode([
    //     "Error"   => false,
    //     "Message" => "Done",
    // ]);
    // exit;
}else if ($action == "Delete") {

    $id = isset($_POST['id']) ? $_POST['id'] : 0;
    //validate input
    if (!is_numeric($id)) {
        echo "Bad request";exit;
    } else if ($id < 0) {
        echo "Bad request";exit;
    }

    $sqlDelete      = "DELETE from micro_biology_workers_hand where mbwh_id='" . $id . "' limit 1 ";
    $smtDeleteQuery = $iconn->query($sqlDelete);
    if($smtDeleteQuery){
    	legacyAuditLogDeleteById('micro_biology_workers_hand', 'mbwh_id', $id, null, $_POST, [
			'ReferenceNo' => $id,
			'Remarks' => 'Workers hand deleted',
		], 'QualityControl');
    	$getUsedBreackUpData = " SELECT KitBreakUpID, sum(BreakUpwiseUsedWells) as used FROM `mediarmihmputilizedkitbreakupdetails` WHERE  swabproductsID = '$id' and fromPage='workhand' GROUP by KitBreakUpID ";
    	//error_log($getUsedBreackUpData);
	    $smtUsedBreackUpData = $iconn->query($getUsedBreackUpData);
	    while($rowUsedBreackUpData = $smtUsedBreackUpData->fetch_assoc()){
	    	$updateBreackDetails = "UPDATE mediakitsbreakupdetails SET 
    mUtilizedWells = mUtilizedWells - " . $rowUsedBreackUpData['used'] . ", 
    mBalanceWells = mBalanceWells + " . $rowUsedBreackUpData['used'] . " 
    WHERE mKitBreakupID = '" . $rowUsedBreackUpData['KitBreakUpID'] . "'";
	    	//error_log($updateBreackDetails);
	    	$StmtUpd = $iconn->query($updateBreackDetails);
	    	if ($StmtUpd) {
			legacyAuditLogUpdateById('mediakitsbreakupdetails', 'mKitBreakupID', $rowUsedBreackUpData['KitBreakUpID'], null, $_POST, [
				'ReferenceNo' => $id,
				'Remarks' => 'Kit breakup wells restored on delete (workhand)',
			], 'QualityControl');
		}
	    }
    }
    echo json_encode([
        "Error"   => false,
        "Message" => "Done",
    ]);
    exit;
} else {
    echo "Bad request";exit;
}
