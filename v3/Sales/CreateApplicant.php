<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$PageTitleFolder="Master";

$BUYER_ID=$_GET['BUYER_ID'];
$ID=$_GET['ID'];
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));



$SqlCountries="SELECT * FROM `$TblScrmCountriesList` ORDER BY `id` ASC";
$StmtCountries=$iconn->query($SqlCountries);



$sql_pur="SELECT * FROM `$ScrmApplicants` as sa 
left join ScrmConsigneeDetails as cd on cd.ConID=sa.ConID 
left join $TblScrmCountriesList as SCL on SCL.id=sa.ApplicantCountry 
where sa.ConID='$BUYER_ID'";
$stmt_pur=$iconn->query($sql_pur);
$row_pur=$stmt_pur->fetch_array();
$stmt_pur=$iconn->query($sql_pur);



  $sql="SELECT * FROM `$ScrmApplicants` where ConID='$BUYER_ID' and ID='$ID'"; 
$stmt=$iconn->query($sql);
$row_applicant=$stmt->fetch_array();
$count=mysqli_num_rows($stmt);

 			 if($count==0){
				 $PageTitle="New Applicant"; }
			 else{
				$PageTitle="Update Applicant"; }
			 

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	 
				 $sql="SELECT * FROM `$ScrmApplicants` where ID='$ID'";  
$stmt=$iconn->query($sql);
 $count1=mysqli_num_rows($stmt);

	 
	 

			 if($count1==0){

		 $sql_org_insert="INSERT INTO `$ScrmApplicants`( `ConID`, `ApplicantName`, `ApplicantAddress`, `ApplicantCountry`) 
	VALUES ('$BUYER','$ApplicantName','$ApplicantAddress','$ApplicantCountry')
	"; 
	
	
	$stmt1=$iconn->query($sql_org_insert);
	$MB_REPORT_ID = $iconn->insert_id;

//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById($ScrmApplicants, 'ID', $MB_REPORT_ID, $_POST, array(
			'ReferenceNo' => $MB_REPORT_ID,
			'Remarks' => 'Applicant created',
		), 'Sales');
	
		header('Location: CreateApplicant.php?BUYER_ID='.$BUYER);
		//header('Location: s.php?ID='.$Inv_ID);
	}
}
	
	else{
		$existingApplicant = legacyAuditFetchRowById($ScrmApplicants, 'ID', $ID, 'Sales');
			
	 $sql_org_update="UPDATE `$ScrmApplicants` SET `ConID`='$BUYER',`ApplicantName`='$ApplicantName',
	`ApplicantAddress`='$ApplicantAddress',`ApplicantCountry`='$ApplicantCountry' WHERE ID='$ID'";  
			
			$stmt2=$iconn->query($sql_org_update);
	
			if($stmt2){
				legacyAuditLogUpdateById($ScrmApplicants, 'ID', $ID, $existingApplicant, $_POST, array(
					'ReferenceNo' => $ID,
					'Remarks' => 'Applicant updated',
				), 'Sales');

				$ArgumentsArray[PostedUserId]=$PostedUserId;
            $ArgumentsArray[PageUrl]=$PageUrl;
            $ArgumentsArray[Remarks]=$Remarks;
            $ArgumentsArray[ReferenceID]=$ID;
            postUserEditLog($ArgumentsArray);
		header('Location: CreateApplicant.php?BUYER_ID='.$BUYER);
	
	
	}}
	
}

 ?>


<!DOCTYPE html>
<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />

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
	
		 	<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1"> Buyer Name</label>
				  <select class="form-control select2" name="BUYER" id="BUYER">
				  
				  <option value="">Select Buyer</option>
				  
				   <?php
				   $sql_inv="SELECT * FROM `ScrmConsigneeDetails` ORDER BY `ConID` DESC";
$stmt_inv=$iconn->query($sql_inv);
				   while ($Row_PO=$stmt_inv->fetch_array()){ 

if(isset($_GET['BUYER_ID'])){
					 ?>	
				 		<option <?php if($row_pur['ConID']==$Row_PO['ConID']){ ?> selected="selected" <?php }?> 
						value="<?php echo $Row_PO['ConID'] ;?>" ><?php echo $Row_PO['ConsigneeName'].' - '.$Row_PO['ConsigneeCountry'] ?></option>
				
					<?php }else {
						?>
							<option value="<?php echo $Row_PO['ConID'] ;?>" ><?php echo $Row_PO['ConsigneeName'].' - '.$Row_PO['ConsigneeCountry'] ?></option>
				
					
<?php 				
	} }   ?>
  
				  </select>
				  
					
		
                </div>
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1"> Applicant Name<span style="color:red">*</span></label>
                  <input required type="text" class="form-control" name='ApplicantName' id="ApplicantName" 
				  value="<?php echo  $row_applicant['ApplicantName']; ?>" placeholder="Enter  Applicant Name">
                </div> 
				
					
			<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1"> Applicant Country<span style="color:red">*</span></label>
				  <select required class="form-control select2" name="ApplicantCountry" id="ApplicantCountry">
				  
				  <option value="">Select Applicant</option>
				  
				   <?php
				  
				   while ($Row_PO=$StmtCountries->fetch_array()){ 

if(isset($_GET['BUYER_ID'])){
					 ?>	
				 		<option <?php if($row_applicant['ApplicantCountry']==$Row_PO['id']){ ?> selected="selected" <?php }?> 
						value="<?php echo $Row_PO['id'] ;?>" ><?php echo $Row_PO['name']?></option>
				
					<?php }else {
						?>
							<option value="<?php echo $Row_PO['id'] ;?>" ><?php echo $Row_PO['name']?></option>
				
					
<?php 				
	} }   ?>	  
				  </select>
				  
					
		
                </div>
				
					<div class="form-group col-lg-6" >
                  <label for="exampleInputEmail1">Applicant Address<span style="color:red">*</span></label>
				  
				  <textarea required name="ApplicantAddress" class="form-control" rows="2" cols="50"
                    id="ApplicantAddress"   
					placeholder="Enter Applicant Address" ><?php echo $row_applicant['ApplicantAddress'] ?></textarea>
				 		
		
                </div>
				
				 
			
			
</div>

 <div class="col-xs-12 box-footer"  align="right">
  			                  <a target="" href="ApplicantList.php"   class="btn btn-danger  "> Close</a>

 <?php  if($count==0){ ?>
			 
                <button type="submit"  name='submit' class="btn btn-success">ADD</button>
				
            
				<?php } else { ?>
				
			 
                <button type="submit"  name='submit' class="btn btn-success">Update</button>
				
             
				<?php } ?> </form>
 
 			
				
              </div>
              </div>
              </div>
	
<div class="row">
                            <div class="col-12">

						 
        <div class="card">
          
        <div class="card-body" id="ShowDataTable">
                    
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
 <thead class="thead-light">

      <tr>
	  
	  
	   <th>S.No</th>
	  <th>Buyer Name</th>
	  <th>Applicant Name</th>
	  <th>Applicant Address</th>
	  <th>Applicant Country</th>
	  <th>Actions</th>

    
	  	
      </tr>
    </thead>
    <tbody>
	<?php
	$S_NO=1;
   while($ROW_inv=$stmt_pur->fetch_array()){


	?>
	<tr >
			
	<td  id="<?php  echo $ROW_inv['ID'];?>" > <?php echo $S_NO; ?>    </td>
	<td id="<?php  echo $ROW_inv['ID'];?>" > <?php echo $ROW_inv['ConsigneeName']; ?> </td>
	<td id="<?php  echo $ROW_inv['ID'];?>" > <?php echo $ROW_inv['ApplicantName']; ?> </td>
	<td id="<?php  echo $ROW_inv['ID'];?>" > <?php echo $ROW_inv['ApplicantAddress']; ?> </td>
	<td id="<?php  echo $ROW_inv['ID'];?>" > <?php echo  $ROW_inv['name'];?> </td>
	
	<TD>
	<!--<button id="<?php  echo $ROW_inv['ID'];?>" 
	onClick="reply_click(this.id)" class="btn btn-danger btn btn-danger"> Delete</button>-->
	
	<button class="btn btn-link text-danger Delete" 
		data-id="<?php  echo $ROW_inv['ID'];?>" data-toggle="tooltip" data-placement="top" title="Delete" value=""
	data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16"></i></button>
							
		<a target="_blank" href="CreateApplicant.php?ID=<?php echo $ROW_inv['ID'];?>&BUYER_ID=<?php echo $BUYER_ID ?>" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Edit" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-18"></i></a>
          			
					
	</TD>
	
	
    </tr>
	
	
	  <?php 
	  $S_NO++;

   }
	  ?>

     </tbody>

  </table>
</div>
    
	</div> <!-- end card body-->
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
		<?php include ($IncludesPath."ShowDataTable.php");?>
  
       
    </body>
	<script>

$('document').ready(function(){
showData();

});
  /*function reply_click(clicked_id) {

   		 
		 alert(clicked_id);		  
		$.ajax({
		  url:'Ajax/AjaxDelete.php',
		  type:'POST',
		  data:{'TYPE':'App_del','D_ID':clicked_id},
		  success:function(data){
			  console.log(data);
			  
			 $("#CODE_LIST").html(data);
			 alert("Deleted");
			location.reload();
			}
			});					  
  } */
  
  $(document).on('click', '.Delete', function(e){
	  var clicked_id =$(this).data('id');


   		var r = confirm("Do you Want To Delete This ?");
  if (r == true) { 
		 		  
		$.ajax({
		 
		  url:'Ajax/AjaxDelete.php',
		  type:'POST',
		  data:{'TYPE':'App_del','D_ID':clicked_id},
		  success:function(data){
			  console.log(data);
			  alert("Deleted");
			  	location.reload();	
	
			}
			});	

} else {
			
  }			
  });
  
</script>
<script>
  $(document).on('click','#sub',function(e){			
			e.preventDefault();
				window.location.href = "/Sales/ApplicantList.php" ;

		
    	});
			
  
  
</script>

</html>				

