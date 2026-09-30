<?php
//ini_set('display_errors', 1);

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


$Year=$_POST['Year'];
$Month=$_POST['Month'];

$SqlPDBilling="SELECT * FROM `PdBillGenerationLog` as PDG 
left join  RmVAStations as RVS on RVS.StationID=PDG.StationId where Month(FromDate)='$Month' 
and Year(FromDate)='$Year'";
$StmtPDBilling=$iconn->query($SqlPDBilling);

  
$content .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
<tr>
      <th>S.No</th>
      <th>Station</th>
      <th>Invoice No</th>
	  <th>From Date</th>
	  <th>To Date</th>
	  <th>BillClosed Date</th>
      <th>Actions</th></tr>
      </thead>
    <tbody>';

	$SNo=1;

	while($RowPDBilling = $StmtPDBilling->fetch_assoc()){
$BillClosedDate=$RowPDBilling['BillClosedDate'];
	$content .= '<tr><td> '.$SNo.' </td>
		
		
		<td > '.$RowPDBilling['UnitName'].'</td>
		<td> '. $RowPDBilling['InvNo'].'</td>  
		<td> '.getFormatedDate($RowPDBilling['FromDate']).'</td>  
		<td> '. getFormatedDate($RowPDBilling['ToDate']).'</td> 
		<td> '. getFormatedDate($RowPDBilling['BillClosedDate']).'</td> 
		<td> ';
if($BillClosedDate==''){		
		
		
	$content .= '	<a target="_blank" href="#" id="EditPDBill" data-id="'.$RowPDBilling['PdBillID'].'"
						 class="mr-3 text-primary"  data-placement="top"
						 title=" Edit" data-original-title="Edit"
><i class="mdi mdi-pencil font-size-16"></i></a>';}
						 
						$content .= ' <a target="_blank" 
						href="Reports/DocPDBillPrint?Month='.$RowPDBilling['FromDate'].'&Year='.$RowPDBilling['ToDate'].'&PsID='.$RowPDBilling['StationId'].'" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Print" data-original-title="Edit"
						 ><i class="mdi mdi-printer font-size-16"></i></a>
					<button class="btn btn-link text-danger Delete" data-id="'.$RowPDBilling['PdBillID'].'" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button>	 
						 
						 </td> </tr> ';
		$SNo++;  

	}
	$content.='</tbody></table>'	;

echo $content;

?>
