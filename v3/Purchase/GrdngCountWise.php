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
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
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
	  $Query.="and PurchaseStationID='$PsID'";
  }else{
	   $Query="";
  }


         if($Type==1){
			$Query= " month(PurchaseDate)='$Month'  and year(PurchaseDate)='$Year'  $Query";
		 }else {
			$Query= " PurchaseDate between '$Month'  and '$Year'  $Query"; 
		 }

global $Query;



  $HlGradingQuery = "
select IM.RmLotID, IM.LotNumber, IM.PurchaseDate, IM.PurchaseCount , 
IM.PurchaseWeight , HW.GradingDate as GradingDate ,
COALESCE(DATEDIFF(HW.GradingDate,IM.PurchaseDate),0) as DaysRemaining,
(SELECT round(sum(Weight),2) FROM `RmBillBreakup`  WHERE ArrivalID=IM.RmLotID) as ActualWeight,
(SELECT GROUP_CONCAT(Count) FROM `RmBillBreakup`  WHERE ArrivalID=IM.RmLotID) as ActualCount
from RmLotDetatils AS IM
left join PpcHLGradingDetails as HW  ON HW.LotID=IM.RmLotID 
left join RmBillDetails AS RBD ON RBD.ArrivalID=IM.RmLotID
 WHERE $Query
 order by IM.PurchaseDate,IM.LotNumber ASC"; // RBD.Yield,

	$HlGradingExec=$iconn->query($HlGradingQuery);

//echo $HlGradingQuery;
	$BILLED_LOTS=$stmt_hl->num_rows;
	$table=	getCostingTable($iconn,$HlGradingExec);

	echo $table;



	function getCostingTable($iconn,$HlGradingExec){

	$text=4;
	$text1=2;



	$content .= '<table border="1"  width="100%" cellspacing="0" cellpadding="1">

			  <tr>

               <th>S No</th>
				<th>Lot Number</th>				               
			   <th>Rm Date</th>
			   <th>Grading Date</th>
			   <th>Days Delayed</th>
                <th>Weight(Expected)</th>
				<th>Count(Expected)</th>
				<th>Weight(Actual)</th>
				<th>Count(Actual)</th>
                <th >Grading Details</th>
                <th>HL Weight</th>
                <th>Yield %</th>


       			   </tr>';







	$SNO=0;
	$PREV_DATE='';


	while($RowGrading=$HlGradingExec->fetch_array()){


		$LotID=$RowGrading['RmLotID'];
		$Yield=$RowGrading['Yield'];
		$PurchaseCount=$RowGrading['PurchaseCount'];
		$PurchaseWeight=ROUND($RowGrading['PurchaseWeight'],2);

		
		$BillWeight=$RowGrading['ActualWeight'];
		$BillCount=$RowGrading['ActualCount'];
		$GradingDate=$RowGrading['GradingDate'];
	
		$LotNumber=$RowGrading['LotNumber'];
		$PurchaseDate=$RowGrading['PurchaseDate'];
		$DaysRemaining=$RowGrading['DaysRemaining'];
		
		$TotalWeightHon+=$BillWeight;
		$ExpWeightHon+=$RowGrading['PurchaseWeight'];




	//umask
	$PurchaseDate=date("d-m-Y",strtotime($PurchaseDate));
  	$GradingDate=date("d-m-Y",strtotime($GradingDate));

	/* */


			



$each_lot='<table border="1" cellspacing="0" cellpadding="1">';

$countRow='<td  align="left" width="10px"><font size="'.$text1.'">Count</td>';
$WeightRow='<td  align="left" width="10px"><font size="'.$text1.'">Weight</td>';
$each_lot_hl_weight=0;

$HlBreakUpQuery="SELECT * FROM PpcHLGradingBreakup WHERE LotID='$LotID'";
$HlBreakUpExec=$iconn->query($HlBreakUpQuery);

while ($RowHlBrkUp=$HlBreakUpExec->fetch_array()) {

$each_lot_hl_weight+=$RowHlBrkUp['HLWeight'];
$NetWeight=0;

    $countRow.='<td  >'.$RowHlBrkUp['HLCount'].'-c</td>';
      $WeightRow.='<td >'.$RowHlBrkUp['HLWeight'].'</td>';



}
 

 $each_lot.='<tr>'.$countRow.'</tr>';
  $each_lot.='<tr>'.$WeightRow.'</tr>';

$each_lot.='</table> ';

$yield=round(($each_lot_hl_weight/$BillWeight)*100,2);

$TotalWeightHL+=$each_lot_hl_weight;


		$content .= '
				<tr>

        <td>'.++$SNO.'</td>
        <td>'.$LotNumber.'</td>
		<td>'.$PurchaseDate.'</td>
        <td>'. $GradingDate.'</td>
        <td>'. $DaysRemaining.'</td>
		<td>'.$PurchaseWeight.'</td>
		<td>'.$PurchaseCount.'</td>
		<td>'.$BillWeight.'</td>
		<td>'.$BillCount.'</td>	
		<td>'. $each_lot.'</td>
		<td>'. $each_lot_hl_weight.'</td>
		<td>'. $yield.'</td>
		';
$content.='</tr>';





	}


	$content.='<tr>

<td >Total </td>'; 
$content.='<td ></td>';
$content.='<td ></td>';
$content.='<td ></td>';
$content.='<td ></td>';
$content.='<td >'.$ExpWeightHon.'</td>';
$content.='<td ></td>';
$content.='<td >'.$TotalWeightHon.'</td>';

$content.='<td ></td>';
$content.='<td ></td>';

$content.='<td >'.$TotalWeightHL.'</td>';

$DAY_PUR_TOT=0;
$T_HL=0;

		  $content.='

				</table>';


	return $content;


	}

 

?>
