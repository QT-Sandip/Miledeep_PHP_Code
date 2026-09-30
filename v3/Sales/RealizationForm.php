<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "/Constants.php");
include("RestAPI.php");

$ClientID = $_SESSION['ClientID'];

$PageTitleFolder = "Payments";
$PageTitle = "RealizationForm - Coll Realisation";
$ContinueSeries = false;

if (isset($_GET["PIID"]) && !empty($_GET["PIID"])) {
    $PIID = $_GET["PIID"];
} else {
    $PIID = "";
}

$api = GetBillRealizationDetails_API;

$bodyContent = array("PoID" => $PIID);

$resGetBillRealizationDetailsAPI = CallAPI($api, "GET", "", $bodyContent);

$resFrmData = $resGetBillRealizationDetailsAPI->Data->BillRealization;
$resBillBreakUp = $resGetBillRealizationDetailsAPI->Data->BillBreakUp;

// var_dump($resFrmData);
if (isset($_POST['submit'])) {

    // $invoiceNo = $_POST["invoiceNo"];
    // $invoiceDate = $_POST["invoiceDate"];
    // $buyer = $_POST["buyer"];
    // $invoiceValue = $_POST["invoiceValue"];
    // $realisedBalance = $_POST["realisedBalance"];
    // $shortExcess = $_POST["shortExcess"];
    $dueDate = $_POST["dueDate"];

    $bankRefNo1 = $_POST["bankRefNo1"];
    $realisedValue1 = $_POST["realisedValue1"];
    $realisedDate1 = $_POST["realisedDate1"];

    $bankRefNo2 = $_POST["bankRefNo2"];
    $realisedValue2 = $_POST["realisedValue2"];
    $realisedDate2 = $_POST["realisedDate2"];

    $bankRefNo3 = $_POST["bankRefNo3"];
    $realisedValue3 = $_POST["realisedValue3"];
    $realisedDate3 = $_POST["realisedDate3"];

    $FBKCharges = $_POST["FBKCharges"];
    $otherCharges = $_POST["otherCharges"];

    $api = SaveBillRealizationDetails_API;

    $bodyContent = array(
        "PIID" => $PIID,  // Getting From URL query string 
        // "InvoiceNo"=>$invoiceNo,
        // "InvoiceDate"=>$invoiceDate,
        // "Buyer"=>$buyer,
        // "InvoiceValue"=>$invoiceValue,
        // "RealisedBalance"=>$realisedBalance,
        // "ShortExcess"=>$shortExcess,
        "DueDate" => $dueDate,
        "BankRefNo1" => $bankRefNo1,
        "RealisedValue1" => $realisedValue1,
        "RealisedDate1" => $realisedDate1,
        "BankRefNo2" => $bankRefNo2,
        "RealisedValue2" => $realisedValue2,
        "RealisedDate2" => $realisedDate2,
        "BankRefNo3" => $bankRefNo3,
        "RealisedValue3" => $realisedValue3,
        "RealisedDate3" => $realisedDate3,
        "FBKCharges" => $FBKCharges,
        "OtherCharges" => $otherCharges,
    );

    $resSaveBillRealization = CallAPI($api, "POST", "", $bodyContent);
    //   print_r ($resSaveBillRealization); exit;

    if (isset($resSaveBillRealization->Msg)) {
        $_SESSION['Status'] = "T";
    }
    if (isset($resSaveBillRealization->Error)) {
        $_SESSION['Error'] = "T";
    }
    header("location: RealizationForm?PIID=" . $PIID);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
    <style>
        select.error+.select2-container,
        input.error,
        textarea.error {
            border: 1px solid red;
        }

        input[type=text]:disabled,
        input[type=date]:disabled {
            background: #F1F1F1;
        }

        #key-datatable_wrapper {
            width: 100%;
        }
    </style>
</head>

<body data-sidebar="dark">
    <?php
    include($IncludesPath . "PreLoader.php"); ?>

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

                    <?php if (isset($_SESSION['Status']) && !empty($_SESSION['Status'])) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all mr-2"></i>
                            Data updated successfully.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php $_SESSION['Status'] = '';      ?>
                    <?php } ?>

                    <?php if (isset($_SESSION['Error']) && !empty($_SESSION['Error'])) { ?>
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

                        <form role="form" method="post" enctype="multipart/form-data" id="frmRealizationForm">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="form-group col-lg-3">
                                                <label for="invoiceNo">Invoice No<span style="color:red">*</span></label>
                                                <input type="text" class="form-control" name='invoiceNo' id="invoiceNo" placeholder="Enter Invoice No" disabled
                                                    value="<?php echo $resFrmData->CustomsInvNo ?>">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="invoiceDate">Invoice Date</label>
                                                <input type="date" onkeydown="return false" class="form-control" name='invoiceDate' id="invoiceDate" placeholder="Enter Invoice Date" disabled
                                                    value="<?php echo $resFrmData->CInvDate ?>">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="buyer">Buyer</label>
                                                <input type="text" class="form-control" name='buyer' id="buyer" placeholder="Enter Buyer" disabled
                                                    value="<?php echo $resFrmData->ConsigneeName ?>">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="invoiceValue">Invoice Value</label>
                                                <input type="text" class="form-control" name='invoiceValue' id="invoiceValue" placeholder="Enter Invoice Value." disabled
                                                    value="<?php echo number_format($resFrmData->InvValueUSD, 2) ?>">
                                            </div>
                                            <div class="form-group col-lg-3">
                                                <label for="realisedBalance">Realised Balance</label>
                                                <input type="text" class="form-control" name='realisedBalance' id="realisedBalance" placeholder="Enter Realised Balance" disabled
                                                    value="<?php echo number_format($resFrmData->RealizedValue, 2) ?>">
                                            </div>
                                            <div class="form-group col-lg-3">
                                                <label for="shortExcess">Bank Charges</label>
                                                <input type="text" class="form-control" name='shortExcess' id="shortExcess" placeholder="Enter Short/Excess" disabled
                                                    value="<?php //echo $resFrmData->ShortFallValue
                                                            echo $resFrmData->BankCharges ?>">
                                            </div>


                                            <div class="form-group col-lg-3">
                                                <label for="dueDate">Due Date.</label>
                                                <input type="date" onkeydown="return false" class="form-control" name='dueDate' id="dueDate" placeholder="Due Date."
                                                    value="<?php echo $resFrmData->DueDate ?>" disabled />
                                            </div>

                                            <div class="form-group col-lg-12">
                                                <hr />
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="bankRefNo">Bank Ref No 1.</label>
                                                <input type="text" class="form-control" name='bankRefNo1' id="bankRefNo1" placeholder="Bank Ref No."
                                                    value="<?php echo $resFrmData->BankRefNo1 ?>" />
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="realisedValue">Realised Value 1</label>
                                                <input type="number" class="form-control" name='realisedValue1' id="realisedValue1" placeholder="Enter Realised Value"
                                                    value="<?php echo $resFrmData->RealisedValue1 ?>" />
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="realisedDate">Realised Date 1</label>
                                                <input type="date" onkeydown="return false" class="form-control" name='realisedDate1' id="realisedDate1" placeholder="Enter Realised Date"
                                                    value="<?php echo $resFrmData->RealisedDate1 ?>" />
                                            </div>

                                            <div class="form-group col-lg-3">&nbsp;</div>

                                            <div class="form-group col-lg-3">
                                                <label for="bankRefNo">Bank Ref No 2.</label>
                                                <input type="text" class="form-control" name='bankRefNo2' id="bankRefNo2" placeholder="Bank Ref No."
                                                    value="<?php echo $resFrmData->BankRefNo2 ?>" />
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="realisedValue">Realised Value 2</label>
                                                <input type="number" class="form-control" name='realisedValue2' id="realisedValue2" placeholder="Enter Realised Value"
                                                    value="<?php echo $resFrmData->RealisedValue2 ?>" />
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="realisedDate">Realised Date 2</label>
                                                <input type="date" onkeydown="return false" class="form-control" name='realisedDate2' id="realisedDate2" placeholder="Enter Realised Date"
                                                    value="<?php echo $resFrmData->RealisedDate2 ?>" />
                                            </div>

                                            <div class="form-group col-lg-3">&nbsp;</div>

                                            <div class="form-group col-lg-3">
                                                <label for="bankRefNo">Bank Ref No 3.</label>
                                                <input type="text" class="form-control" name='bankRefNo3' id="bankRefNo3" placeholder="Bank Ref No."
                                                    value="<?php echo $resFrmData->BankRefNo3 ?>" />
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="realisedValue">Realised Value 3</label>
                                                <input type="number" class="form-control" name='realisedValue3' id="realisedValue3" placeholder="Enter Realised Value"
                                                    value="<?php echo $resFrmData->RealisedValue3 ?>" />
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="realisedDate">Realised Date 3</label>
                                                <input type="date" onkeydown="return false" class="form-control" name='realisedDate3' id="realisedDate3" placeholder="Enter Realised Date"
                                                    value="<?php echo $resFrmData->RealisedDate3 ?>" />
                                            </div>

                                            <div class="form-group col-lg-12">
                                                <hr />
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="FBKCharges">FBK Charges</label>
                                                <input type="text" class="form-control" name='FBKCharges' id="FBKCharges" placeholder="Enter FBK Charges"
                                                    value="<?php echo $resFrmData->FBKCharges ?>" />
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="otherCharges">Other Charges</label>
                                                <input type="text" class="form-control" name='otherCharges' id="otherCharges" placeholder="Enter Other Charges"
                                                    value="<?php echo $resFrmData->OtherCharges ?>" />
                                            </div>
                                            <div class="form-group col-lg-12">
                                                <input type="hidden" id="hdnRealised2" />
                                                <input type="hidden" id="hdnRealised3" />
                                            </div>

                                            <!-- <div class="form-group col-lg-12" >
                                                    <p class="pError text-danger" style="display: none">Realised Value Should be less than Short Fall</p>                             
                                                </div> -->

                                            <!-- <div class="form-group col-lg-4" >
                                                    <label for="realisedDate1">Realised Date1</label>
                                                    <input type="text" class="form-control" name='realisedDate1' id="realisedDate1" placeholder="Enter Realised Date1"  
                                                        value="<?php // echo $resFrmData->CustomsInvNo 
                                                                ?>">
                                                </div>

                                                <div class="form-group col-lg-4" >
                                                    <label for="realisedValue1">Realised Value1</label>
                                                    <input type="text" class="form-control" name='realisedValue1' id="realisedValue1" placeholder="Enter Realised Value1"  
                                                        value="<?php // echo $resFrmData->CustomsInvNo 
                                                                ?>">
                                                </div> -->

                                        </div>

                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-12 text-right pr-5">
                                            <!-- <button hidden type="submit"  name='submit' id="Create" class="btn btn-success">Next</button> -->
                                            <!--<a target="" href="PIList.php" class="btn btn-danger"> Close</a>-->
                                            <a href="Realization" id="close" name="close" class="btn btn-danger">Close </a>
                                            <button type="submit" name="submit" id="CreatePI" class="btn btn-success">Make Payment</button>

                                        </div>
                                    </div>

                                    <!-- end col -->
                                </div> <!-- end row -->
                            </div> <!-- end card body-->

                            <div class="col-12">
                                <div class="card">

                                    <div class="card-body">
                                        <div class="row">
                                            <table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>S.No</th>
                                                        <th>Type</th>
                                                        <th>Ref.No.</th>
                                                        <th>Utilised Date</th>
                                                        <th>Utilised Value $</th>
                                                        <th>Rate</th>
                                                        <th>Utilised Value INR</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $i = 1;
                                                    foreach ($resBillBreakUp as $item) { ?>
                                                        <tr>
                                                            <td><?php echo $i ?></td>
                                                            <td> <?php echo $item->ColType ?></td>
                                                            <td> <?php echo $item->BankRefNo ?></td>
                                                            <td><?php echo date('d-m-Y', strtotime($item->UtilizedDate)) ?></td>
                                                            <td> <?php echo $item->UtilizedValue ?></td>
                                                            <td><?php echo $item->SpotRate ?></td>
                                                            <td><?php echo $item->UtilizedValueINR ?></td>
                                                        </tr>
                                                    <?php $i++;
                                                    } ?>
                                                    <tr style="font-weight: bold;font-size: 16px;">
                                                        <td colspan="4"> <?php echo "Grand Total"; ?></td>
                                                        <td style="display: none"></td>

                                                        <td><?php echo $resFrmData->GrandUtilizedValue ?></td>
                                                        <td></td>
                                                        <td><?php echo $resFrmData->GrandValueINR ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>

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

    <?php include($IncludesPath . "footer.php"); ?>

    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>

    <script>
        jQuery.validator.addMethod('selectcheck', function(value) {
            return (value != '0' || value != '');
        }, "year required");

        jQuery.validator.addMethod("noSpace", function(value, element) {
            console.log(value == '' || value.trim().length != 0);
            return value == '' || value.trim().length != 0;
        }, "No space please and don't leave it empty");

        $(document).ready(function() {
            var clientID = <?php echo $ClientID; ?>;
            $("#frmRealizationForm").validate({
                onkeyup: false,
                onfocusout: false,
                invalidHandler: function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        var firstInvalidElement = $(validator.errorList[0].element);
                        //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
                        firstInvalidElement.focus();
                    }
                },
                ignore: "",
                rules: {
                    invoiceNo: {
                        required: true,
                        noSpace: true
                    },
                    invoiceDate: {
                        required: true,
                        noSpace: true
                    },
                    buyer: {
                        required: true,
                        noSpace: true
                    },
                    invoiceValue: {
                        required: true,
                        noSpace: true
                    },
                    realisedBalance: {
                        required: true,
                        noSpace: true
                    },
                    shortExcess: {
                        required: true,
                        noSpace: true
                    },
                    // dueDate: { required: true, noSpace: true },
                    bankRefNo1: {
                        required: clientID != 'SMEV3',
                        noSpace: true
                    },
                    realisedValue1: {
                        required: true,
                        number: true
                    },
                    realisedDate1: {
                        required: true,
                        noSpace: true
                    },

                    bankRefNo2: {
                        required: function() {
                            return $("#hdnRealised2").val().length > 0;
                        },
                        noSpace: true
                    },
                    realisedValue2: {
                        required: function() {
                            return $("#hdnRealised2").val().length > 0;
                        },
                        number: true
                    },
                    realisedDate2: {
                        required: function() {
                            return $("#hdnRealised2").val().length > 0;
                        },
                        noSpace: true
                    },

                    bankRefNo3: {
                        required: function() {
                            return $("#hdnRealised3").val().length > 0;
                        },
                        noSpace: true
                    },
                    realisedValue3: {
                        required: function() {
                            return $("#hdnRealised3").val().length > 0;
                        },
                        number: true
                    },
                    realisedDate3: {
                        required: function() {
                            return $("#hdnRealised3").val().length > 0;
                        },
                        noSpace: true
                    },

                    FBKCharges: {
                        required: true,
                        number: true,
                        noSpace: true
                    },
                    otherCharges: {
                        number: true
                    },
                    // shippingDate: { required: true, noSpace: true },

                },
                errorPlacement: function() {
                    return false; // suppresses error message text
                },
                submitHandler: function() {
                    return true;
                }
            });
        });

        $('#frmRealizationForm').submit(function() {
            // var invoiceValue = parseFloat($("#invoiceValue").val());
            var invoiceValue = $("#invoiceValue").val().replace(/,/g, ''); //uma              
            var realisedValue1 = parseFloat($("#realisedValue1").val() == "" ? 0 : $("#realisedValue1").val());
            var realisedValue2 = parseFloat($("#realisedValue2").val() == "" ? 0 : $("#realisedValue2").val());
            var realisedValue3 = parseFloat($("#realisedValue3").val() == "" ? 0 : $("#realisedValue3").val());
            var FBKCharges = parseFloat($("#FBKCharges").val() == "" ? 0 : $("#FBKCharges").val());
            var otherCharges = parseFloat($("#otherCharges").val() == "" ? 0 : $("#otherCharges").val());
            // var bankCharges = $("#shortExcess").val();
            var bankCharges = parseFloat($("#shortExcess").val() == "" ? 0 : $("#shortExcess").val());


            // var total = parseFloat(realisedValue1 + realisedValue2 + realisedValue3 + FBKCharges + otherCharges).toFixed(2);
            // var total = parseFloat(realisedValue1 + realisedValue2 + realisedValue3 + FBKCharges + otherCharges+bankCharges).toFixed(2);//uma
            var total = round(realisedValue1 + realisedValue2 + realisedValue3 + FBKCharges + otherCharges + bankCharges, 2);

            // alert(invoiceValue);
            // alert(total);
            // var total = realisedValue1 + realisedValue2 + realisedValue3 + FBKCharges + otherCharges;
            if (total > invoiceValue) {
                console.log(total + "--" + invoiceValue, "Error");
                alert('Realised Value Should be less than Short Fall');
                // alert(total);
                return false;
            } else {
                // alert(total);
                return true;
            }

        });

        function fnRealisedTwoCheck() {
            var bankRefNo2 = $("#bankRefNo2").val();
            var realisedValue2 = $("#realisedValue2").val();
            var realisedDate2 = $("#realisedDate2").val();

            if (bankRefNo2.length > 0 || realisedValue2.length > 0 || realisedDate2.length > 0) {
                $("#hdnRealised2").val("T");
            } else {
                $("#hdnRealised2").val("");
            }
        }

        function fnRealisedThreeCheck() {

            var bankRefNo3 = $("#bankRefNo3").val();
            var realisedValue3 = $("#realisedValue3").val();
            var realisedDate3 = $("#realisedDate3").val();

            if (bankRefNo3.length > 0 || realisedValue3.length > 0 || realisedDate3.length > 0) {
                $("#hdnRealised3").val("T");
                $("#hdnRealised2").val("T");
            } else {
                $("#hdnRealised3").val("");
                $("#hdnRealised2").val("");
            }
        }

        $(document).on("change keyup", "#bankRefNo2, #realisedValue2, #realisedDate2", function() {
            fnRealisedTwoCheck();
        });

        $(document).on("change keyup", "#bankRefNo3, #realisedValue3, #realisedDate3, #realisedDate3", function() {
            fnRealisedThreeCheck();
        });
    </script>

</body>

</html>