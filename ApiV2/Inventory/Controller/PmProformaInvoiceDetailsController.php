<?php
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class PmProformaInvoiceDetailsController
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
        $VarietyID = isset($RequestBody['VarietyID']) ? $RequestBody['VarietyID'] : 0;
        $getSql = "SELECT Id,AvailableQuantity from PmProformaAvailableData WHERE VarietyID=$VarietyID";
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

   


    public function postProformaDetails($RequestBody)
    {
        // -------------------------------
        // Extract header fields safely
        // -------------------------------
        $ProformaDetailsId = $RequestBody['ProformaDetailsId'] ;
        $doc_no = $RequestBody['doc_no'] ;
        $pdate = $RequestBody['pdate'] ;
        $UnitCurrency = $RequestBody['UnitCurrency'] ;
        $ExRate = $RequestBody['ExRate'] !== '' ? floatval($RequestBody['ExRate']) : 0;

        // Numeric dropdowns
        $ConsigneeID = intval($RequestBody['ConsigneeID'] );
        $Company = intval($RequestBody['Company'] );
        $PortofLoading = intval($RequestBody['PortofLoading'] );
        $NotifyPartyID = intval($RequestBody['NotifyPartyID'] );
        $ByPayment = intval($RequestBody['ByPayment'] );
        $DestinationCountry = intval($RequestBody['DestinationCountry'] );
        $OriginCountry = intval($RequestBody['OriginCountry'] );
        $FinalDestination = intval($RequestBody['FinalDestination'] );
        $PortOfDischarge = intval($RequestBody['PortOfDischarge'] );
        $BuyerID = intval($RequestBody['BuyerID'] );

        // Strings
        $narration = $RequestBody['narration'] ;
        $BuyerOrderNo = $RequestBody['BuyerOrderNo'] ;
        $Shipmentdate = $RequestBody['Shipmentdate'] ;
        $POAttachment = $RequestBody['POAttachment'] ;
        $poAdate = $RequestBody['poAdate'] ;
        $Packedby = $RequestBody['Packedby'] ;
        $IsGlaze = $RequestBody['IsGlaze'] ;
        $DeliveryTerms = $RequestBody['DeliveryTerms'] ;
        $amountWords = $RequestBody['amountWords'] ;
        $AdvanceAmount = $RequestBody['AdvanceAmount'] ;
        $ExportReferenceNo = $RequestBody['ExportReferenceNo'] ;


        // BreakUp data
        $breakUp = $RequestBody['breakUp'] ;

        $LastID = $ProformaDetailsId;
        $existingHeader = null;
        if ($ProformaDetailsId != '') {
            $existingHeader = $this->auditLogger->fetchRowById('PmProformaInvoice', 'PiID', $ProformaDetailsId);
        }

        // -------------------------------
        // INSERT / UPDATE HEADER
        // -------------------------------
        if ($ProformaDetailsId == '') {

            // INSERT 
            $sql = "INSERT INTO `PmProformaInvoice`
            (`DocNo`, `PiDate`, `Currency`, `ExchangeRate`, `Consignee`, `Company`,
             `Port of Loading`, `NotifyParty`, `Narration`, `PaymentTerms`, `BuyerOrderNo`,
             `ShipmentDate`, `CountryOfFinalDestination`, `CountryofOrigin`, `FinalDestination`,
             `PurchaseOrderAttachment`, `PoApprovedDate`, `ProcessandPackedby`, `PortOfDischarge`,
             `IsGlaze`, `DeliveryTerms`, `Buyer`, `Amountinwords`, `AdvanceAmount`, `ExportReferenceNo`)
            VALUES ('$doc_no', '$pdate', '$UnitCurrency', '$ExRate', '$ConsigneeID', '$Company',
                    '$PortofLoading', '$NotifyPartyID', '$narration', '$ByPayment', '$BuyerOrderNo',
                    '$Shipmentdate', '$DestinationCountry', '$OriginCountry', '$FinalDestination',
                    '$POAttachment', '$poAdate', '$Packedby', '$PortOfDischarge', '$IsGlaze',
                    '$DeliveryTerms', '$BuyerID', '$amountWords', '$AdvanceAmount', '$ExportReferenceNo')";
        } else {

            //  UPDATE 
            $sql = "UPDATE `PmProformaInvoice` SET
            `PiDate` = '$pdate',
            `Currency` = '$UnitCurrency',
            `ExchangeRate` = '$ExRate',
            `Consignee` = '$ConsigneeID',
            `Company` = '$Company',
            `Port of Loading` = '$PortofLoading',
            `NotifyParty` = '$NotifyPartyID',
            `Narration` = '$narration',
            `PaymentTerms` = '$ByPayment',
            `BuyerOrderNo` = '$BuyerOrderNo',
            `ShipmentDate` = '$Shipmentdate',
            `CountryOfFinalDestination` = '$DestinationCountry',
            `CountryofOrigin` = '$OriginCountry',
            `FinalDestination` = '$FinalDestination',
            `PurchaseOrderAttachment` = '$POAttachment',
            `PoApprovedDate` = '$poAdate',
            `ProcessandPackedby` = '$Packedby',
            `PortOfDischarge` = '$PortOfDischarge',
            `IsGlaze` = '$IsGlaze',
            `DeliveryTerms` = '$DeliveryTerms',
            `Buyer` = '$BuyerID',
            `Amountinwords` = '$amountWords',
            `AdvanceAmount` = '$AdvanceAmount',
            `ExportReferenceNo` = '$ExportReferenceNo'
        WHERE `PiID` = '$ProformaDetailsId'";
        }

        $stmt = $this->conn->prepare($sql);

        if ($stmt->execute()) {
            $LastID = ($ProformaDetailsId == '') ? $this->conn->lastInsertId() : $ProformaDetailsId;
        } else {
            return [
                "status" => "fail",
                "Msg" => "Error while saving Proforma data",
                "Error" => $stmt->errorInfo()[2],
                "Data" => []
            ];
        }

        // -------------------------------
        // SAVE / UPDATE BREAKUP ITEMS
        // -------------------------------
        $error = "";
        $existingBreakupIds = [];

        foreach ($breakUp as $item) {

            $txtVarietyID = intval($item['txtVarietyID'] );
            $brand = intval($item['brand'] );
            $grade = $item['grade'] ;
            $freezing = $item['freezing'] ;
            $packing_style = intval($item['packing_style'] );
            $bap = $item['bap'] ;
            $carton = intval($item['carton'] );
            $weight = floatval($item['weight'] );
            
            $breakUpID = intval($item['BreakUpID'] );

            if ($breakUpID == 0) {
                $existingBreakup = null;
                $sql = "INSERT INTO `PmProformaInvoiceBreakUp`
                (`PiID`, `VarietyID`, `PackingStyleID`, `BAPMaster`, `Cartons`, `Weight`, `BrandID`, `grade`, `freezing`)
                VALUES ($LastID, '$txtVarietyID', '$packing_style', '$bap', '$carton', '$weight', '$brand', '$grade', '$freezing')";
                $stmt = $this->conn->prepare($sql);
            } else {
                $existingBreakup = $this->auditLogger->fetchRowById('PmProformaInvoiceBreakUp', 'BreakUpID', $breakUpID);
                $sql = "UPDATE `PmProformaInvoiceBreakUp` SET
                `VarietyID` = '$txtVarietyID',
                `PackingStyleID` = '$packing_style',
                `BAPMaster` = '$bap',
                `Cartons` = '$carton',
                `Weight` = '$weight',
                `BrandID` = '$brand',
                `grade` = '$grade',
                `freezing` = '$freezing'
            WHERE `BreakUpID` = $breakUpID";
                $stmt = $this->conn->prepare($sql);
            }
            

            if ($stmt->execute()) {
                $savedBreakupId = ($breakUpID == 0) ? $this->conn->lastInsertId() : $breakUpID;
                $existingBreakupIds[] = ($breakUpID == 0) ? $this->conn->lastInsertId() : $breakUpID;
                $savedBreakup = $this->auditLogger->fetchRowById('PmProformaInvoiceBreakUp', 'BreakUpID', $savedBreakupId);
                if ($breakUpID == 0) {
                    $this->auditLogger->logInsert('PmProformaInvoiceBreakUp', $savedBreakupId, $savedBreakup, $item, [
                        'ReferenceNo' => $doc_no,
                        'Remarks' => 'Inventory packaging proforma invoice breakup created',
                    ]);
                } else {
                    $this->auditLogger->logUpdate('PmProformaInvoiceBreakUp', $savedBreakupId, $existingBreakup, $savedBreakup, $item, [
                        'ReferenceNo' => $doc_no,
                        'Remarks' => 'Inventory packaging proforma invoice breakup updated',
                    ]);
                }
            } else {
                $error .= $stmt->errorInfo()[2];
            }
        }

        // Delete removed breakup rows
        if ($ProformaDetailsId != '') {
            $existingBreakups = $this->auditLogger->fetchRowsByConditions('PmProformaInvoiceBreakUp', [
                'PiID' => $LastID,
            ]);
            $existingBreakupMap = [];
            foreach ($existingBreakups as $existingBreakup) {
                if (isset($existingBreakup['BreakUpID'])) {
                    $existingBreakupMap[$existingBreakup['BreakUpID']] = $existingBreakup;
                }
            }
            $idsToKeep = !empty($existingBreakupIds) ? implode(',', $existingBreakupIds) : 0;
            $sql = "DELETE FROM `PmProformaInvoiceBreakUp`
                WHERE `PiID` = $LastID AND `BreakUpID` NOT IN ($idsToKeep)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            foreach ($existingBreakupMap as $existingBreakupId => $existingBreakup) {
                if (!in_array($existingBreakupId, $existingBreakupIds)) {
                    $this->auditLogger->logDelete('PmProformaInvoiceBreakUp', $existingBreakupId, $existingBreakup, $RequestBody, [
                        'ReferenceNo' => $doc_no,
                        'Remarks' => 'Inventory packaging proforma invoice breakup deleted',
                    ]);
                }
            }
        }

        // -------------------------------
        // FINAL RESPONSE
        // -------------------------------
        if ($error === "") {
            $savedHeader = $this->auditLogger->fetchRowById('PmProformaInvoice', 'PiID', $LastID);
            if ($existingHeader === null) {
                $this->auditLogger->logInsert('PmProformaInvoice', $LastID, $savedHeader, $RequestBody, [
                    'ReferenceNo' => $doc_no,
                    'Remarks' => 'Inventory packaging proforma invoice created',
                ]);
            } else {
                $this->auditLogger->logUpdate('PmProformaInvoice', $LastID, $existingHeader, $savedHeader, $RequestBody, [
                    'ReferenceNo' => $doc_no,
                    'Remarks' => 'Inventory packaging proforma invoice updated',
                ]);
            }
            return [
                "status" => "success",
                "Msg" => "Data Saved Successfully",
                "Error" => null,
                "Data" => $this->loadProformaDetails($RequestBody)
            ];
        }

        return [
            "status" => "fail",
            "Msg" => "Error while saving Proforma data",
            "Error" => $error,
            "Data" => []
        ];
    }



    // public function getProformaDetails($RequestBody)
    // {

    //     $ProformaDetails_id = isset($RequestBody['ProformaDetails_id']) ? $RequestBody['ProformaDetails_id'] : '';
    //     $sql = "SELECT * FROM PmProformaInvoice AS PM WHERE PM.Id=$ProformaDetails_id";
    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->execute();
    //     $ProformaDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    //     $Data['Error'] = null;
    //     $Data['status'] = "success";
    //     $Data['Msg'] = $ProformaDetails ? "Success" : "No Data found";
    //     $Data['Data'] = (is_array($ProformaDetails) || isset($ProformaDetails)) ? $ProformaDetails : [];

    //     return $Data;
    // }


    public function getProformaDetails($RequestBody)
    {
        $ProformaDetails_id = isset($RequestBody['ProformaDetails_id']) ? $RequestBody['ProformaDetails_id'] : '';

        // 1️ Get header
        $sql = "SELECT * FROM PmProformaInvoice AS PM WHERE PM.PiID = $ProformaDetails_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $ProformaDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2️ Get breakup rows
        $sql2 = "SELECT * FROM PmProformaInvoiceBreakup WHERE PiID = $ProformaDetails_id";
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute();
        $breakUp = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // 3️Load master data for mapping
        $packingStyles = [];
        $stmtPacking = $this->conn->query("SELECT PCKID, PackingStyle FROM AdminPackingStyles WHERE Status=1");
        while ($row = $stmtPacking->fetch(PDO::FETCH_ASSOC)) {
            $packingStyles[$row['PCKID']] = $row['PackingStyle'];
        }

        $brands = [];
        $stmtBrand = $this->conn->query("SELECT BrandID, BrandName FROM ScrmBrandsMaster WHERE Status=1");
        while ($row = $stmtBrand->fetch(PDO::FETCH_ASSOC)) {
            $brands[$row['BrandID']] = $row['BrandName'];
        }

        $varieties = [];
        $stmtVariety = $this->conn->query("SELECT VarietyID, FullName FROM ScrmPIVarietyList WHERE Status=1");
        while ($row = $stmtVariety->fetch(PDO::FETCH_ASSOC)) {
            $varieties[$row['VarietyID']] = $row['FullName'];
        }

        $freezingTypes = [];
        $stmtFreeze = $this->conn->query("SELECT ID, FreezingTypeName FROM ScrmFreezingTypeMaster WHERE Status=1");
        while ($row = $stmtFreeze->fetch(PDO::FETCH_ASSOC)) {
            $freezingTypes[$row['ID']] = $row['FreezingTypeName'];
        }

        $grades = [];
        $stmtGrades = $this->conn->query("SELECT DISTINCT Grade FROM ScrmPIGradeData");
        while ($row = $stmtGrades->fetch(PDO::FETCH_ASSOC)) {
            $grades[$row['Grade']] = $row['Grade'];
        }

        // 4️ Map master names to breakup rows
        foreach ($breakUp as &$row) {
            $row['PackingStyleName'] = $packingStyles[$row['PackingStyleID']] ;
            $row['BrandName'] = $brands[$row['BrandID']] ;
            $row['VarietyName'] = $varieties[$row['VarietyID']] ;
            $row['FreezingTypeName'] = $freezingTypes[$row['FreezingType']] ;
            $row['GradeName'] = $grades[$row['Grade']] ;
        }

        // 5️ Combine header + breakUp
        if ($ProformaDetails) {
            $ProformaDetails['breakUp'] = $breakUp;
        }

        // 6️ Return data
        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = $ProformaDetails ? "Success" : "No Data found";
        $Data['Data'] = $ProformaDetails ?: [];

        return $Data;
    }




    // public function deleteProformaDetails($RequestBody)
    // {
    //     $ProformaDetails_id = isset($RequestBody['ProformaDetails_id']) ? $RequestBody['ProformaDetails_id'] : '';
    //     $stmt = $this->conn->prepare("DELETE FROM PmProformaInvoice WHERE Id=$ProformaDetails_id");

    //     if ($stmt->execute()) {
    //         $stmt = null;
    //         $stmt = $this->conn->prepare("DELETE FROM PmProformaInvoiceBreakUp WHERE ProformaId=$ProformaDetails_id");
    //         if ($stmt->execute()) {
    //             $Data['Error'] = null;
    //             $Data['status'] = "success";
    //             $Data['Msg'] = "Proforma deleted successfully";
    //             $Data['ProformaDetails_id'] = $ProformaDetails_id;
    //             $Data['Data'] = $this->loadProformaDetails($RequestBody);
    //         } else {
    //             $error = $stmt->errorInfo();
    //             $Data['Error'] = $error[2];
    //             $Data['status'] = "fail";
    //             $Data['Msg'] = "Error deleting Proforma";
    //             $Data['Data'] = [];
    //         }
    //     } else {
    //         $error = $stmt->errorInfo();
    //         $Data['Error'] = $error[2];
    //         $Data['status'] = "fail";
    //         $Data['Msg'] = "Error deleting Proforma";
    //         $Data['Data'] = [];
    //     }

    //     return $Data;
    // }

    public function deleteProformaDetails($RequestBody)
    {
        $PiID = isset($RequestBody['ProformaDetails_id']) ? $RequestBody['ProformaDetails_id'] : '';
        $existingHeader = $this->auditLogger->fetchRowById('PmProformaInvoice', 'PiID', $PiID);
        $existingBreakups = $this->auditLogger->fetchRowsByConditions('PmProformaInvoiceBreakUp', [
            'PiID' => $PiID,
        ]);

        // Delete from parent table
        $stmt = $this->conn->prepare("DELETE FROM pmproformainvoice WHERE PiID = $PiID");

        if ($stmt->execute()) {

            // Delete breakup rows
            $stmt = null;
            $stmt = $this->conn->prepare("DELETE FROM pmproformainvoicebreakup WHERE PiID = $PiID");

            if ($stmt->execute()) {
                foreach ($existingBreakups as $existingBreakup) {
                    $breakUpId = isset($existingBreakup['BreakUpID']) ? $existingBreakup['BreakUpID'] : null;
                    $this->auditLogger->logDelete('PmProformaInvoiceBreakUp', $breakUpId, $existingBreakup, $RequestBody, [
                        'ReferenceNo' => isset($existingHeader['DocNo']) ? $existingHeader['DocNo'] : $PiID,
                        'Remarks' => 'Inventory packaging proforma invoice breakup deleted',
                    ]);
                }
                $this->auditLogger->logDelete('PmProformaInvoice', $PiID, $existingHeader, $RequestBody, [
                    'ReferenceNo' => isset($existingHeader['DocNo']) ? $existingHeader['DocNo'] : $PiID,
                    'Remarks' => 'Inventory packaging proforma invoice deleted',
                ]);
                return [
                    'Error' => null,
                    'status' => 'success',
                    'Msg' => 'Proforma deleted successfully',
                    'ProformaDetails_id' => $PiID,
                    'Data' => $this->loadProformaDetails($RequestBody)
                ];
            } else {
                return [
                    'Error' => $stmt->errorInfo()[2],
                    'status' => 'fail',
                    'Msg' => 'Error deleting Proforma (breakup failed)',
                    'Data' => []
                ];
            }
        } else {
            return [
                'Error' => $stmt->errorInfo()[2],
                'status' => 'fail',
                'Msg' => 'Error deleting Proforma (main failed)',
                'Data' => []
            ];
        }
    }


    public function loadProformaDetails($RequestBody)
    {

        $sql = "SELECT PM.PiID AS Id,PM.DocNo ,PM.PiDate AS `Date` FROM PmProformaInvoice AS PM ORDER BY PM.PiID DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $ProformaDetailsList = [];
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayProformaDetails['SNo'] = ++$i;
            $ArrayProformaDetails['ProformaDetailsId'] = $dataRow['Id'];
            // $ArrayProformaDetails['itemName'] = $dataRow['name'];
            $ArrayProformaDetails['Date'] = $dataRow['Date'];
            $ArrayProformaDetails['DocNo'] = $dataRow['DocNo'];
            // $ArrayProformaDetails['GeneralItemId'] = $dataRow['GeneralItemId'];
            // $ArrayProformaDetails['Quantity'] = $dataRow['Quantity'];

            $ProformaDetailsList[] = $ArrayProformaDetails;
        }

        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = "Success";
        $Data['Data'] = $ProformaDetailsList;

        return $Data;
    }




    

    public function getProformaInvoiceForView($PiID)
    {
        if (empty($PiID)) {
            return [
                "status" => "fail",
                "Msg" => "Proforma ID missing",
                "Data" => []
            ];
        }

        try {
            $PiID = (int)$PiID;

            // 1️⃣ Fetch header
            $sqlHeader = "
            SELECT 
                PiID,
                DocNo,
                PiDate,
                Currency,
                ExchangeRate,
                Consignee,
                Company,
                `Port of Loading`,
                NotifyParty,
                Narration,
                PaymentTerms,
                BuyerOrderNo,
                ShipmentDate,
                CountryOfFinalDestination,
                CountryofOrigin,
                FinalDestination,
                PurchaseOrderAttachment,
                PoApprovedDate,
                ProcessandPackedby,
                PortOfDischarge,
                IsGlaze,
                DeliveryTerms,
                Buyer,
                Amountinwords,
                AdvanceAmount,
                ExportReferenceNo
            FROM pmproformainvoice
            WHERE PiID = $PiID
        ";

            $stmtHeader = $this->conn->prepare($sqlHeader);
            $stmtHeader->execute();
            $header = $stmtHeader->fetch(PDO::FETCH_ASSOC);

            if (!$header) {
                return [
                    "status" => "fail",
                    "Msg" => "No header found for PiID = $PiID",
                    "Data" => []
                ];
            }

            // 2️⃣ Fetch breakup rows
            $sqlBreakup = "SELECT * FROM pmproformainvoicebreakup WHERE PiID = $PiID";
            $stmtBreakup = $this->conn->prepare($sqlBreakup);
            $stmtBreakup->execute();
            $breakup = $stmtBreakup->fetchAll(PDO::FETCH_ASSOC);

            // 3️⃣ Load master data for mapping
            $packingStyles = [];
            $stmtPacking = $this->conn->query("SELECT PCKID, PackingStyle FROM Adminpackingstyles WHERE Status=1");
            while ($row = $stmtPacking->fetch(PDO::FETCH_ASSOC)) {
                $packingStyles[$row['PCKID']] = $row['PackingStyle'];
            }

            $brands = [];
            $stmtBrand = $this->conn->query("SELECT BrandID, BrandName FROM scrmbrandsmaster WHERE Status=1");
            while ($row = $stmtBrand->fetch(PDO::FETCH_ASSOC)) {
                $brands[$row['BrandID']] = $row['BrandName'];
            }

            $varieties = [];
            $stmtVariety = $this->conn->query("SELECT VarietyID, FullName FROM scrmpivarietylist WHERE Status=1");
            while ($row = $stmtVariety->fetch(PDO::FETCH_ASSOC)) {
                $varieties[$row['VarietyID']] = $row['FullName'];
            }

            $freezingTypes = [];
            $stmtFreeze = $this->conn->query("SELECT ID, FreezingTypeName FROM scrmfreezingtypemaster WHERE Status=1");
            while ($row = $stmtFreeze->fetch(PDO::FETCH_ASSOC)) {
                $freezingTypes[$row['ID']] = $row['FreezingTypeName'];
            }

            $grades = [];
            $stmtGrades = $this->conn->query("SELECT DISTINCT Grade FROM scrmpigradedata");
            while ($row = $stmtGrades->fetch(PDO::FETCH_ASSOC)) {
                $grades[$row['Grade']] = $row['Grade'];
            }

            // 4️⃣ Map master names to breakup rows
            foreach ($breakup as &$row) {
                $row['PackingStyleName'] = $packingStyles[$row['PackingStyleID']] ;
                $row['BrandName'] = $brands[$row['BrandID']] ;
                $row['VarietyName'] = $varieties[$row['VarietyID']] ;
                $row['FreezingTypeName'] = $freezingTypes[$row['FreezingType']] ;
                $row['GradeName'] = $grades[$row['Grade']];
            }

            // 5️⃣ Attach breakup to header
            $header['breakUp'] = $breakup;

            return [
                "status" => "success",
                "Msg" => "Success",
                "Data" => $header
            ];
        } catch (Exception $e) {
            return [
                "status" => "fail",
                "Msg" => $e->getMessage(),
                "Data" => []
            ];
        }
    }
}
