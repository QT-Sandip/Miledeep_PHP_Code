<?php

$PageTitle = "Rm Analysis Reports";
$PageTitleFolder = "Quality Control";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$uid = $_SESSION['r_id'];

$sql_add_select = "SELECT ExpApprovalNo,ExpID,ExpName FROM `AdminExporterDetails` WHERE ExpType!=1 ";
$stmt_add_select = $iconn->query($sql_add_select);

$SqlLabName = "SELECT * FROM `QcEiaLabNames` WHERE Status=1";
$StmtLabName = $iconn->query($SqlLabName);
$Query = '';
if(!empty($_POST['processor'])){
    $Query .= ' AND SPD.ProcessorID='.$_POST['processor'].' ';
}
if(!empty($_POST['labNames'])){
    $Query .= " AND QSLE.TestingLab='".$_POST['labNames']."' ";
}
if(!empty($_POST['fromDate']) && !empty($_POST['toDate'])){
    $Query .= " AND QSLE.CompletedDate BETWEEN '".$_POST['fromDate']."' AND '".$_POST['toDate']."' ";
}elseif(!empty($_POST['fromDate'])){
    $Query .= " AND QSLE.CompletedDate >= '".$_POST['fromDate']."' ";
}elseif(!empty($_POST['toDate'])){
    $Query .= " AND QSLE.CompletedDate <= '".$_POST['toDate']."' ";
} 
$InvSql = "SELECT SPD.PoID,SPD.InvNumFull,QSLE.SMPLID
            FROM QcSamplingLettersExt QSLE
            LEFT JOIN ScrmPoData AS SPD ON QSLE.InvID = SPD.PoID
            WHERE QSLE.`CompletedDate` IS NOT NULL AND QSLE.`CompletedDate` != '0000-00-00'
            $Query
            GROUP BY QSLE.InvID";
$StmtInv = $iconn->query($InvSql);
$InvArr = [];
while ($rowInv = $StmtInv->fetch_assoc()) { 
    $InvArr[] = $rowInv;
}
if(!empty($_POST['Request']) && $_POST['Request'] == 'GetInvNo'){
    $data = ['Data' => $InvArr];
    echo json_encode($data);die;
}

$pendingInvSql = "SELECT s.PoID, s.InvNumFull,q.SMPLID
                    FROM ScrmPoData s
                    LEFT JOIN QcSamplingLettersExt q 
                        ON s.PoID = q.InvID
                    WHERE q.InvID IS NULL
                    OR q.CompletedDate = '0000-00-00'
                    OR q.CompletedDate IS NULL
                    GROUP BY s.PoID";
$StmtPendingInv = $iconn->query($pendingInvSql);
$InvPendingArr = [];
while ($rowInv = $StmtPendingInv->fetch_assoc()) { 
    $InvPendingArr[] = $rowInv;
}

/* if(!empty($_POST['Request']) && $_POST['Request'] == 'GetPendingInvNo'){
    $data = ['Data' => $InvPendingArr];
    echo json_encode($data);die;
} */

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
        include($IncludesPath . "sidebar.php");
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

                                        <div class="col-lg-3">
                                            <label for="drpProcessor">Processor</label>
                                            <select class="form-control select2" name="drpProcessor" id="drpProcessor">
                                                <option value="">Select</option>
                                                <?php while ($row_add = $stmt_add_select->fetch_array()) { ?>
                                                    <option Value="<?php echo $row_add['ExpID']; ?>">
                                                        <?php echo $row_add['ExpName'] . ' - ' . $row_add['ExpApprovalNo']; ?>
                                                    </OPTION>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-lg-3">
                                            <label for="drpLabNames">Lab Name</label>
                                            <select class="form-control select2" name="drpLabNames" id="drpLabNames">
                                                <option value="">Select</option>
                                                <?php while ($rowLabNames = $StmtLabName->fetch_array()) { ?>
                                                    <option Value="<?php echo $rowLabNames['LabName']; ?>">
                                                        <?php echo $rowLabNames['LabName'] . ' - ' . $rowLabNames['Address']; ?>
                                                    </OPTION>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-lg-2">
                                            <label for="txtFromDate">From Date</label>
                                            <input type="date" class="form-control" name='txtFromDate' id="txtFromDate"
                                                value="<?php echo date("Y-m-d"); ?>">
                                        </div>

                                        <div class="form-group col-lg-2">
                                            <label for="txtToDate">To Date</label>
                                            <input type="date" class="form-control" name='txtToDate' id="txtToDate"
                                                value="<?php echo date("Y-m-d"); ?>">
                                        </div>
                                        <div class="col-lg-2">
                                            <label for="drpInvoice">Invoice No.</label>
                                            <select class="form-control select2" name="drpInvoice" id="drpInvoice">
                                                <option value="">Select</option>
                                                <?php
                                               foreach($InvArr as $rowInv){  ?>
                                                    <option Value="<?php echo $rowInv['PoID']; ?>">
                                                        <?php echo $rowInv['InvNumFull'] ; ?>
                                                    </OPTION>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-lg-2">
                                            <label for="drpInvoice">Pending Invoice No.</label>
                                            <select class="form-control select2" name="drpPendingInvoice" id="drpPendingInvoice">
                                                <option value="">Select</option>
                                                <?php
                                                foreach($InvPendingArr as $rowInv){  
                                                    if(!empty($rowInv['SMPLID'])){
                                                        $QueryParms = 'PoID=' . $rowInv['PoID'].'&RID='.$rowInv['SMPLID'];
				                                        $QueryParms = urlEncode64($QueryParms);
                                                        $url = "DocEditSamplLetterExtrnl.php?Q=$QueryParms";
                                                    }else{
                                                        $QueryParms = 'PoID=' . $rowInv['PoID'];
				                                        $QueryParms = urlEncode64($QueryParms);
                                                        $url = "DocSampleLetter.php?Q=$QueryParms";
                                                    }
                                                    
                                                    ?>
                                                    <option Value="<?php echo $url; ?>">
                                                        <?php echo $rowInv['InvNumFull'] ; ?>
                                                    </OPTION>
                                                <?php } ?>
                                            </select>
                                        </div>

                                    </div>

                                    <div class="col-xs-12" align="CENTER">
                                        <a id="list" class="btn btn-success">Print</a>
                                        <a id="excel-list" class="btn btn-success">Excel</a>
                                        <a id="createExtSampleLetter" class="btn btn-secondary"> 
                                            <i class="mdi mdi-plus font-size-18"></i>    
                                            Create Ext Labs Sampling Letter
                                        </a>
                                    </div>
                                </div> <!-- end row -->
                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body" id="POList">
                                <!--<h4 class="card-title">Proforma Invoice List</h4>-->

                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div>
                <!-- end row-->
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
</body>

<script>
    $(document).on('change', '#drpProcessor, #drpLabNames, #txtFromDate, #txtToDate', function (e) {
        getInvNos();
    });
    $('document').ready(function () {
        $('#drpInvoice').on('change',function(){
            $('#drpPendingInvoice').prop('disabled',false);
            $('#createExtSampleLetter').removeClass('disabled');
            if($(this).val()){
                $('#drpPendingInvoice').prop('disabled',true);
                $('#createExtSampleLetter').addClass('disabled');
            }
        })
        $('#drpPendingInvoice').on('change',function(){
            $('#drpInvoice').prop('disabled',false);
            $('#list,#excel-list').removeClass('disabled');
            if($(this).val()){
                $('#drpInvoice').prop('disabled',true);
                $('#list,#excel-list').addClass('disabled');
            }
        })
        $('#createExtSampleLetter').on('click',function(){
            var pendingInv = $('#drpPendingInvoice').val();
            if(!pendingInv){
                alert('Please select Pending Invoice first.');
                return;
            }
            window.open(pendingInv, '_blank');
        })
        getInvNos();
        $(document).on('click', '#list', function (e) {
            e.preventDefault();

            var processor = $("#drpProcessor").val();
            var labNames = $("#drpLabNames").val();
            var fromDate = $("#txtFromDate").val();
            var toDate = $("#txtToDate").val();
            var Invoice = $("#drpInvoice").val();

            var link = 'Reports/D1/payment-statement-report.php?Processor=' + processor + '&LabNames=' + labNames + '&FromDate=' + fromDate + '&ToDate=' + toDate + '&Invoice=' + Invoice;
            window.open(link, '_blank');
        });
        $(document).on('click', '#excel-list', function (e) {
            e.preventDefault();

            var processor = $("#drpProcessor").val();
            var labNames = $("#drpLabNames").val();
            var fromDate = $("#txtFromDate").val();
            var toDate = $("#txtToDate").val();
            var Invoice = $("#drpInvoice").val();

            var link = 'Reports/D1/payment-statement-report-excel.php?Processor=' + processor + '&LabNames=' + labNames + '&FromDate=' + fromDate + '&ToDate=' + toDate + '&Invoice=' + Invoice;
            window.open(link, '_blank');
        });
    });
    function getInvNos(){
        var processor = $("#drpProcessor").val();
        var labNames = $("#drpLabNames").val();
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val(); 
        $.ajax({
                type: 'POST',
                dataType: 'json',
                data: {
                    'fromDate': fromDate,
                    'toDate': toDate,
                    'processor' : processor,
                    'labNames' : labNames,
                    'Request': 'GetInvNo'
                },
                success: function (res) {
                    var list = "<option value=''>Select</option>";
                    if(res.Data.length != 0){
                        $.each(res.Data, function (index, value) {
                            list += "<option value='"+value.PoID+"'>"+value.InvNumFull+"</option>";
                        });
                    }
                    $("#drpInvoice").html(list)
                }
            });
    }
</script>