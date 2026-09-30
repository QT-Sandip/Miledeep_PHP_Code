<?php
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class PmPurchaseOrderDetailsController
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
        $sql = "SELECT ID.PiID AS Id,ID.DocNo FROM PmProformaInvoice AS ID where Status=1";
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
        $sql = "SELECT * FROM PmProformaInvoiceBreakUp WHERE PiID=$indent_id";
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

    // public function postPmPurchaseOrder($RequestBody)
    // {
    //     // echo print_r($RequestBody);exit;
    //     // $po_no = isset($RequestBody['po_no']) ? $RequestBody['po_no'] : 0;
    //     $poId = isset($RequestBody['poId']) ? $RequestBody['poId'] : '';
    //     $pdate = isset($RequestBody['pdate']) ? $RequestBody['pdate'] : '';
    //     $doc_no = isset($RequestBody['doc_no']) ? $RequestBody['doc_no'] : '';
    //     $narration = isset($RequestBody['narration']) ? $RequestBody['narration'] : '';
    //     $supplier = (isset($RequestBody['supplier']) && $RequestBody['supplier'] != '') ? $RequestBody['supplier'] : 0;
    //     $payment_terms = isset($RequestBody['payment_terms']) ? $RequestBody['payment_terms'] : '';
    //     $Freight = isset($RequestBody['Freight']) ? $RequestBody['Freight'] : '';
    //     $freight_type = isset($RequestBody['freight_type']) ? $RequestBody['freight_type'] : '';
    //     $subject = isset($RequestBody['subject']) ? $RequestBody['subject'] : '';
    //     $breakUp = (isset($RequestBody['breakUp'])) ? $RequestBody['breakUp'] : [];

    //     $LastID = $poId;
    //     if ($poId == '') {
    //         $sql = "INSERT INTO `PmPurchaseOrder`( `ProformaID`, `PoDate`, `Supplier`, `PaymentTerms`, `FreightType`, `FreightCharges`, `Subject`, `Narration`) 
    //         VALUES ($doc_no,'$pdate',$supplier,'$payment_terms','$freight_type',$Freight,'$subject','$narration')";
    //         // echo $sql;exit;
    //     } else {
    //         $sql = "UPDATE `PmPurchaseOrder` SET `ProformaID`=$doc_no,`PoDate`='$pdate',`Supplier`=$supplier,
    //         `PaymentTerms`='$payment_terms',`FreightType`='$freight_type',`FreightCharges`=$Freight,`Subject`='$subject',`Narration`='$narration'
    //             WHERE Id=$poId";
    //     }
    //     $stmt = $this->conn->prepare($sql);
    //     if ($stmt->execute()) {
    //         $LastID = $poId == '' ? $this->conn->lastInsertId() : $poId;
    //     } else {
    //         $error = $stmt->errorInfo();
    //         $Data['Error'] = $error[2];
    //         $Data['status'] = "fail";
    //         $Data['Msg'] = "Error while saving Po data";
    //         $Data['Data'] = [];
    //         return $Data;
    //     }


    //     $stmt = null;
    //     $sql = "";

    //     foreach ($breakUp as $item) {
    //         // Access individual fields inside each breakup entry
    //         $a_date = isset($item['a_date']) ? $item['a_date'] : '';
    //         $proqty = (isset($item['proqty']) && $item['proqty'] != '') ? $item['proqty'] : 0;
    //         $pro = (isset($item['pro']) && $item['pro'] != '') ? $item['pro'] : 0;
    //         $txtVarietyID = isset($item['txtVarietyID']) ? $item['txtVarietyID'] : 0;
    //         $disp_date = isset($item['disp_date']) ? $item['disp_date'] : '';
    //         $indent_id = isset($item['indent']) ? $item['indent'] : '';
    //         $packing_style = (isset($item['packing_style']) && $item['packing_style'] != '') ? $item['packing_style'] : 0;
    //         $brand = (isset($item['brand']) && $item['brand'] != '') ? $item['brand'] : 0;
    //         $code = isset($item['code']) ? $item['code'] : '';
    //         // $uom = isset($item['uom']) ? $item['uom'] : '';


    //         if ($indent_id == '') {
    //             $sql = "INSERT INTO `PmPurchaseOrderBreakUp`( `PoID`, `ProformaID`, `ApprovedDate`, `VarietyID`, `PackingStyle`, `Brand`, `ProQty`, `DispacthedDate`, `PlantCode`) 
    //             VALUES ($LastID,$pro,'$a_date',$txtVarietyID,$packing_style,$brand,$proqty,'$disp_date','$code')";
    //             $stmt = $this->conn->prepare($sql);
    //             if ($stmt->execute()) {
    //                 $error .= "";
    //             } else {
    //                 $error .= $stmt->errorInfo()[2];
    //             }
    //             $stmt = null;
    //             $sql = "";
    //         } else {
    //             $sql = "UPDATE `PmPurchaseOrderBreakUp` SET `PoID`=$LastID,`ProformaID`=$pro,
    //             `ApprovedDate`='$a_date',`VarietyID`=$txtVarietyID,`PackingStyle`=$packing_style,`Brand`=$brand,`ProQty`=$proqty,
    //             `DispacthedDate`='$disp_date',`PlantCode`='$code'
    //             WHERE BreakUpId=$indent_id";

    //             $stmt = $this->conn->prepare($sql);
    //             if ($stmt->execute()) {
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
    //         $Data['Msg'] = "PO Data Saved Successfully";
    //         $Data['Data'] = $this->loadPmPurchaseOrder($RequestBody);
    //     } else {
    //         $Data['Error'] = $error;
    //         $Data['status'] = "fail";
    //         $Data['Msg'] = "Error while saving PO data";
    //         $Data['Data'] = [];
    //     }


    //     return $Data;
    // }


    public function postPmPurchaseOrder($RequestBody)
    {
        $poId          = $RequestBody['poId'] ;
        $pdate         = $RequestBody['pdate'] ;
        $doc_no        = $RequestBody['doc_no'] ;
        $narration     = $RequestBody['narration'] ;
        $supplier      = !empty($RequestBody['supplier']) ? intval($RequestBody['supplier']) : 0;
        $payment_terms = $RequestBody['payment_terms'] ;
        $Freight       = floatval($RequestBody['Freight'] );
        $freight_type  = $RequestBody['freight_type'] ;
        $subject       = $RequestBody['subject'] ;
        $breakUp       = $RequestBody['breakUp'] ;

        try {
            // ===============================
            // TRANSACTION START
            // ===============================
            $this->conn->beginTransaction();
            $existingSnapshot = null;
            if ($poId != '') {
                $existingSnapshot = $this->auditLogger->fetchRowById('PmPurchaseOrder', 'PoID', $poId);
            }

            // ===============================
            // HEADER INSERT / UPDATE
            // ===============================
            if ($poId == '') {

                $sql = "INSERT INTO PmPurchaseOrder
                    (ProformaID, PoDate, Supplier, PaymentTerms, FreightType, FreightCharges, Subject, Narration)
                    VALUES
                    ('$doc_no', '$pdate', '$supplier', '$payment_terms', '$freight_type', '$Freight', '$subject', '$narration')";

                $stmt = $this->conn->prepare($sql);
                $stmt->execute();

                $LastID = $this->conn->lastInsertId();
            } else {

                $sql = "UPDATE PmPurchaseOrder SET
                        ProformaID     = '$pro',
                        PoDate         = '$pdate',
                        Supplier       = '$supplier',
                        PaymentTerms   = '$payment_terms',
                        FreightType    = '$freight_type',
                        FreightCharges = '$Freight',
                        Subject        = '$subject',
                        Narration      = '$narration'
                    WHERE PoID = $poId";

                $stmt = $this->conn->prepare($sql);
                $stmt->execute();

                $LastID = $poId;
            }

            // ===============================
            // BREAKUP INSERT / UPDATE
            // ===============================
            foreach ($breakUp as $item) {

                $indent_id     = $item['indent'] ;
                $pro           = $item['pro'] ;
                $a_date        = $item['a_date'] ;
                $txtVarietyID  = intval($item['txtVarietyID'] );
                $packing_style = intval($item['packing_style'] );
                $brand         = intval($item['brand'] );
                $size  = $item['size'] ;
                $availqty  = $item['availqty'] ;

                $grade = $item['grade'] ;
                $freezing = $item['freezing'] ;
                $weight = $item['weight'] ;
                $proqty        = floatval($item['proqty'] );
                $disp_date     = $item['disp_date'] ;
                $code          = $item['code'] ;

                if ($indent_id == '') {
                    $existingBreakup = null;

                    // INSERT BREAKUP
                    $sql = "INSERT INTO PmPurchaseOrderBreakUp
                        (PoID, ProformaID, ApprovedDate, VarietyID, PackingStyle, Brand, ProQty, DispacthedDate, PlantCode, grade, freezing, `size`, availqty, `weight`)
                        VALUES
                        ($LastID, '$pro', '$a_date', '$txtVarietyID', '$packing_style', '$brand', '$proqty', '$disp_date', '$code', '$grade', '$freezing', '$size', '$availqty', '$weight')";
                } else {
                    $existingBreakup = $this->auditLogger->fetchRowById('PmPurchaseOrderBreakUp', 'BreakUpId', $indent_id);

                    // UPDATE BREAKUP
                    $sql = "UPDATE PmPurchaseOrderBreakUp SET
                            PoID           = $LastID,
                            ProformaID     = '$pro',
                            ApprovedDate   = '$a_date',
                            VarietyID      = '$txtVarietyID',
                            PackingStyle   = '$packing_style',
                            Brand          = '$brand',
                            grade          = '$grade',
                            `size`           = '$size',
                            availqty       = '$availqty',
                            freezing       = '$freezing',
                            `weight`         = '$weight',
                            ProQty         = '$proqty',
                            DispacthedDate = '$disp_date',
                            PlantCode      = '$code'
                        WHERE BreakUpId = $indent_id";
                }
                 $stmt = $this->conn->prepare($sql);
                    $stmt->execute();
                $savedBreakupId = ($indent_id == '') ? $this->conn->lastInsertId() : $indent_id;
                $savedBreakup = $this->auditLogger->fetchRowById('PmPurchaseOrderBreakUp', 'BreakUpId', $savedBreakupId);
                if ($indent_id == '') {
                    $this->auditLogger->logInsert('PmPurchaseOrderBreakUp', $savedBreakupId, $savedBreakup, $item, [
                        'ReferenceNo' => $LastID,
                        'Remarks' => 'Inventory packaging purchase order breakup created',
                    ]);
                } else {
                    $this->auditLogger->logUpdate('PmPurchaseOrderBreakUp', $savedBreakupId, $existingBreakup, $savedBreakup, $item, [
                        'ReferenceNo' => $LastID,
                        'Remarks' => 'Inventory packaging purchase order breakup updated',
                    ]);
                }
            }

            // ===============================
            // COMMIT TRANSACTION
            // ===============================
            $this->conn->commit();
            $savedSnapshot = $this->auditLogger->fetchRowById('PmPurchaseOrder', 'PoID', $LastID);
            if ($existingSnapshot === null) {
                $this->auditLogger->logInsert('PmPurchaseOrder', $LastID, $savedSnapshot, $RequestBody, [
                    'ReferenceNo' => $LastID,
                    'Remarks' => 'Inventory packaging purchase order created',
                ]);
            } else {
                $this->auditLogger->logUpdate('PmPurchaseOrder', $LastID, $existingSnapshot, $savedSnapshot, $RequestBody, [
                    'ReferenceNo' => $LastID,
                    'Remarks' => 'Inventory packaging purchase order updated',
                ]);
            }

            return [
                'status' => 'success',
                'Msg'    => 'PO Data Saved Successfully',
                'Error'  => null,
                'Data'   => $this->loadPmPurchaseOrder($RequestBody)
            ];
        } catch (PDOException $e) {

            // ===============================
            // ROLLBACK ON ERROR
            // ===============================
            $this->conn->rollBack();

            return [
                'status' => 'fail',
                'Msg'    => 'Error while saving PO data',
                'Error'  => $e->getMessage(),
                'Data'   => []
            ];
        }
    }



    // public function getPmPurchaseOrder($RequestBody)
    // {

    //     $PmPurchaseOrder_id = isset($RequestBody['PmPurchaseOrder_id']) ? $RequestBody['PmPurchaseOrder_id'] : '';
    //     $sql = "SELECT * FROM PmPurchaseOrder AS PM WHERE PM.Id=$PmPurchaseOrder_id";
    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->execute();
    //     $PmPurchaseOrder = $stmt->fetch(PDO::FETCH_ASSOC);
    //     $Data['Error'] = null;
    //     $Data['status'] = "success";
    //     $Data['Msg'] = $PmPurchaseOrder ? "Success" : "No Data found";
    //     $Data['Data'] = (is_array($PmPurchaseOrder) || isset($PmPurchaseOrder)) ? $PmPurchaseOrder : [];

    //     return $Data;
    // }

    public function getPmPurchaseOrderFlat($RequestBody)
    {
        $PoID = $RequestBody['PmPurchaseOrder_id'] ;

        if (empty($PoID)) {
            return ["status" => "fail", "Msg" => "Purchase Order ID missing", "Data" => []];
        }

        try {
            $stmt = $this->conn->prepare("
            SELECT PoID, ProformaID, PoDate, Supplier, PaymentTerms,
                   FreightType, FreightCharges, Subject, Narration
            FROM pmpurchaseorder
            WHERE PoID =$PoID
        ");
            $stmt->execute();
            $header = $stmt->fetch(PDO::FETCH_OBJ);

            if (!$header) {
                return ["status" => "fail", "Msg" => "No Purchase Order found", "Data" => []];
            }

            $stmt = $this->conn->prepare("
            SELECT BreakUpID, PoID, ProformaID, ApprovedDate,
                   VarietyID, PackingStyle, Brand, ProQty, grade, freezing, size, availqty, weight,
                   DispacthedDate, PlantCode
            FROM pmpurchaseorderbreakup
            WHERE PoID =$PoID
        ");
            $stmt->execute();
            $breakups = $stmt->fetchAll(PDO::FETCH_OBJ);

            return [
                "status" => "success",
                "Msg" => "Success",
                "Data" => [
                    "PoID"           => $header->PoID ,
                    "ProformaID"     => $header->ProformaID ,
                    "PoDate"         => $header->PoDate ,
                    "Supplier"       => $header->Supplier ,
                    "PaymentTerms"   => $header->PaymentTerms ,
                    "FreightType"    => $header->FreightType ,
                    "FreightCharges" => $header->FreightCharges ,
                    "Subject"        => $header->Subject ,
                    "Narration"      => $header->Narration ,
                    "breakUp"        => array_map(function ($b) {
                        return [
                            "BreakUpID"      => $b->BreakUpID ,
                            "PoID"           => $b->PoID ,
                            "ProformaID"     => $b->ProformaID ,
                            "ApprovedDate"   => $b->ApprovedDate ,
                            "VarietyID"      => $b->VarietyID ,
                            "PackingStyle"   => $b->PackingStyle ,
                            "Brand"          => $b->Brand ,
                            "grade"          => $b->grade ,
                            
                            "size"           => $b->size ,
                            "freezing"       => $b->freezing ,
                            "weight"         => $b->weight ,
                            "ProQty"         => $b->ProQty ,
                            "DispacthedDate" => ($b->DispacthedDate == '0000-00-00') ? '' : $b->DispacthedDate,
                            "PlantCode"      => $b->PlantCode ,
                            "availqty"       => $b->availqty 
                        ];
                    }, $breakups)
                ]
            ];
        } catch (PDOException $e) {
            return ["status" => "fail", "Msg" => "Database error: " . $e->getMessage(), "Data" => []];
        }
    }







    // public function deletePmPurchaseOrder($RequestBody)
    // {
    //     $PmPurchaseOrder_id = isset($RequestBody['PmPurchaseOrder_id']) ? $RequestBody['PmPurchaseOrder_id'] : '';
    //     $stmt = $this->conn->prepare("DELETE FROM PmPurchaseOrder WHERE Id=$PmPurchaseOrder_id");

    //     if ($stmt->execute()) {
    //         $stmt = null;
    //         $stmt = $this->conn->prepare("DELETE FROM PmPurchaseOrderBreakUp WHERE PoId=$PmPurchaseOrder_id");
    //         if ($stmt->execute()) {
    //             $Data['Error'] = null;
    //             $Data['status'] = "success";
    //             $Data['Msg'] = "Data deleted successfully";
    //             $Data['PmPurchaseOrder_id'] = $PmPurchaseOrder_id;
    //             $Data['Data'] = $this->loadPmPurchaseOrder($RequestBody);
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

    public function deletePmPurchaseOrder($RequestBody)
    {
        $PmPurchaseOrder_id = isset($RequestBody['PmPurchaseOrder_id']) ? $RequestBody['PmPurchaseOrder_id'] : '';
        $existingHeader = $this->auditLogger->fetchRowById('PmPurchaseOrder', 'PoID', $PmPurchaseOrder_id);
        $existingBreakups = $this->auditLogger->fetchRowsByConditions('PmPurchaseOrderBreakUp', [
            'PoID' => $PmPurchaseOrder_id,
        ]);

        // DELETE MAIN ORDER
        $stmt = $this->conn->prepare("DELETE FROM PmPurchaseOrder WHERE PoID = $PmPurchaseOrder_id");
        if ($stmt->execute()) {

            // DELETE BREAKUP ROWS
            $stmt = $this->conn->prepare("DELETE FROM PmPurchaseOrderBreakUp WHERE PoID = $PmPurchaseOrder_id");

            if ($stmt->execute()) {
                foreach ($existingBreakups as $existingBreakup) {
                    $breakUpId = isset($existingBreakup['BreakUpId']) ? $existingBreakup['BreakUpId'] : null;
                    $this->auditLogger->logDelete('PmPurchaseOrderBreakUp', $breakUpId, $existingBreakup, $RequestBody, [
                        'ReferenceNo' => $PmPurchaseOrder_id,
                        'Remarks' => 'Inventory packaging purchase order breakup deleted',
                    ]);
                }
                $this->auditLogger->logDelete('PmPurchaseOrder', $PmPurchaseOrder_id, $existingHeader, $RequestBody, [
                    'ReferenceNo' => $PmPurchaseOrder_id,
                    'Remarks' => 'Inventory packaging purchase order deleted',
                ]);
                return [
                    "Error" => null,
                    "status" => "success",
                    "Msg" => "Data deleted successfully",
                    "PmPurchaseOrder_id" => $PmPurchaseOrder_id,
                    "Data" => $this->loadPmPurchaseOrder($RequestBody)
                ];
            }
        }

        // ON ERROR
        $error = $stmt->errorInfo();
        return [
            "Error" => $error[2],
            "status" => "fail",
            "Msg" => "Error deleting data",
            "Data" => []
        ];
    }

    public function loadPmPurchaseOrder($RequestBody)
    {

       $sql = "SELECT 
            PM.PoID AS Id,
            PM.ProformaID,
            PM.PoDate AS `Date`,
            PM.Supplier
        FROM PmPurchaseOrder AS PM
        ORDER BY PM.PoID DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $PmPurchaseOrderList = [];
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayPmPurchaseOrder['SNo'] = ++$i;
            $ArrayPmPurchaseOrder['PmPurchaseOrderId'] = $dataRow['Id'];
            // $ArrayPmPurchaseOrder['itemName'] = $dataRow['name'];
            $ArrayPmPurchaseOrder['Date'] = $dataRow['Date'];
            $ArrayPmPurchaseOrder['ProformaID'] = $dataRow['ProformaID'];
            $ArrayPmPurchaseOrder['Supplier'] = $dataRow['Supplier'];
            // $ArrayPmPurchaseOrder['GeneralItemId'] = $dataRow['GeneralItemId'];
            // $ArrayPmPurchaseOrder['Quantity'] = $dataRow['Quantity'];

            $PmPurchaseOrderList[] = $ArrayPmPurchaseOrder;
        }

        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = "Success";
        $Data['Data'] = $PmPurchaseOrderList;

        return $Data;
    }


    public function getPurchaseOrderForView($RequestBody)
    {
        $PoID = $RequestBody['PmPurchaseOrderId'] ;

        if (empty($PoID)) {
            return [
                "status" => "fail",
                "Msg" => "Purchase Order ID missing",
                "Data" => []
            ];
        }

        try {
            // 1️⃣ HEADER
            $sqlHeader = "
            SELECT 
                PoID,
                ProformaID,
                PoDate,
                Supplier,
                PaymentTerms,
                FreightType,
                FreightCharges,
                Subject,
                Narration,
                Status
            FROM PmPurchaseOrder
            WHERE PoID = $PoID
        ";

            $stmt = $this->conn->prepare($sqlHeader);
            $stmt->execute();
            $header = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$header) {
                return [
                    "status" => "fail",
                    "Msg" => "No Purchase Order found",
                    "Data" => []
                ];
            }

            // 2️⃣ BREAKUP
            $sqlBreakup = "
            SELECT *
            FROM PmPurchaseOrderBreakUp
            WHERE PoID = $PoID
        ";

            $stmt = $this->conn->prepare($sqlBreakup);
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

            // 4️⃣ MAP MASTER NAMES
            foreach ($breakup as &$row) {
                $row['VarietyName']      = $varieties[$row['VarietyID']] ;
                $row['PackingStyleName'] = $packingStyles[$row['PackingStyle']] ;
                $row['BrandName']        = $brands[$row['Brand']] ;
                $row['GradeName']        = $row['grade'] ;
            }

            // 5️⃣ ATTACH BREAKUP
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
