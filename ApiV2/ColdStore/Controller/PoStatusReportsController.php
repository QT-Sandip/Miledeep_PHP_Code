<?php

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class PoStatusReportsController
{
    private $conn;
    //  private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }

    function getPoStatusDetails($RequestBody)
    {

        $Exporter = $RequestBody['Exporter'];
        $Processor = $RequestBody['Processor'];
        $Consignee = $RequestBody['Consignee'];
        $Plant = $RequestBody['Plant'];
        $PoNumber = $RequestBody['PoNumber'];

        // echo 'Plant' . $Plant;
        $Query = '';

        if ($Exporter != "") {
            $Query = ' and POD.ExporterID=' . $Exporter;
        }
        if ($Processor != '') {
            $Query .= ' and POD.ProcessorID=' . $Processor;
        }

        if ($Consignee != "") {
            $Query .= ' and POD.ConsigneeID=' . $Consignee;
        }
        if ($Plant != "") {
            $Query .= " AND FIND_IN_SET('$Plant', POD.ProductionPlantID) > 0";
        }
        if ($PoNumber != "") {
            $Query .= ' and POD.PoID=' . $PoNumber;
        }

        $sqlFields = "PoID,PoNo,LotNo,SPC.Name as Port,Brand,ConsigneeName,ShipByDate ";

        $sqlQuery = "SELECT $sqlFields FROM ScrmPoData AS POD
        LEFT JOIN ScrmConsigneeDetails AS SCD ON POD.ConsigneeID=SCD.ConID
        LEFT JOIN ScrmCountriesList AS SCL ON POD.DestinationCountry=SCL.id
        LEFT JOIN ScrmPortCities AS SPC ON POD.PortOfDischarge  =SPC.PortId
        LEFT JOIN ScrmShipmentStatusCodes AS SSC ON POD.Status=SSC.Code
        LEFT JOIN ScrmShipmentDetailsBreif AS SDB ON POD.PoID=SDB.PiID
        LEFT JOIN ScrmPoGradeData AS ScPGD ON ScPGD.InvId=POD.PoID
        WHERE PoID not in (SELECT PiID FROM ScrmCustomInvoices WHERE StockOutDate!='') and POD.Status not in (2,3,4)  $Query
        GROUP BY PoID ORDER BY ShipByDate ASC ";

        $stmtPoDetails = $this->conn->prepare($sqlQuery);
        $stmtPoDetails->execute();
        $arySupcConsumed = array();

        $i = 0;
        while ($dataRow = $stmtPoDetails->fetch(PDO::FETCH_ASSOC)) {

            $FinalData = $this->getPoBreakUp($dataRow['PoID'], $arySupcConsumed, $RequestBody);
            $arySupcConsumed = $FinalData['arySupcConsumed'];
            $PoBreakUp = $FinalData['Data'];

            if ($dataRow['LotNo'] != "") {
                $LotNo = "  (" . $dataRow['LotNo'] . ")";
            } else {
                $LotNo = "";
            }

            if ($PoBreakUp != null && !empty($PoBreakUp) && count($PoBreakUp) > 0) {
                $PoNo = $dataRow['PoNo'] . $LotNo;
                $ArrayPoDetails['SNO'] = ++$i;
                $ArrayPoDetails['PoID'] = $dataRow['PoID'];
                $ArrayPoDetails['PoNo'] = $PoNo;
                $ArrayPoDetails['LotNo'] = $LotNo;
                $ArrayPoDetails['ConsigneePort'] = $dataRow['Port'];
                $ArrayPoDetails['Brand'] = $dataRow['Brand'];
                $ArrayPoDetails['ConsigneeName'] = $dataRow['ConsigneeName'];
                $ArrayPoDetails['ShipByDate'] = $dataRow['ShipByDate'] ? date('d-m-Y', strtotime($dataRow['ShipByDate'])) : 'NA';

                $ArrayPoDetails['PoBreakUp'] = $PoBreakUp;

                $Data[] = $ArrayPoDetails;
            }
        }

        return $Data;
    }

    function getPoBreakUp($InvId, $arySupcConsumed, $RequestBody)
    {

        $PackingStyle = $RequestBody['PackingStyle'];
        $GradeId = $RequestBody['Grade'];
        $Freezing = $RequestBody['Freezing'];
        $Soaking = $RequestBody['Soaking'];
        $variety = $RequestBody['Variety'];
        $PDCategory = $RequestBody['PDCategory'];
        $ABPossitive = $RequestBody['ABPossitive'];
        // echo $ABPossitive;

        $Query = "";
        if ($PackingStyle != "") {
            $Query = " and SPGD.PackingStyleID=" . $PackingStyle;
        }
        if ($GradeId != "") {
            $Query .= " and SPGD.Grade='" . $GradeId . "'";
        }
        if ($Freezing != "") {
            $Query .= " and SPGD.FreezingType='" . $Freezing . "'";
        }
        if ($Soaking != "") {
            $Query .= " and SPGD.Treatment='" . $Soaking . "'";
        }
        if ($variety != "") {
            $Query .= " and SPVL.ProductID=" . $variety;
        }
        if ($PDCategory != "") {
            $Query .= " and CSD.ProductCategoryID='" . $PDCategory . "'";
        }
        if ($ABPossitive != "") {
            if($ABPossitive == 0){
                $Query .= " and CSD.AbStatus='AB+'";
            }else{
                $Query .= " AND (CSD.AbStatus <> 'AB+' OR CSD.AbStatus IS NULL OR CSD.AbStatus = '')";
            }
        }
        $ArySupcData = $arySupcConsumed;

        $sqlFields = "Brand,Mcs AS Mcs,SPVL.Grade,SPGD.VarietyID,SPVL.ProductDescription,APS.PackingStyle,TotalSlabs,SlabWeight,MFactor,
        SPVL.DeviSupc,SPVL.ShortName as VarietyForm,SPVL.FreezingType,SPVL.Treatment,
        Mcs*TotalSlabs*SlabWeight*MFactor as OrderKgs, SPGD.ID AS PoGradeId,CSD.AbStatus ";

        $sqlQuery = "SELECT $sqlFields FROM `ScrmPoGradeData`AS SPGD
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPGD.VarietyID=SPVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=SPGD.PackingStyleID
        LEFT JOIN CsStockData AS CSD ON SPGD.InvId=CSD.PoID
        WHERE InvId='$InvId' $Query GROUP BY SPGD.ID,CSD.AbStatus order by SPGD.ID asc";
        // WHERE InvId='2342 '
        $stmtPoBreakUp = $this->conn->prepare($sqlQuery);
        $stmtPoBreakUp->execute();

        // $stmtPoBreakUp1 = $this->conn->prepare($sqlQuery);
        // $stmtPoBreakUp1->execute();

        while ($dataRow = $stmtPoBreakUp->fetch(PDO::FETCH_ASSOC)) {
            $VarietyID = $dataRow['VarietyID'];
            $SupcCode = $dataRow['DeviSupc'];
            $Grade = $dataRow['Grade'];
            $checkCode = $Grade . '-' . $dataRow['ProductDescription'];

            $finalData = $this->getFinalData($dataRow['PoGradeId'],$dataRow['AbStatus']);
            $requiredKgs = round($dataRow['OrderKgs'] - $finalData['FinishedKgs'], 2);
            $AvalStoreKgs = $this->getDummyStock($dataRow['VarietyID'],$dataRow['AbStatus']);
            $ArrayHeadonData = $this->getHeadondata($dataRow['VarietyID']);

            // if
            // $HeadonCount=$ArrayHeadonData['HeadonCount'];


            // $HeadonYield=$ArrayHeadonData['Yield'];
            if ($requiredKgs > 0) {
                $HeadonCount = $ArrayHeadonData['HeadonCount'];
                $HeadonYield = $ArrayHeadonData['Yield'];
                $HeadonQuantityinKgs = ($requiredKgs / $HeadonYield) * 100;
                $HeadonQuantityinTons = $HeadonQuantityinKgs / 1000;
            }

            $usedSupcWeight = $this->getAdjustedWeight($ArySupcData, $checkCode);
            $storeKgs = $AvalStoreKgs - $usedSupcWeight;

            if ($requiredKgs == 0) {
                $supcWeight = 0;
            } else if ($storeKgs > 0 && $storeKgs >= $requiredKgs) {
                $supcWeight = $requiredKgs;
            } else if ($storeKgs > 0 && $requiredKgs > $storeKgs) {
                $supcWeight = $storeKgs;
            } else if ($storeKgs > 0) {
                $supcWeight = $requiredKgs - $storeKgs;
            } else {
                $supcWeight = 0;
            }


            //    echo $supcWeight;exit;
            // $AryHeadONData = $this->getRmReq($VarietyID, $requiredKgs - $supcWeight);

            $AvalStoreMcs = $supcWeight / ($dataRow['TotalSlabs'] * $dataRow['SlabWeight'] * $dataRow['MFactor']);


               $Percent = '0%';
              if ($dataRow['OrderKgs'] > 0) {
            $Percent = round((($finalData['FinishedKgs']) / $dataRow['OrderKgs']) * 100,2) . '%';
            }
            $percentVal = floatval($Percent);

            if ($percentVal >= 90) {
                $Color = '#28a745';
                $Colorclass = 'badge-success';
            } elseif ($percentVal >= 50 && $percentVal<90) {
                $Color = '#ffc107';
                $Colorclass = 'badge-warning';
            } else {
                $Color = '#dc3545';
                $Colorclass = 'badge-danger';
            }

            $ArrayPoBrkUp['Percent'] = $Percent;
            $ArrayPoBrkUp['Color']   = $Color;
            $ArrayPoBrkUp['Colorclass']   = $Colorclass;


            $Item = array();
            $Item['SUPC'] = $checkCode; //$dataRow['ProductDescription']; //$SupcCode;
            $Item['SupcWeight'] = $supcWeight;
            $AvalStoreKgs = $usedSupcWeight;
            $ArySupcData[] = $Item;

            $assignedWeight = 0;

            $ArrayPoBrkUp['OrderedKgs'] = round($dataRow['OrderKgs'], 3);
            $ArrayPoBrkUp['OrderedMcs'] = $dataRow['Mcs'];
            $ArrayPoBrkUp['Grade'] = $dataRow['Grade'];
            $ArrayPoBrkUp['SUPC'] = $SupcCode;
            $ArrayPoBrkUp['Product'] = $dataRow['ProductDescription'];
            $ArrayPoBrkUp['Variety'] = $dataRow['VarietyForm'];
            $ArrayPoBrkUp['FreezingType'] = $dataRow['FreezingType'];
            $ArrayPoBrkUp['SoakingType'] = $dataRow['Treatment'];
            $ArrayPoBrkUp['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayPoBrkUp['VarietyID'] = $dataRow['VarietyID'];
            $ArrayPoBrkUp['PoGradeId'] = $dataRow['PoGradeId'];
            $ArrayPoBrkUp['FinishedMcs'] = $finalData['AvalMcs'];
            $ArrayPoBrkUp['FinishedKgs'] = round($finalData['FinishedKgs'], 3);
            $ArrayPoBrkUp['AvalStoreKgs'] = round($supcWeight, 3);
            $ArrayPoBrkUp['AvalStoreMcs'] = round($AvalStoreMcs);
            $ArrayPoBrkUp['TotalStoreKgs'] = round($storeKgs, 3);
            $ArrayPoBrkUp['BalanceKgs'] = round($requiredKgs, 3);
            $ArrayPoBrkUp['BalanceMcs'] = $ArrayPoBrkUp['OrderedMcs'] - $finalData['AvalMcs'];
            // $ArrayPoBrkUp['HonWeight'] = round($AryHeadONData['HonWeight'], 3);
            // $ArrayPoBrkUp['HonCount'] = $AryHeadONData['HonCount'];
            $ArrayPoBrkUp['HeadonCount'] = $HeadonCount;
            $ArrayPoBrkUp['HeadonQuantityinKgs'] = number_format($HeadonQuantityinKgs, 3);
            $ArrayPoBrkUp['HeadonQuantityinTons'] = number_format($HeadonQuantityinTons, 3);
            $ArrayPoBrkUp['AbStatus'] = $dataRow['AbStatus'];
             $ArrayPoBrkUp['Percent'] = $Percent;



            $Data[] = $ArrayPoBrkUp;
        }
        $FinalData['arySupcConsumed'] = $ArySupcData;
        $FinalData['Data'] = $Data;
        return $FinalData;
    }

    function getAdjustedWeight($Arydata, $refSupc)
    {

        $Weight = 0;
        foreach ($Arydata as $Item) {
            if ($Item['SUPC'] == $refSupc) {
                $Weight += $Item['SupcWeight'];
            }
        }

        return $Weight;
    }

    function getRmReq($VarietyID, $BalWeight)
    {

        $SqlGradeData = "SELECT Grade, ShortName, GlazePerc FROM `ScrmPIVarietyList` WHERE VarietyID=$VarietyID";
        $stmtGradeData = $this->conn->prepare($SqlGradeData);
        $stmtGradeData->execute();

        $RowGradeData = $stmtGradeData->fetch(PDO::FETCH_ASSOC);

        $Grade = $RowGradeData['Grade'];
        $ShortName = $RowGradeData['ShortName'];
        $GlazePerc = $RowGradeData['GlazePerc'];
        $AryGrade = explode('/', $Grade);
        $PackingCount = $AryGrade[1] - 2;

        if ($GlazePerc > 0) {
            $GlazePerc = 100 - $GlazePerc;
            $GlazePerc = $GlazePerc / 100;
            $PackingCount = round(($PackingCount / $GlazePerc), 2);
            $BalWeight = round(($BalWeight * $GlazePerc), 2);
        }

        if ($ShortName == 'HLSO' || $ShortName == 'EZPL') {
            $HLWeight = round($BalWeight * 0.95, 2);
            $HonCount = round(($PackingCount * 0.70 * 2.2) / 0.95, 2);
        } else {
            $HLWeight = round($BalWeight / 0.95, 2);
            $HonCount = round($PackingCount * 0.70 * 2.2 * 0.95, 2);
        }
        $HonWeight = round($HLWeight / 0.70, 2);

        $AryData['HonWeight'] = $HonWeight;
        $AryData['HonCount'] = $HonCount;

        return $AryData;
    }

    function getFinalMcs($PoGradeId)
    {
        $sqlFields = "COALESCE(sum(AvalMcs),0) as AvalMcs,COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor),0) as FinishedKgs";
        $sqlFinalMcs = "SELECT $sqlFields FROM `CsStockData` as CSD
        LEFT JOIN AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        WHERE PoGradeId='$PoGradeId'";

        $stmtFinalMcs = $this->conn->prepare($sqlFinalMcs);
        $stmtFinalMcs->execute();

        $rowData = $stmtFinalMcs->fetch(PDO::FETCH_ASSOC);
        return $rowData;
    }

    function getDummyStock($VarietyID,$AbStatus) // completed
    {
        // $ArrayVarietyInformation[] = '';
        // $sqlFields = "TypeProduct,Glaze,Treatment,Grade,FreezingType,ProductID,Species,PreparationType,GlazePerc";

        // $SqlGradeData = "SELECT $sqlFields FROM `ScrmPIVarietyList` WHERE VarietyID in ($VarietyID)";

        // $stmtStock = $this->conn->prepare($SqlGradeData);
        // $stmtStock->execute();

        // $dataRow = $stmtStock->fetch(PDO::FETCH_ASSOC);
        $ArrayVarietyInformation = $this->getVarietyInformation($VarietyID);

        $TypeProduct = $ArrayVarietyInformation['TypeProduct'];
        $Treatment = $ArrayVarietyInformation['Treatment'];
        $Grade = $ArrayVarietyInformation['Grade'];
        $FreezingType = $ArrayVarietyInformation['FreezingType'];
        $ProductID = $ArrayVarietyInformation['ProductID'];
        $Species = $ArrayVarietyInformation['Species'];
        $PreparationType = $ArrayVarietyInformation['PreparationType'];
        // $Glaze = $dataRow['Glaze'];

        // $TypeProduct = $dataRow['TypeProduct'];
        // // $Glaze = $dataRow['Glaze'];
        // $Treatment = $dataRow['Treatment'];
        // $Grade = $dataRow['Grade'];
        // $FreezingType = $dataRow['FreezingType'];
        // $ProductID = $dataRow['ProductID'];
        // $Species = $dataRow['Species'];
        // $PreparationType = $dataRow['PreparationType'];
        // // $GlazePerc = $dataRow['GlazePerc'];

        $sqlStockData = "SELECT COALESCE(GROUP_CONCAT(VarietyID),0) as VarityID  FROM `ScrmPIVarietyList` WHERE
        TypeProduct ='$TypeProduct'  and Treatment='$Treatment'  and Grade='$Grade'
        and FreezingType='$FreezingType' and ProductID='$ProductID' and Species='$Species' and PreparationType='$PreparationType'
        ";

        $stmtStockData = $this->conn->prepare($sqlStockData);
        $stmtStockData->execute();

        $rowStockData = $stmtStockData->fetch(PDO::FETCH_ASSOC);

        $VarietyIDs = $rowStockData['VarityID'];

        $sqlStoreData = "SELECT COALESCE(sum(AvalKgs),0) as StoreKgs FROM `CsStockData` as CSD  WHERE PoGradeId='0'
         and VarietyID in ($VarietyIDs) AND CSD.AbStatus='$AbStatus'";

        $stmtStoreData = $this->conn->prepare($sqlStoreData);
        $stmtStoreData->execute();

        $rowStoreData = $stmtStoreData->fetch(PDO::FETCH_ASSOC);

        $StoreKg = $rowStoreData['StoreKgs'];

        return $StoreKg;
    }

    function getFinalData($PoGradeId,$AbStatus) // Values displaying as 0
    {

        $sqlFields = "COALESCE(sum(AvalMcs),0) as AvalMcs,COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor),0) as FinishedKgs,COALESCE(sum(LooseBags),0) as LooseBags,COALESCE(sum(LooseBags*SlabWeight*MFactor),0) as LooseBagsKgs";

        $sqlFinalsData = "SELECT $sqlFields FROM `CsStockData` as CSD
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID
        WHERE PoGradeId='$PoGradeId' AND CSD.AbStatus='$AbStatus'";
        $stmtFinalsData = $this->conn->prepare($sqlFinalsData);
        $stmtFinalsData->execute();

        $rowFinalsData = $stmtFinalsData->fetch(PDO::FETCH_ASSOC);

        $finalsData['AvalMcs'] = $rowFinalsData['AvalMcs'];
        $finalsData['FinishedKgs'] = $rowFinalsData['FinishedKgs'] + $rowFinalsData['LooseBagsKgs'];

        return $finalsData;
    }
    function getHeadondata($VarietyID)
    {
        $ArrayVarietyInformation = $this->getVarietyInformation($VarietyID);
        $TypeProduct = $ArrayVarietyInformation['TypeProduct'];
        $Treatment = $ArrayVarietyInformation['Treatment'];
        $Grade = $ArrayVarietyInformation['Grade'];
        $FreezingType = $ArrayVarietyInformation['FreezingType'];
        $Species = $ArrayVarietyInformation['Species'];
        $PreparationType = $ArrayVarietyInformation['PreparationType'];

        $HeadonGroupQuery = " Group by TypeProduct,ShortName,Treatment,Grade,FreezingType,Species";
        $SqlHeadonData = " SELECT   `HeadonCount`, `Yield`  FROM `CsHeadonRequirementMasters`
        WHERE   TypeProduct ='$TypeProduct'  and Treatment='$Treatment'  and Grade='$Grade'
        and FreezingType='$FreezingType'  and Species='$Species'
          $HeadonGroupQuery";
        //   and PreparationType='$PreparationType' 
        $stmtHeadonData = $this->conn->prepare($SqlHeadonData);
        $stmtHeadonData->execute();
        $dataRowHeadonData = $stmtHeadonData->fetch(PDO::FETCH_ASSOC);
        $ArrayHeadonData['HeadonCount'] = $dataRowHeadonData['HeadonCount'];
        $ArrayHeadonData['Yield'] = $dataRowHeadonData['Yield'];
        return $ArrayHeadonData;
    }

    function getVarietyInformation($VarietyID)
    {
        $sqlFields = "TypeProduct,Glaze,Treatment,Grade,FreezingType,ProductID,Species,PreparationType,GlazePerc";

        $SqlVarietyInformation = "SELECT $sqlFields FROM `ScrmPIVarietyList` WHERE VarietyID in ($VarietyID)";

        $stmtVarietyInformation = $this->conn->prepare($SqlVarietyInformation);
        $stmtVarietyInformation->execute();

        $dataRowVarietyInformation = $stmtVarietyInformation->fetch(PDO::FETCH_ASSOC);
        // $ArrayVarietyData[]=$dataRowVarietyInformation;

        return $dataRowVarietyInformation;
    }
}
