<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once '../Config/Token.php'; //uma
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class OrderStatusController
{
    private $conn;
    private $db_table = "ScrmPIData";
    private $utilController;
    private $restApi;
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        //$database = new Database(); //uma
        $this->restApi = new REST(); //uma
        //$this->conn =$database->getAuthConnection();

        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Exports');
    }

    private function parseOrderIds($orderIds)
    {
        $parts = preg_split('/\s*,\s*/', (string) $orderIds);
        $cleanIds = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if ($part !== '') {
                $cleanIds[] = $part;
            }
        }

        return array_values(array_unique($cleanIds));
    }

    private function fetchRowsByIds($tableName, $idField, $orderIds)
    {
        $ids = $this->parseOrderIds($orderIds);
        if (empty($ids)) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($ids as $index => $id) {
            $placeholder = ':id_' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $id;
        }

        $sql = "SELECT * FROM `{$tableName}` WHERE `{$idField}` IN (" . implode(',', $placeholders) . ')';
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $placeholder => $id) {
            $stmt->bindValue($placeholder, $id);
        }
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $indexedRows = [];
        foreach ($rows as $row) {
            if (isset($row[$idField])) {
                $indexedRows[$row[$idField]] = $row;
            }
        }

        return $indexedRows;
    }

    function getToken()
    {
        $authToken = $this->restApi->getRequestHeader();
        return $payload = Token::Verify($authToken);
    }
    // GET ALL
    public function getOrderList($RequestBody)
    {



        $data = array();
        $ApprovalType = $RequestBody['ApprovalType'];
        $SqlProformaCount = "SELECT PoID,ExporterID,InvNo,InvNumFull FROM `ScrmPIData` WHERE Status='0'";
        $stmt = $this->conn->prepare($SqlProformaCount);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $dataRow;
        }

        $AryStaus = $this->utilController->getOrderStatusList();

        $Arydata['Orders'] = $data;
        $Arydata['Status'] = $AryStaus;


        return $Arydata;
    }




    public function updateOrderList($RequestBody)
    {
        /******uma */
        $authToken = $this->restApi->getRequestHeader();
        $payload = Token::Verify($authToken);
        //var_dump($payload); exit;
        $AuthUserID = $payload['UserID'];
        /******uma */

        $Error = null;
        $ApprovalType = $RequestBody['ApprovalType'];
        $Status = $RequestBody['Status'];
        $ApprovedBy = $AuthUserID;
        $OrderIDs = $RequestBody['OrderIDs'];
        $ApprovalType = $RequestBody['ApprovalType'];
        $existingOrders = $this->fetchRowsByIds('ScrmPIData', 'PoID', $OrderIDs);
        if ($ApprovalType == 'Bulk') {
            $SqlProformaCount = "UPDATE `ScrmPIData` SET `Status`='$Status',`ApprovedBy`='$ApprovedBy'  WHERE PoID in ($OrderIDs)";
            $stmt = $this->conn->prepare($SqlProformaCount);
            $stmt->execute();
        } else if ($ApprovalType == 'Single') {
            $PoNumber = isset($RequestBody['PoNumber']) ? $RequestBody['PoNumber'] : '';
            $PoDate = isset($RequestBody['PoDate']) ? $RequestBody['PoDate'] : '';
            $Remarks = isset($RequestBody['Remarks']) ? $RequestBody['Remarks'] : '';
            $ProdPlant = isset($RequestBody['ProdPlant']) ? $RequestBody['ProdPlant'] : '';

            if ($PoNumber != '') {
                $sqlPonoCheck = "select PoID  from `ScrmPIData` WHERE PoNo='$PoNumber' and PoID not in ($OrderIDs)";
                $stmt = $this->conn->prepare($sqlPonoCheck);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    $Error = 'Duplicate PoNo';
                }
            }

            if ($Error == null) {

                $SqlProformaCount = "UPDATE `ScrmPIData` SET `Status`='$Status',`ApprovedBy`='$ApprovedBy',PoNo='$PoNumber',PoDate='$PoDate',
                Remarks='$Remarks', ProductionPlantID='$ProdPlant'  WHERE PoID in ($OrderIDs)";
                $stmt = $this->conn->prepare($SqlProformaCount);
                $stmt->execute();
            } else {
                $Error = 'Duplicate PoNo';
            }
        }
        if ($Status == 5 && $stmt && $Error == null) {
            // echo "hi";
            $Error = $this->postPOData($OrderIDs,$RequestBody);
        } else if ($Status != 5 && $stmt && $Error == null) {
            $Error = $this->poRcvdToApproved($OrderIDs, $RequestBody);
            ;
        }

        if ($stmt && $Error == null) {
            $updatedOrders = $this->fetchRowsByIds('ScrmPIData', 'PoID', $OrderIDs);
            foreach ($updatedOrders as $poId => $updatedOrder) {
                $existingOrder = isset($existingOrders[$poId]) ? $existingOrders[$poId] : null;
                if (!empty($existingOrder)) {
                    $this->auditLogger->logUpdate('ScrmPIData', $poId, $existingOrder, $updatedOrder, $RequestBody, [
                        'ReferenceNo' => $poId,
                        'ActionBy' => $AuthUserID,
                        'Remarks' => 'Order status updated to ' . $Status,
                    ]);
                }
            }
        }

        $Arydata['Error'] = $Error;
        $Arydata['Msg'] = 'Status Updated';
        if ($Error != null) {
            $Arydata['Msg'] = $Error;
        }
        return $Arydata;
    }



    public function postPOData($OrderIDs, array $RequestBody = [])
    {
        $Error = null;
        $SqlPI = "SELECT * FROM `ScrmPIData` WHERE PoID in ($OrderIDs) ";
        $stmt = $this->conn->prepare($SqlPI);
        $stmt->execute();

        while ($RowPI = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $PoID = $RowPI['PoID'];
            $InvNo = $RowPI['InvNo'];
            $InvDate = $RowPI['InvDate'];
            $ExporterID = $RowPI['ExporterID'];
            $PoNo = $RowPI['PoNo'];
            $PoDate = $RowPI['PoDate'];
            //$InvNumFull = getFullPiInvoiceNumbertoSave($ExporterID, $InvDate, $InvNo);



            $sqlShipment = " SELECT * FROM `ScrmShipmentDetailsBreif`   WHERE PiID='$PoID'";
            $stmtShipment = $this->conn->prepare($sqlShipment);
            $stmtShipment->execute();
            //   echo  $stmtShipment->rowCount();

            if ($PoNo != '' && $PoDate != '') {
                if ($stmtShipment->rowCount() == 0) {
                    $existingShipment = null;
                    $existingPoData = null;
                    $existingPoGrades = [];
                    $sqlShipmentInsert = "INSERT INTO `ScrmShipmentDetailsBreif`( `PiID`, `CInvNo`, `CInvDate`, 
                   `FinYear`, `BLNumber`, `ContainerNo`,
                   `AvailableDate`,`ProductionStartedDate`,`TransportedDate`, 
                   `ShippedOn`, `DeliveredDate`, `ClosedDate`, `BillDiscountingDate`, `ReturnedDate`, `ETA`) 
                    VALUES ('$PoID','','','','','','$PoDate','','','','','','','','')";

                    // $SqlPoData = " INSERT INTO `ScrmPoData` select * from ScrmPIData WHERE PoID ='$PoID'";



                    $SqlPoData = "INSERT INTO `ScrmPoData`(
                    `PoID`,`PoRefId`, `InvNo`, `InvNumFull`, 
                    `InvDate`, `ExporterID`, `ProcessorID`, `LotNo`,
                    `SciName`, `ConsigneeID`, `BuyerID`, `PoNo`, `PoDate`, `DestinationCountry`,
                    `TypeCountry`, `PoStatus`, `Year`, `BankID`, `PortOfDischarge`, 
                    `FinalDestination`, `PaymentTerms`, `ShippingTerms`, `DeliveryTerms`, 
                    `PreparedBy`, `PRInstrctns`, `ShipByDate`, `ShippedOutDate`, `Status`,
                    `QtyUnit`, `UnitCurrency`, `LoginId`, `ApprovedBy`, `Remarks`, `ImporterID`, 
                    `NotifyPartyID`, `ReferenceNo`, `Agent`, `Certification`,`ShipByDateTextformat`, 
                    `PortofLoading`, `ShowBank`, `UltimateConsigneeID`, `Freight`,`ChargeName`,`ChargeAmount`, `PostedDate`, 
                    `IsEdit`, `ProductionPlantID`, `OtherCharges`, `FinalDestinationCountry`)
                    SELECT  `PoID`,`PoRefId`, `InvNo`, `InvNumFull`, `InvDate`,
                    `ExporterID`, `ProcessorID`, `LotNo`, `SciName`, `ConsigneeID`,
                    `BuyerID`, `PoNo`, `PoDate`,`DestinationCountry`, `TypeCountry`, 
                    `PoStatus`, `Year`, `BankID`, `PortOfDischarge`, `FinalDestination`,
                    `PaymentTerms`, `ShippingTerms`, `DeliveryTerms`, `PreparedBy`, 
                    `PRInstrctns`, `ShipByDate`, `ShippedOutDate`, `Status`, 
                    `QtyUnit`, `UnitCurrency`, `LoginId`, `ApprovedBy`, `Remarks`, 
                    `ImporterID`, `NotifyPartyID`, `ReferenceNo`, `Agent`, `Certification`,`ShipByDateTextformat`, 
                    `PortofLoading`, `ShowBank`, `UltimateConsigneeID`, `Freight`,`ChargeName`,`ChargeAmount`,
                    `PostedDate`, '0', `ProductionPlantID`, `OtherCharges`, `FinalDestinationCountry` FROM `ScrmPIData` where PoID = '$PoID'";

                    $stmtPoData = $this->conn->prepare($SqlPoData);
                    $stmtPoData->execute();

                    if ($stmtPoData) {
                        $createdPoData = $this->auditLogger->fetchRowById('ScrmPoData', 'PoID', $PoID);
                        if (!empty($createdPoData)) {
                            $this->auditLogger->logInsert('ScrmPoData', $PoID, $createdPoData, $RequestBody, [
                                'ReferenceNo' => $PoID,
                                'Remarks' => 'PO data posted from PI',
                            ]);
                        }

                        $SqlPoGradeData = "Select * from `ScrmPIGradeData` where InvId = '$PoID'";
                        $stmtPoGradeData = $this->conn->prepare($SqlPoGradeData);
                        if ($stmtPoGradeData->execute() && $stmtPoGradeData->rowCount() > 0) {
                            $SqlPIGradeData = "UPDATE INTO `ScrmPIGradeData` (`PoGradeId`,
                            `Grade`, `Mcs`, `FreezingType`, `PackingStyleID`, `GlazeType`, `GlazePerc`,
                            `QcDescription`, `Variety`, `VarietyID`, `Treatment`, `Price`, `Brand`,
                            `DeviSupc`, `BuyerItemCode`, `Description`, `InvId`, `BAP`, `TraysQuantity`, `CUPS`, `CupsPrice`
                            ) VALUES (
                            SELECT  `ID`,`Grade`, `Mcs`, `FreezingType`, `PackingStyleID`, `GlazeType`, `GlazePerc`,
                            `QcDescription`, `Variety`, `VarietyID`, `Treatment`, `Price`, `Brand`,
                            `DeviSupc`, `BuyerItemCode`, `Description`, `InvId`, `BAP`, `TraysQuantity`, `CUPS`, `CupsPrice`
                            FROM `ScrmPoGradeData` where InvId = '$PoID'";
                        } else {
                            $SqlPIGradeData = "INSERT INTO `ScrmPIGradeData` (`PoGradeId`,
                            `Grade`, `Mcs`, `FreezingType`, `PackingStyleID`, `GlazeType`, `GlazePerc`,
                            `QcDescription`, `Variety`, `VarietyID`, `Treatment`, `Price`, `Brand`,
                            `DeviSupc`, `BuyerItemCode`, `Description`, `InvId`, `BAP`, `TraysQuantity`, `CUPS`, `CupsPrice`
                            )
                            SELECT  `ID`,`Grade`, `Mcs`, `FreezingType`, `PackingStyleID`, `GlazeType`, `GlazePerc`,
                            `QcDescription`, `Variety`, `VarietyID`, `Treatment`, `Price`, `Brand`,
                            `DeviSupc`, `BuyerItemCode`, `Description`, `InvId`, `BAP`, `TraysQuantity`, `CUPS`, `CupsPrice`
                            FROM `ScrmPoGradeData` where InvId = '$PoID'";
                        }

                        $stmtPIGradeData = $this->conn->prepare($SqlPIGradeData);
                        $stmtPIGradeData->execute();
                        $createdPoGrades = $this->auditLogger->fetchRowsByConditions('ScrmPIGradeData', ['InvId' => $PoID]);
                        foreach ($createdPoGrades as $gradeRow) {
                            $recordId = isset($gradeRow['ID']) ? $gradeRow['ID'] : (isset($gradeRow['PoGradeId']) ? $gradeRow['PoGradeId'] : $PoID);
                            $this->auditLogger->logInsert('ScrmPIGradeData', $recordId, $gradeRow, $RequestBody, [
                                'ReferenceNo' => $PoID,
                                'Remarks' => 'PI grade data posted for order approval',
                                'Async' => 1,
                            ]);
                        }
                    } else {
                        $Error = "Unable to post PI Grade Data";
                    }
                } else {
                    $existingShipment = $this->auditLogger->fetchRowById('ScrmShipmentDetailsBreif', 'PiID', $PoID);
                    $sqlShipmentInsert = "UPDATE `ScrmShipmentDetailsBreif` SET `AvailableDate`='$PoDate' WHERE `PiID`='$PoID'";
                }
                $stmtShipmentInsert = $this->conn->prepare($sqlShipmentInsert);
                $stmtShipmentInsert->execute();
                $currentShipment = $this->auditLogger->fetchRowById('ScrmShipmentDetailsBreif', 'PiID', $PoID);
                if (!empty($currentShipment)) {
                    if (!empty($existingShipment)) {
                        $this->auditLogger->logUpdate('ScrmShipmentDetailsBreif', $PoID, $existingShipment, $currentShipment, $RequestBody, [
                            'ReferenceNo' => $PoID,
                            'Remarks' => 'Shipment brief updated during PO posting',
                        ]);
                    } else {
                        $this->auditLogger->logInsert('ScrmShipmentDetailsBreif', $PoID, $currentShipment, $RequestBody, [
                            'ReferenceNo' => $PoID,
                            'Remarks' => 'Shipment brief created during PO posting',
                        ]);
                    }
                }
            }
        }

        return $Error;
    }


    public function deletePI($RequestBody)
    {
        $Error = null;
        $OrderID = $RequestBody['OrderID'];
        $Msg = "Deleted Successfully";
        $existingPiData = $this->auditLogger->fetchRowById('ScrmPIData', 'PoID', $OrderID);
        $existingPoData = $this->auditLogger->fetchRowById('ScrmPoData', 'PoID', $OrderID);
        $existingShipment = $this->auditLogger->fetchRowById('ScrmShipmentDetailsBreif', 'PiID', $OrderID);
        $sqlCustomInvoice = "select PiID from `ScrmCustomInvoices` WHERE PiID='$OrderID'";
        $stmt = $this->conn->prepare($sqlCustomInvoice);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {

            $sql = "DELETE FROM `ScrmPIData` WHERE PoID='$OrderID'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $sql = "DELETE FROM `ScrmPoData` WHERE PoID='$OrderID'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $sql = "DELETE FROM `ScrmShipmentDetailsBreif` WHERE PiID='$OrderID'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            if ($stmt) {
                if (!empty($existingPiData)) {
                    $this->auditLogger->logDelete('ScrmPIData', $OrderID, $existingPiData, $RequestBody, [
                        'ReferenceNo' => $OrderID,
                        'Remarks' => 'PI deleted from order status controller',
                    ]);
                }
                if (!empty($existingPoData)) {
                    $this->auditLogger->logDelete('ScrmPoData', $OrderID, $existingPoData, $RequestBody, [
                        'ReferenceNo' => $OrderID,
                        'Remarks' => 'PO deleted from order status controller',
                    ]);
                }
                if (!empty($existingShipment)) {
                    $this->auditLogger->logDelete('ScrmShipmentDetailsBreif', $OrderID, $existingShipment, $RequestBody, [
                        'ReferenceNo' => $OrderID,
                        'Remarks' => 'Shipment brief deleted from order status controller',
                    ]);
                }
                $Error = null;
            }
        } else {
            $Error = 'Unable To Delete Order ! Delete Custom Invoice First';
            $Msg = "";
        }

        $AryData['Error'] = $Error;
        $AryData['Msg'] = $Msg;
        return $AryData;
    }


    public function poRcvdToApproved($OrderID,$RequestBody=[])
    {
        $Error = null;
        $Msg = "Deleted Successfully";
        $existingPoData = $this->auditLogger->fetchRowById('ScrmPoData', 'PoID', $OrderID);
        $existingShipment = $this->auditLogger->fetchRowById('ScrmShipmentDetailsBreif', 'PiID', $OrderID);
        $sqlCustomInvoice = "select PiID from `ScrmCustomInvoices` WHERE PiID='$OrderID'";
        $stmt = $this->conn->prepare($sqlCustomInvoice);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {

            $sql = "DELETE FROM `ScrmShipmentDetailsBreif` WHERE PiID='$OrderID'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $sql = "DELETE FROM `ScrmPoData` WHERE PoID='$OrderID'";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            if ($stmt) {
                if (!empty($existingShipment)) {
                    $this->auditLogger->logDelete('ScrmShipmentDetailsBreif', $OrderID, $existingShipment, $RequestBody, [
                        'ReferenceNo' => $OrderID,
                        'Remarks' => 'Shipment brief removed while rolling order back to approved',
                    ]);
                }
                if (!empty($existingPoData)) {
                    $this->auditLogger->logDelete('ScrmPoData', $OrderID, $existingPoData, $RequestBody, [
                        'ReferenceNo' => $OrderID,
                        'Remarks' => 'PO removed while rolling order back to approved',
                    ]);
                }
                $Error = null;
            }
        } else {
            $Error = 'Unable To Delete Order ! Delete Custom Invoice First';
            $Msg = "";
        }

        return $Error;
    }

    public function duplicatePI($RequestBody)
    {
        $Error = null;
        $Msg = '';
        $OrderID = $RequestBody['OrderID'];
        $Token = $this->getToken();
        $UserID = $Token['UserID'];

        if ($OrderID != '') {

            $SqlPICount = "SELECT * FROM ScrmPIData WHERE PoID = '$OrderID'";
            $stmt = $this->conn->prepare($SqlPICount);
            $stmt->execute();
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

            $ExporterID = $dataRow['ExporterID'];
            $InvDate = $dataRow['InvDate'];
            //$PoNo = $dataRow['PoNo'];
            $InvDate = date('Y-m-d');
            $Qry = 'and InvDate  ' . $this->getCurrentData($InvDate);

            $SqlPiDetails = "SELECT max(InvNo) as InvNO from ScrmPIData where ExporterID='$ExporterID'  $Qry";
            $stmt = $this->conn->prepare($SqlPiDetails);
            $stmt->execute();
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $InvNO = $dataRow['InvNO'] + 1;
            $InvNumFull = $this->utilController->getFullPiInvoiceNumbertoSave($ExporterID, $InvDate, $InvNO);
            // $InvNumFull = $dataRow['InvNO'] + 1; //update codew

            if ($Error == null) {
                $SqlDuplicatePI = "INSERT INTO `ScrmPIData`(
                     `PoRefId`, `InvNo`, `InvNumFull`, 
                    `InvDate`, `ExporterID`, `ProcessorID`, `LotNo`,
                     `SciName`, `ConsigneeID`, `BuyerID`, `PoNo`, `PoDate`, `DestinationCountry`,
                      `TypeCountry`, `PoStatus`, `Year`, `BankID`, `PortOfDischarge`, 
                      `FinalDestination`, `PaymentTerms`, `ShippingTerms`, `DeliveryTerms`, 
                      `PreparedBy`, `PRInstrctns`, `ShipByDate`, `ShippedOutDate`, `Status`,
                       `QtyUnit`, `UnitCurrency`, `LoginId`, `ApprovedBy`, `Remarks`, `ImporterID`, 
                       `NotifyPartyID`, `ReferenceNo`, `Agent`, `Certification`,`ShipByDateTextformat`, 
                `PortofLoading`, `ShowBank`, `UltimateConsigneeID`, `Freight`, `PostedDate`, `IsEdit`, `ProductionPlantID`)
                SELECT  `PoRefId`, '$InvNO', '$InvNumFull', '$InvDate',
                `ExporterID`, `ProcessorID`, `LotNo`, `SciName`, `ConsigneeID`,
                 `BuyerID`, '', '',`DestinationCountry`, `TypeCountry`, 
                 `PoStatus`, `Year`, `BankID`, `PortOfDischarge`, `FinalDestination`,
                  `PaymentTerms`, `ShippingTerms`, `DeliveryTerms`, `PreparedBy`, 
                  `PRInstrctns`, `ShipByDate`, `ShippedOutDate`, '0', 
                  `QtyUnit`, `UnitCurrency`, '$UserID', `ApprovedBy`, `Remarks`, 
                  `ImporterID`, `NotifyPartyID`, `ReferenceNo`, `Agent`, `Certification`,`ShipByDateTextformat`, 
                  `PortofLoading`, `ShowBank`, `UltimateConsigneeID`, `Freight`,
                   `PostedDate`, '0',  RPC.ProcessingStationID FROM `ScrmPIData` AS SPD 
                   LEFT JOIN `AdminExporterDetails` AS AD ON SPD.ProcessorID =AD.ExpID
                   LEFT JOIN `RmProcessingStations` AS RPC ON AD.InvShortCode = RPC.ProdCode  where PoID = '$OrderID'";


                $stmt = $this->conn->prepare($SqlDuplicatePI);
                $stmt->execute();
                $DupInvID = $this->conn->lastInsertId();
                if (!$stmt) {
                    $Error = "Unable Create Temp Order";
                } else {
                    $createdPi = $this->auditLogger->fetchRowById('ScrmPIData', 'PoID', $DupInvID);
                    if (!empty($createdPi)) {
                        $this->auditLogger->logInsert('ScrmPIData', $DupInvID, $createdPi, $RequestBody, [
                            'ReferenceNo' => $DupInvID,
                            'ActionBy' => $UserID,
                            'Remarks' => 'PI duplicated from order ' . $OrderID,
                        ]);
                    }
                }
            }




            if ($Error == null && $DupInvID != '') {

                $SqlGradeBreakUp = "INSERT INTO `ScrmPoGradeData`( 
                `InvId`, `PINumber`, `CInvFull`, `LineNo`, `Grade`, `Mcs`, 
                `CurrentMcs`, `FreezingType`, `PackingStyleID`, `GrossWt`, `GlazeType`, `Variety`, 
                `GlazePerc`, `Treatment`, `Brand`, `Price`, `BAP`, `VarietyID`, `SKUNo`, `BuyerItemCode`, 
                `Description`, `CodeType`, `DeviSupc`, `TraysQuantity`, `CUPS`, `CupsPrice`)
                
                SELECT  $DupInvID, `PINumber`, `CInvFull`, `LineNo`, `Grade`, `Mcs`,
                `CurrentMcs`, `FreezingType`, `PackingStyleID`, `GrossWt`, `GlazeType`, `Variety`, 
                `GlazePerc`, `Treatment`, `Brand`, `Price`, `BAP`, `VarietyID`, `SKUNo`, `BuyerItemCode`, 
                `Description`, `CodeType`, `DeviSupc`, `TraysQuantity`, `CUPS`, `CupsPrice`
                FROM ScrmPoGradeData WHERE InvId = '$OrderID' ORDER BY `ID` ASC";
                $stmt = $this->conn->prepare($SqlGradeBreakUp);
                $stmt->execute();
                if (!$stmt) {
                    $Error = "Unable Update Temp Order";
                } else {
                    $createdGrades = $this->auditLogger->fetchRowsByConditions('ScrmPoGradeData', ['InvId' => $DupInvID]);
                    foreach ($createdGrades as $gradeRow) {
                        $recordId = isset($gradeRow['ID']) ? $gradeRow['ID'] : $DupInvID;
                        $this->auditLogger->logInsert('ScrmPoGradeData', $recordId, $gradeRow, $RequestBody, [
                            'ReferenceNo' => $DupInvID,
                            'ActionBy' => $UserID,
                            'Remarks' => 'PO grade duplicated from order ' . $OrderID,
                            'Async' => 1,
                        ]);
                    }
                }
            }
        }

        $AryData['Error'] = $Error;
        $AryData['Msg'] = $Msg;
        return $AryData;
    }

    function getCurrentData($InvDate)
    {


        // $InvDate ='2023-09-03'; // commented by uma
        $Currentyear = date('Y', strtotime($InvDate));
        $CurrentMonth = date('m', strtotime($InvDate));

        if ($CurrentMonth > 3) {
            $DateStart = $Currentyear . '-04-01';
            $DateEnd = ($Currentyear + 1) . '-03-31';
        } else {
            $DateStart = ($Currentyear - 1) . '-04-01';
            $DateEnd = ($Currentyear) . '-03-31';
        }

        $DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";

        return $DateFilter;
    }
}
