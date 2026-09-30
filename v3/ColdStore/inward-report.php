<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COLDSTORE_DROPDOWNS;
$excludeList = array("Floors","StoreRacks","Stock","ProductGroup","Exporters","Consignee","Processor","Shifts","OptType","OutType","Variety","StockAvailableVariety","StockInVarietyList","PoNumber","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Species","Tranction","Type","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","TransactionTypesNew","InwardGroups");
$bodyContent = array(
    'page'=>'inward-report',
    'exclude_list'=>$excludeList
);
$resList = callAPI($api, "GET", "", $bodyContent);
$resColdStores = $resList->Data->ColdStores;
$resStockBelongsTo = $resList->Data->StockBelongsTo;
$resPackingType = $resList->Data->PackingType;
$resProductGroup = $resList->Data->ProductGroup;

$filterfromDate = "T";
$filtertoDate = "T";
$filterPlant = "T";
$filterColdStores = "T";
$filterStockBlgsTo = "T";
$filterBlocksTwo = "T";

$filterPackingType = "T";
$filterInwardGroup = "T";
// $filterPoNumber = "T";
$filterPoNumberNew = "T";
$filterGrade = "T";
$filterPckgStyle = "T";
$filterVarietyList = "T";
$buttomButton = "T";
$filterTypeProduct = "T";
$filterSoaking = "T";



// $filterStockAvailableVariety = "T";
$filterPDCategory = "T";
$filterFreezingType = "T";
// $filterSpecies = "T";
$filterAbstatus = "T";
// $filterType = "T";
// $filterClosingDate = "T"; //
// $filterBrand = "T";

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>




<script>
    function fnShowDataTable() {
        var FromDate = FormattedDate($("#txtFromDate").val());
        var ToDate = FormattedDate($("#txtToDate").val());
        var DatePeriod = ' StartDate ' + ' ' + FromDate + ' EndDate ' + ToDate;
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
            
            // dom: '<"top-left"Blfrtip>',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Inward-Report'+ DatePeriod,
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],

        });

     table.on('draw.dt', function () {
        let mcs = 0;
        let loosebags = 0;
        let kgs = 0;
       
        $('.mcs').each(function () {
            const weight = parseFloat($(this).text().replace(/,/g, ''));
            if (!isNaN(weight)) {
            mcs += weight;
            }
        });

        $('.loose-bags').each(function () {
            const weight = parseFloat($(this).text().replace(/,/g, ''));
            if (!isNaN(weight)) {
            loosebags += weight;
            }
        });

        $('.kgs').each(function () {
            const weight = parseFloat($(this).text().replace(/,/g, ''));
            if (!isNaN(weight)) {
            kgs += weight;
            }
        });

        // Update the footer with correctly formatted totals
        $('#TotalMcs').text(mcs.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));

        $('#TotalLooseBags').text(loosebags.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));

        $('#TotalKGs').text(kgs.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
    });


    }

    $(document).ready(function() {
        $("#drpGroup option").each(function () {
            if ($(this).text().trim() === "Repacking Out") {
                $(this).text("Repacking In");
            }
        });
        $("#drpGroup").trigger("change.select2");

        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpColdStore").prop('disabled', true);
            $("#drpPlant").prop('disabled', true);
            $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');
            $("#drpPlant").val(productionPlant).trigger('change');

        } else {
            $("#drpColdStore").prop('disabled', false);
            $("#drpPlant").prop('disabled', false);
            $("#drpColdStore").val("").trigger('change');
            $("#drpPlant").val("").trigger('change');
        }

        var $drpPoNumber = $("#drpPoNumber");

        $drpPoNumber.find('option').each(function() {
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

        $("#drpPoNumber").on("change", function() {

            var hdnGradeList = JSON.parse($("#hdnGradeList").val());
            var hdnPackingStyleList = JSON.parse($("#hdnPackingStyle").val());
            console.log($("#drpPoNumber").val());

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

        var productionPlant = <?php echo $ProductionPlant ?>;

        if (productionPlant != 0) {
            console.log('productionPlant' + productionPlant);
            $("#drpStockBlgsTo").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
        } else {
            $("#drpStockBlgsTo").val("").trigger('change');
        }
    });

    $(document).on("change", "#drpColdStore", function() {
        storeBlocks();
    });

    function storeBlocks() {
        var blocksList = JSON.parse($("#hdnBlocks").val());

        var coldStoreId = $("#drpColdStore");

        var selectBlocks = $(coldStoreId).find(':selected').data("blocks");
        var listBlock = "<option value=''>Select</option>";

        if (selectBlocks) {

            var selectBlocksAry = selectBlocks.split(",");


            $.each(blocksList, function(index, val) {
                if (selectBlocksAry.indexOf(val.BlockID) !== -1) {
                    listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
                }
            });
            $("#drpBlocks").html("").html(listBlock);
        } else {
            $("#drpBlocks").html("").html(listBlock);
        }
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
      
    //   debugger
        var drpcoldStore = $("#drpColdStore").val();
        var drpstockbelongsto =$("#drpStockBlgsTo").val();
        var drppoNo =$.trim($("#drpPoNumber").val());
        var selectedOption = $("#drpVariety option:selected");
        var drpvariety = selectedOption.length && selectedOption.attr('data-shortname') ? selectedOption.attr('data-shortname') : '';
        var drppackingType= $("#drpPackingType").val();
        var drppackingStyle= $("#drpPckgStyle").val();
        var  drpgrade= $.trim($("#drpGrade").val());
        var drpfromDate= $.trim($("#txtFromDate").val());
        var drptoDate= $.trim($("#txtToDate").val());
        var drpgroup=$("#drpGroup").val();
        var drpblocks= $("#drpBlocks").val();
        var drpsoaking= $("#drpSoaking").val();
        var drptypeProduct= $("#drpTypeProduct").val();
        var drppDCategory=$("#drpPDCategory").val();
        var drpfreezingType= $("#drpFreezingType").val();
        var drpabstatus= $("#drpAbstatus").val();

       
    var fromDate = new Date(drpfromDate);
    var toDate = new Date(drptoDate);

    var timeDiff = toDate - fromDate;
    var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;

        var days = daysDiff + 1;

        // debugger
        if (days > 31) {
            var url = 'Ajax/ajax-inward-report.php?coldStore='+ drpcoldStore + '&stockbelongsto=' + drpstockbelongsto + '&poNo=' + drppoNo + '&variety=' + drpvariety +
             '&packingType=' + drppackingType + '&packingStyle=' + drppackingStyle +'&grade=' + drpgrade +'&fromDate=' + drpfromDate +'&toDate=' + drptoDate +
             '&group=' + drpgroup +'&blocks=' + drpblocks +'&soaking=' + drpsoaking +'&typeProduct=' + drptypeProduct +'&pDCategory=' + drppDCategory +
             '&freezingType=' + drpfreezingType +'&abstatus=' + drpabstatus + '&excelDownload=T';
            window.open(url, '_blank');
        }

         else {
        $.ajax({
            url: 'Ajax/ajax-inward-report.php',
            type: 'POST',
            data: {
                coldStore: $("#drpColdStore").val(),
                stockbelongsto: $("#drpStockBlgsTo").val(),
                poNo: $.trim($("#drpPoNumber").val()),
                variety: $("#drpVariety option:selected").attr('data-shortname') || '',
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
            // success: function(data) {
            //     fnSuccess(data);
            //     return false;
            // }

            success: function(data) {
                fnSuccess(data);
                    // $("#divDataList").html(data);
                    // fnShowDataTable();
                    return false;
                }
        });
    }
    }

    $("#btnSearch").on("click", function() {

        if ($("#txtTransactionDate").val() != "") {
            ShowList("1", "", "0");
        } else {
            alert("Please select Transaction Date");
            $("#txtTransactionDate").focus();
        }
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

    function FormattedDate(oldformatDate) {
        var dateObject = new Date(oldformatDate);
        var FormattedDate = dateObject.getDate() + "/" + (dateObject.getMonth() + 1) + "/" + dateObject.getFullYear();
        return FormattedDate;
    }
    $("#drpColdStore, #drpPlant").on("change", function () {
        $("#divDataList").html("");
    });
</script>

</body>

</html>
