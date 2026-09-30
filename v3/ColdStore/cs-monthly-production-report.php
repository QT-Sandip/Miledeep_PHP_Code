<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$filterColdStores = "T"; 
$filterStockBlgsTo = "T"; 
$filterBlocksTwo = "T";
// $filterVarietyList = "T";  
$filterStockAvailableVariety = "T";  
$filterPackingType = "T";  
$filterFreezingType = "T";  
$filterPoNumber = "T";  
$filterfromDate = "T";
$filterTypeProduct = "T";
$filterTransaction = "T";
$filterClosingDate = "T";
$filterBrand = "T";
$filterSpecies = "T";
$filterType = "T";
$filterGrade = "T";
$filterGroup = "T";  

$buttomButton = "T";

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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php'; ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">

                                <div id="divDataList">

                                </div>
                                <input type="hidden" id="hdnActionType" />
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
            dom: 'Blfrtip',
            // dom: '<"top-left"Blfrtip>',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'MonthlyProductionReport <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
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

            var hdnGradeList = JSON.parse($("#hdnGradeList").val());

            if ($("#drpPoNumber").val() !== '') {
                var gradeId = $("#drpPoNumber option:selected").data('grade');
                var allowedGradeIds = gradeId.split(",");

                var listGrade = "<option value=''>Select</option>";
                $.each(hdnGradeList, function(index, val) {
                    if (allowedGradeIds.indexOf(val.value) !== -1) {
                        listGrade += '<option value="' + val.value + '">' + val.Options + '</option>';
                    }
                });
                $("#drpGrade").html("").html(listGrade);

            } else {
                var listGrade = "<option value=''>Select</option>";
                $.each(hdnGradeList, function(index, val) {
                    listGrade += '<option value="' + val.value + '">' + val.Options + '</option>';
                });
                $("#drpGrade").html("").html(listGrade);
            }
        });

        if (productionPlant != 0) {
            console.log('productionPlant' + productionPlant);
            $("#drpStockBlgsTo").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
        } else {
            $("#drpStockBlgsTo").val("").trigger('change');
        }
    });

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

    $(document).on("change", "#drpColdStore", function() {
        storeBlocks();
    });

    function storeBlocks() {
        var blocksList = JSON.parse($("#hdnBlocks").val());
        console.log(blocksList);
        var coldStoreId = $("#drpColdStore");

        var selectBlocks = $(coldStoreId).find(':selected').data("blocks");

        if (selectBlocks) {
            var selectBlocksAry = selectBlocks.split(",");

            var listBlock = "<option value=''>Select</option>";
            $.each(blocksList, function(index, val) {
                if (selectBlocksAry.indexOf(val.BlockID) !== -1) {
                    listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
                }
            });
            $("#drpBlocks").html("").html(listBlock);
        } else {
            $("#drpBlocks").html("<option value=''>Select</option>");
        }
    }


    function ShowList(pageNo, searchkey, loadAll) {

        $.ajax({
            url: 'Ajax/ajax-monthly-production-report.php',
            type: 'POST',
            data: {
                coldStore: $.trim($("#drpColdStore").val()),
                stockBlgsTo: $.trim($("#drpStockBlgsTo").val()),
                blocksTwo: $.trim($("#drpBlocks").val()),
                variety: $("#drpVariety option:selected").attr('data-shortname'),
                packingType: $.trim($("#drpPackingType").val()),
                pdCategory: $.trim($("#drpPDCategory").val()),
                productType: $.trim($("#drpTypeProduct").val()),
                species: $.trim($("#drpSpecies").val()),
                transaction: $.trim($("#drpTransaction").val()),
                group: $.trim($("#drpGroup").val()),
                groupType: $("#drpGroup option:selected").attr("data-transtype"),
                freezingType: $.trim($("#drpFreezingType").val()),
                soaking: $.trim($("#drpSoaking").val()),
                abStatus: $.trim($("#drpAbstatus").val()),
                poNumber: $.trim($("#drpPoNumber").val()),
                Type: $.trim($("#drpType").val()),
                fromDate: $.trim($("#txtFromDate").val()),
                closingDate: $.trim($("#txtClosingDate").val()),
                brand: $.trim($("#drpBrand").val()),
                pageNo: pageNo,
                searchKey: searchkey,
                loadAll: loadAll,
            },
            success: function(data) {
                fnSuccess(data);
                return false;
            }
        });

    }

    $(document).on("click", "#btnMoreSearch", function() {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", searchkey, "0");
        }
    });

    $(document).on("click", "#btnLoadAll", function() {
        ShowList("1", "", "1");
    });

    $(document).on("click", ".btnPageno", function() {
        var pageno = $(this).attr("data-pageno");
        ShowList(pageno, "", "0");
    });

    $("#btnSearch").on("click", function() {
        var group = $("#drpGroup").val();
        if (group == '') {
            alert("Group Not found");
        } else {
            ShowList("1", "", "0");
        }
    });
</script>

</body>

</html>