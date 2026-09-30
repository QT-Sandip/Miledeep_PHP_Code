<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
$filterCenter = "T";
$filterRptType = "T";
$filterAgent = "T";
$filterArrivalPlant = "T";
$filterStartDateBills = 'T';
$filterEndDateBills = 'T';
$buttonButtom = 'T';
?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . 'includes/head.php' ?>
</head>
<?php include_once ROOT . '/includes/body-preloader.php' ?>
<div id="layout-wrapper">
    <?php include_once ROOT . 'includes/menu.php' ?>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <?php include_once ROOT . 'includes/page-header.php' ?>
                <?php include_once WORKING_MODULE . 'includes/page-filter.php'; ?>
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <!-- <select id="selectPageSize" onchange="changePageSize()" class="form-select w-auto ms-2" style="display:none;">
                                        <option value="10" selected>10</option>
                                        <option value="20">20</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select> -->
                                <div id="divDataList">

                                    <!-- -->
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- End Page-content -->
        <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . 'includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . 'includes/app-js.php' ?>

<script>
    function fnShowDataTable() {
        var CenterName = $("#select2-drpCenter-container").text();
        var StartDate = FormattedDate($("#txtStartDate").val());
        var EndDate = FormattedDate($("#txtEndDate").val());

        var DatePeriod = StartDate + " - " + EndDate;

        if (CenterName == "Select Center") {
            CenterName = ' ';
        }
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bSort: false,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: CenterName + '  Agent Commision Report on Date : - ' + DatePeriod,
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
         table.on('draw.dt', function () {
            let TotalHarvestAmount = 0;
            let TotalHarvestCommision = 0;
            $('.harvest-amount').each(function () {
                // Parse the text content as a float, ignoring any commas
                const HarvestAmount = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(HarvestAmount)) {
                  TotalHarvestAmount += HarvestAmount;
                }
            });
            $('.bil-harvest-commission').each(function () {
                // Parse the text content as a float, ignoring any commas
                const HarvestCommision = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(HarvestCommision)) {
                  TotalHarvestCommision += HarvestCommision;
                }
            });
           
            $('#tdTotalHarvestAmount').text(TotalHarvestAmount.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#tdTotalHarvestCommision').text(TotalHarvestCommision.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });
    }

    //page = 1, pageSize = 10
    function ShowList() {
        // $("#selectPageSize").show();
        // pageSize = parseInt($("#selectPageSize").val());
        var txtStartDate = $("#txtStartDate").val();
        var txtEndDate = $("#txtEndDate").val();

        var startDate = new Date(txtStartDate);
        var endDate = new Date(txtEndDate);
        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

        var formData = {
            Plant: $("#drpArrivalPlant").val(),
            Center: $("#drpCenter").val(),
            Type: $("#drpBillerType").val(),
            Agent: $("#drpAgent").val(),
            startDate: $("#txtStartDate").val(),
            endDate: $("#txtEndDate").val(),
            RptType: $("#drpRptType").val()

        };
        if (daysDiff > 90) {
            var queryString = $.param(formData) + "&excelDownload=T";
            var finalUrl = "Ajax/AjaxAgentCommisionReport.php?" + queryString;
            window.open(finalUrl, '_blank');
            return;
        }
        $.ajax({
            url: 'Ajax/AjaxAgentCommisionReport.php',
            type: 'POST',
            data: formData,
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }
    $(document).ready(function () {
        //  ShowList();
    });

    let currentPage = 1;
    let pageSize = 20;

    function nextPage() {
        currentPage++;
        $("#current-page").text(currentPage);
        ShowList(currentPage, pageSize);
    }

    function prevPage() {
        if (currentPage > 1) {
            currentPage--;
            $("#current-page").text(currentPage);
            ShowList(currentPage, pageSize);
        }
    }
    //     function changePageSize() {
    //     pageSize = parseInt($("#selectPageSize").val());
    //     currentPage = 1;
    //     ShowList(currentPage,pageSize);
    // }


    $(document).on("change", "#drpBillerType", function () {
        var billerType = $(this).val();
        $('#drpSupplier, #drpAgent').val("").trigger("change");
        if (billerType == "1") {
            $('#drpSupplier').prop("disabled", true);
            $('#drpAgent').prop("disabled", false);
        } else {
            $('#drpSupplier').prop("disabled", false);
            $('#drpAgent').prop("disabled", true);
        }
    });

    $("#btnSearch").on("click", function () {

        var startDate = $("#txtStartDate").val();
        var endDate = $("#txtEndDate").val();

        if (startDate == '') {
            if (startDate == "") {
                $("#txtStartDate").addClass('error');
                return false;
            } else {
                $("#txtStartDate").removeClass('error');
            }
        } else {
            $("#txtStartDate").removeClass('error');
        }

        if (endDate == '') {
            if (endDate == "") {
                $("#txtEndDate").addClass('error');
                return false;
            } else {
                $("#txtEndDate").removeClass('error');
            }
        } else {
            $("#txtEndDate").removeClass('error');
        }

        ShowList();
    })

    function FormattedDate(oldformatDate) {
        var dateObject = new Date(oldformatDate);
        var FormattedDate = dateObject.getDate() + "/" + (dateObject.getMonth() + 1) + "/" + dateObject.getFullYear();
        return FormattedDate;
    }
$("#drpCenter").on("change", function () {
    $('#tblDataTable').DataTable().destroy();
    $("#divDataList").html("");
});
</script>

</body>

</html>
