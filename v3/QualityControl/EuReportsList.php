<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");

$PageTitle = "Eu Reports List";
$PageTitleFolder = "Quality Control";

$uid = $_SESSION['UserID'];

if ($_SESSION["UserID"] == '') {
    header('Location: index.php?msg=Please enter your User name and Password.');
}
?>

<!doctype html>
<html lang="en">
<link href="https://serp.silverlights.in/Clients/JME/User.css" rel="stylesheet" type="text/css" />

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
</head>

<body data-sidebar="dark">

    <?php include($IncludesPath . "PreLoader.php"); ?>

    <div id="layout-wrapper">
        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php include($IncludesPath . "page-title.php"); ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <!-- From Date -->
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>Select From Date</label>
                                                <input type="date" class="form-control" name="FromDate" id="FromDate"
                                                    required value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>

                                        <!-- To Date -->
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label>Select To Date</label>
                                                <input type="date" class="form-control" name="ToDate" id="ToDate"
                                                    required value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div align="center" class="col-lg-12">
                                        <button id="list" align="center" type="button"
                                            class="btn btn-primary waves-effect waves-light">
                                            Search <i class='ri-arrow-right-line align-middle ml-2'></i>
                                        </button>

                                        <button id="createreport" class="btn btn-success">Create Report</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Report List Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="ABReportList">
                                    <!-- DataTable will populate here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include($IncludesPath . "footer.php"); ?>
        </div>
    </div>

    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>
</body>

</html>

<script>
    $(document).ready(function () {

        $('#list').click(function () {
            var FromDate = $('#FromDate').val();
            var ToDate = $('#ToDate').val();

            $.ajax({
                url: 'Ajax/AjaxEuLabReportsList.php',
                type: 'POST',
                data: { FromDate: FromDate, ToDate: ToDate },
                success: function (response) {
                    // $('#ABReportList').html(response);
                    showData(response);
                },
                error: function (xhr, status, error) {
                    console.error("Error occurred: ", error);
                    alert("Failed to load data.");
                }
            });
        });

        $('#createreport').click(function () {
            var FromDate = $('#FromDate').val();
            var ToDate = $('#ToDate').val();
            var today = new Date();
            var DOR = (today.getDate()).toString().padStart(2, "0") + '-' + (today.getMonth() + 1).toString().padStart(2, "0") + '-' + today.getFullYear();
            var DOA = (today.getDate()).toString().padStart(2, "0") + '-' + (today.getMonth() + 1).toString().padStart(2, "0") + '-' + today.getFullYear();

            window.open("EuReportCreate.php?DOA=" + DOA + "&DOR=" + DOR + "&FromDate=" + FromDate + "&ToDate=" + ToDate, "_blank");


        });

        function showData(data) {

            $("#ABReportList").html(data);

            var table = $('#key-datatable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                bInfo: false,
                bSort: false,
                dom: 'Blfrtip',

            });
        }
    });
</script>