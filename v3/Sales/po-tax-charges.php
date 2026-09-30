<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$HasPIOtherTax = $ArrayClientConfigData['HasPIOtherTax'];

$ArrayInvoiceConfigData = json_decode(json_encode($_SESSION['ClientInvoiceConfigData']), true);
$HasCLFCharges = $ArrayInvoiceConfigData['CLFCharges'];
$HasTariff = $ArrayInvoiceConfigData['Tariff'];
$ShowReciprocalTariff = $ArrayInvoiceConfigData['ShowReciprocalTariff'];

// $apiList = API_TAX_CHARGES;

// $ExpTRID = trim($_GET['ExpTRID']);

// $bodyContent = array(
// "ExpTRID" => $ExpTRID
// );

// $result = CallAPI($apiList, "GET", "", $bodyContent);

// $Data = $result->Data[0];

?>
<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>
</head>
<?php include_once ROOT . '/includes/body-preloader.php' ?>
<!-- Begin page -->
<div id="layout-wrapper">
    <?php include_once ROOT . '/includes/menu.php' ?>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <?php include_once ROOT . '/includes/page-header.php' ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpPoDate">Select PO Date<span style="color:red">*</span></label>
                                        <input type="date" id="drpPoDate" class="form-control" value="" />
                                    </div>
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpPoNumbers">Select PO Numbers<span
                                                style="color:red">*</span></label>
                                        <select class="drpSelect form-control" name="drpPoNumbers" id="drpPoNumbers">
                                            <option value="" disabled>Select</option>
                                        </select>
                                        <input type="hidden" name="hdnPoNumber" id="hdnPoNumber" value="">
                                    </div>
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpInvoiceType">Invoice Type<span style="color:red">*</span></label>
                                        <select class="drpSelect form-control" name="drpInvoiceType"
                                            id="drpInvoiceType">
                                            <option value="">Select</option>
                                            <option value="PI">PI</option>
                                            <option value="CUI">CI</option>
                                            <option value="CMI">CMI</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Tax Charges</h4>

                                <div class="row ">
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpRevicedPo">Revised PO<span style="color:red">*</span></label>
                                        <select class="drpSelect form-control" name="drpRevicedPo" id="drpRevicedPo">
                                            <option value="">Select</option>
                                            <option value="1">YES</option>
                                            <option value="0">NO</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpAntiDumpingDuty" class="form-label">Anti Dumping Duty<span
                                                style="color:red">*</span></label>
                                        <input type="text" class="form-control" id="drpAntiDumpingDuty"
                                            name="drpAntiDumpingDuty" placeholder="Enter Anti Dumping Duty"
                                            value='<?php echo $Data->AntiDumpingDuty ?>' />
                                    </div>
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpMerchandiseProcessFee" class="form-label">Merchandise Process
                                            Fee</label>
                                        <input type="text" class="form-control" id="drpMerchandiseProcessFee"
                                            name="drpMerchandiseProcessFee" placeholder="Enter Merchandise Process Fee"
                                            value="<?php echo $Data->MerchandiseProcessFee ?>">
                                    </div>
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpHarbourMaintenanceFee" class="form-label">Harbour Maintenance
                                            Fee</label>
                                        <input type="text" class="form-control" id="drpHarbourMaintenanceFee"
                                            name="drpHarbourMaintenanceFee" placeholder="Enter Harbour Maintenance Fee"
                                            value="<?php echo $Data->HarbourMaintenanceFee ?>">
                                    </div>
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpCountervailingDuty" class="form-label">Countervailing
                                            Duty</label>
                                        <input type="text" class="form-control" id="drpCountervailingDuty"
                                            name="drpCountervailingDuty" placeholder="Enter Countervailing Duty"
                                            value="<?php echo $Data->CountervailingDuty ?>">
                                    </div>
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpClearanceChargePercentage" class="form-label">Clearance
                                            Charge
                                            Percentage</label>
                                        <input type="text" type="text" class="form-control"
                                            id="drpClearanceChargePercentage" name="drpClearanceChargePercentage"
                                            placeholder="Enter Clearance Charge Percentage"
                                            value='<?php echo $Data->ClearanceChargeFee ?>' />
                                    </div>
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpServiceTaxPercentage" class="form-label">Service Tax
                                            Percentage</label>
                                        <input type="text" type="text" class="form-control" id="drpServiceTaxPercentage"
                                            name="drpServiceTaxPercentage" placeholder="Enter Service Tax Percentage"
                                            value='<?php echo $Data->ServiceTaxPercentage ?>' />
                                    </div>
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpDrawbackRateperKG" class="form-label">Drawback
                                            Rate/KG</label>
                                        <input type="text" class="form-control" id="drpDrawbackRateperKG"
                                            name="drpDrawbackRateperKG" placeholder="Enter Drawback Rate per KG "
                                            value="<?php echo $Data->DrawbackRateperKG ?>">
                                    </div>
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpDrawbackPercentage" class="form-label">Drawback
                                            Percentage</label>
                                        <input type="text" class="form-control" id="drpDrawbackPercentage"
                                            name="drpDrawbackPercentage" placeholder="Enter Drawback Percentage"
                                            value="<?php echo $Data->DrawbackPercentage ?>">
                                    </div>
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpBLServiceTax" class="form-label">BL Service Tax</label>
                                        <input type="text" class="form-control" id="drpBLServiceTax"
                                            name="drpBLServiceTax" placeholder="Enter BL Service Tax"
                                            value="<?php echo $Data->BLServiceTax ?>">
                                    </div>
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpISFStaticDuty" class="form-label">ISF Static Duty<span
                                                style="color:red">*</span></label>
                                        <input type="text" type="text" class="form-control" id="drpISFStaticDuty"
                                            name="drpISFStaticDuty" placeholder="Enter ISF Static Duty"
                                            value='<?php echo $Data->ISFStaticDuty ?>' />
                                    </div>
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpReciprocalTariffsPerc" class="form-label">Reciprocal
                                            Tariffs</label>
                                        <input type="text" class="form-control" id="drpReciprocalTariffsPerc"
                                            name="drpReciprocalTariffsPerc" placeholder="Enter Reciprocal Tariffs"
                                            value="<?php echo $Data->ReciprocalTariffs ?>">
                                    </div>
                                    <?php
                                    if ($HasCLFCharges == 1) {
                                    ?>
                                        <div class="col-md-3 mt-3 mb-2">
                                            <label for="drpClearingCharges" class="form-label">Clearing & Forwarding
                                                Charges</label>
                                            <input type="text" class="form-control" id="drpClearingCharges"
                                                name="drpClearingCharges" placeholder="Enter Charges"
                                                value="<?php echo $Data->ClearingCharges ?>">
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <?php
                                    if ($HasPIOtherTax == 1) {
                                    ?>
                                        <div class="col-md-3 mt-3 mb-2">
                                            <label for="drpOtherTaxPerc" class="form-label">Other Tax</label>
                                            <input type="text" class="form-control" id="drpOtherTaxPerc"
                                                name="drpOtherTaxPerc" placeholder="Enter Other Tax"
                                                value="<?php echo $Data->OtherTax ?>">
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <div class="col-md-3 mt-3 mb-2">
                                        <label for="drpInsurancePremiumRate" class="form-label">Insurance Premium
                                            Rate</label>
                                        <input type="text" class="form-control" id="drpInsurancePremiumRate"
                                            name="drpInsurancePremiumRate" placeholder="Enter Insurance Premium Rate"
                                            value="<?php echo $Data->InsurancePremiumRate ?>">
                                    </div>
                                    <?php
                                    if ($HasTariff == 1) {
                                    ?>
                                        <div class="col-md-3 mt-3 mb-2">
                                            <label for="drpTariff" class="form-label">Tariff</label>
                                            <input type="text" class="form-control" id="drpTariff"
                                                name="drpTariff" placeholder="Enter Tariff"
                                                value="<?php echo $Data->Tariff ?>">
                                        </div>
                                        <div class="col-md-3 mt-3 mb-2">
                                            <label for="drpPrintTariff" class="form-label">Print Tariff</label>
                                            <select class="drpSelect form-control" name="drpPrintTariff" id="drpPrintTariff">
                                                <option value="">Select</option>
                                                <option value="1" <?php echo ($Data->PrintTariff == 1) ? 'selected' : ''; ?>>YES</option>
                                                <option value="0" <?php echo ($Data->PrintTariff == 0) ? 'selected' : ''; ?>>NO</option>
                                            </select>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <?php
                                    if ($ShowReciprocalTariff == 1) {
                                    ?>
                                        <div class="col-md-3 mt-3 mb-2">
                                            <label for="drpPrintReciprocalTariff">Print Reciprocal Tariff<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpPrintReciprocalTariff" id="drpPrintReciprocalTariff">
                                                <option value="">Select</option>
                                                <option value="1" <?php echo ($Data->PrintReciprocalTariff == 1) ? 'selected' : ''; ?>>YES</option>
                                                <option value="0" <?php echo ($Data->PrintReciprocalTariff == 0) ? 'selected' : ''; ?>>NO</option>
                                            </select>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <div class="col-md-3 mt-3 mb-2"></div>
                                    <div class="col-md-3 mt-5 mb-2">
                                        <label class="form-label"></label>
                                        <div class="float-end mb-2 mt-2">
                                            <button id="btnSubmit" type="button"
                                                class="btn btn-primary waves-effect waves-light">
                                                Submit
                                            </button>

                                            <button id="btnReset" type="button"
                                                class="btn btn-secondary waves-effect waves-light">
                                                Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <?php include_once ROOT . '/includes/footer.php' ?>
    </div>
    <!-- end main content-->
    <?php include_once ROOT . '/includes/common-js.php' ?>

    <!-- Required datatable js -->
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

    <?php include_once ROOT . '/includes/app-js.php' ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>


    <script>
        function resetForm(fullReset = false) {
            // if (fullReset) {
            // $("#hdnPoNumber").val("");
            // $("#drpPoDate").val("").trigger('change');
            // }
            // // $("#drpPoNumbers").val([]).trigger('change'); // Use trigger to update the select
            // $("#drpReciprocalTariff").val("").trigger('change');;
            // $("#drpRevicedPo").val("").trigger('change');
            // $("#btnUpdate").hide();
            // $("#btnSubmit").show();

            // $("#drpPoDate").val(poDate).trigger('change');
            // $("#drpReciprocalTariff").val(reciprocalTariff).trigger('change');
            // $("#drpRevicedPo").val(revisedPo).trigger('change');
            // $("#drpPoNumbers").val([poID]).trigger('change');
        }

        /* function fnShowDataTable() {
            var table = $('#tblDataTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                bInfo: false
            });
        } */

        function fnSuccess(data) {
            $("#divDataList").html(data);
            // fnShowDataTable();

            $('html, body').animate({
                scrollTop: $("#divDataList").offset().top - 50
            }, 10);
        }

        // function ShowList() {
        // var date = $("#drpPoDate").val();
        // $.ajax({
        // url: 'Ajax/AjaxReciprocalTariffs.php',
        // type: 'POST',
        // data: {
        // methodType: 'getReciprocalTariff',
        // PoDate: date,
        // },
        // success: function(data) {
        // fnSuccess(data);
        // // console.log(data)
        // }
        // });
        // }

        $(document).ready(function() {


            $('#drpInvoiceType').on('change', function() {
                $("#drpPoNumbers").change();
            });
            $('#btnUpdate').hide();




            $("#btnSubmit").click(function(e) {
                e.preventDefault();
                var ShowReciprocalTariff = "<?php echo $ShowReciprocalTariff; ?>";
                var poID = $("#drpPoNumbers").val();
                var invoiceType = $("#drpInvoiceType").val();
                var revisedPo = $("#drpRevicedPo").val();
                var antiDumpDuty = $("#drpAntiDumpingDuty").val();
                var merchandiseProcessFee = $("#drpMerchandiseProcessFee").val();
                var harbourMaintenanceFee = $("#drpHarbourMaintenanceFee").val();
                var countervailingDuty = $("#drpCountervailingDuty").val();
                var serviceTaxPercentage = $("#drpServiceTaxPercentage").val();
                var clearanceChargePercentage = $("#drpClearanceChargePercentage").val();
                var drawbackRateperKG = $("#drpDrawbackRateperKG").val();
                var drawbackPercentage = $("#drpDrawbackPercentage").val();
                var blServiceTax = $("#drpBLServiceTax").val();
                var isfStaticDuty = $("#drpISFStaticDuty").val();
                var insurancePremiumRate = $("#drpInsurancePremiumRate").val();
                var usClearance = $("#drpUsClearance").val();
                var reciprocalTariffs = $("#drpReciprocalTariffsPerc").val();
                var clearingCharges = $("#drpClearingCharges").val();
                var otherTax = $("#drpOtherTaxPerc").val();
                var fromDate = $("#drpFromDate").val();
                var tariff = $("#drpTariff").val();
                var printTariff = ShowReciprocalTariff == '1' ? $("#drpPrintTariff").val() : 1;
                var printReciprocalTariff = ShowReciprocalTariff == '1' ? $("#drpPrintReciprocalTariff").val() : 1;
                // if (date == "" || poIDsStr == "" || reciprocalTariff == "" || revisedPo == "") {
                // alert("Please fill all the fields");
                // return false;
                // }

                $.ajax({
                    url: 'Ajax/ajax-create-po-tax-charges.php',
                    type: 'POST',
                    data: {
                        methodType: 'postData',
                        PoID: poID,
                        InvoiceType: invoiceType,
                        RevisedPo: revisedPo,
                        AntiDumpDuty: antiDumpDuty,
                        MerchandiseProcessFee: merchandiseProcessFee,
                        HarbourMaintenanceFee: harbourMaintenanceFee,
                        CountervailingDuty: countervailingDuty,
                        ServiceTaxPercentage: serviceTaxPercentage,
                        ClearanceChargePercentage: clearanceChargePercentage,
                        DrawbackRateperKG: drawbackRateperKG,
                        DrawbackPercentage: drawbackPercentage,
                        BLServiceTax: blServiceTax,
                        ISFStaticDuty: isfStaticDuty,
                        InsurancePremiumRate: insurancePremiumRate,
                        UsClearance: usClearance,
                        ReciprocalTariffs: reciprocalTariffs,
                        ClearingCharges: clearingCharges,
                        OtherTax: otherTax,
                        Tariff: tariff,
                        PrintTariff: printTariff,
                        PrintReciprocalTariff: printReciprocalTariff,
                    },
                    success: function(data) {
                        // resetForm();
                        // ShowList();
                        alert("Saved Successfully");
                    }
                });
            });

            // $("#btnUpdate").click(function(e) {
            // e.preventDefault();
            // var poDate = $("#drpPoDate").val();
            // var poID = $("#hdnPoNumber").val();
            // var invoiceType = $("#drpInvoiceType").val();
            // var revisedPo = $("#drpRevicedPo").val();
            // console.log("UPDATE ", poDate, poID, invoiceType, revisedPo);
            // if (poDate == "" || poID == "" || invoiceType == "" || revisedPo == "") {
            // alert("Please fill all the fields");
            // return false;
            // }

            // $.ajax({
            // url: 'Ajax/AjaxReciprocalTariffs.php',
            // type: 'POST',
            // data: {
            // methodType: 'updateReciprocalTariff',
            // PoDate: poDate,
            // PoIDs: poID,
            // ReciprocalTariff: reciprocalTariff,
            // RevisedPo: revisedPo
            // },
            // success: function(data) {
            // // resetForm();
            // // ShowList();
            // }
            // });
            // });

            $("#btnReset").click(function(e) {
                e.preventDefault();
                resetForm(true)
            });

            $("#drpPoDate").change(function() {
                resetForm();
                console.log("Date Changed")
                var date = $(this).val();
                $.ajax({
                    url: 'Ajax/AjaxReciprocalTariffs.php',
                    type: 'POST',
                    data: {
                        methodType: 'getPoNumbers',
                        onWater: 'YES',
                        PoDate: date
                    },
                    success: function(response) {
                        if (response == "" || response == null || response == undefined || response == "NULL") {
                            alert("No PO Numbers Found");
                            return false;
                        }

                        var responseData = JSON.parse(response);
                        if (responseData.Error) {
                            alert(responseData.Msg);
                            return false;
                        }

                        var options = '<option value="Select" selected disabled>Select PO</option>';
                        $.each(responseData.Data, function(index, item) {
                            options += '<option value="' + item.PoID + '">' + item.PoNo + '</option>';
                        });
                        $("#drpPoNumbers").html(options);
                    }
                });
            });

            // $(document).on("click", ".btnEditReciprocalTariff", function() {
            // if (confirm("Are you sure you want to edit this record?")) {
            // var poID = $(this).data("poid");
            // var poDate = $(this).data("podate");
            // var reciprocalTariff = $(this).data("reciprocaltariff");
            // var revisedPo = $(this).data("revisedpo");
            // console.log("EDIT ", poDate, poID, reciprocalTariff, revisedPo);
            // // Populate the edit form fields with the data
            // $("#drpPoDate").val(poDate).trigger('change');;
            // $("#drpReciprocalTariff").val(reciprocalTariff).trigger('change');;
            // $("#drpRevicedPo").val(revisedPo).trigger('change');
            // $("#drpPoNumbers").val([poID]).trigger('change');
            // $("#hdnPoNumber").val(poID);
            // $("#btnSubmit").hide();
            // $("#btnUpdate").show();
            // }
            // });

            $("#drpPoNumbers").change(function() {
                var poID = $(this).val();
                var invoiceType = $('#drpInvoiceType').val();
                if (poID == "" || poID == null) {
                    return false;
                }
                $.ajax({
                    url: 'Ajax/ajax-create-po-tax-charges.php',
                    type: 'POST',
                    data: {
                        methodType: 'getPoTaxDetails',
                        PoID: poID,
                        InvoiceType: invoiceType
                    },
                    success: function(response) {
                        try {
                            if (response && response.Data) {
                                var row = response.Data;
                                $("#drpAntiDumpingDuty").val(row.AntiDumpingDuty);
                                $("#drpMerchandiseProcessFee").val(row.MerchandiseProcessFee);
                                $("#drpHarbourMaintenanceFee").val(row.HarbourMaintenanceFee);
                                $("#drpCountervailingDuty").val(row.CountervailingDuty);
                                $("#drpClearanceChargePercentage").val(row.ClearanceCharge == '' ? 0 : row.ClearanceCharge);
                                $("#drpServiceTaxPercentage").val(row.ServiceTaxPercentage);
                                $("#drpDrawbackRateperKG").val(row.DrawbackRateperKG);
                                $("#drpDrawbackPercentage").val(row.DrawbackPercentage);
                                $("#drpBLServiceTax").val(row.BLServiceTax);
                                $("#drpISFStaticDuty").val(row.ISFStaticDuty);
                                $("#drpInsurancePremiumRate").val(row.InsurancePremiumRate);
                                $("#drpUsClearance").val(row.UsClearance);
                                $("#drpReciprocalTariffsPerc").val(row.ReciprocalTariff);
                                $("#drpClearingCharges").val(row.ClearingCharges);
                                $("#drpOtherTaxPerc").val(row.OtherTax);
                                $("#drpTariff").val(row.Tariff);
                                $("#drpPrintTariff").val(row.PrintTariff).trigger('change');
                                $("#drpPrintReciprocalTariff").val(row.PrintReciprocalTariff).trigger('change');
                                $("#drpRevicedPo").val(row.RevisedPo).trigger('change');
                            }
                        } catch (e) {
                            console.log("Invalid JSON response");
                        }
                    },
                    error: function() {
                        alert("Something went wrong.");
                    }
                });
            });
        });
    </script>
    </body>

</html>