<?php
// session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$PageTitle = "Batch track report";
$filterfromDates = "T";
$filterToDates = "T";
$filterYear = "T";
$filterLotId = "T";
$filterButtomButton = "T";

?>
<!doctype html>
<html lang="en">

<head>
<?php include_once ROOT . '/includes/head.php' ?>
<?php include_once ROOT . 'includes/body-preloader.php' ?>
<style>
    .highlight {
        border: 2px solid red;
    }
    .select2-error .select2-selection {
    border: 2px solid red !important;
}
</style>
</head>
<?php //include_once ROOT . '/includes/body-preloader.php' 
?>
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
            <?php include_once WORKING_MODULE . '/includes/page-filter.php'; ?>
            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="row">
                    
                        <div class="card-body">
                            <div id="divDataList">
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
<style>
 th.freezeHLCnt,th.freezeGrade,th.freezeVarierty,th.freezeHLQty,th.freezeVAQty,th.freezeVAYeild,th.freezeSoakingQty,th.freezePackingStyle,th.freezeGradingVariety,
 td.freezeHLCnt,td.freezeGrade,td.freezeVarierty,td.freezeHLQty,td.freezeVAQty,td.freezeVAYeild,td.freezeSoakingQty,td.freezePackingStyle,td.freezeGradingVariety
 {
    position: sticky;   
    background: white;
    z-index: 4;
}
td.freezeHLCnt,th.freezeHLCnt {
    left: 0px;   /* adjust based on your table */
}
th.freezeGrade,td.freezeGrade{
    left: 60px;
}
th.freezeGradingVariety,td.freezeGradingVariety{
    left: 120px;
}
th.freezeVarierty,td.freezeVarierty{
    left: 183px;
}
th.freezeHLQty,td.freezeHLQty{
    left: 243px;
}
th.freezeVAQty,td.freezeVAQty{
    left: 315px;
}
th.freezeVAYeild,td.freezeVAYeild{
    left: 385px;
}
th.freezeSoakingQty,td.freezeSoakingQty{
    left: 425px;
}
th.freezePackingStyle,td.freezePackingStyle{
     left: 490px;
}
</style>
<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<!-- included for fixed column -->
<script
    src="https://cdn.jsdelivr.net/npm/datatables.net-fixedcolumns@4.3.0/js/dataTables.fixedColumns.min.js"></script>
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/datatables.net-fixedcolumns-dt@4.3.0/css/fixedColumns.dataTables.min.css" />
<!-- included for fixed column -->
<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
type="text/javascript"></script>
<script>
$(document).ready(function() {
    
    $("#drpYear").select2({
        placeholder: "Select Year",
        width: "100%",
        minimumResultsForSearch: Infinity
    });

    $("#drpYear").val(null).trigger('change');



    $("#drpLotId").select2({
        placeholder: "Select Lot No",
        width: "100%",
        closeOnSelect: false
    });

    $('#drpLotId').on('click', function(e) {
        $(this).select2('open');
    });



    
});



function fnPurchaseColdStore() {
        var fromDate = $('#txtFromDate').val() || '';
         var toDate   = $('#txtToDate').val() || '';
        //  var selectedLotIds = $("#drpLotId").val();
        //  var lotIdsString = selectedLotIds.join(",");
         var lotIds   = ($("#drpLotId").val() || []).join(",");

        var url = 'Ajax/ajax-purchase-coldstore.php?excelDownload=T' + '&fromDate=' + fromDate + '&toDate=' + toDate   + '&lotIds=' + lotIds;
            
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
              window.location.href = data.filename;
                //  window.location.href = url;
            }
        });
    }


// function fnPurchaseColdStore() {
//     var fromDate = $('#txtFromDate').val() || '';
//     var toDate   = $('#txtToDate').val() || '';
//     var lotIds   = ($("#drpLotId").val() || []).join(",");

//     var url = 'Ajax/ajax-purchase-coldstore.php?excelDownload=T&fromDate=' + fromDate + '&toDate=' + toDate + '&lotIds=' + lotIds;

//     // Direct browser navigation to download Excel
//     window.location.href = url;
// }



    function fnShowDataTable() {
    var table = $('#tblDataTable').DataTable({
        scrollY: "600px",
        scrollX: true,
        sScrollXInner: "100%",
        paging: false,
        destroy: true,
        scrollCollapse: true,
        bInfo: false,
        bSort: false,
        dom: 'Blfrtip',
        fixedHeader: true,
        columnDefs: [
            { targets: 16, className: 'freezeHLCnt' }, // 9th column (index starts from 0)
            { targets: 19, className: 'freezeGrade' },
            { targets: 21, className: 'freezeGradingVariety' },
            { targets: 24, className: 'freezeVarierty' },
            { targets: 25, className: 'freezeHLQty' },
            { targets: 26, className: 'freezeVAQty' },
            { targets: 27, className: 'freezeVAYeild' },
            { targets: 34, className: 'freezeSoakingQty' },
            { targets: 36, className: 'freezePackingStyle' },
        ],
        buttons: [{
            extend: 'excel',
            text: 'Download Excel',
            className: 'btnExcelDataTable btn-left-align',
            title: 'PurchaseColdStore Report <?php echo date("d-m-Y") ?>',
            footer: true,
            exportOptions: {
                columns: ':visible',
            },
            
            action: function() {
            fnPurchaseColdStore(); 
        }

        }],
    });
        $( "<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>"
        // + "<a class='btn btn-primary' id='LoadAll' href='javascript:void(0);' style='padding: 3px 5px;margin-left: 10px;'>Load All</a>"
    ).insertAfter("#tblDataTable_filter label");
}



function ShowList(pageNo , hdnTotalRecords , loadAll , searchkey ) {
    var selectedLotIds = $("#drpLotId").val();
    var lotIdsString = selectedLotIds.join(",");
    $.ajax({
        url: 'Ajax/ajax-purchase-coldstore.php',
        type: 'POST',
        data: {
            fromDate: $("#txtFromDate").val(),
            toDate: $("#txtToDate").val(),
            methodType: "GET",
            lotIds: selectedLotIds,
            PageNo: pageNo,
            loadAll:loadAll,
            hdnTotalRecords: hdnTotalRecords,
            SearchKey: searchkey
        },
        success: function(data) {
            $("#divDataList").html(data);
            fnShowDataTable();
            $("#txtFromDate").attr("disabled", false);
            $("#txtToDate").attr("disabled", false);
            //  $("#drpYear").val("").change();
            return false;
        }
    });
}


$('#txtFromDate,#txtToDate').on('change',function(){
    getBatches();
})
$('#drpYear').on('change', function() {
    var selectedYear = $(this).val();
    if (selectedYear) {
        $("#txtFromDate").val(selectedYear + "-04-01");
        $("#txtToDate").val((parseInt(selectedYear) + 1) + "-03-31");
        //$("#txtFromDate").attr("disabled", true);
       // $("#txtToDate").attr("disabled", true);
       getBatches();
    } else {
        $('#drpLotId').select2('destroy');
        $("#txtFromDate").val(Date.now().toString().substring(0, 10));
        $("#txtToDate").val(Date.now().toString().substring(0, 10));
        //$("#txtFromDate").attr("disabled", false);
        //$("#txtToDate").attr("disabled", false);
    }
});

function getBatches(){
    if( $("#txtFromDate").val() ||  $("#txtToDate").val()){
        $.ajax({
            url: 'Ajax/ajax-purchase-coldstore.php',
            type: 'POST',
            data: {
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                methodType: "GET_BATCH",
            },
            success: function(response) {
                //     console.log(response);return
                // var data = JSON.parse(response);
                // console.log(data);                
                if(response.Error){
                    alert(response.Error);
                    return;
                }
                $('#drpLotId').select2('destroy');
                var List = "<option value=''>Select Lot No</option>";
                response.PurchaseLotsData.forEach(element => {
                // altert();return
                List += `<option value="${element.BookingNumber}">${element.BookingNumber}</option>`;
                });

                $('#drpLotId').html(List).select2();
            }            
        });
    }
}

// $("#btnSearch").on("click", function() {
//     $("#txtFromDate, #txtToDate").removeClass("highlight");
//     var fromDate = $("#txtFromDate").val();
//     var toDate = $("#txtToDate").val();
//     if (fromDate == "" || toDate == "") {
//         if (fromDate == "") {
//             $("#txtFromDate").addClass("highlight");
//         }
//         if (toDate == "") {
//             $("#txtToDate").addClass("highlight");
//         }
//         return false;
//     }
    
//     ShowList();
    
// });




$("#btnSearch").on("click", function () {

    $("#txtFromDate, #txtToDate").removeClass("highlight");
    $("#drpLotId").parent().find('.select2-container')
        .removeClass('select2-error');

    var fromDate = $("#txtFromDate").val();
    var toDate   = $("#txtToDate").val();
    var lotIds   = $("#drpLotId").val();
    var year     = $("#drpYear").val();
    var isError = false;

    if (fromDate == "") {
        $("#txtFromDate").addClass("highlight");
        isError = true;
    }
    if (toDate == "") {
        $("#txtToDate").addClass("highlight");
        isError = true;
    }
    if (year && (!lotIds || lotIds.length === 0)) {
        $("#drpLotId").parent().find('.select2-container')
            .addClass('select2-error');
        isError = true;
    }

    if (isError) return false;

    ShowList();
});




$(document).on('click', '#LoadAll', function(e) {
    ShowList('','',1, "")
})
    $(document).on('click', '#btnMoreSearch', function(e) {
    var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", "", "0",searchkey);
        }
});


$(document).on('click', '.btnPageno', function () {

    if ($(this).hasClass('disabled')) return;

    let pageNo = $(this).data('pageno');
        console.log("pag" , pageNo);

    ShowList(pageNo,'','','');
});



</script>
</body>

</html>
