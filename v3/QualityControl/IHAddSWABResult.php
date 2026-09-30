<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$PageTitleFolder="Quality Control";
$PageTitle="SANITARY STANDARD ASSESSING OF UNIT SWAB";
$ID=$_GET['ID'];


$SqlSWABResult="SELECT * FROM `QcIHSWABResult` WHERE ReportID='$ID' "; 
$StmtSWABResult=$iconn->query($SqlSWABResult);


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	 
      $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
      $TIME_STAMP=$dateTime->format("Y-m-d  H:i:s"); 

 if($SampletLocation=='PRE-PROCESSING AREA'){
		 $SortOrder=1;
	 }

else if($SampletLocation=='PROCESSING AREA'){
		 $SortOrder=2;	
		 $LIMITS='1.TPC -100cfu/cm2';
	 }
else if($SampletLocation=='BLOCK AREA'){
		 $SortOrder=3;
		 $LIMITS='2.E.COLI-NIL';
	 }
else if($SampletLocation=='COOKING AREA'){
		 $SortOrder=4;
		 $LIMITS='3.COLIFORMS-NIL';
	 }	 
else if($SampletLocation=='WORKERS HAND'){
		 $SortOrder=5;
	 }


$ReportID=$ID;
	
	  $sql_org_insert="INSERT INTO `QcIHSWABResult`(`ReportID`,
	  `SampletLocation`, `TPC`, `Stayphloco`, `ECOLI`, `COLIFORMS`, 
	  `VCnVP`, `LIMITS`, `SortOrder`,`Location`) VALUES 
	  ('$ReportID','$SampletLocation','$TPC','$Stayphloco','$ECOLI',
	 '$COLIFORMS','$VCnVP','$LIMITS','$SortOrder','$Location')"; 

	$stmt1=$iconn->query($sql_org_insert);
	$IceWaterID = $iconn->insert_id;
//echo $sql_org_insert; exit;
	if($stmt1){
		legacyAuditLogInsertById('QcIHSWABResult', 'ID', $IceWaterID, $_POST, ['Remarks' => 'SWAB Result Added'], 'QualityControl');
	
		//header('Location: IHAddDlyWaterTPDMicroReport.php?ID='.$LOT_ID);
		header('Location: IHAddSWABResult.php?ID='.$ID);
	}
}




$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE TYPE='SAMPLELOCATION' ";
$stmt_OPTIONS=$iconn->query($SQL_OPTIONS);	


//$ARY_OPTIONS=array();
WHILE($ROW=$stmt_OPTIONS->FETCH_ARRAY()){
	 //$ARY_OPTIONS[]=$ROW;
	 $AryReferenceValues[]=$ROW['OPTIONS'];
}

 $SQL_product="SELECT *  FROM `QcIHSWABResult` WHERE ReportID='$ID' "; 
$STMT_product=$iconn->query($SQL_product);
//$ARY_product=array();

 WHILE($ROW_product=$STMT_product->FETCH_ARRAY()){
	 $CheckValue=$ROW_product['SampletLocation'];
	 if(in_array($CheckValue,$AryReferenceValues,true)){
		 $ValueIndex= array_search($CheckValue,$AryReferenceValues);
		  unset($AryReferenceValues[$ValueIndex]);
	 }
	
}
		

  $count=count($value1);
$constant='Nil cfu/cm2';

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
	 
<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post" enctype="multipart/form-data">
                                    <div class="card-body">
									
										<div class="row">


             		 	<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"> Sample</label>
						
		 <select required class="form-control select2" name="SampletLocation" id="SampletLocation">	
				  <option value="">Select Product</option>
		 
 <?php      foreach($AryReferenceValues as $value){	
						$OPTIONS= $value;
						$VALUE= $AryReferenceValues[$count1]; 
						?>
			 
					<option value="<?php echo $value; ?>"><?php echo $value; ?></option>
					<?php  }?>
				<select> 
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Location</label>
                  <input required type="text" class="form-control" name='Location' id="Location" 
				  placeholder="Enter Location" value="">
                </div> 
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">TPC 37<sup>o</sup>C</label>
                  <input required type="text" class="form-control" name='TPC' id="TPC" 
				  placeholder="Enter TPC Value" value="">
                </div> 
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Stayphlococcusaures</label>
                  <input required type="text" class="form-control" name='Stayphloco' id="Stayphloco" 
				  placeholder="Enter Stayphlo Value" value="<?php echo $constant ?>">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ECOLI</label>
                  <input required type="text" class="form-control" name='ECOLI' id="ECOLI" 
				  placeholder="Enter ECOLI Value" value="<?php echo $constant ?>">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">COLIFORMS</label>
                  <input required type="text" class="form-control" name='COLIFORMS' id="COLIFORMS" 
				  placeholder="Enter COLIFORMS" value="<?php echo $constant ?>">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">V.C & V.P</label>
                  <input required type="text" class="form-control" name='VCnVP' id="VCnVP" 
				  placeholder="Enter V.C & V.P" value="Absent cfu/cm2">
                </div> 
                </div> 
				
			  <div class="col-xs-6 box-footer"  align="RIGHT">
                <button type="submit"  name='submit' class="btn btn-success">Add</button>
					 <a  id='eia_report' href="CommonSearchList.php?id=3" class="btn btn-primary">Submit</a>

              </div>
			  
  <!--<div class="col-xs-6 box-footer"  align="RIGHT">
	 <a  id='eia_report' href="CommonSearchList.php?id=3" class="btn btn-primary">SUBMIT RESULT </a>
   </div> -->       

            </form>
           
       <!-- end col -->
                        </div> <!-- end row -->
                        </div> <!-- end row -->
                        </div> <!-- end row -->
                        </div> <!-- end row -->
				<div class="row">
                            <div class="col-12">

        <div class="card">
          
        <div class="card-body" id="PIList">
                    

  <table id="key-datatable" class="table table-bordered table-hover">
              

    <thead>

      <tr>
      <th align='center'>S.No</th>
	  <th align='center'>Sample</th>
	  <th align='center'>Location</th>
	  <th align='center'>TPC</th>
	  <th align='center'>Stayphlococcusaures</th>
	  <th align='center'>ECOLI</th>
	  <th align='center'>COLIFORMS</th>
	  <th align='center'>V.C & V.P</th>
	 
	  <th align='center'>Remove</th>

	
      </tr>
    </thead>
    <tbody>
	<?php
	$S_NO=1;
	//echo $sql;
   while($RowSWABResult=$StmtSWABResult->fetch_array()){


	
	?>
	<tr >
			
	<td  id="<?php  echo $RowSWABResult['ID'];?>" > <?php echo $S_NO; ?>    </td>
	<td id="<?php  echo $RowSWABResult['ID'];?>" > <?php echo $RowSWABResult['SampletLocation']; ?> </td>
	<td id="<?php  echo $RowSWABResult['ID'];?>" > <?php echo $RowSWABResult['Location']; ?> </td>
	<td id="<?php  echo $RowSWABResult['ID'];?>" > <?php echo $RowSWABResult['TPC']; ?> </td>
	<td id="<?php  echo $RowSWABResult['ID'];?>" > <?php echo $RowSWABResult['Stayphloco']; ?> </td>
	<td id="<?php  echo $RowSWABResult['ID'];?>" > <?php echo $RowSWABResult['ECOLI']; ?> </td>
	<td id="<?php  echo $RowSWABResult['ID'];?>" > <?php echo $RowSWABResult['COLIFORMS']; ?> </td>
	<td id="<?php  echo $RowSWABResult['ID'];?>" > <?php echo $RowSWABResult['VCnVP']; ?> </td>
	
	<TD>
	<button id="<?php  echo $RowSWABResult['ID'];?>" 
	onClick="reply_click(this.id)" class="glyphicon glyphicon-trash
 btn btn-danger"> Delete</button>
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
<script>

function reply_click(clicked_id) {

   		 
		 alert(clicked_id);		  
		$.ajax({
		  url:'Ajax/AjaxIHDelete.php',
		  type:'POST',
		  data:{'TYPE':'SWAB','D_ID':clicked_id},
		  success:function(data){
			  console.log(data);
			  
			// $("#example1").html(data);
			 alert("Deleted");
			location.reload();
			}
			});					  
  }

  /*$(function () {
    $('#example1').DataTable()
    $('#example2').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : false,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })*/
</script>



<script>

function showData(data){
			
			$("#PIList").html(data);	

		var Stable=	$("#key-datatable").DataTable({
			keys:!0,
			language:{
				paginate:{
					previous:"<i class='mdi mdi-chevron-left'>",
					next:"<i class='mdi mdi-chevron-right'>"
					}
					},
					drawCallback:function(){
						$(".dataTables_paginate > .pagination").addClass("pagination-rounded")}});			
 
}

</script>


</html>

			

