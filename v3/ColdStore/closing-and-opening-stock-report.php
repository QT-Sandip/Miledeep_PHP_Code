<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$excludeList = array("Blocks","Floors","StoreRacks","Stock","StockBelongsTo","ProductGroup","Exporters","Consignee","Processor","Shifts","OptType","OutType","Variety","StockAvailableVariety","StockInVarietyList","PoNumber","Plant","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Tranction","Type","CsTransactionTypes","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");
$PageTitle = "Opending & Closing Report";
$filterProductionDate = "T";
$filterGrade = "T";
$filterColdStores = "T";
$filterShortName = "T";
$filterPckgStyle = "T";
$filterTypeProduct = "T";
$filterDateSelection = "T";
$filterSoakingOne = "T";// drpSoaking

$buttomButton = "T";
//   $filterTreatment = "T";
$filterFreezingType = "T";
$filterPackingType = "T";

$filterSpecies = "T";
$filterAbstatus = "T";

$ProductionPlant = $_SESSION['UnitID'];

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
    $(document).ready(function () {

        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpColdStore").prop('disabled', true);
            $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');

        } else {
            $("#drpColdStore").prop('disabled', false);
            $("#drpColdStore").val("").trigger('change');
        }
    });

    function fnShowDataTable() {
        var productionDate = $("#txtProductionDate").val();
        var dateSelection = $("#drpDateSelection").val();
        var variety = $("#drpVariety option:selected").text();
        var colstore = $("#drpColdStore option:selected").text();

        var title = 'Closing And Opening';
        if (dateSelection == 'ClosingDate') {
            title = 'Closing';
        } else if (dateSelection == 'OpeningDate') {
            title = 'Opening';
        }

        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            info: false,
            fixedColumns: true,
            scrollCollapse: true,
            ordering: false,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: title + ' Stock Report_ProductionDate:' + productionDate + '_Variety:' + variety + '_ColdStore:' + colstore,
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
        
        table.on('draw.dt', function () {
            let TotalStockMcs = 0;
            let TotalStockSlabs = 0;
            let TotalStockKgs = 0;
            $('.Mcs').each(function () {
                // Parse the text content as a float, ignoring any commas
                const Mcs = parseInt($(this).text().replace(/,/g, ''));
                if (!isNaN(Mcs)) {
                    TotalStockMcs += Mcs;
                }
            });
            $('.Slabs').each(function () {
                const Slabs = parseInt($(this).text().replace(/,/g, ''));
                if (!isNaN(Slabs)) {
                    TotalStockSlabs += Slabs;
                }
            });
            
            $('.Kgs').each(function () {
                const Kgs = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(Kgs)) {
                    TotalStockKgs += Kgs;
                }
            });
            $('#TotalStockMcs').text(TotalStockMcs.toLocaleString('en-US', {
               // minimumFractionDigits: 2,
               // maximumFractionDigits: 2
            }));
            $('#TotalStockSlabs').text(TotalStockSlabs.toLocaleString('en-US', {
               // minimumFractionDigits: 2,
              //  maximumFractionDigits: 2
            }));
            $('#TotalStockKgs').text(TotalStockKgs.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        })
    }

    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-closing-and-opening-stock-report.php',
            type: 'POST',
            data: {
                productionDate: $("#txtProductionDate").val(),
                variety: $("#drpVariety").val(), // drpVariety
                coldStore: $("#drpColdStore").val(),
                pckgStyle: $("#drpPckgStyle").val(),
                grade: $("#drpGrade").val(),
                soaking: $.trim($("#drpSoaking").val()),
                StockType: $("#drpDateSelection").val(),
                TypeProduct: $("#drpTypeProduct").val(),
                ProductCategory: $("#drpPDCategory").val(),
                packingType: $.trim($("#drpPackingType").val()),
                freezingType: $.trim($("#drpFreezingType").val()),
                species: $.trim($("#drpSpecies").val()),
                abstatus: $("#drpAbstatus").val(),
            },
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();

                return false;
            }
        });
    }

    $("#btnSearch").on("click", function () {
        var fromDate = $("#txtProductionDate").val();
        if (fromDate != '') {
            ShowList();
        } else {
            alert('Select Production Date')
        }
    });
$("#drpColdStore").on("change", function () {
    $("#divDataList").html("");
});
</script>

</body>

</html>
