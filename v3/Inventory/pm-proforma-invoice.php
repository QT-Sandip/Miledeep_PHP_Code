<?php
$Status = $_SESSION['Status'];
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_INVENTORY_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resGeneralItems = $resDropdownsAPI->Data->GeneralItems;
$resVendors = $resDropdownsAPI->Data->Vendors;
$resDepartments = $resDropdownsAPI->Data->Departments;
$resVariety = $resDropdownsAPI->Data->Variety;
$resPackingStyle = $resDropdownsAPI->Data->PackingStyle;
$resGrade = $resDropdownsAPI->Data->Grade;
$resFreezingType = $resDropdownsAPI->Data->FreezingType;
$resBrand = $resDropdownsAPI->Data->Brand;

$User = "Proforma Invoice";
$PageTitle = $User . "";
$PageTitleFolder = "Proforma";

$SqlConsignee = "SELECT * FROM ScrmConsigneeDetails WHERE Status=1";
$StmtConsignee = $iconn->query($SqlConsignee);

$SqlBuyer = "SELECT * FROM ScrmBuyerDetails WHERE Status=1";
$StmtBuyer = $iconn->query($SqlBuyer);

$SqlNotifyparty = "SELECT * FROM `ScrmNotifyparty` WHERE Status=1";
$StmtNotifyparty = $iconn->query($SqlNotifyparty);

$SqlUltimateConsignee = "SELECT * FROM ScrmUltimateConsigneeDetails WHERE Status=1";
$StmtUltimateConsignee = $iconn->query($SqlUltimateConsignee);

$SqlBuyer = "SELECT * FROM ScrmBuyerDetails WHERE Status=1";
$StmtBuyer = $iconn->query($SqlBuyer);

$SqlPorts1 = "SELECT * FROM ScrmPortCities WHERE IsPortofLoading=1";
$StmtPorts = $iconn->query($SqlPorts1);

$SqlCurrency = "SELECT * FROM `ScrmCurrencyMaster` WHERE `Status` = '1'";
$StmtCurrency = $iconn->query($SqlCurrency);

$SqlCountryList = "SELECT * FROM ScrmCountriesList";
$StmtCountryList = $iconn->query($SqlCountryList);

$SqlPaymentTerms = "SELECT * FROM ScrmPaymentTerms  WHERE `Status` = '1'";
$StmtPaymentTerms = $iconn->query($SqlPaymentTerms);


$jsonData = '{
    "1": [{"date":"10/10/2025","qty":10,"price":"35.00"},{"date":"10/09/2025","qty":15,"price":"33.00"}],
    "2": [{"date":"8/10/2025","qty":10,"price":"99.00"},{"date":"8/09/2025","qty":15,"price":"80.00"}],
    "3": [{"date":"5/10/2025","qty":10,"price":"20.00"},{"date":"5/09/2025","qty":15,"price":"18.00"}]
}';

$previousData = json_decode($jsonData, true);

?>

<!DOCTYPE html>
<html lang="en">
<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
    <?php include_once ROOT . 'includes/head.php' ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    <style>
        .row {
            margin-bottom: 10px;
        }

        #itemTable {
            table-layout: fixed;
            width: 100%;
        }

        #itemTable th,
        #itemTable td {
            padding: 5px 8px;
            /* reduce cell padding */
            font-size: 14px;
            /* slightly smaller font */
            vertical-align: middle;
        }



        /* Adjust column widths */
        #itemTable th:nth-child(1),
        #itemTable td:nth-child(1) {
            width: 9%;
            /* Item column wider */
        }

        #itemTable th:nth-child(2),
        #itemTable td:nth-child(2) {
            width: 4%;
            /* UoM */
        }

        #itemTable th:nth-child(3),
        #itemTable td:nth-child(3) {
            width: 4%;
            /* Available Qty */
        }

        #itemTable th:nth-child(4),
        #itemTable td:nth-child(4) {
            width: 4%;
            /* Item Price */
        }

        #itemTable th:nth-child(5),
        #itemTable td:nth-child(5) {
            width: 5%;
            /* Requested Qty */
        }

        #itemTable th:nth-child(6),
        #itemTable td:nth-child(6) {
            width: 5%;
            /* Base Value */
        }

        #itemTable th:nth-child(7),
        #itemTable td:nth-child(7) {
            width: 4%;
            /* GST% */
        }

        #itemTable th:nth-child(8),
        #itemTable td:nth-child(8) {
            width: 4%;
            /* Invoice Amount */
        }

        #itemTable th:nth-child(9),
        #itemTable td:nth-child(9) {
            width: 2%;
            /* Purpose - reduced */
        }
    </style>

</head>

<body data-sidebar="dark">

    <?php include($IncludesPath . "PreLoader.php"); ?>

    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <?php include($IncludesPath . "page-title.php"); ?>

                    <!-- ================= INDENT FORM ================= -->
                    <form id="indentForm" enctype="multipart/form-data">
                        <!-- <input type="hidden" id="proforma_id" name="proforma_id" value=""> -->
                        <input type="hidden" id="proforma_id" name="ProformaDetailsId" value="">

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="row">
                                            <!-- <div class="col-md-3">
                                                <label>Doc No</label>
                                                <input type="text" class="form-control" id="doc_no" name="doc_no" placeholder="Enter Doc No">
                                            </div> -->
                                            <div class="col-md-3">
                                                <label>Doc No</label>
                                                <?php
                                                // Example: IND-YYYYMMDD-XXXX (XXXX = random 4-digit number)
                                                $docNo = "AFF/PRO-" . date('Ymd') . "-" . rand(1000, 9999);
                                                ?>
                                                <input type="text" class="form-control" id="doc_no" name="doc_no"
                                                    value="<?php echo $docNo; ?>" readonly>
                                            </div>


                                            <div class="col-md-3">
                                                <label>Date</label>
                                                <input type="date" class="form-control" id="pdate" name="pdate"
                                                    value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label>Currency</label>
                                                <select class="form-control select2" name="UnitCurrency"
                                                    id="UnitCurrency">
                                                    <option value="">Select Currency</option>

                                                    <?php while ($RowCurrency = $StmtCurrency->fetch_array()) { ?>
                                                        <option value="<?php echo $RowCurrency['CurrencyCode'] ?>">
                                                            <?php echo $RowCurrency['CurrencyName'] . ' - 
					                                    ' . $RowCurrency['CurrencyCode'] . '(' . $RowCurrency['CurrenySymbol'] . ')' ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Exchange rate</label>
                                                <input type="text" class="form-control" id="ExRate" name="ExRate"
                                                    placeholder="0.00">
                                            </div>
                                        </div>



                                        <div class="row">
                                            <!-- <div class="col-md-3">
                                                <label>Doc No</label>
                                                <input type="text" class="form-control" id="doc_no" name="doc_no" placeholder="Enter Doc No">
                                            </div> -->
                                            <div class="col-md-3">
                                                <label for="exampleInputPassword1">Consignee<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="ConsigneeID"
                                                    id="ConsigneeID">
                                                    <option value="">Select Consignee</option>
                                                    <?php while ($RowConsignee = $StmtConsignee->fetch_array()) { ?>
                                                        <option value="<?php echo $RowConsignee['ConID'] ?>">
                                                            <?php echo $RowConsignee['ConsigneeName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>


                                            <div class="col-md-3">
                                                <label>Company</label>
                                                <select class="form-control select2" name="Company" id="Company">
                                                    <option value="">Select</option>
                                                    <?php while ($RowUltimateConsignee = $StmtUltimateConsignee->fetch_array()) { ?>
                                                        <option value="<?php echo $RowUltimateConsignee['ConID'] ?>">
                                                            <?php echo $RowUltimateConsignee['ConsigneeName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Port of Loading</label>
                                                <select required class="form-control select2" name="PortofLoading"
                                                    id="PortofLoading">
                                                    <option value="">Select</option>
                                                    <?php while ($RowPorts = $StmtPorts->fetch_array()) { ?>
                                                        <option value="<?php echo $RowPorts['PortId'] ?>">
                                                            <?php echo $RowPorts['Name'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label>Notify Party</label>
                                                <select class="form-control select2" name="NotifyPartyID"
                                                    id="NotifyPartyID">
                                                    <option value="">Select</option>
                                                    <?php while ($RowNotifyparty = $StmtNotifyparty->fetch_array()) {
                                                        $NPartyAddress = mb_strimwidth($RowNotifyparty['NPartyAddress'], 0, 15, "...");
                                                    ?>
                                                        <option value="<?php echo $RowNotifyparty['NotifyPartyID'] ?>">
                                                            <?php echo $RowNotifyparty['NotifyPartyName'] . ' (' . $NPartyAddress . ')' ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>



                                        <div class="row">
                                            <!-- <div class="col-md-3">
                                                <label>Doc No</label>
                                                <input type="text" class="form-control" id="doc_no" name="doc_no" placeholder="Enter Doc No">
                                            </div> -->
                                            <div class="col-md-3">
                                                <label>Narration</label>
                                                <input type="text" class="form-control" id="narration" name="narration"
                                                    placeholder="Enter Narration">
                                            </div>


                                            <div class="col-md-3">
                                                <label>Term of Delivery and Payment</label>
                                                <select required class="form-control select2" name="ByPayment"
                                                    id="ByPayment">
                                                    <option value="">Select Payment</option>
                                                    <?php while ($RowPayment = $StmtPaymentTerms->fetch_array()) {
                                                        if ($RowPayment['LcStatus'] == 1) {
                                                            $LcStatus = 'LC';
                                                        } else {
                                                            $LcStatus = 'Non LC';
                                                        }
                                                    ?>
                                                        <option value="<?php echo $RowPayment['ID'] ?>">
                                                            <?php echo $RowPayment['PaymentName'] . ' - ' . $LcStatus . (($RowPayment['NoOfDays'] != 0) ? ' - ' . $RowPayment['ConditionDate'] . ' - (' . $RowPayment['NoOfDays'] . ' Days)' : '') ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Buyer Order No</label>
                                                <input type="text" class="form-control" id="BuyerOrderNo"
                                                    name="BuyerOrderNo" placeholder="Enter Buyer Order No">
                                            </div>
                                            <div class="col-md-3">
                                                <label>Export Reference No</label>
                                                <input type="text" class="form-control" id="ExportReferenceNo"
                                                    name="ExportReferenceNo" placeholder="Enter Export Reference No">
                                            </div>
                                        </div>


                                        <div class="row">
                                            <!-- <div class="col-md-3">
                                                <label>Doc No</label>
                                                <input type="text" class="form-control" id="doc_no" name="doc_no" placeholder="Enter Doc No">
                                            </div> -->
                                            <div class="col-md-3">
                                                <label>Shipment Date</label>
                                                <input type="date" class="form-control" id="Shipmentdate"
                                                    name="Shipmentdate" value="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                            <div class="col-md-3">
                                                <label>Country of Final Destination</label>
                                                <select required class="form-control select2" name="DestinationCountry"
                                                    id="DestinationCountry">
                                                    <option value="">Select Country</option>
                                                    <?php while ($RowCountry = $StmtCountryList->fetch_array()) { ?>
                                                        <option value="<?php echo $RowCountry['id'] ?>">
                                                            <?php echo strtoupper($RowCountry['name']) ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label>Country of Origin</label>
                                                <select required class="form-control select2" name="OriginCountry"
                                                    id="OriginCountry">
                                                    <option value="">Select Country</option>
                                                    <?php while ($RowCountry = $StmtCountryList->fetch_array()) { ?>
                                                        <option value="<?php echo $RowCountry['id'] ?>">
                                                            <?php echo strtoupper($RowCountry['name']) ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Final Destination</label>
                                                <select required class="form-control select2" name="FinalDestination"
                                                    id="FinalDestination">
                                                    <option value="">Select Final Destination</option>
                                                    <?php
                                                    $FinalDestination = $RowPIData['FinalDestination'];
                                                    foreach ($AryPorts as $RowPort) { ?>
                                                        <option value="<?php echo $RowPort['PortId'] ?>">
                                                            <?php echo $RowPort['Name'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>

                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- <div class="col-md-3">
                                                <label>Doc No</label>
                                                <input type="text" class="form-control" id="doc_no" name="doc_no" placeholder="Enter Doc No">
                                            </div> -->
                                            <div class="col-md-3">
                                                <label>PRO No</label>
                                                <?php
                                                // Example: IND-YYYYMMDD-XXXX (XXXX = random 4-digit number)
                                                $docNo = "AFF/PRO-" . date('Ymd') . "-" . rand(1000, 9999);
                                                ?>
                                                <input type="text" class="form-control" id="doc_no" name="doc_no"
                                                    value="<?php echo $docNo; ?>" readonly>
                                            </div>

                                            <div class="col-md-3">
                                                <label>Purchase Order Attachment</label>
                                                <input type="file" class="form-control" id="POAttachment"
                                                    name="POAttachment" placeholder="Select file">
                                            </div>

                                            <div class="col-md-3">
                                                <label>Po Approved Date</label>
                                                <input type="date" class="form-control" id="poAdate" name="poAdate"
                                                    value="<?php echo date('Y-m-d'); ?>">
                                            </div>

                                            <div class="col-md-3">
                                                <label>Process and Packed by</label>
                                                <input type="text" class="form-control" id="Packedby" name="Packedby"
                                                    placeholder="Enter Packed By">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- <div class="col-md-3">
                                                <label>Doc No</label>
                                                <input type="text" class="form-control" id="doc_no" name="doc_no" placeholder="Enter Doc No">
                                            </div> -->
                                            <div class="col-md-3">
                                                <label>Port Of Discharge</label>
                                                <select required class="form-control select2" name="PortOfDischarge"
                                                    id="PortOfDischarge">
                                                    <option value="">Select Port</option>
                                                    <?php
                                                    $PortOfDischarge = $RowPIData['PortOfDischarge'];
                                                    foreach ($AryPorts as $RowPort) { ?>
                                                        <option <?php if ($PortOfDischarge == $RowPort['PortId']) { ?>
                                                            selected="selected" <?php } ?>
                                                            value="<?php echo $RowPort['PortId'] ?>">
                                                            <?php echo $RowPort['Name'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="col-md-3">
                                                <label>Is Glaze</label>
                                                <select id="IsGlaze" name="IsGlaze" class="form-control">
                                                    <option value="NO">No</option>
                                                    <option value="YES">Yes</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Delivery Terms</label>
                                                <input type="text" class="form-control" id="DeliveryTerms"
                                                    name="DeliveryTerms" placeholder="Enter Delivery Terms">
                                            </div>
                                            <div class="col-md-3">
                                                <label>Buyer(other than consignee)</label>
                                                <select class="form-control select2" name="BuyerID" id="BuyerID">
                                                    <option value="">Select</option>
                                                    <?php while ($RowBuyer = $StmtBuyer->fetch_array()) { ?>
                                                        <option value="<?php echo $RowBuyer['BuyerID'] ?>">
                                                            <?php echo $RowBuyer['BuyerName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label>Amount in words</label>
                                                <input type="text" class="form-control" id="amountWords"
                                                    name="amountWords" placeholder="Enter Amount in Words">
                                            </div>
                                            <div class="col-md-3">
                                                <label>Advance Amount</label>
                                                <input type="number" class="form-control" id="AdvanceAmount"
                                                    name="AdvanceAmount" placeholder="0.00">
                                            </div>
                                        </div>
                                        <hr>

                                        <!-- Item Details -->
                                        <h4 class="mt-4 mb-3">Item Details</h4>
                                        <table class="table table-bordered" id="itemTable">
                                            <thead>
                                                <tr>
                                                    <th>Variety <span style="color:red">*</span></th>
                                                    <th>Grade</th>
                                                    <th>Packing Style</th>
                                                    <th>Process Type</th>
                                                    <th>Brand</th>
                                                    <th>BAP Master</th>
                                                    <th>No of Cartons</th>
                                                    <th>Total Net Weight(KGs)</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control txtVariety" name="txtVariety[]" placeholder="Click to Select Variety" readonly />
                                                        <input type="hidden" name="txtVarietyID[]" class="txtVarietyID" value="${escapeHtml(item?.VarietyID || 0)}">
                                                        <!-- <input type="hidden" name="breakup_id[]" class="breakup_id" value="${item?.BreakUpID || 0}"> -->
                                                        <input type="hidden" name="BreakUpID[]" class="breakup_id" value="${item?.BreakUpID || 0}">


                                                    </td>

                                                    <td>
                                                        <select class="drpSelect form-control grade" name="grade[]">
                                                            <option value=""> Select</option>
                                                            <?php foreach ($resGrade as $item) { ?>
                                                                <option value="<?php echo $item->value; ?>">
                                                                    <?php echo $item->Options ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="drpSelect form-control packing_style"
                                                            name="packing_style[]">
                                                            <option value=""> Select</option>
                                                            <?php foreach ($resPackingStyle as $item) { ?>
                                                                <option value="<?php echo $item->PCKID; ?>">
                                                                    <?php echo $item->PackingStyle ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="drpSelect form-control freezing"
                                                            name="freezing[]">
                                                            <option value=""> Select</option>
                                                            <?php foreach ($resFreezingType as $item) { ?>
                                                                <option value="<?php echo $item->FreezingTypeName; ?>">
                                                                    <?php echo $item->FreezingTypeName ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="drpSelect form-control brand"
                                                            name="brand[]">
                                                            <option value=""> Select</option>
                                                            <?php foreach ($resBrand as $item) { ?>
                                                                <option value="<?php echo $item->BrandID; ?>">
                                                                    <?php echo $item->BrandName ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td><input type="text" class="form-control bap" name="bap[]"></td>
                                                    <!-- <td><input type="file" class="form-control attachment" name="attachment[]"></td> -->
                                                    <td><input type="number" class="form-control cartons"
                                                            name="cartons[]"></td>

                                                    <td><input type="text" class="form-control weight" name="weight[]">
                                                    </td>
                                                    <td><button type="button" class="btn btn-success btnAddRow"><i
                                                                class="fa fa-plus"></i></button></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <!-- Container where item data will appear -->
                                        <div id="itemDataList" style="margin-top:10px;"></div>

                                        <div class="text-center mt-4">
                                            <button type="button" id="btnSaveIndent"
                                                class="btn btn-primary">Save</button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Indent Records -->
                    <div class="mt-4">
                        <h4>Existing Proformas</h4>
                        <div id="indentListDiv"></div>
                    </div>

                </div>
                <?php include($IncludesPath . "footer.php"); ?>
            </div>
        </div>
        <div class="modal fade" id="modalVarietyList" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Variety List</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div id="modalVariety"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>




    <div class="modal fade" id="viewProformaModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Proforma Invoice - View</h5>
                    <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close">
                        <i class="mdi mdi-close fs-4"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="viewProformaContent">
                        <!-- data injected here -->
                    </div>
                </div>

            </div>
        </div>
    </div>



    <script>
        var table;
        $(document).on('keydown', 'input[pattern]', function(e) {
            var input = $(this);
            var oldVal = input.val();
            var regex = new RegExp(input.attr('pattern'), 'g');

            setTimeout(function() {
                var newVal = input.val();
                if (!regex.test(newVal)) {
                    input.val(oldVal);
                }
            }, 1);
        });


        let poptable = null;
        let currentVarietyInput = null;

        // Use delegated handler so dynamically added .txtVariety inputs work
        $(document).on('click', '.txtVariety', function(e) {
            e.preventDefault();
            // Remember which input opened the modal
            currentVarietyInput = $(this);
            getVarieties();
        });
        $(document).on('shown.bs.modal', '#modalVarietyList', function() {
            // If a DataTable instance exists, destroy it and remove table markup to avoid duplicates
            if ($.fn.dataTable && $.fn.dataTable.isDataTable('.popVarietyTable')) {
                try {
                    $('.popVarietyTable').DataTable().destroy();
                } catch (err) {
                    // ignore
                }
                // Optional: clear any previous table html to avoid duplicates
                // $('.popVarietyTable').empty();
            }

            // Initialize datatable (delegated modal content is already injected via AJAX)
            poptable = $('.popVarietyTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                bInfo: false,
                ordering: false
            });

            // if you need column adjustment after rendering
            if (poptable && typeof poptable.columns === 'function') {
                poptable.columns.adjust().draw(false);
            }
        });


        $(document).on("click", ".btn-close", function() {
            $("#modalVarietyList").modal("hide");
        });

        function getVarieties() {
            $.ajax({
                type: 'POST',
                url: 'Ajax/ajax-pm-proforma-invoice.php',
                data: {
                    'methodType': 'varieties',
                },
                success: function(data) {
                    if (data) {
                        if (data.Error) {
                            alert(data.Error);
                        } else {
                            $('#modalVariety').html("").html(data.HtmlRes);
                            $('#modalVarietyList').modal('show');
                        }
                    }
                }
            });
        }

        // Auto-fill data when item is selected
        // $(document).on('change', '.txtVariety', function () {
        //     const itemId = $(this).val();
        //     const row = $(this).closest('tr');
        //     getAvailableDetails(itemId,row);
        //     getItemDetails(itemId, row);
        // });


        // When the modal is hidden, clear the reference to the input (optional)
        $(document).on('hidden.bs.modal', '#modalVarietyList', function() {
            currentVarietyInput = null;
        });

        // Selection handler — delegated, so it works for dynamically created rows inside the modal
        $(document).on("click", ".selectVariety", function(e) {
            e.preventDefault();

            var selectedVariety = $(this).val() || $(this).text();
            let selectedVarietyID = $(this).attr("data-varietyid");

            let grade = $(this).attr("data-grade");
            let freezing = $(this).attr("data-freezing");
            let packingstyle = $(this).attr("data-packingstyle");
            let brand = $(this).attr("data-brand");

            // update only the row where user clicked
            let row = currentVarietyInput.closest("tr");

            row.find(".txtVariety").val(selectedVariety);
            row.find(".txtVarietyID").val(selectedVarietyID);

            row.find(".grade").val(grade);
            row.find(".freezing").val(freezing);
            row.find(".packing_style").val(packingstyle);
            row.find(".brand").val(brand);

            $("#modalVarietyList").modal("hide");
        });


        function fnShowDataTable() {
            table = $('#tblDataTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                dom: '<"d-flex justify-content-end align-items-center mb-2"fB>rt',
                buttons: [{
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable btn btn-success',
                    title: 'General Items',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        // const previousData = <?php echo json_encode($previousData); ?>;
        // $(document).on('change', '.txtVariety', function () {
        //     const selectedItem = $(this).val();
        //     // const itemKey = selectedItem.toLowerCase(); // normalize to lowercase
        //     const itemData = previousData[selectedItem]; // get the relevant data array

        //     // Get the container
        //     const listContainer = $('#itemDataList');
        //     listContainer.empty(); // clear previous data

        //     if (!selectedItem) {
        //         // listContainer.html('<p style="color:#888;text-align:center;">Please select an item to view data.</p>');
        //         listContainer.html('<p style="color:#888;text-align:center;"></p>');
        //         return;
        //     }

        //     if (!itemData || itemData.length === 0) {
        //         listContainer.html(`<p style="color:red;text-align:center;">No previous data found for ${selectedItem}.</p>`);
        //         return;
        //     }

        //     // Build the HTML list
        //     let html = `
        //         <h5 style="margin-bottom:5px;text-align:center;">Last 5 Months ${selectedItem} Indent Data</h5>
        //         <table class="table table-bordered table-sm" >
        //             <thead class="thead-light">
        //                 <tr>
        //                     <th>Date</th>
        //                     <th>Qty</th>
        //                     <th>Price</th>
        //                 </tr>
        //             </thead>
        //             <tbody>
        //     `;

        //     itemData.forEach(entry => {
        //         html += `
        //             <tr>
        //                 <td>${entry.date}</td>
        //                 <td>${entry.qty}</td>
        //                 <td>${entry.price}</td>
        //             </tr>
        //         `;
        //     });

        //     html += `
        //             </tbody>
        //         </table>
        //     `;

        //     listContainer.html(html);
        // });

        $(document).ready(function() {

            // Mock item data (replace with AJAX fetch if needed)
            const itemData = {
                "Gloves": {
                    uom: "Units",
                    avail_qty: 100,
                    price: 50,
                    gst: 18
                },
                "Scissors": {
                    uom: "Units",
                    avail_qty: 50,
                    price: 120,
                    gst: 12
                },
                "Screwdriver": {
                    uom: "Units",
                    avail_qty: 30,
                    price: 200,
                    gst: 18
                }
            };

            // Add new row
            $(document).on('click', '.btnAddRow', function() {
                var newRow = $('#itemTable tbody tr:first').clone();
                newRow.find('input').val('');
                newRow.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
                newRow.find('td:last').html('<button type="button" class="btn btn-danger btnRemoveRow"><i class="fa fa-trash"></i></button>');
                $('#itemTable tbody').append(newRow);
            });

            // Remove row
            $(document).on('click', '.btnRemoveRow', function() {
                $(this).closest('tr').remove();
            });

            // Show selected file name
            $(document).on('change', '.attachment', function() {
                const fileName = $(this).val().split('\\').pop();
                $(this).closest('td').find('.file-name').text(fileName);
            });

            // // Save Indent
            // $('#btnSaveIndent').click(function() {
            //     var formData = new FormData($('#indentForm')[0]);
            //     formData.append('methodType', 'save');
            //     $.ajax({
            //         url: 'Ajax/ajax-pm-proforma-invoice.php',
            //         type: 'POST',
            //         data: formData,
            //         processData: false,
            //         contentType: false,
            //         dataType: 'json',
            //         success: function(res) {
            //             if (res.status === 'success') {
            //                 alert(res.Msg);
            //                 $('#indentForm')[0].reset();
            //                 $('#itemTable tbody tr:gt(0)').remove();
            //                 $('#itemTable tbody tr:first input').val('');
            //                 $('#itemTable tbody tr:first .requested_qty, .base_value, .invoice_amt').val('0.00');
            //                 loadIndents();
            //             } else {
            //                 alert('Error: ' + res.Msg);
            //             }
            //         },
            //         error: function() {
            //             alert('AJAX request failed!');
            //         }
            //     });
            // });


            // $('#btnSaveIndent').click(function() {
            //     var formData = new FormData($('#indentForm')[0]);
            //     var proforma_id = $('#proforma_id').val();

            //     if (proforma_id && proforma_id !== '') {
            //         formData.append('methodType', 'update');
            //         formData.append('ProformaDetailsId', proforma_id);
            //     } else {
            //         formData.append('methodType', 'save');
            //     }

            //     // Collect all breakup rows
            //     $('#itemTable tbody tr').each(function() {
            //         //  formData.append('BreakUpID[]', $(this).find('.breakup_id').val() || 0);  // map hidden ID
            //         formData.append('txtVarietyID[]', $(this).find('.txtVarietyID').val() || 0);
            //         formData.append('txtVariety[]', $(this).find('.txtVariety').val() || '');
            //         formData.append('brand[]', $(this).find('.brand').val() || 0);
            //         formData.append('packing_style[]', $(this).find('.packing_style').val() || 0);
            //         formData.append('bap[]', $(this).find('.bap').val() || '');
            //         formData.append('cartons[]', $(this).find('.cartons').val() || 0);
            //         formData.append('weight[]', $(this).find('.weight').val() || 0);
            //     });

            //     $.ajax({
            //         url: 'Ajax/ajax-pm-proforma-invoice.php',
            //         type: 'POST',
            //         data: formData,
            //         processData: false,
            //         contentType: false,
            //         dataType: 'json',
            //         success: function(res) {
            //             alert(res.Msg);
            //             if (res.status === 'success') {
            //                 loadIndents(); // reload list
            //             }
            //         },
            //         error: function() {
            //             alert('AJAX request failed!');
            //         }
            //     });
            // });


            $('#btnSaveIndent').click(function() {

                var formData = new FormData($('#indentForm')[0]);
                var proforma_id = $('#proforma_id').val();

                if (proforma_id && proforma_id !== '') {
                    formData.append('methodType', 'update');
                    formData.append('ProformaDetailsId', proforma_id);
                } else {
                    formData.append('methodType', 'save');
                }

                $.ajax({
                    url: 'Ajax/ajax-pm-proforma-invoice.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(res) {
                        alert(res.Msg);
                        if (res.status === 'success') {
                            location.reload(); // FULL page refresh
                        }
                    },
                    error: function() {
                        alert('AJAX request failed!');
                    }
                });
            });







            // function loadIndents() {
            //     $.ajax({
            //         url: 'Ajax/ajax-pm-proforma-invoice.php',
            //         type: 'POST',
            //         data: {
            //             methodType: 'load'
            //         },
            //         success: function(html) {
            //             $('#indentListDiv').html(html);
            //         }
            //     });
            // }



            function loadIndents(loadAll = false, searchKey = '') {
                $.ajax({
                    url: 'Ajax/ajax-pm-proforma-invoice.php',
                    type: 'POST',
                    data: {
                        methodType: 'load',
                        searchKey: searchKey,
                        loadAll: loadAll
                    },
                    success: function(res) {
                        $('#indentListDiv').html(res.html || res); // insert returned HTML

                        // Initialize DataTable after DOM update
                        setTimeout(function() {
                            initIndentDataTable();
                        }, 100);
                    }
                });
            }

            function initIndentDataTable() {
                const tableId = '#tblDataTable'; // your table inside returned HTML

                // Destroy old table if exists
                if ($.fn.DataTable.isDataTable(tableId)) {
                    $(tableId).DataTable().clear().destroy();
                }

                // Initialize DataTable
                $(tableId).DataTable({
                    scrollY: "600px",
                    scrollX: true,
                    sScrollXInner: "100%",
                    paging: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100, -1], // 👈 Load All
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse: true,
                    ordering: false,
                    dom: '<"d-flex justify-content-end align-items-center mb-2"fB>rtip',
                    buttons: [{
                        extend: 'excel',
                        text: 'Download Excel',
                        className: 'btn btn-success',
                        title: 'Proforma Invoice List',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }]
                });

                // Inject More Search and Load All buttons
                setTimeout(function() {
                    const $filter = $(tableId + '_filter');

                    if (!$('#btnMoreSearchIndent').length) {
                        $('<button class="btn btn-primary ml-2" id="btnMoreSearchIndent">More Search</button>')
                            .insertAfter($filter.find('label'));
                    }

                    if (!$('#btnLoadAllIndent').length) {
                        $('<button class="btn btn-primary ml-2" id="btnLoadAllIndent">Load All</button>')
                            .insertAfter('#btnMoreSearchIndent');
                    }
                }, 200);
            }

            // MORE SEARCH
            $(document).on('click', '#btnMoreSearchIndent', function() {
                const searchKey = $('#tblDataTable_filter input').val() || '';
                loadIndents(false, searchKey); // pass search key
            });

            // LOAD ALL
            $(document).on('click', '#btnLoadAllIndent', function() {
                loadIndents(true); // load all rows
            });


            // $(document).on('click', '.btnEdit', function() {
            //     var proforma_id = $(this).data('id'); // assuming data-id holds PiID
            //     $('#btnSaveIndent').text('Update');

            //     $.ajax({
            //         url: 'Ajax/ajax-pm-proforma-invoice.php',
            //         type: 'POST',
            //         data: {
            //             methodType: 'getSingle',
            //             price_db_id: proforma_id
            //         },
            //         dataType: 'json',
            //         success: function(res) {
            //             if (res.status === 'success') {
            //                 // Check if data exists
            //                 if (res.Data) {
            //                     var itemData = res.Data;

            //                     // ===== Header Fields =====
            //                     $('#proforma_id').val(itemData.PiID || '');
            //                     $('#doc_no').val(itemData.DocNo || '');
            //                     $('#pdate').val(itemData.Date || '');
            //                     $('#ExRate').val(itemData.ExRate || '');
            //                     $('#narration').val(itemData.Narration || '');
            //                     $('#BuyerOrderNo').val(itemData.BuyerOrderNo || '');
            //                     $('#ExportReferenceNo').val(itemData.ExportReferenceNo || '');
            //                     $('#Shipmentdate').val(itemData.Shipmentdate || '');
            //                     $('#poAdate').val(itemData.poAdate || '');
            //                     $('#Packedby').val(itemData.Packedby || '');
            //                     $('#DeliveryTerms').val(itemData.DeliveryTerms || '');
            //                     $('#amountWords').val(itemData.amountWords || '');
            //                     $('#AdvanceAmount').val(itemData.AdvanceAmount || '');

            //                     // ===== Select2 Fields =====
            //                     $('#UnitCurrency').val(itemData.UnitCurrency || '').trigger('change');
            //                     $('#ConsigneeID').val(itemData.ConsigneeID || '').trigger('change');
            //                     $('#Company').val(itemData.Company || '').trigger('change');
            //                     $('#PortofLoading').val(itemData.PortofLoading || '').trigger('change');
            //                     $('#NotifyPartyID').val(itemData.NotifyPartyID || '').trigger('change');
            //                     $('#ByPayment').val(itemData.ByPayment || '').trigger('change');
            //                     $('#DestinationCountry').val(itemData.DestinationCountry || '').trigger('change');
            //                     $('#OriginCountry').val(itemData.OriginCountry || '').trigger('change');
            //                     $('#FinalDestination').val(itemData.FinalDestination || '').trigger('change');
            //                     $('#PortOfDischarge').val(itemData.PortOfDischarge || '').trigger('change');
            //                     $('#IsGlaze').val(itemData.IsGlaze || 'NO').trigger('change');
            //                     $('#BuyerID').val(itemData.BuyerID || '').trigger('change');

            //                     // ===== Item Table =====
            //                     $('#itemTable tbody').empty();

            //                     if (itemData.breakUp && itemData.breakUp.length > 0) {
            //                         itemData.breakUp.forEach(function(item) {
            //                             var newRow = `<tr>
            //                     <td>
            //                         <input type="text" class="form-control txtVariety" name="txtVariety[]" value="${item.txtVariety || ''}" readonly />
            //                         <input type="hidden" class="txtVarietyID" name="txtVarietyID[]" value="${item.txtVarietyID || ''}" />
            //                     </td>
            //                     <td><select class="drpSelect form-control grade" name="grade[]">
            //                             <option value="${item.grade || ''}" selected>${item.grade || ''}</option>
            //                         </select>
            //                     </td>
            //                     <td><select class="drpSelect form-control packing_style" name="packing_style[]">
            //                             <option value="${item.packing_style || ''}" selected>${item.packing_style || ''}</option>
            //                         </select>
            //                     </td>
            //                     <td><select class="drpSelect form-control freezing" name="freezing[]">
            //                             <option value="${item.freezing || ''}" selected>${item.freezing || ''}</option>
            //                         </select>
            //                     </td>
            //                     <td><select class="drpSelect form-control brand" name="brand[]">
            //                             <option value="${item.brand || ''}" selected>${item.brand || ''}</option>
            //                         </select>
            //                     </td>
            //                     <td><input type="text" class="form-control bap" name="bap[]" value="${item.bap || ''}" /></td>
            //                     <td><input type="number" class="form-control cartons" name="cartons[]" value="${item.carton || ''}" /></td>
            //                     <td><input type="text" class="form-control weight" name="weight[]" value="${item.weight || ''}" /></td>
            //                     <td><button type="button" class="btn btn-success btnAddRow"><i class="fa fa-plus"></i></button></td>
            //                 </tr>`;
            //                             $('#itemTable tbody').append(newRow);
            //                         });
            //                     } else {
            //                         // If no items, add an empty row
            //                         var emptyRow = `<tr>
            //                 <td>
            //                     <input type="text" class="form-control txtVariety" name="txtVariety[]" readonly />
            //                     <input type="hidden" class="txtVarietyID" name="txtVarietyID[]" />
            //                 </td>
            //                 <td><select class="drpSelect form-control grade" name="grade[]"><option value="">Select</option></select></td>
            //                 <td><select class="drpSelect form-control packing_style" name="packing_style[]"><option value="">Select</option></select></td>
            //                 <td><select class="drpSelect form-control freezing" name="freezing[]"><option value="">Select</option></select></td>
            //                 <td><select class="drpSelect form-control brand" name="brand[]"><option value="">Select</option></select></td>
            //                 <td><input type="text" class="form-control bap" name="bap[]" /></td>
            //                 <td><input type="number" class="form-control cartons" name="cartons[]" /></td>
            //                 <td><input type="text" class="form-control weight" name="weight[]" /></td>
            //                 <td><button type="button" class="btn btn-success btnAddRow"><i class="fa fa-plus"></i></button></td>
            //             </tr>`;
            //                         $('#itemTable tbody').append(emptyRow);
            //                     }

            //                 } else {
            //                     // No data found, clear the form for new entry
            //                     $('#indentForm')[0].reset();
            //                     $('#itemTable tbody').empty();
            //                     var emptyRow = `<tr>
            //             <td>
            //                 <input type="text" class="form-control txtVariety" name="txtVariety[]" readonly />
            //                 <input type="hidden" class="txtVarietyID" name="txtVarietyID[]" />
            //             </td>
            //             <td><select class="drpSelect form-control grade" name="grade[]"><option value="">Select</option></select></td>
            //             <td><select class="drpSelect form-control packing_style" name="packing_style[]"><option value="">Select</option></select></td>
            //             <td><select class="drpSelect form-control freezing" name="freezing[]"><option value="">Select</option></select></td>
            //             <td><select class="drpSelect form-control brand" name="brand[]"><option value="">Select</option></select></td>
            //             <td><input type="text" class="form-control bap" name="bap[]" /></td>
            //             <td><input type="number" class="form-control cartons" name="cartons[]" /></td>
            //             <td><input type="text" class="form-control weight" name="weight[]" /></td>
            //             <td><button type="button" class="btn btn-success btnAddRow"><i class="fa fa-plus"></i></button></td>
            //         </tr>`;
            //                     $('#itemTable tbody').append(emptyRow);
            //                 }

            //             } else {
            //                 alert('Error: ' + res.Msg);
            //             }
            //         },
            //         error: function() {
            //             alert('AJAX request failed!');
            //         }
            //     });
            // });


            $(document).on('click', '.btnEdit', function() {

                const proforma_id = $(this).data('id');
                if (!proforma_id) return;

                $('#btnSaveIndent').text('Update');
                $('#proforma_id').val(proforma_id);

                $.ajax({
                    url: 'Ajax/ajax-pm-proforma-invoice.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        methodType: 'getSingle',
                        price_db_id: proforma_id
                    },
                    success: function(res) {

                        if (!(res && res.status === 'success' && res.Data)) {
                            alert(res?.Msg || 'Unable to load Proforma details.');
                            return;
                        }

                        const data = res.Data;
                        const breakups = data.breakUp || [];

                        /* ================= HEADER FIELDS ================= */

                        $('#doc_no').val(data.DocNo || '').trigger('change.select2');
                        $('#pdate').val(data.PiDate || '');
                        $('#ExRate').val(data.ExchangeRate || '');
                        $('#narration').val(data.Narration || '');
                        $('#BuyerOrderNo').val(data.BuyerOrderNo || '');
                        $('#ExportReferenceNo').val(data.ExportReferenceNo || '');
                        $('#Shipmentdate').val(data.ShipmentDate || '');
                        $('#poAdate').val(data.PoApprovedDate || '');
                        $('#Packedby').val(data.ProcessandPackedby || '');
                        $('#DeliveryTerms').val(data.DeliveryTerms || '');
                        $('#amountWords').val(data.Amountinwords || '');
                        $('#AdvanceAmount').val(data.AdvanceAmount || '');

                        /* ================= SELECT2 FIELDS (PO STYLE) ================= */

                        $('#UnitCurrency').val(data.Currency || '').trigger('change.select2');
                        $('#ConsigneeID').val(data.Consignee || '').trigger('change.select2');
                        $('#Company').val(data.Company || '').trigger('change.select2');
                        $('#PortofLoading').val(data.PortofLoading || '').trigger('change.select2');
                        $('#NotifyPartyID').val(data.NotifyParty || '').trigger('change.select2');
                        $('#ByPayment').val(data.PaymentTerms || '').trigger('change.select2');
                        $('#exportReferenceNo').val(data.ExportReferenceNo || '').trigger('change.select2');
                        $('#DestinationCountry').val(data.CountryOfFinalDestination || '').trigger('change.select2');
                        $('#OriginCountry').val(data.CountryofOrigin || '').trigger('change.select2');
                        $('#FinalDestination').val(data.FinalDestination || '').trigger('change.select2');
                        $('#PortOfDischarge').val(data.PortOfDischarge || '').trigger('change.select2');
                        $('#IsGlaze').val(data.IsGlaze || 'NO').trigger('change.select2');
                        $('#BuyerID').val(data.Buyer || '').trigger('change.select2');

                        /* ================= ITEM TABLE (CLONE ROW PATTERN) ================= */

                        const $templateRow = $('#itemTable tbody tr:first').clone();
                        $('#itemTable tbody').empty();

                        if (breakups.length) {

                            breakups.forEach(function(item) {

                                const $row = $templateRow.clone();

                                $row.find('.txtVariety').val(item.VarietyName || '');
                                $row.find('.txtVarietyID').val(item.VarietyID || 0);
                                $row.find('.breakup_id').val(item.BreakUpID || 0);

                                $row.find('.grade')
                                    .val(item.grade || '')
                                    .trigger('change.select2');

                                $row.find('.packing_style')
                                    .val(item.PackingStyleID || '')
                                    .trigger('change.select2');

                                $row.find('.freezing')
                                    .val(item.freezing || '')
                                    .trigger('change.select2');

                                $row.find('.brand')
                                    .val(item.BrandID || '')
                                    .trigger('change.select2');

                                $row.find('.bap').val(item.BAPMaster || '');
                                $row.find('.cartons').val(item.Cartons || 0);
                                $row.find('.weight').val(item.Weight || 0);

                                $('#itemTable tbody').append($row);
                            });

                        } else {

                            const $emptyRow = $templateRow.clone();
                            $emptyRow.find('input, select')
                                .val('')
                                .trigger('change.select2');

                            $('#itemTable tbody').append($emptyRow);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        alert('Failed to load Proforma details. ' + error);
                    }
                });
            });


            // Delete generalItem
            $(document).on('click', '.btnDelete', function() {
                if (!confirm('Are you sure you want to delete this generalItem?')) return;
                var generalItem_id = $(this).data('id');
                $.ajax({
                    url: 'Ajax/ajax-pm-proforma-invoice.php',
                    type: 'POST',
                    data: {
                        methodType: 'delete',
                        price_db_id: generalItem_id
                    },
                    dataType: 'json',
                    success: function(res) {
                        console.log(res);
                        if (res.status === 'success') {
                            alert(res.Msg);
                            loadIndents();
                        } else {
                            alert('Error: ' + res.Msg);
                        }
                    },
                    error: function() {
                        alert('AJAX request failed!');
                    }
                });
            });

            // Calculate Base Value and Invoice Amount on Requested Qty change
            $(document).on('input', '.requested_qty', function() {
                const row = $(this).closest('tr');
                const qty = parseFloat($(this).val());
                const price = parseFloat(row.find('.item_price').val());
                const gstPercent = parseFloat(row.find('.gst').val());

                const baseValue = qty * price;
                const invoiceAmt = baseValue + (baseValue * gstPercent / 100);

                row.find('.base_value').val(baseValue.toFixed(2));
                row.find('.invoice_amt').val(invoiceAmt.toFixed(2));
            });


            function getAvailableDetails(itemId, row) {
                $.ajax({
                    url: 'Ajax/ajax-pm-proforma-invoice.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        item_id: itemId,
                        methodType: 'availableData'
                    },
                    success: function(res) {
                        if (res && res.Data) {
                            const data = res.Data;

                            // ✅ fill values safely
                            row.find('.avail_qty').val(data?.AvailableQuantity ? data.AvailableQuantity : 0);
                        } else {
                            // reset row if no data
                            row.find('.avail_qty').val(0);
                        }
                    },
                    error: function(xhr, status, err) {
                        console.error('Error:', err);
                    }
                });
            }

            function getItemDetails(itemId, row) {
                $.ajax({
                    url: 'Ajax/ajax-pm-proforma-invoice.php',
                    type: 'POST',
                    dataType: 'json', // ✅ ensures res is automatically parsed
                    data: {
                        item_id: itemId,
                        effective_date: $("#pdate").val(),
                        methodType: 'itemDetails'
                    },
                    success: function(res) {
                        if (res && res.Data) {
                            const data = res.Data;

                            // ✅ fill values safely
                            row.find('.uom').val(data.Units || '');
                            row.find('.item_price').val(data.UnitPrice || '');
                            row.find('.gst').val(data.Gst || '');
                            row.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
                        } else {
                            // reset row if no data
                            row.find('input').val('');
                            row.find('.requested_qty, .base_value, .invoice_amt').val('0.00');
                        }
                    },
                    error: function(xhr, status, err) {
                        console.error('Error:', err);
                    }
                });
            }

            loadIndents();

        });




        $(document).on('click', '.btnView', function() {
            const proformaId = $(this).data('id');

            $.ajax({
                url: 'Ajax/ajax-pm-proforma-invoice.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    methodType: 'viewProforma', // triggers API
                    PiID: proformaId
                },
                success: function(res) {
                    if (res.status === 'success' && res.Data) {
                        renderViewModal(res.Data);
                        $('#viewProformaModal').modal('show');
                    } else {
                        alert(res.Msg || 'No data found');
                    }
                }
            });
        });

        function renderViewModal(data) {
            // Header HTML
            let headerHTML = `
    <h6>Header Details</h6>
    <div class="container-fluid mb-3">
        <div class="row mb-2">
            <div class="col-md-3"><strong>Doc No:</strong></div>
            <div class="col-md-3">${data.DocNo || '-'}</div>
            <div class="col-md-3"><strong>Date:</strong></div>
            <div class="col-md-3">${data.PiDate || '-'}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3"><strong>Currency:</strong></div>
            <div class="col-md-3">${data.Currency || '-'}</div>
            <div class="col-md-3"><strong>Exchange Rate:</strong></div>
            <div class="col-md-3">${data.ExchangeRate || '-'}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3"><strong>Buyer Order No:</strong></div>
            <div class="col-md-3">${data.BuyerOrderNo || '-'}</div>
            <div class="col-md-3"><strong>Shipment Date:</strong></div>
            <div class="col-md-3">${data.ShipmentDate || '-'}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3"><strong>PO Approved Date:</strong></div>
            <div class="col-md-3">${data.PoApprovedDate || '-'}</div>
            <div class="col-md-3"><strong>Processed & Packed By:</strong></div>
            <div class="col-md-3">${data.ProcessandPackedby || '-'}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3"><strong>Consignee:</strong></div>
            <div class="col-md-3">${data.Consignee || '-'}</div>
            <div class="col-md-3"><strong>Company:</strong></div>
            <div class="col-md-3">${data.Company || '-'}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3"><strong>Port of Loading:</strong></div>
            <div class="col-md-3">${data['Port of Loading'] || '-'}</div>
            <div class="col-md-3"><strong>Port of Discharge:</strong></div>
            <div class="col-md-3">${data.PortOfDischarge || '-'}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3"><strong>Notify Party:</strong></div>
            <div class="col-md-3">${data.NotifyParty || '-'}</div>
            <div class="col-md-3"><strong>Delivery Terms:</strong></div>
            <div class="col-md-3">${data.DeliveryTerms || '-'}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3"><strong>Final Destination:</strong></div>
            <div class="col-md-3">${data.FinalDestination || '-'}</div>
            <div class="col-md-3"><strong>Country of Origin:</strong></div>
            <div class="col-md-3">${data.CountryofOrigin || '-'}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3"><strong>Country of Final Destination:</strong></div>
            <div class="col-md-3">${data.CountryOfFinalDestination || '-'}</div>
            <div class="col-md-3"><strong>Is Glaze:</strong></div>
            <div class="col-md-3">${data.IsGlaze || '-'}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3"><strong>Buyer:</strong></div>
            <div class="col-md-3">${data.Buyer || '-'}</div>
            <div class="col-md-3"><strong>Amount in Words:</strong></div>
            <div class="col-md-3">${data.Amountinwords || '-'}</div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3"><strong>Advance Amount:</strong></div>
            <div class="col-md-3">${data.AdvanceAmount || '-'}</div>
            <div class="col-md-3"><strong>Narration:</strong></div>
            <div class="col-md-3">${data.Narration || '-'}</div>
        </div>
    </div>
    `;

            // Breakup table remains unchanged
            let tableHTML = `
    <h6 class="mt-3">Item Breakup</h6>
    <table class="table table-bordered table-sm">
        <thead class="table-primary">
            <tr>
                <th>Variety</th>
                <th>Grade</th>
                <th>Packing Style</th>
                <th>Process Type</th>
                <th>Brand</th>
                <th>BAP</th>
                <th>Cartons</th>
                <th>Weight</th>
            </tr>
        </thead>
        <tbody>`;

            if (data.breakUp && data.breakUp.length) {
                data.breakUp.forEach(item => {
                    tableHTML += `
            <tr>
                <td>${item.VarietyName || ''}</td>
                <td>${item.grade || '-'}</td>
                 <td>${item.PackingStyleName || '-'}</td>
                 <td>${item.freezing || '-'}</td>
                <td>${item.BrandName || ''}</td>
                <td>${item.BAPMaster || ''}</td>
                <td>${item.Cartons || ''}</td>
                <td>${item.Weight || ''}</td>
            </tr>`;
                });
            } else {
                tableHTML += `<tr><td colspan="6" class="text-center">No items</td></tr>`;
            }

            tableHTML += `</tbody></table>`;

            $('#viewProformaContent').html(headerHTML + tableHTML);
        }
    </script>

</body>

</html>