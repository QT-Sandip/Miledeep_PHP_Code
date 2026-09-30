<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $EmpID               = $_POST['EmpID'];
    $Year                = $_POST['Year'];
    $Month               = $_POST['Month'];
    $BioID               = $_POST['BioID'];
    $ArrearAmount        = $_POST['ArrearAmount'];
    $ShipmentCharges     = $_POST['ShipmentCharges'];
    $Commission          = $_POST['Commission'];
    $SundayIncentive     = $_POST['SundayIncentive'];
    $OThours             = $_POST['OThours'];
    $MonthSegment    = $_POST['MonthSegment'];

    // Validate all inputs are numeric
    $fields = [
        $EmpID, $Year, $Month, $BioID,
        $ArrearAmount, $ShipmentCharges, $Commission, $SundayIncentive,
        $OThours
    ];

    foreach ($fields as $value) {
        if (!is_numeric($value)) {
            http_response_code(422);
            echo json_encode(["error" => "Invalid values"]);
            exit;
        }
    }

    // Check if record exists
    $stmt = $iconn->prepare("SELECT AID FROM HrmEmpAdditionDetails WHERE EmpID = ? AND Year = ? AND Month = ? AND MonthSegment = ?");
    $stmt->bind_param("iiii", $EmpID, $Year, $Month, $MonthSegment);
    $stmt->execute();
    $stmt->bind_result($AID);

    if ($stmt->fetch()) {
        $stmt->close();

        $update = $iconn->prepare("
            UPDATE HrmEmpAdditionDetails 
            SET BioID = ?, ArrearAmount = ?, ShipmentCharges = ?, Commission = ?, SundayIncentive = ?, OThours = ?, MonthSegment = ? 
            WHERE AID = ?
        ");
        $update->bind_param("dddddddd", $BioID, $ArrearAmount, $ShipmentCharges, $Commission, $SundayIncentive, $OThours, $MonthSegment,$AID);

        if ($update->execute()) {
            echo json_encode(["status" => "updated", "AID" => $AID]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Update failed"]);
        }
        $update->close();
    } else {
        $stmt->close();

        $insert = $iconn->prepare("
            INSERT INTO HrmEmpAdditionDetails 
            (EmpID, BioID, Year, Month, ArrearAmount, ShipmentCharges, Commission, SundayIncentive, OThours, MonthSegment)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->bind_param("dddddddddd", $EmpID, $BioID, $Year, $Month, $ArrearAmount, $ShipmentCharges, $Commission, $SundayIncentive, $OThours,$MonthSegment);

        if ($insert->execute()) {
            echo json_encode(["status" => "inserted", "AID" => $insert->insert_id]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Insert failed"]);
        }
        $insert->close();
    }

} else {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method"]);
}
