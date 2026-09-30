<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	if(isset($_POST['INT_LOT_NUM'])){
	 $InvId=$_POST['INT_LOT_NUM'];
	
	
	 $data .='<table  class="table table-bordered ">
	 <thead>
		
		
		<tr>
		<td>
		<div class="form-group col-sm-6">
                  <label for="exampleInputPassword1">Shippedout Date</label>
                  <input    type="date" class="form-control" name="ShippedOutDate"
				  id="ShippedOutDate'.$InvId.'" placeholder="Enter Shippedout Date" value="'.date("Y-m-d").'"
				 </input>
                </div> 
				</td>
				
				<td>
		<Button type="text" class="btn btn-success shipped_out" name="shipped_out" id="'.$InvId.'">Submit</Button>
		</td>
		</tr>
		</thead>
		
		<tbody>
		
		';
	

 		echo $data .='</tbody></table>';

		

		
		
	//	echo json_encode($OUT);
	}
	
?>