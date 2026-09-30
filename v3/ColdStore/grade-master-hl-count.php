<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COLDSTORE_DROPDOWNS;
$excludeList = array("ColdStores","Blocks","Floors","StoreRacks","Stock","StockBelongsTo","ProductGroup","Exporters","Consignee","Processor","Shifts","PackingType","ProductCategory","Abstatus","OptType","OutType","Variety","VarietyList","StockAvailableVariety","StockInVarietyList","PoNumber","PackingStyle","Plant","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Tranction","Type","CsTransactionTypes","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");
$bodyContent = ["exclude_list" => $excludeList];
$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

// var_dump($resDropdownsAPI);
$resSpecies = $resDropdownsAPI->Data->Species;
$resTypeProduct = $resDropdownsAPI->Data->TypeProduct;
$resProductForm = $resDropdownsAPI->Data->ProductForm;
$resFreezingType = $resDropdownsAPI->Data->FreezingType;
$resPreparation = $resDropdownsAPI->Data->Preparation;
$resGlazeType = $resDropdownsAPI->Data->GlazeType;
$resSoaking = $resDropdownsAPI->Data->Soaking;
$resGrade = $resDropdownsAPI->Data->Grade;

$apiList = API_GRADE_MASTER_HL_COUNT;

$HeadonDataID = isset($_GET["HeadonDataID"]) && !empty($_GET["HeadonDataID"]) ? $_GET["HeadonDataID"] : "";

$bodyContent = array(
    "HeadonDataID" => $HeadonDataID
);

if ($HeadonDataID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    // var_dump($result);

    $Data = $result->Data[0];
}
// echo json_encode($Data);


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
                                            <label for="drpSpecies" class="form-label">Species<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpSpecies" id="drpSpecies">
                                                <option value="" <?php echo ($Data->Species == '') ? 'selected' : '';  ?>> Select</option>
                                                <?php foreach ($resSpecies as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>" <?php echo ($Data->Species == $item->ShortCode) ? 'selected' : ''; ?>>
                                                        <?php echo $item->SpeciesName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpTypeProduct" class="form-label">Type of Product<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpTypeProduct" id="drpTypeProduct">
                                                <option value="" <?php echo ($Data->TypeProduct == '') ? 'selected' : '';  ?>> Select</option>
                                                <?php foreach ($resTypeProduct as $item) { ?>
                                                    <option value="<?php echo $item; ?>" <?php echo ($Data->TypeProduct == $item) ? 'selected' : ''; ?>>
                                                        <?php echo $item; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProductForm" class="form-label">Product Form<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpProductForm" id="drpProductForm">
                                                <option value="" <?php echo ($Data->ShortName == '') ? 'selected' : '';  ?>> Select</option>
                                                <?php foreach ($resProductForm as $item) { ?>
                                                    <option value="<?php echo $item->ShortName; ?>" <?php echo ($Data->ShortName == $item->ShortName) ? 'selected' : ''; ?>>
                                                        <?php echo $item->ShortName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpFreezingType" class="form-label">Freezing Type<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpFreezingType" id="drpFreezingType">
                                                <option value="" <?php echo ($Data->FreezingType == '') ? 'selected' : '';  ?>> Select</option>
                                                <?php foreach ($resFreezingType as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>" <?php echo ($Data->FreezingType == $item->ShortCode) ? 'selected' : ''; ?>>
                                                        <?php echo $item->FreezingTypeName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                     <!--   <div class="form-group col-sm-3 mb-3">
                                            <label for="drpPreparation" class="form-label">Preparation<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpPreparation" id="drpPreparation">
                                                <option value="" <?php echo ($Data->PreparationType == '') ? 'selected' : '';  ?>> Select</option>
                                                <?php foreach ($resPreparation as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>" <?php echo ($Data->PreparationType == $item->ShortCode) ? 'selected' : ''; ?>>
                                                        <?php echo $item->PreparationName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpGlazeStatus" class="form-label">Glaze Status<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpGlazeStatus" id="drpGlazeStatus">
                                                <option value="">Select</option>
                                                <option value="YES" <?php echo (($Data->GlazeStatus == "YES") ? 'selected' : '') ?>>YES</option>
                                                <option value="NO" <?php echo (($Data->GlazeStatus == "NO") ? 'selected' : '') ?>>NO</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtGlazePerc">Glaze % </label>
                                            <input type="text" class="form-control" name='txtGlazePerc' id="txtGlazePerc" placeholder="Enter Store Name" value="<?php echo $Data->GlazePercentage ?>">
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpGlazeType" class="form-label">Glaze Type<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpGlazeType" id="drpGlazeType">
                                                <option value="" <?php echo ($Data->GlazeType == '') ? 'selected' : '';  ?>> Select</option>
                                                <?php foreach ($resGlazeType as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>" <?php echo ($Data->GlazeType == $item->ShortCode) ? 'selected' : ''; ?>>
                                                        <?php echo $item->GlazeType; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>-->


                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpTreatment" class="form-label">Treatment<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpTreatment" id="drpTreatment">
                                                <option value="" <?php echo ($Data->Treatment == '') ? 'selected' : '';  ?>> Select</option>
                                                <?php foreach ($resSoaking as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>" <?php echo ($Data->Treatment == $item->ShortCode) ? 'selected' : ''; ?>>
                                                        <?php echo $item->TreatmentName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div> 
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpGrade" class="form-label">Grade<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpGrade" id="drpGrade">
                                                <option value="" <?php echo ($Data->Grade == '') ? 'selected' : '';  ?>> Select</option>
                                                <?php foreach ($resGrade as $item) { ?>
                                                    <option value="<?php echo $item->value; ?>" <?php echo ($Data->Grade == $item->value) ? 'selected' : ''; ?>>
                                                        <?php echo $item->value; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtPackingCount">Packing Count<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtPackingCount' id="txtPackingCount" placeholder="Enter Count" value="<?php echo $Data->PackingCount ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtHonCount">Hon Count<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtHonCount' id="txtHonCount" placeholder="Enter HON Count" value="<?php echo $Data->HeadonCount ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtYield">Yield %<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtYield' id="txtYield" placeholder="Enter Yield Percentage" value="<?php echo $Data->Yield ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php if (isset($_GET['HeadonDataID']) && !empty($_GET['HeadonDataID'])) { ?>
                                                    <option value="1" <?php echo ($Data->Status == "1") ? 'selected' : ''; ?>>Active</option>
                                                    <option value="0" <?php echo ($Data->Status == "0") ? 'selected' : ''; ?>>In-Active</option>
                                                <?php } else { ?>
                                                    <option value="">All</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">In-Active</option>
                                                <?php } ?>
                                            </select>
                                        </div>


                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $HeadonDataID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="grade-master-hl-count-list" class="btn btn-danger">Close</a>
                                            <?php if ($HeadonDataID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Submit</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit" id="submit">Update</button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>
    <script>
        var headonDataID = "<?php echo isset($_GET["HeadonDataID"]) && !empty($_GET["HeadonDataID"]) ? $_GET["HeadonDataID"] : "" ?>";

        jQuery.validator.addMethod(
            "noSpace",
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );

        $(document).ready(function() {
            $("#drpGlazeStatus").on("change", function() {
                var value = $(this).val();
                console.log(value);
                if (value == 'NO') {
                    $("#txtGlazePerc").prop('readonly', true);
                } else {
                    $("#txtGlazePerc").prop('readonly', false);
                }
            });

        });

        $("#txtYield").on("keyup", function() {
            var val = $(this).val();
            if (val > 100) {
                $(this).val("");
                alert("Yield Exceeds");
            }
        });


        $("#drpPreparation").on("change", function() {
            var freezingType = $("#drpFreezingType").val();
            var typeProduct = $("#drpTypeProduct").val();
            var preparation = $("#drpPreparation").val();

            // console.log('freezingType ' + freezingType );
            // console.log('typeProduct ' + typeProduct );
            // console.log('preparation ' + preparation );

            $.ajax({ 
                url: 'Ajax/ajax-grade-master-hl-count.php',
                type: 'POST',
                data: {
                    methodType: "checkCombo",
                    freezingType: freezingType,
                    typeProduct: typeProduct,
                    preparation: preparation,
                },
                success: function(data) {
                    console.log(data);
                    if (data.Error != null) {
                        alert(data.Error);
                    }
                    return false;
                }
            })


        });

        $("#frmGradeMaster").validate({

            onkeyup: false,
            onfocusout: false,
            invalidHandler: function(form, validator) {
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
                drpProductForm: {
                    required: true,
                },
                drpFreezingType: {
                    required: true,
                },
                drpPreparation: {
                    required: true,
                },
                drpGlazeStatus: {
                    required: true,
                },
                // txtGlazePerc: {
                //     required: true,
                // },
                drpGlazeType: {
                    required: true,
                },
                drpTreatment: {
                    required: true,
                },
                drpGrade: {
                    required: true,
                },
                drpStatus: {
                    required: true,
                },
                // txtHonCount: {
                //     required: true,
                // },
                // txtYield: {
                //     required: true,
                // },
            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/ajax-grade-master-hl-count.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        headonDataID: headonDataID,
                        species: $("#drpSpecies").val(),
                        typeProduct: $("#drpTypeProduct").val(),
                        productForm: $("#drpProductForm").val(),
                        freezingType: $("#drpFreezingType").val(),
                        preparation: $("#drpPreparation").val(),
                        glazeStatus: $("#drpGlazeStatus").val(),
                        glazePerc: $("#txtGlazePerc").val(),
                        glazeType: $("#drpGlazeType").val(),
                        treatment: $("#drpTreatment").val(),
                        grade: $("#drpGrade").val(),
                        packingCount: $("#txtPackingCount").val(),
                        honCount: $("#txtHonCount").val(),
                        yield: $("#txtYield").val(),
                        status: $("#drpStatus").val(),
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error == null) {
                            alert(data.Msg);
                            window.location.href = "grade-master-hl-count-list";
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