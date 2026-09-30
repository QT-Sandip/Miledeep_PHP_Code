<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$PageTitle="Insurance Template";
$PageTitleFolder="Insurance";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
// include_once $_SERVER['DOCUMENT_ROOT'] . '/config/api-call.php';
include ($cp."Includes/IncludePage.php");
include ("RestAPI.php");


$uid = $_SESSION['UserID'];

$CurrentMonth=date('m');

if($CurrentMonth >3){	
$FinClsYear=$Currentyear+1;	
}else{
$FinClsYear=$Currentyear;
}

$api = GetInsuranceTemplatesList_API;
$bodyContent = "";        	

$resGetInsuranceTemplateInvoicesListAPI = CallAPI($api, "GET", "", $bodyContent);

$resList = $resGetInsuranceTemplateInvoicesListAPI->Data->InsuranceTemplatesList;

// $_SESSION["firstSubmit"] = "1";
if(isset($_POST['submit'])){
    $hdnInsuranceIDs = $_POST["hdnSelectedRow"];	

    $api = CreatePaymentsTemplate_API;
    $bodyContent = array(
        "InsuranceIDs"=>$hdnInsuranceIDs,
        "PostedUserId"=>$uid);

    // if($_SESSION["firstSubmit"] == "1") {

        $resCreatePaymentsTemplateAPI = CallAPI($api, "GET", "", $bodyContent);

        $resCreate = $resCreatePaymentsTemplateAPI->Data;

        if(!isset($resCreate->Error)) {
            $status = "Payment Template created successfully";
            $_SESSION["success"] = "T";
        }else{
            $error = "Error Occurred";
            $_SESSION["error"] = "T";
        } 
    //     $_SESSION["firstSubmit"] = "2";
    // } 

    header("Location: InsurancePayments");
}

?>
<!DOCTYPE html>

<html lang="en">


<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
    <head>        
        <?php include ($IncludesPath."title-meta.php"); ?>
        <?php include ($IncludesPath."DbLinks.php");?>
        <?php include ($IncludesPath."head-css.php");?>
    </head>

    <body data-sidebar="dark">
			<?php include ($IncludesPath."PreLoader.php"); ?>

        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php
                include ($IncludesPath."topbar.php");
                include ($IncludesPath."sidebar.php"); 
			?>
 <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">


                        <?php if($Status != ''){ 	//Show Alert  	?>
								<div class="alert alert-success alert-dismissible fade show" role="alert">
									<i class="mdi mdi-check-all mr-2"></i>
										<?php 
											echo $Status;
											$_SESSION["success"] = '';	  
										?>
									<button type="button" class="close" data-dismiss="alert" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
						<?php } ?>

                        <?php if($error != ''){ 	//Show Alert  	?>
								<div class="alert alert-danger alert-dismissible fade show" role="alert">
									<i class="mdi mdi-check-all mr-2"></i>
										<?php 
											echo $Status;
											$_SESSION["error"] = '';	  
										?>
									<button type="button" class="close" data-dismiss="alert" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
						<?php } ?>

                        <?php include ($IncludesPath."page-title.php"); ?>	
                        
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
                                                        <option <?php  if($Startyear == $FinClsYear) { echo 'selected="selected"'; } ?>
                                                                value="<?php echo $Startyear-1 ?>"> <?php echo $year ?></option>                                                         
                                                            <?php }   ?>
                                                <select>
                                            </div>

                                            <div  class="col-lg-4 text-left">       
                                                <label>&nbsp;</label>      
                                                <form id="frmCreatePaymentTemplate" method="POST">
                                                <button id="btnSearch" type="button" class="btn btn-primary waves-effect waves-light text-left"> Search <i class="ri-arrow-right-line align-middle ml-2"></i></button>													
                                                    <input type="hidden" id="hdnSelectedRow" name="hdnSelectedRow" />
                                                    <button id="btnSave" name="submit" disabled type="submit"  class="btn btn-success waves-effect waves-light text-left"> 
                                                        <i class="fa fa-plus"></i> Save 
                                                    </button>                                                
                                                </form>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
 
                        <div class="row">
                            <div class="col-12">


                                    <div id="divInsPayments">
                                        <!-- -->
                                    </div>



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


<div class="modal fade" id="ModalTempIns" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Insurance Payment</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form role="form" id="frmInsurancePayment" method="post">
		<div class="modal-body">      
			<div class="showInsList">
			</div>	
		</div>
	  </form>
    </div>
  </div>
</div>
        

<?php include ($IncludesPath."vendor-scripts.php");?>
<?php include ($IncludesPath."NewScripts.php");?>
<?php //include ($IncludesPath."ShowDataTable.php");?>

       
    </body>
</html>				

<script>
$('input#btnSave').on('click', function () {   
    $(this).prop('disabled', true);   
    $("#frmCreatePaymentTemplate").submit();   
});
// var mySubmitted = false;

// function MyCheckSubmit()
// {
//     if (mySubmitted)
//         return false; // Already submitted!

//     // if (!validateInquiryForm())
//     //     return false; // Validation failed!

//     // disable submit buttons

//     document.getElementById('btnSave').disabled=true;
//     document.getElementById('btnSave').value='Submitting, please wait...';

//     // Store in our global variable that we submitted this form.
//     mySubmitted = true;

//     return true; // Ok, we can submit!
// }
</script>

<script>


function fnPendingPayments() {
    var table = $('#pendingPayments').DataTable({
        scrollY: "600px",
        scrollX:true,
        sScrollXInner: "150%", 
        paging:true,
        destroy: true,
        fixedColumns: true,
        scrollCollapse: true,  // Make the list collapse to max-height applied.
        bSort : true,
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
        columnDefs: [ {
            "targets": 0,
            "orderable": false
        }],
        "bStateSave": true,
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    });

}
function fnPaymentTemplates() {

    var tableInvoice = $('#paymentTemplates').DataTable({
        bSort : true,
        destroy: true,
        scrollY: "400px",
        scrollX:true,
        sScrollXInner: "150%", 
        scrollCollapse: true,  // Make the list collapse to max-height applied.
        paging:false,
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
        fixedColumns: true,
        "bStateSave": true,
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    });
    
}

function fnShowPageDate() {
    var year = $("#year").val();
    $.ajax({
        url:'Ajax/AjaxInsurancePayments.php',
        type:'POST',
        data:{'Year':year},
        success:function(data){
            $("#divInsPayments").html(data);	
            fnPaymentTemplates();
            fnPendingPayments();
        }
    });
}

fnShowPageDate();

$(document).on("click", "#btnSearch", function(){
    fnShowPageDate();
});

var tableInvoiceList ;
$(function(){

	$(document).on("click", ".btnViewInvoices", function(){
        var insID=$(this).attr("data-insID"); 		
        console.log(insID);
        $.ajax({
            url:'Ajax/AjaxInsurancePaymentList.php',
            type:'POST',
            data:{"InsuranceID":insID},
            // {"BillsType":"0","Year":"2023","Exporter":"1","Consignee":"","Country":""}
            success:function(data){
                $(".showInsList").html(data);
                var totalAmountUSD = $("#totalAmountUSD").val();
                var exRate = $("#exRate").val();

                var totalAmountUSD = parseFloat(totalAmountUSD == "" ? 0 : totalAmountUSD );
                var exRate = parseFloat(exRate == "" ? 0 : exRate );

                var total = parseFloat(totalAmountUSD * exRate).toFixed(2);
                $("#totalAmountRs").val(total);

                tableInvoiceList = $('#tblData').DataTable({
                    bSort : true,
                    destroy: true,
                    scrollY: "400px",
                    scrollX:true,
                    sScrollXInner: "150%", 
                    scrollCollapse: true,  // Make the list collapse to max-height applied.
                    paging:false,
                    fixedColumns: true,
                    "bStateSave": true,
                    "fnStateSave": function (oSettings, oData) {
                        localStorage.setItem('key-datatable', JSON.stringify(oData));
                    },
                    "fnStateLoad": function (oSettings) {
                        return JSON.parse(localStorage.getItem('key-datatable'));
                    }
                });
            }
        });	
    });

    $('#ModalTempIns').on('shown.bs.modal', function (e) {
        // $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        // tableInvoiceList.columns.adjust();
            tableInvoiceList.columns.adjust().draw();
    });


    $(document).on("click", ".btnDeletePaymentTemp", function() {
        var id = $(this).attr("data-insID");
        var text = "Are you sure you want to delete list.";

        if (confirm(text) == true) {
            $.ajax({
                url:'Ajax/AjaxInsuranceTemplateFunction.php',
                type:'POST',
                data:{"InsurancePaymentID":id, "PaymentDeleteOperation": "T"},
                success:function(data){
                    if(data.res = "success"){
                        fnShowPageDate();
                    }else{
                        conosole.log("Some Error");
                    }
                }
            });	
        } else {
            
        }
    });    

    $(document).on("keyup", "#exRate", function() {
        var totalAmountUSD = parseFloat($("#totalAmountUSD").val() == "" ? 0 : $("#totalAmountUSD").val() );
        var exRate = parseFloat($(this).val() == "" ? 0 : $(this).val() );

        var total = parseFloat(totalAmountUSD * exRate).toFixed(2);
        $("#totalAmountRs").val(total);
    })

});


function checkOptions() {
	var selectedOptions = new Array();
	$("input.rowCheck:checked").each(function () {
		selectedOptions.push(this.value);
	});
	// console.log("count - "+selectedOptions.length);
	// console.log(selectedOptions.join(","));
	$('#hdnSelectedRow').val(selectedOptions.join(","));
	if(selectedOptions.length > 0) {
		$("#btnSave").prop("disabled", false);
	}else{
		$("#btnSave").prop("disabled", true);
	}
}

$(document).on("click", "input.rowCheck", function () {
	checkOptions();
	$("#checkall").prop("checked", false);	
});

$(document).on("click", "#checkall", function () {
	if (this.checked) {
		$(".rowCheck").prop("checked", true);		
		checkOptions();
	} else {
		$(".rowCheck").prop("checked", false);		
		checkOptions();
	}	
//    $(".rowCheck").prop('checked', $(this).prop('checked'));
});

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>

<script>
    jQuery.validator.addMethod('selectcheck', function (value) {
        return (value != '0' || value != '');
    }, "year required");

    jQuery.validator.addMethod("noSpace", function(value, element) { 
        return value == '' || value.trim().length != 0;  
    }, "No space please and don't leave it empty");

$("#frmInsurancePayment").validate({
    onkeyup: false,
    onfocusout: false,
    invalidHandler: function (form, validator) {
        var errors = validator.numberOfInvalids();
        if (errors) {
            var firstInvalidElement = $(validator.errorList[0].element);
            //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
            firstInvalidElement.focus();
        }
    },
    ignore: "",
    rules: {
        ourRef: { required: true, noSpace: true },
        totalAmountUSD: { required: true, noSpace: true },
        exRate: { required: true, noSpace: true, min: 1 },
        totalAmountRs: { required: true, noSpace: true },
        paymentDate: { required: true, noSpace: true },
        refNo: { required: true, noSpace: true },
        remarks: { required: true, noSpace: true }
    },
    errorPlacement: function () {
        return false;  // suppresses error message text
    },
    submitHandler: function () {
        
        var usesID = "<?php echo $uid ?>";
        var id = $("#btnSaveTemplatePayment").attr("data-insuranceid");
        var ourRef = $("#ourRef").val();
        // var totalAmountUSD = $("#totalAmountUSD").val();
        var exRate = $("#exRate").val();
        var totalAmountRs = $("#totalAmountRs").val();
        var paymentDate = $("#paymentDate").val();
        var refNo = $("#refNo").val();
        var remarks = $("#remarks").val();

            $.ajax({
                url:'Ajax/AjaxInsuranceTemplateFunction.php',
                type:'POST',
                data:{
                    "InsurancePaymentID":id, 
                    "OurReferenceNo":ourRef,
                    "ReferenceNo":refNo,
                    "PaymentDate":paymentDate,
                    "Amount":totalAmountRs,
                    "PostedUserId":usesID,
                    "Remarks":remarks,
                    "PaymentInsExchangeRate":exRate,
                    "SavePaymentIns": "T",
                    "DebitNote": $("#DebitNote").val()
                },
                success:function(data){
               //alert(data);

                     if(data.trim() == 'success'){
                     
                        alert("Data saved successfully");
                        fnShowPageDate();
                           $('#ModalTempIns').modal('toggle');
                    }else{
                        alert("Some error occurred.");

                        // alert("Data saved successfully");
                        // fnShowPageDate();
                        //    $('#ModalTempIns').modal('toggle');
                    }
                }
            });	

        return false;
    }
});

</script>


