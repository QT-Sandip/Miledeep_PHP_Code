<?php



// ini_set('display_errors', 1);

// ini_set('display_startup_errors', 1);

// error_reporting(E_ALL);





//include_once '../Global/Util.php';



include_once 'AppUtilController.php';

include_once 'UtilController.php';

include_once 'CommercialPrintController.php';



class CommercialPrintController
{



    private $conn;

    // private $DocType = "CMI";

    private $utilController;

    private $appUtilController;

    private $ScrmPIData = "ScrmPoData";

    private $PrintHeader = "COMMERCIAL INVOICE";







    // Db connection

    public function __construct($db)
    {

        $this->conn = $db;

        $this->utilController = new UtilController($this->conn);

        $this->appUtilController = new AppUtilController($this->conn);

    }

    // GET ALL

    public function getCommerccialPrint($RequestBody)
    {





        $proformaID = $RequestBody['ProformaID'];

        $DocType = $RequestBody['DocType'];



        $ScrmPIData = $this->ScrmPIData;



        $sqlFields = " `PoID`, `InvNo`, `InvNumFull`, 

                        `InvDate`, `ExporterID`, `ProcessorID`, `LotNo`, 

                        `ConsigneeID`, `BuyerID`, `PoNo`, `PoDate`,

                         `DestinationCountry`,  `PoStatus`, `BankID`, 

                         `PortOfDischarge`, `FinalDestination`, `PaymentTerms`,

                          `ShippingTerms`, `DeliveryTerms`, `PreparedBy`, 

                           `ShipByDate`, `ShippedOutDate`, `Status`,

                            `QtyUnit`, `UnitCurrency`, `LoginId`, `ApprovedBy`,

                             `Remarks`, `ImporterID`, `NotifyPartyID`, `ReferenceNo`,

                              `Agent`, `Certification`, `PortofLoading`, `ShowBank`, 

                              `UltimateConsigneeID`, `Freight`, `PostedDate`, `IsEdit`,CountryType,ChargeName ";





        $sqlQuery = "SELECT  $sqlFields  FROM $ScrmPIData as SPD left join ScrmCountriesList as SCL on SCL.id=SPD.DestinationCountry

         where PoID= :proformaID";

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->bindParam(':proformaID', $proformaID);

        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);



        $ConsigneeID = $dataRow['ConsigneeID'];

        $BuyerID = $dataRow['BuyerID'];

        $UltimateConsigneeID = $dataRow['UltimateConsigneeID'];

        $NotifyPartyID = $dataRow['NotifyPartyID'];

        $ImporterID = $dataRow['ImporterID'];

        $CountryType = $dataRow['CountryType'];



        $ExporterID = $dataRow['ExporterID'];

        $ProcessorID = $dataRow['ProcessorID'];

        $CountryID = $dataRow['DestinationCountry'];

        $PortofLoading = $dataRow['PortofLoading'];

        $PortOfDischarge = $dataRow['PortOfDischarge'];

        $FinalDestination = $dataRow['FinalDestination'];

        // $PaymentTermID = $dataRow['PaymentTerms'];

        $UnitCurrency = $dataRow['UnitCurrency'];

        //$BankID = $dataRow['BankID'];

        $PoID = $dataRow['PoID'];

        $QtyUnit = $dataRow['QtyUnit'];

        $ChargeName = $dataRow['ChargeName'];

        // $ChargeAmount = $dataRow['ChargeAmount'];





        $ShipByDate = $this->utilController->getFormatedDate($dataRow['ShipByDate']);



        $AryConsigneeDetails = $this->utilController->getConsigneeDetails($ConsigneeID);

        $AryBuyerDetails = $this->utilController->getBuyerDetails($BuyerID);

        $AryUltimateConsigneeDetails = $this->utilController->getUltimateConsigneeDetails($UltimateConsigneeID);

        // $AryNotifyDetails = $this->utilController->getNotifyPartyDetails($NotifyPartyID);

        $AryImporterOfRecordDetails = $this->utilController->getImporterRecordDetails($ImporterID);



        // $AryExporterDetails = $this->utilController->getExporterDetails($ExporterID, $CountryID, $this->DocType);

        // $AryProcessorDetails = $this->utilController->getExporterDetails($ProcessorID, $CountryID, $this->DocType);

        // $AryLoadingPortDetails = $this->utilController->getPortDetails($PortofLoading, $CountryID, $this->DocType);

        // $AryDischargePortDetails = $this->utilController->getPortDetails($PortOfDischarge, $CountryID, $this->DocType);

        // $AryFinalDestination = $this->utilController->getPortDetails($FinalDestination, $CountryID, $this->DocType);



        $AryExporterDetails = $this->utilController->getExporterDetails($ExporterID, $CountryID, $DocType);

        $AryProcessorDetails = $this->utilController->getExporterDetails($ProcessorID, $CountryID, $DocType);

        $AryLoadingPortDetails = $this->utilController->getPortDetails($PortofLoading, $CountryID, $DocType);

        $AryDischargePortDetails = $this->utilController->getPortDetails($PortOfDischarge, $CountryID, $DocType);

        $AryFinalDestination = $this->utilController->getPortDetails($FinalDestination, $CountryID, $DocType);

        $AryCountryDetails = $this->utilController->getCountryDetails($CountryID);

        $AryCurrencyDetails = $this->utilController->getCurrencyDetails($UnitCurrency);



        $AryPriceDetails = $this->utilController->getPriceDetails($PoID, $QtyUnit);

        $AryDutyDetails = $this->utilController->getPaymentValues($PoID, $DocType);

        $AryCustomInvDetails = $this->getCustomInvoiceDetails($PoID);

        // $InvoiceValueWord = convert_number_to_words(number_format($AryPriceDetails['InvoiceValue'], 2), $AryCurrencyDetails['CurrencyName'], 'Cents');

        $ArrayShowLabels = $this->utilController->getInvoiceShowConditions($DocType, $CountryID);







        $ComShippingTerms = $AryCustomInvDetails['ComShippingTerms'];

        if ($ComShippingTerms != '') {

            $ShippingTerms = $ComShippingTerms;

        } else {

            $ShippingTerms = $dataRow['ShippingTerms'];

        }





        $ComPaymentTermID = $AryCustomInvDetails['ComPaymentTerms'];

        if ($ComPaymentTermID != '') {

            $PaymentTermID = $ComPaymentTermID;

        } else {

            $PaymentTermID = $dataRow['PaymentTerms'];

        }





        $ComBankID = $AryCustomInvDetails['BankID'];

        if ($ComBankID['BankID'] == 0) {

            $BankID = $dataRow['BankID'];

        } else {

            $BankID = $ComBankID;

        }



        $AryPaymentTermDetails = $this->utilController->getPaymentTermDetails($PaymentTermID);

        $AryBankDetails = $this->utilController->getBankDetails($BankID);











        $Row['Labels'] = $this->utilController->getLabels($CountryID, $DocType);

        $aryLabelReq['DocType'] = $DocType;

        $aryLabelReq['Country'] = $CountryID;

        $aryLabelReq['UOM'] = $QtyUnit;

        // $aryLabelReq['UnitPrice'] = $QtyUnit . '/' . $dataRow['ShippingTerms'];

        $aryLabelReq['UnitPrice'] = $QtyUnit . '/' . $ShippingTerms;

        $aryLabelReq['Currency'] = $UnitCurrency;

        $aryItemLabels = $this->appUtilController->getItemTableLables($aryLabelReq);



        /*

            $TableLabels['ItemDescription'] = 'Description of Goods';

            $TableLabels['ItemID'] = 'ItemID';

            $TableLabels['Grade'] = 'Grade';

            $TableLabels['MCS'] = 'Cases';

            $TableLabels['Weight'] = 'Quantity in '.$QtyUnit;

            $TableLabels['Weightt'] = 'Quantity in '.$QtyUnit;

            //$TableLabels['LBSWeight'] = 'Net Weight in '.$QtyUnit;

            $TableLabels['UnitPrice'] = 'Rate per '.$QtyUnit;

            $TableLabels['ItemPrice'] = 'Amount in '.$UnitCurrency;

            $LableKeys=array("ItemDescription","Grade","MCS","Weight","Weightt","UnitPrice","ItemPrice");*/





        $Row['LableKeys'] = $aryItemLabels['TableKeys']; // $LableKeys;

        $Row['TableLabels'] = $aryItemLabels['TableLables']; // $TableLabels;

        $Row['ItemsGroup'] = $this->getProformaBreakUp($proformaID, $QtyUnit);

        $Row['GrandTotal'] = $this->getGrandTotals($proformaID, $QtyUnit);





        $AryDutyDetails['FrieghtCharge'] = number_format($AryDutyDetails['FrieghtCharge'], 2);

        $AryDutyDetails['InvValueUSD'] = number_format($AryDutyDetails['InvValueUSD'], 2);

        $AryDutyDetails['FOBValueUSD'] = number_format($AryDutyDetails['FOBValueUSD'], 2);

        $AryDutyDetails['FOBValueINR'] = number_format($AryDutyDetails['FOBValueINR'], 2);

        $AryDutyDetails['InvValueINR'] = number_format($AryDutyDetails['InvValueINR'], 2);

        $AryDutyDetails['DutiesPrice'] = number_format($AryDutyDetails['DutiesPrice'], 2);

        $AryDutyDetails['ReciprocalTariffsFee'] = number_format($AryDutyDetails['ReciprocalTariffsFee'], 2);

        $AryDutyDetails['AntidumpingandPortChargesPrice'] = number_format($AryDutyDetails['AntidumpingandPortChargesPrice'], 2);

        $AryDutyDetails['CountervailingDuty'] = number_format($AryDutyDetails['CountervailingDuty'], 2);

        $AryDutyDetails['InvValueUSDWithOutDuties'] = number_format($AryDutyDetails['InvValueUSDWithOutDuties'], 2);

        $Row['PaymentDetails'] = $AryDutyDetails;

        // var_dump($AryExporterDetails);



        /***uma (17-01-2023) */

        $TotalGrossWtKgs = $AryCustomInvDetails['TotalGrossWt'];

        if ($TotalGrossWtKgs != 0) {
            if ($QtyUnit == 'KGS') {
                $GrossWeightKgs = $TotalGrossWtKgs;
                $GrossWeightLbs = $TotalGrossWtKgs / 0.454;
            } else {
                $GrossWeightKgs = $TotalGrossWtKgs * 0.454;
                $GrossWeightLbs = $TotalGrossWtKgs;
            }
        } else {
            $GrossWeightKgs = $AryPriceDetails['GrossWeightKGS'];
            $GrossWeightLbs = $AryPriceDetails['GrossWeightLBS'];
        }

        // if ($TotalGrossWtKgs != 0) {

        //     if ($QtyUnit == 'KGS') {

        //         $GrossWeightKgs = $TotalGrossWtKgs;

        //         $GrossWeightLbs = $TotalGrossWtKgs * 2.204;

        //     } else {

        //         $GrossWeightKgs = $TotalGrossWtKgs * 0.454;

        //         $GrossWeightLbs = $TotalGrossWtKgs;

        //     }

        // } else {

        //     $GrossWeightKgs = $AryPriceDetails['GrossWeightKGS'];

        //     $GrossWeightLbs = $AryPriceDetails['GrossWeightLBS'];

        // }

        /***uma (17-01-2023) */







        /***showlabels***/

        if ($ArrayShowLabels['NetWeightLBS'] == 1) {

            $NetWeightLBS = number_format($AryPriceDetails['NetWeightLBS'], 3);

        }

        if ($ArrayShowLabels['GrossWeightLBS'] == 1) {

            //$GrossWeightLBS = number_format($AryPriceDetails['GrossWeightLBS'], 3);

            $GrossWeightLBS = number_format($GrossWeightLbs, 3);

        }



        if ($ArrayShowLabels['Usfda'] == 1) {

            $USFDANumber = $AryProcessorDetails['ExpUsfdaNo'];

        }



        if ($AryCustomInvDetails['ShowBank'] == 1) {

            $BankDetails = $AryBankDetails['BankDetails'];

            $BankRoutionDetails = $AryBankDetails['BankRoutionDetails'];

            $BankNegotiationDetails = $AryBankDetails['BankNegotiationDetails'];

        }

        if ($CountryID == '157') { //antidumping charges display only for usa

            $ShowAntiDumping = 1;

        }



        if ($ArrayShowLabels['FrozenWeightkgs'] == 1) {

            $NetWeightKGS = number_format($AryPriceDetails['FrozenWeightKgs'], 3);

            $FrozenWeightKGS = number_format($AryPriceDetails['NetWeightKGS'], 3);

        } else {

            $NetWeightKGS = number_format($AryPriceDetails['NetWeightKGS'], 3);

        }

        if ($CountryType == 2) {

            $EUSelfCertification = 1;



        }



        $AdvanceAmount = $AryCustomInvDetails['AdvanceAmount'];

        //$AdvanceAmount=$AryCustomInvDetails['AdvanceAmount'];

        /***showlabels***/

        $Row['ShowAntiDumping'] = $ShowAntiDumping;

        $Row['ShowEUSelfCertification'] = $EUSelfCertification;

        $Row['Vessel'] = $AryCustomInvDetails['VesselNumber'];

        $Row['CountryOfOrgin'] = 'INDIA';

        $Row['QtyUnits'] = $QtyUnit;

        $Row['ShipByDate'] = $ShipByDate;



        $Row['PrintHeader'] = $this->PrintHeader;



        $Row['PoNumber'] = $dataRow['PoNo'];

        $Row['PoDate'] = $this->utilController->getFormatedDate($dataRow['PoDate']);

        $Row['ReferenceNumber'] = $dataRow['ReferenceNo'];

        $Row['Certification'] = $dataRow['Certification'];

        $Row['DeliveryTerms'] = $dataRow['DeliveryTerms'];

        $Row['PaymentTerms'] = $AryPaymentTermDetails['PaymentTerm'];

        //$Row['ShippingTerms'] = $dataRow['ShippingTerms'];

        $Row['ShippingTerms'] = $ShippingTerms;

        $Row['Declearation'] = 'We declare that this invoice shows the actual price of goods described and that all particulars are true and correct.'; // Doubt

        //$Row['Declearation'] = $this->utilController->getInvoiceDescription($dataRow['InvDate'], $ExporterID)['CommercialNote'];

        $Row['SignFor'] = 'For  ' . $AryExporterDetails['ExporterName']; // Doubt

        $Row['SignDescription'] = 'AUTHORISED SIGNATORY '; // Doubt

        $Row['SignUrl'] = ''; // Need To get From AuthDb

        $Row['Remarks'] = $dataRow['Remarks'] ? $dataRow['Remarks'] : 'No Marks';



        $Row['ExporterName'] = $AryExporterDetails['ExporterName'];

        $Row['ExporterAddress'] = $AryExporterDetails['ExporterAddress'];

        $Row['ExporterIeCode'] = $AryExporterDetails['ExpIeCode'];

        $Row['SignName'] = $AryExporterDetails['AuthSign'];

        $Row['ProcessorName'] = $AryProcessorDetails['ExporterName'];

        $Row['ProcessorAddress'] = $AryProcessorDetails['ExporterAddress'];

        $Row['USFDANumber'] = $USFDANumber;

        $Row['ApprovalNumber'] = $AryProcessorDetails['ExpApprovalNo'];



        $Row['NetWeightKGS'] = $NetWeightKGS;

        //$Row['GrossWeightKGS'] = number_format($AryPriceDetails['GrossWeightKGS'], 3);

        $Row['GrossWeightKGS'] = number_format($GrossWeightKgs, 3);

        $Row['FrozenWeightKGS'] = $FrozenWeightKGS;



        $Row['NetWeightLBS'] = $NetWeightLBS;

        $Row['GrossWeightLBS'] = $GrossWeightLBS;

        $Row['InvoiceValue'] = $AryPriceDetails['InvoiceValue'];

        $Row['AdvanceAmount'] = number_format($AdvanceAmount, 2);



        $BalanceAmount = $AryPriceDetails['InvoiceValue'] - $AdvanceAmount - $AryDutyDetails['DiscountAmount'];

        $Row['BalanceAmount'] = number_format($BalanceAmount, 2);



        //if($DocType=='CMI' && $AdvanceAmount!=''){

        $InvoiceValueWord = convert_number_to_words(number_format($BalanceAmount, 2), $AryCurrencyDetails['CurrencyName'], 'Cents');

        //$InvoiceValueWord = numberTowords($BalanceAmount, $AryCurrencyDetails['CurrencyName']);



        // }



        // else{

        // $InvoiceValueWord = convert_number_to_words(number_format($AryPriceDetails['InvoiceValue'], 2), $AryCurrencyDetails['CurrencyName'], 'Cents');



        // }

        $Row['AmountInWords'] = $InvoiceValueWord;

        $Row['BankDetails'] = $BankDetails;

        $Row['BankRoutionDetails'] = $BankRoutionDetails;

        $Row['BankNegotiationDetails'] = $BankNegotiationDetails;



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







        $Row['InvoiceNumber'] = $AryCustomInvDetails['CustomsInvNo'];

        $Row['InvoiceDate'] = $this->utilController->getFormatedDate($AryCustomInvDetails['CInvDate']);

        $Row['InvoiceType'] = $AryCustomInvDetails['IGST'];

        $Row['IGSTPerc'] = $AryCustomInvDetails['Discount'];

        $Row['DiscountPerc'] = $AryCustomInvDetails['Discount'];

        $Row['Description'] = $AryCustomInvDetails['Description'];

        $Row['CIDescription'] = $AryCustomInvDetails['CIDescription'];

        $Row['CIDescriptionspl'] = $AryCustomInvDetails['CIDescriptionspl'];

        $Row['Remarks'] = $AryCustomInvDetails['Remarks'];

        $Row['ProductTypes'] = $AryCustomInvDetails['ProductTypes'];

        $Row['ContainerNo'] = $AryCustomInvDetails['ContainerNo'];

        $Row['LinearSealNo'] = $AryCustomInvDetails['LinearSealNo'];

        $Row['ComDescriptionSpl'] = $AryCustomInvDetails['ComDescriptionSpl'];

        $Row['PackingListSpfDescription'] = $AryCustomInvDetails['PackingListSpfDescription'];

        $Row['ProductionDate'] = $AryCustomInvDetails['ProductionDate'];

        $Row['BestBeforeDate'] = $AryCustomInvDetails['BestBeforeDate'];



        // $Row['CustomSealNo'] = $AryCustomInvDetails['CustomSealNo'];



        $Row['ShippingBillNo'] = $AryCustomInvDetails['ShippingBillNo'];

        $Row['ShippingBillDate'] = $this->utilController->getFormatedDateEmpty($AryCustomInvDetails['ShippingBillDate']);

        $Row['BLNo'] = $AryCustomInvDetails['BLNo'];

        $Row['BLDate'] = $this->utilController->getFormatedDateEmpty($AryCustomInvDetails['BLDate']);

        $Row['LcNo'] = $AryCustomInvDetails['LcNo'];

        $Row['LcDate'] = $this->utilController->getFormatedDateEmpty($AryCustomInvDetails['LcDate']);

        $Row['ChargeName'] = $ChargeName . ':';

        $Row['ChargeAmount'] = number_format($AryDutyDetails['DiscountAmount'], 2);











        return $Row;

    }



    public function getProformaBreakUp($proformaID, $QtyUnit)
    {



        $SqlSpecies = "SELECT GROUP_CONCAT(DISTINCT(SpeciesName)) as SpeciesName FROM `ScrmCIGradeData` as SPDG 

        LEFT JOIN ScrmPIVarietyList as SPVL ON SPDG.VarietyID=SPVL.VarietyID 

        left join ScrmSpeciesMaster as SSM on SSM.ShortCode=SPVL.Species WHERE InvId =:proformaID";



        $stmtSpecies = $this->conn->prepare($SqlSpecies);

        $stmtSpecies->bindParam(':proformaID', $proformaID);

        $stmtSpecies->execute();

        $dataRowSpecies = $stmtSpecies->fetch(PDO::FETCH_ASSOC);





        $Species = $dataRowSpecies['SpeciesName'];



        $sqlFields = "PGD.ID,sum(McS) As VarietyMcs, BuyerItemCode, CIDescription as Description,Variety,PrintStatus as BrandPrintStatus,

                        PGD.Treatment,PGD.Grade,Aps.PackingStyle,Brand,PGD.PackingStyleID,BAP";



        $sqlQuery = " SELECT  $sqlFields FROM `ScrmCIGradeData` as PGD

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

            $ItemGroup['ItemsList'] = $this->getItemBreakUp($proformaID, $dataRow['Brand'], $dataRow['Variety'], $dataRow['PackingStyleID'], $QtyUnit);

            //print_r($ItemGroup['ItemsList']);

            $ItemData[] = $ItemGroup;



        }



        // $ItemGroup['ItemsList'] = $this->getItemBreakUp($proformaID, $dataRow['Brand'], $dataRow['Variety'], $dataRow['PackingStyleID']);



        // $ItemData[] = $ItemGroup;

        return $ItemData;

    }





    function getItemBreakUp($proformaID, $Brand, $Variety, $PackingStyleID, $QtyUnit)
    {



        $utilController = new UtilController($this->conn);



        // $sqlFields = "PGD.ID as ItemID,PGD.Grade,PGD.MCS,PGD.Brand,PGD.GlazePerc,PGD.FreezingType,PGD.Treatment,

        // PGD.Price as UnitPrice,PGD.CodeType,PGD.BuyerItemCode";



        $sqlFields = "PGD.PoGradeID as ItemID,PGD.Grade,PGD.MCS,PGD.Brand,PGD.GlazePerc,PGD.FreezingType,PGD.Treatment,

        PGD.Price as UnitPrice,PGD.Codes as BatchCode";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmCIGradeData` as PGD

        left join AdminPackingStyles  as PS on PGD.PackingStyleID=PS.PCKID

        

        WHERE InvId= :proformaID and Variety = :Variety and Brand=:Brand 

        and	PGD.PackingStyleID=:PackingStyleID order by abs(PGD.Grade)";

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->bindParam(':proformaID', $proformaID);

        $stmt->bindParam(':Variety', $Variety);

        $stmt->bindParam(':Brand', $Brand);

        $stmt->bindParam(':PackingStyleID', $PackingStyleID);

        $stmt->execute();

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArySubBreakUp[] = $dataRow['ItemID'];

            $ItemTotals = $utilController->getItemWeightandPrice($dataRow['ItemID'], $QtyUnit);

            // $dataRow['ItemTotals']=$ItemTotals;

            // $dataRow['Weight'] = round($ItemTotals['Weight'],2);

            $dataRow['Weight'] = number_format($ItemTotals['Weight'], 3);

            $dataRow['WeightKG'] = number_format($ItemTotals['NWeightKG'], 3);

            // $dataRow['BatchCode'] = '';

            // $dataRow['GrossWeight'] = number_format($ItemTotals['GWeightKG'], 3);

            $dataRow['GrossWeight'] = number_format($ItemTotals['GrossWeight'], 3);

            $dataRow['ItemPrice'] = number_format($ItemTotals['ItemPrice'], 2);

            $dataRow['NWeightKG'] = number_format($ItemTotals['NWeightKG'], 2);

            $dataRow['NWeightLB'] = number_format($ItemTotals['NWeightLB'], 2);

            $dataRow['GWeightKG'] = number_format($ItemTotals['GWeightKG'], 2);

            $dataRow['GWeightLB'] = number_format($ItemTotals['GWeightLB'], 2);

            $dataRow['FWeightKG'] = number_format($ItemTotals['FWeightKG'], 2);

            $dataRow['FWeightLB'] = number_format($ItemTotals['FWeightLB'], 2);

            $ItemData[] = $dataRow;

        }

        $SubVarityBrkUp = implode(',', $ArySubBreakUp);

        $ItemTotals = $utilController->getItemWeightandPrice($SubVarityBrkUp, $QtyUnit);





        $dataRow['Lable'] = 'Sub Total';



        $dataRow['ItemDescription'] = '';

        $dataRow['ItemID'] = 'Sub Total';

        $dataRow['Grade'] = '';

        $dataRow['MCS'] = $ItemTotals['MCS'];

        $dataRow['Weight'] = number_format($ItemTotals['Weight'], 3);

        $dataRow['WeightKG'] = number_format($ItemTotals['NWeightKG'], 3);

        //$dataRow['GrossWeight'] = number_format($ItemTotals['GWeightKG'], 3);

        $dataRow['GrossWeight'] = number_format($ItemTotals['GrossWeight'], 3);

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





        $AryPriceDetails = $this->utilController->getPriceDetails($proformaID, $QtyUnit);



        // $sqlFields = "PGD.ID as ItemID,PGD.Grade,PGD.MCS,PGD.Brand,PGD.GlazePerc,PGD.FreezingType,PGD.Treatment,

        // PGD.Price as UnitPrice,PGD.CodeType,PGD.BuyerItemCode"; 



        $sqlFields = "PGD.PoGradeID as ItemID,PGD.Grade,PGD.MCS,PGD.Brand,PGD.GlazePerc,PGD.FreezingType,PGD.Treatment,

        PGD.Price as UnitPrice,PGD.BuyerItemCode";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmCIGradeData` as PGD

        left join AdminPackingStyles  as PS on PGD.PackingStyleID=PS.PCKID

        WHERE InvId= :proformaID  order by abs(PGD.Grade)";

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->bindParam(':proformaID', $proformaID);

        $stmt->execute();

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArySubBreakUp[] = $dataRow['ItemID'];

        }



        $SubVarityBrkUp = implode(',', $ArySubBreakUp);

        $ItemTotals = $utilController->getItemWeightandPrice($SubVarityBrkUp, $QtyUnit);



        $dataRow['Lable'] = 'Grand Total';



        $dataRow['Grade'] = '';

        $dataRow['MCS'] = $ItemTotals['MCS'];

        $dataRow['Weight'] = number_format($ItemTotals['Weight'], 3);

        $dataRow['WeightKG'] = number_format($ItemTotals['NWeightKG'], 3);

        // $dataRow['GrossWeight'] = number_format($ItemTotals['GWeightKG'], 3);

        $dataRow['GrossWeight'] = number_format($ItemTotals['GrossWeight'], 3);

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





    function getCustomInvoiceDetails($proformaID)
    {

        $AryIsfData = $this->getISFDetails($proformaID);

        $AryShippingBillData = $this->getShippingBillDetails($proformaID);

        $AryCommercialData = $this->getCommercialData($proformaID);

        $AryTranshipmentData = $this->getTranshipmentData($proformaID);

        $AryProductionDates = $this->getProductionDates($proformaID);

        // $sqlFields = "PGD.ID as ItemID,PGD.Grade,PGD.MCS,PGD.Brand,PGD.GlazePerc,PGD.FreezingType,PGD.Treatment,

        // PGD.Price as UnitPrice,PGD.CodeType,PGD.BuyerItemCode";





        $sqlQuery = "SELECT * FROM `ScrmCustomInvoices` WHERE PiID=  :proformaID";

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->bindParam(':proformaID', $proformaID);

        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);



        $ShowBLDate = $AryCommercialData['ShowBLDate'];

        $ShowBLNo = $AryCommercialData['ShowBLNo'];

        if ($ShowBLDate == 1) {

            $BLDate = $AryIsfData['BLDate'];



        }

        if ($ShowBLNo == 1) {

            $BLNo = $AryIsfData['BLNo'];



        }





        // $dataRow['LinearSealNo'] = $AryIsfData['SealNo'];

        $dataRow['LinearSealNo'] = $dataRow['LinerSealNo'];

        // $dataRow['CustomSealNo']=$AryIsfData['SealNo'];

        $dataRow['ShippingBillNo'] = $AryShippingBillData['ShippingBillNo'];

        $dataRow['ShippingBillDate'] = $AryShippingBillData['ShippingBillDate'];

        $dataRow['LcDate'] = $AryCommercialData['LcDate'];

        $dataRow['LcNo'] = $AryCommercialData['LcNo'];

        $dataRow['ShowBank'] = $AryCommercialData['ShowBank'];

        $dataRow['ComShippingTerms'] = $AryCommercialData['ComShippingTerms'];

        $dataRow['ComPaymentTerms'] = $AryCommercialData['ComPaymentTerms'];

        $dataRow['BankID'] = $AryCommercialData['BankID'];

        $dataRow['ComDescriptionSpl'] = $AryCommercialData['ComDescriptionSpl'];

        $dataRow['PackingListSpfDescription'] = $AryCommercialData['PackingListSpfDescription'];

        $dataRow['BLDate'] = $BLDate;

        $dataRow['BLNo'] = $BLNo;

        $dataRow['VesselNumber'] = $AryTranshipmentData['VesselName'] . ' / ' . $AryTranshipmentData['VOYNO'];

        $dataRow['ProductionDate'] = $AryProductionDates['ProductionDate'];

        $dataRow['BestBeforeDate'] = $AryProductionDates['BestBeforeDate'];

        return $dataRow;

    }



    function getISFDetails($proformaID)
    {



        $sqlQuery = "SELECT * FROM `ScrmISFDetails` WHERE PIID=  :proformaID";

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->bindParam(':proformaID', $proformaID);

        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dataRow;

    }



    function getShippingBillDetails($proformaID)
    {

        $sqlFields = "ShippingBillNo,ShippingBillDate";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmShippingBillDetails` WHERE InvID=  :proformaID";

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->bindParam(':proformaID', $proformaID);

        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dataRow;

    }



    function getCommercialData($proformaID)
    {

        $sqlFields = "ShowBank,ShowBLNo,ShowBLDate,LcNo,LcDate,ComDescriptionSpl,ComRemarks as PackingListSpfDescription,ComShippingTerms,

        ComPaymentTerms,BankID";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmCommercialData` WHERE PoID=  :proformaID";

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->bindParam(':proformaID', $proformaID);

        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dataRow;

    }



    function getTranshipmentData($proformaID)
    {

        $sqlFields = "VesselName,VOYNO";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmTranshipmentPortsList` WHERE PIID=:proformaID and Status=1 order by TspID asc LIMIT 1";

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->bindParam(':proformaID', $proformaID);

        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dataRow;

    }



    function getProductionDates($proformaID)
    {



        $sqlFields = "DISTINCT ProductionDate, BestBeforeDate";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmCIGradeData` WHERE InvId=:proformaID and (ProductionDate!='' or BestBeforeDate!='')";

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->bindParam(':proformaID', $proformaID);

        $stmt->execute();

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArrayProductionDate[] = $this->utilController->getFormatedDateEmpty($dataRow['ProductionDate']);

            $ArrayBestBeforeDate[] = $this->utilController->getFormatedDateEmpty($dataRow['BestBeforeDate']);



            $ProductionDate = implode(",", array_unique($ArrayProductionDate));

            $BestBeforeDate = implode(",", array_unique($ArrayBestBeforeDate));



            // $ProductionDate=implode(",",$ArrayProductionDate);

            // $BestBeforeDate=implode(",",$ArrayBestBeforeDate);

        }

        $ArrayDates['ProductionDate'] = $ProductionDate;

        $ArrayDates['BestBeforeDate'] = $BestBeforeDate;

        return $ArrayDates;

    }

}



?>