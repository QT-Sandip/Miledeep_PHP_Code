<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");



 $ID=$_GET['ID'];
 
 $sqlBoreWater="SELECT * FROM `QcIHBoreWater` WHERE ID='$ID'";   
    $stmtBoreWater=$iconn->query($sqlBoreWater);
    $RowBoreWater=$stmtBoreWater->fetch_array();
	$sample_date=date('d-m-Y',strtotime($RowBoreWater['SampleDate']));
 

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

		//$this->Cell(0, 5, 'M/S. SURYAMITRA EXIM PVT.LTD', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		

    }
   

}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  



	$pdf->SetCreator(PDF_CREATOR);  

    $pdf->SetTitle('DAILY BORE WATER ANALYSIS  REPORT');  

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

		//$pdf->Cell(0, 5, 'M/S. SURYAMITRA EXIM PVT.LTD', 0,1, 'C', 0, '', 0, false, 'T', 'M');

				    $pdf->SetFont('helvetica', 'BU', '11');  

		$pdf->Cell(0, 5, 'DAILY BORE WATER ANALYSIS  REPORT  ', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
					//$pdf->Cell('', '', $month_name, 0, 1, '');
$pdf->SetFont('helvetica', 'B', '11');  

		$pdf->Cell(0, 5,'DATE:'.$sample_date, 0, 1, 'R', 0, '', 0, false, '', '');
		//$pdf->Cell(0, 5,''.$month_name, 0, 1, 'L', 0, '', 0, false, '', '');
		$pdf->Cell(0, 5,'', 0, 1, 'L', 0, '', 0,  '', '');


	
    $pdf->SetFont('helvetica', '', 11);  


	
	// $table_data=getP_TABLE($iconn,$date);
	 $table_data=getP_TABLE($iconn,$B_TYPE,$from_date,$to_date);




	$pdf->writeHTML($table_data);
  
 
$file_name='Water Analysis -'.$from_date.'.pdf';


 
$pdf->Ln();


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$ID,$from_date,$to_date){
	global $ID;

 
	 $sqlBoreWater="SELECT * FROM `QcIHBoreWater` WHERE ID='$ID'";   
    $stmtBoreWater=$iconn->query($sqlBoreWater);
    $RowBoreWater=$stmtBoreWater->fetch_array();
	$sample_date=date('d-m-Y',strtotime($RowBoreWater['SampleDate']));

	$content='';

	$size=40;	 
 
	$size1=60;	

			  $S_NO=1;
			  $width=14;
			  $width_s=$width-5;
			  $width_w=$width+5;
              
			  
			  $content .= '<table border="1" cellspacing="0" cellpadding="3">
			   <tr>  
           <th  align="center"  width="'.$width_s.'%"><font size="9">S No.</font></th>              
		   <th  align="center"  width="'.$width.'%"><font size="9"> DATE</font></th>			
		   <th  align="center" width="'.$width.'%" ><font size="9">SAMPLE COLLECTION TIME</font></th>
           <th  align="center"  width="20%" ><font size="9">PARAMETERS </font></th>
           <th  align="center"  width="20%" ><font size="9">BORE WATER SAMPLE <BR><B> Results</B></font>
           </th>
                
		  <th  align="center"  width="'.$width.'%" ><font size="9">UNITS </font></th>
          <th  align="center"  width="10%" ><font size="9">LIMITS </font></th>
                
    
          </tr> 
			   
			  '; 
			   $content .= '
				<tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">1</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$sample_date.'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$RowBoreWater['SampleTime'].'</font></th>
				 <th  align="center" width="20%"><font size="9">P<sup>H</sup>VALUE</font></th>
				 <th  align="center" width="20%" ><font size="9">'.$RowBoreWater['PHValue'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">H+ Conc</font></th>
				 <th  align="center" width="10%"  ><font size="9">6.5 to 8.5</font></th>
				 
				 </tr>
				 
				 <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">2</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">TEMPERATURE</font></th>
				 <th  align="center" width="20%" ><font size="9">'.$RowBoreWater['Temperature'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">C</font></th>
				 <th  align="center" width="10%" ><font size="9">25 to 30</font></th>
				 
				 </tr>
				  
				  <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">3</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">COLOUR</font></th>
				 <th  align="center" width="20%" ><font size="9">Transparent</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">-</font></th>
				 <th  align="center" width="10%"  ><font size="9"></font></th>
				 
				 </tr>
				 
				 <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">4</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">SALINITY</font></th>
				 <th  align="center" width="20%" ><font size="9">'.$RowBoreWater['Salinity'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">ppt</font></th>
				 <th  align="center" width="10%"  ><font size="9">5ppt</font></th>
				 
				 </tr>		

				 <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">5</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">TOTAL DISSOLVED SOLIDS</font></th>
				 <th  align="center" width="20%" ><font size="9">'.$RowBoreWater['DissolvedSolids'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">ppm</font></th>
				 <th  align="center" width="10%"  ><font size="9">300</font></th>
				 
				 </tr>	

				 <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">6</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">HARDNESS</font></th>
				 <th  align="center" width="20%" ><font size="9">'.$RowBoreWater['Hardness'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">ppm</font></th>
				 <th  align="center" width="10%"  ><font size="9">250</font></th>
				 
				 </tr>
				 
				 <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">7</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">CHLORINE</font></th>
				 <th  align="center" width="20%" ><font size="9">'.$RowBoreWater['Chlorine'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">ppm</font></th>
				 <th  align="center" width="10%"  ><font size="9">5 ppm</font></th>
				 
				 </tr>
				 
				 ';
			  
	
		//	 
		

		$content .= '</table>';


	return $content;	
  

}
	$pdf->SetFont('helvetica', 'B', '11');  

$pdf->Cell(49 ,5,'*Remarks:Nil',0,1,'L');
$pdf->Cell(49 ,5,'',0,1,'L');

$pdf->Cell(49 ,5,'CORRECTIVE ACTION:Nil',0,1,'L');
$pdf->Cell(49 ,5,'',0,1,'L');
$pdf->Cell(0 ,5,'ANALYSED BY:'.$RowBoreWater['VerifiedBy'],0,0,'L');
$pdf->Cell(0 ,0,'VERIFIED BY:'.$RowBoreWater['AnalysiedBy'],0,1,'R');
ob_clean();
$pdf->Output($file_name, 'I');

			   

?>