<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


// $filterOrderStausOne = "T"; // drpOrderStatus
// $filterPoNumberOne = "T"; //drpPoNumber
// $filterYearOne = "T"; // drpYear
// $buttomButton = "T"; //btnSearch


$ProductionPlant = $_SESSION['UnitID'];
// echo $ProductionPlant;
// exit;
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
                <?php // include_once WORKING_MODULE . '/includes/page-filter.php' 
                ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
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
                                                <option value="2019">2019-2020</option>
                                                <option value="2020">2020-2021</option>
                                                <option value="2021">2021-2022</option>
                                                <option value="2022">2022-2023</option>
                                                <option value="2023">2023-2024</option>
                                                <option value="2024" >2024-2025</option>
                                                <option value="2025" >2025-2026</option>
                                                <option value="2026" selected>2026-2027</option>
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
                            </div>
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
    function fnPoStatusReportExcel(tableID, filename = '', fromDate, toDate, plant) {
        var year = $("#drpYear").val();
        var orderStatus = $("#drpOrderStatus").val();
        var PoNumber = $("#drpPoNumber").val();

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

        var reportNameRow = document.createElement('tr');
        reportNameRow.innerHTML = `
    <td colspan="10" style="text-align: center; font-weight: bold;">
        Order Packing TimeLine Report_Year: ${year} _ PoNumber:${$("#drpPoNumber option:selected").text()}
    </td>`;
        clonedTable.querySelector('thead').insertBefore(reportNameRow, clonedTable.querySelector('thead').firstChild);

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

        filename = filename ? `${filename}_Date (${fromDate} - ${toDate}).xls` : 'excel_data.xls';

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
                title: 'Order Packing report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                action: function() {
                    var year = $("#drpYear").val();
                    var poNumber = $("#drpPoNumber").val();
                    fnPoStatusReportExcel('tblDataTable', 'Order Packing TimeLine Report', year, poNumber);
                }
            }],
        });
    }

    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-order-packing-timeline.php',
            type: 'POST',
            data: {
                methodType: "getList",
                year: $("#drpYear").val(),
                orderStatus: $("#drpOrderStatus").val(),
                purchaseOrder: $("#drpPoNumber").val(),
            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();

                return false;
            }
        });
    }

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
        var Plant = "<?php echo !empty($_SESSION['UnitID'])?$_SESSION['UnitID']:'';?>";

        $.ajax({
            url: 'Ajax/ajax-order-packing-timeline.php',
            type: 'POST',
            data: {
                methodType: "getPoNumbers",
                year: year,
                orderStatus: orderStatus,
                PlantID:Plant
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

    $("#frmOrderPacking").validate({

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
            drpYear: {
                required: true
            },
            drpOrderStatus: {
                required: true
            },
            drpPoNumber: {
                required: true
            },
        },
        errorPlacement: function() {
            return false; // suppresses error message text
        },
        submitHandler: function() {
            ShowList();

            return false;

        }
    });


    // $("#btnSearch").on("click", function() {
    //     var year = $("#drpYear").val();
    //     var poNumber = $("#drpPoNumber").val();

    //     if (year != '' && poNumber != '') {
    //         ShowList();
    //     } else {
    //         alert('Select Year Date and PoNumber');
    //     }
    // })
$("#drpPoNumber").on("change",function(){
    $("#divDataList").html("");
});
</script>

</body>

</html>
