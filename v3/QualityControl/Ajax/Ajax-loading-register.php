

<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once ROOT . 'config/api-call.php';

//scrmpogradedata for get the packing sytle dropdowns

$action = "";
if( isset($_POST['action']) && $_POST['action'] ){
	$action = $_POST['action'];
}

if( $action == "getInvData" ){
	
	$POID = $_POST['poid']?$_POST['poid']:0;	
	$sqlPIData = "SELECT ConsigneeID,PoNo FROM ScrmPIData WHERE PoID='$POID'";	
	$stmtPIData = $iconn->query($sqlPIData);
	$RowPIData = $stmtPIData->fetch_array();

	$SqlCount = "SELECT ContainerNo,VehicelNumber,LinerSealNo,CustomSealNo,TotalNetWt FROM `ScrmCustomInvoices` WHERE PiID='$POID'";
	$StmtCount = $iconn->query($SqlCount);
	//echo $SqlCount;
	$RowCustomInvoice = $StmtCount->fetch_array();

	$SqlConsignee = "SELECT ConsigneeName FROM ScrmConsigneeDetails WHERE ConID = '".$RowPIData['ConsigneeID']."'";

	$StmtConsignee = $iconn->query($SqlConsignee);	
	$RowConsigneeData = $StmtConsignee->fetch_array();

	$SqlGetVlist = "SELECT 
    spgd.ID,
    aps.PackingStyle as PackingStyleID,
    spgd.Grade,
    spgd.Variety,
    spgd.Brand
FROM ScrmPoGradeData spgd
LEFT JOIN AdminPackingStyles aps ON aps.PCKID = spgd.PackingStyleID
WHERE spgd.InvId ='".$POID."'";
	$StmtVlist = $iconn->query($SqlGetVlist);
	$vllist = array();

	while( $RowVlistData = $StmtVlist->fetch_array() ){
		$vllist[] = $RowVlistData;	
	}

	$finalData = array(
		"ContainerNo" => $RowCustomInvoice["ContainerNo"],
		"PoNo"=>$RowPIData['PoNo'],
		"VehicelNumber" => $RowCustomInvoice['VehicelNumber'],
		"TotalNetWt" => $RowCustomInvoice['TotalNetWt'],
		"SealNo" => $RowCustomInvoice['LinerSealNo'].",".$RowCustomInvoice['CustomSealNo'],
		// "ConsigneeName" => $RowCustomInvoice["ConsigneeName"],
		"ConsigneeName" => $RowConsigneeData["ConsigneeName"], 
		"vllist" => $vllist
	);

	//echo json_encode($RowPIData, JSON_PRETTY_PRINT);

	echo json_encode([
	        "Error"   => false,
	        "Message" => "Done",
	        "Data" => $finalData,
	    ]);

	exit;

}else if( $action == "loadDropdowns" ){

	$POID = $_POST['poid']?$_POST['poid']:0;

	$types = ['PackingStyleID', 'Grade', 'Variety', 'Brand'];
	$result = [];

	foreach ($types as $type) {
		$query = "SELECT $type FROM ScrmPoGradeData	WHERE InvId ='$POID' ";	    
	    $res = $iconn->query($query);	    
	    while ($row = $res->fetch_assoc()) {
	    	if( $type == "PackingStyleID" ){
		    	$sub_qury = " select PackingStyle from AdminPackingStyles where PCKID = '".$row[$type]."' ";
		    	$sub_res = $iconn->query($sub_qury);
		    	$row_sub = $sub_res->fetch_assoc();
		    	$row[$type] = $row_sub['PackingStyle'];
		    }
	        $result[$type][] = $row[$type];
	    }
	}

	header('Content-Type: application/json');
	echo json_encode($result);
	exit;
}else if( $action == 'logdata' ){

	$invoice_no = $_POST['invoice_no'];

	$getMcheck = " select count(ID) as cnt from loading_register_master where invoice_no = '".$invoice_no."' ";
	$stntgetmc = $iconn->query($getMcheck);
	$rowgetmc = $stntgetmc->fetch_assoc();
	//var_dump($rowgetmc);
	if($rowgetmc['cnt'] == 0){
	
	    $container_no = $_POST['container_no'];
	    $vehicle_no = $_POST['vehicle_no'];
	    $seal_no = $_POST['seal_no'];
	    $po_no = $_POST['po_no'];
	    $date = $_POST['date'];
		$remarks = $_POST['remarks'];
		 $buyerName = $_POST['ConsigneeName'];
		$drpVerifiedBy = $_POST['drpVerifiedBy'];
		$drpInspectedBy = $_POST['drpInspectedBy'];

	    // Insert into master table
	    // $stmt = $iconn->prepare("INSERT INTO loading_register_master (invoice_no, container_no, vehicle_no, seal_no, po_no, date,inspected,verified,buyerName) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
	    // $stmt->bind_param("ssssssii", $invoice_no, $container_no, $vehicle_no, $seal_no, $po_no, $date, $drpInspectedBy, $drpVerifiedBy, $buyerName);
	 $stmt = $iconn->prepare("INSERT INTO loading_register_master 
    (invoice_no, container_no, vehicle_no, seal_no, po_no, date, inspected, verified,remarks,buyerName) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
$stmt->bind_param(
     "ssssssisss",
    $invoice_no, 
    $container_no, 
    $vehicle_no, 
    $seal_no, 
    $po_no, 
    $date, 
    $drpInspectedBy, 
    $drpVerifiedBy, 
	$remarks,
    $buyerName
);

		
		$stmt->execute();
	    $master_id = $stmt->insert_id;
	    $stmt->close();
	    if ($master_id) {
		legacyAuditLogInsertById('loading_register_master', 'id', $master_id, null, $_POST, [
			'ReferenceNo' => $master_id,
			'Remarks' => 'Loading register master inserted',
		], 'QualityControl');
	    }

	    $received_ids = [];

		foreach ($_POST['variant'] as $groupIndex => $variant) {		
	        $PackingStyleID = $variant['PackingStyleID'];
	        $Grade = $variant['Grade'];
	        $Variety = $variant['Variety'];
	        $Brand = $variant['Brand'];

	        $recIds = $_POST['rec_id']["grp$groupIndex"];
	        $casesArr = $_POST['no_of_cases']["grp$groupIndex"];
	        $batchArr = $_POST['batch_no']["grp$groupIndex"];
	        $lotArr = $_POST['lot_no']["grp$groupIndex"];
	        $ridArr = $_POST['rid']["grp$groupIndex"];
	        //echo "<pre>"; print_r($_POST['variant']); echo "</pre>";
	        //echo "<pre>"; print_r($casesArr); echo "</pre>";
	        //echo "<pre>"; print_r($ridArr); echo "</pre>";
	        foreach ($casesArr as $i => $no_of_cases) {        	
	            $batch_no = $batchArr[$i];
	            $lot_no = $lotArr[$i];
	            $rid = $ridArr[$i];
	            $rec_id = $groupIndex;            
	            if (!empty($rid)) {
	                // Update existing                
	                $up_query = "UPDATE loading_register_details SET no_of_cases='$no_of_cases', batch_no='$batch_no', lot_no='$lot_no' WHERE id='$rid'";
	                $StmtUpd = $iconn->query($up_query);
	                if ($StmtUpd) {
				legacyAuditLogUpdateById('loading_register_details', 'id', $rid, null, $_POST, [
					'ReferenceNo' => $master_id,
					'Remarks' => 'Loading register detail updated',
				], 'QualityControl');
			    }
	            } else {                
	                $in_query = "INSERT INTO loading_register_details (master_id, rec_id, no_of_cases, batch_no, lot_no) 
	                               VALUES ('$master_id', '$rec_id', '$no_of_cases', '$batch_no', '$lot_no')";
	                $StmtIns = $iconn->query($in_query);
	                if ($StmtIns) {
				legacyAuditLogInsertById('loading_register_details', 'id', $iconn->insert_id, null, $_POST, [
					'ReferenceNo' => $master_id,
					'Remarks' => 'Loading register detail inserted',
				], 'QualityControl');
			    }
	            }
	        }
	    }
	echo json_encode(['status' => 'success']);
    exit;
	}
	echo json_encode(['status' => 'failed']);
    exit;
	
}else if( $action == 'logdataupdate' ){
	$master_id = (int) $_POST['master_id'];
	$drpVerifiedBy = $_POST['drpVerifiedBy'];
	$drpInspectedBy = $_POST['drpInspectedBy'];	
	$remarks = $_POST['remarks'];
	$up_query_main = "UPDATE loading_register_master SET inspected='$drpInspectedBy', verified='$drpVerifiedBy',remarks='$remarks' WHERE id='$master_id'";
    $StmtUpdMain = $iconn->query($up_query_main);
    if ($StmtUpdMain) {
	legacyAuditLogUpdateById('loading_register_master', 'id', $master_id, null, $_POST, [
		'ReferenceNo' => $master_id,
		'Remarks' => 'Loading register master updated',
	], 'QualityControl');
    }
	$received_ids = [];

	foreach ($_POST['variant'] as $groupIndex => $variant) {		
        $PackingStyleID = $variant['PackingStyleID'];
        $Grade = $variant['Grade'];
        $Variety = $variant['Variety'];
        $Brand = $variant['Brand'];

        $recIds = $_POST['rec_id']["grp$groupIndex"];
        $casesArr = $_POST['no_of_cases']["grp$groupIndex"];
        $batchArr = $_POST['batch_no']["grp$groupIndex"];
        $lotArr = $_POST['lot_no']["grp$groupIndex"];
        $ridArr = $_POST['rid']["grp$groupIndex"];

        foreach ($casesArr as $i => $no_of_cases) {        	
            $batch_no = $batchArr[$i];
            $lot_no = $lotArr[$i];
            $rid = $ridArr[$i];
            $rec_id = $groupIndex;            
            if (!empty($rid)) {
                // Update existing                
                $up_query = "UPDATE loading_register_details SET no_of_cases='$no_of_cases', batch_no='$batch_no', lot_no='$lot_no' WHERE id='$rid'";
                $StmtUpd = $iconn->query($up_query);
                if ($StmtUpd) {
				legacyAuditLogUpdateById('loading_register_details', 'id', $rid, null, $_POST, [
					'ReferenceNo' => $master_id,
					'Remarks' => 'Loading register detail updated',
				], 'QualityControl');
			    }
            } else {                
                $in_query = "INSERT INTO loading_register_details (master_id, rec_id, no_of_cases, batch_no, lot_no) 
                               VALUES ('$master_id', '$rec_id', '$no_of_cases', '$batch_no', '$lot_no')";
                $StmtIns = $iconn->query($in_query);
                if ($StmtIns) {
				legacyAuditLogInsertById('loading_register_details', 'id', $iconn->insert_id, null, $_POST, [
					'ReferenceNo' => $master_id,
					'Remarks' => 'Loading register detail inserted',
				], 'QualityControl');
			    }
            }
        }
    }
    
    
    echo json_encode(['status' => 'success']);
    exit;


	echo "success";	
	exit;
}else if( $action == 'List' ){

	$request = $_REQUEST;

	$fromPage = "";
	if (isset($request['fromPage']) && $request['fromPage'] == 'Create') {
	    $fromPage = "Create";
	} else if (isset($request['fromPage']) && $request['fromPage'] == 'Listpage') {
	    $fromPage = "Listpage";
	} else if (isset($request['fromPage']) && $request['fromPage'] == 'Reportpage') {
	    $fromPage = "Reportpage";
	}

	$columns = ['invoice_no'];
	$limit  = (int) $_POST['length'];
	$limit  = $limit < 0 ? 20 : $limit;
	$offset = max(0, (int) $_POST['start']);
	$FromDate = $_POST['fromDate'];
	$ToDate   = $_POST['toDate'];
	$invoice_no   = $_POST['invoice_no'];

	if( isset($_POST['loadAll']) && $_POST['loadAll']){
		$query_limit = " ";
	}else{
		$query_limit = " LIMIT $offset, $limit ";	
	}
	

	$OrderBYVal  = intval($request['order'][0]['column']);
	$searchValue = $iconn->real_escape_string($request['search']['value']);

	$searchQueryParts = [];

	if (!empty($FromDate) && !empty($ToDate)) {
	    $searchQueryParts[] = " date BETWEEN '$FromDate' and '$ToDate' ";
	}

	if (!empty($searchValue)) {
	    $searchQueryParts[] = "(" . $columns[0] . " LIKE '%')";
	}

	if (!empty($invoice_no)) {
	    $searchQueryParts[] = " invoice_no = '$invoice_no' ";
	}
	//echo "<pre>"; print_r($columns); exit;

	$searchQuery = count($searchQueryParts) > 0 ? ' AND ' . implode(' AND ', $searchQueryParts) : '';
	$sql_c = "SELECT COUNT(*) as count FROM loading_register_master";
	
	$totalRecords  = $iconn->query($sql_c)->fetch_assoc()['count'];
	$ttl_flt_query = "SELECT COUNT(*) as count FROM loading_register_master Where 1=1 $searchQuery ";
	//echo $ttl_flt_query; exit;
	$totalFiltered = $iconn->query($ttl_flt_query)->fetch_assoc()['count'];

	$data          = [];
	$sql           = "SELECT * from loading_register_master where 1=1 $searchQuery order by id desc
	            $query_limit  ";

	$result = $iconn->query($sql);
	if ($result && $result->num_rows > 0) {
	    $i = 0;
	    while ($RowLeaveList = $result->fetch_assoc()) {
	        $i++;
	        $singlerow = array();

	        $singlerow['Sno'] = $i;
	        //$singlerow['invoice_no'] = $RowLeaveList['invoice_no'];	
	        $getInvQry = "SELECT PoID, InvNumFull FROM `ScrmPIData` where PoID='".$RowLeaveList['invoice_no']."'";
			$smtInv = $iconn->query( $getInvQry );			
			$rowInvData = $smtInv->fetch_assoc();             
			$singlerow['InvNumFull'] = $rowInvData['InvNumFull'];	
	        $singlerow['container_no'] = $RowLeaveList['container_no'];
	        $singlerow['vehicle_no'] = $RowLeaveList['vehicle_no'];
	        //$singlerow['seal_no'] = $RowLeaveList['seal_no'];
			$singlerow['buyerName'] = $RowLeaveList['ConsigneeName'];
	        $singlerow['po_no'] = $RowLeaveList['po_no'];
	        if( $fromPage == "Reportpage" ){
	        	$singlerow['Action'] = "<a target='_blank' href='loading-register-view?id=" . $RowLeaveList['id'] . "' class='text-info' data-toggle='tooltip' title='View'> <i class='mdi mdi-eye font-size-16'></i> </a>";          
	        }else{
		        $singlerow['Action'] = "<a target='_blank' href='loading-register-edit?id=" . $RowLeaveList['id'] . "'><i class='mdi mdi-pencil font-size-16'></i></a> <button class='btn btn-link text-danger btnDelete' style='position: relative;top: -3px' data-id='" . $RowLeaveList['id'] . "' data-toggle='tooltip' data-placement='top' title='' value='' data-original-title='Delete'> <i class='mdi mdi-trash-can font-size-16'></i></button>";          
        	}
        	$data[] = $singlerow;      
        }
    }
    echo json_encode([
	    "draw"            => intval($request['draw']),
	    "recordsTotal"    => intval($totalRecords),
	    "recordsFiltered" => intval($totalFiltered),
	    "data"            => $data, // could be empty, and that's okay
	]);
	exit;
}else if( $action == 'delete' ){
	$id = $_POST['id'];
	if($id){
		if($iconn->query("DELETE FROM loading_register_master WHERE id=$id")){
			legacyAuditLogDeleteById('loading_register_master', 'id', $id, null, $_POST, [
				'ReferenceNo' => $id,
				'Remarks' => 'Loading register master deleted',
			], 'QualityControl');
			$StmtDel = $iconn->query("DELETE FROM loading_register_master WHERE master_id=$id");
			if ($StmtDel) {
				legacyAuditLogDeleteById('loading_register_master', 'master_id', $id, null, $_POST, [
					'ReferenceNo' => $id,
					'Remarks' => 'Loading register details deleted',
				], 'QualityControl');
			}
		}

	}
	echo json_encode([
	   "Message"=> "Done"
	]);
	exit;
}
