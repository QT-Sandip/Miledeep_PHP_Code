 <?php

$PageTitle = !empty($_GET['id'])?"Edit":"Add";
$PageTitleFolder = "Online Organoleptic Inspection Report";
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
$VerifiedBy = !empty($dropDownData->Data->VerifiedBy) ? $dropDownData->Data->VerifiedBy : [];
$CheckedBy = !empty($dropDownData->Data->CheckedBy) ? $dropDownData->Data->CheckedBy : [];
$resFreezingType = $dropDownData->Data->FreezingType;


//echo '<pre>';print_r($res);die;
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
                                            <label for="ArrivalDate" class="form-label">Production Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="ArrivalDate"
                                                name="ArrivalDate" placeholder=""
                                                value="<?php echo !empty($res->ArrivalDate)?$res->ArrivalDate:date("Y-m-d"); ?>"
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="BatchNoType" class="form-label">Type Of Batch</label>
                                            <select class="drpSelect form-control select2 BatchNoType" name="BatchNoType" id="BatchNoType">
                                                <option value="Arrival" <?php echo empty($res->BatchNoType) || $res->BatchNoType == 'Arrival'?'selected="selected"':'';?>>Arrival</option>
                                                <option value="Reglaze" <?php echo !empty($res->BatchNoType) && $res->BatchNoType == 'Reglaze'?'selected="selected"':'';?>>Reglaze</option>
                                                <option value="Melting" <?php echo !empty($res->BatchNoType) && $res->BatchNoType == 'Melting'?'selected="selected"':'';?>>Melting</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="ReglazeBatchNo" class="form-label">Batch No<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="ReglazeBatchNo" name="ReglazeBatchNo"
                                                placeholder="Enter ReglazeBatchNo" value="<?php echo !empty($res->ReglazeBatchNo)?$res->ReglazeBatchNo:'';?>" 
                                                <?php echo empty($res->BatchNoType) || $res->BatchNoType == 'Arrival'?'style="display:none"':'';?>>
                                            <select class="drpSelect form-control  BatchCode form-control " name="BatchCode[]"
                                                id="BatchCode" 
                                                    multiple="multiple" <?php echo !empty($res->BatchNoType) && $res->BatchNoType != 'Arrival'?'style="display:none"':'';?> required> 
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
                                        <div class="col col-md-3 mb-3">
                                            <label for="FreezingType">Freezing Type<span style="color:red"></span> </label>
                                            <select class="drpSelect form-control  select2 form-control" name="FreezingType" id="FreezingType">
                                                <option value="">All</option>
                                                <?php foreach ($resFreezingType as $FreezingType) { ?>>
                                                    <option value="<?php echo $FreezingType->FreezingTypeName ?>" <?php echo !empty($res->FreezingType) && $res->FreezingType == $FreezingType->FreezingTypeName?'selected="selected"':'';?>>
                                                        <?php echo $FreezingType->FreezingTypeName ?>
                                                    </option>
                                                <?php } ?>
                                            <select>
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="Brand" class="form-label">Brand<span style="color:red">*</span></label>
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
                                                placeholder="Enter Variety" value="<?php echo !empty($res->Variety)?$res->Variety:'';?>" >
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
                                                placeholder="Enter Dehydration"  value="<?php echo !empty($res->Dehydration)?$res->Dehydration:'NILL';?>">
                                        </div>
                                         <div class="form-group col-sm-3 mb-3">
                                            <label for="Discolouration" class="form-label">Discolouration</label>
                                            <input type="text" class="form-control" id="Discolouration"
                                                name="Discolouration" placeholder="Enter Discolouration" value="<?php echo !empty($res->Discolouration)?$res->Discolouration:'NILL';?>">
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
                                                name="ForeignMatter" placeholder="Enter Foreign Matter"  value="<?php echo !empty($res->ForeignMatter)?$res->ForeignMatter:'';?>">
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

                                         <div class="form-group col-lg-3">
                                            <label for="CheckedBy">Inspected By<span style="color:red">*</span></label>
                                            <!-- <input type="text" placeholder="Enter Inspected By" class="form-control" name='VerifiedBy' id="VerifiedBy"
                                                value="K.L. Ganesh"> -->
                                            <select class="form-control select2" name="CheckedBy" id="CheckedBy">
                                                <option value="">Select</option>
                                                <option value="Others">Others</option>
                                                <!--  <option value="P.DEEPA" selected>P.DEEPA</option> -->
                                                <?php
                                                if (!empty($CheckedBy)) {
                                                    foreach ($CheckedBy as $reCheckedBy) { ?>
                                                        <option value="<?php echo $reCheckedBy; ?>"  <?php echo !empty($res->CheckedBy) && $res->CheckedBy == $reCheckedBy ?'selected="selected"':'';?>><?php echo $reCheckedBy; ?></option>
                                                <?php  }
                                                } ?>

                                            </select>
                                            <input type="text" placeholder="Enter Checked By" class="form-control" name='CheckedByNew' id="CheckedByNew"
                                                value="<?php echo !empty($res->CheckedBy)?$res->CheckedBy:'';?>" style="display: none;">
                                        </div>

                                        <div class="form-group col-lg-3">
                                            <label for="VerifiedBy">Verified By<span style="color:red">*</span></label>
                                            <!-- <input type="text" placeholder="Enter Verified By" class="form-control" name='VerifiedBy' id="VerifiedBy"
                                                value="K.L. Ganesh"> -->
                                            <select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
                                                <option value="">Select</option>
                                                <option value="Others">Others</option>
                                                <!--  <option value="KL Ganesh" selected>KL Ganesh</option> -->
                                                <?php
                                                if (!empty($VerifiedBy)) {
                                                    foreach ($VerifiedBy as $reVerified) { ?>
                                                        <option value="<?php echo $reVerified; ?>" <?php echo !empty($res->VerifiedBy) && $res->VerifiedBy == $reVerified ?'selected="selected"':'';?>><?php echo $reVerified; ?></option>
                                                <?php  }
                                                } ?>

                                            </select>
                                            <input type="text" placeholder="Enter Verified By" class="form-control" name='VerifiedByNew' id="VerifiedByNew"
                                                value="<?php echo !empty($res->VerifiedBy)?$res->VerifiedBy:'';?>" style="display: none;">
                                        </div>
                                       

                                        
                                         

                                    <div class="text-center mt-4">
                                        <input type="hidden" name="UserId" value="<?php echo !empty($_SESSION['UserID']) ? $_SESSION['UserID'] : ''; ?>">
                                        <input type="hidden" id="AssuranceId" name="AssuranceId" value="<?php echo !empty($_GET['id'])?$_GET['id']:'';?>">
                                        <a href="online-organoleptic-inspection-report-list.php" class="btn btn-danger">Close</a>

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
                        <div id="divDataList" class="p-3"></div>
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
    $(document).on("change", "#BigPieces", function(e) {
        var BigPieces = ($(this).val())?$(this).val():0;
        var SmallPieces = ($('#SmallPieces').val())?$('#SmallPieces').val():0;
        $('#Uniformity').val((parseInt(BigPieces)/parseInt(SmallPieces)).toFixed(2));
    })
    $(document).on("change", "#SmallPieces", function(e) {
        var SmallPieces = ($(this).val())?$(this).val():0;
        var BigPieces = ($('#BigPieces').val())?$('#BigPieces').val():0;
        $('#Uniformity').val((parseInt(BigPieces)/parseInt(SmallPieces)).toFixed(2));
    })
     $(document).on("change", "#FillingWeightInGms", function(e) {
        var FillingWeightInGms = ($(this).val())?parseInt($(this).val()):0;
        var DeglazedWt = ($('#DeglazedWt').val())?parseInt($('#DeglazedWt').val()):0;
        var glaze = ((FillingWeightInGms-DeglazedWt)/FillingWeightInGms)*100
        $('#Glaze').val(glaze.toFixed(2))
    })
     $(document).on("change", "#DeglazedWt", function(e) {
        var DeglazedWt = ($(this).val())?parseInt($(this).val()):0;
        var FillingWeightInGms = ($('#FillingWeightInGms').val())?parseInt($('#FillingWeightInGms').val()):0;
        var glaze = ((FillingWeightInGms-DeglazedWt)/FillingWeightInGms)*100
        $('#Glaze').val(glaze.toFixed(2));
    })
    /* $(document).on("change", "#ReglazeBatchNo", function(e) {
        var ReglazeBatchNo = ($(this).val())?$(this).val():'';
        if(ReglazeBatchNo){
            $('#BatchCode').prop("disabled", true).select2('destroy').select2();
        }else{
            $('#BatchCode').prop("disabled", false);
            var AssuranceId = "<?php echo !empty($_GET['id'])?$_GET['id']:'';?>";
            getArrivals(AssuranceId);
        }
    })
    $(document).on("change", "#BatchCode", function(e) {
        var BatchCode = ($(this).val())?$(this).val():'';console.log(BatchCode)
        if(BatchCode.length != 0){
            $('#ReglazeBatchNo').prop("readonly", true).val('');
        }else{
            $('#ReglazeBatchNo').prop("readonly", false);
        }
    }) */
    $(document).ready(function () {
        $('#VerifiedBy,#CheckedBy').select2();

        $('#VerifiedBy').on('change', function() {
            if ($(this).val() == 'Others') {
                $('#VerifiedByNew').show().css('display', 'block').attr('required', true);
            } else {
                $('#VerifiedByNew').hide().css('display', 'none').attr('required', false);
            }
        })
         $('#CheckedBy').on('change', function() {
            if ($(this).val() == 'Others') {
                $('#CheckedByNew').show().css('display', 'block').attr('required', true);
            } else {
                $('#CheckedByNew').hide().css('display', 'none').attr('required', false);
            }
        })
        var AssuranceId = "<?php echo !empty($_GET['id'])?$_GET['id']:'';?>";
        getArrivals(AssuranceId);
        loadData()
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
                    /* 'BatchCode[]': {
                        required: true
                    }, */
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
                    },
                    VerifiedBy: {
                        required: true
                    },
                    CheckedBy: {
                        required: true
                    },

                },
                messages: {
                    ArrivalDate:{
                        required: "Please Choose Production Date."
                    },
                    /* 'BatchCode[]': {
                        required: "Please select a BatchCode."
                    }, */
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
                    var submitUrl = "Ajax/ajax-online-organoleptic-inspection-report.php"; 
                    $.ajax({
                        url: submitUrl,
                        type: 'POST',
                        data: formData,
                        success: function(data) {
                            if (data.Error == null) {
                                alert(data.Msg);
                                loadData();
                                var ArrivalDate = $("#ArrivalDate").val()
                               // $('#frmDeHeading')[0].reset();
                                $("#ArrivalDate").val(ArrivalDate)
                                if ($('#VerifiedBy').val() == 'Others') {
                                    var verefidByObj = [];
                                    $("#VerifiedBy option").each(function() {
                                        verefidByObj.push($(this).val())
                                    });
                                    if (jQuery.inArray($('#VerifiedByNew').val(), verefidByObj) === -1) {
                                        $("#VerifiedBy").append('<option value="' + $('#VerifiedByNew').val() + '"> ' + $('#VerifiedByNew').val() + ' </option>');
                                    }
                                }
                                if ($('#CheckedBy').val() == 'Others') {
                                    var inspectedByObj = [];
                                    $("#CheckedBy option").each(function() {
                                        inspectedByObj.push($(this).val())
                                    });
                                    if (jQuery.inArray($('#CheckedByNew').val(), inspectedByObj) === -1) {
                                        $("#CheckedBy").append('<option value="' + $('#CheckedByNew').val() + '"> ' + $('#CheckedByNew').val() + ' </option>');
                                    }
                                }
                                resetForm()
                               // window.location.href = 'online-organoleptic-inspection-report.php';
                            } else {
                                console.log(data.Error);
                            }
                        }
                    });
                }
            });

        <?php 
        if(!empty($res->BatchNoType) && $res->BatchNoType != 'Arrival'){?>
            $('#BatchCode').css('display','none').hide().attr('required',false).select2('destroy').val(null).select2().next('.select2-container').css('display','none').hide();
       <?php } ?>

        $('#ReglazeBatchNo').on('input', function () {
            this.value = this.value.toUpperCase();
        });
        $(document).on("change", "#BatchNoType", function(e) {
            $('#ReglazeBatchNo').val('');
            if($(this).val() == 'Arrival'){
                var AssuranceId = "<?php echo !empty($_GET['id'])?$_GET['id']:'';?>";
                getArrivals(AssuranceId);
                $('#BatchCode').css('display','block').show().attr('required',true);
                $('#BatchCode').select2('destroy').select2();
                $('#BatchCode').next('.select2-container').css('display','block').show();
                $('#ReglazeBatchNo').css('display','none').hide().attr('required',false);
            }else{
                $('#BatchCode').css('display','none').hide().attr('required',false).select2('destroy').val(null).select2().next('.select2-container').css('display','none').hide();
                $('#ReglazeBatchNo').css('display','block').show().attr('required',true);
                if($(this).val() == 'Reglaze'){
                    $('#ReglazeBatchNo').attr('placeholder','Enter Reglaze Batch No').show();
                }else{
                    $('#ReglazeBatchNo').attr('placeholder','Enter Melting Batch No').show();
                }
            }
        })


        $(document).on("change", "#ArrivalDate", function () {
            var AssuranceId = "<?php echo !empty($_GET['id'])?$_GET['id']:'';?>";
           if($('#BatchNoType').val()=='Arrival'){
                getArrivals(AssuranceId);
           }
            loadData()
        });   
        
    });
    function getArrivals(AssuranceId = '') {
        var batchIds = '';
        if($('#AssuranceId').val()){
            batchIds = "<?php echo !empty($res->BatchCode)?$res->BatchCode:''?>";
        }
        var batchIdsArr = batchIds.split(',');
        if($('#BatchNoType').val() == 'Arrival'){
            $.ajax({
                url: 'Ajax/ajax-online-organoleptic-inspection-report.php',
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
    }
    $(document).on("click", ".btnPageno", function(e) {
        loadData( $(this).attr('data-pageno'));
    })
    function loadData(pageNo = '',excelDownload='') {
        // debugger
        // console.log($("#txtBatchCode").val());
        $.ajax({
            url: 'Ajax/ajax-online-organoleptic-inspection-report.php',
            type: 'POST',

            data: {
                methodType: "GET",
                ArrivalDate: $("#ArrivalDate").val(),
                //BatchCode: $("#BatchCode").val(),
                pageNo:pageNo,
                excelDownload:excelDownload
                
            },
            success: function (data) {
                fnSuccess(data);
            }
        });
    }


    function fnSuccess(data) {
        $("#divDataList").html(data);
        fnShowDataTable();
    }

    function fnShowDataTable() {
        $('#tblDataTable').DataTable({
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
            buttons: [
                        {
                            //extend: 'excel',
                            text: 'Load All',
                            className: 'loadAll btn-left-align btn btn-warning pr-2',
                            //title: 'Soaking Report <?php echo date("d-m-Y") ?>',
                            footer: true,
                            /* exportOptions: {
                                columns: ':visible'
                            }, */
                            action: function() {
                                var excelDownload = 'T';
                                loadData('',excelDownload);
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Download Excel',
                            className: 'btnExcelDataTable btn-left-align',
                            title: 'Soaking Report <?php echo date("d-m-Y") ?>',
                            footer: true,
                            exportOptions: {
                                columns: ':visible'
                            },
                            action: function() {
                                //fnPoStatusReportExcel('tblDataTable', 'Treatment_Register_Report');
                                fnPoStatusReportExcelNew();
                            }
                        },
                    ],
        });
    }
    function fnPoStatusReportExcelNew() {
        var ArrivalDate = $("#ArrivalDate").val();
        var BatchCode = $("#BatchCode").val();
        var url = 'Ajax/ajax-online-organoleptic-inspection-report.php?ArrivalDate=' + ArrivalDate+ '&excelDownload=T';
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                window.location.href = data.filename;
            }
        });
    }
    function resetForm(){
        var btnText = $('#submit').text().trim();
        if(btnText == 'Update'){
            $('#submit').text('Save')
            $('#PageHeader,.breadcrumb-item.active').text('Add')
        }
        $('#AssuranceId').val('')
        $('#FreezingType,#Brand,#TypeOfPacking,#DiclaredSizeGrade,#VerifiedBy,#CheckedBy').select2('destroy').val(null).select2().trigger("change")
        $('#BatchCode').html("<option value=''>Select</option>");
        $('#ReglazeBatchNo').attr('required',false).css('display','none').hide();
        $('#BatchNoType').select2('destroy').val('Arrival').select2()
        $('#BatchCode').css('display','block').show().attr('required',true).select2('destroy').val(null).select2().next('.select2-container').css('display','block').show();
        $('#GeneralAppearance').val('Good')
        $('#Colour').val('light')
        $('#Freshness').val('good')
        $('#Odour').val('Fresh')
        $('#Texture').val('Firm')
        $('#Dehydration,#Discolouration').val('NILL')
        $('#ProductionCode,#InvoiceNo,#Variety,#Temperature,#FillingWeightInGms,#DeglazedWt,#Glaze,#Count').val('')
        $('#BigPieces,#SmallPieces,#Uniformity,#TotalPiecesPerSlab,#BlackSpotsPerTails,#BKNDamagedPieces').val('')
        $('#LegsVeinsShellBits,#HangingMeat,#SoftShell,#ForeignMatter,#TailBKN,#TotalPercentageOfDefect').val('')
        $('#SailingIntact,#Remarks,#VerifiedByNew,#CheckedByNew').val('')
        removeUrlParameter('id');
        getArrivals();
    }
    function removeUrlParameter(param) {
        let url = new URL(window.location.href);
        url.searchParams.delete(param);
        window.history.replaceState({}, document.title, url.pathname + url.search);
    }
    $(document).on("click", ".delete", function () {
        if (confirm("Are you sure you want to delete this item ?") == true) {

            var row = $(this).closest("tr");
            //  debugger
            var AssuranceId = $(this).attr("data-assurance_id");
            $.ajax({
                url: 'Ajax/ajax-online-organoleptic-inspection-report.php',
                type: 'POST',
                data: {
                    methodType: "deleteRow",
                    AssuranceId: AssuranceId,
                },
                success: function (data) {
                    // console.log(data);
                    loadData();
                    
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);

                    }

                    return false;
                }
            });
        }
    });
</script>

</body>

</html> 