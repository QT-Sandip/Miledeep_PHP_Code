<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$ID = $_GET['IBTID'];
$RequestStatus = $_GET['RequestStatus'];
$Type = $_GET['Type'];

// echo $Type;

$ProductionPlant = $_SESSION['UnitID'];
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

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . '/includes/page-header.php' ?>
                <?php // include_once WORKING_MODULE . '/includes/page-filter.php' 
                ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <div class="text-center">
                                    <a href="CsIBTRequestList" class="btn btn-danger">
                                        <i class="ri-arrow-left-line align-middle ml-2"></i> Back
                                    </a>

                                    <button id="btnConfirm" style="text-align:left" type="button" class="btn btn-success  waves-effect waves-light" <?php if ($RequestStatus == 3 || $Type == 'IBT OUT') echo 'disabled'; ?>>
                                        Confirm
                                    </button>
                                    <input type="hidden" id="hdnObjRowsSelected" name="hdnObjRowsSelected" />
                                    <input type="hidden" id="hdnSumMCS" name="hdnSumMCS" />
                                    <input type="hidden" id="hdnSumLooseBags" name="hdnSumLooseBags" />
                                </div>

                                <div id="divDataList">
                                    <!-- -->
                                </div>
                                <input type="hidden" id="hdnActionType" />
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
    var IBTID = "<?php echo isset($_GET["IBTID"]) && !empty($_GET["IBTID"]) ? $_GET["IBTID"] : "" ?>";
    var type = "<?php echo $_GET['Type'] ?>";

    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            // bInfo: false,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: 'Cs IBT Request BreakUp Preview<?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

    function ShowList() {
        $.ajax({
            url: 'Ajax/ajax-cs-ibt-breakup-preview.php',
            type: 'POST',
            data: {
                methodType: "get",
                IBTID: IBTID,
                type: type,
            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    };

    $(document).ready(function() {
        ShowList();
    });

    $("#btnConfirm").on("click", function() {

        var jsonObj = [];
        var sumMCS = 0;
        var sumLooseBags = 0;

        $("#tblDataTable tbody tr").each(function() {

            var hdnStockId = $(this).find(".hdnStockId").val();
            var hdnBarCodeID = $(this).find(".hdnBarCodeID").val();
            var hdnStoreID = $(this).find(".hdnStoreID").val();
            var hdnRackID = $(this).find(".hdnRackID").val();
            var hdnBlockID = $(this).find(".hdnBlockID").val();
            var hdnProductionID = $(this).find(".hdnProductionID").val();
            var txtMCS = $(this).find(".hdnMcs").val();
            var txtLooseBags = $(this).find(".hdnLooseBags").val();
            var hdnIBTOutReason = $(this).find(".hdnIBTOutReason").val();

            sumMCS = parseInt(sumMCS) + parseInt(txtMCS);
            sumLooseBags = parseInt(sumLooseBags) + parseInt(txtLooseBags);

            jsonObj.push({
                "StockID": hdnStockId,
                "BarCodeID": hdnBarCodeID,
                "StoreID": hdnStoreID,
                "RackID": hdnRackID,
                "OutMcs": txtMCS,
                "OutLooseBags": txtLooseBags,
                "BlockID": hdnBlockID,
                "IBTOutReason": hdnIBTOutReason,
            });
        });

        $("#hdnSumMCS").val(sumMCS);
        $("#hdnSumLooseBags").val(sumLooseBags);
        $("#hdnObjRowsSelected").val(JSON.stringify(jsonObj));

        var requestStatus = $("#stockDetails").data("requestStatus");
        var stockOutDate = $("#stockDetails").data("stockoutdate");
        var toStore = $("#stockDetails").data("tostore");
        var reason = $("#stockDetails").data("stockoutreason");
        var totMsc = $("#hdnSumMCS").val();
        var totLooseBags = $("#hdnSumLooseBags").val()
        var productionPlant = '<?php echo $ProductionPlant ?>';
        var jsonRowsSelected = JSON.parse($("#hdnObjRowsSelected").val());

        $.ajax({
            url: 'Ajax/ajax-cs-ibt-breakup-preview.php',
            type: 'POST',
            data: {
                "methodType": "ibtOut",
                "ibtRequestID": IBTID,
                "stockOutDate": stockOutDate,
                "toStore": toStore,
                "stockOutReason": reason,
                "reStockinstage": '',
                "productionPlant": productionPlant,
                "totalMsc": totMsc,
                "totalLooseBags": totLooseBags,
                "stockItems": jsonRowsSelected,
                "selectedTransTypeId": '0'
            },
            success: function(data) {
                if (data.Error) {
                    alert(data.Error);
                } else {
                    alert(data.Msg);
                    window.location.href = "inward?ProductionDataID=" + data.ProductionID + "&StoreId=" + toStore;
                }
                console.log(data);
            }
        });
    });

    $(document).on('click', '.btnDelete', function() {
        var breakUpID = $(this).data('id');
        console.log(breakUpID);
        if (confirm('Are you sure you want to delete this record?')) {
            $.ajax({
                url: 'Ajax/ajax-cs-ibt-breakup-preview.php',
                type: 'POST',
                data: {
                    methodType: "delete",
                    breakUpID: breakUpID,
                    ibtID: IBTID,
                },
                success: function(data) {

                    console.log(data);
                    $("#divDataList").html(data);
                    fnShowDataTable();

                    var Msg = $("#stockDetails").data("msg");
                    alert(Msg);
                },
            });

        }
    });
</script>

</body>

</html>