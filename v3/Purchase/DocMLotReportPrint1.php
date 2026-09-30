<?php

session_start();

include "includes/config.php";

include "includes/Globals.php";

  //$date=$_GET['date'];
$from_date=$_GET['from_date'];
  $to_date=$_GET['to_date'];

  $FROM_DATE=$_GET['from_date'];
$TO_DATE=$_GET['to_date'] .' 23:59:59';


 //$month_name= date("F", strtotime($date));
 $month_name= date("F", strtotime($from_date));

 $datediff = $to_date - $from_date   ;

 $diff = abs(strtotime($to_date) - strtotime($from_date));
 $years = floor($diff / (365*60*60*24));
  $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
  
  $content1 .= '<h3 align="center">JAGADEESH MARINE EXPORTS</h3>
			  			  <h3 align="center"> Grading Report-'.$month_name.'</h3>';
echo $content1;
/*
require_once('tcpdf/tcpdf.php');

class MYPDF extends TCPDF {

	public function Footer() {



		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);

		$this->SetY(-55);

   }



    public function Header() {



        $image_file = K_PATH_IMAGES.'jme_logo.jpg';

        $this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);

        $this->SetFont('helvetica', 'B', 15);

		$this->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		$this->Cell(0, 5, 'SURYAMITRA EXIM PVT.LTD', 0,1, 'C', 0, '', 0, false, 'T', 'M');



    }


}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);



	$pdf->SetCreator(PDF_CREATOR);

    $pdf->SetTitle('GRADING Report : ');

    $pdf->SetHeaderData('', '', '', '');

    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));

    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    $pdf->SetDefaultMonospacedFont('helvetica');

    $pdf->SetFooterMargin(15);

	$pdf->SetHeaderMargin(15);

    $pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT,15);

    $pdf->setPrintHeader(false);

    $pdf->setPrintFooter(false);



    $pdf->SetAutoPageBreak(TRUE,15);

    $pdf->SetFont('helvetica', '', 11);

    $pdf->AddPage('l');




		$pdf->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		$pdf->Cell(0, 5, 'JAGADEESH MARINE EXPORTS', 0,1, 'C', 0, '', 0, false, 'T', 'M');

				    $pdf->SetFont('helvetica', 'B', '11');

		$pdf->Cell(0, 5, 'Grading Report - '.$month_name, 0, 1, 'C', 0, 'B', 0, false, 'T', 'M');
					//$pdf->Cell('', '', $month_name, 0, 1, '');

		//$pdf->Cell(0, 5,''.$month_name, 0, 1, 'L', 0, '', 0, false, '', '');


*/

   $aval_hl_reports_sql = "select * from  inputmaterial_details AS
	IM left join  PPC_GRADING_HL as HW ON HW.LOT_ID=IM.id WHERE IM.SAMPLE_TYPE=''
	AND IM.PURCHASE_DATE BETWEEN '$FROM_DATE' and '$TO_DATE'  order by
	IM.PURCHASE_DATE,IM.LOT_NO ASC";
	$stmt_hl=$iconn->query($aval_hl_reports_sql);

	 $BILLED_LOTS=$stmt_hl->num_rows;

	$table=	getGrading($iconn,$EXCHANGE_RATE,$stmt_hl,$BILLED_LOTS,$Expanse,$DEPB);


$AvgData=getAvgDetails($stmt_hl);

/*$pdf->writeHTML($AvgData);

$pdf->writeHTML($table);
*/
	  echo $AvgData;
	  echo'<br><br>';
	  echo $table;

$file_name='Grading Report -'.$from_date.'-'.$to_date.'.pdf';


if($days > 7){
$pdf->Output($file_name, 'D');
}else{
$pdf->Output($file_name, 'I');
}

function getGrading($iconn,$EXCHANGE_RATE,$hl_stmt,$BILLED_LOTS,$Expanse,$DEPB){

include "../includes/Globals.php";

	$ARY_COSTING_RATES=ARRAY();
		$SQL_COSTING="SELECT * FROM ACC_COSTING_RATES WHERE TARCK_ID= (SELECT MAX(TARCK_ID) from ACC_COSTING_RATES)";
		$STMT_SQL_COSTING=$iconn->query($SQL_COSTING);

		while($ROW_COSTING = $STMT_SQL_COSTING->fetch_assoc()){
			$ARY_COSTING_RATES[]=$ROW_COSTING;
		}

		$ARY_DAY_GRADES=array();


	$text=6;
	$text1=6;
	$width=100/30;

	$exchange_rate=$EXCHANGE_RATE;


	$content .= '<table border="1" cellspacing="0" cellpadding="2">

			  <tr>

                <th  align="CENTER" width="'.$width.'%"> <B>GRADING MACHINE</B></th>
                <th  align="CENTER" width="'.$width.'%"> <B>ID.NO</B></th>
                <th  align="CENTER" width="'.$width.'%"> <B>GRADER NAME</B></th>
                <th  align="CENTER" width="'.$width.'%"> <B>AGENT NAME</B></th>
                <th  align="CENTER" width="'.$width.'%"> <B>H.ON C </B></th>
                <th  align="CENTER" width="'.$width.'%"> <B>H.ON QTY </B></th>
                <th  align="CENTER" width="'.$width.'%"><B>AVG C</B></th>
                <th  align="CENTER" width="'.$width.'%"> <B>HL C/KG </B></th>
                <th  align="CENTER" width="'.$width.'%"><B>HL QTY </B></th>
                <th  align="CENTER" width="'.$width.'%"> <B>HL YIELD% </B></th>
                <th  align="CENTER" width="'.$width.'%"><B>S YIELD </B></th>
                <th  align="CENTER" width="'.$width.'%"><B>YIELD DIFF </B></th>';

				foreach($ARY_COSTING_RATES as $ROW_COSTING){
               $content .= ' <th  align="CENTER" width="'.$width.'%"> <B>'.$ROW_COSTING['COUNT'].'</B></th>';

				}

			   $content .= '
			   <th  align="CENTER" width="'.$width.'%"> <B>MIX</B></th>
			   <th  align="CENTER" width="'.$width.'%"><B>Grading Total</B></th>
			   <th  align="CENTER" width="'.$width.'%"> <B>Grading Yield</B></th>
			   <th  align="CENTER" width="'.$width.'%"> <B>VARIATION HL QTY. TO GRADING QTY. </B></th>
			   <th  align="CENTER" width="'.$width.'%"> <B> REMARKS</B></th>
			   </tr>



                ';



	$SNO=1;
	$PREV_DATE='';


$i=0;
$j=0;




	$H_ON=0;
	$TOTAL_HO_COUNT=0;
	$avg_count=0;



		while($r_varity_list=$hl_stmt->fetch_array()){

	 $HlWeight=0;
	 $HlCountPerKg=0;
	 $AryHlCount=array();
	 $RmWeighTotal+=$r_varity_list['WEIGHT_IN_KGS'];


	 $lot_id=$r_varity_list['id'];
	 $RmWeight= $r_varity_list['WEIGHT_IN_KGS'];
	 $json=($r_varity_list['HOSO_W_DATA']);

	 $H_ON_COUNT=COUNT($r_varity_list['COUNT']);
	 $H_ON=$r_varity_list['COUNT'];
	$avg_count=$H_ON/$H_ON_COUNT;



	 $data_hl=json_decode($json,true);




	 foreach($data_hl as $value){
		 $HlWeight+=$value['NetWeight'];
		 $HlWeightTotal+=$value['NetWeight'];
		 if(!in_array($value['Count'],$AryHlCount,true)){
			$AryHlCount[]= $value['Count'];
		 }

	 }

	 foreach($AryHlCount as $Count){
		$HlCountPerKg+= ($Count * 2.205);
	 }

	 $std_yield=getStandardYeild($iconn,$r_varity_list['COUNT'],'HL');

	 $AvgHlCountPerKg= round($HlCountPerKg/count($AryHlCount),2);
	 $ActualYield=  round(($HlWeight/$RmWeight)*100 ,2) .' %';

	 $YieldDiff=round($ActualYield-$std_yield,2);






	  $content .= '
				<tr>

                <th  align="CENTER" width="'.$width.'%"><B>'.$SNO.'</B></th>
                <th  align="CENTER" width="'.$width.'%"><B>'.$r_varity_list['LOT_NO'].'</B></th>
                <th  align="CENTER" width="'.$width.'%"><B>'.$r_varity_list['SUPERVISOR'].'</B></th>
                <th  align="CENTER" width="'.$width.'%"><B>'.$r_varity_list['AGENT_NAME'].'</B></th>



            <th  align="CENTER" width="'.$width.'%"><B>'.$r_varity_list['COUNT'].'</B></th>
                <th  align="CENTER" width="'.$width.'%"><B>'.$r_varity_list['WEIGHT_IN_KGS'].'</B></th>
                <th  align="CENTER" width="'.$width.'%"><B>'.$avg_count.'</B></th>';



$content .= '<th  align="CENTER" width="'.$width.'%"><B>'.$AvgHlCountPerKg.'</B></th>
                <th  align="CENTER" width="'.$width.'%"><B>'.$HlWeight.'</B></th>
                <th  align="CENTER" width="'.$width.'%"><B>'.$ActualYield.'</B></th>';





    $content .= '       <th  align="CENTER" width="'.$width.'%"><B>'.$std_yield.'</B></th>
                <th  align="CENTER" width="'.$width.'%"><B>'.$YieldDiff.'</B></th>';






	$PURCHASE_DATEE=date("d-m-Y",strtotime($PURCHASE_DATE));


			$LOT_NO=$r_varity_list['LOT_NO'];
			$RATE=$r_varity_list['RATE'];
			$exchange_rate	=$r_varity_list['EXCHANGE_RATE'];
			$Expance_kg	=$Expanse;
			$DEPB=$DEPB;

			//$PURCHASE_AMOUNT=($RATE*$r_varity_list['HON_QTY']) ;
			$PURCHASE_WEIGHT=$PURCHASE_WEIGHT +$r_varity_list['HON_QTY'] ;
			$DAY_PUR_TOT+=$PURCHASE_AMOUNT;

			$data = $r_varity_list['HOSO_W_DATA'];
			$json_decode_data=json_decode($data,true);
			$ary_counts=array();
			$ary_samples=array();

			foreach($json_decode_data  as $value){
				$ary_samples[]=$value;  // Colecting all weightments as single arrary
				if(!in_array($value['Count'],$ary_counts)){
				$ary_counts[]= $value['Count'];
				}
			}

			sort($ary_counts);

		//print_r($ary_samples);


			$HL_LOT_WEIGHT=0;

			$ary_lot_grade=array();

foreach($ARY_COSTING_RATES as $ROW_COSTING){

				 $GRADE_SLAB;
				 $weight_sum=0;
				 $range=explode("/",$ROW_COSTING['COUNT']);
				 $start=(int) $range[0];
				 $end=(int) $range[1];

				 $aval=false;
				 $weight_sum=0;

		foreach($ary_counts  as $countt){
				$count=(int) $countt;
			if($count >= $start  && $count <=$end){
			$aval=true;

			foreach($ary_samples  as $value){
			if($value['Count'] == $countt){
				 $weight_sum=$weight_sum + $value['NetWeight'];
			}
			}
			}


		}

		if($aval){

		 $content.='

		 <th  align="CENTER" width="'.$width.'%">'. $weight_sum.'</th>
		';
		}else{
			$content.='<th  align="CENTER" width="'.$width.'%"><B>--</B></th>';
		}

		$sample['GRADE']=$ROW_COSTING['COUNT'];
		$sample['WEIGHT']=$weight_sum;
		$ary_lot_grade[]=$sample;
		$HL_LOT_WEIGHT+=$weight_sum;

	}

$ARY_DAY_GRADES[]=$ary_lot_grade;

//print_r($ary_lot_grade);



$content.='
<th  align="CENTER" width="'.$width.'%"></th>
<th  align="CENTER" width="'.$width.'%"><B>'.$HlWeight.'</B></th>
<th  align="CENTER" width="'.$width.'%"><B>'.$ActualYield.'</B></th>
<th  align="CENTER" width="'.$width.'%"><B>0</B></th>

<th  align="CENTER" width="'.$width.'%"></th>
</tr>';//uma




	$SNO++;


}
$j++;




	////uma


	$content.='<tr>
<td  align="CENTER" width="'.$width.'%"><B>TOTAL</B></td>';
$content.='<td  align="CENTER" width="'.$width.'%"><B></B></td>';
$content.='<td  align="CENTER" width="'.$width.'%"><B></B></td>';
$content.='<td  align="CENTER" width="'.$width.'%"></td>';
$content.='<td  align="CENTER" width="'.$width.'%"></td>';
$content.='<td  align="CENTER" width="'.$width.'%"><B>'.$RmWeighTotal.'</B></td>';
$content.='<td  align="CENTER" width="'.$width.'%"></td>';
$content.='<td  align="CENTER" width="'.$width.'%"></td>';
$content.='<td  align="CENTER" width="'.$width.'%"><B></B>'.$HlWeightTotal.'</td>';
$content.='<td  align="CENTER" width="'.$width.'%"></td>';
$content.='<td  align="CENTER" width="'.$width.'%"></td>';
$content.='<td  align="CENTER" width="'.$width.'%"></td>';



  //uma costing rates
  foreach($ARY_COSTING_RATES as $ROW_COSTING){
	$weight=0;

foreach($ARY_DAY_GRADES as $ROW_GRADES){
foreach($ROW_GRADES as $ROW_GRADE){
	//echo '=='.$ROW_GRADE[0]['GRADE'];
		//print_r($ROW_GRADE);

	if($ROW_GRADE['GRADE'] == $ROW_COSTING['COUNT']){
	//ECHO 'M'.$ROW_COSTING['COUNT'];
		$weight+=$ROW_GRADE['WEIGHT'];
		$T_HL+=$ROW_GRADE['WEIGHT'];
	}
}
}
$content.='<td  align="CENTER" width="'.$width.'%"><B>'.$weight.'</B></td>';

}
$ARY_DAY_GRADES=array();
$content.='<td  align="CENTER" width="'.$width.'%"></td>';
$content.='<td  align="CENTER" width="'.$width.'%"><B>'.$HlWeightTotal.'</B></td>';
$content.='
<td  align="CENTER" width="'.$width.'%"></td>
<td  align="CENTER" width="'.$width.'%"></td>
<td  align="CENTER" width="'.$width.'%"></td>
</tr>';
$DAY_PUR_TOT=0;
$T_HL=0;



//uma
		  $content.='

				</table>';


	return $content;


	}

function getAvgDetails($stmt_hl){
	$Count=mysqli_num_rows($stmt_hl);
	$Data=$stmt_hl->fetch_array();

	//print_r($Data);

	 //$TotalLots=$Data['TotalLots']-1;  
	 $TotalLots=$Count;  
	$TotalWeight=$Data['TotalWeight'];
	$TotalHlWeight=$Data['TotalHlWeight'];
	$TotalDefWeight=$Data['TotalDefWeight'];
	$AvgCount=($Data['AvgCount']/$TotalLots);
	$AvgYield=($Data['AvgYield']/$TotalLots);
	$PerHourQuantity=($Data['PerHourQuantity']/24);



	 $width=120/6;

	 $content = '<table border="1" cellspacing="0" cellpadding="3">

			   <tr>
			   <th  align="center"  width="'.$width.'%">Total Lots</th>
				<th  align="center" width="'.$width.'%" >RM Weight</th>
				<th  align="center" width="'.$width.'%" >HL Weight</th>
				<th  align="center" width="'.$width.'%" >Defective Weight</th>
				<th  align="center"  width="'.$width.'%" >Avg Count</th>
                <th  align="center"  width="'.$width.'%" >Avg Yield</th>

				</tr>


			    <tr>



                <th  align="center"  width="'.$width.'%">'.($TotalLots).'</th>
				 <th  align="center" width="'.$width.'%" >'.$TotalWeight.'</th>
				 <th  align="center" width="'.$width.'%" >'.$TotalHlWeight.'</th>
				 <th  align="center" width="'.$width.'%" >'.$TotalDefWeight.'</th>
				 <th  align="center" width="'.$width.'%" >'.round($AvgCount).'</th>
				 <th  align="center" width="'.$width.'%" >'.round($AvgYield).'</th>




               </tr></table>

			  ';

			  return $content;
}
?>
