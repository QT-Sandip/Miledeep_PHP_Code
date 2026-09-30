<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COLDSTORE_DROPDOWNS;

$resList = callAPI($api, "GET", "", "");

$resColdStores = $resList->Data->ColdStores;
$resStockBelongsTo = $resList->Data->StockBelongsTo;
$resPackingType = $resList->Data->PackingType;
$resProductGroup = $resList->Data->ProductGroup;
$resCsTransactionTypes = $resList->Data->CsTransactionTypes;

// echo json_encode($resCsTransactionTypes, JSON_PRETTY_PRINT);


$TodayDate = date('Y-m-d');

// var_dump($ColdStores);
// exit;

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
                                <form role="form" method="post" id="frmDailyProdList">
                                    <div class="row">

                                        <div class="col-md-3 mb-2">
                                            <label for="txtProductionDate">Production Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtProductionDate" name="txtProductionDate" max="<?php echo $TodayDate; ?>" value="<?php echo $TodayDate; ?>">
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

                                        <div class="col-md-3 mb-2">
                                            <label for="drpStockBlnTo">Stock Belongs to</label>
                                            <select class="drpSelect form-control" name="drpStockBlnTo" id="drpStockBlnTo">
                                                <option value=""> Select</option>
                                                <?php foreach ($resStockBelongsTo as $Item) { ?>
                                                    <option value="<?php echo $Item->TenantID ?>">
                                                        <?php echo $Item->TenantName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label for="drpPackingType">Packing Type</label>
                                            <select class="drpSelect form-control" name="drpPackingType" id="drpPackingType">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPackingType as $Item) { ?>
                                                    <option value="<?php echo $Item->Name ?>">
                                                        <?php echo $Item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label for="drpGroup">Group</label>
                                            <select class="drpSelect form-control" name="drpGroup" id="drpGroup">
                                                <option value=""> Select</option>
                                                <?php 
                                                 foreach ($resCsTransactionTypes as $item) { 
                                                     if ($item->TransType == 1) { 
                                                        echo '<option value="'.$item->TransCategory.'">'.$item->TransCategory.' </option>';
                                                     } 
                                                 } 
                                                 ?>
                                            </select>
                                        </div>

                                        <div class="col-lg-12 text-center">
                                            <button id="btnSearch" type="submit" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                            

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
            bInfo: false
        });
    }

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        // $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
        //     "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>"
        // ).insertAfter("#tblDataTable_filter label");

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    $(document).ready(function() {
        ShowList();
    })

    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/ajax-admin-daily-production-list.php',
            type: 'POST',
            data: {
                date: $("#txtProductionDate").val(),
                coldStore: $("#drpColdStores").val(),
                stockBlnTO: $("#drpStockBlnTo").val(),
                packingType: $("#drpPackingType").val(),
                group: $("#drpGroup").val(),
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
    //     ShowList();
    // });

    $(document).on("click", ".btnDelete", function() {
        if (confirm('Are you sure want to delete?')) {

            $.ajax({
                url: 'Ajax/ajax-admin-daily-production-list.php',
                type: 'POST',
                data: {
                    methodType: "delete",
                    ProductionID: $(this).attr("data-prodid"),
                    ProductionDate: $("#txtProductionDate").val()
                },
                success: function(data) {

                    $("#divDataList").html(data);
                    fnShowDataTable();

                    alert($(document).find("#hdnAPIRes").val());

                    if ($(document).find("#hdnAPIRes").attr("data-restype") == "error") {
                        $("#btnSearch").trigger('click');
                    }

                    return false;
                }
            });
        }

    });

    // $(document).ready(function() {
    //     ShowList("1", "", "0");
    // });

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
        $("#frmDailyProdList").validate({
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
                txtProductionDate: {
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