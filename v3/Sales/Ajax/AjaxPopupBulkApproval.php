<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

	//if(isset($_POST['YEAR'])){
	 $Year=$_POST['YEAR'];
	 $DateStart=$Year.'-04-01';
$YearEnd=$Year+1;
$DateEnd=$YearEnd.'-03-31';
	 		//$SqlStatus="SELECT * FROM `ScrmPIData` WHERE Status='0' and InvDate between '$DateStart' and '$DateEnd'"; 
	 		$SqlStatus="SELECT * FROM `ScrmPIData` WHERE Status='0'"; 
	$StmtStatus=$iconn->query($SqlStatus); 
     $data .='
	 <style>
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
color: #FFFFFF;
}

.select2-container .select2-selection--multiple .select2-selection__choice{
    background-color: #1cbb8c;
    border: 1px solid #1cbb8c;
	color: #FFFFFF;
}
.select2-results__option[aria-selected=true] {
    display: none;
}

</style>
<form role="form" method="post">
	';
	
$data .='	<div class="col-12">
                   <div class="card">
					<div class="card-body">
      <div class="row">


		
		<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Select PI</label>
                 
                <select multiple class="form-control" name="SelectedbulkInvId[]" id="SelectedbulkInvId">';
					
 
					  while($RowPIRef=$StmtStatus->fetch_array()){
						 //$InvNo=$RowPIRef['InvNo'];
						 $RefPoID=$RowPIRef['PoID'];
						 $ExporterID=$RowPIRef['ExporterID'];

						 $InvNo=getPIInvoiceNumber($iconn,$ExporterID,$RefPoID);

						 $RefPoID=$RowPIRef['PoID'];
					 
					$data .='<option value="'.$RefPoID.'">'.$InvNo.'</option>';
					 }
				$data .='</select>
				
				</div>
				
				
		<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Status<span style="color:red">*</span></label>
                  <select  class="form-control select2" name="Status" id="Status" required>
	
	
			<option  Value="">Select Status</option>		   
							 
  <option  Value="1" '; if($Status==1){ $data .='selected="selected"';} $data .='>Approved</option>
  <option  Value="5" '; if($Status==5){ $data .='selected="selected"';} $data .='>Po Received</option>
 


		</select> </div>
		
                </div> 
                </div> 
                </div> 
                </div> 
		
		';
		
		
				  

				
			$data .='     
               
								<div align="CENTER" class="form-group col-sm-12">

		<Button type="text" class="btn btn-success StatusBulkUpdate" name="StatusBulkUpdate" id="'.$InvId.'">Submit</Button>
		
		                </div> 
		      </form>          

		
		
		';
	

 		echo $data .='';

		

		
		
	//	echo json_encode($OUT);
	//}
	
?>
		 <script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>

<Script>

$('document').ready(function(){
	
	  
	  $("#SelectedbulkInvId").select2();
	  $("#Status").select2();
});
</Script>
