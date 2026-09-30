<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$filterColdStores = "T"; // drpColdStore
$filterMonth = "T"; // drpmonth
$filterYear = "T"; // drpYear

$filterSideButton = "T";


?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
        /* .select2-container--open {
                z-index: 9999999
            }             */
    </style>

</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . '/includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . '/includes/page-header.php' ?>
                <?php include_once WORKING_MODULE . '/includes/page-filter.php'; ?>

                <!-- start page title -->
                <!-- Inward and OutWard -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Daily Inward Vs OutWard (Kgs)</h4>
                                <div>
                                    <div id="line-column-chart" class="apex-charts" dir="ltr"></div>
                                </div>
                            </div>

                            <div class="card-body border-top text-center">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="mt-4 mt-sm-0">
                                            <p class="mb-2 text-muted text-truncate">
                                                <input type="checkbox" id="chbkExp" <?php if ($chbkExp != 'false') { ?> checked <?php } ?>>
                                                Inward- <?php echo $TotalOrders ?> Kgs
                                            </p>
                                            <div class="d-inline-flex">
                                                <h5 class="mb-0 mr-2"></h5>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-4">
                                        <div class="mt-4 mt-sm-0">
                                            <p class="mb-2 text-muted text-truncate">
                                                <input type="checkbox" id="chbkShip" <?php if ($chbkShip != 'false') { ?> checked <?php } ?>>
                                                OutWard - <?php echo $TotalShipments ?> Kgs
                                            </p>
                                            <div class="d-inline-flex">
                                                <h5 class="mb-0 mr-2"></h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Inward and OutWard -->



                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                <?php include_once ROOT . '/includes/footer.php' ?>

            </div>
            <!-- end main content-->
            <!-- Modal -->

        </div>
        <!-- END layout-wrapper -->




        <?php include_once ROOT . '/includes/common-js.php' ?>
        </script>

        <!-- Required datatable js -->
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

        <?php include_once ROOT . '/includes/app-js.php' ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>
        <script src="https://cybercoast.in/assetsv2/assets/libs/apexcharts/apexcharts.min.js"></script>


        <script>
            $(document).ready(function() {
                ShowList();
                // InOutWard();
            });


            function ShowList() {

                $.ajax({
                    url: 'Ajax/ajax-index.php',
                    type: 'POST',
                    data: {
                        coldStore: $.trim($("#drpColdStore").val()),
                        month: $.trim($("#drpmonth").val()),
                        year: $.trim($("#drpYear").val()),
                    },
                    success: function(data) {

                        globalData = data;

                        // InOutWard(globalData);

                        const chbkExp = true; 
                        const chbkShip = true; 
                        
                        updateGraph(chbkExp, chbkShip, data);

                        return false;
                    }
                });
            }

            // Inward and Outward

            // $(document).on('click', '#chbkExp', function(e) {
            //     var chbkExp = $("#chbkExp").is(':checked');
            //     var chbkShip = $("#chbkShip").is(':checked');
            //     var SelectedDate = '<?php echo $PresentYear ?>';

            //     updateGraph(chbkExp, chbkShip);
            // });

            // $(document).on('click', '#chbkShip', function(e) {
            //     var chbkExp = $("#chbkExp").is(':checked');
            //     var chbkShip = $("#chbkShip").is(':checked');
            //     var SelectedDate = '<?php echo $PresentYear ?>';

            //     updateGraph(chbkExp, chbkShip);
            // });

            function updateGraph(chbkExp, chbkShip, data) {

                console.log(data.InOutDetails);

                var InOutData = data.InOutDetails.InOutData;
                var InOutData = data.InOutDetails.InOutData;

                // if (chbkExp == true && chbkShip == true) {
                //     var Series = [{
                //             name: "Inward",
                //             type: "column",
                //             data: AryOrders
                //         },
                //         {
                //             name: "OutWard",
                //             type: "column",
                //             data: AryShipMents
                //         }
                //     ];
                //     var Colors = {
                //         colors: ["#5664d2", "#ff0000"]
                //     };
                // } else if (chbkExp) {
                //     var Series = [{
                //         name: "Inward",
                //         type: "column",
                //         data: AryOrders
                //     }];
                //     var Colors = {
                //         colors: ["#5664d2"]
                //     };
                // } else if (chbkShip) {
                //     var Series = [{
                //         name: "OutWard",
                //         type: "column",
                //         data: AryShipMents
                //     }];
                //     var Colors = {
                //         colors: ["#ff0000"]
                //     };
                // } else {
                //     var Series = [{
                //             name: "Inward",
                //             type: "column",
                //             data: AryOrders
                //         },
                //         {
                //             name: "OutWard",
                //             type: "column",
                //             data: AryShipMents
                //         }
                //     ];
                //     var Colors = {
                //         colors: ["#5664d2", "#ff0000"]
                //     };
                // }

                // chart.updateSeries(Series);
                // chart.updateOptions(Colors);
            }
            // Inward and Outward

           
        </script>

        </body>

</html>