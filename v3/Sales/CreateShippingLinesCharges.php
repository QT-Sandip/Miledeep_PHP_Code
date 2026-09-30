<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);



//include ("RestAPI.php");

$PageTitle = "Shipping Line Chharges Form";
$PageTitleFolder = "Payments";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/api-call.php';
include($cp . "Includes/IncludePage.php");


$uid = $_SESSION['r_id'];


$uid = $_SESSION['UserID'];
$ID = isset($_GET['id']) ? $_GET['id'] : "";

if ($ID != "") {
    $apiGet = API_SHIPPING_LINES_CHARGES;
    $bodyContent = array(
        "PoID" => $ID,
        'GetType' => 'LoadShippingLinerDetails'
    );
    $resGetClearingAgentFormDataAPI = CallAPI($apiGet, "GET", "", $bodyContent);

    $resList = $resGetClearingAgentFormDataAPI->Data->ShippingLinerFormData;

    $linerChargeTransactions = $resGetClearingAgentFormDataAPI->Data->LinerChargeTransactions;
    // echo json_encode($resGetClearingAgentFormDataAPI, true);
}

$SqlShippingLinesChargesCategoryTransaction = "SELECT * FROM `ShippingLinesChargesCategory` ORDER by sort ASC";
$StmtShippingLinesChargesCategoryTransaction = $iconn->query($SqlShippingLinesChargesCategoryTransaction);


function funPrintValue($key)
{
    return (isset($key) && $key != "") ? $key : "";
}


extract($_POST);
extract($_GET);


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
                        <div class="col-12">

                            <div class="card">

                                <div class="card-body">

                                    <div class="row">
                                        <div class="form-group col-lg-4">
                                            <label>Invoice No</label>
                                            <input type='text' value="<?php echo funPrintValue($resList->InvoiceNo) ?>"
                                                class="form-control" name="invoiceNo" id="invoiceNo" readonly></input>
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
                                                class="form-control" name="consignee" id="consignee" readonly></input>
                                        </div>

                                        <div class="form-group col-lg-4">
                                            <label>Inv Date</label>
                                            <input type='date' value="<?php echo funPrintValue($resList->CInvDate) ?>"
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

                                        <div class="form-group col-lg-3">
                                            <label>Bill Number</label>
                                            <input type="text" name="billNumber[]" class="form-control"
                                                value="<?php echo $resList->BillNumber; ?>" placeholder="Bill Number"
                                                required>
                                        </div>

                                        <div class="form-group col-lg-3">
                                            <label>Bill Date</label>
                                            <input type="date" name="billDate[]" class="form-control"
                                                value="<?php echo $resList->BillDate; ?>" required>
                                        </div>
                                        <!-- Amount in IGST -->
                                        <div class="form-group col-lg-3">
                                            <label>CGST</label>
                                            <input type="number" name="cgst[]" class="form-control"
                                                value="<?php echo $resList->cgst; ?>" placeholder="CGST" required>
                                        </div>
                                        <!-- Amount in IGST -->
                                        <div class="form-group col-lg-3">
                                            <label>IGST</label>
                                            <input type="number" name="igst[]" class="form-control"
                                                value="<?php echo $resList->igst; ?>" placeholder="IGST" required>
                                        </div>

                                        <!-- Amount in SGST -->
                                        <div class="form-group col-lg-3">
                                            <label>SGST</label>
                                            <input type="number" name="sgst[]" class="form-control"
                                                value="<?php echo $resList->sgst; ?>" placeholder="SGST" required>
                                        </div>

                                        <!-- Amount in SGST -->
                                        <div class="form-group col-lg-3">
                                            <label>Grand Total</label>
                                            <input type="number" name="grandTotal[]" class="form-control"
                                                value="<?php echo $resList->grand_total; ?>" placeholder="Grand Total"
                                                disabled readonly>
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
                                    <form id="frmModalMoreTranctions" method="post">
                                        <div id="transactionList">
                                            <div id="transactionList">
                                                <!-- Header Row -->
                                                <div class="row font-weight-bold text-dark mb-2">
                                                    <div class="col-lg-5">Select Cat</div>
                                                    <!-- <div class="col-lg-2">Bill Number</div> -->
                                                    <!-- <div class="col-lg-2">Bill Date</div> -->
                                                    <div class="col-lg-2">Amount in USD</div>
                                                    <div class="col-lg-2">Ex. Rate</div>
                                                    <div class="col-lg-2">Amount in INR</div>
                                                    <!-- <div class="col-lg-1">IGST</div> -->
                                                    <!-- <div class="col-lg-1">SGST</div> -->
                                                    <div class="col-lg-1">Action</div>
                                                </div>

                                            </div>

                                            <?php
                                            if (count($linerChargeTransactions) == 0) {
                                                $linerChargeTransactions[] = [];
                                            }

                                            foreach ($linerChargeTransactions as $item) {
                                                if (is_array($item)) {
                                                    $item = (object) $item; // Ensure consistent object access
                                                }
                                                ?>
                                                <div class="row transaction-item mb-2">
                                                    <input type="hidden" name="id[]" value="<?php echo $item->id; ?>">

                                                    <!-- Category Dropdown -->
                                                    <div class="col-lg-5">
                                                        <select class="form-control drpSelect" name="drpSelecCat[]"
                                                            required>
                                                            <option value="">Select</option>
                                                            <?php
                                                            $StmtShippingLinesChargesCategoryTransaction->data_seek(0); // Reset pointer
                                                            while ($RowTransaction = $StmtShippingLinesChargesCategoryTransaction->fetch_array()) {
                                                                $selected = ($item->category_code == $RowTransaction['id']) ? 'selected' : '';
                                                                ?>
                                                                <option value="<?php echo $RowTransaction['id']; ?>" <?php echo $selected; ?>>
                                                                    <?php echo $RowTransaction['name']; ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>

                                                    <!-- Bill Number -->
                                                    <!-- <div class="col-lg-2">
                                                        <input type="text" name="billNumber[]" class="form-control"
                                                            value="<?php echo $item->bill_number; ?>"
                                                            placeholder="Bill Number" required>
                                                    </div> -->

                                                    <!-- Bill Date -->
                                                    <!-- <div class="col-lg-2">
                                                        <input type="date" name="billDate[]" class="form-control"
                                                            value="<?php echo $item->bill_date; ?>" required>
                                                    </div> -->

                                                    <!-- Amount in USD -->
                                                    <div class="col-lg-2">
                                                        <input type="number" name="billAmountInUSD[]" class="form-control"
                                                            step="0.01" min="0" value="<?php echo $item->amount_in_usd; ?>"
                                                            placeholder="USD">
                                                    </div>

                                                    <!-- Exchange Rate -->
                                                    <div class="col-lg-2">
                                                        <input type="number" name="exRate[]" class="form-control"
                                                            step="0.01" min="0" value="<?php echo $item->exchange_rate; ?>"
                                                            placeholder="Ex. Rate">
                                                    </div>

                                                    <!-- Amount in INR -->
                                                    <div class="col-lg-2">
                                                        <input type="number" name="billAmountInINR[]" class="form-control"
                                                            step="0.01" min="0" value="<?php echo $item->amount_in_inr; ?>"
                                                            placeholder="Amount in INR">
                                                    </div>



                                                    <!-- Amount in IGST -->
                                                    <!-- <div class="col-lg-1">
                                                        <input type="number" name="igst[]" class="form-control"
                                                            value="<?php echo $item->igst; ?>" placeholder="IGST" required>
                                                    </div> -->

                                                    <!-- Amount in SGST -->
                                                    <!-- <div class="col-lg-1">
                                                        <input type="number" name="sgst[]" class="form-control"
                                                            value="<?php echo $item->sgst; ?>" placeholder="SGST" required>
                                                    </div> -->

                                                    <!-- Remove Button -->
                                                    <div class="col-lg-1">
                                                        <button type="button"
                                                            class="btn btn-link text-danger btn-sm removeRow"><i
                                                                class="mdi mdi-trash-can font-size-16"></i></button>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>

                                        <div class="text-right mt-2">
                                            <button type="button" class="btn btn-primary AddNewItem">Add Item</button>
                                            <button type="submit" class="btn btn-success">Submit</button>
                                            <a href="ShippingLinesCharges" class="btn btn-danger"> Close </a>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
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
        $(document).ready(function () {

            $(document).on('change keyup', 'input', function () {
                calculateGrandTotal();
            });



            // Add new row
            $('.AddNewItem').click(function () {
                const newRow = $('.transaction-item:first').clone();

                // Reset inputs
                newRow.find('input').val('');
                newRow.find('select').val('').trigger('change');

                $('#transactionList').append(newRow);

                // Destroy and re-initialize select2 to avoid broken dropdowns
                newRow.find('select').select2('destroy');
                newRow.find('select').select2();
            });

            // Remove a row
            $('#transactionList').on('click', '.removeRow', function () {
                const $row = $(this).closest('.transaction-item');
                const id = $row.find('input[name="id[]"]').val();

                if (!id) {
                    // Not yet saved to DB, just remove it from UI
                    $row.remove();
                    return;
                }

                if (!confirm("Are you sure you want to delete this item?")) {
                    return;
                }

                $.ajax({
                    url: 'Ajax/AjaxShippingLinesCharges.php',
                    type: 'POST',
                    data: {
                        methodType: 'DELETE_ROW',
                        id: id
                    },
                    success: function (res) {
                        if (res.trim() === 'success') {
                            alert("Deleted successfully");
                            $row.remove(); // remove from UI
                            calculateGrandTotal();
                        } else {
                            alert("Delete failed: " + res);
                        }
                    },
                    error: function (xhr) {
                        alert("AJAX error: " + xhr.responseText);
                    }
                });
            });

            // Form submission
            $('#frmModalMoreTranctions').on('submit', function (e) {
                e.preventDefault();
                let valid = true;
                let errorMessage = '';
                let rowIndex = 1;
                calculateGrandTotal();
                $('.transaction-item').each(function () {
                    const cat = $(this).find('select').val();
                    const exRate = $(this).find('input[name="exRate[]"]').val().trim();
                    const inr = $(this).find('input[name="billAmountInINR[]"]').val().trim();
                    const usd = $(this).find('input[name="billAmountInUSD[]"]').val().trim();

                    if (!cat) {
                        valid = false;
                        errorMessage = `Please fill all fields correctly in row ${rowIndex}`;
                        return false; // break .each
                    }

                    if (usd > 0 && (isNaN(exRate) || exRate <= 0)) {
                        valid = false;
                        errorMessage = `Please fill Ex. Rate correctly in row ${rowIndex}`;
                        return false; // break .each
                    }

                    rowIndex++;
                });

                const billNumber = $('input[name="billNumber[]"]').val()?.trim();
                const billDate = $('input[name="billDate[]"]').val();
                const cgst = $('input[name="cgst[]"]').val()?.trim();
                const igst = $('input[name="igst[]"]').val()?.trim();
                const sgst = $('input[name="sgst[]"]').val()?.trim();
                const grandTotal = $('input[name="grandTotal[]"]').val()?.trim();


                if (!billNumber || !billDate || isNaN(igst) || igst < 0 || isNaN(sgst) || sgst < 0 || isNaN(cgst) || cgst < 0) {
                    valid = false;
                    errorMessage = "Please check the Bill Number, Bill Date, CGST, IGST, and SGST fields.";
                }

                if (!valid) {

                    alert(errorMessage);
                    return false;
                }



                // Submit to your actual API endpoint
                const formData = $(this).serialize() +
                    '&methodType=formSubmit' +
                    '&po_id=' + encodeURIComponent('<?php echo $ID ?>') +
                    '&billNumber=' + encodeURIComponent(billNumber) +
                    '&billDate=' + encodeURIComponent(billDate) +
                    '&cgst=' + encodeURIComponent(cgst) +
                    '&igst=' + encodeURIComponent(igst) +
                    '&sgst=' + encodeURIComponent(sgst) +
                    '&grandTotal=' + encodeURIComponent(grandTotal);

                $.ajax({
                    url: 'Ajax/AjaxShippingLinesCharges',
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        alert("Data submitted successfully.");
                        location.reload();
                        // Optionally refresh or redirect
                    },
                    error: function (xhr) {
                        alert("Error: " + xhr.responseText);
                    }
                });
            });

            function calculateGrandTotal() {
                let grandTotal = 0;
                $('.transaction-item').each(function () {
                    const cat = $(this).find('select').val();
                    let exRate = parseFloat($(this).find('input[name="exRate[]"]').val().trim() || 0.00);
                    let inr = parseFloat($(this).find('input[name="billAmountInINR[]"]').val().trim() || 0.00);
                    let usd = parseFloat($(this).find('input[name="billAmountInUSD[]"]').val().trim() || 0.00);


                    if (usd > 0 && (isNaN(exRate) || exRate <= 0)) {
                        exRate = 1; // break .each
                    }
                    if (usd > 0) {
                        inr = parseFloat(usd * exRate);
                    }
                    $(this).find('input[name="billAmountInINR[]"]').val(inr);

                    grandTotal += parseFloat(inr);


                });

                const billNumber = $('input[name="billNumber[]"]').val()?.trim();
                const billDate = $('input[name="billDate[]"]').val();
                const cgst = parseFloat($('input[name="cgst[]"]').val()?.trim() || 0.00);
                const igst = parseFloat($('input[name="igst[]"]').val()?.trim() || 0.00);
                const sgst = parseFloat($('input[name="sgst[]"]').val()?.trim() || 0.00);

                if (!billNumber || !billDate || isNaN(igst) || igst < 0 || isNaN(sgst) || sgst < 0 || isNaN(cgst) || cgst < 0) {
                    valid = false;
                    errorMessage = "Please check the Bill Number, Bill Date,CGST, IGST, and SGST fields.";
                }

                grandTotal += parseFloat(cgst) + parseFloat(igst) + parseFloat(sgst);
                $('input[name="grandTotal[]"]').val(Math.round((grandTotal + Number.EPSILON) * 100) / 100);
            }

        });
    </script>

</body>

</html>