<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$TableFreeze = $_SESSION['ClientID'] == 'DFLV3' ? 1 : 0;//freeze S.no to stockType

$excludeList = array("Floors","StoreRacks","Stock","ProductGroup","Exporters","Consignee","Processor","Shifts","OptType","OutType","StockAvailableVariety","StockInVarietyList","GeneralCategories","Status","TenantType","ProductionLines","BrandType","Tranction","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");
$filterProductionDate = "T";
$filterSpecies = "T";
$filterTypeProduct = "T"; // drpTypeProduct
$filterPlant = "T";
$filterColdStores = "T";
//$filterStockBlgsTo = "T";
$filterFreezingType = "T"; // drpFreezingType
$filterShortName = "T";
$filterPckgStyle = "T";
$filterSoaking = "T";
$filterTypeSlabsNMcs = "T";
$filterSideButton = "T";
// $filterBalanceTypeSelection = "T";
$ProductionPlant = $_SESSION['UnitID'];

$filterPackingType = "T"; 
$filterInwardGroup = "T";

?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>

    <style>
        .red-focus {
            outline: 2px solid red;
        }

        #tblDataTable {
            width: 100%;
            overflow-x: auto;
            border-collapse: collapse;
        }

        #tblDataTable thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 2;
            border: 1px solid #dee2e6;
        }

        #tblDataTable th,
        #tblDataTable td {
            border: 1px solid #dee2e6;
        }
        #tblDataTable #subTable th:nth-child(-n+1),
        #tblDataTable #subTable td:nth-child(-n+1) {
            position: sticky;
            left: <?php echo ($TableFreeze == 1) ? '420px' : 0;?>;
            background-color: #f8f9fa;
            z-index: 1;
        }

        #tblDataTable #subTable th:nth-child(1),
        #tblDataTable #subTable td:nth-child(1) {
            left: <?php echo ($TableFreeze == 1) ? '420px' : 0;?>;
        }
        <?php 
        if($TableFreeze == 1){?>
            th.freezeSNo,th.freezeVariety,th.freezePackingStyle,th.freezeTreatment,th.freezeStockType,
            td.freezeSNo,td.freezeVariety,td.freezePackingStyle,td.freezeTreatment
            {
                position: sticky !important;
               /*  top: 0 !important; */
                z-index: 1;           
                background-color: #f8f9fa;
            }
            td.freezeSNo,th.freezeSNo {
                left: 0px;   /* adjust based on your table */
            }
            td.freezeVariety,th.freezeVariety {
                left: 68px;
            }
            td.freezePackingStyle,th.freezePackingStyle {
                left: 184px;
            }
            td.freezeTreatment,th.freezeTreatment {
                left: 303px;  
            }
            td.freezeStockType,th.freezeStockType {
                left: 420px;  
            }
        <?php }?>
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
        var allowedPlants = ['Repacking', 'Repacking In', 'Repacking Out'];
        $('#drpGroup option').each(function () {
            /* if ($.trim($(this).text()) === 'FG IN') {
                $(this).after('<option value="Repacking Out" data-isreturnable="1">Repacking In</option>');
            }
            if ($.trim($(this).text()) === 'Repacking Out') {
                 $(this).attr('data-transtype',2)
            } */
            if ($(this).val() == '') {
                return true;
            }
            if ($.inArray($(this).val(), allowedPlants) !== -1) {
                $(this).remove();
            }
        });
        $('#drpGroup').append(` <option value="Repacking IN">Repacking IN</option> <option value="Repacking Out">Repacking Out</option><option value="Audit Out">Audit Out</option>
                            <option value="Physical Out">Physical Out</option>
                            <option value="IBT Out">IBT Out</option>
                            <option value="Conversion Out">Conversion Out</option>`);
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
        var productionDate = $("#txtProductionDate").val();
        var species = $("#drpSpecies").val();
        var typeProduct = $("#drpTypeProduct").val();
        var freezingType = $("#drpFreezingType").val();

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

        var filterRow = document.createElement('tr');
        filterRow.innerHTML = `
        <td style="text-align:center" colspan="${clonedTable.querySelectorAll('tr:first-child th').length}">
            <strong>Daily Production Report_</strong>
            <strong>Date:</strong> ${productionDate} _
            <strong>Species:</strong> ${species} _
            <strong>Type Product:</strong> ${typeProduct} _
            <strong>Freezing Type:</strong> ${freezingType}
        </td>`;
        clonedTable.querySelector('thead').insertBefore(filterRow, clonedTable.querySelector('thead').firstChild);

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

        filename = filename ? filename + '.xls' : 'Daily_Production_Report.xls';

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

        // var excelTitle = "Devi Fisheries Limited (2023-2024) \n Daily production  for Raw Material Supplier for the period ended \n ( " + $("#txtStartDate").val() + " - " + $("#txtEndDate").val() + " )";

        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            scrollCollapse: true,
            bInfo: false,
            bSort: false,
             <?php 
            if($TableFreeze == 1 ){?>
                fixedHeader: true,
                columnDefs: [
                    { targets: 0, className: 'freezeSNo' }, 
                    { targets: 1, className: 'freezeVariety' },
                    { targets: 2, className: 'freezePackingStyle' },
                    { targets: 3, className: 'freezeTreatment' },
                   // { targets: 4, className: 'freezeStockType' },
                ],
            <?php }?>
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Daily Production Report',
                exportOptions: {
                    modifier: {
                        page: 'current'
                    }
                },
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function() {
                    fnPoStatusReportExcel('tblDataTable', 'Daily Production Report');
                },
            }],
        });
        $(table.columns(4).header()).addClass('freezeStockType');
    }

    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-daily-production-report.php',
            type: 'POST',
            data: {
                productionDate: $("#txtProductionDate").val(),
                species: $("#drpSpecies").val(),
                productionPlant: $("#drpPlant").val(),
                typeProduct: $("#drpTypeProduct").val(),
                freezingType: $("#drpFreezingType").val(),
                type: $("#drpType").val(),
                Variety: $("#drpVariety").val(),
                PckgStyle: $("#drpPckgStyle").val(),
                Soaking: $("#drpSoaking").val(),
                coldStore: $("#drpColdStore").val(),
                Variety: $("#drpVariety").val(),
                PckgStyle: $("#drpPckgStyle").val(),
                Soaking: $("#drpSoaking").val(),
                coldStore: $("#drpColdStore").val(),


                packingType: $.trim($("#drpPackingType").val()),
                group: $.trim($("#drpGroup").val()),

                isReturn: $("#drpGroup option:selected").data("isreturnable"),
                transtype: $("#drpGroup option:selected").data("transtype"),
            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }

    $("#btnSearch").on("click", function() {
        var productionDate = $("#txtProductionDate").val();
        var species = $("#drpSpecies").val();
        var typePrduct = $("#drpTypeProduct").val();
        var freezingType = $("#drpFreezingType").val();

        if (productionDate == '') {
            $("#txtProductionDate").focus().addClass('red-focus');
        } else if (typePrduct == '') {
            $("#drpTypeProduct").focus().addClass('red-focus');
            alert("Select Type Product");
        } else if (typePrduct == "RAW" && freezingType == '') {
            $("#drpFreezingType").focus().addClass('red-focus');
            alert("Select Freezing Type");
        } else {
            ShowList();
        }
    });
$("#drpPlant").on("change",function(){
    $("#divDataList").html("");
});
</script>

</body>

</html>
