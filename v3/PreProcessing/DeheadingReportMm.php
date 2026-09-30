<?php

session_start();

include "../includes/config.php";

include "../includes/Globals.php";

  //$date=$_GET['date'];
$from_date=$_GET['from_date'];
  $to_date=$_GET['to_date'];
  $UNIT=$_GET['U'];


 //$month_name= date("F", strtotime($date));
 $month_name= date("F", strtotime($from_date));

require_once('../tcpdf/tcpdf.php');

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

    $pdf->SetTitle('Deheading Addition Report : ');

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
    $pdf->setFontSubsetting(false);

    $pdf->AddPage('l');




		$pdf->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		$pdf->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

				    $pdf->SetFont('helvetica', 'B', '11');

		$pdf->Cell(0, 15, 'DE HEADING REPORT - '. getFormatedDate($from_date), 0, 1, 'C', 0, 'B', 0, false, 'T', 'M');
					//$pdf->Cell('', '', $month_name, 0, 1, '');

		//$pdf->Cell(0, 5,''.$month_name, 0, 1, 'L', 0, '', 0, false, '', '');



    $pdf->SetFont('helvetica', '', 11);


	// $table_data=getP_TABLE($iconn,$date);

$UNIT_NAME='';
  if($UNIT ==0){

  $SqlProStations="SELECT * FROM `RM_PROCESSING_STATIONS`";
  $StmtProStations=$iconn->query($SqlProStations);

  	while($Row=$StmtProStations->fetch_array()){
      $UNIT=$Row['ID'];
      $UNIT_NAME=$Row['UNIT_NAME'];
	  
      $table_data=getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME);
	
	  if($table_data['TOTAL_SENT_WEIGHT']!=0){
      $pdf->writeHTML($table_data['TABLE']);
	  }
    }
  }else{
    $SqlProStations="SELECT * FROM `RM_PROCESSING_STATIONS` where ID='$UNIT' ";
    $StmtProStations=$iconn->query($SqlProStations);
	$Row=$StmtProStations->fetch_array();
	$UNIT_NAME=$Row['UNIT_NAME'];
   $table_data=getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME);
   $pdf->writeHTML($table_data['TABLE']);
  }





$file_name='DE HEADING REPORT -'.$from_date.'-'.$to_date.'.pdf';

$pdf->Output($file_name, 'I');


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$from_date,$to_date,$UNIT_NAME){

global $UNIT;

if($UNIT !=0){
  $Query="and PS.ID='$UNIT'";
}else{
    $Query="";
}
	$date_end=$to_date.' 23:59:59';

	$query.="and imd.id='0'";
	 $sql_lots="SELECT
	  imd.LOT_NO,
	  imd.ANTI_BIOTIC_STATUS,
	  imd.PURCHASE_DATE,
	  imd.WEIGHT_IN_KGS,
	  VA.COUNT,
	  VA.PROCESSING_DATE,
	  VA.SENT_WEIGHT,
	  VA.WgtAfterValueAddition,
	  VA.HL_QTY_RCVD,
	  VA.RETURN_WEIGHT,
	  VA.VARIETY ,
	  VA.SHIFT ,
	  VA.REMARKS ,
	  PS.UNIT_NAME
	  from inputmaterial_details as imd
			 left join PPC_VALUE_ADDITION as VA on imd.id=VA.LOT_ID
			 left join RM_PROCESSING_STATIONS as PS on VA.STATION_ID =PS.ID
			  where VA.PROCESSING_DATE BETWEEN '$from_date' AND '$date_end'
			  $query
			  and VA.VARIETY ='DeHeading' $Query
			  order by PURCHASE_DATE ASC,LOT_NO ASC,VARIETY ASC";
			  $stmt_lots=$iconn->query($sql_lots);
			  $r_lots = $stmt_lots->fetch_array();
			  $stmt_lots=$iconn->query($sql_lots);

	$content='';


	$size=40;

	$size1=60;



if($UNIT_NAME==''){
   $UNIT_NAME= $r_lots['UNIT_NAME'];
}

			  $S_NO=1;
			  $width=95.5/10;
			  $width_s=$width-5;
			  $width_w=$width+5;


			  $content .= '<table border="1" cellspacing="0" cellpadding="3">
			   <tr>

			    <th  align="center" width="100%"><font size="9"><B>'.$UNIT_NAME.' </B></font></th>


				</tr>

				<tr>

                <th  align="center"  width="'.$width_s.'%"><font size="9"><B>S No.</B></font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9"><B>LOT NO </B></font></th>

                <th  align="center"  width="'.$width.'%" ><font size="9"><B>RM Date </B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="9"><B>De Heading Date </B></font></th>





				<th  align="center" width="'.$width.'%" ><font size="9"><B>HON Weight</B></font></th>
				<th  align="center" width="'.$width.'%" ><font size="9"><B>HON Count</B></font></th>
				<th  align="center" width="'.$width.'%" ><font size="9"><B>HON Qty Recieved</B></font></th>
				<th  align="center" width="'.$width.'%" ><font size="9"><B>HON Return </B></font></th>
				<th  align="center" width="'.$width.'%" ><font size="9"><B>HL Qty </B></font></th>
				<th  align="center" width="'.$width.'%"><font size="9"><B>YEILD </B></font></th>
				<th  align="center" width="'.$width.'%"><font size="9"><B>Remarks </B></font></th>



               </tr>

			  ';

$TOTAL_SENT_WEIGHT=0;
		//
		while($r_lots = $stmt_lots->fetch_array()){




$SENT_WEIGHT=$r_lots['SENT_WEIGHT'];
$WgtAfterValueAddition=$r_lots['WgtAfterValueAddition'];
$RECEIVED_WEIGHT=$r_lots['HL_QTY_RCVD'];
$RETURN_WEIGHT=$r_lots['RETURN_WEIGHT'];

$TOTAL_SENT_WEIGHT+=$SENT_WEIGHT;

	$yield=round(($WgtAfterValueAddition/($RECEIVED_WEIGHT-$RETURN_WEIGHT))*100,2);

	$yield1=$yield.'%';
	$yield2=trim(str_replace("()", "",$yield1));

	 $PURCHASE_DATE=date("d-m-Y", strtotime($r_lots['PURCHASE_DATE']));
	 $PROCESSING_DATE=date("d-m-Y", strtotime($r_lots['PROCESSING_DATE']));
	$r_lots['ANTI_BIOTIC_STATUS'];


		$COUNT=$r_lots['EXP_COUNT'];

		//$STANDARD_YIELD=getStandardYeild($iconn,$COUNT);

		if($yield1 < $STANDARD_YIELD ){
			$COLOR='red';
		}else{
			$COLOR='GREEN';
		}





		$RETURN_WEIGHT= isset($r_lots['RETURN_WEIGHT']) ? $r_lots['RETURN_WEIGHT'] : "-";
				    $content .=

					'<tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">'.$S_NO.'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['LOT_NO'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$PURCHASE_DATE.'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$PROCESSING_DATE.'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['SENT_WEIGHT'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['COUNT'].'</font></th>



				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['HL_QTY_RCVD'].'</font></th>
				  <th  align="center" width="'.$width.'%"><font size="9">'.$RETURN_WEIGHT .' </font></th>
				  <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['WgtAfterValueAddition'].'</font></th>

				 <th style="color:'.$COLOR .'" align="center" width="'.$width.'%"><font size="9">'.$yield1.' </font></th>

				  <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['REMARKS'].'</font></th>

				 </tr>';

 $S_NO++;
}

$content .=

'<tr>

<th style="color:'.$COLOR .'" align="center" width="50%"><font size="9">Total </font></th>

<th  align="center" width="50%"><font size="9"><B>'. $TOTAL_SENT_WEIGHT.'</B></font></th>

</tr>';



		$content .= '</table>';

		
		

		$Data['TOTAL_SENT_WEIGHT']=$TOTAL_SENT_WEIGHT;
		$Data['TABLE']=$content;
		
	return $Data;

}

?>
