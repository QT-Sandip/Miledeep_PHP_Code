<?php

// phpinfo();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';


$ProductionPlant = $_SESSION['UnitID'];

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;  //sriram
// $filterArrivalPlant = "T";
if($IsPlantFilter != 0){
$filterPlantTwo = "T";
}
$filterPPCStation = "T";
$filterFromDate = "T";
$filterToDate = "T";
$buttonBottom = "T";
$filterProductionShifts = "T";
?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>

</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . 'includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . 'includes/page-header.php' ?>
                <?php include_once WORKING_MODULE . 'includes/page-filter.php'; ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">

                                <div id="divDataList">
                                    <!-- -->
                                </div>

                            </div>
                        </div>


                    </div>
                </div>

            </div> <!-- container-fluid -->
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
     $(document).ready(function () {
        $("#drpProductionShifts").html(`
            <option value="">Select Shift</option>
            <option value="1">A Shift</option>
            <option value="2">B Shift</option>
            <option value="3">C Shift</option>
        `);
        var productionPlant = <?php echo $ProductionPlant ?>;
        var IsPlantFilter = <?php echo $IsPlantFilter ?>;
        console.log("productionPlant ", productionPlant, " IsPlantFilter ", IsPlantFilter)
        if (productionPlant != 0 || IsPlantFilter == 0) {
            $("#drpPlant").prop('disabled', true);
            if (IsPlantFilter == 0) {
                $("#drpPlant").val("");
            } else {
                $("#drpPlant").val(productionPlant).trigger('change');
            }
        } else {
            $("#drpPlant").prop('disabled', false);
        }
       
    });
    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: 'Value Addition Report 1',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });

        table.on('draw.dt', function() {
            let hLWtKgs = 0;
            let vaOutQty = 0;
            let yield = 0;
            let count = 0;

            $('.HLWtKgs').each(function() {
                const value = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(value)) {
                    hLWtKgs += value;
                }
            });

            $('.VAOutQty').each(function() {
                const value = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(value)) {
                    vaOutQty += value;
                }
            });

            $('.Yield').each(function() {
                const value = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(value)) {
                    yield += value;
                    count++;
                }
            });
            //const averageYield = count > 0 ? (yield / count) : 0;
            const averageYield =  hLWtKgs > 0 ? vaOutQty / hLWtKgs * 100: 0;

                $('#TotalHLWtKgs').text(hLWtKgs.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                $('#TotalVAOutQty').text(vaOutQty.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                $('#TotalYield').text(averageYield.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
            });
        }

        function ShowList() {
            var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
            $.ajax({
                url: 'Ajax/ajax-value-addition-report-group.php',
                type: 'POST',
                data: {
                    Plant: $("#drpPlant").val(),
                    startDate: $("#txtFromDate").val(),
                    endDate: $("#txtToDate").val(),
                    station: $("#drpStation").val(),
                    IsPlantFilter: IsPlantFilter,
                    ProductionShifts : $('#drpProductionShifts').val()


                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });
        }
        $(document).ready(function() {
            //  ShowList();
        });

        $("#btnSearch").on("click", function() {
            var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
            if (IsPlantFilter == 1){
                    if($("#drpPlant").val() != "") {
                    // ShowList();
                    $StartDate = $("#txtFromDate").val();
                    // $EndDate = $("#txtToDate").val();
                    // Sstation= $("#drpStation").val(),
                    if ($StartDate != '') {
                        ShowList();

                    } else {
                        alert("Please select Date");
                    }
                } else {
                    alert("Please select the Plant");
                    // $('#drpProductionPlant').next("span").after('<div class="new-content" style="color: #FF0000">New Content Here</div>');

                }
            }else{
                 $StartDate = $("#txtFromDate").val();
                    // $EndDate = $("#txtToDate").val();
                    // Sstation= $("#drpStation").val(),
                    if ($StartDate != '') {
                        ShowList();

                    } else {
                        alert("Please select Date");
                    }
            }
        });

        function FormattedDate(oldformatDate) {
            var dateObject = new Date(oldformatDate);
            var FormattedDate = dateObject.getDate() + "/" + (dateObject.getMonth() + 1) + "/" + dateObject.getFullYear();
            return FormattedDate;
        }
$("#drpPlant").on("change", function () {
    $('#tblDataTable').DataTable().destroy();
    $("#divDataList").html("");
});
</script>

</body>

</html>
