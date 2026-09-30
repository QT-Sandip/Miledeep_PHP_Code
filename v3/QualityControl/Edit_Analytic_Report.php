<?php



session_start();

include "../includes/config.php";


 $CL_ID=$_GET['id']; 
include "Constants.php";
//include "DocSamplingWsPrint.php";

 
  $sql_codelist="select * from CODE_LIST_CREATE where CODE_LIST_ID=$CL_ID"; 
  
  $stmt_codelist=$iconn->query($sql_codelist);
  $row_codelist=$stmt_codelist->fetch_array();
  $INV_ID=$row_codelist['INV_ID'];
  $GradesTemp=$row_codelist['GRADES'];
  $FARM_NUMBER=$row_codelist['FARM_REG_ID'];
  $GradesTemp=explode(',',$GradesTemp);
  
  foreach($GradesTemp as $Item){
	  if(!in_array($Item,$Grades,true)){
		$Grades[]=$Item;  
	  }
  }
  
  
  
  $TRACEABILITY=$row_codelist['FARM_REG_ID'];

 
 
 
  $SqlQcAnalyticReport="SELECT * FROM QC_ANALYTIC_REPORTS WHERE CODE_LIST_ID= $CL_ID  order by ID ASC";   
  
//INV_ID='$INVOICE_ID' order by NO_OF_SAMPLES asc" ;
$StmtQcAnalyticReport=$iconn->query($SqlQcAnalyticReport);





extract($_POST);
extract($_GET);

if(isset($_POST['submit'])){
	 $sql_org_insert="INSERT INTO `QC_ANALYTIC_REPORTS`(`INV_ID`, `CODE_LIST_ID`, `GRADE`, `DAY_CODE`,
	 `NO_OF_SAMPLES`, `FROZEN_WEIGHT`, `DEGLAZED_WEIGHT`,  `UNIFORMITY`,
	 `GLAZE`, `BROKEN_DAMAGE`, `BLACK_SPOT_MEAT`, `OTHER_DEFECTS`, `DEGLAZED_COUNT`) VALUES 
	 ('$INV_ID','$CL_ID','$GRADE','$TRACEABILITY','$NO_OF_SAMPLES','$FROZEN_WEIGHT','$DEGLAZED_WEIGHT',
	 '$UNIFORMITY','$GLAZE','$BROKEN_DAMAGE','$BLACK_SPOT_MEAT','$OTHER_DEFECTS','$DEGLAZED_COUNT')";
	 
	 // $sql_org_insert;
	$stmt1=$iconn->query($sql_org_insert);
		$iid = $iconn->insert_id;

	if($stmt1){
		legacyAuditLogInsertById('QC_ANALYTIC_REPORTS', 'ID', $iid, $_POST, ['Remarks' => 'Analytic Report Added'], 'QualityControl');
	}
		
		header('Location: Edit_Analytic_Report.php?id='.$CL_ID);
	

	
	
}


 ?>



<!DOCTYPE html>

<html>

<head>

<?php include "../includes/meta.php"; ?>

 </head>

<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">

   

<div class="wrapper">

<?php include "../includes/header.php"; ?>

  <!-- Left side column. contains the logo and sidebar -->

 <?php include "../includes/sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->

  <div class="content-wrapper">

    <!-- Content Header (Page header) -->

    <section class="content-header">

<h2 align="center">Analytic Report <span style="color:Tomato; bold"><?php echo $invoice_details['PO_NUM'] ?> </h2>

<label  for="exampleInputEmail1">Traceability: </label><?php echo $FARM_NUMBER ?>
<label  for="exampleInputEmail1">Number of samples: </label><?php echo $v_sample ?>
          
				
		
<ol class="breadcrumb">

 	

        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Edit Analytic Report </li>

      </ol>

    </section>

	
	
	
	<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title modheading"></h4>
            </div>
            <div class="modal-body checkdiv">
			

			
            
			</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

	

	



    <!-- Main content -->

    <section class="content">

      <!-- Small boxes (Stat box) -->
	  
	  

	  
	 
    



<div id="tdetails" class="table-responsive" >  
        <div class="box">
                    <form role="form" method="post">

        <div class="box-body">
                    

  <table id="tableForm" class="table table-bordered table-hover">
              

    <thead>

      <tr>

	 
	  <td>Declared Grade</td>
	  <td>Glaze%</td>
	  <td>Deglazed Wt</td>
	  <td>Deglazed count</td>
	 
	  <!--<th>TOTAL Pcs</th>-->
	  <td> B\S Ratio</td>	 
	  <td>Bkn/Dmg(g)</td>
	  <td>Blk/Spot Meat(g)</td>
		<!--<th>Sft/Shell(g)</th>-->
	  
	

      </tr>

    </thead>

	

    <tbody id="CODE_LIST">

	
  
	<tr>
	
	<?php $ID= $row['ID']; ?>
	


	
	<td>
	
	 <select class="form-control" name="GRADE" id="GRADE">				  
				  <?php foreach( $Grades as $Item){?>
					<option value="<?php echo $Item?>"><?php echo $Item?></option>
				  <?php } ?>

			
	</td>
	
	<td><input  type="text" class="form-control"  name="GLAZE" id="GLAZE"> </td>
	<td><input  type="text" class="form-control"  name="DEGLAZED_WEIGHT" id="DEGLAZED_WEIGHT"></td>
	<td> <input  type="text" class="form-control"  name="DEGLAZED_COUNT" id="DEGLAZED_COUNT"></td>
	<td > <input  type="text" class="form-control"  name="UNIFORMITY" id="UNIFORMITY"> </td>
	<td > <input  type="text" class="form-control"  name="BROKEN_DAMAGE" id="BROKEN_DAMAGE"> </td>					  
	<td > <input  type="text" class="form-control"  name="BLACK_SPOT_MEAT" id="BLACK_SPOT_MEAT"> </td>
				  
				  
        

        </tr>

	 
	  
	 

     </tbody>


  </table>
  
 	 	 <button id="add" type="submit"  name='submit' class=" glyphicon glyphicon-plus btn btn-success"> Add</button>

              </form>

</div>

<div id="tdetails" class="table-responsive" >  

        <div class="box">
		

          
        <div class="box-body">
             
  <table id="example1" class="table table-bordered table-hover">
              

    <thead>

      <tr>
	  
	 <th>Sample No	</th>
	  <!-- <th>Frozen Wt	</th>-->
	  <th>Declared size grade</th>
	   <th>Glaze%</th>
	  <th>Deglazed Wt	</th>
	  <th>Deglazed count	</th>
	 
	<!--  <th>TOTAL Pcs	</th>-->
	  <th> B\S Ratio</th>
	 <!-- <th>Dehydr(g)	</th>-->
	 <!-- <th>Disclr(g</th>
	  <th>Decompstn(g)	</th>-->
	  <th>Bkn/Dmg(g)	</th>
	 <!-- <th>Hangng Meat(g)	</th>-->
	  <th>Blk/Spot Meat(g)		</th>
	<!-- <th>Sft/Shell(g)		</th>-->
	  <th>Action</th>

      </tr>

    </thead>

    <tbody>

	<?php

$S_NO=1;
   while($row_data=$StmtQcAnalyticReport->fetch_array()){

	?>
	<tr>
		<?php $ID= $row_data['ID']; ?>

	<td > 
				  <?php echo  $S_NO; ?>
	
	 </td>
				  
	
		<td>
	<input  type="text" class="form-control"  name="GRADE" id="GRADE<?php echo $ID; ?>"
		value="<?php echo $row_data['GRADE']; ?>"> 
			
	</td>	
	
		<td>
	<input  type="text" class="form-control"  name="GLAZE" id="GLAZE<?php echo $ID; ?>"
				   value="<?php echo $row_data['GLAZE']; ?>"> 
			
	</td>	

	<td > <input  type="text" class="form-control"  name="DEGLAZED_WEIGHT" id="DEGLAZED_WEIGHT<?php echo $ID; ?>"
				   value="<?php echo $row_data['DEGLAZED_WEIGHT']; ?>">

				  
				   
	<td > <input  type="text" class="form-control"  name="DEGLAZED_COUNT" id="DEGLAZED_COUNT<?php echo $ID; ?>"
				   value="<?php echo $row_data['DEGLAZED_COUNT']; ?>">

				   </td>
				   
			   
				   
	  <!-- <td > <input  type="text" class="form-control"  name="TOTAL_PIECES" id="TOTAL_PIECES<?php echo $ID; ?>"
				   value="<?php// echo $row_data['TOTAL_PIECES']; ?>"> </td></td>-->
				   
				   <td > <input  type="text" class="form-control"  name="UNIFORMITY" id="UNIFORMITY<?php echo $ID; ?>"
				   value="<?php echo $row_data['UNIFORMITY']; ?>"> </td>


                  <td > <input  type="text" class="form-control" 
				  name="BROKEN_DAMAGE" id="BROKEN_DAMAGE<?php echo $ID; ?>"
				   value="<?php echo $row_data['BROKEN_DAMAGE']; ?>"> </td>					  
		  
		
                 <td > <input  type="text" class="form-control"  name="BLACK_SPOT_MEAT" id="BLACK_SPOT_MEAT<?php echo $ID; ?>"
				 value="<?php echo $row_data['BLACK_SPOT_MEAT']; ?>"> </td>
				  

	<td style="width:250px;">  	
	 	 <button id="<?php  echo $row_data['ID'];?>" onClick="update_click(this.id)" class=" glyphicon glyphicon-edit btn btn-primary">  Update</button>
	 </td>	
 
	   

        </tr>

	  <?php 
$S_NO++;
   }
	  ?>

     </tbody>

  </table>
    
</div>

</div>



    </section>
			
	
	</div>
<!--uma -->


    <!-- /.content -->

 

  <!-- /.content-wrapper -->

   <?php include "../includes/footer.php"; ?>

  </div>



</body>
<Script>

$('document').ready(function(){
    var arr=[];


$('#FULL_NAME').change(function() {
	var test=this.value;
		alert(test);
});
$('#add').on('click',function(){
    
    		$('form').submit();
    	});
});



    	

</script>





<script>
  function reply_click(clicked_id) {

  var INVOICE_ID='<?php echo $INVOICE_ID?>';		 
		 alert(clicked_id);		  
		$.ajax({
		  url:'AjaxINSP.php',
		  type:'POST',
		  data:{'TYPE':'INSP_DELETE','D_ID':clicked_id,'INVOICE_ID':INVOICE_ID},
		  success:function(data){
			  console.log(data);
			  
			 $("#CODE_LIST").html(data);
			 alert("Deleted");
			}
			});					  
  }
</script>

<script>

    function update_click(clicked_id){
	  var ref_id='<?php echo $INVOICE_ID?>';
	  var CL_id='<?php echo $CL_ID?>';
	  
	 // var FROZEN_WEIGHT_ID='#FROZEN_WEIGHT'+clicked_id;
	  var GRADE_ID='#GRADE'+clicked_id;
	   var GLAZE_ID='#GLAZE'+clicked_id;
	  var DEGLAZED_WEIGHT_ID='#DEGLAZED_WEIGHT'+clicked_id;
	
	  var DEGLAZED_COUNT_ID='#DEGLAZED_COUNT'+clicked_id;
	  var TPC_VALUE_ID='#TPC_VALUE'+clicked_id;
	  var DEHYDRATION_ID='#DEHYDRATION'+clicked_id;
	  var DISCOLORATION_ID='#DISCOLORATION'+clicked_id;
	  var DECOMPOSITION='#DECOMPOSITION'+clicked_id;
	  var BROKEN_DAMAGE_ID='#BROKEN_DAMAGE'+clicked_id;
	  var HANGING_MEAT_ID='#HANGING_MEAT'+clicked_id;
	  var BLACK_SPOT_MEAT_ID='#BLACK_SPOT_MEAT'+clicked_id;
	  var SOFT_SHELL_ID='#SOFT_SHELL'+clicked_id;
	  var TOTAL_PIECES_ID='#TOTAL_PIECES'+clicked_id;
	  var UNIFORMITY_ID='#UNIFORMITY'+clicked_id;
	  
	 

	  //var FROZEN_WEIGHT=$(FROZEN_WEIGHT_ID+"").val();
	  var GRADE=$(GRADE_ID+"").val();
	  var GLAZE=$(GLAZE_ID+"").val();
	  var DEGLAZED_WEIGHT=$(DEGLAZED_WEIGHT_ID+"").val();
	
	  var DEGLAZED_COUNT=$(DEGLAZED_COUNT_ID+"").val();
	  var TPC_VALUE=$(TPC_VALUE_ID+"").val();
	  var DEHYDRATION=$(DEHYDRATION_ID+"").val();
	  var DISCOLORATION=$(DISCOLORATION_ID+"").val();
	  var DECOMPOSITION=$(DECOMPOSITION+"").val();
	  var BROKEN_DAMAGE=$(BROKEN_DAMAGE_ID+"").val();
	  var HANGING_MEAT=$(HANGING_MEAT_ID+"").val();
	  var BLACK_SPOT_MEAT=$(BLACK_SPOT_MEAT_ID+"").val();
	  var SOFT_SHELL=$(SOFT_SHELL_ID+"").val();
	  var TOTAL_PIECES=$(TOTAL_PIECES_ID+"").val();
	  var UNIFORMITY=$(UNIFORMITY_ID+"").val();
		 
		//alert(FARM_REG_ID);
		  
		$.ajax({
		  url:'AjAnlytc.php',//AjaxBDdelete.php
		  type:'POST',
		  data:{ 
		  
		  'TYPE':'INSP_UPDATE', 
		  'D_ID':clicked_id,
		  'INVOICE_ID':ref_id,
		  'CODE_LIST_ID':CL_id,
		  'GRADE':GRADE,
		  'GLAZE' :GLAZE,
		  'DEGLAZED_WEIGHT' :DEGLAZED_WEIGHT,
		  'DEGLAZED_COUNT':DEGLAZED_COUNT,
		  'TPC_VALUE':TPC_VALUE,
		  'DEHYDRATION':DEHYDRATION,
		  'DISCOLORATION':DISCOLORATION,
		  'DECOMPOSITION':DECOMPOSITION,
		  'BROKEN_DAMAGE':BROKEN_DAMAGE,
		  'HANGING_MEAT':HANGING_MEAT,
		  'BLACK_SPOT_MEAT':BLACK_SPOT_MEAT,
		  'TOTAL_PIECES':TOTAL_PIECES,
		  'UNIFORMITY':UNIFORMITY,
		 'SOFT_SHELL':SOFT_SHELL
		  
		  },
		  success:function(data){
			  console.log(data);
			  
			 $("#example1").html(data);
			 alert("Inspection Report Updated");
			}
			});	
			
			
					  
  }
</script>





</html>

