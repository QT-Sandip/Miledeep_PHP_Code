<?php 
	include '../../includes/config.php';

	if(isset($_POST['LotID'])){
	 $LotID=$_POST['LotID'];
			
  

	
	 $data .='<table  class="table table-bordered ">
	 <thead>
		
		
		<tr>
		
				
			<!--	<td>
				
				<div class="form-group col-sm-6" >
                  <label for="exampleInputEmail1"> Dummy/Master</label>
               <select class="form-control" name="DummyMaster" id="DummyMaster'.$LotID.'">
					 
					<option value="DUMMY">DUMMY</option>
					<option value="MASTER">FINISHED</option>
				
		
				<select>
                </div>
			</td>-->	
			<td align="center">	
				
		<Button type="text" align="center" class="btn btn-warning dummy" name="dummy" id="'.$LotID.'">DUMMY</Button>
		</td><td align="center">	
				
		<Button type="text" align="center" class="btn btn-success master" name="master" id="'.$LotID.'">FINISHED</Button>
		</td>
		</tr>
		</thead>
		
		<tbody>
		
		';
	

 		echo $data .='</tbody></table>';

		

		
		
	//	echo json_encode($OUT);
	}
	
?>