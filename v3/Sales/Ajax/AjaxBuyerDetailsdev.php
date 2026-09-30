<style>
th {
    font-size: 15px;
}

</style><style>
td {
    font-size: 15px;

}

</style>
<?php 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$ConID=$_POST['ConID'];
$YEAR=$_POST['YEAR']=2023;
	
	  $SqlLate="SELECT PoNo,PoID,ShortName as Variety,PGD.Grade,Mcs,APS.PackingStyle,Price,
sum(Mcs*TotalSlabs*SlabWeight*LbsFactor ) as Lbs,
sum(Mcs*TotalSlabs*SlabWeight*Price*PriceFactor ) as TotalPrice
FROM `ScrmPoData` as SPD
LEFT JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
LEFT JOIN ScrmPoGradeData as PGD on PGD.InvId=SPD.PoID 
LEFT JOIN ScrmPIVarietyList as VL on PGD.VarietyID=VL.VarietyID
LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID 
WHERE Year(SPD.PoDate)='$YEAR' and ConID='$ConID' GROUP BY PGD.ID
ORDER by PoID ,Abs(PGD.Grade) ASC";
$StmtLate=$iconn->query($SqlLate);

if (!empty($StmtLate)) {
        echo json_encode($result);

    }

	 $data .='<table  id="key-datatable" data-paging="false" 
class="table dt-responsive wrap w-100"  >
                    <thead class="thead-light" >
                   
                   
                                                        
													
                                                        <th>Po No</th>
                                                        <th>Variety</th>
                                                        <th>Grade</th>
                                                        <th>Mcs</th>
                                                        <th>Price($)</th>
														<th>LBS</th>
														<th>Value($)</th>
                                                        
                                                    
                                                </thead>
                                                <tbody >';
												
 while($LateRow=$StmtLate->fetch_array()){ 
	
	
         $data.='<tr>
		 
                                                      
                                                        <td>'.  $LateRow['PoNo'] .'</a></td>
                                                        <td>'.  $LateRow['Variety'] .'</td>
                                                        <td>'.  $LateRow['Grade'] .'</td>
                                                        <td>'.  $LateRow['Mcs'] .'</td>
														<td>'.  $LateRow['Price'] .'</td>
														<td>'.  $LateRow['Lbs'] .'</td>
														<td>'.  $LateRow['TotalPrice'] .'</td>
                                                    
                                                        
                                                    </tr>';
													
 }
                                               

	

 		echo $data .='</tbody></table>';
	
	
?>

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