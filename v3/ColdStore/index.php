<?php
   include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
   include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");
   include($_SERVER['DOCUMENT_ROOT'] . "/includes/OrderStatusHelper.php");
   $IsIBTGraphShow = !empty($ClientConfigData['ibtgraph']) ? $ClientConfigData['ibtgraph'] : 0;
   $IsIBTGraphShow = 1;
   //echo "<pre>"; print_r($_SESSION); exit;
   function number_format_inr($number) {
        $number = (string) $number;
        $decimal = '';

        if (strpos($number, '.') !== false) {
            list($number, $decimal) = explode('.', $number);
            $decimal = '.' . substr($decimal, 0, 2);
        }

        if (strlen($number) <= 3) {
            return $number . $decimal;
        }

        $last3 = substr($number, -3);
        $rest  = substr($number, 0, -3);

        $rest = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);

        return $rest . ',' . $last3 . $decimal;
    }

   function checkEmpty($Value)
   {

       if ($Value == '') {
           $Value = '--';
       }

       return $Value;
   }

    $SpeciesList = [];

    $sqlQuery = "SELECT ID, SpeciesName, ShortCode, Description
                 FROM ScrmSpeciesMaster
                 WHERE Status = 1";

    $stmt = $iconn->query($sqlQuery);

    while ($dataRow = $stmt->FETCH_ARRAY()) {
        $SpeciesList[] = $dataRow;
    }


   if ($_GET['Month'] != '') {
       $PresentMonth = $_GET['Month'];
   } else {
       $PresentMonth = date('m');
   }

   if ($_GET['Year'] != '') {
       $Year = $_GET['Year'];
   } else {
       $Year = date('Y');
   }

    $SelectedUom = isset($_GET['UOM']) ? strtoupper(trim($_GET['UOM'])) : 'MCS';
    if (!in_array($SelectedUom, ['KGS', 'MCS'], true)) {
        $SelectedUom = 'MCS';
    }
    $SelectedUomLabel = ($SelectedUom === 'MCS') ? 'Mcs' : 'Kgs';

    $SelectedSpecies = isset($_GET['species']) ? trim($_GET['species']) : '';
    if ($SelectedSpecies !== '') {
        $safeSpecies = $iconn->real_escape_string($SelectedSpecies);
        $speciesFilter = " AND PIVL.Species = '$safeSpecies'";
        $speciesFilterVL = " AND VL.Species = '$safeSpecies'";
        $speciesFilterV = " AND V.Species = '$safeSpecies'";
        $speciesFilterSub = " AND PIVL.Species = '$safeSpecies'";
    } else {
        $speciesFilter = '';
        $speciesFilterVL = '';
        $speciesFilterV = '';
        $speciesFilterSub = '';
    }

    $PID = 0;
   if ($_GET['PID'] != '') {
       $PID = $_GET['PID'];
       $Qry = " and PlantID =$PID";
   } else {
       $Qry = "";
   }

   $processorQry='';

   $CurrentFinYear = getCurrentFinancialYear();
   $DateFilter = getDateFilter($Year, $PresentMonth);

   $AccessType = $_SESSION['AccessType'];
   $UnitID = $_SESSION['UnitID'];


   // echo $_SESSION['StoreIDs'].'56';
   //$storeIDs = $_SESSION["StoreIDs"];
   if ($AccessType != 1) {
       if ( $UnitID != 0) {
           $PID = $UnitID;
           $Qry = " and PlantID=$UnitID";
           $Qry_sp = " AND SP.ProductionPlantID =$UnitID ";
       }

       if ($storeIDs  != 0) {
           $SQry = "where StoreID = ($storeIDs)";
       }

   }

   $SqlStoreFacility = "SELECT * FROM `CsStores` $SQry";

   $StmtStoreFacility = $iconn->query($SqlStoreFacility);

   $SqlActiveStockIds = "SELECT GROUP_CONCAT(StockID) as ActiveStockID FROM `CsStockData` as CSD
   /*LEFT Join CsBarCodes as CBC on CSD.BarCodeID=CBC.BarCodeID*/  WHERE  (CSD.AvalMcs!=0 or CSD.LooseBags!=0) $Qry";
   //echo $SqlActiveStockIds; exit;
   $StmtActiveStockIds = $iconn->query($SqlActiveStockIds);

   $RowActiveStockID = $StmtActiveStockIds->fetch_array();
   $ActiveStockID = '0' . $RowActiveStockID['ActiveStockID'];
   $AryPos = array();

      $allowedInwardGroups = [
    'Production',
    'Melting',
    'IBT IN',
    'Conversion In'
];

$allowedOutwardGroups = [
    'Melting',
    'IBT Out',
    'Conversion Out',
    'Shipment Out'
];

// Convert arrays to SQL-safe string lists
$allowedInwardGroupsSql = "'" . implode("','", array_map('addslashes', $allowedInwardGroups)) . "'";
$allowedOutwardGroupsSql = "'" . implode("','", array_map('addslashes', $allowedOutwardGroups)) . "'";

$SqlSales = "
SELECT
    I.TransactionDate,
    I.InwardMcs,
    I.InwardLooseBags,
    I.InwardKgs,
    COALESCE(O.OutMcs, 0) AS OutMcs,
    COALESCE(O.OutLooseBags, 0) AS OutLooseBags,
    COALESCE(O.OutKgs, 0) AS OutKgs

FROM
(
    -- INWARD
    SELECT
        CSD.ProductionDate AS TransactionDate,

        SUM(
            CSD.ActualMcs + (CSD.ActualLooseBags / APS.TotalSlabs)
        ) AS InwardMcs,

        SUM(CSD.ActualLooseBags) AS InwardLooseBags,
        SUM(CAST(CSD.ActualKgs AS DECIMAL(12,2))) AS InwardKgs

    FROM CsStockData CSD
    LEFT JOIN AdminPackingStyles APS
        ON APS.PCKID = CSD.PackingStyleID
    LEFT JOIN ScrmPIVarietyList PIVL
        ON PIVL.VarietyID = CSD.VarietyID

    WHERE CSD.ProductionDate $DateFilter
    $Qry
    $speciesFilter
    AND COALESCE(NULLIF(CSD.ProductionTypeID, ''), 'Production')
        IN ($allowedInwardGroupsSql)

    GROUP BY CSD.ProductionDate
) I

LEFT JOIN
(
    -- OUTWARD
    SELECT
        CSD.OutDate AS TransactionDate,

        SUM(
            CSD.OutMcs + (CSD.OutLooseBags / APS.TotalSlabs)
        ) AS OutMcs,

        SUM(CSD.OutLooseBags) AS OutLooseBags,

        SUM(
            CAST(CSD.ActualOutKgs AS DECIMAL(12,2))
        ) AS OutKgs

    FROM CsStockOutDetails CSD
    INNER JOIN CsStockData CS
        ON CSD.StockID = CS.StockID

    LEFT JOIN AdminPackingStyles APS
        ON APS.PCKID = CS.PackingStyleID
    LEFT JOIN ScrmPIVarietyList PIVL
        ON PIVL.VarietyID = CS.VarietyID

    WHERE CSD.OutDate $DateFilter
    $Qry
    $speciesFilter
    AND COALESCE(NULLIF(CSD.OutType, ''), 'Outward')
        IN ($allowedOutwardGroupsSql)

    GROUP BY CSD.OutDate
) O

ON I.TransactionDate = O.TransactionDate

ORDER BY I.TransactionDate
";

//echo $SqlSales; exit;
   $SalesExec = $iconn->query($SqlSales);


   while ($SalesRow = $SalesExec->fetch_array()) {
    if ($SalesRow['TransactionDate'] != '') {


        $salesInward = ($SelectedUom === 'MCS')
            ? (float)$SalesRow['InwardMcs']
            : (float)$SalesRow['InwardKgs'];


        $salesOutward = ($SelectedUom === 'MCS')
            ? (float)$SalesRow['OutMcs']
            : (float)$SalesRow['OutKgs'];

        // Optional: Balance (if needed)
        $salesBalance = $salesInward - $salesOutward;

        $InvIds = round($salesInward, 2);
        $MonthName[] = getFormatedDate($SalesRow['TransactionDate']);
        $Orders[] = round($salesInward, 2);
        $Shipments[] = round($salesOutward, 2);

        // Optional (if you want to show balance in chart)
        // $Balances[] = round($salesBalance, 2);
    }
}

   $TotalOrders = array_sum($Orders);
   $TotalShipments = array_sum($Shipments);

   $dailyInwardTooltipMap = [];
   $dailyOutwardTooltipMap = [];
   $tooltipLimit = 10;

   $allowedGroups = [
    'Production',
    'Melting',
    'IBT IN',
    'Conversion In'
];

// Convert array to SQL-safe string list
$allowedGroupsSql = "'" . implode("','", array_map('addslashes', $allowedGroups)) . "'";

$SqlInwardTooltip = "SELECT
        CPID.ProductionDate AS ChartDate,
        CASE
            WHEN COALESCE(CPID.ProductionTypeID, '') IN ('', 'Production', 'Melting') THEN 'Production'
            ELSE CPID.ProductionTypeID
        END AS GroupName,
        SUM(CPID.ActualMcs + (CPID.ActualLooseBags / NULLIF(CSP.TotalSlabs, 0))) AS QtyMcs,
        SUM(CAST(CPID.ActualKgs AS DECIMAL(12,2))) AS QtyKgs
    FROM CsStockData AS CPID
    LEFT JOIN AdminPackingStyles AS CSP
        ON CSP.PCKID = CPID.PackingStyleID
    LEFT JOIN ScrmPIVarietyList PIVL
        ON PIVL.VarietyID = CPID.VarietyID
    WHERE CPID.ProductionDate $DateFilter
    $Qry
    $speciesFilter
    AND COALESCE(NULLIF(CPID.ProductionTypeID, ''), 'Production')
        IN ($allowedGroupsSql)
    GROUP BY CPID.ProductionDate, GroupName
    ORDER BY CPID.ProductionDate ASC, QtyKgs DESC, QtyMcs DESC";

$InwardTooltipExec = $iconn->query($SqlInwardTooltip);

if ($InwardTooltipExec) {
    while ($tooltipRow = $InwardTooltipExec->fetch_assoc()) {

        $chartDate = $tooltipRow['ChartDate'];

        $groupName = str_ireplace(
            'Repacking Out',
            'Repacking In',
            $tooltipRow['GroupName']
        );

        $dailyInwardTooltipMap[$chartDate][] = [
            'group' => $groupName,
            'qty'   => round(
                ($SelectedUom === 'MCS')
                    ? (float)$tooltipRow['QtyMcs']
                    : (float)$tooltipRow['QtyKgs'],
                2
            ),
        ];
    }
}

   $allowedGroups = [
    'Melting',
    'IBT Out',
    'Conversion Out',
    'Shipment Out'
];

// Convert array to SQL-safe string list
$allowedGroupsSql = "'" . implode("','", array_map('addslashes', $allowedGroups)) . "'";

$SqlOutwardTooltip = "SELECT
        CSD.OutDate AS ChartDate,
        COALESCE(NULLIF(CSD.OutType, ''), 'Outward') AS GroupName,
        SUM(CSD.OutMcs + (CSD.OutLooseBags / NULLIF(CSP.TotalSlabs, 0))) AS QtyMcs,
        SUM(CAST(CSD.ActualOutKgs AS DECIMAL(12,2))) AS QtyKgs
    FROM CsStockOutDetails AS CSD
    INNER JOIN CsStockData AS CS
        ON CSD.StockID = CS.StockID
    LEFT JOIN AdminPackingStyles AS CSP
        ON CSP.PCKID = CS.PackingStyleID
    LEFT JOIN ScrmPIVarietyList PIVL
        ON PIVL.VarietyID = CS.VarietyID
    WHERE CSD.OutDate $DateFilter
    $Qry
    $speciesFilter
    AND COALESCE(NULLIF(CSD.OutType, ''), 'Outward')
        IN ($allowedGroupsSql)
    GROUP BY CSD.OutDate, GroupName
    ORDER BY CSD.OutDate ASC, QtyKgs DESC, QtyMcs DESC";

$OutwardTooltipExec = $iconn->query($SqlOutwardTooltip);

if ($OutwardTooltipExec) {
    while ($tooltipRow = $OutwardTooltipExec->fetch_assoc()) {

        $chartDate = $tooltipRow['ChartDate'];
        $groupName = $tooltipRow['GroupName'];

        $dailyOutwardTooltipMap[$chartDate][] = [
            'group' => $groupName,
            'qty'   => round(
                ($SelectedUom === 'MCS')
                    ? (float)$tooltipRow['QtyMcs']
                    : (float)$tooltipRow['QtyKgs'],
                2
            ),
        ];
    }
}
//echo "<pre>"; print_r($dailyOutwardTooltipMap); exit;
   /*****************************************Block Wise Date ***************************/



   /*****************************************Block Wise Date ***************************/

   /*****************************************Variety Wise Date ***************************/


   $SqlPresentStockVarityWise = "SELECT ShortName,sum(AvalMcs + LooseBags/TotalSlabs) as MCS ,
   (sum(AvalKgs)) as KGS,
   (sum(LooseBags  * SlabWeight *MFactor)) as LKGS,
   PackingStyleID from CsStockData as CPID
   left JOIN CsBarCodes as CBD on CBD.BarCodeID=CPID.BarCodeID
   left JOIN AdminPackingStyles as CPS on CPID.PackingStyleID=CPS.PCKID
   left join ScrmPIVarietyList as VL on VL.VarietyID=CPID.VarietyID
   WHERE (CPID.AvalMcs!=0 or CPID.LooseBags!=0) $Qry $speciesFilterVL GROUP by ShortName";

   $StmtPresentStockVarityWise = $iconn->query($SqlPresentStockVarityWise);
   $AryVarity = array();
   $AryKgs = array();
   $AryMcs = array();
   $dataPointsMcs = $dataPoints = array();
   $i=0;
   while ($RowPresentStock = $StmtPresentStockVarityWise->fetch_array()) {
        if( !empty($RowPresentStock['ShortName']) ){
           $TotalKgs = $RowPresentStock['KGS'];
           if(!empty($TotalKgs) && $TotalKgs>1 && $RowPresentStock['MCS']>1){
               $TotalMcs = (float)$RowPresentStock['MCS'];
               $AryVarity[] = $RowPresentStock['ShortName'];
               $AryMcs[] = $TotalMcs;
               $AryKgs[] = $TotalKgs;
               $data_d['value'] = round($TotalKgs);
               //$data_d['color'] = sprintf("#%06x", rand(0, 16777215));
               $data_d['color'] = $acls[$i];
               $data_d['highlight'] = '#32CD32';
               $data_d['label'] = $RowPresentStock['ShortName'];
               $Cjs['label'] = $RowPresentStock['ShortName'];
               $Cjs['y'] = round(($TotalKgs));
               $dounat_DATA[] = $data_d;
               $dataPoints[] = $Cjs;
               $dataPointsMcs[] = [
                   'label' => $RowPresentStock['ShortName'],
                   'y' => round($TotalMcs)
               ];
               $i++;
           }
       }
   }


   $OverAllKgs = round((array_sum($AryKgs) / 1000), 2);
   $OverAllMcs = round(array_sum($AryMcs), 2);

   /*****************************************Variety Wise Date ***************************/


   /*****************************************Port Wise Date ***************************/


   $SqlPorts = "SELECT COALESCE(SC.name,'Na') as CountryName,
   Max(SCI.PoDate) as LastShipment,
   COALESCE(SPC.Name,'Na') as PortName,COUNT(*) as Total FROM `ScrmShipmentDetailsBreif` as  SSD LEFT join
   ScrmPoData  as SCI on SCI.PoID=SSD.PiID
   left join ScrmPortCities  as SPC on SCI.PortOfDischarge=SPC.PortId
   left join ScrmCountriesList  as SC on SC.id=SPC.Country
   WHERE SCI.PoDate $DateFilter
    GROUP by PortofDischarge ORDER by Total DESC;";
   $StmtPorts = $iconn->query($SqlPorts);

   $PortsExec = $iconn->query($SqlPorts);
   $i=0;
   while ($RowPorts = $PortsExec->fetch_array()) {
       $RowPorts['Color'] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));;
       $PortSeries[] = $RowPorts['Total'] + 0;
       $PortLables[] = $RowPorts['PortName'];
       //$PortColors[] = $RowPorts['Color'];
       $PortColors[] = $acls[$i];;
       $AryPortDtls[] = $RowPorts;
       $i++;
   }

   /*****************************************Grade Wise***************************/


   $SqlPresentStockGradeWise = "
SELECT
    X_Axis,
    SUM(McsPerGroup) AS MCS,
    SUM(KGS) AS KGS,
    SUM(LKGS) AS LKGS
FROM (
    SELECT
        CPID.Grade AS X_Axis,
        CPID.PackingStyleID,

        (
            CPID.AvalMcs +
            (CPID.LooseBags / NULLIF(CPS.TotalSlabs, 0))
        ) AS McsPerGroup,

        (
            (CPID.AvalKgs)
        ) AS KGS,

        (CPID.LooseBags * CPS.SlabWeight * CPS.MFactor) AS LKGS

    FROM CsStockData AS CPID

    LEFT JOIN CsBarCodes AS CBD
        ON CBD.BarCodeID = CPID.BarCodeID

    LEFT JOIN AdminPackingStyles AS CPS
        ON CPID.PackingStyleID = CPS.PCKID

    LEFT JOIN ScrmPIVarietyList AS VL
        ON VL.VarietyID = CPID.VarietyID

    WHERE
        (CPID.AvalMcs!=0 or CPID.LooseBags!=0) AND CPID.PackingTypeID = 'DUMMY'

        $Qry
        $speciesFilterVL

) t

GROUP BY X_Axis
ORDER BY ABS(X_Axis)
";
//echo $SqlPresentStockGradeWise; exit;

   $StmtPresentStockGradeWise = $iconn->query($SqlPresentStockGradeWise);
   $AryVarity = array();
   $AryKgs = array();
   $AryMcs = array();

   while ($RowPresentStock = $StmtPresentStockGradeWise->fetch_array()) {
        if(($SelectedUom === 'MCS')){
            $condition = ($RowPresentStock['MCS'] > 0);
        }else{
            $condition = ($RowPresentStock['KGS'] > 0);
        }

       if ( $condition ) {
           $AryGradeBar3[] = $RowPresentStock['X_Axis'];
           $AryMcsBar3[] = (int) $RowPresentStock['MCS'];

           $TotalKgsBar3 = $RowPresentStock['KGS'] ;
           $TotalKgsBarMcs3 = round(array_sum($AryMcsBar3), 2);
           $AryKgsBar3[] = round($TotalKgsBar3);

       }
   }

   $TotalKgsBar3 = round(array_sum($AryKgsBar3), 2);
   $TotalMcsBar3 = round(array_sum($AryMcsBar3), 2);
   $TotalDummyKgs = $TotalKgsBar3;
   $TotalDummyMcs = $TotalMcsBar3;
   $TotalDummyDisplay = ($SelectedUom === 'MCS') ? $TotalDummyMcs : $TotalDummyKgs;


   $SqlPresentStockGradeWiseMaster = "SELECT X_Axis, SUM(McsPerGroup) AS MCS, SUM(KGS) AS KGS, SUM(LKGS) AS LKGS FROM (
    SELECT CPID.Grade as X_Axis,
    (CPID.AvalMcs + (CPID.LooseBags / NULLIF(CPS.TotalSlabs, 0))) AS McsPerGroup,
    ((CPID.AvalKgs)) AS KGS,
    (CPID.LooseBags * CPS.SlabWeight * CPS.MFactor) AS LKGS,
    PackingStyleID from CsStockData as CPID
    left JOIN CsBarCodes as CBD on CBD.BarCodeID=CPID.BarCodeID
    left JOIN AdminPackingStyles as CPS on CPID.PackingStyleID=CPS.PCKID
    left join ScrmPIVarietyList as VL on VL.VarietyID=CPID.VarietyID
    WHERE (CPID.AvalMcs!=0 or CPID.LooseBags!=0) AND PackingTypeID='MASTER'
    $speciesFilterVL
) t GROUP BY X_Axis ORDER by Abs(X_Axis)";
   //echo $SqlPresentStockGradeWiseMaster; exit;
   $StmtPresentStockGradeWiseMaster = $iconn->query($SqlPresentStockGradeWiseMaster);
   $AryVarityMaster = array();
   $AryKgsMaster = array();
   $AryMcsMaster = array();

    while ($RowPresentStockMaster = $StmtPresentStockGradeWiseMaster->fetch_array()) {
        if ($RowPresentStockMaster['KGS'] > 0) {
            $AryGradeBar3Master[] = $RowPresentStockMaster['X_Axis'];
            $AryMcsBar3Master[] = (int) $RowPresentStockMaster['MCS'];

            $TotalKgsBar3Master = $RowPresentStockMaster['KGS'];
            $AryKgsBar3Master[] = $TotalKgsBar3Master;

        }
    }

   $TotalKgsBar3Master = round(array_sum($AryKgsBar3Master), 2);
   $TotalMcsBar3Master = round(array_sum($AryMcsBar3Master), 2);
   $TotalMasterKgs = $TotalKgsBar3Master;
   $TotalMasterMcs = $TotalMcsBar3Master;
   $TotalMasterDisplay = ($SelectedUom === 'MCS') ? $TotalMasterMcs : $TotalMasterKgs;

   /* Today Inward Inward || Logic: Sum(ActualMCs) */

   $cardsTodayInward = "
    SELECT
    DATE(CSD.TransactionDate) AS TransDate,
    SUM(CSD.ActualMcs + (CSD.ActualLooseBags / APS.TotalSlabs)) AS InwardMcs
    FROM CsStockData CSD
    LEFT JOIN AdminPackingStyles APS ON APS.PCKID = CSD.PackingStyleID
    LEFT JOIN ScrmPIVarietyList PIVL ON PIVL.VarietyID = CSD.VarietyID
    WHERE DATE(CSD.TransactionDate) = CURDATE()
    $Qry
    $speciesFilter
    GROUP BY DATE(CSD.TransactionDate)";
   $smtCTInwards = $iconn->query($cardsTodayInward);
   $RowCTInwards = $smtCTInwards->fetch_array();
   $countTodayInwards = number_format_inr($RowCTInwards['InwardMcs']);

   /* Today Outward || Logic: SUM(ActualMcs - AvalMcs) */

$cardsTodayOutward = "
    SELECT
    DATE(TransactionDate) AS TransDate,
    SUM(
    (
        ActualMcs + FLOOR(ActualLooseBags / APS.TotalSlabs)
    )
    -
    (
        AvalMcs + FLOOR(LooseBags / APS.TotalSlabs)
    )
) AS OutwardMcs
    FROM CsStockData
    LEFT JOIN AdminPackingStyles APS ON APS.PCKID = CsStockData.PackingStyleID
    LEFT JOIN ScrmPIVarietyList PIVL ON PIVL.VarietyID = CsStockData.VarietyID
    WHERE StockStatus = 2
    AND DATE(TransactionDate) = CURDATE()
    $Qry
    $speciesFilter";
   $smtCTOutwards = $iconn->query($cardsTodayOutward);
   $RowCTOutwards = $smtCTOutwards->fetch_array();
   $countTodayOutwards = number_format_inr($RowCTOutwards['OutwardMcs']);

   /* Current Stock || loginc: SUM(AvalMcs) */

     $cardsCurrentStocks = "
SELECT
    SUM(McsPerGroup) + 50 AS CurrentMcs,
    SUM(RemainingLoose) AS CurrentLooseBags,
    SUM(Kgs) AS CurrentKgs
FROM (
    SELECT
        CSD.PackingStyleID,
        CSD.PackingTypeID,
        CSD.Grade,
        CSD.VarietyID,


        (
            SUM(CSD.AvalMcs) +
            FLOOR(SUM(CSD.LooseBags) / NULLIF(MAX(APS.TotalSlabs),0))
        ) AS McsPerGroup,


        (
            SUM(CSD.LooseBags) % NULLIF(APS.TotalSlabs,0)
        ) AS RemainingLoose,

        SUM(CSD.AvalKgs) AS Kgs

    FROM CsStockData CSD
    JOIN AdminPackingStyles APS
        ON APS.PCKID = CSD.PackingStyleID
    LEFT JOIN ScrmPIVarietyList PIVL
        ON CSD.VarietyID = PIVL.VarietyID

    WHERE
        (CSD.AvalMcs != 0 OR CSD.LooseBags != 0)
        $Qry
        $speciesFilter

    GROUP BY
        CSD.PackingStyleID,
        CSD.PackingTypeID,
        CSD.Grade,
        CSD.VarietyID

) t;";
   //echo $cardsCurrentStocks; exit;
   $smtCTCurrentStocks = $iconn->query($cardsCurrentStocks);
   $RowCTCurrentStocks = $smtCTCurrentStocks->fetch_array();
   $CurrentStockValue = ($SelectedUom === 'MCS')
       ? (float)$RowCTCurrentStocks['CurrentMcs']
       : (float)$RowCTCurrentStocks['CurrentKgs'];
   $countTodayCurrentStocks = number_format_inr(round($CurrentStockValue,2));
//error_log($cardsCurrentStocks);
/* pending kgs || logic: PO Assigned But Not Fully Packed*/
$condtion_pendingkgs = "";
if( isset($_SESSION['UnitID']) && $_SESSION['UnitID']){
    $condtion_pendingkgs = " AND CSD.PlantID = '".$_SESSION['UnitID']."' ";
}else if( isset($_GET['PID']) && $_GET['PID']){
    $condtion_pendingkgs = " AND CSD.PlantID = '".$_GET['PID']."' ";
}

$query_pendingkgs = "
SELECT
    COALESCE(SUM(
        CSD.ActualMcs +
        (CSD.ActualLooseBags / NULLIF(APS.TotalSlabs, 0))
    ), 0) AS TotalMcs,

    COALESCE(SUM(CSD.ActualKgs), 0) AS TotalKgs

FROM CsDailyProductionData CSD
LEFT JOIN AdminPackingStyles APS
    ON APS.PCKID = CSD.PackingStyleID
left join ScrmPIVarietyList as VL on VL.VarietyID=CSD.VarietyID
WHERE CSD.ProductionDate $DateFilter
$speciesFilterVL
AND CSD.StockStatus = 0
$condtion_pendingkgs
";

$smtCTpendingkgs = $iconn->query($query_pendingkgs);
   $RowCTpendingkgs = $smtCTpendingkgs->fetch_array();
   $PendingStockValue = ($SelectedUom === 'MCS')
       ? (float)$RowCTpendingkgs['TotalMcs']
       : (float)$RowCTpendingkgs['TotalKgs'];
   $countTodaypendingkgs = number_format_inr($PendingStockValue);

   /* Stock Aging || logic: DATEDIFF(CURDATE(), ProductionDate)*/

   $sqlAging = "SELECT
  CASE
    WHEN DATEDIFF(CURDATE(), S.ProductionDate) <= 30 THEN '0–30 Days'
    WHEN DATEDIFF(CURDATE(), S.ProductionDate) BETWEEN 31 AND 90 THEN '31–90 Days'
    WHEN DATEDIFF(CURDATE(), S.ProductionDate) BETWEEN 91 AND 180 THEN '91–180 Days'
    WHEN DATEDIFF(CURDATE(), S.ProductionDate) BETWEEN 181 AND 365 THEN '181–365 Days'
    ELSE '>365 Days'
  END AS AgingBucket,

  COUNT(*) AS StockCount,
  SUM(S.AvalMcs + S.LooseBags/APS.TotalSlabs) AS TotalMcs,

  SUM(
    (S.AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) +
    (S.LooseBags * APS.SlabWeight * APS.MFactor)
  ) AS TotalKgs

FROM CsStockData S
JOIN AdminPackingStyles APS ON APS.PCKID = S.PackingStyleID
JOIN CsStoresRackData R ON R.RowID = S.StoreRackID
LEFT JOIN ScrmPIVarietyList PIVL ON PIVL.VarietyID = S.VarietyID

WHERE S.StockStatus = 1
AND (S.AvalMcs > 0 OR S.LooseBags > 0)
$Qry
$speciesFilter
GROUP BY AgingBucket
ORDER BY
  MIN(DATEDIFF(CURDATE(), S.ProductionDate));
";
//echo $sqlAging;
$result = $iconn->query($sqlAging);

$labels_aging = [];
$values_aging = [];
$stock_aging_total = $kgs_aging_total =0;
while ($row = $result->fetch_assoc()) {
    $labels_aging[] = $row['AgingBucket'];
    $selectedQty = ($SelectedUom === 'MCS') ? (float)$row['TotalMcs'] : (float)$row['TotalKgs'];
    $kgs_aging[] = round($selectedQty, 2);
    $values_aging2[$row['AgingBucket']] = $row['StockCount']; // or StockCount
    $values_aging[] = $row['StockCount']; // or StockCount
    $stock_aging_total += $row['StockCount'];
    $kgs_aging_total += round($selectedQty, 2);
}


   $sqlAging = "SELECT CSD.StockID, CSD.Grade, DATEDIFF(CURDATE(), CSD.ProductionDate) AS aging_days FROM CsStockData CSD LEFT JOIN ScrmPIVarietyList PIVL ON PIVL.VarietyID = CSD.VarietyID WHERE CSD.StockStatus = 1 $Qry $speciesFilter HAVING aging_days > 300";
   $smtAging = $iconn->query($sqlAging);

   // Limit to 5 points
//$top5Points = array_slice($dataPoints, 0, 9);
$top5Points = ($SelectedUom === 'MCS') ? $dataPointsMcs : $dataPoints;
// Extract values and labels for JS
$values = array_column($top5Points, 'y');
$labels = array_column($top5Points, 'label');
$labels = array_map('strval', $labels);

/*echo "<pre>"; print_r($values); echo "</pre>";
echo "<pre>"; print_r($labels); echo "</pre>"; exit;*/


$sqlAgingvariety = "SELECT
    V.ShortName AS Variety,
    CASE
        WHEN DATEDIFF(CURDATE(), S.ProductionDate) <= 30 THEN '0–30'
        WHEN DATEDIFF(CURDATE(), S.ProductionDate) BETWEEN 31 AND 90 THEN '31–90'
        WHEN DATEDIFF(CURDATE(), S.ProductionDate) BETWEEN 91 AND 180 THEN '91–180'
        WHEN DATEDIFF(CURDATE(), S.ProductionDate) BETWEEN 181 AND 365 THEN '181–365'
        ELSE '>365'
    END AS AgeBucket,
    GROUP_CONCAT(DISTINCT COALESCE(B.BlockName, 'OnFloor') ORDER BY B.BlockName SEPARATOR ', ') AS BlockNames,
    SUM(
        CASE
            WHEN S.PackingTypeID = 'MASTER' THEN
                (S.AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) +
                (S.LooseBags * APS.SlabWeight * APS.MFactor)
            ELSE 0
        END
    ) AS MasterKgs,
    SUM(
        CASE
            WHEN S.PackingTypeID = 'MASTER' THEN (S.AvalMcs + S.LooseBags/APS.TotalSlabs)
            ELSE 0
        END
    ) AS MasterMcs,
    SUM(
        CASE
            WHEN S.PackingTypeID = 'DUMMY' THEN
                (S.AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) +
                (S.LooseBags * APS.SlabWeight * APS.MFactor)
            ELSE 0
        END
    ) AS DummyKgs,
    SUM(
        CASE
            WHEN S.PackingTypeID = 'DUMMY' THEN (S.AvalMcs + S.LooseBags/APS.TotalSlabs)
            ELSE 0
        END
    ) AS DummyMcs,
    SUM(
        CASE
            WHEN UPPER(S.PackingTypeID) = 'IQF' THEN
                (S.AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) +
                (S.LooseBags * APS.SlabWeight * APS.MFactor)
            ELSE 0
        END
    ) AS IqfKgs,
    SUM(
        CASE
            WHEN UPPER(S.PackingTypeID) = 'IQF' THEN (S.AvalMcs + S.LooseBags/APS.TotalSlabs)
            ELSE 0
        END
    ) AS IqfMcs,

    SUM(
        (S.AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) +
        (S.LooseBags * APS.SlabWeight * APS.MFactor)
    ) AS Kgs,
    SUM(S.AvalMcs + S.LooseBags/APS.TotalSlabs) AS Mcs
FROM CsStockData S
JOIN ScrmPIVarietyList V ON V.VarietyID = S.VarietyID
JOIN AdminPackingStyles APS ON APS.PCKID = S.PackingStyleID
LEFT JOIN CsStoresRackData R ON R.RowID = S.StoreRackID
LEFT JOIN CsStoresBlockData B ON B.BlockID = R.BlockID
WHERE
    (S.AvalMcs > 0 OR S.LooseBags > 0) and
    StockStatus = 1 $Qry $speciesFilterV
GROUP BY V.ShortName, AgeBucket";
//echo $sqlAgingvariety;
$smtAgingvariety = $iconn->query($sqlAgingvariety);

$chartData = [];
$varieties = [];
$ageBuckets = ['0–30', '31–90', '91–180', '181–365', '>365'];

while ($r = $smtAgingvariety->fetch_assoc()) {
    $agingCellValue = ($SelectedUom === 'MCS') ? (float)$r['Mcs'] : (float)$r['Kgs'];
    $chartData[] = [
        'x' => $r['AgeBucket'],  // X axis
        'y' => $r['Variety'],    // Y axis
        'v' => $agingCellValue,  // Cell value
        'block'  => (string)($r['BlockNames'] ?: 'OnFloor'),
        'master' => ($SelectedUom === 'MCS') ? (float)$r['MasterMcs'] : (float)$r['MasterKgs'],
        'dummy'  => ($SelectedUom === 'MCS') ? (float)$r['DummyMcs'] : (float)$r['DummyKgs'],
        'iqf'    => ($SelectedUom === 'MCS') ? (float)$r['IqfMcs'] : (float)$r['IqfKgs']
    ];

    $varieties[$r['Variety']] = true;
    //$ageBuckets[$r['AgeBucket']] = true;
}

$varieties = array_keys($varieties);
//$ageBuckets = array_keys($ageBuckets);

$sqlAgingCount = " SELECT
    ROUND(
        SUM(
            DATEDIFF(CURDATE(), S.ProductionDate) *
            (
                (S.AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) +
                (S.LooseBags * APS.SlabWeight * APS.MFactor)
            )
        ) /
        SUM(
            (S.AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) +
            (S.LooseBags * APS.SlabWeight * APS.MFactor)
        )
    ) AS AvgStockAge
FROM CsStockData S
JOIN AdminPackingStyles APS ON APS.PCKID = S.PackingStyleID
LEFT JOIN ScrmPIVarietyList PIVL ON PIVL.VarietyID = S.VarietyID
WHERE
    (S.AvalMcs > 0 OR S.LooseBags > 0)
    and
    StockStatus = 1 $Qry
    $speciesFilter
" ;
$smtAgingvariety_Count = $iconn->query($sqlAgingCount);
$rowAgingCount = $smtAgingvariety_Count->fetch_assoc();

/* Brand/Grade with quantity */

$queryBrnadGradeQty = "SELECT
    SBM.BrandID,
    COALESCE(SBM.BrandName, 'Unknown') AS BrandName,
    PIVL.Grade AS Grade,
    SUM(
        CPID.AvalMcs +
        (CPID.LooseBags / NULLIF(APS.TotalSlabs, 0))
    ) AS TotalMCS,
    SUM(CPID.LooseBags) AS TotalLooseBags,
    SUM(
        (CPID.AvalMcs * APS.TotalSlabs * APS.SlabWeight * APS.MFactor) +
        (CPID.LooseBags * APS.SlabWeight * APS.MFactor)
    ) AS TotalKGS
FROM CsStockData AS CPID
LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID = PIVL.VarietyID
LEFT JOIN ScrmBrandsMaster AS SBM ON SBM.BrandID = PIVL.BrandName
LEFT JOIN AdminPackingStyles APS ON APS.PCKID = CPID.PackingStyleID
WHERE (CPID.AvalMcs <> 0 OR CPID.LooseBags <> 0)
  /*AND CPID.StockStatus = 1*/
  $Qry
  $speciesFilter
GROUP BY SBM.BrandID, BrandName, PIVL.Grade
ORDER BY BrandName, CAST(SUBSTRING_INDEX(PIVL.Grade, '/', 1) AS UNSIGNED),
  CAST(SUBSTRING_INDEX(PIVL.Grade, '/', -1) AS UNSIGNED)";
//echo $queryBrnadGradeQty; exit;
$smtBrnadGradeQty = $iconn->query($queryBrnadGradeQty);
$dataBrnadGradeQty = []; $brandtotalkgs=0;
if ($smtBrnadGradeQty) {
    while ($row = $smtBrnadGradeQty->fetch_assoc()) {
        $dataBrnadGradeQty[] = [
            'BrandID' => (int)$row['BrandID'],
            'BrandName' => $row['BrandName'],
            'Grade' => $row['Grade'],
            'TotalMCS' => (float)$row['TotalMCS'],
            'TotalLooseBags' => (float)$row['TotalLooseBags'],
            'TotalKGS' => (float)$row['TotalKGS']
        ];
        $brandtotalkgs +=(float)$row['TotalKGS'];
        $brandtotalmcs +=(float)$row['TotalMCS'];
    }
}

$queryPackingTypeID = "
SELECT
    TypeProduct,
    PackingTypeID,
    FreezingType,

    SUM(McsCalc) AS StoreMcs,
    SUM(StoreKgs) AS StoreQuantity

FROM (
    SELECT
        UPPER(PIVL.TypeProduct) AS TypeProduct,
        UPPER(CSD.PackingTypeID) AS PackingTypeID,
        UPPER(CSD.FreezingType) AS FreezingType,

        (
            CSD.AvalMcs +
            (CSD.LooseBags / NULLIF(APS.TotalSlabs, 0))
        ) AS McsCalc,

        (
            CSD.AvalKgs

        ) AS StoreKgs

    FROM CsStockData CSD

    LEFT JOIN AdminPackingStyles APS
        ON APS.PCKID = CSD.PackingStyleID

    LEFT JOIN ScrmPIVarietyList PIVL
        ON PIVL.VarietyID = CSD.VarietyID



    WHERE
        (CSD.AvalMcs != 0 OR CSD.LooseBags != 0)
        /*AND CSD.StockStatus = 1*/
        $Qry
        $packingTypeFilter
        $speciesFilter
) AS FineSummary

GROUP BY
    TypeProduct,
    PackingTypeID,
    FreezingType
";
$smtPackingTypeID = $iconn->query($queryPackingTypeID);

$PackingTypeID = [];
$totkgs_cs = 0;
$dummy_cnt = 0;
while ($row = $smtPackingTypeID->fetch_assoc()) {

    $typeProduct  = strtoupper(trim((string)$row['TypeProduct']));
    $packingType  = strtoupper(trim((string)$row['PackingTypeID']));
    $freezingType = strtoupper(trim((string)$row['FreezingType']));
    $qtyKgs       = $row['StoreQuantity'];
    $qtyMcs       = $row['StoreMcs'];
    $qty          = ($SelectedUom === 'MCS') ? $qtyMcs : $qtyKgs;

    if (empty($typeProduct)) continue;

    $newiqf[$typeProduct][$freezingType][$packingType] =
        ($newiqf[$typeProduct][$freezingType][$packingType] ?$newiqf[$typeProduct][$freezingType][$packingType]:0) + $qty;

    // MASTER / DUMMY
    if (in_array($packingType, ['MASTER', 'DUMMY'])) {
        $PackingTypeID[$typeProduct][$packingType] =
            ($PackingTypeID[$typeProduct][$packingType] ?$PackingTypeID[$typeProduct][$packingType]:0) + $qty;
    }

    if (in_array($packingType, ['DUMMY'])) {
        $dummy_cnt += $qty;
    }

    // IQF / BLOCK
    if (in_array($freezingType, ['IQF','SIQF','BLOCK'])) {
        $PackingTypeID[$typeProduct][$freezingType] =
            ($PackingTypeID[$typeProduct][$freezingType] ?$PackingTypeID[$typeProduct][$freezingType]: 0) + $qty;
    }
    $totkgs_cs += $qty;
}

foreach (['RAW', 'COOKED', 'BLANCHED'] as $typeProduct) {
    foreach (['MASTER', 'DUMMY', 'IQF','SIQF', 'BLOCK'] as $category) {
        if (!isset($PackingTypeID[$typeProduct][$category])) {
            $PackingTypeID[$typeProduct][$category] = 0;
        }
    }
}

$SqlShiftDetails = "
SELECT
    BlockName,
    SUM(StoreMcs) AS MCS,
    SUM(KGS) AS KGS,
    SUM(LKGS) AS LKGS,
    SUM(MasterKgs) AS MasterKgs,
    SUM(DummyKgs) AS DummyKgs,
    SUM(IqfKgs) AS IqfKgs,
    SUM(BlockKgs) AS BlockKgs,
    StoreCapacity,
    ColorCode
FROM (
    SELECT
        COALESCE(CSBD.BlockName,'OnFloor') AS BlockName,
        (
            CSD.AvalMcs +
            (CSD.LooseBags / NULLIF(APS.TotalSlabs, 0))
        ) AS StoreMcs,
        COALESCE(CSD.AvalKgs, 0) AS KGS,
        CSD.LooseBags * APS.SlabWeight * APS.MFactor AS LKGS,
        CASE
            WHEN UPPER(CSD.PackingTypeID) = 'MASTER'
            THEN COALESCE(CSD.AvalKgs, 0)
            ELSE 0
        END AS MasterKgs,
        CASE
            WHEN UPPER(CSD.PackingTypeID) = 'DUMMY'
            THEN COALESCE(CSD.AvalKgs, 0)
            ELSE 0
        END AS DummyKgs,
        CASE
            WHEN UPPER(CSD.FreezingType) = 'IQF'
            THEN COALESCE(CSD.AvalKgs, 0)
            ELSE 0
        END AS IqfKgs,
        CASE
            WHEN UPPER(CSD.FreezingType) = 'BLOCK'
            THEN COALESCE(CSD.AvalKgs, 0)
            ELSE 0
        END AS BlockKgs,
        COALESCE(CSBD.StoreCapacity,'0') AS StoreCapacity,
        COALESCE(CSBD.ColorCode,'#FF0000') AS ColorCode
    FROM CsStockData CSD
    LEFT JOIN AdminPackingStyles APS ON APS.PCKID = CSD.PackingStyleID
    LEFT JOIN ScrmPIVarietyList PIVL ON PIVL.VarietyID = CSD.VarietyID
    LEFT JOIN CsStoresRackData CSRD ON CSRD.RowID = CSD.StoreRackID
    LEFT JOIN CsStoresBlockData CSBD ON CSBD.BlockID = CSRD.BlockID
    WHERE (CSD.AvalMcs != 0 OR CSD.LooseBags != 0)
        $Qry
        $speciesFilter
) AS BlockSummary
WHERE BlockName != 'OnFloor'
GROUP BY BlockName, StoreCapacity, ColorCode
";
   //echo $SqlShiftDetails; exit;
   $StmtShiftDetails = $iconn->query($SqlShiftDetails);
   $acls = ["#5182ef","#40c79a","#4AA3FF","#FCB92C","#FF3D60","#343A40","#5182ef","#40c79a","#4AA3FF","#FCB92C","#FF3D60","#343A40"];
   $i=0;
   $high_dunt_data = [];
   $cscjgs = 0;
   while ($AttShiftRow = $StmtShiftDetails->fetch_array()) {
       //$AttShiftRow['Color'] = $AttShiftRow['ColorCode'];
       $AttShiftRow['Color'] = $acls[$i];
       $AttShiftRow['Total'] = round(($AttShiftRow['KGS']) , 2);
       $cscjgs += round(($AttShiftRow['KGS']) , 2);
       $AttShiftRow['TotalMcs'] =round($AttShiftRow['MCS'],2);
       $AttShiftRow['MasterT'] = round(((float)$AttShiftRow['MasterKgs']) / 1000, 2);
       $AttShiftRow['DummyT'] = round(((float)$AttShiftRow['DummyKgs']) / 1000, 2);
       $AttShiftRow['IqfT'] = round(((float)$AttShiftRow['IqfKgs']) / 1000, 2);
       $AttShiftRow['BlockKgs'] = round(((float)$AttShiftRow['BlockKgs']) / 1000, 2);
       $Series[] = ($SelectedUom === 'MCS')
           ? ($AttShiftRow['MCS'])
           : ($AttShiftRow['KGS']);
       $Lables[] = $AttShiftRow['BlockName'];
       //$Colors[] = $AttShiftRow['ColorCode'];
       $Colors[] = $acls[$i];
       $high_dunt_data[$AttShiftRow['BlockName']] = ($SelectedUom === 'MCS')
           ? ($AttShiftRow['MCS'])
           : ($AttShiftRow['KGS']);
       $AryShiftDtls[] = $AttShiftRow;
       $i++;
   }
   $floor_stock_weight = 0;
   if( isset($AryShiftDtls) && is_array($AryShiftDtls) ){
        $AttShiftRow['BlockName'] = 'OnFloor';
        $AttShiftRow['Color'] = $acls[$i];

        $AttShiftRow['Total'] = $totkgs_cs - round(array_sum(array_column($AryShiftDtls,($SelectedUom === 'MCS') ? 'TotalMcs' : 'Total')), 2);
        $AttShiftRow['TotalMcs'] =$totkgs_cs - round(array_sum(array_column($AryShiftDtls,($SelectedUom === 'MCS') ? 'TotalMcs' : 'Total')), 2);
        if($totkgs_cs - $cscjgs <=0 ) {
            /*$AttShiftRow['Total'] = 0;
            $AttShiftRow['TotalMcs'] = 0;*/
        }
        $floor_stock_weight = ($SelectedUom === 'MCS')?$AttShiftRow['TotalMcs']:$AttShiftRow['Total'];
        $Series[] = ($SelectedUom === 'MCS')
           ? ((float)$AttShiftRow['MCS'])
           : ($AttShiftRow['KGS']);
        $AryShiftDtls[] = $AttShiftRow;
   }

   if($floor_stock_weight<0){
    $floor_stock_weight = 0;
   }

   //$TotalStoreStock = round(array_sum($Series), 2);
   $TotalStoreStock = round(array_sum(array_column(
       $AryShiftDtls,
       ($SelectedUom === 'MCS') ? 'TotalMcs' : 'Total'
   )), 2);

   //echo "<pre>"; print_r($_SESSION); echo "</pre>"; exit;

   $store_cond = "";
   if( isset($PID) && $PID>0){
        $query_getstoreid = "select StoreID from CsStores where ProductionPlantID = ".$PID;
        $smt_getstoreid = $iconn->query($query_getstoreid);
        $fetch_storeid = $smt_getstoreid->fetch_array();
        if(isset($fetch_storeid['StoreID']) && $fetch_storeid['StoreID'] > 0){
            $store_cond = " and CSRD.StoreID = ".$fetch_storeid['StoreID'];
        }
   }

   // --- Store Availability: TotalRacks vs Filled by Block ---
   $PlantBlockFilter = ($PID > 0) ? "
AND CSRD.BlockID IN (
    SELECT DISTINCT CSRD2.BlockID
    FROM CsStoresRackData CSRD2
    INNER JOIN CsStockData CSD
        ON CSD.StoreRackID = CSRD2.RowID
    LEFT JOIN ScrmPIVarietyList PIVL ON PIVL.VarietyID = CSD.VarietyID
    WHERE CSD.PlantID = $PID
      AND CSD.StockStatus = 1
      AND (CSD.AvalMcs <> 0 OR CSD.LooseBags <> 0)
      $speciesFilterSub
)" : "";

$SqlStoreAvailability = "
SELECT
    CSRD.BlockID,
    CSBD.BlockName,
    COUNT(*) AS TotalRacks,

    COUNT(
        DISTINCT CASE
            WHEN EXISTS (
                SELECT 1
                FROM CsStockData CSD
                LEFT JOIN ScrmPIVarietyList PIVL ON PIVL.VarietyID = CSD.VarietyID
                WHERE CSD.StoreRackID = CSRD.RowID
                  AND CSD.StockStatus = 1
                  AND (CSD.AvalMcs <> 0 OR CSD.LooseBags <> 0)
                  " . ($PID > 0 ? "AND CSD.PlantID = $PID" : "") . "
                  $speciesFilterSub
            )
            THEN CSRD.RowID
        END
    ) AS Filled

FROM CsStoresRackData CSRD
LEFT JOIN CsStoresBlockData CSBD
    ON CSRD.BlockID = CSBD.BlockID

WHERE CSRD.Path = 1 $store_cond
$PlantBlockFilter

GROUP BY CSRD.BlockID, CSBD.BlockName, CSRD.StoreID
ORDER BY CSRD.StoreID, CSRD.BlockID ASC";

//echo $SqlStoreAvailability; exit;
   $StoreAvailabilityData = [];
   $StmtStoreAvailability = $iconn->query($SqlStoreAvailability);
   if ($StmtStoreAvailability) {
       while ($SARow = $StmtStoreAvailability->fetch_array()) {
           $total = (int)$SARow['TotalRacks'];
           $filled = (int)$SARow['Filled'];
           $StoreAvailabilityData[] = [
               'BlockName'      => $SARow['BlockName'],
               'TotalRacks'     => $total,
               'Filled'         => $filled,
               'AvailableRacks' => $total - $filled,
           ];
       }
   }

   // --- Order Status: AvalMcs by StationName (with OrderMcs, LooseBags, KGS) ---
   /*$SqlOrderStatus = "SELECT
       CSD.PlantID,
       RPS.StationName,
       SUM(SPGD.Mcs) AS OrderMcs,
       SUM(CSD.AvalMcs) AS AvalMcs,
       SUM(CSD.LooseBags) AS LooseBags,
       SUM(
           (COALESCE(CSD.AvalMcs, 0) * COALESCE(APS.TotalSlabs, 0) * COALESCE(APS.SlabWeight, 0) * COALESCE(APS.MFactor, 0)) +
           (COALESCE(CSD.LooseBags, 0) * COALESCE(APS.SlabWeight, 0) * COALESCE(APS.MFactor, 0))
       ) AS AvalKgs,
       SUM(
           COALESCE(SPGD.Mcs, 0) * COALESCE(APS.TotalSlabs, 0) * COALESCE(APS.SlabWeight, 0) * COALESCE(APS.MFactor, 0)
       ) AS OrderKgs
   FROM CsStockData CSD
   LEFT JOIN RmProcessingStations RPS ON RPS.ProcessingStationID = CSD.PlantID
   LEFT JOIN ScrmPoData SPD ON SPD.PoID = CSD.PoID
   LEFT JOIN ScrmPoGradeData SPGD ON SPGD.InvId = CSD.PoID
   LEFT JOIN AdminPackingStyles APS ON APS.PCKID = CSD.PackingStyleID
   LEFT JOIN ScrmPIVarietyList PIVL ON PIVL.VarietyID = CSD.VarietyID
   WHERE (CSD.AvalMcs > 0 OR CSD.LooseBags > 0)
       $Qry
       $speciesFilter
   GROUP BY CSD.PlantID, RPS.StationName
   ORDER BY CSD.PlantID, AvalMcs DESC";*/
   $podPlantFilter = "";
    if ($AccessType != 1 && $UnitID != 0) {
        $podPlantFilter = "AND FIND_IN_SET('" . (int)$UnitID . "', POD.ProductionPlantID) > 0";
    } else if ($selectedPid > 0) {
        $podPlantFilter = "AND FIND_IN_SET('$selectedPid', POD.ProductionPlantID) > 0";
    }
    $colConditions = "";
    $csdPlantSubFilter = "";
    if ($AccessType != 1 && $UnitID != 0) {
        $csdPlantSubFilter = "AND CSD2.PlantID = " . (int)$UnitID;
    } else if ($selectedPid > 0) {
        $csdPlantSubFilter = "AND CSD2.PlantID = " . $selectedPid;
    }
   $baseFrom = "
        FROM ScrmPoData POD
        LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID = POD.ConsigneeID
        LEFT JOIN (
            SELECT CSD2.PoID, CSD2.PlantID,
                   MIN(CSD2.PackingTypeID) AS PackingTypeID,
                   SUM(COALESCE(CSD2.AvalMcs,0) + (COALESCE(CSD2.LooseBags,0) / NULLIF(APS2.TotalSlabs,0))) AS AvalMcs,
                   SUM(COALESCE(CSD2.LooseBags,0)) AS LooseBags,
                   SUM((COALESCE(CSD2.AvalMcs,0) * APS2.TotalSlabs * APS2.SlabWeight * APS2.MFactor) + (COALESCE(CSD2.LooseBags,0) * APS2.SlabWeight * APS2.MFactor)) AS FinalKgs
            FROM CsStockData CSD2
            LEFT JOIN AdminPackingStyles APS2 ON APS2.PCKID = CSD2.PackingStyleID
            LEFT JOIN ScrmPIVarietyList PIVL ON PIVL.VarietyID = CSD2.VarietyID
            WHERE 1=1 $csdPlantSubFilter $speciesFilter
            GROUP BY CSD2.PoID, CSD2.PlantID
        ) CSD ON CSD.PoID = POD.PoID
        LEFT JOIN (
            SELECT SPGD2.InvId,
                   SUM(COALESCE(SPGD2.Mcs,0)) AS Mcs,
                   SUM(SPGD2.Mcs * APS3.TotalSlabs * APS3.SlabWeight * APS3.MFactor) AS OrderedKgs
            FROM ScrmPoGradeData SPGD2
            JOIN AdminPackingStyles APS3 ON APS3.PCKID = SPGD2.PackingStyleID
            GROUP BY SPGD2.InvId
        ) SPGD ON SPGD.InvId = POD.PoID
        LEFT JOIN RmProcessingStations RPS ON RPS.ProcessingStationID = CSD.PlantID
        WHERE POD.PoID NOT IN (SELECT PiID FROM ScrmCustomInvoices WHERE StockOutDate != '')
        AND POD.Status NOT IN (2,3,4)
        AND POD.ExporterID = 1
        $podPlantFilter
        $colConditions
    ";
    $groupCols = "POD.PoID, POD.PoNo";

    $orderExpr = "COALESCE(SPGD.Mcs,0)";
    $avalExpr = "COALESCE(CSD.AvalMcs,0)";

    $statusCase = getOrderStatusSqlCase($avalExpr, $orderExpr);

    /* --- Summary counts (before pagination/search) --- */
    $statusCaseAlias = getOrderStatusSqlCase('packedQty', 'orderQty');
    $summaryQuery = "SELECT
        COUNT(*) AS TotalOrders,
        SUM(CASE WHEN ($statusCaseAlias) = 'Completed' THEN 1 ELSE 0 END) AS CompletedOrders,
        SUM(CASE WHEN ($statusCaseAlias) = 'Pending' THEN 1 ELSE 0 END) AS PendingOrders,
        SUM(CASE WHEN ($statusCaseAlias) = 'Partial' THEN 1 ELSE 0 END) AS PartialOrders
    FROM (
        SELECT
            ROUND($orderExpr, 2) AS orderQty,
            ROUND($avalExpr, 2) AS packedQty
        $baseFrom
        GROUP BY $groupCols
    ) AS summary_data";
    //echo $summaryQuery; exit;
    $summaryResult = $iconn->query($summaryQuery);
    $summaryRow = $summaryResult ? $summaryResult->fetch_assoc() : null;
    $totalOrders = $summaryRow ? (int)$summaryRow['TotalOrders'] : 0;
    $completedOrders = $summaryRow ? (int)$summaryRow['CompletedOrders'] : 0;
    $pendingOrders = $summaryRow ? (int)$summaryRow['PendingOrders'] : 0;
    $partialOrders = $summaryRow ? (int)$summaryRow['PartialOrders'] : 0;

    /* Compute percentages rounded to 2 decimal places */
    $completedPct = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 2) : 0;
    $pendingPct = $totalOrders > 0 ? round(($pendingOrders / $totalOrders) * 100, 2) : 0;
    $partialPct = $totalOrders > 0 ? round(($partialOrders / $totalOrders) * 100, 2) : 0;

   $csdPlantSubFilter = ($PID > 0) ? "AND CSD2.PlantID = $PID" : "";
   $podPlantCond = ($PID > 0) ? "AND FIND_IN_SET('$PID', POD.ProductionPlantID) > 0" : "";
$podPlantCond ="";
   if($PID > 0){
        $sqlGetProid = " SELECT * FROM `AdminExporterDetails` where ProcessingStationID = '".$PID."' ";
        $restult = $iconn->query($sqlGetProid);
        $row = $restult->fetch_assoc();
        $proccessorId = 0 ;
        if( isset($row['ExpID']) && $row['ExpID']>0 ){
            $proccessorId = $row['ExpID'];
            $podPlantCond = " AND POD.ProcessorID =". $proccessorId;
        }
   }

   $shipmentPlantFilter = '';
   if ($PID > 0) {
       $shipmentPlantFilter = " AND AED.ProcessingStationID = " . (int)$PID . " ";
   }

   $station_query = "SELECT * FROM `RmProcessingStations`";
   $station_smtp = $iconn->query($station_query);
   $staitonnamelist = array();
   while( $rowStationnames = $station_smtp->fetch_array(MYSQLI_ASSOC) ){
        $staitonnamelist[$rowStationnames['ProcessingStationID']] = $rowStationnames['StationName'];
   }

   $SqlOrderStatus = "SELECT
    POD.PoID,
    POD.PoNo,
    POD.LotNo,
    POD.ShipByDate,

    ROUND(COALESCE(POG.OrderedKgs, 0), 3) AS OrderedKgs,

    ROUND(COALESCE(STK.FinishedKgs, 0), 3) AS FinishedKgs,

    ROUND(
        GREATEST(
            COALESCE(POG.OrderedKgs, 0) -
            COALESCE(STK.FinishedKgs, 0),
            0
        ),
        3
    ) AS BalanceKgs,

    ROUND(
        CASE
            WHEN COALESCE(POG.OrderedKgs, 0) > 0
            THEN
                (
                    COALESCE(STK.FinishedKgs, 0)
                    / POG.OrderedKgs
                ) * 100
            ELSE 0
        END,
        2
    ) AS CompletionPercentage,

    CASE

        WHEN COALESCE(POG.OrderedKgs, 0) = 0
            THEN 'Pending'

        WHEN COALESCE(STK.FinishedKgs, 0) = 0
            THEN 'Pending'

        WHEN COALESCE(STK.FinishedKgs, 0) >= COALESCE(POG.OrderedKgs, 0)
            THEN 'Completed'

        ELSE 'Partial'

    END AS PoStatus

FROM ScrmPoData AS POD



LEFT JOIN
(
    SELECT
        SPGD.InvId AS PoID,

        SUM(
            CAST(COALESCE(SPGD.Mcs, 0) AS DECIMAL(15,3))
            *
            COALESCE(APS.TotalSlabs, 0)
            *
            CAST(COALESCE(APS.SlabWeight, 0) AS DECIMAL(15,3))
            *
            CAST(COALESCE(APS.MFactor, 0) AS DECIMAL(15,3))
        ) AS OrderedKgs

    FROM ScrmPoGradeData AS SPGD

    LEFT JOIN AdminPackingStyles AS APS
        ON APS.PCKID = SPGD.PackingStyleID

    GROUP BY SPGD.InvId

) AS POG
    ON POG.PoID = POD.PoID



LEFT JOIN
(
    SELECT
        SPGD.InvId AS PoID,

        SUM(
            CAST(COALESCE(CSD.AvalMcs, 0) AS DECIMAL(15,3))
            *
            COALESCE(APS.TotalSlabs, 0)
            *
            CAST(COALESCE(APS.SlabWeight, 0) AS DECIMAL(15,3))
            *
            CAST(COALESCE(APS.MFactor, 0) AS DECIMAL(15,3))
        )
        +
        SUM(
            CAST(COALESCE(CSD.LooseBags, 0) AS DECIMAL(15,3))
            *
            CAST(COALESCE(APS.SlabWeight, 0) AS DECIMAL(15,3))
            *
            CAST(COALESCE(APS.MFactor, 0) AS DECIMAL(15,3))
        ) AS FinishedKgs

    FROM ScrmPoGradeData AS SPGD

    INNER JOIN CsStockData AS CSD
        ON CSD.PoGradeId = SPGD.ID

    LEFT JOIN AdminPackingStyles AS APS
        ON APS.PCKID = CSD.PackingStyleID

    GROUP BY SPGD.InvId

) AS STK
    ON STK.PoID = POD.PoID



WHERE
    POD.Status NOT IN (2, 3, 4)
    $podPlantCond
    AND NOT EXISTS
    (
        SELECT 1
        FROM ScrmCustomInvoices AS SCI
        WHERE SCI.PiID = POD.PoID
          AND SCI.StockOutDate != ''
    )
ORDER BY `BalanceKgs` ASC;";
//echo $SqlOrderStatus;
   $OrderStatusData = [];
   $StmtOrderStatus = $iconn->query($SqlOrderStatus);
   $stationAgg = [];
   if ($StmtOrderStatus) {

    while (
        $OSRow =
        $StmtOrderStatus->fetch_array(MYSQLI_ASSOC)
    ) {
        if ($PID > 0) {
            $plantId = (int)$PID;
        }else{
            $plantId = (int)$OSRow['PlantID'];
        }

        /*
         * Replace this with your actual plant/station name
         * lookup if you have a plant master table.
         */
        $stationName = $staitonnamelist[$plantId];


        if (!isset($stationAgg[$plantId])) {

            $stationAgg[$plantId] = [

                'PlantID' => $plantId,

                'StationName' => $stationName,

                'TotalOrders' => 0,

                'CompletedCount' => 0,

                'PendingCount' => 0,

                'PartialCount' => 0,

                'CompletedPercent' => 0,

                'PendingPercent' => 0,

                'PartialPercent' => 0,

                'OrderKgs' => 0,

                'AvalKgs' => 0,

                'BalanceKgs' => 0

            ];
        }


        /*
         * Reference current plant
         */
        $s = &$stationAgg[$plantId];


        /*
         * Total PO count
         */
        $s['TotalOrders']++;


        /*
         * Quantities
         */
        $s['OrderKgs'] +=
            (float)$OSRow['OrderedKgs'];

        $s['AvalKgs'] +=
            (float)$OSRow['FinishedKgs'];

        $s['BalanceKgs'] +=
            (float)$OSRow['BalanceKgs'];


        /*
         * Status count
         */
        switch ($OSRow['PoStatus']) {

            case 'Completed':

                $s['CompletedCount']++;

                break;


            case 'Partial':

                $s['PartialCount']++;

                break;


            case 'Pending':

                $s['PendingCount']++;

                break;
        }


        unset($s);
    }


    /* =====================================================
       CALCULATE PLANT-WISE PERCENTAGES
       ===================================================== */

    foreach ($stationAgg as &$s) {

        $totalOrders =
            (int)$s['TotalOrders'];


        if ($totalOrders > 0) {

            $s['CompletedPercent'] = round(

                (
                    $s['CompletedCount']
                    /
                    $totalOrders
                ) * 100,

                2
            );


            $s['PartialPercent'] = round(

                (
                    $s['PartialCount']
                    /
                    $totalOrders
                ) * 100,

                2
            );


            $s['PendingPercent'] = round(

                (
                    $s['PendingCount']
                    /
                    $totalOrders
                ) * 100,

                2
            );

        } else {

            $s['CompletedPercent'] = 0;

            $s['PartialPercent'] = 0;

            $s['PendingPercent'] = 0;
        }


        /*
         * Round quantities
         */
        $s['OrderKgs'] =
            round($s['OrderKgs'], 3);

        $s['AvalKgs'] =
            round($s['AvalKgs'], 3);

        $s['BalanceKgs'] =
            round($s['BalanceKgs'], 3);
    }

    unset($s);


    /*
     * IMPORTANT:
     * Convert associative array to normal JSON array.
     */
    $OrderStatusData =
        array_values($stationAgg);
}
   ?>

<!doctype html>
<html lang="en">
   <head>
      <?php include($IncludesPath . "title-meta.php"); ?>
      <!-- jquery.vectormap css -->
      <link href="<?php echo $BaseDist ?>assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
      <!-- DataTables -->
      <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
      <!-- Responsive datatable examples -->
      <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
      <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
      <!-- <link href="<?php echo $BaseDist ?>assets/css/tailwindcss-v4.2.1.css?=<?php echo rand() ?>" rel="stylesheet" type="text/css" /> -->
      <link href="/assets/css/newstyle.css" rel="stylesheet" type="text/css" />
      <link href="<?php echo $BaseUrl ?>/v3/assets/css/purchase-coldstore-theme.css?=<?php echo rand() ?>" rel="stylesheet" type="text/css" />
      <style>
        .clickable-count {

    padding: 6px 12px;
    color: #5182ef;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
    text-decoration: underline;
}

.clickable-count:hover {
    background: #5182ef;
    color: white;
    transform: translateY(-1px);

}

.clickable-count:active {
    transform: scale(0.98);
}
        #chartjs-tooltip {
    position: absolute;
    pointer-events: none;
    z-index: 9999;
    transform: translate(-50%, -100%);
    transition: all 0.12s ease;
}

.chart-tooltip-box {
    background: #ffffff;
    border-radius: 8px;
    padding: 10px 12px;
    min-width: 280px;
    max-width: 520px;

    border: 1px solid #e5e7eb; /* light gray border */

    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08); /* soft shadow */

    font-family: Arial, sans-serif;
    color: #111827; /* dark text like dashboards */
}
        #chartjs-tooltip {
    position: absolute;
    pointer-events: none;
    z-index: 9999;
    transform: translate(-50%, -100%);
    transition: all 0.12s ease;
}

.chart-tooltip-box {
    background: #ffffff;
    border-radius: 8px;
    padding: 10px 12px;
    min-width: 280px;
    max-width: 520px;

    border: 1px solid #e5e7eb; /* light gray border */

    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08); /* soft shadow */

    font-family: Arial, sans-serif;
    color: #111827; /* dark text like dashboards */
}

.chart-tooltip-arrow {
    width: 0;
    height: 0;
    margin: 0 auto;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-top: 8px solid #ffffff;

    filter: drop-shadow(0 -2px 1px rgba(0,0,0,0.08));
}
        .chart-wrapper{
    position: relative;
    width: 100%;
    height: 400px;
    min-height: 400px;
    max-height: 400px;
    overflow: hidden;
}
         body{font-family: Inter;color: oklch(44.6% 0.03 256.802);}
         label{color: oklch(44.6% 0.03 256.802)!important;}
         label {
         font-size: 15px;
         color: black;
         }
         th {
         font-size: 12px;
         }
         .table tr:hover {
         background-color: #ebeff1 !important;
         color: #000 !important;
         }
         .table td,
         .table th {
         font-size: 12px;
         padding: 7px !important;
         vertical-align: top;
         border-top: 1px solid #eff2f7;
         }
         .customWrap {
         white-space: nowrap !important;
         }
         .warning-row {
         background-color: #f7ff84 !important;
         color: #111;
         font-weight: 500 !important;
         }
         .success-row {
         background-color: #fff !important;
         }
         .deadline-row {
         background-color: #df411d !important;
         color: #ffffff !important;
         font-weight: 500 !important;
         }
         /*.select2-container--default.select2-container--disabled .select2-selection--single .select2-selection__rendered {
         background-color: #f3f4f6;
         cursor: default;
         }*/
         div.dataTables_wrapper div.dataTables_filter input{
         border-radius: 14px;
         height: calc(1.5em + .94rem + 2px);
         }
         .rowFilter label {
         font-size: .85rem;
         font-family: 'Inter';
         font-weight: 600;
         color: #4b5563;
         text-transform: uppercase;
         letter-spacing: .03em;
         }
         /*03-apr-26*/
         /*.select2-container--default .select2-results__option--highlighted[aria-selected] {
         background-color: #eef4ff !important;
         color: #2b63ff !important;
         }
         .select2-container--default .select2-results__option[aria-selected=true]:hover {
         background: #eef4ff !important;
         color: #2b63ff !important;
         }
         #btnSearch{
         background: linear-gradient(90deg,#3b82f6,#6366f1);
         padding: 10px 25px;
         border: none;
         border-radius: 10px;
         color: #fff;
         font-size: 1rem;
         font-weight: 700;
         box-shadow: 0 4px 10px rgba(99,102,241,.3);
         transition: .25s;
         letter-spacing: .5px;
         }
         .select2-container--default .select2-search--dropdown .select2-search__field{
         border-radius: 14px;
         padding: 10px;
         box-shadow: 0 4px 14px rgba(0,0,0,.08);
         }*/
         /* .select2-container--open {
         z-index: 9999999
         }             */
         .rowFilter label{color: oklch(44.6% 0.03 256.802);}
         /*     .rowFilter .select2 span{background: #f4f4f5;}
         .select2-container .select2-selection--single{height: unset!important;border-radius: 5px;}*/

         /*03-apr-26*/
         /*.select2-container .select2-selection--single .select2-selection__rendered{color: #383838!important;}
         .select2-selection--single{background-color: #f4f4f5!important;border:unset!important;height: unset!important;}*/

         .summary-panel-modern{
         display:flex;flex-wrap:wrap;gap:20px;
         font-family:'Segoe UI',Tahoma,sans-serif;justify-content:center;
         margin-bottom: 1.5rem;
         }
         .summary-card-modern{
         flex:1 1 180px;
         /*padding:18px 22px;*/
         display:flex;align-items:center;gap:14px;
         transition:.25s ease;position:relative;overflow:hidden
         --tw-shadow: 0 4px 6px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 2px 4px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.1));
         box-shadow: var(--tw-inset-shadow), var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow);
         }
         .summary-icon{
         width:48px;height:48px;background:#eef2ff;
         border-radius:10px;display:flex;align-items:center;justify-content:center
         }
         .summary-card-modern .rounded-lg{
         width: 100%;
         }
         .p-5 {
         padding: calc(var(--spacing) * 5)!important;
         }
         .p-3 {
         padding: calc(var(--spacing) * 3)!important;
         }
         .text-xs,.text-3xl{ margin-bottom:0px;font-weight:400; }
         .border-1 {
         border-style: var(--tw-border-style);
         border-width: 1px;
         }
         table.table-bordered.dataTable tbody th, table.table-bordered.dataTable tbody td{
         color: oklch(37.3% 0.034 259.733);
         }
         #ibtTable_wrapper .dataTables_scrollBody,
         #poTable_wrapper .dataTables_scrollBody {
         overflow: auto !important;
         }
         #ibtTable_length label,
         #poTable_length label{margin-bottom: 0px!important;}
         #ibtTable_filter,
         #poTable_filter{margin-left: 20px;}
         #ibtTable_filter label{
         display: inline-flex;
         align-items: center;
         margin-bottom: 0;
         }
         #key-datatableFullscreen{
            background-color: #fff;
         }
         #key-datatableFullscreen_filter .dt-search-input{
         padding-left: 48px !important;
         border-color: #4f46e5;
         }
         #ibtTable_filter .dt-search-input{
         padding-left: 48px !important;
         border-color: #4f46e5;
         }
         #poTable_filter .dt-search-input{
         padding-left: 48px !important;
         border-color: #4f46e5;
         }
         .dt-search-input {
         padding: 8px 12px 8px 40px;
         width: 320px;
         border-radius: 8px;
         border: 1px solid #e5e7eb;
         border-color: #4f46e5;
         font-size: 14px;
         }
         .dt-search-input:focus {
         outline: none;
         border-color: #6366f1;
         box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
         }
         /*03-apr-26*/
         /*.select2-selection .select2-selection__rendered{width: 100%;
         background: #ffffff;
         border: 1px solid #dfe3e8;
         border-left-width: 1px;
         border-left-style: solid;
         border-left-color: rgb(223, 227, 232);
         border-left: 4px solid #3b82f6;
         border-radius: 12px;
         padding: 5px 12px 5px 14px;
         position: relative;
         cursor: pointer;
         transition: 0.2s;
         display: flex;
         align-items: center;
         gap: 8px;
         position: relative;
         }
         .select2-container .select2-selection--single .select2-selection__arrow{
         margin-top: 5px;margin-right: 3px;
         }
         .select2-results li {
         font-size: 14px;
         align-items: center;
         display: block;
         width: 100%;
         clear: both;
         font-weight: 400;
         text-align: inherit;
         white-space: nowrap;
         border: 0;
         padding: 8px 10px;
         border-radius: 8px;
         }
         .select2-container .select2-selection--single .select2-selection__rendered
         {
         line-height: 30px;
         font-size: 14px;
         color: #383838 !important;
         }*/
         /*#home .page-content,#profile .page-content,#messages .page-content{padding-top: 30px!important;}*/
         .tab-pane .page-content{padding-top: 25px!important;}
         .card-title{color: oklch(44.6% 0.03 256.802); font-size: 18px; margin: 0 0 7px 0;font-weight: 600; text-align: center;}
         .chart-container {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 300px;
}
.summary-card {
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        .summary-header {
            color: #fff;
            font-weight: 600;
            text-align: center;
            padding: 12px;
            font-size: 18px;
        }

        .summary-body {
            padding: 0;
        }

        .summary-body div {
            padding: 14px;
            border-bottom: 1px solid #eee;
            text-align: center;
            font-weight: 500;
        }

        .summary-footer {
            color: #fff;
            font-weight: 600;
            text-align: center;
            padding: 12px;
            font-size: 16px;
        }


        /* Colors */
        .aging-header, .aging-footer {
            background: #40c79a;
        }

        .quantity-header, .quantity-footer {
            background: #5182ef;
        }
        .dt-search-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .ri-search-line {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            text-align: center;
            line-height: 1;
            font-size: 15px;
            color: #9ca3af;
            pointer-events: none;
            z-index: 2;
        }
        table.dataTable.nowrap th, table.dataTable.nowrap td {
            white-space: nowrap;
            font-size: 14px;
        }
</style>
    <?php include($IncludesPath . "head-css.php"); ?>
<style>
    label {
        font-size: 15px;
        color: black;
    }

    th {
        font-size: 12px;
    }


    .table tr:hover {
        background-color: #ebeff1 !important;
        color: #000 !important;
    }
    .table tr:hover td, .table tfoot tr:hover th {
        color: #000000 !important;
    }

    .table td,
    .table th {
        font-size: 12px;
        padding: 7px !important;
        vertical-align: top;
        border-top: 1px solid #eff2f7;
    }

    .customWrap {
        white-space: nowrap !important;
    }

    .warning-row {
        background-color: #f7ff84 !important;
        color: #111;
        font-weight: 500 !important;
    }

    .success-row {
        background-color: #fff !important;
    }

    .deadline-row {
        background-color: #df411d !important;
        color: #ffffff !important;
        font-weight: 500 !important;
    }
</style>
</head>

    <body data-sidebar="dark">
        <?php include($IncludesPath . "PreLoader.php"); ?>
        <div id="layout-wrapper">
            <?php
            include($IncludesPath . "topbar.php");
            include($IncludesPath . "sidebar.php");
            ?>
            <div class="main-content">
                <div class="page-content">
                    <div class="container-fluid">
                        <?php include "PopUpTable.php"; ?>
                        <div class="row">
                           <div class="col-xl-12">
                              <div class="row" style="display:none;">
                                 <div class="col-md-12">
                                    <div class="card" >
                                       <div class="card-body">
                                          <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                                             <li class="nav-item">
                                                <a class="nav-link active" data-toggle="tab" href="#home" role="tab">
                                                <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                                <span class="d-none d-sm-block">Dashboard</span>
                                                </a>
                                             </li>
                                             <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#profile" role="tab">
                                                <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                                <span class="d-none d-sm-block">View Stock</span>
                                                </a>
                                             </li>
                                             <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#messages" role="tab">
                                                <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                                <span class="d-none d-sm-block">View Store</span>
                                                </a>
                                             </li>
                                          </ul>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <!-- end row -->
                           </div>
                        </div>
            <!-- Filters -->
            <div class="tab-content text-muted">
               <div class="tab-pane active" id="home" role="tabpanel">
                  <div class="row">
                     <div class="col-xl-12">
                        <div class="row">
                           <div class="col-md-12">
                              <div class="card" style="background-color: transparent;border-radius: 0px; box-shadow: none;">
                                 <div class="card-body">
                                    <div class="row">
                                       <div class="col-xl-12">
                                          <div class="card">
                                              <div class="card-body">
                                                  <div class="row">
                                                     <div class="col-lg-2">
                                                        <label for="drpColdStore">STATION</label>
                                                        <div class="form-group">
                                                           <select class="form-control select2" name="PurchaseStationID" id="PurchaseStationID">
                                                              <option value="">All Station</option>
                                                              <?php while ($DataRow = $StmtStoreFacility->fetch_array()) {
                                                                 $isDisabled = ($AccessType != 1 && $storeIDs != 0 && $DataRow['StoreID'] == $storeIDs);
                                                                 ?>
                                                              <option value="<?php echo $DataRow['ProductionPlantID']; ?>" <?php
                                                                 if ($DataRow['ProductionPlantID'] == $PID) {
                                                                     echo 'selected="selected"';
                                                                 }

                                                                 if ($isDisabled || $_SESSION['UnitID']) {
                                                                     echo ' disabled="disabled"';
                                                                 }
                                                                                                                     ?>>
                                                                 <?php echo $DataRow['StoreName']; ?>
                                                              </option>
                                                              <?php    } ?>
                                                           </select>
                                                        </div>
                                                     </div>
                                                     <div class="col-lg-2">
                                                        <label for="drpColdStore">YEAR</label>
                                                        <div class="form-group">
                                                           <select class="form-control select2" name="Year" id="Year">
                                                              <?php
                                                                 for ($year; $Startyear <= $endYear; $Startyear++) { ?>
                                                              <option value="<?php echo $Startyear ?>" <?php if (
                                                                 $Startyear == $Year
                                                                 ) {
                                                                 echo 'selected="selected"';
                                                                 } ?>>
                                                                 <?php echo $Startyear ?>
                                                              </option>
                                                              <?php  }
                                                                 ?>
                                                           </select>
                                                        </div>
                                                     </div>
                                                     <div class="col-lg-2">
                                                        <label for="drpColdStore">MONTH</label>
                                                        <div class="form-group">
                                                           <select class="form-control select2" name="Month" id="Month">
                                                           <?php
                                                              $start = strtotime('first day of this month');
                                                              for ($i = 0; $i < 12; $i++) {
                                                                  $time = strtotime(sprintf('%d months', $i), $start);

                                                                  $label = date('F', $time);
                                                                  $value = date('n', $time);
                                                                  echo "<option value='$value'";
                                                                  if ($PresentMonth == $value) {
                                                                      echo 'selected="selected"';
                                                                  }
                                                                  echo ">$label</option>";
                                                              }

                                                              ?>
                                                           </select>
                                                        </div>
                                                     </div>
                                                     <div class="col-lg-2">
                                                        <label for="UOM">UNIT</label>
                                                        <div class="form-group">
                                                           <select class="form-control select2" name="UOM" id="UOM">
                                                              <option value="KGS" <?php if ($SelectedUom === 'KGS') { echo 'selected="selected"'; } ?>>KGS</option>
                                                              <option value="MCS" <?php if ($SelectedUom === 'MCS') { echo 'selected="selected"'; } ?>>MCS</option>
                                                           </select>
                                                        </div>
                                                     </div>
                                                     <div class="col-lg-2">
                                                        <label for="UOM">Species</label>
                                                        <div class="form-group">
                                                           <select class="form-control select2" name="species" id="species">
                                                            <option value="">All</option>
                                                            <?php if (!empty($SpeciesList)) : ?>
                                                                <?php foreach ($SpeciesList as $speciesVal) : ?>
                                                                    <option value="<?= htmlspecialchars($speciesVal['ShortCode']) ?>"
                                                                        <?= ($SelectedSpecies === $speciesVal['ShortCode']) ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($speciesVal['SpeciesName']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </select>
                                                        </div>
                                                     </div>
                                                     <div align="center" class="col-lg-2">
                                                        <label for="drpColdStore">&nbsp;</label>
                                                        <div class="form-group">
                                                            <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light" style="background: linear-gradient(90deg, #3b82f6, #6366f1);
                                                               padding: 10px 25px;
                                                               border: none;
                                                               border-radius: 10px;
                                                               color: #fff;
                                                               font-size: 1rem
                                                               16px
                                                               ;
                                                               font-weight: 700;
                                                               box-shadow: 0 4px 10px rgba(99, 102, 241, .3);
                                                               transition: .25s;
                                                               letter-spacing: .5px;">
                                                            Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                                            </button>
                                                        </div>
                                                     </div>
                                                  </div>
                                                </div>
                                            </div>
                                       </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xl-12">
                                           <div class="row">
                                                <!--Cards start -->

                                               <div class="col-12">
                                                  <div class="summary-panel-modern">
                                                    <?php if(0){?>
                                                     <!-- <div class="summary-card-modern capacity-card" onclick="graphCapacity([&quot;200&quot;,&quot;35&quot;,165])">
                                                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-md p-5 border-1 border-blue-200" data-fg-rvh160="28.21:28.18466:/components/ViewStock.tsx:331:9:10272:627:e:div">
                                                           <div class="flex items-center justify-between" data-fg-rvh161="28.21:28.18466:/components/ViewStock.tsx:332:11:10391:493:e:div">
                                                              <div data-fg-rvh162="28.21:28.18466:/components/ViewStock.tsx:333:13:10455:274:e:div">
                                                                 <p class="text-xs text-blue-700 uppercase tracking-wide" data-fg-rvh163="28.21:28.18466:/components/ViewStock.tsx:334:15:10475:78:e:p">Today's Inwards</p>
                                                                 <p class="text-3xl mt-2 text-blue-900" data-fg-rvh165="28.21:28.18466:/components/ViewStock.tsx:335:15:10568:61:e:p" id="totalActiveStocks"><?php echo $countTodayInwards?$countTodayInwards:0 ?></p>
                                                                 <p class="text-xs text-blue-600 mt-1" data-fg-rvh167="28.21:28.18466:/components/ViewStock.tsx:336:15:10644:66:e:p">Active stock entries</p>
                                                              </div>
                                                              <div class="bg-blue-200 p-3 rounded-full" data-fg-rvh169="28.21:28.18466:/components/ViewStock.tsx:338:13:10742:125:e:div">
                                                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-7 h-7 text-blue-700" data-fg-rvh170="28.21:28.18466:/components/ViewStock.tsx:339:15:10803:45:e:Package::::::reo">
                                                                    <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                                                    <path d="M12 22V12"></path>
                                                                    <polyline points="3.29 7 12 12 20.71 7"></polyline>
                                                                    <path d="m7.5 4.27 9 5.15"></path>
                                                                 </svg>
                                                              </div>
                                                           </div>
                                                        </div>
                                                     </div> -->
                                                     <!-- <div class="summary-card-modern">
                                                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-md p-5 border-1 border-green-200" data-fg-rvh171="28.21:28.18466:/components/ViewStock.tsx:344:9:10909:629:e:div">
                                                           <div class="flex items-center justify-between" data-fg-rvh172="28.21:28.18466:/components/ViewStock.tsx:345:11:11031:492:e:div">
                                                              <div data-fg-rvh173="28.21:28.18466:/components/ViewStock.tsx:346:13:11095:268:e:div">
                                                                 <p class="text-xs text-green-700 uppercase tracking-wide" data-fg-rvh174="28.21:28.18466:/components/ViewStock.tsx:347:15:11115:75:e:p">Today's Outwards</p>
                                                                 <p class="text-3xl mt-2 text-green-900" data-fg-rvh176="28.21:28.18466:/components/ViewStock.tsx:348:15:11205:58:e:p" id="totalMcs"><?php echo $countTodayOutwards?$countTodayOutwards:0 ?></p>
                                                                 <p class="text-xs text-green-600 mt-1" data-fg-rvh178="28.21:28.18466:/components/ViewStock.tsx:349:15:11278:66:e:p">Active stock entries</p>
                                                              </div>
                                                              <div class="bg-green-200 p-3 rounded-full" data-fg-rvh180="28.21:28.18466:/components/ViewStock.tsx:351:13:11376:130:e:div">
                                                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up w-7 h-7 text-green-700" data-fg-rvh181="28.21:28.18466:/components/ViewStock.tsx:352:15:11438:49:e:TrendingUp::::::D6b0">
                                                                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                                                                    <polyline points="16 7 22 7 22 13"></polyline>
                                                                 </svg>
                                                              </div>
                                                           </div>
                                                        </div>
                                                     </div> -->
                                                    <?php }
                                                    $currentstoack_cs = $PackingTypeID['RAW']['MASTER'] + $PackingTypeID['RAW']['DUMMY'] + $PackingTypeID['COOKED']['MASTER'] + $PackingTypeID['COOKED']['DUMMY'] ; ?>
                                                     <div class="summary-card-modern capacity-card">
                                                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-md pt-5 pb-5 pl-2 pr-2 border-1 border-blue-200" data-fg-rvh182="28.21:28.18466:/components/ViewStock.tsx:357:9:11548:630:e:div">
                                                           <div class="flex items-center justify-between" data-fg-rvh183="28.21:28.18466:/components/ViewStock.tsx:358:11:11673:490:e:div">
                                                              <div class="ml-2" data-fg-rvh184="28.21:28.18466:/components/ViewStock.tsx:359:13:11737:267:e:div">
                                                                 <p class="text-xs text-blue-700 uppercase tracking-wide" data-fg-rvh185="28.21:28.18466:/components/ViewStock.tsx:360:15:11757:78:e:p">Current Stock</p>
                                                                 <p class="text-3xl mt-2 text-blue-900" data-fg-rvh187="28.21:28.18466:/components/ViewStock.tsx:361:15:11850:61:e:p" id="totalLooseBags"><?php echo $totkgs_cs?number_format_inr(round($totkgs_cs,2)):0 ?> <?php echo $SelectedUomLabel; ?></p>
                                                                 <p class="text-xs text-blue-600 mt-1" data-fg-rvh189="28.21:28.18466:/components/ViewStock.tsx:362:15:11926:59:e:p">Active stock <?php echo strtolower($SelectedUomLabel); ?></p>
                                                              </div>
                                                              <div class="bg-blue-200 p-3 rounded-full" data-fg-rvh191="28.21:28.18466:/components/ViewStock.tsx:364:13:12017:129:e:div">
                                                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-7 h-7 text-blue-700" data-fg-rvh192="28.21:28.18466:/components/ViewStock.tsx:365:15:12080:47:e:Package::::::reo">
                                                                    <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                                                    <path d="M12 22V12"></path>
                                                                    <polyline points="3.29 7 12 12 20.71 7"></polyline>
                                                                    <path d="m7.5 4.27 9 5.15"></path>
                                                                 </svg>
                                                              </div>
                                                           </div>
                                                        </div>
                                                     </div>

                                                     <div class="summary-card-modern capacity-card">
                                                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-md pl-2 pr-2 pt-2 pb-2 m2 border border-green-200 flex items-center justify-between">
                                                          <div class="flex-1 pr-2 ml-2">
                                                            <p class="text-xs text-green-700 uppercase tracking-wide font-semibold">Raw</p>
                                                            <p class="text-3xl mt-2 text-green-900" id="rawStockBags"><?php echo number_format_inr($PackingTypeID['RAW']['MASTER'] + $PackingTypeID['RAW']['DUMMY']) ?> <?php echo $SelectedUomLabel; ?></p>

                                                            <div class="text-xs text-green-700 mt-3 grid grid-cols-2 gap-4 border-t border-green-200/60 pt-2">
                                                              <div>
                                                                <span class="font-bold block text-green-600">IQF: <?php echo number_format_inr($PackingTypeID['RAW']['IQF'] + $PackingTypeID['RAW']['SIQF']); ?></span> <br>
                                                                <span class="block opacity-90">Master: <?php echo number_format_inr(($newiqf['RAW']['IQF']['MASTER']+$newiqf['RAW']['SIQF']['MASTER'])); ?></span>
                                                                <span class="block opacity-90">Dummy: <?php echo number_format_inr(($newiqf['RAW']['IQF']['DUMMY']+$newiqf['RAW']['SIQF']['DUMMY'])); ?></span>
                                                              </div>
                                                              <div>
                                                                <span class="font-bold block text-green-800">BLOCK: <?php echo number_format_inr(($PackingTypeID['RAW']['BLOCK'])); ?></span> <br>
                                                                <span class="block opacity-90">Master: <?php echo number_format_inr(($newiqf['RAW']['BLOCK']['MASTER'])); ?></span>
                                                                <span class="block opacity-90">Dummy: <?php echo number_format_inr(($newiqf['RAW']['BLOCK']['DUMMY'])); ?></span>
                                                              </div>
                                                            </div>
                                                          </div>
                                                          <div class="bg-green-200 p-3 rounded-full flex items-center justify-center shrink-0 align-top self-start mt-1">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-7 h-7 text-green-700">
                                                              <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                                              <path d="M12 22V12"></path>
                                                              <polyline points="3.29 7 12 12 20.71 7"></polyline>
                                                              <path d="m7.5 4.27 9 5.15"></path>
                                                            </svg>
                                                          </div>
                                                        </div>
                                                      </div>

                                                     <div class="summary-card-modern capacity-card">
                                                        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow-md pt-5 pb-5 pl-2 pr-2 border-1 border-red-200" data-fg-rvh182="28.21:28.18466:/components/ViewStock.tsx:357:9:11548:630:e:div">
                                                           <div class="flex items-center justify-between" data-fg-rvh183="28.21:28.18466:/components/ViewStock.tsx:358:11:11673:490:e:div">
                                                              <div class="ml-2" data-fg-rvh184="28.21:28.18466:/components/ViewStock.tsx:359:13:11737:267:e:div">
                                                                 <p class="text-xs text-red-700 uppercase tracking-wide" data-fg-rvh185="28.21:28.18466:/components/ViewStock.tsx:360:15:11757:78:e:p">COOKED</p>
                                                                 <p class="text-3xl mt-2 text-red-900" data-fg-rvh187="28.21:28.18466:/components/ViewStock.tsx:361:15:11850:61:e:p" id="totalLooseBags"><?php echo number_format_inr(($PackingTypeID['COOKED']['MASTER'] + $PackingTypeID['COOKED']['DUMMY'] + $PackingTypeID['BLANCHED']['MASTER'] + $PackingTypeID['BLANCHED']['DUMMY']) ) ?> <?php echo $SelectedUomLabel; ?></p>
                                                                 <p class="text-xs text-red-600 mt-1" data-fg-rvh189="28.21:28.18466:/components/ViewStock.tsx:362:15:11926:59:e:p"><span class="col-lg-6" style="padding:0px">Master: <?php echo number_format_inr(($PackingTypeID['COOKED']['MASTER'])); ?> </span>

                                                                     <span class="col-lg-6">Dummy: <?php echo number_format_inr(($PackingTypeID['COOKED']['DUMMY'])); ?></span>

                                                                 </p>
                                                              </div>
                                                              <div class="bg-red-200 p-3 rounded-full" data-fg-rvh191="28.21:28.18466:/components/ViewStock.tsx:364:13:12017:129:e:div">
                                                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-7 h-7 text-red-700" data-fg-rvh192="28.21:28.18466:/components/ViewStock.tsx:365:15:12080:47:e:Package::::::reo">
                                                                    <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                                                    <path d="M12 22V12"></path>
                                                                    <polyline points="3.29 7 12 12 20.71 7"></polyline>
                                                                    <path d="m7.5 4.27 9 5.15"></path>
                                                                 </svg>
                                                              </div>
                                                           </div>
                                                        </div>
                                                     </div>
                                                     <div class="summary-card-modern capacity-card">
                                                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow-md pt-5 pb-5 pl-2 pr-2 border-1 border-yellow-200" data-fg-rvh182="28.21:28.18466:/components/ViewStock.tsx:357:9:11548:630:e:div">
                                                           <div class="flex items-center justify-between" data-fg-rvh183="28.21:28.18466:/components/ViewStock.tsx:358:11:11673:490:e:div">
                                                              <div class="ml-2" data-fg-rvh184="28.21:28.18466:/components/ViewStock.tsx:359:13:11737:267:e:div">
                                                                 <p class="text-xs text-yellow-700 uppercase tracking-wide" data-fg-rvh185="28.21:28.18466:/components/ViewStock.tsx:360:15:11757:78:e:p">Daily Production Pending</p>
                                                                 <p class="text-3xl mt-2 text-yellow-900" data-fg-rvh187="28.21:28.18466:/components/ViewStock.tsx:361:15:11850:61:e:p" id="totalLooseBags"><?php echo $countTodaypendingkgs?$countTodaypendingkgs:0 ?> <?php echo $SelectedUomLabel; ?></p>
                                                                 <p class="text-xs text-yellow-600 mt-1" data-fg-rvh189="28.21:28.18466:/components/ViewStock.tsx:362:15:11926:59:e:p">Daily Production Pending <?php echo strtolower($SelectedUomLabel); ?></p>
                                                              </div>
                                                              <div class="bg-yellow-200 p-3 rounded-full" data-fg-rvh191="28.21:28.18466:/components/ViewStock.tsx:364:13:12017:129:e:div">
                                                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-7 h-7 text-yellow-700" data-fg-rvh192="28.21:28.18466:/components/ViewStock.tsx:365:15:12080:47:e:Package::::::reo">
                                                                    <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                                                    <path d="M12 22V12"></path>
                                                                    <polyline points="3.29 7 12 12 20.71 7"></polyline>
                                                                    <path d="m7.5 4.27 9 5.15"></path>
                                                                 </svg>
                                                              </div>
                                                           </div>
                                                        </div>
                                                     </div>

                                                     <div class="summary-card-modern capacity-card">
                                                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow-md pt-5 pb-5 pl-2 pr-2 border-1 border-orange-200" data-fg-rvh182="28.21:28.18466:/components/ViewStock.tsx:357:9:11548:630:e:div">
                                                           <div class="flex items-center justify-between" data-fg-rvh183="28.21:28.18466:/components/ViewStock.tsx:358:11:11673:490:e:div">
                                                              <div class="ml-2" data-fg-rvh184="28.21:28.18466:/components/ViewStock.tsx:359:13:11737:267:e:div">
                                                                 <p class="text-xs text-orange-700 uppercase tracking-wide" data-fg-rvh185="28.21:28.18466:/components/ViewStock.tsx:360:15:11757:78:e:p">OnFloor</p>
                                                                 <p class="text-3xl mt-2 text-orange-900" data-fg-rvh187="28.21:28.18466:/components/ViewStock.tsx:361:15:11850:61:e:p" id="totalLooseBags"><?php echo $floor_stock_weight?number_format_inr(($floor_stock_weight)):0 ?> <?php echo $SelectedUomLabel; ?></p>
                                                                 <p class="text-xs text-orange-600 mt-1" data-fg-rvh189="28.21:28.18466:/components/ViewStock.tsx:362:15:11926:59:e:p">OnFloor <?php echo strtolower($SelectedUomLabel); ?></p>
                                                              </div>
                                                              <div class="bg-orange-200 p-3 rounded-full" style="background-color: oklch(0.69 0.15 62.37 / 0.35);" data-fg-rvh191="28.21:28.18466:/components/ViewStock.tsx:364:13:12017:129:e:div">
                                                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-7 h-7 text-orange-700" data-fg-rvh192="28.21:28.18466:/components/ViewStock.tsx:365:15:12080:47:e:Package::::::reo">
                                                                    <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                                                    <path d="M12 22V12"></path>
                                                                    <polyline points="3.29 7 12 12 20.71 7"></polyline>
                                                                    <path d="m7.5 4.27 9 5.15"></path>
                                                                 </svg>
                                                              </div>
                                                           </div>
                                                        </div>
                                                     </div>

                                                    <?php if(0){?>
                                                     <!-- <div class="summary-card-modern capacity-card">
                                                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-md pt-5 pb-5 pl-2 pr-2 border-1 border-purple-200" data-fg-rvh182="28.21:28.18466:/components/ViewStock.tsx:357:9:11548:630:e:div">
                                                           <div class="flex items-center justify-between" data-fg-rvh183="28.21:28.18466:/components/ViewStock.tsx:358:11:11673:490:e:div">
                                                              <div data-fg-rvh184="28.21:28.18466:/components/ViewStock.tsx:359:13:11737:267:e:div">
                                                                 <p class="text-xs text-purple-700 uppercase tracking-wide" data-fg-rvh185="28.21:28.18466:/components/ViewStock.tsx:360:15:11757:78:e:p">Average Stock Age</p>
                                                                 <p class="text-3xl mt-2 text-purple-900" data-fg-rvh187="28.21:28.18466:/components/ViewStock.tsx:361:15:11850:61:e:p" id="totalLooseBags"><?php echo $rowAgingCount['AvgStockAge'] ?></p>
                                                                 <p class="text-xs text-purple-600 mt-1" data-fg-rvh189="28.21:28.18466:/components/ViewStock.tsx:362:15:11926:59:e:p">Active stock entries</p>
                                                              </div>
                                                              <div class="bg-purple-200 p-3 rounded-full" data-fg-rvh191="28.21:28.18466:/components/ViewStock.tsx:364:13:12017:129:e:div">
                                                                 <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package w-7 h-7 text-purple-700" data-fg-rvh192="28.21:28.18466:/components/ViewStock.tsx:365:15:12080:47:e:Package::::::reo">
                                                                    <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                                                    <path d="M12 22V12"></path>
                                                                    <polyline points="3.29 7 12 12 20.71 7"></polyline>
                                                                    <path d="m7.5 4.27 9 5.15"></path>
                                                                 </svg>
                                                              </div>
                                                           </div>
                                                        </div>
                                                     </div> -->
                                                    <?php } ?>
                                                  </div>
                                               </div>

                                           </div>

                                           <div class="card">
                                              <div class="card-body">
                                                 <!-- <h3 class="">Daily Inward Vs OutWard (Kgs)</h3> -->
                                                 <h3 class="section-title">
                                                    <span>Daily Inward Vs OutWard (<?php echo $SelectedUomLabel; ?>)</span>
                                                </h3>
                                                 <div>
                                                    <!-- <div id="height-chart-weekly"></div> -->
                                                    <div class="chart-wrapper">
    <canvas id="height-chart-weekly"></canvas>
</div>
                                                    <div id="line-column-chart" class="apex-charts" dir="ltr"></div>
                                                 </div>
                                              </div>
                                           </div>

                                           <div class="card" style="display:none">
                                              <div class="card-body">
                                                 <h3 class="section-title">
                                                    <span>PO-wise Packed vs Balance Stock (<?php echo $SelectedUomLabel; ?>)</span>
                                                </h3>
                                                 <div style="width:100%; height: 500px;position: relative;">
                                                    <canvas id="packedBalanceChart" ></canvas>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                              <div class="card-body">
                                                 <h3 class="section-title">
                                                    <span>Brand-wise / Grade-wise Stock Quantity</span>
                                                </h3>
                                                 <div style="width:100%; height: 500px;">
                                                    <canvas id="brandGradeStockChart"></canvas>

                                                 </div>
                                              </div>
                                           </div>

                                           <div class="card" style="background-color: transparent;border-radius: 0px; box-shadow: none; ">
                                              <div class="card-body"  style="padding:0px">
                                                  <div class="row">
                                                        <div class="col-xl-12" style="display: none;">

                                                            <div style="background: #ffffff;padding: 20px;box-shadow: 0 10px 25px rgba(0,0,0,0.06);height: 600px;">
                                                                <h3 class="section-title">
                                                                    <span>Top 10 Shortages</span>
                                                                </h3>
                                                                <canvas id="topShortagesChart" width="800" height="600"></canvas>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl-12">
                                                            <div style="background: #ffffff;padding: 20px;box-shadow: 0 10px 25px rgba(0,0,0,0.06);height: 600px;">
                                                                <h3 class="section-title">
                                                                    <span>Daily Production Trend (Last 7 Days)</span>
                                                                </h3>
                                                                <canvas id="productionTrendChart"></canvas>
                                                             </div>
                                                        </div>
                                                </div>
                                            </div>
                                        </div>

                                           <div class="card">
                                              <div class="card-body">
                                                 <!-- <h3 class="">Available Dummy Stock : <?php echo number_format_inr($TotalKgsBar3) ?> Kgs </h3> -->
                                                 <h3 class="section-title">
                                                    <span>Available Dummy Stock : <?php echo number_format_inr($SelectedUomLabel != "Mcs"?$dummy_cnt:$dummy_cnt) ?> <?php echo $SelectedUomLabel; ?></span>
                                                </h3>
                                                 <div class="chart-wrapper" style="height: 320px;">
                                                    <canvas id="height-chart-production"></canvas>
                                                 </div>
                                              </div>
                                           </div>
                                           <div class="card" style="display:none">
                                              <div class="card-body">
                                                 <h3 class="section-title">
                                                    <span>Available Master Stock : <?php echo number_format_inr($TotalKgsBar3Master) ?> <?php echo $SelectedUomLabel; ?></span>
                                                </h3>
                                                 <div class="chart-wrapper" style="height: 320px;">
                                                    <canvas id="height-chart-production-master"></canvas>
                                                 </div>
                                              </div>
                                           </div>


                                           <div class="card" style="background-color: transparent;border-radius: 0px; box-shadow: none;">
                                              <div class="card-body" style="padding:0px">
                                                  <div class="row">
                                                        <div class="col-xl-6" style="display:<?php echo $IsIBTGraphShow?'block':'none';?>">
                                                            <div style="background: #ffffff;padding: 20px;box-shadow: 0 10px 25px rgba(0,0,0,0.06);height: 600px;">
                                                                <h3 class="section-title">
                                                                    <span>IBT Pending</span>
                                                                </h3>
                                                                <div class="table-responsive" style="background: oklch(.962 .018 272.314);
    padding: 12px;">
                                                                    <table id="ibtTable" class="table table-bordered table-hover dt-responsive nowrap w-100" style="background: rgb(255, 255, 255); margin-left: 0px; width: 100%;/*min-height:380px*/">
                                                                      <thead>
                                                                        <tr>
                                                                          <th style="border-bottom: 2px solid black; font-size: 14px; width: 100px;">IBt Order Number</th>
                                                                          <th style="border-bottom: 2px solid black; font-size: 14px; width: 100px;">Order Date</th>
                                                                          <th style="border-bottom: 2px solid black; font-size: 14px; width: 100px;">From Plant</th>
                                                                          <th style="border-bottom: 2px solid black; font-size: 14px; width: 120px;">Status</th>
                                                                        </tr>
                                                                      </thead>
                                                                      <tbody></tbody>
                                                                    </table>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <div class="col-xl-<?php echo $IsIBTGraphShow?'6':'12';?>">


                                                                     <div style="background: #ffffff;padding: 20px;box-shadow: 0 10px 25px rgba(0,0,0,0.06);height: 600px;">
                                                                        <h3 class="section-title">
                                                                        <span>Shipment</span>
                                                                    </h3>

                                                                    <div style="display: flex;justify-content: center;align-items: center;position: relative;width: 100%;height: 400px;">
                                                                        <canvas id="coldStoreChart"></canvas>
                                                                        <div id="plantwiseOrders" style="height: 450px; width: 100%;"></div>
                                                                    </div>

                                                                     </div>

                                                        </div>
                                                    </div>
                                              </div>
                                           </div>





                                         </div>
                                     </div>
                                        <!-- Store Availability (sum-of-qty-by-block) -->
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h3 class="section-title">Store Availability</h3>
                                                        <div id="store-availability-chart" style="height: 350px; width: 100%;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Order Status -->
                                        <div class="row">
                                            <div class="col-xl-12">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                                            <h3 class="section-title">Order Status</h3>
                                                            <select id="orderStatusStationSelect" class="form-control form-control-sm" style="width:auto; min-width:180px; font-size:13px; border-radius:6px;display: none;">
                                                            </select>
                                                        </div>
                                                        <div id="order-status-by-plant" style="height: 350px; width: 100%; "></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                         <div class="col-xl-12">
                                             <div class="row">
                                                 <div class="col-xl-6">
                                                    <div class="card">
                                                      <div class="card-body">
                                                         <!-- <h3 >Total Store Details : <?php echo number_format_inr($TotalStoreStock) ?> Kgs</h3> -->
                                                         <h3 class="section-title">
                                                            <span>Total Store Details : <?php echo number_format_inr($TotalStoreStock) ?> <?php echo $SelectedUomLabel; ?></span>
                                                        </h3>
                                                         <!-- <div id="donut-chart" class="apex-charts" style="height: 272px; width: 100%;"></div> -->
                                                         <div id="hc-donut-chart" style="width: 100%; height: 216px;"></div>
                                                         <div class="row">
                                                            <?php foreach ($AryShiftDtls as $ShiftRow) {
                                                                if($ShiftRow['Total']<=0){
                                                                    $ShiftRow['Total'] = 0;
                                                                }
                                                                if($ShiftRow['TotalMcs']<=0){
                                                                    $ShiftRow['TotalMcs'] = 0;
                                                                }
                                                                ?>
                                                            <div class="card p-5 m-2">
                                                               <div class="text-center mt-4">
                                                                  <p class="mb-2 text-truncate"><i class="mdi mdi-circle font-size-10 mr-1" style="color: <?php echo $ShiftRow['Color'] ?>;">
                                                                     </i> <?php echo $ShiftRow['BlockName'] ?>
                                                                  </p>
                                                                  <h6><?php echo number_format_inr(($SelectedUomLabel == 'Kgs') ? $ShiftRow['Total'] : $ShiftRow['TotalMcs']) ?> <?php echo $SelectedUomLabel; ?></h6>
                                                               </div>
                                                            </div>
                                                            <?php    } ?>
                                                         </div>
                                                      </div>
                                                   </div>
                                                </div>
                                                <div class="col-xl-6">
                                                   <div class="card">
                                                      <div class="card-body">
                                                        <div class="float-end d-none d-md-inline-block" style="display:none!important;">
                                                            <div class="btn-group mb-2">
                                                                <button type="button" class="btn btn-sm btn-light active " id="bartab" onclick="customeTabs('barview','bartab')">Bar View</button>
                                                                <button type="button" class="btn btn-sm btn-light " id="pietab" onclick="customeTabs('pieview','pietab')">Pie View</button>
                                                            </div>
                                                        </div>
                                                         <!-- <h3 class="text-left">Stock Details : <?php echo number_format_inr($OverAllKgs) ?> T</h4>     -->
                                                         <h3 class="section-title">
                                                            <span>Stock Details : <?php echo number_format_inr($TotalStoreStock) ?> <?php echo $SelectedUomLabel; ?></span>
                                                        </h3>
                                                                    <div class="text-center" id="pieview" style="display:none;">
                                                                        <div class="row">
                                                                           <!-- <div style="height: 450px; width: 100%;">
                                                                               <canvas id="chartContainer" width="250" height="250"></canvas>
                                                                           </div> -->
                                                                           <!-- <div id="chartContainer" style="height: 350px; width: 100%; overflow: hidden;"></div>                                -->
                                                                           <div id="hc-pie-chart" style="width: 100%; height: 408px;"></div>
                                                                        </div>
                                                                     </div>

                                                                  <div class="text-center" id="barview" style="height: 408px; width: 100%;">
                                                                    <!-- <div id="bar-chart" style="height: 350px;"></div> -->
                                                                    <div class="row" id="canvasid" style="position:absolute; left:-9999px; top:-9999px;">
                                                                        <canvas id="graphCanvas" ></canvas> </div>
                                                                        <canvas id="popupGraphCanvas" width="900" height="450"></canvas>
                                                                  </div>
                                                      </div>
                                                   </div>
                                                </div>


                                            </div>
                                            <!-- <h4 class="card-title mb-4 text-center">Stock Aging Insights</h4> -->
                                         <div class="row" >
                                                    <div class="col-xl-6">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <div class="float-end d-none d-md-inline-block" style="display: none !important;">
                                                                <div class="btn-group mb-2">
                                                                    <button type="button" class="btn btn-sm btn-light " id="ageTabletab" onclick="customeTabs2('ageTableVeiew','ageTabletab')">300+</button>
                                                                    <button type="button" class="btn btn-sm btn-light active" id="ageGraphtab" onclick="customeTabs2('ageGraphVeiew','ageGraphtab')">Overview</button>
                                                                </div>
                                                            </div>

                                                            <div class="text-center">
                                                                <div class="row">
                                                                    <div class="table-responsive">
                                                                        <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">

                                                                            <div id="ageTableVeiew" style="display:none;">
                                                                                <div class="row" style="width: 100%;">
                                                                                    <div class="col-sm-12" style="height: 350px;overflow-x: hidden;width: 80%;">
                                                                                        <table class="table table-centered datatable dt-responsive nowrap dataTable no-footer dtr-inline" data-bs-page-length="5" style="border-collapse: collapse; border-spacing: 0px; width: 100%;" id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
                                                                                            <thead class="table-light" style="position:sticky;top: 0;">
                                                                                                <tr>
                                                                                                    <th class="sorting sorting_asc" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" style="width: 107px;" aria-sort="ascending" aria-label="Order ID: activate to sort column descending">Stock ID</th>
                                                                                                    <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" style="width: 124px;" aria-label="Date: activate to sort column ascending">Grade</th>
                                                                                                    <th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" style="width: 149px;" aria-label="Billing Name: activate to sort column ascending">Aging Days</th>
                                                                                                </tr>
                                                                                            </thead>
                                                                                            <tbody>
                                                                                                <?php
                                                                                                    $i=0;
                                                                                                    while($RowAgingStocks = $smtAging->fetch_array()){
                                                                                                    $tableClass = ( $i%2 == 0 ) ? 'odd' : 'even' ;
                                                                                                    $aging_days = $RowAgingStocks['aging_days'];
                                                                                                    ?>
                                                                                                    <tr class="<?php echo $tableClass; ?>">
                                                                                                        <td><?php echo $RowAgingStocks['StockID'];?></td>
                                                                                                        <td><?php echo $RowAgingStocks['Grade'];?></td>
                                                                                                        <td>
                                                                                                            <div class="badge  bg-danger-subtle text-danger font-size-12"><?php echo number_format_inr($aging_days); ?></div>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                <?php
                                                                                                    $i++;
                                                                                                    }
                                                                                                    ?>
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div id="ageGraphVeiew" style="overflow: hidden;">
                                                                                <div class="row">
                                                                                    <!-- <h3 class="col-sm-12 text-center">Stock Aging By Percentage</h3> -->

                                                                                        <div class="col-sm-12" style="height: 412px;">
                                                                                            <h3 class="section-title mb-30">
                                                                                                    <span>Stock Aging By Percentage</span>
                                                                                            </h3>
                                                                                            <div class="chart-container">

                                                                                                <canvas id="agingChart" ></canvas>
                                                                                            </div>
                                                                                        </div>

                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-xl-6">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h3 class="section-title">
                                                                                        <span>Aging by group</span>
                                                                                    </h3>
    <div class="row">

        <!-- Aging -->
        <div class="col-md-4 mb-4">
            <div class="summary-card">
                <div class="summary-header aging-header">Aging</div>
                <div class="summary-body">
                    <?php foreach($labels_aging as $agelable){ echo '<div>'.$agelable.'</div>'; } ?>
                </div>
                <div class="summary-footer aging-footer">Total</div>
            </div>
        </div>

        <!-- Quantity -->
        <div class="col-md-4 mb-4">
            <div class="summary-card">
                <div class="summary-header quantity-header">Quantity</div>
                <div class="summary-body">
                    <?php foreach($kgs_aging as $kgslable){ echo '<div>'.number_format_inr($kgslable).' '.$SelectedUom.' </div>'; } ?>
                </div>
                <div class="summary-footer quantity-footer"><?php echo number_format_inr($kgs_aging_total); ?></div>
            </div>
        </div>

        <!-- Amount -->
        <div class="col-md-4 mb-4">
            <div class="summary-card">
                <div class="summary-header amount-header"  style="background: #a27df8;">Stock Count</div>
                <div class="summary-body">
                    <?php foreach($values_aging2 as $stocklable_key => $stocklable){ ?>
                        <div class="clickable-count" style="cursor: pointer;" onclick="ShowDetails('<?php echo $stocklable_key; ?>')"><?php echo number_format_inr($stocklable);?></div>
                    <?php } ?>
                </div>
                <div class="summary-footer amount-footer" style="background: #a27df8;"><?php echo number_format_inr($stock_aging_total); ?></div>
            </div>
        </div>

    </div>

</div>

</div>

</div>


                                                </div>
<div class="card">
        <div class="card-body">
<div id="chartWrapper">

            <h3 class="section-title mb-30">
                    <span>Stock Aging by Variety (<?php echo $SelectedUomLabel; ?>)</span>
            </h3>
            <canvas id="agingVarietyChart" style="padding-bottom:20px"></canvas>
        </div>
    </div>
</div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                    </div>
                </div>
                        <!-- end row -->

                     </div>
                  </div>

<!--                <div class="tab-pane" id="profile" role="tabpanel">
                  <p class="mb-0">
                     <iframe id="myIframe2" src="/v3/ColdStore/store-on-floor-stock?iframe=true"  style="width:100%; border:none;height: 100vh;" onload="resizeIframe(this)"></iframe>
                     <?php //include("store-on-floor-tab.php"); ?>
                  </p>
               </div>
               <div class="tab-pane" id="messages" role="tabpanel">
                  <p class="mb-0">
                     <iframe id="myIframe" src="/v3/ColdStore/view-store-layout?iframe=true"  style="width:100%; border:none;height: 100vh;" onload="resizeIframe(this)"></iframe>
                     <?php //include("store_tab.php"); ?>
                  </p>
               </div> -->
            </div>
            <div class="modal fade" id="myModalStockAge" tabindex="-1" role="dialog">
              <div class="modal-dialog modal-xl" role="document" style="max-width:none;margin-left: 6px;">
                <div class="modal-content" >
                  <div class="modal-header" style="padding-bottom:0px">
                    <h5 class="modal-title" id="stockagetableLable" style="line-height:1">Stock Age</h5>
                    <button type="button" class="close" data-dismiss="modal">
                      <span>&times;</span>
                    </button>
                  </div>
                  <div class="modal-body"  >
                    <div class="table-responsive">
                        <table id="stockagetable" class="table table-bordered table-hover w-100" style="background: rgb(255, 255, 255); margin-left: 0px; width: 1788px;">
                            <thead>
                                <tr>
                                    <th style="border-bottom: 2px solid black;font-size: 14px;">Production Date</th>
                                    <th style="border-bottom: 2px solid black;font-size: 14px;">Variety</th>
                                    <th style="border-bottom: 2px solid black;font-size: 14px;">Packing Style</th>
                                    <th style="border-bottom: 2px solid black;font-size: 14px;">Packing Type</th>
                                    <th style="border-bottom: 2px solid black;font-size: 14px;">Grade</th>
                                    <th style="border-bottom: 2px solid black;font-size: 14px;">Stock Age Days</th>
                                    <th style="border-bottom: 2px solid black;font-size: 14px;">Aval Mcs</th>
                                    <th style="border-bottom: 2px solid black;font-size: 14px;">Loose Bags</th>
                                    <th style="border-bottom: 2px solid black;font-size: 14px;">Total Kgs</th>
                                    <th style="border-bottom: 2px solid black;font-size: 14px;">Batch Code</th>
                                    <th style="border-bottom: 2px solid black;font-size: 14px;">Store</th>
                                    <th style="border-bottom: 2px solid black;font-size: 14px;">Block</th>
                                    <th style="border-bottom: 2px solid black;font-size: 14px;">Floor</th>
                                    <th style="border-bottom: 2px solid black;font-size: 14px;">Rack Name</th>

                                </tr>
                            </thead>
                        </table>
                    </div>


                  </div>
                </div>
              </div>
            </div>

            <?php
               include($IncludesPath . "footer.php");

               // echo file_get_contents($BaseDist."partials/footer.php");
               ?>
         </div>
         <!-- end main content-->
      </div>
      <!-- Order Status Detail Modal -->
      <div class="modal fade" id="orderStatusModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document" style="max-width:none;margin-left: 6px;">
          <div class="modal-content">
            <div class="modal-header" style="padding-bottom:0px">
              <h5 class="modal-title" id="orderStatusModalTitle" style="line-height:1">Order Status Details</h5>
              <button type="button" class="close" data-dismiss="modal">
                <span>&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div id="orderStatusLoading" class="text-center py-5" style="display:none;">
                <div class="spinner-border text-primary" role="status">
                  <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading data...</p>
              </div>
              <div id="orderStatusEmpty" class="text-center py-5" style="display:none;">
                <p class="text-muted">No records found.</p>
              </div>

              <!-- Order Status Summary Section -->
              <div id="orderStatusSummary" style="display:none; margin-bottom: 15px;">
                <div class="row">
                  <div class="col-md-3">
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px; text-align:center;">
                      <div style="font-size:12px; color:#166534; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Completed</div>
                      <div id="orderStatusCompletedCount" style="font-size:22px; font-weight:700; color:#15803d;">0</div>
                      <div style="margin-top:6px;">
                        <div style="background:#dcfce7; border-radius:999px; height:8px; overflow:hidden;">
                          <div id="orderStatusCompletedBar" style="background:#22c55e; height:100%; width:0%; border-radius:999px; transition:width 0.4s ease;"></div>
                        </div>
                      </div>
                      <div id="orderStatusCompletedPct" style="font-size:12px; color:#166534; margin-top:4px; font-weight:600;">0%</div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:12px; text-align:center;">
                      <div style="font-size:12px; color:#991b1b; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Pending</div>
                      <div id="orderStatusPendingCount" style="font-size:22px; font-weight:700; color:#dc2626;">0</div>
                      <div style="margin-top:6px;">
                        <div style="background:#fee2e2; border-radius:999px; height:8px; overflow:hidden;">
                          <div id="orderStatusPendingBar" style="background:#ef4444; height:100%; width:0%; border-radius:999px; transition:width 0.4s ease;"></div>
                        </div>
                      </div>
                      <div id="orderStatusPendingPct" style="font-size:12px; color:#991b1b; margin-top:4px; font-weight:600;">0%</div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:12px; text-align:center;">
                      <div style="font-size:12px; color:#92400e; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Partial</div>
                      <div id="orderStatusPartialCount" style="font-size:22px; font-weight:700; color:#d97706;">0</div>
                      <div style="margin-top:6px;">
                        <div style="background:#fef3c7; border-radius:999px; height:8px; overflow:hidden;">
                          <div id="orderStatusPartialBar" style="background:#f59e0b; height:100%; width:0%; border-radius:999px; transition:width 0.4s ease;"></div>
                        </div>
                      </div>
                      <div id="orderStatusPartialPct" style="font-size:12px; color:#92400e; margin-top:4px; font-weight:600;">0%</div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px; text-align:center;">
                      <div style="font-size:12px; color:#1e40af; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Total Orders</div>
                      <div id="orderStatusTotalCount" style="font-size:22px; font-weight:700; color:#2563eb;">0</div>
                      <div style="margin-top:6px;">
                        <div style="background:#dbeafe; border-radius:999px; height:8px; overflow:hidden;">
                          <div id="orderStatusTotalBar" style="background:#3b82f6; height:100%; width:100%; border-radius:999px;"></div>
                        </div>
                      </div>
                      <div style="font-size:12px; color:#1e40af; margin-top:4px; font-weight:600;">100%</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Show Only Filter -->
              <div id="orderStatusFilterSection" style="display:none; margin-bottom:12px;">
                <div class="row align-items-center">
                  <div class="col-md-3">
                    <label style="font-size:13px; font-weight:600; color:#374151; margin-bottom:4px;">Show Only</label>

                  </div>
                </div>
              </div>

              <div class="table-responsive" id="orderStatusTableWrapper">
                <table id="orderStatusDetailTable" class="table table-bordered table-hover dt-responsive nowrap w-100" style="background: rgb(255, 255, 255); margin-left: 0px; width: 100%;">
                  <thead>
    <tr>
        <th style="border-bottom: 2px solid black; font-size: 14px;">PO No</th>
        <th style="border-bottom: 2px solid black; font-size: 14px;">Packing Type</th>
        <th style="border-bottom: 2px solid black; font-size: 14px;">Consignee Name</th>
        <th style="border-bottom: 2px solid black; font-size: 14px;">Ordered Kgs</th>
        <th style="border-bottom: 2px solid black; font-size: 14px;">Ordered Mcs</th>
        <th style="border-bottom: 2px solid black; font-size: 14px;">Finished Kgs</th>
        <th style="border-bottom: 2px solid black; font-size: 14px;">Finished Mcs</th>
        <th style="border-bottom: 2px solid black; font-size: 14px;">Balance Kgs</th>
        <th style="border-bottom: 2px solid black; font-size: 14px;">Balance Mcs</th>
        <th style="border-bottom: 2px solid black; font-size: 14px;">Status</th>
    </tr>

    <tr id="orderStatusFilterRow">
        <!-- 0 -->
        <th>
            <select id="filterPoNo" class="form-control form-control-sm"
                    style="width:100%;font-size:12px;">
                <option value="">All</option>
            </select>
        </th>

        <!-- 1 -->
        <th>
            <select id="filterPackingType" class="form-control form-control-sm"
                    style="width:100%;font-size:12px;">
                <option value="">All</option>
            </select>
        </th>

        <!-- 2 -->
        <th>
            <select id="filterConsignee" class="form-control form-control-sm"
                    style="width:100%;font-size:12px;">
                <option value="">All</option>
            </select>
        </th>

        <!-- 3 -->
        <th></th>

        <!-- 4 -->
        <th></th>

        <!-- 5 -->
        <th></th>

        <!-- 6 -->
        <th></th>

        <!-- 7 -->
        <th></th>

        <!-- 8 -->
        <th></th>

        <!-- 9 -->
        <th>
            <select id="filterOrderStatus"
                    class="form-control form-control-sm"
                    style="font-size:12px; border-radius:6px;">
                <option value="">All Orders</option>
                <option value="Pending">Pending Orders</option>
                <option value="Completed">Completed Orders</option>
                <option value="Partial">Partial Orders</option>
            </select>
        </th>
    </tr>
</thead>

                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal fade" id="poModal" tabindex="-1">
          <div class="modal-dialog modal-xl" style="max-width:none;margin-left: 6px;">
            <div class="modal-content" >
              <div class="modal-header">
                <h5 class="modal-title" id="poModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="poModal" onclick="$('#poModal').modal('hide')">x</button>
              </div>
              <div class="modal-body">
                <div class="table-responsive">
                        <table id="poTable" class="table table-bordered table-hover dt-responsive nowrap w-100" style="background: rgb(255, 255, 255); margin-left: 0px; width: 100%;">

                  <thead>
                    <tr>
                      <th style="border-bottom: 2px solid black; font-size: 14px; width: 100px;">PO Number</th>
                      <th style="border-bottom: 2px solid black; font-size: 14px; width: 100px;">PO Date</th>
                      <th style="border-bottom: 2px solid black; font-size: 14px; width: 100px;">Ship By Date</th>
                      <th style="border-bottom: 2px solid black; font-size: 14px; width: 100px;">Grade</th>
                      <th style="border-bottom: 2px solid black; font-size: 14px; width: 140px;">Variety</th>
                      <th style="border-bottom: 2px solid black; font-size: 14px; width: 140px;">Stock Out Date</th>
                      <th style="border-bottom: 2px solid black; font-size: 14px; width: 100px;">Actual Mcs</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
            </div>
              </div>
            </div>
          </div>
        </div>

<div id="myModalFullScreen" class="modal fade" role="dialog" tabindex="-1">
          <div class="modal-dialog modal-fullscreen">
              <div class="modal-content" style="
    padding: 12px; margin-top:5% ;">
                  <div class="modal-header" style="padding: 5px 15px">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                      </button>
                  </div>
                  <div class="modal-body">
                      <div id="PopUpTableFullScreen" class="col-md-12 scrollPopup"></div>
                  </div>
              </div>
          </div>
      </div>
      <!-- END layout-wrapper -->
      <?php
         include($IncludesPath . "vendor-scripts.php");
         include($IncludesPath . "NewScripts.php");

         ?>
         <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>

       <!-- apexcharts -->
        <script src="https://developer-tools.miledeepworks.in/uiwrapper/Basewrapper/Nazox_v1.0/Admin/Horizontal/dist/assets/libs/apexcharts/apexcharts.min.js"></script>
        <script src="https://developer-tools.miledeepworks.in/assets/js/jquery-jvectormap-world-mill-en.js"></script>

    <script src="/assets/js/highcharts.js"></script>
    <script src="/assets/js/highcharts-3d.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@1.3.0"></script>
      <script>
        function ShowDetails(agegroup) {
            const selectedPid = ($('#PurchaseStationID').length && $('#PurchaseStationID').val()) ? $('#PurchaseStationID').val() : '';
            const selectedSpecies = ($('#species').length && $('#species').val()) ? $('#species').val() : '';

            $('#stockagetableLable').text('Stock age - ' + agegroup);

            if ($.fn.DataTable.isDataTable('#stockagetable')) {
                $('#stockagetable').DataTable().destroy();
            }

            $('#stockagetable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 20,
                scrollX: true,
                lengthChange: false,
                searching: true,
                dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"l f B><"col-md-6">>rtip',
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    pageLength: 10,
                    searching: true,
                    ordering: false,
                    language: {
                        lengthMenu: "Show _MENU_ records",
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        }
                    },
                    drawCallback: function () {
                        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    },
                    buttons: [
                      {
                        text: 'Download Excel',
                        className: 'btn btn-success',
                        action: function (e, dt, button, config) {
                          /* Fetch ALL matching records from the server (no pagination) */
                          var searchVal = dt.search() || '';
                          var exportUrl = '/v3/ColdStore/Ajax/ajax-dashboard-insights.php'
                            + '?action=stockagepopup_export'
                            + '&agegroup=' + encodeURIComponent(agegroup)
                            + '&PID=' + selectedPid
                            + '&Species=' + encodeURIComponent(selectedSpecies)
                            + '&search=' + encodeURIComponent(searchVal);

                          fetch(exportUrl)
                            .then(function (response) { return response.json(); })
                            .then(function (json) {
                              var allData = json.data || [];
                              if (allData.length === 0) {
                                alert('No data available to export for the selected filters.');
                                return;
                              }

                              /* Build CSV content */
                              function csvQuote(val) {
                                return '"' + String(val === null || val === undefined ? '' : val).replace(/"/g, '""') + '"';
                              }
                              var csvRows = [];
                              var headers = ['Production Date', 'Variety', 'Packing Style', 'Packing Type', 'Grade', 'Stock Age Days', 'Aval Mcs', 'Loose Bags', 'Total Kgs', 'Batch Code', 'Store', 'Block', 'Floor', 'Rack Name'];
                              csvRows.push(headers.map(csvQuote).join(','));
                              allData.forEach(function (row) {
                                csvRows.push([
                                  csvQuote(row.ProductionDate),
                                  csvQuote(row.Variety),
                                  csvQuote(row.PackingStyle),
                                  csvQuote(row.PackingType),
                                  csvQuote(row.Grade),
                                  csvQuote(row.StockAgeDays),
                                  csvQuote(row.AvalMcs),
                                  csvQuote(row.LooseBags),
                                  csvQuote(row.TotalKgs),
                                  csvQuote(row.BatchCode),
                                  csvQuote(row.StoreName),
                                  csvQuote(row.BlockName),
                                  csvQuote(row.FloorName),
                                  csvQuote(row.RackName)
                                ].join(','));
                              });

                              var csvContent = csvRows.join('\n');
                              var blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
                              var link = document.createElement('a');
                              link.href = URL.createObjectURL(blob);
                              link.download = 'Stock_Age_' + agegroup.replace(/[^a-zA-Z0-9-]/g, '_') + '.csv';
                              link.click();
                              URL.revokeObjectURL(link.href);
                            })
                            .catch(function () {
                              alert('Failed to export data. Please try again.');
                            });
                        }
                      }
                    ],
                ajax: {
                    url: '/v3/ColdStore/Ajax/ajax-dashboard-insights.php',
                    type: 'GET',
                    data: function (d) {
                        d.action = 'stockagepopup';
                        d.agegroup = agegroup;
                        d.PID = selectedPid;
                        d.Species = Species;
                    }
                },
                columns: [
                    { data: 'ProductionDate' },
                    { data: 'Variety' },
                    { data: 'PackingStyle' },
                    { data: 'PackingType' },
                    { data: 'Grade' },
                    { data: 'StockAgeDays' },
                    { data: 'AvalMcs' },
                    { data: 'LooseBags' },
                    { data: 'TotalKgs' },
                    { data: 'BatchCode' },
                    { data: 'StoreName' },
                    { data: 'BlockName' },
                    { data: 'FloorName' },
                    { data: 'RackName' }
                ]
            });

            $('#myModalStockAge').modal('show');
        }

function customeTabs(gview, gtab) {

    // Hide both views
    document.getElementById("pieview").style.display = "none";
    document.getElementById("barview").style.display = "none";

    // Remove active class from both buttons
    document.getElementById("pietab").classList.remove("active");
    document.getElementById("bartab").classList.remove("active");

    // Show selected view
    document.getElementById(gview).style.display = "block";

    // Activate selected tab
    document.getElementById(gtab).classList.add("active");
}

function customeTabs2(gview, gtab) {
//'ageTableVeiew','ageTabletab' 'ageGraphVeiew','ageGraphtab'
    // Hide both views
    document.getElementById("ageTableVeiew").style.display = "none";
    document.getElementById("ageGraphVeiew").style.display = "none";

    // Remove active class from both buttons
    document.getElementById("ageTabletab").classList.remove("active");
    document.getElementById("ageGraphtab").classList.remove("active");

    // Show selected view
    document.getElementById(gview).style.display = "block";

    // Activate selected tab
    document.getElementById(gtab).classList.add("active");
}

$('#usa-vectormap').vectorMap({
    map: 'world_mill_en',
    backgroundColor: 'transparent',
    regionStyle: {
        initial: {
            fill: '#e8ecf4',
            stroke: '#74788d',
            'stroke-width': 1,
            "stroke-opacity": 0.4,
        }
    },

});
         window.addEventListener("message", function (event) {
         //console.log("Parent received:", event.data);

         if (event.data?.type === "resize-iframe") {
         if(event.data.height>100){
         document.getElementById("myIframe").style.height =
         event.data.height + "px";
         document.getElementById("myIframe2").style.height =
         event.data.height + "px";
         }
         }

         if (event.data.action === "scrollToTop") {
         window.scrollTo({
         top: 0,
         behavior: "smooth"
         });
         }

         });

         $(document).on('click', '#list', function(e) {



             var SID = $('#PurchaseStationID').val();
             var Year = $('#Year').val();
             var Month = $('#Month').val();
             var UOM = $('#UOM').val();
             var Species = $('#species').val();
             //alert(Month);
             //
             var Url = '<?php echo $CurrentUrl ?>index?Year=' + Year + '&Month=' + Month + '&PID=' + SID + '&UOM=' + UOM + '&species=' + encodeURIComponent(Species);
             //alert(Url);

             window.open(Url, "_self");

             // alert(SelectedDate);

         });

         $(function() {
             "use strict";
             var chart = new CanvasJS.Chart("chartContainer", {
                 animationEnabled: true,

                 data: [{
                     type: "pie",
                     yValueFormatString: "#,##0.\" T\"",
                     indexLabel: "{label} ({y})",
                     dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
                 }]
             });
             chart.render();
         })

        /*const filteredData = <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>.filter(d => d.y > 0);

const labels = filteredData.map(d => d.label);
const values = filteredData.map(d => d.y);

new Chart(document.getElementById('chartContainer'), {
    type: 'pie',
    data: {
        labels: labels,
        datasets: [{
            data: values,
            backgroundColor: [
                '#4bc0c0','#9966ff','#36a2eb',
                '#ff6384','#ffcd56','#2ecc71',
                '#e67e22','#1abc9c','#9b59b6'
            ],
            borderColor: '#fff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: {
                top: 40,
                bottom: 20
            }
        },
        legend: {
            display: false
        },
        plugins: {
            outlabels: {
                text: '%l (%v T)',
                color: '#000',
                stretch: 20,           // leader line length
                lineColor: '#000',
                lineWidth: 1.5,
                font: {
                    resizable: true,
                    minSize: 10,
                    maxSize: 14
                },
                align: 'center',
                position: 'outside',
                padding: 2,
                backgroundColor: 'transparent',
                // Only display for non-zero slices
                display: function(context) {
                    return context.dataset.data[context.dataIndex] > 0;
                }
            }
        }
    }
});*/
      </script>
      <script>
         $(document).ready(function() {
             let storeId = <?php echo $storeIDs?$storeIDs:0; ?>;
             if (storeId != 0) {
                 $('#PurchaseStationID').val(storeId).attr('disabled', true)
             }


             // $('#example1').DataTable();
             var table = $('#key-datatable').DataTable({
                 scrollY: "600px",
                 scrollX: true,
                 //sScrollX: "100%",
                 sScrollXInner: "150%",
                 // scrollCollapse:true,
                 paging: true,

                    language: {
                            lengthMenu: "Show _MENU_ records",
                            paginate: {
                                previous: "<i class='mdi mdi-chevron-left'>",
                                next: "<i class='mdi mdi-chevron-right'>"
                            }
                        },
                        drawCallback: function () {
                            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                        },
                        dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',

                    buttons: [],
                 destroy: true,
                 fixedColumns: true,
                 bSort: false
                 /*"columnDefs": [
                     { "width": "20%", "targets": 0 }
                   ]*/
             });
         });
      </script>
      <script>
         $(document).on('click', '#PoDetails', function(e) {
             e.preventDefault();

             var PoNo = $(this).data('value');
             var PoID = $(this).data('id');

             $('#Update').val(PoID);


             //alert(PoNo);

             $.ajax({
                 url: 'Ajax/AjaxPopUpPoDetails.php',
                 type: 'POST',
                 data: {
                     'PoID': PoID
                 },
                 success: function(data) {

                     $('.modal-title').text('PO Status Details - ' + PoNo);
                     $("#PopUpTable").html(data);
                     /*$('#PopUpTable').DataTable({
         "bPaginate": false,
         autoWidth: true,

         });*/
                     $('#myModal').modal('show');



                     //$('#myModal').modal('show');
                 }
             });

         });



         $(document).on('click', '#chbkExp', function(e) {
             var chbkExp = $("#chbkExp").is(':checked');
             var chbkShip = $("#chbkShip").is(':checked');
             var SelectedDate = '<?php echo $PresentYear ?>';

             updateGraph(chbkExp, chbkShip);
         });

         $(document).on('click', '#chbkShip', function(e) {
             var chbkExp = $("#chbkExp").is(':checked');
             var chbkShip = $("#chbkShip").is(':checked');
             var SelectedDate = '<?php echo $PresentYear ?>';


             updateGraph(chbkExp, chbkShip);
         });

         $(document).on('click', '#Update', function(e) {

             var PoID = $('#Update').data('id');
             var PoID = $('#Update').val();

             var Url = 'CreateShipmentStatus.php?PoID=' + PoID;
             window.open(Url, "_blank");
         });

         $(document).on('click', '#BuyerDetails', function(e) {

             var ConID = $('#ConID').val();



             $.ajax({
                 url: 'Ajax/AjaxBuyerDetails.php',
                 type: 'POST',
                 data: {
                     'ConID': ConID
                 },
                 success: function(data) {

                     //$('.modal-title').text('Do You Want To Shipped This Po '+INT_LOT_NUM+' ?');
                     $("#PopUpTable").html(data);
                     /*  $('#PopUpTable').DataTable({
         "bPaginate": false,
         autoWidth: true,
         scrollY: 600,
         scrollX: true,
         scroller: true
         });*/
                     $('#myModal').modal('show');



                     //$('#myModal').modal('show');
                 }
             });

         });
      </script>
      <script>
         var ArySeries = <?php echo json_encode($Series); ?>;
         var AryLables = <?php echo json_encode($Lables, true); ?>;
         var AryLables_high_dunts = <?php echo json_encode($high_dunt_data, true); ?>;
         //console.log(AryLables_high_dunts);
         var AryColors = <?php echo json_encode($Colors, true); ?>;
         var AryColors1 = ["#5182ef","#40c79a","#4AA3FF","#FCB92C","#FF3D60","#343A40"];

         var storeAvailabilityData = <?php echo json_encode($StoreAvailabilityData); ?>;
         var orderStatusData =
    <?php
    echo json_encode(
        $OrderStatusData,
        JSON_NUMERIC_CHECK
    );
    ?>;

         var AryPortSeries = <?php echo json_encode($PortSeries); ?>;
         var AryPortLables = <?php echo json_encode($PortLables, true); ?>;
         var AryPortColors = <?php echo json_encode($PortColors, true); ?>;

         //var Arymerge = AryColors1.concat(AryPortColors);
         var Arymerge = AryPortColors;


         options = {
             series: ArySeries,
             chart: {
                 height: 230,
                 type: "donut"
             },
             labels: AryLables,
             plotOptions: {
                 pie: {
                     donut: {
                         size: "75%"
                     }
                 }
             },
             dataLabels: {
                 enabled: !1
             },
             legend: {
                 show: !1
             },
             colors: AryColors1
         };

         Portoptions = {
             series: AryPortSeries,
             chart: {
                 height: 230,
                 type: "donut"
             },
             labels: AryPortLables,
             plotOptions: {
                 pie: {
                     donut: {
                         size: "75%"
                     }
                 }
             },
             dataLabels: {
                 enabled: !1
             },
             legend: {
                 show: !1
             },
             colors: Arymerge
         };

         (chart = new ApexCharts(document.querySelector("#donut-chart"), options)).render();
         (chart = new ApexCharts(document.querySelector("#donut-chart-port"), Portoptions)).render();

         /*Donut Chart End */




         var AryShipMents = <?php echo json_encode($Shipments, true); ?>;
         var AryOrders = <?php echo json_encode($Orders, true); ?>;
         var AryXlables = <?php echo json_encode($MonthName, true); ?>;
         var DailyInwardTooltipMap = <?php echo json_encode($dailyInwardTooltipMap, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK); ?>;
         var DailyOutwardTooltipMap = <?php echo json_encode($dailyOutwardTooltipMap, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK); ?>;
         var DailyInOutTooltipLimit = <?php echo (int)$tooltipLimit; ?>;
         var SelectedUom = '<?php echo $SelectedUom; ?>';
         var SelectedPID = '<?php echo $PID; ?>';
         var Species = '<?php echo $SelectedSpecies; ?>';
         var SelectedUomLabel = '<?php echo $SelectedUomLabel; ?>';





         var AryExportKgs = <?php echo json_encode($ExportKgs, true); ?>;
         var AryShippedKgs = <?php echo json_encode($ShippedKgs, true); ?>;
         var AryShipmentXlables = <?php echo json_encode($Grade, true); ?>;

         var AryPresent = <?php echo json_encode($Present, true); ?>;
         var AryAbsent = <?php echo json_encode($Absent, true); ?>;
         var AryOnTime = <?php echo json_encode($OnTimeData, true); ?>;
         var AryTotal = <?php echo json_encode($Total, true); ?>;
         var AryDate = <?php echo json_encode($Date, true); ?>;


         var AryMcsBar3 = <?php echo json_encode($AryMcsBar3, true); ?>;
         var AryKgsBar3 = <?php echo json_encode($AryKgsBar3, true); ?>;
         var AryGradeBar3 = <?php echo json_encode($AryGradeBar3, true); ?>;

         var AryMcsBar3Master = <?php echo json_encode($AryMcsBar3Master, true); ?>;
         var AryKgsBar3Master = <?php echo json_encode($AryKgsBar3Master, true); ?>;
         var AryGradeBar3Master = <?php echo json_encode($AryGradeBar3Master, true); ?>;






         // chart = new ApexCharts(document.querySelector("#line-column-chart"), SalesOptions);
         // chart.render();


         // chartProduction = new ApexCharts(document.querySelector("#production-chart"), ProductionOptions);
         // chartProduction.render();



    /*document.addEventListener("DOMContentLoaded", function() {
    var bardataPoints = <?php echo json_encode($values, JSON_NUMERIC_CHECK); ?>;
    var barLabels = <?php echo json_encode($labels); ?>;
    var barcolors = ["#5182ef","#40c79a","#4AA3FF","#FCB92C","#FF3D60"];

    var baroptions = {
        chart: { type: 'bar', height: 350 },
        series: [{ name: '', data: bardataPoints }],
        plotOptions: { bar: { distributed: true, borderRadius: 4, columnWidth: '50%' } },
        colors: barcolors,
        xaxis: { categories: barLabels, title: { text: 'Variety' } },
        yaxis: { title: { text: 'Kgs' } },
        legend: { show: false },
        dataLabels: { enabled: true }
    };

    var barchart = new ApexCharts(document.querySelector("#bar-chart"), baroptions);
    barchart.render();
});*/

         /*document.addEventListener("DOMContentLoaded", function () {

    var bardataPoints = <?php echo json_encode($values, JSON_NUMERIC_CHECK); ?>;
//    var barLabels = <?php echo json_encode($labels); ?>;
    var barLabels = <?php echo json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;


    var options = {
        chart: {
            type: 'bar',
            height: 550,
            toolbar: { show: true }
        },
        series: [{
            name: '',
            data: bardataPoints
        }],
        plotOptions: {
            bar: {
                horizontal: true,
                distributed: true,
                borderRadius: 6,
                barHeight: '20%'
            }
        },
        colors: ["#5182ef", "#40c79a", "#4AA3FF", "#FCB92C", "#FF3D60"],

        xaxis: {
            title: {
                text: 'Kgs'
            },
            labels: {
                formatter: function (val) {
                    return val.toFixed(2);
                }
            }
        },
        yaxis: {
             type: 'category',
            categories: barLabels,
            title: {
                text: 'Varieties'
            },
            labels: {
                style: {
                    fontWeight: 'bold',
                    fontSize: '13px'
                }
            }
        },

        dataLabels: {
            enabled: false,
            formatter: function (val) {
                return val.toFixed(2);
            },
            textAnchor: 'end',   // ✅ correct for horizontal bars
            offsetX: 10,          // space from bar end
            style: {
                fontSize: '12px',
                colors: ['#000']
            }
        },

        legend: {
            show: false
        },

        grid: {
            borderColor: '#e7e7e7',
            row: {
                colors: ['#f8f8f8', 'transparent'],
                opacity: 0.5
            }
        },

        tooltip: {
            y: {
                formatter: function (val) {
                    return val.toFixed(2);
                }
            }
        }
    };

    var chart = new ApexCharts(
        document.querySelector("#bar-chart"),
        options
    );

    chart.render();
});*/

 let chartInstance = null;
function createChart(title, labels, data, type, ax) {
    const isHorizontal = ax === "y";

    /*const modal = document.getElementById("graphModal");
    modal.style.display = "flex";
    modal.style.backdropFilter = "none";
    modal.style.background = "rgba(0,0,0,0.45)";

    document.getElementById("graphTitle").innerText = title;*/

    if (chartInstance) chartInstance.destroy();

    const ctx = document.getElementById("graphCanvas");

    const total = Array.isArray(data)
    ? data.reduce((a, b) => a + b, 0)
    : 0;

    const colors = [
        "#2563eb", "#10b981", "#f59e0b", "#ef4444",
        "#8b5cf6", "#14b8a6", "#f97316", "#6366f1"
    ];

    const dataset = {
        label: title,
        data,
        backgroundColor: colors.map(c => c + "cc"),
        borderColor: colors,
        borderWidth: 1,
        barPercentage: 0.9,
        categoryPercentage: 0.85,

        datalabels: isHorizontal
    ? {
        enabled: true,
        formatter: (value, ctx) => {
            const pct = total > 0 ? (value / total) * 100 : 0;
            if (pct > 10 ) {
                return `(${pct.toFixed(2)}%)`;
            }
            return `(${pct.toFixed(2)}%)`;
        },
        color: "#111",
        anchor: "end",
        align: "right",
        offset: 2,
        font: {
            weight: "600",
            size: 13
        },
        clamp: true,
        clip: false
    }
    : {
        enabled: true,
        formatter: (value) => value.toLocaleString(),
        anchor: "end",
        align: "right",
        color: "#000",
        offset: 5,
        font: {
            weight: "bold",
            size: 12
        }
    }

    };

    const safeData = Array.isArray(data) ? data : [];

    const positiveData = safeData
        .map(Number)
        .filter(v => Number.isFinite(v) && v > 0);

    const maxValue = positiveData.length
        ? Math.max(...positiveData)
        : 1;

    const minValue = 1;


    chartInstance = new Chart(ctx, {
        type,
        data: { labels, datasets: [dataset] },

        plugins: [
            ChartDataLabels,
            {
                id: "valueOutside",
                afterDatasetsDraw(chart) {
                    if (!isHorizontal) return;
                    const ctx = chart.ctx;
                    const meta = chart.getDatasetMeta(0);

                    ctx.save();
                    ctx.fillStyle = "#111";
                    ctx.font = "600 13px sans-serif";

                    meta.data.forEach((bar, i) => {
                        const formatted = data[i].toLocaleString();
                        ctx.fillText(formatted, bar.x + 60, bar.y + 5);
                    });

                    ctx.restore();
                }
            }
        ],

        options: {
            indexAxis: ax,
            responsive: true,

            layout: {
                padding: {
                    right: 130,
                    left: 10
                }
            },

            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "#1e293b",
                    titleColor: "#fff",
                    bodyColor: "#fff",
                    padding: 10,
                    borderRadius: 6
                },
            },

            scales: isHorizontal
                ? {
                    x: {
                        type: "logarithmic",
                        min: minValue,
                        max: maxValue,
                        grid: { display: false },
                        ticks: {
                            color: "#FFF",
                            callback: val => Number(val).toLocaleString()
                        }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            color: "#222",
                            font: { size: 13, weight: 600 },
                            padding: 5
                        }
                    }
                }
                : {
                    x: {
                        ticks: {
                            color: "#000",
                            font: { size: 13, weight: 600 },
                            padding: 5
                        },
                        grid: { color: "#ffffff22", display: true }
                    },
                    y: {
                        ticks: { color: "#fff" },
                        grid: { color: "#ffffff22", display: true }
                    }
                },

            animation: {
                onComplete: () => {
                    window.chartReady = true;
                }
            }
        }
    });

showPopup();
    setTimeout(() => {
          if (chartInstance) {
            chartInstance.resize();
          }
        }, 100);

}
let popupChartInstance;
function showPopup() {
    if (!window.chartReady) {
                    //requestAnimationFrame(showPopup);
                //return;
    }

                const popupCanvas = document.getElementById("popupGraphCanvas");
                const ctx = popupCanvas.getContext("2d");

                if (popupChartInstance) {
                    popupChartInstance.destroy();
    }

                // Create new popup chart using same plugins and options
                popupChartInstance = new Chart(ctx, {
                    type: chartInstance.config.type,
                data: chartInstance.config.data,
                options: chartInstance.config.options,
                plugins: chartInstance.config.plugins  // make sure datalabels + custom plugin work
    });

                popupChartInstance.resize();
                popupChartInstance.update();

                //document.getElementById("canvasPopup").style.display = "none";
}

function graphVarietyKgs(varieties,kgs) {

    createChart(
        "Variety Wise Storage",
        varieties,
        kgs,
        "bar",
        "y"
    );
}
var bardataPoints = <?php echo json_encode($values, JSON_NUMERIC_CHECK); ?>;
var barLabels = <?php echo json_encode($labels); ?>;
graphVarietyKgs(barLabels,bardataPoints);

         //updateGraph();

         function updateGraph(chbkExp, chbkShip) {
             //alert(chbkExp);

             //var AryShipmentXlables=<?php echo json_encode($Grade, true); ?>;

             if (chbkExp == true && chbkShip == true) {
                 var Series = [{
                         name: "Inward",
                         type: "column",
                         data: AryOrders
                     },
                     {
                         name: "OutWard",
                         type: "column",
                         data: AryShipMents
                     }
                 ];
                 var Colors = {
                     colors: ["#5182ef", "#40c79a"]
                 };
             } else if (chbkExp) {
                 var Series = [{
                     name: "Inward",
                     type: "column",
                     data: AryOrders
                 }];
                 var Colors = {
                     colors: ["#5182ef"]
                 };
             } else if (chbkShip) {
                 var Series = [{
                     name: "OutWard",
                     type: "column",
                     data: AryShipMents
                 }];
                 var Colors = {
                     colors: ["#40c79a"]
                 };
             } else {
                 var Series = [{
                         name: "Inward",
                         type: "column",
                         data: AryOrders
                     },
                     {
                         name: "OutWard",
                         type: "column",
                         data: AryShipMents
                     }
                 ];
                 var Colors = {
                     colors: ["#5182ef","#40c79a"]
                 };
             }

             chart.updateSeries(Series);
             chart.updateOptions(Colors);
         }



/* ===== Disable clipping ===== */
Chart.defaults.plugins.legend.labels.clip = false;

/* ===== Leader line plugin ===== */
const leaderLinePlugin = {
    id: 'leaderLine',
    afterDraw(chart) {
        const { ctx } = chart;
        const meta = chart.getDatasetMeta(0);
        if (!meta || !meta.data) return;

        const base = Math.min(chart.width, chart.height);
        const lineOffset = base * 0.06;
        const horizontalOffset = base * 0.12;
        const fontSize = Math.max(11, base * 0.03);
        const minSpacing = fontSize * 1.4;

        ctx.save();
        ctx.font = `${fontSize}px Arial`;
        ctx.fillStyle = '#333';
        ctx.strokeStyle = '#999';
        ctx.lineWidth = 1;

        const leftY = [];
        const rightY = [];

        meta.data.forEach((arc, index) => {
            const value = chart.data.datasets[0].data[index];
            if (!value) return;

            const angle = (arc.startAngle + arc.endAngle) / 2;
            const side = angle > Math.PI ? 'left' : 'right';

            const x = arc.x;
            const y = arc.y;
            const radius = arc.outerRadius;

            let labelY = y + Math.sin(angle) * (radius + lineOffset);

            const used = side === 'left' ? leftY : rightY;
            used.forEach(prevY => {
                if (Math.abs(labelY - prevY) < minSpacing) {
                    labelY = prevY + minSpacing;
                }
            });
            used.push(labelY);

            const startX = x + Math.cos(angle) * radius;
            const startY = y + Math.sin(angle) * radius;
            const midX = x + Math.cos(angle) * (radius + lineOffset);
            const endX = midX + (side === 'left' ? -horizontalOffset : horizontalOffset);

            // Draw leader line
            ctx.beginPath();
            ctx.moveTo(startX, startY);
            ctx.lineTo(midX, labelY);
            ctx.lineTo(endX, labelY);
            ctx.stroke();

            // Draw label
            ctx.textAlign = side === 'left' ? 'right' : 'left';
            ctx.textBaseline = 'middle';
            ctx.fillText(
                chart.data.labels[index],
                endX + (side === 'left' ? -6 : 6),
                labelY
            );
        });

        ctx.restore();
    }
};
const stockAgingTotal = <?= $stock_aging_total ?>;


const rawValues = <?php echo json_encode($values_aging); ?>;

const percentageValues = rawValues.map(val =>
    ((val / stockAgingTotal) * 100).toFixed(2)
);

/* ===== Chart initialization ===== */
const ctx = document.getElementById('agingChart').getContext('2d');

new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($labels_aging); ?>,
        datasets: [{
            data: percentageValues, // already percentages
            backgroundColor: [
                '#5182ef',
                '#40c79a',
                '#f7b13c',
                '#a27df8'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        layout: {
            padding: {
                left: 50,
                right: 50,
                top: 50,
                bottom: 100
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: (context) => {
                        return `${context.label}: ${context.parsed}%`;
                    }
                }
            },
            datalabels: {
                color: '#fff',
                font: {
                    weight: 'bold',
                    size: 14
                },
                formatter: (value) => {
                    return value + '%';
                }
            }
        }
    },
    plugins: [ChartDataLabels, leaderLinePlugin]
});




const hcData = Object.entries(AryLables_high_dunts).map(([label, value]) => [
    label,
    Number(value)
]);

Highcharts.chart('hc-donut-chart', {
    credits: {
        enabled: false  // removes "Highcharts.com"
    },
    title: {
        text: null   // <-- removes the title
    },
    chart: {
        type: 'pie',
        options3d: {
            enabled: true,
            alpha: 45
        }
    },
    colors: ["#2563eb", "#10b981", "#f59e0b", "#ef4444",
        "#8b5cf6", "#14b8a6", "#f97316", "#6366f1"],
    plotOptions: {
        pie: {
            innerSize: '75%',
            depth: 45,
            dataLabels: {
                enabled: false
            }
        }
    },

    series: [{
        name: SelectedUomLabel,
        data: hcData
    }]
});


document.addEventListener('DOMContentLoaded', function () {
    // Convert PHP data to Highcharts format dynamically
    var rawData = <?php echo json_encode($top5Points, JSON_NUMERIC_CHECK); ?>;
    var safeRawData = Array.isArray(rawData) ? rawData : [];

var values = safeRawData
    .map(function (o) {
        return Number(o.y);
    })
    .filter(function (v) {
        return Number.isFinite(v);
    });

var maxVal = values.length ? Math.max.apply(Math, values) : 0;

    // Find the maximum value in the dataset to calculate a smart dynamic offset threshold
//    var maxVal = Math.max.apply(Math, rawData.map(function(o) { return Number(o.y); }));

    var hcData = safeRawData.map(function(dp){
        var numericValue = Number(dp.y);

        var point = {
            name: dp.label,
            y: numericValue
        };

        // DYNAMIC FIX: If a slice is less than 1% of the largest slice,
        // it will dynamically receive the extra X/Y spacing to prevent overlaps.
        if (numericValue > 0 && (numericValue / maxVal) < 0.01) {
            point.dataLabels = {
                x: -30,         // Displaces the text horizontally on the X-axis
                y: -10,         // Nudges the text vertically to break collision
                distance: 55    // Lengthens its specific connector line
            };
        }

        return point;
    });

    Highcharts.chart('hc-pie-chart', {
        chart: {
            type: 'pie'
        },

        title: { text: null },       // remove chart title
        credits: { enabled: false }, // remove Highcharts.com text

        tooltip: {
            // Rounded to whole numbers (,.0f)
            pointFormat: '{series.name}: <b>{point.y:,.0f}</b>'
        },
        colors: ["#2563eb", "#10b981", "#f59e0b", "#ef4444",
        "#8b5cf6", "#14b8a6", "#f97316", "#6366f1"],
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                startAngle: 90,       // Prevents microscopic values from splitting right down the center 12 o'clock line
                dataLabels: {
                    enabled: true,
                    // Rounded to whole numbers (,.0f)
                    format: '{point.name} ({point.y:,.0f})',
                    distance: 30,
                    crop: false,
                    overflow: 'justify',
                    softConnector: true,  // Allows the connector lines to bend dynamically away from crowds
                    padding: 5,
                    style: {
                        fontSize: '11px',
                        textOutline: 'none'
                    }
                }
            }
        },

        series: [{
            name: SelectedUomLabel,
            colorByPoint: true,
            data: hcData
        }]
    });
});

const chartData = <?= json_encode($chartData); ?>;
const varieties  = <?= json_encode($varieties); ?>;
const ageBuckets = ['0–30', '31–90', '91–180', '181–365', '>365'];

const rowHeight = 56; // more modern spacing
const chartWrapper = document.getElementById('chartWrapper');
chartWrapper.style.height = `${varieties.length * rowHeight}px`;
const ctx2 = document.getElementById('agingVarietyChart');

new Chart(ctx2, {
    type: 'matrix',
    data: {
        datasets: [{
            label: 'Stock (' + SelectedUomLabel + ')',
            data: chartData,
            backgroundColor(ctx) {
    if (!ctx.raw) {
        return 'rgba(225,154,154,0.2)';
    }

    const value = ctx.raw.v;
    const alpha = Math.min(value / 5000, 1);
    return `rgba(225,154,154,${0.2 + 0.8 * alpha})`;
}
,
            borderColor: '#f4f6f8',
            borderWidth: 3,
            borderRadius: 6,
            width: ({ chart }) => {
                const area = chart.chartArea;
                return area ? (area.width / ageBuckets.length) - 18 : 20;
            },
            height: ({ chart }) => {
                const area = chart.chartArea;
                return area ? (area.height / varieties.length) - 14 : 20;
            }
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: { },
        scales: {
            x: {
                type: 'category',
                labels: ageBuckets,
                offset: true,
                bounds: 'data',
                title: {
                    display: true,
                    text: 'Aging (Days)',
                    color: '#555',
                    font: { size: 14, weight: '500' },
                    padding: { top: 20, bottom: 60 },
                    margin: {top:20,bottom:0}
                },
                grid: { color: '#eee' },
                ticks: { color: '#333', font: { size: 12 }, padding: 10 }
            },
            y: {
                type: 'category',
                labels: varieties,
                title: { display: true, text: 'Variety', color: '#555', font: { size: 14, weight: '500' }, padding: { right: 60 } },
                grid: { color: '#eee' },
                ticks: { color: '#333', font: { size: 12 } }
            }
        },
        plugins: {
            tooltip: {
                backgroundColor: '#222',
                titleColor: '#fff',
                bodyColor: '#fff',
                cornerRadius: 6,
                padding: 10,
                callbacks: {
                    label: (ctx) => {
                        const d = ctx.raw;

                        return [
                            `Variety: ${d.y}`,
                            `Age: ${d.x} days`,
                            `Block: ${d.block || 'OnFloor'}`,
                            `Total: ${d.v.toLocaleString()} ${SelectedUomLabel}`,
                            `Master: ${d.master.toLocaleString()} ${SelectedUomLabel}`,
                            `Dummy: ${d.dummy.toLocaleString()} ${SelectedUomLabel}`,
                            `IQF: ${(d.iqf || 0).toLocaleString()} ${SelectedUomLabel}`
                        ];
                    }
                }
            },

            legend: { display: false }
        }
    }
});




try{
    if(AryXlables.length > 15){
        plotOptionsAry = {
                column: {
                    borderRadius: 6,
                    //maxPointWidth: 25,
                    dataLabels: {
                        enabled: true,
                        formatter: function () {
                            return Highcharts.numberFormat(this.y, 0, '.', ',');
                        }
                    }
                }
            };
    }else{
        plotOptionsAry = {
                column: {
                    borderRadius: 6,
                    //maxPointWidth: 25,
                    dataLabels: {
                        enabled: true,
                        formatter: function () {
                            return Highcharts.numberFormat(this.y, 0, '.', ',');
                        }
                    }
                }
            };
    }
}catch(e){
    plotOptionsAry = {
                column: {
                    borderRadius: 6,
                    //maxPointWidth: 25,
                    dataLabels: {
                        enabled: true,
                        formatter: function () {
                            return Highcharts.numberFormat(this.y, 0, '.', ',');
                        }
                    }
                }
            };
 }

function escapeTooltipHtml(value) {
    return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatTooltipQty(value) {
    return Number(value || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
}

function normalizeTooltipDateKey(value) {
    return String(value == null ? '' : value).trim();
}

function getAlternateTooltipDateKeys(value) {
    const dateKey = normalizeTooltipDateKey(value);
    const alternateKeys = [dateKey];

    if (/^\d{2}-\d{2}-\d{4}$/.test(dateKey)) {
        const parts = dateKey.split('-');
        alternateKeys.push([parts[2], parts[1], parts[0]].join('-'));
    } else if (/^\d{4}-\d{2}-\d{2}$/.test(dateKey)) {
        const parts = dateKey.split('-');
        alternateKeys.push([parts[2], parts[1], parts[0]].join('-'));
    }

    return alternateKeys;
}

function getChartDateLabel(point, fallbackValue) {
    if (point && point.series && point.series.chart && point.series.chart.xAxis && point.series.chart.xAxis[0]) {
        const categoryIndex = typeof point.x !== 'undefined' ? point.x : fallbackValue;
        const categories = point.series.chart.xAxis[0].categories || [];
        if (typeof categoryIndex !== 'undefined' && categories[categoryIndex]) {
            return normalizeTooltipDateKey(categories[categoryIndex]);
        }
    }

    if (point && typeof point.x !== 'undefined' && AryXlables[point.x]) {
        return normalizeTooltipDateKey(AryXlables[point.x]);
    }

    if (point && point.category) {
        return normalizeTooltipDateKey(point.category);
    }

    if (point && point.key) {
        return normalizeTooltipDateKey(point.key);
    }

    if (typeof fallbackValue !== 'undefined' && AryXlables[fallbackValue]) {
        return normalizeTooltipDateKey(AryXlables[fallbackValue]);
    }

    return normalizeTooltipDateKey(fallbackValue);
}

function getTooltipItemsForPoint(point, fallbackChartDate) {
    const dataSource = point.series.name === 'Inward'
        ? DailyInwardTooltipMap
        : DailyOutwardTooltipMap;

    const lookupKeys = [
        getChartDateLabel(point, fallbackChartDate),
        fallbackChartDate,
        point && point.key,
        point && point.category,
    ]
        .flatMap(getAlternateTooltipDateKeys)
        .filter(Boolean);

    for (const lookupKey of lookupKeys) {
        if (Array.isArray(dataSource[lookupKey]) && dataSource[lookupKey].length) {
            return dataSource[lookupKey];
        }
    }

    return [];
}

function buildDailyTooltipSection(title, color, items) {
    if (!Array.isArray(items) || !items.length) {
        return '';
    }

    const visibleItems = items.slice(0, DailyInOutTooltipLimit).map(item => {
        return `<div style="display:flex;justify-content:space-between;gap:12px;margin-top:4px;">
            <span style="flex:1;white-space:normal;">${escapeTooltipHtml(item.group || '')}</span>
            <span style="font-weight:600;white-space:nowrap;">${formatTooltipQty(item.qty)} ${escapeTooltipHtml(SelectedUomLabel)}</span>
        </div>`;
    }).join('');

    const remainingCount = items.length - DailyInOutTooltipLimit;
    const overflowHtml = remainingCount > 0
        ? `<div style="margin-top:6px;color:#64748b;font-size:11px;">+${remainingCount} more grouped item(s)</div>`
        : '';

    return `<div style="margin-top:10px;padding-top:8px;border-top:1px solid #e2e8f0;">
        <div style="font-weight:700;color:${escapeTooltipHtml(color)};">${escapeTooltipHtml(title)}</div>
        ${visibleItems}
        ${overflowHtml}
    </div>`;
}

if(1){

    (function () {
     window.TotalOrders = <?php echo $TotalOrders?$TotalOrders:0 ?>;
     window.TotalShipments = <?php echo $TotalShipments?$TotalShipments:0 ?>;
    const canvas = document.getElementById('height-chart-weekly');

    if (!(canvas instanceof HTMLCanvasElement)) return;

    const ctx = canvas.getContext('2d');

    if (window.heightChartWeekly instanceof Chart) {
        window.heightChartWeekly.destroy();
    }

    window.heightChartWeekly = new Chart(ctx, {

        type: 'bar',

        data: {

            //labels: Array.isArray(window.AryXlables) ? window.AryXlables : [],
            labels: Array.isArray(window.AryXlables)
            ? window.AryXlables.map(date => {
                if (!date) return '';

                // YYYY-MM-DD -> DD
                return date.split('-')[0];
            })
            : [],

            datasets: [

                {
                    label: 'Inward',
                    data: AryOrders,
                    backgroundColor: '#8abcff',

                    borderRadius: {
                        topLeft: 6,
                        topRight: 6,
                        bottomLeft: 0,
                        bottomRight: 0
                    },

                    borderSkipped: false,
                    categoryPercentage: 0.6,
                    barPercentage: 0.7
                },
                {
                    label: 'OutWard',
                    data: AryShipMents,
                    backgroundColor: '#5CCFA9',

                    borderRadius: {
                        topLeft: 6,
                        topRight: 6,
                        bottomLeft: 0,
                        bottomRight: 0
                    },

                    borderSkipped: false,
                    categoryPercentage: 0.6,
                    barPercentage: 0.7
                }
            ]
        },

        options: {

            responsive: true,
            maintainAspectRatio: false,

            layout: {
                padding: {
                    top: 20,
                    right: 15,
                    bottom: 10,
                    left: 10
                }
            },

            interaction: {
                mode: 'index',
                intersect: false
            },

            plugins: {

                legend: {

                    position: 'bottom',

                    labels: {

                        usePointStyle: true,   //  THIS MAKES IT CIRCLE

                        pointStyle: 'circle',  //  FORCE CIRCLE

                        generateLabels(chart) {

                            const inward = Number(window.TotalOrders || 0);
                            const outward = Number(window.TotalShipments || 0);

                            const format = (v) => Number(v).toLocaleString('en-IN');

                            return chart.data.datasets.map((dataset, i) => {

                                let value = dataset.label === 'Inward'
                                    ? inward
                                    : outward;

                                return {
                                    text:
                                        dataset.label +
                                        ' : ' +
                                        format(value) +
                                        ' '+SelectedUomLabel,

                                    fillStyle: dataset.backgroundColor,
                                    strokeStyle: dataset.backgroundColor,
                                    hidden: !chart.isDatasetVisible(i),
                                    datasetIndex: i,

                                    pointStyle: 'circle' // FORCE per item
                                };
                            });
                        },

                        color: '#666',

                        font: {
                            size: 12,
                            /*weight: 'bold'*/
                        },

                        padding: 18
                    }
                },

                 tooltip: {

    enabled: false,

    external: function (context) {

        const { chart, tooltip } = context;

        let el = document.getElementById('chartjs-tooltip');

        if (!el) {
            el = document.createElement('div');
            el.id = 'chartjs-tooltip';
            document.body.appendChild(el);
        }

        if (tooltip.opacity === 0) {
            el.style.opacity = 0;
            return;
        }

        const dataPoints = tooltip.dataPoints || [];
        if (!dataPoints.length) return;

        const index = dataPoints[0].dataIndex;

        const chartDate =
            window.AryXlables?.[index] || '';

        let summaryHtml = '';
        let detailHtml = '';

        dataPoints.forEach(point => {

            const label = point.dataset.label;
            const value = point.raw || 0;
            const color = point.dataset.backgroundColor;

            summaryHtml += `
                <div style="display:flex;justify-content:space-between;margin-top:6px;">
                    <span style="font-weight:600;color:${color}">
                        ${escapeTooltipHtml(label)}
                    </span>
                    <span style="font-weight:700;">
                        ${formatTooltipQty(value)} ${escapeTooltipHtml(window.SelectedUomLabel || '')}
                    </span>
                </div>
            `;

            const groupedItems =
                getTooltipItemsForPoint(
                    { series: { name: label }, x: index },
                    chartDate
                );

            detailHtml += buildDailyTooltipSection(
                label + ' items',
                color,
                groupedItems
            );
        });

        el.innerHTML = `
            <div class="chart-tooltip-box">

                <div style="font-size:13px;font-weight:700;margin-bottom:6px;color:#111;">
                    ${escapeTooltipHtml(chartDate)}
                </div>

                ${summaryHtml}
                ${detailHtml}
            </div>

            <div class="chart-tooltip-arrow"></div>
        `;

        // POSITION ABOVE BAR (IMPORTANT)
        const rect = chart.canvas.getBoundingClientRect();

        const x = rect.left + window.pageXOffset + tooltip.caretX;
        const y = rect.top + window.pageYOffset + tooltip.caretY;

        el.style.opacity = 1;
        el.style.left = x + 'px';

        // move ABOVE bar instead of cursor
        el.style.top = (y - 10) + 'px';
    }
},

                // DATA LABELS (TOP VALUES LIKE HIGHCHARTS)
                datalabels: {

                    anchor: 'end',
                    align: 'end',

                    color: '#000',
                    font: {
                        /*weight: 'bold',*/
                        size: 12
                    },

                    formatter: function (value) {
                        if (value === null || value === undefined) return '';

                        // round + format like Highcharts
                        const rounded = Math.round(value);

                        return rounded.toLocaleString();
                    }
                }
            },

            scales: {

                x: {

                    grid: {
                        display: false
                    },

                    ticks: {
                        color: '#000',
                        maxRotation: 0,
                        minRotation: 0,
                        font: {
                            size: 12,
                            /*weight: 'bold'*/
                        }
                    }
                },

                y: {

                    beginAtZero: true,

                    grid: {
                        color: '#e6e6e6'
                    },

                    ticks: {

                        color: '#000',

                        font: {
                            size: 12
                        },

                        callback: function (value) {
                            if (value >= 1000000) {
                                return (value / 1000000).toFixed(1) + 'M';
                            }

                            if (value >= 1000) {
                                return (value / 1000).toFixed(0) + 'K';
                            }

                            return value;
                            //return value.toLocaleString(); // Highcharts-style formatting
                        }
                    }
                }
            }
        },

        plugins: [ChartDataLabels] //  IMPORTANT
    });

})();

}else{
Highcharts.chart('height-chart-weekly', {
    chart: {
        type: 'column'
    },

    title: {
        text: ''
    },

    xAxis: {
        categories: AryXlables
    },

    yAxis: {
        allowDecimals: false,
        title: {
            text: ''
        }
    },

    tooltip: {
        shared: true,
        useHTML: true,
        outside: false,
        formatter: function () {
            const points = this.points || (this.point ? [this.point] : []);
            const chartDate = getChartDateLabel(points[0], this.x);
            const summaryHtml = points.map(point => `
                <div style="display:flex;justify-content:space-between;gap:12px;margin-top:6px;">
                    <span style="font-weight:600;color:${escapeTooltipHtml(point.color)};">${escapeTooltipHtml(point.series.name)}</span>
                    <span style="font-weight:700;">${formatTooltipQty(point.y)} ${escapeTooltipHtml(SelectedUomLabel)}</span>
                </div>
            `).join('');

            const detailHtml = points.map(point => {
                const groupedItems = getTooltipItemsForPoint(point, chartDate);

                return buildDailyTooltipSection(
                    point.series.name + ' grouped items',
                    point.color,
                    groupedItems
                );
            }).join('');

            return `<div style="min-width:320px;max-width:520px;padding:2px 4px;">
                <div style="font-size:13px;font-weight:700;margin-bottom:4px;">${escapeTooltipHtml(chartDate || '')}</div>
                ${summaryHtml}
                ${detailHtml}
            </div>`;
        }
    },

    legend: {
        itemStyle: {
            cursor: 'pointer',
            fontSize: '0.8em',
            fill: 'rgb(102, 102, 102)',
            opacity: 1,
            textDecoration: 'none'
        },
        labelFormatter: function () {
            if (this.name === 'Inward') {
                return 'Inward : <?php echo number_format_inr($TotalOrders) ?> <?php echo $SelectedUomLabel; ?>';
            }
            if (this.name === 'OutWard') {
                return 'OutWard : <?php echo number_format_inr($TotalShipments) ?> <?php echo $SelectedUomLabel; ?>';
            }
            return this.name;
        }
    },

    plotOptions: {
        series: {
            events: {
                legendItemClick: function () {
                    const series = this;

                    // Toggle fade state stored in userOptions._faded
                    if (!series.userOptions._faded) {
                        // Fade out series graphics and points
                        series.group.attr({ opacity: 0.2 });
                        series.legendItem.attr({ 'text-decoration': 'none' });
                        if (series.data) {
                            series.data.forEach(point => {
                                if (point.graphic) {
                                    point.graphic.attr({ opacity: 0.2 });

                                }
                            });
                        }
                        // Fade legend text (optional)
                        if (series.legendItem) {
                            series.legendItem.attr({ opacity: 0.5, 'text-decoration': 'none' });
                        }
                        series.userOptions._faded = true;
                    } else {
                        // Restore full opacity
                        series.group.attr({ opacity: 1 });
                        if (series.data) {
                            series.data.forEach(point => {
                                if (point.graphic) {
                                    point.graphic.attr({ opacity: 1 });
                                }
                            });
                        }
                        if (series.legendItem) {
                            series.legendItem.attr({ opacity: 1, 'text-decoration': 'none' });
                        }
                        series.userOptions._faded = false;
                    }

                    // Prevent default toggle (which hides the series and causes strikethrough)
                    return false;
                }
            }
        },
        // merge in your other plotOptions if needed:
        ...plotOptionsAry
    },

    series: [
        {
            name: 'Inward',
            data: AryOrders,
            color: '#8abcff'
        },
        {
            name: 'OutWard',
            data: AryShipMents,
            color: '#5CCFA9'
        }
    ]
});
}







function formatCompactAxisValue(value) {
    if (value >= 1000000) {
        return (value / 1000000).toFixed(1) + 'M';
    }

    if (value >= 1000) {
        return (value / 1000).toFixed(0) + 'K';
    }

    return value;
}

function renderStockColumnChart(config) {
    const canvas = document.getElementById(config.canvasId);

    if (!(canvas instanceof HTMLCanvasElement)) {
        return;
    }

    if (window[config.instanceKey] instanceof Chart) {
        window[config.instanceKey].destroy();
    }

    const ctx = canvas.getContext('2d');

    window[config.instanceKey] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Array.isArray(config.labels) ? config.labels : [],
            datasets: [
                {
                    label: config.label,
                    data: Array.isArray(config.data) ? config.data : [],
                    backgroundColor: config.color || '#5CCFA9',
                    borderRadius: {
                        topLeft: 6,
                        topRight: 6,
                        bottomLeft: 0,
                        bottomRight: 0
                    },
                    borderSkipped: false,
                    categoryPercentage: 0.62,
                    barPercentage: 0.72,
                    maxBarThickness: 42
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 20,
                    right: 15,
                    bottom: 10,
                    left: 10
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        color: '#666',
                        font: {
                            size: 12
                        },
                        padding: 18,
                        generateLabels(chart) {
                            const dataset = chart.data.datasets[0] || {};
                            const isVisible = chart.isDatasetVisible(0);

                            return [
                                {
                                    text: `${config.label} : ${Number(config.total || 0).toLocaleString('en-IN')} ${SelectedUomLabel}`,
                                    fillStyle: dataset.backgroundColor,
                                    strokeStyle: dataset.backgroundColor,
                                    hidden: !isVisible,
                                    datasetIndex: 0,
                                    pointStyle: 'circle'
                                }
                            ];
                        }
                    }
                },
                tooltip: {
                    backgroundColor: '#ffffff',
                    titleColor: '#111827',
                    bodyColor: '#111827',
                    borderColor: '#dbe4f0',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        title(items) {
                            return items[0]?.label || '';
                        },
                        label(context) {
                            return `${config.label}: ${formatTooltipQty(context.parsed.y || 0)} ${SelectedUomLabel}`;
                        }
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    color: '#000',
                    font: {
                        size: 12
                    },
                    formatter(value) {
                        if (value === null || value === undefined || value === '') {
                            return '';
                        }

                        return Math.round(Number(value)).toLocaleString('en-IN');
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#000',
                        maxRotation: 45,
                        minRotation: 45,
                        autoSkip: false,
                        font: {
                            size: 12
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#e6e6e6'
                    },
                    ticks: {
                        color: '#000',
                        font: {
                            size: 12
                        },
                        callback(value) {
                            return formatCompactAxisValue(value);
                        }
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

renderStockColumnChart({
    canvasId: 'height-chart-production',
    instanceKey: 'heightChartProduction',
    label: 'Dummy Stock',
    labels: AryGradeBar3,
    data: (SelectedUom === 'MCS') ? AryMcsBar3 : AryKgsBar3,
    total: <?php echo $dummy_cnt; ?>,
    color: '#5CCFA9'
});

renderStockColumnChart({
    canvasId: 'height-chart-production-master',
    instanceKey: 'heightChartProductionMaster',
    label: 'Master Stock',
    labels: AryGradeBar3Master,
    data: (SelectedUom === 'MCS') ? AryMcsBar3Master : AryKgsBar3Master,
    total: <?php echo json_encode($TotalMasterDisplay, JSON_NUMERIC_CHECK); ?>,
    color: '#5CCFA9'
});



(function () {
const stockData = <?php echo json_encode($dataBrnadGradeQty, JSON_NUMERIC_CHECK); ?>;
    const chartCanvasId = 'brandGradeStockChart';
    const ctx = document.getElementById(chartCanvasId);
    if (!ctx) return;

    // Destroy existing chart if re-rendering
    if (Chart.getChart(chartCanvasId)) {
        Chart.getChart(chartCanvasId).destroy();
    }

    //const stockData = responseData; // your API data

    const brandLabels = [...new Set(stockData.map(d => d.BrandName))];
    //const gradeLabels = [...new Set(stockData.map(d => d.Grade))];
    const gradeLabels = [...new Set(
    stockData
        .map(d => d.Grade)
        .filter(g => g != null && g !== '')
)]
.sort((a, b) => {
    const [a1 = 0, a2 = 0] = String(a).split('/').map(Number);
    const [b1 = 0, b2 = 0] = String(b).split('/').map(Number);

    return a1 - b1 || a2 - b2;
});

    const generateColors = (count) => {
        return Array.from({ length: count }, (_, i) =>
            `hsl(${(i * 360) / count}, 65%, 55%)`
        );
    };

     const gradeColors = generateColors(gradeLabels.length);

    const datasets = gradeLabels.map((grade, index) => ({
    label: grade,
    data: brandLabels.map(brand => {
        return stockData
          .filter(d => d.BrandName === brand && d.Grade === grade)
          .reduce((sum, d) => {
              const metric = (SelectedUom === 'MCS') ? d.TotalMCS : d.TotalKGS;
              return sum + Number(metric || 0);
          }, 0);
    }),
    backgroundColor: `hsl(${(index * 360) / gradeLabels.length}, 65%, 55%)`,
    stack: 'stock'
}));


    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: brandLabels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0,0,0,0.8)',   // translucent dark bg
                    titleFont: { size: 14, weight: 'bold' },  // bold title
                    bodyFont: { size: 12 },                    // comfortable body text
                    padding: 10,
                    cornerRadius: 6,
                    displayColors: true,                       // color boxes for each item
                    maxWidth: 250,                            // limit width to 250px
                    filter: function(tooltipItem) {
                        // Show only items with value > 0
                        return tooltipItem.parsed.y > 0;
                    },
                    position: 'nearest',
                    yAlign: 'top',                            // show above hovered bar
                    callbacks: {
                        label: function(context) {
                            const label = context.dataset.label || '';
                            const value = context.parsed.y || 0;
                            return `${label}: ${value.toLocaleString()}`;  // commas in numbers
                        }
                    }
                }

            },
            scales: {
                x: { stacked: true },
                y: {
                    stacked: true,
                    title: { display: true, text: 'Total ' + SelectedUomLabel }
                }
            }
        }

    });

})();


(() => {
  const yearFilter = ($('#Year').length && $('#Year').val()) ? $('#Year').val() : '';
  const monthFilter = ($('#Month').length && $('#Month').val()) ? $('#Month').val() : '';
  const Species = $('#species').val();
  const canvasId = 'productionTrendChart';

  const trendQuery = new URLSearchParams();
  if (yearFilter) trendQuery.set('Year', yearFilter);
  if (monthFilter) trendQuery.set('Month', monthFilter);
  trendQuery.set('UOM', SelectedUom);
  trendQuery.set('PID', SelectedPID);
  trendQuery.set('Species', Species);

  const apiUrl = '/v3/ColdStore/Ajax/ajax-dashboard-insights.php?' + trendQuery.toString();


  if (window.productionChart instanceof Chart) {
    window.productionChart.destroy();
  }

  fetch(apiUrl)
    .then(res => res.json())
    .then(data => {
      if (!data || !data.length) {
        console.warn('No data received');
        return;
      }

      // ✅ Sort data by date (important)
      data.sort((a, b) => new Date(a.ProductionDate) - new Date(b.ProductionDate));

      // ✅ Use API data directly (FIXED)
      const labels = data.map(d => d.ProductionDate);
      const completed = data.map(d => Number(d.Completed_Kgs || 0));
      const pending = data.map(d => Number(d.Pending_Kgs || 0));

      const ctx = document.getElementById(canvasId).getContext('2d');

      // 🎨 Gradients
      const completedGradient = ctx.createLinearGradient(0, 0, 0, 400);
      completedGradient.addColorStop(0, 'rgba(34,197,94,0.9)');
      completedGradient.addColorStop(1, 'rgba(34,197,94,0.4)');

      const pendingGradient = ctx.createLinearGradient(0, 0, 0, 400);
      pendingGradient.addColorStop(0, 'rgba(239,68,68,0.9)');
      pendingGradient.addColorStop(1, 'rgba(239,68,68,0.4)');

      // 📊 Create chart
      window.productionChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              label: `Completed (${SelectedUomLabel})`,
              data: completed,
              backgroundColor: completedGradient,
              borderRadius: 10,
              barThickness: 26
            },
            {
              label: `Pending (${SelectedUomLabel})`,
              data: pending,
              backgroundColor: pendingGradient,
              borderRadius: 10,
              barThickness: 26
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false
          },
          animation: {
            duration: 1200,
            easing: 'easeOutQuart'
          },
          layout: {
            padding: {
              bottom: 50
            }
          },
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                usePointStyle: true,
                padding: 20,
                boxWidth: 10,
                font: {
                  size: 13,
                  weight: '600'
                }
              }
            },
            tooltip: {
              backgroundColor: '#111827',
              titleColor: '#F9FAFB',
              bodyColor: '#E5E7EB',
              padding: 12,
              callbacks: {
                footer: (items) => {
                  const total = items.reduce((sum, i) => sum + i.parsed.y, 0);
                  return `Total: ${total.toFixed(2)} ${SelectedUomLabel}`;
                }
              }
            }
          },
          scales: {
            x: {
              stacked: true,
              grid: {
                display: false
              },
              ticks: {
                font: {
                  size: 12
                }
              }
            },
            y: {
              stacked: true,
              beginAtZero: true,
              grid: {
                color: 'rgba(0,0,0,0.06)',
                drawBorder: false
              },
              ticks: {
                callback: value => value + ' ' + SelectedUomLabel
              }
            }
          }
        }
      });

      // 🧪 Debug (optional)
      //console.log('Labels:', labels);
      //console.log('Completed:', completed);
      //console.log('Pending:', pending);
    })
    .catch(err => console.error('Chart load error:', err));
})();

(() => {
    const yearFilter = ($('#Year').length && $('#Year').val()) ? $('#Year').val() : '';
    const monthFilter = ($('#Month').length && $('#Month').val()) ? $('#Month').val() : '';
    const shipmentQuery = new URLSearchParams({ action: 'shipment' });
    if (yearFilter) shipmentQuery.set('Year', yearFilter);
    if (monthFilter) shipmentQuery.set('Month', monthFilter);
    if (SelectedPID) shipmentQuery.set('PID', SelectedPID);
    if (Species) shipmentQuery.set('Species', Species);

    const shipmentApiUrl = '/v3/ColdStore/Ajax/ajax-dashboard-insights.php?' + shipmentQuery.toString();

    fetch(shipmentApiUrl)
  .then(res => res.json())
  .then(data => {
    const labels = data.map(d => d.Status);
    const values = data.map(d => {
      const match = String(d.PoCount).match(/\d+/);
      return match ? parseInt(match[0]) : 0;
    });
    const displayValues = data.map(d => d.PoCount);
    new Chart(document.getElementById('coldStoreChart'), {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: values,
          backgroundColor: [
            '#4caf50', // Stock Out Done
            '#2196f3', // Shipment Out Done
            '#ff9800'  // Shipment Out Pending
          ],
          borderColor: '#fff',
          borderWidth: 2
        }]
      },
      options: {
  layout: {
    padding: {
      left: 2,
      right: 0,
      top: 20,
      bottom: 0
    },
    margin:{
        top:0,
        bottom:0
    }
  },
        plugins: {
          legend: {
            position: 'bottom',
            labels: { boxWidth: 20, padding: 15,
                generateLabels: function(chart) {
                    const data = chart.data;
                    return data.labels.map((label, i) => {
                      const value = data.datasets[0].data[i];
                      return {
                        text: `${label}: ${displayValues[i]}`,
                        fillStyle: data.datasets[0].backgroundColor[i],
                        strokeStyle: '#fff',
                        lineWidth: 2,
                        hidden: false,
                        index: i
                      };
                    });
                  }
            }
          },
          tooltip: {
            callbacks: {
              label: function(ctx) {
                return ctx.label + ': ' + ctx.raw ;
              }
            }
          },
          datalabels: {
            color: '#fff',
            font: {
              weight: 'bold',
              size: 14
            },
            formatter: (value) => value.toLocaleString('en-IN'),
            anchor: 'center',
            align: 'center'
          }
        },
        cutout: '50%', // Makes it a ring chart
        onClick: (evt, elements) => {
          if (elements.length > 0) {
            const index = elements[0].index;
            openPoModal(labels[index]);
          }
        }
      }
    });
  });


})();

(() => {
    var plantwiseChart = null;
    var table;

    function renderPlantwiseGraph(data) {
        var options = {
            chart: {
                type: 'bar',
                height: 450,
                toolbar: {
                    show: false
                },
                events: {
                    dataPointSelection: function (event, chartContext, config) {
                        var seriesIndex = config.seriesIndex;
                        var dataPointIndex = config.dataPointIndex;
                        var name = config.w.config.series[seriesIndex]['name'];
                        getBuyerWiseData(data['exporterDetails'][dataPointIndex], name);
                    }
                }
            },
            series: data['series'],
            xaxis: {
                categories: data['stationNames']
            },
            yaxis: {
                title: {
                    text: 'Count'
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    dataLabels: {
                        position: 'top'
                    },
                    columnWidth: '50%'
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    if (parseFloat(val) < 30 && parseFloat(val) > 0) {
                        return parseInt(val);
                    }
                    return '';
                },
                offsetY: -10,
                style: {
                    fontSize: '12px',
                    colors: ["#304758"],
                    rotate: -45
                }
            },
            legend: {
                show: true
            },
            tooltip: {
                enabled: true,
                y: {
                    formatter: function (val) {
                        return val;
                    }
                },
                style: {
                    fontSize: '12px'
                }
            }
        };

        if (plantwiseChart) {
            plantwiseChart.destroy();
        }

        plantwiseChart = new ApexCharts(document.querySelector("#plantwiseOrders"), options);
        plantwiseChart.render().then(() => {
            const dataLabels = document.querySelectorAll('.apexcharts-text.apexcharts-datalabel');
            dataLabels.forEach(label => {
                label.style.transform = 'rotate(-45deg)';
                label.style.transformOrigin = 'center';
            });
        });
    }

    function getPlantwiseOrdersData() {
        /*const yearFilter = ($('#Year').length && $('#Year').val()) ? $('#Year').val() : '';
        const monthFilter = ($('#Month').length && $('#Month').val()) ? $('#Month').val() : '';*/
        const yearFilter = parseInt($('#Year').val());
        const monthFilter = parseInt($('#Month').val());


        // First day of month
        var FromDateObjf = new Date(yearFilter, monthFilter - 1, 1);

        // Last day of month
        var ToDateObjf = new Date(yearFilter, monthFilter, 0);

        // Format to YYYY-MM-DD
        function formatDate2(date) {
            var d = date.getDate().toString().padStart(2, '0');
            var m = (date.getMonth() + 1).toString().padStart(2, '0');
            var y = date.getFullYear();
            return y + '-' + m + '-' + d;
        }
        var FromDatef = formatDate2(FromDateObjf);
        var ToDatef = formatDate2(ToDateObjf);
        const q = new URLSearchParams({ action: 'shipment_plantwise_orders' });
        if (FromDatef) q.set('FromDate', FromDatef);
        if (ToDatef) q.set('ToDate', ToDatef);
        if (SelectedPID) q.set('PID', SelectedPID);
        if (Species) q.set('Species', Species);

        $.get('/v3/ColdStore/Ajax/ajax-dashboard-insights.php?' + q.toString(), function (resp) {
            var data = resp;
            if (typeof resp === 'string') {
                try {
                    data = JSON.parse(resp);
                } catch (e) {
                    console.error('Plant-wise response parse error:', e, resp);
                    return;
                }
            }
            if (data && data.AryPlantwiseGraphDtls && data.AryPlantwiseGraphDtls.series) {
                renderPlantwiseGraph(data.AryPlantwiseGraphDtls);
            } else {
                console.error('Plant-wise data missing:', data);
            }
        });
    }

    // --- Store Availability (sum-of-qty-by-block) ---
    function renderStoreAvailabilityChart() {
        if (!storeAvailabilityData || storeAvailabilityData.length === 0) return;

        var labels = storeAvailabilityData.map(function(item) { return item.BlockName; });
        var totalRacks = storeAvailabilityData.map(function(item) { return item.TotalRacks; });
        var filledRacks = storeAvailabilityData.map(function(item) { return item.Filled; });
        var availableRacks = storeAvailabilityData.map(function(item) { return item.AvailableRacks; });

        var options = {
            series: [
                { name: 'Total Racks', data: totalRacks },
                { name: 'Filled Racks', data: filledRacks },
                { name: 'Available Racks', data: availableRacks }
            ],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '30%'
                }
            },
            xaxis: {
                categories: labels
            },
            yaxis: {
                title: { text: 'Rack Count' }
            },
            legend: { show: true },
            noData: {
                text: 'No data available',
                style: { color: '#FF0000', fontSize: '16px', fontFamily: 'Arial' }
            }
        };

        var chartEl = document.querySelector("#store-availability-chart");
        if (chartEl) {
            var chart = new ApexCharts(chartEl, options);
            chart.render();
        }
    }

    renderStoreAvailabilityChart();

    // --- Order Status (PIE chart by StationName with drill-down) ---
    function renderOrderStatusChart() {

    if (
        typeof orderStatusData === 'undefined' ||
        !Array.isArray(orderStatusData) ||
        orderStatusData.length === 0
    ) {
        console.warn('No order status data available');
        return;
    }

    var $stationSelect = $('#orderStatusStationSelect');

    $stationSelect.empty();

    $.each(orderStatusData, function (i, item) {

        var stationName =
            item.StationName ||
            ('Plant ' + (item.PlantID || ''));

        var totalOrders =
            Number(item.TotalOrders || 0);

        $stationSelect.append(
            $('<option>', {
                value: i,
                text: stationName + ' (' + totalOrders + ' orders)'
            })
        );
    });


    function drawStationChart(idx) {

        var plant = orderStatusData[idx];

        if (!plant) {
            return;
        }

        var plantId = Number(plant.PlantID || 0);

        var stationName =
            plant.StationName ||
            ('Plant ' + plantId);


        var completed =
            Number(plant.CompletedPercent || 0);

        var partial =
            Number(plant.PartialPercent || 0);

        var pending =
            Number(plant.PendingPercent || 0);


        var realValues = [
            completed,
            partial,
            pending
        ];


        /*
         * IMPORTANT
         *
         * Do not use 0.01 for the series.
         * It changes the actual pie values and can
         * interfere with selection.
         *
         * ApexCharts can render zero values.
         */
        var seriesData = [
            completed,
            partial,
            pending
        ];


        var statusList = [
            'Completed',
            'Partial',
            'Pending'
        ];


        var chartEl =
            document.querySelector(
                '#order-status-by-plant'
            );

        if (!chartEl) {
            console.error(
                '#order-status-by-plant not found'
            );
            return;
        }


        /*
         * Destroy previous chart
         */
        if (window.orderStatusApexChart) {

            try {
                window.orderStatusApexChart.destroy();
            } catch (e) {
                console.warn(e);
            }

            window.orderStatusApexChart = null;
        }


        chartEl.innerHTML = '';


        var options = {

            series: seriesData,

            labels: [
                'Completed',
                'Partial',
                'Pending'
            ],

            colors: [
                '#22c55e',
                '#f59e0b',
                '#ef4444'
            ],


            chart: {

                type: 'pie',

                height: 350,

                toolbar: {
                    show: false
                },

                /*
                 * THIS IS THE IMPORTANT PART
                 */
                events: {

                    dataPointSelection:
                        function (
                            event,
                            chartContext,
                            config
                        ) {

                            console.log(
                                'PIE SLICE SELECTED',
                                config
                            );


                            var sliceIndex =
                                config.dataPointIndex;


                            if (
                                sliceIndex === undefined ||
                                sliceIndex === null ||
                                sliceIndex < 0
                            ) {

                                console.warn(
                                    'No pie slice selected',
                                    config
                                );

                                return;
                            }


                            var selectedStatus =
                                statusList[sliceIndex];


                            console.log(
                                'Selected Status:',
                                selectedStatus
                            );

                            console.log(
                                'Plant ID:',
                                plantId
                            );

                            console.log(
                                'Station:',
                                stationName
                            );


                            if (!plantId) {

                                console.error(
                                    'PlantID is missing:',
                                    plant
                                );

                                //return;
                            }


                            if (
                                typeof openOrderStatusDetail !==
                                'function'
                            ) {

                                console.error(
                                    'openOrderStatusDetail() is not defined'
                                );

                                return;
                            }


                            /*
                             * CALL DETAIL FUNCTION
                             */
                            openOrderStatusDetail(
                                plantId,
                                stationName,
                                selectedStatus
                            );
                        }
                }
            },


            /*
             * Allow slice expansion when clicked
             */
            plotOptions: {

                pie: {

                    expandOnClick: true,

                    customScale: 0.9
                }
            },


            legend: {

                show: true,

                position: 'bottom',

                fontSize: '13px',

                formatter:
                    function (
                        seriesName,
                        opts
                    ) {

                        var value =
                            realValues[
                                opts.seriesIndex
                            ] || 0;

                        return (
                            seriesName +
                            ': ' +
                            Number(value).toFixed(1) +
                            '%'
                        );
                    }
            },


            tooltip: {

                y: {

                    formatter:
                        function (
                        seriesName,
                        opts
                    ) {

                        var value =
                            realValues[
                                opts.seriesIndex
                            ] || 0;

                        return (
                            seriesName +
                            '%'
                        );
                    }
                }
            },


            dataLabels: {

                enabled: true,

                formatter:
                    function (
                        value,
                        opts
                    ) {

                        var realValue =
                            realValues[
                                opts.seriesIndex
                            ] || 0;


                        if (realValue <= 0) {
                            return '';
                        }


                        return (
                            Number(realValue)
                                .toFixed(1) +
                            '%'
                        );
                    },

                style: {

                    fontSize: '13px',

                    fontWeight: '600'
                },

                dropShadow: {
                    enabled: false
                }
            },


            stroke: {

                show: true,

                width: 2,

                colors: ['#ffffff']
            },


            noData: {

                text: 'No data available'
            }
        };


        window.orderStatusApexChart =
            new ApexCharts(
                chartEl,
                options
            );


        window.orderStatusApexChart.render();
    }


    /*
     * First plant
     */
    drawStationChart(0);


    /*
     * Plant dropdown change
     */
    $stationSelect
        .off('change.orderStatus')
        .on(
            'change.orderStatus',
            function () {

                var idx =
                    parseInt(
                        $(this).val(),
                        10
                    );

                if (isNaN(idx)) {
                    return;
                }

                drawStationChart(idx);
            }
        );
}




/*
 * Call after orderStatusData is available
 */



renderOrderStatusChart();





    window.getBuyerWiseData = function (Processor, ReqType) {
        var YEAR = parseInt($('#Year').val());
        var MONTH = parseInt($('#Month').val());
        ReqType = (ReqType) ? ReqType : '';

        // First day of month
        var FromDateObj = new Date(YEAR, MONTH - 1, 1);

        // Last day of month
        var ToDateObj = new Date(YEAR, MONTH, 0);

        // Format to YYYY-MM-DD
        function formatDate(date) {
            var d = date.getDate().toString().padStart(2, '0');
            var m = (date.getMonth() + 1).toString().padStart(2, '0');
            var y = date.getFullYear();
            return y + '-' + m + '-' + d;
        }

        var FromDate = formatDate(FromDateObj);
        var ToDate = formatDate(ToDateObj);
        $.ajax({
            url: '/v3/Sales/Ajax/AjaxBuyerDetailsNew.php',
            type: 'POST',
            data: {
                'Year': '',
                'ExpID': '',
                'ConsigneeID': '',
                'Month': '',
                'CountryID': '',
                'Brand': '',
                'FromDate': FromDate,
                'ToDate': ToDate,
                'Processor': (Processor != 'ALL') ? Processor : '',
                'ReqType': ReqType,
                'DashBoard': 1,
                'frompage':'csdashboard',
                'Species': Species
            },
            success: function (data) {
                $("#PopUpTableFullScreen").html(data);
                if ($("#PopUpTableFullScreen #key-datatableFullscreen tbody tr").length === 0) {
                    console.warn('Plant-wise popup returned 0 rows', {
                        Processor: Processor,
                        ReqType: ReqType,
                        Year: YEAR
                    });
                }

                var $popupTable = $("#PopUpTableFullScreen #key-datatableFullscreen");
                $popupTable.removeClass().addClass("table table-bordered table-hover dt-responsive nowrap w-100");
                $popupTable.find("thead th").css({
                    "border-bottom": "2px solid black",
                    "font-size": "14px"
                });

                if ($.fn.dataTable.isDataTable('#key-datatableFullscreen')) {
                    if (table) {
                        table.destroy();
                    }
                }
                table = $('#key-datatableFullscreen').DataTable({
                    autoWidth: false,
                    scrollY: "380px",
                    paging: true,
                    scrollX: true,
                    scrollCollapse: true,
                    dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"l f B><"col-md-6">>rtip',
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    pageLength: 10,
                    searching: true,
                    ordering: false,
                    language: {
                        lengthMenu: "Show _MENU_ records",
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        }
                    },
                    drawCallback: function () {
                        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    },
                    buttons: [
  {
    extend: 'excelHtml5',
    text: 'Download Excel',
    className: 'btn btn-success'
  }
],
                    initComplete: function () {
                        const api = this.api();
                        $("#key-datatableFullscreen_filter label")
                            .contents()
                            .filter(function () {
                                return this.nodeType === 3;
                            })
                            .remove();

                        $("#key-datatableFullscreen_filter input[type='search']")
                            .attr("placeholder", "Search records...")
                            .addClass("dt-search-input");

                        const popupSearchInput = $("#key-datatableFullscreen_filter input[type='search']");
                        if (!popupSearchInput.parent().hasClass("dt-search-wrapper")) {
                            popupSearchInput.wrap('<div class="dt-search-wrapper"></div>');
                            popupSearchInput.before('<span class="ri-search-line"></span>');
                        }

                        setTimeout(function () {
                            api.columns.adjust();
                        }, 0);
                    }
                });
                $('#PopUpTableFullScreen #key-datatableFullscreen_wrapper > .row > .col-sm-12.col-md-6:first-child')
                    .html('<h4 class="modal-title">' + ReqType + ' Orders</h4>');
                $('#myModalFullScreen').modal('show');
                $('#myModalFullScreen').off('shown.bs.modal').on('shown.bs.modal', function () {
                    table.columns.adjust().draw();
                });
            }
        });
    };

    getPlantwiseOrdersData();
})();

let poTable; // global variable

function openPoModal(status) { //return false;
  const yearFilter = ($('#Year').length && $('#Year').val()) ? $('#Year').val() : '';
  const monthFilter = ($('#Month').length && $('#Month').val()) ? $('#Month').val() : '';
  document.getElementById('poModalTitle').innerText = status + ' - PO List';

  if ($.fn.DataTable.isDataTable('#poTable')) {
        $('#poTable').DataTable().destroy();
    }

  // Destroy previous instance if exists
  /*if (poTable) {
    poTable.destroy();
    document.querySelector('#poTable tbody').innerHTML = '';
  }*/

  /*poTable = $('#poTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '/v3/ColdStore/Ajax/ajax-dashboard-insights.php',
      type: 'GET',
      data: {
        action: 'shipmentpopup',
        status: status
      }
    },
    columns: [
      { data: 'PoNo' },
      { data: 'Grade' },
      { data: 'GlazeType' },
      { data: 'Variety' }
    ],
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100]
  });*/
  $('#poTable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                scrollY: "380px",
                scrollX: true,
                scrollCollapse: true,
                dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
                buttons: [
                  {
                    text: 'Download CSV',
                    className: 'btn btn-primary',
                    action: function () {
                      let url = '/v3/ColdStore/Ajax/ajax-dashboard-insights.php?action=shipmentpopup_export'
                        + '&status=' + status
                        + '&Year=' + yearFilter
                        + '&Month=' + monthFilter
                        + '&PID=' + SelectedPID
                        + '&Species=' + encodeURIComponent(Species);

                      window.open(url, '_blank');
                    }
                  }
                ],
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 10,
                searching: true,
                ordering: false,
                language: {
                  lengthMenu: "Show _MENU_ records",
                  info: "Showing _START_ to _END_ of _TOTAL_ entries",
                  infoFiltered: "",
                  paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                  }
                },
                ajax: {
                  url: '/v3/ColdStore/Ajax/ajax-dashboard-insights.php',
                  type: 'GET',
                  data: {
                    action: 'shipmentpopup',
                    status: status,
                    Year: yearFilter,
                    Month: monthFilter,
                    PID: SelectedPID,
                    Species: Species,
                  }
                },
                columns: [
                  { data: 'PoNo' },
                  { data: 'PoDate' },
                  { data: 'ShipByDate' },
                  { data: 'Grade' },
                  { data: 'ProductDescription' },
                  { data: 'StockOutDate' },
                  { data: 'ActualMcs' }
                ],
                pageLength: 10,
                drawCallback: function () {
                  $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                initComplete: function () {
                  const api = this.api();
                  $("#poTable_filter label")
                    .contents()
                    .filter(function () {
                      return this.nodeType === 3;
                    })
                    .remove();

                  $("#poTable_filter input[type='search']")
                    .attr("placeholder", "Search records...")
                    .addClass("dt-search-input");
                  const poSearchInput = $("#poTable_filter input[type='search']");
                  if (!poSearchInput.parent().hasClass("dt-search-wrapper")) {
                    poSearchInput.wrap('<div class="dt-search-wrapper"></div>');
                    poSearchInput.before('<span class="ri-search-line"></span>');
                  }

                  setTimeout(function () {
                    api.columns.adjust();
                  }, 0);
                }
            });

    $('#poModal').off('shown.bs.modal.poTable').on('shown.bs.modal.poTable', function () {
      if ($.fn.DataTable.isDataTable('#poTable')) {
        $('#poTable').DataTable().columns.adjust();
      }
    });

    $('#poModal').modal('show');
}


async function fetchAndRenderTopShortages() {
  try {
    const response = await fetch('/v3/ColdStore/Ajax/ajax-dashboard-insights.php?action=rmreq&UOM=' + encodeURIComponent(SelectedUom) + '&PID='+SelectedPID+ '&Species='+Species);  // Your API endpoint
    const dataFromServer = await response.json();

    const labels = dataFromServer.map(item => `${item.ShortName} (${item.Grade})`);
    const requiredValues = dataFromServer.map(item => item.RequiredKgs);

    const ctx = document.getElementById('topShortagesChart').getContext('2d');

    // Destroy previous chart instance if exists (avoid multiple charts on same canvas)
    if(window.topShortagesChartInstance) {
      window.topShortagesChartInstance.destroy();
    }

    window.topShortagesChartInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Required ' + SelectedUomLabel + ' (Shortage)',
          data: requiredValues,
          backgroundColor: 'rgba(255, 99, 132, 0.7)',
          borderColor: 'rgba(255, 99, 132, 1)',
          borderWidth: 1,
          borderRadius: 4,
        }]
      },
      options: {
        layout: {
    padding: {
      left: 0,
      right: 0,
      top: 0,
      bottom: 50
    },
    margin:{
        top:0,
        bottom:0
    }
  },
        indexAxis: 'y',
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: ctx => ctx.parsed.x.toLocaleString() + ' '
            }
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            ticks: { callback: val => val.toLocaleString() },
            title: { display: true, text: 'Required ' + SelectedUomLabel, font: { size: 14, weight: 'bold' } }
          },
          y: {
            title: { display: true, text: 'Product (Grade)', font: { size: 14, weight: 'bold' } }
          }
        }
      }
    });

  } catch (error) {
    console.error('Failed to fetch or render chart:', error);
  }
}

// Call the function when page loads or after some event
fetchAndRenderTopShortages();

async function fetchIBTRPENDINGList() {

  if ($.fn.DataTable.isDataTable('#ibtTable')) {
    $('#ibtTable').DataTable().destroy();
  }

  $('#ibtTable').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        scrollY: "280px",
        scrollX: true,
        scrollCollapse: true,
        dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6">>rtip',
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        searching: true,
        ordering: false,
        language: {
          lengthMenu: "Show _MENU_ records",
          paginate: {
            previous: "<i class='mdi mdi-chevron-left'>",
            next: "<i class='mdi mdi-chevron-right'>"
          }
        },
        ajax: {
          url: '/v3/ColdStore/Ajax/ajax-dashboard-insights.php',
          type: 'GET',
          data: {
            action: 'IBTRPENDING',
          }
        },
        columns: [
          { data: 'IBTOrderNumber' },
          { data: 'OrderDate' },
          { data: 'FromPlantID' },
          { data: 'RequestStatus' }
        ],
        pageLength: 10,
        drawCallback: function () {
          $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
        },
        initComplete: function () {
          const api = this.api();
          $("#ibtTable_filter label")
            .contents()
            .filter(function () {
              return this.nodeType === 3;
            })
            .remove();

          const searchInput = $("#ibtTable_filter input[type='search']")
            .attr("placeholder", "Search records...")
            .addClass("dt-search-input");
            searchInput.wrap('<div class="dt-search-wrapper"></div>');
            searchInput.before('<span class="ri-search-line"></span>');

          setTimeout(function () {
            api.columns.adjust();
          }, 0);
        }
    });
}

fetchIBTRPENDINGList();

async function fetchAndRenderPackedVsBalance() {
  try {
    const yearFilter = ($('#Year').length && $('#Year').val()) ? $('#Year').val() : '';
    const monthFilter = ($('#Month').length && $('#Month').val()) ? $('#Month').val() : '';
    const fromDateFilter = ($('#FromDate').length && $('#FromDate').val()) ? $('#FromDate').val() : '';
    const toDateFilter = ($('#ToDate').length && $('#ToDate').val()) ? $('#ToDate').val() : '';
    const packedQuery = new URLSearchParams({ action: 'packed_vs_balance' });
    packedQuery.set('UOM', SelectedUom);
    if (yearFilter) packedQuery.set('Year', yearFilter);
    if (monthFilter) packedQuery.set('Month', monthFilter);
    if (fromDateFilter) packedQuery.set('FromDate', fromDateFilter);
    if (toDateFilter) packedQuery.set('ToDate', toDateFilter);
    if (SelectedPID) packedQuery.set('PID', SelectedPID);
    if (Species) packedQuery.set('Species', Species);

    const response = await fetch(
      '/v3/ColdStore/Ajax/ajax-dashboard-insights.php?' + packedQuery.toString()
    );
    const dataFromServer = await response.json();

    const labels  = dataFromServer.map(item => item.PoNo);
    const packed  = dataFromServer.map(item => item.PackedKgs);
    const balance = dataFromServer.map(item => item.BalanceKgs);

    const ctx = document
      .getElementById('packedBalanceChart')
      .getContext('2d');

    // Destroy old instance (important)
    if (window.packedBalanceChartInstance) {
      window.packedBalanceChartInstance.destroy();
    }

    window.packedBalanceChartInstance = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Packed ' + SelectedUomLabel,
            data: packed,
            backgroundColor: 'rgba(40,167,69,0.7)',
            borderRadius: 4
          },
          {
            label: 'Balance ' + SelectedUomLabel,
            data: balance,
            backgroundColor: 'rgba(23,162,184,0.7)',
            borderRadius: 4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false
        },
        plugins: {
          legend: { position: 'top' },
          tooltip: {
            callbacks: {
              label: ctx =>
                `${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString()} ${SelectedUomLabel}`,
              afterBody: items => {
                if (!items || !items.length) return [];
                const idx = items[0].dataIndex;
                const row = dataFromServer[idx] || {};
                const dummy = Number(row.DummyKgs || 0).toLocaleString();
                const master = Number(row.MasterKgs || 0).toLocaleString();
                const dummyBal = Number(row.DummyBalanceKgs || 0).toLocaleString();
                const masterBal = Number(row.MasterBalanceKgs || 0).toLocaleString();
                return [
                  `Dummy Packed ${SelectedUomLabel}: ${dummy}`,
                  `Master Packed ${SelectedUomLabel}: ${master}`,
                  `Dummy Balance ${SelectedUomLabel}: ${dummyBal}`,
                  `Master Balance ${SelectedUomLabel}: ${masterBal}`
                ];
              }
            }
          }
        },
        scales: {
          x: {
            ticks: {
              maxRotation: 60,
              minRotation: 45
            }
          },
          y: {
            beginAtZero: true,
            title: {
              display: true,
              text: SelectedUomLabel
            }
          }
        }
      }
    });

  } catch (error) {
    console.error('Failed to fetch or render Packed vs Balance chart:', error);
  }
}
fetchAndRenderPackedVsBalance();

async function fetchAndRenderTopcardscount() {
  try {
    const response = await fetch('/v3/ColdStore/Ajax/ajax-dashboard-insights.php?action=store_counts&UOM=' + encodeURIComponent(SelectedUom) + '&PID='+SelectedPID+ '&Species='+Species);  // Your API endpoint
    const dataFromServer = await response.json();

   //console.log(dataFromServer);

  } catch (error) {
    console.error('Failed to fetch or render chart:', error);
  }
}

// Call the function when page loads or after some event
//fetchAndRenderTopcardscount();

/* ===== Order Status Detail: Drill-down popup from PIE chart ===== */
function openOrderStatusDetail(plantId, stationName,statusFilter) {

    var uom = SelectedUom || 'MCS';

    $('#orderStatusModalTitle').text('Order Status - ' + stationName);
    $('#filterPoNo, #filterPackingType, #filterConsignee').val('');
    $('#filterOrderStatus').val('');
    $('#orderStatusLoading').show();
    $('#orderStatusEmpty').hide();
    $('#orderStatusTableWrapper').hide();
    $('#orderStatusSummary').hide();
    $('#orderStatusFilterSection').hide();

    $('#filterOrderStatus').val(statusFilter);

    if ($.fn.DataTable.isDataTable('#orderStatusDetailTable')) {
        $('#orderStatusDetailTable').DataTable().destroy();
        $('#orderStatusDetailTable tbody').empty();
    }

    $('#orderStatusModal').modal('show');

    $('#orderStatusDetailTable').DataTable({
        processing: false,
        serverSide: true,
        scrollX: true,
        scrollY: 600,
        searching: true,
        ordering: false,
        lengthChange: false,
        paging:false,
        dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"l f LB><"col-md-6">>rtip',
        language: {
            lengthMenu: "Show _MENU_ records",
            emptyTable: "No records found. Try changing filters or search criteria.",
            paginate: {
                previous: "<i class='mdi mdi-chevron-left'>",
                next: "<i class='mdi mdi-chevron-right'>"
            }
        },
        drawCallback: function () {
            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
        },
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Download Excel',
                className: 'btn btn-success',
                filename: 'Order_Status',
                exportOptions: {
                    modifier: {
                        page: 'all'
                    }
                },
                action: function (e, dt, button, config) {
                    $.ajax({
                        url: '/v3/ColdStore/Ajax/ajax-dashboard-insights.php',
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            action: 'order_status_detail',
                            PlantID: plantId,
                            UOM: uom,
                            Species: Species,
                            draw: 1,
                            start: 0,
                            length: -1
                        },
                        success: function (json) {
                            var allData = json.data || [];
                            if (allData.length === 0) {
                                alert('No data available to export for the selected filters.');
                                return;
                            }
                            function csvQuote(val) {
                                return '"' + String(val === null || val === undefined ? '' : val).replace(/"/g, '""') + '"';
                            }
                            var csvRows = [];
                            var headers = ['PO No', 'Packing Type', 'Consignee Name', 'Ordered Kgs', 'Ordered Mcs', 'Finished Kgs', 'Finished Mcs', 'Balance Kgs', 'Balance Mcs', 'Status'];
                            csvRows.push(headers.map(csvQuote).join(','));
                            allData.forEach(function (row) {
                                csvRows.push([
                                    csvQuote(row.PoNo),
                                    csvQuote(row.PackingType),
                                    csvQuote(row.ConsigneeName),
                                    csvQuote(row.OrderedKgs),
                                    csvQuote(row.OrderedMcs),
                                    csvQuote(row.FinishedKgs),
                                    csvQuote(row.FinishedMcs),
                                    csvQuote(row.BalanceKgs),
                                    csvQuote(row.BalanceMcs),
                                    csvQuote(row.OrderStatus)
                                ].join(','));
                            });
                            var csvContent = csvRows.join('\n');
                            var blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
                            var link = document.createElement('a');
                            link.href = URL.createObjectURL(blob);
                            link.download = 'Order_Status.csv';
                            link.click();
                            URL.revokeObjectURL(link.href);
                        },
                        error: function () {
                            alert('Failed to export Order Status data. Please try again.');
                        }
                    });
                }
            }
        ],
        ajax: {
            url: '/v3/ColdStore/Ajax/ajax-dashboard-insights.php',
            type: 'GET',
            data: function (d) {
                d.action = 'order_status_detail';
                d.PlantID = plantId;
                d.PID = plantId;
                d.UOM = uom;
                d.Species = Species;
                d.statusFilter = $('#filterOrderStatus').val() || '';
                d.columns[0].search.value = $('#filterPoNo').val() || '';
                d.columns[1].search.value = $('#filterPackingType').val() || '';
                d.columns[2].search.value = $('#filterConsignee').val() || '';

                //d.statusFilter = statusFilter;
            },
            beforeSend: function () {
                $('#orderStatusLoading').show();
                $('#orderStatusEmpty').hide();
                $('#orderStatusTableWrapper').hide();
            },
            dataSrc: function (json) {
                $('#orderStatusLoading').hide();
                $('#orderStatusEmpty').hide();
                $('#orderStatusTableWrapper').show();
                $('#orderStatusSummary').hide();
                $('#orderStatusFilterSection').hide();

                /* Update summary display */
                if (json.summary) {
                    var s = json.summary;
                    $('#orderStatusTotalCount').text(Number(s.TotalOrders || 0).toLocaleString('en-IN'));
                    $('#orderStatusCompletedCount').text(Number(s.CompletedOrders || 0).toLocaleString('en-IN'));
                    $('#orderStatusPendingCount').text(Number(s.PendingOrders || 0).toLocaleString('en-IN'));
                    $('#orderStatusPartialCount').text(Number(s.PartialOrders || 0).toLocaleString('en-IN'));

                    $('#orderStatusCompletedPct').text(s.CompletedPct + '%');
                    $('#orderStatusPendingPct').text(s.PendingPct + '%');
                    $('#orderStatusPartialPct').text(s.PartialPct + '%');

                    /* Animate progress bars */
                    $('#orderStatusCompletedBar').css('width', s.CompletedPct + '%');
                    $('#orderStatusPendingBar').css('width', s.PendingPct + '%');
                    $('#orderStatusPartialBar').css('width', s.PartialPct + '%');
                }

                return json.data;
            },
            error: function () {
                $('#orderStatusLoading').hide();
                $('#orderStatusEmpty').text('Error loading data.').show();
                $('#orderStatusTableWrapper').hide();
            }
        },
        columns: [
    {
        data: 'PoNo',
        defaultContent: ''
    },
    {
        data: 'PackingType',
        defaultContent: ''
    },
    {
        data: 'ConsigneeName',
        defaultContent: ''
    },
    {
        data: 'OrderedKgs',
        defaultContent: 0,
        render: function (data) {
            return Number(data || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    },
    {
        data: 'OrderedMcs',
        defaultContent: 0,
        render: function (data) {
            return Number(data || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    },
    {
        data: 'FinishedKgs',
        defaultContent: 0,
        render: function (data) {
            return Number(data || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    },
    {
        data: 'FinishedMcs',
        defaultContent: 0,
        render: function (data) {
            return Number(data || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    },
    {
        data: 'BalanceKgs',
        defaultContent: 0,
        render: function (data) {
            return Number(data || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    },
    {
        data: 'BalanceMcs',
        defaultContent: 0,
        render: function (data) {
            return Number(data || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    },
    {
        data: 'OrderStatus',
        defaultContent: '',
        render: function (data, type, row) {

            var status = data || '';
            var badgeClass = 'secondary';

            if (status === 'Completed') {
                badgeClass = 'success';
            }
            else if (status === 'Pending') {
                badgeClass = 'danger';
            }
            else if (status === 'Partial') {
                badgeClass = 'warning';
            }

            return '<span class="badge btn-' + badgeClass + '">' +
                   status +
                   '</span>';
        }
    }
]
,
        initComplete: function () {
            var api = this.api();
            $("#orderStatusDetailTable_filter label")
                .contents()
                .filter(function () { return this.nodeType === 3; })
                .remove();
            $("#orderStatusDetailTable_filter input[type='search']")
                .attr("placeholder", "Search records...")
                .addClass("dt-search-input");
            var searchInput = $("#orderStatusDetailTable_filter input[type='search']");
            if (!searchInput.parent().hasClass("dt-search-wrapper")) {
                searchInput.wrap('<div class="dt-search-wrapper"></div>');
                searchInput.before('<span class="ri-search-line"></span>');
            }
            setTimeout(function () { api.columns.adjust(); }, 0);
        }
    });

    $.ajax({
    url: '/v3/ColdStore/Ajax/ajax-dashboard-insights.php',
    type: 'GET',
    dataType: 'json',
    data: {
        action: 'order_status_filters',
        PlantID: plantId,
        Species: Species
    },
    success: function(filters) {

        var $po = $('#filterPoNo');
        $po.find('option:not(:first)').remove();
        $.each(filters.poNo || [], function(i, v) {
            $po.append(
                '<option value="' + $('<span>').text(v).html() + '">' +
                $('<span>').text(v).html() +
                '</option>'
            );
        });

        var $pt = $('#filterPackingType');
        $pt.find('option:not(:first)').remove();
        $.each(filters.packingType || [], function(i, v) {
            $pt.append(
                '<option value="' + $('<span>').text(v).html() + '">' +
                $('<span>').text(v).html() +
                '</option>'
            );
        });

        var $cn = $('#filterConsignee');
        $cn.find('option:not(:first)').remove();
        $.each(filters.consigneeName || [], function(i, v) {
            if (v) {
                $cn.append(
                    '<option value="' + $('<span>').text(v).html() + '">' +
                    $('<span>').text(v).html() +
                    '</option>'
                );
            }
        });
    }
});

    /* Bind change events for all filters including status filter */
    $('#orderStatusFilterRow select, #filterOrderStatus').off('change').on('change', function () {
        $('#orderStatusDetailTable').DataTable().ajax.reload();
    });

    $('#orderStatusModal').off('shown.bs.modal.orderStatusTable').on('shown.bs.modal.orderStatusTable', function () {
        if ($.fn.DataTable.isDataTable('#orderStatusDetailTable')) {
            $('#orderStatusDetailTable').DataTable().columns.adjust();
        }
    });
}

      </script>
   </body>
</html>

