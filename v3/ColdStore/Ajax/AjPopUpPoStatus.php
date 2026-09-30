<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	if(isset($_POST['InvId'])){
	$InvId=$_POST['InvId'];
			
    $SqlCsPos="SELECT * FROM `$CsPoData` WHERE PoID = '$InvId'";
	$StmtCsPos=$iconn->query($SqlCsPos);
	
	 $SqlCsGrades="SELECT 
	VarietyID,Grade,PackingStyle,PackingStyleID,FreezingType
	,RequiredCases,GlazePerc,Usd,TotalSlabs,SlabWeight,MFactor,
	SockingType,Brand FROM `CsPoGradeData` as PGD 
	left join CsPackingStyles as CSP 
	on CSP.PckID= PGD.PackingStyleID WHERE PoID = '$InvId'
	order by VarietyID,ABS(Grade) asc";
	$StmtCsGrades=$iconn->query($SqlCsGrades); 
	
	 $data .='<table  class="table table-bordered ">
	 <thead>
		
		
		<tr>
		<td class ="hide" id="bid">'.$BID.'</td>
		<td class ="hide" id="rckid" >'.$RACK_ID.'</td>
		
		<th>Variety</th>
		<th>Grade</th>
		<th>Packing Style</th>
		<th>Price($)</th>
		<th>Ordered / Aval (Balance)MCS</th>
		<th>Req / Aval Kgs</th>
		<th>Stock Value($) </th>
		
		</tr>
		</thead>
		
		<tbody>';
	 while($RowCsGrades=$StmtCsGrades->fetch_array()){
	
	
		$Grade = $RowCsGrades['Grade'];
		$PsId = $RowCsGrades['PackingStyleID'];
			$Variety = $RowCsGrades['VarietyID'];
			$OrderMcsPerGrade = $RowCsGrades['RequiredCases'];
			$PackingStyle = $RowCsGrades['PackingStyle'];
			$USD = $RowCsGrades['Usd'];
			$FreezingType = $RowCsGrades['FreezingType'];
			$TotalSlabs = $RowCsGrades['TotalSlabs'];
			$SlabWeight = $RowCsGrades['SlabWeight'];
			$MFactor = $RowCsGrades['MFactor'];
			
   $SqlCspi="SELECT sum(AvalMcs) as AvalMcs FROM `$CsStockData`
 	WHERE PoID = '$InvId' 
	and GradeID='$Grade' 
	and PackingStyleID='$PsId'
	and VarietyID='$Variety'
	and FreezingTypeID='$FreezingType'";
	
	$StmtCspi=$iconn->query($SqlCspi);
	$RowCspi=$StmtCspi->fetch_array();
	$AvalMcs=$RowCspi['AvalMcs'];
	
	$ReqMcs=$OrderMcsPerGrade-$AvalMcs;
	if($ReqMcs<0){
	$ReqMcs=0;	
	}
	$Balance+=$ReqMcs;
	
     $ReqKgs= ROUND($ReqMcs*$TotalSlabs*$SlabWeight*$MFactor,2);    
     $AvalKgs= ROUND($AvalMcs*$TotalSlabs*$SlabWeight*$MFactor,2);    
	$StockValueUSD=$AvalMcs*$USD*$TotalSlabs*$SlabWeight;		
		$TotalUSD+=	$StockValueUSD;	
				$data .='
				<tr>
		
		<td class ="hide" id="bid">'.$BID.'</td>
		<td >'.$Variety.'</td>
		<td >'.$Grade.'</td>
		<td >'.$PackingStyle .'</td>
		<td >$'.$USD .'</td>
		<td >'.$OrderMcsPerGrade.' / '.$AvalMcs.'('.$ReqMcs.')</td>
		<td >'.$ReqKgs.'Kgs / '.$AvalKgs.'Kgs</td>
		
		<td >$'.$StockValueUSD.'</td>
		
		
			</tr>';
					  
}


 $data .='<tr>
		
		<td >Balance MCS</td>
		<td ></td>
		<td ></td>
		<td ></td>
		<td >'.$Balance.'</td>
		
		<td ></td>
		<td >$'.$TotalUSD.'</td>';
		if($Balance!=0){
		
		$data .='<td ></td>';
		}else{
		$data .='<td></td>
		</tr>';	
}
					
		echo $data .='</tbody></table>';

		
		
		
		
		
	//	echo json_encode($OUT);
	}
	
?>