<style>
.customWrap {
	white-space : nowrap !important;
}
</style>


<?php 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$PoID=$_POST['PoID'];
//$YEAR=$_POST['YEAR'];
	$AccessType=$_SESSION["AccessType"];
	 $SqlVarietyList="SELECT *,SBM.BrandName AS BRND,STM.ShortCode as Treatment,
  APS.PackingStyle AS PCKSTYL
  FROM `ScrmPIVarietyList` SPVL
left join ScrmBrandsMaster SBM ON SBM.BrandID=SPVL.BrandName
left join AdminPackingStyles APS ON APS.PCKID=SPVL.PackingStyle
left join ScrmTreatmentsMaster STM ON STM.ShortCode=SPVL.Treatment
  where SPVL.Status='1' ORDER by VarietyID desc";
$StmtVarietyList=$iconn->query($SqlVarietyList);
	 //$data='<div> <a href="CreateShipmentStatus.php?PoID='.$PoID.'"  class="btn btn-primary ">UpdateStatus  </a> </div>';


	
	
	 $data .='  
	
	 
	 <table id="key-datatable" data-paging="false"  class="table dt-responsive nowrap w-100 " >
			
			
                   <thead class="thead-light">
      
					<th>S.No</th>
					<th>Description</th>
					<th>Grade</th>
					<!--<th>Treatment</th>
					<th>Preparation</th> -->
					<th>BrandName	</th>
					<th>PackingStyle	</th>    
					<th>BuyerSUPC</th>
					<th>Our SUPC</th>
					<th>Actions</th>';

                                                        
		$data .='
				</thead>
			<tbody>';
			$TotalMcs=0;
			$TotalLbs=0;
			$Totalprice=0;
			$SNo=1;
												
 while($RowVariety=$StmtVarietyList->fetch_array()){ 
	
	$VarietyId=$RowVariety['VarietyID'];
         $data.='<tr>
		 
          <td> '.$SNo.'</td>
		<td> '.$RowVariety['ProductDescription']
		
	
		.'</td> 
		<td> '.$RowVariety['Grade'].'</td> 
	<!--	<td> '.$RowVariety['TreatmentName'].'</td> 
		<td> '.$RowVariety['PreparationType'].'</td> -->
		<td> '.$RowVariety['BRND'].'</td> 
		<td> '.$RowVariety['PCKSTYL'].'</td> 
		<td> '.$RowVariety['BuyerSUPC'].'</td> 
		<td> '.$RowVariety['DeviSUPC'].'</td> 
		
		<td>
				<Button type="text" class="btn btn-success SelectVariety" name="SelectVariety" 
				data-id="'.$VarietyId.'">Select</Button>
 						
        </td>';
		//}
		$data .='</tr>';
	$SNo++;												
 }
 
 	echo	 $data .='</tbody> </table>';
	// echo	 $data .='</tbody> ';
		
	function checkEmpty($Value){

	
		if($Value==''){
		$Value='--';	
		}
		
		return $Value;
	}
	
?>
  <?php // include ($IncludesPath."ShowDataTable.php");?>


<script>

function showData(data,sort){
			
		var IsSort=true;
			if(sort==false){
				IsSort=false;
			}else{
				IsSort=true;
			}
		$("#ShowDataTable").html(data);	

		 var table = $('#key-datatable').DataTable({			 
			// scrollY: "380px",
			// scrollX:true,
			// sScrollXInner: "150%", 
			// paging:true,
			// destroy: true,
			// fixedColumns: true,
			bSort : IsSort,
			"bStateSave": true,
			"fnStateSave": function (oSettings, oData) {
				localStorage.setItem('key-datatable', JSON.stringify(oData));
			},
			"fnStateLoad": function (oSettings) {
				return JSON.parse(localStorage.getItem('key-datatable'));
			}
		});
			

}


$(document).ready(function(){
	  showData();
 
	});


  
</script>

