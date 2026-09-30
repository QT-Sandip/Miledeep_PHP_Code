<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


//include_once '../Global/Util.php';

include_once 'AppUtilController.php';
include_once 'UtilController.php';
include_once 'CustomPrintControllerDev.php';

class CustomPrintControllerDev
{

    private $conn;
    private $DocType = "CUI";
    private $utilController;
    private $appUtilController;
    private $ScrmPIData = "ScrmPoData";
    private $PrintHeader = "INVOICE / PACKING LIST";



    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->appUtilController = new AppUtilController($this->conn);
    }
    // GET ALL
    public function getCustomPrint($RequestBody)
    {


        $proformaID = $RequestBody['ProformaID'];
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
                              `UltimateConsigneeID`, `Freight`, `PostedDate`, `IsEdit` ";


        $sqlQuery = "SELECT  $sqlFields  FROM $ScrmPIData where PoID= :proformaID";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

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


        $ShipByDate = $this->utilController->getFormatedDate($dataRow['ShipByDate']);

        $AryConsigneeDetails = $this->utilController->getConsigneeDetails($ConsigneeID);
        $AryBuyerDetails = $this->utilController->getBuyerDetails($BuyerID);
        $AryUltimateConsigneeDetails = $this->utilController->getUltimateConsigneeDetails($UltimateConsigneeID);
        $AryNotifyDetails = $this->utilController->getNotifyPartyDetails($NotifyPartyID);
        $AryImporterOfRecordDetails = $this->utilController->getImporterRecordDetails($ImporterID);

        $AryExporterDetails = $this->utilController->getExporterDetails($ExporterID, $CountryID, $this->DocType);
        $AryProcessorDetails = $this->utilController->getExporterDetails($ProcessorID, $CountryID, $this->DocType);
        $AryLoadingPortDetails = $this->utilController->getPortDetails($PortofLoading, $CountryID, $this->DocType);
        $AryDischargePortDetails = $this->utilController->getPortDetails($PortOfDischarge, $CountryID, $this->DocType);
        $AryFinalDestination = $this->utilController->getPortDetails($FinalDestination, $CountryID, $this->DocType);
        $AryCountryDetails = $this->utilController->getCountryDetails($CountryID);
        $AryPaymentTermDetails = $this->utilController->getPaymentTermDetails($PaymentTermID);
        $AryCurrencyDetails = $this->utilController->getCurrencyDetails($UnitCurrency);
        $AryBankDetails = $this->utilController->getBankDetails($BankID);
        $AryPriceDetails = $this->utilController->getPriceDetails($PoID, $QtyUnit);
        $AryDutyDetails = $this->utilController->getPaymentValues($PoID, $this->DocType);
        $AryCustomInvDetails = $this->getCustomInvoiceDetails($PoID);
        $InvoiceValueWord = convert_number_to_words(number_format($AryPriceDetails['InvoiceValue'], 2), $AryCurrencyDetails['CurrencyName'], 'Cents');
        $ArrayShowLabels = $this->utilController->getInvoiceShowConditions($this->DocType, $CountryID);

        $Row['Labels'] = $this->utilController->getLabels($CountryID, $this->DocType);

        $aryLabelReq['DocType'] = $this->DocType;
        $aryLabelReq['Country'] = $CountryID;
        $aryLabelReq['UOM'] = $QtyUnit;
        //  $aryLabelReq['UnitPrice']=$QtyUnit.'/'.$dataRow['ShippingTerms']; 
        $aryLabelReq['UnitPrice'] = $QtyUnit;
        $aryLabelReq['Currency'] = $UnitCurrency;
        $aryLabelReq['Buyer'] = $ConsigneeID; // Edited by uma on 14-11-2023
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

       $AryDutyDetails['FrieghtCharge']= number_format($AryDutyDetails['FrieghtCharge'],2);
       $AryDutyDetails['InvValueUSD']= number_format($AryDutyDetails['InvValueUSD'],2);
       $AryDutyDetails['FOBValueUSD']= number_format($AryDutyDetails['FOBValueUSD'],2);
       $AryDutyDetails['FOBValueINR']= number_format($AryDutyDetails['FOBValueINR'],2);
       $AryDutyDetails['InvValueINR']= number_format($AryDutyDetails['InvValueINR'],2);
       $AryDutyDetails['DutiesPrice']= number_format($AryDutyDetails['DutiesPrice'],2);
       $AryDutyDetails['InvValueUSDWithOutDuties']= number_format($AryDutyDetails['InvValueUSDWithOutDuties'],2);
        $Row['PaymentDetails'] = $AryDutyDetails;

     $TotalGrossWtKgs =$AryCustomInvDetails['TotalGrossWt'];
/***uma (17-01-2023) */
        if($QtyUnit=='KGS'){
			$GrossWeightKgs=$TotalGrossWtKgs;
			$GrossWeightLbs=$TotalGrossWtKgs*2.204;
			}else{
			$GrossWeightKgs=$TotalGrossWtKgs*0.454;
			$GrossWeightLbs=$TotalGrossWtKgs;
			}
/***uma (17-01-2023) */
        //  var_dump($AryExporterDetails);

        /***showlabels***/
        if ($ArrayShowLabels['NetWeightLBS'] == 1) {
            $NetWeightLBS = number_format($AryPriceDetails['NetWeightLBS'], 3);
        }
        if ($ArrayShowLabels['GrossWeightLBS'] == 1) {
           // $GrossWeightLBS = number_format($AryPriceDetails['GrossWeightLBS'], 3);
            $GrossWeightLBS = number_format($GrossWeightLbs, 3);
        }

      //echo  $ArrayShowLabels['Usfda']; exit;
        if ($ArrayShowLabels['Usfda'] == 1) {
            $USFDANumber = $AryProcessorDetails['ExpUsfdaNo'];
        } 
        
        if ($ArrayShowLabels['RodTepSchemeDeclaration'] == 1) {
            $ShowRodTepSchemeDeclaration =1;
        }

        if($CountryID=='157'){ //antidumping charges display only for usa
            $ShowAntiDumping=1;
        }
        if ($ArrayShowLabels['FrozenWeightkgs'] == 1) {
            $NetWeightKGS = number_format($AryPriceDetails['FrozenWeightKgs'], 3);
            $FrozenWeightKGS = number_format($AryPriceDetails['NetWeightKGS'], 3);
        }
        else{
            $NetWeightKGS = number_format($AryPriceDetails['NetWeightKGS'], 3);
        }
        /***showlabels***/

        $Row['ShowAntiDumping'] = $ShowAntiDumping;
        $Row['Vessel'] = '';
        $Row['CountryOfOrgin'] = 'INDIA';
        $Row['CountryOfOrginCode'] = 'IN';
        $Row['QtyUnits'] = $QtyUnit;
        $Row['ShipByDate'] = $ShipByDate;
        $Row['AmountInWords'] = $InvoiceValueWord;
        $Row['PrintHeader'] = $this->PrintHeader;

        $Row['PoNumber'] = $dataRow['PoNo'];
        $Row['PoDate'] = $this->utilController->getFormatedDate($dataRow['PoDate']);
        $Row['ReferenceNumber'] = $dataRow['ReferenceNo'];
        $Row['Certification'] = $dataRow['Certification'];
        $Row['DeliveryTerms'] = $dataRow['DeliveryTerms'];
        $Row['PaymentTerms'] = $AryPaymentTermDetails['PaymentTerm'];
        $Row['ShippingTerms'] = $dataRow['ShippingTerms'];
        $Row['Declearation'] = 'We declare that this invoice shows the actual price of goods described and that all particulars are true and correct.'; // Doubt
        $Row['SignFor'] = 'For  ' . $AryExporterDetails['ExporterName']; // Doubt
        $Row['SignDescription'] = 'AUTHORISED SIGNATORY <br> Signature & Date'; // Doubt
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

        $Row['ShowRodTepSchemeDeclaration'] = $ShowRodTepSchemeDeclaration;
        $Row['CountryID'] = $CountryID;
        $Row['NetWeightKGS'] = $NetWeightKGS;
       // $Row['GrossWeightKGS'] = number_format($AryPriceDetails['GrossWeightKGS'], 3);
        $Row['GrossWeightKGS'] = number_format($GrossWeightKgs, 3);
        $Row['FrozenWeightKGS'] = $FrozenWeightKGS;

        $Row['NetWeightLBS'] = $NetWeightLBS;
        $Row['GrossWeightLBS'] = $GrossWeightLBS;

        $Row['InvoiceValue'] = $AryPriceDetails['InvoiceValue'];

        $Row['BankDetails'] = $AryBankDetails['BankDetails'];
        $Row['BankRoutionDetails'] = $AryBankDetails['BankRoutionDetails'];
        $Row['BankNegotiationDetails'] = $AryBankDetails['BankNegotiationDetails'];

        $Row['CurrencyCode'] = $AryCurrencyDetails['CurrencyCode']; // $AryCurrencyDetails['CurrencyName'];
        $Row['CurrencySymbol'] = $AryCurrencyDetails['CurrenySymbol'];

        $Row['CountryOfDestination'] = $AryCountryDetails['CountryName'];
        $Row['PortOfLoading'] = $AryLoadingPortDetails['PortName'] . ',INDIA '; //. $AryCountryDetails['CountryName'];
        $Row['PortOfDischarge'] = $AryDischargePortDetails['PortName'] . ' , ' . $AryCountryDetails['CountryName'];
        // $Row['PortOfDischarge'] = $AryDischargePortDetails['PortName'] ;exit;
        $Row['FinalDestination'] = $AryFinalDestination['PortName'] . ' , ' . $AryCountryDetails['CountryName'];

        $Row['ConsigneeName'] = $AryConsigneeDetails['ConsigneeName'];
        $Row['ConsigneeAddress'] = $AryConsigneeDetails['ConsigneeAddress'];

        $AryBuyerDetails = $this->utilController->getBuyerDetails($BuyerID);
        $AryUltimateConsigneeDetails = $this->utilController->getUltimateConsigneeDetails($UltimateConsigneeID);
        $AryNotifyDetails = $this->utilController->getNotifyPartyDetails($NotifyPartyID);
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
        $Row['LinearSealNo'] = $AryCustomInvDetails['LinerSealNo'];
        $Row['CustomSealNo'] = $AryCustomInvDetails['CustomSealNo'];
        $Row['VehicleNo'] = $AryCustomInvDetails['VehicelNumber'];
        $Row['DriverName'] = $AryCustomInvDetails['DriverName'];






        return $Row;
    }

    public function getProformaBreakUp($proformaID, $QtyUnit)
    {
        $SqlSpecies="SELECT GROUP_CONCAT(DISTINCT(SpeciesName)) as SpeciesName FROM `ScrmPoGradeData` as SPDG 
        LEFT JOIN ScrmPIVarietyList as SPVL ON SPDG.VarietyID=SPVL.VarietyID 
        left join ScrmSpeciesMaster as SSM on SSM.ShortCode=SPVL.Species WHERE InvId =:proformaID";

$stmtSpecies = $this->conn->prepare($SqlSpecies);
$stmtSpecies->bindParam(':proformaID', $proformaID);
$stmtSpecies->execute();
$dataRowSpecies = $stmtSpecies->fetch(PDO::FETCH_ASSOC);


        $Species=$dataRowSpecies['SpeciesName'];

        $sqlFields = "PGD.ID,sum(McS) As VarietyMcs, BuyerItemCode, Description as Description,Variety,PrintStatus as BrandPrintStatus,
                        PGD.Treatment,PGD.Grade,Aps.PackingStyle,Brand,PGD.PackingStyleID,BAP";

        $sqlQuery = " SELECT  $sqlFields FROM `ScrmPoGradeData` as PGD
       left join `AdminPackingStyles` as Aps on PGD.PackingStyleID=Aps.PCKID
       left join `ScrmBrandsMaster` SBM on  SBM.BrandName=PGD.Brand
        WHERE InvId= :proformaID  GROUP by Variety,PackingStyleID,Brand Order by PGD.ID asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            if ($dataRow['BrandPrintStatus'] ==1) {
                $ItemGroup['Brand'] = $dataRow['Brand'];
            }

            if ($dataRow['Description'] != '') {
                $ItemGroup['ItemDescription'] = $dataRow['Description'];
            } else {
                $ItemGroup['ItemDescription'] = $dataRow['Variety'];
            }
            $ItemGroup['ShowSubTotal'] = false;
            $ItemGroup['PackingStyle'] = $dataRow['PackingStyle'];
           // $ItemGroup['Brand'] = $dataRow['Brand'];
            $ItemGroup['Product'] = 'SHRIMPS';
            $ItemGroup['Species'] = $Species;
            $ItemGroup['ItemsList'] = $this->getItemBreakUp($proformaID, $dataRow['Brand'], $dataRow['Variety'], $dataRow['PackingStyleID'], $QtyUnit,$dataRow['PackingStyle']);
            //print_r($ItemGroup['ItemsList']); exit;
            $ItemData[] = $ItemGroup;

        }

        // $ItemGroup['ItemsList'] = $this->getItemBreakUp($proformaID, $dataRow['Brand'], $dataRow['Variety'], $dataRow['PackingStyleID']);

        // $ItemData[] = $ItemGroup;
        return $ItemData;
    }


    function getItemBreakUp($proformaID, $Brand, $Variety, $PackingStyleID, $QtyUnit,$PackingStyle)
    {

        $utilController = new UtilController($this->conn);

        $sqlFields = "PGD.ID as ItemID,PGD.Grade,PGD.MCS,PGD.Brand,PGD.GlazePerc,PGD.FreezingType,PGD.Treatment,
        PGD.Price as UnitPrice,PGD.CodeType,PGD.BuyerItemCode";
        $sqlQuery = "SELECT $sqlFields FROM `ScrmPoGradeData` as PGD
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
          //  print_r($ItemTotals); exit;
if($QtyUnit=='KGS'){
   $UnitPrice= $dataRow['UnitPrice'] ; 
}
else{
      $UsdValue= $ItemTotals['ItemPrice'];
    $Qty= $ItemTotals['NWeightLB']*0.454;
     $UnitPrice=round(($UsdValue/$Qty),6);
    
}



            // $dataRow['ItemTotals']=$ItemTotals;
            // $dataRow['Weight'] =  round($ItemTotals['Weight'],2);
            $dataRow['Weight'] = number_format($ItemTotals['NWeightKG'], 3);
            $dataRow['WeightKG'] = number_format($ItemTotals['NWeightKG'], 3);
             $dataRow['ItemPrice'] = number_format($ItemTotals['ItemPrice'], 2);
             $dataRow['UnitPrice'] = $UnitPrice;
             $dataRow['Packing'] = $PackingStyle;//Added by uma

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
        $dataRow['Weight'] = number_format($ItemTotals['NWeightKG'], 3);
        $dataRow['WeightKG'] = number_format($ItemTotals['NWeightKG'], 3);
        $dataRow['UnitPrice'] = '';
        //$dataRow['ItemPrice'] = number_format($ItemTotals['ItemPrice'], 2);
        $dataRow['ItemPrice'] = number_format($ItemPrice, 2);

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

        $sqlFields = "PGD.ID as ItemID,PGD.Grade,PGD.MCS,PGD.Brand,PGD.GlazePerc,PGD.FreezingType,PGD.Treatment,
        PGD.Price as UnitPrice,PGD.CodeType,PGD.BuyerItemCode";
        $sqlQuery = "SELECT $sqlFields FROM `ScrmPoGradeData` as PGD
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
        $dataRow['Weight'] = number_format($ItemTotals['NWeightKG'], 3);
        $dataRow['WeightKG'] = number_format($ItemTotals['NWeightKG'], 3);
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
        $sqlFields = "PGD.ID as ItemID,PGD.Grade,PGD.MCS,PGD.Brand,PGD.GlazePerc,PGD.FreezingType,PGD.Treatment,
        PGD.Price as UnitPrice,PGD.CodeType,PGD.BuyerItemCode";
        $sqlQuery = "SELECT * FROM `ScrmCustomInvoices` WHERE PiID=  :proformaID";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        //$dataRow['LinearSealNo'] = $AryIsfData['SealNo'];
        //$dataRow['CustomSealNo'] = $AryIsfData['SealNo'];
        return $dataRow;
    }

    function getISFDetails($proformaID)
    {
        $sqlFields = "SealNo";
        $sqlQuery = "SELECT $sqlFields FROM `ScrmISFDetails` WHERE PIID=  :proformaID";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dataRow;
    }
}

?>