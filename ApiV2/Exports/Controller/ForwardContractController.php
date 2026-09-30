<?php

include_once '../Global/Util.php';
class ForwardContractController
{

    private $conn;
    private $ScrmPIData = "ScrmPIData";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getForwardContractList($RequestBody)
    {

        $proformaID = $RequestBody['ProformaID'];

        $sqlFields = " *,SPD.Status as PoStatus,ALD.SignURL,
        SCD.ConsigneeName as ConsigneeName,SCD.ConsigneeAddress as ConsigneeAddress,
        ABD.AccntName as AccntName,
        ABD.AccountNumber as AccountNumber,
        ABD.BankName as BankName,
        ABD.BankAddress as BankAddress,SPCL.Name AS LoadingPortName,
        ABD.SwiftCode as SwiftCode,
        SCL.name as PIDestinationCountry ,SC.name as PortofDischargeCountry ,SPC.Name as PortOfDischarge ,
        SPC.CountryCode as PortOfDischargeCountryCode ,SCPL.name as PortofLoadingCountry ,
        SPCL.Name as PortofLoading ,SPCF.Name as FinalDestination ,SPC.CountryCode as CountryCode ";
        $sqlQuery = "SELECT  $sqlFields 
        FROM ScrmPIData as SPD 
        left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
        left JOIN ScrmBuyerDetails as SBD on SPD.BuyerID=SBD.BuyerID
        left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
        left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
        left JOIN ScrmPortCities as SPCL on SPD.PortofLoading=SPCL.PortId
        left JOIN ScrmPortCities as SPCF on SPD.FinalDestination=SPCF.PortId
        left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
        left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
        left JOIN ScrmCountriesList as SCPL on SCPL.id=SPCL.Country
        left JOIN AdminLoginDetails as ALD on SC.id=SPD.ApprovedBy
        left JOIN AdminBankDetails as ABD on ABD.ID=SPD.BankID
        left JOIN ScrmImporterOfRecord as SIR on SIR.ImporterID=SPD.ImporterID
        left JOIN ScrmNotifyparty as SNP on SNP.NotifyPartyID=SPD.NotifyPartyID
        left JOIN ScrmOrderCertification as SOC on SOC.CerificationID=SPD.Certification WHERE SPD.PoID= :proformaID";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Row['PrintHeader'] = $dataRow['ExporterName'];
            $Row['InvoiceNumber'] = $dataRow['ExporterName'];
            $Row['InvoiceDate'] = $dataRow['ExporterName'];
            $Row['ExporterName'] = $dataRow['ExporterName']; 
            $Row['ExporterAddress'] = $dataRow['ExporterName'];
            $Row['PoNumber'] = $dataRow['ExporterName'];
            $Row['PoDate'] = $dataRow['ExporterName'];
            $Row['ReferenceNumber'] = $dataRow['ExporterName'];
            $Row['ConsigneeName'] = $dataRow['ExporterName'];
            $Row['ConsigneeAddress'] = $dataRow['ExporterName'];
            $Row['BuyerName'] = $dataRow['ExporterName'];
            $Row['BuyerAddress'] = $dataRow['ExporterName'];
            $Row['UltimateConsigneeName'] = $dataRow['ExporterName'];
            $Row['UltimateConsigneeAddress'] = $dataRow['ExporterName'];
            $Row['ImporterName'] = $dataRow['ExporterName'];
            $Row['ImporterAddress'] = $dataRow['ExporterName'];
            $Row['NotifyPartyNameAddress'] = $dataRow['ExporterName'];
            $Row['ProcessorName'] = $dataRow['ExporterName'];
            $Row['ProcessorAddress'] = $dataRow['ExporterName'];
            $Row['USFDANumber'] = $dataRow['ExporterName'];
            $Row['Certification'] = $dataRow['ExporterName'];
            $Row['Vessel'] = $dataRow['ExporterName'];
            $Row['PortOfLoading'] = $dataRow['ExporterName'];
            $Row['PortOfDischarge'] = $dataRow['ExporterName'];
            $Row['FinalDestination'] = $dataRow['ExporterName'];
            $Row['CountryOfOrgin'] = $dataRow['ExporterName'];
            $Row['CountryOfDestination'] = $dataRow['ExporterName'];
            $Row['TermsOfDelivery'] = $dataRow['ExporterName'];
            $Row['TermsOfPayment'] = $dataRow['ExporterName'];
            $Row['ShipmentTerm'] = $dataRow['ExporterName'];
            $Row['QtyUnits'] = $dataRow['ExporterName'];
            $Row['CurrencyCode'] = $dataRow['ExporterName'];
            $Row['CurrencySymbol'] = $dataRow['ExporterName'];
            $Row['ShipByDate'] = $dataRow['ExporterName'];           
            $Row['BankNameDetails'] = $dataRow['ExporterName'];
            $Row['NegotiationBank'] = $dataRow['ExporterName'];
            $Row['RoutingBank'] = $dataRow['ExporterName'];
            $Row['AmountInWords'] = $dataRow['ExporterName'];
            $Row['NetWeightKgs'] = $dataRow['ExporterName'];
            $Row['NetWeightlbs'] = $dataRow['ExporterName'];
            $Row['GrossWeightKgs'] = $dataRow['ExporterName'];
            $Row['GrossWeightLbs'] = $dataRow['ExporterName'];
            $Row['Declearation'] = $dataRow['ExporterName'];
            $Row['SignName'] = $dataRow['ExporterName'];
            $Row['SignDescription'] = $dataRow['ExporterName'];
            $Row['SignUrl'] = $dataRow['ExporterName'];
            $Row['Remarks'] = $dataRow['ExporterName'];
            $Row['ItemsGroup'] =  $this->getProformaBreakUp($proformaID);

            $Data[] = $Row;

        }
        return $Data;
    }

    public function getProformaBreakUp($proformaID)
    {

        $sqlFields = "sum(McS) As VarietyMcs, BuyerItemCode, Description as Description,Variety,
                        PGD.Treatment,PGD.Grade,Aps.PackingStyle,Brand,PGD.PackingStyleID,BAP";
       
       $sqlQuery = " SELECT  $sqlFields FROM `ScrmPoGradeData` as PGD
       left join `AdminPackingStyles` as Aps on PGD.PackingStyleID=Aps.PCKID
        WHERE InvId= :proformaID  GROUP by Variety,PackingStyleID,Brand Order by PGD.ID asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if($dataRow['Description']!=''){
            $ItemGroup['ItemDescription']=$dataRow['Description'];
            }else{
            $ItemGroup['ItemDescription']=$dataRow['Variety'];
            }
            $ItemGroup['PackingStyle']=$dataRow['PackingStyle'];
            $ItemGroup['Brand']=$dataRow['Brand'];
            $ItemGroup['ItemsList']=$this->getItemBreakUp($proformaID,$dataRow['Brand'],$dataRow['Variety'],$dataRow['PackingStyleID']);

            $ItemData[]=$ItemGroup;

        }
        return  $ItemData;
    }


    function getItemBreakUp($proformaID,$Brand,$Variety,$PackingStyleID){

       $utilController = new UtilController($this->conn);

        $sqlFields="PGD.ID as ItemID,PGD.Grade,PGD.MCS,PGD.Brand,PGD.GlazePerc,PGD.FreezingType,PGD.Treatment,
        PGD.Price as UnitPrice,PGD.CodeType,PGD.BuyerItemCode";
        $sqlQuery="SELECT $sqlFields FROM `ScrmPoGradeData` as PGD
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
            $ItemTotals=$utilController->getItemWeight($dataRow['ItemID']);
           // $dataRow['ItemTotals']=$ItemTotals;
            $dataRow['Weight']=$ItemTotals['Weight'];
            $dataRow['ItemPrice']=$ItemTotals['ItemPrice'];
            $ItemData[]=$dataRow;
         }
         return  $ItemData;

}
}

?>