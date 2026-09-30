<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class RawMaterialRequirementReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }

    function getRequiredRawMaterialDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $PoID = $RequestBody['PoID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $ProductionPlantID = $RequestBody['Plant'];
        $PlantIDQuery = '';
        $StorePlantIDQuery = '';
        if ($ProductionPlantID != "") {
            $PlantIDQuery .= " and (FIND_IN_SET('$ProductionPlantID', ProductionPlantID) > 0 )";
            $StorePlantIDQuery .= " and (FIND_IN_SET('$ProductionPlantID', PlantID) > 0 )";
        }


        $Query = $this->getFilterQuery($RequestBody)['Query'];
        $FilterPoQuery = $this->getFilterQuery($RequestBody)['PoQuery'];
        $GroupQuery = $this->getGroupQuery()['GroupQuery'];

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $SearchQuery = " and CONCAT_WS('', Species,ProductDescription,TypeProduct,Glaze,VL.GlazePerc,VL.Treatment,VL.FreezingType,
            VL.Grade,ShortName,PreparationType) LIKE '%$SearchKey%'";
        }


        if ($PoID == '') {
            $SqlPoIds = "SELECT GROUP_CONCAT(SPD.PoID) as PoID FROM ScrmPoData as SPD 
            where SPD.PoID not in (SELECT PiID FROM `ScrmCustomInvoices` WHERE StockOutDate!='')and Status not in (2,3,4)  $PlantIDQuery order by SPD.PoID DESC";
            $stmtPoIds = $this->conn->prepare($SqlPoIds);
            $stmtPoIds->execute();
            $dataRowPoIds = $stmtPoIds->fetch(PDO::FETCH_ASSOC);
            $PoID = $dataRowPoIds['PoID'];
        }

        $sqlTotals = "
        SELECT COUNT(*) AS Total FROM `ScrmPoData` AS SPD
        LEFT JOIN `ScrmPoGradeData` AS SPGD ON SPGD.InvId = SPD.PoID
        LEFT JOIN `ScrmPIVarietyList` AS VL ON VL.VarietyID = SPGD.VarietyID
        LEFT JOIN `AdminPackingStyles` AS CSP ON CSP.PckID = SPGD.PackingStyleID
        WHERE SPD.PoID IN ($PoID) 
        $FilterPoQuery 
        $SearchQuery
        $GroupQuery;";

        $stmtTotals = $this->conn->prepare($sqlTotals);
        $stmtTotals->execute();


        $RowTotals = $stmtTotals->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $RowTotals['Totals'];

        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "group_concat(distinct PoNo) as PoNo,VL.Species,ProductDescription,TypeProduct as ProductType,Glaze,VL.GlazePerc,
        VL.Treatment as SockingType,
        VL.FreezingType,VL.Grade,group_concat(distinct SPGD.VarietyID) as VarietyID ,ShortName,PreparationType ,
        sum(Mcs*TotalSlabs*SlabWeight*MFactor) as OrderedKgs,
        (select sum(AvalKGs) from CsStockData AS CSD where CSD.VarietyID = SPGD.VarietyID and CSD.PackingTypeID='MASTER' ) as AvalKGs,PSY.YieldPercentage,PSY.Count";
        // ,PSY.YieldPercentage


        $SqlOrderedKgs = "  SELECT  $sqlFields FROM `ScrmPoData` as SPD   
        left join `ScrmPoGradeData` as SPGD on SPGD.InvId = SPD.PoID
        left join `ScrmPIVarietyList` as VL on VL.VarietyID=SPGD.VarietyID
        left join `AdminPackingStyles` as CSP on CSP.PckID = SPGD.PackingStyleID
        LEFT JOIN `ProdStandardYields` AS PSY ON VL.ShortName=PSY.Variety AND VL.TypeProduct=PSY.TypeOfProduct AND VL.Grade=PSY.Grade AND VL.Species=PSY.Species AND VL.Treatment=PSY.Treatment AND VL.FreezingType=PSY.FreezingType AND PSY.Status=1 AND PSY.TypeOfMaterial='HEAD ON'
        where SPD.PoID in ($PoID)   $FilterPoQuery $SearchQuery $GroupQuery  order by abs(VL.Grade) asc  " . $Limit;
        $stmtOrderedKgs = $this->conn->prepare($SqlOrderedKgs);
        $stmtOrderedKgs->execute();

        $TotalCount = $stmtTotals->rowCount();
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $ArrayRequiredRawMaterialReportData = [];
        $TotalsList = '';
        $TotalLoose = '';
        $TotalKgs = '';
        $Filters = '';
        $i = $LimitMax;
        while ($dataRow = $stmtOrderedKgs->fetch(PDO::FETCH_ASSOC)) {
            $OrderedKgs = $dataRow['OrderedKgs'] - $dataRow['AvalKGs'];
            $DisplayColour = '';

            $AvalMcs = $dataRow['AvalMcs'];
            $LooseBags = $dataRow['LooseBags'];
            $AvalKgs = $dataRow['AvalKgs'];
            $dataRow['Plant'] = $RequestBody['Plant'];
            $dataRow['PoID'] = $RequestBody['PoID'];
            $RequestBody = $dataRow;
            $ArrayHeadonData = $this->getHeadondata($RequestBody);

            $FilterSpecies = $dataRow['Species'];
            $FilterGrade = $dataRow['Grade'];
            $FilterShortName = $dataRow['ShortName'];
            $FilterTypeProduct = $dataRow['ProductType'];
            $FilterFreezingType = $dataRow['FreezingType'];
            $FilterGlaze = $dataRow['Glaze'];
            $FilterGlazePerc = $dataRow['GlazePerc'];
            $FilterTreatment = $dataRow['SockingType'];

            $Filters = " AND VL.Species = '$FilterSpecies' AND VL.Grade = '$FilterGrade' AND VL.ShortName = '$FilterShortName' 
            AND VL.TypeProduct = '$FilterTypeProduct' AND VL.FreezingType = '$FilterFreezingType' AND VL.Glaze = '$FilterGlaze' 
            AND VL.Treatment = '$FilterTreatment'";

            $AvailableKgsinStoreNew = $this->getAvailableStorekgsNew($Filters, $StorePlantIDQuery);
            $AvailableFloorBalanceQuantity = $this->getAvailableFloorBalanceQuantity($Filters, $StorePlantIDQuery);

            $StoreQuantity = $AvailableKgsinStoreNew;
            $FloorBalanceQuantity = $AvailableFloorBalanceQuantity;
            // $RequiredKgs = $OrderedKgs - $StoreQuantity;
            $RequiredKgs = $OrderedKgs - ($StoreQuantity + $FloorBalanceQuantity);

            if ($RequiredKgs > 0) {
                $HeadonCount = $ArrayHeadonData['HeadonCount'];
                $Yield = $ArrayHeadonData['Yield'];
                $YieldPercentage = $dataRow['YieldPercentage'];
                $Count = $dataRow['Count'];
                $HeadonQuantityinKgs = ($RequiredKgs / $YieldPercentage) * 100;
                $HeadonQuantityinTons = $HeadonQuantityinKgs / 1000;
                if ($dataRow['GlazePerc'] == '') {
                    $GlazePerc = 0;
                }

                if ($RequiredKgs < 0) { //means excess stock
                    $DisplayColour = 'red';
                }

                $ArrayRequiredRawMaterialData['SNO'] = ++$i;
                $ArrayRequiredRawMaterialData['PoNo'] = $dataRow['PoNo'];
                $ArrayRequiredRawMaterialData['Species'] = $dataRow['Species'];
                $ArrayRequiredRawMaterialData['Grade'] = $dataRow['Grade'];
                $ArrayRequiredRawMaterialData['Variety'] = $dataRow['ShortName'];
                $ArrayRequiredRawMaterialData['ProductForm'] = $dataRow['ProductType'];
                $ArrayRequiredRawMaterialData['FreezingType'] = $dataRow['FreezingType'];
                $ArrayRequiredRawMaterialData['FrozenCol'] = $dataRow['Glaze'];
                $ArrayRequiredRawMaterialData['Glaze'] = $GlazePerc;
                $ArrayRequiredRawMaterialData['Soaked'] = $dataRow['SockingType'];
                $ArrayRequiredRawMaterialData['FloorBalanceQuantity'] = number_format($FloorBalanceQuantity, 2);
                $ArrayRequiredRawMaterialData['StoreQuantity'] = number_format($StoreQuantity, 2);
                $ArrayRequiredRawMaterialData['OrderQuantity'] = number_format($OrderedKgs, 2);
                $ArrayRequiredRawMaterialData['RequiredQuantity'] = number_format($RequiredKgs, 2);
                $ArrayRequiredRawMaterialData['HeadonCount'] = $Count;
                $ArrayRequiredRawMaterialData['QuantityinKgs'] = number_format($HeadonQuantityinKgs, 2);
                $ArrayRequiredRawMaterialData['QuantityinTons'] = number_format($HeadonQuantityinTons, 2);
                $ArrayRequiredRawMaterialData['DisplayColour'] = $DisplayColour;

                $ArrayRequiredRawMaterialReportData[] = $ArrayRequiredRawMaterialData;

                $TotalsList['TotalMcs'] += $AvalMcs;
                $TotalsList['TotalLoose'] += $LooseBags;
                $TotalsList['TotalKgs'] += $AvalKgs;
            } else {
                // Else condition to decrease totals when RequiredKgs is not greater than 0
                $TotalsList['TotalMcs'] -= $AvalMcs;
                $TotalsList['TotalLoose'] -= $LooseBags;
                $TotalsList['TotalKgs'] -= $AvalKgs;
            }
        }
        if (count($ArrayRequiredRawMaterialReportData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';

        $data['TotalCount'] = count($ArrayRequiredRawMaterialReportData);
        $data['RemainingCount'] = "";
        $data['EditLink'] = "";
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        $data['Data'] = $ArrayRequiredRawMaterialReportData;
        $data['Totals'] = $TotalsList;


        return $data;
    }

    function getGroupQuery()
    {
        $GroupQuery = " Group by VL.TypeProduct,VL.ShortName,VL.Glaze,VL.Treatment,VL.Grade,VL.FreezingType,VL.Species,VL.PreparationType";
        $HeadonGroupQuery = " Group by TypeProduct,ShortName,Treatment,Grade,FreezingType,Species";

        $ArraygroupQuery['GroupQuery'] = $GroupQuery;
        $ArraygroupQuery['HeadonGroupQuery'] = $HeadonGroupQuery;
        return $ArraygroupQuery;
    }

    function getFilterQuery($RequestBody)
    {
        $PoID = $RequestBody['PoID'];
        $VarietyID = $RequestBody['VarietyID']; // we are  getting productid  here from  requestbody 
        $Species = $RequestBody['Species'];
        $TypeProduct = $RequestBody['ProductType'];
        $FreezingType = $RequestBody['FreezingType'];
        $ProductionPlantID = $RequestBody['Plant'];
        $Grade = $RequestBody['Grade'];

        /***getting this from query */
        $Treatment = $RequestBody['SockingType'];
        $PreparationType = $RequestBody['PreparationType'];
        $ShortName = $RequestBody['ShortName'];
        $GlazePerc = $RequestBody['GlazePerc'];
        $Glaze = $RequestBody['Glaze'];
        /***getting this from query */

        $Query = '';
        $PoQuery = '';
        $HeadonFilterQuery = '';

        if ($Treatment != "") {
            $PoQuery .= " and VL.Treatment='$Grade'";
            $HeadonFilterQuery .= "  Treatment='$Treatment'";
        }
        if ($GlazePerc != "") {
            $PoQuery .= " and VL.GlazePerc='$GlazePerc'";
        }
        if ($Glaze != "") {
            $PoQuery .= " and VL.Glaze='$Glaze'";
        }

        if ($PreparationType != "") {
            $PoQuery .= " and VL.PreparationType='$PreparationType'";
        }
        if ($ShortName != "") {
            $Query .= " and VL.ShortName='$ShortName'";
            $PoQuery .= " and VL.ShortName='$ShortName'";
            $HeadonFilterQuery .= " and ShortName='$ShortName'";
        }

        if ($Grade != "") {
            $Query .= " and CPID.Grade='$Grade'";
            $PoQuery .= " and VL.Grade='$Grade'";
            $HeadonFilterQuery .= " and Grade='$Grade'";
        }
        if ($VarietyID != "") {
            $SqlVariety = "SELECT GROUP_CONCAT(VarietyID) as VarietyID FROM ScrmPIVarietyList where ProductID='$VarietyID'";
            $stmtVariety = $this->conn->prepare($SqlVariety);
            $stmtVariety->execute();
            $dataRowVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC);
            $VarietyID = $dataRowVariety['VarietyID'];
            if ($VarietyID != 0) {
                $PoQuery .= " and VL.VarietyID in ($VarietyID)";
            }
        }

        if ($Species != "") {
            $Query .= " and VL.Species='$Species'";
            $PoQuery .= " and VL.Species='$Species'";
            $HeadonFilterQuery .= " and Species='$Species'";
        }

        if ($TypeProduct != "") {
            $Query .= " and VL.TypeProduct='$TypeProduct'";
            $PoQuery .= " and VL.TypeProduct='$TypeProduct'";
            $HeadonFilterQuery .= " and TypeProduct='$TypeProduct'";
        }

        if ($FreezingType != "") {
            $Query .= " and VL.FreezingType='$FreezingType'";
            $PoQuery .= " and VL.FreezingType='$FreezingType'";
            $HeadonFilterQuery .= " and FreezingType='$FreezingType'";
        }

        if ($ProductionPlantID != "") {
            $Query .= " and (FIND_IN_SET('$ProductionPlantID', CPID.PlantID) > 0 )";
            $PoQuery .= " and (FIND_IN_SET('$ProductionPlantID', ProductionPlantID) > 0 )";
        }

        if ($PoID != "") {
            $PoQuery .= " and SPD.PoID='$PoID'";
        }

        $ArrayFilterQuery['Query'] = $Query;
        $ArrayFilterQuery['PoQuery'] = $PoQuery;
        $ArrayFilterQuery['HeadonFilterQuery'] = $HeadonFilterQuery;

        return $ArrayFilterQuery;
    }
    function getHeadondata($RequestBody)
    {
        $HeadonFilterQuery = $this->getFilterQuery($RequestBody)['HeadonFilterQuery'];
        $HeadonGroupQuery = $this->getGroupQuery()['HeadonGroupQuery'];
        $SqlHeadonData = " SELECT   `HeadonCount`, `Yield`  FROM `CsHeadonRequirementMasters` WHERE   $HeadonFilterQuery  $HeadonGroupQuery";
        $stmtHeadonData = $this->conn->prepare($SqlHeadonData);
        $stmtHeadonData->execute();
        $dataRowHeadonData = $stmtHeadonData->fetch(PDO::FETCH_ASSOC);
        $ArrayHeadonData['HeadonCount'] = $dataRowHeadonData['HeadonCount'];
        $ArrayHeadonData['Yield'] = $dataRowHeadonData['Yield'];
        return $ArrayHeadonData;
    }

    function getAvailableStorekgsNew($Filters, $StorePlantIDQuery)
    {
        $sqlSelect = "SELECT sum(AvalKgs) as AvailableKgsinStore FROM `CsStockData` as CPID 
        LEFT JOIN `ScrmPIVarietyList` as VL on VL.VarietyID=CPID.VarietyID                
        where  (CPID.AvalMcs!=0 or CPID.LooseBags !=0) and PackingTypeID='DUMMY' $Filters $StorePlantIDQuery";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $AvailableKgsinStore = $dataRow['AvailableKgsinStore'];
        return $AvailableKgsinStore;
    }
    function getAvailableFloorBalanceQuantity($Filters, $StorePlantIDQuery)
    {
        $SqlFields = "RRD.ReceivingID,group_concat(CsStockID) as StockID, sum(ReceivingQty) as ReceivingQty,
        (SELECT SUM(SentWeight) FROM `PpcValueAddition` as PVA WHERE PVA.TrackID=RRD.ReceivingID and RRD.OutStage=1 ) as VAWeight ,
        (SELECT sum(SoakInQty) FROM `ProdSoakingDetails` WHERE TrackID=RRD.ReceivingID and RRD.OutStage=2 ) as SoakingQunatity,
        (SELECT sum(ProductionWeight) FROM `ProdFreezingData` where TrackID=RRD.ReceivingID and RRD.OutStage=2 )as FreezingWeight";

        $sqlGetFloorBalanceQuantity = "SELECT  $SqlFields
        FROM `RmReceivingDetails` as RRD left join RmReceivingBreakup as RRB on RRB.ReceivingID =RRD.ReceivingID 
        left join `CsStockData` as CPID on CPID.StockID=RRB.CsStockID
         LEFT JOIN `ScrmPIVarietyList` as VL on VL.VarietyID=CPID.VarietyID
        WHERE IsStoreMaterial=1 and (CPID.AvalMcs!=0 or CPID.LooseBags !=0)  $Filters $StorePlantIDQuery";

        $stmtGetFloorBalanceQuantity = $this->conn->prepare($sqlGetFloorBalanceQuantity);
        $stmtGetFloorBalanceQuantity->execute();
        $dataRowGetFloorBalanceQuantity = $stmtGetFloorBalanceQuantity->fetch(PDO::FETCH_ASSOC);
        $InQuantity = $dataRowGetFloorBalanceQuantity['VAWeight'] + $dataRowGetFloorBalanceQuantity['SoakingQunatity'] + $dataRowGetFloorBalanceQuantity['FreezingWeight'];
        $FloorBalanceQuantity = $dataRowGetFloorBalanceQuantity['ReceivingQty'] - $InQuantity;
        return $FloorBalanceQuantity;
    }
}
