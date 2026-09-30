<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STORE_DROPDOWN;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resPoNo = $resDropdownsAPI->Data->PoNo;

$apiList = API_GS_CHEM_PO_PAYMENTS;

$ChemPoID = trim($_GET['ChemPoID']);
$ChemPaymentID = !empty($_GET['ChemPaymentID'])?trim($_GET['ChemPaymentID']):'';

$bodyContent = array(
    "ChemPoID" => $ChemPoID,
    'ChemPaymentID' => $ChemPaymentID
);

if ($ChemPoID != '') {
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
                                            <label for="drpPoNo">PoNo<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpPoNo" id="drpPoNo">
                                                <option value="" <?php echo ($Data->ChemPoNo == '') ? 'selected' : '';  ?>>All</option>
                                                <?php foreach ($resPoNo as $List) { ?>
                                                    <option value="<?php echo $List->ChemPoID ?>" <?php echo ($Data->ChemPoNo == $List->ChemPoNo) ? 'selected' : ''; ?>>
                                                        <?php echo $List->ChemPoNo ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtChemName" class="form-label">Chemical Name<span style="color:red">*</span></label>
                                            <input readonly type="text" class="form-control" id="txtChemName" name="txtChemName" placeholder="Enter Chemical Name" value='<?php echo $Data->ChemicalName ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtPryName" class="form-label">Party Name<span style="color:red">*</span></label>
                                            <input readonly type="text" class="form-control" id="txtPryName" name="txtPryName" placeholder="Enter Party Name" value='<?php echo $Data->BuyerName ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtQty" class="form-label">Recieved Quantity<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtQty" name="txtQty" placeholder="Enter Quantity" value='<?php echo $Data->TotalQty ?>' />
                                        </div>

                                        <!-- <div class="col col-md-3 mb-3">
                                            <label for="txtRate" class="form-label">Rate/kg<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtRate" name="txtRate" placeholder="Enter Rate/kg" value='<?php // echo $Data->TotalRate ?>' />
                                        </div> -->

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAmount" class="form-label">Amount<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtAmount" name="txtAmount" placeholder="Enter Amount" value='<?php echo $Data->InvoiceAmount ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInvNo" class="form-label">Bank Reference No<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtInvNo" name="txtInvNo" placeholder="Enter Invoice No"  value="<?php echo !empty($Data->InvoiceNo)?$Data->InvoiceNo:'';?>"  />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInvDate" class="form-label">Payment Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtInvDate" name="txtInvDate" placeholder="" value="<?php echo !empty($Data->InvoiceDt)?$Data->InvoiceDt:'';?>"/>
                                        </div>

                                       <!--  <div class="col col-md-3 mb-3">
                                            <label for="txtInvAmt" class="form-label">Invoice Amount<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtInvAmt" name="txtInvAmt" placeholder="Enter Invoice Amount" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtPmtDts" class="form-label">Payment Details<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtPmtDts" name="txtPmtDts" placeholder="Enter Payment Details" />
                                        </div> -->

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <input type="text" class="form-control" id="txtRemarks" name="txtRemarks" placeholder="Enter Remarks"  value="<?php echo !empty($Data->Remarks)?$Data->Remarks:'';?>"/>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ChemPoID ?>" />
                                            <input type="hidden" id="hdnNewBrkUp" name="hdnNewBrkUp" />
                                            <input type="hidden" id="hdnPaymentId" name="hdnPaymentId" />
                                            <a href="GsChemPoPaymentList" class="btn btn-danger">Close</a>
                                            <?php if ($ChemPaymentID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Save</button>
                                            <?php   } else { ?>
                                                <button class="btn btn-success btnSave" name="submit" id="submit">Update</button>
                                            <?php }  ?>
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
        var paymentID = "<?php echo isset($_GET["ChemPaymentID"]) && !empty($_GET["ChemPaymentID"]) ? $_GET["ChemPaymentID"] : "" ?>";
        var ChemPoID = "<?php echo isset($_GET["ChemPoID"]) && !empty($_GET["ChemPoID"]) ? $_GET["ChemPoID"] : "" ?>";

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
                    title: 'GS Chemical PO BreakUp -<?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        function ShowList() {

            console.log("Current ChemPoID:", ChemPoID);
            if(!ChemPoID){
                ChemPoID = $('#drpPoNo').val();
            }
            $.ajax({
                url: 'Ajax/AjaxGsChemPoPaymentCreate.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    ChemPoID: ChemPoID
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });
        }

        $(document).ready(function() {

            var urlParams = new URLSearchParams(window.location.search);
            var ChemPoIDFromUrl = urlParams.get('ChemPoID');

            var ChemPoID = ChemPoIDFromUrl;

            if (ChemPoIDFromUrl) {

                $("#drpPoNo").prop('readonly', true).val(ChemPoIDFromUrl).prop('disabled', true);
                $("#txtChemName").prop('readonly', true);
                $("#txtPryName").prop('readonly', true);
                //$("#txtQty").prop('readonly', true);
                // $("#txtRate").prop('readonly', true);
                //$("#txtAmount").prop('readonly', true);
            }

            ShowList();

            $(document).on("change", "#drpPoNo", function() {
                var chemPoID = $(this).val();

                if (chemPoID != "Select") {
                    var selectedChemPoID = ChemPoID || chemPoID;
                    $.ajax({
                        url: 'Ajax/AjaxGsChemPoPaymentCreate.php',
                        type: 'POST',
                        data: {
                            methodType: "get",
                            ChemPoID: selectedChemPoID
                        },
                        success: function(data) {

                            $("#divDataList").html(data);
                            fnShowDataTable();

                            return false;
                        }
                    });
                }

            });

            $('#drpPoNo').change(function() {
                var selectedPoNo = $(this).val();

                if (selectedPoNo !== "") {

                    $.ajax({
                        url: 'Ajax/AjaxGsChemPaymentAutofill.php',
                        type: 'GET',
                        data: {
                            ChemPoID: selectedPoNo
                        },
                        success: function(data) {
                            $('#txtChemName').val(data.ChemicalNames);
                           // $('#txtPryName').val(data.BuyerName);
                            $('#txtPryName').val(data.SupplierName);
                            //$('#txtQty').val(data.TotalQty);
                             $('#txtQty').val(data.QuantityReceived);
                            //$('#txtAmount').val(data.Amount);

                            // updateList(selectedPoNo);
                        },
                        error: function() {

                        }
                    });
                } else {
                    $('#txtChemName, #txtPryName, #txtQty, #txtAmount').val('');
                }
            });

            $(document).on('click', '.btnDelete', function() {
                var paymentID = $(this).data('id');
                if (confirm('Are you sure you want to delete this record?')) {
                    $.ajax({
                        url: 'Ajax/AjaxGsChemPoPaymentCreate.php',
                        type: 'POST',
                        data: {
                            methodType: "delete",
                            paymentID: paymentID
                        },
                        success: function(data) {
                            ShowList();
                        },
                    });
                }
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
                    txtInvNo: {
                        required: true,
                    },
                    txtInvDate: {
                        required: true,
                    },
                    
                    drpPoNo:{
                        required: true,
                    },
                    txtChemName:{
                        required: true,
                    },
                    txtPryName:{
                        required: true,
                    },
                    txtQty:{
                        required: true,
                    },
                    txtAmount:{
                        required: true,
                    }

                },
                errorPlacement: function() {
                    return false;
                },
                submitHandler: function() {

                    var selectedChemPoID = $("#drpPoNo").val();
                    if(!paymentID){
                        paymentID = $('#hdnPaymentId').val()
                    }
                    $.ajax({
                        url: 'Ajax/AjaxGsChemPoPaymentCreate.php',
                        type: 'POST',
                        data: {
                            methodType: "post",
                            paymentID: paymentID,
                            ChemPoID: $("#drpPoNo").val(),
                            invNo: $("#txtInvNo").val(),
                            invDate: $("#txtInvDate").val(),
                            invAmt: $("#txtInvAmt").val(),
                            pmtDts: $("#txtPmtDts").val(),
                            remarks: $("#txtRemarks").val(),
                            Amount: $("#txtAmount").val(),
                        },
                        success: function(data) {
                            $("#divDataList").html(data);
                            fnShowDataTable();
                            $('.btnSave').text('Save')
                             $('#hdnPaymentId,#txtRemarks,#txtInvDate,#txtInvNo,#txtAmount').val('')
                            if (selectedChemPoID !== "Select") {
                                $.ajax({
                                    url: 'Ajax/AjaxGsChemPoPaymentCreate.php',
                                    type: 'POST',
                                    data: {
                                        methodType: "get",
                                        ChemPoID: selectedChemPoID
                                    },
                                    success: function(data) {
                                        $("#divDataList").html(data);
                                        fnShowDataTable();
                                    }
                                });
                            }

                            $("#txtInvNo, #txtInvDate, #txtInvAmt, #txtPmtDts, #txtRemarks").val("");
                            return false;
                        }
                    });
                    return false;
                }
            });
        });
        $(document).on('click','.edit',function(){
            if(confirm('Are you sure to Edit this?')){
                $('select[name="drpPoNo"]:first').val($(this).data('po_id')).trigger("change")
                $('#txtChemName').val($(this).data('chemical_name'))
                $('#txtPryName').val($(this).data('buyyer_name'))
                $('#txtQty').val($(this).data('total_qty'))
                $('#txtAmount').val($(this).data('inv_amount'))
                // $('input[name="txtAmount"]').val($(this).data('inv_amount'))
                $('#txtInvNo').val($(this).data('inv_no'))
                $('#txtInvDate').val($(this).data('inv_date'))
                $('#txtRemarks').val($(this).data('remarks'))
                $('#hdnPaymentId').val($(this).data('chem_payment_id'))
                $('.btnSave').text('Update')
            }else{
                 $('#hdnPaymentId,#txtRemarks,#txtInvDate,#txtInvNo,#txtAmount').val('')
                $('.btnSave').text('Save')
            }
        })
    </script>
    </body>

</html>