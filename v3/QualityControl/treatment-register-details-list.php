<?php

$PageTitle = "Treatment Register Details List";
$PageTitleFolder = "Quality Control";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
//include($cp . "Includes/IncludePage.php");

$uid = $_SESSION['r_id'];

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$ClientConfigData = $_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;
$bodyContent = [];
$api = API_QC_TREATMENT_REGISTER_DROPDOWN;
$dropDownData = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($dropDownData->Data);die;
$chemicalList = !empty($dropDownData->Data->chemical_data) ? $dropDownData->Data->chemical_data : [];
$VerifiedBy = !empty($dropDownData->Data->VerifiedBy) ? $dropDownData->Data->VerifiedBy : [];
$shiftData = !empty($dropDownData->Data->shiftData) ? $dropDownData->Data->shiftData : [];
$InspectedBy = !empty($dropDownData->Data->InspectedBy) ? $dropDownData->Data->InspectedBy : [];
$Veraities = !empty($dropDownData->Data->Veraities) ? $dropDownData->Data->Veraities : [];
$Grades = !empty($dropDownData->Data->Grades) ? $dropDownData->Data->Grades : [];


?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
    <!--  <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
 -->
    <!-- DataTables -->
    <!-- <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css"
        rel="stylesheet" type="text/css" /> -->
    <!-- <link href="/assets/css/newstyle.css?v="rel="stylesheet" type="text/css" /> -->

    <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />

    <style>
        .select2-container .select2-selection--multiple .select2-selection__choice,
        .select2-selection__choice__remove {
            background-color: #1cbb8c !important;
            border: 1px solid #1cbb8c !important;
            color: #FFFFFF !important;
        }
         div:not([class]):has(> ul.pagination) {
            float: right;
            margin-top: 0px;
        }
    </style>
</head>

<body data-sidebar="dark">

    <?php include($IncludesPath . "PreLoader.php"); ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>


        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php include($IncludesPath . "page-title.php"); ?>
                    <?php //include "PopUpTable.php"; 
                    ?>
                    <form role="form" method="post" id="addTreatment">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="row">
                                            <div class="form-group col-lg-3">
                                                <label for="txtFromDate">Soaking Date<span style="color:red">*</span></label>
                                                <input type="date" class="form-control"  id="ArrivalDate" name="ArrivalDate" placeholder="Arrival Date">
                                            </div>                                            
                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="BatchNoType" class="form-label">Type Of Batch</label>
                                                <select class="form-control select2 BatchNoType" name="BatchNoType" id="BatchNoType">
                                                    <option value="Arrival">Arrival</option>
                                                    <option value="Reglaze">Reglaze</option>
                                                    <option value="Melting">Melting</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-sm-3 mb-3">
                                                <label for="ReglazeBatchNo" class="form-label">Batch No<span style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="ReglazeBatchNo" name="ReglazeBatchNo"
                                                    placeholder="Enter Reglaze Batch No" value="<?php echo !empty($res->ReglazeBatchNo)?$res->ReglazeBatchNo:'';?>" style="display:none">
                                                <select class="form-control select2 BatchId" name="BatchId" id="BatchId" required>
                                                    <option value="">Select</option>
                                                </select>
                                            </div>

                                            <!-- <div class="col-lg-3">
                                                <label for="BatchId">Batch No<span style="color:red"></span></label>
                                                <select class="form-control select2 BatchId" name="BatchId" id="BatchId">
                                                    <option value="">Select</option>
                                                </select>
                                            </div> -->
                                            <div class="col-lg-3">
                                                <label for="Grade">Variety<span style="color:red">*</span></label>
                                                <select class="form-control select2 Variety" name="Variety" id="Variety">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-3">
                                                <label for="Grade">Grade<span style="color:red">*</span></label>
                                                <select class="form-control select2 Grade" name="Grade" id="Grade">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>

                                            <div class="col-lg-3">
                                                <label for="ChemicalId">Chemical Name<span style="color:red"></span></label>
                                                <select class="form-control select2" name="ChemicalId" id="ChemicalId">
                                                    <option value="">Select</option>
                                                    <?php
                                                    if (!empty($chemicalList)) {
                                                        foreach ($chemicalList as $rowC) { ?>
                                                            <option Value="<?php echo $rowC->ChemID; ?>">
                                                                <?php echo $rowC->ChemicalName; ?>
                                                            </option>
                                                    <?php }
                                                    } ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-lg-3">
                                                <label for="ChlorineDioxide">Chlorine dioxide (ML)<span style="color:red">*</span></label>
                                                <input type="text" placeholder="Enter Chlorine dioxide (ML)" class="form-control" name='ChlorineDioxide' id="ChlorineDioxide"
                                                    value="">
                                            </div>
                                            <div class="form-group col-lg-3">
                                                <label for="InTemperature">In Temperature(C)<span style="color:red">*</span></label>
                                                <input type="number" placeholder="Enter InTemperature" class="form-control" name='InTemperature' id="InTemperature"
                                                    value="">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="OutTemperature">Out Temperature(C)<span style="color:red">*</span></label>
                                                <input type="number" placeholder="Enter OutTemperature" class="form-control" name='OutTemperature' id="OutTemperature"
                                                    value="">
                                            </div>
                                            <div class="form-group col-lg-3">
                                                <label for="SaltBatchNumber">Salt batch number</label>
                                                <input type="number" placeholder="Enter Salt batch number" class="form-control" name='SaltBatchNumber' id="SaltBatchNumber"
                                                    value="">
                                            </div>
                                            <div class="form-group col-lg-3">
                                                <label for="PhosphateBatchNumber">Phosphate Batch number</label>
                                                <input type="text" placeholder="Enter Phosphate Batch number" class="form-control" name='PhosphateBatchNumber' id="PhosphateBatchNumber"
                                                    value="">
                                            </div>
                                            <div class="form-group col-lg-3">
                                                <label for="NonPhosphateBatchNumber">Non phosphate Batch number</label>
                                                <input type="text" placeholder="Enter Non phosphate Batch number" class="form-control" name='NonPhosphateBatchNumber' id="NonPhosphateBatchNumber"
                                                    value="">
                                            </div>
                                            
                                            <div class="form-group col-lg-3">
                                                <label for="Shift">Shift<span style="color:red">*</span></label>
                                                <select class="form-control" name="Shift" id="Shift">
                                                    <option value="">Select</option>
                                                    <?php
                                                    if (!empty($shiftData)) {
                                                        foreach ($shiftData as $siftRow) { ?>
                                                            <option value="<?php echo $siftRow->ShiftID; ?>"><?php echo $siftRow->ShiftName; ?></option>
                                                    <?php }
                                                    } ?>
                                                </select>
                                            </div>

                                              <div class="form-group col-lg-3">
                                                <label for="InspectedBy">Inspected By<span style="color:red">*</span></label>
                                                <!-- <input type="text" placeholder="Enter Inspected By" class="form-control" name='VerifiedBy' id="VerifiedBy"
                                                    value="K.L. Ganesh"> -->
                                                <select class="form-control select2" name="InspectedBy" id="InspectedBy">
                                                    <option value="">Select</option>
                                                    <option value="Others">Others</option>
                                                    <!--  <option value="P.DEEPA" selected>P.DEEPA</option> -->
                                                    <?php
                                                    if (!empty($InspectedBy)) {
                                                        foreach ($InspectedBy as $reInspectedBy) { ?>
                                                            <option value="<?php echo $reInspectedBy; ?>"><?php echo $reInspectedBy; ?></option>
                                                    <?php  }
                                                    } ?>

                                                </select>
                                                <input type="text" placeholder="Enter Inspected By" class="form-control" name='InspectedByNew' id="InspectedByNew"
                                                    value="" style="display: none;">
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
                                                            <option value="<?php echo $reVerified; ?>"><?php echo $reVerified; ?></option>
                                                    <?php  }
                                                    } ?>

                                                </select>
                                                <input type="text" placeholder="Enter Verified By" class="form-control" name='VerifiedByNew' id="VerifiedByNew"
                                                    value="" style="display: none;">
                                            </div>
                                          



                                            <div class="form-group col-lg-3">
                                                <label for="Remarks">Remarks<span style="color:red"></span></label>
                                                <input type="text" placeholder="Enter Remarks" class="form-control" name='Remarks' id="Remarks"
                                                    value="">
                                            </div>

                                        </div>

                                        <div class="col-xs-12" align="center">
                                            <input type="hidden" name="UserId" value="<?php echo !empty($_SESSION['UserID']) ? $_SESSION['UserID'] : ''; ?>">
                                            <input type="hidden" id="TreatmentId" name="TreatmentId" value="">
                                            <input type="hidden" id="SoakInQuantity" name="SoakInQuantity" value="">
                                            <input type="hidden" id="TubNumber" name="TubNumber" value="">
                                            <!-- <button type="button" class="btn btn-info waves-effect waves-light me-1 btnSearch">
                                                Search
                                            </button> -->
                                            <a href="javascript:void(0)" onclick="resetForm()" class="resetForm btn btn-danger">Reset</a>
                                            <button type="submit" id="submit" class="btn btn-primary" name="submit" value="submit">Save</a>
                                        </div>
                                    </div> <!-- end row -->
                                </div>
                            </div>
                        </div> <!-- end col -->
                    </form>
                </div> <!-- end row -->

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div id="treatmentList"></div>

                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div>
                <!-- end row-->
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
    </div>
    <!-- end row-->

    </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->
 <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>


    <!-- Required datatable js -->
    <!-- <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>



    <!-- Buttons examples -->
    <!--     <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/js/app.js"></script> -->
<style>
    .select2, .select2-container {
        box-sizing: border-box;
        display: inline-block;
        margin: 0;
        position: relative;
        vertical-align: middle;
        width: 100% !important;
    }
</style>

</body>

<script>
    $('document').ready(function() {
        /* $('#ArrivalDate').date({
            //viewMode: 'years',
            format: 'YYYY-MM-DD HH:mm:ss'
        }); */
        
        $("#ArrivalDate").on("change", function() {
           //$(this).val($(this).val().split("-").reverse().join("-")); 
        })
        $("#drpVerifiedBy").val("KL Ganesh").trigger("change");
        $("#drpInspectedBy").val("P.DEEPA").trigger("change");
        getTableData();
        $('#VerifiedBy').on('change', function() {
            if ($(this).val() == 'Others') {
                $('#VerifiedByNew').show().css('display', 'block').attr('required', true);
            } else {
                $('#VerifiedByNew').hide().css('display', 'none').attr('required', false);
            }
        })
         $('#InspectedBy').on('change', function() {
            if ($(this).val() == 'Others') {
                $('#InspectedByNew').show().css('display', 'block').attr('required', true);
            } else {
                $('#InspectedByNew').hide().css('display', 'none').attr('required', false);
            }
        })
        $('#ReglazeBatchNo').on('input', function () {
            this.value = this.value.toUpperCase();
        });
        $(document).on("change", "#BatchNoType", function(e) {
            $('#ReglazeBatchNo, #SoakInQuantity, #TubNumber').val('');
            if($(this).val() == 'Arrival'){
                var arrivaldate = $('#ArrivalDate').val();
                var BatchCode = ($('#BatchId').val())?$('#BatchId').val():'';
                getArrivals(arrivaldate, BatchCode, $('#TreatmentId').val());
                $('#BatchId').css('display','block').show().attr('required',true);
                $('#BatchId').select2('destroy').select2();
                $('#BatchId').next('.select2-container').css('display','block').show();
                $('#ReglazeBatchNo').css('display','none').hide().attr('required',false);
                $('#Grade,#Variety').html("<option value=''>Select</option>");
            }else{
                $('#BatchId').css('display','block').show().attr('required',false).select2('destroy').val(null).select2().next('.select2-container').css('display','none').hide();
                $('#ReglazeBatchNo').css('display','block').show().attr('required',true);
                getGradeForReglze()
                getVaraityForReglze()
                if($(this).val() == 'Reglaze'){
                    $('#ReglazeBatchNo').attr('placeholder','Enter Reglaze Batch No').show();
                }else{
                    $('#ReglazeBatchNo').attr('placeholder','Enter Melting Batch No').show();
                }
            }
        })
        /* $(document).on("change", "#ReglazeBatchNo", function(e) {
            var ReglazeBatchNo = ($(this).val())?$(this).val():'';
            if(ReglazeBatchNo){
                getGradeForReglze()
                getVaraityForReglze()
                $('#BatchId').prop("disabled", true).select2('destroy').select2();
            }else{
                $('#BatchId').prop("disabled", false);
                var arrivaldate = $('#ArrivalDate').val();
                var BatchCode = ($(this).val())?$(this).val():'';
                getArrivals(arrivaldate, BatchCode, $('#TreatmentId').val());
            }
        }) */
       /*  $(document).on("change", "#BatchId", function(e) {
            var BatchCode = ($(this).val())?$(this).val():'';
            if(BatchCode != ''){
                $('#ReglazeBatchNo').prop("readonly", true).val('');
            }else{
                $('#ReglazeBatchNo').prop("readonly", false);
            }
        }) */
        $("#addTreatment").validate({
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
                /* 'BatchId': {
                    required: true
                }, */
                Variety: {
                    required: true
                },
                Grade: {
                    required: true
                },
                InTemperature: {
                    required: true
                },
                OutTemperature: {
                    required: true
                },
                ArrivalDate: {
                    required: true
                },
                VerifiedBy: {
                    required: true
                },
                InspectedBy: {
                    required: true
                },
                ChlorineDioxide: {
                    required: true
                },
                Shift: {
                    required: true
                }
            },
            messages: {
                /* 'BatchId': {
                    required: "Please select a Batch Id."
                }, */
                Variety:{
                     required: "Please select Variety."
                },
                Grade: {
                    required: "Please select Grade."
                },
                InTemperature: {
                    required: "Please enter InTemperature"
                },
                OutTemperature: {
                    required: "Please enter OutTemperature."
                },
                ArrivalDate: {
                    required: "Please Coose ArrivalDate."
                },
                VerifiedBy: {
                    required: "Please Select VerifiedBy."
                },
                InspectedBy:{
                    required: "Please Select InspectedBy."
                },
                ChlorineDioxide: {
                    required: "Please Enter Chlorine dioxide."
                },
                Shift: {
                    required: "Please Select Shift."
                }

            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {
                var formData = $('#addTreatment').serialize();
                var submitUrl = "Ajax/ajax-treatment-register.php";
                $.ajax({
                    url: submitUrl,
                    type: 'POST',
                    data: formData,
                    success: function(data) {
                        if (data.Error == null) {
                            if ($('#VerifiedBy').val() == 'Others') {
                                var verefidByObj = [];
                                $("#VerifiedBy option").each(function() {
                                    verefidByObj.push($(this).val())
                                });
                                if (jQuery.inArray($('#VerifiedByNew').val(), verefidByObj) === -1) {
                                    $("#VerifiedBy").append('<option value="' + $('#VerifiedByNew').val() + '"> ' + $('#VerifiedByNew').val() + ' </option>');
                                }
                            }
                            if ($('#InspectedBy').val() == 'Others') {
                                var inspectedByObj = [];
                                $("#InspectedBy option").each(function() {
                                    inspectedByObj.push($(this).val())
                                });
                                if (jQuery.inArray($('#InspectedByNew').val(), inspectedByObj) === -1) {
                                    $("#InspectedBy").append('<option value="' + $('#InspectedByNew').val() + '"> ' + $('#InspectedByNew').val() + ' </option>');
                                }
                            }
                            resetForm();
                            alert(data.Msg);
                            var formData = [];
                            getTableData(formData);
                        } else {
                            console.log(data.Error);
                        }
                    }
                });
                //return true;
            }
        });
        $('#ChemicalId').on('change', function() {
            var valC = $(this).val();
            if (valC == 1) {
                $('#PhosphateBatchNumber').attr('readonly', false).val('')
                $('#NonPhosphateBatchNumber').attr('readonly', true).val('-')
            } else if (valC == 2) {
                $('#NonPhosphateBatchNumber').attr('readonly', false).val('')
                $('#PhosphateBatchNumber').attr('readonly', true).val('-')
            }
        })
    });
    $('#BatchId').on('change', function() {
         $('#Grade option,#Variety option').prop("selected", false).trigger("change");
         $('#Grade,#Variety').html("<option value=''>Select</option>");
        if($(this).val()){
            getVaraity($(this).val());
            $('select[name="Grade"]:first').val('').trigger("change")           
        }
    })
    $('#Variety').on('change',function(){
        $('#SoakInQuantity,#TubNumber').val('');
        if($('#ReglazeBatchNo').val() == ''){
            $('#Grade option').prop("selected", false).trigger("change");
            $('#Grade').html("<option value=''>Select</option>");
            if($('#BatchId').val() && $(this).val()){
                getGrade($(this).val(), $('#BatchId').val());
                var in_qty = $(this).find(':selected').attr('data-in_qty');
                var TubNumber = $(this).find(':selected').attr('data-tub_number');
                $('#SoakInQuantity').val(in_qty);
                $('#TubNumber').val(TubNumber);
            }
        }        
    })
    $(document).on('click', '.delete', function(e) {
        e.preventDefault();
        var treatment_id = $(this).data('treatment_id')
        if (treatment_id) {
            if (confirm('Are you Sure to Delete this')) {
                $.ajax({
                    url: 'Ajax/ajax-treatment-register.php',
                    type: 'POST',
                    data: {
                        treatment_id: treatment_id,
                        methodType: "DeleteTreatmentReg",
                    },
                    success: function(data) {
                        if (data.Error == null) {
                            alert(data.Msg);
                            getTableData();
                            if ($('#TreatmentId').val() != '' && $('#TreatmentId').val() == treatment_id) {
                                resetForm();
                            }

                        } else {
                            alert(data.Error)
                        }
                        return false;
                    }
                });
            }
        }
    });
    $(document).on("change", "#ArrivalDate", function(e) {
        e.preventDefault();
        if($('#BatchNoType').val() == 'Arrival'){
            $('#Grade,#Variety,#BatchId').html("<option value=''>Select</option>");     
            if($('#ArrivalDate').val()){
                getArrivals($('#ArrivalDate').val());
            }
        }else{
            getGradeForReglze()
            getVaraityForReglze()
        }   
    });
    $(document).on("click", ".edit", function(e) {
        if (confirm('Are you sure to Edit This.')) {
            resetForm();
            var arrivaldate = $(this).data('arrival_date');
            $('#ArrivalDate').val(arrivaldate);
            var batch_ids = $(this).data('batch_ids');
           
            $('select[name="ChemicalId"]:first').val($(this).data('chemical_id')).trigger("change")
            /* if(batch_ids){
                $('#ReglazeBatchNo').prop('readonly',true)
            } */
            $('select[name="BatchNoType"]:first').val($(this).data('batch_no_type')).trigger("change")
            if($(this).data('batch_no_type') != 'Arrival'){
                $('#BatchId').css('display','none').hide().attr('required',false).select2('destroy').val(null).select2().next('.select2-container').css('display','none').hide();
                $('#ReglazeBatchNo').css('display','block').show().attr('required',true).val($(this).data('reglaze_batchno'));
                // $('#BatchId').prop('disabled',true)
                getGradeForReglze($(this).data('grade'))
                getVaraityForReglze($(this).data('variety'))
                $('#SoakInQuantity,#TubNumber').val('')
            }else{
                $('#ReglazeBatchNo').val('').attr('required',false).css('display','none').hide();
                $('#BatchId').css('display','block').show().attr('required',true).select2('destroy').val(null).select2().next('.select2-container').css('display','block').show();
                getArrivals(arrivaldate, batch_ids, $(this).data('treatment_id'));
                getVaraity(batch_ids, $(this).data('variety'),$(this).data('grade'),$(this).data('in_qty'), $(this).data('treatment_id'),$(this).data('tub_number'));
                getGrade($(this).data('variety'),batch_ids, $(this).data('grade'));
                $('#SoakInQuantity').val($(this).data('in_qty'));
                $('#TubNumber').val($(this).data('tub_number'));
            }

            // $('#ChemicalId [value='+$(this).data('chemical_id')+']').attr('selected', 'true').trigger("change");
            $('#ChlorineDioxide').val($(this).data('chlorine_dioxide'))
            $('#InTemperature').val($(this).data('in_temperature'))
            $('#OutTemperature').val($(this).data('out_temperature'))
            $('#VerifiedByNew').val($(this).data('verified_by'))
            $('#InspectedByNew').val($(this).data('inspected_by'))
            $('select[name="VerifiedBy"]:first').val($(this).data('verified_by')).trigger("change")
            $('select[name="InspectedBy"]:first').val($(this).data('inspected_by')).trigger("change")
            //  $('#VerifiedBy [value="'+$(this).data('verified_by')+'"]').attr('selected', 'true').trigger("change");
            $('#Remarks').val($(this).data('remarks'))
            $('#TreatmentId').val($(this).data('treatment_id'))

            $('#SaltBatchNumber').val($(this).data('salt_batch_number'))
            $('#PhosphateBatchNumber').val($(this).data('phosphate_batch_number'))
            $('#NonPhosphateBatchNumber').val($(this).data('non_phosphate_batch_number'))
            //  $('#VerifiedBy').val($(this).data('verified_by'))
            $('select[name="Shift"]:first').val($(this).data('shift')).trigger("change")

            $(window).scrollTop(0);
        }
    })
    $(document).on("click", ".btnPageno", function(e) {
        var postData = {
            'pageNo': $(this).attr('data-pageno')
        }
        getTableData(postData);
    })

    function getArrivals(ArrivalDate, batch_ids = '', TreatmentId = '') {
        if(!$('#ReglazeBatchNo').val()){
            $.ajax({
                //url: 'Ajax/ajax-deheading-check-list-report.php',
                url: 'Ajax/ajax-treatment-register.php',
                type: 'POST',
                data: {
                    ArrivalDate: ArrivalDate,
                    methodType: "GetBatchNumbers",
                    TreatmentId: TreatmentId
                },
                success: function(data) {
                    // console.log(data);
                    var list = "<option value=''>Select</option>";
                    $.each(data.Data.BatchData, function(index, value) {
                        list += "<option value='" + value.ArrivalId + "'>" + value.ArrivalNumber + "</option>";
                    });
                    $('#BatchId').select2('destroy').html(list).select2();
                    if (batch_ids) {//alert(batch_ids)
                        //var batchIdsArr = batch_ids.split(',');
                        /* $.each(batchIdsArr, function(key, val1) {
                            if (val1) {
                                setTimeout(function() {
                                    $('#BatchId [value=' + val1 + ']').attr('selected', 'true').trigger("change");
                                }, 500);
                            }
                        }); */
                        setTimeout(function() {
                            $('#BatchId').select2('destroy').val(batch_ids).select2();
                        // $('#BatchId [value="' + $.trim(batch_ids) + '"]').attr('selected', 'true').trigger("click");
                            //$('select[name="BatchId"]:first').val($.trim(batch_ids)).trigger("click")
                        }, 500);
                    }
                    return false;
                }
            });
        }
    }

    function resetForm() {
        $('#TreatmentId,#ArrivalDate,#InTemperature,#OutTemperature,#ChlorineDioxide,#Remarks,#SaltBatchNumber,#PhosphateBatchNumber,#NonPhosphateBatchNumber,#VerifiedByNew,#InspectedByNew,#SoakInQuantity,#TubNumber').val('');
        $('#BatchId option,#ChemicalId option,#VerifiedBy option,#Grade option,#InspectedBy option,#Variety option').prop("selected", false).trigger("change");
        $('#PhosphateBatchNumber,#NonPhosphateBatchNumber,#ReglazeBatchNo').attr('readonly', false).val('')
        //$('#ChemicalId option').prop("selected", false);
        $('#Shift option').prop("selected", false);
        $('#BatchId').html("<option value=''>Select</option>");
        $('#ReglazeBatchNo').attr('required',false).css('display','none').hide();
        $('#BatchNoType').select2('destroy').val('Arrival').select2()
        $('#BatchId').css('display','block').show().attr('required',true).select2('destroy').val(null).select2().next('.select2-container').css('display','block').show();
    }

    function getTableData(formData = []) {
        $.ajax({
            url: 'Ajax/ajax-treatment-register.php',
            type: 'POST',
            data: formData,
            success: function(data) {
                $('#treatmentList').html(data);
                fnShowDataTable();
            }
        });
    }

    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            scrollCollapse: true,
            bInfo: false,
            bSort: false,
            dom: 'Blfrtip',
            buttons: [{
                text: 'Load All',
                className: 'btnLoadAll btn-left-align btn btn-warning',
                footer: true,
                action: function() {
                    var postData = {
                        'loadAll': 1
                    }
                    getTableData(postData);
                }
            }],
        });
    }
    function getGrade(Variety = '',LotId = '',Grade = ''){
        if($('#ReglazeBatchNo').val() == ''){
        var list = "<option value=''>Select</option>";
            if(Grade){
                list += "<option value='" + $.trim(Grade) + "'>" + Grade + "</option>";
                $('#Grade').select2('destroy').html(list).select2();
                //setTimeout(function() {
                    $('#Grade').select2('destroy').val(Grade).select2();
                //}, 1000);
            }else{
                    var gradeSelect = $('#Variety').find(':selected').attr('data-grade')
                    if(gradeSelect){
                        list += "<option value='" + $.trim(gradeSelect) + "'>" + gradeSelect + "</option>";
                        $('#Grade').select2('destroy').html(list).select2();
                    // setTimeout(function() {
                                $('#Grade').select2('destroy').val(gradeSelect).select2();
                    //}, 1000);
                }
            }
        }
    }
    function getGradeBk(Variety, LotId,Grade = '') {
        $.ajax({
            url: 'Ajax/ajax-treatment-register.php',
            type: 'POST',
            data: {
                TreatmentId: $('#TreatmentId').val(),
                Variety: Variety,
                LotId:LotId,
                methodType: "getGradesByLotId"
            },
            success: function(data) {
                // console.log(data);
                var list = "<option value=''>Select</option>";
                $.each(data.Data.GradeData, function(index, value) {
                    list += "<option value='" + $.trim(value.VAGrade) + "'>" + value.VAGrade + "</option>";
                });
                $('#Grade').select2('destroy').html(list).select2();
                if (Grade) {
                    setTimeout(function() {
                         $('#Grade').select2('destroy').val(Grade).select2();
                       // $('#Grade [value="' + $.trim(Grade) + '"]').attr('selected', 'true').trigger("change");
                       // $('select[name="Grade"]:first').val($.trim(Grade)).trigger("change")
                    }, 1000);
                }
               // return false;
            },
        });
    }
    function getVaraity(Lot_Id , Variety='',gradeV = '', inQty = '', TreatmentId = '', TubNumber = ''){
         $.ajax({
            url: 'Ajax/ajax-treatment-register.php',
            type: 'POST',
            data: {
                TreatmentId: TreatmentId != '' ? TreatmentId : $('#TreatmentId').val(),
                LotId: Lot_Id,
                methodType: "getVaraityByLotId"
            },
            success: function(data) {
                // console.log(data);
                var list = "<option value=''>Select</option>";
                $.each(data.Data.VarietyData, function(index, value) {
                    var disabled = '';
                    if(value.status == true){
                        disabled = 'disabled';
                    }
                    list += "<option value='" + $.trim(value.Variety) + "' data-grade='"+$.trim(value.VAGrade)+"' data-tub_number='"+$.trim(value.TubNumber)+"' data-in_qty='"+$.trim(value.SoakInQty)+"' "+disabled+">" + value.VarietyName + "</option>";
                });
                $('#Variety').select2('destroy').html(list).select2();
                if (Variety) {
                    setTimeout(function() {
                        $('#Variety option').each(function() {//alert( $(this).data('grade'))
                        if ($(this).val() == Variety && $(this).data('grade') == gradeV && $(this).data('in_qty') == inQty && $(this).data('tub_number') == TubNumber) {
                            $(this).prop('selected', true);
                        } else {
                            $(this).prop('selected', false);
                        }
                        });
                         $('#Variety').select2('destroy').select2();
                        // $('#Variety').select2('destroy').val(Variety).select2();
                       // $('#Variety [value="' + $.trim(Variety) + '"]').attr('selected', 'true').trigger("click");
                        //$('select[name="Variety"]:first').val($.trim(Variety)).trigger("change")
                    }, 500);
                }
                return false;
            }
        });
    }
    function getVaraityForReglze( Variety=''){
        var Veraities = <?php echo json_encode($Veraities);?>;
       // console.log(Veraities)
        var list = "<option value=''>Select</option>";
        $.each(Veraities, function(index, value) {
            list += "<option value='" + $.trim(value.VarietyID) + "'>" + value.VarietyName + "</option>";
        });
        $('#Variety').select2('destroy').html(list).select2();
        if (Variety) {
            setTimeout(function() {
                $('#Variety').select2('destroy').val(Variety).select2();
            }, 500);
        }
    }
    function getGradeForReglze(Grade = '') {
        var Grades = <?php echo json_encode($Grades);?>;
        var list = "<option value=''>Select</option>";
        $.each(Grades, function(index, value) {
            list += "<option value='" + $.trim(value) + "'>" + value + "</option>";
        });
        $('#Grade').select2('destroy').html(list).select2();
        if (Grade) {
            setTimeout(function() {
                $('#Grade').select2('destroy').val(Grade).select2();
            }, 500);
        }
    }
</script>