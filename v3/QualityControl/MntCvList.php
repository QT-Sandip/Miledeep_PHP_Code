<?php

session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

include($cp . "Includes/IncludePage.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");



$MONTH = $_GET['M'];
$YEAR = $_GET['Y'];
$NAME_OF_PROCESSOR = $_GET['E'];
$TYPE = $_POST['TYPE'];

/*$date_start=$YEAR.'-'.$MONTH.'-01';
$date_end=$YEAR.'-'.$MONTH.'-31 23:59:59';*/
$DateFilter = getDateFilter($YEAR, $MONTH);
$ClientName = strtoupper($_SESSION['ClientName']);



class MYPDF extends TCPDF
{


	public function Footer()
	{



		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);

		$this->SetY(-55);

	}



	public function Header()
	{



		$image_file = K_PATH_IMAGES . 'jme_logo.jpg';

		$this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);

		$this->SetFont('helvetica', 'B', 15);

		$this->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

		$this->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');


	}


}

$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);



$pdf->SetCreator(PDF_CREATOR);

$pdf->SetTitle('EXTERNAL LAB REGISTER Monthly List : ');

$pdf->SetHeaderData('', '', '', '');

$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));

$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->SetDefaultMonospacedFont('helvetica');

$pdf->SetFooterMargin(45);

$pdf->SetHeaderMargin(15);

$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT, 45);

$pdf->setPrintHeader(false);

$pdf->setPrintFooter(false);



$pdf->SetAutoPageBreak(TRUE, 40);

$pdf->SetFont('helvetica', '', 11);

$pdf->AddPage(L);




$pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');

$pdf->Cell(0, 5, $ClientName, 0, 1, 'C', 0, '', 0, false, 'T', 'M');

$pdf->Cell(0, 5, 'EXTERNAL LAB REGISTER MONTHLY REPORT', 0, 1, 'C', 0, '', 0, false, 'T', 'M');




$table_data = getP_TABLE($iconn, $NAME_OF_PROCESSOR, $DateFilter);


$pdf->writeHTML($table_data);


$file_name = 'LabRegister' . $row['PO_NUM'] . '.pdf';


ob_end_clean();
$pdf->Output($file_name, 'I');


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn, $NAME_OF_PROCESSOR, $DateFilter)
{

	global $TblQcSamplingLettersExt;
	global $TblScrmPoData;
	global $TblScrmConsigneeDetails;

	$sql_lots = "
	 SELECT TQSLE.SMPLID AS SID,TQSLE.TestingLab, TQSLE.SampleNumber ,
	 TQSLE.Date,TQSLE.AnalysisReq,TSPD.PoNo ,TSPD.ConsigneeID ,TSPD.ProcessorID,TSCD.ConsigneeName
	 FROM  `$TblQcSamplingLettersExt`  TQSLE
	 LEFT JOIN $TblScrmPoData  TSPD ON TQSLE.InvID= TSPD.PoID 
	 LEFT JOIN $TblScrmConsigneeDetails  TSCD ON TSPD.ConsigneeID= TSCD.ConID 
	 WHERE TSPD.ProcessorID= '$NAME_OF_PROCESSOR' 
	 and TQSLE.Date $DateFilter
	";

	//echo  $sql_lots;

	$stmt_lots = $iconn->query($sql_lots);

	$content = '';


	$PO_TMSP = strtotime($date);
	$PURCHASE_DATE = date("d-m-Y", $PO_TMSP);

	$size = 40;

	$size1 = 60;


	$content .= '<table border="1" cellspacing="0" cellpadding="3">	


				

				<tr>  

           		

                <th  align="center" width="5%"><font size="9">S No</font></th>
                <th  align="center" width="8%"><font size="9">SENT DATE</font></th>

                <th  align="center" width="10%"><font size="9">LAB NAME	</font></th>

				<th  align="center" width="10%"><font size="9">SME NO</font></th>
				<th  align="center" width="15%"><font size="9">BUYER NAME</font></th>

                <th  align="center" width="10%"><font size="9">PO.NO.</font></th>
                <th  align="center" width="22%"><font size="9">TESTING PARAMETERS</font></th>
                <th  align="center" width="10%"><font size="9">PAYMENT DATE</font></th>
                <th  align="center" width="10%"><font size="9">AMOUNT</font></th>

   
               </tr>';


	$C = 0;
	$S_NO = 1;

	while ($r_lots = $stmt_lots->fetch_array()) {
		$SAMPLE_REG_ID = $r_lots['SID'];

		$sql_analysis = "SELECT DISTINCT(Type) FROM QcEiaAnalysisRequired WHERE ReportId='$SAMPLE_REG_ID'";
		$stmt_analysis = $iconn->query($sql_analysis);

		//echo $sql_analysis;

		$a_req = '';
		while ($r_analysis = $stmt_analysis->fetch_array()) {
			if ($a_req == '') {
				$a_req = $r_analysis['Type'];
			} else {
				$a_req .= ',' . $r_analysis['Type'];
			}


		}

		$SentDate = $r_lots['Date'];
		$LtrSentDate = date("d-m-Y", strtotime($SentDate));

		$content .= '
			
				
				<tr>  

				<th  align="center"  width="5%"><font size="9">' . $S_NO . '</font></th>
				<th  align="center"  width="8%"><font size="9">' . $LtrSentDate . '</font></th>
				<th  align="center"  width="10%"><font size="9">' . $r_lots['TestingLab'] . '</font></th>
                <th  align="center" width="10%"><font size="9">' . $r_lots['SampleNumber'] . '</font></th>
                <th  align="center" width="15%"><font size="9">' . $r_lots['ConsigneeName'] . '</font></th>
                <th  align="center" width="10%"><font size="9">' . $r_lots['PoNo'] . '</font></th>
				<th  align="center" width="22%"><font size="9">' . $a_req . '</font></th> 
				<th  align="center" width="10%"><font size="9">15/04/24</font></th> 
				<th  align="center" width="10%"><font size="9">2500</font></th> 
				
				</tr>';

		$C++;
		$S_NO++;
	}


	$content .= '<tr>
<th  align="center" width="100%"><font size="9"></font></th>
				</tr>

				
		<tr> 
           		

                <th  align="center" width="50%"><font size="9">Total Letters</font></th>
                <th  align="center" width="50%"><font size="9">' . $C . '</font></th>

              
   
               </tr>';

	$content .= '</table>';


	return $content;

}

?>