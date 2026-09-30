<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

 $from_date=$_POST['from_date'];
  $to_date=$_POST['to_date'].' 23:59:59';

   if(isset($_POST['U'])){
	  $UNIT=$_POST['U'];

	  $Query.="and UNIT='$UNIT'";
  }else{
	 //  $Query="";
  }

  if(isset($_POST['PU'])){
	  $PU=$_POST['PU'];

	  $Query.="and UNIT_ID='$PU'";
  }else{
	  // $Query="";
  }


//$M=$_GET['M'];
//$Y=$_GET['Y'];

//$FROM_DATE=$Y.'-'.$M.'-01';
//$TO_DATE=$Y.'-'.$M.'-30 23:59:59';

//$UNIT=$_GET['U'];
global $Query;
$EXCHANGE_RATE=$_POST['EXCHANGE_RATE'];
$Expanse=$_POST['Expanse'];
$DEPB=$_POST['DEPB'];



  echo  $aval_hl_reports_sql = "
	select
IM.id,
IM.LOT_NO,
IM.PURCHASE_DATE,
IM.COUNT as F_COUNT,
IM.WEIGHT_IN_KGS ,
HW.SUBMISSION_DATE as GRADING_DATE,
HW.HOSO_W_DATA,
RBD.SUBMISSION_DATE as BILL_DATE,
RBD.WEIGHT as BILL_WEIGHT,
RBD.COUNT as BILL_COUNT,
RBD.YIELD


   from PPC_GRADING_HL as HW
	left join inputmaterial_details AS IM ON HW.LOT_ID=IM.id
	left join RM_BILL_DETAILS AS RBD ON RBD.LOT_ID=id

	WHERE HW.DATA_TYPE='Headless Grading'
	AND IM.SAMPLE_TYPE=''
	AND IM.PURCHASE_DATE BETWEEN '$from_date' AND
	'$to_date' $Query
	 order by
	IM.PURCHASE_DATE,IM.LOT_NO ASC";

	$stmt_hl=$iconn->query($aval_hl_reports_sql);



	$BILLED_LOTS=$stmt_hl->num_rows;
	$table=	getCostingTable($iconn,$EXCHANGE_RATE,$stmt_hl,$BILLED_LOTS,$Expanse,$DEPB);


	//echo 'Ungraded Lots -'.$UN_GRADED_LOTS.' / Total Lots- '.$TOTAL_LOTS;
	echo $table;



	function getCostingTable($iconn,$EXCHANGE_RATE,$hl_stmt,$BILLED_LOTS,$Expanse,$DEPB){

		$ARY_COSTING_RATES=ARRAY();
		 $SQL_COSTING="SELECT * FROM ACC_COSTING_RATES WHERE TARCK_ID= (SELECT MAX(TARCK_ID) from ACC_COSTING_RATES)";
		$STMT_SQL_COSTING=$iconn->query($SQL_COSTING);

		while($ROW_COSTING = $STMT_SQL_COSTING->fetch_assoc()){
			$ARY_COSTING_RATES[]=$ROW_COSTING;
		}

		$ARY_DAY_GRADES=array();


		$PO_TMSP = strtotime($date);
$PURCHASE_DATE = date("d-m-Y", $PO_TMSP);


 $from_dt=date('d-m-Y', strtotime($from_date));
 $to_dt=date('d-m-Y', strtotime($to_date));


$PO_TMSP = strtotime($date);
$PURCHASE_DATE = date("d-m-Y", $PO_TMSP);

 $from_dt=date('d-m-Y', strtotime($from_date));
 $to_dt=date('d-m-Y', strtotime($to_date));
	$text=4;
	$text1=2;

	$exchange_rate=$EXCHANGE_RATE;


	$content .= '<table border="1" cellspacing="0" cellpadding="1">

			  <tr>

                <th  align="CENTER" style="width: 5%;" ><font size="'.$text.'"> <B>S No</B></font></th>
                <th  align="CENTER" style="width: 10%;"><font size="'.$text.'"> <B>Date</B></font></th>
                <th  align="CENTER" style="width: 10%;"><font size="'.$text.'"> <B>Lot Number</B></font></th>
                <th  align="CENTER" style="width: 5%;"><font size="'.$text.'"> <B>HO Count</B></font></th>
                <th  align="CENTER" style="width: 10%;"><font size="'.$text.'"> <B>HO Weight</B></font></th>
                <th  align="CENTER" style="width: 10%;"><font size="'.$text.'"> <B>Grading Date</B></font></th>
                <th  align="CENTER" style="width: 30%;"><font size="'.$text.'"> <B>Grading Details</B></font></th>
                <th  align="CENTER" style="width: 10%;"><font size="'.$text.'"> <B>HL Weight</B></font></th>
                <th  align="CENTER" style="width: 10%;"><font size="'.$text.'"> <B>Yield%</B></font></th>


       			   </tr>';







	$SNO=1;
	$PREV_DATE='';


	while($r_varity_list=$hl_stmt->fetch_array()){


		$LOT_ID=$r_varity_list['LOT_ID'];
    $F_COUNT=$r_varity_list['F_COUNT'];
    $PURCHASE_WEIGHT=ROUND($r_varity_list['WEIGHT_IN_KGS'],2);
    $BILL_COUNT=$r_varity_list['BILL_COUNT'];
    $BILL_WEIGHT=$r_varity_list['BILL_WEIGHT'];
    $GRADING_DATE=$r_varity_list['GRADING_DATE'];
    $WEIGHT=$r_varity_list['YIELD'];
		$LOT_NO=$r_varity_list['LOT_NO'];
		$PURCHASE_DATE=$r_varity_list['PURCHASE_DATE'];
    $YIELD=$r_varity_list['YIELD'];



	//umask
	$PURCHASE_DATE=date("d-m-Y",strtotime($PURCHASE_DATE));
  	$GRADING_DATE=date("d-m-Y",strtotime($GRADING_DATE));

	/* */

		$content .= '
				<tr>

        <th  align="CENTER" style="width: 5%;"><font size="'.$text.'"><B>'.$SNO.'</B></font></th>
        <th  align="CENTER" style="width: 5%;"><font size="2"><B>'.$PURCHASE_DATE.'</B></font></th>
        <th  align="CENTER" style="width: 5%;"><font size="'.$text.'"><B>'.$LOT_NO.'</B></font></th>';
			 $SNO++;


			$data = $r_varity_list['HOSO_W_DATA'];
			$json_decode_data=json_decode($data,true);
			$ary_counts=array();
			$ary_samples=array();

			foreach($json_decode_data  as $value){


				$ary_samples[]=$value;  // Colecting all weightments as single arrary
				if(!in_array($value['Count'],$ary_counts,true)){
				$ary_counts[]= $value['Count'];
				}
			}

			sort($ary_counts);
$weight_sum=0;
$each_lot_hl_weight=0;



$each_lot='<table border="1" cellspacing="0" cellpadding="1">

      ';

$countRow='<th  align="CENTER"><font size="'.$text1.'">Count</th>';
$WeightRow='<th  align="CENTER"><font size="'.$text1.'">Weight</th>';
$each_lot_hl_weight=0;
foreach ($ary_counts as $key => $count) {

$NetWeight=0;


  foreach($ary_samples  as $value){
  if($value['Count'] == $count){
     $NetWeight=$NetWeight + $value['NetWeight'];


  }
  }
  $T_HL+=$NetWeight;
   $each_lot_hl_weight+=$NetWeight;
    $countRow.='<th  align="LEFT" width="5%"><font size="'.$text1.'">'.$count.'</font></th>';
      $WeightRow.='<th  align="LEFT" width="5%"><font size="'.$text1.'">'. $NetWeight.'</font></th>';



}
  $each_lot.='<tr>'.$countRow.'</tr>';
  $each_lot.='<tr>'.$WeightRow.'</tr>';

$each_lot.='</table> ';





$content.='
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$BILL_COUNT.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'.$BILL_WEIGHT.'</font></th>

<th  align="CENTER" width="5%"><font size="'.$text1.'">'. $GRADING_DATE.'</font></th>
<th  align="LEFT" style="width: 50%;"><font size="'.$text1.'">'. $each_lot.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'. $each_lot_hl_weight.'</font></th>
<th  align="CENTER" width="5%"><font size="'.$text1.'">'. $YIELD.'</font></th>

';
$content.='</tr>';





	}


	$content.='<tr>

<td  align="CENTER" width="5%"><font size="'.$text.'"><B>TOTAL</B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B></B></font></td>';

$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$T_HL.'</B></font></td>';

$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$T_HON.'</B></font></td>';
$content.='<td  align="CENTER" width="5%"><font size="'.$text.'"><B>'.$DEFECT_WGT.'</B></font></td>';

$content.='<td  align="RIGHT" width="5%"><font size="'.$text.'"><B>'.round($DAY_PUR_TOT).'</B></font></td></tr>';
$DAY_PUR_TOT=0;
$T_HL=0;

		  $content.='

				</table>';


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
