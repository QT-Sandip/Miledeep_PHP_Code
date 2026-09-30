<?php

// phpinfo();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

//$filterPlant="T";
$filterArrivalPlant = "T";
$filterCenter = "T";
$filterStartDate = "T";
$filterEndDate = "T";
$buttonBottom = "T";

$PageTitle = "Center Wise Abstract Report";
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

                <?php include_once ROOT . 'includes/page-header.php' ?>
                <?php include_once WORKING_MODULE . 'includes/page-filter.php'; ?>

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
    function fnShowDataTable() {

        var CenterName= $("#select2-drpCenter-container").text();
              var StartDate= FormattedDate ($("#txtStartDate").val());             
              var EndDate= FormattedDate($("#txtEndDate").val());
         
             var DatePeriod=' From ' + ' '+ StartDate + ' To '+ EndDate ;
               
//alert(SupplierName);
            //    if(CenterName=="Select Center") {
            //     CenterName='  Details of Supplier Wise Out Standing Amount '+DatePeriod;
            //    }
            //    else{
            //     CenterName= CenterName+'  Details of Supplier Wise Out Standing Amount '+DatePeriod;
            //    }

        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel', text: 'Download Excel', className: 'btnExcelDataTable', title: 'CENTREWISE PURCHASE ABSTRACT REGISTER FOR THE PERIOD' +DatePeriod, footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }
    function ShowList() {
    var plant = $("#drpArrivalPlant").val();
    var center = $("#drpCenter").val();
    var txtStartDate = $("#txtStartDate").val();
    var txtEndDate = $("#txtEndDate").val();

    var startDate = new Date(txtStartDate);
    var endDate = new Date(txtEndDate);
    
    var timeDiff = endDate.getTime() - startDate.getTime();
    var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
    
    var days = daysDiff + 1;

    console.log(days);

    if(days > 31) {
        var url = 'Ajax/AjaxPurchaseObstractReport.php?Plant='+plant+'&Center='+center+'&StartDate='+txtStartDate+'&EndDate='+txtEndDate+'&excelDownload=T';
        window.open(url, '_blank');
    } else {
        $.ajax({
            url: 'Ajax/AjaxPurchaseObstractReport.php',
            type: 'POST',
            data: {
                // Plant: $("#drpPlant").val(), 
                Plant: $("#drpArrivalPlant").val(),
                Center: $("#drpCenter").val(),
                StartDate: $("#txtStartDate").val(),
                EndDate: $("#txtEndDate").val()

            },
            success: function (data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }
    }
    $(document).ready(function () {
        //                 $StartDate=$("#txtStartDate").val();
        //                 $EndDate=$("#txtEndDate").val();
        // if($StartDate!='' && $EndDate!=''){
        //                 ShowList();
        // }
        // else{
        //     alert("Please select Date");
        // }
    });

    $("#btnSearch").on("click", function () {
        $StartDate = $("#txtStartDate").val();
        $EndDate = $("#txtEndDate").val();
        if ($StartDate != '' && $EndDate != '') {
            ShowList();
        }
        else {
            alert("Please select Date");
        }
    });

    function FormattedDate(oldformatDate) {
               var dateObject = new Date(oldformatDate);
               var FormattedDate = dateObject.getDate()+"/"+(dateObject.getMonth()+1)+"/"+dateObject.getFullYear();
               return FormattedDate;
            }
$("#drpCenter").on("change", function () {
    $('#tblDataTable').DataTable().destroy();
    $("#divDataList").html("");
});
</script>

</body>

</html>

        
