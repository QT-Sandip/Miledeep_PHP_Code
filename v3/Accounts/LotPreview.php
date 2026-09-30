<?php 
$PageTitle="Purchase Details";
$PageTitleFolder="Purchase";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/AjaxPurchaseDetails.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/AjaxGradingDetails.php");

$LotID=$_GET['LotID'];

 $SqlTickets="SELECT *,count(*) as TicketCount FROM `AdminTickets` 
left join AdminGeneralIssues on AdminTickets.IssueID=AdminGeneralIssues.IssueID
where LotID='$LotID' and TicketStatus=0"; 
$StmtTickets=$iconn->query($SqlTickets);
$RowTickets=$StmtTickets->fetch_array();
$LinkName=$RowTickets['LinkName'];
$LinkUrl=$RowTickets['Url'];
$TicketID=$RowTickets['TicketID'];



if($RowTickets['TicketCount'] > 0){
	$ShowRiseTicket=false;
}else{
	$ShowRiseTicket=true;
}

if(!$ShowRiseTicket && $_SESSION['AccessType'] == 2){
	$ShowFixLink=true;
}else{
	$ShowFixLink=false;
}
$QueryBillDetails="select BillStatus from RmBillDetails where LotID='$LotID'";
$BillDetailsExec=$iconn->query($QueryBillDetails);
$RowBillDetails=$BillDetailsExec->fetch_array();

 $BillStatus=$RowBillDetails['BillStatus'];
 $AccessType=$_SESSION['AccessType'];
 
 if(!$ShowRiseTicket && $_SESSION['AccessType'] == 2 && $BillStatus==1){
	$ShowFixLink=true;
}else{
	$ShowFixLink=false;
}
//echo $ShowFixLink; exit;


?>

<!doctype html>
<html lang="en">

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

                        <?php include ($IncludesPath."page-title.php");?>
						
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
									
										<div class="row">
                                               
											 <table id="PurchaseDetails" class="table table-bordered table-hover">
											</table> 
											
                                           <table id="GradingTable" class="table table-bordered table-hover">
											</table>  
											
											<table id="FinalBillTable" class="table table-bordered table-hover">
											</table>      
												
												 


										</div>
                        <!-- end row -->
									</div>
									</div>
									</div>
        
                                         <!-- end col -->
                        </div> <!-- end row -->

                        <div class="row">
                            <div class="col-12">
                                <div class="card" >
                                <div  id="BillData" >
                                </div>
								
								
								 
                                    
                      
				
				<div class="card-body" >

				
				 <div class="col-xs-12 box-footer"  align="right">
				 
				 
                
				<?php if($ShowRiseTicket){ ?>
				<a  href="../Purchase/CreateTicket?LotID=<?php echo $LotID ?>"class="btn btn-warning">Rise Issue</a>
				<?php  } ?>
				
				<?php if($ShowFixLink){ ?>
				<a target="_blank" href="<?php echo $BaseUrl .$LinkUrl.$LotID.'&TicketID='.$TicketID ?>"class="btn btn-warning">View Issue( <?php echo $LinkName ?> )</a>
			    <?php  } ?>
				
				
				<?php if($BillStatus==0 && $AccessType==2){ ?>
				<!--<button class="btn btn-success" id="ApproveBill">Approve Bill</button>-->
			    <?php  } ?>
				
				
			 </div>

								 
								 
								
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
</html> 

<script>
$('document').ready(function(){
		getData();
    });
	
	
function getData(){
	
		var LotID='<?php echo $LotID ?>';

		 $.ajax({
		  url:'Ajax/AjaxGradingDetails.php',
		  type:'POST',
		  data:{'LotID' : LotID,'Type' : 'BillPreview'},
		  success:function(data){
			// console.log(data);
			  var JsonData = JSON.parse(data);
			 //alert(JsonData[1]);


$("#PurchaseDetails").html(JsonData['PurchaseTable']);
$("#BillData").html(JsonData['BillDetails']);
$("#GradingTable").html(JsonData['GradingTable']);
$("#FinalBillTable").html(JsonData['FinalBillDetails']);
$("#COUNT").val(JsonData['BillCount']);
$("#WEIGHT").val(JsonData['HlWeight']);
$("#RATE").val(JsonData['BillRate']);
$("#YIELD").val(JsonData['Yield']);

					}
			});
	
}

</script>



<Script>

$('document').ready(function(){

    	$(document).on('click','#ApproveBill',function(e){
		e.preventDefault();
           
			var LotID= '<?PHP ECHO $LotID ?>'
            var PostedUserID= '<?PHP ECHO $UserID ?>'
            
			var validation=false;


			if(PostedUserID==""){
			alert("Session Exparied Please Login Again");
			}else{
				validation=true;
			}

			if(validation==false){
			return;
			}

			var MData = {
				'LotID': LotID,
				'PostedUserID': PostedUserID
				};
			$.ajax({ 
		  url:'Ajax/AjaxBillApprove.php',
		  type:'POST',
		  dataType: 'json',
		  data: MData,
		  success:function(data){
			 //console.log(data);
			   var obj = JSON.parse(JSON.stringify(data));
			 location.reload();
			   console.log(obj);
			 if(obj.Error==null){
				alert(obj.Msg);
				}else{
				alert(obj.Error);
				}

			}});


    	});
    });

</script>

