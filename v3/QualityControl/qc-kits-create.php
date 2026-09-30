<?php
session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_QC_KITS;

$KitID = $_GET["KitID"];
$DeleteAccess = $_GET['DeleteAccess'];

$ClientConfigData = $_SESSION['ClientConfigData'];
// $DefaultNoOfWells = $ClientConfigData->DefaultNoOfWells;  // uncomment this line after auth-login.php converted to API modal
$DefaultNoOfWells = $ClientConfigData['DefaultNoOfWells']; // remove this line after auth-login.php converted to API modal


$bodyContent = array(
    "KitID" => $KitID,
    "DeleteAccess"=>$DeleteAccess
);
if ($KitID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);
      $Data = $result->Data[0];
}

$api = API_QC_DROPDOWNS;
$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);
$ProductionPlant = $_SESSION['UnitID'];
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
<div KitID="layout-wrapper">

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
                                            <label for="drpParameter">Parameter<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpParameter" id="drpParameter">
                                                <option value="">Select</option>
                                                <option <?php if ($Data->Parameter == 'AOZ') { ?> selected="selected" <?php } ?>
                                                value="AOZ">AOZ</option>
                                                <option <?php if ($Data->Parameter == 'SEM') { ?> selected="selected" <?php } ?>
                                                value="SEM">SEM</option>
                                                <option <?php if ($Data->Parameter == 'CAP') { ?> selected="selected" <?php } ?>value="CAP">CAP</option>
                                                <option <?php if ($Data->Parameter == 'AHD') { ?> selected="selected" <?php } ?>value="AHD">AHD</option>
                                                <option <?php if ($Data->Parameter == 'AMOZ') { ?> selected="selected" <?php } ?>value="AMOZ">AMOZ</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtKitsOrderedDate" class="form-label">Kits Ordered Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtKitsOrderedDate"
                                                name="txtKitsOrderedDate"
                                                value='<?php echo $Data->KitsOrderedDate ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtReceivedDate" class="form-label">Kits Received Date</label>
                                            <input type="date" class="form-control" id="txtReceivedDate"
                                                name="txtReceivedDate" value='<?php echo $Data->KitsReceivedDate ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtKitsLotNo" class="form-label">Kits Lot No <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtKitsLotNo"
                                                name="txtKitsLotNo" placeholder="Enter Kits Lot No"
                                                value='<?php echo $Data->KitsLotNo ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtOrderedKits" class="form-label">Ordered Kits <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtOrderedKits"
                                                name="txtOrderedKits" placeholder="Enter Orderd Kits"
                                                value='<?php echo $Data->NoOfKitsOrdered ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtReceivedKits" class="form-label">Received Kits </label>
                                            <input type="text" class="form-control" id="txtReceivedKits"
                                                name="txtReceivedKits" placeholder="Enter Received Kits"
                                                value='<?php echo $Data->NoOfKitsReceived ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInvoiceNo" class="form-label">Invoice No <span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtInvoiceNo"
                                                name="txtInvoiceNo" placeholder="Enter Invoice No"
                                                value='<?php echo $Data->InvoiceNo ?>'>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="manufactureDate" class="form-label">Manufacture Date</label>
                                            <input type="Date" class="form-control" id="manufactureDate"
                                                name="manufactureDate" value='<?php echo $Data->ManufactureDate ?>'>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="expiryDate" class="form-label">Expiry Date </label>
                                            <input type="Date" class="form-control" id="expiryDate" name="expiryDate"
                                                value='<?php echo $Data->ExpiryDate ?>'>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtTotalWells" class="form-label">Total Wells</label>
                                            <input type="text" class="form-control" id="txtTotalWells"
                                                name="txtTotalWells" placeholder="Enter Total Wells"
                                                value='<?php echo $Data->TotalWells ?>' readonly>
                                        </div>
                                        <div class="text-center">
                                            <input type="hidden" id="hdnKitID" name="hdnKitID"
                                                value="<?php echo $KitID ?>" />
                                            <a href="qc-kits-list" class="btn btn-danger">Close</a>
                                            <?php if ($KitID == '') { ?>
                                                <button type="submit" class="btn btn-primary btnSave" name="submit"
                                                    id="submit">Submit</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit"
                                                    id="submit">Update</button>
                                            <?php } ?>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>
    <script>
        var id = "<?php echo isset($_GET["KitID"]) && !empty($_GET["KitID"]) ? $_GET["KitID"] : "" ?>";
        var id = "<?php echo isset($_GET["DeleteAccess"]) && !empty($_GET["DeleteAccess"]) ? $_GET["DeleteAccess"] : "" ?>";

        function fnShowDataTable() {
            var table = $('#tblDataTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                bInfo: false,
                bsort: true,
                dom: 'Blfrtip',
                // dom: '<"top-left"Blfrtip>',
                buttons: [{
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable btn-left-align',
                    title: 'Qc Kits List <?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }


        $("#frmCountry").validate({
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
                txtKitsOrderedDate: {
                    required: true,
                },
                txtKitsLotNo: {
                    required: true,
                },
                txtOrderedKits: {
                    required: true,
                },
                txtInvoiceNo: {
                    required: true,
                },
               
                drpParameter: {
                    required: true,
                }

            },
            errorPlacement: function () {
                return false;
            },
            submitHandler: function () {
                var orderedKits = $("#txtOrderedKits").val();
                var receivedKits = $("#txtReceivedKits").val();

        // Check if OrderedKits and ReceivedKits are greater than 0
        if (orderedKits <= 0) {
            alert("Ordered Kits must be greater than 0");
            return false;  // Prevent form submission
        }

        if (receivedKits > 0) {
            // Set KitsLotNo readonly only after ReceivedKits is entered and form is submitted
            $("#txtKitsLotNo").prop('readonly', true);
           
        }
                $.ajax({
                    url: 'Ajax/ajax-qc-kits-create.php',
                    type: 'POST',
                    data: {
                        methodType: "postFormData",
                        kitID: $("#hdnKitID").val(),
                        DeleteAccess: $("#btnSave").val(),
                        kitsOrderedDate: $("#txtKitsOrderedDate").val(),
                        receivedDate: $("#txtReceivedDate").val(),
                        kitsLotNo: $("#txtKitsLotNo").val(),
                        orderedKits: $("#txtOrderedKits").val(),
                        receivedKits: $("#txtReceivedKits").val(),
                        invoiceNo: $("#txtInvoiceNo").val(),
                        manufactureDate: $("#manufactureDate").val(),
                        expiryDate: $("#expiryDate").val(),
                        totalWells: $("#txtTotalWells").val(),
                        parameter: $("#drpParameter").val(),
                    },
                    success: function (data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                           // window.location.href = "qc-kits-create?KitID=" + data.KitID;
                            window.location.href = "qc-kits-list.php" ;
                        } else {
                            alert(data.Error);
                        }
                        return false;
                    }
                });
                return false;
            }
            
        });
document.getElementById('txtReceivedKits').addEventListener('input', function () {
		const receivedkits = parseFloat(document.getElementById('txtReceivedKits').value) || 0;
        var DefaultNoOfWells=<?php echo $DefaultNoOfWells?>
		
// var totalwells=receivedkits*96;
var totalwells=receivedkits*DefaultNoOfWells;

			$("#txtTotalWells").val(totalwells);
	});     
    function handleReceivedKitsReadonly(receivedKits = null) {
        const currentReceivedKits = receivedKits !== null ? receivedKits : parseFloat($("#txtReceivedKits").val()) || 0;
        if (currentReceivedKits > 0) {
            $("#txtReceivedKits").prop('readonly', true);
        } else {
            $("#txtReceivedKits").prop('readonly', false);
        }
    }
    
       $(document).ready(function () {
        handleReceivedKitsReadonly();
            var kitID = $("#hdnKitID").val();
           // var DeleteAccess = $("#btnSave").val();
            

            console.log('kitID ' + kitID);
            if (kitID != 0) {
                getBreakUpData();
            }
  

        //     var deleteAccess = <?php echo $DeleteAccess; ?>; // Ensure proper encoding for JSON
        // if (deleteAccess ==1) {
        //     $(".btnSave").prop('disabled', true);
        // } else {
        //     $(".btnSave").prop('disabled', false);
        // }

        var deleteAccess = "<?php echo isset($DeleteAccess) ? $DeleteAccess : '0'; ?>"; // Default to '0' if not set

// Debugging output for deleteAccess
console.log('deleteAccess: ' + deleteAccess);

// Enable or disable the Save button based on deleteAccess
if (parseInt(deleteAccess) === 1) {
    $(".btnSave").prop('disabled', true);
} else {
    $(".btnSave").prop('disabled', false);
}
            
        });

        

        function getBreakUpData() {
            $.ajax({
                url: 'Ajax/ajax-qc-kits-create.php',
                type: 'POST',
                data: {
                    methodType: "getBreakUpData",
                    kitID: $("#hdnKitID").val(),
                    DeleteAccess: $("#btnSave").val(),
                },
                success: function (data) {
                    $("#divDataList").html(data);
                    //console.log(data);
                    fnShowDataTable();
                    

                    $("#txtKitsLotNo").prop('readonly', true);
                    $("#txtOrderedKits").prop('readonly', true);
                    $("#txtTotalWells").prop('readonly', true);

                    handleReceivedKitsReadonly(receivedKits);
                    //     if (data.Error != '') {
                    //             alert(data.Msg);
                    //             window.location.href = "qc-kits-create?KitID=" + data.kitID;
                    //         } else {
                    //             alert(data.Error);
                    //         }
                    //         return false;
                }
            });
            return false;
        }
    </script>
    </body>

</html>