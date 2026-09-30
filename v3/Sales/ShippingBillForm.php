<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

    include_once ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
    include_once ($_SERVER['DOCUMENT_ROOT']."/SalesNew/Constants.php");
    include_once("Includes/CommercialInvoiceLock.php");
    include_once ("RestAPI.php");

    $PageTitleFolder="Shipping ";
    $PageTitle="Shipping Billing";
    $ContinueSeries = false;

    if(isset($_GET["PoID"]) && !empty($_GET["PoID"])){
        $PoID = $_GET["PoID"];
    }else{
        $PoID = "";
    }

    if(!empty($PoID)) {

        $api = GetShipBillDetails_API;
        $bodyContent = array('PoID' => $PoID);
        $resGetShipBillDetails = CallAPI($api, "GET", "", $bodyContent);

        $resGetShipBill = $resGetShipBillDetails->Data->Data;
        $isCommercialInvoiceLocked = isCommercialInvoiceLockedByPiId($iconn, $PoID);
    }else{
        $resGetShipBillDetails = "";
    }

    function funPrintValue($key) {
        echo (isset($key) && $key != "") ? $key : "";
    }

    $PostedUserId = $_SESSION['UserID'];
    // echo $PostedUserId;

    if(isset($_POST['submit'])){
        $invoiceNo = $_POST["invoiceNo"];
        $invoiceDate = $_POST["invoiceDate"];
        $consignee = $_POST["consignee"];
        $portofLoading = $_POST["portofLoading"];
        $portofDischarge = $_POST["portofDischarge"];
        $countyofDestination = $_POST["countyofDestination"];
        $totalNoOfCartons = $_POST["totalNoOfCartons"];
        $netWt = $_POST["netWt"];
        $invoiceValue = $_POST["invoiceValue"];
        $invoiceFreight = $_POST["invoiceFreight"];
        $invoiceOtherDeductions = $_POST["invoiceOtherDeductions"];
        $fOBINR = $_POST["fOBINR"];

        $shippingBillNo = $_POST["shippingBillNo"];
        $shippingDate = $_POST["shippingDate"];
        $lEODate = $_POST["lEODate"];
        $sbInvVal = $_POST["sbInvVal"];
        $freight = $_POST["freight"];
        $otherDeductions = $_POST["otherDeductions"];
        $exchangeRate = $_POST["exchangeRate"];
        $sBFobUs = $_POST["sBFobUs"];
        $sbFobInr = $_POST["sbFobInr"];

        // echo $shippingBillNo ." -- ". $_POST["shippingBillNo"];

        $api = PostShippingBillDetails_API;
        $postBodyContent = array(
            'InvID'=>$PoID,
            'ShippingBillNo'=>$shippingBillNo,
            'ShippingBillDate'=>$shippingDate,
            'LEODate'=>$lEODate,
            'ExchangeRate'=>$exchangeRate,
            'SBFOBUSD'=>$sBFobUs,
            'SBFOBINR'=>$sbFobInr,
            'SBFreight'=>$freight,
            'PostedUserId'=>$PostedUserId
        );

        $resPostShippingBillDetails = CallAPI($api, "POST", "", $postBodyContent);
        // var_dump($resPostShippingBillDetails);
        $resPostShipBill = $resPostShippingBillDetails->Data;

        if(isset($resPostShipBill->Msg)) {
            $_SESSION['Status'] = "T";
        }
        if(isset($resPostShipBill->Error)) {
            $_SESSION['Error'] = "T";
        }
        header("location: ShippingBillForm.php?PoID=".$PoID);
        exit();

    }

$hideIsfEditActions = $isCommercialInvoiceLocked;
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include ($IncludesPath."title-meta.php"); ?>
        <?php  include ($IncludesPath."DbLinks.php");?>
        <?php include ($IncludesPath."head-css.php");?>
        <style>
            select.error + .select2-container, input.error, textarea.error {
                border: 1px solid red;
            }
            input[type=text]:disabled, input[type=date]:disabled{
                background: #F1F1F1;
            }
        </style>
    </head>
    <body data-sidebar="dark">
     <?php
        include ($IncludesPath."PreLoader.php"); ?>

        <!-- Begin page -->
        <div id="layout-wrapper">
        <?php
            include ($IncludesPath."topbar.php");
            include ($IncludesPath."sidebar.php");
        ?>
 <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
                        <?php include ($IncludesPath."page-title.php"); ?>

                        <?php if(isset($_SESSION['Status']) && !empty($_SESSION['Status'])) { ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="mdi mdi-check-all mr-2"></i>
                                Data updated successfully.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <?php $_SESSION['Status'] = '';   ?>
                        <?php } ?>

                        <?php if(isset($_SESSION['Error']) && !empty($_SESSION['Error'])) { ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="mdi mdi-check-all mr-2"></i>
                                Some error has occurred.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <?php $_SESSION['Error'] = ''; ?>
                        <?php } ?>

                        <div class="row">

                            <form role="form" method="post"  id="frmShippingBilling">
                            <div class="col-12">
                                <div class="card">
                                        <div class="card-body">
                                            <div class="row">

                                                <div class="form-group col-lg-4" >
                                                    <label for="invoiceNo">Invoice No<span style="color:red">*</span></label>
                                                    <input type="text" class="form-control" name='invoiceNo' id="invoiceNo" placeholder="Enter Invoice No" disabled
                                                        value="<?php echo funPrintValue($resGetShipBill->CInvNo) ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="invoiceDate">Invoice Date</label>
                                                    <input type="date" onkeydown="return false" class="form-control" name='invoiceDate' id="invoiceDate" placeholder="Enter Invoice Date" disabled
                                                        value="<?php echo funPrintValue($resGetShipBill->CInvDate) ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="consignee">Consignee</label>
                                                    <input type="text" class="form-control" name='consignee' id="consignee" placeholder="Enter Consignee" disabled
                                                        value="<?php echo funPrintValue($resGetShipBill->ConsigneeName) ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="portofLoading">Port of Loading</label>
                                                    <input type="text" class="form-control" name='portofLoading' id="portofLoading" placeholder="Port of Loading" disabled
                                                        value="<?php echo funPrintValue($resGetShipBill->LoadingPort) ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="portofDischarge">Port of Discharge</label>
                                                    <input type="text" class="form-control" name='portofDischarge' id="portofDischarge" placeholder="Port of Discharge" disabled
                                                        value="<?php echo funPrintValue($resGetShipBill->DischargePort) ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="countyofDestination">County of Destination</label>
                                                    <input type="text" class="form-control" name='countyofDestination' id="countyofDestination" placeholder="County of Destination" disabled
                                                        value="<?php echo funPrintValue($resGetShipBill->CountryName) ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="totalNoOfCartons">Total No.of Cartons</label>
                                                    <input type="text" class="form-control" name='totalNoOfCartons' id="totalNoOfCartons" placeholder="Enter Total No.of Cartons" disabled
                                                        value="<?php echo funPrintValue($resGetShipBill->TotalMcs) ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="netWt">Net Wt (KGS)</label>
                                                    <input type="text" class="form-control" name='netWt' id="netWt" placeholder="Enter Net Wt" disabled
                                                        value="<?php echo funPrintValue($resGetShipBill->TotalNetWt) ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="invoiceValue">Invoice Value</label>
                                                    <input type="text" class="form-control" name='invoiceValue' id="invoiceValue" placeholder="Enter Invoice Value" disabled
                                                        value="<?php echo funPrintValue($resGetShipBill->InvValueUSD) ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="invoiceFreight">Freight</label>
                                                    <input type="text" class="form-control" name='invoiceFreight' id="invoiceFreight" placeholder="Enter Freight" disabled
                                                        value="<?php echo funPrintValue($resGetShipBill->Freight) ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="invoiceOtherDeductions">Other Deductions $</label>
                                                    <input type="text" class="form-control" name='invoiceOtherDeductions' id="invoiceOtherDeductions" placeholder="Enter Other Deductions $" disabled
                                                        value="<?php echo funPrintValue($resGetShipBill->Duty)  ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="fOBINR">FOB INR</label>
                                                    <input type="text" class="form-control" name='fOBINR' id="fOBINR" placeholder="Enter FOB INR" disabled
                                                        value="<?php echo funPrintValue($resGetShipBill->FOBValueINR) ?>">
                                                </div>

                                            </div>



                                        </div>

                                <!-- end col -->
                                </div> <!-- end row -->
                            </div> <!-- end card body-->

                            <div class="col-12">
                                <div class="card">

                                        <div class="card-body">
                                            <div class="row">

                                                <div class="form-group col-lg-4" >
                                                    <label for="shippingBillNo">Shipping Bill No<span style="color:red">*</span></label>
                                                    <input type="text" class="form-control" name='shippingBillNo' id="shippingBillNo" placeholder="Enter Shipping Bill No"
                                                        value="<?php echo funPrintValue($resGetShipBill->ShippingBillNo) ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="shippingDate">Shipping Date<span style="color:red">*</span></label>
                                                    <input type="date" onkeydown="return false" class="form-control" name='shippingDate' id="shippingDate" placeholder="Enter Shipping Date"
                                                        value="<?php echo funPrintValue($resGetShipBill->ShippingBillDate)  ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="lEODate">LEO Date<span style="color:red">*</span></label>
                                                    <input type="date" onkeydown="return false" class="form-control" name='lEODate' id="lEODate" placeholder="Enter LEO Date"
                                                        value="<?php echo funPrintValue($resGetShipBill->LEODate) ?>">
                                                </div>



                                                <div class="form-group col-lg-4" style="visibility: hidden;width: 0px;height: 0px;"> <!-- please enable jquery validation in script   -->
                                                    <label for="sbInvVal">SB Inv Val (INR)<span style="color:red">*</span></label>
                                                    <input type="text" class="form-control" name='sbInvVal' id="sbInvVal" placeholder="Enter SB Inv Val(INR)"
                                                        value="<?php // echo "" ?>">
                                                </div>

                                                <div class="form-group col-lg-4"  style="visibility: hidden;width: 0px;height: 0px;"> <!-- please enable jquery validation in script   -->
                                                    <label for="freight">Freight<span style="color:red">*</span></label>
                                                    <input type="text" class="form-control" name='freight' id="freight" placeholder="Enter Freight"
                                                        value="<?php echo funPrintValue($resGetShipBill->SBFreight) ?>">
                                                </div>

                                                <div class="form-group col-lg-4"  style="visibility: hidden;width: 0px;height: 0px;"> <!-- please enable jquery validation in script   -->
                                                    <label for="otherDeductions">Other Deductions $<span style="color:red">*</span></label>
                                                    <input type="text" class="form-control" name='otherDeductions' id="otherDeductions" placeholder="Enter Other Deductions $"
                                                        value="<?php  echo "" ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="exchangeRate">Exchange Rate<span style="color:red">*</span></label>
                                                    <input type="text" class="form-control" name='exchangeRate' id="exchangeRate" placeholder="Enter Exchange Rate"
                                                        value="<?php echo funPrintValue($resGetShipBill->SBExchangeRate) ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="sBFobUs">SB FOB US $<span style="color:red">*</span></label>
                                                    <input type="text" class="form-control" name='sBFobUs' id="sBFobUs" placeholder="Enter SB FOB US$"
                                                        value="<?php echo funPrintValue($resGetShipBill->InvValueUSD) ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="sbFobInr">SB FOB INR<span style="color:red">*</span></label>
                                                    <input type="text" class="form-control" name='sbFobInr' readonly id="sbFobInr" placeholder="Enter SB FOB INR"
                                                        value="<?php echo funPrintValue($resGetShipBill->FOBValueINR) ?>">
                                                </div>

                                            </div>

                                            <div class="row">
                                                <div class="col-12 text-right">
                                                    <!-- <button hidden type="submit"  name='submit' id="Create" class="btn btn-success">Next</button> -->
                                                    <!--<a target="" href="PIList.php" class="btn btn-danger"> Close</a>-->
                                                    <a href="shipping-bill" id="close"  name="close"  class="btn btn-danger">Close </a>
                                                    <?php if( !$hideIsfEditActions ){ ?>
                                                        <button type="submit"  name='submit' id="CreatePI" class="btn btn-success">Save</button>
                                                    <?php } ?>
                                                </div>
                                            </div>

                                        </div>
                                        <!-- end col -->
                                    </div> <!-- end row -->
                                </div> <!-- end card body-->
                            </form>

                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div>
                <!-- end row-->

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <?php include ($IncludesPath."footer.php");?>

    </div>
            <!-- end main content-->

</div>
        <!-- END layout-wrapper -->

    <?php include ($IncludesPath."vendor-scripts.php");?>
    <?php include ($IncludesPath."NewScripts.php");?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>

        <script>
            $(document).on("keyup", "#exchangeRate, #sBFobUs", function(){
                this.value = this.value.replace(/[^0-9\.]/g,'');
                var val1 = +$("#exchangeRate").val();
                var val2 = +$("#sBFobUs").val();
                var sbFobInr=val1 * val2;
                // $("#sbFobInr").val(val1 * val2);
                $("#sbFobInr").val(parseFloat(sbFobInr).toFixed(2));
            })
        </script>
        <script>
            jQuery.validator.addMethod('selectcheck', function (value) {
                return (value != '0' || value != '');
            }, "year required");

            jQuery.validator.addMethod("noSpace", function(value, element) {
                return value == '' || value.trim().length != 0;
            }, "No space please and don't leave it empty");

            $(document).ready(function(){
                $("#frmShippingBilling").validate({
                    onkeyup: false,
                    onfocusout: false,
                    invalidHandler: function (form, validator) {
                        var errors = validator.numberOfInvalids();
                        if (errors) {
                            var firstInvalidElement = $(validator.errorList[0].element);
                            //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
                            firstInvalidElement.focus();
                        }
                    },
                    ignore: "",
                    rules: {
                        invoiceNo: { required: true, noSpace: true },
                        invoiceDate: { required: true, noSpace: true },
                        consignee: { required: true, noSpace: true },
                        portofLoading: { required: true, noSpace: true },
                        portofDischarge: { required: true, noSpace: true },
                        countyofDestination: { required: true, noSpace: true },
                        totalNoOfCartons: { required: true, noSpace: true },
                        netWt: { required: true, noSpace: true },
                        invoiceValue: { required: true, noSpace: true },
                        invoiceFreight: { required: true, noSpace: true },
                        invoiceOtherDeductions: { required: true, noSpace: true },
                        fOBINR: { required: true, noSpace: true },
                        shippingBillNo: { required: true, noSpace: true },
                        shippingDate: { required: true, noSpace: true },
                        lEODate: { required: true, noSpace: true },
                        // sbInvVal: { required: true, noSpace: true },
                        // freight: { required: true, noSpace: true },
                        // otherDeductions: { required: true, noSpace: true },
                        exchangeRate: { required: true, noSpace: true,number:true },
                        sBFobUs: { required: true, noSpace: true,number:true },
                        sbFobInr: { required: true, noSpace: true,number:true,min: 1 },

                    },
                    errorPlacement: function () {
                        return false;  // suppresses error message text
                    },
                    submitHandler: function () {
                        return true;
                    }
                });
            });
        </script>

    </body>
</html>



