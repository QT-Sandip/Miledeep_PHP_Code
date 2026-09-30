<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitle="Internal Check Report ";

$PageTitleFolder="QC";


$UserID= $_SESSION["UserID"];

if($UserID==''){
header('Location: index.php?msg=Please enter your User name and Password.');
}

$LotId=$_GET['LOT_ID'];


extract($_POST);
extract($_GET);




	 $SqlLots="SELECT * FROM `RmLotDetatils` WHERE RmLotID=$LotId"; 
    $StmtLots=$iconn->query($SqlLots);  
	$RowLots=$StmtLots->fetch_array();
	
	
	$SqlReportCount="SELECT COUNT(*) as ReportCount FROM `QcRmOrgnDetails` WHERE LotID=$LotId";
    $StmtReportCount=$iconn->query($SqlReportCount);  
	$RowReportCount=$StmtReportCount->fetch_array();
	$ReportCount=$RowReportCount['ReportCount'];
	
	$ReportCount=$ReportCount+1;
	
	IF($ReportCount>3){
		header('Location: RmaPrint.php?LotID='.$LotId);
		exit;
	}

if(isset($_POST['submit'])){
	
$TimeNow=mktime(date('h')+5,date('i')+30,date('s'));
$PostedDate = date('Y-m-d H:i:s', $TimeNow);	
 $SqlInsert="INSERT INTO `QcRmOrgnDetails`(`LotID`,`PostedDate`,`CheckedCount`, `BlackGills`, 
	`Soft`, `DroopingHead`, `BlackSpot`,`Broken`, `ImproperLegs`, `Bends`, `BigPieces`, `SmallPieces`, 
	`FitForHonPack`,`Remarks`,`PostedUserID`)
	VALUES ($LotId,'$PostedDate','$CheckedCount','$BlackGills','$Soft','$DroopingHead',
	'$BlackSpot','$Broken','$ImproperLegs','$Bends','$BigPieces','$SmallPieces','$FitForHonPack',
	'$Remarks','$UserID')";
	
	
	 
	 
	 

	$StmtInsert=$iconn->query($SqlInsert);
	if($StmtInsert){
		$OrgnDetailsInsertId = $iconn->insert_id;
		legacyAuditLogInsertById('QcRmOrgnDetails', 'ID', $OrgnDetailsInsertId, $_POST, ['Remarks' => 'Orgn Details Added'], 'QualityControl');
	if($ReportCount==3){
				
		header('Location: RmaPrint.php?LotID='.$LotId);
	/*$sql_org_insert="UPDATE `inputmaterial_details` SET `RM_ANA_STATUS`='1' WHERE id='$id'";	
	$stmt1=$iconn->query($sql_org_insert);*/
	}

		// header('Location: #');
		header('Location: RmReportCreate.php?LOT_ID='.$LotId);
		}
}

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
							<h2 align="center"> ORGANOLEPTIC INSPECTION REPORT- <?php echo $RowLots['LotNumber']?></h2>
		
										<div class="row">

               
			    <div class="form-group col-lg-12">
                  <H4 > <U><B><span style="color: #00a65a">SAMPLE- <?php echo $ReportCount; ?> </span></B></U> </H4>
                  
                </div>
				  
				   <div class="form-group col-lg-4" >
                  <label for="exampleInputEmail1"> Checked count/Kg</label>
                    <input required type="TEXT" class="form-control" name='CheckedCount' id="CheckedCount"
					placeholder="Enter Checked count in Kgs">
				 
				  </div>
				  
				   <div class="form-group col-lg-4" >
                  <label for="exampleInputEmail1"> Red/Black gill</label>
                    <input required type="TEXT" class="form-control" name='BlackGills' id="BlackGills" 
					placeholder="Enter Date of analysis">
				 
				  </div>
                
                <div class="form-group col-lg-4">
                  <label for="exampleInputPassword1"> Soft/Semi Soft/Paper Soft</label>
                  <input required type="TEXT" class="form-control" name='Soft' id="Soft"
				  placeholder="Enter Soft/Semi Soft/Paper Soft in %">
                </div>
				
				<div class="form-group col-lg-4">
                  <label for="exampleInputPassword1"> Dropping Head in %</label>
                  <input required type="text" class="form-control" name='DroopingHead' id="DroopingHead" 
				  placeholder="Enter Dropping Head in %">
                </div>
                
				  
				 <div class="form-group col-lg-4">
                  <label for="exampleInputPassword1"> Black spot/Black Dot </label>
                  <input required type="text" class="form-control" name='BlackSpot' id="BlackSpot"
				  placeholder="Enter  Black spot/Black Dot in %">
                </div>
				
				 <div class="form-group col-lg-4">
                  <label for="exampleInputPassword1">  Broken/Damages </label>
                  <input required type="text" class="form-control" name='Broken' id="Broken"
				  placeholder="Enter Broken/Damages">
                </div>
				 <div class="form-group col-lg-4">
                  <label for="exampleInputPassword1"> Improper Legs/Decomposition/Discoloration </label>
                  <input required type="text" class="form-control" name='ImproperLegs' id="ImproperLegs"
				  placeholder="Enter Improper Legs/Decomposition/Discoloration in %">
                </div>
				
				<div class="form-group col-lg-4">
                  <label for="exampleInputPassword1">  Bends</label>
                  <input required type="text" class="form-control" name='Bends' id="Bends" 
				  placeholder="Enter Bends ">
                </div>
				
				<div class="form-group col-lg-4">
                  <label for="exampleInputPassword1">   Material is fit for Head On Packing or not</label>
                  <select class="form-control" name="FitForHonPack" id="FitForHonPack">
				   <option value="YES">YES</option>
				   <option value="NO">NO</option>
				  <select>
                </div>
				 
				 <div class="form-group col-lg-4">
                  <label for="exampleInputPassword1"> Big Pieces </label>
                  <input required type="text" class="form-control" name='BigPieces' id="BigPieces" 
				  placeholder="Enter number of Big Pieces ">
                </div>
				
				
				 <div class="form-group col-lg-4">
                  <label for="exampleInputPassword1"> Small Pieces </label>
                  <input required type="text" class="form-control" name='SmallPieces' id="SmallPieces" 
				  placeholder="Enter number of Small Pieces ">
                </div>
				 
			
						 
				 
				<div class="form-group col-lg-4">
                  <label for="exampleInputPassword1">  Remarks </label>
                  <input required type="text" class="form-control" name="Remarks" id="Remarks" 
				  placeholder="Enter Remarks">
                </div>
                </div>
		
  <div class="col-xs-12 form-group"  align="right">
              
				
				<button type="submit"  name='submit' class="btn btn-primary">SUBMIT RESULT</button>
			 
              </div>
            </form>					
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
	var doa_doc='<?php echo $DOA_DOC?>';
	//var doa_doc='<?php echo $COUNTRY?>';
	
	//alert(doa_doc);
	
    $("#DOA_DOC").val(doa_doc);
    	$(document).on('change','#P_CODES',function(e){
			
    	e.preventDefault();
			 
                         
			var DAY_CODE=$(this).val();
			var INV_ID=<?php echo $INVOICE_ID ?>;
			
			//alert(DAY_CODE);
			$.ajax({
		  url:'b_codelist.php',
		  type:'POST',
		  data:{'D_CODE':DAY_CODE,'INV_ID': INV_ID},
		  success:function(data){
			  console.log(data);
			  // var obj = JSON.parse(data);
			$("#REG_NO").html(data);
			$("#PACK_SIZE").html('<option value="">Select Size</option> ');
					}
			})
			//return false;
			
    		
    
    	});
		
		
		$(document).on('change','#REG_NO',function(e){			
			e.preventDefault();                         
			var REG_NO=$(this).val();
			var INV_ID=<?php echo $INVOICE_ID ?>;
			
			//alert(INV_ID);
			$.ajax({
		  url:'b_codelist.php',
		  type:'POST',
		  data:{'REG_NO':REG_NO,'INV_ID': INV_ID},
		  success:function(data){
			  console.log(data);
			  // var obj = JSON.parse(data);
			$("#PACK_SIZE").html(data);
					}
			})
			//return false;
			
    		
    
    	});
    
    	$('#continuebtn').on('click',function(){
			alert('hi');
    
    		$('form').submit();
    	});
    });    

</script>





</html>

