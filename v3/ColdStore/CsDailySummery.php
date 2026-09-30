<?php

session_start();

$PageTitle = "Production Summary";
$PageTitleFolder = "ColdStore";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$StartDate = date("Y-m") . '-01';
$PurDate = date("Y-m-d");
$PurchaseDate = date('d-m-Y', strtotime($PurDate));

$UnitID = $_SESSION['UnitID'];
$AccessType = $_SESSION['AccessType'];

if ($AccessType != 1) {
    //$SQry="AND ProcessingStationID=$UnitID";

}

$SqlColdstore = "SELECT * FROM `CsStores` WHERE StoreStatus=1 $SQry";
$StmtColdstore = $iconn->query($SqlColdstore);

$SqlTenant = "SELECT * FROM `CsTenant`  WHERE TenantStatus=1 ";
$StmtTenant = $iconn->query($SqlTenant);

$SqlOptions = "SELECT * FROM `AdminGeneralOptions` WHERE  DeptID='CS' ORDER BY OptID asc";
$StmtOptions = $iconn->query($SqlOptions);
while ($RowOptions = $StmtOptions->FETCH_ARRAY()) {
    $AryOptions[] = $RowOptions;
}

$ProductionPlant = $_SESSION['UnitID'];

?>

<!doctype html>
<html lang="en">


<head>

    <?php include($IncludesPath . "title-meta.php"); ?>

    <!-- DataTables -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css" rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />


    <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" />
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

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">

                                    <div class="row">
                                        <div class="form-group col-lg-3">
                                            <label for="exampleInputEmail1">Select FromDate: </label>
                                            <input type='date' value='<?php echo $StartDate; ?>' class="form-control" name="FromDate" id="FromDate"></input>
                                        </div>

                                        <div class="form-group col-lg-3">
                                            <label for="exampleInputEmail1">Select To Date: </label>
                                            <input type='date' value='<?php echo $PurDate; ?>' class="form-control" name="ToDate" id="ToDate"></input>
                                        </div>


                                        <!-- <div class="form-group col-lg-2" >              
                  <label for="exampleInputEmail1">Unit</label>
				
               <select class="form-control select2" name="Station" id="Station">
			     <option value="BOTH">ALL</option>
			   <?php /*foreach($AryOptions as $value){
					if($value['OptionType']=='CS_STATIONS'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['OptID'];
			   ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}*/ ?>
					
				<select>
                </div>-->

                                        <div class="form-group col-sm-3">
                                            <label for="exampleInputEmail1">ColdStores</label>
                                            <select required class="form-control select2" name="ColdstoreID" id="ColdstoreID">
                                                <option value="">ALL</option>
                                                <?php while ($RowColdstore = $StmtColdstore->fetch_array()) { ?>
                                                    <option value="<?php echo $RowColdstore['StoreID'] ?>" data-plant="<?php echo $RowColdstore['ProductionPlantID'] ?>">
                                                        <?php echo $RowColdstore['StoreName'] ?></option>

                                                <?php  } ?>

                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="exampleInputEmail1">Stock belongs to</label>
                                            <select required class="form-control select2" name="TenantID" id="TenantID">
                                                <option value="BOTH">All </option>
                                                <?php while ($RowTenant = $StmtTenant->fetch_array()) { ?>
                                                    <option value="<?php echo $RowTenant['TenantID'] ?>" data-processorID="<?php echo $RowTenant['ProcessorID'] ?>">
                                                        <?php echo $RowTenant['TenantName'] ?></option>

                                                <?php  } ?>


                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="exampleInputPassword1">Packing Type</label>
                                            <select class="form-control select2" name="PackData" id="PackData">
                                                <option value="BOTH">ALL</option>
                                                <?php foreach ($AryOptions as $value) {
                                                    if ($value['OptionType'] == 'PACKING_TYPE') {
                                                        $OPTIONS = $value['Name'];
                                                        $VALUE = $value['Value']; ?>

                                                        <option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
                                                <?php  }
                                                } ?>


                                                <select>
                                        </div>


                                        <div class="form-group col-sm-3">
                                            <label for="exampleInputPassword1">Freezing Type</label>
                                            <select class="form-control select2" name="FreezingType" id="FreezingType">
                                                <option value='BOTH'>All</option>

                                                <?php foreach ($AryOptions as $value) {
                                                    if ($value['OptionType'] == 'FREEZING') {
                                                        $OPTIONS = $value['Name'];
                                                        $VALUE = $value['Value']; ?>

                                                        <option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
                                                <?php  }
                                                } ?>

                                            </select>

                                        </div>


                                    </div>

                                    <div align="center" class="col-lg-12">

                                        <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
                                            Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                        </button>

                                    </div>





                                </div>
                                <!-- end row -->
                            </div>
                        </div>
                    </div>

                    <!-- end col -->
                </div> <!-- end row -->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="EmpList">


                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div>
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
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

    <!-- Datatable init js -->
    <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

    <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>

</body>

</html>

<script>
    $(document).ready(function() {

        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#ColdstoreID").prop('disabled', true);
            $("#ColdstoreID").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');

        } else {
            $("#ColdstoreID").prop('disabled', false);
            $("#ColdstoreID").val("").trigger('change');
        }

        if (productionPlant != 0) {
            console.log('productionPlant' + productionPlant);
            $("#TenantID").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
        } else {
            $("#TenantID").val("").trigger('change');
        }
    });
    $(document).on('click', '#list', function(e) {
        e.preventDefault();

        var FromDate = $('#FromDate').val();
        var ToDate = $('#ToDate').val();
        var Station = $('#Station').val();
        var PackData = $('#PackData').val();
        var FreezingType = $('#FreezingType').val();
        var ColdstoreID = $('#ColdstoreID').val();
        var TenantID = $('#TenantID').val();


        $("#print").show();

        $(document).on('click', '#print', function(e) {
            e.preventDefault();

            var FromDate = $('#FromDate').val();
            var ToDate = $('#ToDate').val();
            var Station = $('#Station').val();
            var PackData = $('#PackData').val();
            var FreezingType = $('#FreezingType').val();
            var ColdstoreID = $('#ColdstoreID').val();
            var TenantID = $('#TenantID').val();

            window.open("Reports/CsSummmaryReportPrint.php?FromDate=" + FromDate + "&ToDate=" + ToDate + "&PackData=" + PackData + "&FreezingType=" + FreezingType + "&ColdstoreID=" +
                ColdstoreID + "&TenantID=" + TenantID, "_blank");


        });

        $.ajax({
            url: 'Ajax/AjaxSummeryReport.php',
            type: 'POST',
            data: {
                'FromDate': FromDate,
                'ToDate': ToDate,
                'Station': Station,
                'PackData': PackData,
                'FreezingType': FreezingType,
                'TenantID': TenantID,
                'ColdstoreID': ColdstoreID
            },
            success: function(data) {

                showData(data);


            }
        })

    });
</script>

<script>
    function showData(data) {

        $("#EmpList").html(data);

        var table = $('#key-datatable').DataTable({
            scrollY: "600px",
            scrollX: true,
            //sScrollX: "100%",
            sScrollXInner: "150%",
            // scrollCollapse:true,
            paging: true,
            destroy: true,
            fixedColumns: true,
            bSort: true,
            order: [
                [1, 'desc']
            ],
            "bStateSave": true,
            "fnStateSave": function(oSettings, oData) {
                localStorage.setItem('key-datatable', JSON.stringify(oData));
            },
            "fnStateLoad": function(oSettings) {
                return JSON.parse(localStorage.getItem('key-datatable'));
            }
        });



    }
</script>