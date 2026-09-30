<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	
	 	$EmpID=$_POST['EmpID'];	
	 	$Month=$_POST['Month'];	
	 	$Year=$_POST['Year'];	
		$dateObj   = DateTime::createFromFormat('!m', $Month);
$monthName = $dateObj->format('F'); // March
		
     $SqlPDBilling="SELECT * from HrmAuditEmployeeDetails as EmpList where EmpID='$EmpID' "; 
	$StmtPDBilling=$iconn->query($SqlPDBilling); 
	$RowPDBilling=$StmtPDBilling->fetch_array();
	$Count=mysqli_num_rows($StmtPDBilling);
	

	
	 $data .='
	 
	 <h6><br> Name: '.$RowPDBilling['EmpName'].'</h6>
	 <h6> Biometric Id: '.$RowPDBilling['BioMetricID'].'</h6>
	 <h6>Month: '.$monthName.'</h6>
	 <h6> Year: '.$Year.'<br><br></h6>
	 
	 <table  class="table table-bordered ">
	 <thead>
		
		
		<tr>
<form role="form" method="post">
	';

		//else{
$data .='	      										<div class="row">
                                                
		
		<div class="form-group col-sm-4">
                  <label for="exampleInputPassword1">Weekoff</label>
                  <input    type="text" class="form-control" name="InvNo"
				  id="InvNo" placeholder="Enter " value="'.$RowStatus['PoNo'].'"
				 </input>
                </div>

				<div class="form-group col-sm-4">
                  <label for="exampleInputPassword1">C-off</label>
                  <input  required  type="text" class="form-control" name="FromDate"
				  id="FromDate"  value="" placeholder="Enter "
				 </input>
                </div> 
				
				<div class="form-group col-sm-4">
                  <label for="exampleInputPassword1">Leave</label>
                  <input    type="text" class="form-control" name="ToDate"
				  id="ToDate" value="" placeholder="Enter "
				 </input>
                </div> 
				
				<div class="form-group col-sm-4">
                  <label for="exampleInputPassword1">Absent</label>
                  <input    type="text" class="form-control" name="ToDate"
				  id="ToDate" value="" placeholder="Enter "
				 </input>
                </div> 
				
                </div> 
		
		';
		
		//}
			$data .='     
                </div> ';
				
				//else{
						$data .='  		<div align="CENTER" class="form-group col-sm-12">

		<Button type="text" class="btn btn-success Submit" name="Submit" id="">Submit</Button>
		
		                </div> ';
				//}
		    $data .='    </form>          

		</tr>
		</thead>
		
		<tbody>
		
		';
	

 		echo $data .='</tbody></table>';

	
?>
<script>
$(document).ready(function(){
	 	  $("#StationId").select2();

	});
</script>