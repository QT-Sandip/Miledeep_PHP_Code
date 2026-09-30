<?php
include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class PorformaRegisterController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
        // $this->utilController = new UtilController($this->conn);
    }

    // ===================== SAVE / UPDATE PROFORMA =====================
public function postProformaDetails($RequestBody)
{
    $ProformaID  = isset($RequestBody['ProformaID']) ? trim($RequestBody['ProformaID']) : '';
    $DateOption  = isset($RequestBody['date_option']) ? trim($RequestBody['date_option']) : '';
    $StartDate   = !empty($RequestBody['start_date']) ? trim($RequestBody['start_date']) : NULL;
    $EndDate     = !empty($RequestBody['end_date']) ? trim($RequestBody['end_date']) : NULL;
    $Layout      = isset($RequestBody['layout']) ? trim($RequestBody['layout']) : '';
    $Output      = isset($RequestBody['output']) ? trim($RequestBody['output']) : '';
    $CurrentDateTime = getCurrentDateTime()['TimeStamp'];

    try {
        $existingProforma = null;
        if ($ProformaID != '' && $ProformaID != '0') {
            $existingProforma = $this->auditLogger->fetchRowById('proforma_register', 'ProformaID', $ProformaID);
        }

        // Prepare values for SQL (convert NULLs to literal NULL)
        $StartDateSQL = ($StartDate !== NULL && $StartDate !== '') ? "'$StartDate'" : "NULL";
        $EndDateSQL   = ($EndDate !== NULL && $EndDate !== '') ? "'$EndDate'" : "NULL";

        if ($ProformaID == '' || $ProformaID == '0') {
            // ===================== INSERT =====================
            $sql = "INSERT INTO proforma_register (
                        DateOption, StartDate, EndDate, Layout, Output, CreatedDate
                    ) VALUES (
                        '$DateOption', $StartDateSQL, $EndDateSQL, '$Layout', '$Output', '$CurrentDateTime'
                    )";
            $stmt = $this->conn->prepare($sql);
        } else {
            // ===================== UPDATE =====================
            $sql = "UPDATE proforma_register SET
                        DateOption  = '$DateOption',
                        StartDate   = $StartDateSQL,
                        EndDate     = $EndDateSQL,
                        Layout      = '$Layout',
                        Output      = '$Output',
                        UpdatedDate = '$CurrentDateTime'
                    WHERE ProformaID = $ProformaID";
            $stmt = $this->conn->prepare($sql);
        }

        if ($stmt->execute()) {
            $savedProformaId = ($ProformaID == '' || $ProformaID == '0') ? $this->conn->lastInsertId() : $ProformaID;
            $savedProforma = $this->auditLogger->fetchRowById('proforma_register', 'ProformaID', $savedProformaId);
            if ($ProformaID == '' || $ProformaID == '0') {
                $this->auditLogger->logInsert('proforma_register', $savedProformaId, $savedProforma, $RequestBody, [
                    'ReferenceNo' => $savedProformaId,
                    'Remarks' => 'Inventory proforma register created',
                ]);
                $LastID = $this->conn->lastInsertId();
                $Data['Error'] = null;
                $Data['Msg']   = 'Proforma Register created successfully';
                $Data['Data']  = ['ProformaID' => $LastID];
            } else {
		$this->auditLogger->logUpdate('proforma_register', $savedProformaId, $existingProforma, $savedProforma, $RequestBody, [
                    'ReferenceNo' => $savedProformaId,
                    'Remarks' => 'Inventory proforma register updated',
                ]);
                $Data['Error'] = null;
                $Data['Msg']   = 'Proforma Register updated successfully';
                $Data['Data']  = ['ProformaID' => $ProformaID];
            }
        } else {
            $error = $stmt->errorInfo();
            $Data['Error'] = isset($error[2]) ? $error[2] : 'Unknown database error';
            $Data['Msg']   = 'Error while saving Proforma Register: ' . (isset($error[2]) ? $error[2] : 'No further info');
            $Data['Data']  = [];
        }
    } catch (PDOException $e) {
        $Data['Error'] = $e->getMessage();
        $Data['Msg']   = 'Database error while saving Proforma Register';
        $Data['Data']  = [];
    }

    return $Data;
}




    // ===================== GET SINGLE PROFORMA =====================
    public function getProforma($ProformaID)
    {
        $sql = "SELECT * FROM proforma_register WHERE ProformaID = :ProformaID";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':ProformaID', $ProformaID, PDO::PARAM_INT);
        $stmt->execute();
        $proforma = $stmt->fetch(PDO::FETCH_ASSOC);

        $Data['Error'] = null;
        $Data['Msg']   = $proforma ? "Success" : "No record found";
        $Data['Data']  = $proforma ?: [];

        return $Data;
    }

    // ===================== DELETE PROFORMA =====================
    public function deleteProforma($RequestBody)
    {
        $ProformaID = isset($RequestBody['ProformaID']) ? trim($RequestBody['ProformaID']) : '';

        if (empty($ProformaID)) {
            return ['Error' => 'Missing Proforma ID', 'Msg' => 'No Proforma ID found', 'Data' => []];
        }

        $existingProforma = $this->auditLogger->fetchRowById('proforma_register', 'ProformaID', $ProformaID);
        $sql = "DELETE FROM proforma_register WHERE ProformaID = :ProformaID";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':ProformaID', $ProformaID, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->auditLogger->logDelete('proforma_register', $ProformaID, $existingProforma, $RequestBody, [
                    'ReferenceNo' => $ProformaID,
                    'Remarks' => 'Inventory proforma register deleted',
                ]);
                $Data['Error'] = null;
                $Data['Msg']   = "Proforma Register deleted successfully";
                $Data['Data']  = ["ProformaID" => $ProformaID];
            } else {
                $Data['Error'] = "No record found";
                $Data['Msg']   = "No record found with ID: $ProformaID";
                $Data['Data']  = [];
            }
        } else {
            $error = $stmt->errorInfo();
            $Data['Error'] = isset($error[2]) ? $error[2] : 'Unknown DB error';
            $Data['Msg']   = "Error deleting record";
            $Data['Data']  = [];
        }

        return $Data;
    }

    // ===================== LOAD ALL PROFORMAS =====================
    public function loadProformas()
    {
        $sql = "SELECT * FROM proforma_register ORDER BY ProformaID DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $proformas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $Data['Error'] = null;
        $Data['Msg']   = "Success";
        $Data['Data']  = $proformas;

        return $Data;
    }
}
?>
