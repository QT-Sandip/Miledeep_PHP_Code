<?php

session_start();

include "../includes/config.php";

include "../includes/Globals.php";

  //$date=$_GET['date'];
$from_date=$_GET['from_date'];
  $to_date=$_GET['to_date'];


 //$month_name= date("F", strtotime($date));
 $month_name= date("F", strtotime($from_date));
 $content1 .= '
			  			  <h3 align="center"> Soaking Report-('.getFormatedDate($from_date).' To '.getFormatedDate($to_date).')</h3>';
echo $content1;

/*require_once('../tcpdf/tcpdf.php');

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
		$pdf->Cell(0, 5, 'SURYAMITRA EXIM PVT.LTD', 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$pdf->SetFont('helvetica', 'B', '11');

		$pdf->Cell(0, 5, 'Soaking Report - '. getFormatedDate($from_date), 0, 1, 'C', 0, '', 0, false, 'T', 'M');

    $pdf->SetFont('helvetica', '', 11);
*/

	// $table_data=getP_TABLE($iconn,$date);
	 $table_data=getP_TABLE($iconn,$from_date,$to_date);

echo $table_data;
	//$pdf->writeHTML($table_data);


$file_name='SOACKING Report -'.$from_date.'-'.$to_date.'.pdf';

//$pdf->Output($file_name, 'I');


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$from_date,$to_date){
	/* $date_end=$date.'-31 23:59:59';
	 $date=$date.'-01'; */

	//$date_end=$date.' 23:59:59';
	$date_end=$to_date.' 23:59:59';


				/*<?php		$mon=$_POST['month']?>*/

	$query.="and imd.id='0'";
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
	$query
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



                <th  align="center"  width="'.$width_s.'%"><B>S No.</B></th>
				 <th  align="center" width="'.$width.'%" ><B> Tub No </B></th>
				 <th  align="center" width="'.$width.'%" ><B> Id No </B></th>
				   <th  align="center"  width="'.$width.'%"><B>Rm Date</B></th>
				   <th  align="center"  width="'.$width.'%"><B>Soaking Date</B></th>
				 <th  align="center" width="'.$width.'%" ><B>Variety</B></th>
                <th  align="center"  width="'.$width.'%" ><B>Grade </B></th>

                <th  align="center"  width="'.$width.'%" ><B>In Quantity </B></th>
                <th  align="center" width="'.$width.'%"><B>In Count </B></th>
				 <th  align="center" width="'.$width.'%" ><B> Time Period</B></th>
				<th  align="center" width="'.$width.'%" ><B>Out Count</B></th>
				<th  align="center" width="'.$width.'%" ><B>Dest</B></th>
				<th  align="center" width="'.$width.'%" ><B>Out Quantity  </B></th>


               </tr>

			  ';


		//
		while($r_lots = $stmt_lots->fetch_array()){


		 $PURCHASE_DATE=date("d-m-Y", strtotime($r_lots['PURCHASE_DATE']));
	 $CreatedDateTime=date("d-m-Y", strtotime($r_lots['SoakingDate']));
if($CreatedDateTime=='01-01-1970'){
	
	$CreatedDateTime='Not Updated';
}
				    $content .=

					'<tr>
				 <th  align="center" width="'.$width_s.'%">'.$S_NO.'</font></th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['T_NO'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['LOT_NO'].'</th>
				 <th  align="center" width="'.$width.'%">'.$PURCHASE_DATE.'</th>
				 <th  align="center" width="'.$width.'%">'.$CreatedDateTime.'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['Variety'].'</th>
				 <th  align="center" width="'.$width.'%" >'.$r_lots['COUNT'].'</th>

				 <th  align="center" width="'.$width.'%">'.$r_lots['QTY'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['C1'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['TimePeriod'].'</th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['C2'].' </th>

				 <th  align="center" width="'.$width.'%">'.$r_lots['DEST'].' </th>
				 <th  align="center" width="'.$width.'%">'.$r_lots['APP_QT'].' </th>

				 </tr>';

 $S_NO++;
}



		$content .= '</table>';


	return $content;

}

?>
