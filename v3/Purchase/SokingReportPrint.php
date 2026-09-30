<?php

session_start();

include "../includes/config.php";

include "../includes/Globals.php";

  //$date=$_GET['date'];
$from_date=$_GET['from_date'];
  $to_date=$_GET['to_date'];


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

		$this->Cell(0, 5, 'JAGADEESH MARINE EXPORTS', 0,1, 'C', 0, '', 0, false, 'T', 'M');



    }


}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);



	$pdf->SetCreator(PDF_CREATOR);

    $pdf->SetTitle('SOAKING Report : ');

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
		$pdf->Cell(0, 5, 'SOACKING Report - '. getFormatedDate($from_date), 0, 1, 'C', 0, 'B', 0, false, 'T', 'M');

    $pdf->SetFont('helvetica', '', 11);


	// $table_data=getP_TABLE($iconn,$date);
	 $table_data=getP_TABLE($iconn,$from_date,$to_date);


	$pdf->writeHTML($table_data);


$file_name='SOACKING Report -'.$from_date.'-'.$to_date.'.pdf';

$pdf->Output($file_name, 'I');


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$from_date,$to_date){
	/* $date_end=$date.'-31 23:59:59';
	 $date=$date.'-01'; */

	//$date_end=$date.' 23:59:59';
	$date_end=$to_date.' 23:59:59';


				/*<?php		$mon=$_POST['month']?>*/


	 $sql_lots="SELECT DISTINCT
	imd.REFERENCE_ID,
	imd.LOT_NO,
	imd.PURCHASE_DATE,
	imd.COUNT,
	SCK.T_NO,
	SCK.SoakingDate,
	SCK.LOT_ID,
	SCK.Variety,
	SCK.QTY,
	SCK.C1,
	SCK.C2,
	SCK.APP_QT,
	SCK.DEST,
	SCK.TimePeriod,
	SCK.CreatedDateTime from inputmaterial_details as
	imd left join soaking_report as SCK on imd.id=SCK.LOT_ID where
	`SoakingDate` BETWEEN '$from_date' AND '$date_end'
	order by SoakingDate asc,LOT_NO asc,Variety asc"; 



			  $stmt_lots=$iconn->query($sql_lots);




	$content='';


	$size=40;

	$size1=60;





			  $S_NO=1;
			  $width=120/15;
			  $width_s=$width-5;
			  $width_w=$width+5;


			  $content .= '<table border="1" cellspacing="0" cellpadding="3">
			   <tr>



                <th  align="center"  width="'.$width_s.'%"><font size="9"><B>SI No.</B></font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9"><B> TUB NO </B></font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9"><B> ID NO </B></font></th>
				   <th  align="center"  width="'.$width.'%"><font size="9"><B>RM DATE</B></font></th>
				   <th  align="center"  width="'.$width.'%"><font size="9"><B>Soaking DATE </B></font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9"><B>VARIETY</B></font></th>
                <th  align="center"  width="'.$width.'%" ><font size="9"><B>GRADE </B></font></th>

                <th  align="center"  width="'.$width.'%" ><font size="9"><B>IN QUANTITY </B></font></th>
                <th  align="center" width="'.$width.'%"><font size="9"><B>IN COUNT </B></font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9"><B> TIME PERIOD</B></font></th>
				<th  align="center" width="'.$width.'%" ><font size="9"><B>OUT COUNT</B></font></th>
				<th  align="center" width="'.$width.'%" ><font size="9"><B>DEST</B></font></th>
				<th  align="center" width="'.$width.'%" ><font size="9"><B>OUT QUANTITY </B></font></th>


               </tr>

			  ';


		//
		while($r_lots = $stmt_lots->fetch_array()){


		 $PURCHASE_DATE=date("d-m-Y", strtotime($r_lots['PURCHASE_DATE']));
	 $CreatedDateTime=date("d-m-Y", strtotime($r_lots['CreatedDateTime']));
if($CreatedDateTime=='01-01-1970'){
	
	$CreatedDateTime='Not Updated';
}
				    $content .=

					'<tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">'.$S_NO.'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['T_NO'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['LOT_NO'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$PURCHASE_DATE.'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$CreatedDateTime.'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">'.$r_lots['Variety'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">'.$r_lots['COUNT'].'</font></th>

				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['QTY'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['C1'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['TimePeriod'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['C2'].' </font></th>

				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['DEST'].' </font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$r_lots['APP_QT'].' </font></th>

				 </tr>';

 $S_NO++;
}



		$content .= '</table>';


	return $content;

}

?>
