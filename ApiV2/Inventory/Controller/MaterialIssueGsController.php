<?php
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class MaterialIssueGsController
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
        $sql = "SELECT ID.Id,ID.DocNo,PO.Id AS PoId,GR.Id AS NoteId FROM GoodsReceiptNote AS GR LEFT JOIN GsProformaOrder AS PO ON GR.PoID=PO.Id LEFT JOIN IndentDetailsGs AS ID ON PO.DocNo=ID.Id WHERE GR.`Status` IN (1) AND GR.Id IS NOT NULL AND PO.Id IS NOT NULL";
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

    // public function postMaterialIssue($RequestBody)
    // {
    //     // echo print_r($RequestBody);exit;
    //     $material_id = isset($RequestBody['material_id']) ? $RequestBody['material_id'] : '';
    //     $po_no = isset($RequestBody['po_no']) ? $RequestBody['po_no'] : 0;
    //     $pdate = isset($RequestBody['pdate']) ? $RequestBody['pdate'] : '';
    //     $doc_no = isset($RequestBody['doc_no']) ? $RequestBody['doc_no'] : '';
    //     $narration = isset($RequestBody['narration']) ? $RequestBody['narration'] : '';
    //     $location = isset($RequestBody['location']) ? $RequestBody['location'] : 0;
    //     $department = isset($RequestBody['department']) ? $RequestBody['department'] : 0;
    //     $breakUp = (isset($RequestBody['breakUp'])) ? $RequestBody['breakUp'] : [];

    //     if ($material_id == '') {
    //         $sql = "INSERT INTO MaterialIssueGs (`Location`, `DocNo`, `Date`, `Narration`, `Department`,`PoNo`) VALUES ($location,'$doc_no','$pdate','$narration',$department,$po_no)";
    //     } else {
    //         $sql = "UPDATE MaterialIssueGs SET
    //             `Location`=$location,`DocNo`='$doc_no',`Date`='$pdate',`Narration`='$narration',`Department`=$department,`PoNo`=$po_no
    //             WHERE Id=$material_id";
    //     }
    //     $stmt = $this->conn->prepare($sql);
    //     if ($stmt->execute()) {
    //         $LastID = $material_id == '' ? $this->conn->lastInsertId() : $material_id;
    //     } else {
    //         $error = $stmt->errorInfo();
    //         $Data['Error'] = $error[2];
    //         $Data['status'] = "fail";
    //         $Data['Msg'] = "Error while saving Price data";
    //         $Data['Data'] = [];
    //         return $Data;
    //     }
    //     // $item = (isset($RequestBody['item'])) ? $RequestBody['item'] : [];
    //     // $received_qty = isset($RequestBody['received_qty']) ? $RequestBody['received_qty'] : '';
    //     // $location = isset($RequestBody['location']) ? $RequestBody['location'] : '';

    //     $error = "";

    //     $stmt = null;
    //     $sql = "";

    //     foreach ($breakUp as $item) {
    //         // Access individual fields inside each breakup entry
    //         $attachment = isset($item['attachment']) ? $item['attachment'] : '';
    //         $itemName = isset($item['item']) ? $item['item'] : 0;
    //         $receivedQty = isset($item['received_qty']) ? $item['received_qty'] : 0;
    //         $purpose = isset($item['purpose']) ? $item['purpose'] : '';
    //         $remark = isset($item['remark']) ? $item['remark'] : '';
    //         $indent_id = isset($item['indent']) ? $item['indent'] : '';
    //         $uom = isset($item['uom']) ? $item['uom'] : '';


    //         if ($indent_id == '') {
    //             $sql = "INSERT INTO MaterialIssueBreakUpGs (`MaterialId`, `GeneralItemId`, `Quantity`,`Remarks`, `Attachment`,`Purpose`,`Units`) VALUES ($LastID,$itemName,$receivedQty,'$remark','$attachment','$purpose','$uom')";
    //             // echo $sql;exit;
    //             $stmt = $this->conn->prepare($sql);
    //             if ($stmt->execute()) {
    //                 $getSql = "SELECT Id,AvailableQuantity from GsAvailableData WHERE GeneralItemId=$itemName";
    //                 $stmtGetSql = $this->conn->prepare($getSql);
    //                 $stmtGetSql->execute();
    //                 $AvailableData = $stmtGetSql->fetch(PDO::FETCH_ASSOC);
    //                 if ($AvailableData && is_array($AvailableData)) {
    //                     $availableQty = $AvailableData['AvailableQuantity'] - $receivedQty;
    //                     $sqlAvailableData = "UPDATE GsAvailableData SET `GeneralItemId`=$itemName, `AvailableQuantity`=$availableQty WHERE Id=" + $AvailableData['Id'];
    //                 }
    //                 $stmtAvailableData = $this->conn->prepare($sqlAvailableData);
    //                 $stmtAvailableData->execute();

    //                 $error .= "";
    //             } else {
    //                 $error .= $stmt->errorInfo()[2];
    //             }
    //             $stmt = null;
    //             $sql = "";
    //         } else {
    //             $sqlGetMi = "SELECT Quantity FROM MaterialIssueBreakUpGs WHERE BreakUpId=$indent_id";
    //             $stmtGetMi = $this->conn->prepare($sqlGetMi);
    //             $stmtGetMi->execute();
    //             $rowGetMi = $stmtGetMi->fetch(PDO::FETCH_ASSOC);
    //             $sql = "UPDATE MaterialIssueBreakUpGs SET `MaterialId`=$LastID,
    //             `GeneralItemId`=$itemName,`Quantity`=$receivedQty,`Purpose`='$purpose',`Remarks`='$remark',`Attachment`='$attachment',`Units`=$uom
    //             WHERE BreakUpId=$indent_id";
    //             $stmt = $this->conn->prepare($sql);
    //             if ($stmt->execute()) {
    //                 $getSql = "SELECT Id,AvailableQuantity from GsAvailableData WHERE GeneralItemId=$itemName";
    //                 $stmtGetSql = $this->conn->prepare($getSql);
    //                 $stmtGetSql->execute();
    //                 $AvailableData = $stmtGetSql->fetch(PDO::FETCH_ASSOC);
    //                 if ($AvailableData && is_array($AvailableData)) {
    //                     $oldReceivedQty = $rowGetMi['Quantity'];
    //                     $availableQty = $AvailableData['AvailableQuantity'] - $receivedQty + $oldReceivedQty;
    //                     $sqlAvailableData = "UPDATE GsAvailableData SET `GeneralItemId`=$itemName, `AvailableQuantity`=$availableQty WHERE Id=" + $AvailableData['Id'];
    //                 }
    //                 $stmtAvailableData = $this->conn->prepare($sqlAvailableData);
    //                 $stmtAvailableData->execute();
    //                 $error .= "";
    //             } else {
    //                 $error .= $stmt->errorInfo()[2];
    //             }
    //             $stmt = null;
    //             $sql = "";
    //         }
    //     }
    //     // echo $sql;exit;
    //     if ($error == "") {

    //         $Data['Error'] = null;
    //         $Data['status'] = "success";
    //         $Data['Msg'] = "Data Saved Successfully";
    //         $Data['Data'] = $this->loadMaterialIssues($RequestBody);
    //     } else {
    //         $Data['Error'] = $error;
    //         $Data['status'] = "fail";
    //         $Data['Msg'] = "Error while saving data";
    //         $Data['Data'] = [];
    //     }


    //     return $Data;
    // }


    public function postMaterialIssue($RequestBody)
    {
        $material_id = $RequestBody['material_id'];
        $po_no = $RequestBody['po_no'];
        $pdate = $RequestBody['pdate'];
        $doc_no = $RequestBody['doc_no'];
        $narration = $RequestBody['narration'];
        $location = $RequestBody['location'];
        $department = $RequestBody['department'];
        $breakUp = $RequestBody['breakUp'];

        try {
            $existingSnapshot = null;
            if (!empty($material_id)) {
                $existingSnapshot = $this->auditLogger->fetchRowById('MaterialIssueGs', 'Id', $material_id);
            }

            // -------------------------
            // Save or update header
            // -------------------------
            if (empty($material_id)) {

                $location = addslashes($location);
                $doc_no = addslashes($doc_no);
                $pdate = addslashes($pdate);
                $narration = addslashes($narration);
                $department = addslashes($department);
                $po_no = addslashes($po_no);

                $sql = "INSERT INTO MaterialIssueGs
            (Location, DocNo, Date, Narration, Department, PoNo)
            VALUES
            ('$location', '$doc_no', '$pdate', '$narration', '$department', '$po_no')";

                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $LastID = $this->conn->lastInsertId();
            } else {

                $material_id = intval($material_id);

                $location = addslashes($location);
                $doc_no = addslashes($doc_no);
                $pdate = addslashes($pdate);
                $narration = addslashes($narration);
                $department = addslashes($department);
                $po_no = addslashes($po_no);

                $sql = "UPDATE MaterialIssueGs SET
            Location='$location',
            DocNo='$doc_no',
            Date='$pdate',
            Narration='$narration',
            Department='$department',
            PoNo='$po_no'
            WHERE Id=$material_id";

                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $LastID = $material_id;
            }


            // -------------------------
            // Get existing breakup IDs
            // -------------------------
            $existingBreakups = $this->auditLogger->fetchRowsByConditions('MaterialIssueBreakUpGs', ['MaterialId' => $LastID]);
            $existingBreakupsById = [];
            foreach ($existingBreakups as $eb) {
                if (isset($eb['BreakUpId'])) {
                    $existingBreakupsById[(int)$eb['BreakUpId']] = $eb;
                }
            }

            $sql = "SELECT BreakUpId FROM MaterialIssueBreakUpGs WHERE MaterialId = $LastID";
            $stmt = $this->conn->query($sql);
            $existingRows = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $incomingIds = [];

            // Process breakup rows
            foreach ($breakUp as $item) {

                $breakUpId = intval($item['BreakUpId']);
                $itemName = addslashes($item['item']);
                $receivedQty = addslashes($item['received_qty']);
                $purpose = addslashes($item['purpose']);
                $remark = addslashes($item['remark']);
                $uom = addslashes($item['uom']);
                $attachment = addslashes($item['attachment']);

                // Update existing row
                if ($breakUpId && in_array($breakUpId, $existingRows)) {
                    $existingBreakup = isset($existingBreakupsById[$breakUpId]) ? $existingBreakupsById[$breakUpId] : null;

                    $sql = "UPDATE MaterialIssueBreakUpGs SET
                GeneralItemId = '$itemName',
                Quantity = '$receivedQty',
                Purpose = '$purpose',
                Remarks = '$remark',
                Attachment = '$attachment',
                Units = '$uom'
                WHERE BreakUpId = $breakUpId";

                    $stmt = $this->conn->prepare($sql);
                    if ($stmt->execute()) {
                        $incomingIds[] = $breakUpId;
                        $savedBreakup = $this->auditLogger->fetchRowById('MaterialIssueBreakUpGs', 'BreakUpId', $breakUpId);
                        $this->auditLogger->logUpdate('MaterialIssueBreakUpGs', $breakUpId, $existingBreakup, $savedBreakup, $item, [
                            'ReferenceNo' => $doc_no,
                            'Remarks' => 'Inventory material issue breakup updated',
                        ]);

                        $sqlGetMi = "SELECT Quantity FROM MaterialIssueBreakUpGs WHERE BreakUpId=$breakUpId";
                        $stmtGetMi = $this->conn->prepare($sqlGetMi);
                        $stmtGetMi->execute();
                        $rowGetMi = $stmtGetMi->fetch(PDO::FETCH_ASSOC);
                        $getSql = "SELECT Id,AvailableQuantity from GsAvailableData WHERE GeneralItemId=$itemName";
                        $stmtGetSql = $this->conn->prepare($getSql);
                        $stmtGetSql->execute();
                        $AvailableData = $stmtGetSql->fetch(PDO::FETCH_ASSOC);
                        if ($AvailableData && is_array($AvailableData)) {
                            $oldReceivedQty = $rowGetMi['Quantity'];
                            $availableQty = $AvailableData['AvailableQuantity'] - $receivedQty + $oldReceivedQty;
                            $sqlAvailableData = "UPDATE GsAvailableData SET `GeneralItemId`=$itemName, `AvailableQuantity`=$availableQty WHERE Id=" + $AvailableData['Id'];
                        }
                        $stmtAvailableData = $this->conn->prepare($sqlAvailableData);
                        $stmtAvailableData->execute();
                        $error .= "";
                    } else {
                        $error .= $stmt->errorInfo()[2];
                    }
                    $stmt = null;
                    $sql = "";
                }
                // Insert new row
                else {

                    $sql = "INSERT INTO MaterialIssueBreakUpGs
                (MaterialId, GeneralItemId, Quantity, Purpose, Remarks, Attachment, Units)
                VALUES
                ($LastID, '$itemName', '$receivedQty', '$purpose', '$remark', '$attachment', '$uom')";

                    $stmt = $this->conn->prepare($sql);
                    if ($stmt->execute()) {
                        $savedBreakupId = $this->conn->lastInsertId();
                        $savedBreakup = $this->auditLogger->fetchRowById('MaterialIssueBreakUpGs', 'BreakUpId', $savedBreakupId);
                        $this->auditLogger->logInsert('MaterialIssueBreakUpGs', $savedBreakupId, $savedBreakup, $RequestBody, [
                            'ReferenceNo' => $doc_no,
                            'Remarks' => 'Inventory material issue breakup created',
                        ]);

                        $getSql = "SELECT Id,AvailableQuantity from GsAvailableData WHERE GeneralItemId=$itemName";
                        $stmtGetSql = $this->conn->prepare($getSql);
                        $stmtGetSql->execute();
                        $AvailableData = $stmtGetSql->fetch(PDO::FETCH_ASSOC);
                        if ($AvailableData) {
                            $availableQty = $AvailableData['AvailableQuantity'] - $receivedQty;
                            $sqlAvailableData = "UPDATE GsAvailableData SET `AvailableQuantity`=$availableQty WHERE Id=" . $AvailableData['Id'];
                            $stmtAvailableData = $this->conn->prepare($sqlAvailableData);
                            $stmtAvailableData->execute();
                        }

                        $error .= "";
                    } else {
                        $error .= $stmt->errorInfo()[2];
                    }
                    $stmt = null;
                    $sql = "";

                }
            }

            // -------------------------
            // Delete removed breakup rows
            // -------------------------
            $rowsToDelete = array_diff($existingRows, $incomingIds);
            if (!empty($rowsToDelete)) {
                foreach ($rowsToDelete as $delId) {
                    $deletedBreakup = isset($existingBreakupsById[$delId]) ? $existingBreakupsById[$delId] : null;
                    $this->auditLogger->logDelete('MaterialIssueBreakUpGs', $delId, $deletedBreakup, $RequestBody, [
                        'ReferenceNo' => $doc_no,
                        'Remarks' => 'Inventory material issue breakup deleted',
                    ]);
                }
                $in = implode(',', $rowsToDelete);
                $stmt = $this->conn->prepare("DELETE FROM MaterialIssueBreakUpGs WHERE BreakUpId IN ($in)");
                $stmt->execute();
            }

            $savedSnapshot = $this->auditLogger->fetchRowById('MaterialIssueGs', 'Id', $LastID);
            if ($existingSnapshot === null) {
                $this->auditLogger->logInsert('MaterialIssueGs', $LastID, $savedSnapshot, $RequestBody, [
                    'ReferenceNo' => $doc_no,
                    'Remarks' => 'Inventory material issue created',
                ]);
            } else {
                $this->auditLogger->logUpdate('MaterialIssueGs', $LastID, $existingSnapshot, $savedSnapshot, $RequestBody, [
                    'ReferenceNo' => $doc_no,
                    'Remarks' => 'Inventory material issue updated',
                ]);
            }

            return [
                'Error' => null,
                'status' => 'success',
                'Msg' => 'Material Issue saved successfully',
                'Data' => $this->loadMaterialIssues($RequestBody)
            ];

        } catch (PDOException $e) {
            $this->conn->rollBack();
            return [
                'Error' => $e->getMessage(),
                'status' => 'fail',
                'Msg' => 'Error saving Material Issue',
                'Data' => []
            ];
        }
    }


    public function getMaterialIssue($RequestBody)
    {

        $MaterialIssue_id = isset($RequestBody['MaterialIssue_id']) ? $RequestBody['MaterialIssue_id'] : '';
        $sql = "SELECT * FROM MaterialIssueGs AS PM WHERE PM.Id=$MaterialIssue_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $MaterialIssue = $stmt->fetch(PDO::FETCH_ASSOC);
        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = $MaterialIssue ? "Success" : "No Data found";
        $Data['Data'] = (is_array($MaterialIssue) || isset($MaterialIssue)) ? $MaterialIssue : [];

        return $Data;
    }

    public function deleteMaterialIssue($RequestBody)
    {
        $MaterialIssue_id = isset($RequestBody['MaterialIssue_id']) ? $RequestBody['MaterialIssue_id'] : '';
        $existingHeader = $this->auditLogger->fetchRowById('MaterialIssueGs', 'Id', $MaterialIssue_id);
        $existingBreakups = $this->auditLogger->fetchRowsByConditions('MaterialIssueBreakUpGs', [
            'MaterialId' => $MaterialIssue_id,
        ]);
        $stmt = $this->conn->prepare("DELETE FROM MaterialIssueGs WHERE Id=$MaterialIssue_id");

        if ($stmt->execute()) {
            $stmt = null;
            $stmt = $this->conn->prepare("DELETE FROM MaterialIssueBreakUpGs WHERE MaterialId=$MaterialIssue_id");
            if ($stmt->execute()) {
                foreach ($existingBreakups as $existingBreakup) {
                    $breakUpId = isset($existingBreakup['BreakUpId']) ? $existingBreakup['BreakUpId'] : null;
                    $this->auditLogger->logDelete('MaterialIssueBreakUpGs', $breakUpId, $existingBreakup, $RequestBody, [
                        'ReferenceNo' => isset($existingHeader['DocNo']) ? $existingHeader['DocNo'] : $MaterialIssue_id,
                        'Remarks' => 'Inventory material issue breakup deleted',
                    ]);
                }
                $this->auditLogger->logDelete('MaterialIssueGs', $MaterialIssue_id, $existingHeader, $RequestBody, [
                    'ReferenceNo' => isset($existingHeader['DocNo']) ? $existingHeader['DocNo'] : $MaterialIssue_id,
                    'Remarks' => 'Inventory material issue deleted',
                ]);
                $Data['Error'] = null;
                $Data['status'] = "success";
                $Data['Msg'] = "Data deleted successfully";
                $Data['GsProformaOrder_id'] = $MaterialIssue_id;
                $Data['Data'] = $this->loadMaterialIssues($RequestBody);
            } else {
                $error = $stmt->errorInfo();
                $Data['Error'] = $error[2];
                $Data['status'] = "fail";
                $Data['Msg'] = "Error deleting data";
                $Data['Data'] = [];
            }
        } else {
            $error = $stmt->errorInfo();
            $Data['Error'] = $error[2];
            $Data['status'] = "fail";
            $Data['Msg'] = "Error deleting data";
            $Data['Data'] = [];
        }

        return $Data;
    }

    public function loadMaterialIssues($RequestBody)
    {

        $sql = "SELECT PM.Id,PM.PoNo,PM.Date FROM MaterialIssueGs AS PM ORDER BY PM.Id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $MaterialIssueList = [];
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayMaterialIssue['SNo'] = ++$i;
            $ArrayMaterialIssue['MaterialIssueId'] = $dataRow['Id'];
            $ArrayMaterialIssue['PoNo'] = $dataRow['PoNo'];
            // $ArrayMaterialIssue['itemName'] = $dataRow['name'];
            $ArrayMaterialIssue['Date'] = $dataRow['Date'];
            // $ArrayMaterialIssue['GeneralItemId'] = $dataRow['GeneralItemId'];
            // $ArrayMaterialIssue['Quantity'] = $dataRow['Quantity'];

            $MaterialIssueList[] = $ArrayMaterialIssue;
        }

        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = "Success";
        $Data['Data'] = $MaterialIssueList;

        return $Data;
    }


    public function getFullMaterialIssueDetails($RequestBody)
    {
        $noteId = isset($RequestBody['NoteId']) ? (int) $RequestBody['NoteId'] : 0;
        error_log("NoteId received in getFullMaterialIssueDetails: " . $noteId);

        //  Fetch header using Id
        $stmt = $this->conn->prepare("SELECT * FROM MaterialIssueGs WHERE Id = :id");
        $stmt->bindParam(':id', $noteId, PDO::PARAM_INT);
        $stmt->execute();
        $header = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$header) {
            return [
                'status' => 'error',
                'Msg' => 'Material Issue not found',
                'Data' => null
            ];
        }

        // Fetch breakup details by MaterialId
        $stmt = $this->conn->prepare("
        SELECT m.*, i.Name AS ItemName
        FROM MaterialIssueBreakUpGs m
        LEFT JOIN GeneralItems i ON m.GeneralItemId = i.GeneralItemId
        WHERE m.MaterialId = :id
    ");
        $stmt->bindParam(':id', $noteId, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'status' => 'success',
            'Msg' => 'Material Issue details fetched successfully',
            'Data' => [
                'DocNo' => $header['DocNo'],
                'Date' => $header['Date'],
                'Location' => $header['Location'],
                'Department' => $header['Department'],
                'Narration' => $header['Narration'],
                'PoNo' => $header['PoNo'],
                'breakUp' => $items
            ]
        ];
    }




}
