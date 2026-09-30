<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class DepartmentMasterMainController
{
    private $conn;
    private $Error = null;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
    }

    // ===================== SAVE / UPDATE DEPARTMENT =====================
   public function postDepartmentDetails($RequestBody)
{
    $DepartmentID   = isset($RequestBody['department_id']) ? $RequestBody['department_id'] : '';
    $DepartmentCode = isset($RequestBody['department_code']) ? $RequestBody['department_code'] : '';
    $DepartmentName = isset($RequestBody['department_name']) ? $RequestBody['department_name'] : '';
    $Category       = isset($RequestBody['category']) ? $RequestBody['category'] : '';
    $Status         = isset($RequestBody['stat']) ? $RequestBody['stat'] : '';
    $CurrentDateTime = getCurrentDateTime()['TimeStamp']; // same style as your example

    try {
        $existingDepartment = null;
        if ($DepartmentID != '' && $DepartmentID != '0') {
            $existingDepartment = $this->auditLogger->fetchRowById('Departments', 'DepartmentID', $DepartmentID);
        }

        if ($DepartmentID == '' || $DepartmentID == '0') {
            // ===================== INSERT =====================
            $sql = "INSERT INTO Departments (
                        DepartmentCode, DepartmentName, Category, Status, CreatedDate
                    ) VALUES (
                        '$DepartmentCode', '$DepartmentName', '$Category', '$Status', '$CurrentDateTime'
                    )";
        } else {
            // ===================== UPDATE =====================
            $sql = "UPDATE Departments SET
                        DepartmentCode = '$DepartmentCode',
                        DepartmentName = '$DepartmentName',
                        Category = '$Category',
                        Status = '$Status',
                        UpdatedDate = '$CurrentDateTime'
                    WHERE DepartmentID = $DepartmentID";
        }
            $stmt = $this->conn->prepare($sql);
        if ($stmt->execute()) {
            $savedDepartmentId = ($DepartmentID == '' || $DepartmentID == '0') ? $this->conn->lastInsertId() : $DepartmentID;
            $savedDepartment = $this->auditLogger->fetchRowById('Departments', 'DepartmentID', $savedDepartmentId);
            if ($DepartmentID == '' || $DepartmentID == '0') {
                $this->auditLogger->logInsert('Departments', $savedDepartmentId, $savedDepartment, $RequestBody, [
                    'ReferenceNo' => $DepartmentCode,
                    'Remarks' => 'Inventory department created',
                ]);
            } else {
                $this->auditLogger->logUpdate('Departments', $savedDepartmentId, $existingDepartment, $savedDepartment, $RequestBody, [
                    'ReferenceNo' => $DepartmentCode,
                    'Remarks' => 'Inventory department updated',
                ]);
            }

            if ($DepartmentID == '' || $DepartmentID == '0') {
                $Data['Error'] = null;
                $Data['Msg']   = 'Department created successfully';
                $Data['Data']  = ['DepartmentID' => $savedDepartmentId];
            } else {
                $Data['Error'] = null;
                $Data['Msg']   = 'Department updated successfully';
                $Data['Data']  = ['DepartmentID' => $DepartmentID];
            }
        } else {
            $error = $stmt->errorInfo();
            $Data['Error'] = isset($error[2]) ? $error[2] : 'Unknown database error';
            $Data['Msg']   = 'Error while saving department: ' . (isset($error[2]) ? $error[2] : 'No further info');
            $Data['Data']  = [];
        }
    } catch (PDOException $e) {
        $Data['Error'] = $e->getMessage();
        $Data['Msg']   = 'Database error while saving department';
        $Data['Data']  = [];
    }

    return $Data;
}


    // ===================== GET SINGLE DEPARTMENT =====================
   // ===================== GET SINGLE DEPARTMENT =====================
public function getDepartment($RequestBody)
{
    // $DepartmentID   = isset($RequestBody['department_id']) ? trim($RequestBody['department_id']) : '';
    // $DepartmentCode = isset($RequestBody['department_code']) ? trim($RequestBody['department_code']) : '';


        if (is_array($RequestBody)) {
            $DepartmentID = '';
            if (isset($RequestBody['DepartmentID'])) {
                $DepartmentID = $RequestBody['DepartmentID'];
            } elseif (isset($RequestBody['department_id'])) {
                $DepartmentID = $RequestBody['department_id'];
            }

            $DepartmentCode = '';
            if (isset($RequestBody['DepartmentCode'])) {
                $DepartmentCode = $RequestBody['DepartmentCode'];
            } elseif (isset($RequestBody['department_code'])) {
                $DepartmentCode = $RequestBody['department_code'];
            }

            $DepartmentID = trim($DepartmentID);
            $DepartmentCode = trim($DepartmentCode);
        } else {
            $DepartmentID = trim($RequestBody);
            $DepartmentCode = '';
        }

    
    $sql = "SELECT DepartmentID, DepartmentCode, DepartmentName, Category, Status 
            FROM Departments 
            WHERE 1=1";

    if (!empty($DepartmentID)) {
        $sql .= " AND DepartmentID = :DepartmentID";
    } elseif (!empty($DepartmentCode)) {
        $sql .= " AND DepartmentCode = :DepartmentCode";
    }

    $stmt = $this->conn->prepare($sql);

    if (!empty($DepartmentID)) {
        $stmt->bindParam(':DepartmentID', $DepartmentID);
    } elseif (!empty($DepartmentCode)) {
        $stmt->bindParam(':DepartmentCode', $DepartmentCode);
    }

    $stmt->execute();
    $department = $stmt->fetch(PDO::FETCH_ASSOC);

    $Data['Error'] = null;
    $Data['Msg']   = $department ? "Success" : "No department found";
    $Data['Data']  = $department ?: [];

    return $Data;
}


    // ===================== DELETE DEPARTMENT =====================
    // ===================== DELETE DEPARTMENT =====================
// ===================== DELETE DEPARTMENT (BY ID) =====================
public function deleteDepartment($RequestBody)
{
    $DepartmentID = isset($RequestBody['department_id']) ? trim($RequestBody['department_id']) : '';

    if (empty($DepartmentID)) {
        return [
            'Error' => 'Missing Department ID',
            'Msg'   => 'Department ID not found in request',
            'Data'  => []
        ];
    }

    $existingDepartment = $this->auditLogger->fetchRowById('Departments', 'DepartmentID', $DepartmentID);
    $sql = "DELETE FROM Departments WHERE DepartmentID = :DepartmentID";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':DepartmentID', $DepartmentID, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            $this->auditLogger->logDelete('Departments', $DepartmentID, $existingDepartment, $RequestBody, [
                'ReferenceNo' => isset($existingDepartment['DepartmentCode']) ? $existingDepartment['DepartmentCode'] : $DepartmentID,
                'Remarks' => 'Inventory department deleted',
            ]);
            $Data['Error'] = null;
            $Data['Msg']   = "Department deleted successfully";
            $Data['Data']  = ["DepartmentID" => $DepartmentID];
        } else {
            $Data['Error'] = "No record found";
            $Data['Msg']   = "No department found with ID: $DepartmentID";
            $Data['Data']  = [];
        }
    } else {
        $error = $stmt->errorInfo();
        $Data['Error'] = isset($error[2]) ? $error[2] : 'Unknown database error';
        $Data['Msg']   = "Error deleting department: " . (isset($error[2]) ? $error[2] : 'No further info');
        $Data['Data']  = [];
    }

    return $Data;
}



    // ===================== LOAD ALL DEPARTMENTS =====================
    public function loadDepartments($RequestBody = [])
    {
        if($RequestBody['methodType'] == 'getDropDowns'){
            $Results = $this->getDropDowns();
            $Data['Error'] = null;
            $Data['Msg']   = 'Success';
            $Data['Data']  = $Results;
            return $Data;
        }
        if($RequestBody['methodType'] == 'getSubDepartments' && $RequestBody['DepartmentValue']){
            $Results = $this->getSubDepartments($RequestBody['DepartmentValue']);
            $Data['Error'] = null;
            $Data['Msg']   = 'Success';
            $Data['Data']  = $Results;
            return $Data;
        }
        $sql = "SELECT DepartmentID, DepartmentCode, 
                CASE WHEN ISD.SubDepartmentName != '' THEN ISD.SubDepartmentName ELSE DPT.DepartmentName END AS DepartmentName, 
                CASE WHEN IDs.DepartmentName != '' THEN IDs.DepartmentName ELSE DPT.Category END AS Category, 
                DPT.`Status` 
                FROM Departments DPT
                LEFT JOIN InventoryDepartments IDs ON IDs.Value = DPT.Category
                LEFT JOIN InventorySubDepartments ISD ON ISD.Value = DPT.DepartmentName
                ORDER BY DepartmentID DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $Departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $Data['Error'] = null;
        $Data['Msg']   = "Success";
        $Data['Data']  = $Departments;

        return $Data;
    }
    public function getDropDowns(){
        $results['Departments'] = $this->getDepartments();
        $results['SubDepartments'] = $this->getSubDepartments();
        return $results;
    }
    public function getDepartments(){
        $sql = "SELECT ID,DepartmentName,Value FROM InventoryDepartments WHERE Status = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $data = [];
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    }
    public function getSubDepartments($ParentID = ''){
        $join = $where = '';
        if($ParentID){
            $join = " LEFT JOIN InventoryDepartments IDs ON IDs.ID = ISD.ParentID ";
            $where = " AND IDs.Value = '$ParentID' ";
        }
        $sql = "SELECT ISD.ID,SubDepartmentName,ISD.Value 
                FROM InventorySubDepartments ISD 
                $join
                WHERE ISD.Status = 1
                $where";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
