<?php
session_start();
include "../includes/config.php";


 $B_TYPE=$_GET['TYPE'];
 $BAR_CODE_ID=$_GET['BID'];
 $LOT_ID=$_GET['ID'];
$today_date=date('Y-m-d');

if($B_TYPE == ''){ 
	$B_TYPE = 1;
}


$SqlPackingStyles="SELECT * from $ProductionPackingStyles ORDER BY TotalSlabs DESC";
$StmtPackingStyles=$iconn->query($SqlPackingStyles);



$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='CS' ORDER BY OPTIONS asc";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}



$SqlOptions="SELECT * FROM `OPTIONS` WHERE  DEPT='Production' ORDER BY OPTIONS asc";
$StmtOptions=$iconn->query($SqlOptions);
$ARYLineOptions=array();
WHILE($RowOptions=$StmtOptions->FETCH_ARRAY()){
	$ARYLineOptions[]=$RowOptions;
}



  $SqlIqfData="SELECT * from CS_PALLET_INPUT_DATA where PURCH_LOT_NUM ='$LOT_ID'ORDER BY DATE_TIME DESC";
$StmtIqfData=$iconn->query($SqlIqfData);

 while($row=$StmtIqfData->fetch_array()){
	 $ARY_STOCK[]=$row;
 }
  $UNIT='';
  $LOT_NUM='';
  
 $SIZE=count($ARY_STOCK);
 
 if($SIZE!=0){
	$SIZE=$SIZE -1;
	 $UNIT=$ARY_STOCK[$SIZE]['JME_MOURYA'];
     $LOT_NUM=$ARY_STOCK[$SIZE]['INT_LOT_NUMBER'];
 }
 
 
 
 
 


extract($_POST);
extract($_GET);


	 $SQL_ID="SELECT * FROM CS_BAR_CODE_DATA ORDER by BAR_CODE_ID DESC LIMIT 1";
	$STMT_ID=$iconn->query($SQL_ID);
	$LAST_ID_ROW=$STMT_ID->fetch_array();
	
	 $LAST_ID=$LAST_ID_ROW['BAR_CODE_ID'];
	
	

if(isset($_POST['submit'])){ 

	if($B_TYPE == 0){	
	
	$SQL_ID="SELECT * FROM CS_BAR_CODE_DATA ORDER by BAR_CODE_ID DESC LIMIT 1";
	$STMT_ID=$iconn->query($SQL_ID);
	$LAST_ID_ROW=$STMT_ID->fetch_array();
	
	 $LAST_ID=$LAST_ID_ROW['BAR_CODE_ID'];
	
	if($LAST_ID==''){
		$LAST_ID=1;
	}else{
		$LAST_ID=$LAST_ID+1;
	}
	
	
	date_default_timezone_set("Asia/Kolkata");
	$BAR_CODE_DATE = date("Y-m-d");	
	$BAR_CODE_VALUE = str_replace("-","",$BAR_CODE_DATE);	
	$BAR_CODE_VALUE=$BAR_CODE_VALUE.$LAST_ID;
	 
	 $sql_org_insert="INSERT INTO `CS_BAR_CODE_DATA`(`BAR_CODE_DATE`, `BAR_CODE_VALUE`, `BAR_CODE_STATUS`) 
	 VALUES ('$BAR_CODE_DATE','$BAR_CODE_VALUE','1')";
	 

	  $stmt1=$iconn->query($sql_org_insert);
	  $BAR_CODE_ID = $iconn->insert_id;
		legacyAuditLogInsertById('CS_BAR_CODE_DATA', 'BAR_CODE_ID', $BAR_CODE_ID, $_POST, [
			'ReferenceNo' => $BAR_CODE_ID,
			'Remarks' => 'Bar code created',
		], 'Production');
	
	}
	
	if($LOT_NUM!=''){
		$JME_MOURYA=$UNIT;
		$INT_LOT_NUMBER=$LOT_NUM;
	}
	  $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	  $T_DATE=$dateTime->format("Y-m-d  H:i:s"); 	
	 
	
		//$PACKING_STYLE = $TOTAL_SLABS.' X ' .$SLAB_WEIGHT.' '. $UOM;

	 $sql_org_insert="INSERT INTO `CS_PALLET_INPUT_DATA`
	 (`CARTONS_NUMBER`, `DUMMY_MASTER`, `LOOSE`,`NET_WT`, `COUNT`,
	 `GRADES`, `TYPE`, `TOTAL_SLABS`, `SLAB_WEIGHT`, `FORM`, 
	 `DATE_TIME`, `INT_LOT_NUMBER`,`TUB_NO`,`BRAND`, `PURCH_LOT_NUM`, `GROUP`,
	 `CATEGORY`, `JME_MOURYA`, `SHIFT`, `BAR_CODE_ID`, `UOM`,
	 `PACKING_STYLE`,`LINE`, `REMARKS`)
	 VALUES
	 ('$CARTONS_NUMBER', '$DUMMY_MASTER','$LOOSE','$NET_WT','$COUNT',
	 '$GRADES','$TYPE','$TOTAL_SLABS','$SLAB_WEIGHT','$FORM',
	 '$DATE_TIME','$INT_LOT_NUMBER','$TUB_NO','$BRAND','$LOT_ID','$GROUP',
	 '$CATEGORY','$JME_MOURYA','$SHIFT',0,'$UOM',
	 '$PACKING_STYLE','$LINE','$REMARKS' )";
	
	 $stmt1=$iconn->query($sql_org_insert);
	
	
	if($stmt1){		
	$iid = $iconn->insert_id;
	legacyAuditLogInsertById('CS_PALLET_INPUT_DATA', 'PALLEDT_ID', $iid, $_POST, [
		'ReferenceNo' => $iid,
		'Remarks' => 'Pallet input data created',
	], 'Production');
	
	header('Location: AdIQFRprt.php?ID='.$LOT_ID);
	}else{
		 echo("Error description: " . mysqli_error($iconn));
	}

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

        

<h2 align="center">Stock Data  <span style="color:Tomato; bold"><?php echo $invoice_details['PO_NUM'] ?></span></h3>



	</a>

			</a>



<ol class="breadcrumb">

        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Stock Data</li>

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
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Production Date</label>
                  <input required type="DATE" class="form-control" name='DATE_TIME' id="DATE_TIME" 
				   max="<?php echo $today_date; ?>"value="" placeholder="">
                </div>
				
				<div class="form-group col-sm-3" >              
                  <label for="exampleInputEmail1">Name of Station</label>
               <select class="form-control" name="JME_MOURYA" id="JME_MOURYA">
			    <?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='CS_STATIONS'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE'];
				?>
			 <option <?php if($UNIT==$VALUE){ ?> selected="selected" <?php }?>
			 value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					
					<?php  }}?>
				<select>
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Dummy/Master Cartons</label>
               <select class="form-control" name="DUMMY_MASTER" id="DUMMY_MASTER">
					 <?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='PACKING_TYPE'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
		
				<select>
                </div>
				
				 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Po Number</label>
                  <input   required type="text" class="form-control" 
				  name="INT_LOT_NUMBER" id="INT_LOT_NUMBER" placeholder="Enter PO Number" >
                </div>

				<!--	<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Po Number</label>
                  <input required  type="text" class="form-control" name="INT_LOT_NUMBER" id="INT_LOT_NUMBER" 
				  placeholder="Enter Internal Lot Number"value="<?php echo $LOT_NUM  ?>" >
                </div>
				-->
				
				
				
			  
				  <div class="form-group col-sm-3" >
				  
                  <label for="exampleInputEmail1">Variety</label>
                 <select class="form-control" name="FORM" id="FORM">
					 <?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='PRODUCT_VARIETY'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
				<select>
                </div>
			  		   
			  
			  <div class="form-group col-sm-3" >              
					 
					 
                  <label for="exampleInputEmail1">Type</label>
               <select class="form-control" name="TYPE" id="TYPE">
					 <?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='FREEZING'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
		
				<select>
                   </div>
				
				 <div class="form-group col-sm-3" >              
					 
					 
                  <label for="exampleInputEmail1">Group</label>
               <select class="form-control" name="GROUP" id="GROUP">
					 <?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='PRODUCT_GROUP'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
		
		
				<select>
                </div>
				
				 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Tub No</label>
                  <input   required type="text" class="form-control" 
				  name="TUB_NO" id="TUB_NO" placeholder="Enter TUB NO" >
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Brand</label>
                  <input   required type="text" class="form-control" 
				  name="BRAND" id="BRAND" placeholder="Enter BRAND" >
                </div>
				
				 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Enter Count</label>
                  <input   required type="text" class="form-control" 
				  name="COUNT" id="COUNT" placeholder="Enter COUNT" >
                </div>
				
				
				
				  <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Enter Grade</label>
                  <input   required type="text" class="form-control" 
				  name="GRADES" id="GRADES" placeholder="Enter grade" >
                </div>
	
			 	<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Packing Style</label>
				
		 <select class="form-control" name="PACKING_STYLE" id="PACKING_STYLE">		   
    <?php while($RowPackingStyles=$StmtPackingStyles->fetch_array()) { 
		
                 $PackingStyle=$RowPackingStyles['PackingStyle'];			
				 ?>  

				<option Value="<?php echo $PackingStyle;?>"
				<?php if( $PackingStyle==$PackingStyle)
					  echo 'selected="selected"'; ?>>
				<?php echo $PackingStyle;?></OPTION>				
				<?php } ?>
				</select> 
                </div>
				
				

				
				    <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">No.of.Cartons (or) Slabs</label>
                  <input required type="number" class="form-control" 
				  name='CARTONS_NUMBER' id="CARTONS_NUMBER" 
				  placeholder="Enter number of cartons (or) Slabs">
                </div>
				 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Net Weight</label>
                  <input   required type="text" class="form-control" 
				  name="NET_WT" id="NET_WT" placeholder="Enter NET WT" >
                </div>
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Loose Boxes</label>
                  <input required type="number" class="form-control" 
				  name='LOOSE' id="LOOSE" placeholder="Enter number of Loose Boxes">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Shift</label>
				  <select class="form-control" name="SHIFT" id="SHIFT">
					<option value="A SHIFT">A SHIFT</option>
					<option value="B SHIFT" >B SHIFT</option>
					<option value="C SHIFT" >C SHIFT</option>
					<option value="NIGHT SHIFT" >NIGHT SHIFT</option>
					
				<select>

			   </div>
			   
			   <div class="form-group col-sm-3" >              
				<label for="exampleInputEmail1">Category</label>
               <select class="form-control" name="CATEGORY" id="CATEGORY">
					<?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='PRODUCT_CATEGORY'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
					
					
		
				<select>
                </div>	

	            <div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Line Number</label>
                 <select class="form-control" name="LINE" id="LINE">
					<?php foreach($ARYLineOptions as $value){
					if($value['TYPE']=='IQF_LINE'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
							
				<select>
				  
                </div>
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Remarks</label>
                  <input  type="text" class="form-control" name='REMARKS' id="REMARKS" placeholder="Enter Remarks">
                </div>
			  
				
            
				
				
				</div>
				
              <!-- /.box-body -->


	<div class="col-xs-12 box-footer"  align="right">
	
	
				<button type="submit"  name='submit' class="btn btn-primary">Add To Pallet</button>
			
				<?php if($BAR_CODE_ID==''){
				}else{ ?>
						<a  id='' href="CsPalletInputData.php?TYPE=0&BID=" class="btn btn-primary">Create New Pallet</a>
				<a  id='' href="CsStoreLayout.php?BID=<?php echo $BAR_CODE_ID ?>" class="btn btn-primary">Place Pallet</a>
				
				<?php } ?>
              </div>
             
            </form>



</div>


</div>

<div id="tdetails" class="table-responsive" >  
        <div class="box">
          
        <div class="box-body">
                    

  <table id="example1" class="table table-bordered table-hover">
              

    <thead>

      <tr>
	  
	  <th>Lot Number</th>
	  <th>Grade</th>
	  <th>Freezing Type</th>
	  <th>packing Style</th>
	  <th>Variety</th>
	  <th>Date</th>
	  <th>Brand</th>
	  <th>Shift</th>
	  <th>Mcs</th>
	<th>Options</th>
	
	  	
      </tr>

    </thead>

    <tbody>

	<?php

	//echo $sql;

 foreach($ARY_STOCK as $row){

   $urltxt='id='.$LOT_ID.'&TYPE='.$B_TYPE.'&BID='.$B_TYPE;
	
	$PRODUCTION_DT= date("d-m-Y", strtotime($row['DATE_TIME']));
	;

	?>
	<tr>  
      
	<td > <?php echo $row['INT_LOT_NUMBER']; ?></td>
	<td > <?php echo $row['GRADES']; ?></td>
	<td > <?php echo $row['TYPE']; ?></td>
	<td > <?php echo $row['TOTAL_SLABS'] . ' X ' .$row['SLAB_WEIGHT']. ' '.$row['UOM'] ?></td>
	<td > <?php echo $row['FORM']; ?></td>
	<td > <?php echo $PRODUCTION_DT; ?></td>
	<td > <?php echo $row['BRAND']; ?></td>
	<td > <?php echo $row['SHIFT']; ?></td>
	<td > <?php echo $row['CARTONS_NUMBER']; ?></td>
	<td>		<a  id='' href="AdIQFRprtEdit.php?<?php echo $urltxt; ?>" class="btn btn-primary">Edit</a>
	<button id="<?php  echo $row['PALLEDT_ID'];?>" 
	onClick="reply(this.id)" class="glyphicon-remove btn btn-danger"> Delete</button>
	
		</td>
		
	
        </tr>

	  <?php 

   }
	  ?>

     </tbody>

  </table>
</div>
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
	
	var LOT_NUM='<?php echo $LOT_NUM ?>';
	//alert(LOT_NUM);
	
	if(LOT_NUM !=''){
	 document.getElementById("INT_LOT_NUMBER").readOnly  = false; 
	 document.getElementById("JME_MOURYA").disabled = false; 
	}


 

});   	

  function reply(clicked_id) {

   		 
		 alert(clicked_id);		  
		$.ajax({
		  url:'AjDltAl.php',
		  type:'POST',
		  data:{'TYPE':'VA','Q_ID':clicked_id},
		  success:function(data){
			  console.log(data);
			  
			 $("#CODE_LIST").html(data);
			 alert("Deleted");
			location.reload();
			}
			});					  
  } 


</script>

</html>
