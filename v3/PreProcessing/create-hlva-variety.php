<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_PREPROCESSING_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resProductList = $resDropdownsAPI->Data->ProductList;

$apiList = API_HLVA_VARIETY_LIST;

$VarietyID = trim($_GET['VarietyID']);

$bodyContent = array(
    "VarietyID" => $VarietyID
);

if ($VarietyID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    $Data = $result->Data[0];
}

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
                                <form role="form" method="post" id="frmHlvaVariety">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpSpecies">Species<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpSpecies" id="drpSpecies" value='<?php echo $Data->Species ?>'>
                                                <option value="" <?php echo ($Data->Species == '') ? 'selected' : ''; ?>>Select</option>
                                                <option value="VM" <?php echo ($Data->Species == 'VM') ? 'selected' : ''; ?>>VM</option>
                                                <option value="BT" <?php echo ($Data->Species == 'BT') ? 'selected' : ''; ?>>BT</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpTypeProduct">Type Product<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpTypeProduct" id="drpTypeProduct">
                                                <option value="" <?php echo ($Data->TypeProduct == '') ? 'selected' : ''; ?>>Select</option>
                                                <option value="RAW" <?php echo ($Data->TypeProduct == 'RAW') ? 'selected' : ''; ?>>RAW</option>
                                                <option value="COOKED" <?php echo ($Data->TypeProduct == 'COOKED') ? 'selected' : ''; ?>>COOKED</option>
                                                <option value="BLANCHED" <?php echo ($Data->TypeProduct == 'BLANCHED') ? 'selected' : ''; ?>>BLANCHED</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtShortName" class="form-label">Short Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtShortName" name="txtShortName" placeholder="Enter Short Name" value='<?php echo $Data->ShortName ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtFullName" class="form-label">Full Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtFullName" name="txtFullName" placeholder="Enter Full Name" value='<?php echo $Data->FullName ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCategory" class="form-label">Category<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtCategory" name="txtCategory" placeholder="Enter Category" value='<?php echo $Data->Category ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpProduct">Product<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpProduct" id="drpProduct">
                                                <option value="" <?php echo ($Data->Product == '') ? 'selected' : ''; ?>>Select</option>
                                                <?php
                                                    // echo json_encode($resProductList,JSON_PRETTY_PRINT);
                                                foreach ($resProductList as $List) { ?>
                                                    <option value="<?php echo $List->ProductID ?>" <?php echo ($Data->Product == $List->ShortName) ? 'selected' : ''; ?>><?php echo $List->ShortName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtPdDescription" class="form-label">Product Description</label>
                                            <input type="text" class="form-control" id="txtPdDescription" name="txtPdDescription" placeholder="Enter Product Description" value='<?php echo $Data->ProductDescription ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <option value="">Select</option>
                                                <option value="1" selected>Active</option>
                                                <option value="0">In-Active</option>
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $VarietyID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="hlva-variety-list" class="btn btn-danger">Close</a>
                                            <?php if ($VarietyID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Save</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit" id="submit">Update</button>
                                            <?php } ?>
                                        </div>

                                    </div>
                                </form>

                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">

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
        var varietyID = "<?php echo isset($_GET["VarietyID"]) && !empty($_GET["VarietyID"]) ? $_GET["VarietyID"] : "" ?>";
        
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
                    title: 'HLVA Variety-List-<?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        $(document).ready(function() {
            $.ajax({
                url: 'Ajax/ajax-create-hlva-variety.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    VarietyID: varietyID
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });
        });

        jQuery.validator.addMethod(
            "noSpace",
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
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
                drpSpecies: {
                    required: true,
                },
                drpTypeProduct: {
                    required: true,
                },
                txtShortName: {
                    required: true,
                    noSpace: true
                },
                txtFullName: {
                    required: true,
                    noSpace: true
                },
                txtCategory: {
                    required: true,
                    noSpace: true
                },
                drpStatus: {
                    required: true,
                },
                drpProduct: {
                    required: true,
                },

            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/ajax-create-hlva-variety.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        varietyID: varietyID,
                        species: $("#drpSpecies").val(),
                        typeProduct: $("#drpTypeProduct").val(),
                        shortName: $("#txtShortName").val(),
                        fullName: $("#txtFullName").val(),
                        category: $("#txtCategory").val(),
                        status: $("#drpStatus").val(),
                        product: $("#drpProduct").val(),
                        productText: $( "#drpProduct option:selected" ).text(),//$("#drpProduct").val(),
                        productDesc: $("#txtPdDescription").val(),
                    },
                    success: function(data) {
                        $("#divDataList").html(data);
                        fnShowDataTable();
                        return false;
                    }
                });

                return false;
            }
        });
    </script>

    </body>

</html>