<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

$Month=$_POST['Month'];
$Month=sprintf("%02d", $Month);
$Year=$_POST['Year'];
$Type=$_POST['Type'];
$FromDate=$_POST['FromDate'];
$ToDate=$_POST['ToDate'];
$Type=$_POST['Type'];
$ContractorID=$_POST['ContactorID'];
$AccessType=$_SESSION['AccessType'];
$DeptID=$_SESSION['DeptID'];
	
$SqlSubContractor="SELECT * FROM `PpcSubContractorMaster` where ContactorID='$ContractorID' ";   
$StmtSubContractor=$iconn->query($SqlSubContractor);
?>

<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      

                  <th align="CENTER">Sub Contractor Name </th>
                  <th align="CENTER">From Date</th>
				  <th align="CENTER">To Date</th>
				  <th align="CENTER">Actions</th>
                </thead>
                <tbody>

			   

<?php	
while($RowSubContractor=$StmtSubContractor->fetch_array()){
	
	$QueryParms='Month='.$FromDate.'&Year='.$ToDate.'&ContractorID='.$ContractorID  ;
	$QueryParms=urlEncode64($QueryParms);
	
	?>
			
				<tr>

                <td  align="left" width="10%"><font size="'.$text.'"><?php echo $RowSubContractor['SubContractorName']; ?></font></td>
                <td  align="left" width="10%"><font size="'.$text.'"><B><?php echo $FromDate; ; ?></B></font></td>
                <td  align="left" width="10%"><font size="'.$text.'"><?php echo $ToDate; ?></font></td>			
  
                <td  align="left" width="5%"><font size="'.$text.'">
				
				 <a target="_blank" href="Reports/DocPDBillPrint.php?Q=<?php echo $QueryParms ?>& Type=1"
						 class="mr-3 text-primary View" data-toggle="tooltip" data-placement="top"
						 title="PD Bill Print" data-original-title="View"><i class="mdi mdi-eye font-size-18"></i></a>
				</font>
				</td>  
              
		</tr>


	<?php } ?>
	</tbody>
	</table>



	