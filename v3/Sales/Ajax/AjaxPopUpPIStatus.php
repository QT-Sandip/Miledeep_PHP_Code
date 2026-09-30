<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	if(isset($_POST['POID'])){
	 $InvId=$_POST['POID'];
	 		
     $SqlStatus="SELECT * FROM `ScrmPIData` WHERE PoID='$InvId'"; 
	$StmtStatus=$iconn->query($SqlStatus); 
	
	 
	$RowStatus=$StmtStatus->fetch_array();
			$Status=$RowStatus['Status'];

	
	
	 $data .='<form role="form" method="post">
	';
if($Status==0 || $Status==1){
		
$data .='	      <div class="col-12">
                   <div class="card">
					<div class="card-body">
						<div class="row">


			<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Select Status<span style="color:red">*</span></label>              			   
				  <select required class="form-control select2" name="PoStatus" id="PoStatus">
				   
				<option  Value="">Select Status</option>		   
	<option  Value="1" '; if($Status==1){ $data .='selected="selected"';} $data .='>Approved</option>
	<option  Value="5" '; if($Status==5){ $data .='selected="selected"';} $data .='>Po Received</option>
	<option  Value="4" '; if($Status==4){ $data .='selected="selected"';} $data .='>Rejected</option>
    <option  Value="2" '; if($Status==2){ $data .='selected="selected"';} $data .='>Cancel</option>
				
				</select>   
                </div>	
		
				<div class="form-group  col-lg-3">
                  <label for="exampleInputPassword1">Po No</label>
                  <input    type="text" class="form-control" name="PoNo"
				  id="PoNo" placeholder="Enter Po No" value="'.$RowStatus['PoNo'].'"
				 </input>
                </div> 
				
				<div class="form-group  col-lg-3">
                  <label for="exampleInputPassword1">Po Date</label>
                  <input    type="date" class="form-control" name="PoDate"
				  id="PoDate" placeholder="Enter Po Date" value="'.$RowStatus['PoDate'].'"
				 </input>
                </div> 
				<div class="form-group  col-lg-3">
                  <label for="exampleInputPassword1">Remarks</label>
                  <input    type="text" class="form-control" name="Remarks"
				  id="Remarks" placeholder="Enter Remarks" value="'.$RowStatus['Remarks'].'"
				 </input>
                </div> 
               
			   </div> 
                </div> 
                </div> 
                </div> 
		
		';
		
		
				  
}else if($Status==5){
		
$data .='	      <div class="row">

<div class="form-group  col-lg-3">
                  <label for="exampleInputPassword1">Status</label>
                  <select  class="form-control select2" name="PoStatus" id="PoStatus" required>
	
	
			<option  Value="">Select Status</OPTION>		   
							 
    <option Value="2">Cancelled</OPTION>
	</select> 
	</div>
	<div class="form-group  col-lg-3">
                  <label for="exampleInputPassword1">Remarks</label>
                  <input    type="text" class="form-control" name="Remarks"
				  id="Remarks" placeholder="Enter Remarks" value="'.$RowStatus['Remarks'].'"
				 </input>
                </div> 
	</div>
	';
				  
}else{
	$data .='<div class="row">

	<div class="form-group  col-lg-3">
                  <label for="exampleInputPassword1">Remarks</label>
                  <input    type="text" class="form-control" name="Remarks"
				  id="Remarks" placeholder="Enter Remarks" value="'.$RowStatus['Remarks'].'"
				 </input>
                </div> 
	</div>';
}	
		
		
		
		
		
				
			$data .='     
                <!--</div> 
		<div align="CENTER" class="form-group col-sm-12">
			<Button type="text" class="btn btn-success Status_Update" name="Status_Update" id="'.$InvId.'">Submit</Button>
			</div>-->
		  </form>          

		';
	

 		echo $data .='';
	}
	
?>