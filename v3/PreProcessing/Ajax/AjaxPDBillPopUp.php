<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	
	 	$PdBillID=$_POST['PdBillID'];	
		
     $SqlPDBilling="SELECT * FROM `PdBillGenerationLog` WHERE PdBillID='$PdBillID'"; 
	$StmtPDBilling=$iconn->query($SqlPDBilling); 
	$Count=mysqli_num_rows($StmtPDBilling);
	
	$SqlStations="SELECT * FROM RmVAStations WHERE UnitStatus='1'"; 
	$StmtStations=$iconn->query($SqlStations); 
	

	
	 $data .='<table  class="table table-bordered ">
	 <thead>
		
		
		<tr>
<form role="form" method="post">
	';
		if($Count!=0){
	$data .='	<div class="form-group col-sm-4">
                  <label for="exampleInputPassword1">Bill Closed Date</label>
                  <input    type="date" class="form-control" name="BillClosedDate"
				  id="BillClosedDate" value=""
				 </input>
                </div>';
		}
		else{
$data .='	      										<div class="row">
                                                <div class="col-lg-3">
                                                    <div class="form-group">   
													<label for="exampleInputEmail1">VA Station</label>

                                                       <select class="form-control select2" name="StationId" id="StationId">
                                                           ';
														
															while($DataRow=$StmtStations->fetch_array()){
				 
											$data .='	<option value="'.$DataRow['StationID'].'" >
												'.$DataRow['UnitName'].'</option>';
												   } 
                                                     $data .='   </select>
													</div>
                                                </div>
		
		<div class="form-group col-sm-4">
                  <label for="exampleInputPassword1">Invoice No </label>
                  <input    type="text" class="form-control" name="InvNo"
				  id="InvNo" placeholder="Enter Invoice No" value="'.$RowStatus['PoNo'].'"
				 </input>
                </div>

				<div class="form-group col-sm-4">
                  <label for="exampleInputPassword1">From Date</label>
                  <input  required  type="date" class="form-control" name="FromDate"
				  id="FromDate"  value="" max="'.date('Y-m-d').'"
				 </input>
                </div> 
				
				<div class="form-group col-sm-4">
                  <label for="exampleInputPassword1">To Date</label>
                  <input    type="date" class="form-control" name="ToDate"
				  id="ToDate" value="" max="'.date('Y-m-d').'"
				 </input>
                </div> 
				
                </div> 
		
		';
		
		}
			$data .='     
                </div> ';if($Count!=0){
						$data .='  		<div align="CENTER" class="form-group col-sm-12">

		<Button type="text" class="btn btn-success UpdateBill" name="UpdateBill" 
		data-id="'.$PdBillID.'"
		id="">Update</Button>
		
		                </div> ';
				}
				
				else{
						$data .='  		<div align="CENTER" class="form-group col-sm-12">

		<Button type="text" class="btn btn-success GenerateBill" name="GenerateBill" id="">Generate Bill</Button>
		
		                </div> ';
				}
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