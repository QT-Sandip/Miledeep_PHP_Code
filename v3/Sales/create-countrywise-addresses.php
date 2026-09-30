<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_EXPORTS_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resExporterPacker = $resDropdownsAPI->Data->ExporterPacker;
$resCountry = $resDropdownsAPI->Data->Country;

$apiList = API_COUNTRY_ADDRESSES;

$ID = trim($_GET['ID']);

$bodyContent = array(
    "ID" => $ID
);

if ($ID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    $Data = $result->Data[0];
}
// echo json_encode($result);

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


                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form role="form" method="post" id="frmCountry">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpType">Type<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpType" id="drpType">
                                                <option value="" <?php echo ($Data->ExporterType == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resExporterPacker as $List) { ?>
                                                    <option value="<?php echo $List->ExpID ?>" 
                                                    <?php echo ($Data->ExporterType == $List->ExporterType) ? 'selected' : '';?>>
                                                    <?php echo $List->ExporterType ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtName" class="form-label">Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtName" name="txtName" placeholder="Enter Name" value='<?php echo $Data->ExporterPackerName ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAddress" class="form-label"> Address<span style="color:red">*</span></label>
                                            <textarea class="form-control" id="txtAddress" name="txtAddress" placeholder="Enter Address"><?php echo $Data->ExporterAddress ?></textarea>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpCountry">Country</label>
                                            <select class="drpSelect" name="drpCountry" id="drpCountry">
                                                <option value="" <?php echo ($Data->CountryName == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resCountry as $List) { ?>
                                                    <option value="<?php echo $List->ID ?>" 
                                                    <?php echo ($Data->CountryName == $List->name) ? 'selected' : '';?>>
                                                    <?php echo $List->name ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpDocType">Doc Type </label>
                                            <select class="drpSelect" name="drpDocType" id="drpDocType" value='<?php echo $Data->DocType ?>'>
                                                <option value="" <?php echo ($Data->DocType == '') ? 'selected' : ''; ?>>Select</option>
                                                <option value="PI" <?php echo ($Data->DocType == 'PI') ? 'selected' : ''; ?>>Proforma Invoice</option>
                                                <option value="CUI" <?php echo ($Data->DocType == 'CUI') ? 'selected' : ''; ?>>Customs Invoice</option>
                                                <option value="CMI" <?php echo ($Data->DocType == 'CMI') ? 'selected' : ''; ?>>Commercial Invoice</option>
                                                <option value="PKL" <?php echo ($Data->DocType == 'PKL') ? 'selected' : ''; ?>>Packing List</option>
                                                <option value="BLI" <?php echo ($Data->DocType == 'BLI') ? 'selected' : ''; ?>>Batch List</option>
                                                <option value="GLI" <?php echo ($Data->DocType == 'GLI') ? 'selected' : ''; ?>>Glaze Invoice</option>
                                                <option value="TPT" <?php echo ($Data->DocType == 'TPT') ? 'selected' : ''; ?>>10+2 Report</option>
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="countrywise-addresses-list" class="btn btn-danger">Close</a>
                                            <?php if ($ID == '') { ?>
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
        var id = "<?php echo isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "" ?>";

        // function fnShowDataTable() {
        //     table = $('#tblDataTable').DataTable({
        //         scrollY: "600px",
        //         scrollX: true,
        //         sScrollXInner: "100%",
        //         paging: false,
        //         destroy: true,
        //         fixedColumns: true,
        //         scrollCollapse: true,
        //         dom: 'Blfrtip',
        //         buttons: [{
        //             extend: 'excel',
        //             text: 'Download Excel',
        //             className: 'btnExcelDataTable',
        //             title: 'Cs IBT Request Form -<?php echo date("d-m-Y") ?>',
        //             footer: true,
        //             exportOptions: {
        //                 columns: ':visible'
        //             }
        //         }],
        //     });
        // }

        // $(document).ready(function() {
        //     $.ajax({
        //         url: 'Ajax/AjaxCsIBTRequestEntryForm.php',
        //         type: 'POST',
        //         data: {
        //             methodType: "get",
        //             ibtID: ibtID
        //         },
        //         success: function(data) {
        //             $("#divDataList").html(data);
        //             fnShowDataTable();
        //             return false;
        //         }
        //     });
        // });

        $("#frmCountry").validate({

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
                drpType: {
                    required: true,
                },
                txtName: {
                    required: true,
                },
                txtAddress: {
                    required: true,
                },
                // drpCountry: {
                //     required: true,
                // },
                // drpDocType: {
                //     required: true,
                // },
            },
            errorPlacement: function() {
                return false; 
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/ajax-create-countrywise-addresses.php',
                    type: 'POST',
                    data: {
                        id: id,
                        type: $("#drpType").val(),
                        name: $("#txtName").val(),
                        address: $("#txtAddress").val(),
                        country: $("#drpCountry").val(),
                        docType: $("#drpDocType").val()
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "countrywise-addresses-list.php";
                        } else {
                            alert(data.Error);
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