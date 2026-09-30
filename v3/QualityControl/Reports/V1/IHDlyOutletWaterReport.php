<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");


 $ID=$_GET['ID'];
 
 
	$sqlOutletWater="SELECT * FROM `QcOutletWaterAnalysis` WHERE ID='$ID'";  
    $stmtOutletWater=$iconn->query($sqlOutletWater);
    $RowOutletWater=$stmtOutletWater->fetch_array();
$SamplINGDate = date("d-m-Y", strtotime($RowOutletWater['SampleDate']));
 
//require_once('../tcpdf/tcpdf.php'); 
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");


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

    $pdf->SetTitle('DAILY ETP OUTLET WATER SAMPLE ANALYSIS REPORT');  

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

		$pdf->Cell(0, 5, 'DAILY ETP OUTLET WATER SAMPLE ANALYSIS REPORT  ', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
					//$pdf->Cell('', '', $month_name, 0, 1, '');
$pdf->SetFont('helvetica', 'B', '11');  
//$pdf->Cell(49 ,5,'*Remarks:Nil',0,1,'L');
		
		$pdf->Cell(10, 5,'DATE:'.$SamplINGDate , 0, 1, 'C', 0, '', 0, false, '', 'R');
		//$pdf->Cell(0, 5,''.$month_name, 0, 1, 'L', 0, '', 0, false, '', '');
		$pdf->Cell(0, 5,'', 0, 1, 'L', 0, '', 0,  '', '');


	
    $pdf->SetFont('helvetica', '', 11);  


	
	// $table_data=getP_TABLE($iconn,$date);
	 $table_data=getP_TABLE($iconn,$B_TYPE,$from_date,$to_date);




	$pdf->writeHTML($table_data);
  
 
$file_name='Outlet Water Analysis -'.$from_date.'.pdf';


 
$pdf->Ln();


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$ID,$from_date,$to_date){
	global $ID;

 
	$sqlOutletWater="SELECT * FROM `QcOutletWaterAnalysis` WHERE ID='$ID'";  
    $stmtOutletWater=$iconn->query($sqlOutletWater);
    $RowOutletWater=$stmtOutletWater->fetch_array();

	$content='';

	$size=40;	 
 
	$size1=60;	

			  $S_NO=1;
			  $width=14;
			  $Swidth=8;
			  $width_s=$width-7;
			  $width_w=$width+5;
              $SamplDate = date("d-m-Y", strtotime($RowOutletWater['SampleDate']));
			  
			  $content .= '<table border="1" cellspacing="0" cellpadding="3">
			   <tr>  
           <th  align="center"  width="'.$width_s.'%"><font size="9">S No.</font></th>              
		   <th  align="center"  width="'.$width.'%"><font size="9"> DATE</font></th>			
		   <th  align="center" width="'.$width.'%" ><font size="9">SAMPLE COLLECTION TIME</font></th>
           <th  align="center"  width="20%" ><font size="9">PARAMETERS </font></th>
           <th  align="center"  width="15%" ><font size="9">Results</font></th>           
		   <th  align="center"  width="15%" ><font size="9">LIMITS </font></th>
                
    
          </tr> 
			   
			  '; 
			   $content .= '
				<tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">1</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$SamplDate.'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$RowOutletWater['SampleTime'].'</font></th>
				 <th  align="center" width="20%"><font size="9">P<sup>H</sup> VALUE</font></th>
				 <th  align="center" width="15%" ><font size="9">'.$RowOutletWater['PHValue'].'</font></th>
				 <th  align="center" width="15%"  ><font size="9">5.5 to 9.0</font></th>
				 
				 </tr>
				 
				 <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">2</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">TEMPERATURE</font></th>
				 <th  align="center" width="15%" ><font size="9">'.$RowOutletWater['Temperature'].'</font></th>
				 <th  align="center" width="15%" ><font size="9">25 to 37<sup>O</sup>C</font></th>
				 
				 </tr>
				  
				  <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">3</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">COLOUR</font></th>
				 <th  align="center" width="15%" ><font size="9">'.$RowOutletWater['Colour'].'</font></th>
				 <th  align="center" width="15%"  ><font size="9">-</font></th>
				 
				 </tr>
				 
				 <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">4</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">SALINITY</font></th>
				 <th  align="center" width="15%" ><font size="9">'.$RowOutletWater['Salinity'].'</font></th>
				 <th  align="center" width="15%"  ><font size="9">20ppt</font></th>
				 
				 </tr>		

				 <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">5</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">TOTAL DISSOLVED SOLIDS(TDS)</font></th>
				 <th  align="center" width="15%" ><font size="9">'.$RowOutletWater['TDS'].'</font></th>
				 <th  align="center" width="15%"  ><font size="9">2100mg/l</font></th>
				 
				 </tr>	

				 <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">6</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">HARDNESS</font></th>
				 <th  align="center" width="15%" ><font size="9">'.$RowOutletWater['Hardness'].'</font></th>
				 <th  align="center" width="15%"  ><font size="9">75-250ppm</font></th>
				 
				 </tr>
				 
				 <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">7</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">TOTAL SUSPENDED SOLIDS(TSS)</font></th>
				 <th  align="center" width="15%" ><font size="9">'.$RowOutletWater['TSS'].'</font></th>
				 <th  align="center" width="15%"  ><font size="9">200mg/l</font></th>
				 
				 </tr>
				 
				 <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">8</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">CHLORINE</font></th>
				 <th  align="center" width="15%" ><font size="9">'.$RowOutletWater['Chlorine'].'</font></th>
				 <th  align="center" width="15%"  ><font size="9">5 ppm</font></th>
				 
				 </tr>
				 <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">9</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">CHEMICAL OXYGEN DEMAND(COD)</font></th>
				 <th  align="center" width="15%" ><font size="9">'.$RowOutletWater['COD'].'</font></th>
				 <th  align="center" width="15%"  ><font size="9">250 mg/l</font></th>
				 
				 </tr>
				 
				 <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">10</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="'.$width.'%"><font size="9"></font></th>
				 <th  align="center" width="20%"><font size="9">5-DAYS-BOD(BIOLOGICAL OXYGEN DEMAND)</font></th>
				 <th  align="left" width="30%" ><font size="9"></font>
				 <table border="1" cellspacing="0" cellpadding="0">
				 <tr>
				 <th  align="center" width="25%"><font size="9">Inlet Water</font></th>
				 <th  align="center" width="25%"><font size="9">Outlet Water</font></th>
				 <th  align="center" width="25%"><font size="9">Inlet Water</font></th>
				 <th  align="center" width="25%"><font size="9">Outlet Water</font></th>
				 </tr>
				 
				 <tr>
				 <th  align="center" width="25%"><font size="9">'.$RowOutletWater['BOD_Inlet'].'</font></th>
				 <th  align="center" width="25%"><font size="9">'.$RowOutletWater['BOD_Outlet'].'</font></th>
				 <th  align="center" width="25%"><font size="9"></font></th>
				 <th  align="center" width="25%"><font size="9">30 mg/l</font></th>
				 </tr>
				 
				 
				 
				 
				 </table>
				 </th>

				 </tr>
				 ';
			  
	
		//	 
		

		$content .= '</table>';


	return $content;	
  

}
	$pdf->SetFont('helvetica', 'B', '11');  

$pdf->Cell(49 ,5,'*Remarks:'.$RowOutletWater['Remarks'],0,1,'L');
$pdf->Cell(49 ,5,'',0,1,'L');

$pdf->Cell(49 ,5,'CORRECTIVE ACTION:'.$RowOutletWater['CorrectiveAction'],0,1,'L');
$pdf->Cell(49 ,5,'',0,1,'L');
$pdf->Cell(0 ,5,'ANALYSED BY:'.$RowOutletWater['AnalysedBy'],0,0,'L');
$pdf->Cell(0 ,0,'VERIFIED BY:'.$RowOutletWater['VerifiedBy'],0,1,'R');

ob_end_clean();
$pdf->Output($file_name, 'I');

			   

?>