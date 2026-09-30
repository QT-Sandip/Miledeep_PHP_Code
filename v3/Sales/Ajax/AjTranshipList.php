<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$PoID = $_POST['PoID'];
include($cp . "Includes/IncludePage.php");
include($cp . "Includes/CommercialInvoiceLock.php");
$AccessType = $_SESSION["AccessType"];
$isCommercialInvoiceLocked = isCommercialInvoiceLockedByPiId($iconn, $PoID);

/* $SqlLate="SELECT `TspID`, `PIID`, `FromTOPID`, `ToTOPID`, `FromDate`,
	`TranshipDt`, `VesselName`, `VOYNO`, SPD.Status,
	SPCF.Name AS FROMPORT,SPCT.Name AS TOPORT FROM
	`ScrmTranshipmentPortsList` as SPD LEFT JOIN
	ScrmPortCities as SPCF on SPCF.PortId=SPD.FromTOPID
	LEFT JOIN ScrmPortCities as SPCT on SPCT.PortId=SPD.ToTOPID
	WHERE  PIID='$PoID' and SPD.Status=1";

*/
$SqlTransShipments = "SELECT * FROM ScrmTranshipmentPortsList WHERE
 PIID='$PoID' and Status=1";
$StmtTransShipments = $iconn->query($SqlTransShipments);


$SqlPorts1 = "SELECT * FROM ScrmPortCities ";
$StmtPorts = $iconn->query($SqlPorts1);
$StmttoPorts = $iconn->query($SqlPorts1);


/*$SqltoPorts = "SELECT * FROM ScrmPortCities ";
$StmttoPorts = $iconn->query($SqltoPorts);*/


$data .= '
	<form role="form" method="post">
	';
$data .= '
<div class="row">

         <div class="form-group col-lg-3">
           <label for="exampleInputPassword1">Departure Port</label>
		          <select  required class="form-control select2" name="FromTOPID" id="FromTOPID">
				  <option value="">Select Port</option>';

while ($RowPorts = $StmtPorts->fetch_array()) {
	$data .= '<option value="' . $RowPorts['Name'] . '">
					 ' . $RowPorts['Name'] . '</option>';
}
$data .= '</select>

	             </div>

				 <div class="form-group col-lg-3">
           <label for="exampleInputPassword1">Arrival Port</label>
		          <select  required class="form-control select2" name="ToTOPID" id="ToTOPID">
				  <option value="">Select Port</option>';

while ($RowPorts = $StmttoPorts->fetch_array()) {
	$data .= '<option value="' . $RowPorts['Name'] . '">
					 ' . $RowPorts['Name'] . '</option>';
}
$data .= '</select>

	             </div>



<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Departure Date<span style="color:red">*</span></label>
				<input required  type="date" class="form-control" name="FromDate" id="FromDate"
				  placeholder="Enter BL Number" >

</div>






<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Arrival Date<span style="color:red">*</span></label>
				<input required  type="date" class="form-control" name="TranshipDt" id="TranshipDt"
				  placeholder="Enter BL Number" value="">

</div>


<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Vessel Name<span style="color:red">*</span></label>
				<input required  type="text" class="form-control" name="VesselName" id="VesselName"
				  placeholder="Enter Vessel Name" value="">

</div>


<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">VOY#</label>
				<input required  type="text" class="form-control" name="VOYNO" id="VOYNO"
				  placeholder="Enter VOY Number" value="">

</div>



   </div>


                </div>
		<div align="CENTER" class="form-group col-sm-12">

		';
if (!$isCommercialInvoiceLocked) {
	$data .= '<Button type="text" class="btn btn-success InsertTranshipments" name="InsertTranshipments" id="' . $PoID . '">Submit</Button>';
}
$data .= '


		                </div>

		      </form>



		';
$SNo = 1;

$data .= '<table id="key-datatable" data-paging="false"
										class="table dt-responsive nowrap w-100  " >
                   <thead class="thead-light">


                                                        <th>S.No</th>
                                                        <th>Departure Port</th>
                                                        <th>Arrival Port</th>
                                                        <th>Departure Date</th>
                                                        <th>Vessel Name</th>
                                                        <th>VOY#</th>
                                                        <th>Arrival Date</th>
                                                        <th>Remove</th>
														';


$data .= '
                                                </thead>
                                                <tbody>';


while ($TransShipmentsRow = $StmtTransShipments->fetch_array()) {


	$data .= '<tr>
                                                        <td>' . $SNo . '</td>




												  <td> <select class="form-control select2" name="DeparturePort" id="DeparturePort'. $TransShipmentsRow['TspID'] . '">';



														$SqlPorts = "SELECT * FROM ScrmPortCities ";
                                                        $StmtFromPorts = $iconn->query($SqlPorts);



														while($RowFromPorts=$StmtFromPorts->fetch_array()){
															$data .= '  <option value="'.$RowFromPorts['Name'].'" ';
														  if($RowFromPorts['Name']==$TransShipmentsRow['FromTOPID'])
														  $data .= 'selected="selected"';
														$data .= ' > '.$RowFromPorts['Name'].'</option>';
														 }
														 $data .= '  </select>
												  </td>

												  <td> <select class="form-control select2" name="ArrivalPort" id="ArrivalPort'. $TransShipmentsRow['TspID'] . '">';



														$SqlPorts = "SELECT * FROM ScrmPortCities ";
                                                        $StmtToPorts = $iconn->query($SqlPorts);



														while($RowToPorts=$StmtToPorts->fetch_array()){
															$data .= '  <option value="'.$RowToPorts['Name'].'" ';
														  if($RowToPorts['Name']==$TransShipmentsRow['ToTOPID'])
														  $data .= 'selected="selected"';
														$data .= ' > '.$RowToPorts['Name'].'</option>';
														 }
														 $data .= '  </select>
												  </td>

												  <td> <input  type="date" onkeydown="return false" class="form-control"  name="DepartureDate" id="DepartureDate'.$TransShipmentsRow['TspID'].'"
												  placeholder="" value="'.$TransShipmentsRow['FromDate'].'"></td>

                                                  <td> <input  type="text"  class="form-control"  name="VesselName" id="VesselName'.$TransShipmentsRow['TspID'].'"
												  placeholder="" value="'.$TransShipmentsRow['VesselName'].'"></td>

                                                  <td> <input  type="text"  class="form-control"  name="VOYNO" id="VOYNO'.$TransShipmentsRow['TspID'].'"
												  placeholder="" value="'.$TransShipmentsRow['VOYNO'].'"></td>

												  <td> <input  type="date" onkeydown="return false" class="form-control"  name="ArrivalDate" id="ArrivalDate'.$TransShipmentsRow['TspID'].'"
												  placeholder="" value="'.$TransShipmentsRow['TranshipDt'].'"></td>





								 <td>
								';
								if (!$isCommercialInvoiceLocked) {
									$data .= '<button class="btn btn-link text-primary UpdateTranshipments" data-id="' . $TransShipmentsRow['TspID'] . '" data-toggle="tooltip" data-placement="top" title="Update" value=""
							data-original-title="Update"><i class="mdi mdi-pencil font-size-16">
							</i></button>
								 <button class="btn btn-link text-danger Delete" data-id="' . $TransShipmentsRow['TspID'] . '" data-toggle="tooltip" data-placement="top" title="Delete" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
							</i></button>';
								}
								$data .= '
								 </td>
														';

	$data .= '</tr>';

	$SNo++;
}
$data .= '<tr>';


$data .= ' </tr>
';


echo $data .= '</tbody></table>';


?>
<script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>
<Script>

	$('document').ready(function () {


		$("#FromTOPID").select2();
		$("#ToTOPID").select2();
		
    var $DeparturePort = $('select[name="' + 'DeparturePort'  + '"]').select2();
    var $ArrivalPort = $('select[name="' + 'ArrivalPort'  + '"]').select2();
    
	});
</Script>

<script>


	/* function showPoPopUp(PoNo,PoID){
	   
				$.ajax({
			  url:'Ajax/AjTranshipList.php',
			 type:'POST',
			 data:{'PoID':PoID},
			 success:function(data){
			   //  $("#Update").hide();
   
				$('.modal-title').text('PO Status Details - '+PoNo);
			   $("#PopUpTable").html(data);
   
			   $('#myModal').modal('show');
					   }
			   });
   
   }
	 */
</script>
