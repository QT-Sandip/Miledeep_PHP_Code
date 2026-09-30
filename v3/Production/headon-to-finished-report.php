<?php
$PageTitle = "Headon to finished report";
$PageTitleFolder = "Purchase";

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$SqlDeptList = "SELECT * FROM PpcVAStations";
$StmtDept = $iconn->query($SqlDeptList);

$today_date = date('Y-m-d');

$ClientConfigData = $_SESSION['ClientConfigData'];

// error_log(print_r($ClientConfigData,true));
// $IsVehicleTrack = $ClientConfigData->IsVehicleTrack;
// $IsPlantFilter = $ClientConfigData->IsPlantFilter;  //sriram
// $IsHONPacking = $ClientConfigData->IsHONPacking;
$IsVehicleTrack = $ClientConfigData['IsVehicleTrack'];
$IsPlantFilter = $ClientConfigData['IsPlantFilter'];  //sriram
$IsHONPacking = $ClientConfigData['IsHONPacking'];
$HeadOnFinishedFileName = $ClientConfigData['HeadOnFinishedFileName'];
$PlantID = $_SESSION['UnitID'];


$ArrivalID = trim($_GET['ArrivalID']);

$ArrivalID = isset($_GET['ArrivalID']) && !empty($_GET['ArrivalID']) ? $_GET['ArrivalID'] : "";

$api = API_PREPROCESSING_DROPDOWNS;

$bodyContent = array(
    'ArrivalID' => $ArrivalID
);

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resProductionPlant = $resDropdownsAPI->Data->Plant;


$CurrentMonth = date('n');
$CurrentYear = date('Y');

/* Financial Year starts in April */
if ($CurrentMonth >= 4) {
    $FYStartYear = $CurrentYear;
} else {
    $FYStartYear = $CurrentYear - 1;
}

$CurrentFY = $FYStartYear . '-' . substr($FYStartYear + 1, -2);

?>

<!doctype html>
<html lang="en">
<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

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
    <style>
        .select2-container--default.select2-container--disabled .select2-selection--single {
            background-color: #eee;
            cursor: default;
        }
    </style>

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

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpArrivalPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>
                                            <select class="select2 form-control" name="drpArrivalPlant"
                                                id="drpArrivalPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductionPlant as $Item) { ?>
                                                    <option value="<?php echo $Item->ProcessingStationID; ?>">
                                                        <?php echo $Item->FullName . ' - ' . $Item->StationName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-2 mb-3">
                                            <label for="drpYear">Year</label>
                                            <select class="form-control select2" id="drpYear" name="drpYear">
                                                <option value="">Select</option>
                                                <option value="<?php echo $FYStartYear - 1; ?>">
                                                    <?php echo ($FYStartYear - 1) . '-' . substr($FYStartYear, -2); ?>
                                                </option>
                                                <option value="<?php echo $FYStartYear; ?>" >
                                                    <?php echo $CurrentFY; ?>
                                                </option>
                                            </select>
                                        </div>


                                        <!-- <div class="form-group col-sm-3">
                                            <label for="exampleInputEmail1">Arrival Date</label>
                                            <input type="date" class="form-control" name='txtArrivalDate'
                                                id="txtArrivalDate" max="<?php echo $today_date; ?>" placeholder="">
                                        </div> -->
                                        <div class="form-group col-sm-2">
                                            <label for="txtFromDate">From Date</label>
                                            <input type="date" class="form-control" name='txtFromDate'
                                                id="txtFromDate" max="<?php echo $today_date; ?>" placeholder="">
                                        </div>
                                        <div class="form-group col-sm-2">
                                            <label for="txtToDate">To Date</label>
                                            <input type="date" class="form-control" name='txtToDate'
                                                id="txtToDate" max="<?php echo $today_date; ?>" placeholder="">
                                        </div>

                                        <div class="col-lg-2">
                                            <div class="form-group ">
                                                <label for="exampleInputEmail1">Batch No</label>
                                                <select class="form-control select2" name="PurchaseStationID"
                                                    id="PurchaseStationID">
                                                    <option value="">Select</option>

                                                    <?php //  while($DataRow=$StmtDept->fetch_array()){ ?>
                                                    <!-- <option value="<?php // echo $DataRow['StationID'] ; ?>" >
                                                                <?php // echo $DataRow['UnitName']; ?></option> 
                                                            <?php   // } ?> -->
                                                </select>
                                            </div>
                                        </div>

                                       

                                    </div>

                                    <!-- <div align="center" class="col-lg-12">
                                                     
                                                       <button id="list" align="right" type="button" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                                            </button>
                                                    </div> -->




                                 <div class="row">
                                    <div class="form-group col-sm-3 col-md-6 col-md-offset-6 text-right">
                                        <button id="list" type="button"
                                            class="btn btn-primary waves-effect waves-light">
                                            Print <i class="ri-arrow-right-line align-middle ml-2"></i>
                                        </button>
                                    </div>
                                </div>

                                </div>
                                <!-- end row -->
                            </div>
                        </div>
                    </div>

                    <!-- end col -->
                </div> <!-- end row -->
                <!--
                        <div class="container-fluid">                        
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body" id="ShowDataTable">
                                            

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->
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

    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>

</body>

</html>

<script>

    // $(document).on('change', '#drpYear', function () {
    //     $('#txtArrivalDate').val('').prop('disabled', true);
    // });

    // When Arrival Date is changed → disable Year
    $(document).on('change', '#txtArrivalDate', function () {
        $('#drpYear').prop('disabled', true);
    });

    $('document').ready(function () {
        getData();

        // $(document).on('click','#list',function(e){
        // 	e.preventDefault();

        // 	getData()
        // });

        // $('#drpArrivalPlant').select2();
    });


    function getData() {
        var productionPlant = <?php echo $PlantID ?>;
        var IsPlantFilter = <?php echo $IsPlantFilter ?>; // sriram
        if (productionPlant != 0 || IsPlantFilter == 0) {
            $("#drpArrivalPlant").prop('disabled', true);
            if (IsPlantFilter == 0) {
                $("#drpArrivalPlant").val("");
            } else {
                $("#drpArrivalPlant").val(productionPlant).trigger('change');

            }
        } else {
            $("#drpArrivalPlant").prop('disabled', false);
        }


        var PsId = $('#PurchaseStationID').val();
        var FromDate = $('#FromDate').val();
        var ToDate = $('#ToDate').val();


        $.ajax({
            url: 'Ajax/AjaxReportList',
            type: 'POST',
            data: { 'PsId': PsId, 'Month': FromDate, 'Year': ToDate, 'Type': 2 },
            success: function (data) {
                showData(data);

            }
        });

    }

</script>

<script>

    $(document).on('click', '#list', function (e) {
        e.preventDefault();
        var HeadOnFinishedFileName = '<?php echo $HeadOnFinishedFileName ?>';

        if ($("#PurchaseStationID").val() != "") {
           // var arrivaldate = $("#txtArrivalDate").val();
           var arrivaldate = $('#drpVariety option:selected').attr('data-arrivaldate');
            var plantid = $("#drpArrivalPlant").val();
            var arrivalid = $("#PurchaseStationID").val();

            var url = "/v3/Production/Reports/" + HeadOnFinishedFileName + ".php?arrivaldate=" + arrivaldate + "&datatype=getReportData&plantid=" + plantid + "&arrivalid=" + arrivalid;
            //var url = "/v3/Production/Reports/" + HeadOnFinishedFileName + ".php?datatype=getReportData&plantid=" + plantid + "&arrivalid=" + arrivalid;
            window.open(url, '_blank');

        }
         else {
            alert("Please select Batch no.");
        }
    });

    // $(document).on('change','#Month',function(e){			
    // 			e.preventDefault();		
    // 			getData();
    // });
    //not using this
    $(document).on('change', '#txtArrivalDate', function (e) {
        $.ajax({
            url: 'Ajax/AjaxReportList',
            type: 'POST',
            data: {
                'ArrivalDate': $(this).val(),  //2025-03-07
                'DataType': "getArrivals",
                'PlantID': $("#drpArrivalPlant").val(),
                'methodType': "dateChange"
            },
            success: function (data) {
              
                var list = "<option value=''>Select</option>";
                $.each(data.ArrivalsList, function (index, value) {
                    list += "<option value='" + value.RmArrivalID + "'>" + value.ArrivalNumber + "</option>";
                });

                $('#PurchaseStationID').select2('destroy');
                $('#PurchaseStationID').html(list);
                $('#PurchaseStationID').select2();

            }
        });
    });    

    $(document).on('change', '#drpYear', function () {
        let fyYear = $(this).val();
        let plantId = $('#drpArrivalPlant').val();

        $('#txtArrivalDate').val('').prop('disabled', true);

        $.ajax({
            url: 'Ajax/AjaxReportList',
            type: 'POST',
            data: {
                'methodType': 'yearChange',
                'DataType': "getArrivalsByFY",
                FYYear: fyYear,
                'PlantID': $("#drpArrivalPlant").val(),
            },
            success: function (data) {
                console.log(data);
                let list = "<option value=''>Select</option>";
            $.each(data.ArrivalsList, function (index, value) {
                        list += "<option value='" + value.RmArrivalID + "'  data-arrivaldate='" + value.ArrivalDate+"'>" + value.ArrivalNumber + "</option>";
                    });

                $('#PurchaseStationID').select2('destroy');
                $('#PurchaseStationID').html(list);
                $('#PurchaseStationID').select2();
            }
        });
    });
    //not using this*/ 
    $(document).on('change', '#txtFromDate,#txtToDate', function (e) {
        let txtFromDate = $('#txtFromDate').val();
        let txtToDate = $('#txtToDate').val();
        let plantId = $('#drpArrivalPlant').val();
        let fyYear = $('#drpYear').val();
        if(!txtFromDate){
            $('#drpYear').val('').prop('disabled', false);
            alert('Please Select From Date First.');
            return;
        }
        if(!txtToDate){
            $('#drpYear').val('').prop('disabled', false);
            alert('Please Select To Date First.');
            return;
        } 
        

        if (txtFromDate && txtToDate) {
            $('#drpYear').val('').prop('disabled', true);
        }
        $.ajax({
            url: 'Ajax/AjaxReportList',
            type: 'POST',
            data: {
                'methodType': 'DatesChange',
                'DataType': "getArrivalsByFY",
                FromDate: txtFromDate,
                ToDate: txtToDate,
                FYYear: fyYear,
                'PlantID': $("#drpArrivalPlant").val(),
            },
            success: function (data) {
                console.log(data);
                let list = "<option value=''>Select</option>";
            $.each(data.ArrivalsList, function (index, value) {
                        list += "<option value='" + value.RmArrivalID + "' data-arrivaldate='" + value.ArrivalDate+"'>" + value.ArrivalNumber + "</option>";
                    });

                $('#PurchaseStationID').select2('destroy');
                $('#PurchaseStationID').html(list);
                $('#PurchaseStationID').select2();
            }
        });
    })
    $(document).on("change", "#drpYear", function () {
        const yearVal = $(this).val();
        $("#txtFromDate,#txtToDate").prop('readonly',false).val('');
        if (yearVal) {
            let startYear, endYear;

            if (yearVal.includes('-')) {
                const parts = yearVal.split('-');
                startYear = parseInt(parts[0]);
                endYear = parseInt('20' + parts[1]);
            } else {
                startYear = parseInt(yearVal);
                endYear = startYear + 1;
            }

            const fromDate = `${startYear}-04-01`;
            const toDate = `${endYear}-03-31`;

            $("#txtFromDate").val(fromDate).prop('readonly',true);
            $("#txtToDate").val(toDate).prop('readonly',true);;
        }
    });



</script>
