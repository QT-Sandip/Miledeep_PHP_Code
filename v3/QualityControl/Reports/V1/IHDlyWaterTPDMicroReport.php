<?php

session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include ($_SERVER['DOCUMENT_ROOT']."/Libraries/tcpdf/tcpdf.php");



 //include "PrintHeaderInc.php";

 $ReportID=$_GET['ID'];
 
   $SQL_MICRO="SELECT * FROM `QcIHTPCMicroAnalysis` as ma LEFT JOIN QcIHTPCMicroAnalysisRprt as mar on mar.ID=ma.ReportID WHERE ReportID='$ReportID'
  ORDER BY ma.`ID` DESC"; 
$STMT_MICRO=$iconn->query($SQL_MICRO);
$row=$STMT_MICRO->fetch_array();
$Unit=$row['Unit'];

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

		$this->Cell(0, 5, 'M/S. SURYAMITRA EXIM PVT.LTD', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		

    }
   

}



    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  



	$pdf->SetCreator(PDF_CREATOR);  

    $pdf->SetTitle('Micro,TPC,E.Coli ANALYSIS  REPORT');  

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
	

    $newDate = date("d-m-Y", strtotime($row['SampleDate']));  
	 

		$pdf->Cell(0, 5, '', 0,1, 'C', 0, '', 0, false, 'T', 'M');

		$pdf->Cell(0, 5, 'M/S. SURYAMITRA EXIM PVT.LTD', 0,1, 'C', 0, '', 0, false, 'T', 'M');

				    $pdf->SetFont('helvetica', 'BU', '11');  

		$pdf->Cell(0, 5, 'DAILY WATER ANALYSIS  REPORT  '.$Unit, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$pdf->Cell(0, 5, '(Physical Parameters & Micro-TPC,E.Coli)', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
					//$pdf->Cell('', '', $month_name, 0, 1, '');
$pdf->SetFont('helvetica', 'B', '11');  
		$pdf->Cell(0, 5,'DATE:'.$newDate, 0, 1, 'R', 0, '', 0, false, '', '');
		//$pdf->Cell(0, 5,''.$month_name, 0, 1, 'L', 0, '', 0, false, '', '');
		$pdf->Cell(0, 5,'', 0, 1, 'L', 0, '', 0,  '', '');


	
    $pdf->SetFont('helvetica', '', 11);  


	
	// $table_data=getP_TABLE($iconn,$date);
	 $table_data=getP_TABLE($iconn,$ReportID);




	$pdf->writeHTML($table_data);
  
 
$file_name='Water Analysis -'.$from_date.'.pdf';


 
$pdf->Ln();


//function getP_TABLE($iconn,$date){
function getP_TABLE($iconn,$ReportID){
	
  global $ReportID ;
 
 
 
 
  $SQL_MICRO="SELECT * FROM `QcIHTPCMicroAnalysis` as ma LEFT JOIN QcIHTPCMicroAnalysisRprt as mar on mar.ID=ma.ReportID WHERE ReportID='$ReportID'
   order by SortOrder ASC"  ; 
$STMT_MICRO=$iconn->query($SQL_MICRO);
 
while($row=$STMT_MICRO->fetch_array()){
 
 if($row['Parameter']=='SAMPLE LOCATION'){
	
$samp_loc1=	$row['Result1'];
$samp_loc2=	$row['Result2'];
$samp_loc3=	$row['Result3'];
$samp_loc4=	$row['Result4'];
 }
}
$STMT_MICRO=$iconn->query($SQL_MICRO);
	

	$content='';

	$size=40;	 
 
	$size1=60;	

			  $S_NO=1;
			  $width=14;
			  $width_s=5;
			  $width_w=10;
              $NIL='Nil-cfu/ml';
			  
			  $content .= '<table border="1" cellspacing="0" cellpadding="3">
			   <tr>  
           <th  align="center"  width="5%"><font size="9">S No.</font></th>              
		   <th  align="center"  width="10%"><font size="9"> DATE</font></th>			
		   <th  align="center" width="10%" ><font size="9">SAMPLE COLLECTION TIME</font></th>
           <th  align="center"  width="'.$width.'%" ><font size="9">PARAMETERS </font></th>
           <th  align="center"  width="10%" ><font size="9"><B> Results<br>'.$samp_loc1.'</B></font> </th>                
           <th  align="center"  width="10%" ><font size="9"><B> Results<br>'.$samp_loc2.'</B></font> </th>                
           <th  align="center"  width="10%" ><font size="9"><B> Results<br>'.$samp_loc3.'</B></font> </th>                
           <th  align="center"  width="10%" ><font size="9"><B> Results<br>'.$samp_loc4.'</B></font> </th>                
		  <th  align="center"  width="'.$width.'%" ><font size="9">UNITS </font></th>
          <th  align="center"  width="10%" ><font size="9">LIMITS </font></th>
                
    
          </tr> 
						
	   
			  '; 
while($row=$STMT_MICRO->fetch_array()){
	IF($row['Parameter']=='SAMPLE LOCATION'){
		
	}
ELSE{ 
	$newDate = date("d-m-Y", strtotime($row['SampleDate']));  
	 

	$date= $row['SampleDate'];
	$time= $row['SampleTime'];
	 if($S_NO>1){
							$newDate= '';
							$time= '';
						 }
	if($S_NO<=9){
						
			 
			   $content .= '
			   <tr>
				 <th  align="center" width="'.$width_s.'%"><font size="9">'.$S_NO.'</font></th>
				
				 <th  align="center" width="'.$width_w.'%"><font size="9">'.$newDate.'</font></th>
				 <th  align="center" width="'.$width_w.'%"><font size="9">'.$time.'</font></th>
				 <th  align="center" width="'.$width.'%"><font size="9">'.$row['Parameter'].'</font></th>
				 <th  align="center" width="'.$width_w.'%" ><font size="9">'.$row['Result1'].'</font></th>
				 <th  align="center" width="'.$width_w.'%" ><font size="9">'.$row['Result2'].'</font></th>
				 <th  align="center" width="'.$width_w.'%" ><font size="9">'.$row['Result3'].'</font></th>
				 		 <th  align="center" width="'.$width.'%" ><font size="9">'.$row['Units'].'</font></th>
				 <th  align="center" width="10%"  ><font size="9">'.$row['Limits'].'</font></th>
				 
				 </tr>
				 ';
			$S_NO++;}
}}
				 
$content .= '</table>';
	return $content;	
}
	$pdf->SetFont('helvetica', 'B', '11');  

$pdf->Cell(49 ,5,'*Remarks:Nil',0,1,'L');
$pdf->Cell(49 ,5,'',0,1,'L');

$pdf->Cell(49 ,5,'CORRECTIVE ACTION:Nil',0,1,'L');
$pdf->Cell(49 ,5,'',0,1,'L');
$pdf->Cell(0 ,5,'ANALYSED BY:'.$row['AnalysedBy'],0,0,'L');
$pdf->Cell(0 ,0,'VERIFIED BY:'.$row['VerifiedBy'],0,1,'R');

ob_end_clean();

$pdf->Output($file_name, 'I');

			   

?>