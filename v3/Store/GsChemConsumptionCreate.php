<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STORE_DROPDOWN;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resPoNo = $resDropdownsAPI->Data->PoNo;
$resChemical = $resDropdownsAPI->Data->ChemicalName;
$resPlant = $resDropdownsAPI->Data->ProcessingPlants;

$apiList = API_GS_CHEM_CONSUMPTION;

$ChemPoID = trim($_GET['ChemPoID']);

$bodyContent = array(
    "ChemPoID" => $ChemPoID
);

if ($ChemPoID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    $Data = $result->Data[0];
}

$PlantID = $_SESSION['UnitID'];
?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>
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


                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form role="form" method="post" id="frmConsumption">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpPlantName">Plant Name<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpPlantName" id="drpPlantName">
                                                <option value="">All</option>
                                                <?php foreach ($resPlant as $List) { ?>
                                                    <option value="<?php echo $List->ProcessingStationID ?>"><?php echo $List->StationName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpUsageType">Usage Type<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpUsageType" id="drpUsageType">
                                                <option value="">Select</option>
                                                <option value="1" selected>Comsumption</option>
                                                <option value="2">Send To</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpChemName">Chemical Name<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpChemName" id="drpChemName">
                                                <option value="">All</option>
                                                <?php foreach ($resChemical as $List) { ?>
                                                    <option value="<?php echo $List->ChemID ?>" data-chemicalqty="<?php echo $List->NoOfKgPerBag ?>"><?php echo $List->ChemicalName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBags" class="form-label">Bags Quantity<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtBags" name="txtBags" placeholder="Enter Chemical Bags Quantity" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtQty" class="form-label">Quantity In KGs<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtQty" name="txtQty" placeholder="Enter Chemical Quantity in KGs" readonly />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtDate" class="form-label">Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtDate" name="txtDate" placeholder="" min="<?php echo date('Y-m-d', strtotime('-1 day')); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpSendToPlant">Send To<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpSendToPlant" id="drpSendToPlant">
                                                <option value="">Select</option>
                                                <?php foreach ($resPlant as $List) { ?>
                                                    <?php if ($List->ProcessingStationID != $PlantID) { ?>
                                                        <option value="<?php echo $List->ProcessingStationID ?>"><?php echo $List->StationName ?></option>
                                                    <?php } ?>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ChemPoID ?>" />
                                            <input type="hidden" id="hdnNewBrkUp" name="hdnNewBrkUp" />
                                            <a href="gs-chem-consumption-report" class="btn btn-danger">Close</a>
                                            <?php // if ($ChemPoID == '') { 
                                            ?>
                                            <button class="btn btn-primary btnSave" name="submit" id="submit">Save</button>
                                            <?php // } else { 
                                            ?>
                                            <!-- <button class="btn btn-success btnSave" name="submit" id="submit">Update</button> -->
                                            <?php // } 
                                            ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">

                                </div>
                            </div>
                        </div>
                    </div>

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

    <!-- Required datatable js -->
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

    <?php include_once ROOT . '/includes/app-js.php' ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>
    <script>
        var entryID = "<?php echo isset($_GET["EntryID"]) && !empty($_GET["EntryID"]) ? $_GET["EntryID"] : "" ?>";
        var ChemPoID = "<?php echo isset($_GET["ChemPoID"]) && !empty($_GET["ChemPoID"]) ? $_GET["ChemPoID"] : "" ?>";

        function fnShowDataTable() {
            table = $('#tblDataTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                dom: 'Blfrtip',
                buttons: [{
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable',
                    title: 'Chemical Consumption -<?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        function ShowList() {
            $.ajax({
                url: 'Ajax/AjaxGsChemConsumptionCreate.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    chemName: $("#drpChemName").val(),
                    plantID: $("#drpPlantName").val(),
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });
        };

        function getTotalQty() {
            var bagQty = $("#drpChemName option:selected").data("chemicalqty");
            var noofBags = $("#txtBags").val();
            var totalQty = bagQty * noofBags;
            totalQty = totalQty ? totalQty : '';
            $("#txtQty").val(totalQty);
        }

        $(document).on("keyup", "#txtBags", function() {
            getTotalQty();
        });

        $(document).on("change", "#drpChemName", function() {
            getTotalQty();
            ShowList();
        });


        function transferPlantEnable() {
            var usageType = $("#drpUsageType option:selected").val();
            if (usageType == 1) {
                $("#drpSendToPlant").prop('disabled', true);
            } else if (usageType == 2) {
                $("#drpSendToPlant").prop('disabled', false);
            }
        }

        $(document).on("change", "#drpUsageType", function() {
            transferPlantEnable();
        });

        $(document).on('click', '.btnDelete', function() {
            var entryID = $(this).data('id');

            if (confirm('Are you sure you want to delete this record?')) {
                $.ajax({
                    url: 'Ajax/AjaxGsChemConsumptionCreate.php',
                    type: 'POST',
                    data: {
                        methodType: "delete",
                        entryID: entryID,
                        chemName: $("#drpChemName").val(),
                        plantID: $("#drpPlantName").val(),
                    },
                    success: function(data) {
                        $("#divDataList").html(data);
                        ShowList();
                    },
                });
            }
        });

        $(document).ready(function() {

            var productionPlant = <?php echo $PlantID ?>;
            if (productionPlant != 0) {
                $("#drpPlantName").prop('disabled', true);
                $("#drpPlantName").val(productionPlant).trigger('change');
            } else {
                $("#drpPlantName").prop('disabled', false);
                $("#drpPlantName").val("").trigger('change');
            }

            // ShowList();
            transferPlantEnable();
        });

        $("#frmConsumption").validate({

            onkeyup: false,
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    firstInvalidElement.focus();
                }
            },
            ignore: "",
            rules: {
                drpPoNo: {
                    required: true,
                },
                drpChemName: {
                    required: true,
                },
                drpPlantName: {
                    required: true,
                },
                txtBags: {
                    required: true,
                },
                txtQty: {
                    required: true,
                },
                txtDate: {
                    required: true,
                },
                drpUsageType: {
                    required: true,
                },
                drpSendToPlant: {
                    required: true,
                },

            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {

                var selectedChemPoID = $("#drpPoNo").val();

                $.ajax({
                    url: 'Ajax/AjaxGsChemConsumptionCreate.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        entryID: entryID,
                        poNo: $("#drpPoNo").val(),
                        chemName: $("#drpChemName").val(),
                        plantName: $("#drpPlantName").val(),
                        bags: $("#txtBags").val(),
                        qty: $("#txtQty").val(),
                        date: $("#txtDate").val(),
                        usageType: $("#drpUsageType").val(),
                        sendToPlant: $("#drpSendToPlant").val(),
                    },
                    success: function(data) {
                        $("#divDataList").html(data);
                        ShowList();

                        $("#txtBags, #txtQty").val("");
                        $("#drpSendToPlant").val("").trigger('change');
                        return false;
                    }
                });
                return false;
            }
        });

        // Initialize DataTable
        // fnShowDataTable();
    </script>
    </body>

</html>