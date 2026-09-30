
<?php
//ini_set('display_errors', 1);

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

//$Type=$_POST['Type'];

$ContractorID=$_POST['ContractorID'];
$SupplierType=$_POST['SupplierType'];
$PurchaseStationId=$_POST['PsId'];
$BatchID=$_POST['BatchID'];
$Date=$_POST['Date'];
$EntryType=$_POST['EntryType'];
$QueryParms=$_POST['QueryParms'];

$_SESSION['D_ID'];
$_SESSION['D_ID'];
$_SESSION['ACCESS_TYPE'];
$Query="";

if($PurchaseStationId !=''){
   $Query.="and PDBD.StationID='$PurchaseStationId'";
  
}
if($SupplierType !=''){
  $Query.=" and  PCM.Type='$SupplierType'";
 
}
if($ContractorID !=''){
  $Query.=" and  PCM.ContractorID='$ContractorID'";
 
}


/*$SqlShifts="SELECT * FROM `PpcOptions` WHERE Value='SHIFTS'";
$StmtShifts=$iconn->query($SqlShifts);
 while($RowShifts=$StmtShifts->fetch_array()) {
	 $ArrayShifts[]=$RowShifts;
 }*/
 $accessType=$_SESSION['ACCESS_TYPE'];

/*
    echo $SqlDetails="SELECT SubContactorID,SubContractorName,PSC.AutoCharge FROM  PpcSubContractorMaster as PSC
  left join PpcContractMaster as PCM on PCM.ContractorID=PSC.ContactorID 
  where SubContactorID in (select DISTINCT BatchID from PpcDailyBatchDetails)
  $Query"; exit;*/
  
     $SqlDetails="SELECT SubContactorID,PDBD.Date,SubContractorName,PSC.AutoCharge,PO.Name as Shift,PDBD.Remarks,PDBD.Extras,PDBD.Adjustments,PDBD.Persons  FROM PpcDailyBatchDetails PDBD 
  left join PpcSubContractorMaster as PSC on PSC.SubContactorID=PDBD.BatchID 
  left join PpcContractMaster as PCM on PCM.ContractorID=PSC.ContactorID 
  left join PpcOptions as PO on PO.OptID=PDBD.ShiftID 
  where SubContactorID in (select DISTINCT BatchID from PpcDailyBatchDetails) and Date between '$Date' and '$Date' and PDBD.Type='$EntryType'  $Query"; 

$StmtDetails=$iconn->query($SqlDetails);


$content .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">										
		
<thead class="thead-light">
          
				 
                  <th>Batch Name</th>
                  <th>Date</th>
                  <th>Bus/Auto</th>
				  <th>Persons</th>
				  <th>Shift </th>
				  <th>Adjustments</th>
				  <th>Extras</th>
				  <th>Remarks</th>
				  <th>Remarks</th>
				 
      </thead>
    <tbody>';

	$PURCHASE_AMOUNT=0;
	$sno=1;

	while($RowDetails=$StmtDetails->fetch_array()){
		
		$BatchID= $RowDetails['SubContactorID'];


 $Url="Reports/hl-pd-bill-print.php?".$QueryParms."&BatchId=$BatchID";
				$content .= '<tr>

                <td>'.$RowDetails['SubContractorName'].'</td>
                <td><B>'.getFormatedDatelines($RowDetails['Date']).'</B></td>
                <td>'.$RowDetails['AutoCharge'].'</td>
                <td>'.$RowDetails['Persons'].'</td>
                <td>'.$RowDetails['Shift'].'</td>
                <td>'.$RowDetails['Adjustments'].'</td>
                <td>'.$RowDetails['Extras'].'</td>
                <td>'.$RowDetails['Remarks'].'</td>
                <td>
				<a target="_blank" href="'.$Url.'"
						 class="mr-3 text-primary View" data-toggle="tooltip" data-placement="top"
						 title="View" data-original-title="View"><i class="mdi mdi-eye font-size-18"></i></a>
				</td>';
                
               

					
						 
				
				 
				$content.=  ' </tr>';
$sno++;

	}
	$content.='</tbody></table>'	;

echo $content;



?>
		 <script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>


