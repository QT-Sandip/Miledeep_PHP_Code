<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	 //$InvId=$_POST['INT_LOT_NUM'];
	 // $TYPE=$_POST['TYPE'];
			

  //if($TYPE=='UMA'){  echo "hi";}
	 $data .='<table  class="table table-bordered ">
	 <thead>
		
		
		<tr>
		<td>
		<div class="form-group col-sm-6">
                  <label for="exampleInputPassword1">Submission Date</label>
                  <input    type="date" class="form-control" name="SubmissionDate"
				  id="SubmissionDate" placeholder="Enter Submission Date" value="'.date("Y-m-d").'"
				 </input>
                </div> 
				</td>
				
				<td>
		<Button type="text" class="btn btn-success submission" name="submission" id="'.$InvId.'">Submit</Button>
		</td>
		</tr>
		</thead>
		
		<tbody>
		
		';
	

 		echo $data .='</tbody></table>';

		

		
		
	//	echo json_encode($OUT);
	
	
?>