<style>
table tr td{
	
	    font-size: 12px;
    text-align: left;
}

table tr th{
	
	    font-size: 15px;
    text-align: left;
	 background: white;
  position: sticky;
  top: 0;
}


</style>
<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

   $QueryParms=$_GET['Q']; 
  
  if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
 
  $Type=$_GET['Type'];
  $Month=$_GET['Month']; 
  $Year=$_GET['Year']; 
  $PsID=$_GET['PsID']; 
  
  if($PsID!=''){
	  $PsID=$_GET['PsID'];	  
	  $Query="and PurchaseStationID='$PsID'";
  }else{
	   $Query="";
  }
 if($Type==1){
			$Query= " and month(PurchaseDate)='$Month'  and year(PurchaseDate)='$Year'  $Query";
		 }else {
			$Query= " and PurchaseDate between '$Month'  and '$Year'  $Query"; 
		 }
   
	$table_data=getP_TABLE($iconn,$from_date,$to_date);
	echo $table_data;


	//$pdf->writeHTML($table_data);
  
 
$file_name='DS.pdf'.$row['PO_NUM'];


function getP_TABLE($iconn,$from_date,$to_date){
	
	 $date_end=$date.' 23:59:59';
	 global $Query,$Month,$Year;
	

	 
	 $LotQuery="select PurchaseDate,LotNumber,HonWeight as HonPackedWeight,HonCount as HonPackedCount,GradingDate from RmLotDetatils as RMD
LEFT join PpcHonGradingDetails as HOGD on RMD.RmLotID=HOGD.LotID
LEFT join PpcHonGradingBreakup as HOG on RMD.RmLotID=HOG.LotID
WHERE  HonWeight IS NOT NULL  $Query
	 ORDER BY PurchaseDate ASC,LotNumber ASC";
	$ExecLots=$iconn->query($LotQuery);
//echo $LotQuery;
	$content='';

	$size=40;	
	$size1=60;
	$w1="10";
	$TextL=5;
	$TextS=2;	
	






	 $content .= '<table border="1" width="100%" cellspacing="0" cellpadding="3">

						
			

				

				<thead>  

                <th>S No</th>
				<th >Purchase Date</th>
                <th>Lot Number</th>
				<th >Head on Count(Packing)</th>
				<th >Head on Qty(Packing)</th>				
											

				</thead>';
				
				




$C=0;
$SNo=0;
$WEIGHT_TOTAL=0;
while($RowLots = $ExecLots->fetch_array()){ 

$PurchaseDate=date('d-m-Y', strtotime($RowLots['PurchaseDate'])); 
$LotNumber=$RowLots['LotNumber']; 
$HonPackedWeight=$RowLots['HonPackedWeight'];
$HonPackedCount=$RowLots['HonPackedCount']; 
$HonPackedRate=$RowLots['HonPackedRate']; 

$WeightTotal+=$HonPackedWeight;



		$content .= ' 
			
				
				<tr> 
				<td>'.++$SNo.'</td>
				<td>'.$PurchaseDate.'</td>
				<td>'.$LotNumber.'</td>
				<td>'.$HonPackedCount.'</td>
				<td>'.$HonPackedWeight.'</td>
				
				</tr>';
	
}


				$content .= '
				<tr>
				<td></td>
				<td>Total Lots</td>
				<td>Total Weight (Kgs)</td>
                 <td></td>
               <td>'.$WeightTotal.'  Kgs</td>
				</tr>';

		$content .= '
		
		</table>';
		
		
		
		
		//header("Content-Type: application/xls");
		//	header("Content-Disposition: attachment; filename=Purchase_Report".$from_date."&".$to_date.".xls");
				

	return $content;


}

?>