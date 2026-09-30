<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

 //include "PrintHeaderInc.php";

 $ID=$_GET['ID'];
 
 $sqlMBReport="SELECT * FROM `QcIHMBReport` WHERE ID='$ID'";  
    $stmtMBReport=$iconn->query($sqlMBReport);
     $RowMBReport=$stmtMBReport->fetch_array();

 $sqlMBResult="SELECT * FROM `QcIHMBParmResult` as mb 
LEFT JOIN ScrmPoData as inv on inv.PoID=mb.InvID
LEFT JOIN ScrmConsigneeDetails as scrm on scrm.ConID=inv.ConsigneeID
where ReportID='$ID' ";  
    $stmtMBResult=$iconn->query($sqlMBResult);
    $RowMBResult=$stmtMBResult->fetch_array();
				  $AnalsisDate=date('d-m-Y',strtotime($RowMBResult['AnalysisDate']));
 
require_once('../tcpdf/tcpdf.php'); 

class MYPDF extends TCPDF {

	public function Footer() {

      

		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);

		$this->SetY(-55);

   }

   

    public function Header() {	

	 

        $image_file = K_PATH_IMAGES.'sme_logo.jpg';

        $this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);

        $this->SetFont('helvetica', 'B', 15);

		$this->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		$this->Cell(0, 5, 'M/S. SURYAMITRA EXIM PVT.LTD', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		

    }
   

}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  



	$pdf->SetCreator(PDF_CREATOR);  

    $pdf->SetTitle('MICRO BIOLOGY REPORT');  

    $pdf->SetHeaderData('', '', '', '');  

    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  

    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  

    $pdf->SetDefaultMonospacedFont('helvetica');  

    $pdf->SetFooterMargin(15); 

	$pdf->SetHeaderMargin(15);	

    $pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT,15);  

    $pdf->setPrintHeader(True);  

    $pdf->setPrintFooter(false);  

	

    $pdf->SetAutoPageBreak(TRUE,15);  

    $pdf->SetFont('helvetica', '', 11);  

    $pdf->AddPage('l');  
	

	 

		$pdf->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		$pdf->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

				    $pdf->SetFont('helvetica', 'BU', '11');  
                    $pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		
		$pdf->Cell(0, 5, 'IN HOUSE MICROBIOLOGY TEST REPORT '.$RowMBReport['UnitName'], 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$pdf->SetFont('helvetica', 'B', '11');  

		$pdf->Cell(0, 5,'DATE:'.$AnalsisDate, 0, 1, 'R', 0, '', 0, false, '', '');
					//$pdf->Cell('', '', $month_name, 0, 1, '');

		//$pdf->Cell(0, 5,''.$month_name, 0, 1, 'L', 0, '', 0, false, '', '');
		$pdf->Cell(0, 5,'', 0, 1, 'L', 0, '', 0,  '', '');


	
    $pdf->SetFont('helvetica', '', 11);  


	
	// $table_data=getP_TABLE($iconn,$date);
	 $table_data=getP_TABLE($iconn,$InvID,$Date);




	$pdf->writeHTML($table_data);
  
 
$file_name='IH MICROBIOLOGY'.$from_date.'.pdf';


 
$pdf->Ln();

 

//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$InvID,$Date){
	global $ID;

 
    
	
	
	
	
	/* $sqlMBResult="SELECT * FROM `QcIHMBParmResult` as mb 
LEFT JOIN INVOICE_DATA as inv on inv.ID=mb.InvID
LEFT JOIN ScrmConsigneeDetails as scrm on scrm.ConID=inv.CONSIGNEE_ID
where ReportID='$ID'  order by SortOrder ASC";  */ 
   $sqlMBResult="SELECT * FROM `QcIHMBParmResult` as mb 
LEFT JOIN ScrmPoData as inv on inv.PoID=mb.InvID
LEFT JOIN ScrmConsigneeDetails as scrm on scrm.ConID=inv.ConsigneeID
where ReportID='$ID'  order by SortOrder ASC";   
    $stmtMBResult=$iconn->query($sqlMBResult);
    
	

	$content='';

	$size=40;	 
 
	$size1=60;	

			  $S_NO=1;
			  $width=15;
			  $width_s=4;
			  $width_w=15;
              $S_NO=1;
			  
			  $content .= '<table border="1" cellspacing="0" cellpadding="3">
			   <tr>  
           <th  align="center"  width="'.$width_s.'%"><font size="9">S No.</font></th>              
		   <th  align="center"  width="'.$width.'%"><font size="9"> PARAMETERS </font></th>			
		   
		  <th  align="center"  width="'.$width_w.'%" ><font size="9">ANALYSIS COMPLETION DATES </font></th>
          <th  align="center"  width="'.$width_w.'%" ><font size="9">DATE OF ANALYSIS </font></th>
          <th  align="center"  width="'.$width_w.'%" ><font size="9">RESULT</font></th>
          <th  align="center"  width="'.$width_w.'%" ><font size="9">BUYER NAME </font></th>
          <th  align="center"  width="'.$width_w.'%" ><font size="9"> PO NO</font></th>
                
    
          </tr> 
			   
			  '; 
			  while($RowMBResult=$stmtMBResult->fetch_array()){
				  $AnalsisDate=date('d-m-Y',strtotime($RowMBResult['AnalysisDate']));
			   $content .= '
				<tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">'.$S_NO.'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$RowMBResult['Parameter'].'</font></th>
				 <th  align="center" width="'.$width_w.'%"><font size="9">'.$RowMBResult['AnalysCmplDates'].'</font></th>
				 <th  align="center" width="'.$width_w.'%"><font size="9">'.$AnalsisDate.'</font></th>
				 <th  align="center" width="'.$width_w.'%" ><font size="9">'.$RowMBResult['Result'].'</font></th>
				 <th  align="center" width="'.$width_w.'%" ><font size="9">'.$RowMBResult['ConsigneeName'].'</font></th>
				 <th  align="center" width="'.$width_w.'%"  ><font size="9">'.$RowMBResult['PoNo'].'</font></th>
				 
				 </tr>
				 
				 ';
			  
			 $S_NO++; }
		//	 
		

		$content .= '</table>';


	return $content;	
  

}
	$pdf->SetFont('helvetica', 'B', '11');  

//$pdf->Cell(49 ,5,'*Remarks:Nil',0,1,'L');
$pdf->Cell(49 ,5,'',0,1,'L');

//$pdf->Cell(49 ,5,'CORRECTIVE ACTION:Nil',0,1,'L');
$pdf->Cell(49 ,5,'',0,1,'L');
$pdf->Cell(0 ,5,'ANALYSED BY:'.$RowMBReport['AnalysedBy'],0,0,'L');
$pdf->Cell(0 ,0,'VERIFIED BY:'.$RowMBReport['VerifiedBy'],0,1,'R');

ob_end_clean();

$pdf->Output($file_name, 'I');

			   

?>