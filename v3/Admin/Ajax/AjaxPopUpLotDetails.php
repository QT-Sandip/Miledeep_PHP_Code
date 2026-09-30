<?php 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$LotID=$_POST['LotID'];
	//$AccessType=$_SESSION["AccessType"];
	 $SqlLate="SELECT `RmLotID`,`LotID`,LotStatusID, `RmReferenceID`, `PostedUserID`, `LotNumber`, RMD.PurchaseStationID ,
ArrivedWeight,ArrivedCount,ArrivedRate,ArrivalDate,RLSD.LotStatus,
`PurchaseCategoryID`,`CreatedDateTime`, `Year`, RMD.LotStatus,
(SELECT UserName from  RmUsersList  as RUL where RUL.ID=RMD.AgentID) as AgentName,
(SELECT UserName from  RmUsersList  as RUL where RUL.ID=RMD.GraderID) as GraderName,
(SELECT PurchaseType from  RmPurchaseCategory  as RUL where RUL.CatID=RMD.PurchaseCategoryID) as PurchaseType
FROM `RmLotStatusDetails` as  RLSD
left join RmLotDetatils  as RMD on RLSD.LotID=RMD.RmLotID 
where RMD.LotStatus='1' and LotID='$LotID'  
ORDER BY RMD.PurchaseDate DESC, RMD.LotNumber DESC LIMIT 100";
$StmtLate=$iconn->query($SqlLate);


	
	
	 $data .='<table id="key-datatable" data-paging="false"  
										class="table dt-responsive nowrap w-100  " >
                   <thead class="thead-light">
                    
                   
                                                        
													
                                                        
                                                       <th>ID</th>
                  <th>LOT</th>
				  <th>Arrival Date</th>
				   <th>Type</th>
				  <th>Grader</th>
				  <th>Agent</th>
				  <th>Count</th>
				  <th>Weight</th>
				  <th>Rate</th>
				  <th>Actions</th>';


                                                        
                                                   $data .='
                                                </thead>
                                                <tbody>';
												
												
 while($RowRmLotDetatils=$StmtLate->fetch_array()){ 
	
	
         $data.='<tr>
		 
<td>'.$RowRmLotDetatils['RmLotID'].'</td>
                <td><B>'.$RowRmLotDetatils['LotNumber'].'</B></td>
                <td>'.date('d-m-Y',strtotime($RowRmLotDetatils['ArrivalDate'])).'</td>			
                <td>'.$RowRmLotDetatils['PurchaseType'].'</td>  
                <td>'.$RowRmLotDetatils['GraderName'].'</td>
                <td>'.$RowRmLotDetatils['AgentName'].'</td>
                <td>'.$RowRmLotDetatils['ArrivedCount'].'</td>
                <td>'.$RowRmLotDetatils['ArrivedWeight'].'</td>
                <td>'.$RowRmLotDetatils['ArrivedRate'].'</td>
				 <td>
						  
				<a target="_blank" href="EditLot.php?ID='.$RowRmLotDetatils['LotStatusID'].'" 
						 class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
                </td>
				</tr>';
													
 }
 

 	echo	 $data .='</tbody>

		</table>';
		
	function checkEmpty($Value){

	
		if($Value==''){
		$Value='--';	
		}
		
		return $Value;
	}
	
?>


