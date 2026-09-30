<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $EmpID               = $_POST['EmpID'];
    $Year                = $_POST['Year'];
    $Month               = $_POST['Month'];
    $BioID               = $_POST['BioID'];
    $PF                  = $_POST['PF'];
    $ESI                 = $_POST['ESI'];
    $Advance             = $_POST['Advance'];
    $MessCharges         = $_POST['MessCharges'];
    $BusChargeDeductions = $_POST['BusChargeDeductions'];
    $LateLoginFine       = $_POST['LateLoginFine'];
    $DessCodeDeductions  = $_POST['DessCodeDeductions'];
    $HelmetDeductions    = $_POST['HelmetDeductions'];
    $MonthSegment    = $_POST['MonthSegment'];

    // Validate all inputs are numeric
    $fields = [
        $EmpID, $Year, $Month, $BioID,
        $PF, $ESI, $MessCharges, $BusChargeDeductions,
        $LateLoginFine, $DessCodeDeductions, $HelmetDeductions
    ];

    foreach ($fields as $value) {
        if (!is_numeric($value)) {
            http_response_code(422);
            echo json_encode(["error" => "Invalid values"]);
            exit;
        }
    }

    // Check if record exists
    $stmt = $iconn->prepare("SELECT DID FROM HrmEmpDeductionDetails WHERE EmpID = ? AND Year = ? AND Month = ? AND MonthSegment = ?");
    $stmt->bind_param("iiii", $EmpID, $Year, $Month, $MonthSegment);
    $stmt->execute();
    $stmt->bind_result($DID);

    if ($stmt->fetch()) {
        $stmt->close();

        $update = $iconn->prepare("
            UPDATE HrmEmpDeductionDetails 
            SET BioID = ?, PF = ?, ESI = ?, Advance = ?,MessCharges = ?, BusChargeDeductions = ?, LateLoginFine = ?, DessCodeDeductions = ?, HelmetDeductions = ?, MonthSegment = ?
            WHERE DID = ?
        ");
        $update->bind_param("ddddddddddd", $BioID, $PF, $ESI, $Advance,$MessCharges, $BusChargeDeductions, $LateLoginFine, $DessCodeDeductions, $HelmetDeductions, $MonthSegment,$DID);

        if ($update->execute()) {
            echo json_encode(["status" => "updated", "DID" => $DID]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Update failed"]);
        }
        $update->close();
    } else {
        $stmt->close();

        $insert = $iconn->prepare("
            INSERT INTO HrmEmpDeductionDetails 
            (EmpID, BioID, Year, Month, PF, ESI, Advance, MessCharges, BusChargeDeductions, LateLoginFine, DessCodeDeductions, HelmetDeductions, MonthSegment)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->bind_param("iiidddddddddd", $EmpID, $BioID, $Year, $Month, $PF, $ESI, $Advance,$MessCharges, $BusChargeDeductions, $LateLoginFine, $DessCodeDeductions, $HelmetDeductions,$MonthSegment);

        if ($insert->execute()) {
            echo json_encode(["status" => "inserted", "DID" => $insert->insert_id]);
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
