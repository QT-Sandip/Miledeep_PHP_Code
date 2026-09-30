<?php
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class IndentDetailsController
{
    private $conn;
    private $Error = null;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
    }

    public function getAvailableQuantity($RequestBody)
    {
        $GeneralItemId = isset($RequestBody['GeneralItemId']) ? $RequestBody['GeneralItemId'] : 0;
        $getSql = "SELECT Id,AvailableQuantity from GsAvailableData WHERE GeneralItemId=$GeneralItemId";
        $stmtGetSql = $this->conn->prepare($getSql);
        $stmtGetSql->execute();
        $AvailableData = $stmtGetSql->fetch(PDO::FETCH_ASSOC);

        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = "Success";
        $Data['Data'] = $AvailableData;
        return $Data;
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
    // public function postIndentDetails($RequestBody)
//     {
//         // echo print_r($RequestBody);exit;
//         // $po_no = isset($RequestBody['po_no']) ? $RequestBody['po_no'] : 0;
//         $IndentDetailsId = isset($RequestBody['IndentDetailsId']) ? $RequestBody['IndentDetailsId'] : '';
//         $pdate = isset($RequestBody['pdate']) ? $RequestBody['pdate'] : '';
//         $doc_no = isset($RequestBody['doc_no']) ? $RequestBody['doc_no'] : '';
//         $narration = isset($RequestBody['narration']) ? $RequestBody['narration'] : '';
//         $department = isset($RequestBody['department']) ? $RequestBody['department'] : 0;
//         $breakUp = (isset($RequestBody['breakUp'])) ? $RequestBody['breakUp'] : [];

    //         // $item = (isset($RequestBody['item'])) ? $RequestBody['item'] : [];
//         // $received_qty = isset($RequestBody['received_qty']) ? $RequestBody['received_qty'] : '';
//         // $location = isset($RequestBody['location']) ? $RequestBody['location'] : '';
//         $LastID = $IndentDetailsId;
//         if ($IndentDetailsId == '') {
//             $sql = "INSERT INTO IndentDetailsGs (`DocNo`, `Date`, `Narration`, `Department`) VALUES ('$doc_no','$pdate','$narration',$department)";

    //         } else {
//             $sql = "UPDATE IndentDetailsGs SET
//             `DocNo`='$doc_no',`Date`='$pdate',`Narration`='$narration',`Department`=$department
//             WHERE Id=$IndentDetailsId";
//         }

    //         $stmt = $this->conn->prepare($sql);
//         if ($stmt->execute()) {
//             $LastID = $IndentDetailsId == '' ? $this->conn->lastInsertId() : $IndentDetailsId;
//         } else {
//             $error = $stmt->errorInfo();
//             $Data['Error'] = $error[2];
//             $Data['status'] = "fail";
//             $Data['Msg'] = "Error while saving Indent data";
//             $Data['Data'] = [];
//             return $Data;
//         }
//         $stmt = null;
//         $sql = "";

    //         foreach ($breakUp as $item) {
//             // Access individual fields inside each breakup entry
//             $attachment = isset($item['attachment']) ? $item['attachment'] : '';
//             $itemName = isset($item['item']) ? $item['item'] : 0;
//             $receivedQty = isset($item['received_qty']) ? $item['received_qty'] : 0;
//             $purpose = isset($item['purpose']) ? $item['purpose'] : '';
//             $remark = isset($item['remark']) ? $item['remark'] : '';
//             $indent_id = isset($item['indent']) ? $item['indent'] : '';
//             // $uom = isset($item['uom']) ? $item['uom'] : '';

    //             if ($indent_id == '') {
//                 $sql = "INSERT INTO IndentDetailsBreakUpGs (IndentId, `GeneralItemId`, `Quantity`,`Remarks`, `Attachment`,`Purpose`) VALUES ($LastID,$itemName,$receivedQty,'$remark','$attachment','$purpose')";
//                 // echo $sql;exit;
//                 $stmt = $this->conn->prepare($sql);
//                 if ($stmt->execute()) {
//                     $error .= "";
//                 } else {
//                     $error .= $stmt->errorInfo()[2];
//                 }
//                 $stmt = null;
//                 $sql = "";
//             } else {
//                 $sql = "UPDATE IndentDetailsBreakUpGs SET
//                 `IndentId`=$LastID,
//                 `GeneralItemId`=$itemName,`Quantity`=$receivedQty,`Purpose`='$purpose',`Remarks`='$remark',`Attachment`='$attachment'
//                 WHERE BreakUpId=$indent_id";
//                 $stmt = $this->conn->prepare($sql);
//                 if ($stmt->execute()) {
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
//             $Data['Data'] = $this->loadIndentDetails($RequestBody);
//         } else {
//             $Data['Error'] = $error;
//             $Data['status'] = "fail";
//             $Data['Msg'] = "Error while saving Indent data";
//             $Data['Data'] = [];
//         }


    //         return $Data;
//     }

    public function postIndentDetails($RequestBody)
    {
        $IndentDetailsId = isset($RequestBody['IndentDetailsId']) ? intval($RequestBody['IndentDetailsId']) : 0;
        $pdate = isset($RequestBody['pdate']) ? $RequestBody['pdate'] : '';
        $doc_no = isset($RequestBody['doc_no']) ? $RequestBody['doc_no'] : '';
        $narration = isset($RequestBody['narration']) ? $RequestBody['narration'] : '';
        $department = isset($RequestBody['department']) ? intval($RequestBody['department']) : 0;
        $breakUp = isset($RequestBody['breakUp']) ? $RequestBody['breakUp'] : [];

        $error = '';

        try {
            $this->conn->beginTransaction();
            $existingIndentSnapshot = $IndentDetailsId > 0 ? $this->getIndentAuditSnapshot($IndentDetailsId) : null;
            $existingBreakups = $IndentDetailsId > 0 ? $this->auditLogger->fetchRowsByConditions('IndentDetailsBreakUpGs', ['IndentId' => $IndentDetailsId]) : [];
            $existingBreakupsById = [];
            foreach ($existingBreakups as $existingBreakup) {
                if (isset($existingBreakup['BreakUpId'])) {
                    $existingBreakupsById[(int) $existingBreakup['BreakUpId']] = $existingBreakup;
                }
            }

            // -------------------- HANDLE HEADER --------------------
            if ($IndentDetailsId <= 0) {
                // Insert new indent
                $sql = "INSERT INTO IndentDetailsGs (DocNo, Date, Narration, Department,`Status`)
                    VALUES (:DocNo, :Date, :Narration, :Department,0)";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    ':DocNo' => $doc_no,
                    ':Date' => $pdate,
                    ':Narration' => $narration,
                    ':Department' => $department
                ]);
                $LastID = $this->conn->lastInsertId();
            } else {
                // Update existing indent
                $sql = "UPDATE IndentDetailsGs SET DocNo=:DocNo, Date=:Date, Narration=:Narration, Department=:Department,`Status`=0
                    WHERE Id=:Id";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    ':DocNo' => $doc_no,
                    ':Date' => $pdate,
                    ':Narration' => $narration,
                    ':Department' => $department,
                    ':Id' => $IndentDetailsId
                ]);
                $LastID = $IndentDetailsId;
            }

            // -------------------- HANDLE BREAKUP ITEMS --------------------
            $sqlInsert = "INSERT INTO IndentDetailsBreakUpGs 
            (IndentId, GeneralItemId, Quantity, Remarks, Attachment, Purpose)
            VALUES (:IndentId, :GeneralItemId, :Quantity, :Remarks, :Attachment, :Purpose)";

            $sqlUpdate = "UPDATE IndentDetailsBreakUpGs SET 
            IndentId=:IndentId, 
            GeneralItemId=:GeneralItemId, 
            Quantity=:Quantity, 
            Remarks=:Remarks, 
            Attachment=:Attachment, 
            Purpose=:Purpose 
            WHERE BreakUpId=:BreakUpId";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtUpdate = $this->conn->prepare($sqlUpdate);

            // foreach ($breakUp as $item) {
            //     $itemName = isset($item['item']) ? intval($item['item']) : 0;
            //     if ($itemName <= 0)
            //         continue; // skip invalid items

            //     $receivedQty = isset($item['received_qty']) ? floatval($item['received_qty']) : 0;
            //     $purpose = isset($item['purpose']) ? $item['purpose'] : '';
            //     $remark = isset($item['remark']) ? $item['remark'] : '';
            //     $attachment = isset($item['attachment']) ? $item['attachment'] : '';
            //     // $breakUpId   = isset($item['indent']) ? intval($item['indent']) : 0;
            //     $breakUpId = isset($item['BreakUpId']) ? intval($item['BreakUpId']) : 0;


            //     if ($breakUpId > 0) {
            //         // Update existing breakup row
            //         $stmtUpdate->execute([
            //             ':IndentId' => $LastID,
            //             ':GeneralItemId' => $itemName,
            //             ':Quantity' => $receivedQty,
            //             ':Remarks' => $remark,
            //             ':Attachment' => $attachment,
            //             ':Purpose' => $purpose,
            //             ':BreakUpId' => $breakUpId
            //         ]);
            //     } else {
            //         // Insert new breakup row
            //         $stmtInsert->execute([
            //             ':IndentId' => $LastID,
            //             ':GeneralItemId' => $itemName,
            //             ':Quantity' => $receivedQty,
            //             ':Remarks' => $remark,
            //             ':Attachment' => $attachment,
            //             ':Purpose' => $purpose
            //         ]);
            //     }
            // }

            foreach ($breakUp as $item) {
                
                $itemName = isset($item['item']) ? intval($item['item']) : 0;
                if ($itemName <= 0) continue;

                $receivedQty = isset($item['received_qty']) ? floatval($item['received_qty']) : 0;

                $purpose = isset($item['purpose']) ? $item['purpose'] : '';
                $remark  = isset($item['remark']) ? $item['remark'] : '';
                $attachment = isset($item['attachment']) ? $item['attachment'] : '';

                $breakUpId = isset($item['BreakUpId']) ? intval($item['BreakUpId']) : 0;

                if ($breakUpId > 0) {
                    $existingBreakup = isset($existingBreakupsById[$breakUpId]) ? $existingBreakupsById[$breakUpId] : null;
                    $stmtUpdate->execute([
                        ':IndentId' => $LastID,
                        ':GeneralItemId' => $itemName,
                        ':Quantity' => $receivedQty,
                        ':Remarks' => $remark,
                        ':Attachment' => $attachment,
                        ':Purpose' => $purpose,
                        ':BreakUpId' => $breakUpId
                    ]);
                    $updatedBreakup = $this->auditLogger->fetchRowById('IndentDetailsBreakUpGs', 'BreakUpId', $breakUpId);
                    $this->auditLogger->logUpdate('IndentDetailsBreakUpGs', $breakUpId, $existingBreakup, $updatedBreakup, $item, [
                        'ReferenceNo' => $doc_no,
                        'Remarks' => 'Inventory indent breakup updated',
                    ]);

                } else {

                    $stmtInsert->execute([
                        ':IndentId' => $LastID,
                        ':GeneralItemId' => $itemName,
                        ':Quantity' => $receivedQty,
                        ':Remarks' => $remark,
                        ':Attachment' => $attachment,
                        ':Purpose' => $purpose
                    ]);
                    $breakUpRecordId = $this->conn->lastInsertId();
                    $savedBreakup = $this->auditLogger->fetchRowById('IndentDetailsBreakUpGs', 'BreakUpId', $breakUpRecordId);
                    $this->auditLogger->logInsert('IndentDetailsBreakUpGs', $breakUpRecordId, $savedBreakup, $item, [
                        'ReferenceNo' => $doc_no,
                        'Remarks' => 'Inventory indent breakup created',
                    ]);
                }
            }

            return [
                'Error' => null,
                'status' => 'success',
                'Msg' => 'Data Saved Successfully',
                'Data' => $this->loadIndentDetails(['IndentDetails_id' => $LastID])
            ];
        } catch (PDOException $e) {
            return [
                'Error' => $e->getMessage(),
                'status' => 'fail',
                'Msg' => 'Error while saving Indent data',
                'Data' => []
            ];
        }
    }

    public function updateIndentStatus($RequestBody)
    {

        $IndentDetails_id = isset($RequestBody['indentId']) ? $RequestBody['indentId'] : '';
        $existingIndent = $this->auditLogger->fetchRowById('IndentDetailsGs', 'Id', $IndentDetails_id);
        $sql = "UPDATE IndentDetailsGs SET `Status` = 1 WHERE Id=$IndentDetails_id";
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute()) {
            $updatedIndent = $this->auditLogger->fetchRowById('IndentDetailsGs', 'Id', $IndentDetails_id);
            $this->auditLogger->logUpdate('IndentDetailsGs', $IndentDetails_id, $existingIndent, $updatedIndent, $RequestBody, [
                'ReferenceNo' => isset($updatedIndent['DocNo']) ? $updatedIndent['DocNo'] : $IndentDetails_id,
                'Remarks' => 'Inventory indent status updated',
            ]);

            $Data['Error'] = null;
            $Data['status'] = "success";
            $Data['Msg'] = "Indent Updated Successfully";
            $Data['Data'] = "Indent Updated Successfully";
        } else {
            $Data['Error'] = "Indent Update Failed";
            $Data['status'] = "FAIL";
            $Data['Msg'] = "Indent Update Failed";
        }


        return $Data;
    }

    public function getIndentDetails($RequestBody)
    {

        $IndentDetails_id = isset($RequestBody['IndentDetails_id']) ? $RequestBody['IndentDetails_id'] : '';
        $sql = "SELECT * FROM IndentDetailsGs AS PM WHERE PM.Id=$IndentDetails_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $IndentDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = $IndentDetails ? "Success" : "No Data found";
        $Data['Data'] = (is_array($IndentDetails) || isset($IndentDetails)) ? $IndentDetails : [];

        return $Data;
    }

    public function deleteIndentDetails($RequestBody)
    {
        $IndentDetails_id = isset($RequestBody['IndentDetails_id']) ? $RequestBody['IndentDetails_id'] : '';
        $existingIndentSnapshot = $this->getIndentAuditSnapshot($IndentDetails_id);
        $existingBreakups = $this->auditLogger->fetchRowsByConditions('IndentDetailsBreakUpGs', ['IndentId' => $IndentDetails_id]);
        $stmt = $this->conn->prepare("DELETE FROM IndentDetailsGs WHERE Id=$IndentDetails_id");

        if ($stmt->execute()) {
            $stmt = null;
            $stmt = $this->conn->prepare("DELETE FROM IndentDetailsBreakUpGs WHERE IndentId=$IndentDetails_id");
            if ($stmt->execute()) {
                $this->auditLogger->logDelete('IndentDetailsGs', $IndentDetails_id, $existingIndentSnapshot, $RequestBody, [
                    'ReferenceNo' => isset($existingIndentSnapshot['DocNo']) ? $existingIndentSnapshot['DocNo'] : $IndentDetails_id,
                    'Remarks' => 'Inventory indent deleted',
                ]);
                $Data['Error'] = null;
                $Data['status'] = "success";
                $Data['Msg'] = "Indent deleted successfully";
                $Data['IndentDetails_id'] = $IndentDetails_id;
                $Data['Data'] = $this->loadIndentDetails($RequestBody);
            } else {
                $error = $stmt->errorInfo();
                $Data['Error'] = $error[2];
                $Data['status'] = "fail";
                $Data['Msg'] = "Error deleting Indent";
                $Data['Data'] = [];
            }
        } else {
            $error = $stmt->errorInfo();
            $Data['Error'] = $error[2];
            $Data['status'] = "fail";
            $Data['Msg'] = "Error deleting Indent";
            $Data['Data'] = [];
        }

        return $Data;
    }

    public function loadIndentDetails($RequestBody)
    {

        $sql = "SELECT PM.Id,PM.Date,PM.DocNo FROM IndentDetailsGs AS PM ORDER BY PM.Id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $IndentDetailsList = [];
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayIndentDetails['SNo'] = ++$i;
            $ArrayIndentDetails['IndentDetailsId'] = $dataRow['Id'];
            // $ArrayIndentDetails['itemName'] = $dataRow['name'];
            $ArrayIndentDetails['Date'] = $dataRow['Date'];
            $ArrayIndentDetails['DocNo'] = $dataRow['DocNo'];
            // $ArrayIndentDetails['GeneralItemId'] = $dataRow['GeneralItemId'];
            // $ArrayIndentDetails['Quantity'] = $dataRow['Quantity'];

            $IndentDetailsList[] = $ArrayIndentDetails;
        }

        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = "Success";
        $Data['Data'] = $IndentDetailsList;

        return $Data;
    }


    public function getFullIndentDetails($RequestBody)
    {
        $indentId = 0;
        if (isset($RequestBody['indent_id'])) {
            $indentId = $RequestBody['indent_id'];
        } elseif (isset($RequestBody['IndentId'])) {
            $indentId = $RequestBody['IndentId'];
        }

        // Fetch Indent header from the correct table
        $stmt = $this->conn->prepare("
        SELECT i.*, d.DepartmentName
        FROM IndentDetailsGs i
        LEFT JOIN Departments d ON i.Department = d.DepartmentId
        WHERE i.Id = :id
    ");
        $stmt->bindParam(':id', $indentId);
        $stmt->execute();
        $header = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$header) {
            return [
                'status' => 'error',
                'Msg' => 'Indent not found',
                'Data' => null
            ];
        }

        // Fetch item breakup from correct table
        $stmt = $this->conn->prepare("
        SELECT ib.*, gi.Name AS ItemName
        FROM IndentDetailsBreakUpGs ib
        LEFT JOIN GeneralItems gi ON ib.GeneralItemId = gi.GeneralItemId
        WHERE ib.IndentId = :id
    ");
        $stmt->bindParam(':id', $indentId);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'status' => 'success',
            'Msg' => 'Indent details fetched successfully',
            'Data' => [
                'DocNo' => $header['DocNo'] ,
                'Date' => $header['Date'] ,
                'Department' => $header['DepartmentName'] ,
                'Narration' => $header['Narration'] ,
                'items' => $items
            ]
        ];
    }

    private function getIndentAuditSnapshot($indentId)
    {
        $indent = $this->auditLogger->fetchRowById('IndentDetailsGs', 'Id', $indentId);
        if ($indent === null) {
            return null;
        }

        $indent['BreakUp'] = $this->auditLogger->fetchRowsByConditions('IndentDetailsBreakUpGs', ['IndentId' => $indentId]);
        return $indent;
    }


}
