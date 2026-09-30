<?php
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class MaterialIssuePackagingController
{
    private $conn;
    private $Error = null;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
    }

    public function getItemDetails($RequestBody)
    {
        $GeneralItemId = isset($RequestBody['GeneralItemId']) ? $RequestBody['GeneralItemId'] : 0;
        $EffectiveDate = $RequestBody['EffectiveDate'];
        $sql = "SELECT PM.Units AS PriceUnits, PM.UnitPrice, GM.Gst FROM (SELECT Units, UnitPrice FROM PriceMaster WHERE GeneralItemId = $GeneralItemId AND FromDate <= '$EffectiveDate' ORDER BY FromDate DESC LIMIT 1) AS PM LEFT JOIN (SELECT Gst FROM GstMaster WHERE GeneralItemId = $GeneralItemId AND FromDate <= '$EffectiveDate' ORDER BY FromDate DESC LIMIT 1) AS GM ON 1=1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $ItemData = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ItemData['Gst'] = $dataRow['Gst'];
            $ItemData['UnitPrice'] = $dataRow['UnitPrice'];
            $ItemData['Units'] = $dataRow['PriceUnits'];
        }

        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = "Success";
        $Data['Data'] = $ItemData;


        return $Data;
    }

    public function getIndentsList()
    {
        $sql = "SELECT ID.PiID AS Id,ID.DocNo,PO.PoID AS PoId,GR.ID AS NoteId FROM PmGoodsReceiptNote AS GR LEFT JOIN PmPurchaseOrder AS PO ON GR.DocNo=PO.PoID LEFT JOIN PmProformaInvoice AS ID ON PO.ProformaID=ID.PiID WHERE GR.status=1 AND GR.ID IS NOT NULL AND PO.PoID IS NOT NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $Indents = [];
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Indent['SNo'] = ++$i;
            $Indent['Id'] = $dataRow['Id'];
            $Indent['DocNo'] = $dataRow['DocNo'];
            $Indent['PoId'] = $dataRow['PoId'];
            $Indent['NoteId'] = $dataRow['NoteId'];

            $Indents[] = $Indent;
        }

        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = "Success";
        $Data['Data'] = $Indents;


        return $Data;
    }

    public function getIndentsBreakUpList($RequestBody)
    {
        $indent_id = isset($RequestBody['indent']) ? $RequestBody['indent'] : '';
        $sql = "SELECT * FROM GsProformaOrderBreakUp WHERE NoteId=$indent_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $Indents = [];
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Indents[] = $dataRow;
        }

        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = "Success";
        $Data['Data'] = $Indents;


        return $Data;
    }

    //     public function postMaterialIssue($RequestBody)
    //     {
    //         // echo print_r($RequestBody);exit;
    //         $material_id = isset($RequestBody['material_id']) ? $RequestBody['material_id'] : '';
    //         $po_no = isset($RequestBody['po_no']) ? $RequestBody['po_no'] : 0;
    //         $pdate = isset($RequestBody['pdate']) ? $RequestBody['pdate'] : '';
    //         $doc_no = isset($RequestBody['doc_no']) ? $RequestBody['doc_no'] : '';
    //         $narration = isset($RequestBody['narration']) ? $RequestBody['narration'] : '';
    //         $pro = isset($RequestBody['pro']) ? $RequestBody['pro'] : 0;
    //         $breakUp = (isset($RequestBody['breakUp'])) ? $RequestBody['breakUp'] : [];

    //         if ($material_id == '') {
    //             $sql = "INSERT INTO `PmMaterialIssue`(`DocNo`, `Date`, `ProNo`, `Narration`)
    //             VALUES ($doc_no,'$pdate',$pro,'$narration')";
    //         } else {
    //             $sql = "UPDATE `PmMaterialIssue` SET `DocNo`=$doc_no,
    //             `Date`='$pdate',`ProNo`=$pro,`Narration`='$narration'
    //                 WHERE Id=$material_id";
    //         }
    //         // echo $sql;exit;
    //         $stmt = $this->conn->prepare($sql);
    //         if ($stmt->execute()) {
    //             $LastID = $material_id == '' ? $this->conn->lastInsertId() : $material_id;
    //         } else {
    //             $error = $stmt->errorInfo();
    //             $Data['Error'] = $error[2];
    //             $Data['status'] = "fail";
    //             $Data['Msg'] = "Error while saving Price data";
    //             $Data['Data'] = [];
    //             return $Data;
    //         }
    //         // $item = (isset($RequestBody['item'])) ? $RequestBody['item'] : [];
    //         // $received_qty = isset($RequestBody['received_qty']) ? $RequestBody['received_qty'] : '';
    //         // $location = isset($RequestBody['location']) ? $RequestBody['location'] : '';

    //         $error = "";

    //         $stmt = null;
    //         $sql = "";

    //         // echo print_r($breakUp);exit;
    //         foreach ($breakUp as $item) {
    //             // Access individual fields inside each breakup entry
    //             $attachment = isset($item['attachment']) ? $item['attachment'] : '';
    //             $txtVariety = isset($item['txtVariety']) ? $item['txtVariety'] : 0;
    //             $txtVarietyId = (isset($item['txtVarietyId']) && $item['txtVarietyId'] != '') ? $item['txtVarietyId'] : 0;
    //             $receivedQty = isset($item['received_qty']) ? $item['received_qty'] : 0;
    //             $purpose = isset($item['purpose']) ? $item['purpose'] : '';
    //             $remark = isset($item['remark']) ? $item['remark'] : '';
    //             $indent_id = isset($item['indent']) ? $item['indent'] : '';
    //             $packing_style = isset($item['packing_style']) ? $item['packing_style'] : 0;
    //             $brand = isset($item['brand']) ? $item['brand'] : 0;
    //             $location = isset($item['location']) ? $item['location'] : '';


    //             if ($indent_id == '') {
    //                 $sql = "INSERT INTO `PmMaterialIssueBreakUp`
    // (`Warehouse`, `VarietyID`, `PackingStyleID`, `Brand`, `Quantity`, `Purpose`, `Attachment`, `Remarks`)
    // VALUES
    // ('$location','$txtVarietyId',$packing_style,'$brand',$receivedQty,'$purpose','$attachment','$remark')";

    //                 // echo $sql;exit;
    //                 $stmt = $this->conn->prepare($sql);
    //                 if ($stmt->execute()) {
    //                     $getSql = "SELECT Id,AvailableQuantity from PmProformaAvailableData WHERE VarietyID=$txtVarietyId";
    //                     $stmtGetSql = $this->conn->prepare($getSql);
    //                     $stmtGetSql->execute();
    //                     $AvailableData = $stmtGetSql->fetch(PDO::FETCH_ASSOC);
    //                     if ($AvailableData && is_array($AvailableData)) {
    //                         $availableQty = $AvailableData['AvailableQuantity'] - $receivedQty;
    //                         $sqlAvailableData = "UPDATE PmProformaAvailableData SET `VarietyID`=$txtVarietyId, `AvailableQuantity`=$availableQty WHERE Id=" + $AvailableData['Id'];
    //                     }
    //                     $stmtAvailableData = $this->conn->prepare($sqlAvailableData);
    //                     $stmtAvailableData->execute();

    //                     $error .= "";
    //                 } else {
    //                     $error .= $stmt->errorInfo()[2];
    //                 }
    //                 $stmt = null;
    //                 $sql = "";
    //             } else {
    //                 $sqlGetMi = "SELECT Quantity FROM PmMaterialIssueBreakUp WHERE BreakUpId=$indent_id";
    //                 $stmtGetMi = $this->conn->prepare($sqlGetMi);
    //                 $stmtGetMi->execute();
    //                 $rowGetMi = $stmtGetMi->fetch(PDO::FETCH_ASSOC);

    //                 $sql = "UPDATE `PmMaterialIssueBreakUp` SET
    // `Warehouse`='$location',
    // `VarietyID`='$txtVarietyId',
    // `PackingStyleID`=$packing_style,
    // `Brand`='$brand',
    // `Quantity`=$receivedQty,
    // `Purpose`='$purpose',
    // `Attachment`='$attachment',
    // `Remarks`='$remark'
    // WHERE BreakUpId=$indent_id";

    //                 $stmt = $this->conn->prepare($sql);
    //                 if ($stmt->execute()) {
    //                     $getSql = "SELECT Id,AvailableQuantity from PmProformaAvailableData WHERE VarietyID=$txtVarietyId";
    //                     $stmtGetSql = $this->conn->prepare($getSql);
    //                     $stmtGetSql->execute();
    //                     $AvailableData = $stmtGetSql->fetch(PDO::FETCH_ASSOC);
    //                     if ($AvailableData && is_array($AvailableData)) {
    //                         $oldReceivedQty = $rowGetMi['Quantity'];
    //                         $availableQty = $AvailableData['AvailableQuantity'] - $receivedQty + $oldReceivedQty;
    //                         $sqlAvailableData = "UPDATE PmProformaAvailableData SET `VarietyID`=$txtVarietyId, `AvailableQuantity`=$availableQty WHERE Id=" + $AvailableData['Id'];
    //                     }
    //                     $stmtAvailableData = $this->conn->prepare($sqlAvailableData);
    //                     $stmtAvailableData->execute();
    //                     $error .= "";
    //                 } else {
    //                     $error .= $stmt->errorInfo()[2];
    //                 }
    //                 $stmt = null;
    //                 $sql = "";
    //             }
    //         }
    //         // echo $sql;exit;
    //         if ($error == "") {

    //             $Data['Error'] = null;
    //             $Data['status'] = "success";
    //             $Data['Msg'] = "Data Saved Successfully";
    //             $Data['Data'] = $this->loadMaterialIssues($RequestBody);
    //         } else {
    //             $Data['Error'] = $error;
    //             $Data['status'] = "fail";
    //             $Data['Msg'] = "Error while saving data";
    //             $Data['Data'] = [];
    //         }


    //         return $Data;
    //     }




    public function postMaterialIssue($RequestBody)
    {
        $material_id = isset($RequestBody['indent_id']) ? $RequestBody['indent_id'] : '';
        $po_no = isset($RequestBody['po_no']) ? $RequestBody['po_no'] : 0;
        $pdate = isset($RequestBody['pdate']) ? $RequestBody['pdate'] : '';
        $doc_no = isset($RequestBody['doc_no']) ? $RequestBody['doc_no'] : '';
        $narration = isset($RequestBody['narration']) ? $RequestBody['narration'] : '';
        $pro = isset($RequestBody['pro']) ? $RequestBody['pro'] : 0;
        $breakUp = (isset($RequestBody['breakUp'])) ? $RequestBody['breakUp'] : [];

        $existingSnapshot = null;
        if ($material_id != '') {
            $existingSnapshot = $this->auditLogger->fetchRowById('PmMaterialIssue', 'ID', $material_id);
        }

        // ---- Header Insert / Update ----
        if ($material_id == '') {
            $sql = "INSERT INTO `PmMaterialIssue`(`DocNo`, `Date`, `ProNo`, `Narration`)
                VALUES ($doc_no,'$pdate',$pro,'$narration')";
        } else {
            $sql = "UPDATE `PmMaterialIssue` SET `DocNo`=$doc_no,
                `Date`='$pdate', `ProNo`=$pro, `Narration`='$narration'
                WHERE Id=$material_id";
        }

        $stmt = $this->conn->prepare($sql);
        if (!$stmt->execute()) {
            $error = $stmt->errorInfo();
            return [
                'Error' => $error[2],
                'status' => 'fail',
                'Msg' => 'Error while saving header data',
                'Data' => []
            ];
        }

        $LastID = $material_id == '' ? $this->conn->lastInsertId() : $material_id;

        $error = "";

        // ---- Process BreakUp Rows ----
        foreach ($breakUp as $item) {
            $BreakUpId   = isset($item['BreakUpId']) ? (int)$item['BreakUpId'] : 0;
            $txtVarietyId = isset($item['txtVarietyId']) ? (int)$item['txtVarietyId'] : 0;
            $receivedQty = isset($item['received_qty']) ? (float)$item['received_qty'] : 0;
            $location    = isset($item['location']) ? $item['location'] : '';
            $packing_style = isset($item['packing_style']) ? (int)$item['packing_style'] : 0;
            $brand       = isset($item['brand']) ? (int)$item['brand'] : 0;
            $purpose     = isset($item['purpose']) ? $item['purpose'] : '';
            $remark      = isset($item['remark']) ? $item['remark'] : '';
            $grade      = isset($item['grade']) ? $item['grade'] : '';
            $freezing      = isset($item['freezing']) ? $item['freezing'] : '';
            $attachment  = isset($item['attachment']) ? $item['attachment'] : '';

            $existingBreakup = null;

            // Get old quantity if updating
            $oldReceivedQty = 0;
            if ($BreakUpId > 0) {
                $existingBreakup = $this->auditLogger->fetchRowById('PmMaterialIssueBreakUp', 'BreakUpId', $BreakUpId);
                $sqlGetOld = "SELECT Quantity FROM PmMaterialIssueBreakUp WHERE BreakUpId = $BreakUpId";
                $stmtOld = $this->conn->prepare($sqlGetOld);
                $stmtOld->execute();
                $rowOld = $stmtOld->fetch(PDO::FETCH_ASSOC);
                $oldReceivedQty = $rowOld['Quantity'] ;
            }

            // Insert or Update BreakUp
            if ($BreakUpId == 0) {
                $sql = "INSERT INTO `PmMaterialIssueBreakUp`
                (`MaterialIssueId`, `Warehouse`, `VarietyID`, `PackingStyleID`, `Brand`, `Quantity`, `Purpose`, `Attachment`, `Remarks`, `Grade`, `Freezing`)
                VALUES
                ($LastID, '$location', $txtVarietyId, $packing_style, $brand, $receivedQty, '$purpose', '$attachment', '$remark', '$grade', '$freezing')";
            } else {
                $sql = "UPDATE `PmMaterialIssueBreakUp` SET
                `MaterialIssueId` = $LastID,
                `Warehouse` = '$location',
                `VarietyID` = $txtVarietyId,
                `PackingStyleID` = $packing_style,
                `Brand` = $brand,
                `Quantity` = $receivedQty,
                `Purpose` = '$purpose',
                `Attachment` = '$attachment',
                `Remarks` = '$remark',
                `Grade` = '$grade',
                `Freezing` = '$freezing'

                WHERE BreakUpId = $BreakUpId";
            }

            $stmt = $this->conn->prepare($sql);
            if (!$stmt->execute()) {
                $errInfo = $stmt->errorInfo();
                $error .= $errInfo[2] ;
                continue;
            }

            $savedBreakUpId = ($BreakUpId == 0) ? $this->conn->lastInsertId() : $BreakUpId;
            $savedBreakup = $this->auditLogger->fetchRowById('PmMaterialIssueBreakUp', 'BreakUpId', $savedBreakUpId);
            if ($BreakUpId == 0) {
                $this->auditLogger->logInsert('PmMaterialIssueBreakUp', $savedBreakUpId, $savedBreakup, $item, [
                    'ReferenceNo' => $doc_no,
                    'Remarks' => 'Inventory packaging material issue breakup created',
                ]);
            } else {
                $this->auditLogger->logUpdate('PmMaterialIssueBreakUp', $savedBreakUpId, $existingBreakup, $savedBreakup, $item, [
                    'ReferenceNo' => $doc_no,
                    'Remarks' => 'Inventory packaging material issue breakup updated',
                ]);
            }

            // ---- Update Available Quantity ----
            $getSql = "SELECT Id, AvailableQuantity FROM PmProformaAvailableData WHERE VarietyID = $txtVarietyId";
            $stmtGetSql = $this->conn->prepare($getSql);
            $stmtGetSql->execute();
            $AvailableData = $stmtGetSql->fetch(PDO::FETCH_ASSOC);
            if ($AvailableData && is_array($AvailableData)) {
                $newAvailableQty = $AvailableData['AvailableQuantity'] - $receivedQty + $oldReceivedQty;
                $sqlAvailableData = "UPDATE PmProformaAvailableData SET AvailableQuantity = $newAvailableQty WHERE Id = " . $AvailableData['Id'];
                $stmtAvailableData = $this->conn->prepare($sqlAvailableData);
                $stmtAvailableData->execute();
            }
        }

        // ---- Audit log for header ----
        $savedSnapshot = $this->auditLogger->fetchRowById('PmMaterialIssue', 'ID', $LastID);
        if ($existingSnapshot === null) {
            $this->auditLogger->logInsert('PmMaterialIssue', $LastID, $savedSnapshot, $RequestBody, [
                'ReferenceNo' => $doc_no,
                'Remarks' => 'Inventory packaging material issue created',
            ]);
        } else {
            $this->auditLogger->logUpdate('PmMaterialIssue', $LastID, $existingSnapshot, $savedSnapshot, $RequestBody, [
                'ReferenceNo' => $doc_no,
                'Remarks' => 'Inventory packaging material issue updated',
            ]);
        }

        // ---- Return Response ----
        if ($error == "") {
            return [
                'Error' => null,
                'status' => 'success',
                'Msg' => 'Data Saved Successfully',
                'Data' => $this->loadMaterialIssues($RequestBody)
            ];
        } else {
            return [
                'Error' => $error,
                'status' => 'fail',
                'Msg' => 'Error while saving data',
                'Data' => []
            ];
        }
    }






    public function getMaterialIssue($RequestBody)
    {
        $MaterialIssue_id = isset($RequestBody['MaterialIssue_id']) ? (int)$RequestBody['MaterialIssue_id'] : 0;
        if (!$MaterialIssue_id) {
            return [
                "Error" => "MaterialIssue_id missing",
                "status" => "fail",
                "Msg" => "MaterialIssue_id is required",
                "Data" => []
            ];
        }

        // 1️⃣ Fetch header
        $stmt = $this->conn->prepare("SELECT * FROM PmMaterialIssue WHERE ID = $MaterialIssue_id");
        $stmt->execute();
        $header = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2️⃣ Fetch breakup rows directly
        $stmt = $this->conn->prepare("
        SELECT * FROM PmMaterialIssueBreakUp WHERE MaterialIssueId = $MaterialIssue_id
    ");
        $stmt->execute();
        $breakups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3️⃣ Combine header + breakup
        $data = $header ?: [];
        $data['breakUp'] = $breakups ?: [];

        return [
            "Error" => null,
            "status" => "success",
            "Msg" => $header ? "Success" : "No Data found",
            "Data" => $data
        ];
    }








    // public function deleteMaterialIssue($RequestBody)
    // {
    //     $MaterialIssue_id = isset($RequestBody['MaterialIssue_id']) ? $RequestBody['MaterialIssue_id'] : '';
    //     $stmt = $this->conn->prepare("DELETE FROM PmMaterialIssue WHERE Id=$MaterialIssue_id");

    //     if ($stmt->execute()) {
    //         $stmt = null;
    //         $stmt = $this->conn->prepare("DELETE FROM PmMaterialIssueBreakUp WHERE MaterialId=$MaterialIssue_id");
    //         if ($stmt->execute()) {
    //             $Data['Error'] = null;
    //             $Data['status'] = "success";
    //             $Data['Msg'] = "Data deleted successfully";
    //             $Data['GsProformaOrder_id'] = $MaterialIssue_id;
    //             $Data['Data'] = $this->loadMaterialIssues($RequestBody);
    //         } else {
    //             $error = $stmt->errorInfo();
    //             $Data['Error'] = $error[2];
    //             $Data['status'] = "fail";
    //             $Data['Msg'] = "Error deleting data";
    //             $Data['Data'] = [];
    //         }
    //     } else {
    //         $error = $stmt->errorInfo();
    //         $Data['Error'] = $error[2];
    //         $Data['status'] = "fail";
    //         $Data['Msg'] = "Error deleting data";
    //         $Data['Data'] = [];
    //     }

    //     return $Data;
    // }


    public function deleteMaterialIssue($RequestBody)
    {
        $MaterialIssue_id = isset($RequestBody['MaterialIssue_id']) ? $RequestBody['MaterialIssue_id'] : '';
        $existingHeader = $this->auditLogger->fetchRowById('PmMaterialIssue', 'ID', $MaterialIssue_id);
        $existingBreakups = $this->auditLogger->fetchRowsByConditions('PmMaterialIssueBreakUp', [
            'MaterialIssueId' => $MaterialIssue_id,
        ]);

        // delete parent first
        $stmt = $this->conn->prepare("DELETE FROM PmMaterialIssue WHERE Id = $MaterialIssue_id");

        if ($stmt->execute()) {
            $stmt = null;
            // delete children using the new MaterialIssueId column
            $stmt = $this->conn->prepare("DELETE FROM PmMaterialIssueBreakUp WHERE MaterialIssueId = $MaterialIssue_id");
            if ($stmt->execute()) {
                foreach ($existingBreakups as $existingBreakup) {
                    $breakUpId = isset($existingBreakup['BreakUpId']) ? $existingBreakup['BreakUpId'] : null;
                    $this->auditLogger->logDelete('PmMaterialIssueBreakUp', $breakUpId, $existingBreakup, $RequestBody, [
                        'ReferenceNo' => isset($existingHeader['DocNo']) ? $existingHeader['DocNo'] : $MaterialIssue_id,
                        'Remarks' => 'Inventory packaging material issue breakup deleted',
                    ]);
                }
                $this->auditLogger->logDelete('PmMaterialIssue', $MaterialIssue_id, $existingHeader, $RequestBody, [
                    'ReferenceNo' => isset($existingHeader['DocNo']) ? $existingHeader['DocNo'] : $MaterialIssue_id,
                    'Remarks' => 'Inventory packaging material issue deleted',
                ]);
                $Data['Error'] = null;
                $Data['status'] = "success";
                $Data['Msg'] = "Data deleted successfully";
                $Data['GsProformaOrder_id'] = $MaterialIssue_id;
                $Data['Data'] = $this->loadMaterialIssues($RequestBody);
            } else {
                $error = $stmt->errorInfo();
                $Data['Error'] = $error[2] ;
                $Data['status'] = "fail";
                $Data['Msg'] = "Error deleting data";
                $Data['Data'] = [];
            }
        } else {
            $error = $stmt->errorInfo();
            $Data['Error'] = $error[2] ;
            $Data['status'] = "fail";
            $Data['Msg'] = "Error deleting data";
            $Data['Data'] = [];
        }

        return $Data;
    }


  public function loadMaterialIssues($RequestBody)
{
    $sql = "SELECT 
                PM.ID AS Id,
                PM.ProNo AS PoNo,
                PM.DocNo,
                PM.Date 
            FROM PmMaterialIssue AS PM 
            ORDER BY PM.ID DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    $MaterialIssueList = [];
    $i = 0;

    while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $ArrayMaterialIssue['SNo'] = ++$i;
        $ArrayMaterialIssue['MaterialIssueId'] = $dataRow['Id'];
        $ArrayMaterialIssue['PoNo'] = $dataRow['PoNo'];
        $ArrayMaterialIssue['DocNo'] = $dataRow['DocNo'];  // <-- NOW IT WORKS
        $ArrayMaterialIssue['Date'] = $dataRow['Date'];

        $MaterialIssueList[] = $ArrayMaterialIssue;
    }

    $Data['Error'] = null;
    $Data['status'] = "success";
    $Data['Msg'] = "Success";
    $Data['Data'] = $MaterialIssueList;

    return $Data;
}

    public function getMaterialIssueForView($RequestBody)
{
    $MaterialIssueId = $RequestBody['MaterialIssue_id'] ;

    if (empty($MaterialIssueId)) {
        return [
            "status" => "fail",
            "Msg" => "Material Issue ID missing",
            "Data" => []
        ];
    }

    try {
        // 1️⃣ HEADER
        $stmt = $this->conn->prepare("
            SELECT ID, DocNo, ProNo, Date, Narration 
            FROM PmMaterialIssue 
            WHERE ID = $MaterialIssueId
        ");
        $stmt->execute();
        $header = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$header) {
            return [
                "status" => "fail",
                "Msg" => "No Material Issue found",
                "Data" => []
            ];
        }

        // 2️⃣ BREAKUP
        $stmt = $this->conn->prepare("
            SELECT * 
            FROM PmMaterialIssueBreakUp 
            WHERE MaterialIssueId = $MaterialIssueId
        ");
        $stmt->execute();
        $breakup = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3️⃣ MASTER DATA
        $packingStyles = [];
        $stmt = $this->conn->query("SELECT PCKID, PackingStyle FROM AdminPackingStyles WHERE Status=1");
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $packingStyles[$r['PCKID']] = $r['PackingStyle'];
        }

        $brands = [];
        $stmt = $this->conn->query("SELECT BrandID, BrandName FROM ScrmBrandsMaster WHERE Status=1");
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $brands[$r['BrandID']] = $r['BrandName'];
        }

        $varieties = [];
        $stmt = $this->conn->query("SELECT VarietyID, FullName FROM ScrmPIVarietyList WHERE Status=1");
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $varieties[$r['VarietyID']] = $r['FullName'];
        }

        // 4️⃣ MAP MASTER NAMES INTO BREAKUP
        foreach ($breakup as &$row) {
            $row['VarietyName']      = $varieties[$row['VarietyID']];
            $row['PackingStyleName'] = $packingStyles[$row['PackingStyleID']] ;
            $row['BrandName']        = $brands[$row['Brand']] ;
            $row['GradeName']        = $row['Grade'] ;
        }

        // 5️⃣ ATTACH BREAKUP TO HEADER
        $header['breakUp'] = $breakup;

        return [
            "status" => "success",
            "Msg" => "Success",
            "Data" => $header
        ];

    } catch (PDOException $e) {
        return [
            "status" => "fail",
            "Msg" => $e->getMessage(),
            "Data" => []
        ];
    }
}

}
