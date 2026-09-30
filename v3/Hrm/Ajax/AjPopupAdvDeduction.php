<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	if(isset($_POST['EmpId'])){
	 $EmpId=$_POST['EmpId'];
	 $Month=$_POST['Month'];
	 $Year=$_POST['Year'];
			
     //$SqlStatus="SELECT * FROM `HrmCashAdvance` WHERE  Year(AdvDate)='$Year' and EmpID='$EmpId'";
     $SqlStatus="SELECT * FROM `HrmCashAdvance` WHERE EmpID='$EmpId'";
	$StmtStatus=$iconn->query($SqlStatus); 


	 $SNo=1;?>
	<?php
	 $data .='
	 <table  class="table table-bordered ">
	 
	 <thead>
		
		
		<tr>
	  <th>S.No</th>
	  <th>Reason</th>
	  <th>Total Amount</th>
	  <th>Pending Amount</th>
	  <th>Date</th>
	  <th>Deduction %(Month) </th>
	  <th>Tools</th>
								

		</tr>
		</thead>';
		while($RowStatus=$StmtStatus->fetch_array()){
		$DueAmount=$RowStatus['DueAmount'];
		/*if($RowStatus['DueAmount']!=''){
			$DueAmount=$RowStatus['DueAmount'].'%';
		}
		else{
			$DueAmount='';
		}*/
		$CAdvID=$RowStatus['CAdvID'];
	$data .='<tr>
		  <th>'.$RowStatus['CAdvID'].'</th>
		  <th>'.$RowStatus['Remarks'].'</th>
		  <th>'.$RowStatus['AdvAmount'].'</th>
		  <th>'.$RowStatus['CurrentPending'].'</th>
		 <th>'.date('d-m-Y',strtotime($RowStatus['AdvDate'])).'</th>
		  <th> 
                  <input    type="text" class="form-control" name="DeductionAmount"
				  id="DeductionAmount'.$CAdvID.'" placeholder="Enter Deduction Percentage" value="'.$DueAmount.'"
				 </input></th>
		<th>
		<!--<Button type="text" class="btn btn-success shipped_out" name="shipped_out" id="'.$CAdvID.'">Submit</Button>-->
		<button class="btn btn-success updateadvdeduction" name="updateadvdeduction" id="'.$CAdvID.'"   align="Center" >
						 <i class="fa fa-edit"></i> </button>
                      
		</th>
	  							

		</tr>';
	$SNo++;	}
	$data .='	<tbody>
		
		';
	

 		echo $data .='</tbody></table>';

		

		
		
	//	echo json_encode($OUT);
	}
	
?>