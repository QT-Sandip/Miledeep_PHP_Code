<?php
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class BrandMasterMainController
{
    private $conn;
    private $Error = null;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
    }

    // ===================== SAVE / UPDATE BRAND =====================
public function postBrandDetails($RequestBody)
{
    $BrandID     = $RequestBody['BrandID'] ;
    $BrandCode   = $RequestBody['BrandCode'] ;
    $BrandName   = $RequestBody['BrandName'] ;
    $CompanyName = $RequestBody['CompanyName'];
    $Category    = $RequestBody['Category'] ;
    $ReqStatus      = !empty($RequestBody['Status']) && $RequestBody['Status'] != '' ? $RequestBody['Status'] : 'Active';

    if($ReqStatus == 'Inactive'){
        $Status = 0;
    } else{
        $Status = 1;
    }

    $CurrentDateTime = getCurrentDateTime()['TimeStamp'];

    try {
        $existingBrand = null;
        if ($BrandID != '' && $BrandID != '0') {
            $existingBrand = $this->auditLogger->fetchRowById('BrandMaster', 'BrandID', $BrandID);
        }

        if ($BrandID == '' || $BrandID == '0') {
            // ===================== INSERT =====================
            $sql = "INSERT INTO BrandMaster 
                    (BrandCode, BrandName, Category, CompanyName, Status, CreatedDate)
                    VALUES 
                    (:BrandCode, :BrandName, :Category, :CompanyName, :Status, :CreatedDate)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':BrandCode', $BrandCode);
            $stmt->bindParam(':BrandName', $BrandName);
            $stmt->bindParam(':Category', $Category);
            $stmt->bindParam(':CompanyName', $CompanyName);
            $stmt->bindParam(':Status', $Status);
            $stmt->bindParam(':CreatedDate', $CurrentDateTime);

        } else {
            // ===================== UPDATE =====================
            $sql = "UPDATE BrandMaster SET
                    BrandCode = :BrandCode,
                    BrandName = :BrandName,
                    Category = :Category,
                    CompanyName = :CompanyName,
                    Status = :Status,
                    UpdatedDate = :UpdatedDate
                    WHERE BrandID = :BrandID";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':BrandID', $BrandID);
            $stmt->bindParam(':BrandCode', $BrandCode);
            $stmt->bindParam(':BrandName', $BrandName);
            $stmt->bindParam(':Category', $Category);
            $stmt->bindParam(':CompanyName', $CompanyName);
            $stmt->bindParam(':Status', $Status);
            $stmt->bindParam(':UpdatedDate', $CurrentDateTime);
        }

        if ($stmt->execute()) {
            $savedBrandId = $BrandID ? $BrandID : $this->conn->lastInsertId();
            $savedBrand = $this->auditLogger->fetchRowById('BrandMaster', 'BrandID', $savedBrandId);
            if ($BrandID == '' || $BrandID == '0') {
                $this->auditLogger->logInsert('BrandMaster', $savedBrandId, $savedBrand, $RequestBody, [
                    'ReferenceNo' => $BrandCode,
                    'Remarks' => 'Inventory brand created',
                ]);
            } else {
                $this->auditLogger->logUpdate('BrandMaster', $savedBrandId, $existingBrand, $savedBrand, $RequestBody, [
                    'ReferenceNo' => $BrandCode,
                    'Remarks' => 'Inventory brand updated',
                ]);
            }

            return [
                "status" => "success",
                "msg" => $BrandID ? "Brand updated successfully" : "Brand created successfully",
                "data" => ["BrandID" => $BrandID ?: $this->conn->lastInsertId()]
            ];
        } else {
            return [
                "status" => "error",
                "msg" => "Error while saving brand",
                "data" => []
            ];
        }

    } catch (PDOException $e) {
        return [
            "status" => "error",
            "msg" => $e->getMessage(),
            "data" => []
        ];
    }
}




    // ===================== GET SINGLE BRAND =====================
public function getBrand($RequestBody)
{
    if (is_array($RequestBody)) {
        $BrandID = isset($RequestBody['BrandID']) ? trim($RequestBody['BrandID']) : (isset($RequestBody['brand_id']) ? trim($RequestBody['brand_id']) : '');
        $BrandCode = isset($RequestBody['BrandCode']) ? trim($RequestBody['BrandCode']) : (isset($RequestBody['brand_code']) ? trim($RequestBody['brand_code']) : '');
    } else {
        $BrandID = trim($RequestBody);
        $BrandCode = '';
    }

    $sql = "SELECT BrandID, BrandCode, BrandName, Category, CompanyName, Status
            FROM BrandMaster
            WHERE 1=1";

    if (!empty($BrandID)) {
        $sql .= " AND BrandID = :BrandID";
    } elseif (!empty($BrandCode)) {
        $sql .= " AND BrandCode = :BrandCode";
    }

    $stmt = $this->conn->prepare($sql);

    if (!empty($BrandID)) {
        $stmt->bindParam(':BrandID', $BrandID);
    } elseif (!empty($BrandCode)) {
        $stmt->bindParam(':BrandCode', $BrandCode);
    }

    $stmt->execute();
    $brand = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'Error' => null,
        'Msg'   => $brand ? "Success" : "No brand found",
        'Data'  => $brand ?: [],
    ];
}


    // ===================== DELETE BRAND =====================
    public function deleteBrand($RequestBody)
    {
        $BrandID = isset($RequestBody['brand_id']) ? trim($RequestBody['brand_id']) : (isset($RequestBody['BrandID']) ? trim($RequestBody['BrandID']) : '');

        if (empty($BrandID)) {
            return [
                'Error' => 'Missing Brand ID',
                'Msg'   => 'Brand ID not found in request',
                'Data'  => []
            ];
        }

        $existingBrand = $this->auditLogger->fetchRowById('BrandMaster', 'BrandID', $BrandID);
        $sql = "DELETE FROM BrandMaster WHERE BrandID = :BrandID";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':BrandID', $BrandID, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->auditLogger->logDelete('BrandMaster', $BrandID, $existingBrand, $RequestBody, [
                    'ReferenceNo' => isset($existingBrand['BrandCode']) ? $existingBrand['BrandCode'] : $BrandID,
                    'Remarks' => 'Inventory brand deleted',
                ]);
                $Data['Error'] = null;
                $Data['Msg']   = "Brand deleted successfully";
                $Data['Data']  = ["BrandID" => $BrandID];
            } else {
                $Data['Error'] = "No record found";
                $Data['Msg']   = "No brand found with ID: $BrandID";
                $Data['Data']  = [];
            }
        } else {
            $error = $stmt->errorInfo();
            $Data['Error'] = isset($error[2]) ? $error[2] : 'Unknown database error';
            $Data['Msg']   = "Error deleting brand";
            $Data['Data']  = [];
        }

        return $Data;
    }

    // ===================== LOAD ALL BRANDS =====================
  public function loadBrands()
{
    $sql = "SELECT BrandID, BrandCode, BrandName, Category, CompanyName, Status
            FROM BrandMaster
            ORDER BY BrandID DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'Error' => null,
        'Msg'   => "Success",
        'Data'  => $brands
    ];
}
}
