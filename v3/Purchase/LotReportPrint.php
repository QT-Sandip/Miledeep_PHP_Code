<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
//include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");

$QueryParms=$_GET['Q']; 
  
  if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
 $Month=$_GET['Month'];
 $Year=$_GET['Year'];
 $PsID=$_GET['PsID'];
 $Type=$_GET['Type'];
 
 $FromDate=$Year.'-'.$Month.'-01';
 $ToDate=$Year.'-'.$Month.'-31 23:59:59';

  /*if(isset($_GET['U'])){
	  $UNIT=$_GET['U'];
	  $Query.="and UNIT='$UNIT'";
  }*/

if($PsID!=''){
	$Query.="and PurchaseStationID='$PsID'";
}
else{
	$Query.="";
}

global $Query,$Month,$Year,$Type;
		 
		 if($Type==1){
			$Query= "month(IM.PurchaseDate)='$Month'  and year(IM.PurchaseDate)='$Year'  $Query";
		 }else {
			$Query= "IM.PurchaseDate between '$Month'  and '$Year'  $Query"; 
		 }
/*$EXCHANGE_RATE=$_POST['EXCHANGE_RATE'];
$Expanse=$_POST['Expanse'];
$DEPB=$_POST['DEPB'];
*/

$ArrayCostingRates=ARRAY();
  $SqlCosting="SELECT * FROM SalesCostingRates WHERE TrackID= (SELECT MAX(TrackID) from SalesCostingRates)"; 
$StmtCosting=$iconn->query($SqlCosting);

while($RowCosting = $StmtCosting->fetch_assoc()){
  $ArrayCostingRates[]=$RowCosting;
}


  /*$SqlLotList = "select IM.RmLotID,IM.LotNumber,RMF.FinancierName,RMU.UserName,IM.PurchaseDate,
RPC.PurchaseType,IM.LotStatus,RBD.Deductions,RBD.Yield,RBD.Remarks,
(SELECT Weight from $RmBillBreakup WHERE Description='Hl Grading Hon' and LotID=IM.RmLotID ) as HONWeight,
(SELECT Count from $RmBillBreakup WHERE Description='Hl Grading Hon' and LotID=IM.RmLotID ) as HONCount,
(SELECT Rate from $RmBillBreakup WHERE Description='Hl Grading Hon' and LotID=IM.RmLotID ) as HONRate,
(SELECT GROUP_CONCAT(Weight)  from $RmBillBreakup WHERE Description='Head On Packed' and LotID=IM.RmLotID ) as HonPackedWeight,
(SELECT GROUP_CONCAT(Count)  from $RmBillBreakup WHERE Description='Head On Packed' and LotID=IM.RmLotID ) as HonPackedCount,
(SELECT GROUP_CONCAT(Rate) from $RmBillBreakup WHERE Description='Head On Packed' and LotID=IM.RmLotID ) as HonPackedRate,
(SELECT GROUP_CONCAT(Weight) from RmBillBreakup WHERE Description!='Head On Packed' and Description!='Hl Grading Hon' and  LotID=IM.RmLotID ) as DefectWeight,

(SELECT sum(Weight*Rate) from RmBillBreakup WHERE Description!='Head On Packed' and Description!='Hl Grading Hon' and  LotID=IM.RmLotID ) as DefectPrice

from $RmLotDetatils  AS IM  left join $RmBillDetails  AS RBD ON RBD.LotID=IM.RmLotID
 left join $PpcHonGradingDetails AS HonPack ON HonPack.LotID=IM.RmLotID
 left join $RmFinanciers as RMF on RMF.ID=RBD.FinancierID
 left join $RmUsersList as RMU on RMU.ID=IM.AgentID
 left join $RmPurchaseCategory as RPC on RPC.CatID=IM.PurchaseCategoryID
 WHERE IM.PurchaseDate BETWEEN '$FromDate'  and
	'$ToDate' $Query
	 order by
	IM.PurchaseDate,IM.LotNumber ASC "; */ 
 $SqlLotList = "select IM.RmLotID,IM.LotNumber,RMF.FinancierName,RMU.UserName,IM.PurchaseDate,
RPC.PurchaseType,IM.LotStatus,RBD.Deductions,RBD.Yield,RBD.Remarks,
(SELECT Weight from RmBillBreakup WHERE Description='Hl Grading Hon' and LotID=IM.RmLotID ) as HONWeight,
(SELECT Count from RmBillBreakup WHERE Description='Hl Grading Hon' and LotID=IM.RmLotID ) as HONCount,
(SELECT Rate from RmBillBreakup WHERE Description='Hl Grading Hon' and LotID=IM.RmLotID ) as HONRate,
(SELECT GROUP_CONCAT(Weight)  from RmBillBreakup WHERE Description='Head On Packed' and LotID=IM.RmLotID ) as HonPackedWeight,
(SELECT GROUP_CONCAT(Count)  from RmBillBreakup WHERE Description='Head On Packed' and LotID=IM.RmLotID ) as HonPackedCount,
(SELECT GROUP_CONCAT(Rate) from RmBillBreakup WHERE Description='Head On Packed' and LotID=IM.RmLotID ) as HonPackedRate,
(SELECT GROUP_CONCAT(Weight) from RmBillBreakup WHERE Description!='Head On Packed' and Description!='Hl Grading Hon' and  LotID=IM.RmLotID ) as DefectWeight,

(SELECT sum(Weight*Rate) from RmBillBreakup WHERE Description!='Head On Packed' and Description!='Hl Grading Hon' and  LotID=IM.RmLotID ) as DefectPrice
, (SELECT sum(Weight) from RmBillBreakup WHERE Description='Head On Packed' and LotID=IM.RmLotID ) as HonPackedWeightSum

from RmLotDetatils  AS IM  left join RmBillDetails  AS RBD ON RBD.LotID=IM.RmLotID
 left join PpcHonGradingDetails AS HonPack ON HonPack.LotID=IM.RmLotID
 left join RmFinanciers as RMF on RMF.ID=RBD.FinancierID
 left join RmUsersList as RMU on RMU.ID=IM.AgentID
 left join RmPurchaseCategory as RPC on RPC.CatID=IM.PurchaseCategoryID
 WHERE  $Query
	 order by
	IM.PurchaseDate,IM.LotNumber ASC ";  

	$StmtLotList=$iconn->query($SqlLotList);
	//$BILLED_LOTS=$StmtLotList->num_rows;
	$table=	getCostingTable();


	echo $table;



	function getCostingTable(){

    global $StmtLotList;
    global $iconn;
    global $ArrayCostingRates,$PpcHLGradingBreakup,$QcRmOrgnReport;
    $ReturnCount=0;



	$TextS=4;
	$TextS1=2;
	
	$TextSL=5;
	$TextS=2;

	$content .= '<table border="1" cellspacing="0" cellpadding="1">

			  <tr>
                <th  align="CENTER" width="3%"><font size="'.$TextS.'"> <B>S No</B></font></th>
                <th  align="CENTER" width="7%"><font size="'.$TextS.'"> <B>Date</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Bill No</B></font></th>
				        <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Bill Name</B></font></th>
				        <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Agent</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Status</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Purchase Wt</B></font></th>';


				foreach($ArrayCostingRates as $RowCosting){
               $content .= ' <th  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.$RowCosting['Grade'].'</B></font></th>';
				}

 $content .= '
		 <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>TOTAL</B></font></th>
		 <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Grading HON</B></font></th>
         <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Grading Count</B></font></th>
	     <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Rate</B></font></th>
         <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Packing HON</B></font></th>
         <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Packing Count</B></font></th>
         <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Rate</B></font></th>
		 <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Defects(Kgs) </B></font></th>
         <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Defects Amount</B></font></th>
		 <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Yield %</B></font></th>
         <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Lab</B></font></th>
         <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Account</B></font></th>
		 <th  align="CENTER" width="5%"><font size="'.$TextS.'"> <B>Amount</B></font></th>
			   </tr>';



	$SNO=1;
	$PREV_DATE='';


	while($RowPurchase=$StmtLotList->fetch_array()){

		$LotId=$RowPurchase['RmLotID'];
    $BILL=$RowPurchase['FinancierName'];
    $AGENT_NAME = $RowPurchase['UserName'];
    $LOT_NO=$RowPurchase['LotNumber'];
    $PURCHASE_DATE=$RowPurchase['PurchaseDate'];
    $GradedHonYield=$RowPurchase['Yield'];
    $PURCHASE_DATE=date("d-m-Y",strtotime($PURCHASE_DATE));

    if($RowPurchase['LotStatus']==2){
      $LotStatus='Return';
      $ReturnCount++;
    }else{
      $LotStatus='';
    }


/*GRADING HON*/
$GradedHonRate=$RowPurchase['HONRate'];
$GradedHonWeight=$RowPurchase['HONWeight'];
$GradedHonCount=$RowPurchase['HONCount'];
/*GRADING HON*/


/*Packed Hon*/
$PackedHonCount=$RowPurchase['HonPackedCount'];
 $PackedHonWeight=$RowPurchase['HonPackedWeight']; 
 $HonPackedWeightSum=$RowPurchase['HonPackedWeightSum']; 
$PackedHonRate=$RowPurchase['HonPackedRate'];
/*Packed Hon*/

/*DEFECT HON*/
/*$DefectiveHonWeight=0;
$DefectiveHonPrice=0;*/
$DefectiveHonWeight=$RowPurchase['DefectWeight'];
$DefectiveHonPrice=$RowPurchase['DefectPrice'];
 
 /* $SqlDefects="SELECT * FROM `RM_DEFECTS_DETAILS` where LOT_ID='$LOT_ID'";
  $StmtDefects=$iconn->query($SqlDefects);
  while($RowDefects=$StmtDefects->fetch_array()){
    $DefectiveHonWeight+=$RowDefects['WEIGHT'];
    $DefectiveHonPrice+=($RowDefects['WEIGHT']*$RowDefects['RATE']);
  }*/
/*DEFECT HON*/

/*LOT TOTALS*/
    $EachLotPurchaseWeight=$GradedHonWeight +$HonPackedWeightSum +$DefectiveHonWeight; 
    $TotalPurchaseWeight+=$EachLotPurchaseWeight;
/*LOT TOTALS*/


/*LAB & Account DEDUCTIONS */

//Minus Defective from $EachLotPurchaseWeight if needed

$SqlQcRmDetails="SELECT * from QcRmOrgnReport where LotID='$LotId'";
$StmtQcRmDetails=$iconn->query($SqlQcRmDetails);
$RowQcRmDetails = $StmtQcRmDetails->fetch_assoc();

$LabDeduction= $RowQcRmDetails['SuggestedDeductions']*$EachLotPurchaseWeight;
$AccDeductions= $RowPurchase['ActualDeductions']*$EachLotPurchaseWeight;
$AccDeductions= $RowPurchase['Deductions']*$EachLotPurchaseWeight;

$EachLotPurchaseAmt=$GradedHonWeight*$GradedHonRate; //Grading Hon Price
$EachLotPurchaseAmt+=$HonPackedWeightSum*$PackedHonRate;// Packing Hon Price
$EachLotPurchaseAmt+=$DefectiveHonPrice; //Defective Hon Price
$EachLotPurchaseAmt=$EachLotPurchaseAmt-$LabDeduction;
$EachLotPurchaseAmt=$EachLotPurchaseAmt+$AccDeductions;

$TotalPurchaseAmount+=$EachLotPurchaseAmt;



/*LAB DEDUCTIONS*/


/*GrandTotals*/

$AryPackedHonWeight = explode (",", $PackedHonWeight); 

$AryGradingHon[]=$GradedHonWeight;
foreach($AryPackedHonWeight as $Item){
$AryPackingHon[]=$Item;
}

$AryDefectiveHonWeight[]=$DefectiveHonWeight;
$AryDefectiveHonPrice[]=$DefectiveHonPrice;
$AryLabDeductions[]=$LabDeduction;
$AryAccountsDeductions[]=$AccDeductions;
$AryTotalPurchaseAmount[]=$EachLotPurchaseAmt;



/*GrandTotals*/




		$content .= '
				<tr>
        <th  align="CENTER" width="3%"><font size="'.$TextS.'"><B>'.$SNO.'</B></font></th>
        <th  align="CENTER" width="7%"><font size="2"><B>'.$PURCHASE_DATE.'</B></font></th>
			  <th  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.$LOT_NO.'</B></font></th>
				<th  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.$BILL.'</B></font></th>
				<th  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.$AGENT_NAME.'</B></font></th>
        <th  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.$LotStatus.'</B></font></th>
				<th  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.$EachLotPurchaseWeight.'</B></font></th>
				  ';
			 $SNO++;

  $AryGradesData=array();
  $AryEachLotSample=array();
  $EachLotGradingHlWeight=0;

      $SqlGrades="SELECT * FROM `PpcHLGradingBreakup` where LotID='$LotId' order by HLCount asc";
      $StmtGrades=$iconn->query($SqlGrades);
      while($RowGrades=$StmtGrades->fetch_array()){
      $AryGradesData[]=$RowGrades;
      }

$EachLotGradingHlWeight=0;
foreach($ArrayCostingRates as $RowCosting){
         $EachGradeWeight=0;
         $start=(int)$RowCosting['LowerLimit'];
				 $end=(int)$RowCosting['UpperLimit'];

		foreach($AryGradesData  as $ItemGrade){
			$count=round($ItemGrade['HLCount']);
			if($count > $start  && $count <= $end){
        $EachGradeWeight+=$ItemGrade['HLWeight'];
        $EachLotGradingHlWeight+=$ItemGrade['HLWeight'];
    }
		}

  $AryEachLotSample[]=$EachGradeWeight;

  if($EachGradeWeight==0){
    $content.='<th  align="CENTER" width="5%"><font size="'.$TextS.'"><B>-</B></font></th>';
  }else{
  $content.='<th  align="CENTER" width="5%"><font size="'.$TextS1.'">'. $EachGradeWeight.'</font></th>';
  }
	}

  $AryAllLotsSamples[]=$AryEachLotSample;
  $ARY_DAY_GRADES[]=$ary_lot_grade;





$content.='
<th  align="CENTER" width="5%"><font size="'.$TextS1.'">'.$EachLotGradingHlWeight.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.$GradedHonWeight.'</B></font></th>
<th  align="CENTER" width="5%"><font size="'.$TextS1.'">'.$GradedHonCount.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$TextS1.'">'.$GradedHonRate.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$TextS1.'">'.$PackedHonWeight.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$TextS1.'">'.$PackedHonCount.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$TextS1.'">'.$PackedHonRate.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$TextS1.'">'.$DefectiveHonWeight.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$TextS1.'">'.$DefectiveHonPrice.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$TextS1.'">'.$GradedHonYield.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$TextS1.'"> -'.$LabDeduction.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$TextS1.'">'.$AccDeductions.'</font></th>
<th  align="RIGHT" width="5%"><font size="'.$TextS1.'">'.round($EachLotPurchaseAmt).'</font></th>
</tr>';

	}




	$content.='<tr>
<td  align="CENTER" width="5%"><font size="'.$TextS.'"><B>TOTAL</B></font></td>
<td  align="CENTER" width="5%"><font size="'.$TextS.'"><B></B></font></td>
<td  align="CENTER" width="5%"><font size="'.$TextS.'"><B></B></font></td>
<td  align="CENTER" width="5%"><font size="'.$TextS.'"><B></B></font></td>
<td  align="CENTER" width="5%"><font size="'.$TextS.'"><B></B></font></td>
<td  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.$ReturnCount.'</B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.$TotalPurchaseWeight.'</B></font></td>';

$Index=0;
foreach($ArrayCostingRates as $EachLot){
  $EachGradeHeadless=0;
foreach($AryAllLotsSamples as $Item){
	  $EachGradeHeadless+= $Item[$Index];
    $TotalHeadless+= $Item[$Index];
}
$content.='<td  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.$EachGradeHeadless.'</B></font></td>';
$Index++;
}


$content.='<td  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.$TotalHeadless.'</B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.array_sum($AryGradingHon).'</B></font></td>
<td  align="CENTER" width="5%"><font size="'.$TextS.'"><B></B></font></td>
<td  align="CENTER" width="5%"><font size="'.$TextS.'"><B></B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.array_sum($AryPackingHon).'</B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$TextS.'"><B></B></font></td>
            <td  align="CENTER" width="5%"><font size="'.$TextS.'"><B></B></font></td>
            <td  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.array_sum($AryDefectiveHonWeight).'</B></font></td>
            <td  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.array_sum($AryDefectiveHonPrice).'</B></font></td>
            <td  align="CENTER" width="5%"><font size="'.$TextS.'"><B></B></font></td>
            <td  align="CENTER" width="5%"><font size="'.$TextS.'"><B>-'.array_sum($AryLabDeductions).'</B></font></td>
            <td  align="CENTER" width="5%"><font size="'.$TextS.'"><B>'.array_sum($AryAccountsDeductions).'</B></font></td>';

$content.='<td  align="RIGHT" width="5%"><font size="'.$TextS.'"><B>'.round(array_sum($AryTotalPurchaseAmount),2).'</B></font></td></tr>';
$DAY_PUR_TOT=0;
$T_HL=0;

		  $content.='

				</table>';

				/*header("Content-Type: application/xls");
			header("Content-Disposition: attachment; filename=download.xls");*/


	return $content;


	}

 function formatToInr($number){
        $number=round($number,2);
        // windows is not supported money_format
    if(setlocale(LC_MONETARY, 'en_IN')){
         return money_format('%!'.$decimal.'n', $number);
         }
     else {
        if(floor($number) == $number) {
            $append='.00';
        }else{
            $append='';
        }
        $number = preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", $number);
        return $number.$append;
        }
    }

?>
