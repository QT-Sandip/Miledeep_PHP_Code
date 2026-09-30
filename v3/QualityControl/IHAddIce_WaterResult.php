<?php
/*ini_set( 'display_errors', 1 );
error_reporting( E_ALL );*/
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitleFolder="Quality Control";
$PageTitle="SANITARY STANDARD ASSESSING OF UNIT WATER & ICE";

$ID=$_GET['ID'];


 $SQL_IceNWater="SELECT * FROM `QcIHIceNWaterResult` WHERE ReportID='$ID' "; 
$STMT_IceNWater=$iconn->query($SQL_IceNWater);



extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	 
      
      $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
      $TIME_STAMP=$dateTime->format("Y-m-d  H:i:s"); 

 if($Product==' WATER'){
		 $SortOrder=1;
	 }

else if($Product=='FLAKE ICE'){
		 $SortOrder=2;	
		 $LIMITS='1.TPC -20cfu/ml';
	 }
else if($Product=='TUBE ICE'){
		 $SortOrder=3;
		 $LIMITS='2.E.COLI-NIL';
	 }
else if($Product=='DRINKING WATER'){
		 $SortOrder=4;
		 $LIMITS='3.COLIFORMS-NIL';
	 }
	 
	 
	 else if($Product=='CHILL WATER'){
		 $SortOrder=5;
	 }


$ReportID=$ID;
	
	  $sql_org_insert="INSERT INTO `QcIHIceNWaterResult`(`ReportID`,
	 `Product`, `TPC`, `Stayphloco`, `ECOLI`, `COLIFORMS`, `VCnVP`,
	 `LIMITS`,`SortOrder`,`Location`)
	 VALUES ('$ReportID','$Product','$TPC','$Stayphloco','$ECOLI',
	 '$COLIFORMS','$VCnVP','$LIMITS','$SortOrder','$Location')"; 

	$stmt1=$iconn->query($sql_org_insert);
	$IceWaterID = $iconn->insert_id;
//echo $IceWaterID;


	if($stmt1){
		legacyAuditLogInsertById('QcIHIceNWaterResult', 'ID', $IceWaterID, $_POST, ['Remarks' => 'Ice & Water Result Added'], 'QualityControl');
		//header('Location: IHAddDlyWaterTPDMicroReport.php?ID='.$LOT_ID);
		header('Location: IHAddIce_WaterResult.php?ID='.$ID);
	}
}


$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE TYPE='PRODUCT' ";
$stmt_OPTIONS=$iconn->query($SQL_OPTIONS);	


//$ARY_OPTIONS=array();
WHILE($ROW=$stmt_OPTIONS->FETCH_ARRAY()){
	 //$ARY_OPTIONS[]=$ROW;
	 $AryReferenceValues[]=$ROW['OPTIONS'];
}

 $SQL_product="SELECT *  FROM `QcIHIceNWaterResult` WHERE ReportID='$ID' "; 
$STMT_product=$iconn->query($SQL_product);
//$ARY_product=array();

 WHILE($ROW_product=$STMT_product->FETCH_ARRAY()){
	 $CheckValue=$ROW_product['Product'];
	 if(in_array($CheckValue,$AryReferenceValues,true)){
		 $ValueIndex= array_search($CheckValue,$AryReferenceValues);
		  unset($AryReferenceValues[$ValueIndex]);
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
	 
<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post" enctype="multipart/form-data">
                                    <div class="card-body">
									
										<div class="row">


      
		 	<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"> Parameters</label>
						
		 <select required class="form-control" name="Product" id="Product">	
				  <option value="">Select Product</option>
		 
 <?php 
 



	foreach($AryReferenceValues as $value){
	
					
						$OPTIONS= $value;
						$VALUE= $AryReferenceValues[$count1]; 
						?>
			 
					<option value="<?php echo $value; ?>"><?php echo $value; ?></option>
					<?php  }?>
				<select> 
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Location</label>
                  <input  type="text" class="form-control" name='Location' id="Location" 
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
				  placeholder="Enter Stayphlo Value" value="Nil cfu/ml">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ECOLI</label>
                  <input required type="text" class="form-control" name='ECOLI' id="ECOLI" 
				  placeholder="Enter ECOLI Value" value="Nil cfu/ml">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">COLIFORMS</label>
                  <input required type="text" class="form-control" name='COLIFORMS' id="COLIFORMS" 
				  placeholder="Enter COLIFORMS" value="Nil cfu/ml">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">V.C & V.P</label>
                  <input required type="text" class="form-control" name='VCnVP' id="VCnVP" 
				  placeholder="Enter V.C & V.P" value="Absent cfu/ml">
                </div> 
                </div> 
				
			  <div class="col-xs-6 box-footer"  align="RIGHT">
                <button type="submit"  name='submit' class="btn btn-success">Add</button>
					 <a  id='eia_report' href="CommonSearchList.php?id=3" class="btn btn-primary">Submit </a>

              </div>
			  
  <!--<div class="col-xs-6 box-footer"  align="RIGHT">
	 <a  id='eia_report' href="CommonSearchList.php?id=4" class="btn btn-primary">SUBMIT RESULT </a>
   </div>  -->      

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
	  <th align='center'>Product</th>
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
   while($row_IceNWater=$STMT_IceNWater->fetch_array()){


	
	?>
	<tr >
			
	<td  id="<?php  echo $row_IceNWater['ID'];?>" > <?php echo $S_NO; ?>    </td>
	<td id="<?php  echo $row_IceNWater['ID'];?>" > <?php echo $row_IceNWater['Product']; ?> </td>
	<td id="<?php  echo $row_IceNWater['ID'];?>" > <?php echo $row_IceNWater['Location']; ?> </td>
	<td id="<?php  echo $row_IceNWater['ID'];?>" > <?php echo $row_IceNWater['TPC']; ?> </td>
	<td id="<?php  echo $row_IceNWater['ID'];?>" > <?php echo $row_IceNWater['Stayphloco']; ?> </td>
	<td id="<?php  echo $row_IceNWater['ID'];?>" > <?php echo $row_IceNWater['ECOLI']; ?> </td>
	<td id="<?php  echo $row_IceNWater['ID'];?>" > <?php echo $row_IceNWater['COLIFORMS']; ?> </td>
	<td id="<?php  echo $row_IceNWater['ID'];?>" > <?php echo $row_IceNWater['VCnVP']; ?> </td>
	
	<TD>
	<button id="<?php  echo $row_IceNWater['ID'];?>" 
	onClick="reply_click(this.id)" class="glyphicon glyphicon-trash btn btn-danger">
	Delete</button>
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
		  data:{'TYPE':'ICENWATER','D_ID':clicked_id},
		  success:function(data){
			  console.log(data);
			  showData(data);
			 //$("#example1").html(data);
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

/*function removeOption() {
			var option=$('#product').val();
  
  
            /* select the option with the 
            value of basic and remove the option*/
            /*$("#product option[value=option]").remove();
        }*/

        
</script>

</html>


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

			

