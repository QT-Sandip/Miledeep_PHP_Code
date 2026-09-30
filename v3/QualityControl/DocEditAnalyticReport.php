<?php



session_start();

include "../includes/config.php";



$INVOICE_ID=$_GET['id'];


$SQL_INSP="SELECT * FROM `INSP_DATA` where INV_ID='$INVOICE_ID' order by NO_OF_SAMPLES asc" ;
$STMT_INSP=$iconn->query($SQL_INSP);







extract($_POST);
extract($_GET);



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

<h2 align="center">Analytic Report- <span style="color:Tomato; bold"><?php echo $invoice_details['PO_NUM'] ?> </h2>

			



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
          
        <div class="box-body">
                    

  <table id="example1" class="table table-bordered table-hover">
              

    <thead>

      <tr>

	  <th>Sample No</th>
	  <th>Frozen Wt</th>
	  <th>Deglazed Wt</th>
	  <th>Deglazed count</th>
	  <th>Bkn/Dmg(g)</th>
	  <th>Hanging Meat(g)</th>
	  <th>Blk/Spot Meat(g)</th>
	  <th>Sft/Shell(g)</th>
	  <th>Edit/Delete</th>
	  
	

      </tr>

    </thead>

	

    <tbody id="CODE_LIST">

	

	<?php

	//echo $sql;

	

   while  ($row   =$STMT_INSP->fetch_array()){

	

	?>  
	<tr>
	
	<?php $ID= $row['ID']; ?>
	

	
	<td > <?php echo  $row['NO_OF_SAMPLES'] , $row['TRACEABILITY']  ?> </td>
				  
				  
				  
	
	<td>
	<input  type="text" class="form-control"  name="FROZEN_WEIGHT" id="FROZEN_WEIGHT<?php echo $ID; ?>"
				   value="<?php echo $row['FROZEN_WEIGHT']; ?>"> 
			
	</td>	
	

	<td > <input  type="text" class="form-control"  name="DEGLAZED_WEIGHT" id="DEGLAZED_WEIGHT<?php echo $ID; ?>"
				   value="<?php echo $row['DEGLAZED_WEIGHT']; ?>">

				   </td>
				   <td > <input  type="text" class="form-control"  name="TOTAL_PIECES" id="TOTAL_PIECES<?php echo $ID; ?>"
				   value="<?php echo $row['TOTAL_PIECES']; ?>"> </td>
				   
				

<td > <input  type="text" class="form-control"  name="BROKEN_DAMAGE" id="BROKEN_DAMAGE<?php echo $ID; ?>"
				   value="<?php echo $row['BROKEN_DAMAGE']; ?>"> </td>					  

<td > <input  type="text" class="form-control"  name="HANGING_MEAT" id="HANGING_MEAT<?php echo $ID; ?>"
				  value="<?php echo $row['HANGING_MEAT']; ?>"> </td>				  
		
<td > <input  type="text" class="form-control"  name="BLACK_SPOT_MEAT" id="BLACK_SPOT_MEAT<?php echo $ID; ?>"
				 value="<?php echo $row['BLACK_SPOT_MEAT']; ?>"> </td>
				  
				  		
<td > <input  type="text" class="form-control"  name="SOFT_SHELL" id="SOFT_SHELL<?php echo $ID; ?>"
				   value="<?php echo $row['SOFT_SHELL']; ?>"> </td>
				  
 <td style="width:250px;">  	
	 	 <button id="<?php  echo $row['ID'];?>" onClick="reply_click(this.id)" class=" glyphicon glyphicon-remove btn btn-danger"> Delete</button>
	 	 <button id="<?php  echo $row['ID'];?>" onClick="update_click(this.id)" class=" glyphicon glyphicon-edit btn btn-primary">  Update</button>
	 </td>	
        

        </tr>

	  <?php 

   }

	  ?>

	  

	 

     </tbody>

  </table>
</div>





    </section>
	
	</div>

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
	  
	  var FROZEN_WEIGHT_ID='#FROZEN_WEIGHT'+clicked_id;
	  var DEGLAZED_WEIGHT_ID='#DEGLAZED_WEIGHT'+clicked_id;
	  var DEHYDRATION_ID='#DEHYDRATION'+clicked_id;
	  var DISCOLORATION_ID='#DISCOLORATION'+clicked_id;
	  var DECOMPOSITION='#DECOMPOSITION'+clicked_id;
	  var BROKEN_DAMAGE_ID='#BROKEN_DAMAGE'+clicked_id;
	  var HANGING_MEAT_ID='#HANGING_MEAT'+clicked_id;
	  var BLACK_SPOT_MEAT_ID='#BLACK_SPOT_MEAT'+clicked_id;
	  var SOFT_SHELL_ID='#SOFT_SHELL'+clicked_id;
	  var TOTAL_PIECES_ID='#TOTAL_PIECES'+clicked_id;
	  var UNIFORMITY_ID='#UNIFORMITY'+clicked_id;
	  
	 

	 var FROZEN_WEIGHT=$(FROZEN_WEIGHT_ID+"").val();
	  var DEGLAZED_WEIGHT=$(DEGLAZED_WEIGHT_ID+"").val();
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
		  url:'AjaxInsp.php',//AjaxBDdelete.php
		  type:'POST',
		  data:{
		  
		  'TYPE':'INSP_UPDATE',
		  'D_ID':clicked_id,
		  'INVOICE_ID':ref_id,
		  'FROZEN_WEIGHT':FROZEN_WEIGHT,
		  'DEGLAZED_WEIGHT':DEGLAZED_WEIGHT,
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

