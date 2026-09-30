<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class ShipmentLineItemwiseReportController
{
    private $conn;
    private $db_table = "";
    private $utilController;
    private $auditLogger;



    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Exports');
    }
    // GET ALL
    public function ShipmentLineItemwiseReportData($RequestBody)
    {
        $RecordsPerPage = 100;
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $ExporterID = $RequestBody['Exporter'];

        $Year = $RequestBody['Year'];
        $ConsigneeId = $RequestBody['ConsigneeId'];
        $CountryId = $RequestBody['CountryId'];
        $DestinationId = $RequestBody['DestinationId'];
        $ProductCode = $RequestBody['ProductCode'];
        $Variety = $RequestBody['Variety'];


        $Query = '';
        $VarietyQuery = '';

        if ($Year != "") {
            $EndYear = $Year + 1;
            $FromDate = $Year . '-04-01';
            $ToDate = $EndYear . '-03-31';
        }
        if ($ConsigneeId != "") {
            $Query .= ' and POD.ConsigneeID in (' . $ConsigneeId . ')';
        }

        if ($ExporterID != "") {
            $Query .= ' and POD.ExporterID in (' . $ExporterID . ')';
        }

        if ($CountryId != "") {
            $Query .= ' and POD.DestinationCountry in (' . $CountryId . ')';
        }

        if ($DestinationId != "") {
            $Query .= ' and POD.FinalDestination in (' . $DestinationId . ')';
        }

        if ($ProductCode != "") {
            $VarietyQuery .= " and SPVL.ProductDescription in ('" . $ProductCode . "') ";
        }
        if ($Variety != "") {
            $VarietyQuery .= " and SPVL.ShortName in ('" . $Variety . "') ";
        }


        $DocType = 'CUI';
        $DateFilter = " between '$FromDate' and '$ToDate'";

        $SqlScrmExporterTaxRatesQuery = "SELECT * FROM ScrmExporterTaxRates WHERE 1 = 1 ORDER BY FromDate DESC";
        $stmt = $this->conn->prepare($SqlScrmExporterTaxRatesQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ScrmExporterTaxRates[] = $dataRow;
        }


        $SqlFieldsList = "SCI.CustomsInvNo,SCI.CustomsInvNo,SCI.CInvDate, SCI.ContainerNo,SCI.ExRate, SCI.ByRoad,SCI.StockOutDate,
        POD.ProcessorID, POD.ExporterID,POD.ImporterID,POD.PoID as OrderID, POD.PoID,POD.ConsigneeID,POD.DestinationCountry,POD.FinalDestination,
        POD.PoNo,POD.PoDate,POD.QtyUnit,POD.ShippingTerms,POD.ProcessorID,POD.PaymentTerms,
        SIFD.BLNo,SIFD.FDAEntryDate,SIFD.EmptyReturn,SOC.CerificationName as Cerification,SSBD.ExchangeRate as SBExchangeRate
        ,SPC.Name AS FinalDestinationPortName,SCD.ConsigneeZipCode";

        $SqlShipmentLineItemwiseQuery = "SELECT $SqlFieldsList,
        (SELECT ComPaymentTerms FROM `ScrmCommercialData`  WHERE PoID=SCI.PiID) as ComPaymentTerms
        FROM   ScrmCustomInvoices as SCI 
        left join ScrmPoData as POD on POD.PoID=SCI.PiID
        left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID
        left join ScrmOrderCertification as SOC on SOC.CerificationID=POD.Certification
        left join ScrmShippingBillDetails  SSBD on SSBD.InvID=POD.PoID

        LEFT JOIN ScrmPortCities SPC ON SPC.PortId = POD.FinalDestination
        LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID = POD.ConsigneeID

        where  CInvDate  $DateFilter and POD.Status not in (2,3,4)   $Query order by SCI.CInvDate asc,POD.PoID asc ";
        $stmt = $this->conn->prepare($SqlShipmentLineItemwiseQuery);
        $stmt->execute();


        $AryShipmentLineitemwiseData = [];
        $i = 0;
        $BreakupCount = 1;
        $ItemCount = 1;
        $PoID = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) { //PoRow

            $OrderID = $dataRow['OrderID'];
            $ConsigneeID = $dataRow['ConsigneeID'];
            $DestinationCountry = $dataRow['DestinationCountry'];
            $FinalDestination = $dataRow['FinalDestination'];
            $ComPaymentTerms = $dataRow['ComPaymentTerms'];
            $CusInvDate = $dataRow['CInvDate'];
            if ($ComPaymentTerms != null && $ComPaymentTerms != '' && $ComPaymentTerms != 0) {
                $PaymentTerms = $ComPaymentTerms;
            } else {
                $PaymentTerms = $dataRow['PaymentTerms'];
            }

            $AryConsignee = $this->utilController->getConsigneeDetails($ConsigneeID);

            // var_dump($AryConsignee); exit;
            $ConsigneeName = $AryConsignee['ConsigneeName'];
            $ConsigneeZipCode = $AryConsignee['ConsigneeZipCode'];
            $CustomerCountry = $this->utilController->getCountryDetails($DestinationCountry)['CountryName'];
            $FinalDestination = $this->utilController->getPortDetails($FinalDestination, '', '')['PortName'];
            $TermsofPayment = $this->utilController->getPaymentTermDetails($PaymentTerms)['PaymentTerm'];
            $Exporter = $this->utilController->getExporterDetails($dataRow['ExporterID'], '157', 'CI')['ExporterName'];
            $Processor = $this->utilController->getExporterDetails($dataRow['ProcessorID'], '157', 'CI')['ExporterName'];
            if ($dataRow['ImporterID'] != 0) {
                $ImporterName = $this->utilController->getImporterRecordDetails($dataRow['ImporterID'])['ImporterName'];
            } else {
                $ImporterName = 'Nil';
            }
            // Write the loop to process line items
            $TaxRateDetails = [];
            foreach ($ScrmExporterTaxRates as $key => $value) {
                if ($value['ExpID'] == $dataRow['ExporterID'] && $value['FromDate'] <= $CusInvDate) {
                    $TaxRateDetails = $value;
                    break;

                }
            }


            $sqlCommercialCount = "SELECT * FROM `ScrmCommercialData` WHERE PoID='$OrderID'";
            $stmtCommercialCount = $this->conn->prepare($sqlCommercialCount);
            $stmtCommercialCount->execute();
            $CommercialCount = $stmtCommercialCount->rowCount();
            $PoGradeTable = 'ScrmPoGradeData';
            if ($CommercialCount == 0) {
                $SqlLineItem = "SELECT *  FROM ScrmPoGradeData as SPGD 
                left join `ScrmPIVarietyList`  as  SPVL on  SPVL.VarietyID=SPGD.VarietyID where InvId='$OrderID'  $VarietyQuery order by ID asc";
            } else {
                $PoGradeTable = 'ScrmCIGradeData';
                $SqlLineItem = "SELECT *  FROM ScrmCIGradeData as SPGD  
                left join `ScrmPIVarietyList`  as  SPVL on  SPVL.VarietyID=SPGD.VarietyID where InvId='$OrderID'  $VarietyQuery order by ID asc";
            }

            $stmtLineItem = $this->conn->prepare($SqlLineItem);
            $stmtLineItem->execute();
            $ItemCount = 0;
            while ($lineItemRow = $stmtLineItem->fetch(PDO::FETCH_ASSOC)) { //Line Item Row
                $PoGradeTableId = $lineItemRow['ID'];
                if ($CommercialCount == 0) {
                    $Variety = $lineItemRow['Description'] ? $lineItemRow['Description'] : $lineItemRow['Variety'];
                    $ItemID = $lineItemRow['ID'];
                } else {
                    $Variety = $lineItemRow['CIDescription'] ? $lineItemRow['CIDescription'] : $lineItemRow['Variety'];
                    $ItemID = $lineItemRow['PoGradeID'];
                }

                $Grade = $lineItemRow['Grade'];
                $VarietyID = $lineItemRow['VarietyID'];
                $PackingStyleID = $lineItemRow['PackingStyleID'];
                $Brand = $lineItemRow['Brand'];
                $Mcs = $lineItemRow['Mcs'];
                $ItemDetails = $this->getProductDetails($VarietyID);
                $SauceUsdValue = 0;
                $SauceLbsWeight = 0;

                $AryShipmentLineitemwiseData['SaucePackingStyle'] = $AryShipmentLineitemwiseData['SauceQty'] = $AryShipmentLineitemwiseData['SauceUnitPrice'] = '--';
                $AryShipmentLineitemwiseData['ShrimpPackingStyle'] = $AryShipmentLineitemwiseData['ShrimpQty'] = $AryShipmentLineitemwiseData['ShrimpUnitPrice'] = '--';
                
                if ($ItemDetails['HasSauce'] == 'Yes') {
                    $PackingStyleID = $ItemDetails['ShrimpPackingStyle'];
                    $SaucePackingDetails = $this->utilController->getPackingListDetails($ItemDetails['SaucePackingStyle']);
                    $OzToLbs = $SaucePackingDetails['SlabWeight'] * $SaucePackingDetails['LbsFactor'];//0.1875
                    $SauceLbsWeight = $SaucePackingDetails['TotalSlabs'] * $OzToLbs * $Mcs;
                    $SauceUsdValue = $SauceLbsWeight * $lineItemRow['CupsPrice'];

                    $AryShipmentLineitemwiseData['SaucePackingStyle'] = $SaucePackingDetails['PackingStyle'];
                    $AryShipmentLineitemwiseData['SauceQty'] = $SauceLbsWeight; 
                    $AryShipmentLineitemwiseData['SauceUnitPrice'] = $lineItemRow['CupsPrice'];

                    $ShrimpPackingDetails = $this->utilController->getPackingListDetails($PackingStyleID);
                    $ShrmipOzToLbs = $ShrimpPackingDetails['SlabWeight'] * $ShrimpPackingDetails['LbsFactor'];//0.1875
                    $ShrimpLbsWeight = $ShrimpPackingDetails['TotalSlabs'] * $ShrmipOzToLbs * $Mcs;

                    $AryShipmentLineitemwiseData['ShrimpPackingStyle'] =  $ShrimpPackingDetails['PackingStyle'];
                    $AryShipmentLineitemwiseData['ShrimpQty'] = $ShrimpLbsWeight;
                    $AryShipmentLineitemwiseData['ShrimpUnitPrice'] = $lineItemRow['Price'];
                }
                $PackingDetails = $this->utilController->getPackingListDetails($PackingStyleID);

                $ItemCount++;
                $ItemCount = sprintf('%02d', $ItemCount);

                // $ConsigneeZipCode = 'NA';

                $WeightDenominationofEachSlabbagpouch = $PackingDetails['SlabWeight'] . substr($dataRow['QtyUnit'], 0, 2);

                if ($PackingDetails['SlabWeight'] < 1) {
                    $PackingDetails['SlabWeight'] = number_format($PackingDetails['SlabWeight'], 3);
                }

                $PackingDetails['SlabWeight'] = number_format($PackingDetails['SlabWeight'], 3);

                // $ArrayPaymentValues = $this->utilController->getPaymentValues($OrderID, $DocType);
                $ArrayWeight = $this->utilController->getItemWeight($ItemID, $dataRow['QtyUnit']);
                // $ArrayWeight = $this->utilController->getItemWeightandPrice($ItemID,$dataRow['QtyUnit']);
                $Quantity = $ArrayWeight['Weight'];
                $WeightKgs = $ArrayWeight['WeightKgs'];
                $WeightKgs = $ArrayWeight['QtyKgs'];
                if ($PackingDetails['Uom'] == 'KGS') {
                    $WeightMeasurement = 'K';
                    $Quantity = $ArrayWeight['WeightKgs'];
                } else if ($PackingDetails['Uom'] == 'LBS') {
                    $WeightMeasurement = 'L';
                } else if ($PackingDetails['Uom'] == 'GRAMS') {
                    $WeightMeasurement = 'K';
                    $Quantity = $ArrayWeight['WeightKgs'];
                } else if ($PackingDetails['Uom'] == 'OZ') {
                    $WeightMeasurement = 'Z';
                } else {
                    $WeightMeasurement = 'undefined';
                }
                // $GlazePerc = $dataRow['GlazePerc'] ? $dataRow['GlazePerc'] : 0;
                $GlazePerc = $ItemDetails['GlazePerc'] ? $ItemDetails['GlazePerc'] : 0;
                $GexQty = $WeightKgs - (($WeightKgs * $GlazePerc) / 100);
                // $Price = $dataRow['Price'];
                $Price = $ArrayWeight['Price'];
                $ValueUSD = $Quantity * $Price;
                $ValueUSD += $SauceUsdValue;

                $Quantity += $SauceLbsWeight;
                $ExchangeRate = $dataRow['ExRate'];
                $ValueINR = $ValueUSD * $ExchangeRate;

                // $ValueUSD = $ArrayPaymentValues['InvValueUSD'];
                // $ValueINR = $ArrayPaymentValues['InvValueINR'];

                if ($dataRow['ByRoad'] != '') {
                    $FinalDestination = $dataRow['ByRoad'];
                }
                $DestinationState = $FinalDestination . ' , ' . $CustomerCountry;

                $AryShipmentLineitemwiseData['SNO'] = ++$i;
                $AryShipmentLineitemwiseData['DestinationStateCode'] = 'Doubt';
                $AryShipmentLineitemwiseData['SaleInvoiceNumber'] = $dataRow['CustomsInvNo'];
                $AryShipmentLineitemwiseData['SaleInvoiceLineNumber'] = $dataRow['CustomsInvNo'] . '-' . $ItemCount;
                $AryShipmentLineitemwiseData['SaleInvoiceDate'] = $this->utilController->getFormatedDatelines($dataRow['CInvDate']);
                $AryShipmentLineitemwiseData['CustomerName'] = $ConsigneeName;
                $AryShipmentLineitemwiseData['CustomerCountry'] = $CustomerCountry;
                $AryShipmentLineitemwiseData['CustomerZipCode'] = $ConsigneeZipCode;
                $AryShipmentLineitemwiseData['PurchaseOrderNumber'] = $dataRow['PoNo'];
                $AryShipmentLineitemwiseData['PurchaseOrderDate'] = $this->utilController->getFormatedDatelines($dataRow['PoDate']);
                $AryShipmentLineitemwiseData['DestinationState'] = $DestinationState;
                $AryShipmentLineitemwiseData['DestinationStateCode'] = 'NA';
                $AryShipmentLineitemwiseData['CountryofFinalDestination'] = $CustomerCountry;
                $AryShipmentLineitemwiseData['TermsofDelivery'] = $dataRow['ShippingTerms'];
                $AryShipmentLineitemwiseData['TermsofPayment'] = $TermsofPayment;
                $AryShipmentLineitemwiseData['ProductDescriptionasinInvoice'] = $Variety;
                $AryShipmentLineitemwiseData['Grade'] = $Grade;
                $AryShipmentLineitemwiseData['CookedForm'] = $ItemDetails['LineItemCode']; //$ItemDetails['TypeProduct'];
                $AryShipmentLineitemwiseData['HeadStatus'] = $ItemDetails['HeadStatus'];
                $AryShipmentLineitemwiseData['ShellStatus'] = $ItemDetails['ShellStatus'];
                $AryShipmentLineitemwiseData['VeinStatus'] = $ItemDetails['VeinStatus'];
                $AryShipmentLineitemwiseData['TailStatus'] = $ItemDetails['TailStatus'];
                $AryShipmentLineitemwiseData['OtherShrimpPreparation'] = $ItemDetails['OtherSPreparation'];
                $AryShipmentLineitemwiseData['FrozenForm'] = $ItemDetails['FreezingType'];
                $AryShipmentLineitemwiseData['Flavoring'] = 'NOFL'; //Constant Instruction Given By Ramesh
                // Checks if the Description of Variety in Invoice contains Skewer or not // 08-05-2025 NithinPC
                if ($ItemDetails['Presentation'] == 'SKW') {
                    if (stripos($Variety, 'SKEWER') !== false) {
                        $AryShipmentLineitemwiseData['Presentation'] = 'SKW';
                    } else {
                        $AryShipmentLineitemwiseData['Presentation'] = 'NOPN';
                    }
                } else {
                    $AryShipmentLineitemwiseData['Presentation'] = $ItemDetails['Presentation'];
                }
                $AryShipmentLineitemwiseData['Species'] = $ItemDetails['Species'];
                $AryShipmentLineitemwiseData['Preservative'] = $ItemDetails['Treatment'];
                $AryShipmentLineitemwiseData['OtherQualityGrade'] = 'Gr-1';
                $AryShipmentLineitemwiseData['GlazePercentage'] = $ItemDetails['GlazePerc'] ? $ItemDetails['GlazePerc'] . '%' : '0%';
                $AryShipmentLineitemwiseData['NWFW'] = substr($ItemDetails['Glaze'], 0, 2);
                $AryShipmentLineitemwiseData['NCFC'] = substr($ItemDetails['Glaze'], 2, 4);
                $AryShipmentLineitemwiseData['SlabsBagsPerCarton'] = $PackingDetails['TotalSlabs'];
                $AryShipmentLineitemwiseData['WeightofEachSlabbagpouch'] = $PackingDetails['SlabWeight'];
                $AryShipmentLineitemwiseData['PackingStyle'] = $PackingDetails['PackingStyle'];


                $AryShipmentLineitemwiseData['WeightMeasurement'] = $WeightMeasurement; // $PackingDetails['Uom'];
                $AryShipmentLineitemwiseData['WeightDenominationofEachSlabbagpouch'] = $WeightDenominationofEachSlabbagpouch;
                $AryShipmentLineitemwiseData['PackingBrand'] = $Brand;
                $AryShipmentLineitemwiseData['NoofMasterCartons'] = $Mcs;
                $AryShipmentLineitemwiseData['Quantity'] = round($Quantity, 2);
                $AryShipmentLineitemwiseData['QuantityUnitofMeasure'] = substr($dataRow['QtyUnit'], 0, 2);
                $AryShipmentLineitemwiseData['GrossUnitPrice'] = $Price;
                $AryShipmentLineitemwiseData['ValueUSD'] = $ValueUSD;
                // $AryShipmentLineitemwiseData['ValueINR'] = $ValueINR;
                $AryShipmentLineitemwiseData['ValueINR'] = $ValueINR = number_format($ValueINR, 2, '.', '');
                $AryShipmentLineitemwiseData['ProcessedandPackedat'] = $Processor;
                $AryShipmentLineitemwiseData['ImporteronRecord'] = $ImporterName;
                $AryShipmentLineitemwiseData['Exporter'] = $Exporter;
                $AryShipmentLineitemwiseData['ContainerNumber'] = $dataRow['ContainerNo'];
                $AryShipmentLineitemwiseData['BillofLadingNumber'] = $dataRow['BLNo'];
                $AryShipmentLineitemwiseData['FinalDestinationasinBL'] = $dataRow['ByRoad'];
                $AryShipmentLineitemwiseData['StateCodeofFinalDestinationasinBL'] = '-';
                $AryShipmentLineitemwiseData['Form7501EntryDate'] = $this->utilController->getFormatedDatelines($dataRow['FDAEntryDate']);
                
                $SqlVarietyDescription = "SELECT `Variety`,PVL.ProductDescription 
                FROM ScrmPoGradeData  PGD
                LEFT JOIN ScrmPIVarietyList AS PVL ON PGD.VarietyID=PVL.VarietyID
                WHERE ID = $ItemID";//CAM-3275
                $stmtVarietyDescription = $this->conn->prepare($SqlVarietyDescription);
                $stmtVarietyDescription->execute();
                //$VarietyDescription = $stmtVarietyDescription->fetch(PDO::FETCH_ASSOC)['Variety'];
                $VarietyData = $stmtVarietyDescription->fetch(PDO::FETCH_ASSOC);
                $VarietyDescription = $VarietyData['Variety'];
                if($RequestBody['DisplayProductDescriptionFromVarietyList'] == 1){
                    $VarietyDescription = $VarietyData['ProductDescription'];
                }
                $AryShipmentLineitemwiseData['ProductDescriptionasinProduction'] = !empty($VarietyDescription) ? $VarietyDescription : '-'; //$Variety . '-' . $PackingDetails['PackingStyle'];
                
                $AryShipmentLineitemwiseData['PartsofBody'] = $ItemDetails['ShortName'];
                $AryShipmentLineitemwiseData['TypeProduct'] = $ItemDetails['TypeProduct'];
                $AryShipmentLineitemwiseData['QtyConvertedtoKgs'] = round($WeightKgs, 2);
                $AryShipmentLineitemwiseData['GEXQty'] = round($GexQty, 2);
                $AryShipmentLineitemwiseData['DateOfDiscountingWithBank'] = 'Doubt';
                $AryShipmentLineitemwiseData['DateOfReceiptOfPayment1'] = 'Doubt';
                $AryShipmentLineitemwiseData['DateOfReceiptOfPayment2'] = 'Doubt';
                $AryShipmentLineitemwiseData['DateOfReceiptOfPayment3'] = 'Doubt';
                $AryShipmentLineitemwiseData['AmountOfReceiptOfPayment1'] = 'Doubt';
                $AryShipmentLineitemwiseData['AmountOfReceiptOfPayment2'] = 'Doubt';
                $AryShipmentLineitemwiseData['AmountOfReceiptOfPayment3'] = 'Doubt';
                $AryShipmentLineitemwiseData['Cerification'] = $dataRow['Cerification'];
                $AryShipmentLineitemwiseData['HSCode'] = $ItemDetails['HSCode'];
                $AryShipmentLineitemwiseData['ProductDescription'] = $ItemDetails['ProductDescription'];
                $AryShipmentLineitemwiseData['SMEInvValueUsd'] = round($Quantity, 2) * $Price;
                $AryShipmentLineitemwiseData['SMEInvValueInr'] = round($Quantity, 2) * $Price * $ExchangeRate;
                $AryShipmentLineitemwiseData['StockOutDate'] = $this->utilController->getFormatedDatelines($dataRow['StockOutDate']);
                $AryShipmentLineitemwiseData['EmptyReturn'] = $this->utilController->getFormatedDatelines($dataRow['EmptyReturn']);
                $AryShipmentLineitemwiseData['FinalDestination'] = $FinalDestination;

                if (!empty($dataRow['StockOutDate']) && empty($dataRow['EmptyReturn'])) {
                    $AryShipmentLineitemwiseData['ItemStatus'] = "Shipped";
                } else if (!empty($dataRow['StockOutDate']) && !empty($dataRow['EmptyReturn'])) {
                    $AryShipmentLineitemwiseData['ItemStatus'] = "Delivered";
                } else if (empty($dataRow['StockOutDate']) && empty($dataRow['EmptyReturn'])) {
                    $AryShipmentLineitemwiseData['ItemStatus'] = "Pending";
                } else {
                    $AryShipmentLineitemwiseData['ItemStatus'] = "Unknown";
                }

                $AryShipmentLineitemwiseData['PoGradeTable'] = $PoGradeTable;
                $AryShipmentLineitemwiseData['PoGradeTableId'] = $PoGradeTableId;
                $AryShipmentLineitemwiseData['PoGradeTableField'] = 'ID';
                $AryShipmentLineitemwiseData['DDPPrice'] = $lineItemRow['DdpPrice'] ? $lineItemRow['DdpPrice'] : 0;
                $AryShipmentLineitemwiseData['Remarks'] = $lineItemRow['Remarks'] ? $lineItemRow['Remarks'] : '';
                $AryShipmentLineitemwiseData['ReciprocalTariffs'] = $TaxRateDetails['ReciprocalTariffs'] ? $TaxRateDetails['ReciprocalTariffs'] : 0;
                $AryShipmentLineitemwiseData['SBExchangeRate'] = $dataRow['SBExchangeRate'];

                $AryShipmentLineitemwiseData['FinalDestinationPortName'] = $dataRow['FinalDestinationPortName'];
                $AryShipmentLineitemwiseData['ConsigneeZipCode'] = $dataRow['ConsigneeZipCode'];
                
                $ArrayShipmentLineitemwiseData[] = $AryShipmentLineitemwiseData;
            }
        }
        if (count($ArrayShipmentLineitemwiseData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['ShipmentLineitemwiseData'] = $ArrayShipmentLineitemwiseData;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }

    function getProductDetails($VarietyID)
    {

        /* $SqlFieldsList="`ProductID`, `TypeProduct`, `Presentation`,`ShortName`, `FullName`, `Category`, `Status`,
                 `HeadStatus`, `ShellStatus`, `VeinStatus`, `TailStatus`, `OtherSPreparation`";

        $SqlProductList="SELECT $SqlFieldsList  FROM ScrmPIVarietyList where ProductID='$ProductID'";
        $stmt = $this->conn->prepare($SqlProductList);
        $stmt->execute(); 
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC); 
*/

        // $SqlFieldsList = "SPL.ProductID, SPVL.TypeProduct,SPVL.TypeProduct,SPM.LineItemCode,SPM.HSCode, SPVL.FreezingType,SPVL.Treatment,SPVL.GlazePerc,SPVL.Glaze,SPVL.Species,SPL.Presentation,SPL.ShortName, SPL.FullName, SPL.Category, SPL.Status, `HeadStatus`, `ShellStatus`, `VeinStatus`, `TailStatus`, `OtherSPreparation`";
        $SqlFieldsList = "SPL.ProductID, SPVL.TypeProduct, SPM.HSCode,SPM.LineItemCode,
                            SPVL.FreezingType, SPVL.Treatment, SPVL.GlazePerc, SPVL.Glaze, SPVL.Species,
                            SPL.ShortName, SPL.FullName, SPL.Category, SPL.Status, `HeadStatus`, `ShellStatus`, `VeinStatus`, `TailStatus`, `OtherSPreparation`,`Presentation`,`SPVL`.`HasSauce`,`SPVL`.`ShrimpPackingStyle`,`SPVL`.`SaucePackingStyle`,`SPVL`.`ProductDescription`";

        $SqlProductList = "SELECT $SqlFieldsList  FROM ScrmPIVarietyList as SPVL 
                            left Join ScrmProductList as SPL on SPVL.ProductID=SPL.ProductID
                            left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType 
                             where SPVL.VarietyID='$VarietyID'"; // ECHO '<BR>';
        $stmt = $this->conn->prepare($SqlProductList);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($dataRow['Species'] == 'VM') {
            $dataRow['Species'] = 'VA';
        }
        // if($dataRow['Treatment'] =='SLT' || $dataRow['Treatment'] =='UNT'){
        // $dataRow['Treatment'] ='SALT';
        // }

        // if($dataRow['Treatment'] =='UNT'){
        // $dataRow['Treatment'] ='NSK';
        // }

        /* $SqlProductList="SELECT LineItemCode FROM `ScrmPreparationMaster` WHERE ShortCode='";
        $stmt = $this->conn->prepare($SqlProductList);
        $stmt->execute(); 
        $RowLineItemCode = $stmt->fetch(PDO::FETCH_ASSOC); 
        $dataRow['LineItemCode']=$RowLineItemCode['LineItemCode'];
        */
        return $dataRow;
    }


    function UpdateDDPPriceRemarks($RequestBody)
    {
        $ItemID = $RequestBody['ItemID'];
        $DDPPrice = $RequestBody['DDPPrice'];
        $Remarks = $RequestBody['Remarks'];
        $Table = $RequestBody['Table'];
        $existingLine = $this->auditLogger->fetchRowById($Table, 'ID', $ItemID);
        $sqlUpdateDDPPriceRemarks = "UPDATE `$Table` SET DdpPrice='$DDPPrice', Remarks='$Remarks' WHERE ID='$ItemID'";
        $stmtUpdateDDPPriceRemarks = $this->conn->prepare($sqlUpdateDDPPriceRemarks);
        $stmtUpdateDDPPriceRemarks->execute();
        if ($stmtUpdateDDPPriceRemarks) {
            $updatedLine = $this->auditLogger->fetchRowById($Table, 'ID', $ItemID);
            $this->auditLogger->logUpdate($Table, $ItemID, $existingLine, $updatedLine, $RequestBody, [
                'ReferenceNo' => $ItemID,
                'Remarks' => 'Export line item DDP price updated',
            ]);
        }
        return "success";
    }
}
