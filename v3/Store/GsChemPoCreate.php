<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STORE_DROPDOWN;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resBuyer = $resDropdownsAPI->Data->Buyer;
$resSupplier = $resDropdownsAPI->Data->Supplier;
$resPort = $resDropdownsAPI->Data->Port;

$apiList = API_GS_CHEM_PO;

$ChemPoID = trim($_GET['ChemPoID']);

$bodyContent = array(
    "ChemPoID" => $ChemPoID,
);

if ($ChemPoID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    $Data = $result->Data[0];
}


$CurrentMonth = date('m');
$CurrentYear = date('Y');

if ($CurrentMonth > 3) {
    $PastFY = $CurrentYear - 1;
    $CurrentFY = $CurrentYear;
} else {
    $PastFY = $CurrentYear - 2;
    $CurrentFY = $CurrentYear - 1;
}


?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
        /* .select2-container--open {
            z-index: 9999999
        } */
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


                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form role="form" method="post" id="frmHlvaVariety">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpSupplier">Supplier<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpSupplier" id="drpSupplier">
                                                <option value="" <?php echo ($Data->SupplierName == '') ? 'selected' : '';  ?>>All</option>
                                                <?php foreach ($resSupplier as $List) {
                                                    if ($List->Status == 1) { ?>
                                                        <option value="<?php echo $List->ChemSupplierID ?>" <?php echo ($Data->SupplierName == $List->SupplierName) ? 'selected' : ''; ?>>
                                                            <?php echo $List->SupplierName ?></option>
                                                <?php  }
                                                } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpPort">PORT NAME</label>
                                            <select class="drpSelect" name="drpPort" id="drpPort">
                                                <option value="" <?php echo ($Data->PortID == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resPort as $List) { ?>
                                                    <option value="<?php echo $List->PortId ?>" <?php echo ($Data->PortName == $List->PortName) ? 'selected' : '';
                                                                                                ?>><?php echo $List->PortName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>


                                        <div class="col col-md-3 mb-3">
                                            <label for="drpMode">MODE of Import<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpMode" id="drpMode" value='<?php echo $Data->ModeOfImport ?>'>
                                                <option value="" <?php echo ($Data->ModeOfImport == '') ? 'selected' : ''; ?>>Select</option>
                                                <option value="DIRECT" <?php echo ($Data->ModeOfImport == 'DIRECT') ? 'selected' : ''; ?>>DIRECT</option>
                                                <option value="HSS" <?php echo ($Data->ModeOfImport == 'HSS') ? 'selected' : ''; ?>>HSS</option>
                                                <option value="HSS_BOND" <?php echo ($Data->ModeOfImport == 'HSS_BOND') ? 'selected' : ''; ?>>HSS_BOND</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtPoNo" class="form-label">PO NO<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtPoNo" name="txtPoNo" placeholder="Enter Po NO" value='<?php echo $Data->ChemPoNo ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtYear" class="form-label">PO Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtYear" name="txtYear" value='<?php echo $Data->ChemPoDate ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtETADate" class="form-label">ETA Date</label>
                                            <input type="date" class="form-control" id="txtETADate" name="txtETADate" value='<?php echo $Data->ChemETADate ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtETDDate" class="form-label">ETD Date</label>
                                            <input type="date" class="form-control" id="txtETDDate" name="txtETDDate" value='<?php echo $Data->ChemETDDate ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <input type="text" class="form-control" id="txtRemarks" name="txtRemarks" placeholder="Enter Remarks" value='<?php echo $Data->Remarks ?>' />
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ChemPoID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="GsChemPosList" class="btn btn-danger">Close</a>
                                            <?php if ($ChemPoID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Save</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit" id="submit">Update</button>
                                            <?php } ?>
                                        </div>

                                    </div>
                                </form>

                            </div>
                        </div>
                        <!-- <div class="card">
                            <div class="card-body">
                                <div id="divDataList">

                                </div>
                            </div>
                        </div> -->
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
        var chemPoID = "<?php echo isset($_GET["ChemPoID"]) && !empty($_GET["ChemPoID"]) ? $_GET["ChemPoID"] : "" ?>";

        function fnShowDataTable() {
            table = $('#tblDataTable').DataTable({
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
                    title: 'GS Chemical PO -<?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        $('#drpSupplier').on('change', function() {
            let supplierID = $(this).val();
            if (supplierID) {
                $.ajax({
                    url: 'Ajax/AjaxGsChemPoCreate.php',
                    type: 'POST',
                    data: {
                        methodType: "generatePoNumber",
                        supplierID: supplierID
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.AutoGeneratedPoNo) {
                            $('#txtPoNo').val(response.AutoGeneratedPoNo);
                            $('#txtPoNo').prop('readonly', true);
                            $('#drpSupplier').select2({
                                closeOnSelect: true,
                                width: '100%'
                            });
                        }
                    },
                    /* Enable this to debug */
                    /* error: function(xhr, status, error) {
                        console.error("AJAX Error:", {
                            status: status,
                            error: error,
                            responseText: xhr.responseText
                        });

                        alert(
                            "AJAX Error!\n" +
                            "Status: " + status + "\n" +
                            "Error: " + error + "\n\n" +
                            "Response:\n" + xhr.responseText
                        );
                    } */
                });
            }
        });

        $(document).ready(function() {
            $.ajax({
                url: 'Ajax/AjaxGsChemPoCreate.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    chemPoID: chemPoID
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });
        });

        $("#frmHlvaVariety").validate({

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
                // drpBuyer: {
                //     required: true,
                // },
                txtPoNo: {
                    required: true,
                },
                drpSupplier: {
                    required: true,
                },
                // drpPort: {
                //     required: true,
                // },
                drpMode: {
                    required: true,
                },
                txtYear: {
                    required: true,
                },

            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/AjaxGsChemPoCreate.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        chemPoID: chemPoID,
                        supplierID: $("#drpSupplier").val(),
                        // buyerID: $("#drpBuyer").val(),
                        poNo: $("#txtPoNo").val(),
                        portID: $("#drpPort").val(),
                        mode: $("#drpMode").val(),
                        year: $("#txtYear").val(),
                        etaDate: $("#txtETADate").val(),
                        etdDate: $("#txtETDDate").val(),
                        remarks: $("#txtRemarks").val(),
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error) {
                            alert(data.Error);
                        } else {
                            alert(data.Msg);
                            window.location.href = "GsChemPosList.php";
                        }

                        return false;
                    }
                });

                return false;
            }
        });
    </script>

    </body>

</html>