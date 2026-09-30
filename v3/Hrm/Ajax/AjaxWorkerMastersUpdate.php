<?php
header("Content-Type: application/json");
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$InputValue = trim($_POST['inputValue']);
$InputType  = trim($_POST['inputType']);
$InputID    = isset($_POST['ID']) ? intval($_POST['ID']) : 0;


$response = [
    "Success" => false,
    "Error" => null,
    "Msg" => "",
    "Data" => []
];

function jsonError($msg, $error = null) {
    echo json_encode([
        "Success" => false,
        "Error" => $error,
        "Msg" => $msg,
        "Data" => []
    ]);
    exit;
}

if(isset($_POST['Action']) && $_POST['Action'] == "DELETE"){


    if (empty($_POST['ID'])) {
        jsonError("ID required for delete");
    }

    $deleteId = intval($_POST['ID']);

    $Table = [];
    if ($InputType == "Batch") {
        $Table['name']  = "EmpWorkerBatchMaster";
        $Table['Field'] = "BatchName";
        $Table['ID']    = "BatchID";
    } elseif ($InputType == "Section") {
        $Table['name']  = "EmpWorkerSectionsMaster";
        $Table['Field'] = "SectionName";
        $Table['ID']    = "SectionID";
    } elseif ($InputType == "Village") {
        $Table['name']  = "EmpWorkerVillageMaster";
        $Table['Field'] = "VillageName";
        $Table['ID']    = "VID";
    } else {
        jsonError("Invalid Input Type");
    }
    $tableName  = $Table['name'];
    $primaryKey = $Table['ID'];
    $deleteStmt = $iconn->prepare("DELETE FROM `$tableName` WHERE `$primaryKey` = ?");
    if (!$deleteStmt) jsonError("Prepare failed for DELETE", $iconn->error);

    $deleteStmt->bind_param("i", $deleteId);
    if ($deleteStmt->execute()) {
        $response["Success"] = true;
        $response["Msg"] = "$fieldName ID '$deleteId' deleted successfully.";
        echo json_encode($response);
        exit;
    } else {
        jsonError("Delete failed", $deleteStmt->error);
    }

}else{


// Set table details based on input type
$Table = [];
if ($InputType == "AddBatch") {
    $Table['name']  = "EmpWorkerBatchMaster";
    $Table['Field'] = "BatchName";
    $Table['ID']    = "BatchID";
} elseif ($InputType == "AddSection") {
    $Table['name']  = "EmpWorkerSectionsMaster";
    $Table['Field'] = "SectionName";
    $Table['ID']    = "SectionID";
} elseif ($InputType == "AddVillage") {
    $Table['name']  = "EmpWorkerVillageMaster";
    $Table['Field'] = "VillageName";
    $Table['ID']    = "VID";
} else {
    jsonError("Invalid Input Type");
}

$tableName  = $Table['name'];
$fieldName  = $Table['Field'];
$primaryKey = $Table['ID'];

if ($InputID > 0) {
    // 🔄 Update existing record
    // Check for duplicate name in another ID
    $checkStmt = $iconn->prepare("SELECT `$primaryKey` FROM `$tableName` WHERE `$fieldName` = ? AND `$primaryKey` != ?");
    if (!$checkStmt) jsonError("Prepare failed for duplicate check", $iconn->error);
    $checkStmt->bind_param("si", $InputValue, $InputID);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        $response["Msg"] = "$fieldName '$InputValue' already exists.";
        echo json_encode($response);
        exit;
    }
    $checkStmt->close();

    // Do the update
    $updateStmt = $iconn->prepare("UPDATE `$tableName` SET `$fieldName` = ? WHERE `$primaryKey` = ?");
    if (!$updateStmt) jsonError("Prepare failed for UPDATE", $iconn->error);
    $updateStmt->bind_param("si", $InputValue, $InputID);

    if ($updateStmt->execute()) {
        $updateStmt->close();

        // Fetch updated row
        $fetchStmt = $iconn->prepare("SELECT * FROM `$tableName` WHERE `$primaryKey` = ?");
        if (!$fetchStmt) jsonError("Prepare failed for FETCH", $iconn->error);
        $fetchStmt->bind_param("i", $InputID);
        $fetchStmt->execute();

        $meta = $fetchStmt->result_metadata();
        $rowData = [];
        if ($meta) {
            $params = [];
            while ($field = $meta->fetch_field()) {
                $params[] = &$rowData[$field->name];
            }
            call_user_func_array([$fetchStmt, 'bind_result'], $params);
            $fetchStmt->fetch();
        }
        $fetchStmt->close();

        $response["Success"] = true;
        $response["Msg"] = "$fieldName updated successfully.";
        $response["Type"] = $InputType;
        $response["Data"] = $rowData;
    } else {
        $response["Error"] = $updateStmt->error;
        $response["Msg"] = "Update failed.";
        $response["Type"] = $InputType;
    }
} else {
    // ➕ Insert new record (original logic)
    $checkStmt = $iconn->prepare("SELECT `$primaryKey` FROM `$tableName` WHERE `$fieldName` = ?");
    if (!$checkStmt) jsonError("Prepare failed for SELECT", $iconn->error);
    $checkStmt->bind_param("s", $InputValue);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        $response["Msg"] = "$fieldName '$InputValue' already exists.";
        echo json_encode($response);
        exit;
    }
    $checkStmt->close();

    $insertStmt = $iconn->prepare("INSERT INTO `$tableName` (`$fieldName`) VALUES (?)");
    if (!$insertStmt) jsonError("Prepare failed for INSERT", $iconn->error);
    $insertStmt->bind_param("s", $InputValue);

    if ($insertStmt->execute()) {
        $insertId = $iconn->insert_id;
        $insertStmt->close();

        // Fetch inserted row
        $fetchStmt = $iconn->prepare("SELECT * FROM `$tableName` WHERE `$primaryKey` = ?");
        if (!$fetchStmt) jsonError("Prepare failed for FETCH", $iconn->error);
        $fetchStmt->bind_param("i", $insertId);
        $fetchStmt->execute();

        $meta = $fetchStmt->result_metadata();
        $rowData = [];
        if ($meta) {
            $params = [];
            while ($field = $meta->fetch_field()) {
                $params[] = &$rowData[$field->name];
            }
            call_user_func_array([$fetchStmt, 'bind_result'], $params);
            $fetchStmt->fetch();
        }
        $fetchStmt->close();

        $response["Success"] = true;
        $response["Msg"] = "$fieldName '$InputValue' added successfully.";
        $response["Type"] = $InputType;
        $response["Data"] = $rowData;
    } else {
        $response["Error"] = $insertStmt->error;
        $response["Msg"] = "Insert failed.";
        $response["Type"] = $InputType;
    }
}

echo json_encode($response);


}


