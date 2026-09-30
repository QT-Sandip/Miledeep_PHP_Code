<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once ROOT . 'config/api-call.php';

$filterPlant = "T";
$filterColdStores = "T";
$filterfromDate = "T";
$filterClosingDate = "T";
$StockSummaryButton = "T";

$ProductionPlant = $_SESSION['UnitID'];
$sqlQuery = "SELECT ProcessingStationID,StationName,FullName FROM `RmProcessingStations` WHERE StationStatus=1 ORDER BY StationName ASC";
$ProductionPlantList = $iconn->query($sqlQuery);

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload</title>
    <?php include_once ROOT . '/includes/head.php' ?>

    <script>
        function getPlantFilteredData(event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);
            const tableContainer = document.getElementById("divDataList");

            fetch('Ajax/AjaxUploadsData.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.text())
                .then(data => {
                    tableContainer.innerHTML = data; // Set the data directly to the container
                    fnShowDataTable();
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function fnShowDataTable() {
            const table = $('#tblDataTable').DataTable({
                scrollY: "400px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                fixedColumns: true,
            });
        }


        function handleUpload(event) {
            event.preventDefault(); // Prevent default form submission

            const form = event.target;
            const formData = new FormData(form);
            const fileNameInput = document.getElementById('fileName').value;
            formData.append('FileName', fileNameInput);
            formData.append('Type', 'COLDSTORE');
            fetch('Ajax/AjaxUpload.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.text())
                .then(result => {
                    alert(result); // Show the response as an alert
                    form.reset(); // Reset the form after upload
                    window.location.reload();
                })
                .catch(error => {
                    alert('File upload failed. Please try again.');
                    console.error('Error:', error);
                });
        }
    </script>
</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<body>
    <div id="layout-wrapper">

        <?php include_once ROOT . '/includes/menu.php' ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php include_once ROOT . '/includes/page-header.php' ?>

                    <?php if ($_SESSION['AccessType'] != 1) { ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form onsubmit="handleUpload(event)" method="post" enctype="multipart/form-data">
                                            <div class="row align-items-center">
                                                <div class="col-md-3 mt-3 mb-2">
                                                    <label for="file">Choose a File<span style="color:red">*</span></label>
                                                    <input type="file" class="form-control" name="file" id="file" required>
                                                </div>
                                                <div class="col-md-3 mt-3 mb-2">
                                                    <label for="fileName">File Name</label>
                                                    <input type="text" class="form-control" name="fileName" id="fileName">
                                                </div>
                                                <div class="col-md-1 mt-3 mb-2"></div>
                                                <div class="col-md-3 mt-4 mb-2" style="top:8px;">
                                                    <button id="list" type="submit"
                                                        class="btn btn-primary waves-effect waves-light">
                                                        Upload
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form onsubmit="getPlantFilteredData(event)" method="post"
                                        enctype="multipart/form-data" id="plantForm">
                                        <?php if ($_SESSION['AccessType'] == 1) { ?>
                                            <div class="row align-items-center">
                                                <div class="col-md-3 mt-3 mb-2">
                                                    <label for="processingPlant" class="form-label">Plant<span
                                                            style="color:red">*</span></label>
                                                    <select class="drpSelect form-control" name="processingPlant"
                                                        id="processingPlant">
                                                        <option value="">Select</option>
                                                        <?php
                                                        while ($Item = $ProductionPlantList->fetch_object()) { ?>
                                                            <option value="<?php echo $Item->ProcessingStationID; ?>">
                                                                <?php echo $Item->FullName . ' - ' . $Item->StationName; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-1 mt-3 mb-2"></div>
                                                <div class="col-md-3 mt-4 mb-2" style="top:8px;">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light"
                                                        id="search-submit">
                                                        Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div id="divDataList">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php include_once ROOT . '/includes/footer.php' ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>

<script>
    $(document).ready(function () {
        $('#plantForm').submit();
    });
</script>