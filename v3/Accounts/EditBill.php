<?php
$Status=$_SESSION['Status']; 
$UserID=$_SESSION['UserID']; 
$LotID=$_GET['LotID'];
$TicketID=$_GET['TicketID'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$SqlRmLot="SELECT LotNumber FROM `RmLotDetatils` WHERE RmLotID='$LotID'";
$StmtRmLot=$iconn->query($SqlRmLot);
$RowRmLot=$StmtRmLot->fetch_array();
$LotNumber=$RowRmLot['LotNumber'];

$PageTitle="Edit Bill".$LotNumber;	
$PageTitleFolder="Accounts";

extract($_POST);
extract($_GET);
$UNIT=$_SESSION["UNIT"];
$today_date=date('Y-m-d');


//ORIGINAL
$SqlRmBills="SELECT * FROM `RmBillDetails` WHERE LotID='$LotID'";
$StmtRmBills=$iconn->query($SqlRmBills);
$LotCount=mysqli_num_rows($StmtRmLots); 

$RowRmBills=$StmtRmBills->fetch_array();


 $SqlRmFin="SELECT * FROM `RmFinanciers`";
$StmtRmFin=$iconn->query($SqlRmFin);





 ?>



<!DOCTYPE html>

<html lang="en">

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ($IncludesPath."DbLinks.php");?>
 

            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php
			include ($IncludesPath."PreLoader.php"); ?>

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
					
						 <?php
	if($Status !=''){
	//Show Alert 
	?>
	 <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="mdi mdi-check-all mr-2"></i>
                                                      <?php echo $Status;
$_SESSION['Status']='';	  ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">

			 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Financier Name</label>

				  <select class="form-control select2" name="FinancierID" id="FinancierID">
				<?php while($RowRmFin=$StmtRmFin->fetch_array()) {
				?>
				<option 
				<?php if($RowRmFin['ID']==$RowRmBills["FinancierID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowRmFin['ID'];?>">
				<?php echo $RowRmFin['FinancierName'];?></OPTION>
				<?php } ?>
				<select>
			</div>


			
		   
		<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Deductions</label>
                 <input type="number"  step="0.01" class="form-control"  name="Deductions" id="Deductions"
				 placeholder="Enter Deductions(if any)" value="<?php echo $RowRmBills['Deductions'];?>">
        </div>
		
				   
		<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Remarks</label>
                 <input type="text" class="form-control"  name="Remarks" id="Remarks"
				 placeholder="Enter Remarks" value="<?php echo $RowRmBills['Remarks'];?>">
        </div>

		

				</div>
              <!-- /.box-body -->


            </form>

			<div class="col-xs-12 box-footer"  align="right">
          
			 <button  type="Update" id="Update"  class="btn btn-success">Edit Lot</button>
				
             </div>

             </div> <!-- end row -->
    
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
		<?php include ($IncludesPath."NewScripts.php");?>
		
  
       
    </body>
</html>				

<Script>

$('document').ready(function(){

    	$(document).on('click','#Update',function(e){
		e.preventDefault();
  $('#CL').attr('disabled','disabled');


			//alert('Hi');

		
            var FinancierID= $('#FinancierID').val();
			var Deductions= $('#Deductions').val();
			var Remarks= $('#Remarks').val();
            var PostedUserID= '<?PHP ECHO $UserID ?>'
            var TicketID= '<?PHP ECHO $TicketID ?>'
            var LotID= '<?PHP ECHO $LotID ?>'
            
			var validation=false;


			if(FinancierID==""){
			alert("Please select Financier");
			$('#LotNumber').focus();
			}else if(Deductions=="Select Vehical"){
			alert("Deductions is empty");
			$('#Deductions').focus();
			}else if(Remarks=="Select Driver"){
			alert("Remarks is empty");
			$('#Remarks').focus();
			}
			
			else{
				validation=true;
			}

			if(validation==false){
				$('#CL').removeAttr('disabled');
			return;
			}

		

			var arr = {
				
				'FinancierID': FinancierID,
				'Deductions': Deductions,
				'Remarks': Remarks,
				'PostedUserID': PostedUserID,
				'TicketID': TicketID,
				'LotID': LotID,
				'SOURCE': 'WEB'


				};
			$.ajax({
		  url:'<?php echo $ApiUrl ?>?f=postEditBill',
		  type:'POST',
		  dataType: 'json',
		  data: JSON.stringify(arr),
		  success:function(data){
			   var obj = JSON.parse(JSON.stringify(data));
			 if(obj.Error==null){
				$('#CL').removeAttr('disabled');
				alert(obj.Msg);
				close();
				}else{
				alert(obj.Error);
				close();
				}

			}});


    	});
    });

</script>

<script>

$(document).ajaxStart(function(){
    $("#wait").css("display", "block");
  });
  $(document).ajaxComplete(function(){
    $("#wait").css("display", "none");
  });




</script>




</html>
