<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class DailyProductionReportController
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

    function getDailyProductionDetails($RequestBody)
    {
        $ProductionDate = $RequestBody['ProductionDate'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $Species = $RequestBody['Species'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $FreezingType = $RequestBody['FreezingType'];
        $Variety = $RequestBody['Variety'];
        $PackingStyleID = $RequestBody['PackingStyleID'];
        $Treatment = $RequestBody['Treatment'];
        $Type = $RequestBody['Type'];

        $Query = '';

        if ($ProductionPlant != '') {
            $Query .= " AND CPID.PlantID='$ProductionPlant'";
        }
        if ($Species != '') {
            $Query .= " AND PIVL.Species='$Species'";
        }
        if ($TypeProduct != '') {
            $Query .= " AND PIVL.TypeProduct='$TypeProduct'";
        }
        if ($FreezingType != '') {
            $Query .= " AND CPID.FreezingType='$FreezingType'";
        }
        if ($Variety != '') {
            $Query .= " AND PIVL.ShortName='$Variety'";
        }
        if ($PackingStyleID != '') {
            $Query .= " AND CPID.PackingStyleID='$PackingStyleID'";
        }
        if ($Treatment != '') {
            $Query .= " AND CPID.SockingType='$Treatment'";
        }

        $AryGardeGroup = $this->getGradesGroup();

        $sqlFields = "CPID.VarietyID, PIVL.ShortName, PIVL.Species, CPID.PackingStyleID, CPID.SockingType, 
        PIVL.TypeProduct, CPS.PackingStyle";
        $sqlSelect = "SELECT $sqlFields FROM `CsStockData` AS CPID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        WHERE CPID.ProductionDate <= '$ProductionDate' $Query
        GROUP BY PIVL.ShortName, CPID.PackingStyleID, PIVL.Treatment, PIVL.TypeProduct";

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayVarietyData = [];
        $SNo = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayList['SNo'] = ++$SNo;
            $ArrayList['ShortName'] = $dataRow['ShortName'] . '-' . $dataRow['TypeProduct'];
            $ArrayList['SockingType'] = $dataRow['SockingType'];
            $ArrayList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayList['TypeProduct'] = $dataRow['TypeProduct'];

            $ShortName = $dataRow['ShortName'];
            $SockingType = $dataRow['SockingType'];
            $PackingStyleID = $dataRow['PackingStyleID'];
            $TypeProduct = $dataRow['TypeProduct'];

            $OpeningStockArray = [];
            $SlabInwardArray = [];
            $SlabOutwardArray = [];

            // Opening Stock
            $SlabsOpeningArray = [];
            $GrandTotals = 0;
            foreach ($AryGardeGroup as $GradeGroup) {
                $grades = $this->getGradesForGroup($GradeGroup, $ProductionDate, $Query);
                $SlabList = [];
                $TotalMcs = 0;
                $TotalSlabs = 0;
                foreach ($grades as $Grade) {

                    $slabINFields = "GROUP_CONCAT(StockID) as StockIDs, SUM(AvalMcs) AS PresentAvalMcs,
                    SUM(TotalSlabs*AvalMcs) AS McsSlabs, SUM(LooseBags) AS LooseSlabs";
                    $sqlSlabsIN = "SELECT $slabINFields FROM `CsStockData` AS CPID 
                    LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                    LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
                    WHERE PIVL.ShortName = '$ShortName' AND CPID.PackingStyleID = '$PackingStyleID' 
                    AND PIVL.Treatment='$SockingType' AND CPID.Grade = '$Grade' AND PIVL.TypeProduct = '$TypeProduct' 
                    AND CPID.ProductionDate < '$ProductionDate' $Query";
                    $stmtSlabsIN = $this->conn->prepare($sqlSlabsIN);
                    $stmtSlabsIN->execute();

                    $rowSlabIN = $stmtSlabsIN->fetch(PDO::FETCH_ASSOC);
                    $PresentMcsSlabs = $rowSlabIN['McsSlabs'] ? $rowSlabIN['McsSlabs'] : 0;
                    $PresentAvalMcs = $rowSlabIN['PresentAvalMcs'] ? $rowSlabIN['PresentAvalMcs'] : 0;  //
                    $PresentLooseSlabs = $rowSlabIN['LooseSlabs'] ? $rowSlabIN['LooseSlabs'] : 0;
                    $StockIDs = $rowSlabIN['StockIDs'] ? $rowSlabIN['StockIDs'] : 0;

                    $sqlSlabsOut = "SELECT  SUM(TotalSlabs*OutMcs) AS OutMcsSlabs, SUM(OutLooseBags) AS OutLooseSlabs,
                    SUM(OutMcs) AS OpeningOutMcs FROM CsStockData AS CPID 
                    LEFT JOIN CsStockOutDetails AS CSD ON CSD.StockID=CPID.StockID 
                    LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                    WHERE CSD.StockID in ($StockIDs)";
                    $stmtSlabsOut = $this->conn->prepare($sqlSlabsOut);
                    $stmtSlabsOut->execute();

                    $rowSlabOut = $stmtSlabsOut->fetch(PDO::FETCH_ASSOC);
                    $OutMcsSlabs = $rowSlabOut['OutMcsSlabs'] ? $rowSlabOut['OutMcsSlabs'] : 0;
                    $OutLooseSlabs = $rowSlabOut['OutLooseSlabs'] ? $rowSlabOut['OutLooseSlabs'] : 0;
                    $OpeningOutMcs = $rowSlabOut['OpeningOutMcs'] ? $rowSlabOut['OpeningOutMcs'] : 0; //

                    $sqlSlabDates = "SELECT  SUM(TotalSlabs*OutMcs) AS OutMcsSlabsDate, SUM(OutLooseBags) AS OutLooseSlabsDate, 
                    SUM(OutMcs) AS OutMcsDate FROM CsStockData AS CPID 
                    LEFT JOIN CsStockOutDetails AS CSD ON CSD.StockID=CPID.StockID 
                    LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                    WHERE CSD.StockID in ($StockIDs) and CSD.OutDate < '$ProductionDate'";

                    $stmtSlabsOutDate = $this->conn->prepare($sqlSlabDates);
                    $stmtSlabsOutDate->execute();

                    $rowSlabOutDate = $stmtSlabsOutDate->fetch(PDO::FETCH_ASSOC);
                    $OutMcsSlabsDate = $rowSlabOutDate['OutMcsSlabsDate'] ? $rowSlabOutDate['OutMcsSlabsDate'] : 0;
                    $OutLooseSlabsDate = $rowSlabOutDate['OutLooseSlabsDate'] ? $rowSlabOutDate['OutLooseSlabsDate'] : 0;
                    $OutMcsDate = $rowSlabOutDate['OutMcsDate'] ? $rowSlabOutDate['OutMcsDate'] : 0; //

                    if ($Type == "Slabs") {
                        $TotalInSlabsPresent = $PresentMcsSlabs + $PresentLooseSlabs;
                        $TotalOutSlabs = $OutMcsSlabs + $OutLooseSlabs;
                        $TotalOutSlabsDate = $OutMcsSlabsDate + $OutLooseSlabsDate;
                    } else if ($Type == "MCS") {
                        $TotalInSlabsPresent = $PresentAvalMcs;
                        $TotalOutSlabs = $OpeningOutMcs;
                        $TotalOutSlabsDate = $OutMcsDate;
                    }
                    $TotalSlabs = ($TotalInSlabsPresent + $TotalOutSlabs) - $TotalOutSlabsDate;

                    $TotalSlabs = $TotalSlabs ? $TotalSlabs : 0;
                    if ($Grade == $GradeGroup) {
                        $TotalMcs = $TotalSlabs;
                    } else if ($Grade != $GradeGroup && $TotalSlabs != 0) {
                        $TotalMcs .= ' <br>(' . $Grade . ' : ' . $TotalSlabs . ')';
                    }
                     $SlabList['TotalMcs'] = $TotalMcs;
                    $GrandTotals += $TotalSlabs;
                    $OpeningStockArray[$Grade] = $TotalSlabs;
                }
                $SlabsOpeningArray[] = $SlabList;
            }
            $SlabsOpeningArray[] = ['TotalMcs' => $GrandTotals];
            $ArrayList['SlabsData']['Opening'] = $SlabsOpeningArray;
            // Opening Stock

            // Inward Group
            $sqlInwardGroup = "SELECT TransCategory FROM CsTransactionTypes WHERE TransStatus=1 AND IsReturnable=1";
            $stmtInwardGroup = $this->conn->prepare($sqlInwardGroup);
            $stmtInwardGroup->execute();
            while ($rowInwardGroup = $stmtInwardGroup->fetch(PDO::FETCH_ASSOC)) {
                $TransCategory = $rowInwardGroup['TransCategory'];
                $SlabInwardArrayData = [];
                $InwardGrandTotals = 0;
                foreach ($AryGardeGroup as $GradeGroup) {

                    $grades = $this->getGradesForGroup($GradeGroup, $ProductionDate, $Query);

                    $SlabList = [];
                    $TotalMcs = 0;
                    $TotalSlabs = 0;
                    foreach ($grades as $Grade) {
                        $slabFields = "SUM(TotalSlabs*ActualMcs) AS McsSlabs, SUM(ActualLooseBags) AS LooseSlabs, SUM(ActualMcs) AS InwardActualMcs";
                        $sqlInwardSlabs = "SELECT $slabFields FROM `CsStockData` AS CPID 
                        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
                        WHERE PIVL.ShortName = '$ShortName' AND CPID.PackingStyleID = '$PackingStyleID' 
                        AND PIVL.Treatment='$SockingType' AND CPID.Grade = '$Grade' AND PIVL.TypeProduct = '$TypeProduct' 
                        AND ProductionTypeID='$TransCategory' AND CPID.ProductionDate='$ProductionDate' $Query";
                        $stmtInwardSlabs = $this->conn->prepare($sqlInwardSlabs);
                        $stmtInwardSlabs->execute();

                        while ($rowInwardSlabs = $stmtInwardSlabs->fetch(PDO::FETCH_ASSOC)) {
                            $McsSlabs = $rowInwardSlabs['McsSlabs'] ? $rowInwardSlabs['McsSlabs'] : 0;
                            $LooseSlabs = $rowInwardSlabs['LooseSlabs'] ? $rowInwardSlabs['LooseSlabs'] : 0;
                            $InwardActualMcs = $rowInwardSlabs['InwardActualMcs'] ? $rowInwardSlabs['InwardActualMcs'] : 0; //
                            if ($Type == "Slabs") {
                                $TotalSlabs = $McsSlabs + $LooseSlabs;
                            } else if ($Type == "MCS") {
                                $TotalSlabs = $InwardActualMcs;
                            }
                            $TotalSlabs = $TotalSlabs ? $TotalSlabs : 0;
                            if ($Grade == $GradeGroup) {
                                $TotalMcs = $TotalSlabs;
                            } else if ($Grade != $GradeGroup && $TotalSlabs != 0) {
                                $TotalMcs .= ' <br>(' . $Grade . ' : ' . $TotalSlabs . ')';
                            }
                        }
                        $SlabList['TotalMcs'] = $TotalMcs;
                        $InwardGrandTotals += $TotalSlabs;
                        $SlabInwardArray[$Grade][$TransCategory] = $TotalSlabs;
                    }
                    $SlabInwardArrayData[] = $SlabList;
                }
                $TransCategory = $TransCategory . ' StockIN';
                $SlabInwardArrayData[] = ['TotalMcs' => $InwardGrandTotals];
                $ArrayList['SlabsData'][$TransCategory] = $SlabInwardArrayData;
            }
            // Inward Group

            // Outward Group
            $sqlOutwardGroup = "SELECT TransCategory FROM CsTransactionTypes WHERE TransType=2 AND TransStatus=1";
            $stmtOutwardGroup = $this->conn->prepare($sqlOutwardGroup);
            $stmtOutwardGroup->execute();
            while ($rowOutwardGroup = $stmtOutwardGroup->fetch(PDO::FETCH_ASSOC)) {
                $TransCategory = $rowOutwardGroup['TransCategory'];

                $SlabOutwardArrayData = [];
                $OutwardGrandTotals = 0;
                foreach ($AryGardeGroup as $GradeGroup) {

                    $grades = $this->getGradesForGroup($GradeGroup, $ProductionDate, $Query);

                    $SlabList = [];
                    $TotalMcs = 0;
                    //  $TotalMcs = '';
                    $TotalSlabs = 0;
                    foreach ($grades as $Grade) {

                        $slabFields = "SUM(TotalSlabs*OutMcs) AS McsSlabs, SUM(OutLooseBags) AS LooseSlabs, SUM(OutMcs) AS OutwardMcs";
                          $sqlSlabs = "SELECT $slabFields FROM `CsStockOutDetails` AS CSD 
                        LEFT JOIN CsStockData AS CPID ON CSD.StockID=CPID.StockID 
                        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
                        WHERE PIVL.ShortName = '$ShortName' AND CPID.PackingStyleID = '$PackingStyleID' 
                        AND PIVL.Treatment='$SockingType' AND CPID.Grade = '$Grade' AND PIVL.TypeProduct = '$TypeProduct' 
                        AND OutType='$TransCategory' AND CSD.OutDate='$ProductionDate' $Query";
                        $stmtSlabs = $this->conn->prepare($sqlSlabs);
                        $stmtSlabs->execute();

                        while ($rowSlabs = $stmtSlabs->fetch(PDO::FETCH_ASSOC)) {
                            $McsSlabs = $rowSlabs['McsSlabs'] ? $rowSlabs['McsSlabs'] : 0;
                            $LooseSlabs = $rowSlabs['LooseSlabs'] ? $rowSlabs['LooseSlabs'] : 0;
                            $OutwardMcs = $rowSlabs['OutwardMcs'] ? $rowSlabs['OutwardMcs'] : 0;
                            if ($Type == "Slabs") {
                                $TotalSlabs = $McsSlabs + $LooseSlabs;
                            } else if ($Type == "MCS") {
                                $TotalSlabs = $OutwardMcs;
                            }

                            $TotalSlabs = $TotalSlabs ? $TotalSlabs : 0;
                            if ($Grade == $GradeGroup) {
                                $TotalMcs = $TotalSlabs;
                            } else if ($Grade != $GradeGroup && $TotalSlabs != 0) {
                                $TotalMcs .= '<br>(' . $Grade . ' : ' . $TotalSlabs . ')';
                            }
                        }
                        $SlabList['TotalMcs'] = $TotalMcs;
                        $OutwardGrandTotals += $TotalSlabs;
                        $SlabOutwardArray[$Grade][$TransCategory] = $TotalSlabs;
                    }
                    $SlabOutwardArrayData[] = $SlabList;
                }
                $TransCategory = $TransCategory . ' StockOut';
                $SlabOutwardArrayData[] = ['TotalMcs' => $OutwardGrandTotals];
                $ArrayList['SlabsData'][$TransCategory] = $SlabOutwardArrayData;
            }
            // Outward Group

            // Closing
            $ClosingStockArray = [];
            $ClosingGrandTotals = 0;
            $TotalMcs = 0;
            $SlabList = [];
            foreach ($AryGardeGroup as $GradeGroup) {
                $SlabList = [];
                $TotalMcs = 0;
                $GradeOpening = 0;
                $GradeInward = 0;
                $GradeOutward = 0;
                $TotalMcsFormatted = '';
                foreach ($this->getGradesForGroup($GradeGroup, $ProductionDate, $Query) as $Grade) {
                    $GradeOpening = isset($OpeningStockArray[$Grade]) ? $OpeningStockArray[$Grade] : 0;
                    $GradeInward = array_sum(isset($SlabInwardArray[$Grade]) ? $SlabInwardArray[$Grade] : 0);
                    $GradeOutward = array_sum(isset($SlabOutwardArray[$Grade]) ? $SlabOutwardArray[$Grade] : 0);

                    $GradeTotalMcs = $GradeOpening + $GradeInward - $GradeOutward;
                    if ($Grade === $GradeGroup) {
                        $TotalMcsFormatted .= $GradeTotalMcs;
                    } else  if ($Grade != $GradeGroup && $GradeTotalMcs != 0) {
                        $TotalMcsFormatted .= '<br>(' . $Grade . ' : ' . $GradeTotalMcs . ')';
                    }
                    $ClosingGrandTotals += $GradeTotalMcs;
                }
                $SlabList['TotalMcs'] = $TotalMcsFormatted;
                $ClosingStockArray[$GradeGroup] = $SlabList;
            }
            $ClosingStockArray[] = ['TotalMcs' => $ClosingGrandTotals];
            $ArrayList['SlabsData']['Closing'] = $ClosingStockArray;
            // Closing

            $ArrayVarietyData[] = $ArrayList;
        }
        $AryGardeGroup[] = "Total";
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['Data'] = $ArrayVarietyData;
        $data['Grades'] = $AryGardeGroup;

        return $data;
    }

    public function getGradesForGroup($GradeGroup, $ProductionDate, $Query)
    {
        $sqlSelectGrades = "SELECT DISTINCT CPID.Grade FROM CsStockData AS CPID
        LEFT JOIN ProductionGrades AS PGD ON CPID.Grade = PGD.value
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        WHERE PGD.GradeGroup = '$GradeGroup' AND CPID.ProductionDate<='$ProductionDate' $Query";
        $stmtSelectGrades = $this->conn->prepare($sqlSelectGrades);
        $stmtSelectGrades->execute();
        $Data = [];
        while ($dataGrade = $stmtSelectGrades->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataGrade['Grade'];
        }
        return $Data;
    }

    public function getGradesGroup()
    {
        $sqlGradesGroup = "SELECT DISTINCT GradeGroup FROM ProductionGrades ORDER BY ABS(GradeGroup)";
        $stmtGradesGroup = $this->conn->prepare($sqlGradesGroup);
        $stmtGradesGroup->execute();

        $Data = [];
        while ($dataGrade = $stmtGradesGroup->fetch(PDO::FETCH_ASSOC)) {
            $GradeGroup = $dataGrade['GradeGroup'];
            $Data[] = $GradeGroup;
        }
        return $Data;
    }
}
