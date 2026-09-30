<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

//$MONTH=sprintf('%02d', $_POST['Month']);
//$YEAR=$_POST['Year'];
$TYPE=$_POST['Type'];
$PsId=$_POST['PsId'];
//$date_start=$YEAR.'-'.$MONTH.'-01';
//$date_end=$YEAR.'-'.$MONTH.'-31 23:59:59';
$ToDate=$_POST['ToDate'];
$FromDate=$_POST['Fromdate'];

$TYPE=$_POST['TO_DATE'];

$DeptID=$_SESSION['DeptID'];
$AccessType=$_SESSION['AccessType'];

$COUNT=$_POST['COUNT'] ;

 

   if($TYPE=='RETURN'){
	  $LOT_ID=$_POST['LOT_ID'];
	  $oldLotData = legacyAuditFetchRowById('inputmaterial_details', 'id', $LOT_ID, 'PreProcessing');
	 $sql="UPDATE inputmaterial_details SET STATUS='1' WHERE id='$LOT_ID' ";
	$stmt=$iconn->query($sql);
	if($stmt){
		legacyAuditLogUpdateById('inputmaterial_details', 'id', $LOT_ID, $oldLotData, $_POST, [
			'ReferenceNo' => $LOT_ID,
			'Remarks' => 'Lot returned (status updated)',
		], 'PreProcessing');
	}
   }
   $query=""; 
   
  /* if($COUNT !=''){
	   $COUNT=explode('&',$COUNT);
	   $LowerCount=$COUNT[0];
	   $UpperCount=$COUNT[1];
	   
	   $query="and PurchaseCount > $LowerCount and PurchaseCount < $UpperCount";
   }else{
	$query="";   
   }*/

if($PsId!=''){
	   $query= " and PVA.StationID=".$PsId;
	   
   }
  
 
 $SqlValueAddition="SELECT ProcessingDate,UnitName,sum(WtAftrVA)as WtAftrVA,sum(SentWeight)as SentWeight,
 sum(ReceivedWeight)as ReceivedWeight,
 sum(ReturnWeight)as ReturnWeight  from PpcValueAddition as PVA
 left join PpcVAStations as PVS on PVS.StationID=PVA.StationID
 
  WHERE LotID='0' and ProcessingDate BETWEEN '$FromDate' AND '$ToDate'
 $query group by ProcessingDate";
  
 
  $StmtValueAddition=$iconn->query($SqlValueAddition);

$content .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      

                <th >Sno</th>
                  <th >VA Date</th>
                  <th >HL Sent (Kgs)</th>
                  <th >HL Received (Kgs)</th>
                  <th >HL Return (Kgs)</th>
                  <th >Difference (Kgs)</th>
                  <th >Va Qty (Kgs))</th>
				  <th >Yield %</th>
				  <th >Actions</th>
                </thead>
                <tbody>

			   ';

	$PURCHASE_AMOUNT=0;
	$Sno=1;

	while($RowValueAddition=$StmtValueAddition->fetch_array()){

		
		$ProcessingDate=getFormatedDate($RowValueAddition['ProcessingDate']);
		$WtAftrVA=$RowValueAddition['WtAftrVA'];
		$SentWeight=$RowValueAddition['SentWeight'];
		$ReceivedWeight=$RowValueAddition['ReceivedWeight'];
		$ReturnWeight=$RowValueAddition['ReturnWeight'];
		$Yield=($WtAftrVA/($ReceivedWeight-$ReturnWeight))*100;
		$Difference=$SentWeight-$ReceivedWeight;
		
		
				$content .= '<tr>

                <td >'.$Sno.'</td>
                <td >'.$ProcessingDate.'</td>
                <td >'.round($SentWeight,2).'</td>
                <td >'.round($ReceivedWeight,2).'</td>
                <td >'.round($ReturnWeight,2).'</td>
                <td >'.round($Difference,2).'</td> 
				<td >'.round($WtAftrVA,2).'</td>
                <td >'.round($Yield,2).'</td>
	       
		 <td>
		 
		<a target="_blank" href="AddVAReport.php?LotID=0&VaDate='.$ProcessingDate.'"
		class="mr-3 text-primary Add" data-toggle="tooltip" data-placement="top"
		title="" data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></a>';
		
    $content.=' </td>
		</tr>';
$Sno++;

	}
	$content.='</tbody></table>'	;


echo $content;

	?>
	
