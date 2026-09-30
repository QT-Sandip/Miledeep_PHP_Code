<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");



 
 $ID=$_GET['ID'];

	  $sqlSwabRprt="SELECT * from QcIHSWABBReprt where ID='$ID'";   		
			// $sqlWaterIce;
	 $stmtSwabRprt=$iconn->query($sqlSwabRprt);
	 $RowSwabRprt=$stmtSwabRprt->fetch_array();
	 $UNIT=$RowSwabRprt['UNIT'];
$Date=$newDate = date("d-m-Y", strtotime($RowSwabRprt['SampleDate']));
  //$date=$_GET['date']; 
$from_date=$_GET['from_date']; 
  $to_date=$_GET['to_date']; 
   

 //$month_name= date("F", strtotime($date)); 
 $month_name= date("F", strtotime($from_date)); 


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

		$this->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		

    }
   

}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  

	$pdf->SetCreator(PDF_CREATOR);  

    $pdf->SetTitle('SWAB REPORT');  

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

		$pdf->Cell(0, 5, 'SANITARY STANDARD ASSESSING OF UNIT SWAB '.$UNIT, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
					//$pdf->Cell('', '', $month_name, 0, 1, '');
				    $pdf->SetFont('helvetica', 'B', '11');  

		$pdf->Cell(0, 5,'DATE:'.$Date, 0, 1, 'R', 0, '', 0, false, '', '');
		$pdf->Cell(0, 5,'', 0, 1, 'L', 0, '', 0,  '', '');


	
    $pdf->SetFont('helvetica', '', 11);  


	
	// $table_data=getP_TABLE($iconn,$date);
	 $table_data=getP_TABLE($iconn,$ID,$from_date,$to_date);
	// $table_data1=getQ_TABLE($iconn,$B_TYPE,$from_date,$to_date);




	$pdf->writeHTML($table_data);
	$pdf->writeHTML($table_data1);
  
 
$file_name='SWAB -'.$from_date.'-'.$to_date.'.pdf';


 
$pdf->Ln();


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$ID,$from_date,$to_date,$UNIT){
	global $ID;

	/* $date_end=$date.'-31 23:59:59';  
	 $date=$date.'-01'; */
	
	//$date_end=$date.' 23:59:59';  
	$date_end=$to_date.' 23:59:59';  
	
	
	 $sqlSWABRslt="SELECT * FROM `QcIHSWABResult` left join QcIHSWABBReprt on 
	 QcIHSWABBReprt.ID=QcIHSWABResult.ReportID WHERE ReportID='$ID' order by SortOrder ASC";  		
			// $sqlWaterIce;
	 $stmtSWABRslt=$iconn->query($sqlSWABRslt);
	 
		 	  
	$content=''; 


	$size=40;	 
 
	$size1=60;	



			  	
			  
			  $S_NO=1;
			  $width=12;
			  $width_s=$width-5;
			  $width_w=$width+5;
			 // $NIL='Nil cfu/ml';
			 // $absent='Absent cfu/ml';
              
			  
			  $content .= '<table border="1" cellspacing="0" cellpadding="3">
			   <tr>  

           		

                <th  align="center"  width="'.$width_s.'%"><font size="9">UNIT</font></th>              
				<th  align="center"  width="'.$width.'%"><font size="9"> PRODUCT</font></th>			
				<th  align="center"  width="'.$width.'%"><font size="9"> LOCATION</font></th>			
				<th  align="center" width="'.$width.'%" ><font size="9">TPC 37<sup>o</sup>C</font></th>
                <th  align="center"  width="'.$width.'%"><font size="9">Stayphlococcusaures</font></th>
                <th  align="center"  width="'.$width.'%" ><font size="9">ECOLI 37<sup>o</sup>C</font></th>                
				<th  align="center"  width="'.$width.'%" ><font size="9">COLIFORMS 37<sup>o</sup>C </font></th>
                <th  align="center"  width="10%" ><font size="9">V.C & V.P</font></th>
                <th  align="center"  width="15%" ><font size="9">LIMIT</font></th>
                
    
               </tr> ';
			  while($RowSWABResult=$stmtSWABRslt->fetch_array()){
			$content .= '  <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">'.$RowSWABResult['UNIT'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$RowSWABResult['SampletLocation'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$RowSWABResult['Location'].'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.htmlentities($RowSWABResult['TPC']).'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$RowSWABResult['Stayphloco'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">'.$RowSWABResult['ECOLI'].'</font></th>
				 <th  align="center" width="'.$width.'%" ><font size="9">'.$RowSWABResult['COLIFORMS'].'</font></th>
				 <th  align="center" width="10%"  ><font size="9">'.$RowSWABResult['VCnVP'].'</font></th>
				 <th  align="center" width="15%"  ><font size="9">'.$RowSWABResult['LIMITS'].'</font></th>
				 
				 </tr>
				 
				 
				';
			  }
	
		//	 
		

		$content .= '</table>';
		
		
		
			 
	return $content;	
  

}
	$pdf->SetFont('helvetica', 'B', '11');  
//$pdf->Cell(49 ,5,'ENVIRONMENTAL SWAB CONTROL:',0,1,'L');
$pdf->Cell(49 ,5,'',0,1,'L');

$pdf->Cell(49 ,5,'*Remarks:'.$RowSwabRprt['Remarks'],0,1,'L');
$pdf->Cell(49 ,5,'',0,1,'L');

$pdf->Cell(49 ,5,'CORRECTIVE ACTION:'.$RowSwabRprt['CorrectiveAction'],0,1,'L');
$pdf->Cell(49 ,5,'',0,1,'L');
$pdf->Cell(0 ,5,'ANALYSED BY:'.$RowSwabRprt['AnalysedBy'],0,0,'L');
$pdf->Cell(0 ,0,'VERIFIED BY:'.$RowSwabRprt['VerifiedBy'],0,1,'R');

ob_end_clean();
$pdf->Output($file_name, 'I');

			   

?>