<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COLDSTORE_DROPDOWNS;

$resList = callAPI($api, "GET", "", "");

$resColdStores = $resList->Data->ColdStores;
$resStockBelongsTo = $resList->Data->StockBelongsTo;
$resPackingType = $resList->Data->PackingType;
$resProductGroup = $resList->Data->ProductGroup;



$filterTransactionDate = "T";
$filterColdStores = "T";
$filterStockBlgsTo = "T";
$filterPackingType = "T";
$filterPckgStyle = "T";
$filterOutWardGroup = "T";
$filterVarietyList = "T";
$filterPoNumber = "T";
$buttomButton = "T";

$ProductionPlant = $_SESSION['UnitID'];

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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

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
    // function fnShowDataTable() {
    //     var table = $('#tblDataTable').DataTable({
    //         scrollY: "600px",
    //         scrollX: true,
    //         sScrollXInner: "100%",
    //         paging: false,
    //         destroy: true,
    //         fixedColumns: true,
    //         scrollCollapse: true,
    //         bSort: false,
    //         bInfo: false
    //     });
    // }

    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bSort: true,
            bInfo: false,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Daily_Out_Report',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],

        });
    }

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    $(document).ready(function() {

        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpColdStore").prop('disabled', true);
            $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');

        } else {
            $("#drpColdStore").prop('disabled', false);
            $("#drpColdStore").val("").trigger('change');
        }

        var $drpPoNumber = $("#drpPoNumber");

        $drpPoNumber.find('option').each(function() {
            var optionPlantId = $(this).data('plantid');

            var plantIdArray = String(optionPlantId).split(',');

            if (plantIdArray.includes(String(productionPlant)) || $(this).val() === "") {
                $(this).show();
            } else {
                $(this).remove();
            }
        });

        $("#drpPoNumber").on("change", function() {

            var hdnPackingStyleList = JSON.parse($("#hdnPackingStyle").val());

            if ($("#drpPoNumber").val() !== '') {

                var packingstyleId = $("#drpPoNumber option:selected").data('packingstyleid').toString();
                var allowedPackingStyleId = packingstyleId.split(",");

                var listPackingStyle = "<option value=''>Select</option>";
                $.each(hdnPackingStyleList, function(index, val) {
                    if (allowedPackingStyleId.indexOf(val.PCKID) !== -1) {
                        listPackingStyle += '<option value="' + val.PCKID + '">' + val.PackingStyle + '</option>';
                    }
                });
                $("#drpPckgStyle").html("").html(listPackingStyle);

            } else {
                var listPackingStyle = "<option value=''>Select</option>";
                $.each(hdnPackingStyleList, function(index, val) {
                    listPackingStyle += '<option value="' + val.PCKID + '">' + val.PackingStyle + '</option>';
                });
                $("#drpPckgStyle").html("").html(listPackingStyle);
            }
        });

        if (productionPlant != 0) {
            console.log('productionPlant' + productionPlant);
            $("#drpStockBlgsTo").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
        } else {
            $("#drpStockBlgsTo").val("").trigger('change');
        }
    });

    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/ajax-daily-outward.php',
            type: 'POST',
            data: {
                date: $("#txtTransactionDate").val(),
                coldStore: $("#drpColdStore").val(),
                stockBlnTO: $("#drpStockBlgsTo").val(),
                poNumber: $("#drpPoNumber").val(),
                //variety: $("#drpVariety").val(),
                variety: $("#drpVariety option:selected").attr('data-shortname'),
                packingType: $("#drpPackingType").val(),
                packingStyle: $("#drpPckgStyle").val(),
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

    $("#btnSearch").on("click", function() {
        // ShowList();

        if ($("#txtTransactionDate").val() != "") {
            ShowList("1", "", "0");
        } else {
            alert("Please select Transaction Date");
            $("#txtTransactionDate").focus();
        }
    })
</script>

</body>

</html>