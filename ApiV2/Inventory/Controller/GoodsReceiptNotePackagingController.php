<?php


include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class GoodsReceiptNotePackagingController
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


    //     public function getPoQuantity($RequestBody)
    // {
    //     $PoId = isset($RequestBody['PoId']) ? (int)$RequestBody['PoId'] : 0;

    //     try {
    //         // 1️⃣ Fetch all headers for this PoID
    //         $stmtHeader = $this->conn->prepare("
    //             SELECT *
    //             FROM PmGoodsReceiptNote
    //             WHERE PoID = :poId
    //             ORDER BY PmGoodsReceiptNoteId DESC
    //         ");
    //         $stmtHeader->bindParam(':poId', $PoId, PDO::PARAM_INT);
    //         $stmtHeader->execute();
    //         $headers = $stmtHeader->fetchAll(PDO::FETCH_ASSOC);

    //         $result = [];

    //         foreach ($headers as $header) {
    //             $noteId = $header['PmGoodsReceiptNoteId'];

    //             // 2️⃣ Fetch breakups **only for this specific PmGoodsReceiptNoteId**
    //             $stmtBreakUp = $this->conn->prepare("
    //                 SELECT *
    //                 FROM PmGoodsReceiptNoteBreakUp
    //                 WHERE PmGoodsReceiptNoteId = :noteId
    //                 ORDER BY SNo ASC
    //             ");
    //             $stmtBreakUp->bindParam(':noteId', $noteId, PDO::PARAM_INT);
    //             $stmtBreakUp->execute();
    //             $breakups = $stmtBreakUp->fetchAll(PDO::FETCH_ASSOC);

    //             // 3️⃣ Attach breakups to the header
    //             $header['BreakUps'] = $breakups;

    //             $result[] = $header;
    //         }

    //         return [
    //             'status' => 'success',
    //             'Msg' => 'Success',
    //             'Data' => $result
    //         ];
    //     } catch (PDOException $e) {
    //         return [
    //             'status' => 'error',
    //             'Msg' => 'Database error: ' . $e->getMessage(),
    //             'Data' => []
    //         ];
    //     }
    // }

    public function getIndentsList()
    {
        $sql = "SELECT ID.PiID AS Id,ID.DocNo,PO.Supplier,PO.PoID AS PoId FROM PmPurchaseOrder AS PO LEFT JOIN PmProformaInvoice AS ID ON PO.ProformaID=ID.PiID WHERE PO.Status=1 AND PO.PoID IS NOT NULL";
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

    // public function getIndentsBreakUpList($RequestBody)
    // {
    //     $indent_id = isset($RequestBody['indent']) ? $RequestBody['indent'] : '';
    //     $sql = "SELECT * FROM PmPurchaseOrderBreakUp WHERE PoID=$indent_id";
    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->execute();

    //     $Indents = [];
    //     $i = 0;
    //     while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //         $Indents[] = $dataRow;
    //     }

    //     $Data['Error'] = null;
    //     $Data['status'] = "success";
    //     $Data['Msg'] = "Success";
    //     $Data['Data'] = $Indents;


    //     return $Data;
    // }

    public function getIndentsBreakUpList($RequestBody)
    {
        $note_id = isset($RequestBody['note_id']) ? $RequestBody['note_id'] : 0;

        $sql = "SELECT * FROM PmGoodsReceiptNoteBreakUp WHERE NoteId = $note_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $Breakups = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Breakups[] = $row;
        }

        return [
            "Error" => null,
            "status" => "success",
            "Msg" => "Success",
            "Data" => $Breakups
        ];
    }




    public function getItemDetails($RequestBody)
    {
        $VarietyID = isset($RequestBody['VarietyID']) ? $RequestBody['VarietyID'] : 0;
        $EffectiveDate = $RequestBody['EffectiveDate'];
        $sql = "SELECT PM.Units AS PriceUnits, PM.UnitPrice, GM.Gst FROM (SELECT Units, UnitPrice FROM PriceMaster WHERE VarietyID = $VarietyID AND FromDate <= '$EffectiveDate' ORDER BY FromDate DESC LIMIT 1) AS PM LEFT JOIN (SELECT Gst FROM GstMaster WHERE GeneralItemId = $VarietyID AND FromDate <= '$EffectiveDate' ORDER BY FromDate DESC LIMIT 1) AS GM ON 1=1";
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
   



    public function postPmGoodsReceiptNote($RequestBody)
    {
        $note_id            = $RequestBody['note_id'] ;
        $doc_no             = $RequestBody['doc_no'] ;
        $pdate              = $RequestBody['pdate'] ;
        $supplier           = $RequestBody['supplier'] ;
        $invoice_no         = $RequestBody['invoice_no'] ;
        $isgst              = $RequestBody['isgst'] ;
        $invoice_date       = $RequestBody['invoice_date'] ;
        $freight            = $RequestBody['freight'] ;
        $invoice_attachment = $RequestBody['invoice_attachment'] ;
        $creditnote         = $RequestBody['creditnote'] ;
        $narration          = $RequestBody['narration'] ;
        $breakUp            = $RequestBody['breakUp'];

        try {
            $this->conn->beginTransaction();
            $existingSnapshot = null;
            if (!empty($note_id)) {
                $existingSnapshot = $this->auditLogger->fetchRowById('PmGoodsReceiptNote', 'ID', $note_id);
            }

            // --- Insert or Update main note ---
            if (empty($note_id)) {
                $sql = "INSERT INTO PmGoodsReceiptNote 
                (DocNo, Date, VendorId, FreightType, IsGST, InvoiceNumber, InvoiceDate, InvoiceAttachment, CreditNoteType, Narration)
                VALUES
                ('$doc_no', '$pdate', '$supplier', '$freight', '$isgst', '$invoice_no', '$invoice_date', '$invoice_attachment', '$creditnote', '$narration')";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                $note_id = $this->conn->lastInsertId();
            } else {
                $sql = "UPDATE PmGoodsReceiptNote SET 
                DocNo=:doc_no, Date='$pdate', VendorId='$supplier', FreightType='$freight', IsGST='$isgst', 
                InvoiceNumber='$invoice_no', InvoiceDate='$invoice_date', InvoiceAttachment='$invoice_attachment', 
                CreditNoteType='$creditnote', Narration='$narration' 
                WHERE ID='$note_id'";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
            }

            // --- Process BreakUps ---
            foreach ($breakUp as $item) {
                // $BreakUpId    = isset($item['breakup_id']) ? (int)$item['breakup_id'] : 0;

                $BreakUpId = (int)($item['BreakUpId'] ? $item['BreakUpId'] : ($item['breakup_id'] ? $item['breakup_id'] : 0) );


                $txtVarietyId = $item['txtVarietyID'] ?  $item['txtVarietyID'] : $item['txtVarietyId'] ;

                $receivedQty  = $item['received_qty'] ;
                $pro          = $item['pro'] ;
                $attachment   = $item['attachment'] ;

                if ($BreakUpId === 0) {
                    $existingBreakup = null;
                    // INSERT new breakup
                    $sql = "INSERT INTO PmGoodsReceiptNoteBreakUp
                    (NoteId, ProformaID, TypeofOrder, VarietyID, PackingStyle, Brand, ReceivedQty, Purpose, Attachment, Remarks, grade, freezing, size)
                    VALUES ('$note_id', '$pro', '".$item['order_type'] ."', '$txtVarietyId', '". $item['packing_style'] ."', '". $item['brand'] ."', '$receivedQty','".$item['purpose'] . "',
                     '$attachment', '" . ($item['remark'] ? $item['remark'] : $item['remarks'] ) . "','". $item['grade'] . "' , '".$item['freezing']."', '".$item['size']."')";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();

                    // Adjust AvailableQuantity for new row
                    $stmtAvailable = $this->conn->prepare("SELECT Id, AvailableQuantity FROM PmProformaAvailableData WHERE VarietyID='$txtVarietyId'");
                    $stmtAvailable->execute();
                    $availableData = $stmtAvailable->fetch(PDO::FETCH_ASSOC);

                    if ($availableData) {
                        $newQty = $availableData['AvailableQuantity'] + $receivedQty;
                        $stmtUpdate = $this->conn->prepare("UPDATE PmProformaAvailableData SET AvailableQuantity='$newQty' WHERE Id='".$availableData['Id']."'");
                        $stmtUpdate->execute();
                    } else {
                        $stmtInsert = $this->conn->prepare("INSERT INTO PmProformaAvailableData (VarietyID, AvailableQuantity) VALUES ('$txtVarietyId', '$receivedQty')");
                        $stmtInsert->execute();
                    }
                } else {
                    $existingBreakup = $this->auditLogger->fetchRowById('PmGoodsReceiptNoteBreakUp', 'BreakUpId', $BreakUpId);
                    // UPDATE existing breakup
                    // --- Get old received quantity ---
                    $stmtOld = $this->conn->prepare("SELECT ReceivedQty FROM PmGoodsReceiptNoteBreakUp WHERE BreakUpId=$BreakUpId");
                    $stmtOld->execute();
                    $oldQty = $stmtOld->fetchColumn() ;

                    $sql = "UPDATE PmGoodsReceiptNoteBreakUp SET
                    NoteId='$note_id', ProformaID='$pro', TypeofOrder='".$item['order_type'] ."', VarietyID='$txtVarietyId',
                    PackingStyle='". $item['packing_style'] ."', Brand='". $item['brand'] ."', ReceivedQty='$receivedQty', Purpose='".$item['purpose'] . "',
                    Attachment='$attachment', Grade='". $item['grade'] . "', Freezing='".$item['freezing']."', Size='".$item['size']."', Remarks='" . ($item['remark'] ? $item['remark'] : $item['remarks'] ) . "'
                    WHERE BreakUpId=$BreakUpId";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute();



                    // Adjust AvailableQuantity
                    $stmtAvailable = $this->conn->prepare("SELECT Id, AvailableQuantity FROM PmProformaAvailableData WHERE VarietyID='$txtVarietyId'");
                    $stmtAvailable->execute();
                    $availableData = $stmtAvailable->fetch(PDO::FETCH_ASSOC);

                    if ($availableData) {
                        $newQty = $availableData['AvailableQuantity'] + $receivedQty - $oldQty;
                        $stmtUpdate = $this->conn->prepare("UPDATE PmProformaAvailableData SET AvailableQuantity='$newQty' WHERE Id='".$availableData['Id']."'");
                        $stmtUpdate->execute();
                    } else {
                        $stmtInsert = $this->conn->prepare("INSERT INTO PmProformaAvailableData (VarietyID, AvailableQuantity) VALUES ('$txtVarietyId', '$receivedQty')");
                        $stmtInsert->execute();
                    }
                }

                $savedBreakUpId = ($BreakUpId === 0) ? $this->conn->lastInsertId() : $BreakUpId;
                $savedBreakup = $this->auditLogger->fetchRowById('PmGoodsReceiptNoteBreakUp', 'BreakUpId', $savedBreakUpId);
                if ($BreakUpId === 0) {
                    $this->auditLogger->logInsert('PmGoodsReceiptNoteBreakUp', $savedBreakUpId, $savedBreakup, $item, [
                        'ReferenceNo' => $doc_no,
                        'Remarks' => 'Inventory packaging goods receipt note breakup created',
                    ]);
                } else {
                    $this->auditLogger->logUpdate('PmGoodsReceiptNoteBreakUp', $savedBreakUpId, $existingBreakup, $savedBreakup, $item, [
                        'ReferenceNo' => $doc_no,
                        'Remarks' => 'Inventory packaging goods receipt note breakup updated',
                    ]);
                }
            }

            $this->conn->commit();
            $savedSnapshot = $this->auditLogger->fetchRowById('PmGoodsReceiptNote', 'ID', $note_id);
            if ($existingSnapshot === null) {
                $this->auditLogger->logInsert('PmGoodsReceiptNote', $note_id, $savedSnapshot, $RequestBody, [
                    'ReferenceNo' => $doc_no,
                    'Remarks' => 'Inventory packaging goods receipt note created',
                ]);
            } else {
                $this->auditLogger->logUpdate('PmGoodsReceiptNote', $note_id, $existingSnapshot, $savedSnapshot, $RequestBody, [
                    'ReferenceNo' => $doc_no,
                    'Remarks' => 'Inventory packaging goods receipt note updated',
                ]);
            }

            return [
                "Error"  => null,
                "status" => "success",
                "Msg"    => "Data Saved Successfully",
                "Data"   => $this->loadPmGoodsReceiptNotes($RequestBody)
            ];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return [
                "Error"  => $e->getMessage(),
                "status" => "fail",
                "Msg"    => "Error while saving data",
                "Data"   => []
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return [
                "Error"  => $e->getMessage(),
                "status" => "fail",
                "Msg"    => "BreakUp row failed to save",
                "Data"   => []
            ];
        }
    }




    // public function getPmGoodsReceiptNote($RequestBody)
    // {
    //     $noteId = $RequestBody['PmGoodsReceiptNoteId'] ;
    //     if (!$noteId) {
    //         return ['status' => 'error', 'Msg' => 'NoteId missing', 'Data' => []];
    //     }

    //     // Fetch main goods receipt note
    //     $sqlNote = "SELECT * FROM pmgoodsreceiptnote WHERE PmGoodsReceiptNoteId = :noteId LIMIT 1";
    //     $stmtNote = $this->conn->prepare($sqlNote);
    //     $stmtNote->bindParam(':noteId', $noteId, PDO::PARAM_INT);
    //     $stmtNote->execute();
    //     $note = $stmtNote->fetch(PDO::FETCH_ASSOC);

    //     if (!$note) {
    //         return ['status' => 'error', 'Msg' => 'Note not found', 'Data' => []];
    //     }

    //     // Fetch all breakups for this note
    //     $sqlBreakups = "SELECT * FROM pmgoodsreceiptnotebreakup WHERE NoteId = :noteId ORDER BY BreakUpId ASC";
    //     $stmtBreakups = $this->conn->prepare($sqlBreakups);
    //     $stmtBreakups->bindParam(':noteId', $noteId, PDO::PARAM_INT);
    //     $stmtBreakups->execute();
    //     $breakupsRaw = $stmtBreakups->fetchAll(PDO::FETCH_ASSOC);

    //     // Map breakups for frontend
    //     $breakups = [];
    //     foreach ($breakupsRaw as $b) {
    //         $bLower = array_change_key_case($b, CASE_LOWER);
    //         $breakups[] = [
    //             'BreakUpId'        => (int)($bLower['breakupid'] ),
    //             'NoteId'           => (int)$noteId,
    //             'ProformaID'       => $b['ProformaID'] ,
    //             'TypeofOrder'      => $b['TypeofOrder'] ,
    //             'VarietyID'        => $b['VarietyID'] ,
    //             'VarietyName'      => $b['VarietyName'] ,
    //             'PackingStyleID'   => $b['PackingStyle'] ?? null,
    //             'PackingStyleName' => $b['PackingStyleName'] ,
    //             'BrandID'          => $b['BrandID'] ?? null,
    //             'Brand'            => $b['Brand'] ,
    //             'ReceivedQty'      => $b['ReceivedQty'] ,
    //             'Purpose'          => $b['Purpose'] ,
    //             'Attachment'       => $b['Attachment'] ,
    //             'Remarks'          => $b['Remarks'] 
    //         ];
    //     }

    //     return [
    //         'status' => 'success',
    //         'Msg'    => 'Success',
    //         'Data'   => [
    //             array_merge($note, ['BreakUps' => $breakups])
    //         ]
    //     ];
    // }


    public function getPmGoodsReceiptNote($RequestBody)
    {
        // Get Note ID from request
        $noteId = ($RequestBody['PmGoodsReceiptNoteId'] ? $RequestBody['PmGoodsReceiptNoteId'] :( $RequestBody['note_id'] ? $RequestBody['note_id'] : $RequestBody['NoteId'] ));

        if (!$noteId) {
            return ['status' => 'error', 'Msg' => 'NoteId missing', 'Data' => []];
        }

        // ===== HEADER =====
        $sqlNote = "SELECT * FROM PmGoodsReceiptNote WHERE ID = $noteId LIMIT 1";
        $stmtNote = $this->conn->prepare($sqlNote);
        $stmtNote->execute();
        $note = $stmtNote->fetch(PDO::FETCH_ASSOC);

        if (!$note) {
            return ['status' => 'error', 'Msg' => 'Note not found', 'Data' => []];
        }

        // ===== BREAKUPS =====
        $sqlBreakups = "SELECT * FROM PmGoodsReceiptNoteBreakUp WHERE NoteId = $noteId ORDER BY BreakUpId ASC";
        $stmtBreakups = $this->conn->prepare($sqlBreakups);
        $stmtBreakups->execute();
        $rows = $stmtBreakups->fetchAll(PDO::FETCH_ASSOC);

        $breakups = [];
        foreach ($rows as $r) {
            // Optional: normalize keys to lowercase
            $r = array_change_key_case($r, CASE_LOWER);

            $breakups[] = [
                'breakup_id'    => (int)$r['breakupid'],
                'note_id'       => (int)$r['noteid'],
                'pro'           => $r['proformaid'],
                'order_type'    => $r['typeoforder'] ,
                'txtVarietyID'  => $r['varietyid'] ,
                'txtVariety'    => $r['varietyid'] , // no VarietyName column, so fallback to ID
                'packing_style' => $r['packingstyle'] ,
                'brand'         => $r['brand'] ,
                'received_qty'  => $r['receivedqty'] ,
                'purpose'       => $r['purpose'] ,
                'attachment'    => $r['attachment'] ,
                'grade'         => $r['grade'] ,
                'freezing'      => $r['freezing'] ,
                'size'          => $r['size'] ,
                'remarks'       => $r['remarks'] 
            ];
        }

        $note['BreakUps'] = $breakups;

        return [
            'status' => 'success',
            'Msg'    => 'Success',
            'Data'   => [$note]
        ];
    }























    public function deletePmGoodsReceiptNote($RequestBody)
    {
        $PmGoodsReceiptNote_id = $RequestBody['PmGoodsReceiptNote_id'] ;

        if (empty($PmGoodsReceiptNote_id)) {
            return [
                "Error" => "Item ID not provided",
                "status" => "fail",
                "Msg" => "Item ID not found",
                "Data" => []
            ];
        }

        try {
            $this->conn->beginTransaction();
            $existingHeader = $this->auditLogger->fetchRowById('PmGoodsReceiptNote', 'ID', $PmGoodsReceiptNote_id);
            $existingBreakups = $this->auditLogger->fetchRowsByConditions('PmGoodsReceiptNoteBreakUp', [
                'NoteId' => $PmGoodsReceiptNote_id,
            ]);

            // Delete breakup records
            $stmtBreakup = $this->conn->prepare(
                "DELETE FROM PmGoodsReceiptNoteBreakUp WHERE NoteId = :id"
            );
            $stmtBreakup->bindParam(':id', $PmGoodsReceiptNote_id);
            $stmtBreakup->execute();

            // Delete main record
            $stmtMain = $this->conn->prepare(
                "DELETE FROM PmGoodsReceiptNote WHERE ID = :id"
            );
            $stmtMain->bindParam(':id', $PmGoodsReceiptNote_id);
            $stmtMain->execute();

            $this->conn->commit();
            foreach ($existingBreakups as $existingBreakup) {
                $breakUpId = isset($existingBreakup['BreakUpId']) ? $existingBreakup['BreakUpId'] : null;
                $this->auditLogger->logDelete('PmGoodsReceiptNoteBreakUp', $breakUpId, $existingBreakup, $RequestBody, [
                    'ReferenceNo' => isset($existingHeader['DocNo']) ? $existingHeader['DocNo'] : $PmGoodsReceiptNote_id,
                    'Remarks' => 'Inventory packaging goods receipt note breakup deleted',
                ]);
            }
            $this->auditLogger->logDelete('PmGoodsReceiptNote', $PmGoodsReceiptNote_id, $existingHeader, $RequestBody, [
                'ReferenceNo' => isset($existingHeader['DocNo']) ? $existingHeader['DocNo'] : $PmGoodsReceiptNote_id,
                'Remarks' => 'Inventory packaging goods receipt note deleted',
            ]);

            // Reload updated table data
            $updatedData = $this->loadPmGoodsReceiptNotes([]);

            return [
                "Error" => null,
                "status" => "success",
                "Msg" => "Data deleted successfully",
                "PmGoodsReceiptNote_id" => $PmGoodsReceiptNote_id,
                "Data" => $updatedData["Data"]
            ];
        } catch (PDOException $e) {
            $this->conn->rollBack();

            return [
                "Error" => $e->getMessage(),
                "status" => "fail",
                "Msg" => "Error deleting data",
                "Data" => []
            ];
        }
    }







    public function loadPmGoodsReceiptNotes($RequestBody)
    {
        $sql = "SELECT 
                PM.ID as Id,
                PM.VendorID,
                VS.VendorName,
                PM.DocNo,
                PM.InvoiceNumber AS InvoiceNo,
                PM.Date,
                PM.FreightType,
                PM.IsGST,
                PM.InvoiceDate,
                PM.CreditNoteType,
                PM.Narration,
                PM.InvoiceAttachment
            FROM PmGoodsReceiptNote AS PM
            LEFT JOIN Vendors AS VS ON PM.VendorId=VS.VendorID
            ORDER BY PM.ID DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $PmGoodsReceiptNoteList = [];
        $i = 0;

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayPmGoodsReceiptNote = [];
            $ArrayPmGoodsReceiptNote['SNo'] = ++$i;
            $ArrayPmGoodsReceiptNote['PmGoodsReceiptNoteId'] = $dataRow['Id'];
            $ArrayPmGoodsReceiptNote['VendorID'] = $dataRow['VendorID'];
            $ArrayPmGoodsReceiptNote['VendorName'] = $dataRow['VendorName'];
            $ArrayPmGoodsReceiptNote['PoID'] = $dataRow['DocNo'];  // assuming DocNo is PoID
            $ArrayPmGoodsReceiptNote['InvoiceNo'] = $dataRow['InvoiceNo'];
            $ArrayPmGoodsReceiptNote['Date'] = $dataRow['Date'];

            // NEW header fields
            $ArrayPmGoodsReceiptNote['FreightType'] = $dataRow['FreightType'];
            $ArrayPmGoodsReceiptNote['IsGST'] = $dataRow['IsGST'];
            $ArrayPmGoodsReceiptNote['InvoiceDate'] = $dataRow['InvoiceDate'];
            $ArrayPmGoodsReceiptNote['CreditNoteType'] = $dataRow['CreditNoteType'];
            $ArrayPmGoodsReceiptNote['Narration'] = $dataRow['Narration'];
            $ArrayPmGoodsReceiptNote['InvoiceAttachment'] = $dataRow['InvoiceAttachment'];

            $PmGoodsReceiptNoteList[] = $ArrayPmGoodsReceiptNote;
        }

        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = "Success";
        $Data['Data'] = $PmGoodsReceiptNoteList;

        return $Data;
    }



   public function getGoodsReceiptNoteForView($RequestBody)
{
    $PmGoodsReceiptNoteId = $RequestBody['PmGoodsReceiptNote_id'] ;

    if (empty($PmGoodsReceiptNoteId)) {
        return [
            "status" => "fail",
            "Msg" => "Goods Receipt Note ID missing",
            "Data" => []
        ];
    }

    try {
        /* ================= 1️⃣ HEADER ================= */
        $stmt = $this->conn->prepare("
            SELECT 
                ID,
                DocNo,
                InvoiceNumber,
                Date,
                InvoiceDate,
                Narration,
                VendorId,
                IsGST,
                CreditNoteType,
                FreightType,
                status
            FROM PmGoodsReceiptNote
            WHERE ID = $PmGoodsReceiptNoteId
        ");
        $stmt->execute();
        $header = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$header) {
            return [
                "status" => "fail",
                "Msg" => "No Goods Receipt Note found",
                "Data" => []
            ];
        }

        /* ================= 2️⃣ BREAKUP ================= */
        $stmt = $this->conn->prepare("
            SELECT *
            FROM PmGoodsReceiptNoteBreakUp
            WHERE NoteId = $PmGoodsReceiptNoteId
        ");
        $stmt->execute();
        $breakup = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /* ================= 3️⃣ MASTER DATA ================= */
        // Packing Styles
        $packingStyles = [];
        $stmt = $this->conn->query("SELECT PCKID, PackingStyle FROM AdminPackingStyles WHERE Status = 1");
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $packingStyles[$r['PCKID']] = $r['PackingStyle'];
        }

        // Brands
        $brands = [];
        $stmt = $this->conn->query("SELECT BrandID, BrandName FROM ScrmBrandsMaster WHERE Status = 1");
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $brands[$r['BrandID']] = $r['BrandName'];
        }

        // Varieties
        $varieties = [];
        $stmt = $this->conn->query("SELECT VarietyID, FullName FROM ScrmPIVarietyList WHERE Status = 1");
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $varieties[$r['VarietyID']] = $r['FullName'];
        }

        /* ================= 4️⃣ MAP MASTER NAMES INTO BREAKUP ================= */
        foreach ($breakup as &$row) {
            $row['VarietyName']      = $varieties[$row['VarietyID']] ;
            $row['PackingStyleName'] = $packingStyles[$row['PackingStyle']] ;
            $row['BrandName']        = $brands[$row['Brand']] ;
        }

        /* ================= 5️⃣ ATTACH BREAKUP TO HEADER ================= */
        $header['BreakUps'] = $breakup;

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
