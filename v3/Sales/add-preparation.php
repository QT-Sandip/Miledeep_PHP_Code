<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_EXPORTS_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resFreezingType = $resDropdownsAPI->Data->FreezingType;

$apiList = API_PREPARATION_MASTARS;

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
                                            <label for="drpFreezingType">Freezing Type<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpFreezingType" id="drpFreezingType">
                                                <option value="" <?php echo ($Data->FreezingType == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resFreezingType as $List) { ?>
                                                    <option value="<?php echo $List->ShortCode ?>" 
                                                    <?php echo ($Data->FreezingType == $List->FreezingTypeName) ? 'selected' : '';?>>
                                                    <?php echo $List->FreezingTypeName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpProdType">Type of Product</label>
                                            <select class="drpSelect" name="drpProdType" id="drpProdType" value='<?php echo $Data->ProdType ?>'>
                                                <option value="" <?php echo ($Data->ProdType == '') ? 'selected' : ''; ?>>Select</option>
                                                <option value="RAW" <?php echo ($Data->ProdType == 'RAW') ? 'selected' : ''; ?>>RAW</option>
                                                <option value="COOKED" <?php echo ($Data->ProdType == 'COOKED') ? 'selected' : ''; ?>>COOKED</option>
                                                <option value="BLANCHED" <?php echo ($Data->ProdType == 'BLANCHED') ? 'selected' : ''; ?>>BLANCHED</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtPreparationName" class="form-label">Preparation Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtPreparationName" name="txtPreparationName" placeholder="Enter Preparation Name" value='<?php echo $Data->PreparationName ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtShortCode" class="form-label">Short Code<span style="color:red">*</span></label>
                                            <input class="form-control" id="txtShortCode" name="txtShortCode" placeholder="Enter Short Code" value="<?php echo $Data->ShortCode ?>">
                                        </div>
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtQCPreparationShortCode" class="form-label">QC Preparation Short Code</label>
                                            <input class="form-control" id="txtQCPreparationShortCode" name="txtQCPreparationShortCode" placeholder="Enter Qc Preparation Short Code" value="<?php echo $Data->QcPreparationShortCode ?>">
                                        </div>
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtHSCode" class="form-label">HS Code<span style="color:red">*</span></label>
                                            <input class="form-control" id="txtHSCode" name="txtHSCode" placeholder="Enter HS Code" value="<?php echo $Data->HSCode ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtGstPerc" class="form-label">Gst Perc<span style="color:red">*</span></label>
                                            <input class="form-control" id="txtGstPerc" name="txtGstPerc" placeholder="Enter Gst Perc" value="<?php echo $Data->GstPerc ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtPrepDesc" class="form-label">Preparation Description<span style="color:red">*</span></label>
                                            <textarea class="form-control" id="txtPrepDesc" name="txtPrepDesc" rows="1" placeholder="Enter Preparation Description"><?php echo $Data->PreparationDesc ?></textarea>
                                        </div>
                                       
                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="preparation-list" class="btn btn-danger">Close</a>
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
                drpFreezingType: {
                    required: true,
                },
                drpProdType: {
                    required: true,
                },
                txtPreparationName: {
                    required: true,
                },
                txtShortCode: {
                    required: true,
                },
                // txtQCPreparationShortCode: {
                //     required: true,
                // },
                txtHSCode: {
                    required: true,
                },
                txtGstPerc: {
                    required: true,
                },
                txtPrepDesc: {
                    required: true,
                },
            },
            errorPlacement: function() {
                return false; 
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/ajax-add-preparation.php',
                    type: 'POST',
                    data: {
                        id: id,
                        freezingType: $("#drpFreezingType").val(),
                        prodType: $("#drpProdType").val(),
                        prepName: $("#txtPreparationName").val(),
                        shortCode: $("#txtShortCode").val(),
                        qcPrepShortCode: $("#txtQCPreparationShortCode").val(),
                        hsCode: $("#txtHSCode").val(),
                        gstPerc: $("#txtGstPerc").val(),
                        prepDesc: $("#txtPrepDesc").val()
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "preparation-list";
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