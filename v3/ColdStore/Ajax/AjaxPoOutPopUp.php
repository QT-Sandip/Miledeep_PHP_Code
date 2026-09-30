<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	if(isset($_POST['PoID'])){
	$InvId=$_POST['PoID'];
	$stockIds=$_POST['stockIds'];
	$sumMCS=$_POST['sumMCS'];
			
    $SqlCsPos="SELECT * FROM `ScrmPoData` WHERE PoID = '$InvId'";
	$StmtCsPos=$iconn->query($SqlCsPos);
	
	 $SqlCsGrades="SELECT 
	PGD.ID AS PoGradeId,Variety,Grade,PackingStyle,PackingStyleID,FreezingType,Mcs,GlazePerc,
	Treatment,Brand FROM `ScrmPoGradeData` as PGD 
	left join AdminPackingStyles as CSP 
	on CSP.PCKID= PGD.PackingStyleID WHERE InvId = '$InvId'
	order by Variety,ABS(Grade) asc"; 
	$StmtCsGrades=$iconn->query($SqlCsGrades); 
	
	 $data .='<table  class="table table-bordered ">
	 <thead>
		
		
		<tr>
	<!--	<td class ="hide" id="bid">'.$BID.'</td>
		<td class ="hide" id="rckid" >'.$RACK_ID.'</td>-->
		
		<th>Variety</th>
		<th>Grade</th>
		<th>PackingStyle Style</th>
		<th>FreezingType Type</th>
		<th>Glaze</th>
		<th>Total Mcs</th>
		<th>Aval Mcs</th>
		<th>Balance Mcs</th>
		</tr>
		</thead>
		
		<tbody>';
	 while($RowBreakUpData=$StmtCsGrades->fetch_array()){
	
	
		$PoGradeId = $RowBreakUpData['PoGradeId'];
		$Brand = $RowBreakUpData['Brand'];
		$Grade = $RowBreakUpData['Grade'];
		$PsId = $RowBreakUpData['PackingStyleStyleID'];
			$Variety = $RowBreakUpData['Variety'];
			$OrderMcsPerGrade = $RowBreakUpData['Mcs'];
			$GlazePerc = $RowBreakUpData['GlazePerc'];
			$PackingStyle = $RowBreakUpData['PackingStyle'];
			$FreezingType = $RowBreakUpData['FreezingType'];
			
		 $SqlCspi="SELECT sum(AvalMcs) as AvalMcs FROM `CsStockData`
 	WHERE PoGradeId = '$PoGradeId' and StockID in ($stockIds)"; 
	
	/*and GradeS='$Grade' 
	and PackingStyle_STYLE_ID='$PsId'
	and FORM='$Variety'
	and TYPE='$FreezingType'";*/
	
	$StmtCspi=$iconn->query($SqlCspi);
	$RowCspi=$StmtCspi->fetch_array();
	$AvalMcs=$RowCspi['AvalMcs'];
	
	// $ReqMcs=$OrderMcsPerGrade-$AvalMcs;
	// $Balance+=$ReqMcs;
		
	$ReqMcs = $sumMCS - $OrderMcsPerGrade;
	$Balance = $sumMCS - $OrderMcsPerGrade;
			
			
				$data .='
				<tr>
		
		<!--<td class ="hide" id="bid">'.$BID.'</td>-->
		<td >'.$Variety.'</td>
		<td >'.$Grade.'</td>
		<td >'.$PackingStyle .'</td>
		<td >'.$FreezingType .'</td>
		<td >'.$GlazePerc .'</td>
		<td >'.$OrderMcsPerGrade .'</td>
		
		<!--<td >'.$AvalMcs.'</td>-->
		<td >'.$sumMCS.'</td>
		<td >'.$ReqMcs.'</td>
	
		
			</tr>';
					  
}


 $data .='<tr>
		
		<td >Balance Mcs</td>
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>';
		$data .='<td>'.$Balance.'</td>';	
	
		echo '</tr>';	
$data .='<tr>
		
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>
		<td ></td>';
		if($Balance!=0){		
			$data .='<td  style="width: 110px"><button type="button" disabled  id="CSOUT" class="btn btn-danger">Stock Out </button></td>';	
		}else{
			// if($sumMCS == $OrderMcsPerGrade) {
			// 	$data .='<td  style="width: 110px"><button type="button" enabled  id="CSOUT" class="btn btn-danger">Stock Out </button></td>';	
			// } else {
			// 	$data .='<td  style="width: 110px"><button type="button" disabled  id="CSOUT" class="btn btn-primary">Stock Out </button></td>';	
			// }
			$data .='<td  style="width: 110px"><button type="button" enabled  id="CSOUT" class="btn btn-primary">Stock Out </button></td>';	
		}
			echo '</tr>';	
					
		echo $data .='</tbody></table>';

		
		
		
		
		
	//	echo json_encode($OUT);
	}
	
?>