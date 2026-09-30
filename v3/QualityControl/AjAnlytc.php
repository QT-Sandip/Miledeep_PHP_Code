<?php
session_start();
include "../includes/config.php";


$TYPE=$_POST['TYPE'];
$D_ID=$_POST['D_ID'];
$CL_ID=$_POST['CODE_LIST_ID'];
$INVOICE_ID=$_POST['INVOICE_ID'];
$REFERENCE_ID=$_POST['ref_id'];


	 if($TYPE=='INSP_UPDATE'){
		 
		 
	 
	 $ID=$_POST['D_ID'];
	 $INVOICE_ID=$_POST['INVOICE_ID'];
	// $FROZEN_WEIGHT=$_POST['FROZEN_WEIGHT'];
	 $GRADE=$_POST['GRADE'];
	  $GLAZE=$_POST['GLAZE'];
	 $DEGLAZED_WEIGHT=$_POST['DEGLAZED_WEIGHT'];
	 $DEGLAZED_COUNT=$_POST['DEGLAZED_COUNT'];
	 $TPC_VALUE=$_POST['TPC_VALUE'];
	 $DEHYDRATION=$_POST['DEHYDRATION'];
	 $DISCOLORATION=$_POST['DISCOLORATION'];
	 $DECOMPOSITION=$_POST['DECOMPOSITION'];
	 $BROKEN_DAMAGE=$_POST['BROKEN_DAMAGE'];
	 $HANGING_MEAT=$_POST['HANGING_MEAT'];
	 $BLACK_SPOT_MEAT=$_POST['BLACK_SPOT_MEAT'];
	 $SOFT_SHELL=$_POST['SOFT_SHELL'];
	 $TOTAL_PIECES=$_POST['TOTAL_PIECES'];
	 $UNIFORMITY=$_POST['UNIFORMITY'];
	
	
	
	$BIG_PIECES_UN=$item['BigPieces'];
	$SMALL_PIECES_UN=$item['SmallPieces'];
	//$UNIFORMITY=round($BIG_PIECES_UN/$SMALL_PIECES_UN,1);  //c
	//$GLAZE=round((($FROZEN_WEIGHT- $DEGLAZED_WEIGHT)/$FROZEN_WEIGHT)*100,1);  //c
	//$FROZEN_WEIGHT=(($DEGLAZED_WEIGHT + $GLAZE)/100);
	
     $sql="UPDATE `QC_ANALYTIC_REPORTS` SET 
	 
	
	 `GRADE`='$GRADE',
	 `GLAZE`='$GLAZE',
	 `DEGLAZED_WEIGHT`='$DEGLAZED_WEIGHT',
	 `DEGLAZED_COUNT`='$DEGLAZED_COUNT',
	
	 `BROKEN_DAMAGE`='$BROKEN_DAMAGE',
	 
	 `BLACK_SPOT_MEAT`='$BLACK_SPOT_MEAT',
	 
	 `UNIFORMITY`='$UNIFORMITY'
	 
	 WHERE `ID`='$ID'";
	
	 $stmt=$iconn->query($sql);
	
	
	if($stmt){
		
$SQL_INSP="SELECT * FROM `QC_ANALYTIC_REPORTS` where CODE_LIST_ID='$CL_ID' order by ID asc" ;
$StmtQcAnalyticReport=$iconn->query($SQL_INSP);

 
?>
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

	
    <tbody id="CODE_LIST">


	<?php
	
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
	
	<?php } }?>	
