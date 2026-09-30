<?php
session_start();
include "../includes/config.php";

$B_TYPE=$_GET['TYPE'];
 $BAR_CODE_ID=$_GET['BID'];

 $plt_id= $_GET['id'];

if($B_TYPE == ''){ 
	$B_TYPE = 1;
}

$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='CS' ORDER BY OPTIONS asc";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}

$SQL_PALLET_IP_DATA="SELECT * from CS_PALLET_INPUT_DATA where BAR_CODE_ID ='$BAR_CODE_ID'";
$STMT_PALLET_IP_DATA=$iconn->query($SQL_PALLET_IP_DATA);
 $COUNT=$STMT_PALLET_IP_DATA->num_rows;

 $SQL_PALLET_IP_DATA="SELECT * from CS_PALLET_INPUT_DATA where PURCH_LOT_NUM ='$plt_id'";
$STMT_PALLET_IP_DATA=$iconn->query($SQL_PALLET_IP_DATA);



extract($_POST);
extract($_GET);


	$ROW=$STMT_PALLET_IP_DATA->fetch_array();
	
	
if(isset($_POST['submit'])){ 
$urltxt='TYPE='.$TYPE.'&BID='.$BAR_CODE_ID;

	$query='';
	if($COUNT == 1){
		$query="`JME_MOURYA`='$JME_MOURYA',`INT_LOT_NUMBER`='$INT_LOT_NUMBER',";
	}

$PACKING_STYLE = $TOTAL_SLABS.' X ' .$SLAB_WEIGHT.' '. $UOM;
	 
	 $sql_org_insert="UPDATE `CS_PALLET_INPUT_DATA` SET 
	 `CARTONS_NUMBER`='$CARTONS_NUMBER',
	 `DUMMY_MASTER`='$DUMMY_MASTER',
	 `GRADES`='$GRADES',
	 `TYPE`='$F_TYPE',
	 `TOTAL_SLABS`='$TOTAL_SLABS',
	 `SLAB_WEIGHT`='$SLAB_WEIGHT',
	 `FORM`='$FORM',
	 `DATE_TIME`='$DATE_TIME',
	  $query
	 `PURCH_LOT_NUM`='$PURCH_LOT_NUM',
	 `GROUP`='$GROUP',
	  `BRAND`='$BRAND',
	  `TUB_NO`='$TUB_NO',
	  `NET_WT`='$NET_WT',
	 `LINE`='$LINE',
	  `LOOSE`='$LOOSE',
	 `SHIFT`='$SHIFT',
	 `UOM`='$UOM',
	 `PACKING_STYLE`='$PACKING_STYLE',
	 `CATEGORY`='$CATEGORY',
	 `REMARKS`='$REMARKS' WHERE PALLEDT_ID='$plt_id'";
	 
	
	
	 $stmt1=$iconn->query($sql_org_insert);
	
	if($stmt1){		
	$iid = $iconn->insert_id;
	
	header('Location: AdIQFRprt.php?ID='.$plt_id);
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
            <div class="modal-body che-ckdiv">
			

			
            
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
                  <label for="exampleInputEmail1">Name of Station</label>
               <select class="form-control" name="JME_MOURYA" id="JME_MOURYA">
			   <?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='CS_STATIONS'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE'];
				?>
			 <option <?php if($ROW['JME_MOURYA']==$VALUE){ ?> selected="selected" <?php }?>
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
			 
					<option <?php if($ROW['DUMMY_MASTER']==$VALUE){ ?> selected="selected" <?php }?>
			 value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
					
				<select>
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Internal Lot Number</label>
                  <input    type="text" class="form-control" value="<?php echo $ROW['INT_LOT_NUMBER']; ?>" name="INT_LOT_NUMBER" id="INT_LOT_NUMBER" placeholder="Enter Internal LOT Number" >
                </div>
				
					<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Purchase Lot Number</label>
                  <input required  type="text" class="form-control" value="<?php echo $ROW['PURCH_LOT_NUM']; ?>" name="PURCH_LOT_NUM" id="PURCH_LOT_NUM" 
				  placeholder="Enter Purchase Lot Number" >
                </div>
				
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Production Date</label>
                  <input required  type="date" class="form-control"value="<?php echo $ROW['DATE_TIME']; ?>" name="DATE_TIME" id="DATE_TIME" 
				  placeholder="Enter Production Date" >
                </div>
				
				
			  
				  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Variety</label>
                 <select class="form-control" name="FORM" id="FORM">
					 <?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='PRODUCT_VARIETY'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
						<option <?php if($ROW['FORM']==$VALUE){ ?> selected="selected" <?php }?>
			 value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
				<select>
                </div>
			  
			  <div class="form-group col-sm-3" >              
					 
					 
                  <label for="exampleInputEmail1">Type</label>
               <select class="form-control" name="F_TYPE" id="F_TYPE">
					<?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='FREEZING'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option <?php if($ROW['TYPE']==$VALUE){ ?> selected="selected" <?php }?>
			 value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
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
			 
					<option <?php if($ROW['GROUP']==$VALUE){ ?> selected="selected" <?php }?>
			 value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
		
					
		
				<select>
                </div>
				
				 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Tub No</label>
                  <input   required type="text" class="form-control" 
				  name="TUB_NO" value="<?php echo $ROW['TUB_NO']; ?>" id="TUB_NO" placeholder="Enter TUB NO" >
                </div>
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Brand</label>
                  <input   required type="text" class="form-control" 
				  name="BRAND" value="<?php echo $ROW['BRAND']; ?>" id="BRAND" placeholder="Enter BRAND" >
                </div>
				
				  <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Enter Grade</label>
                  <input   required type="text" class="form-control" value="<?php echo $ROW['GRADES']; ?>" name="GRADES" id="GRADES" placeholder="Enter Destination Country" >
                </div>
	
	
               <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Total No of Slabs In Carton</label>
                  <input required type="number" class="form-control" value="<?php echo $ROW['TOTAL_SLABS']; ?>" name='TOTAL_SLABS' id="TOTAL_SLABS" 
				  placeholder="Enter TOTAL  PACKINGS">
                </div>
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Weight of Each Slab</label>
                  <input required type="decimal" class="form-control" value="<?php echo $ROW['SLAB_WEIGHT']; ?>" name='SLAB_WEIGHT' id="SLAB_WEIGHT" 
				  placeholder="WEIGHT OF EACH PACKING">
                </div>			  
					
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Loose Boxes</label>
                  <input required type="number" class="form-control" 
				  name='LOOSE'  value="<?php echo $ROW['LOOSE']; ?>" id="LOOSE" placeholder="Enter number of Loose Boxes">
                </div> 
		
				<div class="form-group col-sm-3" >              
				
                  <label for="exampleInputEmail1">UOM</label>
               <select class="form-control"  name="UOM" value="<?php echo $ROW['UOM']; ?>"id="UOM" >
				
					<option value="GRAMS">GRAMS</option>
				<option value="KGS">KGS</option>
				<option value="LBS">LBS</option>

					
					
				<select>
                </div>
					
					 <div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Line Number</label>
                  <input required  type="text" class="form-control" 
				  name="LINE" value="<?php echo $ROW['LINE']; ?>"id="LINE" 
				  placeholder="Enter Line Number" >
                </div>
					
				  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">No. of. Cartons</label>
                  <input required type="number" class="form-control" value="<?php echo $ROW['CARTONS_NUMBER']; ?>" name='CARTONS_NUMBER' id="CARTONS_NUMBER" placeholder="Enter Test Report No">
                </div>
				 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Net Weight</label>
                  <input   required type="text" class="form-control" 
				  name="NET_WT" value="<?php echo $ROW['NET_WT']; ?>" id="NET_WT" placeholder="Enter NET WT" >
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Shift</label>
				  <select class="form-control" name="SHIFT" id="SHIFT">
					<option <?php if($ROW['SHIFT']=='A SHIFT'){ ?> selected="selected" <?php }?> value="A SHIFT">A SHIFT</option>
					<option <?php if($ROW['SHIFT']=='B SHIFT'){ ?> selected="selected" <?php }?> value="B SHIFT" >B SHIFT</option>
					<option <?php if($ROW['SHIFT']=='C SHIFT'){ ?> selected="selected" <?php }?> value="C SHIFT" >C SHIFT</option>
					<option <?php if($ROW['SHIFT']=='NIGHT SHIFT'){ ?> selected="selected" <?php }?> value="NIGHT SHIFT" >NIGHT SHIFT</option>
					
				<select>

			   </div>
			   
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Category</label>
				  <select class="form-control" name="CATEGORY" id="CATEGORY">
					<?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='PRODUCT_CATEGORY'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option <?php if($ROW['CATEGORY']==$VALUE){ ?> selected="selected" <?php }?>
			 value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
					
				<select>

			   </div>
			   
			    
				
				
				 <div class="form-group col-sm-6" >
                  <label for="exampleInputEmail1">Remarks</label>
                  <input required type="text" class="form-control" value="<?php echo $ROW['REMARKS']; ?>" name='REMARKS' id="REMARKS" placeholder="Enter Test Report No">
                </div>
			 
				
            
				
				
				</div>
				
              <!-- /.box-body -->


	<div class="col-xs-12 box-footer"  align="right">
	
	
				<button type="submit"  name='submit' class="btn btn-primary">Update</button>
			
				  </div>
             
            </form>



</div>


</div>

	</div>

    <!-- /.content -->
  <!-- /.content-wrapper -->

   <?php include "../includes/footer.php"; ?>

  </div>

</body>



<Script>

$('document').ready(function(){
	
	var COUNT='<?php echo $COUNT ?>';
	//alert(LOT_NUM);
	
	if(COUNT > 1){
	 document.getElementById("INT_LOT_NUMBER").readOnly  = true; 
	 document.getElementById("JME_MOURYA").disabled = true; 
	}



});   	

</script>

</html>
