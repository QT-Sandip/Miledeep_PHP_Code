<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$PageTitleFolder="Quality Control";
$PageTitle="DAILY WATER ANALYSIS REPORT";
$ReportID=$_GET['ReportID'];


  $SqlMicro="SELECT * FROM `QcIHTPCMicroAnalysis` WHERE ReportID='$ReportID' "; 
$StmtMicro=$iconn->query($SqlMicro);

$today_date=date('Y-m-d');

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
      $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
      $TIME_STAMP=$dateTime->format("Y-m-d  H:i:s"); 
if($PARAMETER=='PH  VALUE'){
		 
		 $UNITS='H+ Conc';
		 $LIMITS='6.5 to 8.5';
		 		 $SortOrder=1;

	 }
else if($PARAMETER=='TEMPERATURE'){
		 
		 $UNITS='C';
		 $LIMITS='25 to 30C';
		 		 $SortOrder=2;

	 }
else if($PARAMETER=='COLOUR'){
		 
		 $UNITS='-';
		 $LIMITS='-';
		 		 $SortOrder=3;

	 }
else if($PARAMETER=='SALINITY'){
		 
		 $UNITS='ppt';
		 $LIMITS='5 ppt';
		 		 $SortOrder=4;

	 }
else if($PARAMETER=='TOTAL DISSOLVED SOLIDS'){
		 
		 $UNITS='ppm';
		 $LIMITS='300';
		 		 $SortOrder=5;

	 }
else if($PARAMETER=='HARDNESS'){
		 
		 $UNITS='ppm';
		 $LIMITS='250';
		 		 $SortOrder=6;

	 }
else if($PARAMETER=='CHLORINE'){
		 
		 $UNITS='ppm';
		 $LIMITS='5 ppm';
		 		 $SortOrder=7;

	 }
else if($PARAMETER=='TPC'){
		 
		 $UNITS='CFU/ML';
		 $LIMITS='20CFU/ML';
		 		 $SortOrder=8;

	 }
else if($PARAMETER=='E-COLI'){
		 
		 $UNITS='CFU/ML';
		 $LIMITS='NIL';
		 $SortOrder=9;
	 }


	
	 $SqlInsert="INSERT INTO `QcIHTPCMicroAnalysis`(`ReportID`,`Parameter`,`Result1`, `Result2`, `Result3`,`Result4`,`Units`, `Limits`,`SortOrder`) 
	 values ('$ReportID','$PARAMETER','$RESULT_I','$RESULT_II','$RESULT_III','$Result4','$UNITS','$LIMITS','$SortOrder')";
	
	

	$stmt1=$iconn->query($SqlInsert);
	$MB_REPORT_ID = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('QcIHTPCMicroAnalysis', 'ID', $MB_REPORT_ID, $_POST, ['Remarks' => 'TPC Micro Analysis Added'], 'QualityControl');
		//header('Location: IHAddDlyWaterTPDMicroReport.php?ID='.$LOT_ID);
		header('Location: IHAddDlyWaterTPDMicroReport.php?ReportID='.$ReportID);
	}
}

			
$SqlOptions="SELECT * FROM `OPTIONS` WHERE TYPE='PARAMETERS' ORDER BY `SortOrder` ASC";
$StmtOptions=$iconn->query($SqlOptions);	


$ARY_OPTIONS=array();
WHILE($ROW=$StmtOptions->FETCH_ARRAY()){
	 $AryReferenceValues[]=$ROW['OPTIONS'];
}


$SqlIHMicroAnalysis="SELECT * FROM `QcIHTPCMicroAnalysis` WHERE ReportID='$ReportID'";
$stmtIHMicroAnalysis=$iconn->query($SqlIHMicroAnalysis);	



WHILE($RowParameter=$stmtIHMicroAnalysis->FETCH_ARRAY()){
	 $CheckValue=$RowParameter['Parameter'];
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


     
		 	<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1"> Parameters</label>
						
		 <select required class="form-control select2" name="PARAMETER" id="PARAMETER">	
				  <option value="">Select Parameter</option>
		 
 <?php foreach($AryReferenceValues as $value){
	
					
						$OPTIONS= $value;
						$VALUE= $AryReferenceValues[$count1];
						 
			 		/*if($value['TYPE']=='PARAMETERS'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE'];*/ ?>
			 
					<option value="<?php echo $value ?>"><?php echo $value ?></option>
					<?php  }?>
				<select> 
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Result1</label>
                  <input required type="text" class="form-control" name='RESULT_I' id="RESULT_I" 
				  placeholder="ENTER RESULT1" value="">
                </div> 
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Result2</label>
                  <input required type="text" class="form-control" name='RESULT_II' id="RESULT_II" 
				  placeholder="ENTER RESULT2" value="">
                </div> 
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Result3</label>
                  <input required type="text" class="form-control" name='RESULT_III' id="RESULT_III" 
				  placeholder="ENTER RESULT3" value="">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Result4</label>
                  <input required type="text" class="form-control" name='Result4' id="Result4" 
				  placeholder="ENTER Result4" value="">
                </div> 
                </div> 
				
				
			  <div class="col-xs-6 box-footer"  align="RIGHT">
                <button type="submit"  name='submit' class="btn btn-success ">Add</button>
			<a  id='eia_report' href="CommonSearchList.php?id=5" class="btn btn-primary">Submit </a>

              </div>
			  
  <!--<div class="col-xs-6 box-footer"  align="RIGHT">
	 <a  id='eia_report' href="CommonSearchList.php?id=5" class="btn btn-primary">SUBMIT RESULT </a>
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

	  <th align='center' >S.No</th>
	  <th align='center'>Parameter</th>
	  <th align='center'>Result-1</th>
	  <th align='center'>Result-2</th>
	  <th align='center'>Result-3</th>
	  <th align='center'>Result-4</th>
	 
	  <th align='center'>Remove</th>

	
      </tr>
    </thead>
    <tbody>
	<?php
	$S_NO=1;
	//echo $sql;
   while($RowMicro=$StmtMicro->fetch_array()){


	
	?>
	<tr >
			
	<td  id="<?php  echo $RowMicro['ID'];?>" > <?php echo $S_NO; ?>    </td>
	<td id="<?php  echo $RowMicro['ID'];?>" > <?php echo $RowMicro['Parameter']; ?> </td>
	<td id="<?php  echo $RowMicro['ID'];?>" > <?php echo $RowMicro['Result1']; ?> </td>
	<td id="<?php  echo $RowMicro['ID'];?>" > <?php echo $RowMicro['Result2']; ?> </td>
	<td id="<?php  echo $RowMicro['ID'];?>" > <?php echo $RowMicro['Result3']; ?> </td>
	<td id="<?php  echo $RowMicro['ID'];?>" > <?php echo $RowMicro['Result4']; ?> </td>
	
	<TD>
	<button id="<?php  echo $RowMicro['ID'];?>" 
	onClick="reply_click(this.id)" class="glyphicon glyphicon-trash btn btn-danger"> Delete</button>
	</TD>
	
	
    </tr>
	
	
	  <?php 
	  $S_NO++;

   }  ?>
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
		  data:{'TYPE':'TPCWATER','D_ID':clicked_id},
		  success:function(data){
			  console.log(data);
			  showData(data);
			 //$("#example1").html(data);
			 alert("Deleted");
			location.reload();
			}
			});					  
  }
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

			

