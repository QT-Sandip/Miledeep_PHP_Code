<?php 
	include '../../includes/config.php';

	if(isset($_POST['EmpBioID'])){
	 $EmpBioID=$_POST['EmpBioID'];
			
   
	
	 $data .='<table  class="table table-bordered ">
	 <thead>
		
		
		<tr>
		<td>
		<div class="form-group col-sm-6">
                  <label for="exampleInputPassword1">Time In</label>
                  <input    type="time" class="form-control" name="TimeIn"
				  id="TimeIn'.$EmpBioID.'" placeholder="" value="'.date("Y-m-d").'"
				 </input>
                </div> 
				</td><td>
				
				<div class="form-group col-sm-6">
                  <label for="exampleInputPassword1">Time Out</label>
                  <input    type="time" class="form-control" name="TimeOut"
				  id="TimeOut'.$EmpBioID.'" placeholder="" value="'.date("Y-m-d").'"
				 </input>
                </div> 
				</td>
				
				<td>
		<Button type="text" class="btn btn-success ManualAttd" name="ManualAttd" id="'.$EmpBioID.'">Submit</Button>
		</td>
		</tr>
		</thead>
		
		<tbody>
		
		';
	

 		echo $data .='</tbody></table>';

		

		
		
	//	echo json_encode($OUT);
	}
	
?>