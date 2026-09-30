<?php
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class GoodsReceiptNoteGsController
{
    private $conn;
    private $Error = null;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
    }

    public function getPoQuantity($RequestBody)
    {
        $PoId = isset($RequestBody['PoId']) ? $RequestBody['PoId'] : 0;
        $itemId = isset($RequestBody['itemId']) ? $RequestBody['itemId'] : 0;
        $getSql = "SELECT GB.Quantity,GB.BreakUpId from GsProformaOrderBreakUp AS GB LEFT JOIN GsProformaOrder AS GP ON GB.PoId=GP.Id  WHERE GP.Id=$PoId AND GB.GeneralItemId=$itemId";
        $stmtGetSql = $this->conn->prepare($getSql);
        $stmtGetSql->execute();
        $AvailableData = $stmtGetSql->fetch(PDO::FETCH_ASSOC);

        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = "Success";
        $Data['Data'] = $AvailableData;
        return $Data;
    }
    public function getIndentsList()
    {
        $sql = "SELECT ID.Id,ID.DocNo,PO.Supplier,PO.Id AS PoId FROM GsProformaOrder AS PO LEFT JOIN IndentDetailsGs AS ID ON PO.DocNo=ID.Id WHERE PO.`Status` IN (1) AND PO.Id IS NOT NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $Indents = [];
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Indent['SNo'] = ++$i;
            $Indent['Id'] = $dataRow['Id'];
            $Indent['DocNo'] = $dataRow['DocNo'];
            $Indent['SupplierId'] = $dataRow['Supplier'];
            $Indent['PoId'] = $dataRow['PoId'];

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
        $sql = "SELECT * FROM GsProformaOrderBreakUp WHERE PoId=$indent_id";
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
    // public function postGoodsReceiptNote($RequestBody)
    // {
    //     // echo print_r($RequestBody);exit;
    //     $note_id = isset($RequestBody['note_id']) ? $RequestBody['note_id'] : 0;
    //     $po_no = isset($RequestBody['po_no']) ? $RequestBody['po_no'] : '';
    //     $pdate = isset($RequestBody['pdate']) ? $RequestBody['pdate'] : 0;
    //     $doc_no = isset($RequestBody['doc_no']) ? $RequestBody['doc_no'] : 0;
    //     $supplier = isset($RequestBody['supplier']) ? $RequestBody['supplier'] : 0;
    //     $invoice_no = isset($RequestBody['invoice_no']) ? $RequestBody['invoice_no'] : '';
    //     $invoice_date = isset($RequestBody['invoice_date']) ? $RequestBody['invoice_date'] : '';
    //     $freight = isset($RequestBody['freight']) ? $RequestBody['freight'] : '';
    //     $narration = isset($RequestBody['narration']) ? $RequestBody['narration'] : '';
    //     $breakUp = (isset($RequestBody['breakUp'])) ? $RequestBody['breakUp'] : [];

    //     $LastID = $note_id;
    //     if ($note_id == '') {
    //         $sql = "INSERT INTO GoodsReceiptNote (`PoID`, `InvoiceNo`, `InvoiceDate`, `Narration`, `FreightType`, `VendorId`, `Date`,`PoNo`) VALUES ($doc_no,'$invoice_no','$invoice_date','$narration','$freight',$supplier,'$pdate','$po_no')";
    //     } else {
    //         $sql = "UPDATE GoodsReceiptNote SET
    //             `PoID`=$doc_no,`InvoiceNo`='$invoice_no',`InvoiceDate`='$invoice_date',`Narration`='$narration',`FreightType`='$freight',`VendorId`=$supplier,`Date`='$pdate',`PoNo`='$po_no'
    //             WHERE Id=$note_id";
    //     }
    //     $stmt = $this->conn->prepare($sql);
    //     if ($stmt->execute()) {
    //         $LastID = $note_id == '' ? $this->conn->lastInsertId() : $note_id;
    //     } else {
    //         $error = $stmt->errorInfo();
    //         $Data['Error'] = $error[2];
    //         $Data['status'] = "fail";
    //         $Data['Msg'] = "Error while saving data";
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
    //         $itemName = (isset($item['item']) && $item['item'] != '') ? $item['item'] : 0;
    //         $receivedQty = (isset($item['received_qty']) && $item['received_qty'] != '') ? $item['received_qty'] : 0;
    //         $location = (isset($item['location']) && $item['location'] != '') ? $item['location'] : 0;
    //         $purpose = isset($item['purpose']) ? $item['purpose'] : '';
    //         $remark = isset($item['remark']) ? $item['remark'] : '';
    //         $indent_id = isset($item['indent']) ? $item['indent'] : '';


    //         if ($indent_id == '') {
    //             $sql = "INSERT INTO GoodsReceiptNoteBreakUp (`NoteId`,`GeneralItemId`, `ReceivedQuantity`, `Location`, `Purpose`, `Remarks`, `Attachment`) VALUES ($LastID,$itemName,$receivedQty,$location,'$purpose','$remark','$attachment')";

    //             $stmt = $this->conn->prepare($sql);
    //             if ($stmt->execute()) {
    //                 $getSql = "SELECT Id,AvailableQuantity from GsAvailableData WHERE GeneralItemId=$itemName";
    //                 $stmtGetSql = $this->conn->prepare($getSql);
    //                 $stmtGetSql->execute();
    //                 $AvailableData = $stmtGetSql->fetch(PDO::FETCH_ASSOC);
    //                 if ($AvailableData && is_array($AvailableData)) {
    //                     $availableQty = $AvailableData['AvailableQuantity'] + $receivedQty;
    //                     $sqlAvailableData = "UPDATE GsAvailableData SET `GeneralItemId`=$itemName, `AvailableQuantity`=$availableQty WHERE Id=" + $AvailableData['Id'];
    //                 } else {
    //                     $sqlAvailableData = "INSERT INTO GsAvailableData (`GeneralItemId`, `AvailableQuantity`) VALUES ($itemName,$receivedQty)";
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
    //             $sqlGetMi = "SELECT ReceivedQuantity FROM GoodsReceiptNoteBreakUp WHERE BreakUpId=$indent_id";
    //             $stmtGetMi = $this->conn->prepare($sqlGetMi);
    //             $stmtGetMi->execute();
    //             $rowGetMi = $stmtGetMi->fetch(PDO::FETCH_ASSOC);
    //             $sql = "UPDATE GoodsReceiptNoteBreakUp SET `NoteId`=$LastID,
    //            `GeneralItemId`=$itemName,`ReceivedQuantity`=$receivedQty,`Location`=$location,`Purpose`='$purpose',`Remarks`='$remark',`Attachment`='$attachment'
    //             WHERE BreakUpId=$indent_id";
    //             $stmt = $this->conn->prepare($sql);
    //             if ($stmt->execute()) {

    //                 $getSql = "SELECT Id,AvailableQuantity from GsAvailableData WHERE GeneralItemId=$itemName";
    //                 $stmtGetSql = $this->conn->prepare($getSql);
    //                 $stmtGetSql->execute();
    //                 $AvailableData = $stmtGetSql->fetch(PDO::FETCH_ASSOC);
    //                 if ($AvailableData && is_array($AvailableData)) {
    //                     $oldReceivedQty = $rowGetMi['ReceivedQuantity'];
    //                     $availableQty = $AvailableData['AvailableQuantity'] + $receivedQty - $oldReceivedQty;
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
    //         // echo $sql;exit;
    //         // $stmt = $this->conn->prepare($sql);
    //         // $stmt->execute();
    //         // $LastIDs[] = $indent_id == '' ? $this->conn->lastInsertId() : $indent_id;
    //     }

    //     if ($error == "") {

    //         $Data['Error'] = null;
    //         $Data['status'] = "success";
    //         $Data['Msg'] = "Data Saved Successfully";
    //         $Data['Data'] = $this->loadGoodsReceiptNotes($RequestBody);
    //     } else {
    //         $Data['Error'] = $error;
    //         $Data['status'] = "fail";
    //         $Data['Msg'] = "Error while saving data";
    //         $Data['Data'] = [];
    //     }

    //     return $Data;
    // }


    public function postGoodsReceiptNote($RequestBody)
    {
        $note_id = !empty($RequestBody['note_id']) ? $RequestBody['note_id'] : 0;
        $po_no = $RequestBody['po_no'];
        $doc_no = $RequestBody['PoId'];
        $supplier = (int) ($RequestBody['supplier']);
        $invoice_no = $RequestBody['invoice_no'];
        $code = $RequestBody['code'];
        $invoice_date = $RequestBody['invoice_date'];
        $pdate = $RequestBody['pdate'];
        $narration = $RequestBody['narration'];
        $status = $RequestBody['status'];
        $freight = $RequestBody['freight'];
        $breakUp = $RequestBody['breakUp'];

        try {
            $existingSnapshot = null;
            if ((int) $note_id > 0) {
                $existingSnapshot = $this->auditLogger->fetchRowById('GoodsReceiptNote', 'Id', $note_id);
            }

            // ------------------ INSERT ------------------
            if ($note_id == 0) {

                $sql = "INSERT INTO GoodsReceiptNote
        (PoID, PoNo, VendorId, InvoiceNo, InvoiceDate, Code, Date, Narration, Status, FreightType)
        VALUES (
            '$doc_no', 
            '$po_no', 
            '$supplier', 
            '$invoice_no', 
            '$invoice_date', 
            '$code', 
            '$pdate', 
            '$narration', 
            '$status', 
            '$freight'
        )";

            }

            // ------------------ UPDATE ------------------
            else {

                $sql = "UPDATE GoodsReceiptNote SET
        PoID = '$doc_no',
        PoNo = '$po_no',
        VendorId = '$supplier',
        InvoiceNo = '$invoice_no',
        Code = '$code',
        InvoiceDate = '$invoice_date',
        Date = '$pdate',
        Narration = '$narration',
        Status = '$status',
        FreightType = '$freight'
        WHERE Id = $note_id";


            }

            $stmt = $this->conn->prepare($sql);

            $stmt->execute();

            $LastID = ($note_id == 0) ? $this->conn->lastInsertId() : $note_id;

            // ------------------ BREAKUP LOOP ------------------
            foreach ($breakUp as $item) {

                $breakup_id = (int) ($item['indent']);
                $itemName = (int) ($item['item']);
                $receivedQty = (float) ($item['received_qty']);
                $location = $item['location'];
                $purpose = $item['purpose'];
                $remark = $item['remark'];
                $attachment = $item['attachment'];

                // NEW INSERT
                if ($breakup_id == 0) {
                    $existingBreakup = null;
                    $sql = "INSERT INTO GoodsReceiptNoteBreakUp
            (NoteId, GeneralItemId, ReceivedQuantity, Location, Purpose, Remarks, Attachment)
            VALUES
            ('$LastID', '$itemName', '$receivedQty', '$location', '$purpose', '$remark', '$attachment')";
                    // $this->conn->query($sql);
                }

                // UPDATE
                else {
                    $breakup_id = intval($breakup_id); // Secure the ID
                    $existingBreakup = $this->auditLogger->fetchRowById('GoodsReceiptNoteBreakUp', 'BreakUpId', $breakup_id);

                    $sql = "UPDATE GoodsReceiptNoteBreakUp SET
            NoteId = '$LastID',
            GeneralItemId = '$itemName',
            ReceivedQuantity = '$receivedQty',
            Location = '$location',
            Purpose = '$purpose',
            Remarks = '$remark',
            Attachment = '$attachment'
            WHERE BreakUpId = $breakup_id";

                    // $this->conn->query($sql);
                }


                $stmt = $this->conn->prepare($sql);

                $stmt->execute();

                $savedBreakupId = ($breakup_id == 0) ? $this->conn->lastInsertId() : $breakup_id;
                $savedBreakup = $this->auditLogger->fetchRowById('GoodsReceiptNoteBreakUp', 'BreakUpId', $savedBreakupId);
                if ($breakup_id == 0) {
                    $this->auditLogger->logInsert('GoodsReceiptNoteBreakUp', $savedBreakupId, $savedBreakup, $RequestBody, [
                        'ReferenceNo' => $code,
                        'Remarks' => 'Inventory goods receipt note breakup created',
                    ]);
                } else {
                    $this->auditLogger->logUpdate('GoodsReceiptNoteBreakUp', $savedBreakupId, $existingBreakup, $savedBreakup, $item, [
                        'ReferenceNo' => $code,
                        'Remarks' => 'Inventory goods receipt note breakup updated',
                    ]);
                }

                // ------------------ UPDATE AVAILABLE STOCK ------------------
                $stmtGet = $this->conn->prepare("SELECT Id, AvailableQuantity FROM GsAvailableData WHERE GeneralItemId = :gid");
                $stmtGet->execute([':gid' => $itemName]);
                $availableData = $stmtGet->fetch(PDO::FETCH_ASSOC);

                if ($availableData) {
                    $availableQty = $availableData['AvailableQuantity'] + $receivedQty;
                    $stmtUpdate = $this->conn->prepare("UPDATE GsAvailableData SET AvailableQuantity = :q WHERE Id = :id");
                    $stmtUpdate->execute([':q' => $availableQty, ':id' => $availableData['Id']]);
                } else {
                    $stmtInsert = $this->conn->prepare("INSERT INTO GsAvailableData (GeneralItemId, AvailableQuantity, VendorId) VALUES (:gid, :qty, :vid)");
                    $stmtInsert->execute([':gid' => $itemName, ':qty' => $receivedQty, ':vid' => $supplier]);
                }
            }

            $savedSnapshot = $this->auditLogger->fetchRowById('GoodsReceiptNote', 'Id', $LastID);
            if ((int) $note_id === 0) {
                $this->auditLogger->logInsert('GoodsReceiptNote', $LastID, $savedSnapshot, $RequestBody, [
                    'ReferenceNo' => $code,
                    'Remarks' => 'Inventory goods receipt note created',
                ]);
            } else {
                $this->auditLogger->logUpdate('GoodsReceiptNote', $LastID, $existingSnapshot, $savedSnapshot, $RequestBody, [
                    'ReferenceNo' => $code,
                    'Remarks' => 'Inventory goods receipt note updated',
                ]);
            }

            return [
                'status' => 'success',
                'Msg' => 'GRN Saved Successfully',
                'Error' => null,
                'Data' => $this->loadGoodsReceiptNotes($RequestBody)
            ];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return [
                'status' => 'fail',
                'Msg' => 'Error saving GRN',
                'Error' => $e->getMessage(),
                'Data' => []
            ];
        }
    }



    public function getGoodsReceiptNote($RequestBody)
    {

        $GoodsReceiptNote_id = isset($RequestBody['GoodsReceiptNote_id']) ? $RequestBody['GoodsReceiptNote_id'] : '';
        $sql = "SELECT * FROM GoodsReceiptNote AS PM WHERE PM.Id=$GoodsReceiptNote_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $GoodsReceiptNote = $stmt->fetch(PDO::FETCH_ASSOC);
        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = $GoodsReceiptNote ? "Success" : "No Data found";
        $Data['Data'] = (is_array($GoodsReceiptNote) || isset($GoodsReceiptNote)) ? $GoodsReceiptNote : [];

        return $Data;
    }

    public function deleteGoodsReceiptNote($RequestBody)
    {
        $GoodsReceiptNote_id = isset($RequestBody['GoodsReceiptNote_id']) ? $RequestBody['GoodsReceiptNote_id'] : '';
        $existingHeader = $this->auditLogger->fetchRowById('GoodsReceiptNote', 'Id', $GoodsReceiptNote_id);
        $existingBreakups = $this->auditLogger->fetchRowsByConditions('GoodsReceiptNoteBreakUp', [
            'NoteId' => $GoodsReceiptNote_id,
        ]);
        $stmt = $this->conn->prepare("DELETE FROM GoodsReceiptNote WHERE Id=$GoodsReceiptNote_id");

        if ($stmt->execute()) {
            $stmt = null;
            $stmt = $this->conn->prepare("DELETE FROM GoodsReceiptNoteBreakUp WHERE NoteId=$GoodsReceiptNote_id");
            if ($stmt->execute()) {
                foreach ($existingBreakups as $existingBreakup) {
                    $breakUpId = isset($existingBreakup['BreakUpId']) ? $existingBreakup['BreakUpId'] : null;
                    $this->auditLogger->logDelete('GoodsReceiptNoteBreakUp', $breakUpId, $existingBreakup, $RequestBody, [
                        'ReferenceNo' => isset($existingHeader['Code']) ? $existingHeader['Code'] : $GoodsReceiptNote_id,
                        'Remarks' => 'Inventory goods receipt note breakup deleted',
                    ]);
                }
                $this->auditLogger->logDelete('GoodsReceiptNote', $GoodsReceiptNote_id, $existingHeader, $RequestBody, [
                    'ReferenceNo' => isset($existingHeader['Code']) ? $existingHeader['Code'] : $GoodsReceiptNote_id,
                    'Remarks' => 'Inventory goods receipt note deleted',
                ]);
                $Data['Error'] = null;
                $Data['status'] = "success";
                $Data['Msg'] = "Data deleted successfully";
                $Data['GsProformaOrder_id'] = $GoodsReceiptNote_id;
                $Data['Data'] = $this->loadGoodsReceiptNotes($RequestBody);
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

    // public function loadGoodsReceiptNotes($RequestBody)
    // {

    //     $sql = "SELECT PM.Id,PM.VendorID,VS.VendorName,PM.PoID,PM.InvoiceNo,PM.Date FROM GoodsReceiptNote AS PM LEFT JOIN vendors AS VS ON PM.VendorId=VS.VendorID ORDER BY PM.Id DESC";
    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->execute();

    //     $GoodsReceiptNoteList = [];
    //     $i = 0;
    //     while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //         $ArrayGoodsReceiptNote['SNo'] = ++$i;
    //         $ArrayGoodsReceiptNote['GoodsReceiptNoteId'] = $dataRow['Id'];
    //         $ArrayGoodsReceiptNote['VendorID'] = $dataRow['VendorID'];
    //         $ArrayGoodsReceiptNote['VendorName'] = $dataRow['VendorName'];
    //         // $ArrayGoodsReceiptNote['itemName'] = $dataRow['name'];
    //         // $ArrayGoodsReceiptNote['GeneralItemId'] = $dataRow['GeneralItemId'];
    //         $ArrayGoodsReceiptNote['PoID'] = $dataRow['PoID'];
    //         $ArrayGoodsReceiptNote['InvoiceNo'] = $dataRow['InvoiceNo'];
    //         $ArrayGoodsReceiptNote['Date'] = $dataRow['Date'];

    //         $GoodsReceiptNoteList[] = $ArrayGoodsReceiptNote;
    //     }

    //     $Data['Error'] = null;
    //     $Data['status'] = "success";
    //     $Data['Msg'] = "Success";
    //     $Data['Data'] = $GoodsReceiptNoteList;

    //     return $Data;
    // }

    public function loadGoodsReceiptNotes($RequestBody)
    {
        $sql = "SELECT PM.Id,
               PM.VendorID,
               VS.VendorName,
               PM.PoNo,
               PM.InvoiceNo,
               PM.Code,
               PM.Date,
               PM.Status
        FROM GoodsReceiptNote AS PM
        LEFT JOIN Vendors AS VS ON PM.VendorId = VS.VendorID
        ORDER BY PM.Id DESC";


        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $GoodsReceiptNoteList = [];
        $i = 0;

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayGoodsReceiptNote['SNo'] = ++$i;
            $ArrayGoodsReceiptNote['GoodsReceiptNoteId'] = $dataRow['Id'];
            $ArrayGoodsReceiptNote['VendorID'] = $dataRow['VendorID'];
            $ArrayGoodsReceiptNote['VendorName'] = $dataRow['VendorName'];
            $ArrayGoodsReceiptNote['PoNo'] = $dataRow['PoNo'];
            $ArrayGoodsReceiptNote['InvoiceNo'] = $dataRow['InvoiceNo'];
            $ArrayGoodsReceiptNote['Code'] = $dataRow['Code'];
            $ArrayGoodsReceiptNote['Date'] = $dataRow['Date'];
            $ArrayGoodsReceiptNote['Status'] = $dataRow['Status'];

            $GoodsReceiptNoteList[] = $ArrayGoodsReceiptNote;
        }

        return [
            'Error' => null,
            'status' => 'success',
            'Msg' => 'Success',
            'Data' => $GoodsReceiptNoteList
        ];
    }



    public function getFullGrnDetails($RequestBody)
    {
        $grnId = 0;
        if (isset($RequestBody['GoodsReceiptNoteId'])) {
            $grnId = $RequestBody['GoodsReceiptNoteId'];
        } elseif (isset($RequestBody['grn_id'])) {
            $grnId = $RequestBody['grn_id'];
        }

        //  JOIN with Vendor table to get VendorName
        $stmt = $this->conn->prepare("
        SELECT grn.*, v.VendorName
        FROM GoodsReceiptNote grn
        LEFT JOIN Vendors v ON grn.VendorId = v.VendorId
        WHERE grn.Id = :id
    ");
        $stmt->bindParam(':id', $grnId);
        $stmt->execute();
        $header = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$header) {
            return ['status' => 'error', 'Msg' => 'GRN not found', 'Data' => null];
        }

        //  Fetch breakup details
        $stmt = $this->conn->prepare("
        SELECT g.*, i.Name AS ItemName
        FROM GoodsReceiptNoteBreakUp g
        LEFT JOIN GeneralItems i ON g.GeneralItemId = i.GeneralItemId
        WHERE g.NoteId = :id
    ");
        $stmt->bindParam(':id', $grnId);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //  Return properly formatted data
        return [
            'status' => 'success',
            'Msg' => 'GRN details fetched successfully',
            'Data' => [
                'DocNo' => $header['PoNo'],
                'Date' => $header['Date'],
                'SupplierID' => $header['VendorId'],
                'SupplierName' => $header['VendorName'],
                'PoNo' => $header['PoNo'],
                'InvoiceNo' => $header['InvoiceNo'],
                'Code' => $header['Code'],
                'InvoiceDate' => $header['InvoiceDate'],
                'Narration' => $header['Narration'],
                'Status' => $header['Status'],

                'breakUp' => $items
            ]
        ];
    }
}
