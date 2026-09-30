<?php
$PageTitle = "Purchase Summary Report";

// phpinfo();
//purchase-summary-report.php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once __DIR__.'/../config/BaseUrl.php';
$BaseUrlConfig = new BaseUrl;
$BaseUrlConfig = $BaseUrlConfig->getBaseUrl(true);
$folderArr = explode('/',$BaseUrlConfig->dir);
$PageTitleFolder = !empty($folderArr[2])?$folderArr[2]:'';
$PageTitleLink = $BaseUrlConfig->dir;

$filterArrivalPlant="T";
//$filterPlant="T";
$filterCenter = "T";
$filterSupplier="T";
//$filterCount="T";
$filterStartDate = "T";
$filterEndDate = "T";
$buttonBottom = "T";

$CurrentMonth = date('m');
$CurrentYear = date('Y');

$Startyear = $_SESSION["StartYear"]; 
$endYear = $CurrentYear + 1;

// Determine the past FY and current FY based on the current month
/* if ($CurrentMonth > 3) {
    $PastFY = $CurrentYear - 1;
    $CurrentFY = $CurrentYear;
} else {
    $PastFY = $CurrentYear - 2;
    $CurrentFY = $CurrentYear - 1;
} */

if ($CurrentMonth > 3) {
    $FinClsYear = $CurrentYear + 1;
    $PastFY = $CurrentYear - 1;
    $CurrentFY = $CurrentYear;
} else {
    $FinClsYear = $CurrentYear;
    $PastFY = $CurrentYear - 2;
    $CurrentFY = $CurrentYear - 1;
}

?>

<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . 'includes/head.php' ?>

    </head>

    <?php include_once ROOT . '/includes/body-preloader.php' ?>  

        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php include_once ROOT . 'includes/menu.php' ?>                   

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
                        <?php include($IncludesPath . "page-title.php"); ?>
                        <?php 
                        //include_once ROOT . 'includes/page-header.php' ?>   
                        <?php //include_once WORKING_MODULE . 'includes/page-filter.php'; ?> 
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">                             
                                        <form id="frmDeHeading">
                                            <div class="row">
                                                <div class="col-md-3 col-lg-3 mb-3">
                                                    <label for="drpYear" class="form-label">Select Year<span style="color:red"></span></label>
                                                    <select class="drpSelect form-control" id="drpYear" name="drpYear">
                                                        <option value="">Select Year</option>
                                                        <!-- <option value="<?php echo $PastFY ?>"><?php echo $PastFY . '-' . ($PastFY + 1) ?></option>
                                                        <option value="<?php echo $CurrentFY ?>" selected>
                                                            <?php echo $CurrentFY . '-' . ($CurrentFY + 1) ?></option> -->
                                                        <?php
                                                        for ($Startyear; $Startyear <= $endYear; $Startyear++) {
                                                            $year = ($Startyear - 1) . '-' . ($Startyear);
                                                        ?>
                                                            <option <?php if ($Startyear == $FinClsYear) {
                                                                        echo 'selected="selected"';
                                                                    } ?> value="<?php echo $Startyear - 1 ?>"> <?php echo $year ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>                                                
                                                <div class="col-md-3 mb-2">
                                                    <label for="txtFromDate">From Date <span style="color:red">*</span></label>
                                                    <input type="date" value="<?php //echo "04/01/".$CurrentFY;?>" class="form-control" name="txtFromDate" id="txtFromDate" disabled="disabled">
                                                </div>
                                                <div class="col-md-3 mb-2">
                                                    <label for="txtToDate">To Date <span style="color:red">*</span></label>
                                                    <input type="date" value="<?php //echo "03/31/".$CurrentFY+1; ?>" class="form-control" name="txtToDate" id="txtToDate" disabled="disabled">
                                                </div>
                                                <div class="col-md-3 mb-2" data-select2-id="9">
                                                    <label for="LotId">Batch No</label>
                                                    <select class="drpSelect form-control" name="drpLotId[]" id="drpLotId" multiple>
                                                        <option>Select Batch No</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row" >
                                                <div class="form-group col-12mt-4" style="display: flex; justify-content: center;">
                                                    <a class="btn btn-primary search""> Search</a>
                                                   
                                                </div> 
                                            </div>                                   
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body">

                                        <div id="divDataList">
                                            <!-- -->
                                        </div>

                                    </div>
                                </div>


                            </div>
                        </div>

                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                <?php include_once ROOT . 'includes/footer.php' ?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <?php include_once ROOT . 'includes/common-js.php' ?>

        <!-- Required datatable js -->
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        
        <?php include_once ROOT . 'includes/app-js.php' ?>

        <script>
            $(document).ready(function() {
                $("#drpYear").val("").change();
                /* $("#drpYear").select2({
                    placeholder: "Select Year",
                    width: "100%",
                }) */

                $("#drpLotId").select2({
                    placeholder: "Select Batch No",
                    width: "100%",
                    closeOnSelect: false
                });

                $('#drpLotId').on('click', function(e) {
                    $(this).select2('open');
                });
                $('#txtFromDate').on('change', function() {
                    var datObj = {
                        fromDate: $(this).val(),
                        toDate: $("#txtToDate").val(),
                        methodType: "GET_BATCH",
                    }
                    getBatches(datObj);
                })
                $('#txtToDate').on('change', function() {
                    var datObj = {
                        toDate: $(this).val(),
                        fromDate: $("#txtFromDate").val(),
                        methodType: "GET_BATCH",
                    }
                    getBatches(datObj);
                })
            });           
            $('#drpYear').on('change', function() {
                var selectedYear = $(this).val();
                if (selectedYear) {
                    $("#txtFromDate").val(selectedYear + "-04-01");
                    $("#txtToDate").val((parseInt(selectedYear) + 1) + "-03-31");
                    $("#txtFromDate").attr("disabled", true);
                    $("#txtToDate").attr("disabled", true);
                     var datObj = {
                        toDate: $("#txtToDate").val(),
                        fromDate: $("#txtFromDate").val(),
                        methodType: "GET_BATCH",
                    }
                    getBatches(datObj);
                } else {
                    $("#txtFromDate").val(Date.now().toString().substring(0, 10));
                    $("#txtToDate").val(Date.now().toString().substring(0, 10));
                    $("#txtFromDate").attr("disabled", false);
                    $("#txtToDate").attr("disabled", false);
                    var List = "<option value=''>Select Batch No</option>";
                    $('#drpLotId').select2('destroy').select2();
                    $('#drpLotId').html(List);
                    $('#drpLotId').select2('destroy').select2();

                }
            });
            function getBatches(datObj){
                $.ajax({
                    url: 'Ajax/AjaxPurchaseItemWiseSummaryReport.php',
                    type: 'POST',
                    data: datObj,
                    dataType: 'JSON',
                    success: function(data) {
                        //console.log(data.Error)
                        if(data.Error){
                            alert(data.Error)
                            return;
                        }
                        var List = "<option value=''>Select Batch No</option>";
                        if(data.Error == null && data.Data.BatchNumbers){
                            data.Data.BatchNumbers.forEach(element => {
                                List += `<option value=${element.RmArrivalID}>${element.ArrivalNumber}</option>`
                            });
                        }
                        $('#drpLotId').select2('destroy').select2();
                        $('#drpLotId').html(List);
                        $('#drpLotId').select2('destroy').select2();
                    }
                });
            }
             function FormattedDate(oldformatDate) {
               var dateObject = new Date(oldformatDate);
               var FormattedDate = dateObject.getDate()+"/"+(dateObject.getMonth()+1)+"/"+dateObject.getFullYear();
               return FormattedDate;
            }
            function fnShowDataTable() {     

              // var CenterName= $("#select2-drpCenter-container").text();
               var drpLotId = $("#drpLotId").val().join();
               var StartDate= FormattedDate ($("#txtFromDate").val());             
               var EndDate= FormattedDate($("#txtToDate").val());
         
               var DatePeriod='(From ' + ' '+ StartDate + ' To '+ EndDate + ')';
               
                var table = $('#tblDataTable').DataTable({                    
                    scrollY: "600px",
                    scrollX:true,
                    sScrollXInner: "100%", 
                    paging:false,
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse:true,
                    dom: 'Blfrtip',
                    // bSortCellsTop: true,
                    // header: true, // Enable complex headers
                    buttons: [{
                        extend: 'excel', 
                        text: 'Download Excel', 
                        className: 'btnExcelDataTable',
                        title: 'Purchase Summary Report - ' + DatePeriod, 
                        footer: true,
                        exportOptions: {
                            columns: ':visible'
                        },
                        // exportOptions: {
                        //     columns: ':visible',
                        //     // rows: function (indx, rowData, domElement) {
                        //     //     return $(domElement).css("display") != "none";
                        //     // }
                        // },
                        // customize: function (xlsx) {
                        //     var sheet = xlsx.xl.worksheets['sheet1.xml'];        
                        //     $('row c[r^="C"]', sheet).attr('s', '2');
                        // }
                        
                    }],
                });         
                // var data = table.buttons.exportData( {
                //     customizeData: function(dataForExport) {
                //         dataForExport.header.push("Header line 1");
                //         dataForExport.header.push("Header line 2");
                //         dataForExport.header.push("Header line 3");
                //     }
                // } );   
            }
            function ShowList() {
             
                var txtStartDate = $("#txtFromDate").val();
                var txtEndDate = $("#txtToDate").val();
                var drpLotId = $("#drpLotId").val().join();

                var startDate = new Date(txtStartDate);
                var endDate = new Date(txtEndDate);
                
                var timeDiff = endDate.getTime() - startDate.getTime();
                var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
                
                var days = daysDiff + 1;

                console.log(days);

                if(days > 31 && !drpLotId) {
                    var url = 'Ajax/AjaxPurchaseItemWiseSummaryReport.php?LotId='+drpLotId+'&StartDate='+txtStartDate+'&EndDate='+txtEndDate+'&excelDownload=T';
                    //window.open(url, '_blank');

                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        timeout: 150000,
                        success: function(data) {
                            debugger;
                            //console.log(data);
                            //window.test=data;
                            window.location.href=data.filename;
                        }
                    }); 
                } else {
                    $.ajax({
                        url:'Ajax/AjaxPurchaseItemWiseSummaryReport.php',
                        type:'POST',
                        data:{ 
                            StartDate: txtStartDate,
                            EndDate: txtEndDate,
                            LotId: drpLotId
                        },
                        success:function(data){
                            $("#divDataList").html(data);
                            fnShowDataTable();
                            return false;
                        }
                    });

                }                
            }
            $(document).ready(function(){
                // ShowList();
            });

            $(".search").on("click", function(){
                
                var txtStartDate = $("#txtFromDate").val();
                var txtEndDate = $("#txtToDate").val();

                if(txtStartDate && txtEndDate) {
                    ShowList();
                } else {
                    alert("Please select the start date and end date");
                }
            })
        </script>

    </body>
</html>
