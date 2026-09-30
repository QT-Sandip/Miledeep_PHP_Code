<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class ExcessStockReportGradewiseController
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

    function getExcessStockReportDetails($RequestBody)
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
        $Species = $RequestBody['Species'];
        $TypeProduct = $RequestBody['TypeProduct'];
        // $ClosingDate = $RequestBody['ClosingDate'];
        $Brand = $RequestBody['Brand'];
        $Blocks = $RequestBody['Blocks'];
        $OutputType = $RequestBody['Type'];

        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $totalMCS = 0;
        $totalKGs = 0;
        $overAllMcs = 0;

        $Query = '';
        $PoQuery = '';

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
            $PoQuery .= " AND SPD.PoID = '$PONumber'";
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

        $sqlCount = "SELECT COUNT(*) AS Total FROM `CsStockData` AS CPID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
        LEFT JOIN ScrmBrandsMaster AS SBM ON SBM.BrandID=PIVL.BrandName
        LEFT JOIN `CsBarCodes` as cb   on cb.BarCodeID = CPID.BarCodeID
        LEFT JOIN CsStoresRackData as CSRD on CSRD.RowID=CPID.StoreRackID
        LEFT JOIN CsStoresBlockData AS CSBD on CSBD.BlockID=CSRD.BlockID
        WHERE 1=1 $Query AND CPID.PackingTypeID = 'DUMMY'
        GROUP BY PIVL.ShortName,PIVL.Species,PIVL.TypeProduct,CPID.PackingStyleID,
        CPID.ProductCategoryID,CPID.AbStatus,PIVL.GlazePerc,PIVL.Treatment,PIVL.GlazePerc
        ORDER BY PIVL.ShortName ASC ";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $TotalCount = $stmtCount->rowCount();
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $SqlFields = "GROUP_CONCAT(CPID.StockID) as StockIDs,GROUP_CONCAT(DISTINCT(CPID.VarietyID)) as VarietyID,PIVL.ProductDescription,PIVL.Species,PIVL.ShortName,PIVL.ProductID,
        PIVL.TypeProduct,CPS.PackingStyle,PIVL.GlazePerc,PIVL.Treatment as Soaking, CPID.ProductionDate,CPID.AbStatus,
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
        WHERE (CPID.AvalMcs!=0 OR CPID.LooseBags !=0) $Query AND CPID.PackingTypeID = 'DUMMY'
        GROUP BY PIVL.ShortName,PIVL.Species,PIVL.TypeProduct,CPID.PackingStyleID,
        CPID.ProductCategoryID,CPID.AbStatus,PIVL.GlazePerc,PIVL.Treatment,PIVL.GlazePerc
        ORDER BY PIVL.ShortName ASC " . $Limit;
        // ORDER BY PIVL.ShortName ASC " . $Limit;
        // WHERE 1=1 $Query AND CPID.PackingTypeID = 'DUMMY'
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $SNo = 0;
        $FilteredGrades = [];

        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $Species = $dataRow['Species'];
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
            $TopRow['AbStatus'] = $dataRow['AbStatus'];
            $TopRow['UnitID'] = $dataRow['UnitID'];
            $TopRow['Glaze'] = $dataRow['Glaze'];
            $TopRow['GlazePerc'] = $dataRow['GlazePerc'];
            $TopRow['ProductionDate'] = $dataRow['ProductionDate'];
            $TopRow['FreezingType'] = $dataRow['FreezingType'];
            $TopRow['Brand'] = $dataRow['BrandName'];
            $TopRow['PackingStyleID'] = $PackStyleId;
            $TopRow['PackingStyle'] = $dataRow['PackingStyle'];

            $AryGradeValues = array();
            // $FilteredGrades = [];
            $ProductMcs = 0;
            $ProductTotalKgs = 0;
            $ProductSlabs = 0;

            $FilterSpecies = $dataRow['Species'];
            $FilterShortName = $dataRow['ShortName'];
            $FilterTypeProduct = $dataRow['TypeProduct'];
            $FilterFreezingType = $dataRow['FreezingType'];
            $FilterGlaze = $dataRow['Glaze'];
            $FilterTreatment = $dataRow['Soaking'];
            $FilterGlazePerc = $dataRow['GlazePerc'];
            $FilterPackingStyle = $dataRow['PackingStyleID'];

            // $GradeValue = 0;
            foreach ($AryGardesNames as $Grades) {

                $sqlFields = "CPS.PackingStyle,
                PackingStyleID, CPID.PackingTypeID as PackingType,SUM(AvalMcs) as MCS ,
                SUM(LooseBags) as LooseBags , SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGS, 
                SUM(LooseBags*SlabWeight*MFactor) as LKGS, FLOOR(SUM(LooseBags)/TotalSlabs) AS LooseMcs, 
                SUM(LooseBags) % TotalSlabs AS RemainLooseBags, SUM(AvalMcs*TotalSlabs) AS McsSlabs";

                $sqlFormMCS = "SELECT  $sqlFields FROM `CsStockData` AS CPID 
                LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID 
                WHERE CPID.StockID in ($StockIDs) AND PackingStyleID='$PackStyleId' AND CPID.Grade ='$Grades' ";
                $stmtFormMCS = $this->conn->prepare($sqlFormMCS);
                $stmtFormMCS->execute();

                $FilterQuery = " AND SPVL.Species = '$FilterSpecies' AND SPVL.Grade = '$Grades' AND SPVL.ShortName = '$FilterShortName'
                AND SPVL.TypeProduct = '$FilterTypeProduct' AND SPVL.FreezingType = '$FilterFreezingType' AND SPVL.Glaze = '$FilterGlaze'
                AND SPVL.Treatment = '$FilterTreatment' AND SPVL.GlazePerc = '$FilterGlazePerc' AND SPVL.PackingStyle = '$FilterPackingStyle'
                $PoQuery";
                $OrderedData = $this->getOrderedDetails($FilterQuery);

                // var_dump($OrderedData);

                $OrderedMcs = $OrderedData['OrderedMcs'];
                $OrderedKgs = $OrderedData["OrderedKGs"];
                $OrderedSlabs = $OrderedData['OrderedSlabs'];

                $dataFormMCS = $stmtFormMCS->fetch(PDO::FETCH_ASSOC);

                $KGS = 0; // $dataFormMCS['KGS'];
                $LKGS = 0; // $dataFormMCS['LKGS'];

                $KGS = $dataFormMCS['KGS'];
                $LKGS = $dataFormMCS['LKGS'];

                $BalanceMcs = $dataFormMCS['MCS'] - $OrderedMcs;
                $StoreKgs = ($KGS + $LKGS);
                $BalanceKGs = ($KGS + $LKGS) - $OrderedKgs;
                $BalanceSlabs = ($dataFormMCS['McsSlabs'] + $dataFormMCS['LooseBags']) - $OrderedSlabs;

                if ($OutputType == 'MCS') {
                    $Balance = $BalanceMcs;
                } else if ($OutputType == 'KGs') {
                    $Balance = $BalanceMcs;
                }

                if ($OutputType == 'MCS') {
                    $GradeValue = $BalanceMcs;
                } else if ($OutputType == 'KGs') {
                    $GradeValue = round($BalanceKGs, 3);
                } else if ($OutputType == 'Slabs') {
                    $GradeValue = $BalanceSlabs;
                }


                if ($GradeValue == "0/") {
                    $GradeValue = "";
                } else if ($GradeValue == "/") {
                    $GradeValue = "";
                } else if ($GradeValue == "0/0") {
                    $GradeValue = "";
                }
                // echo $BalanceMcs . '|';
                if ($StoreKgs > $OrderedKgs) {
                    $gradeItem['GradeName'] = $Grades;
                    $gradeItem['GradeValue'] = $GradeValue;
                    $gradeItem['GradeMcs'] = $BalanceMcs;
                    $gradeItem['GradeKgs'] = $BalanceKGs;
                    $gradeItem['GradeSlabs'] = $BalanceSlabs; // 
                    $AryGradeValues[] = $gradeItem;
                    if (!in_array($Grades, $FilteredGrades, true)) {
                        $FilteredGrades[] = $Grades;
                    }

                    $ProductMcs += $BalanceMcs;
                    $ProductTotalKgs += $BalanceKGs;
                    $ProductSlabs += $BalanceSlabs; //
                } else {
                    $gradeItem['GradeName'] = $Grades;
                    $gradeItem['GradeValue'] = 0;
                    $gradeItem['GradeMcs'] = 0;
                    $gradeItem['GradeKgs'] = 0;
                    $gradeItem['GradeSlabs'] = 0; // 
                    $AryGradeValues[] = $gradeItem;
                }
            }


            $TopRow['SubGrades'] = $AryGradeValues;
            $TopRow['ProductMcs'] = $ProductMcs;
            $TopRow['ProductTotalKgs'] = round($ProductTotalKgs, 2);
            $TopRow['ProductSlabs'] = $ProductSlabs;

            if ($ProductTotalKgs > 0) {
                $ArrayStoreData[] = $TopRow;
            }
        }

        $itemsToStayFlipped = array_flip($FilteredGrades);

        $result = array_filter($AryGardesNames, function ($item) use ($itemsToStayFlipped) {
            return isset($itemsToStayFlipped[$item]);
        });
        foreach ($result as $refGrade) {
            // foreach ($AryGardesNames as $refGrade) {
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
                $GradeValue = $GradeMcs;
            } else if ($OutputType == 'KGs') {
                $GradeValue = round(($GradeWeight), 2);
            } else if ($OutputType == 'Slabs') {
                $GradeValue = $GradeSlabs;
            }
            if ($GradeValue == "/") {
                $GradeValue = "";
            }

            $totalgradeItem['GradeName'] = $refGrade;
            $totalgradeItem['GradeValue'] = $GradeValue;

            $ArrayStockTotals[] = $totalgradeItem;
        }

        $TotalWeight = 0;
        $TotalMcs = 0;
        $TotalLSlabs = 0;
        foreach ($ArrayStoreData as $Item) {
            foreach ($Item['SubGrades'] as $SubItem) {
                $SubItem['GradeName'];
                $TotalWeight += $SubItem['GradeKgs'];
                $TotalMcs += $SubItem['GradeMcs'];
                // $TotalLooseBags += $SubItem['GradeLooseBags'];
                $TotalLSlabs += $SubItem['GradeSlabs'];
            }

            $StockTotals['TotalWeight'] = round($TotalWeight, 2);
            $StockTotals['TotalMcs'] = $TotalMcs;
            // $StockTotals['TotalLooseBags'] = $TotalLooseBags;
            $StockTotals['TotalLSlabs'] = $TotalLSlabs;
        }

        $itemsToStayFlipped = array_flip($FilteredGrades);

        $result = array_filter($AryGardesNames, function ($item) use ($itemsToStayFlipped) {
            return isset($itemsToStayFlipped[$item]);
        });

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['PageCount'] = $PageCount;
        // $data['Grades'] = $AryGardesNames;
        $data['Grades'] = $result;
        $data['Data'] = $ArrayStoreData;
        $data['StockTotal'] = $ArrayStockTotals;
        $data['ProductTotal'] = $StockTotals;

        return $data;
    }


    function getOrderedDetails($FilterQuery)
    {
        $sqlOrderedFields = "SUM(Mcs*TotalSlabs*SlabWeight*MFactor) as OrderedKgs, SUM(Mcs) AS OrderedMcs, SUM(Mcs*TotalSlabs) AS OrderedSlabs";
        $sqlOrderedSelect = "SELECT $sqlOrderedFields FROM `ScrmPoData` AS SPD   
        LEFT JOIN ScrmPoGradeData AS SPGD ON SPGD.InvId = SPD.PoID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID=SPGD.VarietyID
        LEFT JOIN AdminPackingStyles AS CSP ON CSP.PckID = SPGD.PackingStyleID
        WHERE PoID not in (SELECT PiID FROM `ScrmCustomInvoices` WHERE StockOutDate!='') and SPD.Status not in (2,3,4) $FilterQuery";
        $stmtOrderSelect = $this->conn->prepare($sqlOrderedSelect);
        $stmtOrderSelect->execute();

        // $errorInfo = $stmtOrderSelect->errorInfo();
        // var_dump($errorInfo);
        $rowOrderedData = $stmtOrderSelect->fetch(PDO::FETCH_ASSOC);
        $OrderedKgs = $rowOrderedData['OrderedKgs'];
        $OrderedMcs = $rowOrderedData['OrderedMcs'];
        $OrderedSlabs = $rowOrderedData['OrderedSlabs'];

        $Data = [
            "OrderedKGs" => $OrderedKgs,
            "OrderedMcs" => $OrderedMcs,
            "OrderedSlabs" => $OrderedSlabs,
        ];

        return $Data;
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
