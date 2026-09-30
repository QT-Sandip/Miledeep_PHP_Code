<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);



//include ("RestAPI.php");

$PageTitle = "Clearing Agent Form";
$PageTitleFolder = "Payments";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/api-call.php';
include($cp . "Includes/IncludePage.php");


$uid = $_SESSION['r_id'];


// Get the current month
/*$month = date('m');
// Check if the current month is greater than or equal to April
if ($month >= 4) {
	// Get the current year
	$year = date('Y');
} else {
	// Get the previous year
	$year = date('Y') - 1;
}
// Print the financial year
//echo $year . '-' . ($year + 1);

$EndDate = date("Y-m-d");*/

$uid = $_SESSION['UserID'];

$ID = isset($_GET['id']) ? $_GET['id'] : "";
$Status = isset($_GET['status']) ? $_GET['status'] : 0;

if ($ID != "") {
    $apiGet = GetClearingAgentFormDataAPI;
    $bodyContent = array("PoID" => $ID);
    $resGetClearingAgentFormDataAPI = CallAPI($apiGet, "GET", "", $bodyContent);
    $resList = $resGetClearingAgentFormDataAPI->Data->ClearingAgentFormData[0];
}

$SqlClearingAgentTransaction = "SELECT * FROM `ClearingAgentTransaction` ORDER by sort ASC";
$StmtClearingAgentTransaction = $iconn->query($SqlClearingAgentTransaction);


function funPrintValue($key)
{
    return (isset($key) && $key != "") ? $key : "";
}


extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {

    $apiPost = PostClearingAgentDetailsAPI;


    $ClearingAgentEntryNumber = $_POST["entryNumber"];
    $EntryDate = $_POST["entryDate"];
    $ClearingAgentInvoiceNo = $_POST["invNo"];
    $ClearingAgentInvoiceDate = $_POST["invdate"];
    $CandFCharges = $_POST["cAndFCharges"];
    $ADCharges = $_POST["adDuty"];
    $HmDutyCharges = $_POST["hmDuty"];
    $MPCharges = $_POST["mpDuty"];
    $CVDutyCharges = $_POST["cvDuty"];
    $RTDutyCharges = $_POST["rtDuty"];
    $InvValue = $_POST["invValue"];
    $EnteredInvValue = $_POST["enteredInvValue"];



    $bodyContent = array(
        "ClearingAgentEntryNumber" => $ClearingAgentEntryNumber,
        "EntryDate" => $EntryDate,
        "CandFCharges" => $CandFCharges,
        "ClearingAgentInvoiceNo" => $ClearingAgentInvoiceNo,
        "ClearingAgentInvoiceDate" => $ClearingAgentInvoiceDate,
        "ADCharges" => $ADCharges,
        "HmDutyCharges" => $HmDutyCharges,
        "MPCharges" => $MPCharges,
        "CVDutyCharges" => $CVDutyCharges,
        "RTDutyCharges" => $RTDutyCharges,
        "InvValue" => $InvValue,
        "EnteredInvValue" => $EnteredInvValue,
        "PostedUserID" => $uid,
        "PoID" => $ID
    );

    $resPostClearingAgentDetailsAPI = CallAPI($apiPost, "GET", "", $bodyContent);
    $resPost = $resPostClearingAgentDetailsAPI->Data;

    if (isset($resPost->Error)) {
        $Status = "Error has Occurred";
        $_SESSION['danger'] = 'T';
    } else {
        $Status = $resPost->Msg;
        $_SESSION['Status'] = 'T';
    }

    $url = "ClearingAgentForm?id=" . $ID;

    header("Location: $url");


}
?>

<!doctype html>
<html lang="en">



<head>

    <?php include($IncludesPath . "title-meta.php"); ?>

    <!-- DataTables -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />


    <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css"
        rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css"
        rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css"
        rel="stylesheet" />
    <?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">

    <?php include($IncludesPath . "PreLoader.php"); ?>



    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        //include ($IncludesPath."sidebar.php"); 
        ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php include($IncludesPath . "page-title.php"); ?>
                    <?php include "PopUpTable.php"; ?>


                    <div class="row">
                        <form role="form" method="post" style="width: 100%;">
                            <div class="col-12">

                                <div class="card">

                                    <div class="card-body">

                                        <div class="row">
                                            <div class="form-group col-lg-4">
                                                <label>Invoice No</label>
                                                <input type='text'
                                                    value="<?php echo funPrintValue($resList->InvoiceNo) ?>"
                                                    class="form-control" name="invoiceNo" id="invoiceNo"
                                                    readonly></input>
                                            </div>

                                            <div class="form-group col-lg-4">
                                                <label>PO No</label>
                                                <input type='text' value="<?php echo funPrintValue($resList->PoNo) ?>"
                                                    class="form-control" name="poNo" id="poNo" readonly></input>
                                            </div>

                                            <div class="form-group col-lg-4">
                                                <label>Consignee</label>
                                                <input type='text'
                                                    value="<?php echo funPrintValue($resList->ConsigneeName) ?>"
                                                    class="form-control" name="consignee" id="consignee"
                                                    readonly></input>
                                            </div>

                                            <div class="form-group col-lg-4">
                                                <label>Inv Date</label>
                                                <input type='date'
                                                    value="<?php echo funPrintValue($resList->CInvDate) ?>"
                                                    class="form-control" name="invDate" id="invDate" readonly></input>
                                            </div>

                                            <div class="form-group col-lg-4">
                                                <label>BL No</label>
                                                <input type='text' value="<?php echo funPrintValue($resList->BLNo) ?>"
                                                    class="form-control" name="blNo" id="blNo" readonly></input>
                                            </div>

                                            <div class="form-group col-lg-4">
                                                <label>Container No</label>
                                                <input type='text'
                                                    value="<?php echo funPrintValue($resList->ContainerNo) ?>"
                                                    class="form-control" name="contNo" id="contNo" readonly></input>
                                            </div>


                                        </div>

                                    </div>
                                </div>

                            </div>
                    </div>


                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box" style="padding-bottom: 0px;">
                                <h4>Transactions</h4>
                            </div>
                            <div class="card">
                                <div class="card-body">

                                    <div class="row">
                                        <div class="form-group col-lg-4">
                                            <label>Entry Number</label>
                                            <input type='text'
                                                value="<?php echo funPrintValue($resList->EntryNumber) ?>"
                                                class="form-control" name="entryNumber" id="entryNumber"
                                                placeholder="Enter Entry Number" readonly></input>
                                        </div>

                                        <div class="form-group col-lg-4">
                                            <label>Entry Date</label>
                                            <input type='date' value="<?php echo funPrintValue($resList->EntryDate) ?>"
                                                class="form-control" name="entryDate" id="entryDate" readonly></input>
                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>




                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box" style="padding-bottom: 0px;">
                                <h4>Notify</h4>
                            </div>
                            <div class="card">
                                <div class="card-body">

                                    <div class="row">
                                        <div class="form-group col-lg-4">
                                            <label>Notify Party</label>
                                            <input type='text'
                                                value="<?php echo funPrintValue($resList->NotifyPartyName) ?>"
                                                class="form-control" name="notifyParty" id="notifyParty"
                                                readonly></input>
                                        </div>

                                        <div class="form-group col-lg-4">
                                            <label>Invoice No</label>
                                            <input type='text'
                                                value="<?php echo funPrintValue($resList->ClearingAgentInvoiceNo) ?>"
                                                class="form-control" name="invNo" id="invNo"
                                                placeholder="Enter Invoice Number"></input>
                                        </div>

                                        <div class="form-group col-lg-4">
                                            <label>Invoice Date</label>
                                            <input type='date'
                                                value="<?php echo funPrintValue($resList->ClearingAgentInvoiceDate) ?>"
                                                class="form-control" name="invdate" id="invdate"></input>
                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>



                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box" style="padding-bottom: 0px;">
                                <h4>Duty</h4>
                            </div>
                            <div class="card">
                                <div class="card-body">

                                    <div class="row">
                                        <div class="form-group col-lg-4">
                                            <label>C&F Charges</label>
                                            <input type='text'
                                                value="<?php echo funPrintValue($resList->CandFCharges) ?>"
                                                class="form-control" name="cAndFCharges" id="cAndFCharges"
                                                placeholder="Enter C&F Charges"></input>
                                        </div>

                                        <div class="form-group col-lg-4">
                                            <label>AD Duty</label>
                                            <input type='text'
                                                value="<?php echo funPrintValue($resList->AntiDumpingCharges) ?>"
                                                class="form-control" name="adDuty" id="adDuty"></input>
                                        </div>

                                        <div class="form-group col-lg-4">
                                            <label>HM Duty</label>
                                            <input type='text'
                                                value="<?php echo funPrintValue($resList->HarbourMaintenanceFee) ?>"
                                                class="form-control" name="hmDuty" id="hmDuty"></input>
                                        </div>

                                        <div class="form-group col-lg-4">
                                            <label>MP Duty</label>
                                            <input type='text'
                                                value="<?php echo funPrintValue($resList->MerchandiseProcessFee) ?>"
                                                class="form-control" name="mpDuty" id="mpDuty"></input>
                                        </div>

                                        <div class="form-group col-lg-4">
                                            <label>CV Duty</label>
                                            <input type='text'
                                                value="<?php echo funPrintValue($resList->CountervailingDutyFee) ?>"
                                                class="form-control" name="cvDuty" id="cvDuty"></input>
                                        </div>

                                        <div class="form-group col-lg-4">
                                            <label>RT Duty</label>
                                            <input type='text'
                                                value="<?php echo funPrintValue($resList->ReciprocalTariffsFee) ?>"
                                                class="form-control" name="rtDuty" id="rtDuty"></input>
                                        </div>

                                        <div class="form-group col-lg-4">
                                            <label>Invoice Value</label>
                                            <input type='text'
                                                value="<?php echo funPrintValue($resList->InvValueUSD) ?>"
                                                class="form-control" name="invValue" id="invValue" readonly></input>
                                        </div>

                                        <div class="form-group col-lg-4">
                                            <label>Entered Invoice Value</label>
                                            <input type='text'
                                                value="<?php echo funPrintValue($resList->EnteredInvValue) ?>"
                                                class="form-control" name="enteredInvValue" id="enteredInvValue"></input>
                                        </div>

                                        <div class="form-group col-lg-4">
                                            <label>Total</label>
                                            <input type='text'
                                                value="<?php echo funPrintValue($resList->TotalCharges) ?>"
                                                class="form-control" name="total" id="total" readonly></input>
                                        </div>

                                        <div class="form-group col-lg-4">
                                            <label>Grand Total</label>
                                            <input type='text'
                                                value="<?php echo funPrintValue($resList->GrandTotalCharges) ?>"
                                                class="form-control" name="total" id="total" readonly></input>
                                        </div>


                                        <div class="col-lg-12 text-center">
                                            <a href="clearing-agent" class="btn btn-danger"> Close </a>
                                           <?php $isDisabled = ($status == 1) ? 'disabled' : ''; ?>

                                            <button id="list" type="submit" name="submit"
                                                class="btn btn-primary waves-effect waves-light"
                                                <?= $isDisabled ?>>
                                                Save <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>

                                            <button id="openModal" type="button"
                                                class="btn btn-success waves-effect waves-light">
                                                More Transaction <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                    </form>


                    <!-- end col -->
                </div> <!-- end row -->







            </div> <!-- end card body-->
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


    <!-- The Modal -->
    <div class="modal" id="agentModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">More Transaction</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form role="form" method="post" id="frmModalMoreTranctions" style="width: 100%;">
                        <div class="row">

                            <div class="col-lg-3">
                                <label>Select Cat: </label>
                                <select class="form-control select2" name="drpSelecCat" id="drpSelecCat">
                                    <option value="">Select</option>
                                    <?php while ($RowTransaction = $StmtClearingAgentTransaction->fetch_array()) { ?>
                                        <option value="<?php echo $RowTransaction['code']; ?>">
                                            <?php echo $RowTransaction['name']; ?>
                                        </option>
                                    <?php } ?>


                                    <!-- <option value="CartageCharges">Cartage Charges</option>
                                    <option value="DemurrageCharges">Demurrage Charges</option>
                                    <option value="OverSeasClaimCharges">OverSeas Claim Charges</option>
                                    <option value="OverSeasLABCharges">OverSeas LAB Charges</option>
                                    <option value="OverSeasRepackingCharges">OverSeas Repacking Charges</option>
                                    <option value="Others">Others</option> -->
                                    <select>
                            </div>

                            <div class="form-group col-lg-3">
                                <label>Bill Number</label>
                                <input type='text' value='' class="form-control" name="billNumber"
                                    id="billNumber"></input>
                            </div>

                            <div class="form-group col-lg-3">
                                <label>Bill Date</label>
                                <input type='date' value='' class="form-control" name="billDate" id="billDate"></input>
                            </div>

                            <div class="form-group col-lg-3">
                                <label>Bill Amount</label>
                                <input type='text' value='' class="form-control" name="billAmount"
                                    id="billAmount"></input>
                            </div>
                            <div class="col-lg-12 text-center">
                                <?php $isDisabled = ($status == 1) ? 'disabled' : ''; ?>
                                <button id="btnsavecaCharges" type="submit" name="submit"
                                    class="btn btn-primary waves-effect waves-light" data-PoId="<?php echo $ID; ?>" <?= $isDisabled ?>>
                                    Save <i class="ri-arrow-right-line align-middle ml-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="mt-3" id="POList">
                        <!-- <table class="table  wrap w-100">
                            <thead>
                            <tr>
                                <th><b>Category</b></th>
                                <th>Bill Number</th>
                                <th>Bill Date</th>
                                <th>Bill Amount</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Category</td>
                                    <td>Bill Number</td>
                                    <td>Bill Date</td>
                                    <td>Bill Amount</td>
                                </tr>
                                <tr>
                                    <td>Category</td>
                                    <td>Bill Number</td>
                                    <td>Bill Date</td>
                                    <td>Bill Amount</td>
                                </tr>
                                <tr>
                                    <td>Category</td>
                                    <td>Bill Number</td>
                                    <td>Bill Date</td>
                                    <td>Bill Amount</td>
                                </tr>

                            </tbody>

                            <table> -->
                    </div>
                </div>

            </div>
        </div>
    </div>


    <?php include($IncludesPath . "vendor-scripts.php"); ?>


    <!-- Required datatable js -->
    <script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>

    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/js/pages/form-advanced.init.js"></script>


    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <!-- Buttons examples -->
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/jszip/jszip.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/pdfmake.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/vfs_fonts.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-select/js/dataTables.select.min.js"></script>

    <!-- Responsive examples -->
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script
        src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

    <!-- Datatable init js -->
    <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

    <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>

    <script>

        //frmModalMoreTranctions


        function fnShowBreakUps() {
            $.ajax({
                // url:'Ajax/AjaxFUUtilizedNPendngRprt',
                url: 'Ajax/AjaxGetClearingAgentBreakupList',
                type: 'POST',
                data: {
                    'PoID': '<?php echo $ID ?>',
                    'Status': '<?php echo $Status ?>'
                },
                success: function (data) {
                    //showData(data);
                    var columns = [{ title: '' }];
                    let jsonData = JSON.parse(data);
                    $("#POList").html(jsonData.html);

                    if (jsonData.json) {
                        $('#billNumber').val(jsonData.json[0].BillNumber);
                        $('#billDate').val(jsonData.json[0].BillDate);

                    } else {
                        $('#billNumber').val('');
                        $('#billDate').val('');
                    }
                    var table = $('#key-datatable').DataTable({
                        scrollY: "600px",
                        scrollX: true,
                        //sScrollX: "100%",
                        sScrollXInner: "150%",
                        scrollCollapse: true,
                        paging: true,
                        destroy: true,
                        fixedColumns: true,
                        bSort: true,
                        order: [
                            [1, 'desc']
                        ],
                        "bStateSave": true,
                        // dom: 'Blfrtip',
                        // buttons: [{extend: 'excel', text: 'Download Excel', className: 'btnExcelDataTable',title: 'MPEDA Report', footer: true,
                        // exportOptions: {
                        // columns: ':visible',
                        // }
                        // }], 
                        "fnStateSave": function (oSettings, oData) {
                            localStorage.setItem('key-datatable', JSON.stringify(oData));
                        },
                        "fnStateLoad": function (oSettings) {
                            return JSON.parse(localStorage.getItem('key-datatable'));
                        }
                    });
                }
            });
        }

        $("#openModal").click(function () {

            $('#agentModal').modal('show');
            fnShowBreakUps();
        });



        jQuery.validator.addMethod('selectcheck', function (value) {
            return (value != '0' || value != '');
        }, "year required");

        jQuery.validator.addMethod("noSpace", function (value, element) {
            return value == '' || value.trim().length != 0;
        }, "No space please and don't leave it empty");

        $(document).ready(function () {
            $("#frmModalMoreTranctions").validate({
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
                    drpSelecCat: { required: true, noSpace: true },
                    billNumber: { required: true, noSpace: true },
                    billDate: { required: true, noSpace: true },
                    billAmount: { required: true, noSpace: true, number: true },

                },
                errorPlacement: function () {
                    return false; // suppresses error message text
                },
                submitHandler: function () {
                    // return false;
                    var userID = "<?php echo $uid ?>";
                    var id = $("#btnsavecaCharges").attr("data-PoId");
                    var drpSelecCat = $("#drpSelecCat").val();
                    var billNumber = $("#billNumber").val();
                    var billDate = $("#billDate").val();
                    var billAmount = $("#billAmount").val();

                    //alert(billAmount); exit;
                    $.ajax({
                        url: 'Ajax/AjaxClearingAgentChargesBreakUp.php',
                        type: 'POST',
                        data: {
                            "PoID": id,
                            "TypeofCharges": drpSelecCat,
                            "BillNumber": billNumber,
                            "BillDate": billDate,
                            "BillAmount": billAmount,
                            "PostedUserId": userID,
                            "SaveCharges": "T"
                        },
                        success: function (data) {

                            if (data.trim() == "success") {
                                fnShowBreakUps();
                                alert("Data saved successfully.");
                                $("#drpSelecCa, #billNumber, #billDate, #billAmount").val("");
                                //fnShowPageDate();
                                // $('#agentModal').modal('hide');
                            } else {
                                alert("Some error occurred.");
                                //uma//

                                // alert("Data saved successfully.");
                                // $("#drpSelecCa, #billNumber, #billDate, #billAmount").val(""); 

                                // fnShowBreakUps();
                                //uma//
                            }
                        }
                    });

                    return false;
                }
            });
        });


        $(document).on("click", ".btnDelete", function () {
            var breakUpID = $(this).attr("data-id");
            if (confirm('Are you sure you want to delete?')) {
                $.ajax({
                    url: 'Ajax/AjaxClearingMoreTrans.php',
                    type: 'POST',
                    data: {
                        DeleteClearingMoreTrans: "T",
                        BreakUpID: breakUpID
                    },
                    success: function (data) {

                        console.log(data);
                        if (data.Error) {
                            alert(data.Error);
                        } else {
                            alert(data.Msg);
                            // $('#agentModal').modal('hide');
                            // $('#agentModal').modal('show');
                            fnShowBreakUps();

                            // var pageno = $(".active.btnPageno").text();
                            // ShowList(pageno, "", "0");
                        }
                    }
                });
            }
        })


        // $(document).on("click", "#btnsavecaCharges", function() { 
        // }); 

    </script>

</body>

</html>