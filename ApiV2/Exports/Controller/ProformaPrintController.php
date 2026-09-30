<?php



// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);



include_once '../Global/Util.php';



class ProformaPrintController
{



    private $conn;

    // private $DocType = "PI";

    private $utilController;

    private $appUtilController;

    private $ScrmPIData = "ScrmPIData";

    private $PrintHeader = "PROFORMA INVOICE";







    // Db connection

    public function __construct($db)
    {

        $this->conn = $db;

        $this->utilController = new UtilController($this->conn);

        $this->appUtilController = new AppUtilController($this->conn);
    }

    // GET ALL

    public function getProformaPrint($RequestBody)
    {





        $proformaID = $RequestBody['ProformaID'];

        $DocType = $RequestBody['DocType'];



        $sqlFields = " `PoID`, `InvNo`, `InvNumFull`, 

                        `InvDate`, `ExporterID`, `ProcessorID`, `LotNo`, 

                        `ConsigneeID`, `BuyerID`, `PoNo`, `PoDate`,

                         `DestinationCountry`,  `PoStatus`, `BankID`, 

                         `PortOfDischarge`, `FinalDestination`, `PaymentTerms`,

                          `ShippingTerms`, `DeliveryTerms`, `PreparedBy`, 

                           `ShipByDate`, `ShippedOutDate`, `Status`,

                            `QtyUnit`, `UnitCurrency`, `LoginId`, `ApprovedBy`,

                             `Remarks`, `ImporterID`, `NotifyPartyID`, `ReferenceNo`,

                              `Agent`, `Certification`, `PortofLoading`, `ShowBank`, `ShowFrozenWeight`,

                              `UltimateConsigneeID`, `Freight`, `PostedDate`, `IsEdit`,`ChargeName`,`ChargeAmount`";





        $sqlQuery = "SELECT  $sqlFields  FROM ScrmPIData where PoID= :proformaID";

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->bindParam(':proformaID', $proformaID);

        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);


        $InvDate = $dataRow['InvDate'];

        $ConsigneeID = $dataRow['ConsigneeID'];

        $BuyerID = $dataRow['BuyerID'];

        $UltimateConsigneeID = $dataRow['UltimateConsigneeID'];

        $NotifyPartyID = $dataRow['NotifyPartyID'];

        $ImporterID = $dataRow['ImporterID'];



        $ExporterID = $dataRow['ExporterID'];

        $ProcessorID = $dataRow['ProcessorID'];

        $CountryID = $dataRow['DestinationCountry'];

        $PortofLoading = $dataRow['PortofLoading'];

        $PortOfDischarge = $dataRow['PortOfDischarge'];

        $FinalDestination = $dataRow['FinalDestination'];

        $PaymentTermID = $dataRow['PaymentTerms'];

        $UnitCurrency = $dataRow['UnitCurrency'];

        $BankID = $dataRow['BankID'];

        $PoID = $dataRow['PoID'];

        $QtyUnit = $dataRow['QtyUnit'];

        $ShowBank = $dataRow['ShowBank'];

        $ChargeName = $dataRow['ChargeName'];

        $ChargePercentage = $dataRow['ChargeAmount'];

        $ShowFrozenWeight = $dataRow['ShowFrozenWeight'];



        $ShipByDate = $this->utilController->getFormatedDate($dataRow['ShipByDate']);



        $AryConsigneeDetails = $this->utilController->getConsigneeDetails($ConsigneeID);

        $AryBuyerDetails = $this->utilController->getBuyerDetails($BuyerID);

        $AryUltimateConsigneeDetails = $this->utilController->getUltimateConsigneeDetails($UltimateConsigneeID);

        // $AryNotifyDetails = $this->utilController->getNotifyPartyDetails($NotifyPartyID);

        $AryImporterOfRecordDetails = $this->utilController->getImporterRecordDetails($ImporterID);



        $AryExporterDetails = $this->utilController->getExporterDetails($ExporterID, $CountryID, $DocType);

        $AryProcessorDetails = $this->utilController->getExporterDetails($ProcessorID, $CountryID, $DocType);

        $AryLoadingPortDetails = $this->utilController->getPortDetails($PortofLoading, $CountryID, $DocType);

        $AryDischargePortDetails = $this->utilController->getPortDetails($PortOfDischarge, $CountryID, $DocType);

        $AryFinalDestination = $this->utilController->getPortDetails($FinalDestination, $CountryID, $DocType);

        $AryCountryDetails = $this->utilController->getCountryDetails($CountryID);

        $AryPaymentTermDetails = $this->utilController->getPaymentTermDetails($PaymentTermID);

        $AryCurrencyDetails = $this->utilController->getCurrencyDetails($UnitCurrency);

        $AryBankDetails = $this->utilController->getBankDetails($BankID);

        // $AryPriceDetails = $this->utilController->getPriceDetails($PoID, $QtyUnit);

        $AryPriceDetails = $this->utilController->getProformaPriceDetails($PoID, $QtyUnit);



        $InvoiceValue = $AryPriceDetails['InvoiceValue'];



        $ChargeAmount = $this->utilController->getProformaDiscountAmount($PoID);



        if ($ChargeAmount > 0) {

            // $ChargeAmount = ($InvoiceValue * $ChargePercentage) / 100;

            //$InvoiceValueAfterDiscount = $InvoiceValue;

            $BalanceAmount = $InvoiceValue - $ChargeAmount; // By Rantagiri 04/17/2023 5.44 

        } else {

            $BalanceAmount = $InvoiceValue;
        }

        $InvoiceValueWord = convert_number_to_words(number_format($BalanceAmount, 2), $AryCurrencyDetails['CurrencyName'], 'Cents');

        //$InvoiceValueWord = numberTowords($BalanceAmount,$AryCurrencyDetails['CurrencyName']);

        $ArrayShowLabels = $this->utilController->getInvoiceShowConditions($DocType, $CountryID);



        $Row['Labels'] = $this->utilController->getLabels($CountryID, $DocType);

        $aryLabelReq['DocType'] = $DocType;

        $aryLabelReq['Country'] = $CountryID;

        $aryLabelReq['UOM'] = $QtyUnit;

        $aryLabelReq['UnitPrice'] = $QtyUnit . '/' . $dataRow['ShippingTerms'];

        $aryLabelReq['Currency'] = $UnitCurrency;

        $aryLabelReq['Buyer'] = $ConsigneeID; // Edited by uma on 15-11-2023

        $aryItemLabels = $this->appUtilController->getItemTableLables($aryLabelReq);









        // $TableLabels['ItemDescription'] = 'Description of Goods';

        // $TableLabels['ItemID'] = 'ItemID';

        // $TableLabels['Grade'] = 'Grade';

        // $TableLabels['MCS'] = 'Cases';

        // $TableLabels['Weight'] = 'Quantity in ' . $QtyUnit;

        // $TableLabels['Weightt'] = 'Quantity in ' . $QtyUnit;

        // //$TableLabels['LBSWeight'] = 'Net Weight in '.$QtyUnit;

        // $TableLabels['UnitPrice'] = 'Rate per ' . $QtyUnit;

        // $TableLabels['ItemPrice'] = 'Amount in ' . $UnitCurrency;

        // $LableKeys = array("ItemDescription", "Grade", "MCS", "Weight", "Weightt", "UnitPrice", "ItemPrice");



        $Row['LableKeys'] = $aryItemLabels['TableKeys']; // $LableKeys;

        $Row['TableLabels'] = $aryItemLabels['TableLables']; // $TableLabels; 

        $Row['ItemsGroup'] = $this->getProformaBreakUp($proformaID, $QtyUnit);

        //$Row['GrandTotal'] = $this->getGrandTotals($proformaID, $QtyUnit);

        $Row['GrandTotal'] = $this->getGrandTotals($proformaID, $QtyUnit);

        $TotalGrossWtKgs = $this->getGrossweight($proformaID);

        if ($TotalGrossWtKgs != 0) {
            if ($QtyUnit == 'KGS') {
                $GrossWeightKgs = $TotalGrossWtKgs;
                $GrossWeightLbs = $TotalGrossWtKgs * 2.204;
            } else {
                $GrossWeightKgs = $TotalGrossWtKgs * 0.454;
                $GrossWeightLbs = $TotalGrossWtKgs;
            }
        } else {
            $GrossWeightKgs = $AryPriceDetails['GrossWeightKGS'];
            $GrossWeightLbs = $AryPriceDetails['GrossWeightLBS'];
        }

        /***showlabels***/

        if ($ShowBank == 1) {
            $BankDetails = $AryBankDetails['BankDetails'];
        }

        if ($ArrayShowLabels['NetWeightLBS'] == 1) {
            $NetWeightLBS = number_format($AryPriceDetails['NetWeightLBS'], 3);
        }

        if ($ArrayShowLabels['GrossWeightLBS'] == 1) {
            $GrossWeightLBS = number_format($GrossWeightLbs, 3);
        }

        if ($ArrayShowLabels['Usfda'] == 1) {
            $USFDANumber = $AryProcessorDetails['ExpUsfdaNo'];
        }

        if ($PoID == '1383') {
            $ArrayShowLabels['FrozenWeightkgs'] = 0;
        }

        if ($ArrayShowLabels['FrozenWeightkgs'] == 1) {
            if ($InvDate >= '28-05-2026' && $ShowFrozenWeight != 1) {
                $NetWeightKGS = number_format($AryPriceDetails['NetWeightKGS'], 3);
            } else {
                $NetWeightKGS = number_format($AryPriceDetails['FrozenWeightKgs'], 3);
                $FrozenWeightKGS = number_format($AryPriceDetails['NetWeightKGS'], 3);
            }
        } else {
            $NetWeightKGS = number_format($AryPriceDetails['NetWeightKGS'], 3);
        }


        /***showlabels***/

        // var_dump($AryExporterDetails);

        $Row['Vessel'] = '';

        $Row['CountryOfOrgin'] = 'INDIA';

        $Row['CountryOfOrginCode'] = 'IN';

        $Row['QtyUnits'] = $QtyUnit;

        $Row['ShipByDate'] = $ShipByDate;

        $Row['AmountInWords'] = $InvoiceValueWord;

        $Row['PrintHeader'] = $this->PrintHeader;

        $Row['InvoiceNumber'] = $dataRow['InvNumFull'];

        $Row['InvoiceDate'] = $this->utilController->getFormatedDate($dataRow['InvDate']);

        $Row['PoNumber'] = $dataRow['PoNo'];

        $Row['PoDate'] = $this->utilController->getFormatedDateEmpty($dataRow['PoDate']);

        $Row['ReferenceNumber'] = $dataRow['ReferenceNo'];

        $Row['Certification'] = $dataRow['Certification'];

        $Row['DeliveryTerms'] = $dataRow['DeliveryTerms'];

        $Row['PaymentTerms'] = $AryPaymentTermDetails['PaymentTerm'];

        $Row['ShippingTerms'] = $dataRow['ShippingTerms'];

        $Row['Declearation'] = 'We declare that this invoice shows the actual price of goods described

            and that all particulars are true and correct.

            '; // Doubt

        // $Row['Declearation'] = $this->utilController->getInvoiceDescription($dataRow['InvDate'], $ExporterID)['ProformaNote'];
        $Row['SignFor'] = 'For  ' . $AryExporterDetails['ExporterName']; // Doubt

        $Row['SignDescription'] = 'AUTHORISED SIGNATORY '; // Doubt

        $Row['SignUrl'] = ''; // Need To get From AuthDb

        //$Row['Remarks'] = $dataRow['Remarks'] ? $dataRow['Remarks'] : 'No Marks';

        $Row['Remarks'] = $dataRow['Remarks'] ? $dataRow['Remarks'] : '';



        $Row['ExporterName'] = $AryExporterDetails['ExporterName'];

        $Row['ExporterAddress'] = $AryExporterDetails['ExporterAddress'];

        $Row['ExporterIeCode'] = $AryExporterDetails['ExpIeCode'];

        $Row['SignName'] = $AryExporterDetails['AuthSign'];

        $Row['ProcessorName'] = $AryProcessorDetails['ExporterName'];

        $Row['ProcessorAddress'] = $AryProcessorDetails['ExporterAddress'];

        $Row['USFDANumber'] = $USFDANumber;

        $Row['ApprovalNumber'] = $AryProcessorDetails['ExpApprovalNo'];

        $Row['NetWeightKGS'] = $NetWeightKGS;

        // $Row['GrossWeightKGS'] = number_format($AryPriceDetails['GrossWeightKGS'], 3);

        $Row['GrossWeightKGS'] = number_format($GrossWeightKgs, 3); //uma

        $Row['FrozenWeightKGS'] = $FrozenWeightKGS;

        $Row['NetWeightLBS'] = $NetWeightLBS;

        $Row['GrossWeightLBS'] = $GrossWeightLBS;

        $Row['ShowFrozenWeight'] = $ShowFrozenWeight;

        $Row['InvoiceValue'] = $AryPriceDetails['InvoiceValue'];

        //$Row['BankDetails'] = $AryBankDetails['BankDetails'];

        $Row['BankDetails'] = $BankDetails;

        $Row['BankRoutionDetails'] = $AryBankDetails['BankRoutionDetails'];

        $Row['BankNegotiationDetails'] = $AryBankDetails['BankNegotiationDetails'];

        $Row['CurrencyCode'] = $AryCurrencyDetails['CurrencyCode']; // $AryCurrencyDetails['CurrencyName'];

        $Row['CurrencySymbol'] = $AryCurrencyDetails['CurrenySymbol'];

        $Row['CountryOfDestination'] = $AryCountryDetails['CountryName'];

        $Row['PortOfLoading'] = $AryLoadingPortDetails['PortName'] . ',INDIA '; //. $AryCountryDetails['CountryName'];

        $Row['PortOfDischarge'] = $AryDischargePortDetails['PortName'] . ' , ' . $AryCountryDetails['CountryName'];

        $Row['FinalDestination'] = $AryFinalDestination['PortName'] . ' , ' . $AryCountryDetails['CountryName'];

        $Row['ShippingTermsFinalDestination'] = $AryFinalDestination['PortName'] . ' , ' . $AryCountryDetails['CountryName'];
        if ($dataRow['ShippingTerms'] == 'FOB') {
            $Row['ShippingTermsFinalDestination'] = 'VISAKHAPATNAM ,INDIA';
        }


        $Row['ConsigneeName'] = $AryConsigneeDetails['ConsigneeName'];

        $Row['ConsigneeAddress'] = $AryConsigneeDetails['ConsigneeAddress'];

        $AryBuyerDetails = $this->utilController->getBuyerDetails($BuyerID);

        $AryUltimateConsigneeDetails = $this->utilController->getUltimateConsigneeDetails($UltimateConsigneeID);

        if ($ArrayShowLabels['NotifyPrty'] == 1) {
            $AryNotifyDetails = $this->utilController->getNotifyPartyDetails($NotifyPartyID);
        }

        $AryImporterOfRecordDetails = $this->utilController->getImporterRecordDetails($ImporterID);

        if ($AryBuyerDetails != null) {
            $Row['BuyerName'] = $AryBuyerDetails['BuyerName'];
            $Row['BuyerAddress'] = $AryBuyerDetails['BuyerAddress'];
        } else {
            $Row['BuyerName'] = null;
            $Row['BuyerAddress'] = null;
        }


        if ($AryUltimateConsigneeDetails != null) {
            $Row['UltimateConsigneeName'] = $AryConsigneeDetails['ConsigneeName'];
            $Row['UltimateConsigneeAddress'] = $AryConsigneeDetails['ConsigneeAddress'];
        } else {
            $Row['UltimateConsigneeName'] = null;
            $Row['UltimateConsigneeAddress'] = null;
        }


        if ($AryNotifyDetails != null) {
            $Row['NotifyPartyName'] = $AryNotifyDetails['NotifyPartyName'];
            $Row['NotifyPartyNameAddress'] = $AryNotifyDetails['NPartyAddress'];
        } else {
            $Row['NotifyPartyName'] = null;
            $Row['NotifyPartyNameAddress'] = null;
        }


        if ($AryImporterOfRecordDetails != null) {
            $Row['ImporterName'] = $AryImporterOfRecordDetails['ImporterName'];
            $Row['ImporterAddress'] = $AryImporterOfRecordDetails['ImporterAddress'];
        } else {
            $Row['ImporterName'] = null;
            $Row['ImporterAddress'] = null;
        }


        $Row['ChargeName'] = $ChargeName . ':';

        $Row['ChargeAmount'] = number_format($ChargeAmount, 2);

        $Row['BalanceAmount'] = number_format($BalanceAmount, 2);

        return $Row;
    }


    public function getProformaBreakUp($proformaID, $QtyUnit)
    {

        $SqlSpecies = "SELECT GROUP_CONCAT(DISTINCT(SpeciesName)) as SpeciesName FROM `ScrmPoGradeData` as SPDG 

        LEFT JOIN ScrmPIVarietyList as SPVL ON SPDG.VarietyID=SPVL.VarietyID 

        left join ScrmSpeciesMaster as SSM on SSM.ShortCode=SPVL.Species WHERE InvId =:proformaID";



        $stmtSpecies = $this->conn->prepare($SqlSpecies);

        $stmtSpecies->bindParam(':proformaID', $proformaID);

        $stmtSpecies->execute();

        $dataRowSpecies = $stmtSpecies->fetch(PDO::FETCH_ASSOC);

        $Species = $dataRowSpecies['SpeciesName'];

        $sqlFields = "PGD.ID,sum(PIMcs) As VarietyMcs, BuyerItemCode, Description as Description,Variety,PrintStatus as BrandPrintStatus,
            PGD.Treatment,PGD.Grade,Aps.PackingStyle,Brand,PGD.PackingStyleID,BAP,sum(PIGrossWt) as TotalGrossweight";

        $sqlQuery = " SELECT  $sqlFields FROM `ScrmPoGradeData` as PGD

        left join `AdminPackingStyles` as Aps on PGD.PackingStyleID=Aps.PCKID

        left join `ScrmBrandsMaster` SBM on  SBM.BrandName=PGD.Brand

        WHERE InvId= :proformaID  GROUP by Variety,PackingStyleID,Brand Order by PGD.ID asc";

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->bindParam(':proformaID', $proformaID);

        $stmt->execute();

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ItemGroup = [];

            if ($dataRow['BrandPrintStatus'] == 1) {
                $ItemGroup['Brand'] = $dataRow['Brand'];
            }

            if ($dataRow['Description'] != '') {
                $ItemGroup['ItemDescription'] = $dataRow['Description'];
            } else {
                $ItemGroup['ItemDescription'] = $dataRow['Variety'];
            }

            $ItemGroup['ShowSubTotal'] = false;

            $ItemGroup['PackingStyle'] = $dataRow['PackingStyle'];

            //$ItemGroup['Brand'] = $dataRow['Brand'];

            $ItemGroup['Species'] = $Species;

            $ItemGroup['Product'] = 'SHRIMPS';

            $ItemGroup['ItemsList'] = $this->getItemBreakUp($proformaID, $dataRow['Brand'], $dataRow['Variety'], $dataRow['PackingStyleID'], $QtyUnit, $dataRow['PackingStyle']);

            //print_r($ItemGroup['ItemsList']);

            $ItemData[] = $ItemGroup;
        }

        // $ItemGroup['ItemsList'] = $this->getItemBreakUp($proformaID, $dataRow['Brand'], $dataRow['Variety'], $dataRow['PackingStyleID']);

        // $ItemData[] = $ItemGroup;

        return $ItemData;
    }

    public function getGrossweight($proformaID)
    {

        $SqlGrossweight = "SELECT sum(PIGrossWt) as TotalGrossweight FROM `ScrmPoGradeData` WHERE InvId =:proformaID";

        $stmtGrossweight = $this->conn->prepare($SqlGrossweight);

        $stmtGrossweight->bindParam(':proformaID', $proformaID);

        $stmtGrossweight->execute();

        $dataRowGrossweight = $stmtGrossweight->fetch(PDO::FETCH_ASSOC);

        $TotalGrossweight = $dataRowGrossweight['TotalGrossweight'];

        return $TotalGrossweight;
    }



    function getItemBreakUp($proformaID, $Brand, $Variety, $PackingStyleID, $QtyUnit, $PackingStyle)
    {

        $utilController = new UtilController($this->conn);

        $sqlFields = "PGD.ID as ItemID,PGD.Grade,PGD.PIMcs as MCS,PGD.Brand,PGD.GlazePerc,PGD.FreezingType,PGD.Treatment,

        PGD.Price as UnitPrice,PGD.CodeType,PGD.BuyerItemCode";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmPoGradeData` as PGD

        left join AdminPackingStyles  as PS on PGD.PackingStyleID=PS.PCKID

        WHERE InvId= :proformaID and Variety = :Variety and Brand=:Brand 

        and	PGD.PackingStyleID=:PackingStyleID order by abs(PGD.Grade)";
        // echo $sqlQuery;
        // exit;

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->bindParam(':proformaID', $proformaID);

        $stmt->bindParam(':Variety', $Variety);

        $stmt->bindParam(':Brand', $Brand);

        $stmt->bindParam(':PackingStyleID', $PackingStyleID);

        $stmt->execute();

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArySubBreakUp[] = $dataRow['ItemID'];

            $ItemTotals = $utilController->getProformaItemWeightandPrice($dataRow['ItemID'], $QtyUnit);

            // $dataRow['ItemTotals']=$ItemTotals;

            // $dataRow['Weight'] = round($ItemTotals['Weight'],2);

            $dataRow['Weight'] = number_format($ItemTotals['Weight'], 3);

            $dataRow['WeightKG'] = number_format($ItemTotals['NWeightKG'], 3);

            $dataRow['FWeightKG'] = number_format($ItemTotals['FrozenWeight'], 3);

            $dataRow['ItemPrice'] = number_format($ItemTotals['ItemPrice'], 2);

            $dataRow['NWeightKG'] = number_format($ItemTotals['NWeightKG'], 2);

            $dataRow['NWeightLB'] = number_format($ItemTotals['NWeightLB'], 2);

            $dataRow['GWeightKG'] = number_format($ItemTotals['GWeightKG'], 2);

            $dataRow['GWeightLB'] = number_format($ItemTotals['GWeightLB'], 2);

            $dataRow['FWeightKG'] = number_format($ItemTotals['FWeightKG'], 2);

            $dataRow['FWeightLB'] = number_format($ItemTotals['FWeightLB'], 2);

            $dataRow['Packing'] = $PackingStyle; //Added by uma

            $ItemData[] = $dataRow;
        }

        $SubVarityBrkUp = implode(',', $ArySubBreakUp);

        $ItemTotals = $utilController->getProformaItemWeightandPrice($SubVarityBrkUp, $QtyUnit);

        $dataRow['Lable'] = 'Sub Total';

        $dataRow['ItemDescription'] = '';

        $dataRow['ItemID'] = 'Sub Total';

        $dataRow['Grade'] = '';

        $dataRow['MCS'] = $ItemTotals['MCS'];

        $dataRow['Weight'] = number_format($ItemTotals['Weight'], 3);

        $dataRow['WeightKG'] = number_format($ItemTotals['NWeightKG'], 3);

        $dataRow['FWeightKG'] = number_format($ItemTotals['FWeightKG'], 3);

        $dataRow['UnitPrice'] = '';

        $dataRow['ItemPrice'] = number_format($ItemTotals['ItemPrice'], 2);

        $dataRow['NWeightKG'] = number_format($ItemTotals['NWeightKG'], 2);

        $dataRow['NWeightLB'] = number_format($ItemTotals['NWeightLB'], 2);

        $dataRow['GWeightKG'] = number_format($ItemTotals['GWeightKG'], 2);

        $dataRow['GWeightLB'] = number_format($ItemTotals['GWeightLB'], 2);

        $dataRow['FWeightKG'] = number_format($ItemTotals['FWeightKG'], 2);

        $dataRow['FWeightLB'] = number_format($ItemTotals['FWeightLB'], 2);

        $ItemData[] = $dataRow;

        return $ItemData;
    }



    function getGrandTotals($proformaID, $QtyUnit)
    {

        $utilController = new UtilController($this->conn);

        $AryPriceDetails = $this->utilController->getProformaPriceDetails($proformaID, $QtyUnit);

        $sqlFields = "PGD.ID as ItemID,PGD.Grade,PGD.PIMcs as MCS,PGD.Brand,PGD.GlazePerc,PGD.FreezingType,PGD.Treatment,

        PGD.Price as UnitPrice,PGD.CodeType,PGD.BuyerItemCode";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmPoGradeData` as PGD

        left join AdminPackingStyles  as PS on PGD.PackingStyleID=PS.PCKID

        WHERE InvId= :proformaID  order by abs(PGD.Grade)";

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->bindParam(':proformaID', $proformaID);

        $stmt->execute();

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArySubBreakUp[] = $dataRow['ItemID'];

            // $TotalGrossWt=$dataRow['GrossWt'];

        }

        $SubVarityBrkUp = implode(',', $ArySubBreakUp);

        $ItemTotals = $utilController->getProformaItemWeightandPrice($SubVarityBrkUp, $QtyUnit);

        $dataRow['Lable'] = 'Grand Total';

        $dataRow['Grade'] = '';

        $dataRow['MCS'] = $ItemTotals['PIMcs'];

        $dataRow['Weight'] = number_format($ItemTotals['Weight'], 3);

        $dataRow['WeightKG'] = number_format($ItemTotals['NWeightKG'], 3);

        $dataRow['FWeightKG'] = number_format($ItemTotals['FWeightKG'], 3);

        $dataRow['UnitPrice'] = '';

        // $dataRow['ItemPrice'] = $ItemTotals['ItemPrice'];

        $dataRow['ItemPrice'] = number_format($AryPriceDetails['InvoiceValue'], 2);

        $dataRow['NWeightKG'] = number_format($ItemTotals['NWeightKG'], 2);

        $dataRow['NWeightLB'] = number_format($ItemTotals['NWeightLB'], 2);

        $dataRow['GWeightKG'] = number_format($ItemTotals['GWeightKG'], 2);

        $dataRow['GWeightLB'] = number_format($ItemTotals['GWeightLB'], 2);

        $dataRow['FWeightKG'] = number_format($ItemTotals['FWeightKG'], 2);

        $dataRow['FWeightLB'] = number_format($ItemTotals['FWeightLB'], 2);

        return $dataRow;
    }
}
