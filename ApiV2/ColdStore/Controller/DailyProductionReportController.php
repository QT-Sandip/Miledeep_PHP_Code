<?php

include_once '../Global/Util.php';

class DailyProductionReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $gradeGroupMaster = array();
    private $TotalGradesMaster = array();

    public function __construct($db)
    {
        $this->conn = $db;
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
        $balanceType = $RequestBody['balanceType'];
        $ColdStore = $RequestBody['ColdStore'];
        $SlabType = $RequestBody['SlabType'];
        $PackingType = $RequestBody['PackingType'];
        $Group = $RequestBody['Group'];

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
        if ($ColdStore != '') {
            $Query .= " AND CPID.UnitID='$ColdStore'";
        }
        if ($PackingType != '') {
            $Query .= " AND CPID.PackingTypeID='$PackingType'";
        }
        $join = '';
        if ($Group != '') {
            $groupSql = " AND CPID.ProductionTypeID='$Group' ";
            $outArr = ['Physical Out', 'Audit Out', 'IBT Out', 'Conversion Out'];
            if (in_array($Group, $outArr)) {
                $join = ' LEFT JOIN CsStockOutDetails AS CSD ON CSD.StockID=CPID.StockID ';
                $groupSql = " AND CSD.OutType='$Group' ";
            }
            $Query .= $groupSql;
        }
        $this->gradeGroupMaster = $this->getGradesGroup();
        $this->TotalGradesMaster = $this->getTotalGrades();

        $sqlFields = "CPID.VarietyID, PIVL.ShortName, PIVL.Species, CPID.PackingStyleID, CPID.SockingType, 
        PIVL.TypeProduct, CPS.PackingStyle";
        $sqlSelect = "SELECT $sqlFields FROM `CsStockData` AS CPID 
        $join
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        WHERE CPID.ProductionDate <= '$ProductionDate' $Query
        GROUP BY PIVL.ShortName, CPID.PackingStyleID, CPID.SockingType, PIVL.TypeProduct,PIVL.Species 
        ORDER BY PIVL.Species, PIVL.TypeProduct,PIVL.ShortName,CPID.SockingType, ABS(CPS.PackingStyle)";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayVarietyData = [];
        $SNo = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $SlabsData = $this->getTransactionTypes($dataRow, $ProductionDate, $Query, $Type, $balanceType, $join, $Group, $RequestBody);

            if (!empty($SlabsData)) {
                $ArrayList = [];
                $ArrayList['SNo'] = ++$SNo;
                $ArrayList['ShortName'] = $dataRow['Species'] . '-' . $dataRow['TypeProduct'] . '-' . $dataRow['ShortName'];
                $ArrayList['SockingType'] = $dataRow['SockingType'];
                $ArrayList['PackingStyle'] = $dataRow['PackingStyle'];
                $ArrayList['TypeProduct'] = $dataRow['TypeProduct'];
                $ArrayList['SlabsData'] = $SlabsData;

                $ArrayVarietyData[] = $ArrayList;
            }
        }

        $totalClosing = 0;
        $totalOpening = 0;

        foreach ($ArrayVarietyData as $Item) {
            foreach ($Item['SlabsData'] as $SlabsDataRow) {
                if ($SlabsDataRow['StockType'] == 'Closing Balance') {
                    $totalClosing += $SlabsDataRow['TotalStock'];
                }
                if ($SlabsDataRow['StockType'] == 'Opening Balance') {
                    $totalOpening += $SlabsDataRow['TotalStock'];
                }
            }
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalOpening'] = $totalOpening;
        $data['TotalClosing'] = $totalClosing;
        $data['Data'] = $ArrayVarietyData;
        $data['Grades'] = $this->gradeGroupMaster;

        return $data;
    }


    public function getTransactionTypes($dataRow, $ProductionDate, $Query, $Type, $balanceType, $join = '', $Group = '', $RequestBody = [])
    {
        $Data = array();
        $outJoin = '';
        $Query2 = $Query;
        $outArr = ['Physical Out', 'Audit Out', 'IBT Out', 'Conversion Out'];
        if (in_array($Group, $outArr)) {
            $str = " AND CSD.OutType='$Group' ";
            $Query2 = str_replace($str, "", $Query);
        }
        $isReturn = isset($RequestBody['isReturn']) ? $RequestBody['isReturn'] : null;
        $transtype = isset($RequestBody['transtype']) ? $RequestBody['transtype'] : null;
        $TransCategory = "";
        if ($Group) {
            $TransCategory = " AND TransCategory = '$Group' ";
        }

        /**
         * FIX (corrected approach): Opening and Closing balances are now
         * BOTH produced by the exact same ledger function, getBalanceLedger,
         * differing only by comparison operator ('<' vs '<='). This makes
         * Closing(D) and Opening(D+1) mathematically identical by
         * construction -- they can't drift apart due to isReturn/transtype/
         * Group filters, because this calculation never depends on them.
         * The Inward/Outward loops below are now used ONLY to build the
         * on-screen breakdown of what moved during the day; they no longer
         * feed into the Closing Balance total.
         */
        $aryTransactions = [];
        $aryTransactions['StockType'] = 'Opening Balance';
        $aryTransactions['GradeSlabs'] = $this->getBalanceLedger($dataRow, $ProductionDate, $Query2, $Type, $outJoin, '<');
        $aryTransactions['TotalStock'] = $this->getHorizontalTotals($aryTransactions['GradeSlabs']);
        $Data[] = $aryTransactions;

        /*******************************INWARD (display only) *************************************** */
        if ($transtype != 2) {
            $sqlInwardGroup = "SELECT TransCategory FROM CsTransactionTypes WHERE IsReturnable=1 AND IsActive=1 $TransCategory";
            $stmtInwardGroup = $this->conn->prepare($sqlInwardGroup);
            $stmtInwardGroup->execute();
            while ($rowInwardGroup = $stmtInwardGroup->fetch(PDO::FETCH_ASSOC)) {
                $aryDisplay = [];
                if ($rowInwardGroup['TransCategory'] == 'Repacking Out') {
                    $aryDisplay['StockType'] = str_replace('Repacking Out', 'Repacking In', $rowInwardGroup['TransCategory']);
                } else {
                    $aryDisplay['StockType'] = (stripos($rowInwardGroup['TransCategory'], 'IN') === false) ? $rowInwardGroup['TransCategory'] . ' IN ' : $rowInwardGroup['TransCategory'];
                }
                $dataRow['StockType'] = $rowInwardGroup['TransCategory'];
                $aryDisplay['GradeSlabs'] = $this->getInwardTransactions($dataRow, $ProductionDate, $Query, $Type, $join);
                $aryDisplay['TotalStock'] = $this->getHorizontalTotals($aryDisplay['GradeSlabs']);
                if ($aryDisplay['TotalStock'] > 0) {
                    $Data[] = $aryDisplay;
                }
            }
        }
        /********************************INWARD END************************************** */

        /********************************OUTWARD (display only) ************************************** */
        if ($isReturn != 1) {
            $sqlOutwardGroup = "SELECT TransCategory FROM CsTransactionTypes WHERE TransType=2 AND IsActive=1 $TransCategory";
            $stmtOutwardGroup = $this->conn->prepare($sqlOutwardGroup);
            $stmtOutwardGroup->execute();

            while ($rowOutwardGroup = $stmtOutwardGroup->fetch(PDO::FETCH_ASSOC)) {
                $aryDisplay = [];
                if ($rowOutwardGroup['TransCategory'] == 'Repacking Out') {
                    $aryDisplay['StockType'] = $rowOutwardGroup['TransCategory'];
                } else {
                    $aryDisplay['StockType'] = (stripos($rowOutwardGroup['TransCategory'], 'OUT') === false) ? $rowOutwardGroup['TransCategory'] . ' OUT ' : $rowOutwardGroup['TransCategory'];
                }
                $dataRow['StockType'] = $rowOutwardGroup['TransCategory'];

                $aryDisplay['GradeSlabs'] = $this->getOutwardTransactions($dataRow, $ProductionDate, $Query, $Type, $join);
                $aryDisplay['TotalStock'] = $this->getHorizontalTotals($aryDisplay['GradeSlabs']);
                if ($aryDisplay['TotalStock'] > 0) {
                    $Data[] = $aryDisplay;
                }
            }
        }
        /********************************OUTWARD END************************************** */

        /********************************Closing Balance ************************************** */
        $ClosingGradeSlabs = $this->getBalanceLedger($dataRow, $ProductionDate, $Query2, $Type, $outJoin, '<=');

        $aryClosing = [];
        $aryClosing['StockType'] = 'Closing Balance';
        $aryClosing['GradeSlabs'] = $ClosingGradeSlabs;
        $aryClosing['TotalStock'] = $this->getHorizontalTotals($ClosingGradeSlabs);
        $Data[] = $aryClosing;
        /********************************Closing Balance************************************** */

        return $Data;
    }

    /**
     * Computes the true, unfiltered physical stock balance as of
     * $ProductionDate, using $Operator to decide whether that date's own
     * production/outs are included:
     *   '<'  -> balance strictly BEFORE $ProductionDate  (Opening Balance)
     *   '<=' -> balance up to AND INCLUDING $ProductionDate (Closing Balance)
     *
     * This never filters by transaction category, isReturn, or transtype --
     * it always reconstructs the real physical quantity, because every out
     * ever recorded genuinely reduced the live AvalMcs balance regardless of
     * category. Filtering categories out of this calculation (as an earlier
     * version of this fix did) understates the true balance and was the
     * actual cause of Closing/Opening staying out of sync.
     */
    public function getBalanceLedger($dataRow, $ProductionDate, $Query, $Type, $join, $Operator)
    {
        $ShortName = $dataRow['ShortName'];
        $SockingType = $dataRow['SockingType'];
        $PackingStyleID = $dataRow['PackingStyleID'];
        $TypeProduct = $dataRow['TypeProduct'];
        $Species = $dataRow['Species'];

        $aryGroup = [];
        $aryData = [];

        $slabINFields = "
CPID.Grade,
(SELECT GradeGroup  FROM ProductionGrades WHERE value=CPID.Grade) as GradeGroup, 
GROUP_CONCAT(StockID) as StockIDs, SUM(AvalMcs) AS PresentAvalMcs,
SUM(TotalSlabs*AvalMcs) AS McsSlabs, SUM(LooseBags) AS LooseSlabs,
ROUND((SUM(LooseBags)/TotalSlabs),2) AS PresentLooseMcs,SUM(AvalKgs) AS Kgs,
SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) AS PresentMcsKgs,
SUM(LooseBags*SlabWeight*MFactor) AS PresentLooseKgs,CPID.Galze";
        $sqlSlabsIN = "SELECT $slabINFields FROM `CsStockData` AS CPID 
        $join
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        WHERE PIVL.ShortName = '$ShortName' AND CPID.PackingStyleID = '$PackingStyleID' 
        AND CPID.SockingType='$SockingType'  AND PIVL.TypeProduct = '$TypeProduct' and PIVL.Species='$Species'
        AND CPID.ProductionDate $Operator '$ProductionDate' $Query GROUP by CPID.Grade order by abs(CPID.Grade) ASC";
        $stmtSlabsIN = $this->conn->prepare($sqlSlabsIN);
        $stmtSlabsIN->execute();

        while ($rowSlabIN = $stmtSlabsIN->fetch(PDO::FETCH_ASSOC)) {

            $GradeGroup = $rowSlabIN['GradeGroup'];
            $Grade = $rowSlabIN['Grade'];
            $Galze = $rowSlabIN['Galze'];
            $PresentMcsSlabs = $rowSlabIN['McsSlabs'] ? $rowSlabIN['McsSlabs'] : 0;
            $PresentAvalMcs = $rowSlabIN['PresentAvalMcs'] ? $rowSlabIN['PresentAvalMcs'] : 0;
            $PresentLooseMcs = $rowSlabIN['PresentLooseMcs'] ? $rowSlabIN['PresentLooseMcs'] : 0;
            $PresentLooseSlabs = $rowSlabIN['LooseSlabs'] ? $rowSlabIN['LooseSlabs'] : 0;
            $StockIDs = $rowSlabIN['StockIDs'] ? $rowSlabIN['StockIDs'] : 0;
            $Kgs = $rowSlabIN['Kgs'] ? $rowSlabIN['Kgs'] : 0;
            $PresentMcsKgs = $rowSlabIN['PresentMcsKgs'] ? $rowSlabIN['PresentMcsKgs'] : 0;
            $PresentLooseKgs = $rowSlabIN['PresentLooseKgs'] ? $rowSlabIN['PresentLooseKgs'] : 0;

            // All-time outs for these StockIDs -- unfiltered by category,
            // needed to correctly reconstruct the original produced quantity.
            $sqlSlabsOut = "SELECT  SUM(TotalSlabs*OutMcs) AS OutMcsSlabs, SUM(OutLooseBags) AS OutLooseSlabs,
ROUND((SUM(OutLooseBags)/TotalSlabs),2) AS OutLooseMcs,SUM(OutMcs) AS OpeningOutMcs,
SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS OutKgs,
SUM(OutLooseBags*SlabWeight*MFactor) AS OutLooseKgs
FROM CsStockData AS CPID 
LEFT JOIN CsStockOutDetails AS CSD ON CSD.StockID=CPID.StockID 
LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
WHERE CSD.StockID in ($StockIDs)";
            $stmtSlabsOut = $this->conn->prepare($sqlSlabsOut);
            $stmtSlabsOut->execute();

            $rowSlabOut = $stmtSlabsOut->fetch(PDO::FETCH_ASSOC);
            $OutMcsSlabs = $rowSlabOut['OutMcsSlabs'] ? $rowSlabOut['OutMcsSlabs'] : 0;
            $OutLooseSlabs = $rowSlabOut['OutLooseSlabs'] ? $rowSlabOut['OutLooseSlabs'] : 0;
            $OpeningOutMcs = $rowSlabOut['OpeningOutMcs'] ? $rowSlabOut['OpeningOutMcs'] : 0;
            $OpeningOutLooseMcs = $rowSlabOut['OutLooseMcs'] ? $rowSlabOut['OutLooseMcs'] : 0;
            $OutKgs = $rowSlabOut['OutKgs'] ? $rowSlabOut['OutKgs'] : 0;
            $OutLooseKgs = $rowSlabOut['OutLooseKgs'] ? $rowSlabOut['OutLooseKgs'] : 0;

            // Outs up to the cutoff date -- SAME $Operator as the main
            // ProductionDate filter above, so Opening/Closing use exactly
            // consistent cutoffs.
            $sqlSlabDates = "SELECT  SUM(TotalSlabs*OutMcs) AS OutMcsSlabsDate, SUM(OutLooseBags) AS OutLooseSlabsDate, 
ROUND((SUM(OutLooseBags)/TotalSlabs),2) AS OutLooseMcsDate, SUM(OutMcs) AS OutMcsDate,
SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS OutKgsDate,
SUM(OutLooseBags*SlabWeight*MFactor) AS OutLooseKgsDate
FROM CsStockData AS CPID 
LEFT JOIN CsStockOutDetails AS CSD ON CSD.StockID=CPID.StockID 
LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
WHERE CSD.StockID in ($StockIDs) and CSD.OutDate $Operator '$ProductionDate'";
            $stmtSlabsOutDate = $this->conn->prepare($sqlSlabDates);
            $stmtSlabsOutDate->execute();

            $rowSlabOutDate = $stmtSlabsOutDate->fetch(PDO::FETCH_ASSOC);
            $OutMcsSlabsDate = $rowSlabOutDate['OutMcsSlabsDate'] ? $rowSlabOutDate['OutMcsSlabsDate'] : 0;
            $OutLooseSlabsDate = $rowSlabOutDate['OutLooseSlabsDate'] ? $rowSlabOutDate['OutLooseSlabsDate'] : 0;
            $OutMcsDate = $rowSlabOutDate['OutMcsDate'] ? $rowSlabOutDate['OutMcsDate'] : 0;
            $OutLooseMcsDate = $rowSlabOutDate['OutLooseMcsDate'] ? $rowSlabOutDate['OutLooseMcsDate'] : 0;
            $OutKgsDate = $rowSlabOutDate['OutKgsDate'] ? $rowSlabOutDate['OutKgsDate'] : 0;
            $OutLooseKgsDate = $rowSlabOutDate['OutLooseKgsDate'] ? $rowSlabOutDate['OutLooseKgsDate'] : 0;

            // if ($Type == "Slabs") {
            //     $TotalInSlabsPresent = $PresentMcsSlabs + $PresentLooseSlabs;
            //     $TotalOutSlabs       = $OutMcsSlabs + $OutLooseSlabs;
            //     $TotalOutSlabsDate   = $OutMcsSlabsDate + $OutLooseSlabsDate;
            // } else if ($Type == "MCS") {
            //     $TotalInSlabsPresent = $PresentAvalMcs + $PresentLooseMcs;
            //     $TotalOutSlabs       = $OpeningOutMcs + $OpeningOutLooseMcs;
            //     $TotalOutSlabsDate   = $OutMcsDate + $OutLooseMcsDate;
            // } else if ($Type == "KGS") {
            //     $TotalInSlabsPresent = round($Kgs, 2);
            //     $TotalOutSlabs       = round($OutKgs + $OutLooseKgs, 2);
            //     $TotalOutSlabsDate   = round($OutKgsDate + $OutLooseKgsDate, 2);
            // }

            if ($Type == "Slabs") {
                $TotalInSlabsPresent = $PresentMcsSlabs + $PresentLooseSlabs;
                $TotalOutSlabs = $OutMcsSlabs + $OutLooseSlabs;
                $TotalOutSlabsDate = $OutMcsSlabsDate + $OutLooseSlabsDate;
            } else if ($Type == "MCS") {
                $TotalInSlabsPresent = $PresentAvalMcs + $PresentLooseMcs;
                $TotalOutSlabs = $OpeningOutMcs + $OpeningOutLooseMcs;
                $TotalOutSlabsDate = $OutMcsDate + $OutLooseMcsDate;
            } else if ($Type == "KGS") {
                // FIX: was round($Kgs, 2) -- the stored AvalKgs column.
                $TotalInSlabsPresent = round($PresentMcsKgs + $PresentLooseKgs, 2);
                $TotalOutSlabs = round($OutKgs + $OutLooseKgs, 2);
                $TotalOutSlabsDate = round($OutKgsDate + $OutLooseKgsDate, 2);
            }

            $TotalSlabs = ($TotalInSlabsPresent + $TotalOutSlabs) - $TotalOutSlabsDate;
            if ($Type == "KGS") {
                if($Galze > 0){
                     $TotalSlabs = $TotalSlabs - ($TotalSlabs * ($Galze/100));
                }
            }
            $rowInwardSlabs = [];
            $rowInwardSlabs['GradeGroup'] = $GradeGroup;
            $rowInwardSlabs['Grade'] = $Grade;
            $rowInwardSlabs['TotalSlabs'] = $TotalSlabs;
            $aryData[] = $rowInwardSlabs;
        }

        foreach ($this->gradeGroupMaster as $ItemMasterGrade) {
            $GradeKey = $ItemMasterGrade['GradeKey'];
            $GroupGrades = array();
            $GroupSlabs = array();
            $AryTrueGrades = array();
            foreach ($aryData as $ItemGrade) {
                if ($GradeKey == $ItemGrade['GradeGroup']) {
                    $actGradeBreakup = [];
                    $actGradeBreakup['TrueGrade'] = $ItemGrade['Grade'];
                    $actGradeBreakup['TrueGradeValue'] = $ItemGrade['TotalSlabs'];
                    $actGradeBreakup['GradeGroup'] = $ItemGrade['GradeGroup'];
                    $AryTrueGrades[] = $actGradeBreakup;
                    if ($GradeKey == $ItemGrade['Grade']) {
                        $GroupGrades[] = $ItemGrade['TotalSlabs'];
                    } else {
                        $GroupGrades[] = $ItemGrade['Grade'] . '-' . ($ItemGrade['TotalSlabs']);
                    }
                    $GroupSlabs[] = $ItemGrade['TotalSlabs'];
                }
            }

            $ItemFinalGrade = [];
            $ItemFinalGrade['Grade'] = $GradeKey;
            $ItemFinalGrade['ValueGrade'] = implode(',<br>', $GroupGrades);
            $ItemFinalGrade['TrueGrades'] = $AryTrueGrades;
            $ItemFinalGrade['TotalSlabs'] = array_sum($GroupSlabs);
            // Kept for backward-compatibility with any frontend code that
            // reads 'TrueValueGrade' specifically on Closing Balance rows
            // (the old getClosingBalance() output used this key name).
            $ItemFinalGrade['TrueValueGrade'] = array_sum($GroupSlabs);
            $aryGroup[] = $ItemFinalGrade;
        }

        return $aryGroup;
    }

    public function getInwardTransactions($dataRow, $ProductionDate, $Query, $Type, $join = '')
    {
        $ShortName = $dataRow['ShortName'];
        $SockingType = $dataRow['SockingType'];
        $PackingStyleID = $dataRow['PackingStyleID'];
        $TypeProduct = $dataRow['TypeProduct'];
        $TransCategory = $dataRow['StockType'];
        $Species = $dataRow['Species'];

        $aryGroup = [];
        $aryData = [];
        $slabINFields = " CPID.Grade,
        (SELECT GradeGroup  FROM ProductionGrades WHERE value=CPID.Grade) as GradeGroup,            
        SUM(TotalSlabs*ActualMcs) AS McsSlabs, SUM(ActualLooseBags) AS LooseSlabs, SUM(ActualMcs) AS InwardActualMcs,
        ROUND((SUM(ActualLooseBags) / TotalSlabs),2) AS LooseMcs,SUM(ActualKgs) AS Kgs";
        $sqlSlabsIN = "SELECT $slabINFields FROM `CsStockData` AS CPID 
        $join
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        WHERE PIVL.ShortName = '$ShortName' AND CPID.PackingStyleID = '$PackingStyleID' 
        AND CPID.SockingType='$SockingType'  AND PIVL.TypeProduct = '$TypeProduct' AND ProductionTypeID='$TransCategory' and PIVL.Species='$Species'
        AND CPID.ProductionDate = '$ProductionDate' $Query GROUP by CPID.Grade order by abs(CPID.Grade) ASC;";

        $stmtSlabsIN = $this->conn->prepare($sqlSlabsIN);
        $stmtSlabsIN->execute();

        while ($rowInwardSlabs = $stmtSlabsIN->fetch(PDO::FETCH_ASSOC)) {

            $McsSlabs = $rowInwardSlabs['McsSlabs'] ? $rowInwardSlabs['McsSlabs'] : 0;
            $LooseSlabs = $rowInwardSlabs['LooseSlabs'] ? $rowInwardSlabs['LooseSlabs'] : 0;
            $InwardActualMcs = $rowInwardSlabs['InwardActualMcs'] ? $rowInwardSlabs['InwardActualMcs'] : 0;
            $LooseMcs = $rowInwardSlabs['LooseMcs'] ? $rowInwardSlabs['LooseMcs'] : 0;
            $Kgs = $rowInwardSlabs['Kgs'] ? $rowInwardSlabs['Kgs'] : 0;
            if ($Type == "KGS") {
                $TotalSlabs = $Kgs;
            } else if ($Type == "MCS") {
                $TotalSlabs = $InwardActualMcs + $LooseMcs;
            } else {
                $TotalSlabs = $McsSlabs + $LooseSlabs;
            }
            $rowInwardSlabs['TotalSlabs'] = $TotalSlabs;
            $aryData[] = $rowInwardSlabs;
        }

        foreach ($this->gradeGroupMaster as $ItemMasterGrade) {
            $GradeKey = $ItemMasterGrade['GradeKey'];
            $GroupGrades = array();
            $GroupSlabs = array();
            $AryTrueGrades = array();
            foreach ($aryData as $ItemGrade) {
                if ($GradeKey == $ItemGrade['GradeGroup']) {
                    $actGradeBreakup = [];
                    $actGradeBreakup['TrueGrade'] = $ItemGrade['Grade'];
                    $actGradeBreakup['TrueGradeValue'] = $ItemGrade['TotalSlabs'];
                    $actGradeBreakup['GradeGroup'] = $ItemGrade['GradeGroup'];
                    $AryTrueGrades[] = $actGradeBreakup;
                    if ($GradeKey == $ItemGrade['Grade']) {
                        $GroupGrades[] = $ItemGrade['TotalSlabs'];
                    } else {
                        $GroupGrades[] = $ItemGrade['Grade'] . '-' . ($ItemGrade['TotalSlabs']) ;
                    }
                    $GroupSlabs[] = $ItemGrade['TotalSlabs'];
                }
            }

            $ItemFinalGrade = [];
            $ItemFinalGrade['Grade'] = $GradeKey;
            $ItemFinalGrade['ValueGrade'] = implode(',<br>', $GroupGrades);
            $ItemFinalGrade['TotalSlabs'] = array_sum($GroupSlabs);
            $ItemFinalGrade['TrueGrades'] = $AryTrueGrades;
            $aryGroup[] = $ItemFinalGrade;
        }

        return $aryGroup;
    }

    public function getOutwardTransactions($dataRow, $ProductionDate, $Query, $Type, $join = '')
    {
        $ShortName = $dataRow['ShortName'];
        $SockingType = $dataRow['SockingType'];
        $PackingStyleID = $dataRow['PackingStyleID'];
        $TypeProduct = $dataRow['TypeProduct'];
        $TransCategory = $dataRow['StockType'];
        $Species = $dataRow['Species'];

        $aryGroup = [];
        $aryData = [];

        $slabFields = "CPID.Grade,
        (SELECT GradeGroup  FROM ProductionGrades WHERE value=CPID.Grade) as GradeGroup,
        SUM(TotalSlabs*OutMcs) AS McsSlabs, SUM(OutLooseBags) AS LooseSlabs, SUM(OutMcs) AS OutwardMcs, 
        ROUND((SUM(OutLooseBags)/TotalSlabs),2) AS LooseMcs,
        SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS OutKgs,
        SUM(OutLooseBags*SlabWeight*MFactor) AS OutLooseKgs";
        $sqlSlabs = "SELECT $slabFields FROM `CsStockOutDetails` AS CSD 
        LEFT JOIN CsStockData AS CPID ON CSD.StockID=CPID.StockID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        WHERE PIVL.ShortName = '$ShortName' AND CPID.PackingStyleID = '$PackingStyleID' 
        AND CPID.SockingType='$SockingType'  AND PIVL.TypeProduct = '$TypeProduct'  and PIVL.Species='$Species'
        AND OutType='$TransCategory' AND CSD.OutDate='$ProductionDate' $Query GROUP by CPID.Grade order by abs(CPID.Grade) ASC";

        $stmtSlabs = $this->conn->prepare($sqlSlabs);
        $stmtSlabs->execute();
        while ($rowSlabs = $stmtSlabs->fetch(PDO::FETCH_ASSOC)) {
            $McsSlabs = $rowSlabs['McsSlabs'] ? $rowSlabs['McsSlabs'] : 0;
            $LooseSlabs = $rowSlabs['LooseSlabs'] ? $rowSlabs['LooseSlabs'] : 0;
            $OutwardMcs = $rowSlabs['OutwardMcs'] ? $rowSlabs['OutwardMcs'] : 0;
            $LooseMcs = $rowSlabs['LooseMcs'] ? $rowSlabs['LooseMcs'] : 0;
            $OutKgs = $rowSlabs['OutKgs'] ? $rowSlabs['OutKgs'] : 0;
            $OutLooseKgs = $rowSlabs['OutLooseKgs'] ? $rowSlabs['OutLooseKgs'] : 0;
            if ($Type == "KGS") {
                $TotalSlabs = $OutKgs + $OutLooseKgs;
            } else if ($Type == "MCS") {
                $TotalSlabs = $OutwardMcs + $LooseMcs;
            } else {
                $TotalSlabs = $McsSlabs + $LooseSlabs;
            }
            $rowSlabs['TotalSlabs'] = $TotalSlabs;
            $aryData[] = $rowSlabs;
        }

        foreach ($this->gradeGroupMaster as $ItemMasterGrade) {
            $GradeKey = $ItemMasterGrade['GradeKey'];
            $GroupGrades = array();
            $GroupSlabs = array();
            $AryTrueGrades = array();
            foreach ($aryData as $ItemGrade) {
                if ($GradeKey == $ItemGrade['GradeGroup']) {
                    $actGradeBreakup = [];
                    $actGradeBreakup['TrueGrade'] = $ItemGrade['Grade'];
                    $actGradeBreakup['TrueGradeValue'] = '-' . $ItemGrade['TotalSlabs'];
                    $actGradeBreakup['GradeGroup'] = $ItemGrade['GradeGroup'];
                    $AryTrueGrades[] = $actGradeBreakup;
                    if ($GradeKey == $ItemGrade['Grade']) {
                        $GroupGrades[] = $ItemGrade['TotalSlabs'];
                    } else {
                        $GroupGrades[] = $ItemGrade['Grade'] . '-' . ($ItemGrade['TotalSlabs']) ;
                    }
                    $GroupSlabs[] = $ItemGrade['TotalSlabs'];
                }
            }
            $ItemFinalGrade = [];
            $ItemFinalGrade['Grade'] = $GradeKey;
            $ItemFinalGrade['ValueGrade'] = implode(',<br>', $GroupGrades);
            $ItemFinalGrade['TotalSlabs'] = '-' . array_sum($GroupSlabs);
            $ItemFinalGrade['TrueGrades'] = $AryTrueGrades;
            $aryGroup[] = $ItemFinalGrade;
        }
        return $aryGroup;
    }

    /**
     * NOTE: No longer called internally -- Closing Balance is now produced
     * directly by getBalanceLedger('<='), which is guaranteed consistent
     * with Opening Balance. Left in place only in case other code in the
     * project calls this directly; safe to remove if nothing else uses it.
     */
    public function getClosingBalance($SlabsData)
    {
        $aryGroup = array();
        foreach ($this->gradeGroupMaster as $ItemMasterGrade) {
            $GradeKey = $ItemMasterGrade['GradeKey'];
            $GroupGrades = 0;
            foreach ($SlabsData as $Item) {
                foreach ($Item["GradeSlabs"] as $ItemGrade) {
                    if ($GradeKey == $ItemGrade['Grade']) {
                        $GroupGrades = $GroupGrades + ($ItemGrade['TotalSlabs']);
                    }
                }
            }
            $ItemFinalGrade = [];
            $ItemFinalGrade['Grade'] = $GradeKey;
            $ItemFinalGrade['ValueGrade'] = $GroupGrades;
            $ItemFinalGrade['TrueValueGrade'] = $GroupGrades;
            $aryGroup[] = $ItemFinalGrade;
        }

        $FinalAryGrades = array();
        foreach ($SlabsData as $Item) {
            foreach ($Item["GradeSlabs"] as $ItemGrade) {
                $AryTrueGrades = $ItemGrade['TrueGrades'];
                if (count($AryTrueGrades) > 0) {
                    foreach ($AryTrueGrades as $TrueItemGrade) {
                        if ($TrueItemGrade != null) {
                            $FinalAryGrades[] = $TrueItemGrade;
                        }
                    }
                }
            }
        }

        $RGrades = array();
        foreach ($FinalAryGrades as $TrueItemGrade) {
            if (!in_array($TrueItemGrade['TrueGrade'], $RGrades)) {
                $RGrades[] = $TrueItemGrade['TrueGrade'];
            }
        }
        sort($RGrades);

        $FinalAryGradest = array();
        foreach ($RGrades as $ActGrade) {
            $GradeGroup = "";
            $TrueGradeValue = 0;
            foreach ($FinalAryGrades as $TrueItemGrade) {
                if ($ActGrade == $TrueItemGrade['TrueGrade']) {
                    $GradeGroup = $TrueItemGrade['GradeGroup'];
                    $TrueGradeValue = $TrueGradeValue + ($TrueItemGrade['TrueGradeValue']);
                }
            }

            $ItemFinalGrade = [];
            $ItemFinalGrade['Grade'] = $ActGrade;
            $ItemFinalGrade['ValueGrade'] = $TrueGradeValue;
            $ItemFinalGrade['GradeGroup'] = $GradeGroup;
            $FinalAryGradest[] = $ItemFinalGrade;
        }

        $aryGroup = array();
        foreach ($this->gradeGroupMaster as $ItemMasterGrade) {
            $GradeKey = $ItemMasterGrade['GradeKey'];
            $TrueValueGrade = 0;
            $GroupGrades = array();
            foreach ($FinalAryGradest as $ItemGrade) {
                if ($GradeKey == $ItemGrade['GradeGroup']) {
                    $TrueValueGrade = $TrueValueGrade + ($ItemGrade['ValueGrade']);
                    if ($GradeKey == $ItemGrade['Grade']) {
                        $GroupGrades[] = $ItemGrade['ValueGrade'];
                    } else {
                        $GroupGrades[] = $ItemGrade['Grade'] . '-' . ($ItemGrade['ValueGrade']) ;
                    }
                }
            }
            $ItemFinalGrade = [];
            $ItemFinalGrade['Grade'] = $GradeKey;
            $ItemFinalGrade['ValueGrade'] = implode(',<br>', $GroupGrades);
            $ItemFinalGrade['TrueValueGrade'] = $TrueValueGrade;
            $ItemFinalGrade['TotalSlabs'] = $TrueValueGrade;
            $aryGroup[] = $ItemFinalGrade;
        }

        return $aryGroup;
    }


    public function getHorizontalTotals($SlabsData)
    {
        $TotalSlabs = 0;
        foreach ($SlabsData as $Item) {
            $TotalSlabs += $Item['TotalSlabs'];
        }
        $TotalSlabs = abs($TotalSlabs);
        return $TotalSlabs;
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
        $sqlGradesGroup = "SELECT DISTINCT GradeGroup as ValueGrade,GradeGroup as GradeKey FROM ProductionGrades ORDER BY ABS(GradeGroup)";
        $stmtGradesGroup = $this->conn->prepare($sqlGradesGroup);
        $stmtGradesGroup->execute();
        $Data = [];
        while ($dataGrade = $stmtGradesGroup->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataGrade;
        }

        $dataGrade = [];
        $dataGrade['ValueGrade'] = 'Total';
        $dataGrade['GradeKey'] = 'Total';
        $Data[] = $dataGrade;

        return $Data;
    }

    public function getTotalGrades()
    {
        $sqlGradesGroup = "SELECT Options as ActGrade,GradeGroup FROM `ProductionGrades` ORDER BY ABS(GradeGroup) asc";
        $stmtGradesGroup = $this->conn->prepare($sqlGradesGroup);
        $stmtGradesGroup->execute();
        $Data = [];
        while ($dataGrade = $stmtGradesGroup->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataGrade;
        }
        return $Data;
    }
}