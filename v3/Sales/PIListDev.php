<style>

td{
    overflow:hidden;
    text-overflow: ellipsis;
}
</style>

<?php 
	$PageTitle="Proforma Invoice List";
	$PageTitleFolder="Sales";

	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	include ($cp."Includes/IncludePage.php");

	//include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

	$AccessType=$_SESSION["AccessType"]; 
	$uid=$_SESSION['r_id'];
	$CurrentMonth=date('m');	

	if($_GET['Year']==''){
		$SelectedYear='';
	}else{
		$SelectedYear=$_SESSION["SelectedYear"];
	}
	//echo $_SESSION["SelectedYear"];
	if($CurrentMonth > 3){	
		$FinClsYear=$Currentyear+1;	
	}else{
		$FinClsYear=$Currentyear;
	}

	if($SelectedYear!=''){		
		if($CurrentMonth > 3){	
			$FinClsYear=$SelectedYear+1;	
		}else{
			$FinClsYear=$SelectedYear;
		}
	}
	$SqlExporter="SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1 ";
	$StmtExporter=$iconn->query($SqlExporter);

	$SqlConsignee="SELECT * FROM `ScrmConsigneeDetails`";
	$StmtConsignee=$iconn->query($SqlConsignee);

	$SqlCountries="SELECT * FROM `ScrmCountriesList`";
	$StmtCountries=$iconn->query($SqlCountries);

?>

<!doctype html>
<html lang="en">

<link href="https://serp.silverlights.in/Clients/Sme/User.css"  rel="stylesheet" type="text/css" />

    <head>        
		<?php include ($IncludesPath."title-meta.php"); ?>

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
        <?php include ($IncludesPath."head-css.php");?>

    </head>

    <!--<body data-sidebar="dark" class="sidebar-enable vertical-collpsed">-->
    <body data-sidebar="dark" class="sidebar-enable">
	
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

                        <?php include ($IncludesPath."page-title.php");?>
						<?php include "PopUpTable.php"; ?>
						
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
									
										
	  
				  	
	<div class="row">
    			
		<div class="form-group col-lg-3">
			<label>Select Year: </label>
			<select class="form-control select2" name="YEAR" id="YEAR">
			<?php
				for($Startyear; $Startyear <= $endYear; $Startyear++) { 
				$year=($Startyear-1).'-'.($Startyear); ?>
				<option  <?php  if($Startyear == $FinClsYear){ ?> selected="selected"  <?php   }   	?>			
						value="<?php echo $Startyear-1 ?>"><?php echo $year ?></option>
				<?php }   ?>
			<select>
		</div>

		<div class="col-lg-3">
			<label>Select Exporter: </label>	
			<select class="form-control select2" name="drpExporter" id="drpExporter">	 
				<?php while($row_add=$StmtExporter->fetch_array()) { ?>  				
				<option Value="<?php echo $row_add['ExpID'];?>"><?php echo $row_add['ExpName'];?></option>				
				<?php } ?>
				<option value=" " >All</option>
			<select> 				
		</div>
		<div class="col-lg-3">
			<label>Select Consignee: </label>				
			<select class="form-control select2" name="Buyer" id="Buyer">
				<option value=" " >All</option>
				<?php while($RowConsignee=$StmtConsignee->fetch_array()) { ?>  
				<option Value="<?php echo $RowConsignee['ConID'];?>"><?php echo $RowConsignee['ConsigneeName'].' - '.$RowConsignee['ConsigneeCountry'];?></OPTION>				
				<?php } ?>
			<select> 				
		</div>					
		<div class="col-lg-3">
			<label>Select Country: </label>
			<select class="form-control select2" name="Country" id="Country">
				<option value=" " >All</option>
				<?php while($RowCountries=$StmtCountries->fetch_array()) { ?>  
				<option Value="<?php echo $RowCountries['id'];?>"><?php echo $RowCountries['name'];?></OPTION>
				<?php } ?>
			<select> 
		</div>
	</div>				  
	<div class="col-lg-12 text-center">                                                

		<button id="list" type="button" class="btn btn-primary waves-effect waves-light text-left">
			Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
		</button>
		<a href="PIGeneration.php" target="_blank" class="btn btn-success waves-effect waves-light"> <i class="fa fa-plus"></i> New PI </a>				
		<?php if($AccessType==1){ ?>
		<!-- <button id="BtnPopUpBulk" align="left" type="button" class="btn btn-primary waves-effect waves-light">
			Bulk Approval <i class="ri-arrow-right-line align-middle ml-2"></i> 
			</button>-->
			<button  class="btn btn-warning waves-effect waves-light" data-toggle="popover" data-trigger="focus" 
					data-id="<?php echo $row['PoID'];?> " id="BtnPopUpBulk" value=" <?php echo $row['PoID']?>"> Bulk Approval </button>			
		<?php }?>				
	</div> 
    <!-- <a href="EmployeeCreate.php" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New Employee</a>--> 									 
</div>
                        <!-- end row -->
							</div>
							</div>
							</div>
        
                                         <!-- end col -->
                        </div> <!-- end row -->

                        <div class="row gridList" style="display: none;"> 
                            <div class="col-12">
                                <div class="card">									
									<input type="hidden" id="pageNo" value="1" />
                                    <div class="card-body" id="PIList">

										<table  id="tblGrid" data-paging="false" class="table  nowrap w-100 table-bordered">
											<thead class="thead-light">
												<tr>
													<th style="min-width: 50px;">SNo.</th> 
													<th style="min-width: 40px;">ID</th> 
													<th style="min-width: 150px !important;">Processor </th> 
													<th style="min-width: 140px;">P.Inv No</th>
													<th style="min-width: 110px;">P.Inv Date</th>	 
													<th style="min-width: 120px;">Po No</th>
													<th style="min-width: 150px !important;">Buyer</th>
													<th style="min-width: 100px;">Ship By Date </th>	   
													<th style="min-width: 100px;">Status</th>	
													<th style="min-width: 130px;">Actions</th>
												</tr>
											</thead>
											<tbody id="tbody">
											</tbody>
										</table>
                                        <div class=" text-center">										
                                            <button class="btn btn-primary" id="loadAll">Load All</button>										
                                        </div>
									</div> 
									
									<!-- end card body-->
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

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->        

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
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

        <!-- Datatable init js -->
        <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>
        <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>
    </body>




<script>

	// var table;		
	// function fnDataTable() {
	// 	/* Destroy the Datatable if initialize */
	// 	if ($.fn.dataTable.isDataTable('#key-datatable')) {
	// 		$('#key-datatable').DataTable({destroy: true});
	// 	}

	// 	$('#key-datatable').DataTable();
	// }


	$(document).on('click', '#Duplicate', function(e) {
		e.preventDefault();
		var ClickedId = $(this).data('id');
		var PoNo = $(this).data('value');
		var r = confirm("Are You want to Duplicate " + PoNo + "?");
		if (r == true) {
			$.ajax({
				url: 'Ajax/AjaxDuplicatePI.php',
				type: 'POST',
				data: {
					'PiID': ClickedId
				},
				success: function(data) {
					// getPiList();
                    getPiList("1", "1");
				}
			});
		}
	});

	$('document').ready(function() {
		//getPiList();
		$(document).on('click', '#payBtn', function(e) {
			//alert("test");
			e.preventDefault();
			$('.trremove').remove();
			var refid = $(this).val();
			var PO_ID = document.getElementById('PO_ID'); //or grab it by tagname etc
			PO_ID.value = "" + refid;
			var large2 = 'TEST'
			$('.modheading').text('Update Status');
			$('#myModal').modal('toggle');
			//return false;
		});
		$('#elisa').on('click', function() {
			alert("test");
			$('form').submit();
		});
	});
	
function status_update_click(status) {
    var po_id = $('#PO_ID').val();
    //alert(status);
    $.ajax({
        url: 'po_status.php',
        type: 'POST',
        data: {
            'STATUS': status,
            'INVOICE_ID': po_id
        },
        success: function(data) {
            console.log(data);
            alert("DONE");
            location.reload();
        }
    });

}

//function reply_click(clicked_id) {
$(document).on('click', '.Delete', function(e) {
    var PIID = $(this).data('id');
    var r = confirm("Do you Want To Delete This PI " + PIID + "?");
    if (r == true) {
        //alert(clicked_id);		  
        $.ajax({
            // url:'Ajax/AjaxDelete.php',
            url: 'Ajax/AjPoNPiDelete.php',
            type: 'POST',
            data: {
                'TYPE': 'PI_DELETE',
                'D_ID': PIID
            },
            success: function(data) {
                console.log(data);
                alert("Deleted");
                //showData(data);
                // getPiList();
                getPiList("1", "1");
                /*  
                 $("#example1").html(data);
                 alert("Deleted");
                location.reload();*/
                //reload(AjaxDelete.php);
            }
        });
    } else {
        //location.reload();
    }
});

$(document).on('click', '#BtnPopUpBulk', function(e) {
    e.preventDefault();
    var YEAR = $('#YEAR').val();
    //alert(YEAR);
	$.ajax({
        url: 'Ajax/AjaxPopupBulkApproval.php',
        type: 'POST',
        data: {
            'YEAR': YEAR
        },
        success: function(data) {

            $("#PopUpTable").html(data);
            $('#myModal').modal('show');
            $('#Update').hide();
        }
    });
    //}
});

$(document).on('click', '.StatusBulkUpdate', function(e) {
    //$(document).on('click','#Update',function(e){
    e.preventDefault();
    var SelectedbulkInvId = $('#SelectedbulkInvId').val();
    var Status = $('#Status').val();
    if (Status == '') {
        alert('Please Select Status');
        return;
    }
    if (SelectedbulkInvId == '') {
        alert('Please Select Invoices');
        return;
    }
    $.ajax({
        type: 'POST',
        url: 'Ajax/AjaxDelete.php',
        data: {
            'SelectedbulkInvId': SelectedbulkInvId,
            'Status': Status,
            'TYPE': 'BulkApproval'
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (data['sts'] == 'Failed') {
                alert(data['msg']);
                getPiList("1", "1");

            } else {
                $('#myModal').modal('hide');
                alert(data['msg']);
                //location.reload();
                getPiList("1", "1");

            }
        }
    });
});


$(document).on('click', '#BtnPIStatus', function(e) {
    e.preventDefault();
    var POID = $(this).val();
    var UNIT = $('#JME_MOURYA').val();
    if (POID == '') {
        $("#POID").focus();
        alert("PO Number Required");
        return;
    } else {
        $.ajax({
            url: 'Ajax/AjaxPopUpPIStatus.php',
            type: 'POST',
            data: {
                'POID': POID,
                'TYPE': 'KK',
                'UNIT': UNIT
            },
            success: function(data) {
                $('#Update').val(POID);
                //$('.modal-title').text('Do You Want To Shipped This Po '+INT_LOT_NUM+' ?');
                $("#PopUpTable").html(data);
                $('#myModal').modal('show');
            }
        });
    }
});


$(document).on('click', '#Update', function(e) {
    e.preventDefault();
    var InvId = $(this).val();
    var Remarks = $('#Remarks').val();
    var Status = $('#PoStatus').val();
    var PoNo = $('#PoNo').val();
    var PoDate = $('#PoDate').val();
    if (Status == '') {
        alert('Please Select Po Status');
        return;
    }
    if (Status == '5') {
        if (PoNo == '') {
            alert('Please Enter Po Number');
            return;
        } else if (PoDate == '') {
            alert('Please Enter Po Date');
            return;
        }
    }

    //
    //if(Status=='5'){
    $.ajax({
        type: 'POST',
        url: 'Ajax/AjaxDelete.php',
        data: {
            'PoNo': PoNo,
            'PoDate': PoDate,
            'Remarks': Remarks,
            'TYPE': 'PI_STATUS',
            'InvId': InvId,
            'Status': Status
        },
        //dataType: 'json',
        success: function(response) {
            var data = JSON.parse(response);
            if (data['sts'] == 'Failed') {
                alert(data['msg']);
                getPiList("1", "1");

            } else {
                $('#myModal').modal('hide');
                alert(data['msg']);
                //location.reload();
                getPiList("1", "1");
            }
        }
    });


});



$(document).on('click', '#BtnViewPoStatus', function(e) {
    e.preventDefault();
    //	alert("INT_LOT_NUM Required");

    var INT_LOT_NUM = $(this).val();
    //alert(INT_LOT_NUM);

    var UNIT = $('#JME_MOURYA').val();
    if (INT_LOT_NUM == '') {
        $("#INT_LOT_NUM").focus();
        alert("PO Number Required");
        return;
    } else {
        $.ajax({
            url: 'Ajax/AjPopUpPoStatus.php',
            type: 'POST',
            data: {
                'INT_LOT_NUM': INT_LOT_NUM,
                'TYPE': 'KK',
                'UNIT': UNIT
            },
            success: function(data) {
                $('.modal-title').text('Do You Want To Shipped This Po ' + INT_LOT_NUM + ' ?');
                $("#PopUpTable").html(data);
                $('#myModal').modal('show');
            }
        });
    }
});



$(document).on('click', '.shipped_out', function(e) {
    e.preventDefault();
    //var InvId=$(this).val();
    var InvId = $(this).attr('id');
    var ShippedOutDate = 'ShippedOutDate' + InvId;
    var ShippedOutDate = $('#' + ShippedOutDate).val();

    $.ajax({
        type: 'POST',
        url: 'Ajax/AjaxDelete.php',
        data: {
            'ShippedOutDate': ShippedOutDate,
            'TYPE': 'SHIPPED_PO',
            'InvId': InvId
        },
        //dataType: 'json',
        success: function(response) {
            var data = JSON.parse(response);
            if (data['sts'] == 'failed') {
                alert(data['msg']);
            } else {
                $('#myModal').modal('hide');
                alert(data['msg']);
                location.reload();
            }
        }
    });
}); 


	function delete_click(status) {
		var po_id = $('#PO_ID').val();
		var po_id = status;
		//alert(status);
		//alert(po_id);
		//return;
		$.ajax({
			url: 'po_delete.php',
			type: 'POST',
			data: {
				'STATUS': status,
				'INVOICE_ID': po_id
			},
			success: function(data) {
				console.log(data);
				alert(data);
				location.reload();
			}
		});
	}
	

	var dtTable;

	
    $(document).on("click", "#list", function() {
        getPiList("1", "1");
    });
	
	$(document).on("click", "#loadmore", function() {
		var pageNo = $('#pageNo').val();
		getPiList(pageNo, "more");
	});

    $(document).on("click", "#loadAll", function() {
		getPiList("", "all");
	});

	

	function getPiList(pageNo, load) {
		var EXPORTER = $('#drpExporter').val();
		var YEAR = $('#YEAR').val();
		var Buyer = $('#Buyer').val();
		var Country = $('#Country').val();
		$.ajax({
			url: 'Ajax/AjaxPIListDev.php',
			type: 'POST',
			data: {
				'Exporter': EXPORTER,
				'Year': YEAR,
				'Consignee': Buyer,
				'Country': Country,
				'PageNo': pageNo,
                'load':load == "more" ? "more" : ""
			},
			success: function(data) {                    

				if(data.trim() == "NODATE"){
					console.log("No Data");
				}else{
                    console.log("else");
					$(".gridList").show();

                    if ($.fn.dataTable.isDataTable(dtTable)) {	
                        dtTable.destroy();   						
                    }                                        

                    if(load == "all" || load == "1"){
                        $("#tbody").html("").append(data);
                    }else{
                        $("#tbody").append(data);
                    }


					dtTable = $('#tblGrid').DataTable({
						paging: false,
						order: [] 
					}); 

					$("<button class='btn btn-primary' id='loadmore' style='padding: 3px 5px;margin-left: 10px;'>Loadmore</button>").insertAfter("#tblGrid_filter label");    
                    var totalCount = $(document).find("#hdnTotalRecords").val();

                    var ele = $(document).find("#tblGrid_info");
                    var str = ele.text();
                    ele.text( str + " and Total records : " + totalCount);

                    $("#pageNo").val(parseInt(pageNo) + 1);
				}

			}
		});
	}
	

$(document).ajaxStart(function(){
	$("#wait").css("display", "block");
}); 
$(document).ajaxComplete(function(){
	$("#wait").css("display", "none");
});


// function showData(data) {
//     $("#PIList").html(data);

//     var table = $('#key-datatable').DataTable({
//         scrollY: "600px",
//         scrollX: true,
//         //sScrollX: "100%",
//         sScrollXInner: "150%",
//         // scrollCollapse:true,
//         paging: true,
//         destroy: true,
//         fixedColumns: true,
//         bSort: true,
//         order: [
//             [1, 'desc']
//         ],
//         "bStateSave": true,
//         "fnStateSave": function(oSettings, oData) {
//             localStorage.setItem('key-datatable', JSON.stringify(oData));
//         },
//         "fnStateLoad": function(oSettings) {
//             return JSON.parse(localStorage.getItem('key-datatable'));
//         }
//     });
// }




</script>
