<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COLDSTORE_DROPDOWNS;
$excludeList = array("Floors", "StoreRacks", "Stock", "Exporters", "Consignee", "Processor", "Shifts", "OptType", "OutType", "Variety", "StockAvailableVariety", "StockInVarietyList", "PoNumber", "GeneralCategories", "Status", "TenantType", "ProductionLines", "Brand", "BrandType", "Species", "Tranction", "Type", "ProductForm", "Preparation", "GlazeType", "ShipoutPoNumber", "ProductionPlant", "TransactionTypesNew", "InwardGroups");
$bodyContent = array(
    'page' => 'outward-report',
    'exclude_list' => $excludeList
);
$resList = callAPI($api, "GET", "", $bodyContent);

$resColdStores = $resList->Data->ColdStores;
$resStockBelongsTo = $resList->Data->StockBelongsTo;
$resPackingType = $resList->Data->PackingType;
$resProductGroup = $resList->Data->ProductGroup;

$filterfromDate = "T";
$filtertoDate = "T";
$filterColdStores = "T";
$filterBlocksTwo = "T";
$filterStockBlgsTo = "T";
$filterPackingType = "T";
$filterOutWardReportGroup = "T";
// $filterInwardGroup = "T";
// $filterPoNumber = "T";
$filterGrade = "T";
$filterPckgStyle = "T";
$filterVarietyList = "T";
$buttomButton = "T";
$filterTypeProduct = "T";
$filterSoaking = "T";


$filterPoNumberNew = "T";
$filterPlant = "T";
$filterPDCategory = "T";
$filterFreezingType = "T";
// $filterStockAvailableVariety = "T";
// $filterInwardGroup = "T";
// $filterSpecies = "T";
$filterAbstatus = "T";
// $filterType = "T";
// $filterClosingDate = "T"; //
// $filterBrand = "T";

// var_dump($ColdStores);
// exit;

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>


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
    //         bSort: true,
    //         bInfo: false
    //     });
    // }


    function fnSuccess(data, afterRenderCallback) {
        $("#divDataList").html(data);

        fnShowDataTable(afterRenderCallback); // Call table setup with callback

        $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
            "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>"
        ).insertAfter("#tblDataTable_filter label");

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    function fnShowDataTable(afterInitCallback) {
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
            buttons: [
                {
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable btn-left-align',
                    title: 'Outward-Report',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'excel',
                    text: 'Download All Excel',
                    className: 'btnExcelDataAll btn-left-align',
                    title: 'Outward-Report',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    },
                    action: function (e, dt, button, config) {
                        ShowListAll(function () {
                            // Delay slightly to ensure DataTable has re-rendered
                            setTimeout(() => {
                                $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, $('#tblDataTable').DataTable(), button, config);
                            }, 200);
                        }.bind(this));
                    }
                }
            ]
        });

        table.on('draw.dt', function () {
            let outMcs = 0;
            let outLooseBags = 0;
            let kgs = 0;
            let withoutNetQty = 0;

            $('.out-mcs').each(function () {
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) outMcs += weight;
            });

            $('.out-loose-bags').each(function () {
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) outLooseBags += weight;
            });

            $('.kgs').each(function () {
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) kgs += weight;
            });

            $('.without-netqty').each(function () {
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) withoutNetQty += weight;
            });

            $('#TotalOutMcs').text(outMcs.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#TotalOutLooseBags').text(outLooseBags.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#TotalKGs').text(kgs.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        });

        // ✅ Call the callback after table is fully initialized
        if (typeof afterInitCallback === "function") {
            afterInitCallback();
        }
    }


    $(document).ready(function () {

        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpColdStore").prop('disabled', true);
            $("#drpPlant").prop('disabled', true);
            $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');
            $("#drpPlant").val(productionPlant).trigger('change');
        } else {
            $("#drpColdStore").prop('disabled', false);
            $("#drpColdStore").val("").trigger('change');
        }

        var blockIds = $("#drpColdStore option:selected").data("blocks");
        console.log('blockIds ' + blockIds);

        if (blockIds && blockIds.length > 0) {
            var blockIdsArray = blockIds.split(',').map(function (id) {
                return id.trim();
            });

            var $drpBlocks = $("#drpBlocks");

            $drpBlocks.find('option').each(function () {
                var optionValue = $(this).val();
                if (optionValue === "" || blockIdsArray.includes(optionValue)) {
                    $(this).show();
                } else {
                    $(this).remove();
                }
            });
        }

        var $drpPoNumber = $("#drpPoNumber");

        $drpPoNumber.find('option').each(function () {
            var optionPlantId = $(this).data('plantid');

            if (productionPlant != 0) {
                var plantIdArray = String(optionPlantId).split(',');

                if (plantIdArray.includes(String(productionPlant)) || $(this).val() === "") {
                    $(this).show();
                } else {
                    $(this).remove();
                }
            } else {
                $(this).show();
            }
        });
        $("#drpPoNumber").on("change", function () {

            var hdnGradeList = JSON.parse($("#hdnGradeList").val());
            var hdnPackingStyleList = JSON.parse($("#hdnPackingStyle").val());

            if ($("#drpPoNumber").val() !== '') {
                // var gradeId = $("#drpPoNumber option:selected").data('grade');
                // var allowedGradeIds = gradeId.split(",");

                // var listGrade = "<option value=''>Select</option>";
                // $.each(hdnGradeList, function(index, val) {
                //     if (allowedGradeIds.indexOf(val.value) !== -1) {
                //         listGrade += '<option value="' + val.value + '">' + val.Options + '</option>';
                //     }
                // });
                // $("#drpGrade").html("").html(listGrade);

                // var packingstyleId = $("#drpPoNumber option:selected").data('packingstyleid').toString();
                // var allowedPackingStyleId = packingstyleId.split(",");

                // var listPackingStyle = "<option value=''>Select</option>";
                // $.each(hdnPackingStyleList, function(index, val) {
                //     if (allowedPackingStyleId.indexOf(val.PCKID) !== -1) {
                //         listPackingStyle += '<option value="' + val.PCKID + '">' + val.PackingStyle + '</option>';
                //     }
                // });
                // $("#drpPckgStyle").html("").html(listPackingStyle);


            } else {
                var listGrade = "<option value=''>Select</option>";
                $.each(hdnGradeList, function (index, val) {
                    listGrade += '<option value="' + val.value + '">' + val.Options + '</option>';
                });
                $("#drpGrade").html("").html(listGrade);

                var listPackingStyle = "<option value=''>Select</option>";
                $.each(hdnPackingStyleList, function (index, val) {
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

    $(document).on("change", "#drpColdStore", function () {
        storeBlocks();
    });

    function storeBlocks() {
        var blocksList = JSON.parse($("#hdnBlocks").val());

        var coldStoreId = $("#drpColdStore");

        var selectBlocks = $(coldStoreId).find(':selected').data("blocks");
        var listBlock = "<option value=''>Select</option>";

        if (selectBlocks) {

            var selectBlocksAry = selectBlocks.split(",");

            $.each(blocksList, function (index, val) {
                if (selectBlocksAry.indexOf(val.BlockID) !== -1) {
                    listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
                }
            });
            $("#drpBlocks").html("").html(listBlock);
        } else {
            $("#drpBlocks").html("").html(listBlock);
        }
    }


    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/ajax-outward-report.php',
            type: 'POST',
            data: {
                //date: $("#txtTransactionDate").val(),
                coldStore: $("#drpColdStore").val(),
                stockBlnTO: $("#drpStockBlgsTo").val(),
                poNumber: $("#drpPoNumber").val(),
                //variety: $("#drpVariety").val(),
                variety: $("#drpVariety option:selected").attr('data-shortname'),
                packingType: $("#drpPackingType").val(),
                packingStyle: $("#drpPckgStyle").val(),
                grade: $.trim($("#drpGrade").val()),
                fromDate: $.trim($("#txtFromDate").val()),
                toDate: $.trim($("#txtToDate").val()),
                group: $("#drpGroup").val(),
                blocks: $("#drpBlocks").val(),
                soaking: $("#drpSoaking").val(),
                typeProduct: $("#drpTypeProduct").val(),
                pDCategory: $("#drpPDCategory").val(),
                freezingType: $("#drpFreezingType").val(),
                abstatus: $("#drpAbstatus").val(),
                pageNo: pageNo,
                searchKey: searchkey,
                loadAll: loadAll,
            },
            success: function (data) {
                fnSuccess(data);
                return false;
            }
        });
    }

    function ShowListAll(callback) {
        $.ajax({
            url: 'Ajax/ajax-outward-report.php',
            type: 'POST',
            data: {
                methodType: "GET",
                coldStore: $("#drpColdStore").val(),
                stockBlnTO: $("#drpStockBlgsTo").val(),
                poNumber: $("#drpPoNumber").val(),
                variety: $("#drpVariety option:selected").attr('data-shortname'),
                packingType: $("#drpPackingType").val(),
                packingStyle: $("#drpPckgStyle").val(),
                grade: $.trim($("#drpGrade").val()),
                fromDate: $.trim($("#txtFromDate").val()),
                toDate: $.trim($("#txtToDate").val()),
                group: $("#drpGroup").val(),
                blocks: $("#drpBlocks").val(),
                soaking: $("#drpSoaking").val(),
                typeProduct: $("#drpTypeProduct").val(),
                pDCategory: $("#drpPDCategory").val(),
                freezingType: $("#drpFreezingType").val(),
                abstatus: $("#drpAbstatus").val(),
                pageNo: '1',
                loadAll: '1',
                searchKey: '',
            },
            success: function (data) {
                fnSuccess(data, callback); // Pass callback forward
            }
        });
    }



    // $(document).ready(function() {
    //     ShowList("1", "", "0");
    // });

    $(document).on("click", "#btnMoreSearch", function () {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", searchkey, "0");
        }
    });

    $(document).on("click", "#btnLoadAll", function () {
        ShowList("1", "", "1");
    });


    $(document).on("click", ".btnExcelDataAll ", function () {
        //  console.log("btnExcelDataAll");
        ShowListAll();
    });

    $(document).on("click", ".btnPageno", function () {
        var pageno = $(this).attr("data-pageno");
        ShowList(pageno, "", "0");
    });

    $("#btnSearch").on("click", function () {
        // ShowList();

        if ($("#txtTransactionDate").val() != "") {
            ShowList("1", "", "0");
        } else {
            alert("Please select Transaction Date");
            $("#txtTransactionDate").focus();
        }
    });
    $("#drpColdStore, #drpPlant").on("change",function(){
        $("#divDataList").html("");
    });
</script>

</body>

</html>
