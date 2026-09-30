<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$PageTitle = "Daily StockOut";

$api = API_COLDSTORE_DROPDOWNS;
$excludeList = array("ColdStores","Blocks","Floors","StoreRacks","Stock","Exporters","Consignee","Processor","Shifts","FreezingType","Soaking","ProductCategory","Abstatus","OptType","OutType","Grade","Variety","VarietyList","StockAvailableVariety","StockInVarietyList","PoNumber","PackingStyle","Plant","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Species","Tranction","TypeProduct","Type","CsTransactionTypes","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");
$bodyContent['exclude_list'] = $excludeList ? $excludeList : [];
$resList = callAPI($api, "GET", "", $bodyContent);

$resColdStores = $resList->Data->ColdStores;
$resStockBelongsTo = $resList->Data->StockBelongsTo;
$resPackingType = $resList->Data->PackingType;
$resProductGroup = $resList->Data->ProductGroup;

$TodayDate = date('Y-m-d');

$ProductionPlant = $_SESSION['UnitID'];

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
                <?php // include_once WORKING_MODULE . '/includes/page-filter.php' 
                ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form role="form" method="post" id="frmDailyStockoutDetailsList">
                                    <div class="row">

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtStockOutDate">Stock Out Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtStockOutDate" name="txtStockOutDate" max="<?php echo $TodayDate; ?>" value="<?php echo $TodayDate; ?>">
                                        </div>

                                        <!-- <div class="form-group col-sm-3 mb-3">
                                            <label for="drpColdStores">Cold Stores</label>
                                            <select class="drpSelect" name="drpColdStores" id="drpColdStores">
                                                <option value=""> Select</option>
                                                <?php foreach ($resColdStores as $Item) { ?>
                                                    <option value="<?php echo $Item->StoreID ?>">
                                                        <?php echo $Item->StoreName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div> -->

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpStockBlnTo">Stock Belongs to</label>
                                            <select class="drpSelect" name="drpStockBlnTo" id="drpStockBlnTo">
                                                <option value=""> Select</option>
                                                <?php foreach ($resStockBelongsTo as $Item) { ?>
                                                    <option value="<?php echo $Item->TenantID ?>" data-processorID="<?php echo $Item->ProcessorID; ?>">
                                                        <?php echo $Item->TenantName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpPackingType">Packing Type</label>
                                            <select class="drpSelect" name="drpPackingType" id="drpPackingType">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPackingType as $Item) { ?>
                                                    <option value="<?php echo $Item->Name ?>">
                                                        <?php echo $Item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpGroup">Group</label>
                                            <select class="drpSelect" name="drpGroup" id="drpGroup">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductGroup as $Item) { ?>
                                                    <option value="<?php echo $Item->Name ?>">
                                                        <?php echo $Item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <!-- <div class="col-md-3 mb-2">
                                            <label for="drpStatus">Status</label>
                                            <select class="drpSelect form-control" name="drpStatus" id="drpStatus">
                                                <option value="0">Pending</option>
                                                <option value="1">Completed</option>
                                            </select>
                                        </div> -->

                                        <div align="center" class="col-lg-12">
                                            <button id="btnSearch" align="left" type="submit" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                            <!-- <a href="daily-production-data.php" class="btn btn-success waves-effect waves-light"><i class="fa fa-plus"></i> Add</a> -->


                                        </div>

                                    </div>
                                </form>
                                <!-- <input type="hidden" id="hdnActionType" /> -->
                            </div>

                        </div>
                        <div id="divDataList">
                            <!-- -->
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
    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bInfo: false,
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
        });
    }

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
                "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>"
            )
            .insertAfter("#tblDataTable_filter label");

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    function ShowList(pageNo, searchkey, loadAll) {
        var productionPlant = <?php echo $ProductionPlant ?>;
        $.ajax({
            url: 'Ajax/ajax-daily-stockout-details-list.php',
            type: 'POST',
            data: {
                date: $("#txtStockOutDate").val(),
                coldStore: $("#drpColdStores").val(),
                stockBlnTO: $("#drpStockBlnTo").val(),
                packingType: $("#drpPackingType").val(),
                group: $("#drpGroup").val(),
                productionPlant: productionPlant,
                //status: $("#drpStatus").val(),
                PageNo: pageNo,
                SearchKey: searchkey,
                LoadAll: loadAll,
            },
            success: function(data) {
                fnSuccess(data);
                return false;
            }
        });
    }

    // $("#btnSearch").on("click", function() {
    //   ShowList();
    // });

    $(document).ready(function() {
        // ShowList("1", "", "0");
        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            console.log('productionPlant' + productionPlant);
            $("#drpStockBlnTo").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
            $("#drpStockBlnTo").prop("disabled", true);
        } else {
            $("#drpStockBlnTo").val("").trigger('change');
            $("#drpStockBlnTo").prop("disabled", false);
        }
    });

    // $(document).on("click", "#btnMoreSearch", function() {
    //     var searchkey = $("#tblDataTable_filter input").val();
    //     if (searchkey.length > 0) {
    //         ShowList("1", searchkey, "0");
    //     }
    // });

    // $(document).on("click", "#btnLoadAll", function() {
    //     ShowList("1", "", "1");
    // });

    // $(document).on("click", ".btnPageno", function() {
    //     var pageno = $(this).attr("data-pageno");
    //     ShowList(pageno, "", "0");
    // });
    jQuery.validator.addMethod(
        "noSpace",
        function(value, element) {
            return value == "" || value.trim().length != 0;
        },
        "No space please and don't leave it empty"
    );
    $("#frmDailyStockoutDetailsList").validate({
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
            txtStockOutDate: {
                required: true
            }
        },
        errorPlacement: function() {
            return false; // suppresses error message text
        },
        submitHandler: function() {
            ShowList();
            return false;
        }
    });
</script>

</body>

</html>
