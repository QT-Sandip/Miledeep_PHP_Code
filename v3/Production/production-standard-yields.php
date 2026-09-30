<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$api = API_COLDSTORE_DROPDOWNS;
$excludeList = array("ColdStores","Blocks","Floors","StoreRacks","Stock","StockBelongsTo","ProductGroup","Exporters","Consignee","Processor","Shifts","PackingType","ProductCategory","Abstatus","OptType","OutType","Variety","VarietyList","StockAvailableVariety","StockInVarietyList","PoNumber","PackingStyle","Plant","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Tranction","Type","CsTransactionTypes","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");
$bodyContent = ["exclude_list" => $excludeList];
$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resSpecies = $resDropdownsAPI->Data->Species;
$resTypeProduct = $resDropdownsAPI->Data->TypeProduct;
$resProductForm = $resDropdownsAPI->Data->ProductForm;
$resFreezingType = $resDropdownsAPI->Data->FreezingType;
$resPreparation = $resDropdownsAPI->Data->Preparation;
$resGlazeType = $resDropdownsAPI->Data->GlazeType;
$resSoaking = $resDropdownsAPI->Data->Soaking;
$resGrade = $resDropdownsAPI->Data->Grade;

$apiList = API_PRODUCTION_STANDARD_YIELDS;

$YieldID = isset($_GET["YieldID"]) && !empty($_GET["YieldID"]) ? $_GET["YieldID"] : "";

$bodyContent = array(
    "YieldID" => $YieldID
);

if ($YieldID != '') {
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
                                <form role="form" method="post" id="frmGradeMaster">
                                    <div class="row">

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpSpecies" class="form-label">Species<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpSpecies" id="drpSpecies">
                                                <option value="" <?php echo ($Data->Species == '') ? 'selected' : ''; ?>>
                                                    Select</option>
                                                <?php foreach ($resSpecies as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>" <?php echo ($Data->Species == $item->ShortCode) ? 'selected' : ''; ?>>
                                                        <?php echo $item->SpeciesName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpVariety" class="form-label">Variety<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpVariety" id="drpVariety">
                                                <option value="" <?php echo ($Data->Variety == '') ? 'selected' : ''; ?>>
                                                    Select</option>
                                                <?php foreach ($resProductForm as $item) { ?>
                                                    <option value="<?php echo $item->ProductID; ?>" <?php echo ($Data->ProductID == $item->ProductID) ? 'selected' : ''; ?>>
                                                        <?php echo $item->ShortName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>



                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpTypeProduct" class="form-label">Type of Product<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpTypeProduct"
                                                id="drpTypeProduct">
                                                <option value="" <?php echo ($Data->TypeProduct == '') ? 'selected' : ''; ?>> Select</option>
                                                <?php foreach ($resTypeProduct as $item) { ?>
                                                    <option value="<?php echo $item; ?>" <?php echo ($Data->TypeProduct == $item) ? 'selected' : ''; ?>>
                                                        <?php echo $item; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3" id="drpGradeWrapper">
                                            <label for="drpGrade" class="form-label">Grade<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpGrade" id="drpGrade"
                                                disabled>
                                                <option value="" <?php echo ($Data->Grade == '') ? 'selected' : ''; ?>>
                                                    Select</option>
                                                <?php foreach ($resGrade as $item) { ?>
                                                    <option value="<?php echo $item->Options; ?>" <?php echo ($Data->Grade == $item->Options) ? 'selected' : ''; ?>>
                                                        <?php echo $item->Options; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCount">Count<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtCount' id="txtCount"
                                                placeholder="Enter Count"
                                                value="<?php echo $Data->Count ?>">
                                        </div>


                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpFreezingType" class="form-label">Freezing Type<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpFreezingType"
                                                id="drpFreezingType">
                                                <option value="" <?php echo ($Data->FreezingType == '') ? 'selected' : ''; ?>> Select</option>
                                                <?php foreach ($resFreezingType as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>" <?php echo ($Data->FreezingType == $item->ShortCode) ? 'selected' : ''; ?>>
                                                        <?php echo $item->FreezingTypeName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>



                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpTreatment" class="form-label">Treatment<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpTreatment"
                                                id="drpTreatment">
                                                <option value="" <?php echo ($Data->Treatment == '') ? 'selected' : ''; ?>> Select</option>
                                                <?php foreach ($resSoaking as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>" <?php echo ($Data->Treatment == $item->ShortCode) ? 'selected' : ''; ?>>
                                                        <?php echo $item->TreatmentName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <!-- <div class="col col-md-3 mb-3">
                                            <label for="drpTypeofMaterial">Type of Material<span
                                                    style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpTypeofMaterial" id="drpTypeofMaterial">
                                                <?php if (isset($_GET['YieldID']) && !empty($_GET['YieldID'])) { ?>
                                                    <option value="HL" <?php echo ($Data->TypeOfMaterial == "HL") ? 'selected' : ''; ?>>
                                                        HL</option>
                                                    <option value="HEAD ON" <?php echo ($Data->TypeOfMaterial == "HEAD ON") ? 'selected' : ''; ?>>
                                                        HEAD ON</option>
                                                <?php } else { ?>
                                                    <option value="HL" selected>HL</option>
                                                    <option value="HEAD ON">HEAD ON</option>
                                                <?php } ?>
                                            </select>
                                        </div> -->

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpTypeofMaterial">Type of Material<span
                                                    style="color:red">*</span></label>
                                            <select class="form-control" name="drpTypeofMaterial"
                                                id="drpTypeofMaterial">
                                                <option value="HL" <?= ($Data->TypeOfMaterial == 'HL') ? 'selected' : '' ?>>HL</option>
                                                <option value="HEAD ON" <?= ($Data->TypeOfMaterial == 'HEAD ON') ? 'selected' : '' ?>>HEAD ON</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtYield">Yield %<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtYield' id="txtYield"
                                                placeholder="Enter Yield Percentage"
                                                value="<?php echo $Data->YieldPercentage ?>">
                                        </div>

                                        <!-- <div class="col col-md-3 mb-3">
                                            <label for="txtEffectedDate">Effected Date<span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" name='txtEffectedDate'
                                                id="txtEffectedDate" placeholder="Enter "
                                                value="<?php echo $Data->Yield ?>">
                                        </div> -->



                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php if (isset($_GET['YieldID']) && !empty($_GET['YieldID'])) { ?>
                                                    <option value="1" <?php echo ($Data->Status == "1") ? 'selected' : ''; ?>>
                                                        Active</option>
                                                    <option value="0" <?php echo ($Data->Status == "0") ? 'selected' : ''; ?>>
                                                        In-Active</option>
                                                <?php } else { ?>
                                                    <option value="">All</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">In-Active</option>
                                                <?php } ?>
                                            </select>
                                        </div>


                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                                value="<?php echo $YieldID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="production-standard-yields-list.php"
                                                class="btn btn-danger">Close</a>
                                            <?php if ($YieldID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit"
                                                    id="submit">Submit</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit"
                                                    id="submit">Update</button>
                                            <?php } ?>

                                        </div>

                                    </div>
                                </form>

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>
    <script>
        var yieldID = "<?php echo isset($_GET["YieldID"]) && !empty($_GET["YieldID"]) ? $_GET["YieldID"] : "" ?>";

        jQuery.validator.addMethod(
            "noSpace",
            function (value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );

        function toggleGradeField() {
            const material = $("#drpTypeofMaterial").val();

            if (material === "HEAD ON") {
                $("#txtCount").prop("disabled", false);
                $("#drpGrade").prop("disabled", false);
                $("#drpGrade").rules("add", {
                    required: true,
                    messages: {
                        required: "Grade is required when Type of Material is HEAD ON"
                    }
                });
                $("#txtCount").rules("add", {
                    required: true,
                    messages: {
                        required: "Count is required when Type of Material is HEAD ON"
                    }
                });
            } else {
                $("#drpGrade").rules("remove");
                $("#drpGrade").val(""); // Clear the value
                $("#drpGrade").prop("disabled", true);

                $("#txtCount").rules("remove");
                $("#txtCount").val(""); // Clear the value
                $("#txtCount").prop("disabled", true);

                // If using Select2, also clear the visual
                if ($("#drpGrade").hasClass("select2-hidden-accessible")) {
                    $("#drpGrade").val(null).trigger("change");
                }
            }
        }


        // $("#txtYield").on("keyup", function () {
        //     var val = $(this).val();
        //     if (val > 100) {
        //         $(this).val("");
        //         alert("Yield Exceeds");
        //     }
        // });

        $(document).ready(function () {
            toggleGradeField(); // Initial call

            $('#drpTypeofMaterial').on('change', function () {
                toggleGradeField(); // Recheck on change
            });
        });


            $("#frmGradeMaster").validate({

                onkeyup: false,
                onfocusout: false,
                invalidHandler: function (form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        var firstInvalidElement = $(validator.errorList[0].element);
                        firstInvalidElement.focus();
                    }
                },
                ignore: "readonly",
                rules: {
                    drpSpecies: {
                        required: true,
                    },
                    drpTypeProduct: {
                        required: true,
                    },
                    drpVariety: {
                        required: true,
                    },
                    drpFreezingType: {
                        required: true,
                    },

                    drpTypeofMaterial: {
                        required: true,
                    },

                    drpTreatment: {
                        required: true,
                    },

                    drpStatus: {
                        required: true,
                    },

                    txtYield: {
                        required: true,
                    },
                },
                errorPlacement: function () {
                    return false; // suppresses error message text
                },
                submitHandler: function () {

                    $.ajax({
                        url: 'Ajax/ajax-production-standard-yields.php',
                        type: 'POST',
                        data: {
                            methodType: "post",
                            yieldID: yieldID,
                            species: $("#drpSpecies").val(),
                            typeProduct: $("#drpTypeProduct").val(),
                            variety: $("#drpVariety").val(),
                            freezingType: $("#drpFreezingType").val(),
                            typeofMaterial: $("#drpTypeofMaterial").val(),
                            treatment: $("#drpTreatment").val(),
                            yield: $("#txtYield").val(),
                            count: $("#txtCount").val(),
                            status: $("#drpStatus").val(),
                            Grade: $("#drpGrade").val()
                        },
                        success: function (data) {
                            console.log(data);
                            if (data.Error == null) {
                                alert(data.Msg);
                                window.location.href = "production-standard-yields-list.php";
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