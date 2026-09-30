<?php
session_start();
include "../includes/config.php";


$TYPE=$_POST['TYPE'];
$D_ID=$_POST['D_ID'];
$REFERENCE_ID=$_POST['ref_id'];


	 if($TYPE=='INSP_UPDATE'){
		 
		 
	 
	 $ID=$_POST['D_ID'];
	 $INVOICE_ID=$_POST['INVOICE_ID'];
	 $FROZEN_WEIGHT=$_POST['FROZEN_WEIGHT'];
	 $DEGLAZED_WEIGHT=$_POST['DEGLAZED_WEIGHT'];
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
	$GLAZE=round((($FROZEN_WEIGHT- $DEGLAZED_WEIGHT)/$FROZEN_WEIGHT)*100,1);  //c
	

     $sql="UPDATE `INSP_DATA` SET 
	 
	 `FROZEN_WEIGHT`='$FROZEN_WEIGHT',
	 `DEGLAZED_WEIGHT`='$DEGLAZED_WEIGHT',
	 `DEHYDRATION`='$DEHYDRATION',
	 `DISCOLORATION`='$DISCOLORATION',
	 `DECOMPOSITION`='$DECOMPOSITION',
	 `BROKEN_DAMAGE`='$BROKEN_DAMAGE',
	 `HANGING_MEAT`='$HANGING_MEAT',
	 `BLACK_SPOT_MEAT`='$BLACK_SPOT_MEAT',
	 `SOFT_SHELL`='$SOFT_SHELL',
	 `TOTAL_PIECES`='$TOTAL_PIECES',
	 `UNIFORMITY`='$UNIFORMITY',
	 `GLAZE`='$GLAZE'
	 WHERE `ID`='$ID'";
	 $stmt=$iconn->query($sql);
	 
	
	
	if($stmt){
		
$SQL_INSP="SELECT * FROM `INSP_DATA` where INV_ID='$INVOICE_ID' order by NO_OF_SAMPLES asc" ;
$STMT_INSP=$iconn->query($SQL_INSP);
	

?>
	
	 <thead>

      <tr>

	  <th>Sample No</th>
	  <th>Frozen Wt</th>
	  <th>Deglazed Wt</th>
	  <th>TOTAL Pcs</th>
	  <th>Uniformity</th>
	  <th>Dehydra(g)</th>
	  <th>Discolor(g</th>
	  <th>Decomposition(g)</th>
	  <th>Bkn/Damage(g)</th>
	  <th>Hanging Meat(g)</th>
	  <th>Blk/Spot Meat(g)</th>
	  <th>Sft/Shell(g)</th>
	  <th>Edit/Delete</th>
	  
	

      </tr>

    </thead>

	

    <tbody id="CODE_LIST">

	

	<?php
	
	while ($row = $STMT_INSP->fetch_array()){ ?>  
	
	<tr>
	
	<?php $ID= $row['ID']; ?>
	

	
	<td > <?php echo  $row['NO_OF_SAMPLES']  ?> </td>
				  
				 
	
	<td>
	<input  type="text" class="form-control"  name="FROZEN_WEIGHT" id="FROZEN_WEIGHT<?php echo $ID; ?>"
				   value="<?php echo $row['FROZEN_WEIGHT']; ?>"> 
			
	</td>	
	
	<td > <input  type="text" class="form-control"  name="DEGLAZED_WEIGHT" id="DEGLAZED_WEIGHT<?php echo $ID; ?>"
				   value="<?php echo $row['DEGLAZED_WEIGHT']; ?>"> </td>
				   
				   </td><td > <input  type="text" class="form-control"  name="TOTAL_PIECES" id="TOTAL_PIECES<?php echo $ID; ?>"
				   value="<?php echo $row['TOTAL_PIECES']; ?>"> </td></td>
				   
				   <td > <input  type="text" class="form-control"  name="UNIFORMITY" id="UNIFORMITY<?php echo $ID; ?>"
				   value="<?php echo $row['UNIFORMITY']; ?>"> </td>

	<td > <input  type="text" class="form-control"  name="DEHYDRATION" id="DEHYDRATION<?php echo $ID; ?>"
              value="<?php echo $row['DEHYDRATION']; ?>"> </td>

	<td > <input  type="text" class="form-control"  name="DISCOLORATION" id="DISCOLORATION<?php echo $ID; ?>"
				  value="<?php echo $row['DISCOLORATION']; ?>"> </td>

	<td > <input  type="text" class="form-control"  name="DECOMPOSITION" id="DECOMPOSITION<?php echo $ID; ?>"
				   value="<?php echo $row['DECOMPOSITION']; ?>"> </td>

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

	  <?php } ?>


     </tbody>

	 <?php } }?>	
