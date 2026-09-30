<?php
include ($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$IsSplitTracebilityAndPondID = $ArrayClientConfigData['TracebilityIDAndPondIDSeparator'];

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$api = API_QC_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");
$resBap = $resDropdownsAPI->Data->Bap;

//if(isset($_POST['YEAR'])){
/* $Year=$_POST['YEAR'];
	$DateStart=$Year.'-04-01';
$YearEnd=$Year+1;
$DateEnd=$YearEnd.'-03-31';
			$SqlStatus="SELECT * FROM `ScrmPIData` WHERE Status='0' and InvDate between '$DateStart' and '$DateEnd'"; 
   $StmtStatus=$iconn->query($SqlStatus); */
$data .= '<table  class="table table-bordered ">
	 <thead>
		
		
		<tr>
<form role="form" method="post">
	';
//if($Status==0 || $Status==1){

$data .= '  <div class="row">
               <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Farmer Name </label>
                   <input required  type="text" class="form-control" name="FarmerName" id="FarmerName" 
				   placeholder="Enter Farmer Name" >
				</div>
				<div class="form-group col-lg-6" >
                  <label for="exampleInputEmail1">Address </label>
                   <input required  type="text" class="form-control" name="AddressContinued" id="AddressContinued" 
				   placeholder="Enter Address Of Farmer"  >
				</div>
		
		        <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Farmer Code </label>
                   <input   type="text" class="form-control" name="FarmerCode" id="FarmerCode" 
				   placeholder="Enter FarmerCode" >
				</div> 
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Traceability </label>
                   <input required  type="text" class="form-control" name="Tracebility" id="Tracebility" 
				   placeholder="Enter Traceability" >
				</div>';
				
					if($IsSplitTracebilityAndPondID){
						$data .= '<div class="form-group col-lg-3" >
										<label for="exampleInputEmail1">Pond ID </label>
										<input required  type="text" class="form-control" name="PondNumber" id="PondNumber" 
											placeholder="Enter Pond ID" oninput="validateNumber()">
									</div>';				
					}
				
				
					$data .= '
				<div class="form-group col-sm-3">
					<label for="exampleInputEmail1">Pond Area</label>
					<input type="text" class="form-control" name="PondArea" id="PondArea" placeholder="Enter Pond Area" value="">
				</div>	
				<div class="form-group col-sm-3" >
					<label for="RegAuthority">Reg Authority</label>
					<select  class="form-control" id="RegAuthority" >
						<option value="">Select</option>
						<option value="MPEDA">MPEDA</option>
						<option value="CAA">CAA</option>
					</select>
				</div> 
				<div class="form-group col-sm-3">
					<label for="Certification">Certification (MPEDA/CAA)</label>
					<div class="form-check form-control pl-4">
						<input type="radio" class="form-check-input" name="Certification" id="Certification1" value="1" >
						<label class="form-check-label mr-5" for="Certification1">YES</label>

						<input type="radio" class="form-check-input" name="Certification" id="Certification0" value="0" >
						<label class="form-check-label" for="Certification0">NO</label>
					</div>
				</div>
				
				<div class="form-group col-sm-3" >
					<label for="exampleInputEmail1">Phone Number</label>
					<input  type="text" class="form-control" name="FarmPhoneNumber" id="FarmPhoneNumber" 
					placeholder="Enter Farmer Phone Number">
				</div>
				<div class="form-group col-sm-3">
					<label for="Tonnage">Tonnage </label>
					<input type="text" class="form-control" name="Tonnage" id="Tonnage"
						placeholder="Enter Tonnage in kgs"
						value="">
				</div>
				<input type="hidden" name="UsedQuantity" id="UsedQuantity" value="">

				
				<div class="form-group col-sm-3 mb-3">
					<label for="txtBap" class="form-label">Bap</label>
					<select class="drpSelect form-control" name="txtBap" id="txtBap">
						<option value=""> Select</option>';
						if (!empty($resBap)) { 
							foreach ($resBap as $Item) {
								$data .= '<option value="'. trim($Item->Name).' ">'.$Item->Name .'</option>';
							} 
						} 
					$data .= '</select>
				</div>
				<div class="col col-md-3 mb-3">
					<label for="StartDate">Start Date</label>
					<input type="date" class="form-control" name="StartDate" id="StartDate"
						value="" />
				</div>
				<div class="col col-md-3 mb-3">
					<label for="ExpiryDate">Expiry Date</label>
					<input type="date" class="form-control" name="ExpiryDate"
						id="ExpiryDate" value="" />
				</div>
				

				<div class="form-group col-sm-3">
					<label for="L2Status">L2 Status </label>
					<select  class="form-control" name="L2Status" id="L2Status">
						<option value="">Select</option>
						<option value="0" >Pending</option>
						<option value="1"  >Completed</option>
					</select>
				</div>
			</div>
			<div class="row">
				<div class="form-group col-sm-3">
					<label for="FarmType">Form Type</label>
					<select  class="form-control" name="FarmType" id="FarmType">
						<option value="">Select</option>
						<option value="Lease" >Lease</option>
						<option value="Own"  >Own</option>
					</select>
				</div>

				<div class="col col-md-3 mb-3">
					<label for="FromDateForLease">Start Date For Lease</label>
					<input type="date" class="form-control" name="FromDateForLease" id="FromDateForLease"
						value="" />
				</div>
				<div class="col col-md-3 mb-3">
					<label for="EndDateForLease">End Date  For Lease</label>
					<input type="date" class="form-control" name="EndDateForLease"
						id="EndDateForLease" value=""  />
				</div>
		
        	</div>
		 
		<div align="CENTER" class="form-group col-sm-12">

			<Button type="text" class="btn btn-success ButtonAddTrace" name="ButtonAddTrace" id="' . $InvId . '">Submit</Button>

		</div> 
		      </form>          

		</tr>
		</thead>
		
		<tbody>
		
		';


echo $data .= '</tbody></table>';





//	echo json_encode($OUT);
//}

?>
<Script>

	$('document').ready(function () {

		$('#FarmType').on('change',function(){
			if($(this).val() == 'Own'){
				$('#FromDateForLease,#EndDateForLease').val('').prop('readonly',true).attr('required',false)
			}else{
				$('#FromDateForLease,#EndDateForLease').prop('readonly',false).attr('required',true)
			}
		})
		$("#SelectedbulkInvId").select2();
	});

    function validateNumber() {
      const input = document.getElementById('PondNumber');
      const value = input.value;
      // Replace all non-numeric characters
      input.value = value.replace(/[^0-9]/g, '');
    }	

</Script>