<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$excludeList = array("Floors","StoreRacks","Stock","ProductGroup","Exporters","Consignee","Processor","Shifts","OptType","OutType","StockInVarietyList","GeneralCategories","Status","TenantType","ProductionLines","BrandType","Tranction","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");

$filterColdStores = "T";
$filterStockBlgsTo = "T";
$filterorderStatus = "T";
$filterShipoutPoNumber = "T";
$filterStockAvailableVariety = "T";
$filterGrade = "T";
$filterSpecies = "T";
$filterTypeProduct = "T";
//$filterfromDate = "T";
$filtertoDate = "T";
$filterStatus = "T";
$filterPckgStyle = "T";
// $filterPackingType = "T";
// $filterPDCategory = "T";
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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>

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
            bInfo: false,
            dom: 'Blfrtip',
            // dom: '<"top-left"Blfrtip>',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Excess Order Report <?php echo date("d-m-Y") ?>',
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

            if (productionPlant != 0) {
                var plantIdArray = String(optionPlantId).split(',');


                // var plantIdArray = String(optionPlantId).split(',');

                if (plantIdArray.includes(String(productionPlant)) || $(this).val() === "") {
                    $(this).show();
                } else {
                    $(this).remove();
                }
            } else {
                $(this).show();
            }
        });

        $("#drpPoNumber").on("change", function() {

            var hdnGradeList = JSON.parse($("#hdnGradeList").val());
            var hdnPackingStyleList = JSON.parse($("#hdnPackingStyle").val());

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
                var listGrade = "<option value=''>Select</option>";
                $.each(hdnGradeList, function(index, val) {
                    listGrade += '<option value="' + val.value + '">' + val.Options + '</option>';
                });
                $("#drpGrade").html("").html(listGrade);

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

    // $("#drpVariety").on("change", function() {
    //     var species = $.trim($(this).find(':selected').data('species'));
    //     var shortName = $.trim($(this).find(':selected').data('shortname'));
    //     var typeProduct = $.trim($(this).find(':selected').data('typeproduct'));

    //     $("#hdnFilterSpecies").val(species);
    //     $("#hdnFilterShortName").val(shortName);
    //     $("#hdnFilterTypeProduct").val(typeProduct);

    // })

    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/ajax-excess-order-report.php',
            type: 'POST',
            data: {
                coldStore: $("#drpColdStore").val(),
                stockBlgsTo: $("#drpStockBlgsTo").val(),
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                poNo: $("#drpPoNumber").val(),
                variety: $("#drpVariety").val(),
                grade: $("#drpGrade").val(),
                pckgStyle: $("#drpPckgStyle").val(),
                pckgType: $("#drpPackingType").val(),
                prodCategory: $("#drpPDCategory").val(),
                fSpecies: $("#drpSpecies").val(),
                status: $("#drpStatus").val(),
                fShortName: $("#hdnFilterShortName").val(),
                fTypeProduct: $("#drpTypeProduct").val(),
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


    $(document).ready(function() {
        getPoNumbers();
        ShowList("1", "", "0");
    });

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
        ShowList("1", "", "0");
    });


    $(document).on("change", "#drpYear", function() {
        getPoNumbers();
        $("#divDataList").html("");
    });

    $(document).on("change", "#drpOrderStatus", function() {
        getPoNumbers();
        $("#divDataList").html("");

    });

    function getPoNumbers() {
        var year = $("#drpYear").val();
        var orderStatus = $("#drpOrderStatus").val();

        $.ajax({
            url: 'Ajax/ajax-order-packing-timeline.php',
            type: 'POST',
            data: {
                methodType: "getPoNumbers",
                year: year,
                orderStatus: orderStatus,
            },
            success: function(data) {
                console.log(data);

                if (data) {
                    var listBlock = "<option value=''>Select</option>";
                    $.each(data, function(index, val) {
                        listBlock += '<option value="' + val.PoID + '" data-plantId="' + val.ProductionPlantID + '">' + val.PoNo + '</option>';
                    });
                    $("#drpPoNumber").html("").html(listBlock);
                }
            }
        });
    }

$("#drpColdStore").on("change", function () {
    $("#divDataList").html("");
});
</script>

</body>

</html>
