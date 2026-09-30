<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");


$AccessType = $_SESSION["AccessType"];

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
$endYear = $Currentyear + 1;//uma 10-04-2024


if ($CurrentMonth > 3 && $Year == '') {
    // $FinClsYear = $Currentyear;
    $FinClsYear = $Currentyear + 1;//uma
} else if ($Year != '') {
    $FinClsYear = $Year;
} else {
    // $FinClsYear = $Currentyear - 1;
    $FinClsYear = $Currentyear;//uma
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
$StmtConsignee = $iconn->query($SqlConsignee);

$SqlCountries = "SELECT * FROM `ScrmCountriesList`";
$StmtCountries = $iconn->query($SqlCountries);

$DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";
$PastDateFilter = "BETWEEN '$PastYearStart' and '$PastYearEnd'";


$CurrentFincialYear = $DateStart;







$SqlBuyerData = "SELECT ConID,PoID, MAX(PoDate)as LastPoDate, ConsigneeName, count(*) as TotalPo,
(SELECT sum(Mcs*TotalSlabs*SlabWeight*Mfactor) from ScrmPoGradeData as PGD 
 LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID WHERE PGD.InvId=SPD.PoID ) as Kgs,
(SELECT sum(Mcs*TotalSlabs*SlabWeight*Price) from ScrmPoGradeData as PGD 
 LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID WHERE PGD.InvId=SPD.PoID ) as Price 
FROM `ScrmPoData` as SPD
LEFT JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID   
 WHERE SPD.PoDate  $DateFilter GROUP by ConID order by TotalPo Desc";
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

$SqlCountries = "SELECT * FROM `ScrmCountriesList`";
$StmtCountries = $iconn->query($SqlCountries);

$SqlBrand = "SELECT * FROM `ScrmBrandsMaster`WHERE Status=1;";
$StmtBrand = $iconn->query($SqlBrand);




?>

<!doctype html>
<html lang="en">

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>

    <style>
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

    <?php include($IncludesPath . "head-css.php"); ?>

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
    <?php include($IncludesPath . "PreLoader.php"); ?>
    <!-- class="sidebar-enable vertical-collpsed" Begin page -->
    <div id="layout-wrapper">
        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php //include ($IncludesPath."page-title.php"); ?>





                    <div class="row">


                        <!-- Global Search  -->
                        <?php include($cp . "Includes/GlobalSearch.php"); ?>
                        <!-- Global Search  -->



                        <div class="col-xl-8">
                            <div class="row">

                                <!--<div class="col-md-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="media">
                                                    <div class="media-body overflow-hidden">
                                                        <p class="text-truncate font-size-14 mb-2">Opening Balance</p>
                                                        <h4 class="mb-0"><?php echo $OpeningBalance ?></h4>
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="ri-briefcase-4-line font-size-24"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div> -->




                                <div class="col-md-4">
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

                                <div class="col-md-4">
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

                                <div class="col-md-4">
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







                            </div>
                            <div class="row">

                                <div class="col-md-4">
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

                                <div class="col-md-4">
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

                                <div class="col-md-4">
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
                            <!-- end row -->

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

                            <!--/****************************/  -->
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-4">Export Qty vs To Ship Qty</h4>
                                    <div>
                                        <div id="shipment-chart" class="apex-charts" dir="ltr"></div>
                                    </div>
                                </div>



                                <div class="card-body border-top text-center">
                                    <div class="row">


                                        <div class="col-sm-4">
                                            <div class="mt-4 mt-sm-0">
                                                <p class="mb-2 text-muted text-truncate">
                                                    <!--<i class="mdi mdi-circle text-primary font-size-10 mr-1"></i>-->
                                                    <input type="checkbox" id="chbkExp">
                                                    Export Qty-
                                                    <?php echo $TotalExportKgs ?> Kgs
                                                </p>
                                                <div class="d-inline-flex">
                                                    <h5 class="mb-0 mr-2"></h5>
                                                    <!--<div class="text-success">
                                                            <i class="mdi mdi-menu-up font-size-14"> </i>2.1 %
                                                        </div> -->
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="mt-4 mt-sm-0">
                                                <p class="mb-2 text-muted text-truncate">
                                                    <!--<i class="mdi mdi-circle text-primary font-size-10 mr-1"></i>-->
                                                    <input type="checkbox" id="chbkShip">

                                                    To Ship Qty
                                                    -
                                                    <?php echo
                                                        $TotalShippedKgs ?> Kgs
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




                        <div class="col-xl-4">



                            <div class="card">
                                <div class="card-body">

                                    <h4 class="card-title mb-4">Pending Contracts Plant Wise
                                        <?php //echo $Fi='('. $FinClsYear . '-' .($FinClsYear+1) .')'; ?>
                                    </h4>

                                    <!--   <div id="donut-chart" class="apex-charts"></div>-->
                                    <div id="pendingOrders" style="height: 250px; width: 100%;"></div>

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


                            <div class="card">
                                <div class="card-body">

                                    <h4 class="card-title mb-4">Country Wise Details
                                        <?php //echo $Fi='('. $FinClsYear . '-' .($FinClsYear+1) .')'; ?>
                                    </h4>

                                    <!--   <div id="donut-chart" class="apex-charts"></div>-->
                                    <div id="orderPie" style="height: 250px; width: 100%;"></div>

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



                            <!-- <div class="card">
                                    <div class="card-body">
                                       
                                        <h4 class="card-title mb-4">Shipments 
                                        <?php // echo $Fi='('. $FinClsYear . '-' .($FinClsYear+1) .')'; ?></h4>

                                        <div id="donut-chart-shipments" class="apex-charts"></div>

                                        <div class="row">
                                        <div class="col-4">
                                                <div class="text-center mt-4">
                                                    <p class="mb-2 text-truncate"><i 
                                                    
                                                    >
                                                    </i>Total</p>
                                                    <h5><?php //echo $TotalPos ?></h5>
                                                </div>
                                            </div>
                                         
                                            <? php // foreach($AryShiftDtls as $ShiftRow){ ?>
                                                <div class="col-4">
                                                <div class="text-center mt-4">
                                                    <p class="mb-2 text-truncate"><i 
                                                    class="mdi mdi-circle font-size-10 mr-1" 
                                                    style="color: <? php // echo $ShiftRow['Color'] ?>;">
                                                    </i> <? php // echo $ShiftRow['Name'] ?></p>
                                                    <h5><? php // echo $ShiftRow['Total'] ?></h5>
                                                </div>
                                            </div>
                                        <? php //	} ?>
                                            
                                            
                                           
                                        </div>
                                    </div>
                                </div> -->









                            <div class="card">
                                <div class="card-body">

                                    <h4 class="card-title mb-4">Port - Containers</h4>
                                    <div id="portChart" style="height: 250px; width: 100%;"></div>

                                    <!--<div id="donut-chart-port" class="apex-charts"></div>-->


                                </div>
                            </div>


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




                <!-- end row -->

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <?php
        include($IncludesPath . "footer.php");

        // echo file_get_contents($BaseDist."partials/footer.php"); ?>
    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->


    <div id="myModalFullScreen" class="modal fade" role="dialog" tabindex="-1">
        <div class="modal-dialog  modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header" style="padding: 5px 15px">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
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
    include($IncludesPath . "vendor-scripts.php");
    include($IncludesPath . "NewScripts.php");
    ?>

    <link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.js"></script>

    <!-- <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script> -->
    <!-- <script src="https://uiwrapper.silverlights.in/CanvasJs/canvasjs.min.js"></script>  -->
    <script src="<?php echo $BaseDist ?>assets/js/canvasjs.min.js"></script>
    <!-- <script src="https://silverlights.in/uiwrapper/CanvasJs/canvasjs.min.js"></script> -->
    <script>
        $(document).ready(function () {
            getList();
        });

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

        function getList() {

            // var ConID = $('#Year').val();

            var EXPORTER = $('#drpExporter').val();
            var YEAR = $('#Year').val();
            var Buyer = $('#Buyer').val();
            var Country = $('#Country').val();
            var Month = $('#drpMonth').val();
            var Brand = $('#Brand').val();

            $.ajax({
                url: 'Ajax/AjaxDashboard.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    // 'ConID': ConID
                    'Year': YEAR,
                    'ExpID': EXPORTER,
                    'ConsigneeID': Buyer,
                    'Month': Month,
                    'CountryID': Country,
                    'Brand': Brand
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
                    renderExpSaleKgsGraph(AryData.ArySaleQty); // Export Qty vs To Ship Qty
                    renderPortGraph(AryData.AryPortDtls); //Pending Order (2023-2024)
                    renderPoGraph(AryData.AryPoDtls); // Port - Containers
                    renderOrderPie(AryData.AryCountryPoDtlsPie); // Port - Containers
                    //renderPlantShipmentGraph(AryData.AryPoDtls); // #donut-chart-shipments            

                    //  renderCountryGraph(AryData.AryCountryPoDtls); // Country Wise Details  // Ramesh
                    // UpdateCountryChat(AryData.AryCountryPoDtls); // again update the country details // Ramesh
                }
            });
        }

        // function postDataToNewTab(data, url) {    
        //     // console.log(data);
        //     // if(data){
        //     //     var win = window.open(url, '_blank').postMessage(data, '*');
        //     // }else{
        //     //     console.log("no");
        //     // }
        //     // win.postMessage(data, '*');
        //     window.open(url, '_blank')
        // }

        function exportTableToExcel(tableID, filename = '') {
            var downloadLink;
            var dataType = 'data:application/vnd.ms-excel';
            var tableSelect = document.getElementById(tableID).cloneNode(true);

            // Remove elements with display:none
            var elements = tableSelect.querySelectorAll('*[style*="display: none"]');
            for (var k = 0; k < elements.length; k++) {
                elements[k].parentNode.removeChild(elements[k]);
            }

            // Apply CSS styles for borders
            tableSelect.style.border = '1px solid #eff2f7';
            var trs = tableSelect.getElementsByTagName('tr');
            for (var i = 0; i < trs.length; i++) {
                trs[i].style.border = '1px solid #eff2f7';
                var tds = trs[i].getElementsByTagName('td');
                for (var j = 0; j < tds.length; j++) {
                    tds[j].style.border = '1px solid #eff2f7';
                }
            }

            var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

            filename = filename ? filename + '.xls' : 'excel_data.xls';

            downloadLink = document.createElement('a');
            document.body.appendChild(downloadLink);

            if (navigator.msSaveOrOpenBlob) {
                var blob = new Blob(['\ufeff', tableHTML], {
                    type: dataType
                });
                navigator.msSaveOrOpenBlob(blob, filename);
            } else {
                downloadLink.href = dataType + ', ' + tableHTML;
                downloadLink.download = filename;
                downloadLink.click();
            }
        }


        function fnPendingOrderPlantWiseExcel(processorID, type) {
            // var exporter = $('#drpExporter').val();
            // var year = $('#Year').val();
            // var buyer = $('#Buyer').val();
            // var country = $('#Country').val();
            // var showPrice = $("#chkShowPrice").is(':checked') ? "T" : ""; //$('#chkShowPrice').val();
            // var InvType = typeof $('#InvType').val() === "undefined" ? "" : $('#InvType').val();


            // var query = '?Type=' + type + '&Year=' + year + '&Exporter=' + exporter + '&Processor=' + processorID +
            //     '&Consignee=' + buyer + '&Country=' + country + '&ShowPrice=' + showPrice;
            // var url = 'Reports/DocPendingOrderPlantWise.php' + query;
            // console.log(url);
            // window.open(url, '_blank')

            var excel_data = $('#key-datatableFullscreen').closest("div.col-sm-12").html();
            exportTableToExcel('key-datatableFullscreen', 'PendingOrderPlantWise');

        };


        $(document).on('click', '#TileID1', function (e) {
            // getPendingContracts();
            /*    var EXPORTER = $('#drpExporter').val();
                var YEAR = $('#Year').val();
                var Buyer = $('#Buyer').val();
                var Country = $('#Country').val();
                var Brand = $('#Brand').val();
                var InvType = typeof $('#InvType').val() === "undefined" ? "" : $('#InvType').val();
                */

            pendingContractsPlantWise('');


        });

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



        function pendingContractsPlantWise(processor) {

            var year = $("#Year").val();
            var exporter = $("#drpExporter").val();
            var consignee = $("#Buyer").val();
            var country = $("#Country").val();
            var Brand = $("#Brand").val();
            var AccessType = '<?php echo $AccessType; ?>';
            //'InvType': InvType,
            $.ajax({
                // url: 'Ajax/AjaxDashboardPendingCtrs.php',
                url: 'Ajax/AjaxPendingOrderPlantWise.php',
                type: 'POST',
                // data:{'EXPORTER':EXPORTER,'YEAR':YEAR,'TYPE':'POLIST'},
                data: {
                    'EXPORTER': exporter,
                    'TYPE': 'POLIST',
                    'YEAR': year,
                    'Buyer': consignee,
                    'Country': country,
                    "Brand": Brand,
                    "AccessType": AccessType,
                    "Processor": processor,
                },
                success: function (data) {

                    // // $('.modal-title').text('Pending Contracts');
                    // $("#PopUpTable").html(data);
                    // fnDataTable();
                    // $('#myModal').modal('show');

                    // $(".modal-title").hide();
                    // $('#key-datatable_wrapper > .row > .col-sm-12.col-md-6:first-child').html('<h4 class="modal-title">Pending Contracts</h4>');
                    // $(".modal-header").html('<button type="button" class="close" data-dismiss="modal" aria-label="Close">'+
                    //     '<span aria-hidden="true">×</span>'+
                    // '</button>').css("padding","5px 15px");
                    // $(".modal-footer").hide();


                    $("#PopUpTableFullScreen").html(data);
                    if ($.fn.dataTable.isDataTable('#key-datatableFullscreen')) {
                        table.destroy();
                    }
                    // { dom: 'lifrtp' }
                    table = $('#key-datatableFullscreen').DataTable();
                    var buttons = new $.fn.dataTable.Buttons(table, {
                        buttons: [
                            // {extend: 'excelHtml5', text: 'Download Excel', className: 'btnExcelDataTable', 	title: 'Pending Contracts Report', footer: true,
                            // exportOptions: {
                            //     columns: ':visible'
                            // }},
                            {
                                text: 'Download Excel',
                                className: 'btnExcelDataTable',
                                exportOptions: {
                                    columns: ':visible'
                                },
                                action: function () {
                                    // fnPendingOrderPlantWiseExcel();
                                    fnPendingOrderPlantWiseExcel("", "POLIST");
                                }
                            }
                        ]
                    }).container().appendTo($('#key-datatableFullscreen_filter'));

                    $('#PopUpTableFullScreen #key-datatableFullscreen_wrapper > .row > .col-sm-12.col-md-6:first-child')
                        .html(
                            '<h4 class="modal-title">Pending Contracts &nbsp;<input type="checkbox" id="chkShowPrice" />&nbsp;<label for="chkShowPrice">Show Price<label></h4>'
                        );
                    $('#myModalFullScreen').modal('show');

                    $('<div class="dt-buttons btn-group flex-wrap"><button class="btn btn-secondary buttons-excel btnExcelDataTable" type="button"><span>Download Excel</span></button> </div>')
                        .insertAfter("#PopUpTableFullScreen #key-datatableFullscreen_filter lable");

                }
            });
        }


        // function getPendingContracts() {
        //     //});
        // }

        $(document).on('click', '#list', function (e) {
            // updateList();
            getList();
        });

        // function renderCountryGraph(ShipmentData) {
        //     // console.log(ShipmentData);
        //     var AryOrders = ShipmentData.Orders;
        //     var AryXlables = ShipmentData.CountryName;
        //     var SalesOptions = {
        //         series: [
        //             //{name:"Total Kgs X 1000",type:"line",data:AryKgs},
        //             {
        //                 name: "Orders",
        //                 type: "column",
        //                 data: AryOrders
        //             }
        //         ],
        //         stroke: {
        //             width: [2, 2, 2, 2],
        //             curve: "smooth"
        //         },
        //         chart: {
        //             height: 280,
        //             type: "line",
        //             toolbar: {
        //                 show: !1
        //             },
        //             events: {
        //                 dataPointSelection: function (event, chartContext, config) {
        //                     //  getBuyerWiseData();  // Country Wise Details Popup
        //                 }
        //             }
        //         },
        //         plotOptions: {
        //             bar: {
        //                 horizontal: !1,
        //                 columnWidth: "10%"
        //             }
        //         },
        //         dataLabels: {
        //             enabled: !1
        //         },
        //         legend: {
        //             show: 1
        //         },
        //         //colors:["#eeb902","#666","#ff0000"],
        //         colors: ["#666"],
        //         labels: AryXlables
        //     };
        //     // console.log(SalesOptions);
        //     countryChart = new ApexCharts(document.querySelector("#countryChart"), SalesOptions);
        //     countryChart.render();

        // }






        // function UpdateCountryChat(ShipmentData) {

        //     var AryOrders = ShipmentData.Orders;
        //     var AryXlables = ShipmentData.CountryName;
        //     var series = [{
        //         name: "Orders",
        //         type: "column",
        //         data: AryOrders
        //     }];
        //     countryChart.updateOptions({
        //         series: series,
        //         labels: AryXlables
        //     });
        // }


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

            AryExportKgs = ShipmentData.ExportKgs;
            AryShippedKgs = ShipmentData.ShippedKgs;
            AryShipmentXlables = ShipmentData.Grade;

        /*
        var AryExportKgs=<?php //echo json_encode($ExportKgs,true); ?>;
            var AryShippedKgs = <?php // echo json_encode($ShippedKgs,true); ?>;
            var AryShipmentXlables = <?php // echo json_encode($Grade,true); ?>;
        */

            var Series = [{
                name: "Export in Tons",
                type: "column",
                data: AryExportKgs
            },
            {
                name: "Shipment in Tons",
                type: "column",
                data: AryShippedKgs
            }
            ]

            var shipmentOptions = {
                series: Series,
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
                labels: AryShipmentXlables
            };

            chartShipment = new ApexCharts(document.querySelector("#shipment-chart"), shipmentOptions);
            chartShipment.render();

            updateGraph(true, true);

        }


        function renderPoGraph(data) {

            /*	
            var ArySeries=data.PoSeries;
            var AryLables=data.PoLables;
            var AryColors=data.PoColors;
    
            options={series : ArySeries,chart:{height:230,type:"donut",events: {
                  dataPointSelection: function(event, chartContext, config) {
                      console.log(config.w.config.series[config.dataPointIndex])
              console.log(config.w.config.labels[config.dataPointIndex])
                  }
                }},
            labels : AryLables,
            plotOptions:{pie:{donut:{size:"75%"}}},
            dataLabels:{enabled:1},legend:{show:!1},
    
            colors : AryColors};
    
            (chart=new ApexCharts(document.querySelector("#donut-chart"),options)).render();
            */



            var dataPoints = data.dataPoints;

            console.log(dataPoints);

            "use strict";
            var chart = new CanvasJS.Chart("pendingOrders", {
                animationEnabled: true,

                data: [{
                    type: "pie",
                    click: onClick,
                    // yValueFormatString: "#,##0.\"\"",
                    indexLabel: "{x} : {y}",
                    toolTipContent: "{x} : {y}",
                    dataPoints: dataPoints
                }]
            });
            chart.render();

            function onClick(e) {
                //	alert(  e.dataPoint.PortID+ ", dataPoint { x:" + e.dataPoint.x + ", y: "+ e.dataPoint.y + " }" ); 
                console.log(e.dataPoint.ProcessorID);

                var processor = e.dataPoint.ProcessorID; // Processor
                var year = $("#Year").val();
                var exporter = $("#drpExporter").val();
                var consignee = $("#Buyer").val();
                var country = $("#Country").val();
                var country = $("#Brand").val();

                // alert(processor);
                pendingContractsPlantWise(processor);

                // getBuyerWiseData();
            }


        }

        $(document).on("click", "#chkShowPrice", function () {
            if ($(this).is(':checked')) {
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


        function GetPortWisePoData(PortID, Portname) {
            var year = $("#Year").val();
            var exporter = $("#drpExporter").val();
            var consignee = $("#Buyer").val();
            var country = $("#Country").val();
            var Brand = $("#Brand").val();
            var Month = $('#drpMonth').val();

            $.ajax({
                url: 'Ajax/AjaxGetPortWisePoData.php',
                type: 'POST',
                // data:{'EXPORTER':EXPORTER,'YEAR':YEAR,'TYPE':'POLIST'},
                data: {
                    // {"Year":"2023","Exporter":"1","Consignee":"","Country":"","PortID":"823"}
                    "PortID": PortID,
                    "Year": year,
                    "Exporter": exporter,
                    "Consignee": consignee,
                    "Brand": Brand,
                    "Country": country,
                    "Month": Month
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
                        sScrollXInner: "150%",
                    });

                    var buttons = new $.fn.dataTable.Buttons(table, {

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
                    }).container().appendTo($('#key-datatableFullscreen_filter'));

                    var port;
                    if (Portname != "") {
                        port = "( " + Portname + " )";
                    } else {
                        port = "";
                    }

                    $('#PopUpTableFullScreen #key-datatableFullscreen_wrapper > .row > .col-sm-12.col-md-6:first-child')
                        .html('<h4 class="modal-title">On Water Report ' + port + ' </h4>');
                    $('#myModalFullScreen').modal('show');

                    // // $('.modal-title').text('Port container details ');
                    // $("#PopUpTable").html(data);
                    // fnDataTable();
                    // //  showData(data);
                    // $('#myModal').modal('show');

                    // $(".modal-title").hide();
                    // $('#key-datatable_wrapper > .row > .col-sm-12.col-md-6:first-child').html('<h4 class="modal-title">Port container details</h4>');
                    // $(".modal-header").html('<button type="button" class="close" data-dismiss="modal" aria-label="Close">'+
                    //     '<span aria-hidden="true">×</span>'+
                    // '</button>').css("padding","5px 15px");
                    // $(".modal-footer").hide();
                    ;

                }
            });
        }

        $(document).on("click", "#TileID2", function () {
            GetPortWisePoData("", "");
        })
        $(document).on("click", "#TileID4", function () {
            getBuyerWiseData();
        })




        function renderOrderPie(data) {
            var data = JSON.parse(JSON.stringify(data));

            /*var AryPortSeries=data.PortSeries;
            var AryPortLables=data.PortLables;
            var AryPortColors=data.PortColors;
    
            Portoptions={series : AryPortSeries,chart:{height:230,type:"donut"},
            labels : AryPortLables,
            plotOptions:{pie:{donut:{size:"75%"}}},
            dataLabels:{enabled:1},legend:{show:1},
            colors : AryPortColors};
    
            (chart=new ApexCharts(document.querySelector("#donut-chart-port"),Portoptions)).render();
            */

            var dataPoints = data.dataPoints;

            console.log(dataPoints);

            "use strict";
            var chart = new CanvasJS.Chart("orderPie", {
                animationEnabled: true,

                data: [{
                    type: "pie",
                    click: onClick,
                    yValueFormatString: "#,##0.\"\"",
                    indexLabel: "{x} : {y}",
                    toolTipContent: "{x} : {y}",
                    dataPoints: dataPoints
                }]
            });
            chart.render();

            function onClick(e) {
                //	alert(  e.dataPoint.PortID+ ", dataPoint { x:" + e.dataPoint.x + ", y: "+ e.dataPoint.y + " }" ); 
                //  GetPortWisePoData(e.dataPoint.PortID, e.dataPoint.x);
                // GetPortWisePoData("");
                // getBuyerWiseData();
            }



        }


        function renderPortGraph(data) {
            var data = JSON.parse(JSON.stringify(data));

            /*var AryPortSeries=data.PortSeries;
            var AryPortLables=data.PortLables;
            var AryPortColors=data.PortColors;
    
            Portoptions={series : AryPortSeries,chart:{height:230,type:"donut"},
            labels : AryPortLables,
            plotOptions:{pie:{donut:{size:"75%"}}},
            dataLabels:{enabled:1},legend:{show:1},
            colors : AryPortColors};
    
            (chart=new ApexCharts(document.querySelector("#donut-chart-port"),Portoptions)).render();
            */

            var dataPoints = data.dataPoints;

            console.log(dataPoints);

            "use strict";
            var chart = new CanvasJS.Chart("portChart", {
                animationEnabled: true,

                data: [{
                    type: "pie",
                    click: onClick,
                    yValueFormatString: "#,##0.\"\"",
                    indexLabel: "{x} : {y}",
                    toolTipContent: "{x} : {y}",
                    dataPoints: dataPoints
                }]
            });
            chart.render();

            function onClick(e) {
                //	alert(  e.dataPoint.PortID+ ", dataPoint { x:" + e.dataPoint.x + ", y: "+ e.dataPoint.y + " }" ); 
                GetPortWisePoData(e.dataPoint.PortID, e.dataPoint.x);
                // GetPortWisePoData("");
                // getBuyerWiseData();
            }



        }

        $(document).on("click", "#TileID3", function () {
            var year = $("#Year").val();
            var Month = $('#drpMonth').val();
            var exporter = $("#drpExporter").val();
            var consignee = $("#Buyer").val();
            var country = $("#Country").val();
            var Brand = $("#Brand").val();

            $.ajax({
                url: 'Ajax/AjaxGetPendingPaymentsData.php',
                type: 'POST',
                // data:{'EXPORTER':EXPORTER,'YEAR':YEAR,'TYPE':'POLIST'},
                data: {
                    // {"Year":"2023","Exporter":"1","Consignee":"","Country":"","PortID":"823"}
                    // "PortID":PortID,
                    "Year": year,
                    "Exporter": exporter,
                    "Consignee": consignee,
                    "Brand": Brand,
                    "Month": Month,
                    "Country": country

                },
                success: function (data) {

                    $("#PopUpTableFullScreen").html(data);

                    /* Destroy the Datatable if initialize */
                    if ($.fn.dataTable.isDataTable('#key-datatableFullscreen')) {
                        table.destroy();
                    }
                    // { dom: 'lifrtp' }
                    table = $('#key-datatableFullscreen').DataTable();

                    var buttons = new $.fn.dataTable.Buttons(table, {
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
                    }).container().appendTo($('#key-datatableFullscreen_filter'));

                    $('#PopUpTableFullScreen #key-datatableFullscreen_wrapper > .row > .col-sm-12.col-md-6:first-child')
                        .html('<h4 class="modal-title">Pending Payments</h4>');
                    $('#myModalFullScreen').modal('show');

                    // // $('.modal-title').text('Pending Payments');
                    // $("#PopUpTable").html(data);
                    // fnDataTable();
                    // //  showData(data);
                    // $('#myModal').modal('show');

                    // $(".modal-title").hide();
                    // $('#key-datatable_wrapper > .row > .col-sm-12.col-md-6:first-child').html('<h4 class="modal-title">Pending Payments</h4>');
                    // $(".modal-header").html('<button type="button" class="close" data-dismiss="modal" aria-label="Close">'+
                    //     '<span aria-hidden="true">×</span>'+
                    // '</button>').css("padding","5px 15px");
                    // $(".modal-footer").hide();


                    //  $("#key-datatable tbody tr:first-child").children('td').each(function() {
                    //     var w = $(this).text();
                    //     console.log(w + "--");
                    // });

                    var aWidth = [];
                    $("#key-datatable tbody tr:first-child").find("td").each(function () {
                        var td = $(this).width();
                        aWidth.push(td);
                    });
                    $(".dataTables_scrollHeadInner table thead tr th").each(function (i) {
                        $(this).width(aWidth[i]);
                    });

                }
            });
        })

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
        function getBuyerWiseData() {

            var EXPORTER = $('#drpExporter').val();
            var YEAR = $('#Year').val();
            var Buyer = $('#Buyer').val();
            var Country = $('#Country').val();
            var Month = $('#drpMonth').val();
            var Brand = $('#Brand').val();

            $.ajax({
                url: 'Ajax/AjaxBuyerDetailsNew.php',// 'Ajax/AjaxBuyerDetails.php',
                type: 'POST',
                data: {
                    'Year': YEAR,
                    'ExpID': EXPORTER,
                    'ConsigneeID': Buyer,
                    'Month': Month,
                    'CountryID': Country,
                    'Brand': Brand
                },
                success: function (data) {

                    //  $("#PopUpTableFullScreen").html(data);
                    // if ($.fn.dataTable.isDataTable('#key-datatableFullscreen')) {
                    //     table.destroy();
                    // }

                    // table = $('#key-datatableFullscreen').DataTable();

                    // var buttons = new $.fn.dataTable.Buttons(table, {

                    //     buttons: [
                    //         {extend: 'excelHtml5', text: 'Download Excel', className: 'btnExcelDataTable', 	title: 'Orders Report', footer: true,
                    //         exportOptions: {
                    //             columns: ':visible'
                    //         }}
                    //     ]
                    // }).container().appendTo($('#key-datatableFullscreen_filter'));

                    // $('#PopUpTableFullScreen #key-datatableFullscreen_wrapper > .row > .col-sm-12.col-md-6:first-child').html('<h4 class="modal-title">Orders Report </h4>');
                    // $('#myModalFullScreen').modal('show');



                    // $('#myModal').modal('show');

                    // $('#myModal .modal-dialog').removeClass("modal-lg").addClass("modal-fullscreen");
                    // $("#myModal .modal-footer").hide();


                    $("#PopUpTableFullScreen").html(data);
                    if ($.fn.dataTable.isDataTable('#key-datatableFullscreen')) {
                        table.destroy();
                    }
                    // { dom: 'lifrtp' }
                    table = $('#key-datatableFullscreen').DataTable();
                    var buttons = new $.fn.dataTable.Buttons(table, {
                        buttons: [
                            // {extend: 'excelHtml5', text: 'Download Excel', className: 'btnExcelDataTable', 	title: 'Pending Contracts Report', footer: true,
                            // exportOptions: {
                            //     columns: ':visible'
                            // }},
                            {
                                text: 'Download Excel',
                                className: 'btnExcelDataTable',
                                exportOptions: {
                                    columns: ':visible'
                                },
                                action: function () {
                                    // fnPendingOrderPlantWiseExcel();
                                    // fnPendingOrderPlantWiseExcel("", "POLIST");
                                    var excel_data = $('#key-datatableFullscreen').closest("div.col-sm-12").html();
                                    exportTableToExcel('key-datatableFullscreen', 'Orders');
                                }
                            }
                        ]
                    }).container().appendTo($('#key-datatableFullscreen_filter'));

                    $('#PopUpTableFullScreen #key-datatableFullscreen_wrapper > .row > .col-sm-12.col-md-6:first-child')
                        .html('<h4 class="modal-title">Orders</h4>');
                    $('#myModalFullScreen').modal('show');

                    $('<div class="dt-buttons btn-group flex-wrap"><button class="btn btn-secondary buttons-excel btnExcelDataTable" type="button"><span>Download Excel</span></button> </div>')
                        .insertAfter("#PopUpTableFullScreen #key-datatableFullscreen_filter lable");

                }
            });

        }

        /* Need to check */
        // $(document).on('click', '#BuyerDetails', function(e) {
        //     getBuyerWiseData();
        // });



        $(document).on('click', '.btnDeliveredModal', function (e) {

            var EXPORTER = $('#drpExporter').val();
            var Buyer = $('#Buyer').val();
            var Brand = $('#Brand').val();
            var Country = $('#Country').val();
            var YEAR = $('#Year').val();
            var MONTH = $('#drpMonth').val();
            var ConsigneeID = $('#Buyer').val();

            $.ajax({
                url: 'Ajax/AjaxEtaPoList.php',
                type: 'POST',
                data: {
                    "Type": "Dashboard",
                    "Year": YEAR,
                    "Month": MONTH,
                    "Exporter": EXPORTER,
                    "Buyer": Buyer,
                    "Brand": Brand,
                    "Country": Country,
                    "ConsigneeID": ConsigneeID,

                },
                success: function (data) {

                    $("#PopUpTableFullScreen").html(data);
                    if ($.fn.dataTable.isDataTable('#key-datatableFullscreen')) {
                        table.destroy();
                    }

                    table = $('#key-datatableFullscreen').DataTable();

                    var buttons = new $.fn.dataTable.Buttons(table, {

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
                    }).container().appendTo($('#key-datatableFullscreen_filter'));

                    $('#PopUpTableFullScreen #key-datatableFullscreen_wrapper > .row > .col-sm-12.col-md-6:first-child')
                        .html('<h4 class="modal-title">Delivered Report </h4>');
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

            $.ajax({
                url: 'Ajax/AjaxPayReceived.php',//AjaxPayReceivedList.php
                type: 'POST',
                data: {
                    "Type": "Dashboard",
                    "FromDate": YEAR,
                    "Month": MONTH,
                    "Exporter": EXPORTER,
                    "Buyer": Buyer,
                    "Brand": Brand,
                    "Country": Country
                },
                success: function (data) {

                    $("#PopUpTableFullScreen").html(data);
                    if ($.fn.dataTable.isDataTable('#key-datatableFullscreen')) {
                        table.destroy();
                    }

                    table = $('#key-datatableFullscreen').DataTable();

                    var buttons = new $.fn.dataTable.Buttons(table, {

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
                    }).container().appendTo($('#key-datatableFullscreen_filter'));

                    $('#PopUpTableFullScreen #key-datatableFullscreen_wrapper > .row > .col-sm-12.col-md-6:first-child')
                        .html('<h4 class="modal-title">Payments Received Report </h4>');
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
                    name: "Export in Tons",
                    type: "column",
                    data: AryExportKgs
                },
                {
                    name: "Shipment Tons",
                    type: "column",
                    data: AryShippedKgs
                }
                ];
                var Colors = {
                    colors: ["#5664d2", "#3cb371"]
                };
            } else if (chbkExp) {
                var Series = [{
                    name: "Export in Tons",
                    type: "column",
                    data: AryExportKgs
                }];
                var Colors = {
                    colors: ["#5664d2"]
                };
            } else if (chbkShip) {
                var Series = [{
                    name: "Shipment in Tons",
                    type: "column",
                    data: AryShippedKgs
                }];
                var Colors = {
                    colors: ["#3cb371"]
                };
            } else {
                var Series = [{
                    name: "Export in Tons",
                    type: "column",
                    data: AryExportKgs
                },
                {
                    name: "Shipment in Tons",
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
    </script>




</body>

</html>