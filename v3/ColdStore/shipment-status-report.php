
<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$excludeList = array("ColdStores","Blocks","Floors","StoreRacks","Stock","StockBelongsTo","ProductGroup","Exporters","Consignee","Processor","Shifts","PackingType","FreezingType","Soaking","Abstatus","OptType","OutType","Grade","Variety","VarietyList","StockAvailableVariety","StockInVarietyList","PoNumber","PackingStyle","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Species","Tranction","TypeProduct","Type","CsTransactionTypes","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");
// $filterfromDate = "T";
// $filtertoDate = "T";
$filterPlant = "T";
$filterOrderStausOne="T"; // drpYear
$filterYearOne="T"; // drpPoNumber
$filterPoNumberOne="T"; // drpOrderStatus
$buttomButton = "T";

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
                            <!-- <div class="card-body">
                                <form id="frmOrderPacking">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="drpOrderStatus" class="form-label">Order Status</label>
                                            <select class="form-control drpSelect" name="drpOrderStatus" id="drpOrderStatus" required>
                                                <option value="0">Pending</option>
                                                <option value="1">Completed</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="drpYear">Year</label>
                                            <select class="drpSelect form-control" id="drpYear" name="drpYear">
                                                <option value="2019">2019</option>
                                                <option value="2020">2020</option>
                                                <option value="2021">2021</option>
                                                <option value="2022">2022</option>
                                                <option value="2023">2023</option>
                                                <option value="2024" selected>2024</option>
                                                <option value="2025">2025</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="drpPoNumber" class="form-label">Po Number</label>
                                            <select class="form-control drpSelect" name="drpPoNumber" id="drpPoNumber" required>
                                                <option value="">Select</option>
                                            </select>
                                        </div>

                                        <div class="text-center mt-1">
                                            <button id="btnSearch" type="submit" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div> -->
                        </div>

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
    $(document).ready(function() {

        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpPlant").prop('disabled', true);
            $("#drpPlant").val(productionPlant).trigger('change');

        } else {
            $("#drpPlant").prop('disabled', false);
            $("#drpPlant").val("").trigger('change');
        }
    });

    function fnPoStatusReportExcel(tableID, filename = '') {
        var year = $("#drpYear").val().split('-').reverse().join('/');
        var poNumber = $("#drpPoNumber").val().split('-').reverse().join('/');
        var productionPlant = $("#drpPlant option:selected").text();

        $("#tblDataTable").find(".thead-light  tr").height("auto");
        $("#tblDataTable").find(".thead-light  tr th").height("auto");
        $("#tblDataTable").find(".thead-light  tr th div").height("auto");

        $("#tblDataTable").find("tfoot tr").height("auto");
        $("#tblDataTable").find("tfoot tr td").height("auto");
        $("#tblDataTable").find("tfoot tr th div").height("auto").css("font-weight", "bold");

        var downloadLink;
        var dataType = 'data:application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);

        var clonedTable = tableSelect.cloneNode(true);

        // var reportNameRow = document.createElement('tr');
        // reportNameRow.innerHTML = `
        // <td colspan="5" style="text-align: center; font-weight: bold;">
        //     Shipment Status Report
        // </td>`;
        // clonedTable.querySelector('thead').insertBefore(reportNameRow, clonedTable.querySelector('thead').firstChild);


        var trs = clonedTable.getElementsByTagName('tr');
        for (var i = 0; i < trs.length; i++) {
            trs[i].style.border = '1px solid #eff2f7';
            var tds = trs[i].getElementsByTagName('td');
            for (var j = 0; j < tds.length; j++) {
                tds[j].style.border = '1px solid #eff2f7';
            }
        }

        var hiddenCells = clonedTable.querySelectorAll('td[style*="display: none"]');
        hiddenCells.forEach(function(cell) {
            cell.style.visibility = 'hidden';
        });

        var tableHTML = clonedTable.outerHTML;

        filename = filename ? filename  + '.xls' : 'excel_data.xls';

        downloadLink = document.createElement('a');
        document.body.appendChild(downloadLink);

        if (navigator.msSaveOrOpenBlob) {
            var blob = new Blob(['\ufeff', tableHTML], {
                type: dataType
            });
            navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            downloadLink.href = dataType + ', ' + encodeURIComponent(tableHTML);
            downloadLink.download = filename;
            downloadLink.click();
        }

        $("#tblDataTable").find(".thead-light  tr").height("0px");
        $("#tblDataTable").find(".thead-light  tr th").height("0px");
        $("#tblDataTable").find(".thead-light  tr th div").height("0px");

        $("#tblDataTable").find("tfoot tr").height("0px");
        $("#tblDataTable").find("tfoot tr td").height("0px");
        $("#tblDataTable").find("tfoot tr th div").height("0px");
    }

    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            scrollCollapse: true,
            bInfo: false,
            bSort: false,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Shipment Status Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function() {
                    fnPoStatusReportExcel('tblDataTable', 'Shipment Status Report');
                }
            }],
        });
    }

    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-shipment-status-report.php',
            type: 'POST',
            data: {
                productionPlant: $("#drpPlant").val(),
                PoID: $("#drpPoNumber").val(),
                year: $("#drpYear").val(),
                orderStatus: $("#drpOrderStatus").val(),
            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();

                return false;
            }
        });
    }

    $("#btnSearch").on("click", function() {
        var productionPlant = $("#drpPlant").val();
        var poNumber = $("#drpPoNumber").val();
        
        if (poNumber == '' ) {
            alert('Select PoNumber')
        } else if (productionPlant == '') {
            alert('Select Production Plant')
        } else {
            ShowList();
        }
    })


    $(document).ready(function() {
        getPoNumbers();
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
        var productionPlant = $("#drpPlant").val();

        $.ajax({
            url: 'Ajax/ajax-order-packing-timeline.php',
            type: 'POST',
            data: {
                methodType: "getPoNumbers",
                year: year,
                orderStatus: orderStatus,
                PlantID: productionPlant
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
$("#drpPlant, #drpPoNumber").on("change",function(){
    $("#divDataList").html("");
});
</script>

</body>

</html>
