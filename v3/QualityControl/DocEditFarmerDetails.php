<?php



session_start();

include "../includes/config.php";



//$INVOICE_ID=$_GET['PoID'];
$PoID=$_GET['PoID'];

/*$sql_invoice="SELECT * FROM `INVOICE_DATA` where ID='$PoID'";
$stmt_invoice=$iconn->query($sql_invoice);
$invoice_details=$stmt_invoice->fetch_array();*/



 $SQL_CODE_LIST="SELECT * FROM `$TblQcCodeList`as cl left JOIN $TblQcTracebilityList as tl ON tl.TraceID=cl.TracebilityID 
 left join $TblScrmPoData as pd on pd.PoID=cl.InvID
where InvID='$PoID'"; 
$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);
$row=$STMT_CODE_LIST->fetch_array();

$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);



/*$SQL_PO_LIST="SELECT * FROM `PO_VARIETY_LIST` where INV_ID='$PoID'";
$STMT_PO_LIST=$iconn->query($SQL_PO_LIST);


$sql_invoice="SELECT * FROM `INVOICE_DATA` where ID='$PoID'";
$stmt_invoice=$iconn->query($sql_invoice);
$invoice_details=$stmt_invoice->fetch_array();
*/



extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	$D_O_ANALYSIS = date("d-m-Y", strtotime($D_O_ANALYSIS));
	$D_O_COMPLEATION = date("d-m-Y", strtotime($D_O_COMPLEATION));
	
	$datas = explode('&',$FULL_NAME);
	 $FULL_NAME = $datas[0];
	 $SHORT_NAME = $datas[1];
	
	
	
	
	 /*$sql_org_insert="INSERT INTO `CODE_LIST_CREATE`(`INV_ID`, `SHORT_NAME`, `FULL_NAME`, 
	`BRAND`, `T_O_MCS`, `GRADES`, `MCS`, `PACKING`, `GLAZE_PERC`, `DAY_CODE`, `FARM_REG_ID`,`NAME_OF_FARMER`,`ADD_OF_FARMER`)
	VALUES ('$INVOICE_ID','$SHORT_NAME','$FULL_NAME','$BRAND','$T_O_MCS','$GRADES',
	'$MCS','$PACKING','$GLAZE_PERC','$DAY_CODE','$FARM_REG_ID','$NAME_OF_FARMER','$ADD_OF_FARMER')";
	
	

	$stmt1=$iconn->query($sql_org_insert);
	$M_T_REPORT_ID = $iconn->insert_id;
	*/
			header('Location: DocClCreate.php?PoID='.$PoID);

	
//echo $iid;
	
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

      

<h2 align="center">CODE LIST - <span style="color:Tomato; bold"><?php echo $row['PoNo'] ?> </h2>

			



<ol class="breadcrumb">

 	

        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Edit Farmer Details</li>

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
	  
	  <th>Tracebility</th>
	  <th>Farmer Name</th>
	  <th>Address</th>
	 <th>Actions</th>
	  
	

      </tr>

    </thead>

	

	

	 

			

			

			

    <tbody id="CODE_LIST">

	

	<?php

	//echo $sql;

	

   while($row=$STMT_CODE_LIST->fetch_array()){

	

	?>
	<tr>
	
	<?php $CODE_LIST_ID= $row['CLID']; ?>
	





 <td>
			<select class="form-control" name="FARM_REG_ID" id="FARM_REG_ID<?php echo $CODE_LIST_ID; ?>">				  
				  <?php 
				   $T_ID=$row['TracebilityID']; 
				 $SQL_trace="SELECT * FROM `$TblQcTracebilityList`";
                    $STMT_trace=$iconn->query($SQL_trace);
		
		while($row_trace=$STMT_trace->fetch_array()){?>
					<option value="<?php echo $row_trace['TraceID']?>"  
					<?php if($row_trace['TraceID']==$T_ID) echo 'selected="selected"'; ?>><?php echo $row_trace['Tracebility']?></option>
				  <?php } ?>
			<select>				  
				  
	</td>

	
				
				<td style="width:200px;" > <input  type="text" class="form-control"  name="NAME_OF_FARMER" 
	id="NAME_OF_FARMER<?php echo $CODE_LIST_ID; ?>"
				  placeholder="Enter Farmer Name" value="<?php echo $row['FarmerName']; ?>"> </td>

	<td > <input  type="text" class="form-control"  name="ADD_OF_FARMER" id="ADD_OF_FARMER<?php echo $CODE_LIST_ID; ?>"
				  placeholder="Enter Farmer Address" value="<?php echo $row['AddressContinued']; ?>"> </td>
	
				  
		
 <td style="width:250px;">  	
	 
	 	 <button id="<?php  echo $row['CLID'];?>" onClick="update_click(this.id)" class=" glyphicon glyphicon-edit btn btn-primary">  Update</button>
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
    <?php include "../includes/scripts.php"; ?>
   <?php include "../includes/AjaxLoader.php"; ?>


  </div>



</body>
<Script>

$('document').ready(function(){
    var arr=[];



  $('#FARM_REG_ID').change(function(){
var FarmId= $('#FARM_REG_ID').val();

 

   $.ajax({
    type: 'POST',
    url: 'AjaxFarmerList.php',
	dataType: 'json',
    data: {'Type':'FARMERS','FarmId' : FarmId},  
    success: function(response){
		 var myJSON = JSON.parse(JSON.stringify(response));
				 console.log(myJSON);

		 $("#NAME_OF_FARMER").val(myJSON['FarmerName']);
		 $("#ADD_OF_FARMER").val(myJSON['AddressContinued']);
		
	 }
  });
  
  
 });


/*$('#FULL_NAME').change(function() {
	var test=this.value;
		alert(test);
});*/


});


    	


    function update_click(clicked_id){
	  var ref_id='<?php echo $PoID?>';
	  
	  var FARM_REG_ID='#FARM_REG_ID'+clicked_id;
	  var NAME_OF_FARMER='#NAME_OF_FARMER'+clicked_id;
	  var ADD_OF_FARMER='#ADD_OF_FARMER'+clicked_id;
	
	 
	  var FARM_REG_ID=$(FARM_REG_ID+"").val();
	  var NAME_OF_FARMER=$(NAME_OF_FARMER+"").val();
	  var ADD_OF_FARMER=$(ADD_OF_FARMER+"").val();
		 
		
		  
		$.ajax({
		  url:'AjaxEditFarmerDetails.php',
		  type:'POST',
		  data:{
		  
		  'TYPE':'CODE_LIST_UPDATE',
		  'D_ID':clicked_id,
		  'ref_id':ref_id,
		  'FARM_REG_ID':FARM_REG_ID,
		  'NAME_OF_FARMER':NAME_OF_FARMER,
		  'ADD_OF_FARMER':ADD_OF_FARMER
		  
		  },
		  success:function(data){
			  console.log(data);
			  
			 $("#PACK_SIZE").html(data);
			 alert("Farmer List Updated");
			}
			});	
			
			
					  
  }




</script>



</html>

