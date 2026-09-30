<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class StockReportController
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

    function getStockReportDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $PackDatas = $RequestBody['PackData'];
        $Group = $RequestBody['Group'];
        $Categorys = $RequestBody['Category'];
        $FreezingTypes = $RequestBody['FreezingType'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $Grades = $RequestBody['Grade'];
        $PONumber = $RequestBody['PONumber'];
        $VarietyIDs = $RequestBody['VarietyID'];
        $SName = $RequestBody['VarietyID'];
        $AntiBiotic = $RequestBody['AntiBiotic'];
        $Soakings = $RequestBody['Soaking'];
        $TenantID = $RequestBody['TenantID'];
        $SearchKey = $RequestBody['SearchKey'];
        $Species = $RequestBody['Species'];
        $TypeProducts = $RequestBody['TypeProduct'];
        // $ClosingDate = $RequestBody['ClosingDate'];
        $Brand = $RequestBody['Brand'];
        $Blocks = $RequestBody['Blocks'];
        $ConvertLooseToMcs = $RequestBody['ConvertLooseToMcs'];

        $PageNo = $RequestBody['PageNo'];
        $OutputType = $RequestBody['Type'];
        $LoadAll = $RequestBody['LoadAll'];

        $totalMCS = 0;
        $totalKGs = 0;
        $overAllMcs = 0;

        $Query = '';

        if ($ColdstoreID != '') {
            $Query .= "AND CPID.`UnitID` IN ($ColdstoreID)";
        }
        if ($PackDatas != '') {
            // $Query .= "AND CPID.`PackingTypeID`='$PackData'";
             $PackData = "'" . implode("','", array_map('trim', explode(',', $PackDatas))) . "'";
            $Query .= " and CPID.PackingTypeID IN ($PackData)";
        }
        if ($Group != '') {
            $Query .= "AND CPID.`ProductionTypeID`='$Group'";
        }
        if ($PackingStyle != '') {
            $Query .= "AND CPID.`PackingStyleID` IN ($PackingStyle)";
        }
        if ($Grades != '') {
            // $Query .= "AND CPID.`Grade`='$Grade'";
             $Grade = "'" . implode("','", array_map('trim', explode(',', $Grades))) . "'";
            $Query .= " and CPID.Grade IN ($Grade)";
        }
        if ($Categorys != '') {
            // $Query .= "AND CPID.`ProductCategoryID` IN ($Category)";
            $Category = "'" . implode("','", array_map('trim', explode(',', $Categorys))) . "'";
            $Query .= " and CPID.ProductCategoryID IN ($Category)";
        }
        if ($FreezingTypes != '') {
            // $Query .= "AND CPID.`FreezingType`='$FreezingType'";
             $FreezingType = "'" . implode("','", array_map('trim', explode(',', $FreezingTypes))) . "'";
            $Query .= " and CPID.FreezingType IN ($FreezingType)";
        }
        if ($PONumber != '') {
            $Query .= "AND CPID.`PoID`='$PONumber'";
        }
        if ($VarietyIDs != '') {
            // $Query .= "AND PIVL.ShortName IN ($VarietyID)";
            $VarietyID = "'" . implode("','", array_map('trim', explode(',', $VarietyIDs))) . "'";
            $Query .= " AND PIVL.ShortName IN ($VarietyID)";
        }
        if ($Brand != '') {
            $Query .= " AND PIVL.`BrandName`='$Brand'";
        }
        if ($AntiBiotic != '') {
            $Query .= " AND CPID.`AbStatus` = '$AntiBiotic' ";
        }

        if ($Soakings != '') {
            // $Query .= " AND CPID.`SockingType` IN ($Soaking)";
            $Soaking = "'" . implode("','", array_map('trim', explode(',', $Soakings))) . "'";
            $Query .= " and CPID.SockingType IN ($Soaking)";
        }

        if ($TenantID != '') {
            $Query .= " AND CPID.StockHolderID IN ($TenantID)";
        }

        if ($Species != '') {
            $Query .= " AND PIVL.Species='$Species'";
        }
        if ($TypeProducts != '') {
            // $Query .= " AND PIVL.TypeProduct='$TypeProduct'";
             $TypeProduct = "'" . implode("','", array_map('trim', explode(',', $TypeProducts))) . "'";
            $Query .= " and PIVL.TypeProduct IN ($TypeProduct)";
        }

        // if ($ClosingDate != '') {
        //     $Query .= " AND CPID.ProductionDate<='$ClosingDate'";
        // }

        if ($Blocks != "") {
            $Query .= " and CSBD.BlockID='$Blocks'";
        }

        $AryGardesNames = $this->getGradeNames($Query);

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

       if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', CPID.VarietyID, ShortName, PackingStyle, AvalMcs, LooseBags) LIKE '%$SearchKey%'";
        }

        $SqlFields = "GROUP_CONCAT(CPID.StockID) as StockIDs,GROUP_CONCAT(DISTINCT(CPID.VarietyID)) as VarietyID,PIVL.ProductDescription,PIVL.Species,PIVL.ShortName,PIVL.ProductID,
        PIVL.TypeProduct,CPS.PackingStyle,PIVL.GlazePerc,PIVL.Treatment as Soaking, CPID.ProductionDate,CPID.AbStatus,
        PackingStyleID, CPID.PackingTypeID as PackingType,SUM(AvalMcs) as MCS ,PIVL.Glaze , ProductCategoryID,
        SUM(LooseBags) as LooseBags ,GROUP_CONCAT(LooseBags) AS mcscheck,SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGS, 
        SUM(LooseBags*SlabWeight*MFactor) as LKGS, SBM.BrandName,CPID.UnitID, CPID.FreezingType,SBM.BrandID AS BrandID";

        $sqlQuery = "SELECT $SqlFields FROM `CsStockData` AS CPID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
        LEFT JOIN ScrmBrandsMaster AS SBM ON SBM.BrandID=PIVL.BrandName
        LEFT JOIN `CsBarCodes` as cb   on cb.BarCodeID = CPID.BarCodeID
        LEFT JOIN CsStoresRackData as CSRD on CSRD.RowID=CPID.StoreRackID
        LEFT JOIN CsStoresBlockData AS CSBD on CSBD.BlockID=CSRD.BlockID
        WHERE (AvalMcs !=0 OR LooseBags!=0) $Query
        group by CPID.PackingTypeID,PIVL.ShortName,PIVL.Species,PIVL.TypeProduct,CPID.PackingStyleID,
        CPID.ProductCategoryID,CPID.AbStatus,PIVL.Glaze,CPID.SockingType,PIVL.GlazePerc,PIVL.BrandName,CPID.FreezingType
        ORDER BY PIVL.ShortName ASC ";
        // echo print_r($sqlQuery);exit;
        // WHERE  cb.BarCodeStatus=1 and (AvalMcs !=0 OR LooseBags!=0) $Query
        // echo $sqlQuery;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $TotalCount = $stmtSelect->rowCount();

        $SNo = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $Species = $dataRow['Species'];
            $ProductID = $dataRow['ProductID'];
            $TypeProduct = $dataRow['TypeProduct'];
            $StockIDs = $dataRow['StockIDs'];
            // echo $dataRow['mcscheck'];
            $VarietyID = $dataRow['VarietyID'];
            $PackStyleId = $dataRow['PackingStyleID'];
            $TopRow['SNo'] = ++$SNo;
            $TopRow['VarietyID'] = $dataRow['VarietyID'];
            $TopRow['ProductDescription'] = $dataRow['ProductDescription'];
            $TopRow['Variety'] = $dataRow['ShortName'];
            $TopRow['Species'] = $dataRow['Species'];
            $TopRow['TypeProduct'] = $dataRow['TypeProduct'];
            $TopRow['PackingType'] = $dataRow['PackingType'];
            $TopRow['Soaking'] = $dataRow['Soaking'];
            $TopRow['ProductCategory'] = $dataRow['ProductCategoryID'];
            $TopRow['AbStatus'] = $dataRow['AbStatus'];
            $TopRow['UnitID'] = $dataRow['UnitID'];
            $TopRow['Glaze'] = $dataRow['Glaze'];
            $TopRow['GlazePerc'] = $dataRow['GlazePerc'];
            $TopRow['ProductionDate'] = $dataRow['ProductionDate'];
            $TopRow['FreezingType'] = $dataRow['FreezingType'];
            $TopRow['Brand'] = $dataRow['BrandName'];
            $TopRow['BrandID'] = $dataRow['BrandID'];
            $TopRow['PackingStyleID'] = $PackStyleId;
            $TopRow['PackingStyle'] = $dataRow['PackingStyle'];

            $AryGradeValues = array();
            $ProductMcs = 0;
            $ProductLooseBags = 0;
            $ProductTotalKgs = 0;
            $ProductSlabs = 0;

            foreach ($AryGardesNames as $Grades) {

                $sqlFields = "CPS.PackingStyle,
                PackingStyleID, CPID.PackingTypeID as PackingType,SUM(AvalMcs) as MCS ,
                SUM(LooseBags) as LooseBags , SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGS, 
                SUM(LooseBags*SlabWeight*MFactor) as LKGS, FLOOR(SUM(LooseBags)/TotalSlabs) AS LooseMcs, 
                SUM(LooseBags) % TotalSlabs AS RemainLooseBags, SUM(AvalMcs*TotalSlabs) AS McsSlabs,CPS.TotalSlabs,SUM(CPID.AvalKgs) AS AvalKgs";

                $sqlFormMCS = "SELECT  $sqlFields FROM `CsStockData` AS CPID 
                LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID 
                WHERE CPID.StockID in ($StockIDs) AND PackingStyleID='$PackStyleId' AND CPID.Grade ='$Grades' ";
                // echo $sqlFormMCS;exit;
                $stmtFormMCS = $this->conn->prepare($sqlFormMCS);
                $stmtFormMCS->execute();

                $dataFormMCS = $stmtFormMCS->fetch(PDO::FETCH_ASSOC);

                $KGS = $dataFormMCS['KGS'];
                $LKGS = $dataFormMCS['LKGS'];
                $AvalKgs = $dataFormMCS['AvalKgs'];
                $MCS = $dataFormMCS['MCS'];
                $LOOSE = $dataFormMCS['LooseBags'];
                $Slabs = $dataFormMCS['McsSlabs'] + $dataFormMCS['LooseBags']; // 
                $TotalSlabs = $dataFormMCS['TotalSlabs'];

                $MCS = $dataFormMCS['MCS'];
                $MCS = $MCS ? $MCS : '0';
                // $LOOSE = $dataFormMCS['RemainLooseBags'];

                if ($OutputType == 'MCS') {
                    if($ConvertLooseToMcs == 1){
                        $LooseMcs = FLOOR($LOOSE/$TotalSlabs);
                        // $LOOSE = ($LOOSE % $TotalSlabs);
                        $LOOSE = $LOOSE - ($LooseMcs * $TotalSlabs);
                        $MCS = $MCS + $LooseMcs;
                    }
                    $GradeValue = $MCS . '/' . $LOOSE;
                } else if ($OutputType == 'KGS') {
                    // $GradeValue = round(($KGS + $LKGS), 2);
                    $GradeValue = round(($AvalKgs), 2);
                } else if ($OutputType == 'Slabs') {
                    $GradeValue = $Slabs;
                }
                if ($GradeValue == "0/") {
                    $GradeValue = "";
                } else if ($GradeValue == "/") {
                    $GradeValue = "";
                } else if ($GradeValue == "0/0") {
                    $GradeValue = "";
                }
                $gradeItem['GradeName'] = $Grades;
                $gradeItem['GradeValue'] = $GradeValue;
                $gradeItem['GradeMcs'] = $MCS;
                $gradeItem['GradeLooseBags'] = $LOOSE;
                $gradeItem['GradeKgs'] = $AvalKgs;
                $gradeItem['GradeSlabs'] = $Slabs; // 
                $AryGradeValues[] = $gradeItem;

                $ProductMcs += $MCS;
                $ProductLooseBags += $LOOSE;
                $ProductTotalKgs += ($AvalKgs);
                $ProductSlabs += $Slabs; //
            }

            $TopRow['SubGrades'] = $AryGradeValues;
            $TopRow['ProductMcs'] = $ProductMcs;
            $TopRow['ProductLooseBags'] = $ProductLooseBags;
            $TopRow['ProductTotalKgs'] = round($ProductTotalKgs, 2);
            $TopRow['ProductSlabs'] = $ProductSlabs;

            $ArrayStoreData[] = $TopRow;
        }

        foreach ($AryGardesNames as $refGrade) {
            $GradeWeight = 0;
            $GradeMcs = 0;
            $GradeLooseBags = 0;
            $GradeSlabs = 0;

            foreach ($ArrayStoreData as $Item) {
                foreach ($Item['SubGrades'] as $SubItem) {

                    if ($SubItem['GradeName'] == $refGrade) {
                        $SubItem['GradeName'];
                        $GradeWeight += $SubItem['GradeKgs'];
                        $GradeMcs += $SubItem['GradeMcs'];
                        $GradeLooseBags += $SubItem['GradeLooseBags'];
                        $GradeSlabs += $SubItem['GradeSlabs'];
                    }
                }
            }

            if ($OutputType == 'MCS') {
                $GradeValue = $GradeMcs . '/' . $GradeLooseBags;
            } else if ($OutputType == 'KGS') {
                $GradeValue = round(($GradeWeight), 2);
            } else if ($OutputType == 'Slabs') {
                $GradeValue = $GradeSlabs;
            }
            if ($GradeValue == "/") {
                $GradeValue = "";
            }

            $totalgradeItem['GradeName'] = $refGrade;
            $totalgradeItem['GradeValue'] = $GradeValue;
            $totalgradeItem['GradeMcs'] = $GradeMcs;
            $totalgradeItem['GradeLooseBags'] = $GradeLooseBags;
            $totalgradeItem['GradeWeight'] = $GradeWeight;

            $ArrayStockTotals[] = $totalgradeItem;
        }

        foreach ($ArrayStoreData as $Item) {
            foreach ($Item['SubGrades'] as $SubItem) {
                $SubItem['GradeName'];
                $TotalWeight += $SubItem['GradeKgs'];
                $TotalMcs += $SubItem['GradeMcs'];
                $TotalLooseBags += $SubItem['GradeLooseBags'];
                $TotalLSlabs += $SubItem['GradeSlabs'];
            }

            $StockTotals['TotalWeight'] = round($TotalWeight, 2);
            $StockTotals['TotalMcs'] = $TotalMcs;
            $StockTotals['TotalLooseBags'] = $TotalLooseBags;
            $StockTotals['TotalLSlabs'] = $TotalLSlabs;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['Grades'] = $AryGardesNames;
        $data['Data'] = $ArrayStoreData;
        $data['StockTotal'] = $ArrayStockTotals;
        $data['ProductTotal'] = $StockTotals;

        return $data;
    }

    public function getGradeNames($Query)
    {

        $sqlGrades = "SELECT DISTINCT CPID.Grade as GradeName  FROM `CsStockData` as CPID
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN CsStoresRackData as CSRD on CSRD.RowID=CPID.StoreRackID
        LEFT JOIN CsStoresBlockData AS CSBD on CSBD.BlockID=CSRD.BlockID
        WHERE (AvalMcs!=0 or LooseBags!=0)  $Query order by abs(CPID.Grade) asc";
        $stmtGrades = $this->conn->prepare($sqlGrades);
        $stmtGrades->execute();

        $Data = array();
        while ($dataGrade = $stmtGrades->fetch(PDO::FETCH_ASSOC)) {
            $GradeName = $dataGrade['GradeName'];
            $Data[] = $GradeName;
        }
        return $Data;
    }

    public function getPackingStyle($VarietyID)
    {
        $sqlPackingStyle = "SELECT DISTINCT PackingStyleID,PackingStyle FROM `CsStockData` AS CPID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPID.PackingStyleID = CPS.PCKID 
        WHERE VarietyID in ($VarietyID) ORDER BY ABS(PackingStyle) ASC";

        $stmtPackingStyle = $this->conn->prepare($sqlPackingStyle);
        $stmtPackingStyle->execute();

        while ($dataRow = $stmtPackingStyle->fetch(PDO::FETCH_ASSOC)) {
            $Data['PackingStyleID'] = $dataRow['PackingStyleID'];
            $Data['PackingStyle'] = $dataRow['PackingStyle'];
        }
        return $Data;
    }
}
