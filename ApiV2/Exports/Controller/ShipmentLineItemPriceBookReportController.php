<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class ShipmentLineItemPriceBookReportController
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
        $Status = $RequestBody['Status'];
        $GradeFilter = $RequestBody['Grade'];
        $TypeProduct = $RequestBody['TypeProduct'];



        $Query = '';
        $VarietyQuery = '';

        if ($Year != "") {
            $EndYear = $Year + 1;
            $FromDate = $Year . '-04-01';
            $ToDate = $EndYear . '-03-31';
        }



        $DateFilter = "CInvDate BETWEEN '$FromDate' and '$ToDate'";

        if ($Status != "") {
            if ($Status == 'PENDING') {
                $DateFilter = "(SCI.CInvID IS NULL OR (SCI.StockOutDate IS NULL OR SCI.StockOutDate = ''))";
            } else if ($Status == 'SHIPPED') {
                $DateFilter = "CInvDate BETWEEN '$FromDate' and '$ToDate' ";
            } else if ($Status == 'ON_WATER') {
                $DateFilter .= " AND (SCI.StockOutDate IS NOT NULL AND SCI.StockOutDate != '') AND (SIFD.EmptyReturn IS NULL OR SIFD.EmptyReturn = '' ) ";
            } else if ($Status == 'DELIVERED') {
                $DateFilter .= " AND (SCI.StockOutDate !='' AND SCI.StockOutDate IS NOT NULL) AND (SIFD.EmptyReturn!='' AND SIFD.EmptyReturn IS NOT NULL)  ";
            }
        } else {
            $DateFilter = "(" . $DateFilter . " OR (SCI.CInvID IS NULL OR (SCI.StockOutDate IS NULL OR SCI.StockOutDate = ''))) ";
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

        if ($TypeProduct != "") {
            $VarietyQuery .= " and SPVL.TypeProduct in ('" . $TypeProduct . "') ";
        }

        if ($GradeFilter != "") {
            $VarietyQuery .= " and SPVL.Grade in ('" . $GradeFilter . "') ";
        }








        $SqlFieldsList = "SPGD.ID AS PiPoGradeId,SCIGD.ID AS POCIGradeId,
        SCIGD.CIDescription, SPGD.Description,SPGD.Variety, SPGD.VarietyID, SPGD.PackingStyleID, SPGD.Grade, SPGD.Brand, SPGD.Mcs,SPGD.CupsPrice,
        SPGD.DdpPrice, SPGD.Remarks,
        CONCAT(
        SCI.CustomsInvNo, '-',
        LPAD(
            ROW_NUMBER() OVER (
                PARTITION BY SCI.CustomsInvNo
                ORDER BY SPGD.ID
            ),
            2, '0'
        )
    ) AS CustomsInvNo_Seq, SCI.CustomsInvNo,SCI.CustomsInvNo,SCI.CInvDate, SCI.ContainerNo,SCI.ExRate, SCI.ByRoad,SCI.StockOutDate,
        POD.ProcessorID, POD.ExporterID,POD.ImporterID,POD.PoID as OrderID, POD.PoID,POD.ConsigneeID,POD.DestinationCountry,POD.FinalDestination,
        POD.PoNo,POD.PoDate,POD.QtyUnit,POD.ShippingTerms,POD.ProcessorID,POD.PaymentTerms,
        SIFD.BLNo,SIFD.FDAEntryDate,SIFD.EmptyReturn,SIFD.BLDate,SOC.CerificationName as Cerificatio, SCD.ComPaymentTerms AS ComPaymentTerms,
        SCD.CommercialID as CommercialID ";

        $SqlShipmentLineItemwiseQuery = "SELECT $SqlFieldsList
        FROM ScrmPoGradeData as SPGD
        left JOIN ScrmCIGradeData as SCIGD ON SPGD.ID = SCIGD.PoGradeID 
        left JOIN ScrmPoData as POD ON SPGD.InvId = POD.PoID
        left join ScrmCustomInvoices as SCI on POD.PoID=SCI.PiID
        left join ScrmISFDetails as SIFD on POD.PoID=SIFD.PIID
        left join ScrmCommercialData as SCD on POD.PoID=SCD.PoID
        left join ScrmOrderCertification as SOC on SOC.CerificationID=POD.Certification
        left join `ScrmPIVarietyList`  as  SPVL on  SPVL.VarietyID=SPGD.VarietyID
        left join ScrmPIData as PID ON PID.PoID = POD.PoID
        where 1 = 1 AND  $DateFilter and POD.Status not in (2,3,4)   $Query $VarietyQuery
        -- order by SCI.CInvDate asc,CustomsInvNo_Seq, POD.PoID asc 
        ORDER BY POD.PoDate DESC
         ";

        $stmt = $this->conn->prepare($SqlShipmentLineItemwiseQuery);
        $stmt->execute();


        $AryShipmentLineitemwiseData = [];
        $i = 0;
        $BreakupCount = 1;
        $ItemCount = 1;
        $PoID = 0;



        $ScrmExporterTaxRatesCache = [];
        $consigneeCache = [];
        $countryCache = [];
        $portCache = [];
        $paymentTermCache = [];
        $exporterCache = [];
        $importerCache = [];
        $ProductCache = [];
        $PackingListCache = [];

        $SqlScrmExporterTaxRatesQuery = "SELECT * FROM ScrmExporterTaxRates WHERE 1 = 1 ORDER BY FromDate DESC";
        $stmtScrmExporterTaxRatesQuery = $this->conn->prepare($SqlScrmExporterTaxRatesQuery);
        $stmtScrmExporterTaxRatesQuery->execute();
        while ($dataRow = $stmtScrmExporterTaxRatesQuery->fetch(PDO::FETCH_ASSOC)) {
            $ScrmExporterTaxRatesCache[$dataRow['ExpTRID']] = $dataRow;
        }


        $SqlScrmConsigneeDetailsQuery = "SELECT * FROM ScrmConsigneeDetails WHERE 1 = 1 ";
        $stmtScrmConsigneeDetailsQuery = $this->conn->prepare($SqlScrmConsigneeDetailsQuery);
        $stmtScrmConsigneeDetailsQuery->execute();
        while ($dataRow = $stmtScrmConsigneeDetailsQuery->fetch(PDO::FETCH_ASSOC)) {
            $consigneeCache[$dataRow['ConID']] = $dataRow;
        }


        $sqlScrmCountriesListQuery = "SELECT id, name AS CountryName,GstCountryCode As CountryGstCode FROM ScrmCountriesList WHERE 1 = 1 ";
        $stmtScrmCountriesListQuery = $this->conn->prepare($sqlScrmCountriesListQuery);
        $stmtScrmCountriesListQuery->execute();
        while ($dataRow = $stmtScrmCountriesListQuery->fetch(PDO::FETCH_ASSOC)) {
            $countryCache[$dataRow['id']] = $dataRow;
        }

        $sqlScrmPortsListQuery = "SELECT PortId, Name AS PortName, PortCode FROM ScrmPortCities WHERE 1 = 1 ";
        $stmtScrmPortsListQuery = $this->conn->prepare($sqlScrmPortsListQuery);
        $stmtScrmPortsListQuery->execute();
        while ($dataRow = $stmtScrmPortsListQuery->fetch(PDO::FETCH_ASSOC)) {
            $portCache[$dataRow['PortId']] = $dataRow;
        }
        $sqlPaymentTermDetailsQuery = "SELECT ID,PaymentName AS PaymentTerm, PaymentMode, LcStatus, NoOfDays, ConditionDate, ShortName FROM ScrmPaymentTerms WHERE 1 = 1 ";
        $stmtPaymentTermDetailsQuery = $this->conn->prepare($sqlPaymentTermDetailsQuery);
        $stmtPaymentTermDetailsQuery->execute();
        while ($dataRow = $stmtPaymentTermDetailsQuery->fetch(PDO::FETCH_ASSOC)) {
            $paymentTermCache[$dataRow['ID']] = $dataRow;
        }


        $SqlFieldsList = "SPVL.VarietyID, SPL.ProductID, SPVL.TypeProduct, SPM.HSCode,SPM.LineItemCode,
                            SPVL.FreezingType, SPVL.Treatment, SPVL.GlazePerc, SPVL.Glaze, SPVL.Species,
                            SPL.ShortName, SPL.FullName, SPL.Category, SPL.Status, `HeadStatus`, `ShellStatus`, `VeinStatus`,
                            `TailStatus`, `OtherSPreparation`,`Presentation`,`SPVL`.`PackingStyle`,
                            `SPVL`.`HasSauce`,`SPVL`.`ShrimpPackingStyle`,`SPVL`.`SaucePackingStyle`,`SPVL`.`ProductDescription`";

        $sqlProductDetailsQuery = "SELECT $SqlFieldsList  FROM ScrmPIVarietyList as SPVL 
                            left Join ScrmProductList as SPL on SPVL.ProductID=SPL.ProductID
                            left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType 
                             where 1=1 ";


        $stmtProductDetailsQuery = $this->conn->prepare($sqlProductDetailsQuery);
        $stmtProductDetailsQuery->execute();
        while ($dataRow = $stmtProductDetailsQuery->fetch(PDO::FETCH_ASSOC)) {
            if ($dataRow['Species'] == 'VM') {
                $dataRow['Species'] = 'VA';
            }
            $ProductCache[$dataRow['VarietyID']] = $dataRow;
        }


        $sqlFields = "`PCKID`,`TotalSlabs`,`SlabWeight`,`Uom`,`PackingStyle`,`GrossWeight`, `LbsFactor`";

        $sqlAdminPackingQuery = "SELECT $sqlFields  FROM `AdminPackingStyles` where 1=1 ";
        $stmtAdminPacking = $this->conn->prepare($sqlAdminPackingQuery);
        $stmtAdminPacking->execute();

        while ($dataRow = $stmtAdminPacking->fetch(PDO::FETCH_ASSOC)) {
            $PackingListCache[$dataRow['PCKID']] = $dataRow;
        }

        $AryFilteredCountriesList = [];
        $AryFilteredPortsList = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) { //PoRow


            $OrderID = $dataRow['OrderID'];
            $ConsigneeID = $dataRow['ConsigneeID'];
            $DestinationCountry = $dataRow['DestinationCountry'];
            $FinalDestinationId = $dataRow['FinalDestination'];
            $FinalDestination = $dataRow['FinalDestination'];
            $ComPaymentTerms = $dataRow['ComPaymentTerms'];
            $CusInvDate = $dataRow['CInvDate'];
            $PaymentTerms = !empty($dataRow['ComPaymentTerms'])
                ? $dataRow['ComPaymentTerms']
                : $dataRow['PaymentTerms'];


            $AryConsignee = $consigneeCache[$ConsigneeID];

            // var_dump($AryConsignee); exit;
            // $ConsigneeName = $AryConsignee['ConsigneeName'];
            $ConsigneeName = $AryConsignee['ConsigneeCode'];
            $ConsigneeZipCode = $AryConsignee['ConsigneeZipCode'];
            $CustomerCountry = $countryCache[$DestinationCountry]['CountryName'];
            $FinalDestination = $portCache[$FinalDestination]['PortName'];
            $TermsofPayment = $paymentTermCache[$PaymentTerms]['PaymentTerm'];
            $Exporter = $this->utilController->getExporterDetailsFromCache($dataRow['ExporterID'], '157', 'CI')['ExporterName'];
            $Processor = $this->utilController->getExporterDetailsFromCache($dataRow['ProcessorID'], '157', 'CI')['ExporterName'];
            if ($dataRow['ImporterID'] != 0) {
                $ImporterName = $this->utilController->getImporterRecordDetailsFromCache($dataRow['ImporterID'])['ImporterName'];
            } else {
                $ImporterName = 'Nil';
            }
            // Write the loop to process line items
            $TaxRateDetails = [];
            // echo "<pre>";
            // echo "ConsigneeID: " . $ConsigneeID . " | CusInvDate: " . $CusInvDate . "\n";
            $ReciprocalTariffsPerc = 0;
            foreach ($ScrmExporterTaxRatesCache as $key => $value) {
                if ($value['ExpID'] == $dataRow['ExporterID'] && $value['FromDate'] <= $CusInvDate) {
                    $TaxRateDetails = $value;
                    $ReciprocalTariffsPerc = $TaxRateDetails['ReciprocalTariffs'];
                    break;
                }
            }


            if ($dataRow['BLDate'] != '' && $dataRow['BLDate'] != null) {
                $blDate = new DateTime($dataRow['BLDate']);
                $startDate = new DateTime('2025-04-05');
                $endDate = new DateTime('2025-04-10');

                if ($blDate <= $endDate) {
                    $reciprocalSql = "SELECT * FROM `ReciprocalTariffs` WHERE PoID=$OrderID";
                    $reciprocalStmt = $this->conn->query($reciprocalSql);
                    $reciprocalStmt->execute();

                    $reciprocalRow = $reciprocalStmt->fetch(PDO::FETCH_ASSOC);
                    $ReciprocalTariff = $reciprocalRow['ReciprocalTariff'];
                    $TariffRate = $reciprocalRow['TariffRate'];

                    if (!empty($ReciprocalTariff) && !empty($TariffRate) && $ReciprocalTariff == 1) {
                        $ReciprocalTariffsPerc = $TariffRate;
                    }
                }
            }




            $CommercialCount = $dataRow['CommercialID'] ? $dataRow['CommercialID'] : 0;

            $PoGradeTable = 'ScrmPoGradeData';
            $PoGradeTableId = $dataRow['PiPoGradeId'];
            if ($dataRow['POCIGradeId']) {
                // $PoGradeTable = 'ScrmCIGradeData';
                // $PoGradeTableId = $dataRow['POCIGradeId'];
            }

            $ItemID = $dataRow['PiPoGradeId'];
            if ($CommercialCount == 0) {
                $Variety = $dataRow['Description'] ? $dataRow['Description'] : $dataRow['Variety'];
            } else {
                $Variety = $dataRow['CIDescription'] ? $dataRow['CIDescription'] : $dataRow['Variety'];
            }


            $Grade = $dataRow['Grade'];
            $VarietyID = $dataRow['VarietyID'];
            $PackingStyleID = $dataRow['PackingStyleID'];
            $Brand = $dataRow['Brand'];
            $Mcs = $dataRow['Mcs'];
            $ItemDetails = $ProductCache[$VarietyID];
            $SauceUsdValue = 0;
            $SauceLbsWeight = 0;
            if ($ItemDetails['HasSauce'] == 'Yes') {
                $PackingStyleID = $ItemDetails['PackingStyle'];
                $ShrimpPackingStyle = $ItemDetails['ShrimpPackingStyle'];
                $SaucePackingDetails = $PackingListCache[$ItemDetails['SaucePackingStyle']];
                $OzToLbs = $SaucePackingDetails['SlabWeight'] * $SaucePackingDetails['LbsFactor']; //0.1875
                $SauceLbsWeight = $SaucePackingDetails['TotalSlabs'] * $OzToLbs * $Mcs;
                $SauceUsdValue = $SauceLbsWeight * $dataRow['CupsPrice'];
            }
            $PackingDetails = $PackingListCache[$PackingStyleID];

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
            $AryShipmentLineitemwiseData['SaleInvoiceNumber'] = $dataRow['CustomsInvNo'];
            $AryShipmentLineitemwiseData['SaleInvoiceLineNumber'] = $dataRow['CustomsInvNo_Seq'] ? $dataRow['CustomsInvNo_Seq'] : '';
            $AryShipmentLineitemwiseData['SaleInvoiceDate'] = $this->utilController->getFormatedDatelines($dataRow['CInvDate']);
            $AryShipmentLineitemwiseData['CustomerName'] = $ConsigneeName;
            $AryShipmentLineitemwiseData['CustomerCountry'] = $CustomerCountry;
            $AryShipmentLineitemwiseData['CustomerZipCode'] = $ConsigneeZipCode;
            $AryShipmentLineitemwiseData['PurchaseOrderNumber'] = $dataRow['PoNo'];
            $AryShipmentLineitemwiseData['PurchaseOrderDate'] = $this->utilController->getFormatedDatelines($dataRow['PoDate']);
            $AryShipmentLineitemwiseData['DestinationState'] = $DestinationState;
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
            $AryShipmentLineitemwiseData['ProductDescriptionasinProduction'] = '-'; //$Variety . '-' . $PackingDetails['PackingStyle'];
            $AryShipmentLineitemwiseData['PartsofBody'] = $ItemDetails['ShortName'];
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
            $AryShipmentLineitemwiseData['DDPPrice'] = $dataRow['DdpPrice'] ? $dataRow['DdpPrice'] : 0;
            $AryShipmentLineitemwiseData['Remarks'] = $dataRow['Remarks'] ? $dataRow['Remarks'] : '';
            if ($DestinationCountry != 157) {
                $ReciprocalTariffsPerc = 0;
            }
            $AryShipmentLineitemwiseData['ReciprocalTariffs'] = $ReciprocalTariffsPerc;

            $AryFilteredCountriesList[] = [
                "id" => $DestinationCountry,
                "value" => $countryCache[$DestinationCountry]['CountryName']
            ];

            $AryFilteredPortsList[] = [
                "id" => $FinalDestinationId,
                "value" => $portCache[$FinalDestinationId]['PortName']
            ];

            $ArrayShipmentLineitemwiseData[] = $AryShipmentLineitemwiseData;
        }
        if (count($ArrayShipmentLineitemwiseData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $UniqueCountries = [];
        foreach ($AryFilteredCountriesList as $item) {
            $UniqueCountries[$item['id']] = $item;
        }

        $UniquePorts = [];
        foreach ($AryFilteredPortsList as $item) {
            $UniquePorts[$item['id']] = $item;
        }

        $data['ShipmentLineitemwiseData'] = $ArrayShipmentLineitemwiseData;
        $data['RecordsAval'] = $RecordsAval;
        $data['FilteredCountries'] = array_values($UniqueCountries);
        $data['FilteredPorts'] = array_values($UniquePorts);

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
        $existingLine = $this->auditLogger->fetchRowById('ScrmPoGradeData', 'ID', $ItemID);
        $sqlUpdateDDPPriceRemarks = "UPDATE `ScrmPoGradeData` SET DdpPrice='$DDPPrice', Remarks='$Remarks' WHERE ID='$ItemID'";
        $stmtUpdateDDPPriceRemarks = $this->conn->prepare($sqlUpdateDDPPriceRemarks);
        $stmtUpdateDDPPriceRemarks->execute();
        if ($stmtUpdateDDPPriceRemarks) {
            $updatedLine = $this->auditLogger->fetchRowById('ScrmPoGradeData', 'ID', $ItemID);
            $this->auditLogger->logUpdate('ScrmPoGradeData', $ItemID, $existingLine, $updatedLine, $RequestBody, [
                'ReferenceNo' => $ItemID,
                'Remarks' => 'Export line item DDP price updated',
            ]);
        }
        return "success";
    }
}
