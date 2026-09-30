 <?php

$PageTitle = !empty($_GET['id'])?"Edit":"Add";
$PageTitleFolder = "Quality Assurance Department";
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once ROOT . 'config/api-call.php';

$res = [];
if(($_GET['id'])){
    $bodyContent = ['AssuranceId'=>$_GET['id']];

    $api = API_QC_QUALITY_ASSURANCE_CHECK_LIST_GET;
    $results = CallAPI($api, "GET", "", $bodyContent);
    $res = !empty($results->Data)?$results->Data:[];
}

// var_dump($ArrivalID);
// exit;

$bodyContent = [];
$api = API_QC_QUALITY_ASSURANCE_CHECK_LIST_DROPDOWNS;
$dropDownData = CallAPI($api, "GET", "", $bodyContent);
$Grade = !empty($dropDownData->Data->Grade) ? $dropDownData->Data->Grade : [];
$PackingType = !empty($dropDownData->Data->PackingType) ? $dropDownData->Data->PackingType : [];
$Brand = !empty($dropDownData->Data->Brand) ? $dropDownData->Data->Brand : [];

//echo '<pre>';print_r($dropDownData);die;
?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>
    

</head>

<?php include_once ROOT . 'includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . 'includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php //include_once ROOT . 'includes/page-header.php' ?>
                <?php include($IncludesPath . "page-title.php"); ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">

                             
                                <form id="frmDeHeading">
                                    <div class="row">

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="ArrivalDate" class="form-label">Arrival Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="ArrivalDate"
                                                name="ArrivalDate" placeholder=""
                                                value="<?php echo !empty($res->ArrivalDate)?$res->ArrivalDate:date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>


                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="BatchCode" class="form-label">Batch Code<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control  BatchCode form-control " name="BatchCode[]"
                                                id="BatchCode" required
                                                    multiple="multiple"> 
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        
                                           

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="ProductionCode" class="form-label">Production Code</label>
                                            <input type="text" class="form-control" id="ProductionCode" name="ProductionCode"
                                                placeholder="Enter Production Code" value="<?php echo !empty($res->ProductionCode)?$res->ProductionCode:'';?>" >
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="InvoiceNo" class="form-label">Invoice No</label>
                                            <input type="text" class="form-control" id="InvoiceNo" name="InvoiceNo"
                                                placeholder="Enter Invoice No" value="<?php echo !empty($res->InvoiceNo)?$res->InvoiceNo:'';?>" >
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="Brand" class="form-label">Brand</label>
                                           <!--  <input type="text" class="form-control" id="Brand" name="Brand"
                                                placeholder="Enter Brand" value="" > -->
                                            <select class="drpSelect form-control  select2 form-control " name="Brand"
                                                     id="Brand" required> 
                                                <option value=""> Select</option>
                                                <?php 
                                                if(!empty($Brand)){
                                                    foreach($Brand as $val){?>
                                                        <option <?php echo !empty($res->Brand) && $res->Brand == $val->BrandID?'selected="selected"':'';?> value="<?php echo $val->BrandID;?>"> <?php echo $val->BrandName;?></option>
                                                   <?php }
                                                }?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="Variety" class="form-label">Variety<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="Variety" name="Variety"
                                                placeholder="Enter Variety" value="<?php echo !empty($res->Variety)?$res->Variety:'HON-HL';?>" >
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="TypeOfPacking" class="form-label">Type of Packing<span style="color:red">*</span></label>
                                            <!-- <input type="text" class="form-control" id="TypeOfPacking" name="TypeOfPacking"
                                                placeholder="Enter Type of Packing" value="" > -->
                                            <select class="drpSelect form-control  select2 form-control " name="TypeOfPacking"
                                                     id="TypeOfPacking" required> 
                                                <option value=""> Select</option>
                                                <?php 
                                                if(!empty($PackingType)){
                                                    foreach($PackingType as $val){?>
                                                        <option <?php echo !empty($res->TypeOfPacking) && $res->TypeOfPacking == $val->OptID?'selected="selected"':'';?> value="<?php echo $val->OptID;?>"> <?php echo $val->Value;?></option>
                                                   <?php }
                                                }?>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="DiclaredSizeGrade" class="form-label">Diclared Size Grade <span style="color:red">*</span></label>
                                            <!-- <input type="text" class="form-control" id="DiclaredSizeGrade" name="DiclaredSizeGrade"
                                                placeholder="Enter Diclared Size Grade" value="" > -->

                                            <select class="drpSelect form-control  select2 form-control " name="DiclaredSizeGrade"
                                                     id="DiclaredSizeGrade" required> 
                                                <option value=""> Select</option>
                                                <?php 
                                                if(!empty($Grade)){
                                                    foreach($Grade as $val){?>
                                                        <option  <?php echo !empty($res->DiclaredSizeGrade) && $res->DiclaredSizeGrade == $val?'selected="selected"':'';?> value="<?php echo $val;?>"> <?php echo $val;?></option>
                                                   <?php }
                                                }?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="GeneralAppearance" class="form-label">General Appearance</label>
                                            <input type="text" class="form-control" id="GeneralAppearance"
                                                name="GeneralAppearance" value="<?php echo !empty($res->GeneralAppearance)?$res->GeneralAppearance:'Good';?>" >
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="Colour" class="form-label">Colour</label>
                                            <input type="text" class="form-control" id="Colour" name="Colour"
                                                placeholder="Enter Colour" value="<?php echo !empty($res->Colour)?$res->Colour:'light';?>" >
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="Freshness" class="form-label">Freshness</label>
                                            <input type="text" class="form-control" id="Freshness"
                                                name="Freshness" value="<?php echo !empty($res->Freshness)?$res->Freshness:'good';?>" >
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="Odour" class="form-label">Odour</label>
                                            <input type="text" class="form-control" id="Odour" name="Odour"
                                                value="<?php echo !empty($res->Odour)?$res->Odour:'Fresh';?>" >
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="Texture" class="form-label">Texture</label>
                                            <input type="text" class="form-control" id="Texture" name="Texture"
                                                value="<?php echo !empty($res->Texture)?$res->Texture:'Firm';?>" >
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="Temperature" class="form-label">Temperature<span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="Temperature"
                                                name="Temperature" value="<?php echo !empty($res->Temperature)?$res->Temperature:'';?>">
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="FillingWeightInGms" class="form-label">Filling Weight in Gms<span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="FillingWeightInGms"
                                                name="FillingWeightInGms" placeholder="Enter Filling Weight in Gms" value="<?php echo !empty($res->FillingWeightInGms)?$res->FillingWeightInGms:'';?>">
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="DeglazedWt" class="form-label">Deglazed wt<span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="DeglazedWt"
                                                name="DeglazedWt" placeholder="Enter Deglazed wt" value="<?php echo !empty($res->DeglazedWt)?$res->DeglazedWt:'';?>">
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="Glaze" class="form-label">Glaze %<span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="Glaze"
                                                name="Glaze" placeholder="Enter Glaze %" value="<?php echo !empty($res->Glaze)?$res->Glaze:'';?>">
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="Count" class="form-label">Count<span style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="Count" name="Count"
                                                placeholder="Enter Count"  value="<?php echo !empty($res->Count)?$res->Count:'';?>">
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="BigPieces" class="form-label">Big Pieces (gms)<span
                                                    style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="BigPieces"
                                                name="BigPieces"  placeholder="Enter Big Pieces (gms)" value="<?php echo !empty($res->BigPieces)?$res->BigPieces:'';?>">
                                        </div>
                                         <div class="form-group col-sm-3 mb-3">
                                            <label for="SmallPieces" class="form-label">Small Pieces (gms)<span style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="SmallPieces" name="SmallPieces"
                                                placeholder="Enter Small Pieces (gms)"  value="<?php echo !empty($res->SmallPieces)?$res->SmallPieces:'';?>">
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="Uniformity" class="form-label">Uniformity<span style="color:red">*</span></label>
                                            <input type="number" class="form-control" id="Uniformity" name="Uniformity"
                                                placeholder="Enter Uniformity" value="<?php echo !empty($res->Uniformity)?$res->Uniformity:'';?>" >
                                        </div>
                                       <div class="form-group col-sm-3 mb-3">
                                            <label for="TotalPiecesPerSlab" class="form-label">Total Pieces / Slab </label>
                                            <input type="number" class="form-control" id="TotalPiecesPerSlab" name="TotalPiecesPerSlab"
                                                placeholder="Enter Total Pieces / Slab " value="<?php echo !empty($res->TotalPiecesPerSlab)?$res->TotalPiecesPerSlab:'';?>" >
                                        </div>
                                         <div class="form-group col-sm-3 mb-3">
                                            <label for="Dehydration" class="form-label">Dehydration</label>
                                            <input type="text" class="form-control" id="Dehydration" name="Dehydration"
                                                placeholder="Enter Dehydration"  value="<?php echo !empty($res->Dehydration)?$res->Dehydration:'';?>">
                                        </div>
                                         <div class="form-group col-sm-3 mb-3">
                                            <label for="Discolouration" class="form-label">Discolouration</label>
                                            <input type="text" class="form-control" id="Discolouration"
                                                name="Discolouration" placeholder="Enter Discolouration" value="<?php echo !empty($res->Discolouration)?$res->Discolouration:'';?>">
                                        </div>
                                         <div class="form-group col-sm-3 mb-3">
                                            <label for="BlackSpotsPerTails" class="form-label">Black Spots / Tails</label>
                                            <input type="text" class="form-control" id="BlackSpotsPerTails" name="BlackSpotsPerTails"
                                                placeholder="Enter Black Spots / Tails"  value="<?php echo !empty($res->BlackSpotsPerTails)?$res->BlackSpotsPerTails:'';?>">
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="BKNDamagedPieces" class="form-label">BKN Damaged Pieces</label>
                                            <input type="text" class="form-control" id="BKNDamagedPieces"
                                                name="BKNDamagedPieces"   placeholder="Enter BKN Damaged Pieces"  value="<?php echo !empty($res->BKNDamagedPieces)?$res->BKNDamagedPieces:'';?>">
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="LegsVeinsShellBits" class="form-label">Legs, Veins, Shell Bits</label>
                                            <input type="text" class="form-control" id="LegsVeinsShellBits" name="LegsVeinsShellBits"
                                                placeholder="Enter Legs, Veins, Shell Bits" value="<?php echo !empty($res->LegsVeinsShellBits)?$res->LegsVeinsShellBits:'';?>" >
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="HangingMeat" class="form-label">Hanging Meat</label>
                                            <input type="text" class="form-control" id="HangingMeat" name="HangingMeat"
                                                placeholder="Enter Hanging Meat"  value="<?php echo !empty($res->HangingMeat)?$res->HangingMeat:'';?>">
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="SoftShell" class="form-label">Soft Shell</label>
                                            <input type="text" class="form-control" id="SoftShell"
                                                name="SoftShell"  placeholder="Enter Soft Shell"  value="<?php echo !empty($res->SoftShell)?$res->SoftShell:'';?>">
                                        </div>

                                       <div class="form-group col-sm-3 mb-3">
                                            <label for="ForeignMatter" class="form-label">Foreign Matter</label>
                                            <input type="text" class="form-control" id="ForeignMatter"
                                                name="ForeignMatter" value="Grass" placeholder="Enter Foreign Matter<"  value="<?php echo !empty($res->ForeignMatter)?$res->ForeignMatter:'';?>">
                                        </div>

                                        
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="TailBKN" class="form-label">Tail BKN</label>
                                            <input type="text" class="form-control" id="TailBKN"
                                                name="TailBKN" placeholder="Enter Tail BKN" value="<?php echo !empty($res->TailBKN)?$res->TailBKN:'';?>">
                                        </div>

                                       

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="TotalPercentageOfDefect" class="form-label">Total % of Defect</label>
                                            <input type="text" class="form-control" id="TotalPercentageOfDefect"
                                                name="TotalPercentageOfDefect" placeholder="Enter Total % of Defect" value="<?php echo !empty($res->TotalPercentageOfDefect)?$res->TotalPercentageOfDefect:'';?>">
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="SailingIntact" class="form-label">Sailing Intact</label>
                                            <input type="text" class="form-control" id="SailingIntact"
                                                name="SailingIntact" placeholder="Enter Sailing Intact"  value="<?php echo !empty($res->Remarks)?$res->Remarks:'';?>"/>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="Remarks" class="form-label">Remarks</label>
                                            <input type="text" class="form-control" id="Remarks"
                                                name="Remarks" placeholder="Enter Remarks"  value="<?php echo !empty($res->Remarks)?$res->Remarks:'';?>"/>
                                        </div>

                                        
                                         

                                    <div class="text-center mt-4">
                                        <input type="hidden" name="UserId" value="<?php echo !empty($_SESSION['UserID']) ? $_SESSION['UserID'] : ''; ?>">
                                        <input type="hidden" id="AssuranceId" name="AssuranceId" value="<?php echo !empty($_GET['id'])?$_GET['id']:'';?>">
                                        <a href="quality-assurance-check-list.php" class="btn btn-danger">Close</a>

                                        <button class="btn btn-primary"  name="submit" value="submit" id="submit" type="submit">
                                            <?php
                                            if (empty($_GET['id'])) {
                                                echo "Save";
                                            } else {
                                                echo "Update";
                                            }
                                            ?>
                                        </button>

                                    </div>
                                </form>
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

<?php include_once WORKING_MODULE . 'modalSupplier.php' ?>


<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>

<script>
    $(document).ready(function () {
        

        $(document).ready(function () {
            var AssuranceId = "<?php echo !empty($_GET['id'])?$_GET['id']:'';?>";
            getArrivals(AssuranceId);
            $("#frmDeHeading").validate({
                    onkeyup: false,
                    onfocusout: false,
                    invalidHandler: function (form, validator) {
                        var errors = validator.numberOfInvalids();
                        if (errors) {
                            var firstInvalidElement = $(validator.errorList[0].element);
                            firstInvalidElement.focus();
                        }
                    },
                    ignore: "",
                    rules: {
                        ArrivalDate:{
                            required: true
                        },
                        'BatchCode[]': {
                            required: true
                        },
                        Variety: {
                            required: true
                        },
                        Temperature: {
                            required: true
                        },
                        TypeOfPacking: {
                            required: true
                        },
                        DiclaredSizeGrade: {
                            required: true
                        },
                        FillingWeightInGms: {
                            required: true
                        },
                        DeglazedWt: {
                            required: true
                        },
                        Glaze: {
                            required: true
                        },
                        Count: {
                            required: true
                        },
                        BigPieces: {
                            required: true
                        },
                        SmallPieces: {
                            required: true
                        },
                        Uniformity:{
                             required: true
                        }

                    },
                    messages: {
                        ArrivalDate:{
                            required: "Please Choose Arrival Date."
                        },
                        'BatchCode[]': {
                            required: "Please select a BatchCode."
                        },
                        Variety: {
                            required: "Please Enter Variety."
                        },
                        Temperature: {
                            required:  "Please Enter Temperature."
                        },
                        TypeOfPacking: {
                            required: "Please select a Type Of Packing."
                        },
                        DiclaredSizeGrade: {
                            required: "Please select a Diclared Size Grade."
                        },
                        FillingWeightInGms: {
                            required: "Please Enter FillingWeight In Gms."
                        },
                        DeglazedWt: {
                            required: "Please Enter Deglazed Wt."
                        },
                        Glaze: {
                            required: "Please Enter Glaze."
                        },

                        Count: {
                            required: "Please Enter Count."
                        },
                        BigPieces: {
                            required: "Please Enter Big Pieces."
                        },
                        SmallPieces: {
                            required: "Please Enter Small Pieces."
                        },
                        Uniformity: {
                            required: "Please Enter Uniformity."
                        },


                    },
                    errorPlacement: function () {
                        return false;
                    },
                    invalidHandler: function (form, validator) {
                        if (validator.numberOfInvalids()) {
                            $(validator.errorList[0].element).focus();
                        }
                    },
                    submitHandler: function() {
                        var formData = $('#frmDeHeading').serialize();
                        var submitUrl = "Ajax/ajax-quality-assurance-check-list.php"; 
                        $.ajax({
                            url: submitUrl,
                            type: 'POST',
                            data: formData,
                            success: function(data) {
                                if (data.Error == null) {
                                    alert(data.Msg);
                                    window.location.href = 'quality-assurance-check-list.php';
                                } else {
                                    console.log(data.Error);
                                }
                            }
                        });
                    }
                });



           


            $(document).on("change", "#ArrivalDate", function () {
                var AssuranceId = "<?php echo !empty($_GET['id'])?$_GET['id']:'';?>";
                getArrivals(AssuranceId);
            });

        
            function getArrivals(AssuranceId = '') {
                var batchIds = "<?php echo !empty($res->BatchCode)?$res->BatchCode:''?>";
                var batchIdsArr = batchIds.split(',');
                $.ajax({
                    url: 'Ajax/ajax-quality-assurance-check-list.php',
                    type: 'POST',
                    data: {
                        ArrivalDate: $('#ArrivalDate').val(),
                        methodType: "GetBatchNumbers",
                        AssuranceId:AssuranceId
                    },
                    success: function (data) {
                        //console.log(data);
                        var list = "<option value=''>Select</option>";
                        if (data?.Data?.BatchData) {
                            $.each(data.Data.BatchData, function (index, value) {
                                list += "<option ";
                                list += batchIdsArr.length!=0 && jQuery.inArray(value.ArrivalId, batchIdsArr) !== -1 ?'selected="selected"':'';
                                list +="value='" + value.ArrivalId + "' >" + value.ArrivalNumber + "</option>";
                            });
                        }
                        // console.log(list)
                        $('#BatchCode').select2('destroy');
                        $('#BatchCode').html(list);
                        $('#BatchCode').select2();

                        return false;
                    }
                });
            }
        });
    });
</script>


</script>

</body>

</html> 