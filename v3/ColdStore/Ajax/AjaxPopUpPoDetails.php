<?php 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$PoID=$_POST['PoID'];
//$YEAR=$_POST['YEAR'];
	$AccessType=$_SESSION["AccessType"];
	 $SqlLate="SELECT FreezingType,PGD.ID AS PGDID,CurrentMcs,PoNo,PoID,ShortName as Variety,Grade,Mcs,PackingStyle,Price as LbPrice,
sum(Mcs*TotalSlabs*SlabWeight*LbsFactor ) as Lbs,
sum(Mcs*TotalSlabs*SlabWeight*Price ) as Price
FROM `ScrmPoData` as SPD
LEFT JOIN ScrmPoGradeData as PGD on PGD.InvId=SPD.PoID 
LEFT JOIN ScrmPIVarietyList as VL on PGD.VarietyID=VL.VarietyID
LEFT JOIN AdminPackingStyles as APS on APS.PCKID=PGD.PackingStyleID 
WHERE  PoID='$PoID' GROUP BY PGD.ID order by Abs(Grade) ASC";
$StmtLate=$iconn->query($SqlLate);
	 //$data='<div> <a href="CreateShipmentStatus.php?PoID='.$PoID.'"  class="btn btn-primary ">UpdateStatus  </a> </div>';


	
	
	 $data .='<table id="key-datatable" data-paging="false"  
										class="table dt-responsive nowrap w-100  " >
                   <thead class="thead-light">
                    
                   
                                                        
													
                                                        
                                                        <th>Variety</th>
                                                        <th>Freezing Type</th>
                                                        <th>Grade</th>
                                                        <th>Mcs</th>
                                                        <th>Current Mcs</th>
                                                        <th>Packing Style</th>';
														if($AccessType < 3){
														$data .=' <th>Price($)</th>';}
														$data .=' 	<th>LBS</th>';
														if($AccessType < 3){
														$data .=' <th>Value($)</th>';
														}
													/*	$data .=' <th>Current Mcs</th>';
														$data .=' <th>Actions</th>';
*/

                                                        
                                                   $data .='
                                                </thead>
                                                <tbody>';
												$TotalMcs=0;
												$TotalLbs=0;
												$Totalprice=0;
												
 while($LateRow=$StmtLate->fetch_array()){ 
	
	$PoNo =$LateRow['PoNo'];
	$TotalMcs +=$LateRow['Mcs'];
	$TotalLbs +=$LateRow['Lbs'];
    $Totalprice +=$LateRow['Price'];
    $TotalCMcs +=$LateRow['CurrentMcs'];
    $PgdID=$LateRow['PGDID'];
         $data.='<tr>
		 
                                                      
                                                        <td>'.  $LateRow['Variety'] .'</td>
														<td>'.  $LateRow['FreezingType'] .'</td>
                                                        <td>'.  $LateRow['Grade'] .'</td>
                                                        <td>'.  $LateRow['Mcs'] .'</td>
                                                        <td>'.  checkEmpty($LateRow['CurrentMcs']) .'</td>
                                                        <td>'.  $LateRow['PackingStyle'] .'</td>';
                                                        if($AccessType<3){
														$data .=' <td>'.  $LateRow['LbPrice'] .'</td>	';}		
													$data .=' 	<td>'. number_format( $LateRow['Lbs'],2) .'</td>';
                                                        if($AccessType<3){
														$data .=' <td>'.  number_format($LateRow['Price'],2) .'</td>';
														}
													/*	$data .='<td>
														<input   type="text" class="form-control" 
														name="CurrentMcs" id="CurrentMcs'.$PgdID.'" id="CurrentMcs" 
														placeholder="Enter Current Mcs" 
														value="'. $LateRow['CurrentMcs'] .'"  </td>
														
														<td>
														
														
														<button class="btn btn-link text-primary Edit" 
														data-id="'.$PgdID .'" 	 
														onclick="updateCurrentMcs('.$PgdID .')"
																										
													data-toggle="tooltip" data-placement="top" 
													title="Update" value=""
													data-original-title="Edit">
													<i class="mdi mdi-pencil font-size-18"></i>
													</button> 						
								 

																</td>';*/
														
														$data .='</tr>';
													
 }
 $data.='<tr>
		 
                                                      
                                                       <td>Total</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td>'.  $TotalMcs.'</td>
                                                        <td>'.  checkEmpty($TotalCMcs).'</td>
                                                        
														<td></td>';
														if($AccessType<3){
													 $data .='	<td></td>';}
														 $data .='<td>'.  $TotalLbs.'</td>';
														if($AccessType<3){
														 $data .='<td>'.  number_format($Totalprice,2) .'</td>';}
                                                    
                                                        
                                                   $data .=' </tr>
';
                                               

 	echo	 $data .='</tbody>

		</table>';
		
	function checkEmpty($Value){

	
		if($Value==''){
		$Value='--';	
		}
		
		return $Value;
	}
	
?>


<script>

    function updateCurrentMcs(GradeId){


	  
	  var CurrentMcs='#CurrentMcs'+GradeId;
	 
	  var CurrentMcs=$(CurrentMcs+"").val();
	  var r = confirm("Are You Sure Do You Want To Update?");
  if (r == true) { 
		$.ajax({
		  url:'Ajax/AjCsUpdates.php',
		  type:'POST',
		  data:{
		  
		  'Type':'CurrentMcsUpdate',
		  'GradeId':GradeId,
		  'CurrentMcs':CurrentMcs
		  
		  },
		  success:function(data){
			  
			  var obj = JSON.parse(JSON.stringify(data));  
		 if(obj.error == null){
			 alert("Updated Successfully");
				var PoID= $('#Update').val();
				var PoNo= '<?php echo $PoNo ?>';
				showPoPopUp(PoNo,PoID)
		 }else{
			 alert(obj.error);
		 }
			  
			  

			}
			});	
  }else{}		
			
					  
  }
  
  //);
  
  function showPoPopUp(PoNo,PoID){
	
			 $.ajax({
		   url:'Ajax/AjaxPopUpPoDetails.php',
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
  
</script>

