<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include_once '../Global/Util.php';
class UtilController
{

    private $conn;
    private $ScrmPIData = "ScrmPIData";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getItemWeight($ItemID,$QtyUnit)
    {
        
        $Qry = "WHERE ID in ($ItemID)";
        $PriceQry = 'Price';
        $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";

                  $sqlQuery = "SELECT sum(Mcs) as MCS,TotalSlabs,InvId,  $sqlExt  ,
			$PriceQry ,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
			sum(Mcs*TotalSlabs*Price ) as PriceBags
			FROM 
			`ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry";

//


        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $ItemData['Weight'] = $dataRow['NetWgtLbs'];
        $ItemData['WeightKgs'] = $dataRow['NetWgtKgs'];
        $ItemData['GrossWeight'] = $dataRow['GrossWeightKgs'];//uma
        $ItemData['ItemPrice'] = $dataRow['PriceKgs'];
        $ItemData['MCS'] = $dataRow['MCS'];
        $ItemData['Price'] = $dataRow['Price'];

        if ($QtyUnit == 'KGS') {
            $ItemData['Weight'] = $dataRow['NetWgtKgs'];
        } else {
            $ItemData['Weight'] = $dataRow['NetWgtLbs'];
        }
        $ItemData['QtyKgs'] = $dataRow['NetWgtLbs']/2.2046;

        return $ItemData;
    }  
    
    public function getItemWeightandPrice($ItemID,$QtyUnit)//item weight calculation quantity wise in invoices (usage in invoices prints)
    {
        $Qry = "WHERE ID in ($ItemID)";
        $PriceQry = 'Price';
        $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,
                sum(Mcs*TotalSlabs*SlabWeight*MFactor*(GlazePerc/100)) as FrozenWeightKgs,
				 sum(Mcs*TotalSlabs*SlabWeight*LbsFactor*(GlazePerc/100)) as FrozenWeightLbs";

        $sqlQuery = "SELECT sum(Mcs) as MCS,TotalSlabs,InvId,  $sqlExt  ,
			$PriceQry ,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
			sum(Mcs*TotalSlabs*Price ) as PriceBags
			FROM 
			`ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry";



        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
       
        $ItemData['WeightKgs'] = $dataRow['NetWgtKgs'];
        $ItemData['GrossWeight'] = $dataRow['GrossWeightKgs'];//uma
        $ItemData['Price'] = $dataRow['Price'];//uma
       // $ItemData['ItemPrice'] = $dataRow['PriceKgs'];
        $ItemData['MCS'] = $dataRow['MCS'];

        if ($QtyUnit == 'KGS') {
            $ItemData['ItemPrice'] =$dataRow['PriceKgs'];
            $ItemData['Weight'] = $dataRow['NetWgtKgs'];
            $ItemData['GrossWeight'] = $dataRow['GrossWeightKgs'];
            $ItemData['FrozenWeight'] = $dataRow['NetWgtKgs']-$dataRow['FrozenWeightKgs'];
        } else {
            $ItemData['ItemPrice'] = $dataRow['PriceLbs'];
            $ItemData['Weight'] = $dataRow['NetWgtLbs'];
            $ItemData['GrossWeight'] = $dataRow['GrossWeightLbs'];
            $ItemData['FrozenWeight'] = $dataRow['NetWgtLbs']-$dataRow['FrozenWeightLbs'];
        }



       
        $ItemData['NWeightKG'] = $dataRow['NetWgtKgs'];
        $ItemData['NWeightLB'] = $dataRow['NetWgtLbs'];
        $ItemData['GWeightKG'] = $dataRow['GrossWeightKgs'];
        $ItemData['GWeightLB'] = $dataRow['GrossWeightLbs'];
        $ItemData['FWeightKG'] = $dataRow['NetWgtKgs']-$dataRow['FrozenWeightKgs'];
        $ItemData['FWeightLB'] = $dataRow['NetWgtLbs']-$dataRow['FrozenWeightLbs'];

        return $ItemData;
    }



    public function getPriceDetails($PoID, $QtyUnit)
    {
        $Qry = "WHERE InvId in ($PoID)";
        $PriceQry = 'Price';
        $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,
                sum(Mcs*TotalSlabs*SlabWeight*MFactor*(GlazePerc/100)) as FrozenWeightKgs,
                sum(Mcs*TotalSlabs*SlabWeight*LbsFactor*(GlazePerc/100)) as FrozenWeightLbs";

        $sqlQuery = "SELECT sum(Mcs) as MCS,TotalSlabs,InvId,  $sqlExt  ,
			$PriceQry ,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
			sum(Mcs*TotalSlabs*Price ) as PriceBags
			FROM 
			`ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry";



        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $ItemData['NetWeightKGS'] = $dataRow['NetWgtKgs'];
        $ItemData['NetWeightLBS'] = $dataRow['NetWgtLbs'];
        $ItemData['GrossWeightKGS'] = $dataRow['GrossWeightKgs'];
        $ItemData['GrossWeightLBS'] = $dataRow['GrossWeightLbs'];
        $ItemData['FrozenWeightKgs'] = $dataRow['NetWgtKgs']-$dataRow['FrozenWeightKgs']; 
        $ItemData['FrozenWeightLbs'] = $dataRow['NetWgtLbs']-$dataRow['FrozenWeightLbs'];
        if ($QtyUnit == 'KGS') {
            $ItemData['InvoiceValue'] = round($dataRow['PriceKgs'], 2);
        } else {
            $ItemData['InvoiceValue'] = round($dataRow['PriceLbs'], 2);
        }

        return $ItemData;
    }

    public function getProductWisePriceDetails($ItemID, $QtyUnit)
    {
        $Qry = "WHERE ID in ($ItemID)";
        $PriceQry = 'Price';
        $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";

        $sqlQuery = "SELECT sum(Mcs) as MCS,TotalSlabs,InvId,  $sqlExt  ,
			$PriceQry ,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
			sum(Mcs*TotalSlabs*Price ) as PriceBags
			FROM 
			`ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry";



        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $ItemData['NetWeightKGS'] = $dataRow['NetWgtKgs'];
        $ItemData['NetWeightLBS'] = $dataRow['NetWgtLbs'];
        $ItemData['GrossWeightKGS'] = $dataRow['GrossWeightKgs'];
        $ItemData['GrossWeightLBS'] = $dataRow['GrossWeightLbs'];
        if ($QtyUnit == 'KGS') {
            $ItemData['InvoiceValue'] = round($dataRow['PriceKgs'], 2);
        } else {
            $ItemData['InvoiceValue'] = round($dataRow['PriceLbs'], 2);
        }

        return $ItemData;
    }

    public function getConsigneeDetails($ConsigneeID)
    {

        $sqlFields = "`ConsigneeName`, `ConsigneeAddress`,
                     `ConsigneeCountry`, `ConsigneeSplIns`, `ConsigneeFishingNo`, 
                     `ConsigneeEmailAddress`, `ContactPerson`, `ContactNumber`, 
                     `PersonDesignation`, `ConsigneeCode`, `ConsigneeFaxNo`, `ConsigneeIRSNumber`,
                      `ConsigneeUSCICODE`, `ConsigneeIFTPCODE`, `ConsigneeState`, `ConsigneeZipCode`,
                      `Status`";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmConsigneeDetails` where ConID=:QryID ";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->bindParam(':QryID', $ConsigneeID);

        $stmtExec->execute();
        $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);
        $ItemData['ConsigneeName'] = $dataRow['ConsigneeName'];
        $ItemData['ConsigneeAddress'] = $dataRow['ConsigneeAddress'];
        $ItemData['ConsigneeCode'] = $dataRow['ConsigneeCode'];
        $ItemData['ConsigneePhoneNo'] = $dataRow['ContactNumber'];
        $ItemData['ConsigneeZipCode'] = $dataRow['ConsigneeZipCode'];

        return $ItemData;

    }
    public function getBuyerDetails($BuyerID)
    {

        $sqlFields = "`BuyerName`, `BuyerCode`, `BuyerAddress`, `BuyerCountry`";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmBuyerDetails` where BuyerID=:QryID ";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->bindParam(':QryID', $BuyerID);

        $stmtExec->execute();
        $rowCount = $stmtExec->rowCount();

        if ($rowCount > 0) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);
            $ItemData['BuyerName'] = $dataRow['BuyerName'];
            $ItemData['BuyerAddress'] = $dataRow['BuyerAddress'];
            $ItemData['BuyerCode'] = $dataRow['BuyerCode'];
            //getpaymentdetails 
        } else {
            $ItemData = null;
        }

        return $ItemData;

    }
    public function getUltimateConsigneeDetails($UltimateConsigneeID)
    {

        $sqlFields = " `ConsigneeName`, `ConsigneeAddress`, `ConsigneeCode`";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmUltimateConsigneeDetails` where ConID=:QryID ";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->bindParam(':QryID', $UltimateConsigneeID);

        $stmtExec->execute();
        $rowCount = $stmtExec->rowCount();

        if ($rowCount > 0) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);
            $ItemData['UConsigneeName'] = $dataRow['ConsigneeName'];
            $ItemData['UConsigneeAddress'] = $dataRow['ConsigneeAddress'];
            $ItemData['UConsigneeCode'] = $dataRow['ConsigneeCode'];
        } else {
            $ItemData = null;
        }

        return $ItemData;

    }
    public function getNotifyPartyDetails($NotifyPartyID)
    {

        $sqlFields = " `NotifyPartyName`, `NPartyAddress`,NPartyCode";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmNotifyparty` where NotifyPartyID=:QryID ";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->bindParam(':QryID', $NotifyPartyID);

        $stmtExec->execute();
        $rowCount = $stmtExec->rowCount();

        if ($rowCount > 0) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);
            $ItemData['NotifyPartyName'] = $dataRow['NotifyPartyName'];
            $ItemData['NPartyAddress'] = $dataRow['NPartyAddress'];
            $ItemData['NPartyCode'] = $dataRow['NPartyCode'];
        } else {
            $ItemData = null;
        }

        return $ItemData;

    }


    public function getImporterRecordDetails($ImporterID)
    {

        $sqlFields = "`ImporterName`, `ImporterAddress`";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmImporterOfRecord` where ImporterID=:QryID ";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->bindParam(':QryID', $ImporterID);

        $stmtExec->execute();
        $rowCount = $stmtExec->rowCount();

        if ($rowCount > 0) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);
            $ItemData['ImporterName'] = $dataRow['ImporterName'];
            $ItemData['ImporterAddress'] = $dataRow['ImporterAddress'];
        } else {
            $ItemData = null;
        }
        return $ItemData;

    }


    public function getExporterDetails($ExporterID, $CountryID, $DocType)
    {
//echo $ExporterID; echo $CountryID;
        $queryParms = '';
        if (isset($DocType)) {
            //  O default  
            $queryParms .= " and DocType in (0,'$DocType')";
        }
        //if (!isset($CountryID)) {
        if (isset($CountryID) && $CountryID!='') {
            //  O default  
            $queryParms .= " and CountryID in (0,$CountryID)";
        }

        $sqlFields = "AED.ExpName,AED.ExpAddress,AED.ExpApprovalNo,  AED.ExpGstNo, AED.InvShortCode,
        AED.ExpMailID, AED.ExpPhoneNumber, AED.ExpUsfdaNo, AED.ExpFeiNo, AED.ExpIeCode, AED.ExpIeCustomsNo,
          AED.AuthSign, AED.LutNumber, AED.ReptStatus, AED.InvDateFormat";

 $sqlQuery = "SELECT $sqlFields  FROM `AdminExporterDetails`  as AED where ExpID ='$ExporterID' ";
$stmtExec = $this->conn->prepare($sqlQuery);
$stmtExec->execute();
        
        if ($stmtExec) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);


            $ItemData['ExporterName'] = $dataRow['ExpName'];
            $ItemData['ExporterAddress'] = $dataRow['ExpAddress'];
            $ItemData['ExpApprovalNo'] = $dataRow['ExpApprovalNo'];
            $ItemData['ExpGstNo'] = $dataRow['ExpGstNo'];
            $ItemData['ExpMailID'] = $dataRow['ExpMailID'];
            $ItemData['ExpUsfdaNo'] = $dataRow['ExpUsfdaNo'];
            $ItemData['ExpFeiNo'] = $dataRow['ExpFeiNo'];
            $ItemData['ExpIeCode'] = $dataRow['ExpIeCode'];
            $ItemData['ExpIeCustomsNo'] = $dataRow['ExpIeCustomsNo'];
            $ItemData['AuthSign'] = $dataRow['AuthSign'];
            $ItemData['LutNumber'] = $dataRow['LutNumber'];
            $ItemData['InvDateFormat'] = $dataRow['InvDateFormat'];
            $ItemData['InvShortCode'] = $dataRow['InvShortCode'];
            $sqlCQuery = "SELECT  ExporterName,ExporterAddress  FROM  ScrmCountrywiseAddresses  where  ExporterID ='$ExporterID' $queryParms";
        $stmtCExec = $this->conn->prepare($sqlCQuery);
        $stmtCExec->execute();
        if($stmtCExec->rowCount()>0){
            $dataRow = $stmtCExec->fetch(PDO::FETCH_ASSOC);
            $ItemData['ExporterName'] = $dataRow['ExporterName'];
            $ItemData['ExporterAddress'] = $dataRow['ExporterAddress'];
        }

            return $ItemData;
        }

    }



    public function getPortDetails($PortID, $CountryID, $DocType)
    {
        $sqlFields = " Name,PortCode";

        $sqlQuery = "SELECT $sqlFields  FROM `ScrmPortCities` where PortId ='$PortID'";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();
        if ($stmtExec) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

            $ItemData['PortName'] = $dataRow['Name'];
            $ItemData['PortCode'] = $dataRow['PortCode'];

            return $ItemData;
        }

    }


    public function getCountryDetails($CountryID)
    {
        $sqlFields = " name,GstCountryCode";

        $sqlQuery = "SELECT $sqlFields  FROM `ScrmCountriesList` where id ='$CountryID'";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();
        if ($stmtExec) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

            $ItemData['CountryName'] = $dataRow['name'];
            $ItemData['CountryGstCode'] = $dataRow['GstCountryCode'];

            return $ItemData;
        }

    }
    public function getOrderStatusList()
    {
        $AryData=null;
        $sqlFields = " id as StatusID,Name as StatusName";

        $sqlQuery = "SELECT $sqlFields  FROM `ScrmShipmentStatusCodes`";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();
        if ($stmtExec) {
           while( $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC)){

            $ItemData['StatusID'] = $dataRow['StatusID'];
            $ItemData['StatusName'] = $dataRow['StatusName'];
            $AryData[]= $ItemData;
           }
           
        }
        return $AryData;
    }


    public function getPaymentTermDetails($PaymentTermID)
    {
        $sqlFields = "`PaymentName`, `PaymentMode`, `LcStatus`, `NoOfDays`, `ConditionDate`, `ShortName`";

        $sqlQuery = "SELECT $sqlFields  FROM `ScrmPaymentTerms` where ID ='$PaymentTermID'";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();
        if ($stmtExec) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

            $ItemData['PaymentTerm'] = $dataRow['PaymentName'];
            $ItemData['PaymentMode'] = $dataRow['PaymentMode'];
            $ItemData['LcStatus'] = $dataRow['LcStatus'];
            $ItemData['NoOfDays'] = $dataRow['NoOfDays'];
            $ItemData['ConditionDate'] = $dataRow['ConditionDate'];
            $ItemData['ShortName'] = $dataRow['ShortName'];

            return $ItemData;
        }

    }
    public function getCurrencyDetails($CurrencyCode)
    {
        $sqlFields = "`CurrencyName`, `CurrencyCode`, `CurrenySymbol`, `CurrencyShortCode`, `CurrencyGstName`, `CurrencyGstCode`";

        $sqlQuery = "SELECT $sqlFields  FROM `ScrmCurrencyMaster` where CurrencyCode ='$CurrencyCode'";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();
        if ($stmtExec) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

            $ItemData['CurrencyName'] = $dataRow['CurrencyName'];
            $ItemData['CurrencyCode'] = $dataRow['CurrencyCode'];
            $ItemData['CurrenySymbol'] = $dataRow['CurrenySymbol'];
            $ItemData['CurrencyShortCode'] = $dataRow['CurrencyShortCode'];
            $ItemData['CurrencyGstName'] = $dataRow['CurrencyGstName'];
            $ItemData['CurrencyGstCode'] = $dataRow['CurrencyGstCode'];

            return $ItemData;
        }

    }

    function getFormatedDate($rawDate){

       
        $Date = date("d-m-Y", strtotime($rawDate));
        if($Date=='01-01-1970'){
            $Date='NA';
        }
        return  $Date;
    } 
    
    function getFormatedDateEmpty($rawDate){

       
        $Date = date("d-m-Y", strtotime($rawDate));
        if($Date=='01-01-1970'){
            $Date='';
        }
        return  $Date;
    }
    public function getBankDetails($BankID)
    {
        $sqlFields = "`AccntName`, `AccountNumber`, `BankName`, `BankAddress`, `SwiftCode`, 
                        `ABA_Numnber`, `BankDetails`, `RoutingThrough`, `NegotiatingBank`";

        $sqlQuery = "SELECT $sqlFields  FROM `ScrmBankDetailsMaster` where ID ='$BankID'";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();
        if ($stmtExec) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

            $ItemData['BankName'] = $dataRow['BankName'];
            $ItemData['BankAddress'] = $dataRow['BankAddress'];
            $ItemData['BankAccountName'] = $dataRow['AccntName'];
            $ItemData['BankAccountNumber'] = $dataRow['AccountNumber'];
            $ItemData['BankSwiftCode'] = $dataRow['SwiftCode'];
            $ItemData['BankAbaNumber'] = $dataRow['ABA_Numnber'];
            $ItemData['BankDetails'] = $dataRow['BankDetails'];
            $ItemData['BankRoutionDetails'] = $dataRow['RoutingThrough'];
            $ItemData['BankNegotiationDetails'] = $dataRow['NegotiatingBank'];

            return $ItemData;
        }

    }
    public function getLabels($CountryID, $DocType)
    {
        /*  if(isset($DocType)){
              $queryParms.="and Doctype='$DocType'";
          }*/

        if (isset($DocType)) {
            $queryParms = "and CountryID in (0,$CountryID)";
        }

        $sqlFields = "`Exporter`, `InvoiceNoDt`, `PurchseOrderNo`, `PurchaseOrderDate`, `ReferenceNo`, `Consignee`, `ImporterOfRcrd`, ShippingTerm,
`NotifyPrty`, `Manufacturer`, `Vessel`, `PortOfLoading`, `PortOfDischrg`, `FinlDestintn`, `CountryOfOrigin`,InvoiceDate, 
`FinalDestintnCountry`, `DeliveryTerms`, `PaymentTerms`, `GoodsDescription`, `Quantity`, `RatePerLB`, `AmountInUSD`, 
`ShipmetDt`, `AmountInWords`, `Declrtn`, `ContainerNo`, `SealNo`, `BLNo`, `BLDate`,`ShippingBillNo`, `BankDetails`, `ApprovalNo`,`UsfdaRegistrationNo`";
        $sqlQuery = "SELECT $sqlFields FROM `ScrmCountryWiseLabelConditions` WHERE   Doctype='$DocType'  $queryParms ORDER by CountryID DESC LIMIT 1";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();
        if ($stmtExec->rowCount() > 0) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);
            return $dataRow;
        } else {
            return null;
        }

    }

    // function getInvoiceShowConditions($DocType,$CountryID,$BuyerID){
    function getInvoiceShowConditions($DocType,$CountryID){
        if (isset($DocType)) {
            $queryParms = "and CountryID in (0,$CountryID)";
        }
        $sqlQuery="SELECT * FROM `ScrmCountryWiseShowConditions` WHERE   
        DocType='$DocType' $queryParms order by CountryID desc limit 1";
        
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();
        if ($stmtExec->rowCount() > 0) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);
            return $dataRow;
        } else {
            return null;
        }

       
        
    }
    public function getBuyerListDetails($BuyerID)
    {
        $sqlFields = "`BuyerName`,`BuyerAddress`,`BuyerCountry`,`BuyerEmailAddress`";

        $sqlQuery = "SELECT $sqlFields  FROM `ScrmBuyerDetails` where BuyerID='$BuyerID'";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();

        if ($stmtExec) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

            $ItemData['Buyer'] = $dataRow['BuyerName'];
            $ItemData['Address'] = $dataRow['BuyerAddress'];
            $ItemData['Country'] = $dataRow['BuyerCountry'];
            $ItemData['EmailAddress'] = $dataRow['BuyerEmailAddress'];

            return $ItemData;
        }

    }
   
    public function getBrandsListDetails($BrandID)
    {
        $sqlFields = "`BrandName`, `Remarks`";

        $sqlQuery = "SELECT $sqlFields  FROM `ScrmBrandsMaster` where BrandID ='$BrandID'";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();

        if ($stmtExec) {
            $no=$stmtExec->rowCount();
         if($no>0){

            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

            $ItemData['BrandName'] = $dataRow['BrandName'];
            $ItemData['Remarks'] = $dataRow['Remarks'];
            return $ItemData;
         }
         else{
            $ItemData['BrandName'] = '';
            $ItemData['Remarks'] = '';
            return $ItemData;

         }    

           
        }

    }

    public function getPackingListDetails($PackingID)
    {
        $sqlFields = "`TotalSlabs`,`SlabWeight`,`Uom`,`PackingStyle`,`GrossWeight`";

        $sqlQuery = "SELECT $sqlFields  FROM `AdminPackingStyles` where PCKID='$PackingID'";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute(); 

        if ($stmtExec) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

            $ItemData['PackingStyle'] = $dataRow['PackingStyle'];
            $ItemData['GrossWeight'] = $dataRow['GrossWeight'];
            $ItemData['TotalSlabs'] = $dataRow['TotalSlabs'];
            $ItemData['SlabWeight'] = $dataRow['SlabWeight'];
            $ItemData['Uom'] = $dataRow['Uom'];
         
            return $ItemData;
        }

    }

    public function getTreatmentDetails($ShortCode)
    {

        $sqlQuery = "SELECT TreatmentName  FROM `ScrmTreatmentsMaster` where ShortCode='$ShortCode'";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();

        if ($stmtExec) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

            $ItemData['Treatment'] = $dataRow['TreatmentName'];
          
         
            return $ItemData;
        }

    }

    public function getShipmentStatusCodes($status)
    {

        $sqlQuery = "SELECT Color  FROM `ScrmShipmentStatusCodes` where Name='$status'";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();

        if ($stmtExec) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

           $dataRow['Color'];
          
         
            return $dataRow['Color'];
        }

    }

    public function getFormatedDatelines($DATE){

        $TIME_STAMP = strtotime($DATE);
         $formated_date = date("d-m-Y", $TIME_STAMP);
         if($formated_date=='01-01-1970'){
            $formated_date='--'; 
         }
        return $formated_date;
        }

        public function getFinancialYear($FinClsYear)
        {
            $DateStart = $FinClsYear . '-04-01';
            $DateEnd = ($FinClsYear + 1) . '-03-31';
    
            return $DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
    
        }

   

    public function getPaymentValues($PoID, $DocType)
    {

        
           $LbsFactor=0.454; 			// To Convert Lbs into Kgs  multiply value
         /*  $Discount=0;					// Get from Custom Invoice
           $InsurancePerc=0.11;			// Varible
           $AntiDumpingDFactor=101.35	// Anti Dumping Duty Factor(Formula)
           $AntiDumpingMFactor=1.35		// Anti Dumping Duty Factor(Formula)
           $StandardDutyCharges=125;	// Standard Value
           $TermName=125;	// Standard Value
           $CenttoUsd=100;	// Standard Value 1 usd=100 cents
           
           */

        //  echo $DocType; exit;

        /* $RawIpData = file_get_contents('php://input');
        $IpData = json_decode($RawIpData,true);
        $PoID=$IpData['PoID'];*/


        $CenttoUsd = 100;
        if ($DocType == 'CMI') {
            $sqlCInvData = "SELECT *,ExchngRte as ExRate FROM ScrmPoData as SPD 
                LEFT JOIN ScrmCommercialData as SCD on 
                SPD.PoID=SCD.PoID WHERE SPD.PoID=$PoID";
        } else {
            $sqlCInvData = "SELECT * FROM ScrmPoData as SPD 
                LEFT JOIN ScrmCustomInvoices as SCI on 
                SPD.PoID=SCI.PiID WHERE SPD.PoID=$PoID";
        }
       // echo $sqlCInvData; 


        $stmtCInvData = $this->conn->prepare($sqlCInvData);
        $stmtCInvData->execute();
        $dataRowCInvData = $stmtCInvData->fetch(PDO::FETCH_ASSOC);
       // var_dump($dataRowCInvData);
        $FrieghtCharge=0;
        $ExchangeRate=0;
        $Discount=0;
       $DestinationCountryID=$dataRowCInvData['DestinationCountry'];
       if($DocType=='CMI'){
			$ArrayFreight=$this->checkFreight($PoID);
         //   var_dump($ArrayFreight);
			$FrieghtCharge=$ArrayFreight['FrieghtCharge'];
			$ExchangeRate=$ArrayFreight['ExchangeRate'];
			
		}
			else{
				$FrieghtCharge = $dataRowCInvData['Freight'];
				$ExchangeRate = $dataRowCInvData['ExRate'];
			}
          

        $TermName = $dataRowCInvData['ShippingTerms'];
        $Discount = $dataRowCInvData['Discount'];
       /* $FrieghtCharge = $dataRowCInvData['Freight'];
        $ExchangeRate = $dataRowCInvData['ExRate'];*/
        $QtyUnit = $dataRowCInvData['QtyUnit'];
        // $CInvCode = $dataRowCInvData['CInvCode'];

        // if ($CInvCode == '03061720') {
        //     $IgstPerc = 5;
        // } else {
        //     $IgstPerc = 12;
        // }


/**uma(14-10-2023) */
 $SqlGstPerc="SELECT DISTINCT GstPerc FROM `ScrmPoGradeData` as SPGD 
left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID 
left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";
$StmtGstPerc = $this->conn->prepare($SqlGstPerc);
$StmtGstPerc->execute();
$RowGstPerc = $StmtGstPerc->fetch(PDO::FETCH_ASSOC);


//while($RowHsCode=$StmtHsCode->fetch_assoc()){
 // $ArrayHsCode[]=$RowHsCode['HSCode'];
  // $CInvCode = implode(',', $ArrayHsCode);
 
//}

$IgstPerc = $RowGstPerc['GstPerc'];
// if($CInvCode=='03061720'){
// 	$IgstPerc=5;
// }else{
// 	$IgstPerc=12;	
// }

/**uma (14-10-2023)*/

        $QtyUnit = $dataRowCInvData['QtyUnit'];

        $sqlShipTermsMaster = "SELECT * FROM `ScrmShipmentTermsMaster` where TermName='$TermName'";
        $stmtShipTermsMaster = $this->conn->prepare($sqlShipTermsMaster);
        $stmtShipTermsMaster->execute();
        $dataRowShipTermsMaster = $stmtShipTermsMaster->fetch(PDO::FETCH_ASSOC);



        // Update With ExportId Condition
        $sqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates`";
        $stmtExporterTaxRates = $this->conn->prepare($sqlExporterTaxRates);        
        $stmtExporterTaxRates->execute();
        $dataRowExporterTaxRates = $stmtExporterTaxRates->fetch(PDO::FETCH_ASSOC);

        
        



        $AntiDumpingDutyPerc = $dataRowExporterTaxRates['AntiDumpingDuty'];
        $MerchandiseProcessFeePerc = $dataRowExporterTaxRates['MerchandiseProcessFee'];
        $HarbourMaintenanceFeePerc = $dataRowExporterTaxRates['HarbourMaintenanceFee'];
        $ISFStaticDuty = $dataRowExporterTaxRates['ISFStaticDuty'];
        $InsurancePerc = $dataRowExporterTaxRates['InsurancePremiumRate'];

         $TotalDutyPerc = 100 + $AntiDumpingDutyPerc +
            $MerchandiseProcessFeePerc +
            $HarbourMaintenanceFeePerc;



        $IsFreight = $dataRowShipTermsMaster['Freight'];
        $IsDuty = $dataRowShipTermsMaster['Duty'];
        $Insurance = $dataRowShipTermsMaster['Insurance'];
        $AntiDumpingDFactor = $dataRowShipTermsMaster['AntiDumpingDFactor'];
        $AntiDumpingMFactor = $dataRowShipTermsMaster['AntiDumpingMFactor'];
        $StandardDutyCharges = $dataRowShipTermsMaster['StandardDutyCharges'];



        $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
                    sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
                    sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
                    sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";


        $sqlTotal = "SELECT sum(Mcs) as MCS,  $sqlExt  ,
                sum(Price) as Price,			
                sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
                sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs 
                FROM `ScrmPoGradeData` 
                as PGD left join AdminPackingStyles as PS on 
                PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'";
$stmtTotal = $this->conn->prepare($sqlTotal);
$stmtTotal->execute();
        $dataRowTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
        
        $DiscountAmount=0;
        $NetWgtKgs = $dataRowTotal['NetWgtLbs'] * $LbsFactor;
        $NetWgtLbs = $dataRowTotal['NetWgtLbs'];

        $GrossWeightKgs = $dataRowTotal['GrossWeightKgs'];
        $GrossWeightLbs = $dataRowTotal['GrossWeightLbs'];
        
        

        $TotalMcs = $dataRowTotal['MCS'];

                
        

        if ($QtyUnit == 'KGS') {
            $Weight = $dataRowTotal['NetWgtKgs'];
            $InvoiceValue = $dataRowTotal['PriceKgs'];
        } else {
            $Weight = $dataRowTotal['NetWgtLbs'] * $LbsFactor;
            $InvoiceValue = $dataRowTotal['PriceLbs'];
        }

        if ($Discount > 0) {
            $DiscountAmount = ($InvoiceValue * $Discount) / 100;
            $InvoiceValueAfterDiscount = $InvoiceValue;
            $InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount; // By Rantagiri 04/17/2023 5.44			
        } else {
            $InvoiceValueAfterDiscount = $InvoiceValue;
        }

        if ($Insurance == 1) {
            $InsuranceValue = ($PriceAfterDiscount * $InsurancePerc) / 100;
        } else {
            $InsuranceValue = 0;
        }

       // if ($IsDuty == 1 ) { //uma
        if ($IsDuty == 1 && $DestinationCountryID==157) { //uma
            /*echo $MerchandiseProcessFeePerc=0; 
            echo $InvoiceValueAfterDiscount=0;
            echo $InsuranceValue=0;
            echo $FrieghtCharge=0;
           // echo $TotalDutyPerc=0;*/


            $MerchandiseProcessFee = (($InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge) / $TotalDutyPerc) * $MerchandiseProcessFeePerc;
            //$MerchandiseProcessFee=round((($EffInvValue*$MerchandiseProcessFeePerc)/$TotalDutyPerc),2);
            if ($MerchandiseProcessFee > $ISFStaticDuty) {
                $ISFStatic = true;
                $MerchandiseProcessFee = $ISFStaticDuty;
                $TotalDutyPerc = $TotalDutyPerc - $MerchandiseProcessFeePerc;
                $EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $MerchandiseProcessFee;
            } else {
                $ISFStatic = false;
                $EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge;
                $MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);

            }

            $AntiDumpingCharges = round((($EffInvValue * $AntiDumpingDutyPerc) / $TotalDutyPerc), 2);
            $HarbourMaintenanceFee = round((($EffInvValue * $HarbourMaintenanceFeePerc) / $TotalDutyPerc), 2);

            if ($ISFStatic) {
                $DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee;
            } else {
                $DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee;
            }

        } else {
            $DutiesPrice = $DutiesPrice + $StandardDutyCharges;
        }

        $InvValueUSD = $InvoiceValueAfterDiscount;
        if ($ISFStatic) {
            $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $ISFStaticDuty;
            $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $ISFStaticDuty;
        } else {
            $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge;
            $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice;
        }

       /* echo 'hi';
        echo $ExchangeRate?$ExchangeRate:0;
        echo $FOBValueUSD?$FOBValueUSD:0;
        echo $InvValueUSD?$InvValueUSD:0;
        */

        $InvValueINR = $InvValueUSD * $ExchangeRate;
        $FOBValueINR = $FOBValueUSD * $ExchangeRate;

        $IgstValue = ($InvValueINR * $IgstPerc) / 100;
        $TotalInvoiceValueINR=$IgstValue+$InvValueINR;



        $AryPrices['DiscountPerc'] = $Discount;
        $AryPrices['ExchangeRate'] = $ExchangeRate;
        $AryPrices['DiscountAmount'] = $DiscountAmount;
        $AryPrices['InsuranceValue'] = $InsuranceValue;
        $AryPrices['DutiesPrice'] = $DutiesPrice;
        $AryPrices['FrieghtCharge'] = $FrieghtCharge;

        $AryPrices['InvValueUSD'] = $InvValueUSD;
        $AryPrices['InvValueUSDWithOutDuties'] = $InvValueUSDWithOutDuties;
        $AryPrices['InvValueINR'] = $InvValueINR;
        $AryPrices['FOBValueUSD'] = $FOBValueUSD;
        $AryPrices['FOBValueINR'] = $FOBValueINR;
        $AryPrices['TotalInvoiceValueINR'] = $TotalInvoiceValueINR;
        $AryPrices['PaymentTerm'] = $TermName;
        $AryPrices['IgstPerc'] = $IgstPerc;
        $AryPrices['IgstValue'] = $IgstValue;
        $AryPrices['AntiDumpingDuty'] = $AntiDumpingCharges;
        $AryPrices['MerchantDuty'] = $MerchandiseProcessFee;
        $AryPrices['HarborDuty'] = $HarbourMaintenanceFee;
        $AryPrices['ISFStatic'] = $ISFStatic;
        $AryPrices['CenttoUsd'] = $CenttoUsd;
        $AryPrices['TotalMcs'] = $TotalMcs;
       // $AryPrices['ShowAdd'] = 1;//usa->show,others->hide

        return $AryPrices;

    }

   public function checkFreight($PoID){

        $sqlQuery="SELECT SCI.Freight as CustomFreight,SCI.ExRate as CustomExrate,SCD.Freight as ComFreight,
        SCD.ExchngRte as ComExrate FROM `ScrmCustomInvoices` as SCI left join ScrmCommercialData as SCD on SCD.PoID=SCI.PiID 
        WHERE PiID=$PoID;
        ";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();
        $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

       

        
    
        if($dataRow['ComFreight']!=0){
            $FrieghtCharge=$dataRow['ComFreight'];
             $ExchangeRate=$dataRow['ComExrate'];
        }
    
        else{
            $FrieghtCharge=$dataRow['CustomFreight'];
            $ExchangeRate=$dataRow['CustomExrate'];
        }
      
    $ArrayFreight['FrieghtCharge']=$FrieghtCharge ?$FrieghtCharge :0 ;
    $ArrayFreight['ExchangeRate']=$ExchangeRate ? $ExchangeRate :0;
        return $ArrayFreight;
    }
  

    public function getInsurancePaymentsDetails($InsurancePaymentID)
    {
$sqlQuery = "SELECT  OurReferenceNo,PaymentDate FROM `ScrmInsurancePayments` where InsurancePaymentID='2'";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();

        if ($stmtExec) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

            $ItemData['OurReferenceNo'] = $dataRow['OurReferenceNo'];
            $ItemData['PaymentDate'] = $dataRow['PaymentDate'];
          
         
            return $ItemData;
        }

    }

    function getFullPiInvoiceNumbertoSave($ExpID,$InvDate,$InvNo){
        $ExporterData=$this->getExporterDetails($ExpID,'','');
        //print_r($ExporterData);
        $InvShortCode=$ExporterData['InvShortCode'];
       $InvDateFormat=$ExporterData['InvDateFormat'];
      
      $CurrentMonth = date('n',strtotime($InvDate));   
      if($InvDateFormat=='Short'){		
      if($CurrentMonth<=3){
       $NxtYear = date("y",strtotime($InvDate));  	
       $CurrentYear = date("y",strtotime($InvDate))-1;  	
      } 
      else{ 
      $CurrentYear = date("y",strtotime($InvDate));  	 
      $NxtYear = date("y",strtotime($InvDate))+1;  	
       
      }
      }else{	
      
      if($CurrentMonth<=3){
       $NxtYear = date('y',strtotime($InvDate));  	
       $CurrentYear = date('y',strtotime($InvDate))-1;  		
      }else{ 
      $CurrentYear = date('y',strtotime($InvDate));  	 
      $NxtYear = date('y',strtotime($InvDate))+1;  	
        
      }
      }
        $InvNumFull= $InvShortCode.'PI/'.$InvNo.'/'.$CurrentYear.'-'.$NxtYear;
       return $InvNumFull; 
        
          
      }

}

?> 