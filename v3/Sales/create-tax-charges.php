<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_EXPORTS_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resAdminExporters = $resDropdownsAPI->Data->AdminExporters;
$resCountry = $resDropdownsAPI->Data->Country;

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$HasPIOtherTax = $ArrayClientConfigData['HasPIOtherTax'];

$ArrayInvoiceConfigData = json_decode(json_encode($_SESSION['ClientInvoiceConfigData']), true);
$HasCLFCharges = $ArrayInvoiceConfigData['CLFCharges'];
$HasTariff = $ArrayInvoiceConfigData['Tariff'];
$ShowReciprocalTariff = $ArrayInvoiceConfigData['ShowReciprocalTariff'];

$apiList = API_TAX_CHARGES;

$ExpTRID = trim($_GET['ExpTRID']);

$bodyContent = array(
    "ExpTRID" => $ExpTRID
);


// if ($ExpTRID != '') {
$result = CallAPI($apiList, "GET", "", $bodyContent);

$Data = $result->Data[0];
// }
// echo json_encode($result);

?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
        /* .select2-container--open {
            z-index: 9999999
            } */
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
                                            <label for="drpExporter">Exporter<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpExporter" id="drpExporter">
                                                <option value="" <?php echo ($Data->ExpName == '') ? 'selected' : ''; ?>>
                                                    Select</option>
                                                <?php foreach ($resAdminExporters as $List) { ?>
                                                    <option value="<?php echo $List->ExpID ?>" <?php echo ($Data->ExpName == $List->ExpName) ? 'selected' : ''; ?>>
                                                        <?php echo $List->ExpName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtFromDate" class="form-label">From Date<span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtFromDate" name="txtFromDate"
                                                placeholder="Enter From Date" value="<?php echo $Data->FromDate ?>">
                                        </div>

                                        <!-- <div class="col col-md-3 mb-3">
                                            <label for="drpCountry">Country</label>
                                            <select class="drpSelect form-control" name="drpCountry" id="drpCountry" multiple>
                                                <option value="">Select</option>
                                                <?php foreach ($resCountry as $List) { ?>
                                                    <option value="<?php echo $List->ID ?>" <?php echo ($Data->CountryName == $List->name) ? 'selected' : ''; ?>>
                                                        <?php echo $List->name ?></option>
                                                <?php } ?>
                                            </select>
                                        </div> -->

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAntiDumpingDuty" class="form-label">Anti Dumping Duty<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtAntiDumpingDuty"
                                                name="txtAntiDumpingDuty" placeholder="Enter Anti Dumping Duty"
                                                value='<?php echo $Data->AntiDumpingDuty ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtMerchandiseProcessFee"
                                                class="form-label">MerchandiseProcessFee</label>
                                            <input type="text" class="form-control" id="txtMerchandiseProcessFee"
                                                name="txtMerchandiseProcessFee"
                                                placeholder="Enter Merchandise Process Fee"
                                                value="<?php echo $Data->MerchandiseProcessFee ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtHarbourMaintenanceFee" class="form-label">Harbour Maintenance
                                                Fee</label>
                                            <input type="text" class="form-control" id="txtHarbourMaintenanceFee"
                                                name="txtHarbourMaintenanceFee"
                                                placeholder="Enter Harbour Maintenance Fee"
                                                value="<?php echo $Data->HarbourMaintenanceFee ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCountervailingDuty" class="form-label">Countervailing
                                                Duty</label>
                                            <input type="text" class="form-control" id="txtCountervailingDuty"
                                                name="txtCountervailingDuty" placeholder="Enter Countervailing Duty"
                                                value="<?php echo $Data->CountervailingDuty ?>">
                                        </div>
                                        <!-- -->
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtClearanceChargePercentage" class="form-label">Clearance
                                                Charge
                                                Percentage</label>
                                            <input type="text" type="text" class="form-control"
                                                id="txtClearanceChargePercentage" name="txtClearanceChargePercentage"
                                                placeholder="Enter Clearance Charge Percentage"
                                                value='<?php echo $Data->ClearanceChargeFee ?>' />
                                        </div>
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtServiceTaxPercentage" class="form-label">Service Tax
                                                Percentage</label>
                                            <input type="text" type="text" class="form-control"
                                                id="txtServiceTaxPercentage" name="txtServiceTaxPercentage"
                                                placeholder="Enter Service Tax Percentage"
                                                value='<?php echo $Data->ServiceTaxPercentage ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtDrawbackRateperKG" class="form-label">Drawback
                                                Rate/KG</label>
                                            <input type="text" class="form-control" id="txtDrawbackRateperKG"
                                                name="txtDrawbackRateperKG" placeholder="Enter Drawback Rate per KG "
                                                value="<?php echo $Data->DrawbackRateperKG ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtDrawbackPercentage" class="form-label">Drawback
                                                Percentage</label>
                                            <input type="text" class="form-control" id="txtDrawbackPercentage"
                                                name="txtDrawbackPercentage" placeholder="Enter Drawback Percentage"
                                                value="<?php echo $Data->DrawbackPercentage ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBLServiceTax" class="form-label">BL Service Tax</label>
                                            <input type="text" class="form-control" id="txtBLServiceTax"
                                                name="txtBLServiceTax" placeholder="Enter BL Service Tax"
                                                value="<?php echo $Data->BLServiceTax ?>">
                                        </div>
                                        <!-- --> <!-- -->
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtISFStaticDuty" class="form-label">ISF Static Duty<span
                                                    style="color:red">*</span></label>
                                            <input type="text" type="text" class="form-control" id="txtISFStaticDuty"
                                                name="txtISFStaticDuty" placeholder="Enter ISF Static Duty"
                                                value='<?php echo $Data->ISFStaticDuty ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtReciprocalTariffsPerc" class="form-label">Reciprocal
                                                Tariffs</label>
                                            <input type="text" class="form-control" id="txtReciprocalTariffsPerc"
                                                name="txtReciprocalTariffsPerc" placeholder="Enter Reciprocal Tariffs"
                                                value="<?php echo $Data->ReciprocalTariffs ?>">
                                        </div>

                                        <?php
                                        if ($HasCLFCharges == 1) {
                                        ?>
                                            <div class="col col-md-3 mb-3">
                                                <label for="txtClearingCharges" class="form-label">Clearing & Forwarding Charges</label>
                                                <input type="text" class="form-control" id="txtClearingCharges"
                                                    name="txtClearingCharges" placeholder="Enter Charges"
                                                    value="<?php echo $Data->ClearingCharges ?>">
                                            </div>
                                        <?php
                                        }
                                        ?>

                                        <?php
                                        if ($HasPIOtherTax == 1) {

                                        ?>
                                            <div class="col col-md-3 mb-3">
                                                <label for="txtOtherTaxPerc" class="form-label">Other Tax</label>
                                                <input type="text" class="form-control" id="txtOtherTaxPerc"
                                                    name="txtOtherTaxPerc" placeholder="Enter Other Tax"
                                                    value="<?php echo $Data->OtherTax ?>">
                                            </div>
                                        <?php
                                        }
                                        ?>



                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInsurancePremiumRate" class="form-label">Insurance Premium
                                                Rate</label>
                                            <input type="text" class="form-control" id="txtInsurancePremiumRate"
                                                name="txtInsurancePremiumRate"
                                                placeholder="Enter Insurance Premium Rate"
                                                value="<?php echo $Data->InsurancePremiumRate ?>">
                                        </div>
                                        <?php
                                        if ($HasTariff == 1) {
                                        ?>
                                            <div class="col col-md-3 mb-3">
                                                <label for="txtTariff" class="form-label">Tariff</label>
                                                <input type="text" class="form-control" id="txtTariff"
                                                    name="txtTariff" placeholder="Enter Tariff"
                                                    value="<?php echo $Data->Tariff ?>">
                                            </div>
                                            <!-- <div class="col col-md-3 mb-3">
                                                <label for="txtPrintTariff" class="form-label">Print Tariff</label>
                                                <select class="drpSelect form-control" name="txtPrintTariff" id="txtPrintTariff">
                                                    <option value="">Select</option>
                                                    <option value="1" <?php echo ($Data->PrintTariff == 1) ? 'selected' : ''; ?>>YES</option>
                                                    <option value="0" <?php echo ($Data->PrintTariff == 0) ? 'selected' : ''; ?>>NO</option>
                                                </select>
                                            </div> -->
                                        <?php
                                        }
                                        ?>
                                        <!-- <?php
                                        if ($ShowReciprocalTariff == 1) {
                                        ?>
                                            <div class="col col-md-3 mb-3">
                                                <label for="txtPrintReciprocalTariff">Print Reciprocal Tariff<span style="color:red">*</span></label>
                                                <select class="drpSelect form-control" name="txtPrintReciprocalTariff" id="txtPrintReciprocalTariff">
                                                    <option value="">Select</option>
                                                    <option value="1" <?php echo ($Data->PrintReciprocalTariff == 1) ? 'selected' : ''; ?>>YES</option>
                                                    <option value="0" <?php echo ($Data->PrintReciprocalTariff == 0) ? 'selected' : ''; ?>>NO</option>
                                                </select>
                                            </div>
                                        <?php
                                        }
                                        ?> -->
                                        <!-- <div class="col col-md-3 mb-3">
        <label for="txtUsClearance" class="form-label">US Clearance</label>
        <input type="text" class="form-control" id="txtUsClearance"
            name="txtUsClearance" placeholder="Enter US Clearance"
            value="<?php echo $Data->UsClearance ?>">
    </div> -->

                                        <!-- <div class="col col-md-3 mb-3">
        <label for="txtIGSTRaw" class="form-label">IGST Raw</label>
        <input type="text" class="form-control" id="txtIGSTRaw" name="txtIGSTRaw"
            placeholder="Enter IGST Raw" value="<?php echo $Data->IGSTRaw ?>">
    </div>
    <div class="col col-md-3 mb-3">
        <label for="txtIGSTBrainFrozen" class="form-label">IGST Brain Frozen</label>
        <input type="text" type="text" class="form-control" id="txtIGSTBrainFrozen"
            name="txtIGSTBrainFrozen" placeholder="Enter IGST Brain Frozen"
            value='<?php echo $Data->IGSTBrainFrozen ?>' />
    </div>

    <div class="col col-md-3 mb-3">
        <label for="txtIGSTCooked" class="form-label">IGST Cooked</label>
        <input type="text" class="form-control" id="txtIGSTCooked"
            name="txtIGSTCooked" placeholder="Enter IGST Cooked"
            value="<?php echo $Data->IGSTCooked ?>">
    </div>

    <div class="col col-md-3 mb-3">
        <label for="txtIGSTCookedInShell" class="form-label">IGST CookedIn
            Shell</label>
        <input type="text" class="form-control" id="txtIGSTCookedInShell"
            name="txtIGSTCookedInShell" placeholder="Enter IGST CookedIn Shell"
            value="<?php echo $Data->IGSTCookedInShell ?>">
    </div>

    <div class="col col-md-3 mb-3">
        <label for="txtIGSTBlanched" class="form-label">IGST Blanched</label>
        <input type="text" class="form-control" id="txtIGSTBlanched"
            name="txtIGSTBlanched" placeholder="Enter IGST Blanched"
            value="<?php echo $Data->IGSTBlanched ?>">
    </div> -->


                                        <!-- <div class="col col-md-3 mb-3">
        <label for="txtUptoDate" class="form-label">Upto Date</label>
        <input type="date" class="form-control" id="txtUptoDate" name="txtUptoDate" placeholder="Enter Upto Date" value="<?php echo $Data->UptoDate ?>">
    </div> -->

                                        <div class="text-center">
                                            <input type="hidden" id="hdnTaxCharge" name="hdnTaxCharge"
                                                value="<?php echo $ExpTRID ?>" />
                                            <!-- <input type="hidden" id="hdnTaxCharge" name="hdnTaxCharge" value="" /> -->
                                            <a href="tax-charges-list" class="btn btn-danger">Close</a>
                                            <?php if ($ExpTRID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit"
                                                    id="submit">Save</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit"
                                                    id="submit">Update</button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>
    <script>
        // var expTRID = "<?php echo isset($_GET["ExpTRID"]) && !empty($_GET["ExpTRID"]) ? $_GET["ExpTRID"] : "" ?>";

        // $(document).ready(function() {
        // $('#drpCountry').select2();
        // });

        // $(document).on("change", "#drpExporter", function () {

        // $("#txtFromDate").val("");
        // $("#txtAntiDumpingDuty").val("");
        // $("#txtMerchandiseProcessFee").val("");
        // $("#txtHarbourMaintenanceFee").val("");
        // $("#txtCountervailingDuty").val("");
        // $("#txtServiceTaxPercentage").val("");
        // $("#txtClearanceChargePercentage").val("");
        // $("#txtDrawbackRateperKG").val("");
        // $("#txtDrawbackPercentage").val("");
        // $("#txtBLServiceTax").val("");
        // $("#txtISFStaticDuty").val("");
        // $("#txtInsurancePremiumRate").val("");
        // $("#txtUsClearance").val("");
        // $("#txtIGSTRaw").val("");
        // $("#txtIGSTBrainFrozen").val("");
        // $("#txtIGSTCooked").val("");
        // $("#txtIGSTCookedInShell").val("");
        // $("#txtIGSTBlanched").val("");
        // $("#hdnTaxCharge").val("");

        // $("#submit").text("Save");
        // $("#submit").removeClass("btn-success").addClass("btn-primary");

        // });
        // $(document).on("change", "#txtFromDate", function () {

        // $.ajax({
        // url: 'Ajax/ajax-create-tax-charges.php',
        // type: 'POST',
        // data: {
        // methodType: "getData",
        // fromDate: $('#txtFromDate').val(),
        // exporter: $('#drpExporter').val(),
        // },
        // success: function (data) {
        // console.log(data);

        // if (data.Data && data.Data.length > 0) {

        // var responseData = data.Data[0];

        // $("#txtAntiDumpingDuty").val(responseData.AntiDumpingDuty);
        // $("#txtMerchandiseProcessFee").val(responseData.MerchandiseProcessFee);
        // $("#txtHarbourMaintenanceFee").val(responseData.HarbourMaintenanceFee);
        // $("#txtCountervailingDuty").val(responseData.CountervailingDuty);
        // $("#txtServiceTaxPercentage").val(responseData.ServiceTaxPercentage);
        // $("#txtClearanceChargePercentage").val(responseData.ClearanceChargeFee);//
        // $("#txtDrawbackRateperKG").val(responseData.DrawbackRateperKG);
        // $("#txtDrawbackPercentage").val(responseData.DrawbackPercentage);
        // $("#txtBLServiceTax").val(responseData.BLServiceTax);
        // $("#txtISFStaticDuty").val(responseData.ISFStaticDuty);
        // $("#txtInsurancePremiumRate").val(responseData.InsurancePremiumRate);
        // $("#txtUsClearance").val(responseData.UsClearance);
        // $("#txtIGSTRaw").val(responseData.IGSTRaw);
        // $("#txtIGSTBrainFrozen").val(responseData.IGSTBrainFrozen);
        // $("#txtIGSTCooked").val(responseData.IGSTCooked);
        // $("#txtIGSTCookedInShell").val(responseData.IGSTCookedInShell);
        // $("#txtIGSTBlanched").val(responseData.IGSTBlanched);
        // // $("#txtFromDate").val(responseData.FromDate);
        // $("#hdnTaxCharge").val(responseData.ExpTRID);

        // $("#submit").text("Update");
        // $("#submit").removeClass("btn-primary").addClass("btn-success");

        // } else {

        // $("#txtAntiDumpingDuty").val("");
        // $("#txtMerchandiseProcessFee").val("");
        // $("#txtHarbourMaintenanceFee").val("");
        // $("#txtCountervailingDuty").val("");
        // $("#txtServiceTaxPercentage").val("");
        // $("#txtClearanceChargePercentage").val("");
        // $("#txtDrawbackRateperKG").val("");
        // $("#txtDrawbackPercentage").val("");
        // $("#txtBLServiceTax").val("");
        // $("#txtISFStaticDuty").val("");
        // $("#txtInsurancePremiumRate").val("");
        // $("#txtUsClearance").val("");
        // $("#txtIGSTRaw").val("");
        // $("#txtIGSTBrainFrozen").val("");
        // $("#txtIGSTCooked").val("");
        // $("#txtIGSTCookedInShell").val("");
        // $("#txtIGSTBlanched").val("");
        // $("#hdnTaxCharge").val("");

        // $("#submit").text("Save");
        // $("#submit").removeClass("btn-success").addClass("btn-primary");
        // }

        // return false;
        // }
        // });
        // });

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
                drpExporter: {
                    required: true,
                },
                txtFromDate: {
                    required: true,
                },
                txtAntiDumpingDuty: {
                    required: true,
                },
                txtISFStaticDuty: {
                    required: true,
                },
            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {

                // var countrys = $('#drpCountry').val();
                // countrys = countrys.join(",");

                $.ajax({
                    url: 'Ajax/ajax-create-tax-charges.php',
                    type: 'POST',
                    data: {
                        methodType: "postData",
                        // expTRID: expTRID,
                        expTRID: $("#hdnTaxCharge").val(),
                        exporter: $("#drpExporter").val(),
                        // country: countrys,
                        antiDumpDuty: $("#txtAntiDumpingDuty").val(),
                        merchandiseProcessFee: $("#txtMerchandiseProcessFee").val(),
                        harbourMaintenanceFee: $("#txtHarbourMaintenanceFee").val(),
                        countervailingDuty: $("#txtCountervailingDuty").val(),
                        serviceTaxPercentage: $("#txtServiceTaxPercentage").val(),
                        clearanceChargePercentage: $("#txtClearanceChargePercentage").val(),
                        drawbackRateperKG: $("#txtDrawbackRateperKG").val(),
                        drawbackPercentage: $("#txtDrawbackPercentage").val(),
                        blServiceTax: $("#txtBLServiceTax").val(),
                        isfStaticDuty: $("#txtISFStaticDuty").val(),
                        insurancePremiumRate: $("#txtInsurancePremiumRate").val(),
                        usClearance: $("#txtUsClearance").val(),
                        reciprocalTariffs: $("#txtReciprocalTariffsPerc").val(),
                        clearingCharges: $("#txtClearingCharges").val(),
                        tariff: $("#txtTariff").val(),
                        // printReciprocalTariff: $("#txtPrintReciprocalTariff").val(),
                        // printTariff: $("#txtPrintTariff").val(),
                        // forwardingCharges: $("#txtForwardingCharges").val(),
                        otherTax: $("#txtOtherTaxPerc").val(),
                        // iGSTRaw: $("#txtIGSTRaw").val(),
                        // iGSTBrainFrozen: $("#txtIGSTBrainFrozen").val(),
                        // iGSTCooked: $("#txtIGSTCooked").val(),
                        // iGSTCookedInShell: $("#txtIGSTCookedInShell").val(),
                        // iGSTBlanched: $("#txtIGSTBlanched").val(),
                        fromDate: $("#txtFromDate").val(),
                        // uptoDate: $("#txtUptoDate").val(),
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "tax-charges-list";
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