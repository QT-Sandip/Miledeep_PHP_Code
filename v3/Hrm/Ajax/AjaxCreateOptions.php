<?php
session_start();
include "../../includes/config.php";


 $DEPT_ID=$_POST['DEPT_ID']; 
$TYPE=$_POST['TYPE'];

if($TYPE=='SUB'){

$sql_sub="SELECT * FROM `HRM_SUB_DEPARTMENT_MASTER` WHERE DEPARTMENT_ID='$DEPT_ID'";

$stmt_sub=$iconn->query($sql_sub);


			?>	
				
				
				  <select class="form-control" name="S_DEPT" id="S_DEPT" required>
				  <option value="">-Select- </option>
				  <?php while($ROW_sub=$stmt_sub->fetch_array()) { 
				
				?> 
				  
				  <option value="<?php echo $ROW_sub['SUB_ID'];?>"><?php echo $ROW_sub['SUB_DEPARTMENT_NAME'];?></option>
				  				<?php } ?>

				  <select>

<?php }	

?>					  				



				  				


