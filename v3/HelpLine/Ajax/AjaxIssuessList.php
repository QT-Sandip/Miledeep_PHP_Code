<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$Month=$_POST['Month'];
$Year=$_POST['Year'];
$Type=$_POST['Type'];  
$TicketType=$_POST['TicketType'];  
$DeptID=$_SESSION['DeptID'];


if($PsId!=''){
	$Query="and RLD.PurchaseStationID='$PsId'";
}

  $SqlRmLotDetatils="SELECT * FROM AdminSerpTickets as AST
left join AdminSerpTicketOptions as ASTO on ASTO.Id=AST.TicketType
left join AdminLoginDetails as ALD on AST.RaisedBy=ALD.UserID
left join AdminSerpTicketStatusCodes as ASTC on ASTC.Code=AST.IssueStatus
where IssueStatus='$TicketType' and month(AST.CreateDate)='$Month' and  year(AST.CreateDate)='$Year'";   


$StmtRmLotDetatils=$iconn->query($SqlRmLotDetatils);


?>

<table  id="key-datatable" data-paging="false" class="table dt-responsive wrap w-100" >										
		
<thead class="thead-light wrap">
      

                  <th>ID</th>
                  <th>Issue Type</th>
				  <th>Issue Description</th>
				  <th>Raised By</th>
				  <th>Raised Date</th>
				  <th>Status</th>
				  <th>Actions</th>
				  
                </thead>
                <tbody>

			   

<?php	
	while($RowRmLotDetatils=$StmtRmLotDetatils->fetch_array()){
		
$Status=$RowRmLotDetatils['Name'];
 $Colour=$RowRmLotDetatils['Colour'];


	?>
			
				<tr>

                <td  ><?php echo $RowRmLotDetatils['TicketId']; ?></td>
                <td  ><?php echo $RowRmLotDetatils['TicketName']; ?></td>
				<td  ><?php echo $RowRmLotDetatils['IssueDescription']; ?></td>
                
                <td  ><?php echo $RowRmLotDetatils['UserName']; ?></td>
                <td  ><?php echo getFormatedDate($RowRmLotDetatils['CreateDate']); ?></td>
              <!--  <td >
				<a target="_blank" href="<?php echo $LinkUrl.$RowRmLotDetatils['LotID'] ?>" 
					class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" 
					title="" data-original-title="Edit"><i class="dripicons dripicons-preview font-size-18"></i></a>
				</td> -->
				
				<td >
				<div  class=" badge badge-warning font-size-10 "
	style="width:70px;background:<?php echo $Colour; ?>;color:white"><?php echo  $Status ?> </div>
				</td>
				
				<td >
				<a target="_blank" href="<?php echo $RowRmLotDetatils['IssueLink']?>" 
					class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" 
					title="View Issue" data-original-title="Edit"><i class="dripicons dripicons-preview font-size-18"></i></a>
				
				<a target="_blank" href="<?php echo $RowRmLotDetatils['IssueImage']?>" 
					class="mr-3 text-warning" data-toggle="tooltip" data-placement="top" 
					title="View Image" data-original-title="Edit"><i class="dripicons dripicons-preview font-size-18"></i></a>
				
				</td>
                



			

		</tr>


	<?php } ?>
	</tbody>
	</table>



	