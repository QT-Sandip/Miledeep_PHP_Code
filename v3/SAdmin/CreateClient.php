<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

$PageTitle="Create Client";

$PageTitleFolder="Admin";

$AccessType=$_SESSION['AccessType'];



 
extract($_POST);
extract($_GET);

 
if(isset($_POST['submit'])){
	  
$Year=Date('Y'); 

/*
$SqlConfig="SELECT * FROM `AdminConfigDetails` order by ConfigID DESC limit 1";  
$StmtConfig=$iconn->query($SqlConfig);
$RowConfig=$StmtConfig->fetch_array();
$ClientPath=$RowConfig['ClientPath'];*/
$Path="v3/";


 $ClientAccountID =getClientID($iconn,$Year,1); 
 
	$SqlClientInsert="INSERT INTO `AdminRegisteredClients`(`ClientAccountID`, `ClientName`,
	 `ClientAddress`, `ClientShortName`, `Path`, `StartYear`) 
	 VALUES('$ClientAccountID','$ClientName','$ClientAddress','$ClientShortName','$Path',
	 '$StartYear')";
	 
	
	 
	$StmtClientInsert=$iconn->query($SqlClientInsert);
    $JobID= $iconn->insert_id;
   
	//exit;
	if($StmtClientInsert){
		legacyAuditLogInsertById('AdminRegisteredClients', 'ClientID', $JobID, $_POST, [
			'ReferenceNo' => $JobID,
			'Remarks' => 'Client created',
		], 'SAdmin');
	$Q=urlEncode64('ClientID='.$JobID);
	header('Location:AddDbDetails.php?Q='.$Q);
	}else{
	echo "Unable To Create Client";	
	}


}

function getClientID($iconn,$Year,$UserCount){
	
$Count=1;
  $Code=sprintf('%03d', $Count);
  $ClientAccountID ='SLS'.$Year.$Code; 
$AccAval=true;

while($AccAval){

 $SqlCount="SELECT * FROM `AdminRegisteredClients` WHERE ClientAccountID='$ClientAccountID'";  
$StmtCount=$iconn->query($SqlCount);
 $UserCount=mysqli_num_rows($StmtCount);
 if($UserCount==0){
 $AccAval=false;
 }else{
  $Code=sprintf('%03d', ++$Count);
  $ClientAccountID ='SLS'.$Year.$Code; 
 }
 

}

return $ClientAccountID;
	
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

	
	 <?php			include ($IncludesPath."PreLoader.php"); ?>

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
                  <label for="exampleInputEmail1">Client Name</label>
                  <input required type="text" class="form-control" name='ClientName' id="ClientName"
				  placeholder="Enter Client Name" 
				  value="<?php echo $RowJobDetails['ClientName']?>"  >
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Joning Year</label>
                  <input required type="text" class="form-control" name='StartYear' id="StartYear"
				  placeholder="StartYear" value="<?php echo $RowJobDetails['StartYear']?>"  >
                </div>
				
				<div class="col-lg-3">
                    <label for="exampleInputEmail1">Client Short Name</label>                                     
                    <input required type="text" class="form-control" name='ClientShortName' id="ClientShortName"
				  placeholder="Enter ClientShortName" value="<?php echo $RowJobDetails['ClientShortName']?>"  >
		          
                </div>
			
					

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Address</label>
                 <input required type="text" class="form-control" name='ClientAddress' id="ClientAddress"
				  placeholder="Enter Client Address" value="<?php echo $RowJobDetails['ClientAddress']?>"  >
		          
                </div>
				
			
				
				
                </div>
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-success">Create</button>
              </div>
            </form>
       <!-- end col -->
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
<script>
$(document).on('change','#BranchID',function(e){
       e.preventDefault();
       var BranchID=$(this).val();
	   var Type='BranchCode';

		$.ajax({
		  url:'Ajax/AjInputvalues.php',
		  type:'POST',
		  data:{'BranchID':BranchID,'Type':Type},
		  success:function(data){
			$("#BranchCode").val(data);

					}
			})
		
    
    	});
			
		
	  $(document).on('change','#ClientID',function(e){
       e.preventDefault();
       var ClientID=$(this).val();
	   var Type='GSTNumber';

		$.ajax({
		  url:'Ajax/AjDropDowns',
		  type:'POST',
		  data:{'ClientID':ClientID,'Type':Type},
		  success:function(data){
			$("#GstID").html(data);
					}
			})
		
    
    	});
		
	
		
		
</script>
