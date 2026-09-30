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
        $PackData = $RequestBody['PackData'];
        $Group = $RequestBody['Group'];
        $Category = $RequestBody['Category'];
        $FreezingType = $RequestBody['FreezingType'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $Grade = $RequestBody['Grade'];
        $PONumber = $RequestBody['PONumber'];
        $VarietyID = $RequestBody['VarietyID'];
        $SName = $RequestBody['VarietyID'];
        $AntiBiotic = $RequestBody['AntiBiotic'];
        $Soaking = $RequestBody['Soaking'];
        $TenantID = $RequestBody['TenantID'];
        $SearchKey = $RequestBody['SearchKey'];
        $Species = $RequestBody['Species'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $ClosingDate = $RequestBody['ClosingDate'];
        $Brand = $RequestBody['Brand'];
        $Blocks = $RequestBody['Blocks'];

        $PageNo = $RequestBody['PageNo'];
        $OutputType = $RequestBody['Type'];
        $LoadAll = $RequestBody['LoadAll'];

        $totalMCS = 0;
        $totalKGs = 0;
        $overAllMcs = 0;

        $Query = '';

        if ($ColdstoreID != '') {
            $Query .= "AND CPID.`UnitID`='$ColdstoreID'";
        }
        if ($PackData != '') {
            $Query = "AND CPID.`PackingTypeID`='$PackData'";
        }
        if ($Group != '') {
            $Query .= "AND CPID.`ProductionTypeID`='$Group'";
        }
        if ($PackingStyle != '') {
            $Query .= "AND CPID.`PackingStyleID`='$PackingStyle'";
        }
        if ($Grade != '') {
            $Query .= "AND CPID.`Grade`='$Grade'";
        }
        if ($Category != '') {
            $Query .= "AND CPID.`ProductCategoryID`='$Category'";
        }
        if ($FreezingType != '') {
            $Query .= "AND CPID.`FreezingType`='$FreezingType'";
        }
        if ($PONumber != '') {
            $Query .= "AND CPID.`PoID`='$PONumber'";
        }
        if ($VarietyID != '') {
            $Query .= "AND PIVL.ShortName='$VarietyID'";
        }
        if ($Brand != '') {
            $Query .= " AND PIVL.`BrandName`='$Brand'";
        }
        if ($AntiBiotic != '') {
            $Query .= " AND CPID.`AbStatus` = '$AntiBiotic' ";
        }

        if ($Soaking != '') {
            $Query .= " AND CPID.`SockingType`='$Soaking'";
        }

        if ($TenantID != '') {
            $Query .= " AND CPID.StockHolderID='$TenantID'";
        }

        if ($Species != '') {
            $Query .= " AND PIVL.Species='$Species'";
        }
        if ($TypeProduct != '') {
            $Query .= " AND PIVL.TypeProduct='$TypeProduct'";
        }

        if ($ClosingDate != '') {
            $Query .= " AND CPID.TransactionDate<='$ClosingDate'";
        }
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
        PIVL.TypeProduct,CPS.PackingStyle,PIVL.GlazePerc,PIVL.Treatment as Soaking, CPID.TransactionDate,
        PackingStyleID, CPID.PackingTypeID as PackingType,SUM(AvalMcs) as MCS ,PIVL.Glaze , ProductCategoryID,
        SUM(LooseBags) as LooseBags , SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGS, 
        SUM(LooseBags*SlabWeight*MFactor) as LKGS, SBM.BrandName,CPID.UnitID, CPID.FreezingType";

        $sqlQuery = "SELECT $SqlFields FROM `CsStockData` AS CPID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
        LEFT JOIN ScrmBrandsMaster AS SBM ON SBM.BrandID=PIVL.BrandName
        LEFT JOIN `CsBarCodes` as cb   on cb.BarCodeID = CPID.BarCodeID
        LEFT JOIN CsStoresRackData as CSRD on CSRD.RowID=CPID.StoreRackID
        LEFT JOIN CsStoresBlockData AS CSBD on CSBD.BlockID=CSRD.BlockID
        WHERE  cb.BarCodeStatus=1 and (AvalMcs !=0 OR LooseBags!=0) $Query
        group by CPID.PackingTypeID,PIVL.ShortName,PIVL.Species,PIVL.TypeProduct,CPID.PackingStyleID,
        PIVL.GlazePerc,PIVL.Treatment,PIVL.GlazePerc
        ORDER BY PIVL.ShortName ASC "; 

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $TotalCount = $stmtSelect->rowCount();

        $SNo = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $Species=$dataRow['Species'];
            $ProductID = $dataRow['ProductID'];
            $TypeProduct = $dataRow['TypeProduct'];
            $StockIDs = $dataRow['StockIDs'];

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
            $TopRow['UnitID'] = $dataRow['UnitID'];
            $TopRow['Glaze'] = $dataRow['Glaze'];
            $TopRow['GlazePerc'] = $dataRow['GlazePerc'];
            $TopRow['TransactionDate'] = $dataRow['TransactionDate'];
            $TopRow['FreezingType'] = $dataRow['FreezingType'];
            $TopRow['Brand'] = $dataRow['BrandName'];
            $TopRow['PackingStyleID'] = $PackStyleId;
            $TopRow['PackingStyle'] = $PackingStyle['PackingStyle']; //  Removed Because Showing Wrong Packing Style 
            $TopRow['PackingStyle'] = $dataRow['PackingStyle'];

            $AryGradeValues = array();
            $ProductMcs = 0;
            $ProductLooseBags = 0;
            $ProductTotalKgs = 0;

            foreach ($AryGardesNames as $Grades) {

                $sqlFields = "CPS.PackingStyle,
                PackingStyleID, CPID.PackingTypeID as PackingType,SUM(AvalMcs) as MCS ,
                SUM(LooseBags) as LooseBags , SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGS, 
                SUM(LooseBags*SlabWeight*MFactor) as LKGS";

                $sqlFormMCS = "SELECT  $sqlFields FROM `CsStockData` AS CPID 
                LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID 
                WHERE CPID.StockID in ($StockIDs) AND PackingStyleID='$PackStyleId' AND CPID.Grade ='$Grades' ";

                $stmtFormMCS = $this->conn->prepare($sqlFormMCS);
                $stmtFormMCS->execute();

                $dataFormMCS = $stmtFormMCS->fetch(PDO::FETCH_ASSOC);

                $KGS = $dataFormMCS['KGS'];
                $LKGS = $dataFormMCS['LKGS'];
                $MCS = $dataFormMCS['MCS'];
                $LOOSE = $dataFormMCS['LooseBags'];

                if ($OutputType == 'MCS') {
                    $GradeValue = $MCS . '/' . $LOOSE;
                } else if ($OutputType == 'KGs') {
                    $GradeValue = round(($KGS + $LKGS), 2);
                }
                if ($GradeValue == "/") {
                    $GradeValue = "";
                }
                $gradeItem['GradeName'] = $Grades;
                $gradeItem['GradeValue'] = $GradeValue;
                $gradeItem['GradeMcs'] = $MCS;
                $gradeItem['GradeLooseBags'] = $LOOSE;
                $gradeItem['GradeKgs'] = $KGS + $LKGS;
                $AryGradeValues[] = $gradeItem;

                $ProductMcs += $MCS;
                $ProductLooseBags += $LOOSE;
                $ProductTotalKgs += ($KGS + $LKGS);
            }

            $TopRow['SubGrades'] = $AryGradeValues;
            $TopRow['ProductMcs'] = $ProductMcs;
            $TopRow['ProductLooseBags'] = $ProductLooseBags;
            $TopRow['ProductTotalKgs'] = round($ProductTotalKgs, 2);

            $ArrayStoreData[] = $TopRow;
        }

        foreach ($AryGardesNames as $refGrade) {
            $GradeWeight = 0;
            $GradeMcs = 0;
            $GradeLooseBags = 0;

            foreach ($ArrayStoreData as $Item) {
                foreach ($Item['SubGrades'] as $SubItem) {

                    if ($SubItem['GradeName'] == $refGrade) {
                        $SubItem['GradeName'];
                        $GradeWeight += $SubItem['GradeKgs'];
                        $GradeMcs += $SubItem['GradeMcs'];
                        $GradeLooseBags += $SubItem['GradeLooseBags'];
                    }
                }
            }

            if ($OutputType == 'MCS') {
                $GradeValue = $GradeMcs . '/' . $GradeLooseBags;
            } else if ($OutputType == 'KGs') {
                $GradeValue = round(($GradeWeight), 2);
            }
            if ($GradeValue == "/") {
                $GradeValue = "";
            }


            $totalgradeItem['GradeName'] = $refGrade;
            $totalgradeItem['GradeValue'] = $GradeValue;

            $ArrayStockTotals[] = $totalgradeItem;
        }

        foreach ($ArrayStoreData as $Item) {
            foreach ($Item['SubGrades'] as $SubItem) {
                $SubItem['GradeName'];
                $TotalWeight += $SubItem['GradeKgs'];
                $TotalMcs += $SubItem['GradeMcs'];
                $TotalLooseBags += $SubItem['GradeLooseBags'];
            }

            $StockTotals['TotalWeight'] = round($TotalWeight, 2);
            $StockTotals['TotalMcs'] = $TotalMcs;
            $StockTotals['TotalLooseBags'] = $TotalLooseBags;
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
