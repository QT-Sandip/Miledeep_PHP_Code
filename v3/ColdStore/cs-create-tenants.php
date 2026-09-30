<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api  = API_COLDSTORE_DROPDOWNS;
$excludeList = array("ColdStores","Blocks","Floors","StoreRacks","Stock","StockBelongsTo","ProductGroup","Exporters","Consignee","Processor","Shifts","PackingType","FreezingType","Soaking","ProductCategory","Abstatus","OptType","OutType","Grade","Variety","VarietyList","StockAvailableVariety","StockInVarietyList","PoNumber","PackingStyle","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Species","Tranction","TypeProduct","Type","CsTransactionTypes","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");
$bodyContent = ["exclude_list" => $excludeList];
$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resPlant = $resDropdownsAPI->Data->Plant;

$apiList = API_STORE_TENANTS;

$TenantID = isset($_GET["TenantID"]) && !empty($_GET["TenantID"]) ? $_GET["TenantID"] : "";

$bodyContent = array(
    "TenantID" => $TenantID
);

if ($TenantID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    // var_dump($result);

    $Data = $result->Data[0];

    // var_dump($Data);
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
                                <form role="form" method="post" id="frmCreateTenants">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtTenantName">Tenant Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtTenantName' id="txtTenantName" placeholder="Enter Name" <?php echo (!empty($TenantID) ? "readonly" : "") ?> value="<?php echo $Data->TenantName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtTenantAddress">Tenant Address<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtTenantAddress' id="txtTenantAddress" placeholder="Enter Address" <?php echo (!empty($TenantID) ? "readonly" : "") ?> value="<?php echo $Data->TenantAddress ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpTenantType">Tenant Type<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpTenantType" id="drpTenantType" <?php echo (!empty($TenantID) ? "disabled" : "") ?> value='<?php echo $Data->TenantType ?>'>
                                                <option value="" <?php echo ($Data->TenantType == '') ? 'selected' : ''; ?>>Select</option>
                                                <option value="1" <?php echo ($Data->TenantType == '1') ? 'selected' : ''; ?>>For Own</option>
                                                <option value="2" <?php echo ($Data->TenantType == '2') ? 'selected' : ''; ?>>For Others</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpProcessor">Processor<span id="spanProcessor" style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpProcessor" id="drpProcessor" <?php echo (!empty($TenantID) ? "disabled" : "") ?>>
                                                <option value="" <?php echo ($Data->StationName == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resPlant as $item) { ?>
                                                    <option value="<?php echo $item->ProcessingStationID ?>" <?php echo ($Data->StationName == $item->StationName) ? 'selected' : ''; ?>> <?php echo $item->StationName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span></label>
                                            <?php // var_dump($Data->TenantStatus) 
                                            ?>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php if (isset($_GET['TenantID']) && !empty($_GET['TenantID'])) { ?>
                                                    <option value="1" <?php echo ($Data->TenantStatus == "1") ? 'selected' : ''; ?>>Active</option>
                                                    <option value="0" <?php echo ($Data->TenantStatus == "0") ? 'selected' : ''; ?>>In-Active</option>
                                                <?php } else { ?>
                                                    <option value="">Select</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">In - Active</option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $TenantID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="cs-tenants-list" class="btn btn-danger">Close</a>
                                            <?php if ($TenantID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Submit</button>
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
        var tenantID = "<?php echo isset($_GET["TenantID"]) && !empty($_GET["TenantID"]) ? $_GET["TenantID"] : "" ?>";



        jQuery.validator.addMethod(
            "noSpace",
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );


        $(document).ready(function() {
            $("#drpTenantType").on("change", function() {
                var type = $(this).val();
                if (type == 2) {
                    $("#spanProcessor").text("");
                } else {
                    $("#spanProcessor").text("*");
                }
            });

            $("#frmCreateTenants").validate({

                onkeyup: false,
                onfocusout: false,
                errorClass: "error",
                invalidHandler: function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        var firstInvalidElement = $(validator.errorList[0].element);
                        firstInvalidElement.focus();
                    }
                },
                ignore: "",
                rules: {
                    txtTenantName: {
                        required: true,
                        noSpace: true
                    },
                    txtTenantAddress: {
                        required: true,
                        noSpace: true
                    },
                    drpTenantType: {
                        required: true,
                    },
                    drpProcessor: {
                        required: function() {
                            return ($("#drpTenantType").val() == 1 || $("#drpTenantType").val() == '');
                        }
                    },
                    drpStatus: {
                        required: true,
                    },
                },
                errorPlacement: function() {
                    return false; // suppresses error message text
                },
                submitHandler: function() {

                    $.ajax({
                        url: 'Ajax/ajax-cs-create-tenants.php',
                        type: 'POST',
                        data: {
                            // methodType: "post",
                            tenantID: tenantID,
                            tenantName: $("#txtTenantName").val(),
                            tenantAddress: $("#txtTenantAddress").val(),
                            tenantType: $("#drpTenantType").val(),
                            processor: $("#drpProcessor").val(),
                            status: $("#drpStatus").val(),
                        },
                        success: function(data) {
                            console.log(data);
                            if (data.Error != '') {
                                alert(data.Msg);
                                window.location.href = "cs-tenants-list.php";
                            } else {
                                alert(data.Error);
                            }

                            return false;
                        }
                    });

                    return false;
                }
            });

            $("#drpTenantType").change(function() {
                var tenantTypeValue = $(this).val();
                var processorField = $("#drpProcessor");

                if (tenantTypeValue == 1 || tenantTypeValue == '') {
                    processorField.rules("add", {
                        required: true
                    });
                    processorField.focus();
                } else if (tenantTypeValue == 2) {
                    processorField.rules("remove", "required");
                    processorField.removeClass("error");
                }
            });
        });
    </script>

    </body>

</html>