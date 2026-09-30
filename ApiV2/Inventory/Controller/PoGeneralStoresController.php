<?php
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class PoGeneralStoresController
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
        $sql = "SELECT ID.Id,ID.DocNo FROM IndentDetailsGs AS ID where ID.`Status` IN (1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $Indents = [];
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Indent['SNo'] = ++$i;
            $Indent['Id'] = $dataRow['Id'];
            $Indent['DocNo'] = $dataRow['DocNo'];

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
        $sql = "SELECT * FROM IndentDetailsBreakUpGs WHERE IndentId=$indent_id";
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

    //     public function postGsProformaOrder($RequestBody)
    //     {
    //         // echo print_r($RequestBody);exit;
    //         // $po_no = isset($RequestBody['po_no']) ? $RequestBody['po_no'] : 0;
    //         $poId = isset($RequestBody['poId']) ? $RequestBody['poId'] : '';
    //         $pdate = isset($RequestBody['pdate']) ? $RequestBody['pdate'] : '';
    //         $doc_no = isset($RequestBody['doc_no']) ? $RequestBody['doc_no'] : '';
    //         $narration = isset($RequestBody['narration']) ? $RequestBody['narration'] : '';
    //         $supplier = (isset($RequestBody['supplier']) && $RequestBody['supplier'] != '') ? $RequestBody['supplier'] : 0;
    //         $payment_terms = isset($RequestBody['payment_terms']) ? $RequestBody['payment_terms'] : '';
    //         $is_igst = isset($RequestBody['is_igst']) ? $RequestBody['is_igst'] : '';
    //         $freight_type = isset($RequestBody['freight_type']) ? $RequestBody['freight_type'] : '';
    //         $sub_delivery = isset($RequestBody['sub_delivery']) ? $RequestBody['sub_delivery'] : '';
    //         $breakUp = (isset($RequestBody['breakUp'])) ? $RequestBody['breakUp'] : [];

    //         $LastID = $poId;
    //         if ($poId == '') {
    //             $sql = "INSERT INTO GsProformaOrder (`DocNo`, `Date`, `Narration`,`supplier`,`payment_terms`,`is_igst`,`freight_type`,`sub_delivery`) 
    //                VALUES ('$doc_no','$pdate','$narration',$supplier,'$payment_terms','$is_igst','$freight_type','$sub_delivery')
    // ";
    //             // echo $sql;exit;
    //         } else {
    //            $sql = "UPDATE GsProformaOrder SET
    //     `DocNo`='$doc_no',
    //     `Date`='$pdate',
    //     `Narration`='$narration',
    //     supplier=$supplier,
    //     payment_terms='$payment_terms',
    //     is_igst='$is_igst',
    //     freight_type='$freight_type',
    //     sub_delivery='$sub_delivery'
    // WHERE Id=$poId";

    //         }
    //         $stmt = $this->conn->prepare($sql);
    //         if ($stmt->execute()) {
    //             $LastID = $poId == '' ? $this->conn->lastInsertId() : $poId;
    //         } else {
    //             $error = $stmt->errorInfo();
    //             $Data['Error'] = $error[2];
    //             $Data['status'] = "fail";
    //             $Data['Msg'] = "Error while saving Po data";
    //             $Data['Data'] = [];
    //             return $Data;
    //         }


    //         $stmt = null;
    //         $sql = "";
    //         foreach ($breakUp as $item) {
    //             // Access individual fields inside each breakup entry
    //             $itemName = isset($item['item']) ? $item['item'] : 0;
    //             $receivedQty = (isset($item['received_qty']) && $item['received_qty'] != '') ? $item['received_qty'] : 0;
    //             $pur_req = isset($item['pur_req']) ? $item['pur_req'] : '';
    //             $remark = isset($item['remark']) ? $item['remark'] : '';
    //             $disp_date = isset($item['disp_date']) ? $item['disp_date'] : '';
    //             // $indent_id = isset($item['indent']) ? $item['indent'] : '';

    //             $indent_id = isset($item['breakup_id']) ? $item['breakup_id'] : '';

    //             $discount_perc = (isset($item['discount_perc']) && $item['discount_perc'] != '') ? $item['discount_perc'] : 0;
    //             // $uom = isset($item['uom']) ? $item['uom'] : '';


    //             if ($indent_id == '') {
    //                 $sql = "INSERT INTO GsProformaOrderBreakUp (`PoId`, `GeneralItemId`, `Quantity`,`Remarks`,`pur_req`,`disp_date`,`discount_perc`) 
    //                 VALUES ($LastID,$itemName,$receivedQty,'$remark','$pur_req','$disp_date',$discount_perc)";
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
    //                 $sql = "UPDATE GsProformaOrderBreakUp SET
    //     GeneralItemId=$itemName,
    //     Quantity=$receivedQty,
    //     Remarks='$remark',
    //     pur_req='$pur_req',
    //     disp_date='$disp_date',
    //     discount_perc=$discount_perc
    // WHERE BreakUpId=$indent_id";

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
    //             $Data['Msg'] = "PO Data Saved Successfully";
    //             $Data['Data'] = $this->loadGsProformaOrder($RequestBody);
    //         } else {
    //             $Data['Error'] = $error;
    //             $Data['status'] = "fail";
    //             $Data['Msg'] = "Error while saving PO data";
    //             $Data['Data'] = [];
    //         }


    //         return $Data;
    //     }






    public function postGsProformaOrder($RequestBody)
    {
        $poId = isset($RequestBody['poId']) ? $RequestBody['poId'] : '';
        $pdate = isset($RequestBody['pdate']) ? $RequestBody['pdate'] : '';
        $doc_no = isset($RequestBody['doc_no']) ? $RequestBody['doc_no'] : '';
        $code = isset($RequestBody['code']) ? $RequestBody['code'] : '';
        $narration = isset($RequestBody['narration']) ? $RequestBody['narration'] : '';
        $reqstatus = isset($RequestBody['status']) ? $RequestBody['status'] : '';
        $supplier = !empty($RequestBody['supplier']) ? $RequestBody['supplier'] : 0;
        $payment_terms = isset($RequestBody['payment_terms']) ? $RequestBody['payment_terms'] : '';
        $is_igst = isset($RequestBody['is_igst']) ? $RequestBody['is_igst'] : '';
        $freight_type = isset($RequestBody['freight_type']) ? $RequestBody['freight_type'] : '';
        $sub_delivery = isset($RequestBody['sub_delivery']) ? $RequestBody['sub_delivery'] : '';
        $breakUp = isset($RequestBody['breakUp']) ? $RequestBody['breakUp'] : [];
        if ($reqstatus == 'Pending') {
            $status = 1;
        } else if ($reqstatus == 'Completed') {
            $status = 2;
        } else {
            $status = 0;
        }
        try {
            // Start Transaction
            $this->conn->beginTransaction();
            $existingSnapshot = null;
            if ($poId != '' && $poId != 0) {
                $existingSnapshot = $this->auditLogger->fetchRowById('GsProformaOrder', 'Id', $poId);
            }

            // ===========================
            //   INSERT / UPDATE MASTER
            // ===========================
            if ($poId == '' || $poId == 0) {

                $sql = "INSERT INTO GsProformaOrder (`DocNo`,  `Code`, `Date`, `Narration`, `status`, `supplier`, 
                    `payment_terms`, `is_igst`, `freight_type`, `sub_delivery`) 
                    VALUES ('$doc_no', '$code', '$pdate','$narration',  '$status', $supplier,'$payment_terms',
                    '$is_igst','$freight_type','$sub_delivery')";
            } else {

                $sql = "UPDATE GsProformaOrder SET
                    `DocNo`='$doc_no',
                    `Code`='$code',
                    `Date`='$pdate',
                    `Narration`='$narration',
                    `status`='$status',
                    supplier=$supplier,
                    payment_terms='$payment_terms',
                    is_igst='$is_igst',
                    freight_type='$freight_type',
                    sub_delivery='$sub_delivery'
                    WHERE Id=$poId";
            }

            $stmt = $this->conn->prepare($sql);

            if ($stmt->execute()) {
                $poId = ($poId == '' || $poId == 0) ? $this->conn->lastInsertId() : $poId;
            } else {
                $error = $stmt->errorInfo();
                $Data['Error'] = $error[2];
                $Data['status'] = "fail";
                $Data['Msg'] = "Error while saving Po data";
                $Data['Data'] = [];
                return $Data;
            }

            // ===========================
            //   INSERT / UPDATE BREAKUP
            // ===========================
            foreach ($breakUp as $item) {

                $breakupId = isset($item['breakup_id']) && $item['breakup_id'] != '' ? $item['breakup_id'] : 0;
                $itemName = isset($item['item']) ? $item['item'] : 0;
                $receivedQty = isset($item['received_qty']) && $item['received_qty'] != '' ? $item['received_qty'] : 0;
                $remark = isset($item['remark']) ? $item['remark'] : '';
                $disp_date = isset($item['disp_date']) ? $item['disp_date'] : '';
                $discountPerc = isset($item['discount_perc']) && $item['discount_perc'] != '' ? $item['discount_perc'] : 0;

                if ($breakupId == 0) {
                    $existingBreakup = null;

                    $sqlBreak = "INSERT INTO GsProformaOrderBreakUp 
                            (PoId, GeneralItemId, Quantity, Remarks, disp_date, discount_perc)
                            VALUES ($poId, $itemName, $receivedQty, '$remark', '$disp_date', $discountPerc)";
                } else {
                    $existingBreakup = $this->auditLogger->fetchRowById('GsProformaOrderBreakUp', 'BreakUpId', $breakupId);

                    $sqlBreak = "UPDATE GsProformaOrderBreakUp SET
                            GeneralItemId = $itemName,
                            Quantity = $receivedQty,
                            Remarks = '$remark',
                            disp_date = '$disp_date',
                            discount_perc = $discountPerc
                            WHERE BreakUpId = $breakupId";
                }

                $stmtBreak = $this->conn->prepare($sqlBreak);

                if (!$stmtBreak->execute()) {
                    $error = $stmtBreak->errorInfo();
                    $Data['Error'] = $error[2];
                    $Data['status'] = "fail";
                    $Data['Msg'] = "Error while saving breakup data";
                    $Data['Data'] = [];
                    return $Data;
                }

                $savedBreakupId = ($breakupId == 0) ? $this->conn->lastInsertId() : $breakupId;
                $savedBreakup = $this->auditLogger->fetchRowById('GsProformaOrderBreakUp', 'BreakUpId', $savedBreakupId);
                if ($breakupId == 0) {
                    $this->auditLogger->logInsert('GsProformaOrderBreakUp', $savedBreakupId, $savedBreakup, $item, [
                        'ReferenceNo' => $code,
                        'Remarks' => 'Inventory general stores PO breakup created',
                    ]);
                } else {
                    $this->auditLogger->logUpdate('GsProformaOrderBreakUp', $savedBreakupId, $existingBreakup, $savedBreakup, $item, [
                        'ReferenceNo' => $code,
                        'Remarks' => 'Inventory general stores PO breakup updated',
                    ]);
                }
            }

            // Commit Transaction
            $this->conn->commit();
            $savedSnapshot = $this->auditLogger->fetchRowById('GsProformaOrder', 'Id', $poId);
            if ($existingSnapshot === null) {
                $this->auditLogger->logInsert('GsProformaOrder', $poId, $savedSnapshot, $RequestBody, [
                    'ReferenceNo' => $code,
                    'Remarks' => 'Inventory general stores PO created',
                ]);
            } else {
                $this->auditLogger->logUpdate('GsProformaOrder', $poId, $existingSnapshot, $savedSnapshot, $RequestBody, [
                    'ReferenceNo' => $code,
                    'Remarks' => 'Inventory general stores PO updated',
                ]);
            }

            $Data['Error'] = null;
            $Data['status'] = 'success';
            $Data['Msg'] = 'PO Data Saved Successfully';
            $Data['Data'] = $this->loadGsProformaOrder(['poId' => $poId]);
        } catch (Exception $e) {

            $this->conn->rollBack();
            $Data['Error'] = $e->getMessage();
            $Data['status'] = 'fail';
            $Data['Msg'] = 'Error while saving PO data';
            $Data['Data'] = [];
        }

        return $Data;
    }

    public function getGsProformaOrder($RequestBody)
    {

        $GsProformaOrder_id = isset($RequestBody['GsProformaOrder_id']) ? $RequestBody['GsProformaOrder_id'] : '';
        $sql = "SELECT * FROM GsProformaOrder AS PM WHERE PM.Id=$GsProformaOrder_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $GsProformaOrder = $stmt->fetch(PDO::FETCH_ASSOC);
        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = $GsProformaOrder ? "Success" : "No Data found";
        $Data['Data'] = (is_array($GsProformaOrder) || isset($GsProformaOrder)) ? $GsProformaOrder : [];

        return $Data;
    }

    public function deleteGsProformaOrder($RequestBody)
    {
        $GsProformaOrder_id = isset($RequestBody['GsProformaOrder_id']) ? $RequestBody['GsProformaOrder_id'] : '';
        $existingHeader = $this->auditLogger->fetchRowById('GsProformaOrder', 'Id', $GsProformaOrder_id);
        $existingBreakups = $this->auditLogger->fetchRowsByConditions('GsProformaOrderBreakUp', [
            'PoId' => $GsProformaOrder_id,
        ]);
        $stmt = $this->conn->prepare("DELETE FROM GsProformaOrder WHERE Id=$GsProformaOrder_id");

        if ($stmt->execute()) {
            $stmt = null;
            $stmt = $this->conn->prepare("DELETE FROM GsProformaOrderBreakUp WHERE PoId=$GsProformaOrder_id");
            if ($stmt->execute()) {
                foreach ($existingBreakups as $existingBreakup) {
                    $breakUpId = isset($existingBreakup['BreakUpId']) ? $existingBreakup['BreakUpId'] : null;
                    $this->auditLogger->logDelete('GsProformaOrderBreakUp', $breakUpId, $existingBreakup, $RequestBody, [
                        'ReferenceNo' => isset($existingHeader['Code']) ? $existingHeader['Code'] : $GsProformaOrder_id,
                        'Remarks' => 'Inventory general stores PO breakup deleted',
                    ]);
                }
                $this->auditLogger->logDelete('GsProformaOrder', $GsProformaOrder_id, $existingHeader, $RequestBody, [
                    'ReferenceNo' => isset($existingHeader['Code']) ? $existingHeader['Code'] : $GsProformaOrder_id,
                    'Remarks' => 'Inventory general stores PO deleted',
                ]);
                $Data['Error'] = null;
                $Data['status'] = "success";
                $Data['Msg'] = "Data deleted successfully";
                $Data['GsProformaOrder_id'] = $GsProformaOrder_id;
                $Data['Data'] = $this->loadGsProformaOrder($RequestBody);
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

    public function loadGsProformaOrder($RequestBody)
    {
        $sql = "SELECT PM.Id, PM.DocNo, PM.Date, PM.Supplier, PM.Code, PM.Status
            FROM GsProformaOrder AS PM
            ORDER BY PM.Id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $GsProformaOrderList = [];
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayGsProformaOrder['SNo'] = ++$i;
            $ArrayGsProformaOrder['GsProformaOrderId'] = $dataRow['Id'];
            $ArrayGsProformaOrder['DocNo'] = $dataRow['DocNo'];
            $ArrayGsProformaOrder['Code'] = $dataRow['Code'];
            $ArrayGsProformaOrder['Date'] = $dataRow['Date'];
            $ArrayGsProformaOrder['Supplier'] = $dataRow['Supplier'];
            $ArrayGsProformaOrder['Status'] = $dataRow['Status'];

            $GsProformaOrderList[] = $ArrayGsProformaOrder;
        }

        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = "Success";
        $Data['Data'] = $GsProformaOrderList;

        return $Data;
    }



    public function getFullPoDetails($RequestBody)
    {
        $poId = isset($RequestBody['GsProformaOrderId']) ? $RequestBody['GsProformaOrderId'] : 0;

        // --- Get PO Header ---
        $stmt = $this->conn->prepare("SELECT * FROM GsProformaOrder WHERE Id = :poId");
        $stmt->bindParam(':poId', $poId, PDO::PARAM_INT);
        $stmt->execute();
        $poHeader = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$poHeader) {
            return [
                'Error' => 'PO not found',
                'status' => 'error',
                'Msg' => 'PO not found',
                'Data' => null
            ];
        }

        // --- Get PO Items ---
        $stmt = $this->conn->prepare("SELECT * FROM gsproformaorderbreakup WHERE PoId = :poId");
        $stmt->bindParam(':poId', $poId, PDO::PARAM_INT);
        $stmt->execute();
        $poItemsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $poItems = [];
        foreach ($poItemsRaw as $item) {
            $poItems[] = [
                'BreakUpId' => $item['BreakUpId'],
                'ItemName' => $item['GeneralItemId'], // Replace with item name join if needed
                'Unit' => '-',                     // Not in table
                'AvailableQty' => 0,                       // Not in table
                'UnitPrice' => 0,                       // Not in table
                'RequestedQty' => $item['Quantity'],
                'DiscountPerc' => $item['discount_perc'],
                'Gst' => 0,                       // Not in table
                'TotalValue' => 0,                       // Not in table
                'TaxAmt' => 0,                       // Not in table
                'InvoiceAmt' => 0,                       // Not in table
                'DispDate' => $item['disp_date'],
                'Remarks' => $item['Remarks']
            ];
        }

        // --- Return combined data ---
        return [
            'Error' => null,
            'status' => 'success',
            'Msg' => 'PO details fetched successfully',
            'Data' => [
                'DocNo' => $poHeader['DocNo'],
                'Code' => $poHeader['Code'],
                'Date' => $poHeader['Date'],
                'Supplier' => $poHeader['Supplier'],
                'payment_terms' => $poHeader['payment_terms'],
                'freight_type' => $poHeader['freight_type'],
                'sub_delivery' => $poHeader['sub_delivery'],
                'Narration' => $poHeader['Narration'],
                'Status' => $poHeader['status'],
                'breakUp' => $poItems
            ]
        ];
    }
}
