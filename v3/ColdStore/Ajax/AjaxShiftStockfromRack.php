

<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$UnitID=$_SESSION['UnitID']; 

	if(isset($_POST['StockID'])){
	$StockID=$_POST['StockID'];
			
    $SqlRackData="SELECT * FROM `CsStoresRackData` where Position=1";
	$StmtRackData=$iconn->query($SqlRackData);
	
	$SqlCsBlocks="SELECT * FROM `CsStoresBlockData`  where ProcessingStationID='$UnitID'";
	$StmtCsPos=$iconn->query($SqlCsBlocks);
	
	
	
	 $data .='<table  class="table table-bordered ">
	 <thead>
		
		
		<tr>	
		<th>Block</th>
		<th>Rack</th>
		
		
		</tr>
		</thead>
		
		<tbody>';
	 
				$data .='
				
				<tr>';
		$data .='
		<td><select class="form-control select2" name="ToBlockID" id="ToBlockID">';
		$data .='<option value="">Select</option>';
		 while($RowData=$StmtCsPos->fetch_array()){
					$BlockName=$RowData['BlockName'];
					$BlockID=$RowData['BlockID'];
			 
		$data .='<option value="'.$BlockID.'">'.$BlockName.'</option>';
		 }

		 $data .='</select></td>';
		 
		 
		/* $data .='<td><select class="form-control Select2" name="PopUpRackId" id="PopUpRackId">';
		 while($RackData=$StmtRackData->fetch_array()){
					$RackName=$RackData['RackName'];
					$RowID=$RackData['RowID'];
			 
		$data .='<option value="'.$RowID.'">'.$RackName.'</option>';
		 }

		 $data .='</select></td>';*/
		 
				
             $data .=' <td> <select class="form-control select2" name="PopUpRackId" id="PopUpRackId">
				<option value="">Select Rack Name</option>
				</select></td>';
		
		echo $data .='</tr></tbody></table>';
					  
	}
	
?>


 <script>
 			$("#PopUpRackId").select2();	
 			$("#ToBlockID").select2();	
			
			$('#ToBlockID').change(function(){
				
		  //$("#PopUpRackId").select2();
	var block= $('#ToBlockID').val();
	//alert(block);	

	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxRackNumberSearch.php',
	method:"post",
			  data:{'id':block},
			  success:function(data){
				$('#PopUpRackId').html(data);
				
	}
	});

	});

 </script>