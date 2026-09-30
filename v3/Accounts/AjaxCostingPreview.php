<?php
include "../includes/config.php";
$LOT_ID=$_POST['LOT_ID'];
$EXPANSE=$_POST['EXPANSE'];
$EXCHANGE_RATE=$_POST['EXCHANGE_RATE'];
$DEPB=$_POST['DEPB'];
$TYPE=$_POST['TYPE'];
$USER_ID=$_POST['USER_ID'];
$sql="SELECT * FROM `RM_COSTING_GRADES` where LOT_ID='$LOT_ID'";
$STMT_GRADES=$iconn->query($sql);


if($TYPE=='SUBMIT'){
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$SUBMISSION_DATE=$dateTime->format("Y-m-d  H:i:s"); 

	$sql_org_insert="INSERT INTO `RM_COSTING_DETAILS`
	(`LOT_ID`, `USER_ID`, `SUBMISSION_DATE`, `REMARKS`, `EXPANSE`, `DEPB`, `EXCHANGE_RATE`) 
	VALUES ('$LOT_ID','$USER_ID','$SUBMISSION_DATE','$REMARKS','$EXPANSE','$DEPB','$EXCHANGE_RATE')";	
	//echo $sql_org_insert; 

	$stmt1=$iconn->query($sql_org_insert);


	if($stmt1){
		$insertId = $iconn->insert_id;
		legacyAuditLogInsertById('RM_COSTING_DETAILS', 'ID', $insertId, array('LOT_ID' => $LOT_ID, 'USER_ID' => $USER_ID), array(), 'Accounts');

		echo "Success";
		
	}
}else{
?>

<div class="form-group col-sm-12">


		

<h2 align="center">BreakUp  Details   
</h2>
</div>
<!-- <button id="update"  name='update' class="btn btn-primary">Update</button> 

-->

<table id="example1" class="table table-bordered table-hover">
              

    <thead>

      <tr>
		<th>Count</th>

        <th>Grade </th>
        
		<th>Weight</th>

        <th>USD</th>
        <th>Sale Price (INR)</th>

		

      </tr>

    </thead>


    <tbody>
				
		 
		 <tbody>

	

	<?php

	//echo $sql;

	

   while($row=$STMT_GRADES->fetch_array()){
	   
	  $WEIGHT= $row['WEIGHT'];
	   
	   $SALE_PRICE=$row['WEIGHT']*$row['USD']*$EXCHANGE_RATE;
	   $TOTAL_HL_WEIGHT+=$WEIGHT;
	   $TOTAL_SALE_PRICE+=$SALE_PRICE;


	?>

        
        <td id="<?php echo $row['LOT_NO']; ?>"><?php echo $row['COUNT']; ?> </td>
        <td id="<?php echo $row['LOT_NO']; ?>"><?php echo $row['GRADE']; ?> </td>
        <td id="<?php echo $row['LOT_NO']; ?>"><?php echo $row['WEIGHT']; ?> </td>
        <td id="<?php echo $row['LOT_NO']; ?>"><?php echo $row['USD']; ?> </td>
        <td id="<?php echo $row['LOT_NO']; ?>"><?php echo $SALE_PRICE; ?> </td>
		
		
		
		

       

		
        </tr>

	  <?php 

   }
  
		$SqlBillDetails="SELECT *  FROM `RM_BILL_DETAILS` WHERE LOT_ID='$LOT_ID'";
		$StmtBillDetails=$iconn->query($SqlBillDetails);
		$RowBillDetails=$StmtBillDetails->fetch_array();		
		$TotalWeight=$RowBillDetails['WEIGHT'];
		$Rate=$RowBillDetails['RATE'];
		
		$SqlDefectsDetails="SELECT SUM(WEIGHT) AS DEF_WEIGHT FROM `RM_DEFECTS_DETAILS` WHERE LOT_ID='$LOT_ID'";
		$StmtDefectsDetails=$iconn->query($SqlDefectsDetails);
		$RowDefectsDetails=$StmtDefectsDetails->fetch_array();
		$DefWeight=$RowDefectsDetails['DEF_WEIGHT'];
   
		$SqlHonGrading="SELECT * FROM `PPC_GRADING_HON` WHERE LOT_ID='$LOT_ID'";
		$StmtHonGrading=$iconn->query($SqlHonGrading);
		$RowHonGrading=$StmtHonGrading->fetch_array();
		
		
		
		if($RowHonGrading!=0){
			$HonUsed=$RowHonGrading['HON_WEIGHT'];
		}else{
			$HonUsed=0;
		}
   
   
		$Weight=$TotalWeight + $DefWeight - $HonUsed; 
		$PurchaseCost=$Weight * $Rate; 
		$TOTAL_DEPB=($DEPB * $TOTAL_SALE_PRICE)/100; 
		$TOTAL_Expance=$TOTAL_HL_WEIGHT * $EXPANSE;
		
		$PROFIT_LOSS=($TOTAL_SALE_PRICE+$TOTAL_DEPB)-($TOTAL_Expance+$PurchaseCost);
		
		if($PROFIT_LOSS>0){
			$TRANSACTION='GAIN';
			}else{
			$TRANSACTION='LOSS';
			}

	  ?>
	  
	  
	  
	  <tr>	  
	    <td id="<?php echo $row['LOT_NO']; ?>"><b> Total HL Weight </b></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"><b><?php echo $TOTAL_HL_WEIGHT ?></b></td>
	  </tr>
	  
	  
	
	  <tr>	  
	    <td id="<?php echo $row['LOT_NO']; ?>"><b> DEPB </b></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"><b><?php echo  $TOTAL_DEPB  ?></b></td>
	  </tr> 
	  
	  <tr>	  
	    <td id="<?php echo $row['LOT_NO']; ?>"><b> Purchase </b></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"><b><?php echo  $PurchaseCost  ?></b></td>
	  </tr>
	  
	  <tr>	  
	    <td id="<?php echo $row['LOT_NO']; ?>"><b> Expanse </b></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"><b><?php echo $TOTAL_Expance  ?></b></td>
	  </tr>
	  
	  <tr>	  
	    <td id="<?php echo $row['LOT_NO']; ?>"><b> <?php echo $TRANSACTION ?> </b></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"></td>
        <td id="<?php echo $row['LOT_NO']; ?>"><b><?php echo $PROFIT_LOSS  ?></b></td>
	  </tr>

 </tbody>

  </table>
<?php } ?>
