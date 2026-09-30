<?php



session_start();

include "../includes/config.php";


$today_date=date('Y-m-d');

//$INVOICE_ID=$_GET['id'];
$LOT_ID=$_GET['ID'];
$sql_LOTS="SELECT * FROM `inputmaterial_details` where id='$LOT_ID'";
$stmt_LOTS=$iconn->query($sql_LOTS);
$ROW_LOTS=$stmt_LOTS->fetch_array();


$SQL_VALUE_ADDITION="SELECT * FROM `PPC_COOKED_DATA` where ID='$LOT_ID'  ORDER BY ID DESC";
$STMT_VALUE_ADDITION=$iconn->query($SQL_VALUE_ADDITION);


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
      $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
      $TIME_STAMP=$dateTime->format("Y-m-d  H:i:s"); 
	 
$LOT_ID=$LOT_ID;
	
	 $sql_org_insert="INSERT INTO `PPC_COOKED_DATA`
	 ( `LOT_ID`, `PURCHASE_DATE`, 
	 `PROCESS_DATE`, `VARIETY`, `GRADE`, `SOCK_IN_QTY`, 
	 `SOCK_OUT_QTY`, `TEMPERATURE`, `NET_QTY`, `START_TIME`,
	 `END_TIME`, `BREAK`, `SHIFT`) 
	 VALUES ('$LOT_ID','$PURCHASE_DATE',
	 '$TIME_STAMP','$VARIETY','$GRADE',
	 '$SOCK_IN_QTY', '$SOCK_OUT_QTY','$TEMPERATURE',
	 '$NET_QTY','$START_TIME','$END_TIME',
	 '$BREAK','$SHIFT')";
	
	

	$stmt1=$iconn->query($sql_org_insert);
	$MB_REPORT_ID = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('PPC_COOKED_DATA', 'ID', $MB_REPORT_ID, $_POST, [
			'ReferenceNo' => $MB_REPORT_ID,
			'Remarks' => 'Cooking report created',
		], 'PreProcessing');
		header('Location: AddCookngReport.php?ID='.$LOT_ID);
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

<h2 align="center"><span style="color:Tomato; bold">COOKING REPORT</span></h3>

	</a>
		</a>

<ol class="breadcrumb">

        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Cooking Report</li>

      </ol>

    </section>


    <!-- Main content -->

    <section class="content">
      <!-- Small boxes (Stat box) -->
    <div id="tdetails" class="table-responsive" >  
        <div class="box">		
          <form role="form" method="post">		             
			    <div class="box-body">
				
					 	
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Select Variety</label>
                   <select class="form-control" name="VARIETY" id="VARIETY">
                    <option Value="EZPL">EZPL</OPTION>
                    <option Value="PD">PD</OPTION>
                    <option Value="PD-BUTTERFLY">PD-BUTTERFLY</OPTION>
                    <option Value="PDTO">PDTO</OPTION>
                    <option Value="PDTO 50%-PARCUT">PDTO 50%-PARCUT</OPTION>
                    <option Value="PDTO 70%-BUTTERFLY">PDTO 70%-BUTTERFLY</OPTION>
				    <option Value="PUD">PUD</OPTION>
				    <option Value="PULL VEIN">PULL VEIN</OPTION>
				    <option Value="OTHER">OTHER</OPTION>
				   <select> 
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Select Shift</label>
                  <select class="form-control" name="SHIFT" id="SHIFT">
                    <option Value="A-SHIFT">A-SHIFT</OPTION>
                    <option Value="B-SHIFT">B-SHIFT</OPTION>
                    <option Value="C-SHIFT">C-SHIFT</OPTION>
                    <option Value="GENERAL SHIFT">GENERAL SHIFT</OPTION>
                    <option Value="NIGHT SHIFT">NIGHT SHIFT</OPTION>
					<select> 
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">RM Date</label>
                  <input type="date" class="form-control" name='PURCHASE_DATE' id="PURCHASE_DATE" 
				  max="<?php echo $today_date; ?>"placeholder="" value="">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Soak in Qty(Kgs)</label>
                  <input required type="text" class="form-control" name='SOCK_IN_QTY' id="SOCK_IN_QTY" 
				  value="" placeholder="Enter Soak in weight in Kgs">
                </div> 
				
			
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Grade </label>
                  <input required type="text" class="form-control" name='GRADE' id="GRADE" 
				  value="" placeholder="Enter Grade">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Soak Out Qty(Kgs) </label>
                  <input required type="text" class="form-control" name='SOCK_OUT_QTY' id="SOCK_OUT_QTY" 
				  value="" placeholder="Enter Soak in weight in Kgs">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Temperature</label>
                  <input required type="text" class="form-control" name='TEMPERATURE' id="TEMPERATURE" 
				  value="" placeholder="Enter Cooking Temperature">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Net Quantity</label>
                  <input required type="text" class="form-control" name='NET_QTY' id="NET_QTY" 
				  value="" placeholder="Enter Weight after cooking">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Start Time</label>
                  <input required type="text" class="form-control" name='START_TIME' id="START_TIME" 
				  value="" placeholder="Enter Start Time">
                </div> 
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">End Time</label>
                  <input required type="text" class="form-control" name='END_TIME' id="END_TIME" 
				  value="" placeholder="Enter End Time">
                </div> 
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Break time</label>
                  <input required type="text" class="form-control" name='BREAK' id="BREAK" 
				  value="" placeholder="Enter Break time">
                </div> 
				

			  <div class="col-xs-12 box-footer"  align="center">
                <button type="submit"  name='submit' class="btn btn-primary">ADD</button>
              </div>
            </form>

</div>


	
<div id="tdetails" class="table-responsive col-sm-12" >  
    <div class="box">
          
        <div class="box-body">
                    

  <table id="example1" class="table table-bordered table-hover" ALIGN="CENTER">
    <thead >
      <tr >
	  
	  <th align='center' >S.No</th>
	  <th align='center'>Lot number</th>
	   <th align='center'>RM Date</th>
	  <th align='center'>Variety</th>
	  <th align='center'>Grade</th>
	  <th align='center'>Soak in Qty(Kgs) </th>
	  <th align='center'>Soak out Qty(Kgs)</th>
	  <th align='center'>Yeild</th>
	  <th align='center'>Temperature</th>
	  <th align='center'>Nett Qty</th>
	  <th align='center'>Cooking loss</th>
	  <th align='center'>Remove</th>

      </tr>
    </thead>
    <tbody>

	<?php
	
	




 

$S_NO=1;
	//echo $sql;
   while($row_VALUE_ADDITION=$STMT_VALUE_ADDITION->fetch_array()){

$sockinQty=$row_VALUE_ADDITION['SOCK_IN_QTY'];
$sockoutQty=$row_VALUE_ADDITION['SOCK_OUT_QTY'];
$PurchaseDate=$row_VALUE_ADDITION['PURCHASE_DATE'];
$CookedQty=$row_VALUE_ADDITION['NET_QTY'];
$Numerator=$sockoutQty-$sockinQty;
   
   $YEILD=ROUND((($Numerator/$sockinQty)*100),2);
   $CookingLoss=($CookedQty/$sockoutQty);
   $CookingLoss=ROUND((1-$CookingLoss),3);
    $CookingLoss= $CookingLoss*100;
  
	$Purchase_Date = date("d-m-Y", strtotime($PurchaseDate));
	?>
	<tr >
			
	<td  id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo $S_NO; ?>    </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo $row_VALUE_ADDITION['LOT_NUMBER'];; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo $Purchase_Date; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo $row_VALUE_ADDITION['VARIETY']; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo $row_VALUE_ADDITION['GRADE']; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo $row_VALUE_ADDITION['SOCK_IN_QTY']; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo  $row_VALUE_ADDITION['SOCK_OUT_QTY'];?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo $YEILD;  ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo  $row_VALUE_ADDITION['TEMPERATURE']; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo  $row_VALUE_ADDITION['NET_QTY']; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo  $CookingLoss; ?> </td>
	
	<TD>
	<button id="<?php  echo $row_VALUE_ADDITION['V_ID'];?>" 
	onClick="reply_click(this.id)" class="glyphicon-remove btn btn-danger"> Delete</button>
	</TD>
	
	
    </tr>
	
	
	  <?php 
	  $S_NO++;

   }
	  ?>

     </tbody>

  </table>
</div>
</div>

</div>


    </section>
	
</div>

    <?php include "../includes/footer.php"; ?>

  </div>

</body>
<script>
  function reply_click(clicked_id) {

   		 
		 alert(clicked_id);		  
		$.ajax({
		  url:'AjDltAl.php',
		  type:'POST',
		  data:{'TYPE':'VA_REPORT','D_ID':clicked_id},
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

