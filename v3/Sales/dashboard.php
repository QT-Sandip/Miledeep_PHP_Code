<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php";

$AccessType = $_SESSION["AccessType"];
$ClientID = !empty($_SESSION["ClientID"]) ? $_SESSION["ClientID"] : '';
$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$FreightRatesGraph = $ClientConfigData->FreightRatesGraph;
$EnableBankFilter = $ClientConfigData->EnableBankFilter;
$FreezeCol = isset($ClientID) && $ClientID === 'SMEV3' ? 1 : 0;// pending payments popup freeze columns

$PaymentStatusFilter = $ClientID == 'SMEV3' ? true : false;

function checkEmpty($Value)
{
    if ($Value == '') {
        $Value = '--';
    }

    return $Value;
}

if ($_GET['chbkExp'] == '') {
    $chbkExp = 'true';
} else {
    $chbkExp = $_GET['chbkExp'];
}

if ($_GET['chbkShip'] == '') {
    $chbkShip = 'true';
} else {
    $chbkShip = $_GET['chbkShip'];
}

//Start Year From Config
//End Year If Current Year+1

$Year = $_GET['Year'];
$CurrentMonth = date('m');

if ($Year == '') {
    $Currentyear = date('Y');
} else {
    $Currentyear = $Year;
}

$Startyear = $_SESSION["StartYear"]; //uma 10-04-2024
$endYear = $Currentyear + 1; //uma 10-04-2024

if ($CurrentMonth > 3 && $Year == '') {
    // $FinClsYear = $Currentyear;
    $FinClsYear = $Currentyear + 1; //uma
} else if ($Year != '') {
    $FinClsYear = $Year;
} else {
    // $FinClsYear = $Currentyear - 1;
    $FinClsYear = $Currentyear; //uma
}

if ($CurrentMonth > 3) {
    $RunningFinStartYear = date('Y');
} else {
    $RunningFinStartYear = (date('Y') - 1);
}

$DateStart = $FinClsYear . '-04-01';
$DateEnd = ($FinClsYear + 1) . '-03-31';

$PastYearStart = ($FinClsYear - 1) . '-04-01';
$PastYearEnd = ($FinClsYear) . '-03-31';
$PastNextYear = $FinClsYear . "-03-31";

$SqlConsignee = "SELECT * FROM `ScrmConsigneeDetails`";
$StmtConsigneePendingPayments = $StmtConsignee = $iconn->query($SqlConsignee);

$SqlCountries = "SELECT * FROM `ScrmCountriesList`";
$StmtCountries = $iconn->query($SqlCountries);

$DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
$PastDateFilter = "BETWEEN '$PastYearStart' and '$PastYearEnd'";

$CurrentFincialYear = $DateStart;

// $SqlBuyerData = "SELECT ConID,PoID, MAX(PoDate)as LastPoDate, ConsigneeName, count(*) as TotalPo,
// (SELECT sum(Mcs*TotalSlabs*SlabWeight*Mfactor) from ScrmPoGradeData as PGD
//  LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID WHERE PGD.InvId=SPD.PoID ) as Kgs,
// (SELECT sum(Mcs*TotalSlabs*SlabWeight*Price) from ScrmPoGradeData as PGD
//  LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID WHERE PGD.InvId=SPD.PoID ) as Price
// FROM `ScrmPoData` as SPD
// LEFT JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
//  WHERE SPD.PoDate  $DateFilter GROUP by ConID order by TotalPo Desc";
// $BuyerDataExec = $iconn->query($SqlBuyerData);



$SqlBuyerData = "
SELECT 
    SCD.ConID,
    SPD.PoID,
    MAX(SPD.PoDate) AS LastPoDate,
    SCD.ConsigneeName,
    COUNT(*) AS TotalPo,

    COALESCE(ISF.FDAContainerStatus, '--') AS FDAContainerStatus,

    (SELECT SUM(Mcs*TotalSlabs*SlabWeight*Mfactor)
        FROM ScrmPoGradeData PGD
        LEFT JOIN AdminPackingStyles APS 
            ON APS.PCKID = PGD.PackingStyleID
        WHERE PGD.InvId = SPD.PoID
    ) AS Kgs,

    (SELECT SUM(Mcs*TotalSlabs*SlabWeight*Price)
        FROM ScrmPoGradeData PGD
        LEFT JOIN AdminPackingStyles APS 
            ON APS.PCKID = PGD.PackingStyleID
        WHERE PGD.InvId = SPD.PoID
    ) AS Price

FROM ScrmPoData SPD
LEFT JOIN ScrmConsigneeDetails SCD 
    ON SPD.ConsigneeID = SCD.ConID
LEFT JOIN ScrmCustomInvoices SCI 
    ON SCI.PiID = SPD.PoID
LEFT JOIN ScrmISFDetails ISF 
    ON ISF.PIID = SCI.PiID

WHERE SPD.PoDate $DateFilter
GROUP BY SCD.ConID
ORDER BY TotalPo DESC
";
$BuyerDataExec = $iconn->query($SqlBuyerData);

$DelayedShimpentsSql = "SELECT PoNo,PoDate,ShipByDate,ShippedOn,
DATEDIFF(SCI.TransportedDate,SPD.ShipByDate) as DelayedDays from ScrmShipmentDetailsBreif as SCI
LEFT JOIN ScrmPoData as SPD on SCI.PiID=SPD.PoID
WHERE  DATE_SUB(SCI.ShippedOn, INTERVAL 10 DAY) > SPD.ShipByDate  and ShippedOn!='' ORDER by ShipByDate ASC";
$DelayedShimpentsExec = $iconn->query($DelayedShimpentsSql);
$TotalDelayedShipments = mysqli_num_rows($DelayedShimpentsExec);

$SqlExporter = "SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1 ";
$StmtExporter = $iconn->query($SqlExporter);

$SqlConsignee = "SELECT * FROM `ScrmConsigneeDetails`";
$StmtConsignee = $iconn->query($SqlConsignee);

$SqlCountries = "SELECT * FROM `ScrmCountriesList` as SCL LEFT join ScrmPoData as SPD On SCL.id=SPD.DestinationCountry WHERE SPD.PoID != '' GROUP by SPD.DestinationCountry order by name asc";
$StmtCountries = $iconn->query($SqlCountries);

$SqlBrand = "SELECT * FROM `ScrmBrandsMaster`WHERE Status=1;";
$StmtBrand = $iconn->query($SqlBrand);

if ($FreightRatesGraph) {
    /*for freight rates graph*/
    $SqlLinerMaster = "SELECT * FROM `ScrmLinerMaster`";
    $StmtLinerMaster = $iconn->query($SqlLinerMaster);

    $portIdsSql = "WITH RECURSIVE split_ports AS (
                        SELECT
                            id,
                            TRIM(SUBSTRING_INDEX(DestinationPort, ',', 1)) AS `Port`,
                            SUBSTRING(DestinationPort, LENGTH(SUBSTRING_INDEX(DestinationPort, ',', 1)) + 2) AS remaining
                        FROM ScrmFreightRates

                        UNION ALL

                        SELECT
                            id,
                            TRIM(SUBSTRING_INDEX(remaining, ',', 1)),
                            SUBSTRING(remaining, LENGTH(SUBSTRING_INDEX(remaining, ',', 1)) + 2)
                        FROM split_ports
                        WHERE remaining <> ''
                        )
                    SELECT  `Port`
                    FROM split_ports GROUP BY `Port`";
    $SqlScrmPortCities = "SELECT * FROM `ScrmPortCities`  WHERE PortId IN($portIdsSql)";
    $StmtPortCities = $iconn->query($SqlScrmPortCities);

    $topLinerSql = "SELECT ShippingLineID,COUNT(ShippingLineID) CNT FROM ScrmISFDetails GROUP BY ShippingLineID ORDER BY  COUNT(ShippingLineID) DESC LIMIT 5";
    $StmtTopLiner = $iconn->query($topLinerSql);
    //while ($RowPortCities = $StmtTopLiner->fetch_array()) {
    $LinerRes = $StmtTopLiner->fetch_all(MYSQLI_ASSOC);
    $linerArr = [];
    foreach ($LinerRes as $liner) {
        $linerArr[] = $liner['ShippingLineID'];
    }

    $topPortSql = "SELECT
                        SPC.PortId,
                        COALESCE(SPC.Name, 'Na') AS PortName,
                        COUNT(*) AS Total    
                    FROM
                    ScrmPoData SPD
                    LEFT JOIN ScrmPortCities SPC ON SPD.PortOfDischarge = SPC.PortId
                    LEFT JOIN ScrmCustomInvoices SCI ON SCI.PiID = SPD.PoID
                    WHERE 
                    (SCI.StockOutDate != '' AND SCI.StockOutDate IS NOT NULL) AND  SCI.CInvDate  $PastDateFilter  AND SPD.ExporterID='1' AND SPD.Status NOT IN (4)
                    GROUP BY SPC.PortId
                    ORDER BY Total DESC LIMIT 1";
    $StmtTopPort = $iconn->query($topPortSql);
    $PortRes = $StmtTopPort->fetch_all(MYSQLI_ASSOC);
    $PortArr = [];
    foreach ($PortRes as $Port) {
        $PortArr[] = $Port['PortId'];
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <?php include $IncludesPath . "title-meta.php"; ?>
    <style>
        .dataTables_wrapper .row {
            display: flex;
            justify-content: space-between;
            align-items: normal !important;
        }

        .chart-skeleton-loader {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10;
            padding: 20px;
        }

        .skeleton-line {
            width: 80%;
            height: 12px;
            background: linear-gradient(90deg, #eee, #ddd, #eee);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            margin: 6px 0;
            border-radius: 6px;
        }

        .skeleton-line.short {
            width: 40%;
        }

        .skeleton-text {
            margin-top: 20px;
            font-size: 14px;
            color: #999;
            font-style: italic;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        label {
            font-size: 15px;
            color: black;
        }

        th {
            font-size: 12px;
        }

        .table tr:hover {
            background-color: #343a40 !important;
            color: #fff !important;
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

        .subTable {
            width: 100%;
            height: 100%
        }

        .subTable tr td {
            padding-left: 3px !important;
            padding-right: 3px !important;
        }

        .table thead th {
            white-space: break-spaces !important;
        }
    </style>

    <!-- jquery.vectormap css -->
    <link href="<?php echo $BaseDist ?>assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css"
        rel="stylesheet" type="text/css" />

    <!-- DataTables -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />

    <?php include $IncludesPath . "head-css.php"; ?>

    <style>
        .pending-contract {
            border: none;
            background: none;
            color: #343a40 !important;
            font-size: 1.35rem;
            font-family: Inter, sans-serif;
            font-weight: 500;
        }

        .pending-contract:hover {
            color: #5664d2 !important;
            text-decoration: underline;
        }

        @media (min-width: 992px) {

            .modal-lg,
            .modal-xl {
                max-width: 1230px;
                max-height: 210px;
            }
        }

        /* table#key-datatable {
  border-collapse: collapse;
  width: 100%;
}

#key-datatable thead {
  position: sticky;
  top: 0;
  z-index: 999;
}
div#key-datatable_wrapper  .row:nth-child(2){
    overflow: scroll;
    height: 380px;
  } */

        /* #key-datatable tbody {
  overflow: auto;
} */

        .modal .modal-body div#key-datatable_wrapper .row:nth-child(2) {
            height: 70vh;
        }
    </style>
</head>

<body data-sidebar="dark">
    <?php include "PopUpTable.php"; ?>
    <?php include $IncludesPath . "PreLoader.php"; ?>
    <!-- class="sidebar-enable vertical-collpsed" Begin page -->
    <div id="layout-wrapper">
        <?php
        include $IncludesPath . "topbar.php";
        include $IncludesPath . "sidebar.php";
        ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php //include ($IncludesPath."page-title.php");
                    ?>





                    <div class="row">
                        <!-- Global Search  -->
                        <?php include $_SERVER['DOCUMENT_ROOT'] . "/v3/Sales/Includes/GlobalSearch.php"; ?>
                        <!-- Global Search  -->

                        <div class="col-xl-12">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="media">
                                                <div class="media-body overflow-hidden">
                                                    <p class="text-truncate font-size-14 mb-2">Pending Contracts</p>
                                                    <button class="mb-0 text-primary pending-contract" id="TileID1">
                                                        <?php echo $OrderOnHand ?>
                                                    </button>
                                                </div>
                                                <div class="text-primary">
                                                    <i class="ri-stack-line font-size-24"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="media">
                                                <div class="media-body overflow-hidden">
                                                    <p class="text-truncate font-size-14 mb-2">On Water</p>

                                                    <button class="mb-0 text-primary pending-contract" id="TileID2">
                                                        <?php echo $OnSea ?>
                                                    </button>
                                                </div>
                                                <div class="text-primary">
                                                    <i class="ri-briefcase-4-line font-size-24"></i>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="media">
                                                <div class="media-body overflow-hidden">
                                                    <p class="text-truncate font-size-14 mb-2">Delivered</p>

                                                    <button class="mb-0 text-primary pending-contract btnDeliveredModal"
                                                        id="TileID5">
                                                        <?php echo $Delivered ?>
                                                    </button>
                                                </div>
                                                <div class="text-primary">
                                                    <i class="ri-store-2-line font-size-24"></i>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="media">
                                                <div class="media-body overflow-hidden">
                                                    <p class="text-truncate font-size-14 mb-2">Orders</p>
                                                    <!-- <h4 class="mb-0"id="TileID4" ><?php echo $Delivered ?></h4>-->
                                                    <button class="mb-0 text-primary pending-contract" id="TileID4">
                                                        <?php echo $Delivered ?>
                                                    </button>
                                                </div>
                                                <div class="text-primary">
                                                    <i class="ri-store-2-line font-size-24"></i>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="media">
                                                <div class="media-body overflow-hidden">
                                                    <p class="text-truncate font-size-14 mb-2">Pending Payments</p>
                                                    <!-- <h4 class="mb-0" id="TileID3"><?php echo $Delivered ?></h4> -->
                                                    <button class="mb-0 text-primary pending-contract" id="TileID3">
                                                        <?php echo $Delivered ?>
                                                    </button>
                                                </div>
                                                <div class="text-primary">
                                                    <i class="ri-store-2-line font-size-24"></i>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="media">
                                                <div class="media-body overflow-hidden">
                                                    <p class="text-truncate font-size-14 mb-2">Payments Received</p>
                                                    <button
                                                        class="mb-0 text-primary pending-contract btnPaymentsReceived"
                                                        id="TileID6">
                                                        <?php echo $Delivered ?>
                                                    </button>
                                                </div>
                                                <div class="text-primary">
                                                    <i class="ri-store-2-line font-size-24"></i>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-8">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">Invoice Vs Shipments</h4>
                                            <div>
                                                <div id="line-column-chart" class="apex-charts" dir="ltr"></div>
                                            </div>
                                        </div>

                                        <div class="card-body border-top text-center">
                                            <div class="row">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">



                            <div class="card">
                                <div class="card-body">

                                    <h4 class="card-title mb-4">Plant Wise Orders
                                        <?php //echo $Fi='('. $FinClsYear . '-' .($FinClsYear+1) .')';
                                        ?>
                                    </h4>

                                    <!--   <div id="donut-chart" class="apex-charts"></div>-->
                                    <div id="plantwiseOrders" style="height: 200px; width: 100%;"></div>

                                    <div class="row">
                                        <div class="col-4">

                                        </div>

                                        <?php foreach ($AryShiftDtls as $ShiftRow) { ?>
                                            <div class="col-4">
                                                <div class="text-center mt-4">
                                                    <p class="mb-2 text-truncate"><i
                                                            class="mdi mdi-circle font-size-10 mr-1"
                                                            style="color: <?php echo $ShiftRow['Color'] ?>;">
                                                        </i>
                                                        <?php echo $ShiftRow['Name'] ?>
                                                    </p>
                                                    <h5>
                                                        <?php echo $ShiftRow['Total'] ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        <?php } ?>



                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body">

                                    <h4 class="card-title mb-4">Country Wise Details
                                        <?php //echo $Fi='('. $FinClsYear . '-' .($FinClsYear+1) .')';
                                        ?>
                                    </h4>

                                    <div id="countryWiseDetails" style="height: 350px; width: 100%;"></div>

                                    <div class="row">
                                        <div class="col-4">
                                        </div>
                                        <?php foreach ($AryShiftDtls as $ShiftRow) { ?>
                                            <div class="col-4">
                                                <div class="text-center mt-4">
                                                    <p class="mb-2 text-truncate"><i
                                                            class="mdi mdi-circle font-size-10 mr-1"
                                                            style="color: <?php echo $ShiftRow['Color'] ?>;">
                                                        </i>
                                                        <?php echo $ShiftRow['Name'] ?>
                                                    </p>
                                                    <h5>
                                                        <?php echo $ShiftRow['Total'] ?>
                                                    </h5>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body">

                                    <h4 class="card-title mb-4">Shipped Containers (Port Wise)</h4>
                                    <div id="portChart" style="height: 350px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">To be shipped Qty vs Shipped Qty</h4>
                                    <div>
                                        <div id="shipment-chart" class="apex-charts" dir="ltr">
                                            Loading please wait ...
                                            <div id="chart-loader" class="chart-loader">
                                                <div class="chart-skeleton-loader" id="chartLoader">
                                                    <div class="skeleton-line short"></div>
                                                    <div class="skeleton-line"></div>
                                                    <div class="skeleton-line"></div>
                                                    <div class="skeleton-line"></div>
                                                    <div class="skeleton-line"></div>
                                                    <div class="skeleton-text">Loading chart data...</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body border-top text-center">
                                    <div class="row">
                                        <div class="col-sm-3"></div>
                                        <div class="col-sm-3">
                                            <div class="mt-4 mt-sm-0">
                                                <p class="mb-2 text-muted text-truncate">
                                                    <!--<i class="mdi mdi-circle text-primary font-size-10 mr-1"></i>-->
                                                    <input type="checkbox" id="chbkExp">

                                                    To be shipped Qty
                                                    -
                                                    <?php echo
                                                        $TotalShippedKgs ?> Tons
                                                </p>
                                                <div class="d-inline-flex">
                                                    <h5 class="mb-0 mr-2"></h5>
                                                    <!--<div class="text-success">
                                                            <i class="mdi mdi-menu-up font-size-14"> </i>2.1 %
                                                        </div> -->
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-sm-3">
                                            <div class="mt-4 mt-sm-0">
                                                <p class="mb-2 text-muted text-truncate">
                                                    <!--<i class="mdi mdi-circle text-primary font-size-10 mr-1"></i>-->
                                                    <input type="checkbox" id="chbkShip">
                                                    Shipped Qty-
                                                    <?php echo $TotalExportKgs ?> Tons
                                                </p>
                                                <div class="d-inline-flex">
                                                    <h5 class="mb-0 mr-2"></h5>
                                                    <!--<div class="text-success">
                                                            <i class="mdi mdi-menu-up font-size-14"> </i>2.1 %
                                                        </div> -->
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- frieght rates code start -->
                    <?php if ($FreightRatesGraph) { ?>
                        <style>
                            .select2-container .select2-selection--multiple .select2-selection__choice,
                            .select2-selection__choice__remove {
                                background-color: #1cbb8c !important;
                                border: 1px solid #1cbb8c !important;
                                color: #FFFFFF !important;
                            }
                        </style>
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xl-2 col-sm-2">
                                                <h4 class="card-title mb-4">Freight Rates(Sample data)</h4>
                                            </div>

                                            <div class="form-group col-lg-2 col-sm-2">
                                                <label for="liner_id">Liner</label>
                                                <select required class="form-control select2" multiple name="liner_id[]"
                                                    id="liner_id">
                                                    <option value=""> All </Option>
                                                    <?php while ($RowLinerMaster = $StmtLinerMaster->fetch_array()) { ?>
                                                        <option <?php if ((!empty($linerArr) && in_array($RowLinerMaster['LinerID'], $linerArr)) || ($RowLinerMaster['LinerID'] == 6)) { ?> selected="selected" <?php } ?>
                                                            value="<?php echo $RowLinerMaster['LinerID']; ?>">
                                                            <?php echo $RowLinerMaster['LinerName']; ?>
                                                        </option>
                                                    <?php } ?>

                                                </select>
                                            </div>

                                            <div class="form-group col-lg-2 col-sm-2">
                                                <label for="port_id">Destination Port</label>
                                                <select required multiple class="form-control select2" name="port_id[]"
                                                    id="port_id">
                                                    <option value=""> All </Option>
                                                    <?php while ($RowPortCities = $StmtPortCities->fetch_array()) { ?>
                                                        <option <?php //if ((!empty($PortArr) && in_array($RowPortCities['PortId'], $PortArr)) || ($RowPortCities['PortId'] == 461)) { 
                                                                if ((!empty($PortArr) && in_array($RowPortCities['PortId'], $PortArr))) {
                                                                    ?>
                                                                selected="selected" <?php } ?>
                                                            value="<?php echo $RowPortCities['PortId']; ?>">
                                                            <?php echo $RowPortCities['Name']; ?>
                                                        </option>
                                                    <?php } ?>

                                                </select>
                                            </div>

                                            <div class="form-group col-lg-2 col-sm-3">
                                                <label for="rmFreightFromDate">From Date:</label>
                                                <input type="date"
                                                    value="<?php echo date('Y-m-d', strtotime('-2 months')); ?>"
                                                    class="form-control" name="rmFreightFromDate" id="rmFreightFromDate">
                                            </div>
                                            <div class="form-group col-lg-2 col-sm-3">
                                                <label for="rmFreightToDate">To Date:</label>
                                                <input type="date" value="<?php echo date('Y-m-d'); ?>" class="form-control"
                                                    name="rmFreightToDate" id="rmFreightToDate">
                                            </div>

                                            <div class="form-group col-lg-2 col-sm-3">
                                                <label style="display: block;">&nbsp;</label>
                                                <button id="rm-freight-search" type="button"
                                                    class="btn btn-primary mb-2 waves-effect waves-light  text-left">
                                                    Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                                </button>
                                                <button id="rm-freight-download" type="button"
                                                    class="btn btn-warning mb-2 waves-effect waves-light  text-left">
                                                    Download <i class="ri-download-line align-middle ml-2"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div id="freight-rates-grpah" style="height: 200px; width: 100%; "> </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php } ?>

                    <!-- freight rates code end -->
                </div>
            </div>
            <!-- end row -->

            <div class="row" style="display: none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Country Wise Details</h4>


                            <div id="countryChart" class="apex-charts" dir="ltr"></div>
                        </div>
                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div> <!-- end col-->
        </div>
    </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <?php
    include $IncludesPath . "footer.php";

    // echo file_get_contents($BaseDist."partials/footer.php");
    ?>
    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->


    <div id="myModalFullScreen" class="modal fade" role="dialog" tabindex="-1">
        <div class="modal-dialog  modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header" style="padding: 5px 15px">
                    <h4 id="showcstitle"></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="drpPendingContractsType">Select Type</label>
                            <select class="form-control select2" id="drpPendingContractsType">
                                <option value="">Select one</option>
                                <option value="RDP">Realization Pending</option>
                                <option value="NGCP">Negotiation Pending</option>
                                <option value="COLP">Collections Pending</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="drpFdaRelease">FDA Release</label>
                            <select class="form-control select2" id="drpFdaRelease">
                                <option value="">Select one</option>
                                <option value="YES">Yes</option>
                                <option value="NO">No</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-sm-6">
                            <label>Buyer/Consignee</label>
                            <select class="form-control select2" name="drpBuyer" id="drpBuyer">
                                <option value=" ">All</option>
                                <?php while ($RowConsignee = $StmtConsigneePendingPayments->fetch_assoc()) { ?>
                                    <option Value="<?php echo $RowConsignee['ConID']; ?>">
                                        <?php echo $RowConsignee['ConsigneeName']; ?>
                                    </OPTION>
                                <?php } ?>
                                <select>
                        </div>
                        <?php
                        if (!empty($EnableBankFilter)) {
                            $BankSql = "SELECT * FROM ScrmBankDetailsMaster WHERE BankStatus=1";
                            $StmtBank = $StmtBank = $iconn->query($BankSql); ?>
                            <div class="col-lg-2 col-sm-6">
                                <label>Bank Details</label>
                                <select class="form-control select2" name="drpBank" id="drpBank">
                                    <option value=" ">All</option>
                                    <?php while ($RowBank = $StmtBank->fetch_assoc()) { ?>
                                        <option Value="<?php echo $RowBank['ID']; ?>">
                                            <?php echo $RowBank['AccntName'] . ' - ' . $RowBank['BankName'] . ' - ' . $RowBank['AccountNumber']; ?>
                                        </option>
                                    <?php } ?>
                                    <select>
                            </div>
                        <?php } ?>

                        <div class="col-md-2">
                            <label for="Fbdstatus">On Water Status</label>
                            <select class="form-control select2" name="Fbdstatus" id="Fbdstatus">
                                <option value="">ALL</option>
                                <option value="FDA RELEASE">FDA RELEASE</option>
                                <option value="FDA PENDING">FDA PENDING</option>
                                <option value="GATE OUT">GATE OUT</option>
                                <!-- <option value="ON WATER">ON WATER</option> -->
                            </select>
                        </div>

                        <?php
                        if ($PaymentStatusFilter) { ?>
                            <div class="col-md-2">
                                <label for="PaymentStatusFilter">Payment Status</label>
                                <select class="form-control select2" name="PaymentStatusFilter" id="PaymentStatusFilter">
                                    <option value="">ALL</option>
                                    <option value="ADVISED PENDING">ADVISED PENDING</option>
                                </select>
                            </div>
                        <?php } ?>

                    </div>
                    <div id="PopUpTableFullScreen" class="col-md-12 scrollPopup"> </div>
                </div>
                <!-- <div class="modal-footer">
        <button type="button" id='Update' value="" class="btn btn-info" >Update</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div> -->
            </div>
        </div>
    </div>

    <?php
    include $IncludesPath . "vendor-scripts.php";
    include $IncludesPath . "NewScripts.php";
    ?>
    <?php
    if ($FreezeCol == 1) { ?>
        <style>
            th.freezeInvNo,
            th.freezePoNo,
            th.freezeBuyerName,
            th.freezePort,
            th.freezeInvValUsd,
            td.freezeInvNo,
            td.freezePoNo,
            td.freezeBuyerName,
            td.freezePort,
            td.freezeInvValUsd {
                position: sticky !important;
                top: 0 !important;
                z-index: 4;
                background-color: #fff !important;
                /*  color: #000 !important; */
            }

            tr:hover th.freezeInvNo,
            tr:hover th.freezePoNo,
            tr:hover th.freezeBuyerName,
            tr:hover th.freezePort,
            tr:hover th.freezeInvValUsd,
            tr:hover td.freezeInvNo,
            tr:hover td.freezePoNo,
            tr:hover td.freezeBuyerName,
            tr:hover td.freezePort,
            tr:hover td.freezeInvValUsd {
                background-color: #fff !important;
                color: #000 !important;
            }


            /* th.freezesNO,
                td.freezesNO {
                    left: 0;
                } */

            th.freezeInvNo,
            td.freezeInvNo {
                left: 0px;
            }

            th.freezePoNo,
            td.freezePoNo {
                left: 140px;
            }

            th.freezeBuyerName,
            td.freezeBuyerName {
                left: 340px;
            }

            th.freezePort,
            td.freezePort {
                left: 586px;
            }

            th.freezeInvValUsd,
            td.freezeInvValUsd {
                left: 706px;
            }
        </style>
    <?php } ?>

    <link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.js"></script>

    <!-- <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script> -->
    <!-- <script src="https://uiwrapper.silverlights.in/CanvasJs/canvasjs.min.js"></script>  -->
    <script src="<?php echo $BaseDist ?>assets/js/canvasjs.min.js"></script>
    <!-- <script src="https://silverlights.in/uiwrapper/CanvasJs/canvasjs.min.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({
                closeOnSelect: true
            });
            jQuery('#drpFdaRelease').val('').parent().hide();
            jQuery('#drpPendingContractsType').val('').parent().hide();
            jQuery('#drpBuyer').val('').parent().hide();
            jQuery('#Fbdstatus').val('').parent().hide();



            jQuery('#fromDate').val('<?php echo $PastYearStart; ?>');
            jQuery('#toDate').val('<?php echo $PastYearEnd; ?>');
            getList();
            $(document).off('click', '#rm-freight-search');
            $(document).on('click', '#rm-freight-search', function (e) {
                getList();
            });

            // $(document).off('change', '#drpPendingContractsType, #drpFdaRelease, #drpBuyer');
            // $(document).on('change', '#drpPendingContractsType, #drpFdaRelease, #drpBuyer', function (e) {
            //     renderPendingContractsPopup();
            //     pendingContractsPlantWise('');
            // });

            // $(document).on("click", "#TileID3", function () {
            //      $("#drpPendingContractsType").parent().show();
            //     $("#drpFdaRelease").parent().show();
            //     $("#drpBuyer").parent().show();
            //     renderPendingContractsPopup();
            // })
            $(document).on("click", "#TileID3", function () {
                $("#drpPendingContractsType").parent().show();
                $("#drpFdaRelease").parent().show();
                $("#drpBuyer").parent().show();
                $("#PaymentStatusFilter").parent().show();

              /*   $("#drpBuyer").val("").trigger("change.select2");
                $("#drpPendingContractsType").val("").trigger("change.select2");
                $("#drpFdaRelease").val("").trigger("change.select2"); */

               /*  $(document).off('change', '#drpPendingContractsType, #drpFdaRelease');
                $(document).on('change', '#drpPendingContractsType, #drpFdaRelease', function (e) {
                    renderPendingContractsPopup();
                }) */;
                renderPendingContractsPopup();

            })





        });
        function renderPendingContractsPopup(popUp = '') {
            // debugger;
            if (popUp) {
                var consignee = $("#drpBuyer").val();
            } else {
                var consignee = $("#Buyer").val();
            }

            var year = $("#Year").val();
            var Month = $('#drpMonth').val();
            var exporter = $("#drpExporter").val();
            //var consignee = $("#drpBuyer").val();

            var country = $("#Country").val();
            var Brand = $("#Brand").val();
            var PendingContractsType = $('#drpPendingContractsType').val();
            var FdaRelease = $('#drpFdaRelease').val();
            var PaymentStatus = $('#PaymentStatusFilter').val();
            if ((year == '') && ($('#fromDate').val() == '' && $('#fromDate').val() == '')) {
                alert('Select a year or date range.');
                return;
            }
            $.ajax({
                url: 'Ajax/AjaxGetPendingPaymentsData.php',
                type: 'POST',
                // data:{'EXPORTER':EXPORTER,'YEAR':YEAR,'TYPE':'POLIST'},
                data: {
                    // {"Year":"2023","Exporter":"1","Consignee":"","Country":"","PortID":"823"}
                    // "PortID":PortID,
                    "Year": year,
                    'FromDate': $('#fromDate').val(),
                    'ToDate': $('#toDate').val(),
                    "Exporter": exporter,
                    "Consignee": consignee,
                    "Brand": Brand,
                    "Month": Month,
                    "Country": country,
                    "PendingContractsType": PendingContractsType,
                    "FdaRelease": FdaRelease,
                    'Bank': $('#drpBank').val(),
                    "PaymentStatus": PaymentStatus
                },
                success: function (data) {
                    // jQuery('#drpPendingContractsType').parent().show();
                    // jQuery('#drpFdaRelease').parent().show();
                    // jQuery('#drpBuyer').parent().show();
                    $("#PopUpTableFullScreen").html(data);

                    /* Destroy the Datatable if initialize */
                    if ($.fn.dataTable.isDataTable('#key-datatableFullscreen')) {
                        table.destroy();
                    }
                    // { dom: 'lifrtp' }
                    table = $('#key-datatableFullscreen').DataTable({
                        //processing: true,
                        scrollX: true,
                        lengthChange: false,
                        searching: true,
                        paging: false,
                        searching: true,
                        scrollX: true,
                        scrollCollapse: true,
                        scrollY: '60vh',
                        <?php
                        if ($FreezeCol == 1) { ?>                                
                                    columnDefs: [
                                { targets: 0, className: 'freezesNO' },
                                { targets: 1, className: 'freezeInvNo' },
                                { targets: 2, className: 'freezePoNo' },
                                { targets: 3, className: 'freezeBuyerName' },
                                { targets: 5, className: 'freezePort' },
                                { targets: 8, className: 'freezeInvValUsd' },
                            ],
                        <?php } ?>

                        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                        pageLength: 10,
                        searching: true,

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
                        buttons: [{
                            extend: 'excelHtml5',
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            title: 'Pending Payments',
                            footer: true,
                            exportOptions: {
                                columns: ':visible'
                            }
                        }],
                        initComplete: function () {
                            this.api().columns.adjust();
                        }
                    });
                    setTimeout(function () {
                        table.columns.adjust();
                    }, 1000);
                    /*var buttons = new $.fn.dataTable.Buttons(table, {
                        buttons: [{
                            extend: 'excelHtml5',
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            title: 'Pending Payments',
                            footer: true,
                            exportOptions: {
                                columns: ':visible'
                            }
                        }]
                    }).container().appendTo($('#key-datatableFullscreen_filter'));*/

                    /*$('#PopUpTableFullScreen #key-datatableFullscreen_wrapper > .row > .col-sm-12.col-md-6:first-child')
                        .html('<h4 class="modal-title">Pending Payments</h4>');*/
                    $("#showcstitle").html("Pending Payments");
                    $('#myModalFullScreen').modal('show');


                    $('#drpPendingContractsType').select2();
                    $('#drpBuyer').select2();

                    // $('#drpPendingContractsType').val(PendingContractsType);


                    var aWidth = [];
                    $("#key-datatable tbody tr:first-child").find("td").each(function () {
                        var td = $(this).width();
                        aWidth.push(td);
                    });
                    $(".dataTables_scrollHeadInner table thead tr th").each(function (i) {
                        $(this).width(aWidth[i]);
                    });

                    $('#myModalFullScreen').on('hidden.bs.modal', function () {
                        console.log("Modal closed!");
                        // your callback code here
                        // e.g. reset dropdown, destroy DataTable, clear content etc.
                        jQuery('#drpFdaRelease').val('').parent().hide();
                        jQuery('#drpPendingContractsType').val('').parent().hide();
                        jQuery('#drpBuyer').val('').parent().hide();
                    });
                    table.on('draw.dt', function () {
                        let totalCarton = 0;
                        let totalInvValUSD = 0;
                        let totalRemainInvValUSD = 0;
                        let tnvValINR = 0;
                        $('.Mcs').each(function () {
                            // Parse the text content as a float, ignoring any commas
                            const Mcs = parseFloat($(this).text().replace(/,/g, ''));
                            if (!isNaN(Mcs)) {
                                totalCarton += Mcs;
                            }
                        });
                        $('.TotalInvoiceValue').each(function () {
                            // Parse the text content as a float, ignoring any commas
                            const TotalInvoiceValue = parseFloat($(this).text().replace(/,/g, ''));
                            if (!isNaN(TotalInvoiceValue)) {
                                totalInvValUSD += TotalInvoiceValue;
                            }
                        });
                        $('.TotalInvoiceValueINR').each(function () {
                            // Parse the text content as a float, ignoring any commas
                            const TotalInvoiceValueINR = parseFloat($(this).text().replace(/,/g, ''));
                            if (!isNaN(TotalInvoiceValueINR)) {
                                tnvValINR += TotalInvoiceValueINR;
                            }
                        });
                        $('.RemainingInvoiceValue').each(function () {
                            // Parse the text content as a float, ignoring any commas
                            const RemainingInvoiceValue = parseFloat($(this).text().replace(/,/g, ''));
                            if (!isNaN(RemainingInvoiceValue)) {
                                totalRemainInvValUSD += RemainingInvoiceValue;
                            }
                        });

                        $('#totalCarton').text(totalCarton.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        $('#totalInvValUSD').text(totalInvValUSD.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        $('#totalInvValINR').text(tnvValINR.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        $('#totalRemainInvValUSD').text(totalRemainInvValUSD.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                    });

                    setTimeout(function () {
                        table.columns.adjust();
                    }, 100);

                }

            });

        }

        $("#projectKey").change(function () {
            alert("test");
        });

        $('#myModalFullScreen, #myModal').on('shown.bs.modal', function (e) {
            // console.log("Open");
            $("html").addClass("overflow-hidden");
            $(this).css({
                "padding": "0"
            });
        })
        $('#myModalFullScreen, #myModal').on('hidden.bs.modal', function (e) {
            // console.log("closed");
            $("html").removeClass("overflow-hidden");
        })
    </script>

    <script>
        var table;
        var chartInstances = {};

        function fnDataTable() {
            /* Destroy the Datatable if initialize */
            if ($.fn.dataTable.isDataTable('#key-datatable')) {
                table.destroy();
            }
            // { dom: 'lifrtp' }
            table = $('#key-datatable').DataTable();
            // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();

            // table = $('#key-datatable').DataTable();

            // table.columns.adjust().draw();
            // $($.fn.dataTable.tables(true)).DataTable().columns.adjust();

            // table = $('#key-datatable').DataTable({
            //     // sScrollXInner: "150%",
            //     // paging:true,
            //     // destroy: true,
            //     // fixedColumns: true,
            //     // ordering: false,
            //     scrollX:true,
            //     scrollY: '450px',
            //     fixedHeader: true,
            //     // bSort: true,
            //     "bStateSave": true,
            //     "fnStateSave": function(oSettings, oData) {
            //         localStorage.setItem('key-datatable', JSON.stringify(oData));
            //     },
            //     "fnStateLoad": function(oSettings) {
            //         return JSON.parse(localStorage.getItem('key-datatable'));
            //     }
            // });
            //  $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            // table.columns.adjust();

            // $(window).resize( function () {
            //         table.columns.adjust();
            // });


        }

        $(document).on('click', '#PoDetails', function (e) {
            e.preventDefault();

            var PoNo = $(this).data('value');
            var PoID = $(this).data('id');

            $('#Update').val(PoID);

            showPoPopUp(PoNo, PoID);
        });

        function showPoPopUp(PoNo, PoID) {

            $.ajax({
                url: 'Ajax/AjaxPopUpPoDetails.php',
                type: 'POST',
                data: {
                    'PoID': PoID
                },
                success: function (data) {
                    //  $("#Update").hide();

                    $('.modal-title').text('PO Status Details - ' + PoNo);

                    showData(data);
                    $('#myModal').modal('show');
                    $('#myModal .modal-dialog').removeClass("modal-lg").addClass("modal-fullscreen");
                }
            });

        }



        function showData(data, sort) {

            var IsSort = true;
            if (sort == false) {
                IsSort = false;
            } else {
                IsSort = true;
            }
            $("#PopUpTable").html(data);

            /* Destroy the Datatable if initialize */
            if ($.fn.dataTable.isDataTable('#myTable')) {
                table.destroy();
            }


            table = $('#key-datatable').DataTable({
                // scrollY: "380px",
                //scrollX:true,
                // sScrollXInner: "150%",
                // paging:true,
                // destroy: true,
                // fixedColumns: true,

                fixedHeader: true,
                bSort: IsSort,
                "bStateSave": true,
                "fnStateSave": function (oSettings, oData) {
                    localStorage.setItem('key-datatable', JSON.stringify(oData));
                },
                "fnStateLoad": function (oSettings) {
                    return JSON.parse(localStorage.getItem('key-datatable'));
                }
            });



        }
        $('#Year').on('change', function () {
            if ($(this).val()) {
                $('#fromDate,#toDate').val('').prop('disabled', true)
            } else {
                $('#fromDate,#toDate').prop('disabled', false);
                jQuery('#fromDate').val('<?php echo $PastYearStart; ?>');
                jQuery('#toDate').val('<?php echo $PastYearEnd; ?>');
            }
        })
        function getList() {

            // var ConID = $('#Year').val();

            var EXPORTER = $('#drpExporter').val();
            var YEAR = $('#Year').val();
            var Buyer = $('#Buyer').val();
            var Country = $('#Country').val();
            var Month = $('#drpMonth').val();
            var Brand = $('#Brand').val();
            var FreightRatesGraph = '<?php echo $FreightRatesGraph; ?>';
            $.ajax({
                url: 'Ajax/AjaxDeviDashboard.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    // 'ConID': ConID
                    'Year': YEAR,
                    'FromDate': $('#fromDate').val(),
                    'ToDate': $('#toDate').val(),
                    'ExpID': EXPORTER,
                    'ConsigneeID': Buyer,
                    'Month': Month,
                    'CountryID': Country,
                    'Brand': Brand,
                    'FreightFromDate': $('#rmFreightFromDate').val(),
                    'FreightToDate': $('#rmFreightToDate').val(),
                    'PortId': $('#port_id').val(),
                    'LinerId': $('#liner_id').val()
                },
                success: function (data) {
                    console.log(data);
                    var AryData = JSON.parse(JSON.stringify(data));
                    $('#TileID1').text(AryData.TileID1);
                    $('#TileID2').text(AryData.TileID2);
                    $('#TileID3').text(AryData.TileID3);
                    $('#TileID4').text(AryData.TileID4);
                    $('#TileID5').text(AryData.TileID5);
                    $('#TileID6').text(AryData.TileID6);
                    renderShipmentsGraph(AryData.ArySaleNos); // Invoice Vs Shipments
                    //renderExpSaleKgsGraph(AryData.ArySaleQty); // Export Qty vs To Ship Qty
                    renderPortGraph(AryData.AryPortContainerDtls); //Port - Containers
                    renderCountryWiseDetailsPie(AryData.CountryWiseDetailsPie); //Country Wise Details
                    renderPlantwiseGraph(AryData.AryPlantwiseGraphDtls); // Plantwise Orders
                    getListOnlySalesQty();
                    if (FreightRatesGraph == 1) {
                        renderFreightDataGrpah(AryData.FreightChargesData);
                    }
                }
            });
        }

        function getListOnlySalesQty() {

            // var ConID = $('#Year').val();

            var EXPORTER = $('#drpExporter').val();
            var YEAR = $('#Year').val();
            var Buyer = $('#Buyer').val();
            var Country = $('#Country').val();
            var Month = $('#drpMonth').val();
            var Brand = $('#Brand').val();

            $.ajax({
                url: 'Ajax/AjaxDeviDashboard.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    // 'ConID': ConID
                    'Year': $('#Year').val(),
                    'FromDate': $('#fromDate').val(),
                    'ToDate': $('#toDate').val(),
                    'ExpID': EXPORTER,
                    'ConsigneeID': Buyer,
                    'Month': Month,
                    'CountryID': Country,
                    'Brand': Brand,
                    'Only': 'True'
                },
                success: function (data) {
                    console.log(data);
                    var AryData = JSON.parse(JSON.stringify(data));

                    renderExpSaleKgsGraph(AryData.ArySaleQty); // Export Qty vs To Ship Qty



                }
            });
        }

        // function exportTableToExcel(tableID, filename = '') {
        //     var downloadLink;
        //     var dataType = 'data:application/vnd.ms-excel';
        //     var tableSelect = document.getElementById(tableID).cloneNode(true);

        //     // Remove elements with display:none
        //     var elements = tableSelect.querySelectorAll('*[style*="display: none"]');
        //     for (var k = 0; k < elements.length; k++) {
        //         elements[k].parentNode.removeChild(elements[k]);
        //     }
        //     // Apply CSS styles for borders
        //     tableSelect.style.border = '1px solid #eff2f7';
        //     var trs = tableSelect.getElementsByTagName('tr');
        //     for (var i = 0; i < trs.length; i++) {
        //         trs[i].style.border = '1px solid #eff2f7';
        //         var tds = trs[i].getElementsByTagName('td');
        //         for (var j = 0; j < tds.length; j++) {
        //             tds[j].style.border = '1px solid #eff2f7';
        //             var spans = tds[j].getElementsByTagName('span');
        //             if (spans.length > 0) {
        //                 for (var k = 0; k < spans.length; k++) {
        //                     // spans[k].innerHTML = '';
        //                 }
        //             }
        //         }
        //     }

        //     var tableHTML = tableSelect.outerHTML

        //     filename = filename ? filename + '.xls' : 'excel_data.xls';

        //     downloadLink = document.createElement('a');
        //     document.body.appendChild(downloadLink);

        //     if (navigator.msSaveOrOpenBlob) {
        //         var blob = new Blob(['\ufeff', tableHTML], {
        //             type: dataType
        //         });
        //         navigator.msSaveOrOpenBlob(blob, filename);
        //     } else {
        //         downloadLink.href = dataType + ', ' + encodeURIComponent(tableHTML);
        //         downloadLink.download = filename;
        //         downloadLink.click();
        //     }
        // }


        function exportTableToExcel(tableID, filename = 'excel_data') {
            try {
                const table = document.getElementById(tableID).cloneNode(true);


                // 1. Remove hidden elements:
                table.querySelectorAll('*[style*="display: none"]').forEach(el => el.remove());

                // 2. Apply border styles:
                table.querySelectorAll('tr, td').forEach(el => el.style.border = '1px solid #eff2f7');

                // 3. Prepare cell values:
                table.querySelectorAll('td').forEach(td => {
                    let text = td.textContent.replace(/^=/, '').trim();
                    td.textContent = text; // Just set the text content directly. NO SINGLE QUOTE!
                });

                // 4. Convert table to worksheet:
                const workbook = XLSX.utils.table_to_book(table, {
                    sheet: "Sheet1",
                    raw: true
                });

                // 5. Force Text Formatting:
                const worksheet = workbook.Sheets["Sheet1"];
                const range = XLSX.utils.decode_range(worksheet['!ref']);
                for (let row = range.s.r; row <= range.e.r; ++row) {
                    for (let col = range.s.c; col <= range.e.c; ++col) {
                        const cellAddress = XLSX.utils.encode_cell({
                            r: row,
                            c: col
                        });
                        if (worksheet[cellAddress] && row > 1) {
                            let value = worksheet[cellAddress].v;

                            if ($("#chkShowPrice").is(':checked') && col > 30) {
                                if (value !== undefined && value !== null && value !== '') {
                                    worksheet[cellAddress].t = 'n';
                                    worksheet[cellAddress].v = value;
                                } else {
                                    delete worksheet[cellAddress].t;
                                    delete worksheet[cellAddress].v;
                                }
                            } else if (col > 14) {
                                if (value !== undefined && value !== null && value !== '') {
                                    worksheet[cellAddress].t = 'n';
                                    worksheet[cellAddress].v = value;
                                } else {
                                    delete worksheet[cellAddress].t;
                                    delete worksheet[cellAddress].v;
                                }
                            } else {
                                worksheet[cellAddress].t = 's';
                                worksheet[cellAddress].v = value !== undefined && value !== null ? value.toString() : '';
                            }
                        }
                    }
                }


                // 6. Export the Excel file:
                XLSX.writeFile(workbook, filename + ".xlsx");

            } catch (error) {
                console.error("Error exporting to Excel:", error);
                alert("An error occurred during export. Please check the console for details.");
            }
        }



        function fnPendingOrderPlantWiseExcel(processorID, type) {

            var excel_data = $('#key-datatableFullscreen').closest("div.col-sm-12").html();
            // console.log(excel_data)
            exportTableToExcel('key-datatableFullscreen', 'PendingOrderPlantWise');
            // exportTableToExcel(excel_data, 'PendingOrderPlantWise');

        };


        // $(document).on('click', '#TileID1', function (e) {
        //     if (JSON.parse($('#TileID1').text()) != 0) {
        //           $("#drpBuyer").parent().show();
        //         // Hide the other two
        //         $("#drpPendingContractsType").parent().hide();
        //         $("#drpFdaRelease").parent().hide();
        //         pendingContractsPlantWise('');
        //     }

        // });
        $(document).on('click', '#TileID1', function (e) {
            if (JSON.parse($('#TileID1').text()) != 0) {
                $("#drpBuyer").parent().show();
                // Hide the other two
                $("#drpPendingContractsType").parent().hide();
                $("#drpFdaRelease").parent().hide();
                $("#Fbdstatus").parent().hide();

                //  $("#drpBuyer").val("").trigger("change.select2");

                /* $(document).off('change', '#drpPendingContractsType, #drpFdaRelease, #drpBuyer');
                $(document).on('change', '#drpPendingContractsType, #drpFdaRelease', function (e) {
                    var popup = 1;
                    pendingContractsPlantWise('',popup);
                }); */
                pendingContractsPlantWise('');
            }
        });


        $(document).on('click', '#TileID2', function () {
            $('#Fbdstatus').parent().show();
            $('#drpBuyer,#drpPendingContractsType,#drpFdaRelease').parent().hide();

            // $('#Fbdstatus').val('').trigger('change.select2');

            GetPortWisePoData("", "", "", "popUp");
        });


        $(document).on('change', '#Fbdstatus', function () {
            // console.log('Fbdstatus changed:', $(this).val());

            GetPortWisePoData("", "", "", "popUp");
        });





        $(document).on('click', '#TileID1', function (e) {
            $('#drpBuyer,#drpPendingContractsType, #drpFdaRelease, #FbdStatus, #drpBank').removeAttr('onchange').show().select2('destroy').val('').select2();
            $("#Fbdstatus").parent().hide();
            $("#drpPendingContractsType,#drpFdaRelease,#drpBank,#PaymentStatusFilter").parent().hide()
            $('#drpBuyer').attr('onchange', 'pendingContractsPlantWise("","popUp")');
        })
        $(document).on('click', '#TileID2', function (e) {
            $('#drpBuyer,#drpPendingContractsType, #drpFdaRelease, #Fbdstatus, #drpBank').removeAttr('onchange').show().select2('destroy').val('').select2();
            $("#drpPendingContractsType,#drpFdaRelease,#drpBank,#PaymentStatusFilter").parent().hide();
            $('#drpBuyer').attr('onchange', 'GetPortWisePoData("","", "","popUp")');
            //   $('#Fbdstatus').parent().show();

        })
        $(document).on('click', '#TileID3', function (e) {
            $('#drpBuyer,#drpPendingContractsType, #drpFdaRelease, #Fbdstatus, #drpBank').removeAttr('onchange').show().select2('destroy').val('').select2();
            $("#Fbdstatus").parent().hide();
            $("#drpBank").parent().show();
            $('#drpBuyer,#drpPendingContractsType, #drpFdaRelease, #drpBank, #PaymentStatusFilter').attr('onchange', 'renderPendingContractsPopup("popUp")');
        })
        $(document).on('click', '#TileID4', function (e) {
            $('#drpBuyer,#drpPendingContractsType, #drpFdaRelease, #Fbdstatus, #drpBank').removeAttr('onchange').hide().select2('destroy').val('').select2();
            $('#drpBuyer').attr('onchange', 'getBuyerWiseData("", "Total","popUp")');
            $('#drpBuyer').parent().show();
            $("#drpBank").parent().hide();
            $("#PaymentStatusFilter").parent().hide();
        })
        $(document).on('click', '#TileID5,#TileID6', function (e) {
            $('#drpBuyer,#drpPendingContractsType,#drpFdaRelease,#Fbdstatus, #drpBank').removeAttr('onchange');
            $("#drpPendingContractsType,#drpFdaRelease,#drpBuyer,#Fbdstatus, #drpBank, #PaymentStatusFilter").parent().hide();
        })

        $("#Buyer").change(function () {
            var Buyer = $('#Buyer').val();

            $.ajax({
                url: 'Ajax/AjaxBrandSearch.php',
                type: 'POST',
                data: {
                    'Buyer': Buyer,
                },
                success: function (data) {
                    var response = JSON.parse(data);
                    //  alert(data);
                    console.log(response);
                    $("#Brand").html(response.Brand);
                    $("#Country").html(response.Country);

                }

            });

        });
        function resetRowspan(tableId, columnIndexes) {
            const $rows = $(`${tableId} tbody tr`);
            $rows.each(function () {
                const $cells = $(this).children('td');
                columnIndexes.forEach(index => {
                    const $cell = $cells.eq(index);
                    if ($cell.length) {
                        $cell.removeAttr('rowspan').css('display', '');
                    }
                });
            });
        }
        function applyRowspan(tableId, columnIndexes) {
            const $rows = $(`${tableId} tbody tr`);
            const groupColIndex = columnIndexes[0];
            let prevGroupValue = null;
            let groupStartIndex = 0;
            let groupRowCount = 0;

            $rows.each(function (rowIndex) {
                const $cells = $(this).children('td');
                const currentGroupValue = $cells.eq(groupColIndex).text().trim();

                if (prevGroupValue === currentGroupValue) {
                    groupRowCount++;
                    columnIndexes.forEach(index => {
                        $cells.eq(index).css('display', 'none');
                    });
                } else {
                    if (groupRowCount > 1) {
                        const $groupStartRow = $rows.eq(groupStartIndex);
                        columnIndexes.forEach(index => {
                            $groupStartRow.children('td').eq(index).attr('rowspan', groupRowCount);
                        });
                    }
                    prevGroupValue = currentGroupValue;
                    groupStartIndex = rowIndex;
                    groupRowCount = 1;
                }
            });

            // Final group
            if (groupRowCount > 1) {
                const $groupStartRow = $rows.eq(groupStartIndex);
                columnIndexes.forEach(index => {
                    $groupStartRow.children('td').eq(index).attr('rowspan', groupRowCount);
                });
            }
        }



        function pendingContractsPlantWise(processor, popup = '') {
            // debugger;
            if (popup) {
                var consignee = $("#drpBuyer").val();
            } else {
                var consignee = $("#Buyer").val();
            }
            var year = $("#Year").val();
            var exporter = $("#drpExporter").val();
            var country = $("#Country").val();
            var Brand = $("#Brand").val();
            var AccessType = '<?php echo $AccessType; ?>';
            //'InvType': InvType,
            if ((year == '') && ($('#fromDate').val() == '' && $('#fromDate').val() == '')) {
                alert('Select a year or date range.');
                return;
            }
            $.ajax({
                // url: 'Ajax/AjaxDashboardPendingCtrs.php',
                url: 'Ajax/AjaxPendingOrderPlantWise.php',
                type: 'POST',
                // data:{'EXPORTER':EXPORTER,'YEAR':YEAR,'TYPE':'POLIST'},
                data: {
                    'EXPORTER': exporter,
                    'TYPE': 'POLIST',
                    'YEAR': year,
                    'FromDate': $('#fromDate').val(),
                    'ToDate': $('#toDate').val(),
                    'DashBoard': 1,
                    'Buyer': consignee,
                    'Country': country,
                    "Brand": Brand,
                    "AccessType": AccessType,
                    "Processor": processor,
                },

                success: function (data) {
                    const tableId = '#key-datatableFullscreen';
                    $("#PopUpTableFullScreen").html(data);
                    if ($.fn.dataTable.isDataTable('#key-datatableFullscreen')) {
                        table.destroy();
                    }
                    table = $('#key-datatableFullscreen').DataTable({
                        paging: false,
                        ordering: true,
                        order: [],


                        searching: true,
                        scrollX: true,
                        scrollCollapse: true,
                        scrollY: '60vh',



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
                        infoCallback: function (settings, start, end, max, total, pre) {

                            let api = this.api();
                            let snoIndex = 0; // S No column index

                            let rows = api.rows({ search: 'applied' }).data().toArray();

                            let uniqueSno = new Set(
                                rows.map(row => row[snoIndex])
                            );

                            let count = uniqueSno.size;

                            if (count === 0) {
                                return 'Showing 0 to 0 of 0 entries';
                            }

                            return 'Showing 1 to ' + count + ' of ' + count + ' entries';
                        },
                        buttons: [{
                            extend: 'excelHtml5',
                            text: 'Download Excel',
                            title: 'Pending Orders Plant Wise',
                            className: 'btnExcelDataTable',
                            exportOptions: {
                                columns: ':visible',
                                format: {
                                    body: function (data, row, column, node) {

                                        var clone = $(node).clone(); //

                                        clone.find('.breakUpPrice').each(function () {
                                            if ($(this).css('display') === 'none') {
                                                $(this).remove();
                                            }
                                        });

                                        return $.trim(clone.text());
                                    }
                                }
                            },
                            customize: function (xlsx) {
                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                var cols = $('col', sheet);

                                // Set fixed width for ALL columns
                                cols.each(function () {
                                    $(this).attr('width', 20);
                                });
                            }
                            // action: function () {
                            //     fnPendingOrderPlantWiseExcel("", "POLIST");
                            // }
                        }]
                    });

                    setTimeout(function () {
                        table.columns.adjust();
                    }, 1000);


                    /*var buttons = new $.fn.dataTable.Buttons(table, {
                        buttons: [{
                            extend: 'excelHtml5',
                            text: 'Download Excel',
                            title: 'Pending Orders Plant Wise',
                            className: 'btnExcelDataTable',
                            exportOptions: {
                                columns: ':visible'
                            },
                            customize: function (xlsx) {
                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                var cols = $('col', sheet);

                                // Set fixed width for ALL columns
                                cols.each(function () {
                                    $(this).attr('width', 20);
                                });
                            }
                            // action: function () {
                            //     fnPendingOrderPlantWiseExcel("", "POLIST");
                            // }
                        }]
                    }).container().appendTo($('#key-datatableFullscreen_filter'));*/

                    /*$('#PopUpTableFullScreen #key-datatableFullscreen_wrapper > .row > .col-sm-12.col-md-6:first-child')
                        .html(
                            '<h4 class="modal-title">Pending Contracts &nbsp;<input type="checkbox" id="chkShowPrice" />&nbsp;<label for="chkShowPrice">Show Price<label></h4>'
                        );*/
                    $("#showcstitle").html('Pending Contracts &nbsp;<input type="checkbox" id="chkShowPrice" />&nbsp;<label for="chkShowPrice">Show Price<label>');
                    $('#myModalFullScreen').modal('show');

                    $('<div class="dt-buttons btn-group flex-wrap"><button class="btn btn-secondary buttons-excel btnExcelDataTable" type="button"><span>Download Excel</span></button> </div>')
                        .insertAfter("#PopUpTableFullScreen #key-datatableFullscreen_filter lable");
                    let columnIndexesToMerge = [0, 1, 2, 3, 4, 5, 6, 7, 32];
                    setTimeout(function () {
                        applyRowspan(tableId, columnIndexesToMerge); // Adjust columns as needed
                        $('#key-datatableFullscreen td[rowspan]').css('vertical-align', 'middle');
                    }, 100);
                    // table.on('draw.dt', function () {
                    //     resetRowspan(tableId, columnIndexesToMerge);
                    //     applyRowspan(tableId, columnIndexesToMerge);
                    // });
                    table.on('draw.dt', function () {

                        let api = table;
                        let lastKey = null;
                        let counter = 1;

                        api.rows({ search: 'applied' }).every(function () {

                            let row = this.node();
                            let data = this.data();
                            let currentKey = data[0]; // Sno column

                            if (currentKey !== lastKey) {
                                $('td:eq(0)', row).html(counter);
                                counter++;
                                lastKey = currentKey;
                            } else {
                                $('td:eq(0)', row).html('');
                            }
                        });

                        resetRowspan(tableId, columnIndexesToMerge);
                        applyRowspan(tableId, columnIndexesToMerge);
                    });

                }
            });
            $.fn.dataTable.ext.search.push(function (settings, data) {

                if (settings.nTable.id !== 'key-datatableFullscreen') {
                    return true;
                }


                let searchValue = settings.oPreviousSearch.sSearch
                    .toLowerCase()
                    .replace(/\s+/g, '')   // remove spaces
                    .replace(/x/g, 'x');   // normalize X

                if (!searchValue) return true;

                // Combine all column text
                let rowText = data.map(col =>
                    $('<div>').html(col).text()
                ).join(' ')
                    .toLowerCase()
                    .replace(/\s+/g, '')
                    .replace(/x/g, 'x');

                return rowText.includes(searchValue);
            });

        }


        // function getPendingContracts() {
        //     //});
        // }

        $(document).on('click', '#list', function (e) {
            // updateList();
            getList();
        });

        function renderShipmentsGraph(ShipmentData) {

            var AryShipMents = ShipmentData.Shipments;
            var AryOrders = ShipmentData.Orders;
            var AryXlables = ShipmentData.MonthName;
            var series = [
                //{name:"Total Kgs X 1000",type:"line",data:AryKgs},
                {
                    name: "Invoices ",
                    type: "column",
                    data: AryOrders
                },
                {
                    name: "Shipments",
                    type: "column",
                    data: AryShipMents
                }

            ];

            var SalesOptions = {
                series: [
                    //{name:"Total Kgs X 1000",type:"line",data:AryKgs},
                    {
                        name: "Invoices ",
                        type: "column",
                        data: AryOrders
                    },
                    {
                        name: "Shipments",
                        type: "column",
                        data: AryShipMents
                    }

                ],
                stroke: {
                    width: [2, 2, 2, 2],
                    curve: "smooth"
                },
                chart: {
                    height: 280,
                    type: "line",
                    toolbar: {
                        show: !1
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: !1,
                        columnWidth: "20%"
                    }
                },
                dataLabels: {
                    enabled: !1
                },
                legend: {
                    show: 1
                },
                //colors:["#eeb902","#666","#ff0000"],
                colors: ["#666", "#3cb371"],
                labels: AryXlables
            };


            //  if(chart=null){
            chart = new ApexCharts(document.querySelector("#line-column-chart"), SalesOptions);
            chart.render();
            //}


            chart.updateSeries(series);
            //chart.updateOptions(Colors);


        }


        function renderExpSaleKgsGraph(data) {

            var ShipmentData = JSON.parse(JSON.stringify(data));

            AryExportKgs = ShipmentData?.ExportKgs;
            AryShippedKgs = ShipmentData?.ShippedKgs;
            AryShipmentXlables = ShipmentData?.Grade;

            var totalLabels = AryShipmentXlables?.map(function (label, index) {
                var total = AryExportKgs[index] + AryShippedKgs[index];
                return label + " (Total: " + total + " Tons)";
            });
            /*
            var AryExportKgs=<?php //echo json_encode($ExportKgs,true);
            ?>;
            var AryShippedKgs = <?php // echo json_encode($ShippedKgs,true);
            ?>;
            var AryShipmentXlables = <?php // echo json_encode($Grade,true);
            ?>;
            */

            var Series = [{
                name: "To be shipped Qty",
                type: "column",
                data: (AryExportKgs) ? AryExportKgs : []
            },
            {
                name: "Shipped Qty",
                type: "column",
                data: (AryShippedKgs) ? AryShippedKgs : []
            }
            ]

            var shipmentOptions = {
                series: (Series) ? Series : [],
                stroke: {
                    width: [6, 6, 5, 5], // ["blue candel", "green candel"]
                    curve: "smooth"
                },
                chart: {
                    height: 280,
                    type: "line",
                    toolbar: {
                        show: !1
                    },
                    events: {
                        dataPointSelection: function (event, chartContext, config) {
                            // getBuyerWiseData();  // Export Qty vs To Ship Qty POPUP
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: !1,
                        columnWidth: "20%"
                    }
                },
                dataLabels: {
                    enabled: !1
                },
                legend: {
                    show: !1
                },
                colors: ["#5664d2", "#3cb371"],
                //colors:["#ff0000"],
                labels: (AryShipmentXlables) ? AryShipmentXlables : [],


                tooltip: {
                    x: {
                        formatter: function (val, opts) {
                            var dataPointIndex = opts?.dataPointIndex;
                            var series = opts.series;
                            var total = 0;
                            for (let index = 0; index < series.length; index++) {
                                const element = series[index];
                                total += parseFloat(element[dataPointIndex]);
                            }
                            return AryShipmentXlables[dataPointIndex] + " (Total: " + total.toFixed(3) + " Tons)";
                        },
                    }
                }
            };

            chartShipment = new ApexCharts(document.querySelector("#shipment-chart"), shipmentOptions);
            chartShipment.render();

            updateGraph(true, true);

        }


        function renderPlantwiseGraph(data) {
            var options = {
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: {
                        show: false // Hides the toolbar
                    },
                    events: {
                        dataPointSelection: function (event, chartContext, config) {
                            // Getting details of the clicked bar
                            var seriesIndex = config.seriesIndex;
                            var dataPointIndex = config.dataPointIndex;
                            var name = config.w.config.series[seriesIndex]['name'];
                            var plantName = config.w.config.xaxis.categories[dataPointIndex];

                            // Call function with data and bar details
                            getBuyerWiseData(data['exporterDetails'][dataPointIndex], name);
                        }
                    }
                },
                series: data['series'],
                xaxis: {
                    categories: data['stationNames'],
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
                        columnWidth: '50%' // Adjusts width for improved visibility
                    }
                },
                dataLabels: {
                    enabled: true, // Enables labels for clarity
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
                    show: true // Fixes to show legend properly
                },
                tooltip: {
                    enabled: true, // Ensures tooltip is enabled
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

            if (chartInstances['plantwiseOrders']) {
                chartInstances['plantwiseOrders'].destroy();
            }
            // Render the chart
            var chart = new ApexCharts(document.querySelector("#plantwiseOrders"), options);
            chart.render().then(() => {
                // Use JavaScript to target the data labels and apply a CSS transformation
                const dataLabels = document.querySelectorAll('.apexcharts-text.apexcharts-datalabel');
                dataLabels.forEach(label => {
                    label.style.transform = 'rotate(-45deg)'; // Rotating labels by -45 degrees
                    label.style.transformOrigin = 'center'; // Setting the origin for rotation
                });
            });

            chartInstances['plantwiseOrders'] = chart;

        }

        $(document).on("click", "#chkShowPrice", function () {
            if ($(this).is(':checked')) {
                console.log(document.getElementById("breakUpPrice"));
                $(".breakUpPrice").show();
            } else {
                $(".breakUpPrice").hide();
            }
        });

        function renderPlantShipmentGraph(data) {

            var ArySeries = data.PoSeries;
            var AryLables = data.PoLables;
            var AryColors = data.PoColors;
            // console.log(ArySeries);
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
                colors: AryColors
            };
            chartPort = new ApexCharts(document.querySelector("#donut-chart-shipments"), options);

            chartPort.render();



        }


        function GetPortWisePoData(PortID, Portname, Type, popup) {
            if (popup) {
                var consignee = $("#drpBuyer").val();
            } else {
                var consignee = $("#Buyer").val();
            }
            var year = $("#Year").val();
            var exporter = $("#drpExporter").val();
            var country = $("#Country").val();
            var Brand = $("#Brand").val();
            var Month = $('#drpMonth').val();
            var Fbdstatus = $('#Fbdstatus').val();
            if ((year == '') && ($('#fromDate').val() == '' && $('#fromDate').val() == '')) {
                alert('Select a year or date range.');
                return;
            }
            $.ajax({
                url: 'Ajax/AjaxGetPortWisePoData.php',
                type: 'POST',
                // data:{'EXPORTER':EXPORTER,'YEAR':YEAR,'TYPE':'POLIST'},
                data: {
                    // {"Year":"2023","Exporter":"1","Consignee":"","Country":"","PortID":"823"}
                    "PortID": PortID,
                    "Year": year,
                    'FromDate': $('#fromDate').val(),
                    'ToDate': $('#toDate').val(),
                    "Exporter": exporter,
                    "Consignee": consignee,
                    "Brand": Brand,
                    "Country": country,
                    "Month": Month,
                    "Fbdstatus": Fbdstatus,
                    "Type": Type
                },
                success: function (data) {

                    $("#PopUpTableFullScreen").html(data);
                    // fnDataTable();
                    //  showData(data);

                    /* Destroy the Datatable if initialize */
                    if ($.fn.dataTable.isDataTable('#key-datatableFullscreen')) {
                        table.destroy();
                    }
                    // { dom: 'lifrtp' }
                    //table = $('#key-datatableFullscreen').DataTable({bSort:false});
                    table = $('#key-datatableFullscreen').DataTable({
                        paging: false,

                        searching: true,
                        scrollX: true,
                        scrollCollapse: true,
                        scrollY: '60vh',



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
                        buttons: [{
                            extend: 'excelHtml5',
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            title: 'On Water Report',
                            footer: true,
                            exportOptions: {
                                columns: ':visible'
                            }
                        }]
                    });
                    setTimeout(function () {
                        table.columns.adjust();
                    }, 1000);
                    /*var buttons = new $.fn.dataTable.Buttons(table, {

                        buttons: [{
                            extend: 'excelHtml5',
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            title: 'On Water Report',
                            footer: true,
                            exportOptions: {
                                columns: ':visible'
                            }
                        }]
                    }).container().appendTo($('#key-datatableFullscreen_filter'));*/

                    var port;
                    if (Portname != "") {
                        port = "( " + Portname + " )";
                    } else {
                        port = "";
                    }

                    /*$('#PopUpTableFullScreen #key-datatableFullscreen_wrapper > .row > .col-sm-12.col-md-6:first-child')
                        .html('<h4 class="modal-title">On Water Report ' + port + ' </h4>');*/
                    $("#showcstitle").html('On Water Report ' + port);
                    $('#myModalFullScreen').modal('show');

                    ;

                }
            });
        }

        $(document).on("click", "#TileID2", function () {
            $('#Fbdstatus').parent().show();
            GetPortWisePoData("", "");
        })






        $(document).on("click", "#TileID4", function () {
            getBuyerWiseData('', 'Total');
        })




        function renderCountryWiseDetailsPie(data) {

            var data = JSON.parse(JSON.stringify(data));
            var dataPoints = data?.dataPoints || [];
            console.log(dataPoints);
            var total = dataPoints?.reduce(function (sum, point) {
                return sum + point.y;
            }, 0);
            "use strict";
            var chart = new CanvasJS.Chart("countryWiseDetails", {
                animationEnabled: true,
                // legend: {
                //     horizontalAlign: "right", // "left", "right", "center"
                //     verticalAlign: "top", // "top", "bottom", "center"
                //     fontSize: 12,
                //     // Other properties...
                // },
                data: [{
                    type: "pie",
                    click: onClick,
                    markerType: "none",
                    indexLabelFontSize: 11,
                    yValueFormatString: "#,##0.\"\"",
                    indexLabel: "{x}: {y}(#percent%)",
                    toolTipContent: "{x}: {y} - (#percent%)",
                    indexLabelPlacement: "outside",
                    // showInLegend: true,  // Enable legend
                    legendText: "{x}: {y} (#percent%)",
                    dataPoints: dataPoints?.map(function (point) {

                        var percent = ((point.y / total) * 100);
                        return {
                            x: point.x,
                            y: point.y,
                            percent: (percent).toFixed(2),

                        };
                    })
                }]
            });
            chart.render();

            function onClick(e) {
                //  alert(  e.dataPoint.PortID+ ", dataPoint { x:" + e.dataPoint.x + ", y: "+ e.dataPoint.y + " }" );
                //  GetPortWisePoData(e.dataPoint.PortID, e.dataPoint.x);
                // GetPortWisePoData("");
                // getBuyerWiseData();
            }



        }


        function renderPortGraph(data) {
            var data = JSON.parse(JSON.stringify(data));
            var dataPoints = data.dataPoints;
            console.log(dataPoints);
            "use strict";
            var chart = new CanvasJS.Chart("portChart", {
                animationEnabled: true,
                zoomEnabled: true,
                axisX: {
                    labelFontWeight: "bold", // Bold Axis Labels
                    interval: 1
                },
                data: [{
                    type: "pie",
                    click: onClick,
                    indexLabelFontSize: 9,
                    indexLabelFontWeight: "bold",
                    yValueFormatString: "#,##0.\"\"",
                    indexLabel: "{x}: {y}",
                    toolTipContent: "{x}: {y}(On water: {OnWater}, Delivered: {Delivered})",
                    dataPoints: dataPoints?.map(function (point) {
                        return {
                            x: point.x,
                            y: point.y,
                            OnWater: point?.OnWaterAndDelivered[0] || 0,
                            Delivered: point?.OnWaterAndDelivered[1] || 0,
                            PortID: point.PortID
                        };
                    })
                }]

            });
            chart.render();

            function onClick(e) {
                GetPortWisePoData(e.dataPoint.PortID, e.dataPoint.x, 'Delivered');
            }

        }

        // $(document).on("click", "#TileID3", function () {
        //     renderPendingContractsPopup();
        // })

        $(document).on('click', '#chbkExp', function (e) {
            var chbkExp = $("#chbkExp").is(':checked');
            var chbkShip = $("#chbkShip").is(':checked');
            var SelectedDate = '<?php echo $PresentYear ?>';
            updateGraph(chbkExp, chbkShip);
        });

        $(document).on('click', '#chbkShip', function (e) {
            var chbkExp = $("#chbkExp").is(':checked');
            var chbkShip = $("#chbkShip").is(':checked');
            var SelectedDate = '<?php echo $PresentYear ?>';

            updateGraph(chbkExp, chbkShip);
        });

        $(document).on('click', '#Update', function (e) {

            var PoID = $('#Update').data('id');
            var PoID = $('#Update').val();

            var Url = 'CreateShipmentStatus.php?PoID=' + PoID;
            window.open(Url, "_blank");
        });
        // var table;
        function getBuyerWiseData(Processor, ReqType, popUp) {
            var YEAR = $('#Year').val();
            var RunningFinStartYear = <?php echo $RunningFinStartYear ?>;
            var Currentyear = <?php echo $Currentyear ?>;
            if (RunningFinStartYear != YEAR && YEAR != '') {
                ReqType = 'Shipped';
            }
            if (popUp) {
                var Buyer = $('#drpBuyer').val();
            } else {
                var Buyer = $('#Buyer').val();
            }

            var EXPORTER = $('#drpExporter').val();


            var Country = $('#Country').val();
            var Month = $('#drpMonth').val();
            var Brand = $('#Brand').val();
            ReqType = (ReqType) ? ReqType : '';
            if ((YEAR == '') && ($('#fromDate').val() == '' && $('#fromDate').val() == '')) {
                alert('Select a year or date range.');
                return;
            }
            $.ajax({
                url: 'Ajax/AjaxBuyerDetailsNew.php', // 'Ajax/AjaxBuyerDetails.php',
                type: 'POST',
                data: {
                    'Year': YEAR,
                    'ExpID': EXPORTER,
                    'ConsigneeID': Buyer,
                    'Month': Month,
                    'CountryID': Country,
                    'Brand': Brand,
                    'Processor': (Processor != 'ALL') ? Processor : '',
                    'ReqType': ReqType,
                    'FromDate': $('#fromDate').val(),
                    'ToDate': $('#toDate').val(),
                    'DashBoard': 1
                },
                success: function (data) {

                    $("#PopUpTableFullScreen").html(data);
                    if ($.fn.dataTable.isDataTable('#key-datatableFullscreen')) {
                        table.destroy();
                    }
                    table = $('#key-datatableFullscreen').DataTable({
                        paging: false,

                        searching: true,
                        scroll: true,
                        scrollCollapse: true,
                        scrollY: '60vh',



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
                        buttons: [{
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            exportOptions: {
                                columns: ':visible'
                            },
                            action: function () {
                                // fnPendingOrderPlantWiseExcel();
                                // fnPendingOrderPlantWiseExcel("", "POLIST");
                                var excel_data = $('#key-datatableFullscreen').closest("div.col-sm-12").html();
                                exportTableToExcel('key-datatableFullscreen', ReqType + ' Orders');
                            }
                        }]
                    });
                    setTimeout(function () {
                        table.columns.adjust();
                    }, 1000);
                    /*var buttons = new $.fn.dataTable.Buttons(table, {
                        buttons: [{
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            exportOptions: {
                                columns: ':visible'
                            },
                            action: function () {
                                // fnPendingOrderPlantWiseExcel();
                                // fnPendingOrderPlantWiseExcel("", "POLIST");
                                var excel_data = $('#key-datatableFullscreen').closest("div.col-sm-12").html();
                                exportTableToExcel('key-datatableFullscreen', ReqType + ' Orders');
                            }
                        }]
                    }).container().appendTo($('#key-datatableFullscreen_filter'));*/

                    /*$('#PopUpTableFullScreen #key-datatableFullscreen_wrapper > .row > .col-sm-12.col-md-6:first-child')
                        .html('<h4 class="modal-title">' + ReqType + ' Orders</h4>');*/
                    $("#showcstitle").html(ReqType + ' Orders');
                    $('#myModalFullScreen').modal('show');

                    $('<div class="dt-buttons btn-group flex-wrap"><button class="btn btn-secondary buttons-excel btnExcelDataTable" type="button"><span>Download Excel</span></button> </div>')
                        .insertAfter("#PopUpTableFullScreen #key-datatableFullscreen_filter lable");

                }
            });

        }

        $(document).on('click', '.btnDeliveredModal', function (e) {

            var EXPORTER = $('#drpExporter').val();
            var Buyer = $('#Buyer').val();
            var Brand = $('#Brand').val();
            var Country = $('#Country').val();
            var YEAR = $('#Year').val();
            var MONTH = $('#drpMonth').val();
            var ConsigneeID = $('#Buyer').val();
            if (($('#Year').val() == '') && ($('#fromDate').val() == '' && $('#fromDate').val() == '')) {
                alert('Select a year or date range.');
                return;
            }

            $.ajax({
                url: 'Ajax/AjaxEtaPoList.php',
                type: 'POST',
                data: {
                    "Type": "Dashboard",
                    "Year": YEAR,
                    'FromDate': $('#fromDate').val(),
                    'ToDate': $('#toDate').val(),
                    "Month": MONTH,
                    "Exporter": EXPORTER,
                    "Buyer": Buyer,
                    "Brand": Brand,
                    "Country": Country,
                    "ConsigneeID": ConsigneeID,
                    'FromDate': $('#fromDate').val(),
                    'ToDate': $('#toDate').val(),

                },
                success: function (data) {

                    $("#PopUpTableFullScreen").html(data);
                    if ($.fn.dataTable.isDataTable('#key-datatableFullscreen')) {
                        table.destroy();
                    }

                    table = $('#key-datatableFullscreen').DataTable({
                        paging: false,

                        searching: true,
                        scrollX: true,
                        scrollCollapse: true,
                        scrollY: '60vh',


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
                        buttons: [{
                            extend: 'excelHtml5',
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            title: 'Delivered Report',
                            footer: true,
                            exportOptions: {
                                columns: ':visible'
                            }
                        }]
                    });
                    setTimeout(function () {
                        table.columns.adjust();
                    }, 1000);
                    /*var buttons = new $.fn.dataTable.Buttons(table, {

                        buttons: [{
                            extend: 'excelHtml5',
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            title: 'Delivered Report',
                            footer: true,
                            exportOptions: {
                                columns: ':visible'
                            }
                        }]
                    }).container().appendTo($('#key-datatableFullscreen_filter'));*/

                    /*$('#PopUpTableFullScreen #key-datatableFullscreen_wrapper > .row > .col-sm-12.col-md-6:first-child')
                        .html('<h4 class="modal-title">Delivered Report </h4>');*/
                    $("#showcstitle").html("Delivered Report");
                    $('#myModalFullScreen').modal('show');

                }
            });
        });


        $(document).on('click', '.btnPaymentsReceived', function (e) {

            var EXPORTER = $('#drpExporter').val();
            var Buyer = $('#Buyer').val();
            var Brand = $('#Brand').val();
            var Country = $('#Country').val();
            var YEAR = $('#Year').val();
            var MONTH = $('#drpMonth').val();
            if ((YEAR == '') && ($('#fromDate').val() == '' && $('#fromDate').val() == '')) {
                alert('Select a year or date range.');
                return;
            }

            $.ajax({
                url: 'Ajax/AjaxPayReceived.php', //AjaxPayReceivedList.php
                type: 'POST',
                data: {
                    "Type": "Dashboard",
                    //"FromDate": YEAR,
                    "Year": YEAR,
                    "Month": MONTH,
                    "Exporter": EXPORTER,
                    "Buyer": Buyer,
                    "Brand": Brand,
                    "Country": Country,
                    'FromDate': $('#fromDate').val(),
                    'ToDate': $('#toDate').val(),
                    'Dashboard': 1
                },
                success: function (data) {

                    $("#PopUpTableFullScreen").html(data);
                    if ($.fn.dataTable.isDataTable('#key-datatableFullscreen')) {
                        table.destroy();
                    }

                    table = $('#key-datatableFullscreen').DataTable({
                        scrollX: true,
                        lengthChange: false,
                        searching: true,
                        paging: false,
                        searching: true,
                        scrollX: true,
                        scrollCollapse: true,
                        scrollY: '60vh',

                        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                        pageLength: 10,
                        searching: true,

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
                        buttons: [{
                            extend: 'excelHtml5',
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            title: 'Payments Received Report',
                            footer: true,
                            exportOptions: {
                                columns: ':visible'
                            }
                        }]

                    });
                    setTimeout(function () {
                        table.columns.adjust();
                    }, 1000);
                    /*var buttons = new $.fn.dataTable.Buttons(table, {

                        buttons: [{
                            extend: 'excelHtml5',
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            title: 'Payments Received Report',
                            footer: true,
                            exportOptions: {
                                columns: ':visible'
                            }
                        }]
                    }).container().appendTo($('#key-datatableFullscreen_filter'));*/

                    /*$('#PopUpTableFullScreen #key-datatableFullscreen_wrapper > .row > .col-sm-12.col-md-6:first-child')
                        .html('<h4 class="modal-title">Payments Received Report </h4>');*/
                    $("#showcstitle").html("Payments Received Report");
                    $('#myModalFullScreen').modal('show');

                }
            });
        });
    </script>
    <script>
        function updateGraph(chbkExp, chbkShip) {


            //var AryShipmentXlables=<?php echo json_encode($Grade, true); ?>;

            if (chbkExp == true && chbkShip == true) {
                var Series = [{
                    name: "To be shipped Qty",
                    type: "column",
                    data: AryExportKgs
                },
                {
                    name: "Shipped Qty",
                    type: "column",
                    data: AryShippedKgs
                }
                ];
                var Colors = {
                    colors: ["#5664d2", "#3cb371"]
                };
            } else if (chbkExp) {
                var Series = [{
                    name: "To be shipped Qty",
                    type: "column",
                    data: AryExportKgs
                }];
                var Colors = {
                    colors: ["#5664d2"]
                };
            } else if (chbkShip) {
                var Series = [{
                    name: "Shipped Qty",
                    type: "column",
                    data: AryShippedKgs
                }];
                var Colors = {
                    colors: ["#3cb371"]
                };
            } else {
                var Series = [{
                    name: "To be shipped Qty",
                    type: "column",
                    data: AryExportKgs
                },
                {
                    name: "Shipped Qty",
                    type: "column",
                    data: AryShippedKgs
                }
                ];
                var Colors = {
                    colors: ["#5664d2", "#3cb371"]
                };
            }

            chartShipment.updateSeries(Series);
            chartShipment.updateOptions(Colors);

            /*chartShipment.updateSeries(
            [
            {name:"Shipment Kgs",type:"column",data:AryShippedKgs}
            ]
            )
            */

        }
        function renderGraph(data, divId, chartType, callback) {
            const customColors = ['#3444c1', '#FF33F3']; // Example colors

            var Options = {
                series: data.series,
                stroke: {
                    width: [2, 2, 2, 2],
                    curve: "smooth"
                },
                chart: {
                    height: 400,
                    type: chartType,
                    toolbar: {
                        show: !1
                    },
                    events: {
                        dataPointSelection: function (event, chartContext, config) {
                            callback(event, chartContext, config);
                        }
                    }
                },

                plotOptions: {
                    bar: {
                        horizontal: !1,
                        columnWidth: data?.columnWidth || "30%"
                    },

                    pie: {
                        size: '30%'
                    }
                },
                dataLabels: {
                    enabled: data.dataLabels ? data.dataLabels : false
                },
                legend: {
                    show: 0 //if it is one the display liner names at the bottom of the graph
                },
                noData: {
                    text: 'No data available', // Custom message
                    align: 'center', // Options: 'left', 'center', 'right'
                    verticalAlign: 'middle', // Options: 'top', 'middle', 'bottom'
                    offsetX: 0, // Horizontal offset
                    offsetY: 0, // Vertical offset
                    style: {
                        color: '#FF0000', // Text color
                        fontSize: '16px', // Font size
                        fontFamily: 'Arial' // Font family
                    }
                },
                labels: data.labels,
                xaxis: data.xaxis,
                yaxis: data.yaxis
            };

            if (chartInstances[divId]) {
                chartInstances[divId].destroy();
            }
            chart = new ApexCharts(document.querySelector("#" + divId), Options);
            chart.render();
            chartInstances[divId] = chart;
        }
        function renderFreightDataGrpah(freightData) {

            // Your data
            const data = freightData;

            // Extracting data for each series
            const dates = data?.map(item => item.Date);
            const seriesData = {};

            if (dates != null) {
                // Dynamically extract keys and populate series data
                data.forEach(item => {
                    Object.keys(item).forEach(key => {
                        if (key !== "Date") { // Skip the "Date" key
                            if (!seriesData[key]) {
                                seriesData[key] = []; // Initialize the array if not exists
                            }
                            seriesData[key].push(item[key]); // Add the value to the corresponding series
                        }
                    });
                });
            }

            // Convert the seriesData object into the desired series array
            const series = Object.keys(seriesData).map(key => ({
                name: key,
                data: seriesData[key]
            }));


            renderGraph({
                series: series || [],
                labels: [],
                xaxis: {
                    categories: dates,
                    title: {
                        text: 'Date'
                    }
                },
                yaxis: {
                    title: {
                        text: 'Rates'
                    }
                },
                dataLabels: true
            }, 'freight-rates-grpah', "line", function (event, chartContext, config) {

            });
        }
        $('#rmFreightFromDate,#rmFreightToDate').on('change', function () {
            let fromDate = $('#rmFreightFromDate').val();
            let toDate = $('#rmFreightToDate').val();
            let from = new Date(fromDate);
            let to = new Date(toDate);

            let diff = (to - from) / (1000 * 60 * 60 * 24);

            if (diff > 60) {
                alert("Maximum 60 days allowed.");
                $(this).val("").trigger('change');
            }
        });
        $(document).on('click', '#rm-freight-download', function () {
            var fromDate = $('#rmFreightFromDate').val();
            var toDate = $('#rmFreightToDate').val();
            var liner_id = $('#liner_id').val() ? $('#liner_id').val().join(",") : '';
            var port_id = $('#port_id').val() ? $('#port_id').val().join(",") : '';
            var liner_id = '', port_id = '';
            if (!fromDate || !toDate) {
                alert('Please select both dates first.');
                return;
            }
            var link = "Ajax/AjaxFreightRatesReport.php?dashboard=T&fromDate=" + fromDate + "&toDate=" + toDate + "&liner_id=" + liner_id + "&port_id=" + port_id;
            window.open(link, '_blank');
        });
    </script>




</body>

</html>