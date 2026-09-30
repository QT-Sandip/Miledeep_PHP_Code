<?php
//ini_set('display_errors', 1);
session_start();
include "../includes/config.php";

 $from_date=$_GET['from_date'];
 $to_date=$_GET['to_date'].' 23:59:59';

  if(isset($_GET['U'])){
	  $UNIT=$_GET['U'];
	  $Query.="and UNIT='$UNIT'";
  }

  if(isset($_GET['PU'])){
	  $PU=$_GET['PU'];
	  $Query.="and UNIT_ID='$PU'";
  }

global $Query;
$EXCHANGE_RATE=$_POST['EXCHANGE_RATE'];
$Expanse=$_POST['Expanse'];
$DEPB=$_POST['DEPB'];


$ARY_COSTING_RATES=ARRAY();
$SQL_COSTING="SELECT * FROM ACC_COSTING_RATES WHERE TARCK_ID= (SELECT MAX(TARCK_ID) from ACC_COSTING_RATES)";
$STMT_SQL_COSTING=$iconn->query($SQL_COSTING);

while($ROW_COSTING = $STMT_SQL_COSTING->fetch_assoc()){
  $ARY_COSTING_RATES[]=$ROW_COSTING;
}


  $SqlLotList = "select id,LOT_NO,BILL_NAME,AGENT_NAME,PURCHASE_DATE,
PURCHASE_TYPE,IM.STATUS,
WEIGHT,RBD.COUNT,RATE,DEDUCTIONS,YIELD,REMARKS,
HON_WEIGHT,HON_PACKED_RATE,HON_PACKED_COUNT
from inputmaterial_details  AS IM  left join RM_BILL_DETAILS  AS RBD ON RBD.LOT_ID=IM.id
 left join PPC_GRADING_HON AS HonPack ON HonPack.LOT_ID=IM.id
 WHERE IM.PURCHASE_DATE BETWEEN '$from_date'  and
	'$to_date' $Query
	 order by
	IM.PURCHASE_DATE,IM.LOT_NO ASC ";

	$StmtLotList=$iconn->query($SqlLotList);
	$BILLED_LOTS=$StmtLotList->num_rows;
	$table=	getCostingTable();


	//echo 'Ungraded Lots -'.$UN_GRADED_LOTS.' / Total Lots- '.$TOTAL_LOTS;
	echo $table;



	function getCostingTable(){

    global $StmtLotList;
    global $iconn;
    global $ARY_COSTING_RATES;
    $ReturnCount=0;



	$text=4;
	$text1=2;

	$content .= '<table border="1" cellspacing="0" cellpadding="1">

			  <tr>
                <th  align="CENTER" width="3%"><font size="'.$text.'"> <B>S No</B></font></th>
                <th  align="CENTER" width="7%"><font size="'.$text.'"> <B>Date</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Bill No</B></font></th>
				        <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Bill Name</B></font></th>
				        <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Agent</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Status</B></font></th>
                <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Purchase Wt</B></font></th>';


				foreach($ARY_COSTING_RATES as $ROW_COSTING){
               $content .= ' <th  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$ROW_COSTING['GRADE'].'</B></font></th>';
				}

 $content .= '<th  align="CENTER" width="5%"><font size="'.$text.'"> <B>TOTAL</B></font></th>
 <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Grading HON</B></font></th>
         <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Grading Count</B></font></th>
			   <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Rate</B></font></th>
         <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Packing HON</B></font></th>
         <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Packing Count</B></font></th>
         <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Rate</B></font></th>
			   <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Defects(Kgs) </B></font></th>
         <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Defects Amount</B></font></th>
			   <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Yield %</B></font></th>
         <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Lab</B></font></th>
         <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Account</B></font></th>
			   <th  align="CENTER" width="5%"><font size="'.$text.'"> <B>Amount</B></font></th>
			   </tr>';



	$SNO=1;
	$PREV_DATE='';


	while($RowPurchase=$StmtLotList->fetch_array()){

		$LOT_ID=$RowPurchase['id'];
    $BILL=$RowPurchase['BILL_NAME'];
    $AGENT_NAME = $RowPurchase['AGENT_NAME'];
    $LOT_NO=$RowPurchase['LOT_NO'];
    $PURCHASE_DATE=$RowPurchase['PURCHASE_DATE'];
    $GradedHonYield=$RowPurchase['YIELD'];
    $PURCHASE_DATE=date("d-m-Y",strtotime($PURCHASE_DATE));

    if($RowPurchase['STATUS']==1){
      $LotStatus='Return';
      $ReturnCount++;
    }else{
      $LotStatus='';
    }


/*GRADING HON*/
$GradedHonRate=$RowPurchase['RATE'];
$GradedHonWeight=$RowPurchase['WEIGHT'];
$GradedHonCount=$RowPurchase['COUNT'];
/*GRADING HON*/


/*Packed Hon*/
$PackedHonCount=$RowPurchase['HON_PACKED_COUNT'];
$PackedHonWeight=$RowPurchase['HON_WEIGHT'];
$PackedHonRate=$RowPurchase['HON_PACKED_RATE'];
/*Packed Hon*/

/*DEFECT HON*/
$DefectiveHonWeight=0;
$DefectiveHonPrice=0;

  $SqlDefects="SELECT * FROM `RM_DEFECTS_DETAILS` where LOT_ID='$LOT_ID'";
  $StmtDefects=$iconn->query($SqlDefects);
  while($RowDefects=$StmtDefects->fetch_array()){
    $DefectiveHonWeight+=$RowDefects['WEIGHT'];
    $DefectiveHonPrice+=($RowDefects['WEIGHT']*$RowDefects['RATE']);
  }
/*DEFECT HON*/

/*LOT TOTALS*/
    $EachLotPurchaseWeight=$GradedHonWeight +$PackedHonWeight +$DefectiveHonWeight;
    $TotalPurchaseWeight+=$EachLotPurchaseWeight;
/*LOT TOTALS*/


/*LAB & Account DEDUCTIONS */

//Minus Defective from $EachLotPurchaseWeight if needed

$SqlQcRmDetails="SELECT * from ORGN_INSP_REPORT where LOT_ID='$LOT_ID'";
$StmtQcRmDetails=$iconn->query($SqlQcRmDetails);
$RowQcRmDetails = $StmtQcRmDetails->fetch_assoc();

$LabDeduction= $RowQcRmDetails['DEDUCTIONS']*$EachLotPurchaseWeight;
$AccDeductions= $RowPurchase['DEDUCTIONS']*$EachLotPurchaseWeight;

$EachLotPurchaseAmt=$GradedHonWeight*$GradedHonRate; //Grading Hon Price
$EachLotPurchaseAmt+=$PackedHonWeight*$PackedHonRate;// Packing Hon Price
$EachLotPurchaseAmt+=$DefectiveHonPrice; //Defective Hon Price
$EachLotPurchaseAmt=$EachLotPurchaseAmt-$LabDeduction;
$EachLotPurchaseAmt=$EachLotPurchaseAmt+$AccDeductions;

$TotalPurchaseAmount+=$EachLotPurchaseAmt;



/*LAB DEDUCTIONS*/


/*GrandTotals*/

$AryGradingHon[]=$GradedHonWeight;
$AryPackingHon[]=$PackedHonWeight;
$AryDefectiveHonWeight[]=$DefectiveHonWeight;
$AryDefectiveHonPrice[]=$DefectiveHonPrice;
$AryLabDeductions[]=$LabDeduction;
$AryAccountsDeductions[]=$AccDeductions;
$AryTotalPurchaseAmount[]=$EachLotPurchaseAmt;

/*GrandTotals*/




		$content .= '
				<tr>
        <th  align="CENTER" width="3%"><font size="'.$text.'"><B>'.$SNO.'</B></font></th>
        <th  align="CENTER" width="7%"><font size="2"><B>'.$PURCHASE_DATE.'</B></font></th>
			  <th  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$LOT_NO.'</B></font></th>
				<th  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$BILL.'</B></font></th>
				<th  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$AGENT_NAME.'</B></font></th>
        <th  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$LotStatus.'</B></font></th>
				<th  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$EachLotPurchaseWeight.'</B></font></th>
				  ';
			 $SNO++;

  $AryGradesData=array();
  $AryEachLotSample=array();
  $EachLotGradingHlWeight=0;

      $SqlGrades="SELECT * FROM `PPC_FINAL_GRADING_HL` where LOT_ID='$LOT_ID' order by COUNT asc";
      $StmtGrades=$iconn->query($SqlGrades);
      while($RowGrades=$StmtGrades->fetch_array()){
      $AryGradesData[]=$RowGrades;
      }

$EachLotGradingHlWeight=0;
foreach($ARY_COSTING_RATES as $ROW_COSTING){
         $EachGradeWeight=0;
         $start=(int)$ROW_COSTING['LOWER_LIMIT'];
				 $end=(int)$ROW_COSTING['UPPER_LIMIT'];

		foreach($AryGradesData  as $ItemGrade){
			$count=round($ItemGrade['COUNT']);
			if($count > $start  && $count <= $end){
        $EachGradeWeight+=$ItemGrade['WEIGHT'];
        $EachLotGradingHlWeight+=$ItemGrade['WEIGHT'];
    }
		}

  $AryEachLotSample[]=$EachGradeWeight;

  if($EachGradeWeight==0){
    $content.='<th  align="CENTER" width="5%"><font size="'.$text.'"><B>-</B></font></th>';
  }else{
  $content.='<th  align="CENTER" width="5%"><font size="'.$text1.'">'. $EachGradeWeight.'</font></th>';
  }
	}

  $AryAllLotsSamples[]=$AryEachLotSample;
  $ARY_DAY_GRADES[]=$ary_lot_grade;





$content.='
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$EachLotGradingHlWeight.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$GradedHonWeight.'</B></font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$GradedHonCount.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$GradedHonRate.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$PackedHonWeight.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$PackedHonCount.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$PackedHonRate.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$DefectiveHonWeight.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$DefectiveHonPrice.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$GradedHonYield.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'"> -'.$LabDeduction.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$AccDeductions.'</font></th>
<th  align="RIGHT" width="5%"><font size="'.$text1.'">'.round($EachLotPurchaseAmt).'</font></th>
</tr>';

	}




	$content.='<tr>
<td  align="CENTER" width="5%"><font size="'.$text.'"><B>TOTAL</B></font></td>
<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>
<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>
<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>
<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>
<td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$ReturnCount.'</B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$TotalPurchaseWeight.'</B></font></td>';

$Index=0;
foreach($ARY_COSTING_RATES as $EachLot){
  $EachGradeHeadless=0;
foreach($AryAllLotsSamples as $Item){
	  $EachGradeHeadless+= $Item[$Index];
    $TotalHeadless+= $Item[$Index];
}
$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$EachGradeHeadless.'</B></font></td>';
$Index++;
}


$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$TotalHeadless.'</B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.array_sum($AryGradingHon).'</B></font></td>
<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>
<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.array_sum($AryPackingHon).'</B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>
            <td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>
            <td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.array_sum($AryDefectiveHonWeight).'</B></font></td>
            <td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.array_sum($AryDefectiveHonPrice).'</B></font></td>
            <td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>
            <td  align="CENTER" width="5%"><font size="'.$text.'"><B>-'.array_sum($AryLabDeductions).'</B></font></td>
            <td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.array_sum($AryAccountsDeductions).'</B></font></td>';

$content.='<td  align="RIGHT" width="5%"><font size="'.$text.'"><B>'.round(array_sum($AryTotalPurchaseAmount),2).'</B></font></td></tr>';
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
