<?php



session_start();

include "../includes/config.php";

//$INVOICE_ID=$_GET['id'];
$LOT_ID=$_GET['ID'];
$sql_LOTS="SELECT * FROM `inputmaterial_details` where id='$LOT_ID'";
$stmt_LOTS=$iconn->query($sql_LOTS);
$ROW_LOTS=$stmt_LOTS->fetch_array();


$SQL_VALUE_ADDITION="SELECT * FROM `PPC_VALUE_ADDITION` where LOT_ID='$LOT_ID' and VARIETY !='DeHeading' ORDER BY TIME_STAMP DESC";
$STMT_VALUE_ADDITION=$iconn->query($SQL_VALUE_ADDITION);


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
      $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
      $TIME_STAMP=$dateTime->format("Y-m-d  H:i:s"); 
	 

	
	 $sql_org_insert="INSERT INTO `PPC_VALUE_ADDITION`(`LOT_ID`, `COUNT`, 
	 `PROCESSING_DATE`, `TIME_STAMP`, `SENT_WEIGHT`, `WgtAfterValueAddition`,
	 `CountAfterValueAddition`, `RETURN_WEIGHT`, `HL_QTY_RCVD`, `VARIETY`,
	 `SHIFT`, `SUPERVISOR`, `STATION_ID`, `USER_ID`, `REMARKS`)
	 VALUES('$LOT_ID','$COUNT','$PROCESSING_DATE','$TIME_STAMP','$SENT_WEIGHT',
	 '$WtAftrVA','$CntAftrVA', '$RETURN_WEIGHT','$HL_QTY_RCVD',
				 '$VARIETY','$SHIFT','$SUPERVISOR','$STATION'
				 ,'$USER_ID','$REMARKS')";
	
	

	$stmt1=$iconn->query($sql_org_insert);
	$MB_REPORT_ID = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('PPC_VALUE_ADDITION', 'ID', $MB_REPORT_ID, $_POST, [
			'ReferenceNo' => $MB_REPORT_ID,
			'Remarks' => 'Value addition (OMM) created',
		], 'PreProcessing');
		header('Location: AddVAReprt.php?ID='.$LOT_ID);
	}
}

			
$SQL_PROCESSING_STATIONS="SELECT * FROM `RM_PROCESSING_STATIONS`";
$stmt_PROCESSING_STATIONS=$iconn->query($SQL_PROCESSING_STATIONS);	


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



	
<div id="tdetails" class="table-responsive col-sm-12" >  
    <div class="box">
          
        <div class="box-body">
<h1 ALIGN="CENTER">Value Addition(Melting Data)</h1>

  <table id="" class="table table-bordered table-hover" ALIGN="CENTER">
    <thead >
      <tr >
	  
	  <th align='center' >S.No</th>
	  <th align='center'>Station</th>
	  <th align='center'>Processing Date</th>
	  <th align='center'>Variety</th>
	  <th align='center'>Sent HL/HO Wt(Kgs)</th>
	  <th align='center'>Received HL/HO Qty(Kgs) </th>
	  <th align='center'>HL/HO Count</th>
	  <th align='center'>Count After VA</th>
	  <th align='center'>Weight After VA</th>
	  <th align='center'>Returned Weight(if any)</th>
	  <th align='center'>Yeild</th>
	  <th align='center'>Remarks</th>
	  <th align='center'>Remove</th>

      </tr>
    </thead>
    <tbody>

	<?php
	
	




 

$S_NO=1;
	//echo $sql;
   while($row_VALUE_ADDITION=$STMT_VALUE_ADDITION->fetch_array()){

$SHED_ID=$row_VALUE_ADDITION['STATION_ID'];

   $SQL_SHEDNAME="SELECT * FROM `RM_PROCESSING_STATIONS` where ID='$SHED_ID'";
$STMT_SHEDNAME=$iconn->query($SQL_SHEDNAME);
$row_SHEDNAME=$STMT_SHEDNAME->fetch_array();
   
   $YEILD=ROUND(($row_VALUE_ADDITION['WgtAfterValueAddition']/$row_VALUE_ADDITION['SENT_WEIGHT'])*100);
   $SHORTAGE=$row_VALUE_ADDITION['SENT_WEIGHT']-$row_VALUE_ADDITION['HL_QTY_RCVD'];
	$ProcessingDate = date("d-m-Y", strtotime($row_VALUE_ADDITION['PROCESSING_DATE']));
	?>
	<tr >
			
	<td  id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo $S_NO; ?>    </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo $row_SHEDNAME['UNIT_NAME']; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo $ProcessingDate; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo $row_VALUE_ADDITION['VARIETY']; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo $row_VALUE_ADDITION['SENT_WEIGHT']; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo $row_VALUE_ADDITION['HL_QTY_RCVD']; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo  $row_VALUE_ADDITION['COUNT'];?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo $row_VALUE_ADDITION['CountAfterValueAddition'];  ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo  $row_VALUE_ADDITION['WgtAfterValueAddition']; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo  $row_VALUE_ADDITION['RETURN_WEIGHT']; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo  $YEILD; ?> </td>
	<td id="<?php  echo $row_VALUE_ADDITION['ID'];?>" > <?php echo  $row_VALUE_ADDITION['REMARKS']; ?> </td>
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
	<?php include "../includes/scripts.php"; ?>
 <?php include "../includes/AjaxLoader.php"; ?>

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

