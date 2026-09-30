<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COLDSTORE_DROPDOWNS;

$resList = callAPI($api, "GET", "", "");

$resColdStores = $resList->Data->ColdStores;
$resStockBelongsTo = $resList->Data->StockBelongsTo;
$resPackingType = $resList->Data->PackingType;
$resProductGroup = $resList->Data->ProductGroup;


// $filterTransactionDate = "T";
$filterGrade = "T";
$filterfromDate = "T";
$filtertoDate = "T";
$filterColdStores = "T";
$filterStockBlgsTo = "T";
$filterPackingType = "T";
$filterPckgStyle = "T";
$filterInwardGroup = "T";
$filterVarietyList = "T";
$filterPoNumber = "T";
$buttomButton = "T";


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
                <?php  include_once WORKING_MODULE . '/includes/page-filter.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <!-- <div class="card">
                            <div class="card-body">
                                <form role="form" method="post" id="frmDailyInward">
                                    <div class="row">

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTransactionDate">Transaction Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtTransactionDate" name="txtTransactionDate" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpColdStores">Cold Stores</label>
                                            <select class="drpSelect form-control" name="drpColdStores" id="drpColdStores">
                                                <option value=""> Select</option>
                                                <?php foreach ($resColdStores as $Item) { ?>
                                                    <option value="<?php echo $Item->StoreID ?>">
                                                        <?php echo $Item->StoreName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
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

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpPackingType">Packing Type</label>
                                            <select class="drpSelect form-control" name="drpPackingType" id="drpPackingType">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPackingType as $Item) { ?>
                                                    <option value="<?php echo $Item->OptID ?>">
                                                        <?php echo $Item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpGroup">Group</label>
                                            <select class="drpSelect form-control" name="drpGroup" id="drpGroup">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductGroup as $Item) { ?>
                                                    <option value="<?php echo $Item->OptID ?>">
                                                        <?php echo $Item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div  class="col-sm-3 mb-3">
                                            <label class="invisible" style="width: 100%;" for="drpGroup">Label</label>
                                            <button id="btnSearch"  type="submit" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                        </div>

                                    </div>
                                </form>

                            </div>

                        </div> -->
                        
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
            bSort: false,
            bInfo: false
        });
    }

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        // $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
        //         "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>"
        //     )
        //     .insertAfter("#tblDataTable_filter label");

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/ajax-daily-inward.php',
            type: 'POST',
            data: {
                //date: $("#txtTransactionDate").val(),
                coldStore: $("#drpColdStore").val(),  // drpColdStore
                stockBlnTO: $("#drpStockBlgsTo").val(),
                poNumber : $.trim($("#drpPoNumber").val()),                // variety: $("#drpVariety").val(),
                variety: $("#drpVariety option:selected").attr('data-shortname'),
                packingType: $("#drpPackingType").val(),
                packingStyle: $("#drpPckgStyle").val(),
                grade: $.trim($("#drpGrade").val()),
                fromDate: $.trim($("#txtFromDate").val()),
                toDate: $.trim($("#txtToDate").val()),
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

    $("#btnSearch").on("click", function() {
       
        if($("#txtTransactionDate").val() != "") {
            ShowList("1", "", "0");
        } else{
            alert("Please select Transaction Date");
            $("#txtTransactionDate").focus();
        }
    });

    // jQuery.validator.addMethod(
    //         "noSpace",
    //         function(value, element) {
    //             return value == "" || value.trim().length != 0;
    //         },
    //         "No space please and don't leave it empty"
    //     );
    //     $("#frmDailyInward").validate({

    //         onkeyup: false,
    //         onfocusout: false,
    //         invalidHandler: function(form, validator) {
    //             var errors = validator.numberOfInvalids();
    //             if (errors) {
    //                 var firstInvalidElement = $(validator.errorList[0].element);
    //                 firstInvalidElement.focus();
    //             }
    //         },
    //         ignore: "",
    //         rules: {
    //             txtTransactionDate: {
    //                 required: true,
    //                 // noSpace: true
    //             }               
    //         },
    //         errorPlacement: function() {
    //             return false; // suppresses error message text
    //         },
    //         submitHandler: function() {

    //             ShowList("1", "", "0");
    //             // ShowList();

    //             // $.ajax({
    //             //     url: 'Ajax/ajax-cs-create-floor.php',
    //             //     type: 'POST',
    //             //     data: {
    //             //         // methodType: "post",
    //             //         floorID: floorID,
    //             //         floorName: $("#txtFloorName").val(),
    //             //         status: $("#drpStatus").val(),
    //             //     },
    //             //     success: function(data) {
    //             //         if (data.Error != '') {
    //             //             alert(data.Msg);
    //             //             window.location.href = "cs-floor-list.php";
    //             //         } else {
    //             //             alert(data.Error);
    //             //         }
    //             //         // $("#divDataList").html(data);
    //             //         // fnShowDataTable();
    //             //         return false;
    //             //     }
    //             // });

    //             return false;
    //         }
    //     });

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
</script>

</body>

</html>