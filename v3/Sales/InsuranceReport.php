<?php 

//include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
// include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
// include_once $_SERVER['DOCUMENT_ROOT'] . '/config/api-call.php';

// include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
// include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

// include ($cp."Includes/IncludePage.php");
// include ("RestAPI.php");



include_once ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include_once ($_SERVER['DOCUMENT_ROOT']."/SalesNew/Constants.php");
$ClientConfigData = $_SESSION['ClientConfigData'];
$EnableCertificationNoAndDebitNote = !empty($ClientConfigData['EnableCertificationNoAndDebitNote']) ? trim($ClientConfigData['EnableCertificationNoAndDebitNote']):0; //anil
//1. Insurance Type Chnaged Rejection to Transit & rejection, 2. Enable Add/Edit Certification Number, 3. Dispaly Consignee Name at Insurane Payments, 4. Add new field i.e. Debit Note in Insurance Payment Form.
$RejectionLable = 'Rejection Insurance';
if(!empty($EnableCertificationNoAndDebitNote)){
    $RejectionLable = 'Transit & Rejection Insurance';
}
include_once ("RestAPI.php");

$PageTitle="Pending Insurance";
$PageTitleFolder="Sales";

$uid=$_SESSION['r_id'];
$PostedUserId = $_SESSION['UserID'];

// echo $PostedUserId;
//echo $href;exit;
 $CurrentMonth=date('m');	
//echo $endYear;exit;

if($CurrentMonth >3){	
$FinClsYear=$Currentyear+1;	
}else{
$FinClsYear=$Currentyear;
}
 //echo $endYear;exit;

$SqlExporter="SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1";
$StmtExporter=$iconn->query($SqlExporter);

$SqlConsignee="SELECT * FROM `ScrmConsigneeDetails`";
$StmtConsignee=$iconn->query($SqlConsignee);

$SqlCountries="SELECT * FROM `ScrmCountriesList`";
$StmtCountries=$iconn->query($SqlCountries);

$ClientID = $_SESSION['ClientID'];
$RejectionValue = ($ClientID == 'CAPLV3') ? '0.5' : '0.4';
$TransitValue = ($ClientID == 'CAPLV3') ? '0.1' : '0.08';

if(isset($_POST['submit'])){

    
    $hdnSelectedRow = $_POST["hdnSelectedRow"];
	$insType = $_POST["insType"];
    
	$api = CreateInsuranceTemplate_API;
    // echo $api;
    // exit();
	
	$postBodyContent = array(
			// 'BillsType'=>"0",
            "PoIDs"=>$hdnSelectedRow,
            "InsuranceType"=>$insType,
            "RejectionValue"=>$RejectionValue,
            "TransitValue"=>$TransitValue,
            "PostedUserId"=>$PostedUserId);

	$resCreateInsuranceTemplate = CallAPI($api, "POST", "", $postBodyContent);
	
	$resCreateInsType = $resCreateInsuranceTemplate->Data;

	// var_dump($resCreateInsType);

	if(isset($resCreateInsType->Msg)) {
		$_SESSION['Status'] = "T";
	}
	if(isset($resCreateInsType->Error)) {
		$_SESSION['Error'] = "T";
	}
	header("location: InsuranceReport");
	exit();
}

$ClientConfigDataObj = (object) $_SESSION['ClientConfigData'];
$DefaultExporterID = $ClientConfigDataObj->DefaultExporterID;

?>

<!doctype html>
<html lang="en">

<link href="<?php echo  $href; ?>" rel="stylesheet" type="text/css" />


<head>


    <?php include ($IncludesPath."title-meta.php"); ?>

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
    <?php include ($IncludesPath."head-css.php");?>

</head>

<!-- <body data-sidebar="dark" class="sidebar-enable vertical-collpsed">-->

<body data-sidebar="dark" class="sidebar-enable">

    <?php include ($IncludesPath."PreLoader.php"); ?>



    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php

        //echo $IncludesPath; exit;
        include ($IncludesPath."topbar.php");
        include ($IncludesPath."sidebar.php"); 
			?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php include ($IncludesPath."page-title.php");?>
                    <?php include "PopUpTable.php"; ?>


                    <?php if(isset($_SESSION['Status']) && !empty($_SESSION['Status'])) { ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-all mr-2"></i>
                        Insurance Template created successfully
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php $_SESSION['Status'] = '';	  ?>
                    <?php } ?>

                    <?php if(isset($_SESSION['Error']) && !empty($_SESSION['Error'])) { ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-all mr-2"></i>
                        Some error has occurred.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php $_SESSION['Error'] = ''; ?>
                    <?php } ?>


                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">




                                    <div class="row">
                                        <div class="form-group col-lg-3">
                                            <label>Select Year: </label>
                                            <select class="form-control select2" name="year" id="year">
                                                <?php	 						
								for($Startyear; $Startyear <= $endYear; $Startyear++) { 						
										$year=($Startyear-1).'-'.($Startyear); 
							?>
                                                <option
                                                    <?php  if($Startyear == $FinClsYear) { echo 'selected="selected"'; } ?>
                                                    value="<?php echo $Startyear-1 ?>"> <?php echo $year ?></option>
                                                <?php }   ?>
                                                <select>
                                        </div>

                                        <div class="col-lg-3">
                                            <label>Select Exporter: </label>
                                            <select class="form-control select2" name="drpExporter" id="drpExporter">
                                                <option value="">All</option>
                                                <?php while($row_add=$StmtExporter->fetch_array()) { 
                                                $selected = !empty($DefaultExporterID) && ($DefaultExporterID == $row_add['ExpID']) ? 'selected' : '' ; ?>
                                                <option Value="<?php echo $row_add['ExpID'];?>" <?php echo $selected;?>>
                                                    <?php echo $row_add['ExpName'];?></option>
                                                <?php } ?>
                                                <select>
                                        </div>
                                        <div class="col-lg-3">
                                            <label>Select Consignee: </label>
                                            <select class="form-control select2" name="consignee" id="consignee">
                                                <option value="0">All</option>
                                                <?php while($RowConsignee=$StmtConsignee->fetch_array()) {  ?>
                                                <option Value="<?php echo $RowConsignee['ConID'];?>">
                                                    <?php echo $RowConsignee['ConsigneeName']; ?></options>
                                                    <?php } ?>
                                                    <select>
                                        </div>

                                        <div class="col-lg-3">
                                            <label>Select Country: </label>
                                            <select class="form-control select2" name="Country" id="Country">
                                                <option value=" ">All</option>
                                                <?php while($RowCountries=$StmtCountries->fetch_array()) { ?>
                                                <option Value="<?php echo $RowCountries['id'];?>">
                                                    <?php echo $RowCountries['name'];?></option>
                                                <?php } ?>
                                                <select>
                                        </div>
                                        <!-- <div class="col-lg-2">
                    <label>Invoice Status</label>				
					<select class="form-control select2" name="InvType" id="InvType">			 
						<option Value="0">Pending</option>
						<option Value="1">Completed</option>				
					</select> 				
				</div>					 -->

                                    </div>
                                    <div class="row">

                                        <div class="col-lg-12 text-center">
                                            <button id="list" type="button"
                                                class="btn btn-primary waves-effect waves-light text-left"> Search <i
                                                    class="ri-arrow-right-line align-middle ml-2"></i></button>
                                            <button id="btnSave" disabled type="button" data-toggle="modal"
                                                data-target="#ModalInsType"
                                                class="btn btn-success waves-effect waves-light text-left"> <i
                                                    class="fa fa-plus"></i> Save </button>

                                        </div>
                                    </div>

                                </div>


                                <!--     <a href="EmployeeCreate.php" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New Employee</a>-->
                                <!-- end row -->
                            </div>
                        </div>
                    </div>

                    <!-- end col -->


                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="PIList"></div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end row -->

                <!-- end row-->


            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
    </div>
    <!-- end row-->

    </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <?php include ($IncludesPath."footer.php");?>

    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->


    <div class="modal fade" id="ModalInsType" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Create Insurance Template</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <!--<form role="form" id="frmTransitType" onsubmit="return validateForm()" method="post">-->
                <form role="form" id="frmTransitType"  method="post">
                    <div class="modal-body">
                        <div class="">
                            <label>Insurance Type</label>
                            <select class="form-control select2" name="insType" id="insType">
                                <option value="TRANSIT">Transit Insurance </option>
                                <option value="REJECT"><?php echo $RejectionLable;?> </option>
                            </select>
                            <input type="hidden" id="hdnSelectedRow" name="hdnSelectedRow" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        <button type="submit" name='submit' class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php 
		if(!empty($EnableCertificationNoAndDebitNote)){?>
            <div class="modal fade" id="ModalAddCertification" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="CertificationModalLabel">Add / Edit Certification Number</h5>
                            <button type="button" class="close CertificateClose" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form role="form" id="frmCertification"  method="post">
                            <div class="modal-body">
                                <div class="">
                                    <label>Certification Number</label>
                                    <input type="text" class="form-control group" id="CertificationNumber" name="CertificationNumber"
                                                    value="" />
                                    <input type="hidden" id="InvID" name="InvID" />
                                    <input type="hidden" id="CertificationID" name="CertificationID" />
                                    <input type="hidden" id="UserId" name="UserId" value="<?php echo !empty($_SESSION['UserID']) ? $_SESSION['UserID'] : ''; ?>"/>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger CertificateClose" data-dismiss="modal">Close</button>
                                <button type="button" name='submit' class="btn btn-primary storeCertificateNumber">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    <?php }?>


    <?php include ($IncludesPath."vendor-scripts.php");?>


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
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js">
    </script>

    <!-- Datatable init js -->
    <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

    <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>

</body>




<script>
$(function() {
    $('#list').click(function() {
        getPiList();
    });
});

function getPiList() {
    var exporter = $('#drpExporter').val();
    var YEAR = $('#year').val();
    // var InvType=$('#InvType').val();
    var consignee = $('#consignee').val();
    var Country = $('#Country').val();
    $.ajax({
        url: 'Ajax/AjaxInsuranceReportList.php',
        type: 'POST',
        data: {
            "Year": YEAR,
            "Exporter": exporter,
            "Consignee": consignee,
            "Country": Country
        },
        // {"BillsType":"0","Year":"2023","Exporter":"1","Consignee":"","Country":""}
        success: function(data) {
            showData(data);
        }
    });
}
$(document).ajaxStart(function() {
    $("#wait").css("display", "block");
});
$(document).ajaxComplete(function() {
    $("#wait").css("display", "none");
});

function showData(data) {
    $("#PIList").html(data);
    var table = $('#key-datatable').DataTable({
        scrollY: "600px",
        scrollX: true,
        //sScrollX: "100%",
        sScrollXInner: "150%",
        // scrollCollapse:true,
        paging: true,
        destroy: true,
        fixedColumns: true,
        columnDefs: [{
            searchable: false,
            orderable: false,
            targets: 0
        }],
        bSort: true,
        language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
            buttons:[],
        "bStateSave": true,
        "fnStateSave": function(oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function(oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    });
}

function checkOptions(unCheckVal) {
    var selectedOptions = new Array();
    $("input.rowCheck:checked").each(function() {

        selectedOptions.push(this.value);
    });

    var hdnSelectedVal = $('#hdnSelectedRow').val();
    if (hdnSelectedVal != "") {
        var sArray = hdnSelectedVal.split(",");
        var combinedArray = sArray.concat(selectedOptions);

        var uniqueArray = [];
        $.each(combinedArray, function(index, value) {
            if ($.inArray(value, uniqueArray) === -1) {
                uniqueArray.push(value);
            }
        });

        if (unCheckVal) {
            uniqueArray.splice(uniqueArray.indexOf(unCheckVal), 1);
        }

        var joinedArray = uniqueArray.join(",");

        $('#hdnSelectedRow').val(joinedArray);
    } else {
        $('#hdnSelectedRow').val(selectedOptions.join(","));
    }


    // $('#hdnSelectedRow').val(joinedArray);
    if (selectedOptions.length > 0) {
        $("#btnSave").prop("disabled", false);
    } else {
        $("#btnSave").prop("disabled", true);
    }
}

$(document).on("click", "input.rowCheck", function() {

    console.log($(this).val());
    var isChecked = $(this).prop('checked');

    if (isChecked) {
        checkOptions();
    } else {
        checkOptions($(this).val());
    }




    $("#checkall").prop("checked", false);
});

$(document).on("click", "#checkall", function() {
    if (this.checked) {
        $(".rowCheck").prop("checked", true);
        checkOptions();
    } else {
        $(".rowCheck").prop("checked", false);
        checkOptions();
    }
    //    $(".rowCheck").prop('checked', $(this).prop('checked'));
});

$(document).on("click", "#btnSave", function() {

});
<?php 
	if(!empty($EnableCertificationNoAndDebitNote)){?>
        $(document).on("click", ".AddEditCertification", function() {
            $('#CertificationNumber').val($(this).data('certification_number'));
            $('#CertificationID').val($(this).data('certification_id'));
            $('#InvID').val($(this).data('inv_id'));
        });
        $(document).on("click", ".CertificateClose", function() {
            $('#CertificationNumber, #CertificationID, #InvID').val('');
        });
        $(document).on("click", ".storeCertificateNumber", function() {
            if(!$('#CertificationNumber').val()){
                alert('Please enter the Certificate Number first');
                return false;
            }
            $.ajax({
                url: 'Ajax/AjaxInsuranceReportList.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    CertificationNumber  : $('#CertificationNumber').val(),
                    InvID  : $('#InvID').val(),
                    CertificationID  : $('#CertificationID').val(),
                    UserId  : $('#UserId').val(),
                    MethodType  : 'AddEditCertificate',
                },
                success: function (data) {
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        $('.CertificateClose').trigger('click')
                        alert(data.Msg);
                        getPiList();
                    }
                    return false;
                }
            });

        })
<?php }?>
</script>