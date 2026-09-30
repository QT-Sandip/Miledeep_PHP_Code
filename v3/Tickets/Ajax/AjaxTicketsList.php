<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$Month=$_POST['Month'];
$Year=$_POST['Year'];
$Type=$_POST['Type'];  
$TicketType=$_POST['TicketType'];  
$DeptID=$_SESSION['DeptID'];


if($PsId!=''){
	$Query="and RLD.PurchaseStationID='$PsId'";
}


 $SqlRmLotDetatils="SELECT ATL.*,RLD.LotNumber,UserName,DepartmentName,IssueName,IssueDescription,ViewUrl,
ActionToBeTaken,Url FROM AdminTickets as ATL
left join AdminGeneralIssues as AGI on AGI.IssueID=ATL.IssueID
left join AdminLoginDetails as ALD on ATL.PostedUserID=ALD.UserID
left join RmLotDetatils as RLD on RLD.RmLotID=ATL.LotID
LEFT join AdminDepartments as  AD on AD.DeptID=ALD.DeptID where TicketStatus='$TicketType' and month(ATL.PostedDateTime)='$Month' and  year(ATL.PostedDateTime)='$Year'";   


$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);

if($DeptID==3){
	$LinkUrl="../Purchase/";
}else if($DeptID==8){
	$LinkUrl="../Accounts/";
}
$LinkUrl=$_SESSION["IncludePath"];
?>

<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light nowrap">
      

                  <th>ID</th>
                  <th>Lot No</th>
                  <th>Name</th>
				  <th>Department</th>
				   <th>Date</th>
				  <th>Category</th>
				  <th>Description</th>
				  <th>Remarks</th>
				  <th>Actions</th>
                </thead>
                <tbody>

			   

<?php	
	while($RowRmLotDetatils=$StmtRmLotDetatils->fetch_array()){
		$LotID=$RowRmLotDetatils['LotID'];
		$LinkUrl=$_SESSION["SideBarPath"].$RowRmLotDetatils['ViewUrl'];



if($RowRmLotDetatils['AbStatus']!=''){	
$AbStatus=$RowRmLotDetatils['Name'];
 $Colour=$RowRmLotDetatils['Colour'];
}else{
$AbStatus='Test Pending';
$Colour='gray';
}




		if($RowRmLotDetatils['LotStatus']==2){			
			$AbStatus.='  (R)';
		}
		
		IF($RowRmLotDetatils['PurchaseType']=='Shed weighment'){
			$AbStatus='NO TEST ';
			$Colour='#FFA500';
		}
	?>
			
				<tr>

                <td  ><?php echo $RowRmLotDetatils['TicketID']; ?></td>
                <td  ><?php echo $RowRmLotDetatils['LotNumber']; ?></td>
                <td  ><?php echo $RowRmLotDetatils['UserName']; ?></td>
				<td  ><?php echo $RowRmLotDetatils['DepartmentName']; ?></td>
                <td  ><?php echo date('d-m-Y',strtotime($RowRmLotDetatils['PostedDateTime'])); ?></td>			
                <td  ><?php echo $RowRmLotDetatils['IssueName']; ?></td>  
                <td  ><?php echo $RowRmLotDetatils['Description']; ?></td>
                <td  ><?php echo $RowRmLotDetatils['Remarks']; ?></td>
                <td >
				<a target="_blank" href="<?php echo $LinkUrl.$RowRmLotDetatils['LotID'] ?>" 
					class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" 
					title="" data-original-title="Edit"><i class="dripicons dripicons-preview font-size-18"></i></a>
				</td>
                



			

		</tr>


	<?php } ?>
	</tbody>
	</table>



	