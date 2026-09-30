<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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
    public function getItemWeight($ItemID, $QtyUnit)
    {

        $Qry = "WHERE ID in ($ItemID)";
        $PriceQry = 'Price';
        $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
        sum(Mcs*TotalSlabs*SlabWeight*LbsFactor*0.454) as NetWgtOzKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";

        $sqlQuery = "SELECT sum(Mcs) as MCS,TotalSlabs,InvId,  $sqlExt  ,
			$PriceQry ,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
			sum(Mcs*TotalSlabs*Price ) as PriceBags,
             PS.Uom AS Uom
			FROM 
			`ScrmPoGradeData` 
			as PGD 
            LEFT JOIN (
                SELECT 
                    *, 
                    CASE 
                        WHEN HasSauce = 'Yes' THEN ShrimpPackingStyle 
                        ELSE PackingStyle 
                    END AS EffectivePackingStyle 
                FROM ScrmPIVarietyList
            ) AS VL ON VL.VarietyID = PGD.VarietyID
			left join AdminPackingStyles as PS on VL.EffectivePackingStyle=PS.PCKID $Qry";

        //


        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $ItemData['Weight'] = $dataRow['NetWgtLbs'];
        $ItemData['WeightKgs'] = $dataRow['NetWgtKgs'];
        $ItemData['GrossWeight'] = $dataRow['GrossWeightKgs']; //uma
        $ItemData['ItemPrice'] = $dataRow['PriceKgs'];
        $ItemData['MCS'] = $dataRow['MCS'];
        $ItemData['Price'] = $dataRow['Price'];

        $ItemData['Uom'] = $dataRow['Uom'];

        if ($QtyUnit == 'KGS' && !$ItemData['Uom'] == 'OZ') {
            $ItemData['Weight'] = $dataRow['NetWgtKgs'];
            $ItemData['QtyKgs'] = $dataRow['NetWgtKgs']; //uma
        } else if ($ItemData['Uom'] == 'OZ') {
            $ItemData['Weight'] = $dataRow['NetWgtLbs'];
            $ItemData['QtyKgs'] = $dataRow['NetWgtOzKgs']; //uma
        } else {
            $ItemData['Weight'] = $dataRow['NetWgtLbs'];
            $ItemData['QtyKgs'] = $dataRow['NetWgtLbs'] / 2.2046; //uma
            $ItemData['QtyKgs'] = $dataRow['NetWgtKgs']; //uma
        }
        // $ItemData['QtyKgs'] = $dataRow['NetWgtLbs'] / 2.2046;


        return $ItemData;
    }

    public function getItemWeightandPrice($ItemID, $QtyUnit) //item weight calculation quantity wise in invoices (usage in invoices prints)
    {
        $Qry = "WHERE ID in ($ItemID)";
        $PriceQry = 'Price';
        $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,
                sum(Mcs*TotalSlabs*SlabWeight*MFactor*(GlazePerc/100)) as FrozenWeightKgs,
				 sum(Mcs*TotalSlabs*SlabWeight*LbsFactor*(GlazePerc/100)) as FrozenWeightLbs,sum(GrossWt) as EditedGrossWeight
                 ";

        $sqlQuery = "SELECT sum(Mcs) as MCS,sum(PIMcs) as PIMcs,TotalSlabs,InvId,  $sqlExt  ,
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
        $EditedGrossWeight = $dataRow['EditedGrossWeight'];

        $ItemData['WeightKgs'] = $dataRow['NetWgtKgs'];
        $ItemData['GrossWeight'] = $dataRow['GrossWeightKgs']; //uma
        $ItemData['Price'] = $dataRow['Price']; //uma
        // $ItemData['ItemPrice'] = $dataRow['PriceKgs'];
        $ItemData['MCS'] = $dataRow['MCS'];
        $ItemData['PIMcs'] = $dataRow['PIMcs'];





        if ($QtyUnit == 'KGS') {


            $ItemData['ItemPrice'] = $dataRow['PriceKgs'];
            $ItemData['Weight'] = $dataRow['NetWgtKgs'];
            $ItemData['FrozenWeight'] = $dataRow['NetWgtKgs'] - $dataRow['FrozenWeightKgs'];
            /**uma */
            if ($EditedGrossWeight != 0) {
                $ItemData['GrossWeight'] = $EditedGrossWeight;
                $GrossWeightKgs = $EditedGrossWeight;
                $GrossWeightLbs = $EditedGrossWeight * 2.204;
            } else {
                $ItemData['GrossWeight'] = $dataRow['GrossWeightKgs'];
                $GrossWeightKgs = $dataRow['GrossWeightKgs'];
                $GrossWeightLbs = $dataRow['GrossWeightLbs'];
            }
            /**uma */
        } else {

            $ItemData['ItemPrice'] = $dataRow['PriceLbs'];
            $ItemData['Weight'] = $dataRow['NetWgtLbs'];
            $ItemData['FrozenWeight'] = $dataRow['NetWgtLbs'] - $dataRow['FrozenWeightLbs'];


            /**uma */
            if ($EditedGrossWeight != 0) {


                $ItemData['GrossWeight'] = $EditedGrossWeight;
                $GrossWeightKgs = $EditedGrossWeight * 0.454;
                $GrossWeightLbs = $EditedGrossWeight;
            } else {

                $ItemData['GrossWeight'] = $dataRow['GrossWeightLbs'];
                $GrossWeightKgs = $dataRow['GrossWeightKgs'];
                $GrossWeightLbs = $dataRow['GrossWeightLbs'];
            }
            /**uma */
        }




        $ItemData['NWeightKG'] = $dataRow['NetWgtKgs'];
        $ItemData['NWeightLB'] = $dataRow['NetWgtLbs'];
        $ItemData['GWeightKG'] = $GrossWeightKgs;
        $ItemData['GWeightLB'] = $GrossWeightLbs;

        // $ItemData['GWeightKG'] = $dataRow['GrossWeightKgs'];
        // $ItemData['GWeightLB'] = $dataRow['GrossWeightLbs'];
        $ItemData['FWeightKG'] = $dataRow['NetWgtKgs'] - $dataRow['FrozenWeightKgs'];
        $ItemData['FWeightLB'] = $dataRow['NetWgtLbs'] - $dataRow['FrozenWeightLbs'];

        return $ItemData;
    }

    public function getProformaItemWeightandPrice($ItemID, $QtyUnit) //item weight calculation quantity wise in invoices (usage in invoices prints)
    {
        $Qry = "WHERE ID in ($ItemID)";
        $PriceQry = 'Price';
        $sqlExt = "sum(PIMcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(PIMcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(PIMcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(PIMcs*GrossWeight*LbsFactor) as GrossWeightLbs,
                sum(PIMcs*TotalSlabs*SlabWeight*MFactor*(GlazePerc/100)) as FrozenWeightKgs,
				 sum(PIMcs*TotalSlabs*SlabWeight*LbsFactor*(GlazePerc/100)) as FrozenWeightLbs,sum(GrossWt) as EditedGrossWeight
                 ";

        $sqlQuery = "SELECT sum(Mcs) as MCS,sum(PIMcs) as PIMcs,TotalSlabs,InvId,  $sqlExt  ,
			$PriceQry ,			
			sum(PIMcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(PIMcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
			sum(PIMcs*TotalSlabs*Price ) as PriceBags
			FROM 
			`ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS 
			on PGD.PackingStyleID=PS.PCKID $Qry";



        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $EditedGrossWeight = $dataRow['EditedGrossWeight'];

        $ItemData['WeightKgs'] = $dataRow['NetWgtKgs'];
        $ItemData['GrossWeight'] = $dataRow['GrossWeightKgs']; //uma
        $ItemData['Price'] = $dataRow['Price']; //uma
        // $ItemData['ItemPrice'] = $dataRow['PriceKgs'];
        $ItemData['MCS'] = $dataRow['MCS'];
        $ItemData['PIMcs'] = $dataRow['PIMcs'];





        if ($QtyUnit == 'KGS') {
            $ItemData['ItemPrice'] = $dataRow['PriceKgs'];
            $ItemData['Weight'] = $dataRow['NetWgtKgs'];
            $ItemData['FrozenWeight'] = $dataRow['NetWgtKgs'] - $dataRow['FrozenWeightKgs'];
            /**uma */
            if ($EditedGrossWeight != 0) {
                $ItemData['GrossWeight'] = $EditedGrossWeight;
                $GrossWeightKgs = $EditedGrossWeight;
                $GrossWeightLbs = $EditedGrossWeight * 2.204;
            } else {
                $ItemData['GrossWeight'] = $dataRow['GrossWeightKgs'];
                $GrossWeightKgs = $dataRow['GrossWeightKgs'];
                $GrossWeightLbs = $dataRow['GrossWeightLbs'];
            }
            /**uma */
        } else {

            $ItemData['ItemPrice'] = $dataRow['PriceLbs'];
            $ItemData['Weight'] = $dataRow['NetWgtLbs'];
            $ItemData['FrozenWeight'] = $dataRow['NetWgtLbs'] - $dataRow['FrozenWeightLbs'];


            /**uma */
            if ($EditedGrossWeight != 0) {
                $ItemData['GrossWeight'] = $EditedGrossWeight;
                $GrossWeightKgs = $EditedGrossWeight * 0.454;
                $GrossWeightLbs = $EditedGrossWeight;
            } else {
                $ItemData['GrossWeight'] = $dataRow['GrossWeightLbs'];
                $GrossWeightKgs = $dataRow['GrossWeightKgs'];
                $GrossWeightLbs = $dataRow['GrossWeightLbs'];
            }
            /**uma */
        }


        $ItemData['NWeightKG'] = $dataRow['NetWgtKgs'];
        $ItemData['NWeightLB'] = $dataRow['NetWgtLbs'];
        $ItemData['GWeightKG'] = $GrossWeightKgs;
        $ItemData['GWeightLB'] = $GrossWeightLbs;

        // $ItemData['GWeightKG'] = $dataRow['GrossWeightKgs'];
        // $ItemData['GWeightLB'] = $dataRow['GrossWeightLbs'];
        $ItemData['FWeightKG'] = $dataRow['NetWgtKgs'] - $dataRow['FrozenWeightKgs'];
        $ItemData['FWeightLB'] = $dataRow['NetWgtLbs'] - $dataRow['FrozenWeightLbs'];

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
        $ItemData['FrozenWeightKgs'] = $dataRow['NetWgtKgs'] - $dataRow['FrozenWeightKgs'];
        $ItemData['FrozenWeightLbs'] = $dataRow['NetWgtLbs'] - $dataRow['FrozenWeightLbs'];
        if ($QtyUnit == 'KGS') {
            $ItemData['InvoiceValue'] = round($dataRow['PriceKgs'], 2);
        } else {
            $ItemData['InvoiceValue'] = round($dataRow['PriceLbs'], 2);
        }

        return $ItemData;
    }
    public function getProformaPriceDetails($PoID, $QtyUnit)
    {
        $Qry = "WHERE InvId in ($PoID)";
        $PriceQry = 'Price';
        $sqlExt = "sum(PIMcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(PIMcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(PIMcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(PIMcs*GrossWeight*LbsFactor) as GrossWeightLbs,
                sum(PIMcs*TotalSlabs*SlabWeight*MFactor*(GlazePerc/100)) as FrozenWeightKgs,
                sum(PIMcs*TotalSlabs*SlabWeight*LbsFactor*(GlazePerc/100)) as FrozenWeightLbs";

        $sqlQuery = "SELECT sum(Mcs) as MCS, sum(PIMcs) as PIMcs,TotalSlabs,InvId,  $sqlExt  ,
			$PriceQry ,			
			sum(PIMcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(PIMcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
			sum(PIMcs*TotalSlabs*Price ) as PriceBags
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
        $ItemData['FrozenWeightKgs'] = $dataRow['NetWgtKgs'] - $dataRow['FrozenWeightKgs'];
        $ItemData['FrozenWeightLbs'] = $dataRow['NetWgtLbs'] - $dataRow['FrozenWeightLbs'];
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


    public function getImporterRecordDetailsFromCache($ImporterID)
    {
        if (empty($ImporterID) || $ImporterID == 0) {
            return null;
        }

        // Request-level cache
        static $cache = null;

        // Load ALL importers once per request
        if ($cache === null) {

            $cache = [];

            $sqlQuery = "
            SELECT ImporterID, ImporterName, ImporterAddress
            FROM ScrmImporterOfRecord
        ";

            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cache[$row['ImporterID']] = [
                    'ImporterName' => $row['ImporterName'],
                    'ImporterAddress' => $row['ImporterAddress'],
                ];
            }
        }

        // Return from cache (null if not exists)
        return $cache[$ImporterID] ? $cache[$ImporterID] : null;
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


    public function getExporterDetailsFromCache($ExporterID, $CountryID, $DocType)
    {
        static $cache = [];

        $cacheKey = $ExporterID . '_' . $CountryID . '_' . $DocType;
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $queryParms = '';
        $params = ['expId' => $ExporterID];

        if (!empty($DocType)) {
            $queryParms .= " AND DocType IN ('', :docType)";
            $params['docType'] = $DocType;
        }

        if (!empty($CountryID)) {
            $queryParms .= " AND CountryID IN (0, :countryId)";
            $params['countryId'] = $CountryID;
        }


        $sqlQuery = "
        SELECT 
            ExpName, ExpAddress, ExpApprovalNo, ExpGstNo, InvShortCode,
            ExpMailID, ExpPhoneNumber, ExpUsfdaNo, ExpFeiNo,
            ExpIeCode, ExpIeCustomsNo, AuthSign, LutNumber, InvDateFormat
        FROM AdminExporterDetails
        WHERE ExpID = :expId
    ";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute(['expId' => $ExporterID]);
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dataRow) {
            return [];
        }

        $sqlCQuery = "
        SELECT ExporterName, ExporterAddress
        FROM ScrmCountrywiseAddresses
        WHERE ExporterID = :expId
        $queryParms
        ORDER BY CountryID DESC
        LIMIT 1
    ";

        $stmtC = $this->conn->prepare($sqlCQuery);
        $stmtC->execute($params);

        if ($stmtC->rowCount() > 0) {
            $override = $stmtC->fetch(PDO::FETCH_ASSOC);
            $dataRow['ExpName'] = $override['ExporterName'];
            $dataRow['ExpAddress'] = $override['ExporterAddress'];
        }
        $result = [
            'ExporterName' => $dataRow['ExpName'],
            'ExporterAddress' => $dataRow['ExpAddress'],
            'ExpApprovalNo' => $dataRow['ExpApprovalNo'],
            'ExpGstNo' => $dataRow['ExpGstNo'],
            'ExpMailID' => $dataRow['ExpMailID'],
            'ExpUsfdaNo' => $dataRow['ExpUsfdaNo'],
            'ExpFeiNo' => $dataRow['ExpFeiNo'],
            'ExpIeCode' => $dataRow['ExpIeCode'],
            'ExpIeCustomsNo' => $dataRow['ExpIeCustomsNo'],
            'AuthSign' => $dataRow['AuthSign'],
            'LutNumber' => $dataRow['LutNumber'],
            'InvDateFormat' => $dataRow['InvDateFormat'],
            'InvShortCode' => $dataRow['InvShortCode'],
        ];

        $cache[$cacheKey] = $result;
        return $result;
    }

    public function getExporterDetails($ExporterID, $CountryID, $DocType)
    {
        //echo $ExporterID; echo $CountryID;
        $queryParms = '';
        if (isset($DocType)) {
            // O default 
            // $queryParms .= " and DocType in (0,'$DocType')";
            $queryParms .= " and DocType in ('','$DocType')"; //edited by uma
        }
        //if (!isset($CountryID)) {
        if (isset($CountryID) && $CountryID != '') {
            // O default 
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

            // $sqlCQuery = "SELECT  ExporterName,ExporterAddress  FROM  ScrmCountrywiseAddresses  where  ExporterID ='$ExporterID' $queryParms ";
            $sqlCQuery = "SELECT  ExporterName,ExporterAddress  FROM  ScrmCountrywiseAddresses  where  ExporterID ='$ExporterID' $queryParms order by CountryID desc limit 1"; //edited by uma
            $stmtCExec = $this->conn->prepare($sqlCQuery);
            $stmtCExec->execute();
            if ($stmtCExec->rowCount() > 0) {
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
        $AryData = null;
        // $sqlFields = " id as StatusID,Name as StatusName";
        $sqlFields = " Code as StatusID,Name as StatusName"; // updated by uma on 10-07-2024

        // $sqlQuery = "SELECT $sqlFields  FROM `ScrmShipmentStatusCodes` ";
        $sqlQuery = "SELECT $sqlFields  FROM `ScrmShipmentStatusCodes` where BulkApprovalStatus=1"; // updated by uma on 10-07-2024
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();
        if ($stmtExec) {
            while ($dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC)) {

                $ItemData['StatusID'] = $dataRow['StatusID'];
                $ItemData['StatusName'] = $dataRow['StatusName'];
                $AryData[] = $ItemData;
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

    function getFormatedDate($rawDate)
    {


        $Date = date("d-m-Y", strtotime($rawDate));
        if ($Date == '01-01-1970') {
            $Date = 'NA';
        }
        return $Date;
    }

    function getFormatedDateEmpty($rawDate)
    {


        $Date = date("d-m-Y", strtotime($rawDate));
        if ($Date == '01-01-1970') {
            $Date = '';
        }
        return $Date;
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
        /* if(isset($DocType)){
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
    function getInvoiceShowConditions($DocType, $CountryID)
    {
        if (isset($DocType)) {
            $queryParms = "and CountryID in (0,$CountryID)";
        }
        $sqlQuery = "SELECT * FROM `ScrmCountryWiseShowConditions` WHERE   
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
            $no = $stmtExec->rowCount();
            if ($no > 0) {

                $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

                $ItemData['BrandName'] = $dataRow['BrandName'];
                $ItemData['Remarks'] = $dataRow['Remarks'];
                return $ItemData;
            } else {
                $ItemData['BrandName'] = '';
                $ItemData['Remarks'] = '';
                return $ItemData;
            }
        }
    }

    public function getPackingListDetails($PackingID)
    {
        $sqlFields = "`TotalSlabs`,`SlabWeight`,`Uom`,`PackingStyle`,`GrossWeight`, `LbsFactor`";

        $sqlQuery = "SELECT $sqlFields  FROM `AdminPackingStyles` where PCKID='$PackingID'";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();

        if ($stmtExec) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

            $ItemData['PackingStyle'] = $dataRow['PackingStyle'];
            $ItemData['LbsFactor'] = $dataRow['LbsFactor'];
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

    public function getFormatedDatelines($DATE)
    {

        $TIME_STAMP = strtotime($DATE);
        $formated_date = date("d-m-Y", $TIME_STAMP);
        if ($formated_date == '01-01-1970') {
            $formated_date = '--';
        }
        return $formated_date;
    }

    public function getFinancialYear($FinClsYear)
    {
        $DateStart = $FinClsYear . '-04-01';
        $DateEnd = ($FinClsYear + 1) . '-03-31';

        return $DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
    }

    function getVariety($PoID)
    {

        $sql = "SELECT GROUP_CONCAT(SPVL.Grade,'-',ProductDescription) as Variety FROM  `ScrmPoGradeData` as SPGD
		left join `ScrmPIVarietyList`  as  SPVL on  SPVL.VarietyID=SPGD.VarietyID WHERE InvId='$PoID'";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $Variety = $row['Variety'];
        $Variety = str_replace(',', ',<br>', $Variety);

        return $Variety;
    }

    public function getPaymentValuesOld($PoID, $DocType)
    {


        $LbsFactor = 0.454; // To Convert Lbs into Kgs multiply value
        /* $Discount=0; // Get from Custom Invoice
            $InsurancePerc=0.11; // Varible
            $AntiDumpingDFactor=101.35 // Anti Dumping Duty Factor(Formula)
            $AntiDumpingMFactor=1.35 // Anti Dumping Duty Factor(Formula)
            $StandardDutyCharges=125; // Standard Value
            $TermName=125; // Standard Value
            $CenttoUsd=100; // Standard Value 1 usd=100 cents

            */

        // echo $DocType; exit;

        /* $RawIpData = file_get_contents('php://input');
        $IpData = json_decode($RawIpData,true);
        $PoID=$IpData['PoID'];*/


        $sqlDiscountType = "SELECT DiscountType FROM `AdminConfigDetails`";
        $stmtDiscountType = $this->conn->prepare($sqlDiscountType);
        $stmtDiscountType->execute();
        $dataRowDiscountType = $stmtDiscountType->fetch(PDO::FETCH_ASSOC);
        $DiscountType = $dataRowDiscountType['DiscountType'];

        $CenttoUsd = 100;
        if ($DocType == 'CMI') {
            $sqlCInvData = "SELECT *,ExchngRte as ExRate,SPD.ExporterID as ExpID FROM ScrmPoData as SPD 
                LEFT JOIN ScrmCommercialData as SCD on 
                SPD.PoID=SCD.PoID WHERE SPD.PoID=$PoID";
        } else {
            $sqlCInvData = "SELECT *,SPD.ExporterID as ExpID FROM ScrmPoData as SPD 
                LEFT JOIN ScrmCustomInvoices as SCI on 
                SPD.PoID=SCI.PiID WHERE SPD.PoID=$PoID";
        }
        // echo $sqlCInvData; 


        $stmtCInvData = $this->conn->prepare($sqlCInvData);
        $stmtCInvData->execute();
        $dataRowCInvData = $stmtCInvData->fetch(PDO::FETCH_ASSOC);
        // var_dump($dataRowCInvData);
        $FrieghtCharge = 0;
        $ExchangeRate = 0;
        $Discount = 0;
        $DestinationCountryID = $dataRowCInvData['DestinationCountry'];
        if ($DocType == 'CMI') {
            $ArrayFreight = $this->checkFreight($PoID);
            // var_dump($ArrayFreight);
            $FrieghtCharge = $ArrayFreight['FrieghtCharge'];
            $ExchangeRate = $ArrayFreight['ExchangeRate'];
        } else {
            $FrieghtCharge = $dataRowCInvData['Freight'];
            $ExchangeRate = $dataRowCInvData['ExRate'];
        }


        $TermName = $dataRowCInvData['ShippingTerms'];
        $Discount = $dataRowCInvData['Discount'];
        /* $FrieghtCharge = $dataRowCInvData['Freight'];
            $ExchangeRate = $dataRowCInvData['ExRate'];*/
        $QtyUnit = $dataRowCInvData['QtyUnit'];
        $ExporterID = $dataRowCInvData['ExpID'];
        // $CInvCode = $dataRowCInvData['CInvCode'];

        // if ($CInvCode == '03061720') {
        // $IgstPerc = 5;
        // } else {
        // $IgstPerc = 12;
        // }


        /**uma(14-10-2023) */
        // $SqlGstPerc = "SELECT DISTINCT GstPerc FROM `ScrmPoGradeData` as SPGD
        // left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID 
        // left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'"; 

        $SqlGstPerc = "SELECT DISTINCT (SELECT GstPerc FROM ScrmPreparationMasterGstPerc SPMG WHERE SPMG.ParentId=SPM.ID AND SPMG.`FromDate`<=SCI.`CInvDate` ORDER BY SPMG.ID DESC LIMIT 1)
		GstPerc,
		SPM.GstPerc GstPercP,FDAEntryDate,CInvDate FROM `ScrmPoGradeData` as SPGD
		left join ScrmCustomInvoices as SCI on SCI.PiID=SPGD.InvId
		left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
		left join ScrmISFDetails as ISF on ISF.PIID=SPGD.InvId		
		 left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";

        $StmtGstPerc = $this->conn->prepare($SqlGstPerc);
        $StmtGstPerc->execute();
        $RowGstPerc = $StmtGstPerc->fetch(PDO::FETCH_ASSOC);


        //while($RowHsCode=$StmtHsCode->fetch_assoc()){
        // $ArrayHsCode[]=$RowHsCode['HSCode'];
        // $CInvCode = implode(',', $ArrayHsCode);

        //}

        $IgstPerc = !empty($RowGstPerc['GstPerc']) ? $RowGstPerc['GstPerc'] : $RowGstPerc['GstPercP'];
        $FDAEntryDate = $RowGstPerc['FDAEntryDate'];
        $CInvDate = $RowGstPerc['CInvDate'];
        // if($CInvCode=='03061720'){
        // $IgstPerc=5;
        // }else{
        // $IgstPerc=12; 
        // }

        /**uma (14-10-2023)*/

        $QtyUnit = $dataRowCInvData['QtyUnit'];
        $Query = " and FromDate<='$CInvDate'";
        if ($DocType == 'CMI') {
            if ($FDAEntryDate != '') {
                $Query = " and FromDate<='$FDAEntryDate'";
            }
        }

        $sqlShipTermsMaster = "SELECT * FROM `ScrmShipmentTermsMaster` where TermName='$TermName'";
        $stmtShipTermsMaster = $this->conn->prepare($sqlShipTermsMaster);
        $stmtShipTermsMaster->execute();
        $dataRowShipTermsMaster = $stmtShipTermsMaster->fetch(PDO::FETCH_ASSOC);



        // Update With ExportId Condition
        // $sqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates`";
        // $sqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' $Query  ORDER BY ExpTRID DESC limit 1";
        $sqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' $Query  ORDER BY ExpTRID DESC limit 1";
        $stmtExporterTaxRates = $this->conn->prepare($sqlExporterTaxRates);
        $stmtExporterTaxRates->execute();
        $dataRowExporterTaxRates = $stmtExporterTaxRates->fetch(PDO::FETCH_ASSOC);






        $AntiDumpingDutyPerc = $dataRowExporterTaxRates['AntiDumpingDuty'];
        $CountervailingDutyPerc = $dataRowExporterTaxRates['CountervailingDuty']; //uma cvd calculation included //12-04-2024
        $MerchandiseProcessFeePerc = $dataRowExporterTaxRates['MerchandiseProcessFee'];
        $HarbourMaintenanceFeePerc = $dataRowExporterTaxRates['HarbourMaintenanceFee'];
        $ISFStaticDuty = $dataRowExporterTaxRates['ISFStaticDuty'];
        $InsurancePerc = $dataRowExporterTaxRates['InsurancePremiumRate'];
        $ClearingAndForwardingCharges = $dataRowExporterTaxRates['ClearingCharges']; // Clearing and Forwarding Charges // Nithin[23-10-2025]
        $DrawbackRateperKG = $dataRowExporterTaxRates['DrawbackRateperKG'];
        $DrawbackPercentage = $dataRowExporterTaxRates['DrawbackPercentage'];



        $SqlReciprocalTariffsExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' and FromDate<='$CInvDate'  ORDER BY ExpTRID DESC limit 1";
        $StmtReciprocalTariffsExporterTaxRates = $this->conn->prepare($SqlReciprocalTariffsExporterTaxRates);
        $StmtReciprocalTariffsExporterTaxRates->execute();
        $RowReciprocalTariffsExporterTaxRates = $StmtReciprocalTariffsExporterTaxRates->fetch(PDO::FETCH_ASSOC);
        $ReciprocalTariffsPerc = $RowReciprocalTariffsExporterTaxRates['ReciprocalTariffs'];

        // $TotalDutyPerc = 100 + $AntiDumpingDutyPerc +
        // $MerchandiseProcessFeePerc +
        // $HarbourMaintenanceFeePerc;
        $TotalDutyPerc = 100 + $AntiDumpingDutyPerc +
            $MerchandiseProcessFeePerc +
            $HarbourMaintenanceFeePerc + $CountervailingDutyPerc + $ReciprocalTariffsPerc; //uma cvd calculation included //12-04-2024


        $IsFreight = $dataRowShipTermsMaster['Freight'];
        $IsDuty = $dataRowShipTermsMaster['Duty'];
        $Insurance = $dataRowShipTermsMaster['Insurance'];
        $AntiDumpingDFactor = $dataRowShipTermsMaster['AntiDumpingDFactor'];
        // $AntiDumpingMFactor = $dataRowShipTermsMaster['AntiDumpingMFactor'];
        $StandardDutyCharges = $dataRowShipTermsMaster['StandardDutyCharges'];



        $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
                    sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
                    sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
                    sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";


        /* $sqlTotal = "SELECT sum(Mcs) as MCS,  $sqlExt  ,
                sum(Price) as Price,			
                sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
                sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs 
                FROM `ScrmPoGradeData` 
                as PGD left join AdminPackingStyles as PS on 
                PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'"; */

        /* Added this Query to calculate Shrimp & Sauce value if Sauce exists in an Invoice --Nithin[23-10-2025] */
        $sqlTotal = "SELECT sum(Mcs) as MCS, $sqlExt,
			sum(Price) as Price,PS.Uom,
			sum(CASE WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * LbsFactor * 0.454 * Price ELSE 0 END) AS PriceOZKgs,
			sum(CASE WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * LbsFactor * Price ELSE 0 END) AS PriceOZLbs,
			sum(CASE WHEN PS.Uom != 'OZ' THEN Mcs * TotalSlabs * SlabWeight * Price * MFactor ELSE 0 END) AS PriceKgs,
			sum(CASE WHEN PS.Uom != 'OZ' THEN Mcs * TotalSlabs * SlabWeight * Price * LbsFactor ELSE 0 END) AS PriceLbs,
    		sum(Mcs*TotalSlabs*SlabWeight*MFactor*Price / 0.454) AS UsdValue,
			GlazeStatus,
			VL.GlazePerc,ISF.BLDate
			FROM `ScrmPoGradeData` as PGD
			LEFT JOIN (
                SELECT 
                    *, 
                    CASE 
                        WHEN HasSauce = 'Yes' THEN ShrimpPackingStyle 
                        ELSE PackingStyle 
                    END AS EffectivePackingStyle 
                FROM ScrmPIVarietyList
            ) AS VL ON VL.VarietyID = PGD.VarietyID
			left join ScrmISFDetails as ISF on ISF.PIID=PGD.InvId
			left join AdminPackingStyles as PS on VL.EffectivePackingStyle=PS.PCKID WHERE InvId='$PoID'";
        $stmtTotal = $this->conn->prepare($sqlTotal);
        $stmtTotal->execute();
        $dataRowTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);

        $IsSauce = $this->checkSauceExists($PoID);
        $SauceTotalUsdValue = 0;
        $SauceTotalKgs = 0;
        $SauceTotalLbs = 0;
        if ($IsSauce && $IsSauce == 1) {
            $SqlSauce = "SELECT PGD.Mcs, PGD.VarietyID, PGD.CUPS, PGD.CupsPrice, PS.*, SPD.QtyUnit
			FROM ScrmPoGradeData as PGD
			LEFT JOIN ScrmPoData as SPD ON SPD.PoID = PGD.InvId
			LEFT JOIN ScrmPIVarietyList as SPVL ON SPVL.VarietyID = PGD.VarietyID
			LEFT JOIN AdminPackingStyles as PS ON SPVL.SaucePackingStyle = PS.PCKID
			WHERE InvId='$PoID' AND SPVL.HasSauce = 'Yes'";
            $StmtSauce = $this->conn->prepare($SqlSauce);
            $StmtSauce->execute();
            while ($RowSauce = $StmtSauce->fetch(PDO::FETCH_ASSOC)) {
                $SauceLbsWeight = $RowSauce['TotalSlabs'] * $RowSauce['SlabWeight'] * $RowSauce['Mcs'];
                if ($RowSauce['Uom'] == 'OZ') {
                    $OzToLbs = $RowSauce['SlabWeight'] * $RowSauce['LbsFactor'];
                    $SauceLbsWeight *= $OzToLbs / $RowSauce['SlabWeight'];
                }
                $SauceKgsWeight = $SauceLbsWeight * 0.454;
                if ($RowSauce['QtyUnit'] == 'LBS' && $PType == 'KGS') {
                    $QtyCalc = $SauceKgsWeight;
                    $RowSauce['CupsPrice'] = $RowSauce['CupsPrice'] / 0.454;
                } else {
                    $QtyCalc = $SauceLbsWeight;
                }
                $SauceTotalUsdValue += $QtyCalc * $RowSauce['CupsPrice'];
                $SauceTotalKgs += $SauceKgsWeight;
                $SauceTotalLbs += $SauceLbsWeight;
            }
        }

        $DiscountAmount = 0;
        $NetWgtKgs = $dataRowTotal['NetWgtLbs'] * $LbsFactor;
        $NetWgtLbs = $dataRowTotal['NetWgtLbs'];

        $GrossWeightKgs = $dataRowTotal['GrossWeightKgs'];
        $GrossWeightLbs = $dataRowTotal['GrossWeightLbs'];

        $TotalMcs = $dataRowTotal['MCS'];

        if ($QtyUnit == 'KGS') {
            $Weight = $dataRowTotal['NetWgtKgs'] + $SauceKgsWeight;
            $InvoiceValue = $dataRowTotal['PriceOZKgs'] > 0 ? $dataRowTotal['PriceOZKgs'] + $dataRowTotal['PriceKgs'] : $dataRowTotal['PriceKgs'];
            $AryPrices['ShrimpValueUsd'] = $InvoiceValue;
            $AryPrices['SauceValueUsd'] = $SauceTotalUsdValue;
        } else {
            $Weight = $dataRowTotal['NetWgtLbs'] * $LbsFactor;
            $InvoiceValue = $dataRowTotal['PriceOZLbs'] > 0 ? $dataRowTotal['PriceOZLbs'] + $dataRowTotal['PriceLbs'] : $dataRowTotal['PriceLbs'];
            $AryPrices['ShrimpValueUsd'] = $InvoiceValue;
            $AryPrices['SauceValueUsd'] = $SauceTotalUsdValue;
            // $InvoiceValue += $SauceTotalUsdValue * 0.454;
        }

        $InvoiceValue += $SauceTotalUsdValue;

        if ($Discount > 0) {
            if ($DiscountType == 0) {

                $DiscountAmount = ($InvoiceValue * $Discount) / 100;
                $InvoiceValueAfterDiscount = $InvoiceValue;
                $InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount; // By Rantagiri 04/17/2023 5.44 
            } else {
                $DiscountAmount = $Discount;
                $InvoiceValueAfterDiscount = $InvoiceValue;
                $InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount;
            }
        } else {
            $InvoiceValueAfterDiscount = $InvoiceValue;
        }

        if ($Insurance == 1) {
            $InsuranceValue = ($InvoiceValueAfterDiscount * $InsurancePerc) / 100;
        } else {
            $InsuranceValue = 0;
        }

        // if ($IsDuty == 1 ) { //uma
        if ($IsDuty == 1 && $DestinationCountryID == 157) { //uma
            /*echo $MerchandiseProcessFeePerc=0; 
            echo $InvoiceValueAfterDiscount=0;
            echo $InsuranceValue=0;
            echo $FrieghtCharge=0;
            // echo $TotalDutyPerc=0;*/


            $MerchandiseProcessFee = (($InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges) / $TotalDutyPerc) * $MerchandiseProcessFeePerc;
            //$MerchandiseProcessFee=round((($EffInvValue*$MerchandiseProcessFeePerc)/$TotalDutyPerc),2);
            if ($MerchandiseProcessFee > $ISFStaticDuty) {
                $ISFStatic = true;
                $MerchandiseProcessFee = $ISFStaticDuty;
                $TotalDutyPerc = $TotalDutyPerc - $MerchandiseProcessFeePerc;
                $EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges - $MerchandiseProcessFee;
            } else {
                $ISFStatic = false;
                $EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges;
                $MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
            }

            $AntiDumpingCharges = round((($EffInvValue * $AntiDumpingDutyPerc) / $TotalDutyPerc), 2);
            $HarbourMaintenanceFee = round((($EffInvValue * $HarbourMaintenanceFeePerc) / $TotalDutyPerc), 2);
            $CountervailingDutyFee = round((($EffInvValue * $CountervailingDutyPerc) / $TotalDutyPerc), 2); //uma cvd calculation included //12-04-2024
            $ReciprocalTariffsFee = 0;
            if ($ReciprocalTariffsPerc > 0) {
                $ReciprocalTariffsFee = round((($EffInvValue * $ReciprocalTariffsPerc) / $TotalDutyPerc), 2); //uma cvd calculation included //18-03-2024
            }
            if ($ISFStatic) {
                // $DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee;
                $AntidumpingandPortChargesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee; //uma cvd calculation included //12-04-2024
                $DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee + $CountervailingDutyFee + $ReciprocalTariffsFee; //uma cvd calculation included //12-04-2024
            } else {
                // $DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee;
                $AntidumpingandPortChargesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee;
                $DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee + $CountervailingDutyFee + $ReciprocalTariffsFee; //uma cvd calculation included //12-04-2024

            }
        } else {
            $DutiesPrice = $DutiesPrice + $StandardDutyCharges;
            $AntidumpingandPortChargesPrice = $AntidumpingandPortChargesPrice + $StandardDutyCharges;
        }

        $InvValueUSD = $InvoiceValueAfterDiscount;
        if ($ISFStatic) {
            $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $ISFStaticDuty;
            $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $ISFStaticDuty;
        } else {
            $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge;
            $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice;
        }

        if ($ClearingAndForwardingCharges > 0) {
            $InvValueUSDWithOutDuties = $InvValueUSDWithOutDuties - $ClearingAndForwardingCharges;
            $FOBValueUSD -= $ClearingAndForwardingCharges;
        }

        /* echo 'hi';
            echo $ExchangeRate?$ExchangeRate:0;
            echo $FOBValueUSD?$FOBValueUSD:0;
            echo $InvValueUSD?$InvValueUSD:0;
            */

        // $InvValueINR = $InvValueUSD * $ExchangeRate;
        $InvValueINR = $InvoiceValue * $ExchangeRate; //calculate igst for invoice value @coastal
        $FOBValueINR = $FOBValueUSD * $ExchangeRate;

        $IgstValue = ($InvValueINR * $IgstPerc) / 100;
        $TotalInvoiceValueINR = $IgstValue + $InvValueINR;



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
        $AryPrices['DrawbackPercentage'] = $DrawbackPercentage;
        $AryPrices['DrawbackRateperKG'] = $DrawbackRateperKG;
        $AryPrices['AntidumpingandPortChargesPrice'] = $AntidumpingandPortChargesPrice;
        $AryPrices['CountervailingDuty'] = $CountervailingDutyFee; //uma cvd calculation included //12-04-2024
        $AryPrices['CountervailingDutyPerc'] = $CountervailingDutyPerc; //uma cvd calculation included //12-04-2024
        $AryPrices['HarbourMaintenanceFeePerc'] = $HarbourMaintenanceFeePerc; //uma cvd calculation included //12-04-2024
        $AryPrices['AntiDumpingDutyPerc'] = $AntiDumpingDutyPerc; //uma cvd calculation included //12-04-2024
        $AryPrices['MerchandiseProcessFeePerc'] = $MerchandiseProcessFeePerc; //uma cvd calculation included //12-04-2024
        $AryPrices['ReciprocalTariffsFee'] = $ReciprocalTariffsFee;
        $AryPrices['ReciprocalTariffsPerc'] = $ReciprocalTariffsPerc;
        // $AryPrices['ShowAdd'] = 1;//usa->show,others->hide

        return $AryPrices;
    }

    function getPaymentValues($PoID, $DocType, $ConfigDetails)
    {
        $sqlDiscountType = "SELECT DiscountType FROM `AdminConfigDetails`";
        $stmtDiscountType = $this->conn->prepare($sqlDiscountType);
        $stmtDiscountType->execute();
        $dataRowDiscountType = $stmtDiscountType->fetch(PDO::FETCH_ASSOC);
        $DiscountType = $dataRowDiscountType['DiscountType'];

        $CenttoUsd = 100;
        if ($DocType == 'CMI') {
            $SqlCInvData = "SELECT *,ExchngRte as ExRate,SPD.ExporterID as ExpID FROM ScrmPoData as SPD
				LEFT JOIN ScrmCommercialData as SCD on SPD.PoID=SCD.PoID WHERE SPD.PoID=$PoID";
        } else {
            $SqlCInvData = "SELECT *,SPD.ExporterID as ExpID, SCDD.Freight, SCI.Freight AS CustomInvoiceFreight FROM ScrmPoData as SPD
				LEFT JOIN ScrmCommercialData as SCDD on SPD.PoID=SCDD.PoID LEFT JOIN ScrmCustomInvoices as SCI on
				SPD.PoID=SCI.PiID WHERE SPD.PoID=$PoID";
        }

        $StmtCInvData = $this->conn->prepare($SqlCInvData);
        $StmtCInvData->execute();
        $RowCInvData = $StmtCInvData->fetch(PDO::FETCH_ASSOC);

        $ExporterID = $RowCInvData['ExpID'];

        $CustomInvoiceFreight = $RowCInvData['CustomInvoiceFreight'];
        $CommercialFrieghtCharge = $RowCInvData['Freight'];

        if ($DocType == 'CMI') {
            $ArrayFreight = $this->checkFreight($PoID);
            $FrieghtCharge = $ArrayFreight['FrieghtCharge'];
            $ExchangeRate = $ArrayFreight['ExchangeRate'];
        } else {
            $FrieghtCharge = $RowCInvData['Freight'];
            $ExchangeRate = $RowCInvData['ExRate'];
        }

        $TermName = $RowCInvData['ShippingTerms'];
        $Discount = $RowCInvData['Discount'];

        $QtyUnit = $RowCInvData['QtyUnit'];
        $CInvCode = $RowCInvData['CInvCode'];

        $UnitCurrency = $RowCInvData['UnitCurrency'];
        $DestinationCountryId = $RowCInvData['DestinationCountry'];

        // $SqlGstPerc = "SELECT DISTINCT GstPerc,FDAEntryDate FROM `ScrmPoGradeData` as SPGD
        // left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
        // left join ScrmISFDetails as ISF on ISF.PIID=SPGD.InvId
        // left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";

        $SqlGstPerc = "SELECT DISTINCT (SELECT GstPerc FROM ScrmPreparationMasterGstPerc SPMG WHERE SPMG.ParentId=SPM.ID
			AND SPMG.`FromDate`<=SCI.`CInvDate` ORDER BY SPMG.ID DESC LIMIT 1) GstPerc,
			SPM.GstPerc GstPercP,FDAEntryDate,CInvDate,ISF.BLDate FROM `ScrmPoGradeData` as SPGD
			left join ScrmCustomInvoices as SCI on SCI.PiID=SPGD.InvId
			left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
			left join ScrmISFDetails as ISF on ISF.PIID=SPGD.InvId
			left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";

        // $StmtGstPerc = $iconn->query($SqlGstPerc);
        // $RowGstPerc = $StmtGstPerc->fetch_assoc();

        $StmtGstPerc = $this->conn->prepare($SqlGstPerc);
        $StmtGstPerc->execute();
        $RowGstPerc = $StmtGstPerc->fetch(PDO::FETCH_ASSOC);

        $FDAEntryDate = $RowGstPerc['FDAEntryDate'];
        $IgstPerc = !empty($RowGstPerc['GstPerc']) ? $RowGstPerc['GstPerc'] : $RowGstPerc['GstPercP'];
        $CInvDate = $RowGstPerc['CInvDate'];
        $BLDate = $RowGstPerc['BLDate'];

        // $CanadaLbsFactor = $ClientId == 'SMEV3' && $DestinationCountryId == 23 && $CInvDate >= '2026-03-01' ? 2.2046 : 'LbsFactor';

        // if ($CInvCode == '03061720') {
        //     $IgstPerc = 5;
        // } else {
        //     $IgstPerc = 12;
        // }

        $QtyUnit = $RowCInvData['QtyUnit'];

        if ($FDAEntryDate != '') {
            $Query = " and FromDate<='$FDAEntryDate'";
            // $DocType = !isset($DocType) ? $DocType : 'CMI';
        } else {
            $Query = " and FromDate<='$CInvDate'";
            // $DocType = !isset($DocType) ? $DocType : 'CUI';
        }

        $SqlShipTermsMaster = "SELECT * FROM `ScrmShipmentTermsMaster` where TermName='$TermName'";
        $StmtShipTermsMaster = $this->conn->prepare($SqlShipTermsMaster);
        $StmtShipTermsMaster->execute();
        $RowShipTermsMaster = $StmtShipTermsMaster->fetch(PDO::FETCH_ASSOC);

        // Update With ExportId Condition
        // $SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates`";
        $SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' $Query  ORDER BY ExpTRID DESC limit 1";

        $StmtExporterTaxRates = $this->conn->prepare($SqlExporterTaxRates);
        $StmtExporterTaxRates->execute();
        $RowExporterTaxRates = $StmtExporterTaxRates->fetch(PDO::FETCH_ASSOC);
        $AntiDumpingDutyPerc = $RowExporterTaxRates['AntiDumpingDuty'];
        $CountervailingDutyPerc = $RowExporterTaxRates['CountervailingDuty'];
        $MerchandiseProcessFeePerc = $RowExporterTaxRates['MerchandiseProcessFee'];
        $HarbourMaintenanceFeePerc = $RowExporterTaxRates['HarbourMaintenanceFee'];
        $ISFStaticDuty = $RowExporterTaxRates['ISFStaticDuty'];
        $InsurancePerc = $RowExporterTaxRates['InsurancePremiumRate'];
        $ClearanceChargePerc = $RowExporterTaxRates['ClearanceCharge'];
        $ReciprocalTariffsPerc = $RowExporterTaxRates['ReciprocalTariffs'];
        $ClearingAndForwardingCharges = $RowExporterTaxRates['ClearingCharges'];
        $TariffPerc = $RowExporterTaxRates['Tariff'];
        $DrawbackRateperKG = $RowExporterTaxRates['DrawbackRateperKG'];
        $DrawbackPercentage = $RowExporterTaxRates['DrawbackPercentage'];

        $SqlReciprocalTariffsExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' and FromDate<='$CInvDate' ORDER BY ExpTRID DESC limit 1";
        $StmtReciprocalTariffsExporterTaxRates = $this->conn->prepare($SqlReciprocalTariffsExporterTaxRates);
        $StmtReciprocalTariffsExporterTaxRates->execute();
        $RowReciprocalTariffsExporterTaxRates = $StmtReciprocalTariffsExporterTaxRates->fetch(PDO::FETCH_ASSOC);
        $ReciprocalTariffsPerc = $RowReciprocalTariffsExporterTaxRates['ReciprocalTariffs'];
        $TariffPerc = $RowReciprocalTariffsExporterTaxRates['Tariff'];

        $SqlRevPoTaxRates = "
			SELECT *
				FROM ScrmPoTaxRates
				WHERE PoID = '$PoID'
				AND InvoiceType = (
					CASE
						WHEN EXISTS (
							SELECT 1
							FROM ScrmPoTaxRates
							WHERE PoID = '$PoID'
							AND InvoiceType = 'CMI'
						)
						THEN 'CMI'
						ELSE 'CUI'
					END
				)
				ORDER BY ID DESC LIMIT 1
			";
        $StmtRevPoTaxRates = $this->conn->prepare($SqlRevPoTaxRates);
        $StmtRevPoTaxRates->execute();
        if ($StmtRevPoTaxRates && $StmtRevPoTaxRates->rowCount() > 0) {
            $RowRevPoTaxRates = $StmtRevPoTaxRates->fetch(PDO::FETCH_ASSOC);
            $AntiDumpingDutyPerc = $RowRevPoTaxRates['AntiDumpingDuty'];
            $AntiDumpingDutyPerc = $RowRevPoTaxRates['AntiDumpingDuty'];
            $CountervailingDutyPerc = $RowRevPoTaxRates['CountervailingDuty'];
            $MerchandiseProcessFeePerc = $RowRevPoTaxRates['MerchandiseProcessFee'];
            $HarbourMaintenanceFeePerc = $RowRevPoTaxRates['HarbourMaintenanceFee'];
            $ISFStaticDuty = $RowRevPoTaxRates['ISFStaticDuty'];
            $InsurancePerc = $RowRevPoTaxRates['InsurancePremiumRate'];
            $ReciprocalTariffsPerc = $RowRevPoTaxRates['ReciprocalTariff'];
            $ClearingAndForwardingCharges = $RowRevPoTaxRates['ClearingCharges'];
            $TariffPerc = $RowRevPoTaxRates['Tariff'];
        }

        $TotalDutyPerc = 100 + $AntiDumpingDutyPerc +
            $MerchandiseProcessFeePerc +
            $HarbourMaintenanceFeePerc + $CountervailingDutyPerc +
            $ReciprocalTariffsPerc + $TariffPerc;

        $IsFreight = $RowShipTermsMaster['Freight'];
        $IsDuty = $RowShipTermsMaster['Duty'];
        $Insurance = $RowShipTermsMaster['Insurance'];
        $AntiDumpingDFactor = $RowShipTermsMaster['AntiDumpingDFactor'];
        $AntiDumpingMFactor = $RowShipTermsMaster['AntiDumpingMFactor'];
        $StandardDutyCharges = $RowShipTermsMaster['StandardDutyCharges'];

        // $ConfigDetails['CanadaLbsFactor'] = $CanadaLbsFactor;
        $ConfigDetails['DestCountryID'] = $DestinationCountryId;
        // $ConfigDetails['ClientId'] = $ClientId;
        $AryInvBreakUpData = $this->getGradeBreakUpData('', $PoID, $ConfigDetails);
        $IsSauce = $AryInvBreakUpData['HasSauce'];

        $SauceTotalUsdValue = 0;
        $SauceTotalKgs = 0;
        $SauceTotalLbs = 0;
        $TotalSauceLbsWeight = 0;
        if ($IsSauce == 'Yes') {
            $SqlSauce = "SELECT PGD.Mcs, PGD.VarietyID, PGD.CUPS, PGD.CupsPrice, PS.*, SPD.QtyUnit
                FROM ScrmPoGradeData as PGD
                LEFT JOIN ScrmPoData as SPD ON SPD.PoID = PGD.InvId
                LEFT JOIN ScrmPIVarietyList as SPVL ON SPVL.VarietyID = PGD.VarietyID
                LEFT JOIN AdminPackingStyles as PS ON SPVL.SaucePackingStyle = PS.PCKID
                WHERE InvId='$PoID' AND SPVL.HasSauce = 'Yes'";
            $StmtSauce = $this->conn->prepare($SqlSauce);
            $StmtSauce->execute();
            while ($RowSauce = $StmtSauce->fetch(PDO::FETCH_ASSOC)) {
                $SauceLbsWeight = $RowSauce['TotalSlabs'] * $RowSauce['SlabWeight'] * $RowSauce['Mcs'];
                if ($RowSauce['Uom'] == 'OZ') {
                    $OzToLbs = $RowSauce['SlabWeight'] * $RowSauce['LbsFactor'];
                    $SauceLbsWeight *= $OzToLbs / $RowSauce['SlabWeight'];
                }
                // $SauceKgsWeight = $SauceLbsWeight * 0.454;
                // if ($RowSauce['QtyUnit'] == 'LBS' && $PType == 'KGS') {
                //     $QtyCalc = $SauceKgsWeight;
                //     $RowSauce['CupsPrice'] = $RowSauce['CupsPrice'] / 0.454;
                // } else {
                //     $QtyCalc = $SauceLbsWeight;
                // }
                $SauceTotalUsdValue += $SauceLbsWeight * $RowSauce['CupsPrice'];
                // $SauceTotalKgs += $SauceKgsWeight;
                $SauceTotalLbs += $SauceLbsWeight;
                $TotalSauceLbsWeight += $SauceLbsWeight;
            }
        }


        // $TotalMcs = $AryInvBreakUpData['MCS'];

        // if ($QtyUnit == 'KGS') {
        // 	$Weight = $AryInvBreakUpData['NetWgtKgs'] + $SauceKgsWeight;
        // 	$InvoiceValue = $AryInvBreakUpData['PriceKgs'];
        // } else {
        // 	$Weight = $AryInvBreakUpData['NetWgtLbs'];
        // 	$InvoiceValue = $AryInvBreakUpData['PriceLbs'];
        // }

        $InvoiceValue = $AryInvBreakUpData['UsdValue'] + $SauceTotalUsdValue;

        $AryPrices['ShrimpValueUsd'] = $InvoiceValue;
        $AryPrices['SauceValueUsd'] = $SauceTotalUsdValue;

        // if ($RowCInvData['CInvDate'] >= '2025-10-14') {
        //     $InvoiceValue += $SauceTotalUsdValue;
        // }

        if ($Discount > 0) {
            if ($DiscountType == 0) {
                $DiscountAmount = ($InvoiceValue * $Discount) / 100;
                $InvoiceValueAfterDiscount = $InvoiceValue;
                $InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount; // By Rantagiri 04/17/2023 5.44 
            } else {
                $DiscountAmount = $Discount;
                $InvoiceValueAfterDiscount = $InvoiceValue;
                $InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount;
            }
        } else {
            $InvoiceValueAfterDiscount = $InvoiceValue;
        }

        if ($Insurance == 1) {
            // $InsuranceValue = ($PriceAfterDiscount * $InsurancePerc) / 100;
            $InsuranceValue = ($InvoiceValueAfterDiscount * $InsurancePerc) / 100;
        } else {
            $InsuranceValue = 0;
        }

        $FrieghtCharge = $DocType == 'CUI' ? $CustomInvoiceFreight : $CommercialFrieghtCharge;

        if ($IsDuty == 1 && $DestinationCountryId == 157) {
            $ClearanceChargeFee = (($InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges) * $ClearanceChargePerc) / 100; //#jme uma ClearanceCharge calculation included //01-04-2024
            $MerchandiseProcessFee = (($InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges) / $TotalDutyPerc) * $MerchandiseProcessFeePerc;
            // $MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
            if ($MerchandiseProcessFeePerc == 0) {
                $ISFStatic = false;
                $MerchandiseProcessFee = $ISFStaticDuty;
                $EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges;
                // $MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
            } else if ($ISFStaticDuty != 0 && $MerchandiseProcessFee > $ISFStaticDuty) {
                $ISFStatic = true;
                $MerchandiseProcessFee = $ISFStaticDuty;
                $TotalDutyPerc = $TotalDutyPerc - $MerchandiseProcessFeePerc;
                $EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges - $MerchandiseProcessFee;
            } else {
                $ISFStatic = false;
                $EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges;
                $MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
                // $MerchandiseProcessFee = $ISFStaticDuty;
            }

            $EffInvValue = $EffInvValue - $ClearanceChargeFee;

            $AntiDumpingCharges = round((($EffInvValue * $AntiDumpingDutyPerc) / $TotalDutyPerc), 2);
            $HarbourMaintenanceFee = round((($EffInvValue * $HarbourMaintenanceFeePerc) / $TotalDutyPerc), 2);
            $CountervailingDutyFee = round((($EffInvValue * $CountervailingDutyPerc) / $TotalDutyPerc), 2);
            $ReciprocalTariffsFee = 0;
            $TariffFee = 0;
            if ($ReciprocalTariffsPerc > 0) {
                $ReciprocalTariffsFee = round((($EffInvValue * $ReciprocalTariffsPerc) / $TotalDutyPerc), 2);
            }

            if ($TariffPerc > 0) {
                $TariffFee = round((($EffInvValue * $TariffPerc) / $TotalDutyPerc), 2);
            }

            if ($ISFStatic) {
                $DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee + $CountervailingDutyFee + $ReciprocalTariffsFee + $TariffFee;
            } else {
                $DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee + $CountervailingDutyFee + $ReciprocalTariffsFee + $TariffFee;
            }
            $AntidumpingandPortChargesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee;
        } else {
            $DutiesPrice = $DutiesPrice + $StandardDutyCharges;
        }

        $InvValueUSD = $InvoiceValueAfterDiscount;

        if ($ISFStatic) {
            $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $ISFStaticDuty - $InsuranceValue; //#jme uma ClearanceCharge calculation included //01-04-2024
            $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
            $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $ISFStaticDuty - $InsuranceValue - $ClearanceChargeFee; //#jme uma ClearanceCharge calculation included //01-04-2024
        } else {

            // $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge;
            // $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
            // $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice;

            // $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge-$InsuranceValue;
            $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $InsuranceValue;
            $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
            $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $InsuranceValue - $ClearanceChargeFee;
        }

        $HasCFLCharges = $ConfigDetails['CLFCharges'];

        if ($HasCFLCharges && $RowCInvData['DestinationCountry'] == '157') {
            $InvValueUSDWithOutDuties = $InvValueUSDWithOutDuties - $ClearingAndForwardingCharges;
            $FOBValueUSD -= $ClearingAndForwardingCharges;
            $AryPrices['ClearingAndForwardingCharges'] = $ClearingAndForwardingCharges;
        }

        // $InvValueINR = $InvValueUSD * $ExchangeRate;
        $InvValueINR = $InvoiceValue * $ExchangeRate;
        $FOBValueINR = $FOBValueUSD * $ExchangeRate;

        $IgstValue = ($InvValueINR * $IgstPerc) / 100;
        $TotalInvoiceValueINR = $IgstValue + $InvValueINR;

        $AryPrices['DiscountPerc'] = $Discount;
        $AryPrices['ExchangeRate'] = $ExchangeRate;
        $AryPrices['UnitCurrency'] = $UnitCurrency;

        //fetch Currency Symbol
        $SqlCurrency = "SELECT CurrenySymbol, CurrencyCode FROM `ScrmCurrencyMaster` WHERE CurrencyCode='$UnitCurrency'";
        $StmtCurrency = $this->conn->prepare($SqlCurrency);
        $StmtCurrency->execute();
        $RowCurrency = $StmtCurrency->fetch(PDO::FETCH_ASSOC);
        $AryPrices['CurrencySymbol'] = $RowCurrency['CurrenySymbol'];

        $AryPrices['DiscountAmount'] = $DiscountAmount;
        $AryPrices['InsuranceValue'] = $InsuranceValue;
        $AryPrices['DutiesPrice'] = $DutiesPrice;
        $AryPrices['FrieghtCharge'] = $FrieghtCharge;
        $AryPrices['CustomInvoiceFrieghtCharge'] = $CustomInvoiceFreight;
        $AryPrices['CalculatedFrieghtCharge'] = $FrieghtCharge;

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
        $AryPrices['AntiDumpingDutyPerc'] = $AntiDumpingDutyPerc;
        $AryPrices['MerchantDuty'] = $MerchandiseProcessFee;
        $AryPrices['HarborDuty'] = $HarbourMaintenanceFee;
        $AryPrices['AntidumpingandPortChargesPrice'] = $AntidumpingandPortChargesPrice;
        $AryPrices['MerchandiseProcessFeePerc'] = $MerchandiseProcessFeePerc;
        $AryPrices['HarbourMaintenanceFeePerc'] = $HarbourMaintenanceFeePerc;
        $AryPrices['DrawbackPercentage'] = $DrawbackPercentage;
        $AryPrices['DrawbackRateperKG'] = $DrawbackRateperKG;
        $AryPrices['ISFStatic'] = $ISFStatic;
        $AryPrices['CenttoUsd'] = $CenttoUsd;
        $AryPrices['TotalMcs'] = $TotalMcs;
        $AryPrices['CountervailingDuty'] = $CountervailingDutyFee;
        $AryPrices['CountervailingDutyPerc'] = $CountervailingDutyPerc;
        $AryPrices['ReciprocalTariffsFee'] = $ReciprocalTariffsFee;
        $AryPrices['ReciprocalTariffsPerc'] = $ReciprocalTariffsPerc;
        $AryPrices['TariffFee'] = $TariffFee;
        $AryPrices['TariffPerc'] = $TariffPerc;
        $AryPrices['DocType'] = $DocType;
        $AryPrices['NetWgtKgs'] = $AryInvBreakUpData['NetWgtKgs'];
        $AryPrices['NetWgtLbs'] = $AryInvBreakUpData['NetWgtLbs'];
        $AryPrices['SauceUsdValue'] = $SauceTotalUsdValue;
        $AryPrices['SauceLbsWeight'] = $TotalSauceLbsWeight;
        // $AryPrices['CanadaLbsFactor'] = $CanadaLbsFactor;
        $AryPrices['HasSauce'] = $AryInvBreakUpData['HasSauce'];
        // $AryPrices['Token'] = json_encode($Token);
        $AryPrices['Query'] = $AryInvBreakUpData['Query'];
        // $AryPrices['GradeData'] = $AryInvBreakUpData;
        return $AryPrices;
    }

    function getPaymentValuesByInvValue($PoID, $DocType, $InvValue)
    {
        $sqlDiscountType = "SELECT DiscountType FROM `AdminConfigDetails`";
        $stmtDiscountType = $this->conn->prepare($sqlDiscountType);
        $stmtDiscountType->execute();
        $dataRowDiscountType = $stmtDiscountType->fetch(PDO::FETCH_ASSOC);
        $DiscountType = $dataRowDiscountType['DiscountType'];

        $CenttoUsd = 100;
        if ($DocType == 'CMI') {
            $SqlCInvData = "SELECT *,ExchngRte as ExRate,SPD.ExporterID as ExpID FROM ScrmPoData as SPD
				LEFT JOIN ScrmCommercialData as SCD on SPD.PoID=SCD.PoID WHERE SPD.PoID=$PoID";
        } else {
            $SqlCInvData = "SELECT *,SPD.ExporterID as ExpID, SCDD.Freight, SCI.Freight AS CustomInvoiceFreight FROM ScrmPoData as SPD
				LEFT JOIN ScrmCommercialData as SCDD on SPD.PoID=SCDD.PoID LEFT JOIN ScrmCustomInvoices as SCI on
				SPD.PoID=SCI.PiID WHERE SPD.PoID=$PoID";
        }

        $StmtCInvData = $this->conn->prepare($SqlCInvData);
        $StmtCInvData->execute();
        $RowCInvData = $StmtCInvData->fetch(PDO::FETCH_ASSOC);

        $ExporterID = $RowCInvData['ExpID'];

        $CustomInvoiceFreight = $RowCInvData['CustomInvoiceFreight'];
        $CommercialFrieghtCharge = $RowCInvData['Freight'];

        if ($DocType == 'CMI') {
            $ArrayFreight = $this->checkFreight($PoID);
            $FrieghtCharge = $ArrayFreight['FrieghtCharge'];
            $ExchangeRate = $ArrayFreight['ExchangeRate'];
        } else {
            $FrieghtCharge = $RowCInvData['Freight'];
            $ExchangeRate = $RowCInvData['ExRate'];
        }

        $TermName = $RowCInvData['ShippingTerms'];
        $Discount = $RowCInvData['Discount'];

        $QtyUnit = $RowCInvData['QtyUnit'];
        $CInvCode = $RowCInvData['CInvCode'];

        $UnitCurrency = $RowCInvData['UnitCurrency'];
        $DestinationCountryId = $RowCInvData['DestinationCountry'];

        // $SqlGstPerc = "SELECT DISTINCT GstPerc,FDAEntryDate FROM `ScrmPoGradeData` as SPGD
        // left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
        // left join ScrmISFDetails as ISF on ISF.PIID=SPGD.InvId
        // left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";

        $SqlGstPerc = "SELECT DISTINCT (SELECT GstPerc FROM ScrmPreparationMasterGstPerc SPMG WHERE SPMG.ParentId=SPM.ID
			AND SPMG.`FromDate`<=SCI.`CInvDate` ORDER BY SPMG.ID DESC LIMIT 1) GstPerc,
			SPM.GstPerc GstPercP,FDAEntryDate,CInvDate,ISF.BLDate FROM `ScrmPoGradeData` as SPGD
			left join ScrmCustomInvoices as SCI on SCI.PiID=SPGD.InvId
			left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
			left join ScrmISFDetails as ISF on ISF.PIID=SPGD.InvId
			left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";

        // $StmtGstPerc = $iconn->query($SqlGstPerc);
        // $RowGstPerc = $StmtGstPerc->fetch_assoc();

        $StmtGstPerc = $this->conn->prepare($SqlGstPerc);
        $StmtGstPerc->execute();
        $RowGstPerc = $StmtGstPerc->fetch(PDO::FETCH_ASSOC);

        $FDAEntryDate = $RowGstPerc['FDAEntryDate'];
        $IgstPerc = !empty($RowGstPerc['GstPerc']) ? $RowGstPerc['GstPerc'] : $RowGstPerc['GstPercP'];
        $CInvDate = $RowGstPerc['CInvDate'];
        $BLDate = $RowGstPerc['BLDate'];

        // $CanadaLbsFactor = $ClientId == 'SMEV3' && $DestinationCountryId == 23 && $CInvDate >= '2026-03-01' ? 2.2046 : 'LbsFactor';

        // if ($CInvCode == '03061720') {
        //     $IgstPerc = 5;
        // } else {
        //     $IgstPerc = 12;
        // }

        $QtyUnit = $RowCInvData['QtyUnit'];

        if ($FDAEntryDate != '') {
            $Query = " and FromDate<='$FDAEntryDate'";
            $DocType = !isset($DocType) ? $DocType : 'CMI';
        } else {
            $Query = " and FromDate<='$CInvDate'";
            $DocType = !isset($DocType) ? $DocType : 'CUI';
        }

        $SqlShipTermsMaster = "SELECT * FROM `ScrmShipmentTermsMaster` where TermName='$TermName'";
        $StmtShipTermsMaster = $this->conn->prepare($SqlShipTermsMaster);
        $StmtShipTermsMaster->execute();
        $RowShipTermsMaster = $StmtShipTermsMaster->fetch(PDO::FETCH_ASSOC);

        // Update With ExportId Condition
        // $SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates`";
        $SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' $Query  ORDER BY ExpTRID DESC limit 1";

        $StmtExporterTaxRates = $this->conn->prepare($SqlExporterTaxRates);
        $StmtExporterTaxRates->execute();
        $RowExporterTaxRates = $StmtExporterTaxRates->fetch(PDO::FETCH_ASSOC);
        $AntiDumpingDutyPerc = $RowExporterTaxRates['AntiDumpingDuty'];
        $CountervailingDutyPerc = $RowExporterTaxRates['CountervailingDuty'];
        $MerchandiseProcessFeePerc = $RowExporterTaxRates['MerchandiseProcessFee'];
        $HarbourMaintenanceFeePerc = $RowExporterTaxRates['HarbourMaintenanceFee'];
        $ISFStaticDuty = $RowExporterTaxRates['ISFStaticDuty'];
        $InsurancePerc = $RowExporterTaxRates['InsurancePremiumRate'];
        $ClearanceChargePerc = $RowExporterTaxRates['ClearanceCharge'];
        $ReciprocalTariffsPerc = $RowExporterTaxRates['ReciprocalTariffs'];
        $ClearingAndForwardingCharges = $RowExporterTaxRates['ClearingCharges'];
        $TariffPerc = $RowExporterTaxRates['Tariff'];

        $SqlReciprocalTariffsExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' and FromDate<='$CInvDate' ORDER BY ExpTRID DESC limit 1";
        $StmtReciprocalTariffsExporterTaxRates = $this->conn->prepare($SqlReciprocalTariffsExporterTaxRates);
        $StmtReciprocalTariffsExporterTaxRates->execute();
        $RowReciprocalTariffsExporterTaxRates = $StmtReciprocalTariffsExporterTaxRates->fetch(PDO::FETCH_ASSOC);
        $ReciprocalTariffsPerc = $RowReciprocalTariffsExporterTaxRates['ReciprocalTariffs'];
        $TariffPerc = $RowReciprocalTariffsExporterTaxRates['Tariff'];

        $SqlRevPoTaxRates = "
			SELECT *
				FROM ScrmPoTaxRates
				WHERE PoID = '$PoID'
				AND InvoiceType = (
					CASE
						WHEN EXISTS (
							SELECT 1
							FROM ScrmPoTaxRates
							WHERE PoID = '$PoID'
							AND InvoiceType = 'CMI'
						)
						THEN 'CMI'
						ELSE 'CUI'
					END
				)
				ORDER BY ID DESC LIMIT 1
			";
        $StmtRevPoTaxRates = $this->conn->prepare($SqlRevPoTaxRates);
        $StmtRevPoTaxRates->execute();
        if ($StmtRevPoTaxRates && $StmtRevPoTaxRates->rowCount() > 0) {
            $RowRevPoTaxRates = $StmtRevPoTaxRates->fetch(PDO::FETCH_ASSOC);
            $AntiDumpingDutyPerc = $RowRevPoTaxRates['AntiDumpingDuty'];
            $AntiDumpingDutyPerc = $RowRevPoTaxRates['AntiDumpingDuty'];
            $CountervailingDutyPerc = $RowRevPoTaxRates['CountervailingDuty'];
            $MerchandiseProcessFeePerc = $RowRevPoTaxRates['MerchandiseProcessFee'];
            $HarbourMaintenanceFeePerc = $RowRevPoTaxRates['HarbourMaintenanceFee'];
            $ISFStaticDuty = $RowRevPoTaxRates['ISFStaticDuty'];
            $InsurancePerc = $RowRevPoTaxRates['InsurancePremiumRate'];
            $ReciprocalTariffsPerc = $RowRevPoTaxRates['ReciprocalTariff'];
            $ClearingAndForwardingCharges = $RowRevPoTaxRates['ClearingCharges'];
            $TariffPerc = $RowRevPoTaxRates['Tariff'];
        }

        $TotalDutyPerc = 100 + $AntiDumpingDutyPerc +
            $MerchandiseProcessFeePerc +
            $HarbourMaintenanceFeePerc + $CountervailingDutyPerc +
            $ReciprocalTariffsPerc + $TariffPerc;

        $IsFreight = $RowShipTermsMaster['Freight'];
        $IsDuty = $RowShipTermsMaster['Duty'];
        $Insurance = $RowShipTermsMaster['Insurance'];
        $AntiDumpingDFactor = $RowShipTermsMaster['AntiDumpingDFactor'];
        $AntiDumpingMFactor = $RowShipTermsMaster['AntiDumpingMFactor'];
        $StandardDutyCharges = $RowShipTermsMaster['StandardDutyCharges'];

        // $ConfigDetails['CanadaLbsFactor'] = $CanadaLbsFactor;
        $ConfigDetails['DestCountryID'] = $DestinationCountryId;
        // $ConfigDetails['ClientId'] = $ClientId;
        // $AryInvBreakUpData = $this->getGradeBreakUpData($PoID, $ConfigDetails);

        // if ($RowCInvData['CInvDate'] >= '2025-10-14') {
        //     $IsSauce = $this->checkSauceExists($PoID);
        //     $SauceTotalUsdValue = 0;
        //     $SauceTotalKgs = 0;
        //     $SauceTotalLbs = 0;
        //     if ($IsSauce && $IsSauce == 1) {
        //         $SqlSauce = "SELECT PGD.Mcs, PGD.VarietyID, PGD.CUPS, PGD.CupsPrice, PS.*, SPD.QtyUnit
        // 			FROM ScrmPoGradeData as PGD
        // 			LEFT JOIN ScrmPoData as SPD ON SPD.PoID = PGD.InvId
        // 			LEFT JOIN ScrmPIVarietyList as SPVL ON SPVL.VarietyID = PGD.VarietyID
        // 			LEFT JOIN AdminPackingStyles as PS ON SPVL.SaucePackingStyle = PS.PCKID
        // 			WHERE InvId='$PoID' AND SPVL.HasSauce = 'Yes'";
        //         $StmtSauce = $this->conn->prepare($SqlSauce);
        //         while ($RowSauce = $StmtSauce->fetch(PDO::FETCH_ASSOC)) {
        //             $SauceLbsWeight = $RowSauce['TotalSlabs'] * $RowSauce['SlabWeight'] * $RowSauce['Mcs'];
        //             if ($RowSauce['Uom'] == 'OZ') {
        //                 $OzToLbs = $RowSauce['SlabWeight'] * $RowSauce['LbsFactor'];
        //                 $SauceLbsWeight *= $OzToLbs / $RowSauce['SlabWeight'];
        //             }
        //             $SauceKgsWeight = $SauceLbsWeight * 0.454;
        //             if ($RowSauce['QtyUnit'] == 'LBS' && $PType == 'KGS') {
        //                 $QtyCalc = $SauceKgsWeight;
        //                 $RowSauce['CupsPrice'] = $RowSauce['CupsPrice'] / 0.454;
        //             } else {
        //                 $QtyCalc = $SauceLbsWeight;
        //             }
        //             $SauceTotalUsdValue += $QtyCalc * $RowSauce['CupsPrice'];
        //             $SauceTotalKgs += $SauceKgsWeight;
        //             $SauceTotalLbs += $SauceLbsWeight;
        //         }
        //     }
        // }


        // $TotalMcs = $AryInvBreakUpData['MCS'];

        // if ($QtyUnit == 'KGS') {
        // 	$Weight = $AryInvBreakUpData['NetWgtKgs'] + $SauceKgsWeight;
        // 	$InvoiceValue = $AryInvBreakUpData['PriceKgs'];
        // } else {
        // 	$Weight = $AryInvBreakUpData['NetWgtLbs'];
        // 	$InvoiceValue = $AryInvBreakUpData['PriceLbs'];
        // }

        $InvoiceValue = $InvValue;

        $AryPrices['ShrimpValueUsd'] = $InvoiceValue;
        $AryPrices['SauceValueUsd'] = $SauceTotalUsdValue;

        if ($RowCInvData['CInvDate'] >= '2025-10-14') {
            $InvoiceValue += $SauceTotalUsdValue;
        }

        if ($Discount > 0) {
            if ($DiscountType == 0) {
                $DiscountAmount = ($InvoiceValue * $Discount) / 100;
                $InvoiceValueAfterDiscount = $InvoiceValue;
                $InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount; // By Rantagiri 04/17/2023 5.44 
            } else {
                $DiscountAmount = $Discount;
                $InvoiceValueAfterDiscount = $InvoiceValue;
                $InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount;
            }
        } else {
            $InvoiceValueAfterDiscount = $InvoiceValue;
        }

        if ($Insurance == 1) {
            // $InsuranceValue = ($PriceAfterDiscount * $InsurancePerc) / 100;
            $InsuranceValue = ($InvoiceValueAfterDiscount * $InsurancePerc) / 100;
        } else {
            $InsuranceValue = 0;
        }

        $FrieghtCharge = empty($CommercialFrieghtCharge) ? $CustomInvoiceFreight : $CommercialFrieghtCharge;

        if ($IsDuty == 1) {
            $ClearanceChargeFee = (($InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges) * $ClearanceChargePerc) / 100; //#jme uma ClearanceCharge calculation included //01-04-2024
            $MerchandiseProcessFee = (($InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges) / $TotalDutyPerc) * $MerchandiseProcessFeePerc;
            // $MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
            if ($MerchandiseProcessFeePerc == 0) {
                $ISFStatic = false;
                $MerchandiseProcessFee = $ISFStaticDuty;
                $EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges;
                // $MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
            } else if ($ISFStaticDuty != 0 && $MerchandiseProcessFee > $ISFStaticDuty) {
                $ISFStatic = true;
                $MerchandiseProcessFee = $ISFStaticDuty;
                $TotalDutyPerc = $TotalDutyPerc - $MerchandiseProcessFeePerc;
                $EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges - $MerchandiseProcessFee;
            } else {
                $ISFStatic = false;
                $EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges;
                $MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
                // $MerchandiseProcessFee = $ISFStaticDuty;
            }

            $EffInvValue = $EffInvValue - $ClearanceChargeFee;

            $AntiDumpingCharges = round((($EffInvValue * $AntiDumpingDutyPerc) / $TotalDutyPerc), 2);
            $HarbourMaintenanceFee = round((($EffInvValue * $HarbourMaintenanceFeePerc) / $TotalDutyPerc), 2);
            $CountervailingDutyFee = round((($EffInvValue * $CountervailingDutyPerc) / $TotalDutyPerc), 2);
            $ReciprocalTariffsFee = 0;
            $TariffFee = 0;
            if ($ReciprocalTariffsPerc > 0) {
                $ReciprocalTariffsFee = round((($EffInvValue * $ReciprocalTariffsPerc) / $TotalDutyPerc), 2);
            }

            if ($TariffPerc > 0) {
                $TariffFee = round((($EffInvValue * $TariffPerc) / $TotalDutyPerc), 2);
            }

            if ($ISFStatic) {
                $DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee + $CountervailingDutyFee + $ReciprocalTariffsFee + $TariffFee;
            } else {
                $DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee + $CountervailingDutyFee + $ReciprocalTariffsFee + $TariffFee;
            }
        } else {
            $DutiesPrice = $DutiesPrice + $StandardDutyCharges;
        }

        $InvValueUSD = $InvoiceValueAfterDiscount;

        if ($ISFStatic) {
            $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $ISFStaticDuty - $InsuranceValue; //#jme uma ClearanceCharge calculation included //01-04-2024
            $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
            $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $ISFStaticDuty - $InsuranceValue - $ClearanceChargeFee; //#jme uma ClearanceCharge calculation included //01-04-2024
        } else {

            // $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge;
            // $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
            // $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice;

            // $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge-$InsuranceValue;
            $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $InsuranceValue;
            $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
            $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $InsuranceValue - $ClearanceChargeFee;
        }

        $HasCFLCharges = $rowInvClientDetails['CLFCharges'];

        if ($HasCFLCharges && $RowCInvData['DestinationCountry'] == '157') {
            $InvValueUSDWithOutDuties = $InvValueUSDWithOutDuties - $ClearingAndForwardingCharges;
            $FOBValueUSD -= $ClearingAndForwardingCharges;
            $AryPrices['ClearingAndForwardingCharges'] = $ClearingAndForwardingCharges;
        }

        $InvValueINR = $InvValueUSD * $ExchangeRate;
        $FOBValueINR = $FOBValueUSD * $ExchangeRate;
        $IgstValue = ($InvValueINR * $IgstPerc) / 100;

        $AryPrices['DiscountPerc'] = $Discount;
        $AryPrices['ExchangeRate'] = $ExchangeRate;
        $AryPrices['UnitCurrency'] = $UnitCurrency;

        //fetch Currency Symbol
        $SqlCurrency = "SELECT CurrenySymbol, CurrencyCode FROM `ScrmCurrencyMaster` WHERE CurrencyCode='$UnitCurrency'";
        $StmtCurrency = $this->conn->prepare($SqlCurrency);
        $StmtCurrency->execute();
        $RowCurrency = $StmtCurrency->fetch(PDO::FETCH_ASSOC);
        $AryPrices['CurrencySymbol'] = $RowCurrency['CurrenySymbol'];

        $AryPrices['DiscountAmount'] = $DiscountAmount;
        $AryPrices['InsuranceValue'] = $InsuranceValue;
        $AryPrices['DutiesPrice'] = $DutiesPrice;
        $AryPrices['FrieghtCharge'] = $CommercialFrieghtCharge;
        $AryPrices['CustomInvoiceFrieghtCharge'] = $CustomInvoiceFreight;

        $AryPrices['InvValueUSD'] = $InvValueUSD;
        $AryPrices['InvValueUSDWithOutDuties'] = $InvValueUSDWithOutDuties;
        $AryPrices['InvValueINR'] = $InvValueINR;
        $AryPrices['FOBValueUSD'] = $FOBValueUSD;
        $AryPrices['FOBValueINR'] = $FOBValueINR;
        $AryPrices['PaymentTerm'] = $TermName;
        $AryPrices['IgstPerc'] = $IgstPerc;
        $AryPrices['IgstValue'] = $IgstValue;
        $AryPrices['AntiDumpingDuty'] = $AntiDumpingCharges;
        $AryPrices['AntiDumpingDutyPerc'] = $AntiDumpingDutyPerc;
        $AryPrices['MerchantDuty'] = $MerchandiseProcessFee;
        $AryPrices['HarborDuty'] = $HarbourMaintenanceFee;
        $AryPrices['ISFStatic'] = $ISFStatic;
        $AryPrices['CenttoUsd'] = $CenttoUsd;
        $AryPrices['TotalMcs'] = $TotalMcs;
        $AryPrices['CountervailingDuty'] = $CountervailingDutyFee;
        $AryPrices['CountervailingDutyPerc'] = $CountervailingDutyPerc;
        $AryPrices['ReciprocalTariffsFee'] = $ReciprocalTariffsFee;
        $AryPrices['ReciprocalTariffsPerc'] = $ReciprocalTariffsPerc;
        $AryPrices['TariffFee'] = $TariffFee;
        $AryPrices['TariffPerc'] = $TariffPerc;
        $AryPrices['NetWgtKgs'] = $AryInvBreakUpData['NetWgtKgs'];
        $AryPrices['NetWgtLbs'] = $AryInvBreakUpData['NetWgtLbs'];
        // $AryPrices['CanadaLbsFactor'] = $CanadaLbsFactor;
        $AryPrices['HasSauce'] = $AryInvBreakUpData['HasSauce'];
        // $AryPrices['Token'] = json_encode($Token);
        // $AryPrices['Query'] = $AryInvBreakUpData['Query'];
        // $AryPrices['GradeData'] = json_encode($AryInvBreakUpData);
        return $AryPrices;
    }

    function getGradeBreakUpData($GradeID, $PiID, $ConfigDetails)
    {
        $ClientId = $ConfigDetails['ClientId'];
        $gradeTable = 'ScrmPoGradeData';
        $poTable = $gradeTable == 'ScrmPoGradeData' ? 'ScrmPoData' : 'ScrmPIData';
        $DestCountryID = $ConfigDetails['DestCountryID'];
        $CanadaLbsFactor = $ConfigDetails['CanadaLbsFactor'];
        $InvId = $PiID;

        if ($PiID == '') {
            $Qry = "WHERE ID in ($GradeID)";
            $PriceQry = 'Price';
        } else {
            $Qry = "WHERE InvId='$PiID'";
            $PriceQry = 'sum(Price) as Price';
        }

        $sqlExt = "sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
		sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs,";

        if ($ClientId == 'SMEV3' && $DestCountryID == '23') {
            $sqlExt .= "CASE WHEN PS.Uom = 'GRAMS' THEN
			sum(Mcs * TotalSlabs * SlabWeight * Mfactor * $CanadaLbsFactor)
			ELSE sum(Mcs * TotalSlabs * SlabWeight * LbsFactor)
			END AS NetWgtLbs,
			CASE WHEN PS.Uom = 'OZ' THEN
			sum(Mcs * TotalSlabs * SlabWeight / (1000 * $CanadaLbsFactor))
			WHEN PS.Uom = 'LBS' THEN
			sum(Mcs * TotalSlabs * SlabWeight * LbsFactor / $CanadaLbsFactor)
			ELSE sum(Mcs * TotalSlabs * SlabWeight * MFactor)
			END AS NetWgtKgs";

            $PriceExt = "sum(CASE WHEN PS.Uom = 'GRAMS' THEN
			Mcs * TotalSlabs * SlabWeight * Mfactor * $CanadaLbsFactor * Price
			ELSE Mcs * TotalSlabs * SlabWeight * LbsFactor * Price END) AS PriceLbs,
			sum(CASE WHEN PS.Uom = 'OZ' THEN
			Mcs * TotalSlabs * SlabWeight / (1000 * $CanadaLbsFactor) * Price
			WHEN PS.Uom = 'LBS' THEN
			(Mcs * TotalSlabs * SlabWeight * LbsFactor / $CanadaLbsFactor) * price
			ELSE Mcs * TotalSlabs * SlabWeight * MFactor * Price END) AS PriceKgs";
        } else {
            // $sqlExt .= "sum(Mcs * TotalSlabs * SlabWeight * MFactor) as NetWgtKgs,
            // 	sum(Mcs * TotalSlabs * SlabWeight * LbsFactor) as NetWgtLbs";

            $sqlExt .= "sum(CASE WHEN PS.Uom = 'OZ' THEN
			Mcs * TotalSlabs * SlabWeight * LbsFactor * 0.454
			ELSE Mcs * TotalSlabs * SlabWeight * MFactor END) as NetWgtKgs,
			sum(Mcs * TotalSlabs * SlabWeight * LbsFactor) as NetWgtLbs";

            $PriceExt = "sum(CASE WHEN PS.Uom = 'OZ' AND SPVL.HasSauce = 'Yes' THEN (Mcs * TotalSlabs * SlabWeight * Price * Mfactor)
			WHEN PS.Uom = 'LBS' THEN (Mcs * TotalSlabs * SlabWeight * LbsFactor * Price)
			WHEN PS.Uom = 'OZ' THEN (Mcs * TotalSlabs * SlabWeight * LbsFactor * 0.454 * Price)
			ELSE (Mcs * TotalSlabs * SlabWeight * Mfactor * Price) END) as PriceKgs,
			sum(CASE WHEN PS.Uom = 'OZ' AND SPVL.HasSauce = 'Yes' THEN (Mcs * TotalSlabs * SlabWeight * Price * LbsFactor)
			WHEN PS.Uom = 'KGS' THEN (Mcs * TotalSlabs * SlabWeight * MFactor * Price)
			WHEN PS.Uom = 'GRAMS' THEN (Mcs * TotalSlabs * SlabWeight * MFactor * 0.454 * Price)
			ELSE (Mcs * TotalSlabs * SlabWeight * LbsFactor * Price) END) as PriceLbs";
        }

        $PLQry = 'CASE WHEN SPVL.HasSauce = "Yes" THEN SPVL.ShrimpPackingStyle ELSE SPVL.PackingStyle END';
        $SPLQry = 'MAX(CASE WHEN SPVL.HasSauce = "Yes" THEN SPVL.SaucePackingStyle ELSE NULL END ) AS SaucePackingStyle';
        $HasSauceQry = ' CASE WHEN MAX(CASE WHEN SPVL.HasSauce = "Yes" THEN 1 ELSE 0 END) = 1 THEN "Yes" ELSE "No" END AS HasSauce';

        $SqlTotal = "SELECT $SPLQry,$HasSauceQry, sum(Mcs) as MCS,TotalSlabs,InvId, $sqlExt, $PriceQry, $PriceExt,
		CASE WHEN SPVL.HasSauce = 'Yes' THEN sum(Mcs * TotalSlabs * Price)
		ELSE 0 END as PriceSauce,
		sum(TraysQuantity) as TraysQuantity
		FROM $gradeTable as PGD
        left join ScrmPIVarietyList as SPVL
		on SPVL.VarietyID=PGD.VarietyID
		left join AdminPackingStyles as PS
		on PS.PCKID = $PLQry
		$Qry";
        // echo $SqlTotal;exit;
        $StmtTotal = $this->conn->prepare($SqlTotal);
        $StmtTotal->execute();
        $RowTotal = $StmtTotal->fetch(PDO::FETCH_ASSOC);
        $InvId = $RowTotal['InvId'];
        $WtPerCtnTxt = '';
        if ($PiID != '') {
            $SqlWtPerCtn = "SELECT
			round((TotalSlabs*SlabWeight*LbsFactor),2) as NetWtLbs,
			round(GrossWt/Sum(Mcs),2) as GrossWt FROM $gradeTable as SPGD
			left JOIN AdminPackingStyles as APS on APS.PCKID=SPGD.PackingStyleID
			WHERE InvId='$InvId' GROUP by PackingStyleID";
            $StmtWtPerCtn = $this->conn->prepare($SqlWtPerCtn);
            $StmtWtPerCtn->execute();
            $RowWtPerCtn = $StmtWtPerCtn->fetch(PDO::FETCH_ASSOC);
            $NetWtPerCtn = $RowWtPerCtn['NetWtLbs'];
            $GrossWtPerCtn = $RowWtPerCtn['GrossWt'];
            $WtPerCtnTxt = 'EACH M/CTNS. CONSISTS OF NET WT. ' . $NetWtPerCtn . ' LBS AND GROSS WT. ' . $GrossWtPerCtn . ' LBS.';
        }

        $SqlQtyUnit = "SELECT QtyUnit from $poTable WHERE PoID='$InvId'";
        $StmtQtyUnit = $this->conn->prepare($SqlQtyUnit);
        $StmtQtyUnit->execute();
        $RowQtyUnit = $StmtQtyUnit->fetch(PDO::FETCH_ASSOC);
        $QtyUnit = $RowQtyUnit['QtyUnit'];

        switch ($QtyUnit) {
            case 'BAGS':
            case 'TRAYS':
            case 'RINGS':
            case 'UNITS':
                $Qty = $RowTotal['MCS'] * $RowTotal['TotalSlabs'];
                $UsdValue = $RowTotal['PriceSauce'];
                break;
            case 'KGS':
                $Qty = $RowTotal['NetWgtKgs'];
                $UsdValue = $RowTotal['PriceKgs'];
                break;
            default: // LBS or any other value
                $Qty = $RowTotal['NetWgtLbs'];
                $UsdValue = $RowTotal['PriceLbs'];
                break;
        }

        $AryPriceData['Price'] = $RowTotal['Price'];
        $AryPriceData['TotalMcs'] = $RowTotal['MCS'];
        $AryPriceData['Mcs'] = $RowTotal['MCS'];
        $AryPriceData['SaucePackingStyle'] = $RowTotal['SaucePackingStyle'];
        $AryPriceData['Qty'] = $Qty;
        $AryPriceData['UsdValue'] = $UsdValue;

        // if ($DestCountryID == '23') {
        // 	$AryPriceData['UsdValue'] = $Qty * $RowTotal['Price'];
        // }

        $AryPriceData['NetWgtKgs'] = $RowTotal['NetWgtKgs'];
        $AryPriceData['NetWgtLbs'] = $RowTotal['NetWgtLbs'];
        $AryPriceData['GrossWeightKgs'] = $RowTotal['GrossWeightKgs'];
        $AryPriceData['GrossWeightLbs'] = $RowTotal['GrossWeightLbs'];
        $AryPriceData['WtPerCtnTxt'] = $WtPerCtnTxt;
        $AryPriceData['HasSauce'] = $RowTotal['HasSauce'];
        // $AryPriceData['VarietyId'] = $RowTotal['HasSauce'];
        $AryPriceData['Query'] = $SqlTotal;
        // $AryPriceData['BreakUpPaymentValues'] = $this->getPaymentValuesByInvValue($InvId, 'CI', $UsdValue);

        return $AryPriceData;
    }

    public function getProformaDiscountAmount($PoID)
    {

        $LbsFactor = 0.454;

        $sqlDiscountType = "SELECT DiscountType FROM `AdminConfigDetails`";
        $stmtDiscountType = $this->conn->prepare($sqlDiscountType);
        $stmtDiscountType->execute();
        $dataRowDiscountType = $stmtDiscountType->fetch(PDO::FETCH_ASSOC);
        $DiscountType = $dataRowDiscountType['DiscountType'];

        $sqlQuery = "SELECT ChargeAmount,QtyUnit FROM `ScrmPIData` WHERE PoID='$PoID'  ";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();
        $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);
        $Discount = $dataRow['ChargeAmount'];
        $QtyUnit = $dataRow['QtyUnit'];

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

        if ($QtyUnit == 'KGS') {

            $InvoiceValue = $dataRowTotal['PriceKgs'];
        } else {

            $InvoiceValue = $dataRowTotal['PriceLbs'];
        }

        if ($Discount > 0) {
            if ($DiscountType == 0) {

                $DiscountAmount = ($InvoiceValue * $Discount) / 100;
            } else {
                $DiscountAmount = $Discount;
            }
        }


        return $DiscountAmount;
    }
    public function checkFreight($PoID)
    {

        $sqlQuery = "SELECT SCI.Freight as CustomFreight,SCI.ExRate as CustomExrate,SCD.Freight as ComFreight,
        SCD.ExchngRte as ComExrate FROM `ScrmCustomInvoices` as SCI left join ScrmCommercialData as SCD on SCD.PoID=SCI.PiID 
        WHERE PiID=$PoID;
        ";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();
        $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);





        if ($dataRow['ComFreight'] != 0) {
            $FrieghtCharge = $dataRow['ComFreight'];
            $ExchangeRate = $dataRow['ComExrate'];
        } else {
            $FrieghtCharge = $dataRow['CustomFreight'];
            $ExchangeRate = $dataRow['CustomExrate'];
        }

        $ArrayFreight['FrieghtCharge'] = $FrieghtCharge ? $FrieghtCharge : 0;
        $ArrayFreight['ExchangeRate'] = $ExchangeRate ? $ExchangeRate : 0;
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

    function getFullPiInvoiceNumbertoSave($ExpID, $InvDate, $InvNo)
    {
        $ExporterData = $this->getExporterDetails($ExpID, '', '');
        //print_r($ExporterData);
        $InvShortCode = $ExporterData['InvShortCode'];
        $InvDateFormat = $ExporterData['InvDateFormat'];

        $CurrentMonth = date('n', strtotime($InvDate));
        if ($InvDateFormat == 'Short') {
            if ($CurrentMonth <= 3) {
                $NxtYear = date("y", strtotime($InvDate));
                $CurrentYear = date("Y", strtotime($InvDate)) - 1;
            } else {
                $CurrentYear = date("Y", strtotime($InvDate));
                $NxtYear = date("y", strtotime($InvDate)) + 1;
            }
        } else {

            if ($CurrentMonth <= 3) {
                $NxtYear = date('y', strtotime($InvDate));
                $CurrentYear = date('Y', strtotime($InvDate)) - 1;
            } else {
                $CurrentYear = date('Y', strtotime($InvDate));
                $NxtYear = date('y', strtotime($InvDate)) + 1;
            }
        }
        $InvNoWithPadding = sprintf('%03d', $InvNo);
        $InvNumFull = $InvShortCode . '/PI/' . $InvNoWithPadding . '/' . $CurrentYear . '-' . $NxtYear;
        return $InvNumFull;
    }

    function checkSauceExists($PoID)
    {
        $SqlQuery = "SELECT COUNT(*) as SauceCount FROM ScrmPIVarietyList WHERE VarietyID IN 
        (Select VarietyID FROM ScrmPoGradeData WHERE InvID = $PoID) AND HasSauce = 'Yes'";
        $Stmt = $this->conn->prepare($SqlQuery);
        $Stmt->execute();
        $Row = $Stmt->fetch(PDO::FETCH_ASSOC);
        return $Row['SauceCount'] > 0;
    }


    public function getShrimpData($gradeData)
    {
        $QtyToDisplay = $gradeData['QtyToDisplay'];
        $sqlFields = "`TotalSlabs`,`SlabWeight`,`Uom`,`PackingStyle`,`LbsFactor`,`MFactor`";

        $sqlQuery = "SELECT $sqlFields  FROM `AdminPackingStyles` where PCKID='$gradeData[PackingStyle]'";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();

        if ($stmtExec) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

            $ItemData['PackingStyle'] = $dataRow['PackingStyle'];
            $ItemData['MFactor'] = $dataRow['MFactor'];
            $ItemData['LbsFactor'] = $dataRow['LbsFactor'];
            $ItemData['TotalSlabs'] = $dataRow['TotalSlabs'];
            $ItemData['SlabWeight'] = $dataRow['SlabWeight'];
            $ItemData['Uom'] = $dataRow['Uom'];

            $ShrimpLbsWeight = $dataRow['TotalSlabs'] * $dataRow['SlabWeight'] * $gradeData['Mcs'];
            if ($dataRow['Uom'] == 'OZ') {
                $OzToLbs = $dataRow['SlabWeight'] * $dataRow['LbsFactor'];
                $ShrimpLbsWeight *= $OzToLbs / $dataRow['SlabWeight'];
            }
            if ($QtyToDisplay !== '') {
                switch ($QtyToDisplay) {
                    case $dataRow['Uom'] == 'KGS':
                        $ShrimpKgsWeight = $ShrimpLbsWeight * $dataRow['MFactor'];
                        break;
                    default:
                        $ShrimpKgsWeight = $ShrimpLbsWeight * 0.454;
                }
            }

            $ItemData['ShrimpQty'] = $ShrimpKgsWeight;
            $ItemData['ShrimpUsdValue'] = $ShrimpLbsWeight * $gradeData['Price'];
            return $ItemData;
        }
    }

    public function getSauceData($gradeData)
    {
        $sqlFields = "`TotalSlabs`,`SlabWeight`,`Uom`,`PackingStyle`,`LbsFactor`";

        $sqlQuery = "SELECT $sqlFields  FROM `AdminPackingStyles` where PCKID='$gradeData[SaucePackingStyle]'";
        $stmtExec = $this->conn->prepare($sqlQuery);
        $stmtExec->execute();

        if ($stmtExec) {
            $dataRow = $stmtExec->fetch(PDO::FETCH_ASSOC);

            $ItemData['PackingStyle'] = $dataRow['PackingStyle'];
            $ItemData['LbsFactor'] = $dataRow['LbsFactor'];
            $ItemData['TotalSlabs'] = $dataRow['TotalSlabs'];
            $ItemData['SlabWeight'] = $dataRow['SlabWeight'];
            $ItemData['Uom'] = $dataRow['Uom'];

            $SauceLbsWeight = $dataRow['TotalSlabs'] * $dataRow['SlabWeight'] * $gradeData['Mcs'];
            if ($dataRow['Uom'] == 'OZ') {
                $OzToLbs = $dataRow['SlabWeight'] * $dataRow['LbsFactor'];
                $SauceLbsWeight *= $OzToLbs / $dataRow['SlabWeight'];
            }
            $SauceKgsWeight = $SauceLbsWeight * 0.454;
            $ItemData['SauceQty'] = $SauceKgsWeight;
            $ItemData['SauceUsdValue'] = $SauceLbsWeight * $gradeData['CupsPrice'];
            return $ItemData;
        }
    }

    function getShrimpPaymentValues($PoID, $DocType)
    {
        /*
    $LbsFactor=0.454; // To Convert Lbs into Kgs multiply value
    $Discount=0; // Get from Custom Invoice
    $InsurancePerc=0.11; // Varible
    $AntiDumpingDFactor=101.35 // Anti Dumping Duty Factor(Formula)
    $AntiDumpingMFactor=1.35 // Anti Dumping Duty Factor(Formula)
    $StandardDutyCharges=125; // Standard Value
    $TermName=125; // Standard Value
    $CenttoUsd=100; // Standard Value 1 usd=100 cents

    */

        // echo $PoID; exit;
        $DiscountType = $AdminConfigDetails['DiscountType'];

        // $SqlCInvDate = "SELECT CInvDate FROM `ScrmCustomInvoices` WHERE PiID=$PoID";
        // $StmtCInvDate = $iconn->query($SqlCInvDate);
        // $RowCInvDate = $StmtCInvDate->fetch_assoc();
        // $CInvDate = $RowCInvDate['CInvDate'];

        $CenttoUsd = 100;
        if ($DocType == 'CMI') {
            $SqlCInvData = "SELECT *,ExchngRte as ExRate,SPD.ExporterID as ExpID FROM ScrmPoData as SPD
			LEFT JOIN ScrmCommercialData as SCD on
			SPD.PoID=SCD.PoID WHERE SPD.PoID=$PoID";
        } else {
            $SqlCInvData = "SELECT *,SPD.ExporterID as ExpID FROM ScrmPoData as SPD
			LEFT JOIN ScrmCustomInvoices as SCI on
			SPD.PoID=SCI.PiID WHERE SPD.PoID=$PoID";
        }
        // echo $SqlCInvData;


        $StmtCInvData = $this->conn->query($SqlCInvData);
        $RowCInvData = $StmtCInvData->fetch(PDO::FETCH_ASSOC);
        $TermName = $RowCInvData['ShippingTerms'];
        $Discount = $RowCInvData['Discount'];
        $FrieghtCharge = $RowCInvData['Freight'];
        $ExchangeRate = $RowCInvData['ExRate'];
        $QtyUnit = $RowCInvData['QtyUnit'];
        $CInvCode = $RowCInvData['CInvCode'];
        $ExporterID = $RowCInvData['ExpID'];

        /**uma(14-10-2023) */
        // $SqlGstPerc = "SELECT DISTINCT GstPerc FROM `ScrmPoGradeData` as SPGD
        // 	left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
        // 	left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";

        $SqlGstPerc = "SELECT DISTINCT (SELECT GstPerc FROM ScrmPreparationMasterGstPerc SPMG WHERE SPMG.ParentId=SPM.ID AND SPMG.`FromDate`<=SCI.`CInvDate` ORDER BY SPMG.ID DESC LIMIT 1)
		GstPerc,
		SPM.GstPerc GstPercP,FDAEntryDate,CInvDate,TypeProduct,ISF.BLDate FROM `ScrmPoGradeData` as SPGD
		left join ScrmCustomInvoices as SCI on SCI.PiID=SPGD.InvId
		left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID
		left join ScrmISFDetails as ISF on ISF.PIID=SPGD.InvId
		 left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";

        $StmtGstPerc = $this->conn->query($SqlGstPerc);
        $RowGstPerc = $StmtGstPerc->fetch(PDO::FETCH_ASSOC);
        $StmtRawCookedGstPerc = $this->conn->query($SqlGstPerc); #jme

        //while($RowHsCode=$StmtHsCode->fetch_assoc()){
        // $ArrayHsCode[]=$RowHsCode['HSCode'];
        // $CInvCode = implode(',', $ArrayHsCode);

        //}

        // echo $RowGstPerc;

        // { "GstPerc": "5", "FDAEntryDate": null, "CInvDate": "2024-07-12", "TypeProduct": "RAW" } Harinath
        $IgstPerc = !empty($RowGstPerc['GstPerc']) ? $RowGstPerc['GstPerc'] : $RowGstPerc['GstPercP'];
        $FDAEntryDate = $RowGstPerc['FDAEntryDate'];
        $CInvDate = $RowGstPerc['CInvDate'];
        $BLDate = $RowGstPerc['BLDate'];

        // if($CInvCode=='03061720'){
        // $IgstPerc=5;
        // }else{
        // $IgstPerc=12;
        // }

        /**uma (14-10-2023)*/
        $QtyUnit = $RowCInvData['QtyUnit'];
        $Query = " and FromDate<='$CInvDate'";
        if ($DocType == 'CMI') {
            if ($FDAEntryDate != '') {
                $Query = " and FromDate<='$FDAEntryDate'";
            }
        }
        // else{
        // $Query=" and FromDate<='$CInvDate'";
        // }


        $SqlShipTermsMaster = "SELECT * FROM `ScrmShipmentTermsMaster` where TermName='$TermName'";
        $StmtShipTermsMaster = $this->conn->query($SqlShipTermsMaster);
        $RowShipTermsMaster = $StmtShipTermsMaster->fetch(PDO::FETCH_ASSOC);

        // SELECT * FROM `ScrmShipmentTermsMaster` where TermName='CIF'
        // { "ID": "4", "TermName": "CIF", "Cost": "0",
        // "Insurance": "1", "Freight": "1",
        // "Duty": "1", "AntiDumpingDFactor": "101.35",
        // "AntiDumpingMFactor": "1.35",
        // "StandardDutyCharges": "0.00",
        // "Status": "1" } Harinath


        // Update With ExportId Condition
        //$SqlExporterTaxRates="SELECT * FROM `ScrmExporterTaxRates`";
        // $SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where FromDate<='$CInvDate'  ORDER BY ExpTRID DESC limit 1";
        $SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' $Query  ORDER BY ExpTRID DESC limit 1";
        $StmtExporterTaxRates = $this->conn->query($SqlExporterTaxRates);
        $RowExporterTaxRates = $StmtExporterTaxRates->fetch(PDO::FETCH_ASSOC);


        // SELECT * FROM `ScrmExporterTaxRates` where ExpID='1' and FromDate<='2024-07-12' ORDER BY ExpTRID DESC limit 1;
        // { "ExpTRID": "3", "ExpID": "1", "CountryID": "", "ClearanceCharge": "1", "AntiDumpingDuty": "1.35",
        // "MerchandiseProcessFee": "0", "HarbourMaintenanceFee": "0", "CountervailingDuty": "4.36",
        // "ServiceTaxPercentage": "0", "DrawbackRateperKG": "23.6", "DrawbackPercentage": "0",
        // "BLServiceTax": "0", "ISFStaticDuty": "0", "InsurancePremiumRate": "0.11", "UsClearance": "",
        // "IGSTRaw": "", "IGSTBrainFrozen": "", "IGSTCooked": "", "IGSTCookedInShell": "", "IGSTBlanched": "",
        // "FromDate": "2024-04-01", "UptoDate": "", "PostedDate": "2024-08-06 16:47:55"
        // } Harinath


        $AntiDumpingDutyPerc = $RowExporterTaxRates['AntiDumpingDuty'];
        $CountervailingDutyPerc = $RowExporterTaxRates['CountervailingDuty']; //uma cvd calculation included //18-03-2024
        $MerchandiseProcessFeePerc = $RowExporterTaxRates['MerchandiseProcessFee'];
        $HarbourMaintenanceFeePerc = $RowExporterTaxRates['HarbourMaintenanceFee'];
        $ISFStaticDuty = $RowExporterTaxRates['ISFStaticDuty'];
        $InsurancePerc = $RowExporterTaxRates['InsurancePremiumRate'];
        $ReciprocalTariffsPerc = $RowExporterTaxRates['ReciprocalTariffs'];
        $ClearingAndForwardingCharges = $RowExporterTaxRates['ClearingCharges'];

        $SqlReciprocalTariffsExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' and FromDate<='$CInvDate'  ORDER BY ExpTRID DESC limit 1";
        $StmtReciprocalTariffsExporterTaxRates = $this->conn->query($SqlReciprocalTariffsExporterTaxRates);
        $RowReciprocalTariffsExporterTaxRates = $StmtReciprocalTariffsExporterTaxRates->fetch(PDO::FETCH_ASSOC);
        $ReciprocalTariffsPerc = $RowReciprocalTariffsExporterTaxRates['ReciprocalTariffs'];

        // Temporary logic added only for the DEV client. This will be removed soon.
        if ($BLDate && $AdminConfigDetails['ClientID'] == 'DFLV3' && $AdminConfigDetails['ComInvPrintReciprocal'] && $AdminConfigDetails['ComInvPrintReciprocal'] == true) {
            $blDate = new DateTime($BLDate);
            $startDate = new DateTime('2025-04-05');
            $endDate = new DateTime('2025-04-10');

            if ($blDate <= $endDate) {
                $reciprocalSql = "SELECT * FROM `ReciprocalTariffs` WHERE PoID=$PoID";
                $reciprocalStmt = $this->conn->query($reciprocalSql);
                $reciprocalRow = $reciprocalStmt->fetch(PDO::FETCH_ASSOC);
                $ReciprocalTariff = $reciprocalRow['ReciprocalTariff'];
                $TariffRate = $reciprocalRow['TariffRate'];

                if (!empty($ReciprocalTariff) && !empty($TariffRate) && $ReciprocalTariff == 1) {
                    $ReciprocalTariffsPerc = $TariffRate;
                }
            }
        }



        // if ($DocType == 'CMI') {
        $ClearanceChargePerc = $RowExporterTaxRates['ClearanceCharge']; //uma ClearanceCharge calculation included //01-04-2024
        // }
        // $TotalDutyPerc = 100 + $AntiDumpingDutyPerc +
        // $MerchandiseProcessFeePerc +
        // $HarbourMaintenanceFeePerc;

        $SqlRevPoTaxRates = "SELECT * FROM `ScrmPoTaxRates` where PoID='$PoID' AND InvoiceType = '$DocType' ORDER BY ID DESC";
        // echo $SqlRevPoTaxRates;exit;
        $StmtRevPoTaxRates = $this->conn->query($SqlRevPoTaxRates);
        if ($StmtRevPoTaxRates && $StmtRevPoTaxRates->num_rows > 0) {
            $RowRevPoTaxRates = $StmtRevPoTaxRates->fetch(PDO::FETCH_ASSOC);
            // echo json_encode($RowRevPoTaxRates);exit;
            $AntiDumpingDutyPerc = $RowRevPoTaxRates['AntiDumpingDuty'];
            $CountervailingDutyPerc = $RowRevPoTaxRates['CountervailingDuty'];
            $MerchandiseProcessFeePerc = $RowRevPoTaxRates['MerchandiseProcessFee'];
            $HarbourMaintenanceFeePerc = $RowRevPoTaxRates['HarbourMaintenanceFee'];
            $ISFStaticDuty = $RowRevPoTaxRates['ISFStaticDuty'];
            $InsurancePerc = $RowRevPoTaxRates['InsurancePremiumRate'];
            $ReciprocalTariffsPerc = $RowRevPoTaxRates['ReciprocalTariff'];
            $ClearingAndForwardingCharges = $RowRevPoTaxRates['ClearingCharges'];
        }

        $TotalDutyPerc = 100 + $AntiDumpingDutyPerc +
            $MerchandiseProcessFeePerc +
            $HarbourMaintenanceFeePerc + $CountervailingDutyPerc + $ReciprocalTariffsPerc; //uma cvd calculation included //18-03-2024

        // echo $TotalDutyPerc; exit;

        $IsFreight = $RowShipTermsMaster['Freight'];
        $IsDuty = $RowShipTermsMaster['Duty'];
        $Insurance = $RowShipTermsMaster['Insurance'];
        $AntiDumpingDFactor = $RowShipTermsMaster['AntiDumpingDFactor'];
        $AntiDumpingMFactor = $RowShipTermsMaster['AntiDumpingMFactor'];
        $StandardDutyCharges = $RowShipTermsMaster['StandardDutyCharges'];



        $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";


        // $SqlTotal = "SELECT sum(Mcs) as MCS,  $sqlExt  ,
        // 		sum(Price) as Price,
        // 		sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
        // 		sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
        // 		SUM(Mcs * TotalSlabs * SlabWeight * MFactor * Price / 0.454) AS UsdValue,
        // 		GlazeStatus,
        // 		SPV.GlazePerc,ISF.BLDate
        // 		FROM `ScrmPoGradeData` as PGD
        // 		left join ScrmPIVarietyList as SPV on SPV.VarietyID=PGD.VarietyID
        // 		left join ScrmISFDetails as ISF on ISF.PIID=PGD.InvId
        // 		left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'";

        $SqlTotal = "SELECT sum(Mcs) as MCS, $sqlExt,
			sum(Price) as Price,PS.Uom,
			sum(CASE WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * LbsFactor * 0.454 * Price ELSE 0 END) AS PriceOZKgs,
			sum(CASE WHEN PS.Uom = 'OZ' THEN Mcs * TotalSlabs * SlabWeight * LbsFactor * Price ELSE 0 END) AS PriceOZLbs,
			sum(CASE WHEN PS.Uom != 'OZ' THEN Mcs * TotalSlabs * SlabWeight * Price * MFactor ELSE 0 END) AS PriceKgs,
			sum(CASE WHEN PS.Uom != 'OZ' THEN Mcs * TotalSlabs * SlabWeight * Price * LbsFactor ELSE 0 END) AS PriceLbs,
            sum(Mcs*TotalSlabs*SlabWeight*MFactor*Price / 0.454) AS UsdValue,
			GlazeStatus,
			VL.GlazePerc,ISF.BLDate
			FROM `ScrmPoGradeData` as PGD

LEFT JOIN ScrmPIVarietyList VL
    ON VL.VarietyID = PGD.VarietyID

LEFT JOIN ScrmISFDetails ISF
    ON ISF.PIID = PGD.InvId

LEFT JOIN AdminPackingStyles PS
    ON PS.PCKID =
       IF(
           VL.HasSauce = 'Yes' AND IFNULL(PGD.CUPS, 0) > 0,
           VL.ShrimpPackingStyle,
           VL.PackingStyle
       )
WHERE InvId='$PoID'";

        $StmtTotal = $this->conn->query($SqlTotal);
        // echo $SqlTotal; exit;
        $RowTotal = $StmtTotal->fetch(PDO::FETCH_ASSOC);




        // SELECT sum(Mcs) as MCS, sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs, sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
        // sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs, sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs ,
        // sum(Price) as Price, sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
        // sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs, GlazeStatus, SPV.GlazePerc FROM `ScrmPoGradeData` as PGD
        // left join ScrmPIVarietyList as SPV on SPV.VarietyID=PGD.VarietyID
        // left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='3326'

        // { "MCS": "3285", "NetWgtKgs": "15569.8", "GrossWeightKgs": "17422.25", "NetWgtLbs": "34290.4", "GrossWeightLbs": "38375",
        // "Price": "29.050000000000004", "PriceKgs": "56498.59", "PriceLbs": "124430.92", "GlazeStatus": "No", "GlazePerc": "" }

        // SELECT sum(Mcs) as MCS, sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs, sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
        // sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs, sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs ,
        // sum(Price) as Price, sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
        // sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM `ScrmPoGradeData` as PGD
        // left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='3326'
        // { "MCS": "3285", "NetWgtKgs": "15569.8", "GrossWeightKgs": "17422.25", "NetWgtLbs": "34290.4", "GrossWeightLbs": "38375",
        // "Price": "29.050000000000004", "PriceKgs": "56498.59", "PriceLbs": "124430.92" } Harinath


        $IsSauce = $this->checkSauceExists($PoID);
        $SauceTotalUsdValue = 0;
        $SauceTotalKgs = 0;
        $SauceTotalLbs = 0;
        if ($IsSauce && $IsSauce == 1) {
            $SqlSauce = "SELECT PGD.Mcs, PGD.VarietyID, PGD.CUPS, PGD.CupsPrice, PS.*, SPD.QtyUnit
			FROM ScrmPoGradeData as PGD
			LEFT JOIN ScrmPoData as SPD ON SPD.PoID = PGD.InvId
			LEFT JOIN ScrmPIVarietyList as SPVL ON SPVL.VarietyID = PGD.VarietyID
			LEFT JOIN AdminPackingStyles as PS ON SPVL.SaucePackingStyle = PS.PCKID
			WHERE InvId='$PoID' AND SPVL.HasSauce = 'Yes' AND PGD.CUPS > 0";
            $StmtSauce = $this->conn->query($SqlSauce);
            while ($RowSauce = $StmtSauce->fetch(PDO::FETCH_ASSOC)) {
                $SauceLbsWeight = $RowSauce['TotalSlabs'] * $RowSauce['SlabWeight'] * $RowSauce['Mcs'];
                if ($RowSauce['Uom'] == 'OZ') {
                    $OzToLbs = $RowSauce['SlabWeight'] * $RowSauce['LbsFactor'];
                    $SauceLbsWeight *= $OzToLbs / $RowSauce['SlabWeight'];
                }
                $SauceKgsWeight = $SauceLbsWeight * 0.454;
                if ($RowSauce['QtyUnit'] == 'LBS' && $PType == 'KGS') {
                    $QtyCalc = $SauceKgsWeight;
                    $RowSauce['CupsPrice'] = $RowSauce['CupsPrice'] / 0.454;
                } else {
                    $QtyCalc = $SauceLbsWeight;
                }
                $SauceTotalUsdValue += $QtyCalc * $RowSauce['CupsPrice'];
                // echo $SauceTotalUsdValue;exit;
                $SauceTotalKgs += $SauceKgsWeight;
                $SauceTotalLbs += $SauceLbsWeight;
            }
        }

        $NetWgtKgs = $RowTotal['NetWgtLbs'] * $LbsFactor;
        $NetWgtKgs += $SauceKgsWeight;
        $NetWgtLbs = $RowTotal['NetWgtLbs'];
        $NetWgtLbs += $SauceTotalLbs;

        $GrossWeightKgs = $RowTotal['GrossWeightKgs'];
        $GrossWeightLbs = $RowTotal['GrossWeightLbs'];


        if ($QtyUnit == 'KGS') {
            $Weight = $RowTotal['NetWgtKgs'] + $SauceKgsWeight;
            $InvoiceValue = $RowTotal['PriceOZKgs'] > 0 ? $RowTotal['PriceOZKgs'] + $RowTotal['PriceKgs'] : $RowTotal['PriceKgs'];
            $AryPrices['ShrimpValueUsd'] = $InvoiceValue;
            $AryPrices['SauceValueUsd'] = $SauceTotalUsdValue;
            // $InvoiceValue += $SauceTotalUsdValue; // Add Sauce USD Value
        } else {
            $Weight = $RowTotal['NetWgtLbs'] * $LbsFactor;
            $InvoiceValue = $RowTotal['PriceOZLbs'] > 0 ? $RowTotal['PriceOZLbs'] + $RowTotal['PriceLbs'] : $RowTotal['PriceLbs'];
            $AryPrices['ShrimpValueUsd'] = $InvoiceValue;
            $AryPrices['SauceValueUsd'] = $SauceTotalUsdValue;
            // $InvoiceValue += $SauceTotalUsdValue * 0.454;
        }

        $InvoiceValue += $SauceTotalUsdValue;

        // echo $SauceTotalUsdValue;exit;
        // $InvoiceValue = $RowTotal['UsdValue'];



        if ($Discount > 0) {

            if (isset($DiscountType) && $DiscountType == 0) {
                $DiscountAmount = ($InvoiceValue * $Discount) / 100;
                $InvoiceValueAfterDiscount = $InvoiceValue;
                $InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount; // By Rantagiri 04/17/2023 5.44 
            } else {
                $DiscountAmount = $Discount;
                $InvoiceValueAfterDiscount = $InvoiceValue;
                $InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount;
            }
        } else {
            $InvoiceValueAfterDiscount = $InvoiceValue;
        }

        if ($Insurance == 1) {
            //$InsuranceValue= ($PriceAfterDiscount*$InsurancePerc)/100;
            $InsuranceValue = ($InvoiceValueAfterDiscount * $InsurancePerc) / 100;
        } else {
            $InsuranceValue = 0;
        }

        // $InsuranceValue = 0;


        if ($IsDuty == 1) {

            $ClearanceChargeFee = (($InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge) * $ClearanceChargePerc) / 100; //#jme uma ClearanceCharge calculation included //01-04-2024

            $MerchandiseProcessFee = (($InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges) / $TotalDutyPerc) * $MerchandiseProcessFeePerc;
            //$MerchandiseProcessFee=round((($EffInvValue*$MerchandiseProcessFeePerc)/$TotalDutyPerc),2);

            if ($MerchandiseProcessFeePerc == 0) {
                $ISFStatic = false;
                $MerchandiseProcessFee = $ISFStaticDuty;
                $EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges;
                // $MerchandiseProcessFee=round((($EffInvValue*$MerchandiseProcessFeePerc)/$TotalDutyPerc),2);

            } else if ($ISFStaticDuty != 0 && $MerchandiseProcessFee > $ISFStaticDuty) {
                $ISFStatic = true;
                $MerchandiseProcessFee = $ISFStaticDuty;
                $TotalDutyPerc = $TotalDutyPerc - $MerchandiseProcessFeePerc;
                $EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges - $MerchandiseProcessFee;
            } else {
                $ISFStatic = false;
                $EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $ClearingAndForwardingCharges;
                $MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
                // $MerchandiseProcessFee=$ISFStaticDuty;
            }
            // ECHO $MerchandiseProcessFee; EXIT;

            // echo 'InvValueUSD: ' . $InvoiceValueAfterDiscount;
            // echo 'EffInvValue: ' . $EffInvValue;
            // echo '<br>MerchandiseProcessFee: ' . $MerchandiseProcessFee;
            // echo '<br>InsuranceValue: ' . $InsuranceValue;
            // echo '<br>FrieghtCharge: ' . $FrieghtCharge;
            // echo '<br>ClearanceChargeFee: ' . $ClearanceChargeFee;
            // echo '<br>ISFStaticDuty: ' . $ISFStaticDuty;
            // echo '<br>ISFStatic: ' . $ISFStatic;
            // echo '<br>TotalDutyPerc: ' . $TotalDutyPerc;
            // echo '<br>AntiDumpingDutyPerc: ' . $AntiDumpingDutyPerc;
            // echo '<br>HarbourMaintenanceFeePerc: ' . $HarbourMaintenanceFeePerc;
            // echo '<br>CountervailingDutyPerc: ' . $CountervailingDutyPerc;
            // echo '<br>ReciprocalTariffsPerc: ' . $ReciprocalTariffsPerc;
            // echo '<br>InvoiceValueAfterDiscount: ' . $InvoiceValueAfterDiscount;
            // echo '<br>InvoiceValue: ' . $InvoiceValue;
            // echo '<br>DiscountAmount: ' . $DiscountAmount;
            // echo '<br>InsurancePerc: ' . $InsurancePerc;
            // echo '<br>SauceTotalUsdValue: ' . $SauceTotalUsdValue;
            // echo '<br>ShrimpTotalUsdValue: ' . $AryPrices['ShrimpValueUsd'];
            // exit;
            $EffInvValue = $EffInvValue - $ClearanceChargeFee; //#jme uma ClearanceCharge calculation included //01-04-2024

            // if ($IsSauce && $IsSauce == 1) {
            // $EffInvValue = $EffInvValue - $SauceTotalUsdValue; // Deduct Sauce USD Value from Effective Invoice Value
            // }

            $AntiDumpingCharges = round((($EffInvValue * $AntiDumpingDutyPerc) / $TotalDutyPerc), 2);
            $HarbourMaintenanceFee = round((($EffInvValue * $HarbourMaintenanceFeePerc) / $TotalDutyPerc), 2);
            $CountervailingDutyFee = round((($EffInvValue * $CountervailingDutyPerc) / $TotalDutyPerc), 2); //uma cvd calculation included //18-03-2024
            $ReciprocalTariffsFee = 0;
            if ($ReciprocalTariffsPerc > 0) {
                $ReciprocalTariffsFee = round((($EffInvValue * $ReciprocalTariffsPerc) / $TotalDutyPerc), 2); //uma cvd calculation included //18-03-2024
            }
            if ($ISFStatic) {
                // $DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee;
                $DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee + $CountervailingDutyFee + $ReciprocalTariffsFee;
                // $DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee+$CountervailingDutyFee+$ClearanceChargeFee; //uma ClearanceCharge calculation included //01-04-2024
            } else {
                // $DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee;
                $DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee + $CountervailingDutyFee + $ReciprocalTariffsFee; //uma cvd calculation included //18-03-2024
                // $DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee+$CountervailingDutyFee+$ClearanceChargeFee;//uma ClearanceCharge calculation included //01-04-2024
            }
        } else {
            $DutiesPrice = $DutiesPrice + $StandardDutyCharges;
        }

        // echo $DutiesPrice; exit;


        $InvValueUSD = $InvoiceValueAfterDiscount;
        // echo $DutiesPrice; exit;
        if ($ISFStatic) {

            //commented by uma
            // $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $ISFStaticDuty;
            // $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
            // $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $ISFStaticDuty; 

            //commented by uma

            $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $ISFStaticDuty - $InsuranceValue; //#jme uma ClearanceCharge calculation included //01-04-2024
            $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
            $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $ISFStaticDuty - $InsuranceValue - $ClearanceChargeFee; //#jme uma ClearanceCharge calculation included //01-04-2024
        } else {
            // $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge;
            // $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
            // $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice;

            // $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge-$InsuranceValue;
            $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $InsuranceValue; //#jme uma ClearanceCharge calculation included //01-04-2024
            $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
            $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $InsuranceValue - $ClearanceChargeFee; //#jme uma ClearanceCharge calculation included //01-04-2024
        }

        $HasCFLCharges = $AdminConfigDetails['HasCLFCharges'];
        if ($HasCFLCharges) {
            $InvValueUSDWithOutDuties = $InvValueUSDWithOutDuties - $ClearingAndForwardingCharges;
            $FOBValueUSD -= $ClearingAndForwardingCharges;
        }

        $InvValueINR = $InvValueUSD * $ExchangeRate;
        $FOBValueINR = $FOBValueUSD * $ExchangeRate;



        // $IgstValueBasedonProducttype = getIgstValueBasedonProducttype($this->conn, $PoID, $ExchangeRate); //if Cooked and raw both are existed in same po

        // print_r($IgstValueBasedonProducttype); exit;
        $IgstValue = ($InvValueINR * $IgstPerc) / 100;



        $AryPrices['DiscountPerc'] = $Discount;
        $AryPrices['ExchangeRate'] = $ExchangeRate;
        $AryPrices['DiscountAmount'] = $DiscountAmount;
        $AryPrices['InsuranceValue'] = $InsuranceValue;
        $AryPrices['DutiesPrice'] = $DutiesPrice;
        $AryPrices['FrieghtCharge'] = $FrieghtCharge;

        $AryPrices['InvValueUSD'] = $InvValueUSD;
        $AryPrices['InvValueUSDWithOutDuties'] = $InvValueUSDWithOutDuties;
        $AryPrices['InvValueINRWithOutDuties'] = $InvValueINRWithOutDuties;
        $AryPrices['InvValueINR'] = $InvValueINR;
        $AryPrices['FOBValueUSD'] = $FOBValueUSD;
        $AryPrices['FOBValueINR'] = $FOBValueINR;
        $AryPrices['PaymentTerm'] = $TermName;
        $AryPrices['IgstPerc'] = $IgstPerc;
        $AryPrices['IgstValue'] = $IgstValue;
        $AryPrices['AntiDumpingDuty'] = $AntiDumpingCharges;
        $AryPrices['MerchantDuty'] = $MerchandiseProcessFee;
        $AryPrices['HarborDuty'] = $HarbourMaintenanceFee;
        $AryPrices['CountervailingDuty'] = $CountervailingDutyFee; //uma cvd calculation included //18-03-2024
        $AryPrices['ReciprocalTariffsFee'] = $ReciprocalTariffsFee;

        $AryPrices['ClearanceCharge'] = round($ClearanceChargeFee, 2); //uma ClearanceCharge calculation included //01-04-2024
        $AryPrices['ISFStatic'] = $ISFStatic;
        $AryPrices['CenttoUsd'] = $CenttoUsd;
        $AryPrices['ActualPrice'] = $InvoiceValue;
        $AryPrices['HarbourMaintenanceFeePerc'] = $HarbourMaintenanceFeePerc;
        $AryPrices['CountervailingDutyPerc'] = $CountervailingDutyPerc; //uma cvd calculation included //18-03-2024
        $AryPrices['ReciprocalTariffsPerc'] = $ReciprocalTariffsPerc;

        $AryPrices['ClearanceChargePerc'] = $ClearanceChargePerc; //uma ClearanceCharge calculation included //01-04-2024
        $AryPrices['AntiDumpingDutyPerc'] = $AntiDumpingDutyPerc;
        $AryPrices['MerchandiseProcessFeePerc'] = $MerchandiseProcessFeePerc;

        // $AryPrices['IgstValueBasedonProducttype'] = $IgstValueBasedonProducttype; ////if Cooked and raw both are existed in same po #jme
        $AryPrices['ClearingAndForwardingCharges'] = $ClearingAndForwardingCharges;


        return $AryPrices;
    }

    public function getInvoiceDescription($InvDate, $ExpID)
    {
        // $ExpID; exit;
        $SqlDescMaster = "SELECT * FROM `ScrmDescriptionMaster` WHERE EffectiveFromDate
			<=  '$InvDate' AND ExporterID = $ExpID ORDER by EffectiveFromDate desc limit 1; ";
        $StmtDescMaster = $this->conn->query($SqlDescMaster);

        $dataRow = $StmtDescMaster->fetch(PDO::FETCH_ASSOC);
        return $dataRow;
    }
}
